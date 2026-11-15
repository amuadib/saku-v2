<?php

namespace App\Livewire\Admin\Pembelian;

use App\Models\Barang;
use App\Models\Pembelian;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class PembelianForm extends Component
{
    public ?Pembelian $pembelian = null;

    public $kode = '';

    public $supplier_id = '';

    // Array of detail items
    // format: [['_uid' => '...', 'id' => null, 'barang_id' => '', 'jumlah' => 1, 'harga' => 0, 'total' => 0]]
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

    protected $messages = [
        'kode.required' => 'Kode pembelian wajib diisi.',
        'supplier_id.required' => 'Silakan pilih supplier.',
        'supplier_id.exists' => 'Supplier yang dipilih tidak valid.',
        'items.required' => 'Minimal harus ada satu baris barang.',
        'items.min' => 'Minimal harus ada satu baris barang.',
        'items.*.barang_id.required' => 'Pilih barang untuk setiap baris.',
        'items.*.barang_id.exists' => 'Barang tidak ditemukan dalam sistem.',
        'items.*.jumlah.required' => 'Jumlah barang wajib diisi.',
        'items.*.jumlah.min' => 'Jumlah minimal 1.',
        'items.*.harga.required' => 'Harga beli wajib diisi.',
        'items.*.harga.min' => 'Harga beli tidak boleh negatif.',
    ];

    public function mount(?Pembelian $pembelian = null)
    {
        if ($pembelian && $pembelian->exists) {
            $this->pembelian = $pembelian;
            $this->kode = $pembelian->kode;
            $this->supplier_id = $pembelian->supplier_id;

            foreach ($pembelian->detail as $detail) {
                $this->items[] = [
                    '_uid' => 'row_'.(string) Str::uuid(),
                    'id' => $detail->id, // track existing id
                    'barang_id' => (string) $detail->barang_id,
                    'jumlah' => (int) $detail->jumlah,
                    'harga' => (float) $detail->harga,
                    'total' => (float) $detail->total,
                ];
            }
            $this->calculateTotal();
        } else {
            $this->kode = 'PB-'.strtoupper(Str::random(6));
            $this->addItem();
        }
    }

    public function addItem()
    {
        $this->items[] = [
            '_uid' => 'row_'.(string) Str::uuid(),
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
        if (empty($this->items)) {
            $this->addItem();
        }
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total_transaksi = (float) collect($this->items)->sum('total');
    }

    /**
     * Otomatis membuat barang baru jika data tidak ditemukan saat pencarian.
     */
    #[Renderless]
    public function quickCreateBarang(string $nama, ?int $index = null): array
    {
        $cleanNama = trim($nama);
        if ($cleanNama === '') {
            return [];
        }

        // Cek jika barang dengan nama yang sama sudah ada
        $existing = Barang::where('nama', 'like', $cleanNama)->first();
        if ($existing) {
            if ($index !== null && isset($this->items[$index])) {
                $this->items[$index]['barang_id'] = (string) $existing->id;
                $this->items[$index]['harga'] = (float) ($existing->harga_beli ?? 0);
                $qty = (int) ($this->items[$index]['jumlah'] ?? 1);
                $this->items[$index]['total'] = $qty * (float) ($existing->harga_beli ?? 0);
                $this->calculateTotal();
            }

            return [
                'id' => (string) $existing->id,
                'nama' => $existing->nama,
                'kode' => $existing->kode ?? '',
                'stok' => (int) $existing->stok,
                'satuan' => $existing->satuan ?? 'PCS',
                'harga_beli' => (float) ($existing->harga_beli ?? 0),
                'is_new' => false,
            ];
        }

        // Generate kode unik
        $kode = 'BRG-'.strtoupper(Str::random(6));
        while (Barang::where('kode', $kode)->exists()) {
            $kode = 'BRG-'.strtoupper(Str::random(6));
        }

        $lembagaId = auth()->user()?->authable?->lembaga_id ?? array_key_first(config('custom.lembaga') ?? []) ?? 1;

        $barang = Barang::create([
            'nama' => $cleanNama,
            'kode' => $kode,
            'jenis' => 'UMU',
            'satuan' => 'PCS',
            'harga' => 0,
            'harga_beli' => 0,
            'stok' => 0,
            'stok_minimal' => 0,
            'lembaga_id' => $lembagaId,
        ]);

        if ($index !== null && isset($this->items[$index])) {
            $this->items[$index]['barang_id'] = (string) $barang->id;
            $this->items[$index]['harga'] = 0;
            $this->items[$index]['total'] = 0;
            $this->calculateTotal();
        }

        \Flux::toast("Barang \"{$barang->nama}\" berhasil ditambahkan ke master barang.", variant: 'success');

        return [
            'id' => (string) $barang->id,
            'nama' => $barang->nama,
            'kode' => $barang->kode,
            'stok' => 0,
            'satuan' => 'PCS',
            'harga_beli' => 0,
            'is_new' => true,
        ];
    }

    public function save(array $itemsPayload = [])
    {
        if (! empty($itemsPayload)) {
            $filtered = array_values(array_filter($itemsPayload, fn ($item) => ! empty($item['barang_id'])));
            $this->items = ! empty($filtered) ? $filtered : $itemsPayload;
        }

        foreach ($this->items as $idx => $item) {
            $qty = max(1, (int) ($item['jumlah'] ?? 1));
            $price = max(0, (float) ($item['harga'] ?? 0));
            $this->items[$idx]['jumlah'] = $qty;
            $this->items[$idx]['harga'] = $price;
            $this->items[$idx]['total'] = $qty * $price;
        }
        $this->calculateTotal();

        $this->validate();

        DB::transaction(function () {
            $pembelianData = [
                'kode' => $this->kode,
                'supplier_id' => $this->supplier_id,
                'total' => $this->total_transaksi,
                'user_id' => auth()->id(),
            ];

            if ($this->pembelian && $this->pembelian->exists) {
                $this->pembelian->update($pembelianData);
                $this->pembelian->detail()->delete();
                $targetPembelian = $this->pembelian;
            } else {
                $targetPembelian = Pembelian::create($pembelianData);
            }

            foreach ($this->items as $item) {
                $targetPembelian->detail()->create([
                    'barang_id' => $item['barang_id'],
                    'jumlah' => $item['jumlah'],
                    'harga' => $item['harga'],
                    'total' => $item['total'],
                ]);

                // Update harga_beli pada master barang jika diisi
                if (! empty($item['harga']) && $item['harga'] > 0) {
                    Barang::where('id', $item['barang_id'])->update(['harga_beli' => $item['harga']]);
                }
            }
        });

        session()->flash('message', $this->pembelian && $this->pembelian->exists ? 'Pembelian berhasil diperbarui.' : 'Pembelian berhasil ditambahkan.');

        return $this->redirect(route('admin.pembelian.index'), navigate: true);
    }

    public function render()
    {
        $suppliers = Supplier::orderBy('nama')->get();
        $barangs = Barang::orderBy('nama')->get();

        $barangsFormatted = $barangs->map(function ($b) {
            return [
                'id' => (string) $b->id,
                'nama' => $b->nama,
                'kode' => $b->kode ?? '',
                'stok' => (int) $b->stok,
                'satuan' => $b->satuan ?? 'PCS',
                'harga_beli' => (float) ($b->harga_beli ?? 0),
            ];
        })->values()->toArray();

        return view('livewire.admin.pembelian.pembelian-form', [
            'suppliers' => $suppliers,
            'barangs' => $barangs,
            'barangsFormatted' => $barangsFormatted,
        ])->layout('components.admin-layout');
    }
}
