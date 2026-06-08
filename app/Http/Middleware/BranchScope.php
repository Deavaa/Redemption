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
 * Key business rules:
 * - Branch principals control staff, students, parents in their branch ONLY
 * - Branch principals do NOT control academic year, terms, exams
 * - Branch principals can transfer students to other branches
 * - Branch principals can add branch-scoped calendar events
 * - Admin and general_manager see everything
 */
class BranchScope
{
    /**
     * Roles that are branch-scoped (can only see their branch data).
     */
    protected const BRANCH_SCOPED_ROLES = ['branch_principal', 'finance', 'hr', 'cashier', 'librarian', 'registrar'];

    /**
     * Routes that branch principals CANNOT access (academic setup managed by admin/GM only).
     */
    protected const PRINCIPAL_RESTRICTED_ROUTES = [
        // Academic Years — full restriction (no create/edit/delete/update)
        'admin.academic-years.index',
        'admin.academic-years.create',
        'admin.academic-years.store',
        'admin.academic-years.show',
        'admin.academic-years.edit',
        'admin.academic-years.update',
        'admin.academic-years.destroy',
        // Terms — full restriction
        'admin.terms.index',
        'admin.terms.create',
        'admin.terms.store',
        'admin.terms.show',
        'admin.terms.edit',
        'admin.terms.update',
        'admin.terms.destroy',
        // Exams — no access for branch principals
        'admin.exams.index',
        'admin.exams.create',
        'admin.exams.store',
        'admin.exams.show',
        'admin.exams.edit',
        'admin.exams.update',
        'admin.exams.destroy',
        // Team Members — admin/GM only
        'admin.team-members.index',
        'admin.team-members.create',
        'admin.team-members.store',
        'admin.team-members.show',
        'admin.team-members.edit',
        'admin.team-members.update',
        'admin.team-members.destroy',
        // Website — admin/GM only
        'admin.sliders.index', 'admin.sliders.create', 'admin.sliders.store',
        'admin.sliders.edit', 'admin.sliders.update', 'admin.sliders.destroy',
        'admin.gallery-images.index', 'admin.gallery-images.create', 'admin.gallery-images.store',
        'admin.gallery-images.edit', 'admin.gallery-images.update', 'admin.gallery-images.destroy',
        'admin.gallery-videos.index', 'admin.gallery-videos.create', 'admin.gallery-videos.store',
        'admin.gallery-videos.edit', 'admin.gallery-videos.update', 'admin.gallery-videos.destroy',
        'admin.branches.index', 'admin.branches.create', 'admin.branches.store',
        'admin.branches.edit', 'admin.branches.update', 'admin.branches.destroy',
        'admin.contact-messages.index', 'admin.contact-messages.show', 'admin.contact-messages.destroy',
        'admin.web-content.index', 'admin.web-content.edit', 'admin.web-content.update',
        'admin.news.index', 'admin.news.create', 'admin.news.store',
        'admin.news.edit', 'admin.news.update', 'admin.news.destroy',
        // Roles & Permissions — admin only
        'admin.roles.index', 'admin.roles.create', 'admin.roles.store',
        'admin.roles.edit', 'admin.roles.update', 'admin.roles.destroy',
        // Settings — admin only
        'admin.settings.index', 'admin.settings.update',
        // Backup — admin only
        'admin.backup.index', 'admin.backup.download', 'admin.backup.schedule',
        // Audits — admin only
        'admin.audits.index',
    ];

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

            // Special restriction for branch_principals: cannot create/edit/delete academic year/terms/exams
            if ($user->role === 'branch_principal') {
                $routeName = $request->route()?->getName() ?? '';
                if (in_array($routeName, self::PRINCIPAL_RESTRICTED_ROUTES)) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'error' => 'Branch principals cannot modify academic years, terms, or exams. Contact the General Manager.'
                        ], 403);
                    }
                    return redirect()->route('admin.dashboard')
                        ->with('error', 'Branch principals cannot modify academic years, terms, or exams. This is managed by the General Manager.');
                }
            }
        } else {
            $request->attributes->set('branch_scope', null);
        }

        // Teachers: restrict calendar event creation
        if ($user->role === 'teacher') {
            $routeName = $request->route()?->getName() ?? '';
            if (in_array($routeName, ['admin.calendar.store', 'admin.calendar.update', 'admin.calendar.destroy'])) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Teachers cannot add or modify calendar events.'
                    ], 403);
                }
                return redirect()->route('admin.dashboard')
                    ->with('error', 'Teachers cannot add or modify calendar events. Only branch principals and managers can add events.');
            }
        }

        return $next($request);
    }
}
