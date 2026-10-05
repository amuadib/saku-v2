<?php

namespace App\Traits;

use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Request;

trait CatatAktivitas
{
    /**
     * Set to true to disable logging for the current model instance.
     */
    public $disableLogging = false;

    public static function bootCatatAktivitas()
    {
        static::created(function ($model) {
            if ($model->disableLogging) return;

            LogAktivitas::create([
                'user_id' => auth()->id(),
                'model' => get_class($model),
                'model_id' => $model->getKey(),
                'aksi' => 'create',
                'data_lama' => null,
                'data_baru' => $model->getAttributes(),
                'ip_address' => Request::ip(),
            ]);
        });

        static::updated(function ($model) {
            if ($model->disableLogging) return;

            // Only log if there are actual changes
            if (!empty($model->getChanges())) {
                $dataLama = [];
                $dataBaru = [];
                
                // Get the changed attributes
                foreach ($model->getChanges() as $key => $value) {
                    // Ignore updated_at column
                    if ($key === $model->getUpdatedAtColumn()) continue;

                    $dataLama[$key] = $model->getOriginal($key);
                    $dataBaru[$key] = $value;
                }

                if (!empty($dataLama)) {
                    LogAktivitas::create([
                        'user_id' => auth()->id(),
                        'model' => get_class($model),
                        'model_id' => $model->getKey(),
                        'aksi' => 'update',
                        'data_lama' => $dataLama,
                        'data_baru' => $dataBaru,
                        'ip_address' => Request::ip(),
                    ]);
                }
            }
        });

        static::deleted(function ($model) {
            if ($model->disableLogging) return;

            LogAktivitas::create([
                'user_id' => auth()->id(),
                'model' => get_class($model),
                'model_id' => $model->getKey(),
                'aksi' => 'delete',
                'data_lama' => $model->getAttributes(),
                'data_baru' => null,
                'ip_address' => Request::ip(),
            ]);
        });
    }
}
