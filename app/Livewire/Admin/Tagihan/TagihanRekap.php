<?php

namespace App\Livewire\Admin\Tagihan;

use App\Models\Kas;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Services\WhatsappService;
use Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class TagihanRekap extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortField = 'total';

    public string $sortDirection = 'desc';

    public int $perPage = 25;

    public function mount(): void
    {
        Gate::authorize('viewAny', Tagihan::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            // Sorting by numbers (kas amounts or total) defaults to desc, textual to asc
            $this->sortDirection = ($field === 'total' || str_starts_with($field, 'kas_')) ? 'desc' : 'asc';
        }

        $this->resetPage();
    }

    public function kirimWa(string $siswaId): void
    {
        $siswa = Siswa::with(['kelas', 'tagihan' => function ($q): void {
            $q->where(function ($sub): void {
                $sub->whereNull('bayar')->orWhereColumn('bayar', '<', 'jumlah');
            })->with('kas');
        }])->findOrFail($siswaId);

        if (empty($siswa->telepon)) {
            Flux::toast('Nomor WhatsApp siswa/wali belum terdaftar.', variant: 'warning');

            return;
        }

        $rincian = '';
        $no = 1;
        $total = 0;

        foreach ($siswa->tagihan as $t) {
            $sisa = (float) $t->jumlah - (float) ($t->bayar ?? 0);
            if ($sisa > 0) {
                $tgl = $t->updated_at ? $t->updated_at->format('d-m-Y') : $t->created_at->format('d-m-Y');
                $namaKasOrKet = $t->keterangan ? ucfirst($t->keterangan) : ($t->kas->nama ?? 'Tagihan');
                $rincian .= $no.'. '.$namaKasOrKet.' ('.$tgl.'): Rp '.number_format($sisa, 0, ',', '.').PHP_EOL;
                $total += $sisa;
                $no++;
            }
        }

        if ($total <= 0) {
            Flux::toast('Siswa ini tidak memiliki tagihan aktif yang belum lunas.', variant: 'info');

            return;
        }

        $pesan = WhatsappService::prosesPesan(
            siswa: $siswa,
            data: [
                'lembaga' => config('custom.lembaga.'.$siswa->lembaga_id.'.nama'),
                'kontak.nama' => config('custom.lembaga.'.$siswa->lembaga_id.'.kontak'),
                'kontak.telp' => config('custom.lembaga.'.$siswa->lembaga_id.'.telp'),
                'tagihan.rincian' => $rincian,
                'tagihan.total' => 'Rp '.number_format($total, 0, ',', '.'),
            ],
            jenis: $siswa->status == 3 ? 'tagihan.daftar_alumni' : 'tagihan.daftar'
        );

        $hasApi = ! empty(config('whatsapp.WA_API_URL'))
            && config('whatsapp.WA_API_URL') !== 'https://api.whatsapp.com'
            && config('whatsapp.WHATSAPP_NOTIFICATION');

        if ($hasApi) {
            $res = WhatsappService::kirimWa(
                nomor: $siswa->telepon,
                pesan: $pesan,
                sessionId: WhatsappService::getSessionId($siswa),
                nama: $siswa->nama
            );

            if (($res['status'] ?? '') === 'success') {
                Flux::toast("Rekap tagihan berhasil dikirim ke WhatsApp {$siswa->nama}.", variant: 'success');

                return;
            }
        }

        // Buka WhatsApp Web / wa.me link
        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $siswa->telepon);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62'.substr($cleanPhone, 1);
        }
        $url = 'https://api.whatsapp.com/send?phone='.$cleanPhone.'&text='.rawurlencode($pesan);
        $this->js("window.open('{$url}', '_blank');");
        Flux::toast("Membuka WhatsApp untuk {$siswa->nama}...", variant: 'success');
    }

    public function kirimSemuaWa(): void
    {
        $user = auth()->user();
        $isNonAdmin = $user && ! $user->isAdmin();
        $userLembagaId = $isNonAdmin ? ($user->authable->lembaga_id ?? null) : null;

        $siswas = Siswa::query()
            ->when($userLembagaId, fn ($q) => $q->where('lembaga_id', $userLembagaId))
            ->whereNotNull('telepon')
            ->where('telepon', '!=', '')
            ->whereHas('tagihan', function ($q): void {
                $q->where(function ($sub): void {
                    $sub->whereNull('bayar')->orWhereColumn('bayar', '<', 'jumlah');
                });
            })
            ->when($this->search, function ($q): void {
                $q->where(function ($sub): void {
                    $sub->where('nama', 'like', '%'.$this->search.'%')
                        ->orWhere('nis', 'like', '%'.$this->search.'%');
                });
            })
            ->with(['kelas', 'tagihan' => function ($q): void {
                $q->where(function ($sub): void {
                    $sub->whereNull('bayar')->orWhereColumn('bayar', '<', 'jumlah');
                })->with('kas');
            }])
            ->get();

        $pesanList = [];
        foreach ($siswas as $s) {
            $rincian = '';
            $no = 1;
            $total = 0;

            foreach ($s->tagihan as $t) {
                $sisa = (float) $t->jumlah - (float) ($t->bayar ?? 0);
                if ($sisa > 0) {
                    $tgl = $t->updated_at ? $t->updated_at->format('d-m-Y') : $t->created_at->format('d-m-Y');
                    $namaKasOrKet = $t->keterangan ? ucfirst($t->keterangan) : ($t->kas->nama ?? 'Tagihan');
                    $rincian .= $no.'. '.$namaKasOrKet.' ('.$tgl.'): Rp '.number_format($sisa, 0, ',', '.').PHP_EOL;
                    $total += $sisa;
                    $no++;
                }
            }

            if ($total <= 0) {
                continue;
            }

            $nomor = env('APP_ENV') == 'local' ? config('whatsapp.WHATSAPP_TEST_NUMBER') : (string) $s->telepon;
            $pesanList[] = [
                'name' => $s->nama,
                'number' => $nomor,
                'message' => WhatsappService::prosesPesan(
                    siswa: $s,
                    data: [
                        'lembaga' => config('custom.lembaga.'.$s->lembaga_id.'.nama'),
                        'kontak.nama' => config('custom.lembaga.'.$s->lembaga_id.'.kontak'),
                        'kontak.telp' => config('custom.lembaga.'.$s->lembaga_id.'.telp'),
                        'tagihan.rincian' => $rincian,
                        'tagihan.total' => 'Rp '.number_format($total, 0, ',', '.'),
                    ],
                    jenis: $s->status == 3 ? 'tagihan.daftar_alumni' : 'tagihan.daftar'
                ),
                'sessionId' => WhatsappService::getSessionId($s),
            ];
        }

        if (empty($pesanList)) {
            Flux::toast('Tidak ada siswa dengan tagihan dan nomor WhatsApp valid.', variant: 'warning');

            return;
        }

        $res = WhatsappService::kirimWa(kumpulan_pesan: $pesanList);
        if (($res['status'] ?? '') === 'success') {
            Flux::toast('Rekap tagihan berhasil dikirimkan ke '.count($pesanList).' siswa.', variant: 'success');
        } else {
            Flux::toast('Gagal mengirim massal: '.($res['message'] ?? 'Periksa konfigurasi WhatsApp API'), variant: 'danger');
        }
    }

    #[Layout('components.admin-layout')]
    public function render(): View
    {
        $user = auth()->user();
        $isNonAdmin = $user && ! $user->isAdmin();
        $userLembagaId = $isNonAdmin ? ($user->authable->lembaga_id ?? null) : null;

        $kasList = Kas::where('ada_tagihan', 1)
            ->when($userLembagaId, fn ($q) => $q->where('lembaga_id', $userLembagaId))
            ->orderBy('nama')
            ->get();

        $query = Siswa::query()
            ->when($userLembagaId, fn ($q) => $q->where('lembaga_id', $userLembagaId))
            ->whereHas('tagihan', function ($q): void {
                $q->where(function ($sub): void {
                    $sub->whereNull('bayar')->orWhereColumn('bayar', '<', 'jumlah');
                });
            })
            ->with(['kelas:id,nama', 'tagihan' => function ($q): void {
                $q->where(function ($sub): void {
                    $sub->whereNull('bayar')->orWhereColumn('bayar', '<', 'jumlah');
                })->with('kas:id,nama');
            }])
            ->when($this->search, function ($q): void {
                $q->where(function ($sub): void {
                    $sub->where('nama', 'like', '%'.$this->search.'%')
                        ->orWhere('nis', 'like', '%'.$this->search.'%');
                });
            });

        $direction = in_array(strtolower($this->sortDirection), ['asc', 'desc']) ? strtolower($this->sortDirection) : 'desc';

        if ($this->sortField === 'kelas') {
            $query->orderBy(
                Kelas::select('nama')
                    ->whereColumn('kelas.id', 'siswa.kelas_id')
                    ->limit(1),
                $direction
            )->orderBy('siswa.nama', 'asc');
        } elseif ($this->sortField === 'total') {
            $query->orderBy(
                Tagihan::selectRaw('COALESCE(SUM(jumlah - COALESCE(bayar, 0)), 0)')
                    ->whereColumn('tagihan.siswa_id', 'siswa.id')
                    ->where(function ($sub): void {
                        $sub->whereNull('bayar')->orWhereColumn('bayar', '<', 'jumlah');
                    }),
                $direction
            )->orderBy('siswa.nama', 'asc');
        } elseif (str_starts_with($this->sortField, 'kas_')) {
            $kasId = substr($this->sortField, 4);
            $query->orderBy(
                Tagihan::selectRaw('COALESCE(SUM(jumlah - COALESCE(bayar, 0)), 0)')
                    ->whereColumn('tagihan.siswa_id', 'siswa.id')
                    ->where('tagihan.kas_id', $kasId)
                    ->where(function ($sub): void {
                        $sub->whereNull('bayar')->orWhereColumn('bayar', '<', 'jumlah');
                    }),
                $direction
            )->orderBy('siswa.nama', 'asc');
        } else {
            $query->orderBy('siswa.nama', $direction);
        }

        $siswaList = $query->paginate($this->perPage);

        $grandTotalTunggakan = Tagihan::query()
            ->where(function ($sub): void {
                $sub->whereNull('bayar')->orWhereColumn('bayar', '<', 'jumlah');
            })
            ->whereHas('siswa', function ($q) use ($userLembagaId): void {
                if ($userLembagaId) {
                    $q->where('lembaga_id', $userLembagaId);
                }
                $q->when($this->search, function ($sub): void {
                    $sub->where('nama', 'like', '%'.$this->search.'%')
                        ->orWhere('nis', 'like', '%'.$this->search.'%');
                });
            })
            ->selectRaw('SUM(jumlah - COALESCE(bayar, 0)) as total_sisa')
            ->value('total_sisa') ?? 0;

        return view('livewire.admin.tagihan.tagihan-rekap', [
            'kasList' => $kasList,
            'siswaList' => $siswaList,
            'grandTotalTunggakan' => (float) $grandTotalTunggakan,
        ]);
    }
}
