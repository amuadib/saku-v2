<?php

namespace App\Livewire\Siswa;

use Livewire\Component;

class Profil extends Component
{
    public $siswa;

    public function mount()
    {
        $this->siswa = auth()->user()->authable;
    }

    public function render()
    {
        return view('livewire.siswa.profil')
            ->layout('layouts.siswa', ['title' => 'Profil']);
    }
}
