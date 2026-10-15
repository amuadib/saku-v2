<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasUuids;

    protected $table = 'chat_messages';

    protected $fillable = [
        'siswa_id',
        'sender_type',
        'sender_id',
        'pesan',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function scopeForSiswa($query, string $siswaId)
    {
        return $query->where('siswa_id', $siswaId);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public static function unreadCountForUser(?User $user = null): int
    {
        $user = $user ?? auth()->user();
        if (! $user) {
            return 0;
        }

        if ($user->authable_type === Siswa::class && $user->authable_id) {
            return static::where('siswa_id', $user->authable_id)
                ->where('sender_type', 'admin')
                ->where('is_read', false)
                ->count();
        }

        $query = static::where('sender_type', 'siswa')->where('is_read', false);

        if (! $user->isAdmin() && isset($user->authable->lembaga_id)) {
            $query->whereHas('siswa', function ($q) use ($user) {
                $q->where('lembaga_id', $user->authable->lembaga_id);
            });
        }

        return $query->count();
    }
}
