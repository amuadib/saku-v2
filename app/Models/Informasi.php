<?php

namespace App\Models;

use App\Traits\CatatAktivitas;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Informasi extends Model
{
    use CatatAktivitas;
    use HasFactory, HasUuids, LogsActivity;

    protected $table = 'informasi';

    protected $fillable = [
        'judul',
        'kategori',
        'konten',
        'gambar',
        'lembaga_id',
        'is_aktif',
        'is_sticky',
        'tanggal',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_aktif' => 'boolean',
        'is_sticky' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logExcept([
                'id',
                'created_at',
                'updated_at',
            ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getGambarUrlAttribute(): ?string
    {
        if (empty($this->gambar)) {
            return null;
        }

        if (str_starts_with($this->gambar, 'http://') || str_starts_with($this->gambar, 'https://')) {
            return $this->gambar;
        }

        return Storage::disk('public')->url($this->gambar);
    }

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    public function scopeSticky($query)
    {
        return $query->where('is_sticky', true);
    }

    public function scopeNotSticky($query)
    {
        return $query->where('is_sticky', false);
    }

    public function scopeForLembaga($query, ?int $lembagaId = null)
    {
        return $query->where(function ($q) use ($lembagaId) {
            $q->whereNull('lembaga_id')
                ->orWhere('lembaga_id', 99);
            if ($lembagaId) {
                $q->orWhere('lembaga_id', $lembagaId);
            }
        });
    }

    public function getNamaLembagaAttribute(): string
    {
        if (empty($this->lembaga_id) || $this->lembaga_id == 99) {
            return 'Semua Lembaga';
        }

        return config('custom.lembaga.'.$this->lembaga_id.'.nama', 'Lembaga #'.$this->lembaga_id);
    }
}
