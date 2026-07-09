<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SettingService;

use App\Models\Institution;
use App\Observers\InstitutionObserver;
use App\Models\SalaryTemplateAllowance;
use App\Models\SalaryTemplateDeduction;
use App\Observers\SalaryTemplateChildObserver;
use App\Models\SalaryAdvanceRepayment;
use App\Observers\SalaryAdvanceRepaymentObserver;

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
       SalaryTemplateAllowance::observe(SalaryTemplateChildObserver::class);
       SalaryTemplateDeduction::observe(SalaryTemplateChildObserver::class);
       SalaryAdvanceRepayment::observe(SalaryAdvanceRepaymentObserver::class);
    }
}
