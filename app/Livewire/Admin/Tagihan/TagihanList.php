<?php

namespace App\Livewire\Admin\Tagihan;

use App\Models\Kas;
use App\Models\Tagihan;
use App\Services\WhatsappService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class TagihanList extends Component
{
    use WithPagination;

    public $search = '';

    public $filter_kas_id = '';

    public $filter_status = '';

    public $showPayModal = false;

    public $selectedTagihanId = null;

    public $nominalBayar = 0;

    public function mount()
    {
        Gate::authorize('viewAny', Tagihan::class);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterKasId()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function openPayModal($id)
    {
        $this->selectedTagihanId = $id;
        $tagihan = Tagihan::find($id);
        $this->nominalBayar = $tagihan ? ($tagihan->jumlah - $tagihan->bayar) : 0;
        $this->showPayModal = true;
    }

    public function getSelectedTagihanProperty()
    {
        return $this->selectedTagihanId ? Tagihan::with(['siswa', 'kas'])->find($this->selectedTagihanId) : null;
    }

    public function prosesPembayaran()
    {
        $tagihan = Tagihan::findOrFail($this->selectedTagihanId);
        $sisa = $tagihan->jumlah - $tagihan->bayar;

        // Force payment to be exact remaining balance
        $this->nominalBayar = $sisa;

        if ($this->nominalBayar <= 0) {
            $this->addError('nominalBayar', 'Tagihan ini sudah lunas.');
            return;
        }

        $tagihan->transaksi()->create([
            'kode' => $tagihan->kode,
            'transable_type' => 'App\Models\Tagihan',
            'transable_id' => $tagihan->id,
            'jumlah' => $this->nominalBayar,
            'keterangan' => 'Pembayaran tagihan '.$tagihan->kas->nama.' '.$tagihan->keterangan.' '.$tagihan->siswa->nama,
            'user_id' => auth()->id(),
        ]);

        $tagihan->bayar += $this->nominalBayar;
        $tagihan->save();

        if ($tagihan->kas) {
            $tagihan->kas->increment('saldo', $this->nominalBayar);
        }

        // kirim WA
        if (config('whatsapp.WHATSAPP_NOTIFICATION')) {
            if ($tagihan->siswa->telepon != '') {
                $pesan = WhatsappService::prosesPesan(
                    $tagihan->siswa,
                    [
                        'tagihan.keterangan' => 'Pembayaran tagihan '.$tagihan->kas->nama.' '.$tagihan->keterangan,
                        'tagihan.jumlah' => 'Rp '.number_format($this->nominalBayar, thousands_separator: '.'),
                    ],
                    'tagihan.bayar'
                );
                WhatsappService::kirimWa(
                    nama: $tagihan->siswa->nama,
                    nomor: $tagihan->siswa->telepon,
                    pesan: $pesan,
                    sessionId: WhatsappService::getSessionId($tagihan->siswa)
                );
            }
        }

        $url = route('admin.siswa.cetak-kwitansi', ['siswa' => $tagihan->siswa_id, 'ids' => $tagihan->id]);
        $this->js("window.open('{$url}', '_blank');");

        $this->showPayModal = false;
        $this->selectedTagihanId = null;
        session()->flash('message', 'Pembayaran tagihan berhasil dicatat.');
    }

    public function delete($id)
    {
        $tagihan = Tagihan::findOrFail($id);
        Gate::authorize('delete', $tagihan);
        $tagihan->delete();
        session()->flash('message', 'Tagihan berhasil dihapus.');
    }

    public function render()
    {
        $tagihans = Tagihan::with(['siswa', 'kas'])
            ->when(! auth()->user()->isAdmin(), function ($q) {
                $q->whereHas('siswa', function ($sub) {
                    $sub->where('lembaga_id', auth()->user()->authable->lembaga_id ?? null);
                });
            })
            ->when($this->filter_kas_id !== '', function ($q) {
                $q->where('kas_id', $this->filter_kas_id);
            })
            ->when($this->filter_status !== '', function ($q) {
                if ($this->filter_status === 'lunas') {
                    $q->whereColumn('bayar', '>=', 'jumlah');
                } elseif ($this->filter_status === 'belum') {
                    $q->where(function ($sub) {
                        $sub->whereNull('bayar')->orWhereColumn('bayar', '<', 'jumlah');
                    });
                }
            })
            ->where(function ($query) {
                $query->whereHas('siswa', function ($q) {
                    $q->where('nama', 'like', '%'.$this->search.'%')
                        ->orWhere('nis', 'like', '%'.$this->search.'%');
                })
                    ->orWhere('kode', 'like', '%'.$this->search.'%')
                    ->orWhere('keterangan', 'like', '%'.$this->search.'%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $kasList = Kas::where('ada_tagihan', true)
            ->when(! auth()->user()->isAdmin(), function ($q) {
                $q->where('lembaga_id', auth()->user()->authable->lembaga_id ?? null);
            })->get();

        return view('livewire.admin.tagihan.tagihan-list', [
            'tagihans' => $tagihans,
            'kasList' => $kasList,
        ])->layout('components.admin-layout');
    }
}
