<?php

namespace App\Console\Commands;

use App\Jobs\ProsesAdminTabungan;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tabungan:potong-admin {--tahun= : Tahun pemotongan (default: tahun berjalan)} {--force : Paksa proses pemotongan meskipun tahun ini sudah pernah diproses} {--sync : Jalankan secara synchronous tanpa antrean queue}')]
#[Description('Eksekusi pemotongan biaya administrasi tabungan tahunan siswa')]
class ProsesAdminTabunganCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tahun = $this->option('tahun') ? (int) $this->option('tahun') : (int) date('Y');
        $force = (bool) $this->option('force');
        $sync = (bool) $this->option('sync');

        $job = new ProsesAdminTabungan(
            user_id: null,
            tahun: $tahun,
            force: $force
        );

        if (! $force && $job->sudahDiprosesTahunIni($tahun)) {
            $this->warn("Biaya administrasi tabungan untuk tahun {$tahun} sudah pernah diproses.");
            $this->line('Gunakan opsi --force jika ingin tetap memproses tabungan siswa yang belum dipotong.');

            return self::SUCCESS;
        }

        $this->info("Menjalankan pemotongan administrasi tabungan tahun {$tahun}...");

        if ($sync) {
            dispatch_sync($job);
            $this->info("Pemotongan administrasi tabungan tahun {$tahun} selesai diproses (sync).");
        } else {
            dispatch($job);
            $this->info("Job pemotongan administrasi tabungan tahun {$tahun} telah dimasukkan ke antrean queue.");
        }

        return self::SUCCESS;
    }
}
