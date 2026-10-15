<?php

namespace App\Http\Middleware;

use App\Models\Siswa;
use App\Models\SiswaAktivitas;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CatatAktivitasSiswa
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Hanya catat untuk response sukses/redirect
        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        if (! auth()->check()) {
            return $response;
        }

        $user = auth()->user();
        if ($user->authable_type !== Siswa::class && ! ($user->authable instanceof Siswa)) {
            return $response;
        }

        $path = $request->path();
        $routeName = $request->route()?->getName() ?? '';

        // Abaikan request internal Livewire (seperti /livewire-hash/update, default-livewire.update), assets, static, dan login
        if (
            str_contains($path, 'livewire')
            || str_contains($routeName, 'livewire')
            || $request->is('_debugbar*')
            || $request->is('_ignition*')
            || $request->is('storage/*')
            || $request->is('up')
            || $request->is('favicon.ico')
            || str_starts_with($path, 'login')
            || str_starts_with($path, 'm/')
            || $request->is('logout')
        ) {
            return $response;
        }

        $siswa = $user->authable ?? Siswa::find($user->authable_id);
        if (! $siswa) {
            return $response;
        }

        // Throttle agar tidak mencatat halaman sama berulang dalam 5 detik (misal double-click / reload)
        $cacheKey = "siswa_act_{$siswa->id}_".md5($request->path().'_'.$request->method());
        if (Cache::has($cacheKey)) {
            return $response;
        }
        Cache::put($cacheKey, true, now()->addSeconds(5));

        $routeName = $request->route()?->getName() ?? '';

        $keterangan = match (true) {
            $routeName === 'siswa.dashboard' || $path === 'dashboard-siswa' => 'Membuka Dashboard Siswa',
            $routeName === 'siswa.informasi' || $path === 'informasi' => 'Membuka Halaman Informasi',
            $routeName === 'siswa.profil' || $path === 'profil' => 'Membuka Profil Singkat',
            $routeName === 'siswa.profil.detail' || $path === 'profil-siswa' => 'Membuka Detail Profil Siswa',
            $routeName === 'siswa.tabungan.list' || $path === 'saldo-tabungan' => 'Melihat Saldo Tabungan',
            $routeName === 'siswa.tabungan.riwayat' || $path === 'riwayat-tabungan' => 'Melihat Riwayat Tabungan',
            $routeName === 'siswa.tagihan' || $path === 'tagihan' => 'Melihat Daftar Tagihan',
            $routeName === 'siswa.transaksi' || $path === 'transaksi' => 'Melihat Riwayat Transaksi',
            $routeName === 'siswa.kartu' || $path === 'kartu-siswa' => 'Membuka Kartu Siswa Digital',
            $routeName === 'siswa.chat' || $path === 'chat' => 'Membuka Halaman Chat',
            default => 'Mengakses: /'.ltrim($path, '/'),
        };

        if (session()->has('impersonate_by')) {
            $keterangan .= ' (Impersonasi oleh Admin)';
        }

        $parsed = SiswaAktivitas::parseUserAgent($request->userAgent());

        SiswaAktivitas::create([
            'siswa_id' => $siswa->id,
            'user_id' => $user->id,
            'tipe' => 'akses_link',
            'url' => $request->fullUrl(),
            'route_name' => $routeName ?: null,
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device' => $parsed['device'],
            'browser' => $parsed['browser'],
            'keterangan' => $keterangan,
        ]);

        return $response;
    }
}
