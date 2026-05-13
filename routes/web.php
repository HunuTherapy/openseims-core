<?php

use App\Http\Controllers\KoboWebhookController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

Route::post('webhook/kobo', KoboWebhookController::class)
    ->withoutMiddleware(VerifyCsrfToken::class);
