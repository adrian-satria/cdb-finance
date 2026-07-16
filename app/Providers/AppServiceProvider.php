<?php

namespace App\Providers;

use App\Models\SuratPermintaan;
use App\Observers\SppObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        SuratPermintaan::observe(SppObserver::class);
        Paginator::useBootstrapFive();
    }
}
