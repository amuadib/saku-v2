<?php

namespace App\Livewire\Admin\Tabungan;

use App\Models\Kas;
use App\Models\Siswa;
use App\Models\Tabungan;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class TabunganForm extends Component
{
    public ?Tabungan $tabungan = null;

    public $siswa_id = '';

    public $kas_id = '';

    public $saldo = 0;

    protected function rules()
    {
        return [
            'siswa_id' => 'required|exists:siswa,id',
            'kas_id' => 'required|exists:kas,id',
            'saldo' => 'required|numeric|min:0',
        ];
    }

    public function mount(?Tabungan $tabungan = null)
    {
        if ($tabungan && $tabungan->exists) {
            Gate::authorize('update', $tabungan);
            $this->tabungan = $tabungan;
            $this->siswa_id = $tabungan->siswa_id;
            $this->kas_id = $tabungan->kas_id;
            $this->saldo = $tabungan->saldo;
        } else {
            Gate::authorize('create', Tabungan::class);
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'siswa_id' => $this->siswa_id,
            'kas_id' => $this->kas_id,
            'saldo' => $this->saldo,
        ];

        if ($this->tabungan && $this->tabungan->exists) {
            $this->tabungan->update($data);
            session()->flash('message', 'Data tabungan berhasil diperbarui.');
        } else {
            Tabungan::create($data);
            session()->flash('message', 'Data tabungan berhasil ditambahkan.');
        }

        return $this->redirect(route('admin.tabungan.index'), navigate: true);
    }

    public function render()
    {
        // For performance, we might just load active siswa
        $siswa_list = Siswa::select('id', 'nama', 'nis')->orderBy('nama')->get();
        // Load kas that are marked as tabungan
        $kas_list = Kas::where('tabungan', 1)->orderBy('nama')->get();

        return view('livewire.admin.tabungan.tabungan-form', [
            'siswa_list' => $siswa_list,
            'kas_list' => $kas_list,
        ])->layout('components.admin-layout');
    }
}
