<?php

use Illuminate\Support\Facades\Route;
use E3DevelopmentSolutions\QuickBooks\Http\Controllers\QuickBooksAuthController;

// Protected routes
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/quickbooks/connect', [QuickBooksAuthController::class, 'connect'])
        ->name('quickbooks.connect');
    
    Route::post('/quickbooks/disconnect', [QuickBooksAuthController::class, 'disconnect'])
        ->middleware('csrf')
        ->name('quickbooks.disconnect');
});

// Public callback route (no auth middleware but still needs web middleware for session)
Route::middleware(['web'])->group(function () {
    Route::get('/quickbooks/callback', [QuickBooksAuthController::class, 'callback'])
        ->name('quickbooks.callback');
});
