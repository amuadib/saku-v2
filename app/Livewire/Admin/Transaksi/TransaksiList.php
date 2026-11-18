<?php

namespace App\Livewire\Admin\Transaksi;

use App\Models\Kas;
use App\Models\Penjualan;
use App\Models\Tabungan;
use App\Models\Tagihan;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class TransaksiList extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function reversal($id)
    {
        $transaksi = Transaksi::with('transable')->findOrFail($id);

        if (str_starts_with($transaksi->keterangan, '[REVERSAL]')) {
            \Flux::toast('Transaksi ini sudah merupakan reversal.', variant: 'danger');

            return;
        }

        $revChecks = ['REV'.$transaksi->kode, 'REV-'.$transaksi->kode];
        if (str_starts_with((string) $transaksi->kode, 'ADM')) {
            $revChecks[] = 'MRV'.substr((string) $transaksi->kode, 3);
        } elseif (str_starts_with((string) $transaksi->kode, 'MTX')) {
            $revChecks[] = 'KRV'.substr((string) $transaksi->kode, 3);
        }

        $isReversed = Transaksi::whereIn('kode', $revChecks)->exists();
        if ($isReversed) {
            \Flux::toast('Transaksi ini sudah direversal sebelumnya.', variant: 'warning');

            return;
        }

        DB::transaction(function () use ($transaksi) {
            $transable = $transaksi->transable;
            $jumlah = $transaksi->jumlah;

            if ($transable instanceof Tabungan) {
                if (str_starts_with($transaksi->kode, 'STB') || stripos($transaksi->keterangan, 'Setoran') !== false) {
                    $transable->decrement('saldo', $jumlah);
                    if ($transable->kas) {
                        $transable->kas->decrement('saldo', $jumlah);
                    }
                } elseif (str_starts_with($transaksi->kode, 'TTB') || stripos($transaksi->keterangan, 'Penarikan') !== false) {
                    $transable->increment('saldo', $jumlah);
                    if ($transable->kas) {
                        $transable->kas->increment('saldo', $jumlah);
                    }
                }
            } elseif ($transable instanceof Tagihan) {
                $transable->decrement('bayar', $jumlah);
                if ($transable->kas) {
                    $transable->kas->decrement('saldo', $jumlah);
                }
            } elseif ($transable instanceof Penjualan) {
                $pembayaran = $transable->pembayaran;
                $kasPenjualan = Kas::where('penjualan', true)->first();

                if ($pembayaran === 'tun') {
                    if ($kasPenjualan) {
                        $kasPenjualan->decrement('saldo', $jumlah);
                    }
                } elseif ($pembayaran === 'tab') {
                    if ($kasPenjualan) {
                        $kasPenjualan->decrement('saldo', $jumlah);
                    }
                    // Cari tabungan siswa
                    $tabungan = Tabungan::where('siswa_id', $transable->siswa_id)->first();
                    if ($tabungan) {
                        $tabungan->increment('saldo', $jumlah);
                    }
                } elseif ($pembayaran === 'tag') {
                    $tagihan = Tagihan::where('tagihanable_type', Penjualan::class)
                        ->where('tagihanable_id', $transable->id)->first();
                    if ($tagihan) {
                        $tagihan->delete();
                    }
                }

                // Tandai penjualan sebagai batal
                $transable->update(['status' => 'batal']);

                // Kembalikan stok barang
                foreach ($transable->detail as $detail) {
                    if ($detail->barang) {
                        $detail->barang->increment('stok', $detail->jumlah);
                    }
                }
            }

            Transaksi::create([
                'kode' => 'REV'.$transaksi->kode,
                'jumlah' => -$jumlah,
                'keterangan' => '[REVERSAL] '.$transaksi->keterangan,
                'transable_type' => $transaksi->transable_type,
                'transable_id' => $transaksi->transable_id,
                'user_id' => auth()->id(),
            ]);
        });

        \Flux::toast('Reversal transaksi berhasil. Saldo telah dikembalikan.', variant: 'success');
    }

    public function render()
    {
        $transaksis = Transaksi::with(['petugas', 'transable'])
            ->when(! auth()->user()->isAdmin(), function ($q) {
                $lembagaId = auth()->user()->authable->lembaga_id ?? null;
                $userIds = User::whereHasMorph('authable', '*', function ($query) use ($lembagaId) {
                    $query->where('lembaga_id', $lembagaId);
                })->pluck('id');
                $q->whereIn('user_id', $userIds);
            })
            ->where(function ($query) {
                $query->where('kode', 'like', '%'.$this->search.'%')
                    ->orWhere('keterangan', 'like', '%'.$this->search.'%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Find which transaksis are already reversed
        $kodes = $transaksis->pluck('kode')->filter()->values()->all();
        $possibleRevKodes = [];
        foreach ($kodes as $kode) {
            $possibleRevKodes[] = 'REV-'.$kode;
            $possibleRevKodes[] = 'REV'.$kode;
            if (str_starts_with($kode, 'ADM')) {
                $possibleRevKodes[] = 'MRV'.substr($kode, 3);
            } elseif (str_starts_with($kode, 'MTX')) {
                $possibleRevKodes[] = 'KRV'.substr($kode, 3);
            }
        }

        $reversedKodes = empty($possibleRevKodes)
            ? []
            : Transaksi::whereIn('kode', $possibleRevKodes)->pluck('kode')->toArray();

        $reversedOriginalKodes = [];
        foreach ($kodes as $kode) {
            if (
                in_array('REV-'.$kode, $reversedKodes, true)
                || in_array('REV'.$kode, $reversedKodes, true)
                || (str_starts_with($kode, 'ADM') && in_array('MRV'.substr($kode, 3), $reversedKodes, true))
                || (str_starts_with($kode, 'MTX') && in_array('KRV'.substr($kode, 3), $reversedKodes, true))
            ) {
                $reversedOriginalKodes[] = $kode;
            }
        }

        $today = today();
        $stats = [
            'transaksiHariIni' => Transaksi::whereDate('created_at', $today)->count(),
            'masukHariIni' => (float) Transaksi::whereDate('created_at', $today)->where('jumlah', '>', 0)->sum('jumlah'),
            'keluarHariIni' => (float) abs(Transaksi::whereDate('created_at', $today)->where('jumlah', '<', 0)->sum('jumlah')),
            'totalSemua' => Transaksi::count(),
        ];

        return view('livewire.admin.transaksi.transaksi-list', [
            'transaksis' => $transaksis,
            'reversedOriginalKodes' => $reversedOriginalKodes,
            'stats' => $stats,
        ])->layout('components.admin-layout');
    }
}
