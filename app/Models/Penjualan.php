<?php

namespace App\Models;

use App\Traits\CatatAktivitas;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\morphOne;

class Penjualan extends Model
{
    use CatatAktivitas;
    use HasUuids;

    protected $table = 'penjualan';

    protected $fillable = [
        'kode',
        'siswa_id',
        'user_id',
        'total',
        'pembayaran',
        'status',
    ];

    public function transaksi(): morphOne
    {
        return $this->morphOne(Transaksi::class, 'transable');
    }

    public function tagihan(): morphOne
    {
        return $this->morphOne(Tagihan::class, 'tagihanable');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function detail(): HasMany
    {
        return $this->HasMany(DetailPenjualan::class, 'penjualan_id');
    }
}
