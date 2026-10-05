<?php

namespace App\Livewire\Admin\Siswa;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;
use Livewire\Component;
use Livewire\WithFileUploads;

class SiswaForm extends Component
{
    use WithFileUploads;

    public ?Siswa $siswa = null;

    public $nis = '';

    public $nik = '';

    public $nisn = '';

    public $nama = '';

    public $tempat_lahir = '';

    public $tanggal_lahir = '';

    public $jenis_kelamin = 'l';

    public $alamat = '';

    public $nama_ayah = '';

    public $nama_ibu = '';

    public $telepon = '';

    public $email = '';

    public $kelas_id = '';

    public $status = 1;

    public $label = [];

    public $foto;

    protected function rules()
    {
        return [
            'nis' => 'required|string|max:50|unique:siswa,nis,'.$this->siswa?->id,
            'nik' => 'required|string|max:16|unique:siswa,nik,'.$this->siswa?->id,
            'nisn' => 'required|string|max:50|unique:siswa,nisn,'.$this->siswa?->id,
            'nama' => 'required|string|max:255',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|in:l,p',
            'alamat' => 'nullable|string',
            'nama_ayah' => 'nullable|string|max:255',
            'nama_ibu' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'kelas_id' => 'nullable|exists:kelas,id',
            'status' => 'required|integer',
            'foto' => 'nullable|image|max:1024',
        ];
    }

    public function mount(?Siswa $siswa = null)
    {
        if ($siswa && $siswa->exists) {
            Gate::authorize('update', $siswa);
            $this->siswa = $siswa;
            $this->nis = $siswa->nis;
            $this->nik = $siswa->nik;
            $this->nisn = $siswa->nisn;
            $this->nama = $siswa->nama;
            $this->tempat_lahir = $siswa->tempat_lahir;
            $this->tanggal_lahir = $siswa->tanggal_lahir;
            $this->jenis_kelamin = $siswa->jenis_kelamin;
            $this->alamat = $siswa->alamat;
            $this->nama_ayah = $siswa->nama_ayah;
            $this->nama_ibu = $siswa->nama_ibu;
            $this->telepon = $siswa->telepon;
            $this->email = $siswa->email;
            $this->kelas_id = $siswa->kelas_id;
            $this->status = $siswa->status;
            $this->label = $siswa->tags->pluck('id')->toArray();
        } else {
            Gate::authorize('create', Siswa::class);
        }
    }

    public function save()
    {
        $this->validate();

        $fotoPath = $this->siswa?->foto ?? null;
        if ($this->foto) {
            $manager = new ImageManager(new Driver);
            $image = $manager->decodePath($this->foto->getRealPath());
            $image->scaleDown(300, 400);

            $filename = 'siswa_foto/'.Str::random(40).'.jpg';
            $encoded = $image->encode(new JpegEncoder(quality: 80));
            Storage::disk('public')->put($filename, (string) $encoded);
            $fotoPath = $filename;

            if ($this->siswa && $this->siswa->foto && Storage::disk('public')->exists($this->siswa->foto)) {
                Storage::disk('public')->delete($this->siswa->foto);
            }
        }

        $data = [
            'nis' => $this->nis,
            'nik' => $this->nik,
            'nisn' => $this->nisn,
            'nama' => $this->nama,
            'tempat_lahir' => $this->tempat_lahir,
            'tanggal_lahir' => $this->tanggal_lahir,
            'jenis_kelamin' => $this->jenis_kelamin,
            'alamat' => $this->alamat,
            'nama_ayah' => $this->nama_ayah,
            'nama_ibu' => $this->nama_ibu,
            'telepon' => $this->telepon,
            'email' => $this->email,
            'kelas_id' => $this->kelas_id ?: null,
            'status' => $this->status,
            'lembaga_id' => auth()->user()->authable->lembaga_id ?? 99,
            'foto' => $fotoPath,
        ];

        if ($this->siswa && $this->siswa->exists) {
            $this->siswa->update($data);
            $this->siswa->tags()->sync($this->label);
            session()->flash('message', 'Data siswa berhasil diperbarui.');
        } else {
            $siswa = Siswa::create($data);
            $siswa->tags()->sync($this->label);
            session()->flash('message', 'Data siswa berhasil ditambahkan.');
        }

        return $this->redirect(route('admin.siswa.index'), navigate: true);
    }

    public function deleteFoto()
    {
        if ($this->siswa && $this->siswa->foto) {
            if (Storage::disk('public')->exists($this->siswa->foto)) {
                Storage::disk('public')->delete($this->siswa->foto);
            }
            $this->siswa->update(['foto' => null]);
            $this->siswa->foto = null;
        }

        $this->foto = null;
    }

    public function render()
    {
        $kelas = Kelas::orderBy('nama')->get();

        return view('livewire.admin.siswa.siswa-form', [
            'kelas' => $kelas,
        ])->layout('components.admin-layout');
    }
}
