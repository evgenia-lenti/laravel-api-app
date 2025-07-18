<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
       'message' => 'Welcome to Ferryscanner'
    ], 200);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

