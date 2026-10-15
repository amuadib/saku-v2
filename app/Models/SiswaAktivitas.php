<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiswaAktivitas extends Model
{
    protected $table = 'siswa_aktivitas';

    protected $fillable = [
        'siswa_id',
        'user_id',
        'tipe',
        'url',
        'route_name',
        'method',
        'ip_address',
        'user_agent',
        'device',
        'browser',
        'keterangan',
        'data_lama',
        'data_baru',
    ];

    protected $casts = [
        'data_lama' => 'array',
        'data_baru' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function getFieldLabels(): array
    {
        return [
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
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Parse device and browser name from user agent string.
     *
     * @return array{device: string, browser: string}
     */
    public static function parseUserAgent(?string $userAgent): array
    {
        if (! $userAgent) {
            return ['device' => 'Unknown', 'browser' => 'Unknown'];
        }

        $device = 'Desktop';
        if (preg_match('/(android|iphone|ipad|ipod|mobile|phone)/i', $userAgent)) {
            $device = 'Mobile';
            if (preg_match('/android/i', $userAgent)) {
                $device = 'Android';
            } elseif (preg_match('/(iphone|ipad|ipod)/i', $userAgent)) {
                $device = 'iOS';
            }
        } elseif (preg_match('/windows/i', $userAgent)) {
            $device = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $device = 'macOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $device = 'Linux';
        }

        $browser = 'Unknown';
        if (preg_match('/edg/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/chrome/i', $userAgent) && ! preg_match('/edg/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/safari/i', $userAgent) && ! preg_match('/chrome/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/opera|opr/i', $userAgent)) {
            $browser = 'Opera';
        }

        return [
            'device' => $device,
            'browser' => $browser,
        ];
    }
}
