<?php

namespace App\Console\Commands;

use App\Models\Kas;
use App\Models\Setting;
use App\Models\Tabungan;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Signature('tabungan:rollback-admin {tahun? : Tahun pemotongan yang akan di-rollback (default: tahun berjalan)} {--tanggal= : Filter tanggal transaksi YYYY-MM-DD (misal: 2026-11-15)} {--dry-run : Simulasi tanpa mengubah database} {--force : Langsung eksekusi tanpa konfirmasi interaktif}')]
#[Description('Rollback pemotongan biaya administrasi tabungan tahunan menggunakan jurnal balik (Opsi B / Reversal) secara aman dan idempotent')]
class RollbackAdminTabunganCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tahun = $this->argument('tahun') ? (int) $this->argument('tahun') : (int) date('Y');
        $tanggal = $this->option('tanggal') ? trim((string) $this->option('tanggal')) : null;
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        if ($tanggal !== null && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            $this->error('Format opsi --tanggal harus YYYY-MM-DD (contoh: 2026-11-15).');

            return self::FAILURE;
        }

        $admQuery = Transaksi::query()
            ->where('transable_type', Tabungan::class)
            ->where('kode', 'like', "ADM{$tahun}%")
            ->orderBy('created_at')
            ->orderBy('id');

        $mtxQuery = Transaksi::query()
            ->where('transable_type', Kas::class)
            ->where('kode', 'like', "MTX{$tahun}%")
            ->where('keterangan', 'like', "Penerimaan Administrasi % Tahun {$tahun} - %")
            ->orderBy('created_at')
            ->orderBy('id');

        if ($tanggal !== null) {
            $admQuery->whereDate('created_at', $tanggal);
            $mtxQuery->whereDate('created_at', $tanggal);
        }

        $allAdms = $admQuery->get();
        $allMtxs = $mtxQuery->get();

        if ($allAdms->isEmpty() && $allMtxs->isEmpty()) {
            $this->warn("Tidak ditemukan transaksi pemotongan ADM{$tahun} yang perlu di-rollback.");

            return self::SUCCESS;
        }

        // Filter idempotency: abaikan transaksi yang sudah memiliki pasangan jurnal balik MRV / KRV
        $existingMrvKodes = Transaksi::query()
            ->whereIn('kode', $allAdms->map(fn (Transaksi $t): string => $this->buildTabunganReversalKode((string) $t->kode))->all())
            ->pluck('kode')
            ->flip();

        $existingKrvKodes = Transaksi::query()
            ->whereIn('kode', $allMtxs->map(fn (Transaksi $t): string => $this->buildKasReversalKode((string) $t->kode))->all())
            ->pluck('kode')
            ->flip();

        $pendingAdms = $allAdms
            ->filter(fn (Transaksi $t): bool => ! $existingMrvKodes->has($this->buildTabunganReversalKode((string) $t->kode)))
            ->values();

        $pendingMtxs = $allMtxs
            ->filter(fn (Transaksi $t): bool => ! $existingKrvKodes->has($this->buildKasReversalKode((string) $t->kode)))
            ->values();

        if ($pendingAdms->isEmpty() && $pendingMtxs->isEmpty()) {
            $this->info("Seluruh transaksi pemotongan ADM{$tahun} sudah pernah di-rollback sebelumnya. Tidak ada perubahan dilakukan.");

            return self::SUCCESS;
        }

        $totalNominalAdm = (int) $pendingAdms->sum(fn (Transaksi $t): int => abs((int) $t->jumlah));
        $totalNominalMtx = (int) $pendingMtxs->sum(fn (Transaksi $t): int => abs((int) $t->jumlah));

        if ($pendingAdms->count() !== $pendingMtxs->count() || $totalNominalAdm !== $totalNominalMtx) {
            $this->error(
                "Data tidak konsisten! Jumlah ADM belum rollback: {$pendingAdms->count()} (Rp ".number_format($totalNominalAdm, 0, ',', '.').'), '
                ."sedangkan MTX belum rollback: {$pendingMtxs->count()} (Rp ".number_format($totalNominalMtx, 0, ',', '.').'). '
                .'Proses dibatalkan demi keamanan data.'
            );

            return self::FAILURE;
        }

        $tabungans = Tabungan::query()
            ->with(['kas', 'siswa'])
            ->whereIn('id', $pendingAdms->pluck('transable_id')->unique()->all())
            ->get()
            ->keyBy('id');

        $missingTabungans = $pendingAdms->filter(fn (Transaksi $t): bool => ! $tabungans->has($t->transable_id));
        if ($missingTabungans->isNotEmpty()) {
            $this->error("Terdapat {$missingTabungans->count()} transaksi ADM yang rekening tabungannya tidak ditemukan. Proses dibatalkan.");

            return self::FAILURE;
        }

        // Hitung rencana koreksi saldo per Kas (dinamis dari data transaksi)
        $kasDeltas = $this->calculateKasDeltas($pendingAdms, $pendingMtxs, $tabungans);
        $kasModels = Kas::query()->whereIn('id', array_keys($kasDeltas))->get()->keyBy('id');

        $this->renderSummary($tahun, $tanggal, $pendingAdms, $tabungans, $kasDeltas, $kasModels, $totalNominalAdm, $dryRun);

        if ($dryRun) {
            $this->info('Mode --dry-run selesai. Tidak ada perubahan pada database.');

            return self::SUCCESS;
        }

        if (! $force && ! $this->confirm("Lanjutkan eksekusi jurnal balik (Opsi B) untuk {$pendingAdms->count()} rekening dengan total Rp ".number_format($totalNominalAdm, 0, ',', '.').'?')) {
            $this->warn('Eksekusi rollback dibatalkan oleh pengguna.');

            return self::SUCCESS;
        }

        $userId = $this->resolveUserId($pendingAdms->first()?->user_id);

        DB::transaction(function () use ($tahun, $pendingAdms, $pendingMtxs, $tabungans, $totalNominalAdm, $userId): void {
            $count = $pendingAdms->count();

            for ($i = 0; $i < $count; $i++) {
                /** @var Transaksi $adm */
                $adm = $pendingAdms[$i];
                /** @var Transaksi $mtx */
                $mtx = $pendingMtxs[$i];

                $nominal = abs((int) $adm->jumlah);
                $nominalMtx = abs((int) $mtx->jumlah);

                if ($nominal !== $nominalMtx) {
                    throw new \RuntimeException("Nominal pasangan transaksi {$adm->kode} ({$nominal}) dan {$mtx->kode} ({$nominalMtx}) tidak sama.");
                }

                $mrvKode = $this->buildTabunganReversalKode((string) $adm->kode);
                $krvKode = $this->buildKasReversalKode((string) $mtx->kode);

                // Pengaman ganda di dalam transaksi database
                if (Transaksi::where('kode', $mrvKode)->exists() || Transaksi::where('kode', $krvKode)->exists()) {
                    continue;
                }

                /** @var Tabungan $tabungan */
                $tabungan = $tabungans->get($adm->transable_id);
                $kasPenerimaId = (string) $mtx->transable_id;

                // 1. Jurnal balik pada Riwayat Tabungan (kode diawali 'M' agar tampil sebagai dana masuk '+' di UI siswa & admin)
                Transaksi::create([
                    'kode' => $mrvKode,
                    'transable_type' => Tabungan::class,
                    'transable_id' => $tabungan->id,
                    'jumlah' => $nominal,
                    'keterangan' => "[REVERSAL] Koreksi / Pengembalian {$adm->keterangan} [Ref: {$adm->kode}]",
                    'user_id' => $userId,
                ]);

                // 2. Jurnal balik pada Buku Besar Kas penerima (kode diawali 'K' & minus agar tampil sebagai koreksi keluar '-')
                Transaksi::create([
                    'kode' => $krvKode,
                    'transable_type' => Kas::class,
                    'transable_id' => $kasPenerimaId,
                    'jumlah' => -$nominal,
                    'keterangan' => "[REVERSAL] Koreksi / Pengembalian {$mtx->keterangan} [Ref: {$mtx->kode}]",
                    'user_id' => $userId,
                ]);

                // 3. Kembalikan saldo tabungan HANYA untuk rekening yang dipotong
                Tabungan::where('id', $tabungan->id)->increment('saldo', $nominal);

                // 4. Kembalikan saldo Kas sumber & Kas penerima (membalik ProsesAdminTabungan::prosesTransaksi)
                if ((string) $tabungan->kas_id !== $kasPenerimaId) {
                    if ($tabungan->kas_id) {
                        Kas::where('id', $tabungan->kas_id)->increment('saldo', $nominal);
                    }

                    Kas::where('id', $kasPenerimaId)->decrement('saldo', $nominal);
                }
            }

            // 5. Pertahankan penanda setting tahun ini agar OtomatisPotongAdminTabungan TIDAK memicu pemotongan ulang!
            $setting = Setting::where('key', 'tabungan_admin_'.$tahun)->first();
            $existingPayload = is_array($setting?->payload) ? $setting->payload : [];

            Setting::updateOrCreate(
                ['key' => 'tabungan_admin_'.$tahun],
                [
                    'payload' => array_merge($existingPayload, [
                        'tahun' => $tahun,
                        'status' => 'rolled_back_already_processed',
                        'metode_rollback' => 'reversal_jurnal_balik',
                        'tanggal_rollback' => now()->toDateTimeString(),
                        'total_rekening_rollback' => $count,
                        'total_nominal_rollback' => $totalNominalAdm,
                        'user_id_rollback' => $userId,
                    ]),
                ]
            );
        });

        // Pastikan cache tahun berjalan tetap terpasang sampai pergantian tahun berikutnya
        $sisaDetikSampaiAkhirTahun = max(3600, (int) (strtotime(($tahun + 1).'-01-01 00:00:00') - time()));
        Cache::put('tabungan_admin_checked_'.$tahun, true, $sisaDetikSampaiAkhirTahun);

        Log::info("RollbackAdminTabungan: Berhasil melakukan jurnal balik (Opsi B) untuk tahun {$tahun}. {$pendingAdms->count()} rekening dikembalikan dengan total Rp ".number_format($totalNominalAdm, 0, ',', '.').'.');

        $this->info("Rollback (Opsi B / Jurnal Balik) tahun {$tahun} BERHASIL! {$pendingAdms->count()} rekening dikembalikan (Total: Rp ".number_format($totalNominalAdm, 0, ',', '.').').');
        $this->line("Penanda setting 'tabungan_admin_{$tahun}' tetap dipertahankan agar pemotongan otomatis tidak berjalan lagi tahun ini.");

        return self::SUCCESS;
    }

    /**
     * Hitung perubahan saldo masing-masing Kas untuk preview dan validasi.
     *
     * @param  Collection<int, Transaksi>  $pendingAdms
     * @param  Collection<int, Transaksi>  $pendingMtxs
     * @param  Collection<string, Tabungan>  $tabungans
     * @return array<string, int>
     */
    private function calculateKasDeltas(Collection $pendingAdms, Collection $pendingMtxs, Collection $tabungans): array
    {
        $deltas = [];
        $count = $pendingAdms->count();

        for ($i = 0; $i < $count; $i++) {
            /** @var Transaksi $adm */
            $adm = $pendingAdms[$i];
            /** @var Transaksi $mtx */
            $mtx = $pendingMtxs[$i];

            $nominal = abs((int) $adm->jumlah);
            /** @var Tabungan $tabungan */
            $tabungan = $tabungans->get($adm->transable_id);
            $kasTabunganId = (string) $tabungan->kas_id;
            $kasPenerimaId = (string) $mtx->transable_id;

            if ($kasTabunganId !== $kasPenerimaId) {
                $deltas[$kasTabunganId] = ($deltas[$kasTabunganId] ?? 0) + $nominal;
                $deltas[$kasPenerimaId] = ($deltas[$kasPenerimaId] ?? 0) - $nominal;
            }
        }

        return $deltas;
    }

    /**
     * Tampilkan ringkasan transaksi yang akan di-rollback.
     *
     * @param  Collection<int, Transaksi>  $pendingAdms
     * @param  Collection<string, Tabungan>  $tabungans
     * @param  array<string, int>  $kasDeltas
     * @param  Collection<string, Kas>  $kasModels
     */
    private function renderSummary(
        int $tahun,
        ?string $tanggal,
        Collection $pendingAdms,
        Collection $tabungans,
        array $kasDeltas,
        Collection $kasModels,
        int $totalNominal,
        bool $dryRun
    ): void {
        $modeLabel = $dryRun ? 'SIMULASI (DRY-RUN)' : 'EKSEKUSI ROLLBACK (OPSI B - JURNAL BALIK)';
        $this->info("=== {$modeLabel} ===");
        $this->line("Tahun          : {$tahun}");
        $this->line('Filter Tanggal : '.($tanggal ?? 'Semua tanggal di tahun '.$tahun));
        $this->line("Total Rekening : {$pendingAdms->count()} rekening terdampak");
        $this->line('Total Nominal  : Rp '.number_format($totalNominal, 0, ',', '.'));
        $this->newLine();

        $this->line('Dampak Perubahan Saldo Kas:');
        $kasRows = [];
        foreach ($kasDeltas as $kasId => $delta) {
            /** @var Kas|null $kas */
            $kas = $kasModels->get($kasId);
            $saldoSekarang = (int) ($kas?->saldo ?? 0);
            $saldoSetelah = $saldoSekarang + $delta;
            $kasRows[] = [
                $kas?->nama ?? $kasId,
                $kasId,
                ($delta >= 0 ? '+' : '-').'Rp '.number_format(abs($delta), 0, ',', '.'),
                'Rp '.number_format($saldoSekarang, 0, ',', '.'),
                'Rp '.number_format($saldoSetelah, 0, ',', '.'),
            ];
        }
        $this->table(['Nama Kas', 'ID Kas', 'Koreksi', 'Saldo Saat Ini', 'Saldo Setelah Rollback'], $kasRows);

        $this->line('Sampel Rekening Tabungan Terdampak (maks. 5 pertama):');
        $sampleRows = [];
        foreach ($pendingAdms->take(5) as $adm) {
            /** @var Tabungan|null $tabungan */
            $tabungan = $tabungans->get($adm->transable_id);
            $nominal = abs((int) $adm->jumlah);
            $saldoSekarang = (int) ($tabungan?->saldo ?? 0);
            $sampleRows[] = [
                $adm->kode,
                $this->buildTabunganReversalKode((string) $adm->kode),
                $tabungan?->siswa?->nama ?? '-',
                $tabungan?->kas?->nama ?? '-',
                '+Rp '.number_format($nominal, 0, ',', '.'),
                'Rp '.number_format($saldoSekarang, 0, ',', '.'),
                'Rp '.number_format($saldoSekarang + $nominal, 0, ',', '.'),
            ];
        }
        $this->table(['Kode Asal', 'Kode Reversal', 'Siswa', 'Kas Tabungan', 'Pengembalian', 'Saldo Sekarang', 'Saldo Setelah'], $sampleRows);
    }

    private function buildTabunganReversalKode(string $admKode): string
    {
        return 'MRV'.substr($admKode, 3);
    }

    private function buildKasReversalKode(string $mtxKode): string
    {
        return 'KRV'.substr($mtxKode, 3);
    }

    private function resolveUserId(?string $preferredUserId): string
    {
        if ($preferredUserId && User::where('id', $preferredUserId)->exists()) {
            return $preferredUserId;
        }

        return (string) (User::where('role_id', 1)->value('id') ?? User::first()?->id);
    }
}
