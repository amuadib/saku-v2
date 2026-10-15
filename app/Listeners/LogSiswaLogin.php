<?php

namespace App\Listeners;

use App\Models\Siswa;
use App\Models\SiswaAktivitas;
use Illuminate\Auth\Events\Login;

class LogSiswaLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        if (! $user) {
            return;
        }

        // Check if user is a Siswa
        if ($user->authable_type !== Siswa::class && ! ($user->authable instanceof Siswa)) {
            return;
        }

        $siswa = $user->authable ?? Siswa::find($user->authable_id);
        if (! $siswa) {
            return;
        }

        $request = request();
        $isMagicLink = $request->routeIs('login.magic*')
            || str_contains($request->path(), 'login/magic')
            || str_starts_with($request->path(), 'm/');

        $keterangan = $isMagicLink ? 'Login via Magic Link' : 'Login ke Aplikasi';

        if (session()->has('impersonate_by')) {
            $keterangan .= ' (Impersonasi oleh Admin)';
        }

        $parsed = SiswaAktivitas::parseUserAgent($request->userAgent());

        SiswaAktivitas::create([
            'siswa_id' => $siswa->id,
            'user_id' => $user->id,
            'tipe' => 'login',
            'url' => $request->fullUrl(),
            'route_name' => $request->route()?->getName(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device' => $parsed['device'],
            'browser' => $parsed['browser'],
            'keterangan' => $keterangan,
        ]);

        $siswa->disableLogging = true;
        $siswa->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->saveQuietly();
    }
}
