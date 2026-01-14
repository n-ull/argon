<?php

namespace App\Providers;

use Domain\Ordering\Events\OrderCompleted;
use Domain\Ticketing\Listeners\GenerateTicketsForOrder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\Domain\Ticketing\Support\TokenGenerator::class, function ($app) {
            return new \Domain\Ticketing\Support\TokenGenerator();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            OrderCompleted::class,
            GenerateTicketsForOrder::class
        );

        Inertia::share([
            'locale' => fn() => App::getLocale(),
        ]);
    }
}
