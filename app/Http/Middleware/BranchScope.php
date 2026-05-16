<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * BranchScope Middleware
 *
 * Restricts branch-scoped users (branch_principal, finance, hr, etc.)
 * to only see data from their assigned branch.
 *
 * How it works:
 * - Checks if the authenticated user has a branch_id
 * - If so, injects a query scope that filters by branch
 * - Controllers can use request()->attributes->get('branch_scope') to get the branch_id
 */
class BranchScope
{
    /**
     * Roles that are branch-scoped (can only see their branch data).
     */
    protected const BRANCH_SCOPED_ROLES = ['branch_principal', 'finance', 'hr', 'cashier', 'librarian', 'registrar'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Admin and general manager see everything
        if (in_array($user->role, ['admin', 'super_admin', 'general_manager'])) {
            $request->attributes->set('branch_scope', null);
            return $next($request);
        }

        // Branch-scoped roles: only see their branch
        if (in_array($user->role, self::BRANCH_SCOPED_ROLES) && $user->branch_id) {
            $request->attributes->set('branch_scope', $user->branch_id);
        } else {
            $request->attributes->set('branch_scope', null);
        }

        return $next($request);
    }
}
