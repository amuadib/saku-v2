<?php

namespace App\Livewire\Admin\User;

use App\Models\Anggota;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
{
    use WithPagination;

    public $search = '';

    public $filter_role = '';

    public function mount()
    {
        Gate::authorize('viewAny', User::class);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterRole()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        Gate::authorize('delete', $user);
        $user->delete();
        session()->flash('message', 'User berhasil dihapus.');
    }

    public function render()
    {
        $users = User::with('authable')
            ->when($this->filter_role, fn ($q) => $q->where('role_id', $this->filter_role))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('username', 'like', '%'.$this->search.'%')
                        ->orWhereHasMorph('authable', [Siswa::class, Anggota::class], function ($morphQ) {
                            $morphQ->where('nama', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->orderBy('username')
            ->paginate(10);

        return view('livewire.admin.user.user-list', [
            'users' => $users,
        ])->layout('components.admin-layout');
    }
}
