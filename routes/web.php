<?php

use App\Http\Controllers\GlucoseReadingController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::resource('glucose-readings', GlucoseReadingController::class)
        ->except(['show', 'create', 'edit'])
        ->names('glucose-readings')
        ->parameters(['glucose-readings' => 'reading']);
});

require __DIR__.'/settings.php';
