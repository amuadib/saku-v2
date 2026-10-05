<?php

namespace App\Livewire\Admin\Kas;

use App\Models\Kas;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class KasList extends Component
{
    use WithPagination;

    public $search = '';

    public $filter_lembaga = '';

    public function mount()
    {
        Gate::authorize('viewAny', Kas::class);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterLembaga()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $kas = Kas::findOrFail($id);
        Gate::authorize('delete', $kas);
        $kas->delete();
        session()->flash('message', 'Kas berhasil dihapus.');
    }

    public function render()
    {
        $kas_items = Kas::where('nama', 'like', '%'.$this->search.'%')
            ->when($this->filter_lembaga, function ($query) {
                $query->where('lembaga_id', $this->filter_lembaga);
            })
            ->orderBy('nama')
            ->paginate(10);

        return view('livewire.admin.kas.kas-list', [
            'kas_items' => $kas_items,
        ])->layout('components.admin-layout');
    }
}
