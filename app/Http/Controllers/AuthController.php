<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use App\Notifications\AdminResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\URL;

class AuthController extends Controller
{
    public function showLogin(Request $request) {
        // If already authenticated, send the user to their home dashboard.
        if (Auth::check()) {
            return redirect()->intended($this->getHomeRoute(Auth::user()));
        }

        // ── LOOP BUG FIX ──────────────────────────────────────────────────
        // Simplified: just regenerate the token. Don't invalidate+regenerate
        // which can cause issues if the session file can't be written.
        // ──────────────────────────────────────────────────────────────────
        try {
            $request->session()->regenerateToken();
        } catch (\Throwable $e) {
            Log::warning('AuthController@showLogin: token regeneration failed', [
                'error' => $e->getMessage(),
            ]);
        }

        // If there's a redirect parameter, store it in the session so
        // redirect()->intended() can use it after login.
        if ($request->has('redirect')) {
            $redirectUrl = $request->redirect;

            // Validate and normalize the redirect URL
            $validatedRedirect = $this->validateRedirectUrl($redirectUrl);
            if ($validatedRedirect) {
                session(['url.intended' => $validatedRedirect]);
            }
        }

        // Regenerate the CSRF token for a fresh login page.
        $request->session()->regenerateToken();

        // Note: session->save() is NOT needed here — Laravel saves the session
        // automatically at the end of the request via middleware.
        // Calling save() manually can fail if the session directory isn't writable,
        // which causes the login loop.

        return response()->view('auth.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function login(Request $r) {
        $r->validate(['login'=>'required','password'=>'required']);

        $login = $r->login;
        $password = $r->password;
        // SECURITY: Do NOT log PII (login identifier) or CSRF token.
        // These were previously written to laravel.log which is a leak risk.
        Log::info('AuthController@login attempt', [
            'remember' => $r->boolean('remember'),
            'ip' => $r->ip(),
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
            Log::warning('AuthController@login failed: user not found', ['ip' => $r->ip()]);
            // Reuse the same view-render helper so we always send no-store
            // headers (prevents the browser from caching the login form with
            // a stale CSRF token, which would cause the loop bug).
            return $this->renderLoginViewWithNoStoreHeaders([
                'login' => $login,
                'error' => 'Invalid credentials. Please try again.',
                'errors' => ['login' => 'Invalid credentials. Please try again.'],
            ]);
        }

        // Check password
        if (!Hash::check($password, $user->password)) {
            Log::warning('AuthController@login failed: wrong password', ['user_id' => $user->id, 'ip' => $r->ip()]);
            return $this->renderLoginViewWithNoStoreHeaders([
                'login' => $login,
                'error' => 'Invalid credentials. Please try again.',
                'errors' => ['login' => 'Invalid credentials. Please try again.'],
            ]);
        }

        // Check if account is active
        if (isset($user->is_active) && !$user->is_active) {
            Log::warning('AuthController@login blocked: account inactive', ['user_id' => $user->id, 'ip' => $r->ip()]);
            Auth::logout();
            $r->session()->invalidate();
            return $this->renderLoginViewWithNoStoreHeaders([
                'login' => $login,
                'error' => 'Your account has been deactivated. Please contact the administrator.',
                'errors' => ['login' => 'Your account has been deactivated. Please contact the administrator.'],
            ]);
        }

        // Log in the user
        Auth::login($user, $r->boolean('remember'));
        Log::info('AuthController@login success', ['user_id' => $user->id, 'ip' => $r->ip()]);

        // ── IMPORTANT: Read the redirect URL BEFORE regenerating the session.
        $redirectUrl = $r->input('redirect') ?: $r->session()->get('url.intended');

        // Regenerate session ID to prevent session fixation
        $r->session()->regenerate();

        // Note: session->save() removed — Laravel saves automatically at end of request.
        // Manual save() can fail on XAMPP if storage/framework/sessions isn't writable,
        // which causes the session to not persist and the login loop.

        // Validate and normalize the redirect URL
        $validatedRedirect = $this->validateRedirectUrl($redirectUrl);

        // ── Redirect strategy: always convert to a RELATIVE path for maximum
        // compatibility across XAMPP subdirectories, live domains, and mobile WebView.
        // Using relative paths avoids the double-path 404 bug where Laravel prepends
        // APP_URL to an already-full URL.
        if ($validatedRedirect) {
            // Store as intended URL — redirect()->intended() handles it correctly
            // whether it's relative (/admin/...) or full URL
            session(['url.intended' => $validatedRedirect]);
        }

        return redirect()->intended($this->getHomeRoute($user));
    }

    public function logout(Request $r) {
        // Aggressive logout — clears Auth, session, AND cookies to prevent
        // the login-loop bug where a stale session cookie keeps redirecting
        // back to /login after the user has already logged out.
        Auth::logout();
        try {
            $r->session()->invalidate();
            $r->session()->regenerateToken();
        } catch (\Throwable $e) {
            // Session may already be gone — keep going
        }

        // Clear the session cookie entirely (forces browser to drop it)
        $cookieName = config('session.cookie', 'redemption_session_v5');
        try {
            cookie()->queue(cookie()->forget($cookieName));
            // Also clear the remember_web_* cookie if present
            cookie()->queue(cookie()->forget(\Illuminate\Support\Str::slug(config('auth.defaults.guard', 'web')) . '_remember'));
        } catch (\Throwable $e) {}

        return redirect('/')->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
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
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user = User::where('email', $r->email)->first();

        if (!$user) {
            throw ValidationException::withMessages(['password' => 'Account not found.']);
        }

        // NOTE: User model casts 'password' as 'hashed', so we pass the plain
        // password — Laravel will hash it on assignment. Do NOT call Hash::make()
        // here, that would double-hash and make login impossible.
        $user->update(['password' => $r->password]);

        return redirect()->route('login')
            ->with('reset_success', 'Password reset successfully! You can now log in with your new password.');
    }

    // ── Email-Based Password Reset Flow ──────────────────────────────────

    /**
     * Show the forgot password form (email-based).
     */
    public function showLinkRequestForm()
    {
        return redirect()->route('login')->with('show_email_forgot', true);
    }

    /**
     * Send a password reset link via email.
     */
    public function sendResetLinkEmail(Request $r)
    {
        $r->validate([
            'email' => 'required|email',
        ]);

        // Find user
        $user = User::where('email', $r->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'No account found with that email address.',
            ]);
        }

        // Create a reset token
        $token = Password::broker()->createToken($user);

        // Send the notification
        try {
            $user->notify(new AdminResetPasswordNotification($token));
        } catch (\Throwable $e) {
            Log::error('Failed to send password reset email: ' . $e->getMessage());
            throw ValidationException::withMessages([
                'email' => 'Failed to send reset email. Please check your email configuration or contact the administrator.',
            ]);
        }

        return redirect()->route('login')
            ->with('reset_link_sent', true)
            ->with('reset_email_sent', $r->email);
    }

