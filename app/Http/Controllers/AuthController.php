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

        $login = $r->login;
        $password = $r->password;
        
        // Try to find user by email, id_number, or phone
        $user = User::where('email', $login)
            ->orWhere('id_number', $login)
            ->orWhere('phone', $login)
            ->first();
        
        if (!$user) {
            throw ValidationException::withMessages(['login' => 'Invalid credentials. Please check your login details.']);
        }
        
        // Check password
        if (!Hash::check($password, $user->password)) {
            throw ValidationException::withMessages(['login' => 'Invalid credentials. Please check your password.']);
        }
        
        // Check if account is active
        if (isset($user->is_active) && !$user->is_active) {
            Auth::logout();
            $r->session()->invalidate();
            throw ValidationException::withMessages(['login' => 'Your account has been deactivated. Please contact the administrator.']);
        }
        
        // Log in the user
        Auth::login($user, $r->boolean('remember'));
        $r->session()->regenerate();
        
        // Redirect based on role
        return redirect()->intended($this->getHomeRoute($user));
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
        // Students go to student portal
        if ($user->role === 'student') {
            return route('student.dashboard');
        }

        // Parents go to parent portal
        if ($user->role === 'parent') {
            return route('parent.dashboard');
        }

        // Admin, teacher, staff all go to admin dashboard
        $panelRoles = ['admin', 'teacher', 'staff', 'super_admin', 'branch_principal', 'general_manager', 'librarian', 'cashier', 'registrar', 'finance', 'hr'];

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
