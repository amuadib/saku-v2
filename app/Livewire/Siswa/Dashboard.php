<?php

namespace App\Livewire\Siswa;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $siswa = Auth::user()->authable;

        return view('livewire.siswa.dashboard', [
            'siswa' => $siswa,
            'saldo' => $siswa->tabungan->sum('saldo') ?? 0,
        ])->layout('layouts.siswa', ['title' => 'Dashboard Siswa']);
    }
}
