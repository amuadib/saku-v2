<?php

namespace App\Livewire\Admin\Tabungan;

use App\Models\Tabungan;
use App\Models\Transaksi;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class TabunganRiwayat extends Component
{
    use WithPagination;

    public string $tabungan_id = '';

    public Tabungan $tabungan;

    public function mount(Tabungan $tabungan): void
    {
        $this->tabungan = $tabungan->loadMissing(['siswa', 'kas']);
        $this->tabungan_id = (string) $tabungan->id;
    }

    #[Layout('components.admin-layout', ['title' => 'Riwayat Tabungan'])]
    public function render(): View
    {
        $transaksis = Transaksi::where('transable_id', $this->tabungan_id)
            ->whereIn('transable_type', [Tabungan::class, 'tabungan', 'App\Models\Tabungan'])
            ->latest()
            ->paginate(15);

        return view('livewire.admin.tabungan.tabungan-riwayat', [
            'transaksis' => $transaksis,
        ]);
    }
}
