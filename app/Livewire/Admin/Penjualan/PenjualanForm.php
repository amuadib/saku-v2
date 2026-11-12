<?php

namespace App\Livewire\Admin\Penjualan;

use App\Models\Barang;
use App\Models\DetailPenjualan;
use App\Models\Kas;
use App\Models\Kelas;
use App\Models\Penjualan;
use App\Models\Siswa;
use App\Models\Tabungan;
use App\Models\Transaksi;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class PenjualanForm extends Component
{
    public ?Penjualan $penjualan = null;

    public string $kode = '';

    public string $siswa_id = '';

    public string $pembayaran = 'tun'; // tun, tab, tag

    /**
     * Array of detail items.
     *
     * @var array<int, array{id?: string|null, barang_id: string, jumlah: int|string, harga: int|float|string, total: int|float}>
     */
    public array $items = [];

    public int|float $total_transaksi = 0;

    /**
     * @return array<string, string>
     */
    protected function rules(): array
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

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'kode.required' => 'Kode penjualan wajib diisi.',
            'siswa_id.required' => 'Siswa pembeli wajib dipilih.',
            'siswa_id.exists' => 'Siswa pembeli tidak valid.',
            'pembayaran.required' => 'Metode pembayaran wajib dipilih.',
            'items.required' => 'Minimal pilih 1 barang.',
            'items.min' => 'Minimal pilih 1 barang.',
            'items.*.barang_id.required' => 'Barang wajib dipilih.',
            'items.*.jumlah.min' => 'Jumlah minimal 1.',
            'items.*.harga.min' => 'Harga tidak boleh negatif.',
        ];
    }

    public function mount(?Penjualan $penjualan = null): void
    {
        if ($penjualan && $penjualan->exists) {
            $this->penjualan = $penjualan;
            $this->kode = (string) ($penjualan->kode ?? '');
            $this->siswa_id = (string) $penjualan->siswa_id;
            $this->pembayaran = (string) ($penjualan->pembayaran ?? 'tun');

            foreach (DetailPenjualan::where('penjualan_id', $penjualan->id)->get() as $detail) {
                $this->items[] = [
                    'id' => $detail->id,
                    'barang_id' => (string) $detail->barang_id,
                    'jumlah' => (int) $detail->jumlah,
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

    public function addItem(): void
    {
        $this->items[] = [
            'id' => null,
            'barang_id' => '',
            'jumlah' => 1,
            'harga' => 0,
            'total' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotal();
    }

    public function updated(string $property, mixed $value): void
    {
        if (Str::startsWith($property, 'items.')) {
            $parts = explode('.', $property);
            if (count($parts) === 3) {
                $index = (int) $parts[1];
                $field = $parts[2];

                if (! isset($this->items[$index])) {
                    return;
                }

                if ($field === 'barang_id') {
                    $barang = is_string($value) || is_int($value) ? Barang::whereKey($value)->first() : null;
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

                        if ($existingIndex !== null && isset($this->items[$existingIndex])) {
                            $existingItem = $this->items[$existingIndex];
                            $newQty = (int) $existingItem['jumlah'] + 1;
                            if ($newQty > $barang->stok) {
                                \Flux::toast('Jumlah melebihi stok yang tersedia.', variant: 'danger');
                            } else {
                                $price = (int) $existingItem['harga'];
                                $existingItem['jumlah'] = $newQty;
                                $existingItem['total'] = $newQty * $price;
                                $this->items[$existingIndex] = $existingItem;
                                \Flux::toast('Barang sudah ada di daftar. Jumlah ditambahkan.', variant: 'success');
                            }

                            $this->items[$index]['barang_id'] = '';
                            $this->calculateTotal();

                            return;
                        }

                        $this->items[$index]['harga'] = (int) $barang->harga;
                        $this->items[$index]['jumlah'] = 1;

                        if ($index === count($this->items) - 1) {
                            $this->addItem();
                        }
                    }
                }

                if ($field === 'jumlah') {
                    $barangId = $this->items[$index]['barang_id'];
                    if ($barangId !== '') {
                        $barang = Barang::whereKey($barangId)->first();
                        if ($barang) {
                            $qty = is_numeric($value) ? (int) $value : 1;
                            if ($qty > $barang->stok) {
                                \Flux::toast('Jumlah melebihi stok yang tersedia ('.$barang->stok.').', variant: 'danger');
                                $this->items[$index]['jumlah'] = $barang->stok;
                            } elseif ($qty < 1) {
                                $this->items[$index]['jumlah'] = 1;
                            }
                        }
                    }
                }

                $qty = (int) $this->items[$index]['jumlah'];
                $price = (int) $this->items[$index]['harga'];
                $this->items[$index]['total'] = $qty * $price;

                $this->calculateTotal();
            }
        }
    }

    public function updatedSiswaId(mixed $value): void
    {
        $this->items = [];
        $this->addItem();
        $this->calculateTotal();
    }

    public function calculateTotal(): void
    {
        $this->total_transaksi = collect($this->items)->sum('total');
    }

    /**
     * Single-roundtrip submission from the desktop-like client POS UI.
     *
     * @param  array{kode?: string, siswa_id?: string, pembayaran?: string, items?: array<int, array<string, mixed>>}  $payload
     * @return array{ok: bool, errors: array<string, array<int, string>>}|null
     */
    public function saveTransaction(array $payload = []): mixed
    {
        if ($this->penjualan && $this->penjualan->exists) {
            return ['ok' => false, 'errors' => ['general' => ['Transaksi yang sudah tersimpan tidak dapat diubah.']]];
        }

        $this->kode = trim((string) ($payload['kode'] ?? $this->kode));
        $this->siswa_id = (string) ($payload['siswa_id'] ?? $this->siswa_id);
        $this->pembayaran = (string) ($payload['pembayaran'] ?? $this->pembayaran);

        $rawItems = $payload['items'] ?? $this->items;
        $this->items = collect($rawItems)
            ->filter(fn (array $item): bool => ! empty($item['barang_id']))
            ->map(function (array $item): array {
                $qty = is_numeric($item['jumlah'] ?? null) ? (int) $item['jumlah'] : 0;
                $harga = is_numeric($item['harga'] ?? null) ? (int) $item['harga'] : 0;

                return [
                    'id' => isset($item['id']) ? (string) $item['id'] : null,
                    'barang_id' => (string) $item['barang_id'],
                    'jumlah' => $qty,
                    'harga' => $harga,
                    'total' => $qty * $harga,
                ];
            })
            ->values()
            ->all();

        $this->calculateTotal();
        $this->resetErrorBag();

        try {
            if (! $this->persistPenjualan()) {
                return [
                    'ok' => false,
                    'errors' => $this->getErrorBag()->toArray(),
                ];
            }
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());

            return [
                'ok' => false,
                'errors' => $e->validator->errors()->toArray(),
            ];
        }

        return $this->redirect(route('admin.penjualan.index'), navigate: true);
    }

    public function save(): mixed
    {
        if (! $this->persistPenjualan()) {
            return null;
        }

        return $this->redirect(route('admin.penjualan.index'), navigate: true);
    }

    protected function persistPenjualan(): bool
    {
        $this->items = array_values(array_filter($this->items, function ($item) {
            return ! empty($item['barang_id']);
        }));

        foreach ($this->items as $index => $item) {
            $qty = (int) $item['jumlah'];
            $harga = (int) $item['harga'];
            $this->items[$index]['jumlah'] = $qty;
            $this->items[$index]['harga'] = $harga;
            $this->items[$index]['total'] = $qty * $harga;
        }

        $this->calculateTotal();
        $this->validate();

        $tabunganDipilih = null;
        if ($this->pembayaran === 'tab') {
            $tabunganDipilih = Tabungan::where('siswa_id', $this->siswa_id)
                ->where('saldo', '>=', $this->total_transaksi)
                ->first();

            if (! $tabunganDipilih) {
                $this->addError('pembayaran', 'Saldo tabungan tidak mencukupi untuk total belanja.');
                \Flux::toast('Saldo tabungan siswa tidak mencukupi.', variant: 'danger');

                return false;
            }
        }

        $kasPenjualan = Kas::where('penjualan', true)->first();
        if (! $kasPenjualan && in_array($this->pembayaran, ['tun', 'tab'])) {
            $this->addError('pembayaran', 'Kas Penjualan belum diatur di sistem.');
            \Flux::toast('Kas Penjualan belum diatur di sistem.', variant: 'danger');

            return false;
        }

        $penjualanData = [
            'kode' => $this->kode,
            'siswa_id' => $this->siswa_id,
            'pembayaran' => $this->pembayaran,
            'total' => $this->total_transaksi,
            'user_id' => auth()->id(),
        ];

        if ($this->penjualan && $this->penjualan->exists) {
            $this->penjualan->update($penjualanData);

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
                // Aggregate requested quantities per barang_id to validate stock accurately.
                $requestedByBarang = [];
                foreach ($this->items as $item) {
                    $barangId = (string) $item['barang_id'];
                    $requestedByBarang[$barangId] = ($requestedByBarang[$barangId] ?? 0) + (int) $item['jumlah'];
                }

                $lockedBarangs = Barang::whereIn('id', array_keys($requestedByBarang))
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($requestedByBarang as $barangId => $requestedQty) {
                    $barang = $lockedBarangs->get($barangId);
                    if (! $barang) {
                        throw ValidationException::withMessages([
                            'items' => 'Barang tidak ditemukan.',
                        ]);
                    }

                    if ($barang->stok < $requestedQty) {
                        throw ValidationException::withMessages([
                            'items' => "Stok {$barang->nama} tidak mencukupi (tersedia: {$barang->stok}, diminta: {$requestedQty}).",
                        ]);
                    }
                }

                $penjualan = Penjualan::create($penjualanData);

                foreach ($this->items as $item) {
                    $penjualan->detail()->create([
                        'barang_id' => $item['barang_id'],
                        'jumlah' => $item['jumlah'],
                        'harga' => $item['harga'],
                        'total' => $item['total'],
                    ]);

                    $barang = $lockedBarangs->get((string) $item['barang_id']);
                    if ($barang) {
                        $barang->decrement('stok', (int) $item['jumlah']);
                    }
                }

                $keteranganMetode = match ($this->pembayaran) {
                    'tun' => 'Tunai',
                    'tab' => 'Tabungan',
                    'tag' => 'Tagihan',
                    default => 'Lainnya',
                };

                $siswaModel = Siswa::find($this->siswa_id);
                $namaSiswa = $siswaModel ? $siswaModel->nama : '-';

                Transaksi::create([
                    'kode' => 'PB'.strtoupper(Str::random(6)),
                    'jumlah' => $this->total_transaksi,
                    'keterangan' => 'Penjualan Siswa '.$namaSiswa.' (Metode: '.$keteranganMetode.')',
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
                    $penjualan->tagihan()->create([
                        'kode' => 'TAG'.strtoupper(Str::random(6)),
                        'siswa_id' => $this->siswa_id,
                        'kas_id' => $tagihanKas ? $tagihanKas->id : null,
                        'jumlah' => $this->total_transaksi,
                        'bayar' => 0,
                        'keterangan' => 'Tagihan dari Penjualan '.$penjualan->kode,
                        'user_id' => auth()->id(),
                    ]);
                }
            });

            session()->flash('message', 'Penjualan berhasil ditambahkan.');
        }

        return true;
    }

    public function render(): View
    {
        $lembagaId = null;
        if (! auth()->user()->isAdmin()) {
            $lembagaId = auth()->user()->authable->lembaga_id ?? null;
        }

        $siswas = Siswa::select('id', 'nama', 'nis', 'lembaga_id', 'kelas_id')
            ->with('kelas:id,nama')
            ->withMax('tabungan', 'saldo')
            ->when($lembagaId, function ($q) use ($lembagaId) {
                $q->where('lembaga_id', $lembagaId);
            })
            ->orderBy('nama')
            ->get();

        $barangs = Barang::select('id', 'kode', 'nama', 'harga', 'stok', 'satuan', 'lembaga_id')
            ->when($lembagaId, function ($q) use ($lembagaId) {
                $q->where('lembaga_id', $lembagaId);
            })
            ->orderBy('nama')
            ->get();

        $siswasData = $siswas->map(function (Siswa $siswa): array {
            $kelas = $siswa->kelas;
            $kelasNama = $kelas instanceof Kelas ? $kelas->nama : '';

            return [
                'id' => (string) $siswa->id,
                'nama' => (string) $siswa->nama,
                'nis' => (string) ($siswa->nis ?? '-'),
                'kelas' => (string) $kelasNama,
                'lembaga_id' => $siswa->lembaga_id,
                'tabungan_saldo' => (int) ($siswa->tabungan_max_saldo ?? 0),
            ];
        })->values()->all();

        $barangsData = $barangs->map(fn (Barang $barang): array => [
            'id' => (string) $barang->id,
            'kode' => (string) ($barang->kode ?? ''),
            'nama' => (string) $barang->nama,
            'harga' => (int) ($barang->harga ?? 0),
            'stok' => (int) ($barang->stok ?? 0),
            'satuan' => (string) ($barang->satuan ?? 'PCS'),
            'lembaga_id' => $barang->lembaga_id,
        ])->values()->all();

        return view('livewire.admin.penjualan.penjualan-form', [
            'siswas' => $siswas,
            'barangs' => $barangs,
            'siswasData' => $siswasData,
            'barangsData' => $barangsData,
            'isReadOnly' => (bool) ($this->penjualan && $this->penjualan->exists),
        ])->layout('components.admin-layout');
    }
}
