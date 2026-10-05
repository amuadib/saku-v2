<?php

namespace App\Livewire\Admin\Tagihan;

use App\Models\Kas;
use App\Models\Siswa;
use App\Models\Tagihan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;

class TagihanForm extends Component
{
    public ?Tagihan $tagihan = null;

    public $siswa_id = '';

    public $kas_id = '';

    public $jumlah = 0;

    public $bayar = 0;

    public $keterangan = '';

    public $kode = '';

    public $tanggal_kadaluarsa = '';

    protected function rules()
    {
        return [
            'siswa_id' => 'required|exists:siswa,id',
            'kas_id' => 'required|exists:kas,id',
            'jumlah' => 'required|numeric|min:0',
            'bayar' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'kode' => 'nullable|string|max:20',
            'tanggal_kadaluarsa' => 'nullable|date',
        ];
    }

    public function mount(?Tagihan $tagihan = null)
    {
        if ($tagihan && $tagihan->exists) {
            Gate::authorize('update', $tagihan);
            $this->tagihan = $tagihan;
            $this->siswa_id = $tagihan->siswa_id;
            $this->kas_id = $tagihan->kas_id;
            $this->jumlah = $tagihan->jumlah;
            $this->bayar = $tagihan->bayar ?? 0;
            $this->keterangan = $tagihan->keterangan;
            $this->kode = $tagihan->kode;
            $this->tanggal_kadaluarsa = $tagihan->tanggal_kadaluarsa;
        } else {
            Gate::authorize('create', Tagihan::class);
            // Generate default kode
            $this->kode = 'INV-'.strtoupper(Str::random(6));
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'siswa_id' => $this->siswa_id,
            'kas_id' => $this->kas_id,
            'jumlah' => $this->jumlah,
            'bayar' => $this->bayar,
            'keterangan' => $this->keterangan,
            'kode' => $this->kode,
            'tanggal_kadaluarsa' => $this->tanggal_kadaluarsa ?: null,
            'user_id' => auth()->id(), // the one creating/editing
        ];

        if ($this->tagihan && $this->tagihan->exists) {
            $this->tagihan->update($data);
            session()->flash('message', 'Tagihan berhasil diperbarui.');
        } else {
            Tagihan::create($data);
            session()->flash('message', 'Tagihan berhasil ditambahkan.');
        }

        return $this->redirect(route('admin.tagihan.index'), navigate: true);
    }

    public function render()
    {
        $siswa_list = Siswa::select('id', 'nama', 'nis')->orderBy('nama')->get();
        // Hanya kas yang di setting sebagai ada_tagihan = 1
        $kas_list = Kas::where('ada_tagihan', 1)->orderBy('nama')->get();

        return view('livewire.admin.tagihan.tagihan-form', [
            'siswa_list' => $siswa_list,
            'kas_list' => $kas_list,
        ])->layout('components.admin-layout');
    }
}
