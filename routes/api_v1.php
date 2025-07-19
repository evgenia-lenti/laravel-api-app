<?php

use App\Http\Controllers\Api\V1\ExchangeRateController;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Route;

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

// Protected API routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('exchange-rates', [ExchangeRateController::class, 'index'])->name('exchange-rates.index')->can('viewAny', ExchangeRate::class);
    Route::get('exchange-rates/{exchangeRate}', [ExchangeRateController::class, 'show'])->name('exchange-rates.show')->can('view', 'exchangeRate');
});