    /**
     * Show the password reset form (from email link).
     */
    public function showResetForm(Request $r, string $token)
    {
        $email = $r->query('email');

        if (!$email) {
            return redirect()->route('password.forgot')
                ->withErrors(['email' => 'Invalid password reset link.']);
        }

        // Verify token exists
        $resetRecord = \DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$resetRecord || !Hash::check($token, $resetRecord->token)) {
            return redirect()->route('password.forgot')
                ->withErrors(['email' => 'This password reset link is invalid or has expired. Please request a new one.']);
        }

        // Check expiry (60 minutes default)
        $expires = config('auth.passwords.users.expire', 60);
        if ($resetRecord->created_at && now()->diffInMinutes($resetRecord->created_at) > $expires) {
            \DB::table('password_reset_tokens')->where('email', $email)->delete();
            return redirect()->route('password.forgot')
                ->withErrors(['email' => 'This password reset link has expired. Please request a new one.']);
        }

        $user = User::where('email', $email)->first();

        return redirect()->route('login')
            ->with('show_email_reset', true)
            ->with('reset_token', $token)
            ->with('reset_email', $email)
            ->with('reset_user_name', $user ? $user->name : '');
    }

    /**
     * Reset the password using token from email.
     */
    public function resetPasswordWithToken(Request $r)
    {
        $r->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        // Verify token
        $resetRecord = \DB::table('password_reset_tokens')
            ->where('email', $r->email)
            ->first();

        if (!$resetRecord || !Hash::check($r->token, $resetRecord->token)) {
            throw ValidationException::withMessages([
                'password' => 'Invalid or expired reset token. Please request a new password reset link.',
            ]);
        }

        // Check expiry
        $expires = config('auth.passwords.users.expire', 60);
        if ($resetRecord->created_at && now()->diffInMinutes($resetRecord->created_at) > $expires) {
            \DB::table('password_reset_tokens')->where('email', $r->email)->delete();
            throw ValidationException::withMessages([
                'password' => 'Reset token has expired. Please request a new password reset link.',
            ]);
        }

        $user = User::where('email', $r->email)->first();

        if (!$user) {
            throw ValidationException::withMessages(['password' => 'Account not found.']);
        }

        // Update password — model cast handles hashing
        $user->update(['password' => $r->password]);

        // Delete used token
        \DB::table('password_reset_tokens')->where('email', $r->email)->delete();

        Log::info('Password reset via email token successful', ['user_id' => $user->id, 'email' => $r->email]);

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

    /**
     * Validate and normalize a redirect URL.
     *
     * - Accepts both full URLs and relative paths
     * - Validates the host against BOTH APP_URL and the current request host
     *   (fixes 404 when APP_URL doesn't match the live domain)
     * - Converts full URLs to relative paths for maximum compatibility
     * - Blocks redirects to login page or external sites
     *
     * @param string|null $url
     * @return string|null Normalized relative path, or null if invalid
     */
    private function validateRedirectUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        // If it's a relative path, validate and return as-is
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            // It's a relative path like /admin/mark-entries
            // Block paths that point to the login page
            if (str_ends_with($url, '/login') || $url === '/login') {
                return null;
            }
            // Block external-looking paths (starting with //)
            if (str_starts_with($url, '//')) {
                return null;
            }
            // Must start with /
            if (!str_starts_with($url, '/')) {
                return null;
            }

            // ── SAFETY NET: Strip the subdirectory base path if present ──
            // If the relative path starts with the APP_URL's path prefix
            // (e.g., /redemption/admin/... when APP_URL=http://localhost/redemption),
            // strip the prefix to avoid double-path when redirect()->intended()
            // prepends the base URL again.
            // Example: /redemption/admin/certificate-print → /admin/certificate-print
            $appUrlPath = rtrim(parse_url(config('app.url'), PHP_URL_PATH) ?: '', '/');
            if ($appUrlPath && $appUrlPath !== '/' && str_starts_with($url, $appUrlPath . '/')) {
                $url = substr($url, strlen($appUrlPath));
                // Ensure it still starts with /
                if (!str_starts_with($url, '/')) {
                    $url = '/' . $url;
                }
            }

            return $url;
        }

        // It's a full URL — validate host and convert to relative path
        $parsed = parse_url($url);
        $redirectHost = $parsed['host'] ?? null;
        $redirectPath = $parsed['path'] ?? '/';

        // Block redirects to login page
        if (str_ends_with($redirectPath, '/login') || $redirectPath === '/login') {
            return null;
        }

        if (!$redirectHost) {
            return null;
        }

        // Validate host against APP_URL host
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);

        // Also validate against the current request's host — this is critical
        // because APP_URL might still be set to XAMPP values on the live server
        $requestHost = request()->getHost();

        $hostAllowed = (
            $redirectHost === $appHost ||
            $redirectHost === $requestHost ||
            // Also allow if the redirect host ends with the request host (subdomain)
            str_ends_with($redirectHost, '.' . $requestHost)
        );

        if (!$hostAllowed) {
            return null; // Not our domain — block
        }

        // Convert full URL to relative path for maximum compatibility
        // This avoids the double-path 404 bug with redirect()->away()
        // Also strip the subdirectory base path if present to prevent duplication
        $relativePath = $redirectPath;
        $appUrlPath = rtrim(parse_url(config('app.url'), PHP_URL_PATH) ?: '', '/');
        if ($appUrlPath && $appUrlPath !== '/' && str_starts_with($relativePath, $appUrlPath . '/')) {
            $relativePath = substr($relativePath, strlen($appUrlPath));
            if (!str_starts_with($relativePath, '/')) {
                $relativePath = '/' . $relativePath;
            }
        }
        if (isset($parsed['query'])) {
            $relativePath .= '?' . $parsed['query'];
        }

        return $relativePath;
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

    /**
     * Render the login view with no-store cache headers and a fresh CSRF
     * token. Used by the login() failure paths to ensure the browser never
     * caches the login form (which would freeze the CSRF token and cause
     * the "session expired" loop on the next submit).
     *
     * @param array $data  View data: 'login', 'error', 'errors'
     */
    private function renderLoginViewWithNoStoreHeaders(array $data = [])
    {
        // Regenerate the CSRF token so the new form gets a fresh one.
        // Note: no manual save() — Laravel saves at end of request.
        try {
            request()->session()->regenerateToken();
        } catch (\Throwable $e) {
            // Keep going — the view will still render.
        }

        // Flash error/errors to the session so the view can pick them up
        // via session('error') and $errors->any(). We can't use ->with()
        // on a Response object (only RedirectResponse has that method).
        if (isset($data['error'])) {
            try {
                request()->session()->flash('error', $data['error']);
            } catch (\Throwable $e) {}
        }
        if (isset($data['errors'])) {
            try {
                request()->session()->flash('errors', $data['errors']);
            } catch (\Throwable $e) {}
        }

        // Pass the remaining data (like 'login') directly to the view.
        $viewData = $data;
        unset($viewData['error'], $viewData['errors']);

        return response()->view('auth.login', $viewData)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
