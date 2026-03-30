<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * If the authenticated user has force_password_change = true,
     * redirect them to the change-password page before they can do anything else.
     *
     * The change-password route itself is excluded so they don't loop.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->user() &&
            $request->user()->force_password_change &&
            ! $request->routeIs('password.change') &&
            ! $request->routeIs('password.change.update') &&
            ! $request->routeIs('logout')
        ) {
            return redirect()->route('password.change')
                ->with('info', 'Please change your password before continuing.');
        }

        return $next($request);
    }
}
