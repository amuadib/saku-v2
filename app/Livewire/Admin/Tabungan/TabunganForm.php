<?php

namespace App\Livewire\Admin\Tabungan;

use App\Models\Kas;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Tabungan;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class TabunganForm extends Component
{
    public ?Tabungan $tabungan = null;

    public string $siswa_id = '';

    public string $kas_id = '';

    public string $saldo = '0';

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'siswa_id' => 'required|exists:siswa,id',
            'kas_id' => [
                'required',
                'exists:kas,id',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! $this->siswa_id) {
                        $fail('Pilih siswa terlebih dahulu.');

                        return;
                    }

                    $siswa = Siswa::whereKey($this->siswa_id)->first();
                    if (! $siswa) {
                        $fail('Siswa tidak ditemukan.');

                        return;
                    }

                    $kas = Kas::whereKey($value)
                        ->where('tabungan', true)
                        ->where('lembaga_id', $siswa->lembaga_id)
                        ->first();

                    if (! $kas) {
                        $fail('Kas tabungan harus sesuai dengan lembaga siswa terpilih.');

                        return;
                    }
                },
            ],
            'saldo' => [
                'required',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $raw = trim((string) $value);
                    if ($raw === '') {
                        $fail('Saldo awal wajib diisi.');

                        return;
                    }

                    if (str_starts_with($raw, '-')) {
                        $fail('Saldo tidak boleh negatif.');

                        return;
                    }

                    $cleaned = preg_replace('/[^0-9]/', '', $raw);
                    if ($cleaned === '' || ! is_numeric($cleaned)) {
                        $fail('Saldo harus berupa nominal angka valid.');
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'siswa_id.required' => 'Siswa wajib dipilih.',
            'siswa_id.exists' => 'Siswa yang dipilih tidak valid.',
            'kas_id.required' => 'Kas tabungan wajib dipilih.',
            'kas_id.exists' => 'Kas tabungan tidak valid.',
            'saldo.required' => 'Saldo awal wajib diisi.',
        ];
    }

    public function mount(?Tabungan $tabungan = null): void
    {
        if ($tabungan && $tabungan->exists) {
            Gate::authorize('update', $tabungan);
            $this->tabungan = $tabungan;
            $this->siswa_id = (string) $tabungan->siswa_id;
            $this->kas_id = (string) $tabungan->kas_id;
            $this->saldo = number_format((float) $tabungan->saldo, 0, ',', '.');
        } else {
            Gate::authorize('create', Tabungan::class);
            $this->saldo = '0';
        }
    }

    public function updatedSiswaId(mixed $value): void
    {
        $this->resetErrorBag(['siswa_id', 'kas_id']);

        if (! $value) {
            $this->kas_id = '';

            return;
        }

        $siswa = Siswa::whereKey($value)->first();
        if (! $siswa) {
            $this->kas_id = '';

            return;
        }

        $availableKas = Kas::where('tabungan', true)
            ->where('lembaga_id', $siswa->lembaga_id)
            ->orderBy('nama')
            ->get();

        if ($this->kas_id && ! $availableKas->contains('id', $this->kas_id)) {
            $this->kas_id = '';
        }

        if (empty($this->kas_id) && $availableKas->count() === 1) {
            $this->kas_id = (string) $availableKas->first()->id;
        }
    }

    public function save(): mixed
    {
        $this->validate();

        $cleanSaldo = (int) preg_replace('/[^0-9]/', '', (string) $this->saldo);

        DB::transaction(function () use ($cleanSaldo): void {
            if ($this->tabungan && $this->tabungan->exists) {
                $this->tabungan->update([
                    'siswa_id' => $this->siswa_id,
                    'kas_id' => $this->kas_id,
                    'saldo' => $cleanSaldo,
                ]);
                session()->flash('message', 'Data tabungan berhasil diperbarui.');
            } else {
                $existingTabungan = Tabungan::where('siswa_id', $this->siswa_id)
                    ->where('kas_id', $this->kas_id)
                    ->first();

                if ($existingTabungan) {
                    $existingTabungan->increment('saldo', $cleanSaldo);
                    $tabunganRecord = $existingTabungan;
                    session()->flash('message', 'Saldo berhasil ditambahkan ke tabungan siswa.');
                } else {
                    $tabunganRecord = Tabungan::create([
                        'siswa_id' => $this->siswa_id,
                        'kas_id' => $this->kas_id,
                        'saldo' => $cleanSaldo,
                    ]);
                    session()->flash('message', 'Data tabungan berhasil ditambahkan.');
                }

                if ($cleanSaldo > 0) {
                    $kas = Kas::find($this->kas_id);
                    $siswa = Siswa::find($this->siswa_id);
                    $kasNama = $kas instanceof Kas ? $kas->nama : '';
                    $siswaNama = $siswa instanceof Siswa ? $siswa->nama : '';

                    $tabunganRecord->transaksi()->create([
                        'kode' => 'STB'.date('YmdHis'),
                        'jumlah' => $cleanSaldo,
                        'keterangan' => 'Setor Tabungan '.$kasNama.' '.$siswaNama,
                        'user_id' => auth()->id(),
                    ]);

                    if ($kas) {
                        $kas->increment('saldo', $cleanSaldo);
                    }
                }
            }
        });

        return $this->redirect(route('admin.tabungan.index'), navigate: true);
    }

    public function render(): View
    {
        $user = auth()->user();
        $lembagaId = null;
        if ($user && ! $user->isAdmin()) {
            $lembagaId = $user->authable->lembaga_id ?? null;
        }

        $siswaList = Siswa::select('id', 'nama', 'nis', 'lembaga_id', 'kelas_id')
            ->with('kelas:id,nama')
            ->when($lembagaId, function ($q) use ($lembagaId) {
                $q->where('lembaga_id', $lembagaId);
            })
            ->orderBy('nama')
            ->get();

        $selectedSiswa = null;
        $kasList = collect();
        $existingTabungan = null;

        if ($this->siswa_id) {
            $selectedSiswa = $siswaList->firstWhere('id', $this->siswa_id)
                ?? Siswa::with('kelas:id,nama')->whereKey($this->siswa_id)->first();

            if ($selectedSiswa) {
                $kasList = Kas::where('tabungan', true)
                    ->where('lembaga_id', $selectedSiswa->lembaga_id)
                    ->orderBy('nama')
                    ->get();
            }

            if ($this->kas_id && (! $this->tabungan || ! $this->tabungan->exists)) {
                $existingTabungan = Tabungan::where('siswa_id', $this->siswa_id)
                    ->where('kas_id', $this->kas_id)
                    ->first();
            }
        }

        $siswaOptions = $siswaList->map(function (Siswa $s): array {
            $kelas = $s->kelas;
            $kelasNama = $kelas instanceof Kelas ? $kelas->nama : '';
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

        return view('livewire.admin.tabungan.tabungan-form', [
            'siswaOptions' => $siswaOptions,
            'selectedSiswa' => $selectedSiswa,
            'kas_list' => $kasList,
            'existingTabungan' => $existingTabungan,
        ])->layout('components.admin-layout');
    }
}
