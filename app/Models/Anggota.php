<?php

namespace App\Models;

use App\Traits\CatatAktivitas;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    use CatatAktivitas;
    use HasUuids;
    protected $table = 'anggota';
}
