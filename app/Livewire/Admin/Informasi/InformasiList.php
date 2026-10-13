<?php

namespace App\Livewire\Admin\Informasi;

use App\Models\Informasi;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class InformasiList extends Component
{
    use WithPagination;

    public $search = '';

    public $filter_kategori = '';

    public $filter_lembaga = '';

    public $filter_status = '';

    public function mount()
    {
        Gate::authorize('viewAny', Informasi::class);
    }

    public function updating($property)
    {
        if (in_array($property, ['search', 'filter_kategori', 'filter_lembaga', 'filter_status'])) {
            $this->resetPage();
        }
    }

    public function toggleStatus($id)
    {
        $informasi = Informasi::findOrFail($id);
        Gate::authorize('update', $informasi);

        $informasi->is_aktif = ! $informasi->is_aktif;
        $informasi->save();

        session()->flash('message', 'Status informasi "'.$informasi->judul.'" berhasil diperbarui.');
    }

    public function toggleSticky($id)
    {
        $informasi = Informasi::findOrFail($id);
        Gate::authorize('update', $informasi);

        $informasi->is_sticky = ! $informasi->is_sticky;
        $informasi->save();

        session()->flash('message', 'Status sticky informasi "'.$informasi->judul.'" berhasil diperbarui.');
    }

    public function delete($id)
    {
        $informasi = Informasi::findOrFail($id);
        Gate::authorize('delete', $informasi);

        if ($informasi->gambar && Storage::disk('public')->exists($informasi->gambar)) {
            Storage::disk('public')->delete($informasi->gambar);
        }

        $informasi->delete();
        session()->flash('message', 'Informasi berhasil dihapus.');
    }

    public function render()
    {
        $user = auth()->user();
        $userLembaga = $user->authable->lembaga_id ?? null;

        $kategoris = Informasi::distinct()->pluck('kategori')->filter()->values();

        $informasi_items = Informasi::with('user')
            ->when(! $user->isAdmin() && $userLembaga, function ($q) use ($userLembaga) {
                $q->forLembaga($userLembaga);
            })
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('judul', 'like', '%'.$this->search.'%')
                        ->orWhere('konten', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filter_kategori, function ($q) {
                $q->where('kategori', $this->filter_kategori);
            })
            ->when($this->filter_lembaga !== '', function ($q) {
                if ($this->filter_lembaga === 'all') {
                    $q->whereNull('lembaga_id')->orWhere('lembaga_id', 99);
                } else {
                    $q->where('lembaga_id', $this->filter_lembaga);
                }
            })
            ->when($this->filter_status !== '', function ($q) {
                $q->where('is_aktif', (bool) $this->filter_status);
            })
            ->orderBy('is_sticky', 'desc')
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.informasi.informasi-list', [
            'informasi_items' => $informasi_items,
            'kategoris' => $kategoris,
            'lembaga_list' => config('custom.lembaga') ?? [],
        ])->layout('components.admin-layout');
    }
}
