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
        ->name('store');

    Route::get('checkout/{order}', [\App\Modules\Ordering\Controllers\CheckoutController::class, 'checkout'])
        ->name('checkout');
});

Route::group([
    'prefix' => 'organizers',
    'as' => 'organizers.',
    'middleware' => ['auth', 'verified'],
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

Route::group([
    'prefix' => 'user',
    'as' => 'user.',
    'middleware' => ['auth', 'verified'],
], function () {});

require __DIR__.'/settings.php';
