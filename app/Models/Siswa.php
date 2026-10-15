<?php

namespace App\Models;

use App\Traits\CatatAktivitas;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Siswa extends Model
{
    use CatatAktivitas;
    use HasUuids, LogsActivity;

    protected $table = 'siswa';

    protected $fillable = [
        'nis',
        'nisn',
        'nik',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'nama_ayah',
        'nama_ibu',
        'telepon',
        'email',
        'lembaga_id',
        'kelas_id',
        'status',

        'foto',
        'last_login_at',
        'last_login_ip',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
    ];

    public function auth()
    {
        return $this->morphOne(User::class, 'authable');
    }

    public function aktivitas(): HasMany
    {
        return $this->hasMany(SiswaAktivitas::class, 'siswa_id');
    }

    protected static function booted(): void
    {
        static::updated(function (Siswa $siswa) {
            if (! auth()->check()) {
                return;
            }

            $user = auth()->user();
            $isStudent = ($user->authable_type === Siswa::class && $user->authable_id === $siswa->id)
                || ($user->authable instanceof Siswa && $user->authable->id === $siswa->id)
                || request()->routeIs('siswa.*')
                || request()->is('profil*');

            if (! $isStudent && ! session()->has('impersonate_by')) {
                return;
            }

            $ignored = ['updated_at', 'last_login_at', 'last_login_ip'];
            $changes = array_diff_key($siswa->getChanges(), array_flip($ignored));

            if (empty($changes)) {
                return;
            }

            $fieldLabels = [
                'nama' => 'Nama Lengkap',
                'nik' => 'NIK',
                'nis' => 'NIS',
                'nisn' => 'NISN',
                'tempat_lahir' => 'Tempat Lahir',
                'tanggal_lahir' => 'Tanggal Lahir',
                'jenis_kelamin' => 'Jenis Kelamin',
                'alamat' => 'Alamat',
                'nama_ayah' => 'Nama Ayah',
                'nama_ibu' => 'Nama Ibu',
                'telepon' => 'No. Telepon/WA',
                'email' => 'Email',
                'foto' => 'Foto Profil',
            ];

            $changedLabels = [];
            foreach (array_keys($changes) as $key) {
                $changedLabels[] = $fieldLabels[$key] ?? ucfirst(str_replace('_', ' ', $key));
            }

            $keterangan = 'Memperbarui profil ('.implode(', ', $changedLabels).')';
            if (session()->has('impersonate_by')) {
                $keterangan .= ' (Impersonasi oleh Admin)';
            }

            $dataLama = [];
            $dataBaru = [];
            foreach ($changes as $key => $newValue) {
                $oldValue = $siswa->getOriginal($key);
                $dataLama[$key] = $oldValue instanceof \DateTimeInterface ? $oldValue->format('Y-m-d') : $oldValue;
                $dataBaru[$key] = $newValue instanceof \DateTimeInterface ? $newValue->format('Y-m-d') : $newValue;
            }

            $request = request();
            $url = $request->fullUrl();
            $routeName = $request->route()?->getName();

            // Jika request datang dari internal Livewire endpoint, gunakan URL referer (halaman sebenarnya)
            if (str_contains($request->path(), 'livewire') || str_contains((string) $routeName, 'livewire')) {
                $referer = $request->headers->get('referer');
                if ($referer) {
                    $url = $referer;
                    try {
                        $matchedRoute = app('router')->getRoutes()->match(app('request')->create($referer));
                        $routeName = $matchedRoute?->getName() ?? 'siswa.profil.detail';
                    } catch (\Throwable $e) {
                        $routeName = 'siswa.profil.detail';
                    }
                } else {
                    $url = route('siswa.profil.detail');
                    $routeName = 'siswa.profil.detail';
                }
            }

            $parsed = SiswaAktivitas::parseUserAgent($request->userAgent());

            SiswaAktivitas::create([
                'siswa_id' => $siswa->id,
                'user_id' => $user->id,
                'tipe' => 'ubah_data',
                'url' => $url,
                'route_name' => $routeName,
                'method' => 'POST',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device' => $parsed['device'],
                'browser' => $parsed['browser'],
                'keterangan' => $keterangan,
                'data_lama' => $dataLama,
                'data_baru' => $dataBaru,
            ]);
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logExcept([
                'id',
                'created_at',
                'updated_at',
            ]);
    }

    public function getNamaSiswaAttribute()
    {
        return ucwords(strtolower($this->nama));
    }

    public function scopeGetDaftarSiswa($query, $lembaga_id = null)
    {
        $lembaga_id == null ? auth()->user()->authable->lembaga_id : $lembaga_id;
        $query
            ->when($lembaga_id != 99, function ($w) use ($lembaga_id) {
                $w->where('lembaga_id', $lembaga_id);
            })
            ->where('status', 1)
            ->select('id', 'nama');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function tagihan(): HasMany
    {
        return $this->hasMany(Tagihan::class);
    }

    public function tabungan(): HasMany
    {
        return $this->hasMany(Tabungan::class);
    }

    public function keranjang(): HasMany
    {
        return $this->hasMany(Keranjang::class);
    }

    public function getLembagaAttribute(): array
    {
        return config('custom.lembaga.'.$this->lembaga_id, []);
    }

    public function resolveLembagaMediaUrl(?string $rawPath): ?string
    {
        if (empty($rawPath)) {
            return null;
        }

        if (str_starts_with($rawPath, 'http://') || str_starts_with($rawPath, 'https://')) {
            return $rawPath;
        }

        $cleanPath = ltrim($rawPath, '/');

        if (file_exists(public_path($cleanPath))) {
            return asset($cleanPath);
        }

        if (file_exists(public_path('storage/'.$cleanPath))) {
            return asset('storage/'.$cleanPath);
        }

        $apiBase = config('custom.api_akademik.url');
        if ($apiBase) {
            $path = str_starts_with($cleanPath, 'storage/') ? $cleanPath : 'storage/'.$cleanPath;

            return rtrim($apiBase, '/').'/'.$path;
        }

        return asset($rawPath);
    }

    public function getLembagaLogoUrlAttribute(): ?string
    {
        return $this->resolveLembagaMediaUrl($this->lembaga['logo_url'] ?? ($this->lembaga['logo'] ?? null));
    }

    public function getLembagaKopUrlAttribute(): ?string
    {
        return $this->resolveLembagaMediaUrl($this->lembaga['kop_url'] ?? ($this->lembaga['kop'] ?? null));
    }

    public function getLembagaTtdUrlAttribute(): ?string
    {
        return $this->resolveLembagaMediaUrl($this->lembaga['ttd_url'] ?? ($this->lembaga['ttd'] ?? null));
    }

    public function getLembagaStempelUrlAttribute(): ?string
    {
        return $this->resolveLembagaMediaUrl($this->lembaga['stempel_url'] ?? ($this->lembaga['stempel'] ?? null));
    }

    public function getLembagaKepalaAttribute(): ?string
    {
        return $this->lembaga['kepala'] ?? null;
    }

    public function getLembagaNipKepalaAttribute(): ?string
    {
        return $this->lembaga['nip_kepala'] ?? null;
    }

    public function getLembagaEmailAttribute(): ?string
    {
        return $this->lembaga['email'] ?? null;
    }

    public function getLembagaLatAttribute(): mixed
    {
        return $this->lembaga['lat'] ?? null;
    }

    public function getLembagaLonAttribute(): mixed
    {
        return $this->lembaga['lon'] ?? null;
    }

    public function getLembagaMapsEmbedAttribute(): ?string
    {
        return $this->lembaga['maps_embed'] ?? null;
    }

    public function getLembagaMapsEmbedUrlAttribute(): ?string
    {
        if (! empty($this->lembaga['maps_embed_url'])) {
            return $this->lembaga['maps_embed_url'];
        }

        $embed = trim((string) ($this->lembaga['maps_embed'] ?? ''));
        if (! empty($embed)) {
            if (preg_match('/src=["\']([^"\']+)["\']/', $embed, $matches)) {
                return $matches[1];
            }
            if (str_starts_with($embed, 'http')) {
                return $embed;
            }
        }

        $lat = trim((string) ($this->lembaga['lat'] ?? ''));
        $lon = trim((string) ($this->lembaga['lon'] ?? ''));
        if (! empty($lat) && ! empty($lon) && (float) $lat != 0 && (float) $lon != 0) {
            return "https://maps.google.com/maps?q={$lat},{$lon}&hl=id&z=16&output=embed";
        }

        if (! empty($this->lembaga['alamat'])) {
            return 'https://maps.google.com/maps?q='.urlencode($this->lembaga['alamat']).'&hl=id&z=15&output=embed';
        }

        return null;
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'siswa_id')->orderBy('created_at', 'asc');
    }

    public function latestChatMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class, 'siswa_id')->latestOfMany();
    }
}
