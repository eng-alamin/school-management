<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SettingService;
use App\Models\Institution;
use App\Observers\InstitutionObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('setting', function () {
            return new SettingService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       Institution::observe(InstitutionObserver::class);
    }
}
