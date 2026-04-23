<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        // Block inactive accounts immediately after login attempt
        if (! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Your account has been deactivated. Please contact the school administrator.',
            ]);
        }

        // Route each user type to their portal.
        // Special case: a teacher who also has a parent profile with active children
        // is sent to the parent portal — they can always navigate to their teacher
        // results page from there via the direct URL /teacher/results.
        if (in_array($user->user_type, ['teacher', 'teaching_assistant'])) {
            if ($user->parentProfile && $user->parentProfile->students()->where('status', 'active')->exists()) {
                return redirect()->intended(route('parent.dashboard'));
            }
        }

        return redirect()->intended(match($user->user_type) {
            'super_admin', 'admin' => route('admin.dashboard'),
            'teacher', 'teaching_assistant' => route('teacher.dashboard'),
            'accountant'           => route('accountant.dashboard'),
            'parent'               => route('parent.dashboard'),
            default                => route('login'),
        });
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
