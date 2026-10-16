<?php

namespace App\Livewire\Siswa;

use App\Models\Tagihan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class TagihanList extends Component
{
    use WithPagination;

    public $siswa;

    public function mount()
    {
        $this->siswa = Auth::user()->authable;
    }

    #[Layout('layouts.siswa', ['title' => 'Tagihan Siswa'])]
    public function render()
    {
        $query = Tagihan::with('kas')
            ->where('siswa_id', $this->siswa->id)
            ->where(function ($q) {
                $q->whereNull('bayar')->orWhereColumn('bayar', '<', 'jumlah');
            });

        $totalTagihan = (float) ((clone $query)->sum('jumlah') ?? 0);
        $totalTelahBayar = (float) ((clone $query)->sum('bayar') ?? 0);
        $totalBelumLunas = max(0, $totalTagihan - $totalTelahBayar);
        $countBelumLunas = (clone $query)->count();

        $tagihans = $query->latest()->paginate(15);

        return view('livewire.siswa.tagihan-list', [
            'tagihans' => $tagihans,
            'totalBelumLunas' => $totalBelumLunas,
            'totalTagihan' => $totalTagihan,
            'totalTelahBayar' => $totalTelahBayar,
            'countBelumLunas' => $countBelumLunas,
        ]);
    }
}
