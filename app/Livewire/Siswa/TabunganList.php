<?php

namespace App\Livewire\Siswa;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TabunganList extends Component
{
    public $siswa;

    public function mount()
    {
        $this->siswa = Auth::user()->authable;
    }

    #[Layout('layouts.siswa', ['title' => 'Tabungan Siswa'])]
    public function render()
    {
        $tabungans = $this->siswa->tabungan()->with('kas')->get();

        return view('livewire.siswa.tabungan-list', [
            'tabungans' => $tabungans,
        ]);
    }
}
