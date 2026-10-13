<?php

namespace App\Livewire\Siswa;

use App\Models\Informasi as InformasiModel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

class Informasi extends Component
{
    #[Url]
    public $kategori = 'Semua';

    #[Url]
    public ?string $id = null;

    public ?string $selectedId = null;

    public function mount(): void
    {
        if ($this->id) {
            $this->selectedId = $this->id;
        }
    }

    public function setKategori(string $kat): void
    {
        $this->kategori = $kat;
    }

    public function selectInformasi(string $id): void
    {
        $this->selectedId = $id;
        $this->id = $id;
    }

    public function closeDetail(): void
    {
        $this->selectedId = null;
        $this->id = null;
    }

    #[Layout('layouts.siswa', ['title' => 'Info dan Berita'])]
    public function render()
    {
        $siswa = auth()->user()?->authable;
        $lembagaId = $siswa?->lembaga_id;

        $categories = InformasiModel::query()
            ->aktif()
            ->forLembaga($lembagaId)
            ->distinct()
            ->pluck('kategori')
            ->filter()
            ->values()
            ->toArray();

        $informasiItems = InformasiModel::query()
            ->aktif()
            ->forLembaga($lembagaId)
            ->when($this->kategori !== 'Semua', function ($q) {
                $q->where('kategori', $this->kategori);
            })
            ->orderBy('is_sticky', 'desc')
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $selectedInformasi = null;
        if ($this->selectedId) {
            $selectedInformasi = InformasiModel::find($this->selectedId);
        }

        return view('livewire.siswa.informasi', [
            'informasi_items' => $informasiItems,
            'categories' => $categories,
            'selectedInformasi' => $selectedInformasi,
            'siswa' => $siswa,
        ]);
    }
}
