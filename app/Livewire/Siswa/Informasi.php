<?php

namespace App\Livewire\Siswa;

use Livewire\Component;

class Informasi extends Component
{
    public $kategori = 'Semua';

    public function setKategori($kat)
    {
        $this->kategori = $kat;
    }

    public function render()
    {
        return view('livewire.siswa.informasi')
            ->layout('layouts.siswa', ['title' => 'Informasi']);
    }
}
