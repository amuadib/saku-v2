<?php

namespace App\Livewire\Admin\Periode;

use App\Models\Periode;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class PeriodeForm extends Component
{
    public ?Periode $periode = null;

    public $nama = '';

    public $mulai = '';

    public $selesai = '';

    public $aktif = false;

    protected function rules()
    {
        return [
            'nama' => 'required|string|max:255',
            'mulai' => 'required|date',
            'selesai' => 'required|date|after_or_equal:mulai',
            'aktif' => 'boolean',
        ];
    }

    public function mount(?Periode $periode = null)
    {
        if ($periode && $periode->exists) {
            Gate::authorize('update', $periode);
            $this->periode = $periode;
            $this->nama = $periode->nama;
            $this->mulai = $periode->mulai;
            $this->selesai = $periode->selesai;
            $this->aktif = (bool) $periode->aktif;
        } else {
            Gate::authorize('create', Periode::class);
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'nama' => $this->nama,
            'mulai' => $this->mulai,
            'selesai' => $this->selesai,
            'aktif' => $this->aktif,
        ];

        if ($this->periode && $this->periode->exists) {
            $this->periode->update($data);
            session()->flash('message', 'Periode berhasil diperbarui.');
        } else {
            Periode::create($data);
            session()->flash('message', 'Periode berhasil ditambahkan.');
        }

        return $this->redirect(route('admin.periode.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.periode.periode-form')->layout('components.admin-layout');
    }
}
