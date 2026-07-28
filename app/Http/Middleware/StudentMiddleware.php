<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use App\Models\Student;
use Symfony\Component\HttpFoundation\Response;

class StudentMiddleware
{
    /**
     * Allow access only to student users.
     *
     * Graduate access: if the linked student record has status='graduated',
     * access is allowed only when the 'graduate_access_enabled' setting is '1'
     * (default). When disabled, graduated students are logged out.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            // Use path() (relative to app root) instead of getRequestUri() (which includes subdirectory)
            // to avoid the double-path 404 bug where /redemption/student/... becomes /redemption/redemption/student/...
            return redirect()->route('login')->withIntended('/' . $request->path());
        }

        if (isset($user->is_active) && !$user->is_active) {
            Auth::logout();
            abort(403, 'Your account has been deactivated.');
        }

        if ($user->role !== 'student') {
            abort(403, 'You do not have access to the student portal.');
        }

        // ── Graduate access check ──
        // Look up the linked student record. If status='graduated' and the
        // 'graduate_access_enabled' setting is off, deny access.
        $student = Student::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if ($student && $student->status === 'graduated') {
            $gradAccessEnabled = '1';
            try {
                $gradAccessEnabled = Setting::get('graduate_access_enabled', '1');
            } catch (\Throwable $e) {}

            if ($gradAccessEnabled !== '1') {
                Auth::logout();
                abort(403, 'Your access has been revoked. You have graduated and graduate portal access is currently disabled. Please contact the school administration if you need to view your records.');
            }

            // Allow access — flag the request so controllers can show a
            // graduated-specific view (transcript/certificate links only).
            $request->attributes->set('is_graduated_student', true);
        }

        return $next($request);
    }
}
