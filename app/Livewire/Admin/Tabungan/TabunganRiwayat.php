<?php

namespace App\Livewire\Admin\Tabungan;

use App\Models\Tabungan;
use App\Models\Transaksi;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class TabunganRiwayat extends Component
{
    use WithPagination;

    public $tabungan_id;

    public Tabungan $tabungan;

    public function mount(Tabungan $tabungan)
    {
        $this->tabungan = $tabungan;
        $this->tabungan_id = $tabungan->id;
    }

    #[Layout('components.admin-layout', ['title' => 'Riwayat Tabungan'])]
    public function render()
    {
        $transaksis = Transaksi::where('transable_id', $this->tabungan_id)
            ->where('transable_type', 'App\Models\Tabungan')
            ->latest()
            ->paginate(15);

        return view('livewire.admin.tabungan.tabungan-riwayat', [
            'transaksis' => $transaksis,
        ]);
    }
}
