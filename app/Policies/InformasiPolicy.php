<?php

namespace App\Policies;

use App\Models\Informasi;
use App\Models\User;

class InformasiPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isPetugas();
    }

    public function view(User $user, Informasi $informasi): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isPetugas();
    }

    public function update(User $user, Informasi $informasi): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isPetugas()) {
            $userLembaga = $user->authable->lembaga_id ?? null;

            return $informasi->lembaga_id === null || $informasi->lembaga_id == $userLembaga;
        }

        return false;
    }

    public function delete(User $user, Informasi $informasi): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isPetugas() && $informasi->user_id === $user->id;
    }
}
