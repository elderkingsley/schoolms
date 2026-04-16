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
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        // Security: Only impersonate teachers
        if (!$user->isTeacher()) {
            return back()->with('error', 'You can only impersonate teachers.');
        }

        // Store admin ID in session to return later
        session([
            'impersonate_admin_id' => auth()->id(),
            'impersonate_teacher_id' => $user->id,
        ]);

        // Log the impersonation (optional but recommended)
        \Log::info('Admin impersonating teacher', [
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name,
            'teacher_id' => $user->id,
            'teacher_name' => $user->name,
        ]);

        return redirect()->route('teacher.dashboard')
            ->with('success', "You are now viewing as {$user->name}. Click 'Stop Impersonating' to return to admin.");
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

        // Log back in as admin
        Auth::loginUsingId($adminId);

        return redirect()->route('admin.dashboard')
            ->with('success', 'You have returned to your admin account.');
    }
}
