<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin(Request $request) {
        // If already authenticated, send the user to their home dashboard.
        if (Auth::check()) {
            return redirect()->intended($this->getHomeRoute(Auth::user()));
        }

        // If there's a redirect parameter, store it in the session so
        // redirect()->intended() can use it after login.
        if ($request->has('redirect')) {
            $redirectUrl = $request->redirect;

            // Validate: only allow redirects to our own domain to prevent open redirect attacks
            // Accept both full URLs (https://localhost/...) and relative paths (/admin/...)
            if (filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
                // Full URL — verify it belongs to our app domain
                $appHost = parse_url(config('app.url'), PHP_URL_HOST);
                $redirectHost = parse_url($redirectUrl, PHP_URL_HOST);
                if ($redirectHost && $redirectHost === $appHost) {
                    session(['url.intended' => $redirectUrl]);
                }
            } else {
                // Relative path — store as-is (Laravel will resolve it against APP_URL)
                session(['url.intended' => $redirectUrl]);
            }
        }

        // Regenerate the CSRF token for a fresh login page.
        $request->session()->regenerateToken();

        return response()->view('auth.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function login(Request $r) {
        $r->validate(['login'=>'required','password'=>'required']);

        $login = $r->login;
        $password = $r->password;
        Log::debug('AuthController@login attempt', [
            'login' => $login,
            'remember' => $r->boolean('remember'),
            'session_id' => $r->session()->getId(),
            'csrf_token' => $r->input('_token'),
        ]);
        
        // Normalize phone number: strip country code (+251, 251), remove spaces/dashes
        // Format: 0900000000 (10 digits starting with 0)
        $normalizedPhone = $this->normalizePhone($login);
        
        // Try to find user by email, employee_id, id_number, or phone
        // Check which columns actually exist to avoid QueryException
        try {
            $hasIdNumber = \Schema::hasColumn('users', 'id_number');
            $hasPhone = \Schema::hasColumn('users', 'phone');
            $hasEmployeeId = \Schema::hasColumn('users', 'employee_id');
        } catch (\Throwable $e) {
            $hasIdNumber = false;
            $hasPhone = false;
            $hasEmployeeId = false;
        }
        
        $query = User::where('email', $login);
        if ($hasEmployeeId) {
            $query->orWhere('employee_id', $login);
        }
        if ($hasIdNumber) {
            $query->orWhere('id_number', $login);
        }
        if ($hasPhone) {
            // Try both raw login and normalized phone
            $query->orWhere('phone', $login);
            if ($normalizedPhone !== $login) {
                $query->orWhere('phone', $normalizedPhone);
            }
        }
        $user = $query->first();
        
        if (!$user) {
            Log::warning('AuthController@login failed: user not found', ['login' => $login]);
            return view('auth.login')
                ->with('login', $login)
                ->withErrors(['login' => 'Invalid credentials. Please check your login details.'])
                ->with('error', 'Invalid credentials. Please check your login details.');
        }
        Log::debug('AuthController@login user found', ['user_id' => $user->id, 'login' => $login]);
        
        // Check password
        if (!Hash::check($password, $user->password)) {
            Log::warning('AuthController@login failed: wrong password', ['user_id' => $user->id, 'login' => $login]);
            return view('auth.login')
                ->with('login', $login)
                ->withErrors(['login' => 'Invalid credentials. Please check your password.'])
                ->with('error', 'Invalid credentials. Please check your password.');
        }
        
        // Check if account is active
        if (isset($user->is_active) && !$user->is_active) {
            Log::warning('AuthController@login blocked: account inactive', ['user_id' => $user->id, 'login' => $login]);
            Auth::logout();
            $r->session()->invalidate();
            return view('auth.login')
                ->with('login', $login)
                ->withErrors(['login' => 'Your account has been deactivated. Please contact the administrator.'])
                ->with('error', 'Your account has been deactivated. Please contact the administrator.');
        }
        
        // Log in the user
        Auth::login($user, $r->boolean('remember'));
        Log::info('AuthController@login success', ['user_id' => $user->id, 'login' => $login]);

        // ── IMPORTANT: Read the redirect URL BEFORE regenerating the session.
        // Session regeneration creates a new session ID and migrates data, but
        // we want to be explicit about preserving the redirect URL.
        // Priority: POST input > session 'url.intended'
        $redirectUrl = $r->input('redirect') ?: $r->session()->get('url.intended');

        $r->session()->regenerate();

        // Force-save the session to database immediately (prevents session loss)
        $r->session()->save();

        // Validate redirect URL — prevent redirect to login page or external sites
        if ($redirectUrl) {
            // If it's a full URL, verify it belongs to our app
            if (filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
                $appHost = parse_url(config('app.url'), PHP_URL_HOST);
                $redirectHost = parse_url($redirectUrl, PHP_URL_HOST);
                if (!$redirectHost || $redirectHost !== $appHost) {
                    $redirectUrl = null; // Not our domain — ignore
                }
            }

            // Don't redirect back to login page
            $loginPath = '/login';
            if (str_ends_with(parse_url($redirectUrl, PHP_URL_PATH) ?? '', $loginPath)) {
                $redirectUrl = null;
            }
        }

        // ── Use redirect()->away() for FULL URLs to prevent XAMPP double-path 404.
        // On XAMPP with subdirectory (e.g. /Redemption/public/), redirect()->intended()
        // may prepend APP_URL to an already-full URL, creating:
        //   /Redemption/public/https://localhost/Redemption/public/admin/mark-entries
        // which results in a 404. redirect()->away() uses the URL as-is.
        if ($redirectUrl && filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
            // Full URL — use away() to prevent path manipulation
            return redirect()->away($redirectUrl);
        }

        // Relative path or no redirect — use intended() with fallback
        if ($redirectUrl) {
            session(['url.intended' => $redirectUrl]);
        }

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
     * Normalize a phone number to Ethiopian local format: 0900000000
     * - Removes spaces, dashes, parentheses
     * - Strips country code prefix (+251, 00251, 251) and prepends 0
     * - Returns the original string if it doesn't look like a phone number
     */
    private function normalizePhone(string $input): string
    {
        // Remove all spaces, dashes, parentheses, dots
        $cleaned = preg_replace('/[\s\-().]/', '', $input);
        
        // If starts with +251 or 00251, replace with 0
        if (preg_match('/^(\+251|00251)(\d{9})$/', $cleaned, $m)) {
            return '0' . $m[2];
        }
        
        // If starts with 251 (without + or 00), replace with 0
        if (preg_match('/^251(\d{9})$/', $cleaned, $m)) {
            return '0' . $m[1];
        }
        
        // If already in 0XXXXXXXXX format (10 digits starting with 0)
        if (preg_match('/^0\d{9}$/', $cleaned)) {
            return $cleaned;
        }
        
        // Return cleaned input as-is (may be email or ID number)
        return $cleaned;
    }

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
