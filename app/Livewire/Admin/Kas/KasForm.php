<?php

namespace App\Livewire\Admin\Kas;

use App\Models\Kas;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class KasForm extends Component
{
    public ?Kas $kas = null;

    public $nama = '';

    public $lembaga_id = '';

    public $keterangan = '';

    public $saldo = 0;

    public $ada_tagihan = false;

    public $tabungan = false;

    public $penjualan = false;

    public $setoran = false;

    // We skip array/json fields for simple CRUD or initialize them empty
    public $jenis_transaksi = [];

    public $aturan_tagihan = [];

    protected function rules()
    {
        return [
            'nama' => 'required|string|max:255',
            'lembaga_id' => 'required|integer',
            'keterangan' => 'nullable|string',
            'saldo' => 'required|numeric|min:0',
            'ada_tagihan' => 'boolean',
            'tabungan' => 'boolean',
            'penjualan' => 'boolean',
            'setoran' => 'boolean',
        ];
    }

    public function mount(?Kas $kas = null)
    {
        if ($kas && $kas->exists) {
            Gate::authorize('update', $kas);
            $this->kas = $kas;
            $this->nama = $kas->nama;
            $this->lembaga_id = $kas->lembaga_id;
            $this->keterangan = $kas->keterangan;
            $this->saldo = $kas->saldo;
            $this->ada_tagihan = $kas->ada_tagihan;
            $this->tabungan = $kas->tabungan;
            $this->penjualan = $kas->penjualan;
            $this->setoran = $kas->setoran;
            $this->jenis_transaksi = $kas->jenis_transaksi ?? [];
            $this->aturan_tagihan = $kas->aturan_tagihan ?? [];
        } else {
            Gate::authorize('create', Kas::class);
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'nama' => $this->nama,
            'keterangan' => $this->keterangan,
            'saldo' => $this->saldo,
            'ada_tagihan' => $this->ada_tagihan,
            'tabungan' => $this->tabungan,
            'penjualan' => $this->penjualan,
            'setoran' => $this->setoran,
            'lembaga_id' => $this->lembaga_id,
        ];

        if ($this->kas && $this->kas->exists) {
            $this->kas->update($data);
            session()->flash('message', 'Kas berhasil diperbarui.');
        } else {
            $data['jenis_transaksi'] = [];
            $data['aturan_tagihan'] = [];
            Kas::create($data);
            session()->flash('message', 'Kas berhasil ditambahkan.');
        }

        return $this->redirect(route('admin.kas.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.kas.kas-form')->layout('components.admin-layout');
    }
}
