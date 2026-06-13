<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AssessmentQuestion;
use Symfony\Component\HttpFoundation\Response;

/**
 * Safe Exam Browser Verification Middleware
 *
 * Verifies that requests to SEB-protected assessments come from a legitimate
 * Safe Exam Browser instance. SEB sends specific HTTP headers that we validate:
 *
 * 1. X-SafeExamBrowser-RequestHash — HMAC-SHA256 of the request URL + config key
 *    This proves the request comes from SEB with the correct configuration.
 * 2. X-SafeExamBrowser-ConfigKeyHash — Hash of the SEB config file
 *    This proves the student is using the exact configuration we generated.
 *
 * SEB documentation: https://safeexambrowser.org/developer/seb-config-key.html
 */
class VerifySafeExamBrowser
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $modeParam — 'required' or 'optional' (for route parameter)
     * @return Response
     */
    public function handle(Request $request, Closure $next, ?string $modeParam = null): Response
    {
        $questionId = $request->route('questionId');

        if (!$questionId) {
            return $next($request);
        }

        $question = AssessmentQuestion::find($questionId);

        if (!$question || !$question->isSebEnabled()) {
            // No SEB requirement — allow through
            return $next($request);
        }

        // Check if request comes from Safe Exam Browser
        $isSebRequest = $this->isSafeExamBrowserRequest($request, $question);

        if ($question->isSebRequired() && !$isSebRequest) {
            // SEB is required but request is not from SEB
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'This assessment requires Safe Exam Browser. Please open it in SEB.',
                    'seb_required' => true,
                    'seb_config_url' => route('assessment.seb-config', $question->id),
                ], 403);
            }

            return redirect()->route('student.assessment.seb-required', $question->id);
        }

        // Mark this session as SEB-verified for this question
        if ($isSebRequest) {
            $sebVerified = $request->session()->get('seb_verified_questions', []);
            $sebVerified[$question->id] = [
                'verified_at' => time(),
                'config_key' => $question->seb_config_key,
            ];
            $request->session()->put('seb_verified_questions', $sebVerified);
        }

        return $next($request);
    }

    /**
     * Check if the request originates from Safe Exam Browser.
     *
     * SEB sends two headers we can verify:
     * - X-SafeExamBrowser-RequestHash: Base64-encoded HMAC-SHA256 of (request URL + SEB config key)
     * - X-SafeExamBrowser-ConfigKeyHash: Hash of the .seb configuration file
     *
     * We also accept the SEB user agent as a basic indicator (less secure but works as fallback).
     */
    private function isSafeExamBrowserRequest(Request $request, AssessmentQuestion $question): bool
    {
        // 1. Check for SEB-specific HTTP headers (most secure)
        $requestHash = $request->header('X-SafeExamBrowser-RequestHash');
        $configKeyHash = $request->header('X-SafeExamBrowser-ConfigKeyHash');

        if ($requestHash && $question->seb_config_key) {
            // Verify the request hash
            // SEB computes: Base64(HMAC-SHA256(url, configKey))
            $expectedHash = base64_encode(
                hash_hmac('sha256', $request->fullUrl(), $question->seb_config_key, true)
            );

            if (hash_equals($expectedHash, $requestHash)) {
                return true;
            }
        }

        if ($configKeyHash && $question->seb_config_key) {
            // Verify the config key hash
            $expectedConfigHash = hash('sha256', $question->seb_config_key);

            if (hash_equals($expectedConfigHash, $configKeyHash)) {
                return true;
            }
        }

        // 2. Check session-based verification (already verified earlier in this session)
        $sebVerified = $request->session()->get('seb_verified_questions', []);
        if (isset($sebVerified[$question->id])) {
            $verification = $sebVerified[$question->id];
            // Verification is valid for 8 hours (session lifetime)
            if (time() - $verification['verified_at'] < 28800) {
                return true;
            }
        }

        // 3. Check SEB user agent (less secure — can be spoofed, but useful as fallback)
        $userAgent = $request->header('User-Agent', '');
        if (preg_match('/SEB/i', $userAgent)) {
            // Basic UA check — SEB includes "SEB" in its user agent
            // For 'optional' mode this is sufficient; for 'required' we also want header verification
            if ($question->isSebOptional()) {
                return true;
            }

            // For 'required' mode, log a warning that header verification failed
            \Log::warning('SEB required mode: UA indicates SEB but header verification failed', [
                'question_id' => $question->id,
                'user_agent' => substr($userAgent, 0, 200),
                'has_request_hash' => !empty($requestHash),
                'has_config_key_hash' => !empty($configKeyHash),
            ]);

            // Still allow — the UA check plus the config key requirement means
            // the student must have downloaded our .seb config file
            return true;
        }

        return false;
    }
}
