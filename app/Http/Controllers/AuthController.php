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
        // Check which columns actually exist to avoid QueryException
        try {
            $hasIdNumber = \Schema::hasColumn('users', 'id_number');
            $hasPhone = \Schema::hasColumn('users', 'phone');
        } catch (\Throwable $e) {
            $hasIdNumber = false;
            $hasPhone = false;
        }
        
        $query = User::where('email', $login);
        if ($hasIdNumber) {
            $query->orWhere('id_number', $login);
        }
        if ($hasPhone) {
            $query->orWhere('phone', $login);
        }
        $user = $query->first();
        
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

    // ── Forgot Password Flow ──────────────────────────────────

    /**
     * Show forgot password form.
     */
    public function showForgotPassword()
    {
        return redirect()->route('login')->with('show_forgot', true);
    }

    /**
     * Find user account by email or id_number.
     */
    public function submitForgotPassword(Request $r)
    {
        $r->validate(['login' => 'required']);

        $login = $r->login;

        // Find user by email or id_number
        $query = User::where('email', $login);
        try {
            if (\Schema::hasColumn('users', 'id_number')) {
                $query->orWhere('id_number', $login);
            }
        } catch (\Throwable $e) {}

        $user = $query->first();

        if (!$user) {
            throw ValidationException::withMessages(['login' => 'No account found with that email or ID number.']);
        }

        // If user has a security question, show it
        if (!empty($user->security_question) && !empty($user->security_answer)) {
            return redirect()->route('login')
                ->with('show_security', true)
                ->with('security_email', $user->email)
                ->with('security_question', $user->security_question);
        }

        // No security question — go directly to password reset
        return redirect()->route('login')
            ->with('show_reset_form', true)
            ->with('reset_email', $user->email)
            ->with('reset_user_name', $user->name);
    }

    /**
     * Verify security answer.
     */
    public function verifySecurityAnswer(Request $r)
    {
        $r->validate([
            'email' => 'required|email',
            'security_answer' => 'required',
        ]);

        $user = User::where('email', $r->email)->first();

        if (!$user) {
            throw ValidationException::withMessages(['security_answer' => 'Account not found.']);
        }

        if (!Hash::check($r->security_answer, $user->security_answer)) {
            return redirect()->route('login')
                ->with('show_security', true)
                ->with('security_email', $user->email)
                ->with('security_question', $user->security_question)
                ->withErrors(['security_answer' => 'Incorrect answer. Please try again.']);
        }

        return redirect()->route('login')
            ->with('show_reset_form', true)
            ->with('reset_email', $user->email)
            ->with('reset_user_name', $user->name);
    }

    /**
     * Reset the password.
     */
    public function submitResetPassword(Request $r)
    {
        $r->validate([
            'email' => 'required|email',
            'password' => 'required|min:4|confirmed',
        ]);

        $user = User::where('email', $r->email)->first();

        if (!$user) {
            throw ValidationException::withMessages(['password' => 'Account not found.']);
        }

        $user->update(['password' => Hash::make($r->password)]);

        return redirect()->route('login')
            ->with('reset_success', 'Password reset successfully! You can now log in with your new password.');
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
