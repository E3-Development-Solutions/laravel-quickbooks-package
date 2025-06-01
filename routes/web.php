<?php

use Illuminate\Support\Facades\Route;
use E3DevelopmentSolutions\QuickBooks\Http\Controllers\QuickBooksAuthController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/quickbooks/connect', [QuickBooksAuthController::class, 'connect'])
        ->name('quickbooks.connect');
    
    Route::post('/quickbooks/disconnect', [QuickBooksAuthController::class, 'disconnect'])
        ->name('quickbooks.disconnect');
});

// Callback route needs web middleware for session and should attempt auth
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/quickbooks/callback', [QuickBooksAuthController::class, 'callback'])
        ->name('quickbooks.callback')
        ->withoutMiddleware(['auth']); // Remove auth middleware just for this route but keep the session
});