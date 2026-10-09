<?php

namespace App\Livewire\Admin\Pengaduan;

use App\Models\Pengaduan;
use App\Models\Siswa;
use Livewire\Component;

class PengaduanForm extends Component
{
    public ?Pengaduan $pengaduan = null;

    public $siswa_id = '';

    public $laporan = '';

    public $status = '0';

    public $keterangan = '';

    protected function rules()
    {
        return [
            'siswa_id' => 'required|exists:siswa,id',
            'laporan' => 'required|string',
            'status' => 'required|in:0,1,2',
            'keterangan' => 'nullable|string',
        ];
    }

    public function mount(?Pengaduan $pengaduan = null)
    {
        if ($pengaduan && $pengaduan->exists) {
            $this->pengaduan = $pengaduan;
            $this->siswa_id = $pengaduan->siswa_id;
            $this->laporan = $pengaduan->laporan;
            $this->status = $pengaduan->status;
            $this->keterangan = $pengaduan->keterangan;
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'siswa_id' => $this->siswa_id,
            'laporan' => $this->laporan,
            'status' => $this->status,
            'keterangan' => $this->keterangan,
        ];

        if ($this->pengaduan && $this->pengaduan->exists) {
            $this->pengaduan->update($data);
            session()->flash('message', 'Pengaduan berhasil diperbarui.');
        } else {
            Pengaduan::create($data);
            session()->flash('message', 'Pengaduan berhasil ditambahkan.');
        }

        return $this->redirect(route('admin.pengaduan.index'), navigate: true);
    }

    public function render()
    {
        $siswa_list = Siswa::select('id', 'nama', 'nis')->orderBy('nama')->get();

        return view('livewire.admin.pengaduan.pengaduan-form', [
            'siswa_list' => $siswa_list,
        ])->layout('components.admin-layout');
    }
}
