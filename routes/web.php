<?php

use App\Modules\OrganizerManagement\Controllers\DashboardController;
use App\Modules\Ticketing\Controllers\TicketDetailsController;
use App\Modules\Ticketing\Controllers\TicketIndexController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', DashboardController::class)->name('dashboard')->middleware('auth');

Route::group([
    'prefix' => 'tickets',
    'as' => 'tickets.',
], function () {
    Route::get('/', [TicketIndexController::class, 'index'])
        ->name('index');

    Route::get('{ticket}', TicketDetailsController::class)
        ->name('show');
});

Route::group([
    'prefix' => 'events',
    'as' => 'events.',
], function () {
    Route::get('/', [\App\Modules\EventManagement\Controllers\EventIndexController::class, 'index'])
        ->name('index');

    Route::get('{event}', \App\Modules\EventManagement\Controllers\EventDetailsController::class)
        ->name('show');

    Route::delete('{event}/referral', \App\Modules\EventManagement\Controllers\RemoveReferralController::class)
        ->name('referral.remove');
});

// Orders routes (Public access for guest checkout)
Route::group([
    'prefix' => 'orders',
    'as' => 'orders.',
], function () {
    Route::post('store', [\App\Modules\Ordering\Controllers\OrderController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('store');

    Route::get('checkout/{order}', [\App\Modules\Ordering\Controllers\CheckoutController::class, 'checkout'])
        ->name('checkout');

    Route::get('{order}', [\App\Modules\Ordering\Controllers\OrderDetailsController::class, 'show'])
        ->name('show');

    Route::post('checkout/{order}/cancel', [\App\Modules\Ordering\Controllers\CheckoutController::class, 'cancel'])
        ->name('cancel');

    Route::post('checkout/{order}/payment-intent', \Domain\Ordering\Actions\CreatePaymentIntent::class)
        ->middleware(['auth'])
        ->name('payment-intent');

    Route::post('checkout/{order}/register', [\App\Modules\Ordering\Controllers\CheckoutAuthController::class, 'register'])
        ->name('register');
});

// organizer routes
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
        // create organizer
        Route::get('create', [\App\Modules\OrganizerManagement\Controllers\CreateOrganizerController::class, 'create'])
            ->name('create');

        Route::post('create', [\App\Modules\OrganizerManagement\Controllers\CreateOrganizerController::class, 'store'])
            ->name('create.store');

        Route::get('{organizer}', [\App\Modules\OrganizerManagement\Controllers\ManageOrganizations::class, 'show'])
            ->name('show');

        Route::get('{organizer}/events', [\App\Modules\OrganizerManagement\Controllers\ManageOrganizations::class, 'events'])
            ->name('events');

        Route::post('{organizer}/events', \Domain\EventManagement\Actions\CreateEvent::class)
            ->name('events.store');

        Route::get('{organizer}/cooperators', [\App\Modules\OrganizerManagement\Controllers\ManageOrganizations::class, 'cooperators'])
            ->name('cooperators');

        Route::get('{organizer}/settings', [\App\Modules\OrganizerManagement\Controllers\ManageOrganizations::class, 'settings'])
            ->name('settings');

        Route::put('{organizer}/settings', \Domain\OrganizerManagement\Actions\SaveSettings::class)
            ->name('settings.update');

        Route::post('{organizer}/taxes-and-fees', [\App\Modules\OrganizerManagement\Controllers\TaxAndFeeController::class, 'store'])
            ->name('taxes-and-fees.store');

        Route::put('{organizer}/taxes-and-fees/{taxAndFee}', [\App\Modules\OrganizerManagement\Controllers\TaxAndFeeController::class, 'update'])
            ->name('taxes-and-fees.update');

        Route::delete('{organizer}/taxes-and-fees/{taxAndFee}', [\App\Modules\OrganizerManagement\Controllers\TaxAndFeeController::class, 'destroy'])
            ->name('taxes-and-fees.destroy');

        // Route::get('{organizer}/mercadopago/vinculate', \Domain\OrganizerManagement\Actions\VinculateMercadoPagoAccount::class)
        //     ->name('mercadopago.vinculate');
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

        Route::get('{event}/analytics/sales', [\App\Modules\EventManagement\Controllers\ManageEventController::class, 'analyticsSales'])
            ->name('analytics.sales');

        Route::get('{event}/analytics/promoters', [\App\Modules\EventManagement\Controllers\ManageEventController::class, 'analyticsPromoters'])
            ->name('analytics.promoters');

        Route::get('{event}/products', [\App\Modules\EventManagement\Controllers\ManageEventController::class, 'products'])
            ->name('products');

        Route::post('{event}/products', \Domain\EventManagement\Actions\CreateEventProduct::class)
            ->name('products.store');

        Route::delete('{event}/products/{product}', \Domain\EventManagement\Actions\DeleteEventProduct::class)
            ->name('products.delete');

        Route::put('{event}/products/{product}', \Domain\EventManagement\Actions\EditEventProduct::class)
            ->name('products.update');

        Route::patch('{event}/products/{product}/sort', \Domain\EventManagement\Actions\SortEventProduct::class)
            ->name('products.sort');

        Route::post('{event}/products/{product}/duplicate', \Domain\EventManagement\Actions\DuplicateEventProduct::class)
            ->name('products.duplicate');

        Route::get('{event}/orders', [\App\Modules\EventManagement\Controllers\ManageEventController::class, 'orders'])
            ->name('orders');

        Route::get('{event}/promoters', [\App\Modules\EventManagement\Controllers\ManageEventController::class, 'promoters'])
            ->name('promoters');

        Route::post('{event}/promoters', \Domain\Promoters\Actions\InvitePromoter::class)
            ->name('promoters.store');

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

        Route::patch('{event}/promoters/{promoter}/enable', \Domain\Promoters\Actions\EnablePromoterForEvent::class)
            ->name('promoters.enable');

        Route::delete('{event}/promoters/{promoter}', \Domain\Promoters\Actions\RemovePromoterFromEvent::class)
            ->name('promoters.delete');

        Route::delete('{event}/promoters/invitations/{invitation}', \Domain\Promoters\Actions\DeletePromoterInvitation::class)
            ->name('promoters.invitations.delete');

        Route::get('{event}/promoters/{promoter}/stats', [\App\Modules\EventManagement\Controllers\ManageEventController::class, 'promoterStats'])
            ->name('promoters.stats');

        Route::patch('{event}/status', \Domain\EventManagement\Actions\UpdateEventStatus::class)
            ->name('status.update');

        Route::get('{event}/courtesies', \Domain\EventManagement\Actions\ManageCourtesies::class)
            ->name('courtesies');

        Route::post('{event}/courtesies', \Domain\Ticketing\Actions\CreateCourtesyTicket::class)
            ->name('courtesies.store');

        Route::delete('{event}/courtesies/bulk', \Domain\Ticketing\Actions\BulkDeleteCourtesyTickets::class)
            ->name('courtesies.bulk-delete');

        Route::delete('{event}/courtesies/{courtesy}', \Domain\Ticketing\Actions\DeleteCourtesyTicket::class)
            ->name('courtesies.delete');
    });

});

