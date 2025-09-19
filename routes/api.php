<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;

// default bawaan laravel
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ✅ webhook Telegram, HARUS di sini (bukan di web.php)
Route::post('/telegram/webhook', [TelegramController::class, 'webhook']);
