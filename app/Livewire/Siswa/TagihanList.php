<?php

namespace App\Livewire\Siswa;

use App\Models\Tagihan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class TagihanList extends Component
{
    use WithPagination;

    public $siswa;

    public $status = 'Semua';

    public function mount()
    {
        $this->siswa = Auth::user()->authable;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        $this->resetPage();
    }

    #[Layout('layouts.siswa', ['title' => 'Tagihan Siswa'])]
    public function render()
    {
        $query = Tagihan::with('kas')->where('siswa_id', $this->siswa->id);

        if ($this->status === 'Lunas') {
            $query->whereColumn('bayar', '>=', 'jumlah');
        } elseif ($this->status === 'Belum Lunas') {
            $query->where(function ($q) {
                $q->whereNull('bayar')->orWhereColumn('bayar', '<', 'jumlah');
            });
        }

        $tagihans = $query->latest()->paginate(15);

        return view('livewire.siswa.tagihan-list', [
            'tagihans' => $tagihans,
        ]);
    }
}
