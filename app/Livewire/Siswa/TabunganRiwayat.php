<?php

namespace App\Livewire\Siswa;

use App\Models\Kas;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class TabunganRiwayat extends Component
{
    use WithPagination;

    public $siswa;

    public function mount()
    {
        $this->siswa = Auth::user()->authable;
    }

    public $kategori = 'Semua';

    public function setKategori($kategori)
    {
        $this->kategori = $kategori;
        $this->resetPage();
    }

    #[Layout('layouts.siswa', ['title' => 'Riwayat Tabungan'])]
    public function render()
    {
        $kasTabungan = Kas::where('lembaga_id', $this->siswa->lembaga_id)->where('tabungan', true)->get();

        $tabunganQuery = $this->siswa->tabungan();
        if ($this->kategori !== 'Semua') {
            $tabunganQuery->where('kas_id', $this->kategori);
        }
        $tabunganIds = $tabunganQuery->pluck('id');

        $transaksis = Transaksi::whereIn('transable_id', $tabunganIds)
            ->where('transable_type', 'App\Models\Tabungan')
            ->latest()
            ->paginate(15);

        return view('livewire.siswa.tabungan-riwayat', [
            'transaksis' => $transaksis,
            'kasTabungan' => $kasTabungan,
        ]);
    }
}
