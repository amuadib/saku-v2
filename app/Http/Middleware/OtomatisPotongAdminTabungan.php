<?php

namespace App\Http\Middleware;

use App\Jobs\ProsesAdminTabungan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class OtomatisPotongAdminTabungan
{
    /**
     * Handle an incoming request.
     *
     * Berfungsi sebagai pseudo-cron / lazy trigger tahunan.
     * Berjalan otomatis saat ada admin/petugas yang mengakses aplikasi di tahun berjalan.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Hanya jalankan untuk response sukses / redirect
        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        // Abaikan request internal Livewire, aset statis, atau debug
        $path = $request->path();
        if (
            str_contains($path, 'livewire')
            || $request->is('_debugbar*')
            || $request->is('_ignition*')
            || $request->is('storage/*')
            || $request->is('up')
            || $request->is('favicon.ico')
        ) {
            return $response;
        }

        $user = $request->user();
        if ($user && $user->isPetugas()) {
            $this->cekDanProses((string) $user->id);
        }

        return $response;
    }

    /**
     * Cek apakah potongan admin tahun berjalan sudah dieksekusi.
     * Menggunakan cache O(1) sehingga tidak membebani database setiap request.
     */
    protected function cekDanProses(string $userId): void
    {
        $tahun = (int) date('Y');
        $cacheKey = 'tabungan_admin_checked_'.$tahun;

        // 1. Cek dari cache (sangat cepat, <0.1ms). Jika sudah ditandai, langsung selesai.
        if (Cache::has($cacheKey)) {
            return;
        }

        // 2. Cek database apakah tahun ini sudah pernah diproses sebelumnya (termasuk format lama KTB)
        if ((new ProsesAdminTabungan(tahun: $tahun))->sudahDiprosesTahunIni($tahun)) {
            $sisaDetik = max(3600, (int) (strtotime(($tahun + 1).'-01-01 00:00:00') - time()));
            Cache::put($cacheKey, true, $sisaDetik);

            return;
        }

        // 3. Pasang kunci sementara di cache selama 1 jam agar request bersamaan tidak memicu duplikasi
        Cache::put($cacheKey, true, 3600);

        Log::info("OtomatisPotongAdminTabungan: Memicu pemotongan administrasi tabungan tahun {$tahun} melalui lazy web trigger (User: {$userId}).");

        // 4. Eksekusi job SETELAH response dikirim ke browser (admin tidak akan merasakan lag)
        dispatch(new ProsesAdminTabungan(
            user_id: $userId,
            tahun: $tahun,
            force: false
        ))->afterResponse();
    }
}
