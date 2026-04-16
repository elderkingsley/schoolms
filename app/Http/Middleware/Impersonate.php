<?php
// Deploy to: app/Http/Middleware/Impersonate.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Impersonate
{
    public function handle(Request $request, Closure $next)
    {
        // Check if there's an impersonation session
        if (session()->has('impersonate_admin_id') && session()->has('impersonate_teacher_id')) {
            // Log in as the teacher
            Auth::loginUsingId(session('impersonate_teacher_id'));
        }

        return $next($request);
    }
}
