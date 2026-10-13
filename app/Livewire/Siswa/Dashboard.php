<?php

namespace App\Livewire\Siswa;

use App\Models\Informasi;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $siswa = Auth::user()->authable;
        $lembaga = $siswa?->lembaga ?? config('custom.lembaga.'.$siswa?->lembaga_id, []);
        $logoUrl = $siswa?->lembaga_logo_url;

        $informasiSticky = Informasi::aktif()
            ->forLembaga($siswa?->lembaga_id)
            ->sticky()
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $informasiLainnya = Informasi::aktif()
            ->forLembaga($siswa?->lembaga_id)
            ->when($informasiSticky, fn ($q) => $q->where('id', '!=', $informasiSticky->id))
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('livewire.siswa.dashboard', [
            'lembaga' => $lembaga,
            'logoUrl' => $logoUrl,
            'siswa' => $siswa,
            'saldo' => $siswa?->tabungan?->sum('saldo') ?? 0,
            'informasiSticky' => $informasiSticky,
            'informasiLainnya' => $informasiLainnya,
        ])->layout('layouts.siswa', ['title' => 'Dashboard Siswa']);
    }
}
