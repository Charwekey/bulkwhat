<?php

use App\Http\Controllers\UltraMsgWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhook/ultramsg', [UltraMsgWebhookController::class, 'handle'])
    ->name('webhook.ultramsg');
