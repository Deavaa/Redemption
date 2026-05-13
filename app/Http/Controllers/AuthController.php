<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }

    public function login(Request $r) {
        $r->validate(['login'=>'required','password'=>'required']);
        
        // Determine if login is email or not
        $loginField = filter_var($r->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'email';
        $credentials = [$loginField => $r->login, 'password' => $r->password];
        
        if (Auth::attempt($credentials, $r->boolean('remember'))) {
            $r->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }
        throw ValidationException::withMessages(['email' => 'Invalid credentials. Please check your email and password.']);
    }

    public function logout(Request $r) {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect('/');
    }
}