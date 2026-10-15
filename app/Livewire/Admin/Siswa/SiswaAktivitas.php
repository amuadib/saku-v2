<?php

namespace App\Livewire\Admin\Siswa;

use App\Models\Siswa;
use App\Models\SiswaAktivitas as SiswaAktivitasModel;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class SiswaAktivitas extends Component
{
    use WithPagination;

    public Siswa $siswa;

    public $search = '';

    public $filter_tipe = '';

    public $sort_order = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'filter_tipe' => ['except' => ''],
    ];

    public function mount(Siswa $siswa): void
    {
        Gate::authorize('view', $siswa);
        $this->siswa = $siswa;
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'filter_tipe', 'sort_order'])) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filter_tipe = '';
        $this->sort_order = 'desc';
        $this->resetPage();
    }

    public function render()
    {
        $this->siswa->loadMissing(['kelas', 'auth']);

        $stats = [
            'total_login' => SiswaAktivitasModel::where('siswa_id', $this->siswa->id)->where('tipe', 'login')->count(),
            'total_akses' => SiswaAktivitasModel::where('siswa_id', $this->siswa->id)->where('tipe', 'akses_link')->count(),
            'total_ubah_data' => SiswaAktivitasModel::where('siswa_id', $this->siswa->id)->where('tipe', 'ubah_data')->count(),
            'last_login' => $this->siswa->last_login_at,
            'last_ip' => $this->siswa->last_login_ip,
            'last_activity' => SiswaAktivitasModel::where('siswa_id', $this->siswa->id)->latest('created_at')->first(),
        ];

        $aktivitas = SiswaAktivitasModel::where('siswa_id', $this->siswa->id)
            ->when($this->filter_tipe, function ($q) {
                $q->where('tipe', $this->filter_tipe);
            })
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('keterangan', 'like', '%'.$this->search.'%')
                        ->orWhere('url', 'like', '%'.$this->search.'%')
                        ->orWhere('ip_address', 'like', '%'.$this->search.'%')
                        ->orWhere('device', 'like', '%'.$this->search.'%')
                        ->orWhere('browser', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('created_at', $this->sort_order === 'asc' ? 'asc' : 'desc')
            ->paginate(15);

        return view('livewire.admin.siswa.siswa-aktivitas', [
            'aktivitas' => $aktivitas,
            'stats' => $stats,
        ])->layout('components.admin-layout', ['header' => 'Aktivitas & Log Akses: '.$this->siswa->nama]);
    }
}
