<?php

namespace App\Livewire\Siswa;

use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProfilDetail extends Component
{
    use WithFileUploads;

    public ?Siswa $siswa = null;

    public bool $isEditing = false;

    // Form fields
    public string $nama = '';

    public ?string $nik = '';

    public ?string $nis = '';

    public ?string $nisn = '';

    public ?string $tempat_lahir = '';

    public ?string $tanggal_lahir = '';

    public string $jenis_kelamin = 'l';

    public ?string $alamat = '';

    public ?string $nama_ayah = '';

    public ?string $nama_ibu = '';

    public ?string $telepon = '';

    public ?string $email = '';

    public $foto = null;

    public ?string $existingFoto = null;

    public function mount(): void
    {
        $this->loadSiswa();
    }

    public function loadSiswa(): void
    {
        $this->siswa = Auth::user()?->authable;

        if (! $this->siswa) {
            return;
        }

        $this->nama = (string) ($this->siswa->nama ?? '');
        $this->nik = (string) ($this->siswa->nik ?? '');
        $this->nis = (string) ($this->siswa->nis ?? '');
        $this->nisn = (string) ($this->siswa->nisn ?? '');
        $this->tempat_lahir = (string) ($this->siswa->tempat_lahir ?? '');
        $this->tanggal_lahir = $this->siswa->tanggal_lahir ? Carbon::parse($this->siswa->tanggal_lahir)->format('Y-m-d') : '';
        $this->jenis_kelamin = in_array($this->siswa->jenis_kelamin, ['l', 'p'], true) ? $this->siswa->jenis_kelamin : 'l';
        $this->alamat = (string) ($this->siswa->alamat ?? '');
        $this->nama_ayah = (string) ($this->siswa->nama_ayah ?? '');
        $this->nama_ibu = (string) ($this->siswa->nama_ibu ?? '');
        $this->telepon = (string) ($this->siswa->telepon ?? '');
        $this->email = (string) ($this->siswa->email ?? '');
        $this->existingFoto = $this->siswa->foto;
    }

    protected function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'nik' => 'nullable|string|max:16',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|in:l,p',
            'alamat' => 'nullable|string|max:500',
            'nama_ayah' => 'nullable|string|max:255',
            'nama_ibu' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'foto' => 'nullable|image|max:2048',
        ];
    }

    protected function messages(): array
    {
        return [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'nik.max' => 'NIK maksimal 16 digit.',
            'jenis_kelamin.required' => 'Jenis kelamin harus dipilih.',
            'jenis_kelamin.in' => 'Pilihan jenis kelamin tidak valid.',
            'email.email' => 'Format alamat email tidak valid.',
            'foto.image' => 'File harus berupa gambar (JPG, PNG).',
            'foto.max' => 'Ukuran file foto maksimal 2MB.',
        ];
    }

    public function toggleEdit(): void
    {
        $this->isEditing = ! $this->isEditing;
        $this->resetErrorBag();

        if (! $this->isEditing) {
            $this->loadSiswa();
            $this->foto = null;
        }
    }

    public function cancelEdit(): void
    {
        $this->isEditing = false;
        $this->foto = null;
        $this->resetErrorBag();
        $this->loadSiswa();
    }

    public function save(): void
    {
        $this->validate();

        if (! $this->siswa) {
            session()->flash('error', 'Data siswa tidak ditemukan.');

            return;
        }

        $fotoPath = $this->existingFoto;

        if ($this->foto) {
            try {
                $manager = new ImageManager(new Driver);
                $image = $manager->decodePath($this->foto->getRealPath());
                $image->scaleDown(300, 400);

                $filename = 'siswa_foto/'.Str::random(40).'.jpg';
                $encoded = $image->encode(new JpegEncoder(quality: 80));
                Storage::disk('public')->put($filename, (string) $encoded);
                $fotoPath = $filename;

                if ($this->siswa->foto && Storage::disk('public')->exists($this->siswa->foto)) {
                    Storage::disk('public')->delete($this->siswa->foto);
                }
            } catch (\Throwable $e) {
                $fotoPath = $this->foto->store('siswa_foto', 'public');
            }
        }

        $this->siswa->update([
            'nama' => $this->nama,
            'nik' => $this->nik ?: null,
            'tempat_lahir' => $this->tempat_lahir ?: null,
            'tanggal_lahir' => $this->tanggal_lahir ?: null,
            'jenis_kelamin' => $this->jenis_kelamin,
            'alamat' => $this->alamat ?: null,
            'nama_ayah' => $this->nama_ayah ?: null,
            'nama_ibu' => $this->nama_ibu ?: null,
            'telepon' => $this->telepon ?: null,
            'email' => $this->email ?: null,
            'foto' => $fotoPath,
        ]);

        $this->existingFoto = $fotoPath;
        $this->foto = null;
        $this->isEditing = false;
        $this->siswa->refresh();

        session()->flash('message', 'Data profil berhasil diperbarui.');
    }

    #[Layout('layouts.siswa', ['title' => 'Profil Siswa'])]
    public function render()
    {
        return view('livewire.siswa.profil-detail', [
            'lembaga' => $this->siswa?->lembaga ?? config('custom.lembaga.'.$this->siswa?->lembaga_id, []),
        ]);
    }
}
