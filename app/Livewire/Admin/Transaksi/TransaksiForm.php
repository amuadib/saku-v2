<?php

namespace App\Livewire\Admin\Transaksi;

use App\Models\Kas;
use App\Models\Transaksi;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Component;

class TransaksiForm extends Component
{
    #[Rule('required')]
    public $kas_id;

    #[Rule('required|numeric|not_in:0')]
    public $jumlah;

    #[Rule('nullable|string')]
    public $keterangan;

    public function save()
    {
        $this->validate();

        $transaksi = new Transaksi;
        $transaksi->kas_id = $this->kas_id;
        $transaksi->jumlah = $this->jumlah;
        $transaksi->keterangan = $this->keterangan;
        $transaksi->user_id = auth()->id();
        $transaksi->kode = 'MNL-'.strtoupper(Str::random(6));

        // Attempt to set lembaga_id if user belongs to an authable entity
        if (auth()->check() && auth()->user()->authable) {
            $transaksi->lembaga_id = auth()->user()->authable->lembaga_id ?? null;
        }

        $transaksi->save();

        session()->flash('message', 'Transaksi manual berhasil ditambahkan.');

        return $this->redirectRoute('admin.transaksi.index', navigate: true);
    }

    public function render()
    {
        $kas = Kas::all();

        return view('livewire.admin.transaksi.transaksi-form', [
            'kas_items' => $kas,
        ])->layout('components.admin-layout', ['header' => 'Tambah Transaksi Manual']);
    }
}
