<?php

namespace App\Models;

use App\Traits\CatatAktivitas;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Barang extends Model
{
    use CatatAktivitas;
    use HasFactory, HasUuids, LogsActivity;

    protected $table = 'barang';

    protected $casts = [
        'varian' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logExcept([
                'id',
                'varian',
                'created_at',
                'updated_at',
            ]);
    }
}
