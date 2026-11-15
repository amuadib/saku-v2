<?php

namespace App\Livewire\Admin\Pembelian;

use App\Models\Pembelian;
use Livewire\Component;
use Livewire\WithPagination;

class PembelianList extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $pembelian = Pembelian::findOrFail($id);
        // Cascading delete is ideally handled in the DB schema, but we can explicitly delete details here if needed
        $pembelian->detail()->delete();
        $pembelian->delete();

        session()->flash('message', 'Pembelian berhasil dihapus.');
    }

    public function render()
    {
        $pembelians = Pembelian::with(['supplier', 'petugas', 'detail.barang'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('kode', 'like', '%'.$this->search.'%')
                        ->orWhereHas('supplier', function ($sub) {
                            $sub->where('nama', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('detail.barang', function ($sub) {
                            $sub->where('nama', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $today = today();
        $stats = [
            'totalHariIni' => (float) Pembelian::whereDate('created_at', $today)->sum('total'),
            'transaksiHariIni' => (int) Pembelian::whereDate('created_at', $today)->count(),
            'totalBulanIni' => (float) Pembelian::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total'),
            'totalSemua' => (int) Pembelian::count(),
        ];

        return view('livewire.admin.pembelian.pembelian-list', [
            'pembelians' => $pembelians,
            'stats' => $stats,
        ])->layout('components.admin-layout');
    }
}
