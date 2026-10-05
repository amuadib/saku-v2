<?php

namespace App\Livewire\Admin\Supplier;

use App\Models\Supplier;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierList extends Component
{
    use WithPagination;

    public $search = '';

    public function mount()
    {
        Gate::authorize('viewAny', Supplier::class);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $supplier = Supplier::findOrFail($id);
        Gate::authorize('delete', $supplier);
        $supplier->delete();
        session()->flash('message', 'Supplier berhasil dihapus.');
    }

    public function render()
    {
        $suppliers = Supplier::where('nama', 'like', '%'.$this->search.'%')
            ->orderBy('nama')
            ->paginate(10);

        return view('livewire.admin.supplier.supplier-list', [
            'suppliers' => $suppliers,
        ])->layout('components.admin-layout');
    }
}
