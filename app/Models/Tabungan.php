<?php

namespace App\Models;

use App\Traits\CatatAktivitas;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tabungan extends Model
{
    use CatatAktivitas;
    use HasUuids;

    protected $table = 'tabungan';

    protected $guarded = [];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kas(): BelongsTo
    {
        return $this->belongsTo(Kas::class);
    }

    public function transaksi()
    {
        return $this->morphMany(Transaksi::class, 'transable');
    }
}
