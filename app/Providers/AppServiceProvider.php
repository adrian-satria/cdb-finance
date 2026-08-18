<?php

namespace App\Providers;

use App\Models\LpjUangMuka;
use App\Models\PengajuanUangMuka;
use App\Models\ReimburseLpj;
use App\Models\SuratPermintaan;
use App\Observers\AdvanceObserver;
use App\Observers\LpjObserver;
use App\Observers\ReimburseObserver;
use App\Observers\SppObserver;
use App\Services\NotificationService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NotificationService::class);
    }

    public function boot(): void
    {
        SuratPermintaan::observe(SppObserver::class);
        PengajuanUangMuka::observe(AdvanceObserver::class);
        LpjUangMuka::observe(LpjObserver::class);
        ReimburseLpj::observe(ReimburseObserver::class);
        Paginator::useBootstrapFive();
    }
}
