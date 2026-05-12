<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        // Determine if login is email, phone, or ID number
        $login = $credentials['login'];
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'id_number';
        
        // For student, only use ID number; for others use email or phone
        $user = \App\Models\User::where($field, $login)->first();
        
        if (!$user) {
            return back()->withErrors([
                'login' => 'The provided credentials do not match our records.',
            ])->onlyInput('login');
        }

        // Attempt to login
        if (Auth::attempt([$field => $login, 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            
            // Redirect based on role
            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'));
            } elseif ($user->role === 'teacher') {
                return redirect()->intended(route('teacher.dashboard'));
            } elseif ($user->role === 'student') {
                return redirect()->intended(route('student.dashboard'));
            } elseif ($user->role === 'parent') {
                return redirect()->intended(route('parent.dashboard'));
            }
            
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'password' => 'The provided password is incorrect.',
        ])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }
}