<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordController extends Controller
{
    public function show()
    {
        return view('auth.change-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->numbers(),
            ],
        ]);

        $user = $request->user();

        $user->update([
            'password'              => Hash::make($request->password),
            'force_password_change' => false,
        ]);

        // Redirect to their appropriate dashboard
        return redirect()->intended(match($user->user_type) {
            'super_admin', 'admin' => route('admin.dashboard'),
            'teacher'              => route('teacher.dashboard'),
            'accountant'           => route('accountant.dashboard'),
            'parent'               => route('parent.dashboard'),
            default                => route('dashboard'),
        })->with('success', 'Password changed successfully. Welcome!');
    }
}
