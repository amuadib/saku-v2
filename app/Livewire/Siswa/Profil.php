<?php

namespace App\Livewire\Siswa;

use App\Models\Siswa;
use App\Models\SiswaAktivitas;
use Livewire\Component;

class Profil extends Component
{
    public $siswa;

    public $showConfirmModal = false;

    public function mount()
    {
        $this->siswa = auth()->user()->authable;
    }

    public function confirmRegenerate()
    {
        $this->showConfirmModal = true;
    }

    public function regenerateAndRedirect()
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $user->regenerateMagicToken();
        $newMagicUrl = $user->getMagicLoginUrl();

        $siswa = $user->authable ?? Siswa::find($user->authable_id);
        if ($siswa) {
            $parsed = SiswaAktivitas::parseUserAgent(request()->userAgent());
            $url = request()->headers->get('referer') ?: route('siswa.profil');
            SiswaAktivitas::create([
                'siswa_id' => $siswa->id,
                'user_id' => $user->id,
                'tipe' => 'ubah_data',
                'url' => $url,
                'route_name' => 'siswa.profil',
                'method' => 'POST',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'device' => $parsed['device'],
                'browser' => $parsed['browser'],
                'keterangan' => 'Membuat ulang (regenerate) link akses & QR Code siswa',
            ]);
        }

        return redirect()->to($newMagicUrl);
    }

    public function render()
    {
        return view('livewire.siswa.profil')
            ->layout('layouts.siswa', ['title' => 'Profil']);
    }
}
