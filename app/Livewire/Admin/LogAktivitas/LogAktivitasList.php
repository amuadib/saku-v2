<?php

namespace App\Livewire\Admin\LogAktivitas;

use App\Models\LogAktivitas;
use Livewire\Component;
use Livewire\WithPagination;

class LogAktivitasList extends Component
{
    use WithPagination;

    public $search = '';

    protected $queryString = ['search'];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function undo($id)
    {
        $log = LogAktivitas::findOrFail($id);
        $modelClass = $log->model;

        if (! class_exists($modelClass)) {
            \Flux::toast('Class model tidak ditemukan.', variant: 'danger');

            return;
        }

        try {
            // Kita coba undo
            if ($log->aksi === 'create') {
                // Berarti aslinya data dibuat. Undonya adalah menghapus data.
                $model = $modelClass::find($log->model_id);
                if ($model) {
                    $model->disableLogging = true;
                    $model->delete();
                }
            } elseif ($log->aksi === 'update') {
                // Berarti aslinya data diupdate. Undonya adalah mengembalikan ke data lama.
                $model = $modelClass::find($log->model_id);
                if ($model && $log->data_lama) {
                    $model->disableLogging = true;
                    $model->update($log->data_lama);
                }
            } elseif ($log->aksi === 'delete') {
                // Berarti aslinya data dihapus. Undonya adalah memasukkan kembali data lama.
                if ($log->data_lama) {
                    $model = new $modelClass;
                    $model->disableLogging = true;
                    // Force the ID and old data
                    $model->forceFill($log->data_lama);
                    $model->save();
                }
            }

            // Hapus log ini karena sudah di-undo (atau bisa diberi penanda)
            $log->delete();

            \Flux::toast('Aktivitas berhasil di-undo.', variant: 'success');
        } catch (\Exception $e) {
            \Flux::toast('Gagal melakukan undo: '.$e->getMessage(), variant: 'danger');
        }
    }

    public function render()
    {
        $logs = LogAktivitas::with('user')
            ->where(function ($query) {
                $query->where('model', 'like', '%'.$this->search.'%')
                    ->orWhere('aksi', 'like', '%'.$this->search.'%')
                    ->orWhereHas('user', function ($q) {
                        $q->where('username', 'like', '%'.$this->search.'%');
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.admin.log-aktivitas.log-aktivitas-list', [
            'logs' => $logs,
        ])->layout('components.admin-layout');
    }
}
