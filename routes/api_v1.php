<?php

use App\Http\Controllers\Api\V1\ExchangeRateController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Route;

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

//Public API routes (no authentication required)
Route::post('register', [RegisteredUserController::class, 'store'])
    ->middleware('guest')
    ->name('register');

Route::post('/login', [AuthenticatedSessionController::class, 'apiLogin'])
    ->middleware('guest')
    ->name('login');


//Protected API routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'apiLogout'])->name('logout');
    Route::get('exchange-rates', [ExchangeRateController::class, 'index'])->name('exchange-rates.index')->can('viewAny', ExchangeRate::class);
    Route::get('exchange-rates/{exchangeRate}', [ExchangeRateController::class, 'show'])->name('exchange-rates.show')->can('view', 'exchangeRate');
});
