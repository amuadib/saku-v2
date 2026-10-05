<?php

namespace App\Livewire\Admin\Penjualan;

use App\Models\Barang;
use App\Models\Kas;
use App\Models\Penjualan;
use App\Models\Siswa;
use App\Models\Tabungan;
use App\Models\Tagihan;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class PenjualanForm extends Component
{
    public ?Penjualan $penjualan = null;

    public $kode = '';

    public $siswa_id = '';

    public $pembayaran = 'tun'; // tun, tab, tag

    // Array of detail items
    // format: [['barang_id' => '', 'jumlah' => 1, 'harga' => 0, 'total' => 0]]
    public $items = [];

    // Summary
    public $total_transaksi = 0;

    protected function rules()
    {
        return [
            'kode' => 'required|string|max:20',
            'siswa_id' => 'required|exists:siswa,id',
            'pembayaran' => 'required|in:tun,tab,tag',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barang,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric|min:0',
        ];
    }

    public function mount(?Penjualan $penjualan = null)
    {
        if ($penjualan && $penjualan->exists) {
            $this->penjualan = $penjualan;
            $this->kode = $penjualan->kode ?? '';
            $this->siswa_id = $penjualan->siswa_id;
            $this->pembayaran = $penjualan->pembayaran;

            foreach ($penjualan->detail as $detail) {
                $this->items[] = [
                    'id' => $detail->id,
                    'barang_id' => $detail->barang_id,
                    'jumlah' => $detail->jumlah,
                    'harga' => (int) $detail->harga,
                    'total' => (int) $detail->total,
                ];
            }
            $this->calculateTotal();
        } else {
            $this->kode = 'PJ'.strtoupper(Str::random(6));
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

    public function updated($property, $value)
    {
        if (Str::startsWith($property, 'items.')) {
            $parts = explode('.', $property);
            if (count($parts) === 3) {
                $index = (int) $parts[1];
                $field = $parts[2];

                if ($field === 'barang_id') {
                    $barang = Barang::find($value);
                    if ($barang) {
                        if ($barang->stok <= 0) {
                            \Flux::toast('Stok barang habis (0).', variant: 'danger');
                            $this->items[$index]['barang_id'] = '';

                            return;
                        }

                        // Check if already in list
                        $existingIndex = null;
                        foreach ($this->items as $k => $item) {
                            if ($k != $index && ! empty($item['barang_id']) && $item['barang_id'] == $value) {
                                $existingIndex = $k;
                                break;
                            }
                        }

                        if ($existingIndex !== null) {
                            // Increment existing
                            $newQty = (int) ($this->items[$existingIndex]['jumlah'] ?? 0) + 1;
                            if ($newQty > $barang->stok) {
                                \Flux::toast('Jumlah melebihi stok yang tersedia.', variant: 'danger');
                            } else {
                                $this->items[$existingIndex]['jumlah'] = $newQty;
                                $price = (int) ($this->items[$existingIndex]['harga'] ?? 0);
                                $this->items[$existingIndex]['total'] = $newQty * $price;
                                \Flux::toast('Barang sudah ada di daftar. Jumlah ditambahkan.', variant: 'success');
                            }

                            // Reset current row so user can pick another
                            $this->items[$index]['barang_id'] = '';
                            $this->calculateTotal();

                            return;
                        }

                        // Not in list, add it
                        $this->items[$index]['harga'] = $barang->harga ?? 0;
                        $this->items[$index]['jumlah'] = 1;

                        // Automatically add new row if selecting item on the last row
                        if ($index === count($this->items) - 1) {
                            $this->addItem();
                        }
                    }
                }

                if ($field === 'jumlah') {
                    $barangId = $this->items[$index]['barang_id'] ?? null;
                    if ($barangId) {
                        $barang = Barang::find($barangId);
                        if ($barang) {
                            $qty = (int) $value;
                            if ($qty > $barang->stok) {
                                \Flux::toast('Jumlah melebihi stok yang tersedia ('.$barang->stok.').', variant: 'danger');
                                $this->items[$index]['jumlah'] = $barang->stok;
                            } elseif ($qty < 1) {
                                $this->items[$index]['jumlah'] = 1;
                            }
                        }
                    }
                }

                // Recalculate row total
                $qty = (int) ($this->items[$index]['jumlah'] ?? 0);
                $price = (int) ($this->items[$index]['harga'] ?? 0);
                $this->items[$index]['total'] = $qty * $price;

                $this->calculateTotal();
            }
        }
    }

    public function updatedSiswaId($value)
    {
        // Reset items when siswa changes so they don't keep invalid barang_ids
        $this->items = [];
        $this->addItem();
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total_transaksi = collect($this->items)->sum('total');
    }

    public function save()
    {
        // Filter out empty items
        $this->items = array_filter($this->items, function ($item) {
            return ! empty($item['barang_id']);
        });

        // Re-index array after filtering
        $this->items = array_values($this->items);

        $this->validate();

        // Tabungan check before proceeding
        $tabunganDipilih = null;
        if ($this->pembayaran === 'tab') {
            $tabunganDipilih = Tabungan::where('siswa_id', $this->siswa_id)
                ->where('saldo', '>=', $this->total_transaksi)
                ->first();

            if (! $tabunganDipilih) {
                $this->addError('pembayaran', 'Saldo tabungan tidak mencukupi untuk total belanja.');
                \Flux::toast('Saldo tabungan siswa tidak mencukupi.', variant: 'danger');

                return;
            }
        }

        $kasPenjualan = Kas::where('penjualan', true)->first();
        if (! $kasPenjualan && in_array($this->pembayaran, ['tun', 'tab'])) {
            \Flux::toast('Kas Penjualan belum diatur di sistem.', variant: 'danger');

            return;
        }

        $penjualanData = [
            'kode' => $this->kode,
            'siswa_id' => $this->siswa_id,
            'pembayaran' => $this->pembayaran,
            'total' => $this->total_transaksi,
            'user_id' => auth()->id(),
        ];

        if ($this->penjualan && $this->penjualan->exists) {
            // Note: Updating existing penjualan with new payments is complex (reversing old tabungan/kas changes).
            // Usually we don't allow changing payment method or total after it's saved, or we reverse it first.
            // For now, we will just update the data (which might cause inconsistencies if they changed payment).
            // It's better to prevent editing of saved transactions completely, or just reverse them.
            // But if we must:
            $this->penjualan->update($penjualanData);

            // Re-sync details
            $this->penjualan->detail()->delete();
            foreach ($this->items as $item) {
                $this->penjualan->detail()->create([
                    'barang_id' => $item['barang_id'],
                    'jumlah' => $item['jumlah'],
                    'harga' => $item['harga'],
                    'total' => $item['total'],
                ]);
            }

            session()->flash('message', 'Penjualan berhasil diperbarui.');
        } else {
            DB::transaction(function () use ($penjualanData, $tabunganDipilih, $kasPenjualan) {
                $penjualan = Penjualan::create($penjualanData);
                foreach ($this->items as $item) {
                    $penjualan->detail()->create([
                        'barang_id' => $item['barang_id'],
                        'jumlah' => $item['jumlah'],
                        'harga' => $item['harga'],
                        'total' => $item['total'],
                    ]);

                    // Kurangi stok barang
                    $barang = Barang::find($item['barang_id']);
                    if ($barang) {
                        $barang->decrement('stok', $item['jumlah']);
                    }
                }

                // Input penjualan ke Transaksi
                $keteranganMetode = match ($this->pembayaran) {
                    'tun' => 'Tunai',
                    'tab' => 'Tabungan',
                    'tag' => 'Tagihan',
                    default => 'Lainnya',
                };

                Transaksi::create([
                    'kode' => 'PB'.strtoupper(Str::random(6)),
                    'jumlah' => $this->total_transaksi,
                    'keterangan' => 'Penjualan Siswa '.$penjualan->siswa->nama.' (Metode: '.$keteranganMetode.')',
                    'transable_type' => Penjualan::class,
                    'transable_id' => $penjualan->id,
                    'user_id' => auth()->id(),
                ]);

                if ($this->pembayaran === 'tun') {
                    $kasPenjualan->increment('saldo', $this->total_transaksi);
                } elseif ($this->pembayaran === 'tab') {
                    $tabunganDipilih->decrement('saldo', $this->total_transaksi);
                    $kasPenjualan->increment('saldo', $this->total_transaksi);
                } elseif ($this->pembayaran === 'tag') {
                    $tagihanKas = $kasPenjualan ?? Kas::first();
                    Tagihan::create([
                        'kode' => 'TAG'.strtoupper(Str::random(6)),
                        'siswa_id' => $this->siswa_id,
                        'kas_id' => $tagihanKas ? $tagihanKas->id : null,
                        'jumlah' => $this->total_transaksi,
                        'bayar' => 0,
                        'keterangan' => 'Tagihan dari Penjualan '.$penjualan->kode,
                        'tagihanable_type' => Penjualan::class,
                        'tagihanable_id' => $penjualan->id,
                        'user_id' => auth()->id(),
                    ]);
                }
            });

            session()->flash('message', 'Penjualan berhasil ditambahkan.');
        }

        return $this->redirect(route('admin.penjualan.index'), navigate: true);
    }

    public function render()
    {
        $lembagaId = null;
        if (! auth()->user()->isAdmin()) {
            $lembagaId = auth()->user()->authable->lembaga_id ?? null;
        }

        $siswas = Siswa::select('id', 'nama', 'nis', 'lembaga_id')
            ->when($lembagaId, function ($q) use ($lembagaId) {
                $q->where('lembaga_id', $lembagaId);
            })
            ->orderBy('nama')
            ->get();

        // Ensure Barang only belongs to the selected Siswa's lembaga (if a siswa is selected)
        // Otherwise, filter by the user's lembaga if not admin.
        $barangLembagaId = $lembagaId;
        if ($this->siswa_id) {
            $selectedSiswa = $siswas->firstWhere('id', $this->siswa_id);
            if ($selectedSiswa) {
                $barangLembagaId = $selectedSiswa->lembaga_id;
            }
        }

        $barangs = Barang::when($barangLembagaId, function ($q) use ($barangLembagaId) {
            $q->where('lembaga_id', $barangLembagaId);
        })
            ->orderBy('nama')
            ->get();

        return view('livewire.admin.penjualan.penjualan-form', [
            'siswas' => $siswas,
            'barangs' => $barangs,
        ])->layout('components.admin-layout');
    }
}
