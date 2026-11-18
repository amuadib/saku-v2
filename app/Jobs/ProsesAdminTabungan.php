<?php

namespace App\Jobs;

use App\Models\Kas;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\Tabungan;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\WhatsappService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProsesAdminTabungan implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     *
     * @param  string|null  $user_id  User ID petugas/sistem yang mengeksekusi
     * @param  int|null  $tahun  Tahun pemotongan (default: tahun berjalan)
     * @param  bool  $force  Paksa proses meskipun tahun ini sudah pernah dicatat
     */
    public function __construct(
        public ?string $user_id = null,
        public ?int $tahun = null,
        public bool $force = false
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tahun = $this->tahun ?: (int) date('Y');

        // Pengecekan efisien: Jika tahun ini sudah pernah diproses dan tidak dipaksa (--force), segera keluar
        if (! $this->force && $this->sudahDiprosesTahunIni($tahun)) {
            Log::info("ProsesAdminTabungan: Pemotongan administrasi tabungan tahun {$tahun} sudah pernah diproses. Melewati eksekusi.");

            return;
        }

        $userId = $this->resolveUserId();
        $kasPotonganId = config('custom.tabungan.potongan.kas_admin_id');
        $potongan = (int) (config('custom.tabungan.potongan.jumlah_per_tahun') ?? 10000);
        $saldoMinimal = (int) (config('custom.tabungan.potongan.min_saldo_tidak_kena_admin') ?? 50000);
        $lembagaList = config('custom.tabungan.potongan.lembaga') ?? [1, 2];

        if (empty($kasPotonganId) || ! Kas::where('id', $kasPotonganId)->exists()) {
            $kasPotonganId = Kas::where('nama', 'like', '%Administrasi%')->value('id')
                ?? Kas::first()?->id;
        }

        if (! $kasPotonganId || $potongan <= 0) {
            Log::warning('ProsesAdminTabungan: Konfigurasi kas_admin_id tidak ditemukan atau potongan <= 0.');

            return;
        }

        $siswas = Siswa::where('status', 1)
            ->whereIn('lembaga_id', $lembagaList)
            ->with(['tabungan.kas', 'kelas'])
            ->get();

        $pesan = [];
        $totalDiproses = 0;
        $totalNominal = 0;

        foreach ($siswas as $s) {
            if (! $s->tabungan || $s->tabungan->isEmpty()) {
                continue;
            }

            $totalTabungan = 0;
            $rincian = '';
            $tabunganTidakKenaAdmin = 0;
            $no = 0;

            foreach ($s->tabungan as $t) {
                $no++;
                $keteranganPotongan = '';

                // Cek apakah tabungan ini sudah pernah dipotong admin pada tahun yang sama (format baru ADM maupun format lama KTB)
                $alreadyCharged = Transaksi::where('transable_type', Tabungan::class)
                    ->where('transable_id', $t->id)
                    ->where(function ($query) use ($tahun) {
                        $query->where('kode', 'like', "ADM{$tahun}%")
                            ->orWhere(function ($legacy) use ($tahun) {
                                $legacy->whereYear('created_at', $tahun)
                                    ->where('kode', 'like', 'KTB%')
                                    ->where('keterangan', 'like', '%Administrasi%');
                            });
                    })
                    ->exists();

                if ($t->saldo > $saldoMinimal && ! $alreadyCharged) {
                    $this->prosesTransaksi($kasPotonganId, $t, $potongan, $tahun, $userId);
                    $saldoAkhir = (float) $t->saldo;
                    $totalDiproses++;
                    $totalNominal += $potongan;
                } else {
                    $saldoAkhir = (float) $t->saldo;
                    $tabunganTidakKenaAdmin++;
                    $keteranganPotongan = $alreadyCharged ? ' (sudah dipotong tahun ini)' : ' (tidak dikenakan potongan)';
                }

                $kasNama = $t->kas?->nama ?? 'Kas Tabungan';
                $rincian .= $no.'. '.$kasNama.'. Saldo Rp '.number_format($saldoAkhir, 0, ',', '.').$keteranganPotongan.PHP_EOL;
                $totalTabungan += $saldoAkhir;
            }

            // Kirim pesan WA HANYA jika ada tabungan yang kena potongan
            if ($tabunganTidakKenaAdmin !== $no) {
                $nomorTujuan = env('APP_ENV') === 'local' ? env('WHATSAPP_TEST_NUMBER') : (string) $s->telepon;
                if (! empty($nomorTujuan)) {
                    $pesan[] = [
                        'name' => $s->nama,
                        'number' => $nomorTujuan,
                        'message' => $this->generatePesan($s, $rincian, $totalTabungan, $potongan),
                        'sessionId' => WhatsappService::getSessionId($s),
                    ];
                }
            }
        }

        // Tandai bahwa tahun ini telah selesai diproses
        $this->tandaiTahunSelesai($tahun, $totalDiproses, $totalNominal, $userId);

        // Kirim WhatsApp jika ada pesan
        if (! empty($pesan)) {
            WhatsappService::kirimWa(kumpulan_pesan: $pesan);
        }

        Log::info("ProsesAdminTabungan: Selesai memproses pemotongan tahun {$tahun}. {$totalDiproses} rekening dipotong dengan total Rp ".number_format($totalNominal, 0, ',', '.'));
    }

    /**
     * Cek secara instan apakah pemotongan tahun ini sudah pernah dieksekusi.
     */
    public function sudahDiprosesTahunIni(int $tahun): bool
    {
        if (Setting::where('key', 'tabungan_admin_'.$tahun)->exists()) {
            return true;
        }

        return Transaksi::where('transable_type', Tabungan::class)
            ->where(function ($query) use ($tahun) {
                $query->where('kode', 'like', "ADM{$tahun}%")
                    ->orWhere(function ($legacy) use ($tahun) {
                        $legacy->whereYear('created_at', $tahun)
                            ->where('kode', 'like', 'KTB%')
                            ->where('keterangan', 'like', '%Administrasi%');
                    });
            })
            ->exists();
    }

    /**
     * Memproses pemotongan tabungan dan mencatat transaksi ganda:
     * 1. Tabel Riwayat Tabungan (mutasi keluar pada rekening tabungan siswa)
     * 2. Tabel Transaksi Kas (mutasi masuk pada buku kas potongan admin)
     */
    public function prosesTransaksi(string $kasPotonganId, Tabungan $tabungan, int $jumlah, int $tahun, string $userId): void
    {
        DB::transaction(function () use ($kasPotonganId, $tabungan, $jumlah, $tahun, $userId) {
            $kasNama = $tabungan->kas?->nama ?? 'Tabungan';
            $siswaNama = $tabungan->siswa?->nama ?? 'Siswa';

            // Generate kode unik untuk mutasi tabungan (ADM + TAHUN + MMDD + RANDOM)
            $kodeTabungan = 'ADM'.$tahun.date('md').strtoupper(Str::random(4));
            while (Transaksi::where('kode', $kodeTabungan)->exists()) {
                $kodeTabungan = 'ADM'.$tahun.date('md').strtoupper(Str::random(4));
            }

            // Generate kode unik untuk kas masuk admin (MTX + TAHUN + MMDD + RANDOM)
            $kodeKas = 'MTX'.$tahun.date('md').strtoupper(Str::random(4));
            while (Transaksi::where('kode', $kodeKas)->exists()) {
                $kodeKas = 'MTX'.$tahun.date('md').strtoupper(Str::random(4));
            }

            // 1. CATAT KE TABEL RIWAYAT TABUNGAN (transable = Tabungan)
            Transaksi::create([
                'kode' => $kodeTabungan,
                'transable_type' => Tabungan::class,
                'transable_id' => $tabungan->id,
                'jumlah' => -abs($jumlah), // minus agar terdeteksi sebagai potongan/keluar
                'keterangan' => "Biaya Administrasi {$kasNama} Tahun {$tahun} ({$siswaNama})",
                'user_id' => $userId,
            ]);

            // 2. CATAT KE TABEL TRANSAKSI KAS / BUKU BESAR (transable = Kas Admin)
            Transaksi::create([
                'kode' => $kodeKas,
                'transable_type' => Kas::class,
                'transable_id' => $kasPotonganId,
                'jumlah' => abs($jumlah), // positif sebagai kas masuk
                'keterangan' => "Penerimaan Administrasi {$kasNama} Tahun {$tahun} - {$siswaNama}",
                'user_id' => $userId,
            ]);

            // 3. Potong Saldo Tabungan Siswa
            $tabungan->decrement('saldo', $jumlah);

            // 4. Sinkronisasi Saldo Kas jika kas tabungan terpisah dari kas admin potongan
            if ($tabungan->kas_id !== $kasPotonganId) {
                if ($tabungan->kas) {
                    $tabungan->kas->decrement('saldo', $jumlah);
                }

                $kasPotongan = Kas::find($kasPotonganId);
                if ($kasPotongan) {
                    $kasPotongan->increment('saldo', $jumlah);
                }
            }
        });
    }

    private function resolveUserId(): string
    {
        if ($this->user_id && User::where('id', $this->user_id)->exists()) {
            return $this->user_id;
        }

        return (string) (User::where('role_id', 1)->value('id') ?? User::first()?->id);
    }

    private function tandaiTahunSelesai(int $tahun, int $totalDiproses, int $totalNominal, string $userId): void
    {
        Setting::updateOrCreate(
            ['key' => 'tabungan_admin_'.$tahun],
            [
                'payload' => [
                    'tahun' => $tahun,
                    'tanggal_eksekusi' => now()->toDateTimeString(),
                    'total_rekening' => $totalDiproses,
                    'total_nominal' => $totalNominal,
                    'user_id' => $userId,
                ],
            ]
        );

        // Tandai cache selesai hingga pergantian tahun berikutnya
        $sisaDetikSampaiAkhirTahun = max(3600, (int) (strtotime(($tahun + 1).'-01-01 00:00:00') - time()));
        Cache::put('tabungan_admin_checked_'.$tahun, true, $sisaDetikSampaiAkhirTahun);

        // Update tanggal di local_config untuk backward compatibility (jangan menulis saat unit test berjalan)
        if (! app()->runningUnitTests()) {
            try {
                $cfg = config('custom');
                $cfg['tabungan']['potongan']['tanggal'] = date('Y-m-d');
                $str = '<?php '.PHP_EOL.'$local_config ='.var_export($cfg, true).';';
                @file_put_contents(base_path('storage/app/local_config.php'), $str);
            } catch (\Throwable $e) {
                Log::warning('Gagal menulis local_config.php: '.$e->getMessage());
            }
        }
    }

    private function generatePesan($s, $rincian_tabungan, $total_tabungan, $jumlah_potongan): string
    {
        return WhatsappService::prosesPesan(
            siswa: $s,
            data: [
                'lembaga' => config('custom.lembaga.'.$s->lembaga_id),
                'kontak.nama' => config('custom.kontak_lembaga.'.$s->lembaga_id.'.kontak'),
                'tabungan.potongan' => 'Rp '.number_format($jumlah_potongan, 0, ',', '.'),
                'tabungan.rincian' => $rincian_tabungan,
                'tabungan.total' => 'Rp '.number_format($total_tabungan, 0, ',', '.'),
            ],
            jenis: 'tabungan.potongan'
        );
    }
}
