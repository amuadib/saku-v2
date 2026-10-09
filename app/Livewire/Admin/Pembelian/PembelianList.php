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
        $pembelians = Pembelian::with(['supplier', 'petugas'])
            ->where('kode', 'like', '%'.$this->search.'%')
            ->orWhereHas('supplier', function ($q) {
                $q->where('nama', 'like', '%'.$this->search.'%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.pembelian.pembelian-list', [
            'pembelians' => $pembelians,
        ])->layout('components.admin-layout');
    }
}
