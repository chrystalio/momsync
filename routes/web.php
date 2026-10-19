<?php

use App\Http\Controllers\GlucoseReadingController;
use App\Http\Controllers\Webhooks\TelegramWebhookController;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::resource('glucose-readings', GlucoseReadingController::class)
        ->except(['show', 'create', 'edit'])
        ->names('glucose-readings')
        ->parameters(['glucose-readings' => 'reading']);
});

Route::post('webhooks/telegram', [TelegramWebhookController::class, 'handle'])
    ->withoutMiddleware('web')
    ->middleware([SubstituteBindings::class])
    ->name('webhooks.telegram');

require __DIR__.'/settings.php';
