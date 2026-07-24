<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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
        Route::pattern('id', '[0-9]+');

        Request::macro('isDebugDisabled', function (): bool {
            if (!config('app.debug')) return true;

            $hValue = $this->header('X-Disable-Debug');
            return $hValue && in_array(strtolower($hValue), ['true', '1'], true);
        });
    }
}
