<?php

namespace App\Providers;

use App\Models\SuratPermintaan;
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
        Paginator::useBootstrapFive();
    }
}
