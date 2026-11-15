<?php

namespace App\Livewire\Admin\Kas;

use App\Models\Kas;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
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

        DB::transaction(function () use ($kas) {
            $todayCount = Transaksi::whereDate('created_at', today())->count();
            $baseKode = date('Ymd').str_pad($todayCount + 1, 4, '0', STR_PAD_LEFT);
            $kode = 'KTX'.$baseKode;

            $transaksi = new Transaksi;
            $transaksi->transable_type = Kas::class;
            $transaksi->transable_id = $kas->id;
            $transaksi->jumlah = -abs($kas->saldo);
            $transaksi->keterangan = 'Setor Dana Kas '.$kas->nama;
            $transaksi->user_id = auth()->id();
            $transaksi->kode = $kode;
            $transaksi->save();

            $kas->update(['saldo' => 0]);
        });

        session()->flash('message', 'Dana Kas '.$kas->nama.' berhasil disetor.');
    }

    public function render()
    {
        $baseQuery = Kas::when($this->filter_lembaga, function ($query) {
            $query->where('lembaga_id', $this->filter_lembaga);
        });

        $kas_items = (clone $baseQuery)
            ->where('nama', 'like', '%'.$this->search.'%')
            ->orderBy('nama')
            ->paginate(10);

        $stats = [
            'totalKas' => (clone $baseQuery)->count(),
            'totalSaldo' => (float) (clone $baseQuery)->sum('saldo'),
            'kasTagihan' => (clone $baseQuery)->where('ada_tagihan', true)->count(),
            'kasTabungan' => (clone $baseQuery)->where('tabungan', true)->count(),
        ];

        return view('livewire.admin.kas.kas-list', [
            'kas_items' => $kas_items,
            'stats' => $stats,
        ])->layout('components.admin-layout');
    }
}
