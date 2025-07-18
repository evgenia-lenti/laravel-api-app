<?php

use App\Http\Controllers\Api\V1\ExchangeRateController;
use Illuminate\Support\Facades\Route;

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

// Protected API routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/exchange-rates', [ExchangeRateController::class, 'index']);
    Route::get('/exchange-rates/{exchangeRate}', [ExchangeRateController::class, 'show']);
});
