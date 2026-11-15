<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Pemotongan administrasi tabungan tahunan siswa.
 * Dijalankan otomatis setiap hari pada pukul 01:00 dini hari.
 * Berkat pemeriksaan idempotensi (sudahDiprosesTahunIni), proses ini aman,
 * sangat cepat (<1ms), dan langsung keluar jika tahun berjalan sudah diproses.
 * Metode ini menjamin eksekusi tidak terlewat meskipun server offline pada 1 Januari.
 */
Schedule::command('tabungan:potong-admin --sync')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->runInBackground();
