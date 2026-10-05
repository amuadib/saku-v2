<?php

namespace App\Livewire\Admin\User;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UserForm extends Component
{
    public ?User $user = null;

    public $username = '';

    public $password = '';

    public $password_confirmation = '';

    public $role_id = 5; // Default some non-admin role

    public $authable_type = '';

    public $authable_id = '';

    protected function rules()
    {
        return [
            'username' => 'required|string|max:255|unique:users,username,'.($this->user->id ?? 'NULL'),
            'password' => $this->user && $this->user->exists ? 'nullable|min:8|confirmed' : 'required|min:8|confirmed',
            'role_id' => 'required|integer',
            'authable_type' => 'nullable|string',
            'authable_id' => 'nullable|uuid',
        ];
    }

    public function mount(?User $user = null)
    {
        if ($user && $user->exists) {
            Gate::authorize('update', $user);
            $this->user = $user;
            $this->username = $user->username;
            $this->role_id = $user->role_id;
            $this->authable_type = $user->authable_type;
            $this->authable_id = $user->authable_id;
        } else {
            Gate::authorize('create', User::class);
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'username' => $this->username,
            'role_id' => $this->role_id,
            'authable_type' => $this->authable_type ?: null,
            'authable_id' => $this->authable_id ?: null,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->user && $this->user->exists) {
            $this->user->update($data);
            session()->flash('message', 'Pengguna berhasil diperbarui.');
        } else {
            User::create($data);
            session()->flash('message', 'Pengguna berhasil ditambahkan.');
        }

        return $this->redirect(route('admin.user.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.user.user-form')->layout('components.admin-layout');
    }
}
