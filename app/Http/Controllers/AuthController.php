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
            'email' => 'required|email|max:255',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials,$request->boolean('remember'))){
            return back()->withErrors([
                'email'=>'Invalid Credentials.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();


        $user = Auth::user();

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
