<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Domain\Ordering\Events\OrderCompleted;
use Domain\Ticketing\Listeners\GenerateTicketsForOrder;
use Illuminate\Support\Facades\Event;
use Inertia\Inertia;

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
        Inertia::share([
            'locale' => fn() => App::getLocale()
        ]);
    }
}
