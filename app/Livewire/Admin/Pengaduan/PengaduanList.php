<?php

namespace App\Livewire\Admin\Pengaduan;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Pengaduan;

class PengaduanList extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        Pengaduan::findOrFail($id)->delete();
        session()->flash('message', 'Pengaduan berhasil dihapus.');
    }

    public function render()
    {
        $pengaduans = Pengaduan::with('siswa')
            ->when(!auth()->user()->isAdmin(), function ($q) {
                $q->whereHas('siswa', function ($sub) {
                    $sub->where('lembaga_id', auth()->user()->authable->lembaga_id ?? null);
                });
            })
            ->where(function ($query) {
                $query->whereHas('siswa', function($q) {
                    $q->where('nama', 'like', '%' . $this->search . '%')
                      ->orWhere('nis', 'like', '%' . $this->search . '%');
                })
                ->orWhere('laporan', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.pengaduan.pengaduan-list', [
            'pengaduans' => $pengaduans
        ])->layout('components.admin-layout');
    }
}
