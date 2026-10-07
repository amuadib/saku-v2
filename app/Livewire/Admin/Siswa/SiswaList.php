<?php

namespace App\Livewire\Admin\Siswa;

use App\Models\Kelas;
use App\Models\Penjualan;
use App\Models\Siswa;
use App\Models\User;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class SiswaList extends Component
{
    use WithPagination;

    public $search = '';

    public $filter_lembaga = '';

    public $filter_kelas = '';

    public $filter_status = '';

    public $filter_label = '';

    public $export_fields = [
        'nis_nisn' => true,
        'nama' => true,
        'jenis_kelamin' => true,
        'tempat_tanggal_lahir' => false,
        'agama' => false,
        'alamat' => false,
        'telepon' => true,
        'orangtua' => false,
        'lembaga' => true,
        'kelas' => true,
        'status' => true,
        'label' => false,
    ];

    public $selected = [];

    public $selectAllPage = false;

    public $selectAll = false;

    public $showDeleteModal = false;

    public $deleteConfirmationCode = '';

    public $inputConfirmationCode = '';

    public $showSyncOutputModal = false;

    public $syncOutput = '';

    public $showMagicLinkModal = false;

    public $magicLinkUrl = '';

    public $magicLinkSiswaName = '';

    // Penjualan Modal Properties removed (extracted to SiswaPenjualanModal)

    public function mount()
    {
        Gate::authorize('viewAny', Siswa::class);
    }

    public function updating($property, $value)
    {
        if (in_array($property, ['search', 'filter_lembaga', 'filter_kelas', 'filter_status', 'filter_label'])) {
            $this->resetPage();
            $this->selected = [];
            $this->selectAllPage = false;
            $this->selectAll = false;
        }

        if ($property === 'filter_lembaga') {
            $this->filter_kelas = '';
        }
    }

    public function updatedSelectAllPage($value)
    {
        if ($value) {
            $this->selected = $this->getSiswaQuery()->paginate(10)->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
            $this->selectAll = false;
        }
    }

    public function updatedSelected()
    {
        $this->selectAllPage = false;
        $this->selectAll = false;
    }

    public function selectAllMatching()
    {
        $this->selectAll = true;
        $this->selected = $this->getSiswaQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
    }

    public function deselectAll()
    {
        $this->selectAll = false;
        $this->selectAllPage = false;
        $this->selected = [];
    }

    public function generateMagicLink($siswa_id)
    {
        $siswa = Siswa::find($siswa_id);
        if (! $siswa) {
            return;
        }

        $user = User::where('authable_type', Siswa::class)
            ->where('authable_id', $siswa->id)
            ->first();

        if (! $user) {
            \Flux::toast('User belum digenerate untuk siswa ini. Silakan generate user terlebih dahulu.', variant: 'danger');

            return;
        }

        $this->magicLinkSiswaName = $siswa->nama;
        $relativeUrl = URL::temporarySignedRoute(
            'login.magic', now()->addDays(30), ['user' => $user->id], false
        );
        $this->magicLinkUrl = request()->getSchemeAndHttpHost().$relativeUrl;

        $this->showMagicLinkModal = true;
    }

    // Penjualan Modal Methods removed (extracted to SiswaPenjualanModal)

    public function kirimTagihan()
    {
        if (empty($this->selected) && ! $this->selectAll) {
            return;
        }

        $query = $this->getSiswaQuery();

        if (! $this->selectAll) {
            $query->whereIn('id', $this->selected);
        }

        $records = $query->with('tagihan')->get();

        $pesan = [];
        foreach ($records as $s) {
            if (empty($s->telepon)) {
                continue;
            }
            $nomor = env('APP_ENV') == 'local' ? config('custom.whatsapp.test_number') : ''.$s->telepon;
            $rincian = '';
            $no = 1;
            $total = 0;

            foreach ($s->tagihan as $t) {
                if (! $t->isLunas()) {
                    $tgl = $t->updated_at == null ? $t->created_at->format('d-m-Y') : $t->updated_at->format('d-m-Y');
                    $rincian .= $no.'. '.ucfirst($t->keterangan).' ('.$tgl.'): Rp '.number_format($t->jumlah, thousands_separator: '.').PHP_EOL;
                    $total += $t->jumlah;
                    $no++;
                }
            }

            if ($total == 0) {
                continue;
            }

            $pesan[] = [
                'name' => $s->nama,
                'number' => $nomor,
                'message' => WhatsappService::prosesPesan(
                    siswa: $s,
                    data: [
                        'lembaga' => config('custom.lembaga.'.$s->lembaga_id),
                        'kontak.nama' => config('custom.kontak_lembaga.'.$s->lembaga_id.'.kontak'),
                        'tagihan.rincian' => $rincian,
                        'tagihan.total' => 'Rp '.number_format($total, thousands_separator: '.'),
                    ],
                    jenis: $s->status == 3 ? 'tagihan.daftar_alumni' : 'tagihan.daftar'
                ),
                'sessionId' => WhatsappService::getSessionId($s),
            ];
        }

        if (count($pesan) > 0) {
            $response = WhatsappService::kirimWa(
                kumpulan_pesan: $pesan
            );

            if ($response['status'] == 'success') {
                session()->flash('message', 'Data Tagihan siswa terpilih telah dikirimkan');
            } else {
                Log::error('Gagal mengirim pesan. Response: '.json_encode($response));
                session()->flash('error', 'Gagal mengirim pesan. '.$response['message']);
            }
        } else {
            session()->flash('message', 'Tagihan untuk siswa tidak ditemukan');
        }

        $this->deselectAll();
    }

    public function confirmBulkDelete()
    {
        if (empty($this->selected)) {
            return;
        }
        $this->deleteConfirmationCode = strtoupper(Str::random(6));
        $this->inputConfirmationCode = '';
        $this->showDeleteModal = true;
    }

    public function executeBulkDelete()
    {
        if (strtoupper($this->inputConfirmationCode) !== $this->deleteConfirmationCode) {
            $this->addError('inputConfirmationCode', 'Kode konfirmasi tidak cocok.');

            return;
        }

        $siswas = Siswa::whereIn('id', $this->selected)->get();
        foreach ($siswas as $siswa) {
            Gate::authorize('delete', $siswa);
            $siswa->delete();
        }

        $this->selected = [];
        $this->selectAll = false;
        $this->selectAllPage = false;
        $this->showDeleteModal = false;
        $this->deleteConfirmationCode = '';
        $this->inputConfirmationCode = '';

        session()->flash('message', 'Data siswa terpilih berhasil dihapus.');
    }

    public function generateUserSiswa()
    {
        $query = Siswa::where('status', 1);

        if (! auth()->user()->isAdmin()) {
            $query->where('lembaga_id', auth()->user()->lembaga_id);
        }

        $siswas = $query->get();
        $count = 0;

        foreach ($siswas as $siswa) {
            $userExists = User::where('authable_type', Siswa::class)
                ->where('authable_id', $siswa->id)
                ->exists();

            if (! $userExists) {
                if (! empty($siswa->nisn)) {
                    $username = $siswa->nisn;
                } elseif (! empty($siswa->nis)) {
                    $username = $siswa->nis;
                } else {
                    $namaHuruf = preg_replace('/[^a-zA-Z]/', '', $siswa->nama);
                    $nama5 = strtolower(substr($namaHuruf, 0, 5));
                    $username = $nama5.mt_rand(1000, 9999);
                }

                if (User::where('username', $username)->exists()) {
                    $username .= '_'.Str::random(4);
                }

                User::create([
                    'role_id' => 5,
                    'username' => $username,
                    'password' => Hash::make($username),
                    'authable_type' => Siswa::class,
                    'authable_id' => $siswa->id,
                ]);
                $count++;
            }
        }

        $this->deselectAll();

        if ($count > 0) {
            \Flux::toast("$count User Siswa berhasil dibuat.", variant: 'success');
        } else {
            \Flux::toast('Tidak ada user baru yang dibuat (semua siswa terpilih sudah memiliki user).', variant: 'warning');
        }
    }

    public function exportData()
    {
        if (empty($this->selected) && ! $this->selectAll) {
            \Flux::toast('Tidak ada data yang dipilih.', variant: 'danger');

            return;
        }

        $query = $this->getSiswaQuery();
        if (! $this->selectAll) {
            $query->whereIn('id', $this->selected);
        }

        $siswas = $query->with('kelas')->get();

        $filename = 'export_siswa_'.date('Ymd_His').'.csv';

        $callback = function () use ($siswas) {
            $file = fopen('php://output', 'w');

            // Add BOM for Excel UTF-8 compatibility
            fwrite($file, $bom = (chr(0xEF).chr(0xBB).chr(0xBF)));

            $headers = [];
            if ($this->export_fields['nis_nisn']) {
                $headers[] = 'NIS';
                $headers[] = 'NISN';
            }
            if ($this->export_fields['nama']) {
                $headers[] = 'Nama Siswa';
            }
            if ($this->export_fields['jenis_kelamin']) {
                $headers[] = 'Jenis Kelamin';
            }
            if ($this->export_fields['tempat_tanggal_lahir']) {
                $headers[] = 'Tempat Lahir';
                $headers[] = 'Tanggal Lahir';
            }
            if ($this->export_fields['agama']) {
                $headers[] = 'Agama';
            }
            if ($this->export_fields['alamat']) {
                $headers[] = 'Alamat';
            }
            if ($this->export_fields['telepon']) {
                $headers[] = 'Telepon/WhatsApp';
            }
            if ($this->export_fields['orangtua']) {
                $headers[] = 'Nama Ayah';
                $headers[] = 'Nama Ibu';
            }
            if ($this->export_fields['lembaga']) {
                $headers[] = 'Lembaga';
            }
            if ($this->export_fields['kelas']) {
                $headers[] = 'Kelas';
            }
            if ($this->export_fields['status']) {
                $headers[] = 'Status';
            }
            if ($this->export_fields['label']) {
                $headers[] = 'Label';
            }

            fputcsv($file, $headers);

            foreach ($siswas as $siswa) {
                $row = [];
                if ($this->export_fields['nis_nisn']) {
                    $row[] = $siswa->nis;
                    $row[] = $siswa->nisn;
                }
                if ($this->export_fields['nama']) {
                    $row[] = $siswa->nama;
                }
                if ($this->export_fields['jenis_kelamin']) {
                    $row[] = $siswa->jenis_kelamin === 'l' ? 'Laki-laki' : ($siswa->jenis_kelamin === 'p' ? 'Perempuan' : '');
                }
                if ($this->export_fields['tempat_tanggal_lahir']) {
                    $row[] = $siswa->tempat_lahir;
                    $row[] = $siswa->tanggal_lahir ? Carbon::parse($siswa->tanggal_lahir)->format('d/m/Y') : '';
                }
                if ($this->export_fields['agama']) {
                    $row[] = $siswa->agama;
                }
                if ($this->export_fields['alamat']) {
                    $row[] = $siswa->alamat;
                }
                if ($this->export_fields['telepon']) {
                    $row[] = $siswa->telepon;
                }
                if ($this->export_fields['orangtua']) {
                    $row[] = $siswa->nama_ayah;
                    $row[] = $siswa->nama_ibu;
                }
                if ($this->export_fields['lembaga']) {
                    $row[] = config('custom.lembaga.'.$siswa->lembaga_id);
                }
                if ($this->export_fields['kelas']) {
                    $row[] = $siswa->kelas ? $siswa->kelas->nama : '';
                }
                if ($this->export_fields['status']) {
                    $row[] = config('custom.siswa.status.'.$siswa->status);
                }
                if ($this->export_fields['label']) {
                    $row[] = $siswa->tags->pluck('name')->implode(', ');
                }

                fputcsv($file, $row);
            }
            fclose($file);
        };

        \Flux::modal('export-modal')->close();

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function syncSiswa()
    {
        try {
            Artisan::call('sync:siswa');
            $this->syncOutput = Artisan::output();
            $this->showSyncOutputModal = true;

            if (str_contains($this->syncOutput, 'Gagal') || str_contains($this->syncOutput, 'Terjadi kesalahan')) {
                \Flux::toast('Gagal melakukan sinkronisasi data siswa.', variant: 'danger');
            } else {
                \Flux::toast('Sinkronisasi data siswa berhasil dijalankan.', variant: 'success');
            }
        } catch (\Exception $e) {
            $this->syncOutput = "Terjadi kesalahan saat sinkronisasi:\n".$e->getMessage();
            $this->showSyncOutputModal = true;
            \Flux::toast('Terjadi kesalahan saat sinkronisasi: '.$e->getMessage(), variant: 'danger');
        }
    }

    public function render()
    {
        $kelas_items = collect();

        if (auth()->user()->isAdmin()) {
            if ($this->filter_lembaga) {
                $kelas_items = Kelas::whereHas('periode', fn ($q) => $q->where('aktif', 1))
                    ->where('lembaga_id', $this->filter_lembaga)
                    ->orderBy('nama')
                    ->get();
            }
        } else {
            $userLembagaId = auth()->user()->authable->lembaga_id ?? null;
            if ($userLembagaId) {
                $kelas_items = Kelas::whereHas('periode', fn ($q) => $q->where('aktif', 1))
                    ->where('lembaga_id', $userLembagaId)
                    ->orderBy('nama')
                    ->get();
            }
        }

        $siswa_items = $this->getSiswaQuery()->paginate(10);

        return view('livewire.admin.siswa.siswa-list', [
            'siswa_items' => $siswa_items,
            'kelas_items' => $kelas_items,
        ])->layout('components.admin-layout');
    }

    public function getSiswaQuery()
    {
        return Siswa::with('kelas', 'tags')
            ->when(! auth()->user()->isAdmin(), function ($q) {
                $q->where('lembaga_id', auth()->user()->authable->lembaga_id ?? null);
            })
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('nama', 'like', '%'.$this->search.'%')
                        ->orWhere('nis', 'like', '%'.$this->search.'%')
                        ->orWhere('nisn', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filter_lembaga, function ($q) {
                $q->where('lembaga_id', $this->filter_lembaga);
            })
            ->when($this->filter_kelas, function ($q) {
                $q->where('kelas_id', $this->filter_kelas);
            })
            ->when($this->filter_status, function ($q) {
                $q->where('status', $this->filter_status);
            })
            ->when($this->filter_label, function ($q) {
                $q->whereHas('tags', function ($sub) {
                    $sub->where('tags.id', $this->filter_label);
                });
            })
            ->orderBy('nama')
            ->orderBy('id');
    }
}
