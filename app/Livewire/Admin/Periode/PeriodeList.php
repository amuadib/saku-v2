<?php

namespace App\Livewire\Admin\Periode;

use App\Models\Periode;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class PeriodeList extends Component
{
    use WithPagination;

    public $search = '';

    public function mount()
    {
        Gate::authorize('viewAny', Periode::class);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $periode = Periode::findOrFail($id);
        Gate::authorize('delete', $periode);
        $periode->delete();
        session()->flash('message', 'Periode berhasil dihapus.');
    }

    public function render()
    {
        $periodes = Periode::where('nama', 'like', '%'.$this->search.'%')
            ->orderBy('mulai', 'desc')
            ->paginate(10);

        return view('livewire.admin.periode.periode-list', [
            'periodes' => $periodes,
        ])->layout('components.admin-layout');
    }
}
