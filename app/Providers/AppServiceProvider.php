<?php

namespace App\Providers;

use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        try {
            if (Schema::hasTable('settings')) {
                $dbSettings = Cache::rememberForever('custom_settings', function () {
                    return Setting::pluck('payload', 'key')->toArray();
                });

                $customConfig = config('custom', []);

                foreach ($dbSettings as $key => $value) {
                    if (isset($customConfig[$key]) && is_array($customConfig[$key]) && is_array($value)) {
                        $customConfig[$key] = array_replace_recursive($customConfig[$key], $value);
                    } else {
                        $customConfig[$key] = $value;
                    }
                }
                config(['custom' => $customConfig]);
            }
        } catch (\Exception $e) {
            // Ignore if DB is not ready during migrations
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
