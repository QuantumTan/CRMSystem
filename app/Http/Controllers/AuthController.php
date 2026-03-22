<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email|max:255',
            'password' => 'required',
            'role'     => 'required|in:admin,manager,sales',
        ]);

        if (Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'role' => $credentials['role'],
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            return match ($user?->role) {
                'admin' => redirect()->route('dashboard.admin'),
                'manager' => redirect()->route('dashboard.manager'),
                'sales' => redirect()->route('dashboard.sales'),
                default => redirect('/login')->withErrors(['role' => 'Unauthorized role.']),
            };
        }

        return back()->withErrors([
            'email' => 'Invalid credentials or role mismatch.',
        ])->onlyInput('email', 'role');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function profile()
    {
        $user = Auth::user();
        return view('auth.profile', compact('user'));
    }
}
