<?php
// Deploy to: app/Http/Controllers/Admin/ImpersonationController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    /**
     * Start impersonating a teacher
     */
    public function start(Request $request, User $user)
    {
        // Security: Only admins can impersonate
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can impersonate.');
        }

        // Security: Only impersonate teachers or teaching assistants
        if (!in_array($user->user_type, ['teacher', 'teaching_assistant'])) {
            return back()->with('error', 'You can only impersonate teachers or teaching assistants.');
        }

        // Store original admin ID in session
        session([
            'impersonate_admin_id' => auth()->id(),
            'impersonate_teacher_id' => $user->id,
        ]);

        // Log the impersonation
        \Log::info('Admin impersonating teacher', [
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name,
            'teacher_id' => $user->id,
            'teacher_name' => $user->name,
        ]);

        // Log out current user and login as teacher
        Auth::logout();
        Auth::login($user);

        return redirect()->route('teacher.dashboard')
            ->with('success', "You are now viewing the portal as {$user->name}. Use 'Stop Impersonating' to return.");
    }

    /**
     * Stop impersonating and return to admin
     */
    public function stop()
    {
        $adminId = session('impersonate_admin_id');

        if (!$adminId) {
            return redirect()->route('admin.dashboard');
        }

        // Clear impersonation session
        session()->forget(['impersonate_admin_id', 'impersonate_teacher_id']);

        // Log out current user (teacher) and log back in as admin
        Auth::logout();
        Auth::loginUsingId($adminId);

        return redirect()->route('admin.dashboard')
            ->with('success', 'You have returned to your admin account.');
    }
}
