<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Telefono;
use App\Observers\TelefonoObserver;
use App\Models\Accesorio;
use App\Observers\AccesorioObserver;

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
        Telefono::observe(TelefonoObserver::class);
        Accesorio::observe(AccesorioObserver::class);
    }
}
