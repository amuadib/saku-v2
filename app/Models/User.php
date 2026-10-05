<?php

namespace App\Models;

use App\Traits\CatatAktivitas;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use CatatAktivitas;
    use HasUuids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'authable_type',
        'authable_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
    public function authable()
    {
        return $this->morphTo();
    }

    public function getNameAttribute()
    {
        return optional($this->authable)->nama ?? $this->username;
    }

    public function getEmailAttribute()
    {
        // Fallback for views that require email (saku-v2 uses username)
        return $this->username;
    }

    public function initials(): string
    {
        $name = $this->name;
        $words = explode(' ', $name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        
        return strtoupper(substr($name, 0, 2));
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->authable->foto && Storage::disk('public')->exists($this->authable->foto) ? url('/storage/' . $this->authable->foto) : env('AVATAR_PROVIDER', 'https://ui-avatars.com/api/?rounded=true&background=09090b&color=fbac1e&name=') . urlencode($this->authable->nama);
    }
    public function getFilamentName(): string
    {
        return $this->authable->nama;
    }
    public function isAdmin(): bool
    {
        return $this->role_id === 1;
    }
    public function isKepala(): bool
    {
        return $this->role_id === 3;
    }
    public function isPetugas(): bool
    {
        return $this->role_id <= 4;
    }
    public function isTataUsaha(): bool
    {
        return in_array($this->role_id, [1, 4]);
    }
    public function canImpersonate()
    {
        return $this->role_id === 1;
    }
}
