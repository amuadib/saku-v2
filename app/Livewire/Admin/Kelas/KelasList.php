<?php

namespace App\Livewire\Admin\Kelas;

use App\Models\Kelas;
use App\Models\Periode;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class KelasList extends Component
{
    use WithPagination;

    public $filter_periode = '';

    public $filter_lembaga = '';

    public function mount()
    {
        Gate::authorize('viewAny', Kelas::class);
        $aktif = Periode::where('aktif', true)->first();
        if ($aktif) {
            $this->filter_periode = $aktif->id;
        }
    }

    public function updating($property)
    {
        if (in_array($property, ['filter_periode', 'filter_lembaga'])) {
            $this->resetPage();
        }
    }

    public function delete($id)
    {
        $kelas = Kelas::findOrFail($id);
        Gate::authorize('delete', $kelas);
        $kelas->delete();
        session()->flash('message', 'Kelas berhasil dihapus.');
    }

    public function render()
    {
        $periodes = Periode::orderBy('nama', 'desc')->get();

        $kelas_items = Kelas::with('periode')
            ->when($this->filter_periode, function ($q) {
                $q->where('periode_id', $this->filter_periode);
            })
            ->when($this->filter_lembaga, function ($q) {
                $q->where('lembaga_id', $this->filter_lembaga);
            })
            ->orderBy('nama')
            ->paginate(10);

        return view('livewire.admin.kelas.kelas-list', [
            'kelas_items' => $kelas_items,
            'periodes' => $periodes,
        ])->layout('components.admin-layout');
    }
}
