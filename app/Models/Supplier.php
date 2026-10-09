<?php

namespace App\Models;

use App\Traits\CatatAktivitas;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use CatatAktivitas;
    use HasUuids;

    protected $table = 'supplier';

    public $timestamps = false;
}
