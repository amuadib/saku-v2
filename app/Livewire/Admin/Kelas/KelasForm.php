<?php

namespace App\Livewire\Admin\Kelas;

use App\Models\Kelas;
use App\Models\Periode;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class KelasForm extends Component
{
    public ?Kelas $kelas = null;

    public $nama = '';

    public $tingkat = null;

    public $periode_id = '';

    protected function rules()
    {
        return [
            'nama' => 'required|string|max:255',
            'tingkat' => 'nullable|integer',
            'periode_id' => 'required|exists:periode,id',
        ];
    }

    public function mount(?Kelas $kelas = null)
    {
        if ($kelas && $kelas->exists) {
            Gate::authorize('update', $kelas);
            $this->kelas = $kelas;
            $this->nama = $kelas->nama;
            $this->tingkat = $kelas->tingkat;
            $this->periode_id = $kelas->periode_id;
        } else {
            Gate::authorize('create', Kelas::class);
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'nama' => $this->nama,
            'tingkat' => $this->tingkat,
            'periode_id' => $this->periode_id,
            'lembaga_id' => auth()->user()->authable->lembaga_id ?? 99,
        ];

        if ($this->kelas && $this->kelas->exists) {
            $this->kelas->update($data);
            session()->flash('message', 'Kelas berhasil diperbarui.');
        } else {
            Kelas::create($data);
            session()->flash('message', 'Kelas berhasil ditambahkan.');
        }

        return $this->redirect(route('admin.kelas.index'), navigate: true);
    }

    public function render()
    {
        $periodes = Periode::orderBy('nama', 'desc')->get();

        return view('livewire.admin.kelas.kelas-form', [
            'periodes' => $periodes,
        ])->layout('components.admin-layout');
    }
}
