<?php

namespace App\Livewire\Admin\Pembelian;

use Livewire\Component;
use App\Models\Pembelian;
use App\Models\DetailPembelian;
use App\Models\Supplier;
use App\Models\Barang;
use Illuminate\Support\Str;

class PembelianForm extends Component
{
    public ?Pembelian $pembelian = null;
    
    public $kode = '';
    public $supplier_id = '';
    
    // Array of detail items
    // format: [['barang_id' => '', 'jumlah' => 1, 'harga' => 0, 'total' => 0]]
    public $items = [];

    // Summary
    public $total_transaksi = 0;

    protected function rules()
    {
        return [
            'kode' => 'required|string|max:20',
            'supplier_id' => 'required|exists:supplier,id',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barang,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric|min:0',
        ];
    }

    public function mount(Pembelian $pembelian = null)
    {
        if ($pembelian && $pembelian->exists) {
            $this->pembelian = $pembelian;
            $this->kode = $pembelian->kode;
            $this->supplier_id = $pembelian->supplier_id;
            
            foreach ($pembelian->detail as $detail) {
                $this->items[] = [
                    'id' => $detail->id, // track existing id
                    'barang_id' => $detail->barang_id,
                    'jumlah' => $detail->jumlah,
                    'harga' => (int) $detail->harga,
                    'total' => (int) $detail->total,
                ];
            }
            $this->calculateTotal();
        } else {
            $this->kode = 'PB-' . strtoupper(Str::random(6));
            $this->addItem();
        }
    }

    public function addItem()
    {
        $this->items[] = [
            'id' => null,
            'barang_id' => '',
            'jumlah' => 1,
            'harga' => 0,
            'total' => 0,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotal();
    }

    public function updatedItems($value, $key)
    {
        // $key looks like "0.jumlah" or "1.barang_id"
        $parts = explode('.', $key);
        if (count($parts) == 2) {
            $index = $parts[0];
            $field = $parts[1];

            // If barang changed, auto-fill price from product list (basic implementation)
            if ($field === 'barang_id') {
                $barang = Barang::find($this->items[$index]['barang_id']);
                if ($barang) {
                    $this->items[$index]['harga'] = $barang->harga_beli ?? 0;
                }
            }

            // Recalculate row total
            $qty = (int) ($this->items[$index]['jumlah'] ?? 0);
            $price = (int) ($this->items[$index]['harga'] ?? 0);
            $this->items[$index]['total'] = $qty * $price;

            $this->calculateTotal();
        }
    }

    public function calculateTotal()
    {
        $this->total_transaksi = collect($this->items)->sum('total');
    }

    public function save()
    {
        $this->validate();

        $pembelianData = [
            'kode' => $this->kode,
            'supplier_id' => $this->supplier_id,
            'total' => $this->total_transaksi,
            'user_id' => auth()->id(),
        ];

        if ($this->pembelian && $this->pembelian->exists) {
            $this->pembelian->update($pembelianData);
            
            // Re-sync details. For simplicity, delete old and recreate
            $this->pembelian->detail()->delete();
            foreach ($this->items as $item) {
                $this->pembelian->detail()->create([
                    'barang_id' => $item['barang_id'],
                    'jumlah' => $item['jumlah'],
                    'harga' => $item['harga'],
                    'total' => $item['total'],
                ]);
            }
            
            session()->flash('message', 'Pembelian berhasil diperbarui.');
        } else {
            $pembelian = Pembelian::create($pembelianData);
            foreach ($this->items as $item) {
                $pembelian->detail()->create([
                    'barang_id' => $item['barang_id'],
                    'jumlah' => $item['jumlah'],
                    'harga' => $item['harga'],
                    'total' => $item['total'],
                ]);
            }
            
            session()->flash('message', 'Pembelian berhasil ditambahkan.');
        }

        return $this->redirect(route('admin.pembelian.index'), navigate: true);
    }

    public function render()
    {
        $suppliers = Supplier::orderBy('nama')->get();
        $barangs = Barang::orderBy('nama')->get();

        return view('livewire.admin.pembelian.pembelian-form', [
            'suppliers' => $suppliers,
            'barangs' => $barangs
        ])->layout('components.admin-layout');
    }
}
