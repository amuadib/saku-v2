<?php

namespace App\Livewire\Siswa;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class KartuSiswa extends Component
{
    public $siswa;

    public function mount()
    {
        $this->siswa = Auth::user()->authable;
    }

    #[Layout('layouts.siswa', ['title' => 'Kartu Siswa'])]
    public function render()
    {
        return view('livewire.siswa.kartu-siswa');
    }
}
