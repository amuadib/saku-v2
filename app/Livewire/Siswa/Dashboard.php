<?php

namespace App\Livewire\Siswa;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $siswa = Auth::user()->authable;
        $lembaga = $siswa?->lembaga ?? config('custom.lembaga.'.$siswa?->lembaga_id, []);
        $logoUrl = $siswa?->lembaga_logo_url;

        return view('livewire.siswa.dashboard', [
            'lembaga' => $lembaga,
            'logoUrl' => $logoUrl,
            'siswa' => $siswa,
            'saldo' => $siswa?->tabungan?->sum('saldo') ?? 0,
        ])->layout('layouts.siswa', ['title' => 'Dashboard Siswa']);
    }
}
