<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

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
        // Локаль устанавливается в SetLocale middleware
        // Не устанавливаем здесь, чтобы не перезаписывать middleware
        // Carbon::setLocale будет установлен в SetLocale middleware вместе с app()->setLocale()
    }
}
