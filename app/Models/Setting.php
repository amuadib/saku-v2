<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $table = 'settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
    ];

    protected static function booted()
    {
        static::saved(function ($model) {
            Cache::forget('custom_settings');
        });

        static::deleted(function ($model) {
            Cache::forget('custom_settings');
        });
    }
}
