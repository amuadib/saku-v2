<?php

namespace App\Livewire\Admin\User;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
{
    use WithPagination;

    public $search = '';

    public function mount()
    {
        Gate::authorize('viewAny', User::class);
    }

    public function updatingSearch()
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
            ->where('username', 'like', '%'.$this->search.'%')
            ->orderBy('username')
            ->paginate(10);

        return view('livewire.admin.user.user-list', [
            'users' => $users,
        ])->layout('components.admin-layout');
    }
}
