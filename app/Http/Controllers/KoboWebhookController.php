<?php

namespace App\Http\Controllers;

use App\Services\WebhookLogger;
use Illuminate\Http\Request;

class KoboWebhookController extends Controller
{
    /**
     * Handle an incoming Kobo webhook request.
     *
     * @return bool
     */
    public function __invoke(Request $request)
    {
        $payload = $request->all();

        return WebhookLogger::handle($payload, 'kobo');
    }
}
