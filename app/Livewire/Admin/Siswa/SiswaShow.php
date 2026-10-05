<?php

namespace App\Livewire\Admin\Siswa;

use App\Models\Kas;
use App\Models\Siswa;
use App\Models\Tabungan;
use App\Models\Tagihan;
use App\Services\WhatsappService;
use App\Traits\TagihanTrait;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class SiswaShow extends Component
{
    public Siswa $siswa;

    public $selectedTagihan = [];

    public $form_tagihan_kas_id = '';

    public $form_tagihan_jumlah = '';

    public $form_tagihan_keterangan = '';

    public $form_setor_kas_id = '';

    public $form_setor_jumlah = '';

    public $form_setor_keterangan = '';

    public $active_tabungan_id = null;

    public $active_tabungan_nama = '';

    public $form_tarik_jumlah = '';

    public $form_tarik_keterangan = '';

    public function selectAllTagihan()
    {
        $this->selectedTagihan = $this->siswa->tagihan->pluck('id')->map(fn ($id) => (string) $id)->toArray();
    }

    public function deselectAllTagihan()
    {
        $this->selectedTagihan = [];
    }

    public function mount(Siswa $siswa)
    {
        Gate::authorize('view', $siswa);
        $this->siswa = $siswa;
    }

    public function saveTagihan()
    {
        $this->validate([
            'form_tagihan_kas_id' => 'required|exists:kas,id',
            'form_tagihan_jumlah' => 'required|numeric|min:1',
            'form_tagihan_keterangan' => 'nullable|string|max:255',
        ]);

        $kode = TagihanTrait::getKodeTagihan('MTG');
        $prefix = substr($kode, 0, 11);
        $urut = intval(substr($kode, -4));
        Tagihan::create([
            'kode' => $prefix.str_pad($urut, 4, '0', STR_PAD_LEFT),
            'siswa_id' => $this->siswa->id,
            'kas_id' => $this->form_tagihan_kas_id,
            'jumlah' => $this->form_tagihan_jumlah,
            'keterangan' => $this->form_tagihan_keterangan,
            'user_id' => auth()->id(),
        ]);

        $this->reset(['form_tagihan_kas_id', 'form_tagihan_jumlah', 'form_tagihan_keterangan']);

        \Flux::modal('input-tagihan')->close();
        \Flux::toast('Tagihan berhasil ditambahkan.', variant: 'success');
    }

    public function deleteTagihanTerpilih()
    {
        $tagihans = $this->siswa->tagihan()->whereIn('id', $this->selectedTagihan)->get();
        foreach ($tagihans as $tagihan) {
            $tagihan->delete();
        }
        $this->selectedTagihan = [];
        \Flux::toast('Tagihan berhasil dihapus.', variant: 'success');
        \Flux::modal('confirm-delete-tagihan')->close();
    }

    public function setorTabungan()
    {
        $this->validate([
            'form_setor_kas_id' => 'required|exists:kas,id',
            'form_setor_jumlah' => 'required|numeric|min:1',
        ]);

        $tabungan = $this->siswa->tabungan()->firstOrCreate(
            ['kas_id' => $this->form_setor_kas_id]
        );

        $tabungan->increment('saldo', $this->form_setor_jumlah);

        $transaksi = $tabungan->transaksi()->create([
            'kode' => 'STB'.date('YmdHis'),
            'jumlah' => $this->form_setor_jumlah,
            'keterangan' => 'Setor Tabungan '.$tabungan->kas->nama.' '.$this->siswa->nama,
            'user_id' => auth()->id(),
        ]);

        $tabungan->kas->increment('saldo', $this->form_setor_jumlah);

        $this->reset(['form_setor_kas_id', 'form_setor_jumlah']);
        \Flux::modal('setor-tabungan')->close();
        \Flux::toast('Setor tabungan berhasil.', variant: 'success');
        $this->siswa->load('tabungan');

        $url = route('admin.siswa.cetak-transaksi', ['siswa' => $this->siswa->id, 'ids' => $transaksi->id]);
        $this->js("window.open('{$url}', '_blank');");
    }

    public function openSetorSpecific($id)
    {
        $tabungan = Tabungan::with('kas')->findOrFail($id);
        $this->active_tabungan_id = $tabungan->id;
        $this->active_tabungan_nama = $tabungan->kas->nama;
        $this->form_setor_jumlah = '';
        $this->form_setor_keterangan = '';

        \Flux::modal('setor-tabungan-specific')->show();
    }

    public function saveSetorSpecific()
    {
        $this->validate([
            'active_tabungan_id' => 'required|exists:tabungan,id',
            'form_setor_jumlah' => 'required|numeric|min:1',
        ]);

        $tabungan = Tabungan::findOrFail($this->active_tabungan_id);
        $tabungan->increment('saldo', $this->form_setor_jumlah);

        $transaksi = $tabungan->transaksi()->create([
            'kode' => 'STB'.date('YmdHis'),
            'jumlah' => $this->form_setor_jumlah,
            'keterangan' => 'Setoran '.$tabungan->kas->nama.' '.$this->siswa->nama.' '.$this->form_setor_keterangan,
            'user_id' => auth()->id(),
        ]);

        $tabungan->kas->increment('saldo', $this->form_setor_jumlah);

        $this->reset(['active_tabungan_id', 'active_tabungan_nama', 'form_setor_jumlah', 'form_setor_keterangan']);
        \Flux::modal('setor-tabungan-specific')->close();
        \Flux::toast('Setor tabungan berhasil.', variant: 'success');
        $this->siswa->load('tabungan');

        $url = route('admin.siswa.cetak-transaksi', ['siswa' => $this->siswa->id, 'ids' => $transaksi->id]);
        $this->js("window.open('{$url}', '_blank');");
    }

    public function openTarikSpecific($id)
    {
        $tabungan = Tabungan::with('kas')->findOrFail($id);
        $this->active_tabungan_id = $tabungan->id;
        $this->active_tabungan_nama = $tabungan->kas->nama;
        $this->form_tarik_jumlah = '';
        $this->form_tarik_keterangan = '';

        \Flux::modal('tarik-tabungan-specific')->show();
    }

    public function saveTarikSpecific()
    {
        $this->validate([
            'active_tabungan_id' => 'required|exists:tabungan,id',
            'form_tarik_jumlah' => 'required|numeric|min:1',
        ]);

        $tabungan = Tabungan::findOrFail($this->active_tabungan_id);

        if ($this->form_tarik_jumlah > $tabungan->saldo) {
            \Flux::toast('Saldo tidak mencukupi.', variant: 'danger');

            return;
        }

        $tabungan->decrement('saldo', $this->form_tarik_jumlah);

        $transaksi = $tabungan->transaksi()->create([
            'kode' => 'TTB'.date('YmdHis'),
            'jumlah' => $this->form_tarik_jumlah,
            'keterangan' => 'Penarikan '.$tabungan->kas->nama.' '.$this->siswa->nama.' '.$this->form_tarik_keterangan,
            'user_id' => auth()->id(),
        ]);

        $tabungan->kas->decrement('saldo', $this->form_tarik_jumlah);

        $this->reset(['active_tabungan_id', 'active_tabungan_nama', 'form_tarik_jumlah', 'form_tarik_keterangan']);
        \Flux::modal('tarik-tabungan-specific')->close();
        \Flux::toast('Tarik tabungan berhasil.', variant: 'success');
        $this->siswa->load('tabungan');

        $url = route('admin.siswa.cetak-transaksi', ['siswa' => $this->siswa->id, 'ids' => $transaksi->id]);
        $this->js("window.open('{$url}', '_blank');");
    }

    public function bayarTagihanTerpilih()
    {
        $tagihans = $this->siswa->tagihan()->whereIn('id', $this->selectedTagihan)->get();
        $total_tagihan = 0;
        $rincian = ''.PHP_EOL;
        $no = 1;
        foreach ($tagihans as $tagihan) {
            $sisaBayar = $tagihan->jumlah - ($tagihan->bayar ?? 0);
            $total_tagihan += $sisaBayar;
            $tagihan->transaksi()->create([
                'kode' => $tagihan->kode,
                'transable_type' => 'App\Models\Tagihan',
                'transable_id' => $tagihan->id,
                'jumlah' => $sisaBayar,
                'keterangan' => 'Pembayaran tagihan '.$tagihan->kas->nama.' '.$tagihan->keterangan.' '.$tagihan->siswa->nama,
                'user_id' => auth()->id(),
            ]);
            $rincian .= $no.'. '.$tagihan->kas->nama.' '.$tagihan->keterangan.' Rp '.number_format($sisaBayar, thousands_separator: '.').PHP_EOL;
            $no++;
            $tagihan->update(['bayar' => $tagihan->jumlah]);

            if ($tagihan->kas) {
                $tagihan->kas->increment('saldo', $sisaBayar);
            }
        }

        // kirim WA
        if (config('whatsapp.WHATSAPP_NOTIFICATION')) {
            if ($this->siswa->telepon != '') {
                $pesan = WhatsappService::prosesPesan(
                    $this->siswa,
                    [
                        'tagihan.rincian' => $rincian,
                        'tagihan.total' => 'Rp '.number_format($total_tagihan, thousands_separator: '.'),
                    ],
                    'tagihan.bayar_banyak'
                );
                WhatsappService::kirimWa(
                    nama: $this->siswa->nama,
                    nomor: $this->siswa->telepon,
                    pesan: $pesan,
                    sessionId: WhatsappService::getSessionId($this->siswa)
                );
            }
        }

        $ids = $tagihans->pluck('id')->implode(',');
        $url = route('admin.siswa.cetak-kwitansi', ['siswa' => $this->siswa->id, 'ids' => $ids]);
        $this->js("window.open('{$url}', '_blank');");

        $this->selectedTagihan = [];
        \Flux::modal('confirm-pay-selected')->close();
        \Flux::toast(count($tagihans).' Tagihan berhasil dilunasi.', variant: 'success');
    }

    public function payAllTagihan()
    {
        $tagihans = $this->siswa->tagihan()->where(function ($q) {
            $q->whereNull('bayar')->orWhereColumn('bayar', '<', 'jumlah');
        })->get();

        foreach ($tagihans as $tagihan) {
            $sisaBayar = $tagihan->jumlah - ($tagihan->bayar ?? 0);

            $tagihan->transaksi()->create([
                'kode' => $tagihan->kode,
                'transable_type' => 'App\Models\Tagihan',
                'transable_id' => $tagihan->id,
                'jumlah' => $sisaBayar,
                'keterangan' => 'Pembayaran tagihan '.$tagihan->kas->nama.' '.$tagihan->keterangan.' '.$tagihan->siswa->nama,
                'user_id' => auth()->id(),
            ]);

            $tagihan->update(['bayar' => $tagihan->jumlah]);
            if ($tagihan->kas) {
                $tagihan->kas->increment('saldo', $sisaBayar);
            }
        }

        $ids = $tagihans->pluck('id')->implode(',');
        $url = route('admin.siswa.cetak-kwitansi', ['siswa' => $this->siswa->id, 'ids' => $ids]);
        $this->js("window.open('{$url}', '_blank');");

        $this->selectedTagihan = [];
        \Flux::modal('confirm-pay-all')->close();
        \Flux::toast('Semua tagihan berhasil dilunasi.', variant: 'success');
    }

    public function render()
    {
        $this->siswa->load([
            'kelas',
            'tagihan' => function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('bayar')->orWhereColumn('bayar', '<', 'jumlah');
                })->orderBy('created_at', 'desc')->with('kas');
            },
            'tabungan.kas',
        ]);

        $daftarKasTagihan = Kas::where('lembaga_id', $this->siswa->lembaga_id)
            ->where('ada_tagihan', true)
            ->get();

        return view('livewire.admin.siswa.siswa-show', [
            'daftarKasTagihan' => $daftarKasTagihan,
        ])
            ->layout('components.admin-layout', ['header' => 'Detail Siswa']);
    }
}
