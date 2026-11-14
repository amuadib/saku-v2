<?php

namespace App\Livewire\Admin\Tagihan;

use App\Models\Kas;
use App\Models\Kelas;
use App\Models\Periode;
use App\Models\Siswa;
use App\Models\Tagihan;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;

class TagihanForm extends Component
{
    public ?Tagihan $tagihan = null;

    public string $lembaga_id = '';

    public string $peserta_tipe = 'semua'; // 'semua', 'kelas', 'siswa'

    public string $kelas_id = '';

    public string $siswa_id = '';

    public string $kas_id = '';

    public string $jumlah = '0';

    public string $bayar = '0';

    public string $keterangan = '';

    public string $kode = '';

    public ?string $tanggal_kadaluarsa = null;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $rules = [
            'lembaga_id' => [
                'required',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! $this->tagihan || ! $this->tagihan->exists) {
                        if ($this->peserta_tipe === 'semua') {
                            $hasSiswa = Siswa::where('lembaga_id', $value)->where('status', 1)->exists();
                            if (! $hasSiswa) {
                                $fail('Tidak ada siswa aktif pada lembaga terpilih.');
                            }
                        }
                    }
                },
            ],
            'kas_id' => [
                'required',
                'exists:kas,id',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($this->lembaga_id) {
                        $kas = Kas::whereKey($value)
                            ->where('ada_tagihan', 1)
                            ->where('lembaga_id', $this->lembaga_id)
                            ->first();

                        if (! $kas) {
                            $fail('Kas tagihan harus sesuai dengan lembaga yang dipilih.');
                        }
                    }
                },
            ],
            'jumlah' => [
                'required',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $raw = trim((string) $value);
                    if ($raw === '') {
                        $fail('Jumlah tagihan wajib diisi.');

                        return;
                    }

                    if (str_starts_with($raw, '-')) {
                        $fail('Jumlah tagihan tidak boleh negatif.');

                        return;
                    }

                    $cleaned = preg_replace('/[^0-9]/', '', $raw);
                    if ($cleaned === '' || ! is_numeric($cleaned)) {
                        $fail('Jumlah tagihan harus berupa nominal angka valid.');

                        return;
                    }

                    if ((int) $cleaned < 0) {
                        $fail('Jumlah tagihan minimal 0.');
                    }
                },
            ],
            'keterangan' => 'nullable|string',
            'kode' => 'nullable|string|max:20',
            'tanggal_kadaluarsa' => 'nullable|date',
        ];

        if (! $this->tagihan || ! $this->tagihan->exists) {
            $rules['peserta_tipe'] = 'required|in:semua,kelas,siswa';

            if ($this->peserta_tipe === 'kelas') {
                $rules['kelas_id'] = [
                    'required',
                    'exists:kelas,id',
                    function (string $attribute, mixed $value, Closure $fail): void {
                        if ($this->lembaga_id) {
                            $kelas = Kelas::whereKey($value)->where('lembaga_id', $this->lembaga_id)->first();
                            if (! $kelas) {
                                $fail('Kelas harus sesuai dengan lembaga yang dipilih.');

                                return;
                            }

                            $hasSiswa = Siswa::where('kelas_id', $value)->where('status', 1)->exists();
                            if (! $hasSiswa) {
                                $fail('Tidak ada siswa aktif di kelas terpilih.');
                            }
                        }
                    },
                ];
            } elseif ($this->peserta_tipe === 'siswa') {
                $rules['siswa_id'] = [
                    'required',
                    'exists:siswa,id',
                    function (string $attribute, mixed $value, Closure $fail): void {
                        if ($this->lembaga_id) {
                            $siswa = Siswa::whereKey($value)->where('lembaga_id', $this->lembaga_id)->first();
                            if (! $siswa) {
                                $fail('Siswa harus sesuai dengan lembaga yang dipilih.');
                            }
                        }
                    },
                ];
                $rules['bayar'] = [
                    'nullable',
                    function (string $attribute, mixed $value, Closure $fail): void {
                        $raw = trim((string) $value);
                        if ($raw === '') {
                            return;
                        }
                        if (str_starts_with($raw, '-')) {
                            $fail('Jumlah dibayar tidak boleh negatif.');

                            return;
                        }
                        $cleaned = preg_replace('/[^0-9]/', '', $raw);
                        if ($cleaned === '' || ! is_numeric($cleaned)) {
                            $fail('Jumlah dibayar harus berupa nominal angka valid.');
                        }
                    },
                ];
            }
        } else {
            $rules['siswa_id'] = 'required|exists:siswa,id';
            $rules['bayar'] = 'nullable|numeric|min:0';
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'lembaga_id.required' => 'Lembaga wajib dipilih.',
            'peserta_tipe.required' => 'Opsi peserta tagihan wajib dipilih.',
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'kelas_id.exists' => 'Kelas tidak valid.',
            'siswa_id.required' => 'Siswa wajib dipilih.',
            'siswa_id.exists' => 'Siswa tidak valid.',
            'kas_id.required' => 'Jenis tagihan (kas) wajib dipilih.',
            'kas_id.exists' => 'Jenis tagihan (kas) tidak valid.',
            'jumlah.required' => 'Jumlah tagihan wajib diisi.',
            'kode.max' => 'Kode tagihan maksimal 20 karakter.',
        ];
    }

    public function mount(?Tagihan $tagihan = null): void
    {
        $user = auth()->user();
        $userLembagaId = $user && ! $user->isAdmin() ? ($user->authable->lembaga_id ?? null) : null;

        if ($tagihan && $tagihan->exists) {
            Gate::authorize('update', $tagihan);
            $this->tagihan = $tagihan->loadMissing(['siswa', 'kas']);
            $this->lembaga_id = (string) ($tagihan->siswa->lembaga_id ?? $userLembagaId ?? '');
            $this->peserta_tipe = 'siswa';
            $this->siswa_id = (string) $tagihan->siswa_id;
            $this->kelas_id = (string) ($tagihan->siswa->kelas_id ?? '');
            $this->kas_id = (string) $tagihan->kas_id;
            $this->jumlah = number_format((float) $tagihan->jumlah, 0, ',', '.');
            $this->bayar = number_format((float) ($tagihan->bayar ?? 0), 0, ',', '.');
            $this->keterangan = (string) ($tagihan->keterangan ?? '');
            $this->kode = (string) ($tagihan->kode ?? '');
            $this->tanggal_kadaluarsa = $tagihan->tanggal_kadaluarsa;
        } else {
            Gate::authorize('create', Tagihan::class);
            $this->kode = 'INV-'.strtoupper(Str::random(6));

            if ($userLembagaId) {
                $this->lembaga_id = (string) $userLembagaId;
            } else {
                $lembagas = config('custom.lembaga') ?? [];
                $firstKey = array_key_first($lembagas);
                $this->lembaga_id = $firstKey ? (string) $firstKey : '';
            }

            if ($this->lembaga_id) {
                $this->autoSelectKas();
            }
        }
    }

    public function updatedLembagaId(mixed $value): void
    {
        $this->resetErrorBag();
        $this->kas_id = '';
        $this->kelas_id = '';
        $this->siswa_id = '';

        if ($value) {
            $this->autoSelectKas();
        }
    }

    public function updatedPesertaTipe(mixed $value): void
    {
        $this->resetErrorBag(['peserta_tipe', 'kelas_id', 'siswa_id']);
        if ($value !== 'kelas') {
            $this->kelas_id = '';
        }
        if ($value !== 'siswa') {
            $this->siswa_id = '';
        }
    }

    public function updatedKelasId(mixed $value): void
    {
        $this->resetErrorBag(['kelas_id']);
    }

    public function updatedSiswaId(mixed $value): void
    {
        $this->resetErrorBag(['siswa_id']);
    }

    private function autoSelectKas(): void
    {
        if (! $this->lembaga_id) {
            return;
        }

        $availableKas = Kas::where('ada_tagihan', 1)
            ->where('lembaga_id', $this->lembaga_id)
            ->orderBy('nama')
            ->get();

        if ($availableKas->count() === 1) {
            $this->kas_id = (string) $availableKas->first()->id;
        }
    }

    public function save(): mixed
    {
        $this->validate();

        $cleanJumlah = (int) preg_replace('/[^0-9]/', '', (string) $this->jumlah);
        $cleanBayar = (int) preg_replace('/[^0-9]/', '', (string) $this->bayar);

        if ($this->tagihan && $this->tagihan->exists) {
            $this->tagihan->update([
                'siswa_id' => $this->siswa_id,
                'kas_id' => $this->kas_id,
                'jumlah' => $cleanJumlah,
                'bayar' => $cleanBayar,
                'keterangan' => $this->keterangan,
                'kode' => $this->kode,
                'tanggal_kadaluarsa' => $this->tanggal_kadaluarsa ?: null,
            ]);
            session()->flash('message', 'Tagihan berhasil diperbarui.');

            return $this->redirect(route('admin.tagihan.index'), navigate: true);
        }

        DB::transaction(function () use ($cleanJumlah, $cleanBayar): void {
            if ($this->peserta_tipe === 'semua') {
                $targetSiswaIds = Siswa::where('lembaga_id', $this->lembaga_id)
                    ->where('status', 1)
                    ->pluck('id');
            } elseif ($this->peserta_tipe === 'kelas') {
                $targetSiswaIds = Siswa::where('kelas_id', $this->kelas_id)
                    ->where('status', 1)
                    ->pluck('id');
            } else {
                $targetSiswaIds = collect([$this->siswa_id]);
            }

            if ($targetSiswaIds->isEmpty()) {
                throw new \RuntimeException('Tidak ada siswa aktif yang ditemukan untuk dibuatkan tagihan.');
            }

            $count = $targetSiswaIds->count();
            $todayCount = Tagihan::whereDate('created_at', today())->count();

            foreach ($targetSiswaIds as $index => $sId) {
                if ($count === 1) {
                    $kode = $this->kode ?: ('MTG'.date('Ymd').str_pad((string) ($todayCount + 1), 4, '0', STR_PAD_LEFT));
                } else {
                    $urut = $todayCount + $index + 1;
                    $kode = 'MTG'.date('Ymd').str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
                }

                Tagihan::create([
                    'siswa_id' => $sId,
                    'kas_id' => $this->kas_id,
                    'jumlah' => $cleanJumlah,
                    'bayar' => $count === 1 ? $cleanBayar : 0,
                    'keterangan' => $this->keterangan,
                    'kode' => $kode,
                    'tanggal_kadaluarsa' => $this->tanggal_kadaluarsa ?: null,
                    'user_id' => auth()->id(),
                ]);
            }

            if ($count > 1) {
                session()->flash('message', "Berhasil membuat tagihan untuk {$count} siswa.");
            } else {
                session()->flash('message', 'Tagihan berhasil ditambahkan.');
            }
        });

        return $this->redirect(route('admin.tagihan.index'), navigate: true);
    }

    public function render(): View
    {
        $lembagas = config('custom.lembaga') ?? [];

        $kasList = collect();
        $kelasList = collect();
        $siswaList = collect();
        $semuaSiswaCount = 0;
        $kelasSiswaCount = 0;
        $selectedSiswa = null;

        if ($this->lembaga_id) {
            $kasList = Kas::where('ada_tagihan', 1)
                ->where('lembaga_id', $this->lembaga_id)
                ->orderBy('nama')
                ->get();

            $kelasList = Kelas::where('lembaga_id', $this->lembaga_id)
                ->when(Periode::where('aktif', 1)->exists(), function ($q): void {
                    $q->whereHas('periode', fn ($p) => $p->where('aktif', 1));
                })
                ->orderBy('nama')
                ->get();

            $siswaList = Siswa::select('id', 'nama', 'nis', 'lembaga_id', 'kelas_id')
                ->with('kelas:id,nama')
                ->where('lembaga_id', $this->lembaga_id)
                ->where('status', 1)
                ->orderBy('nama')
                ->get();

            $semuaSiswaCount = $siswaList->count();

            if ($this->kelas_id) {
                $kelasSiswaCount = $siswaList->where('kelas_id', $this->kelas_id)->count();
            }

            if ($this->siswa_id) {
                $selectedSiswa = $siswaList->firstWhere('id', $this->siswa_id)
                    ?? Siswa::with('kelas:id,nama')->whereKey($this->siswa_id)->first();
            }
        }

        $siswaOptions = $siswaList->map(function (Siswa $s): array {
            $kelasNama = $s->kelas instanceof Kelas ? $s->kelas->nama : '';
            $lembagaConfig = config("custom.lembaga.{$s->lembaga_id}");
            $lembagaNama = is_array($lembagaConfig)
                ? ($lembagaConfig['singkatan'] ?? ($lembagaConfig['nama'] ?? ''))
                : (string) ($lembagaConfig ?? '');

            return [
                'id' => (string) $s->id,
                'nama' => (string) $s->nama,
                'nis' => (string) ($s->nis ?? '-'),
                'kelas' => (string) $kelasNama,
                'lembaga' => (string) $lembagaNama,
                'lembaga_id' => $s->lembaga_id,
            ];
        })->values()->all();

        return view('livewire.admin.tagihan.tagihan-form', [
            'lembagas' => $lembagas,
            'kas_list' => $kasList,
            'kelas_list' => $kelasList,
            'siswa_list' => $siswaList,
            'siswaOptions' => $siswaOptions,
            'semuaSiswaCount' => $semuaSiswaCount,
            'kelasSiswaCount' => $kelasSiswaCount,
            'selectedSiswa' => $selectedSiswa,
        ])->layout('components.admin-layout');
    }
}
