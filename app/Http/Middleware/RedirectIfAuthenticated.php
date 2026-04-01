<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * RedirectIfAuthenticated
 *
 * Overrides Laravel's default which redirects to /home (which doesn't exist).
 * Instead we redirect to each user type's portal — the same logic used in
 * AuthenticatedSessionController::store() after login.
 *
 * This fixes the issue where a logged-in user visiting /login would be sent
 * to /home → 404, leaving them stuck until they cleared their cookies.
 */
class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect($this->dashboardFor(Auth::guard($guard)->user()));
            }
        }

        return $next($request);
    }

    private function dashboardFor(\App\Models\User $user): string
    {
        return match($user->user_type) {
            'super_admin', 'admin' => route('admin.dashboard'),
            'teacher'              => route('teacher.dashboard'),
            'teaching_assistant'   => route('teacher.dashboard'),
            'accountant'           => route('accountant.dashboard'),
            'parent'               => route('parent.dashboard'),
            default                => route('admin.dashboard'),
        };
    }
}
