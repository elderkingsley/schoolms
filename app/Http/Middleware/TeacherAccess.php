<?php
// Deploy to: app/Http/Middleware/TeacherAccess.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TeacherAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // Allow if user is teacher/TA OR if admin is impersonating
        if ($user && (in_array($user->user_type, ['teacher', 'teaching_assistant']) || session()->has('impersonate_admin_id'))) {
            return $next($request);
        }

        abort(403, 'Access denied. Teacher or admin impersonation required.');
    }
}
