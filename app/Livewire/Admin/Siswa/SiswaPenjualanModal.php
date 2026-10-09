<?php

namespace App\Livewire\Admin\Siswa;

use App\Models\Barang;
use App\Models\Kas;
use App\Models\Penjualan;
use App\Models\Siswa;
use App\Models\Tabungan;
use App\Models\Tagihan;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class SiswaPenjualanModal extends Component
{
    public $showPenjualanModal = false;

    public $showWarningModal = false;

    public $penjualan_siswa_id = null;

    public $penjualan_siswa_lembaga_id = null;

    public $penjualan_siswa_nama = '';

    public $penjualan_kode = '';

    public $penjualan_pembayaran = 'KAS';

    public $penjualan_items = [];

    public $penjualan_total = 0;

    #[On('open-penjualan-modal')]
    public function openPenjualanModal($siswa_id)
    {
        $siswa = Siswa::findOrFail($siswa_id);
        $this->penjualan_siswa_id = $siswa->id;
        $this->penjualan_siswa_lembaga_id = $siswa->lembaga_id;
        $this->penjualan_siswa_nama = $siswa->nama;

        $barangsCount = Barang::where('lembaga_id', $siswa->lembaga_id)->count();

        if ($barangsCount === 0) {
            $this->showWarningModal = true;

            return;
        }

        $this->penjualan_kode = 'PJ'.strtoupper(Str::random(6));
        $this->penjualan_pembayaran = 'tun';
        $this->penjualan_items = [];
        $this->addPenjualanItem();
        $this->showPenjualanModal = true;
    }

    public function addPenjualanItem()
    {
        $this->penjualan_items[] = [
            'barang_id' => '',
            'jumlah' => 1,
            'harga' => 0,
            'total' => 0,
        ];
    }

    public function removePenjualanItem($index)
    {
        unset($this->penjualan_items[$index]);
        $this->penjualan_items = array_values($this->penjualan_items);
        $this->calculatePenjualanTotal();
    }

    public function updated($property, $value)
    {
        if (Str::startsWith($property, 'penjualan_items.')) {
            $parts = explode('.', $property);
            if (count($parts) === 3) {
                $index = $parts[1];
                $field = $parts[2];

                if ($field === 'barang_id') {
                    $barang = Barang::find($value);
                    if ($barang) {
                        if ($barang->stok <= 0) {
                            \Flux::toast('Stok barang habis (0).', variant: 'danger');
                            $this->penjualan_items[$index]['barang_id'] = '';

                            return;
                        }

                        // Check if already in list
                        $existingIndex = null;
                        foreach ($this->penjualan_items as $k => $item) {
                            if ($k != $index && ! empty($item['barang_id']) && $item['barang_id'] == $value) {
                                $existingIndex = $k;
                                break;
                            }
                        }

                        if ($existingIndex !== null) {
                            // Increment existing
                            $newQty = (int) ($this->penjualan_items[$existingIndex]['jumlah'] ?? 0) + 1;
                            if ($newQty > $barang->stok) {
                                \Flux::toast('Jumlah melebihi stok yang tersedia.', variant: 'danger');
                            } else {
                                $this->penjualan_items[$existingIndex]['jumlah'] = $newQty;
                                $price = (int) ($this->penjualan_items[$existingIndex]['harga'] ?? 0);
                                $this->penjualan_items[$existingIndex]['total'] = $newQty * $price;
                                \Flux::toast('Barang sudah ada di daftar. Jumlah ditambahkan.', variant: 'success');
                            }

                            // Reset current row so user can pick another
                            $this->penjualan_items[$index]['barang_id'] = '';
                            $this->calculatePenjualanTotal();

                            return;
                        }

                        // Not in list, add it
                        $this->penjualan_items[$index]['harga'] = $barang->harga ?? 0;
                        $this->penjualan_items[$index]['jumlah'] = 1;

                        // Automatically add new row if selecting item on the last row
                        if ($index == count($this->penjualan_items) - 1) {
                            $this->addPenjualanItem();
                        }
                    }
                }

                if ($field === 'jumlah') {
                    $barangId = $this->penjualan_items[$index]['barang_id'] ?? null;
                    if ($barangId) {
                        $barang = Barang::find($barangId);
                        if ($barang) {
                            $qty = (int) $value;
                            if ($qty > $barang->stok) {
                                \Flux::toast('Jumlah melebihi stok yang tersedia ('.$barang->stok.').', variant: 'danger');
                                $this->penjualan_items[$index]['jumlah'] = $barang->stok;
                            } elseif ($qty < 1) {
                                $this->penjualan_items[$index]['jumlah'] = 1;
                            }
                        }
                    }
                }

                $qty = (int) ($this->penjualan_items[$index]['jumlah'] ?? 0);
                $price = (int) ($this->penjualan_items[$index]['harga'] ?? 0);
                $this->penjualan_items[$index]['total'] = $qty * $price;

                $this->calculatePenjualanTotal();
            }
        }
    }

    public function calculatePenjualanTotal()
    {
        $this->penjualan_total = collect($this->penjualan_items)->sum('total');
    }

    public function savePenjualan()
    {
        // Remove empty rows before validating
        $this->penjualan_items = array_filter($this->penjualan_items, function ($item) {
            return ! empty($item['barang_id']);
        });

        // Re-index array after filtering
        $this->penjualan_items = array_values($this->penjualan_items);

        $this->validate([
            'penjualan_kode' => 'required|string|max:20',
            'penjualan_siswa_id' => 'required|exists:siswa,id',
            'penjualan_pembayaran' => 'required|in:tun,tab,tag',
            'penjualan_items' => 'required|array|min:1',
            'penjualan_items.*.barang_id' => 'required|exists:barang,id',
            'penjualan_items.*.jumlah' => 'required|integer|min:1',
            'penjualan_items.*.harga' => 'required|numeric|min:0',
        ]);

        // Tabungan check before proceeding
        $tabunganDipilih = null;
        if ($this->penjualan_pembayaran === 'tab') {
            $tabunganDipilih = Tabungan::where('siswa_id', $this->penjualan_siswa_id)
                ->where('saldo', '>=', $this->penjualan_total)
                ->first();

            if (! $tabunganDipilih) {
                $this->addError('penjualan_pembayaran', 'Saldo tabungan tidak mencukupi untuk total belanja.');
                \Flux::toast('Saldo tabungan siswa tidak mencukupi.', variant: 'danger');

                return;
            }
        }

        $kasPenjualan = Kas::where('penjualan', true)->first();
        if (! $kasPenjualan && in_array($this->penjualan_pembayaran, ['tun', 'tab'])) {
            \Flux::toast('Kas Penjualan belum diatur di sistem.', variant: 'danger');

            return;
        }

        DB::transaction(function () use ($tabunganDipilih, $kasPenjualan) {
            $penjualan = Penjualan::create([
                'kode' => $this->penjualan_kode,
                'siswa_id' => $this->penjualan_siswa_id,
                'pembayaran' => $this->penjualan_pembayaran,
                'total' => $this->penjualan_total,
                'user_id' => auth()->id(),
            ]);

            foreach ($this->penjualan_items as $item) {
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
            $keteranganMetode = match ($this->penjualan_pembayaran) {
                'tun' => 'Tunai',
                'tab' => 'Tabungan',
                'tag' => 'Tagihan',
                default => 'Lainnya',
            };

            Transaksi::create([
                'kode' => 'PB'.strtoupper(Str::random(6)),
                'jumlah' => $this->penjualan_total,
                'keterangan' => 'Penjualan Siswa '.$penjualan->siswa->nama.' (Metode: '.$keteranganMetode.')',
                'transable_type' => Penjualan::class,
                'transable_id' => $penjualan->id,
                'user_id' => auth()->id(),
            ]);

            if ($this->penjualan_pembayaran === 'tun') {
                $kasPenjualan->increment('saldo', $this->penjualan_total);
            } elseif ($this->penjualan_pembayaran === 'tab') {
                $tabunganDipilih->decrement('saldo', $this->penjualan_total);
                $kasPenjualan->increment('saldo', $this->penjualan_total);
            } elseif ($this->penjualan_pembayaran === 'tag') {
                $tagihanKas = $kasPenjualan ?? Kas::first();
                Tagihan::create([
                    'kode' => 'TAG'.strtoupper(Str::random(6)),
                    'siswa_id' => $this->penjualan_siswa_id,
                    'kas_id' => $tagihanKas ? $tagihanKas->id : null,
                    'jumlah' => $this->penjualan_total,
                    'bayar' => 0,
                    'keterangan' => 'Tagihan dari Penjualan '.$penjualan->kode,
                    'tagihanable_type' => Penjualan::class,
                    'tagihanable_id' => $penjualan->id,
                    'user_id' => auth()->id(),
                ]);
            }
        });

        $this->showPenjualanModal = false;
        \Flux::toast('Transaksi Penjualan berhasil disimpan.', variant: 'success');
    }

    public function render()
    {
        $barangs = Barang::when($this->penjualan_siswa_lembaga_id, function ($query) {
            $query->where('lembaga_id', $this->penjualan_siswa_lembaga_id);
        })->orderBy('nama')->get();

        return view('livewire.admin.siswa.siswa-penjualan-modal', [
            'barangs' => $barangs,
        ]);
    }
}
