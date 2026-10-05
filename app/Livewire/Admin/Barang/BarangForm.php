<?php

namespace App\Livewire\Admin\Barang;

use App\Models\Barang;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class BarangForm extends Component
{
    public ?Barang $barang = null;

    public $kode = '';

    public $jenis = '';

    public $nama = '';

    public $keterangan = '';

    public $harga = 0;

    public $harga_beli = null;

    public $stok = 0;

    public $stok_minimal = 0;

    public $satuan = 'PCS';

    protected function rules()
    {
        return [
            'kode' => 'required|string|max:20|unique:barang,kode,'.($this->barang->id ?? 'NULL'),
            'jenis' => 'required|string|max:3',
            'nama' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'harga_beli' => 'nullable|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'stok_minimal' => 'required|integer|min:0',
            'satuan' => 'required|string|max:3',
        ];
    }

    public function mount(?Barang $barang = null)
    {
        if ($barang && $barang->exists) {
            Gate::authorize('update', $barang);
            $this->barang = $barang;
            $this->kode = $barang->kode;
            $this->jenis = $barang->jenis;
            $this->nama = $barang->nama;
            $this->keterangan = $barang->keterangan;
            $this->harga = $barang->harga;
            $this->harga_beli = $barang->harga_beli;
            $this->stok = $barang->stok;
            $this->stok_minimal = $barang->stok_minimal;
            $this->satuan = $barang->satuan;
        } else {
            Gate::authorize('create', Barang::class);
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->barang && $this->barang->exists) {
            $this->barang->update([
                'kode' => $this->kode,
                'jenis' => $this->jenis,
                'nama' => $this->nama,
                'keterangan' => $this->keterangan,
                'harga' => $this->harga,
                'harga_beli' => $this->harga_beli,
                'stok' => $this->stok,
                'stok_minimal' => $this->stok_minimal,
                'satuan' => $this->satuan,
            ]);
            session()->flash('message', 'Barang berhasil diperbarui.');
        } else {
            Barang::create([
                'kode' => $this->kode,
                'jenis' => $this->jenis,
                'nama' => $this->nama,
                'keterangan' => $this->keterangan,
                'harga' => $this->harga,
                'harga_beli' => $this->harga_beli,
                'stok' => $this->stok,
                'stok_minimal' => $this->stok_minimal,
                'satuan' => $this->satuan,
            ]);
            session()->flash('message', 'Barang berhasil ditambahkan.');
        }

        return $this->redirect(route('admin.barang.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.barang.barang-form')->layout('components.admin-layout');
    }
}
