<?php

namespace App\Livewire\Admin\Informasi;

use App\Models\Informasi;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;
use Livewire\Component;
use Livewire\WithFileUploads;

class InformasiForm extends Component
{
    use WithFileUploads;

    public ?Informasi $informasi = null;

    public string $judul = '';

    public string $kategori = 'Umum';

    public string $konten = '';

    public ?int $lembaga_id = null;

    public bool $is_aktif = true;

    public bool $is_sticky = false;

    public string $tanggal = '';

    public $gambar = null;

    public ?string $existingGambar = null;

    protected function rules(): array
    {
        return [
            'judul' => 'required|string|max:255',
            'kategori' => 'required|string|max:50',
            'konten' => 'required|string',
            'lembaga_id' => 'nullable|integer',
            'is_aktif' => 'boolean',
            'is_sticky' => 'boolean',
            'tanggal' => 'required|date',
            'gambar' => 'nullable|image|max:2048',
        ];
    }

    protected function messages(): array
    {
        return [
            'judul.required' => 'Judul informasi wajib diisi.',
            'kategori.required' => 'Kategori informasi wajib dipilih atau diisi.',
            'konten.required' => 'Konten atau isi informasi wajib diisi.',
            'tanggal.required' => 'Tanggal informasi wajib diisi.',
            'gambar.image' => 'File harus berupa gambar (JPG, PNG, WebP).',
            'gambar.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }

    public function mount(?Informasi $informasi = null): void
    {
        if ($informasi && $informasi->exists) {
            Gate::authorize('update', $informasi);
            $this->informasi = $informasi;
            $this->judul = $informasi->judul;
            $this->kategori = $informasi->kategori;
            $this->konten = $informasi->konten;
            $this->lembaga_id = $informasi->lembaga_id;
            $this->is_aktif = (bool) $informasi->is_aktif;
            $this->is_sticky = (bool) $informasi->is_sticky;
            $this->tanggal = $informasi->tanggal ? $informasi->tanggal->format('Y-m-d') : now()->format('Y-m-d');
            $this->existingGambar = $informasi->gambar;
        } else {
            Gate::authorize('create', Informasi::class);
            $this->tanggal = now()->format('Y-m-d');
            $userLembaga = auth()->user()->authable->lembaga_id ?? null;
            if (! auth()->user()->isAdmin() && $userLembaga) {
                $this->lembaga_id = (int) $userLembaga;
            }
        }
    }

    public function removeGambar(): void
    {
        if ($this->informasi && $this->informasi->gambar) {
            if (Storage::disk('public')->exists($this->informasi->gambar)) {
                Storage::disk('public')->delete($this->informasi->gambar);
            }
            $this->informasi->update(['gambar' => null]);
        }
        $this->existingGambar = null;
        $this->gambar = null;
    }

    public function save()
    {
        $this->validate();

        $gambarPath = $this->existingGambar;

        if ($this->gambar) {
            try {
                $manager = new ImageManager(new Driver);
                $image = $manager->decodePath($this->gambar->getRealPath());
                $image->scaleDown(800, 600);

                $filename = 'informasi_gambar/'.Str::random(40).'.jpg';
                $encoded = $image->encode(new JpegEncoder(quality: 80));
                Storage::disk('public')->put($filename, (string) $encoded);
                $gambarPath = $filename;

                if ($this->informasi && $this->informasi->gambar && Storage::disk('public')->exists($this->informasi->gambar)) {
                    Storage::disk('public')->delete($this->informasi->gambar);
                }
            } catch (\Throwable $e) {
                $gambarPath = $this->gambar->store('informasi_gambar', 'public');
            }
        }

        $data = [
            'judul' => $this->judul,
            'kategori' => $this->kategori,
            'konten' => $this->konten,
            'lembaga_id' => $this->lembaga_id ?: null,
            'is_aktif' => $this->is_aktif,
            'is_sticky' => $this->is_sticky,
            'tanggal' => $this->tanggal,
            'gambar' => $gambarPath,
            'user_id' => auth()->id(),
        ];

        if ($this->informasi && $this->informasi->exists) {
            $this->informasi->update($data);
            session()->flash('message', 'Informasi berhasil diperbarui.');
        } else {
            Informasi::create($data);
            session()->flash('message', 'Informasi baru berhasil ditambahkan.');
        }

        return $this->redirect(route('admin.informasi.index'), navigate: true);
    }

    public function render()
    {
        $lembagaList = config('custom.lembaga') ?? [];

        return view('livewire.admin.informasi.informasi-form', [
            'lembaga_list' => $lembagaList,
            'kategori_options' => [
                'Umum',
                'Akademik',
                'Santri',
                'Tagihan',
                'Kegiatan',
                'Libur',
                'Prestasi',
            ],
        ])->layout('components.admin-layout');
    }
}
