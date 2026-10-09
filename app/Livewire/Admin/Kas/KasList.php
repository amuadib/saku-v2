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

    public function setorDana($id)
    {
        $kas = Kas::findOrFail($id);
        Gate::authorize('update', $kas);

        if ($kas->saldo <= 0) {
            session()->flash('error', 'Saldo kas kosong, tidak ada dana yang bisa disetor.');
            return;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($kas) {
            $todayCount = \App\Models\Transaksi::whereDate('created_at', today())->count();
            $baseKode = date('Ymd') . str_pad($todayCount + 1, 4, '0', STR_PAD_LEFT);
            $kode = 'KTX'.$baseKode;

            $transaksi = new \App\Models\Transaksi;
            $transaksi->transable_type = Kas::class;
            $transaksi->transable_id = $kas->id;
            $transaksi->jumlah = -abs($kas->saldo);
            $transaksi->keterangan = 'Setor Dana Kas ' . $kas->nama;
            $transaksi->user_id = auth()->id();
            $transaksi->kode = $kode;
            $transaksi->save();

            $kas->update(['saldo' => 0]);
        });

        session()->flash('message', 'Dana berhasil disetor, saldo kas kini 0.');
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
