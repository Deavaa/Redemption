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
            $user = Auth::user();

            // Check if account is active
            if (isset($user->is_active) && !$user->is_active) {
                Auth::logout();
                $r->session()->invalidate();
                throw ValidationException::withMessages(['email' => 'Your account has been deactivated. Please contact the administrator.']);
            }

            $r->session()->regenerate();

            // Redirect based on role
            return redirect()->intended($this->getHomeRoute($user));
        }
        throw ValidationException::withMessages(['email' => 'Invalid credentials. Please check your email and password.']);
    }

    public function logout(Request $r) {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect('/');
    }

    /**
     * Determine the home route based on user role.
     */
    private function getHomeRoute(User $user): string
    {
        // Admin, teacher, staff all go to admin dashboard
        $panelRoles = ['admin', 'teacher', 'staff', 'super_admin'];

        if (in_array($user->role, $panelRoles)) {
            return route('admin.dashboard');
        }

        // Check RBAC roles — if user has any role, they get admin panel access
        try {
            if ($user->roles()->exists()) {
                return route('admin.dashboard');
            }
        } catch (\Throwable $e) {}

        // Default: try admin dashboard (middleware will handle access control)
        return route('admin.dashboard');
    }
}
