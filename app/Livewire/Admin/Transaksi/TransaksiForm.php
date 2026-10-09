<?php

namespace App\Livewire\Admin\Transaksi;

use App\Models\Kas;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TransaksiForm extends Component
{
    public $kas_id;

    public $jumlah;

    public $keterangan;

    public $lembaga_id = null;

    public $jenis = 'tun';

    public $mutasi = 'masuk';

    public $kas_id_asal;

    public $kas_id_tujuan;

    public function updatedLembagaId()
    {
        $this->kas_id = null;
        $this->kas_id_asal = null;
        $this->kas_id_tujuan = null;
    }

    public function updatedJenis()
    {
        $this->resetValidation();
    }

    public function save()
    {
        $rules = [
            'jumlah' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string',
        ];

        if ($this->jenis === 'tun') {
            $rules['kas_id'] = 'required';
        } else {
            $rules['kas_id_asal'] = 'required|different:kas_id_tujuan';
            $rules['kas_id_tujuan'] = 'required';
        }

        $messages = [
            'kas_id_asal.different' => 'Kas asal dan Kas tujuan tidak boleh sama.',
        ];

        $this->validate($rules, $messages);

        $todayCount = Transaksi::whereDate('created_at', today())->count();
        $baseKode = date('Ymd').str_pad($todayCount + 1, 4, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($baseKode) {
            // $lembagaId = null;
            // if (auth()->check() && auth()->user()->isAdmin()) {
            //     $lembagaId = $this->lembaga_id;
            // } elseif (auth()->check() && auth()->user()->authable) {
            //     $lembagaId = auth()->user()->authable->lembaga_id ?? null;
            // }

            if ($this->jenis === 'tun') {
                if ($this->mutasi == 'keluar') {
                    $kode = 'KTX'.$baseKode;
                } else {
                    $kode = 'MTX'.$baseKode;
                }
                $transaksi = new Transaksi;
                $transaksi->transable_type = Kas::class;
                $transaksi->transable_id = $this->kas_id;
                $transaksi->jumlah = $this->mutasi === 'keluar' ? -abs($this->jumlah) : abs($this->jumlah);
                $transaksi->keterangan = $this->keterangan;
                $transaksi->user_id = auth()->id();
                $transaksi->kode = $kode;
                $transaksi->save();

                $kas = Kas::find($this->kas_id);
                if ($kas) {
                    $kas->increment('saldo', $transaksi->jumlah);
                }

            } else {
                // Transfer
                // Kas Asal (Keluar)
                $trxAsal = new Transaksi;
                $trxAsal->transable_type = Kas::class;
                $trxAsal->transable_id = $this->kas_id_asal;
                $trxAsal->jumlah = -abs($this->jumlah);
                $trxAsal->keterangan = $this->keterangan ?? 'Transfer ke Kas Tujuan';
                $trxAsal->user_id = auth()->id();
                $trxAsal->kode = 'KTX'.$baseKode;
                $trxAsal->save();

                $kasAsal = Kas::find($this->kas_id_asal);
                if ($kasAsal) {
                    $kasAsal->decrement('saldo', abs($this->jumlah));
                }

                // Kas Tujuan (Masuk)
                $trxTujuan = new Transaksi;
                $trxTujuan->transable_type = Kas::class;
                $trxTujuan->transable_id = $this->kas_id_tujuan;
                $trxTujuan->jumlah = abs($this->jumlah);
                $trxTujuan->keterangan = $this->keterangan ?? 'Terima transfer dari Kas Asal';
                $trxTujuan->user_id = auth()->id();
                $trxTujuan->kode = 'MTX'.$baseKode;
                $trxTujuan->save();

                $kasTujuan = Kas::find($this->kas_id_tujuan);
                if ($kasTujuan) {
                    $kasTujuan->increment('saldo', abs($this->jumlah));
                }
            }
        });

        session()->flash('message', 'Transaksi manual berhasil ditambahkan.');

        return $this->redirectRoute('admin.transaksi.index', navigate: true);
    }

    public function render()
    {
        $query = Kas::query();
        $lembagas = [];

        if (auth()->check() && auth()->user()->isAdmin()) {
            $lembagas = config('custom.lembaga') ?? [];
            if ($this->lembaga_id) {
                $query->where('lembaga_id', $this->lembaga_id);
            }
        } elseif (auth()->check() && ! auth()->user()->isAdmin()) {
            $lembagaId = auth()->user()->authable->lembaga_id ?? null;
            $query->where('lembaga_id', $lembagaId);
        }

        $kas = $query
            ->orderBy('nama')
            ->get();

        return view('livewire.admin.transaksi.transaksi-form', [
            'kas_items' => $kas,
            'lembagas' => $lembagas,
        ])->layout('components.admin-layout', ['header' => 'Tambah Transaksi Manual']);
    }
}
