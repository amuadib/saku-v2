<?php

namespace App\Livewire\Siswa;

use App\Models\Penjualan;
use App\Models\Tagihan;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class TransaksiList extends Component
{
    use WithPagination;

    public $siswa;

    public $kategori = 'Semua';

    public function mount()
    {
        $this->siswa = Auth::user()->authable;
    }

    public function setKategori($kategori)
    {
        $this->kategori = $kategori;
        $this->resetPage();
    }

    #[Layout('layouts.siswa', ['title' => 'Daftar Transaksi'])]
    public function render()
    {
        $tabunganIds = $this->siswa->tabungan()->pluck('id');
        $tagihanIds = Tagihan::where('siswa_id', $this->siswa->id)->pluck('id');
        $penjualanIds = Penjualan::where('siswa_id', $this->siswa->id)->pluck('id');

        $query = Transaksi::with('transable');

        $query->where(function ($q) use ($tabunganIds, $tagihanIds, $penjualanIds) {
            $hasCondition = false;

            if ($this->kategori === 'Semua' || $this->kategori === 'Tabungan') {
                $q->orWhere(function ($subQ) use ($tabunganIds) {
                    $subQ->where('transable_type', 'App\Models\Tabungan')
                        ->whereIn('transable_id', $tabunganIds);
                });
                $hasCondition = true;
            }

            if ($this->kategori === 'Semua' || $this->kategori === 'Tagihan') {
                $q->orWhere(function ($subQ) use ($tagihanIds) {
                    $subQ->where('transable_type', 'App\Models\Tagihan')
                        ->whereIn('transable_id', $tagihanIds);
                });
                $hasCondition = true;
            }

            if ($this->kategori === 'Semua' || $this->kategori === 'Penjualan') {
                $q->orWhere(function ($subQ) use ($penjualanIds) {
                    $subQ->where('transable_type', 'App\Models\Penjualan')
                        ->whereIn('transable_id', $penjualanIds);
                });
                $hasCondition = true;
            }

            // Fallback to prevent returning everything if no matches
            if (! $hasCondition) {
                $q->whereRaw('1 = 0');
            }
        });

        $transaksis = $query->latest()->paginate(15);

        return view('livewire.siswa.transaksi-list', [
            'transaksis' => $transaksis,
        ]);
    }
}
