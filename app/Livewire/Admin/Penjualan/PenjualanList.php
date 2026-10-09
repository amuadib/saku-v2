<?php

namespace App\Livewire\Admin\Penjualan;

use App\Models\Penjualan;
use Livewire\Component;
use Livewire\WithPagination;

class PenjualanList extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $penjualan = Penjualan::findOrFail($id);
        $penjualan->detail()->delete();
        // If there's an associated tagihan or transaksi due to polymorphic relations, ideally they should be deleted via model boot cascading or handled here.
        if ($penjualan->tagihan) {
            $penjualan->tagihan()->delete();
        }
        if ($penjualan->transaksi) {
            $penjualan->transaksi()->delete();
        }
        $penjualan->delete();

        session()->flash('message', 'Penjualan berhasil dihapus.');
    }

    public function render()
    {
        $penjualans = Penjualan::with(['siswa', 'petugas'])
            ->when(! auth()->user()->isAdmin(), function ($q) {
                $q->whereHas('siswa', function ($sub) {
                    $sub->where('lembaga_id', auth()->user()->authable->lembaga_id ?? null);
                });
            })
            ->where(function ($query) {
                $query->where('kode', 'like', '%'.$this->search.'%')
                    ->orWhereHas('siswa', function ($q) {
                        $q->where('nama', 'like', '%'.$this->search.'%')
                            ->orWhere('nis', 'like', '%'.$this->search.'%');
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.penjualan.penjualan-list', [
            'penjualans' => $penjualans,
        ])->layout('components.admin-layout');
    }
}