Route::group([
    'prefix' => 'promoters',
    'as' => 'promoters.',
], function () {
    Route::get('invitations/{token}', [\App\Modules\Promoters\Controllers\PromoterInvitationController::class, 'show'])->name('invitations.show');
    Route::get('dashboard', \App\Modules\Promoters\Controllers\PromoterDashboardController::class)->name('dashboard');
    Route::get('events/{event}/stats', \App\Modules\Promoters\Controllers\PromoterStatsController::class)->name('events.stats');
});

Route::group([
    'prefix' => 'user',
    'as' => 'user.',
    'middleware' => ['auth', 'verified'],
], function () { });

Route::group([
    'prefix' => 'promoters',
    'as' => 'promoters.',
    'middleware' => ['auth', 'verified'],
], function () {
    Route::post('invitations/{token}/accept', \Domain\Promoters\Actions\AcceptPromoterInvitation::class)->name('invitations.accept');
    Route::post('invitations/{token}/decline', \Domain\Promoters\Actions\DeclinePromoterInvitation::class)->name('invitations.decline');
});

Route::get('mercado-pago/callback', \App\Modules\OrganizerManagement\Controllers\MercadoPagoOAuthController::class)->middleware(['auth'])->name('mp.oauth');

require __DIR__.'/settings.php';
