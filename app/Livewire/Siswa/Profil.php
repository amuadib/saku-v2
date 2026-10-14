<?php

namespace App\Livewire\Siswa;

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

        return redirect()->to($newMagicUrl);
    }

    public function render()
    {
        return view('livewire.siswa.profil')
            ->layout('layouts.siswa', ['title' => 'Profil']);
    }
}
