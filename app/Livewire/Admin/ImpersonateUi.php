<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;

class ImpersonateUi extends Component
{
    public $target_user_id = '';

    public function getUsersProperty()
    {
        return User::orderBy('username')->get();
    }

    public function impersonate()
    {
        if (empty($this->target_user_id)) {
            return;
        }

        $target = User::find($this->target_user_id);

        // Cek apakah user aslinya punya hak impersonate, atau user saat ini
        $originalUserId = session()->get('impersonate_by');
        $hasPermission = false;

        if ($originalUserId) {
            $originalUser = User::find($originalUserId);
            if ($originalUser && $originalUser->can('impersonate', $target)) {
                $hasPermission = true;
            }
        } else {
            if (auth()->user()->can('impersonate', $target)) {
                $hasPermission = true;
            }
        }

        if ($target && $hasPermission) {
            if (! session()->has('impersonate_by')) {
                session()->put('impersonate_by', auth()->id());
            }
            auth()->login($target);

            return redirect()->route('admin.dashboard')->with('message', 'Berhasil login sebagai '.$target->name);
        }
    }

    public function leave()
    {
        if (session()->has('impersonate_by')) {
            $original = User::find(session()->pull('impersonate_by'));
            if ($original) {
                auth()->login($original);

                return redirect()->route('admin.user.index')->with('message', 'Kembali ke akun semula.');
            }
        }

        return redirect()->route('admin.dashboard');
    }

    public function render()
    {
        return view('livewire.admin.impersonate-ui');
    }
}
