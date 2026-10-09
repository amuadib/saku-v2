<?php

namespace App\Livewire\Admin\Barang;

use App\Models\Barang;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class BarangList extends Component
{
    use WithPagination;

    public $search = '';

    public function mount()
    {
        Gate::authorize('viewAny', Barang::class);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $barang = Barang::findOrFail($id);
        Gate::authorize('delete', $barang);
        $barang->delete();
        session()->flash('message', 'Barang berhasil dihapus.');
    }

    public function render()
    {
        $barangs = Barang::when(! auth()->user()->isAdmin(), function ($q) {
            $q->where('lembaga_id', auth()->user()->authable->lembaga_id ?? null);
        })
            ->where(function ($q) {
                $q->where('nama', 'like', '%'.$this->search.'%')
                    ->orWhere('kode', 'like', '%'.$this->search.'%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.barang.barang-list', [
            'barangs' => $barangs,
        ])->layout('components.admin-layout');
    }
}
