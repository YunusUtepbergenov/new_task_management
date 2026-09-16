<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordNotExpired
{
    /**
     * Force users with an expired password to change it before using the app.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->passwordExpired() && ! $this->isAllowed($request)) {
            return redirect()->route('settings')->with('password_expired', true);
        }

        return $next($request);
    }

    /**
     * Routes the user may still reach while their password is expired,
     * so they can actually change it (settings page + its Livewire calls), log out, or switch language.
     *
     * Livewire endpoints are matched by route name because Livewire 4 serves them
     * under a hashed prefix (e.g. "livewire-95f8ed9f/update"), not "livewire/*".
     */
    private function isAllowed(Request $request): bool
    {
        return $request->routeIs(
            'settings',
            'logout',
            'locale.switch',
            '*livewire.update',
            'livewire.upload-file',
            'livewire.preview-file',
        );
    }
}
