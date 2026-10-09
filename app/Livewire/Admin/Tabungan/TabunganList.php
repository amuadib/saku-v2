<?php

namespace App\Livewire\Admin\Tabungan;

use App\Models\Tabungan;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class TabunganList extends Component
{
    use WithPagination;

    public $search = '';

    public function mount()
    {
        Gate::authorize('viewAny', Tabungan::class);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $tabungan = Tabungan::findOrFail($id);
        Gate::authorize('delete', $tabungan);
        $tabungan->delete();
        session()->flash('message', 'Tabungan berhasil dihapus.');
    }

    public function render()
    {
        $tabungans = Tabungan::with(['siswa', 'kas'])
            ->when(! auth()->user()->isAdmin(), function ($q) {
                $q->whereHas('siswa', function ($sub) {
                    $sub->where('lembaga_id', auth()->user()->authable->lembaga_id ?? null);
                });
            })
            ->whereHas('siswa', function ($q) {
                $q->where('nama', 'like', '%'.$this->search.'%')
                    ->orWhere('nis', 'like', '%'.$this->search.'%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.tabungan.tabungan-list', [
            'tabungans' => $tabungans,
        ])->layout('components.admin-layout');
    }
}
