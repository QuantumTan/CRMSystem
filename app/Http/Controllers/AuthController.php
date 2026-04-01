<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function profile()
    {
        $user = Auth::user();

        return view('auth.profile', compact('user'));
    }
}
