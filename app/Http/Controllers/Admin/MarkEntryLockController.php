<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\MarkEntryLock;
use App\Models\Term;
use Illuminate\Http\Request;

class MarkEntryLockController extends Controller
{
    /**
     * Display mark entry lock management page.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Branch principals can only see their branch
        if ($user->role === 'branch_principal') {
            $branches = Branch::where('id', $user->branch_id)->get();
        } else {
            $branches = Branch::orderBy('name')->get();
        }

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $currentAy = AcademicYear::where('is_current', true)->first();

        $selectedBranch = $request->filled('branch_id') ? Branch::find($request->branch_id) : $branches->first();
        $selectedAy = $request->filled('academic_year_id') ? AcademicYear::find($request->academic_year_id) : $currentAy;

        $terms = $selectedAy ? Term::where('academic_year_id', $selectedAy->id)->orderBy('id')->get() : collect();

        // Get lock status for each term of the selected branch/AY
        $lockStatuses = collect();
        if ($selectedBranch && $selectedAy) {
            foreach ($terms as $term) {
                $lock = MarkEntryLock::getOrCreate($selectedBranch->id, $selectedAy->id, $term->id);
                $lockStatuses->push([
                    'term' => $term,
                    'lock' => $lock,
                ]);
            }
        }

        // Get all lock records for display
        $allLocks = MarkEntryLock::with(['branch', 'academicYear', 'term', 'lockedBy', 'unlockedBy'])
            ->when($selectedBranch, fn($q) => $q->where('branch_id', $selectedBranch->id))
            ->when($selectedAy, fn($q) => $q->where('academic_year_id', $selectedAy->id))
            ->orderBy('id', 'desc')
            ->get();

        $userBranch = $user->role === 'branch_principal' ? Branch::find($user->branch_id) : null;

        $locks = $allLocks;

        return view('admin.mark_entry_locks.index', compact(
            'branches', 'academicYears', 'terms', 'lockStatuses', 'allLocks', 'locks',
            'selectedBranch', 'selectedAy', 'userBranch'
        ));
    }

    /**
     * Lock mark entry for a specific branch/term.
     */
    public function lock(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'lock_reason' => 'nullable|string|max:500',
        ]);

        // Authorization check: only branch_principal (for their branch) or admin/super_admin
        $user = auth()->user();
        if ($user->role === 'branch_principal' && $user->branch_id != $validated['branch_id']) {
            abort(403, 'You can only manage mark entry locks for your own branch.');
        }

        $lock = MarkEntryLock::getOrCreate(
            $validated['branch_id'],
            $validated['academic_year_id'],
            $validated['term_id']
        );

        if ($lock->is_locked) {
            return redirect()->back()->with('info', 'Mark entry is already locked for this term.');
        }

        $lock->update([
            'is_locked' => true,
            'locked_by' => $user->id,
            'locked_at' => now(),
            'lock_reason' => $validated['lock_reason'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Mark entry has been locked. Teachers cannot edit marks for this term unless specifically permitted.');
    }

    /**
     * Unlock mark entry for a specific branch/term.
     */
    public function unlock(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'unlock_reason' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        if ($user->role === 'branch_principal' && $user->branch_id != $validated['branch_id']) {
            abort(403, 'You can only manage mark entry locks for your own branch.');
        }

        $lock = MarkEntryLock::where('branch_id', $validated['branch_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('term_id', $validated['term_id'])
            ->first();

        if (!$lock || !$lock->is_locked) {
            return redirect()->back()->with('info', 'Mark entry is not locked for this term.');
        }

        $lock->update([
            'is_locked' => false,
            'unlocked_by' => $user->id,
            'unlocked_at' => now(),
            'unlock_reason' => $validated['unlock_reason'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Mark entry has been unlocked. Teachers can now edit marks for this term.');
    }

    /**
     * API: Check if mark entry is locked for a branch/term.
     */
    public function apiCheckLock(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
        ]);

        $isLocked = MarkEntryLock::isLocked(
            $request->branch_id,
            $request->academic_year_id,
            $request->term_id
        );

        return response()->json(['is_locked' => $isLocked]);
    }

    /**
     * Publish ranks for a term — makes ranks visible to students and parents.
     * Called by branch principal or admin after verifying final exam marks.
     */
    public function publishRanks(Request $request)
    {
        $request->validate([
            'term_id' => 'required|exists:terms,id',
        ]);

        $term = \App\Models\Term::findOrFail($request->term_id);
        $term->ranks_published = true;
        $term->ranks_published_at = now();
        $term->ranks_published_by = auth()->id();
        $term->save();

        \Log::info('Ranks published', [
            'term_id' => $term->id,
            'term_name' => $term->name,
            'published_by' => auth()->id(),
        ]);

        return back()->with('success', "Ranks published for {$term->name}. Students and parents can now see class ranks.");
    }

    /**
     * Unpublish ranks for a term — hides ranks from students and parents.
     */
    public function unpublishRanks(Request $request)
    {
        $request->validate([
            'term_id' => 'required|exists:terms,id',
        ]);

        $term = \App\Models\Term::findOrFail($request->term_id);
        $term->ranks_published = false;
        $term->ranks_published_at = null;
        $term->ranks_published_by = null;
        $term->save();

        \Log::info('Ranks unpublished', [
            'term_id' => $term->id,
            'term_name' => $term->name,
            'unpublished_by' => auth()->id(),
        ]);

        return back()->with('success', "Ranks hidden for {$term->name}. Students and parents will no longer see class ranks.");
    }
}
