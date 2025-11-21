<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('events', [\App\Modules\EventManagement\Controllers\EventIndexController::class, 'index'])
    ->name('events.index');

Route::get('events/{event}', \App\Modules\EventManagement\Controllers\EventDetailsController::class)
    ->name('events.show');

require __DIR__ . '/settings.php';
