<?php

use App\Modules\OrganizerManagement\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', DashboardController::class)->name('dashboard');

Route::group([
    'prefix' => 'events',
    'as' => 'events.',
], function () {
    Route::get('/', [\App\Modules\EventManagement\Controllers\EventIndexController::class, 'index'])
        ->name('index');

    Route::get('{event}', \App\Modules\EventManagement\Controllers\EventDetailsController::class)
        ->name('show');
});

Route::group([
    'prefix' => 'orders',
    'as' => 'orders.',
    'middleware' => ['auth', 'verified'],
], function () {
    Route::post('store', [\App\Modules\Ordering\Controllers\OrderController::class, 'store'])
        // ->middleware('throttle:5,1')
        ->name('store');

    Route::get('checkout/{order}', [\App\Modules\Ordering\Controllers\CheckoutController::class, 'checkout'])
        ->name('checkout');

    Route::post('checkout/{order}/cancel', [\App\Modules\Ordering\Controllers\CheckoutController::class, 'cancel'])
        ->name('cancel');
});

// organizer routes
// TODO: change o to organizer and separate the routes from event to manage 
Route::group([
    'prefix' => 'manage',
    'as' => 'manage.',
    'middleware' => ['auth', 'verified'],
], function () {
    // manage organizer
    Route::group([
        'prefix' => 'organizer',
        'as' => 'organizer.',
    ], function () {
        Route::get('/', [\App\Modules\OrganizerManagement\Controllers\ManageOrganizations::class, 'index'])
            ->name('index');

        Route::get('create', [\App\Modules\OrganizerManagement\Controllers\CreateOrganizerController::class, 'create'])
            ->name('create');

        Route::post('store', [\App\Modules\OrganizerManagement\Controllers\CreateOrganizerController::class, 'store'])
            ->name('store');

        Route::get('{organizer}', [\App\Modules\OrganizerManagement\Controllers\ManageOrganizations::class, 'show'])
            ->name('show');
    });

    // manage event
    Route::group([
        'prefix' => 'event',
        'as' => 'event.',
    ], function () {
        Route::get('{event}', [\App\Modules\EventManagement\Controllers\ManageEventController::class, 'dashboard'])
            ->name('dashboard');

        Route::get('{event}/analyitics', [\App\Modules\EventManagement\Controllers\ManageEventController::class, 'analytics'])
            ->name('analytics');

        Route::get('{event}/products', [\App\Modules\EventManagement\Controllers\ManageEventController::class, 'products'])
            ->name('products');

        Route::get('{event}/orders', [\App\Modules\EventManagement\Controllers\ManageEventController::class, 'orders'])
            ->name('orders');

        Route::get('{event}/promoters', [\App\Modules\EventManagement\Controllers\ManageEventController::class, 'promoters'])
            ->name('promoters');

        Route::get('{event}/settings', [\App\Modules\EventManagement\Controllers\ManageEventController::class, 'settings'])
            ->name('settings');
        Route::post('{event}/settings', \App\Modules\EventManagement\Controllers\UpdateEventSettingsController::class)
            ->name('settings.update');

        Route::get('{event}/attendees', [\App\Modules\EventManagement\Controllers\ManageEventController::class, 'attendees'])
            ->name('attendees');

        Route::get('{event}/doormen', [\App\Modules\EventManagement\Controllers\ManageEventController::class, 'doormen'])
            ->name('doormen');

        Route::get('{event}/vouchers', [\App\Modules\EventManagement\Controllers\ManageEventController::class, 'vouchers'])
            ->name('vouchers');
    });

});

Route::group([
    'prefix' => 'user',
    'as' => 'user.',
    'middleware' => ['auth', 'verified'],
], function () { });

require __DIR__ . '/settings.php';
