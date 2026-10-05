<?php

namespace App\Models;

use App\Traits\CatatAktivitas;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaksi extends Model
{
    use CatatAktivitas;
    use HasUuids;

    protected $table = 'transaksi';

    protected $fillable = ['transable_type', 'transable_id', 'jumlah', 'keterangan', 'user_id', 'kode'];

    public function transable()
    {
        return $this->morphTo();
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jenis()
    {
        return substr($this->transable_type, 11);
    }
}
