<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTelegramApiSecret
{
    /**
     * Only let through callers (the Telegram bot service) that send the shared API secret.
     * Fails closed: if no secret is configured, every request is rejected.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) config('services.telegram.api_secret');
        $provided = (string) $request->header('X-Telegram-Api-Secret');

        if ($secret === '' || ! hash_equals($secret, $provided)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
