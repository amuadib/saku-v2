<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ChatMessage extends Model
{
    use HasUuids;

    protected $table = 'chat_messages';

    protected $fillable = [
        'siswa_id',
        'sender_type',
        'sender_id',
        'pesan',
        'attachment_path',
        'attachment_name',
        'attachment_type',
        'attachment_size',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'attachment_size' => 'integer',
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

    public function hasAttachment(): bool
    {
        return ! empty($this->attachment_path);
    }

    public function isImage(): bool
    {
        if (! $this->hasAttachment()) {
            return false;
        }

        if ($this->attachment_type && str_starts_with($this->attachment_type, 'image/')) {
            return true;
        }

        $extension = strtolower(pathinfo($this->attachment_path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
    }

    public function isPdf(): bool
    {
        if (! $this->hasAttachment()) {
            return false;
        }

        if ($this->attachment_type === 'application/pdf') {
            return true;
        }

        return strtolower(pathinfo($this->attachment_path, PATHINFO_EXTENSION)) === 'pdf';
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (! $this->attachment_path) {
            return null;
        }

        return Storage::disk('public')->url($this->attachment_path);
    }

    public function getFormattedAttachmentSizeAttribute(): string
    {
        if (! $this->attachment_size) {
            return '';
        }

        $bytes = (int) $this->attachment_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }

        return number_format($bytes / 1024, 0).' KB';
    }
}
