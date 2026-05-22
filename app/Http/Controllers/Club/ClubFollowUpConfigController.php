<?php

namespace App\Http\Controllers\Club;

use App\Http\Controllers\Controller;
use App\Models\ClubFollowUpConfig;
use App\Models\Club;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClubFollowUpConfigController extends Controller
{
    public function index(Request $request)
    {
        $query = ClubFollowUpConfig::with(['club', 'branch', 'createdBy']);

        if ($request->filled('club_id')) {
            $query->where('club_id', $request->club_id);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('follow_up_type')) {
            $query->where('follow_up_type', $request->follow_up_type);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        $configs = $query->orderBy('created_at', 'desc')->paginate(20);
        $clubs = Club::active()->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();

        $activeCount = ClubFollowUpConfig::where('is_active', true)->count();
        $totalConfigs = ClubFollowUpConfig::count();

        return view('admin.club-follow-up-configs.index', compact(
            'configs', 'clubs', 'branches', 'activeCount', 'totalConfigs'
        ));
    }

    public function create()
    {
        $clubs = Club::active()->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        $defaultChecklist = ClubFollowUpConfig::defaultChecklistItems();
        $defaultCriteria = ClubFollowUpConfig::defaultRatingCriteria();

        return view('admin.club-follow-up-configs.create', compact(
            'clubs', 'branches', 'defaultChecklist', 'defaultCriteria'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'club_id' => 'nullable|exists:clubs,id',
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'required|string|max:255',
            'follow_up_type' => 'required|in:regular,post_event,monthly,quarterly,annual',
            'description' => 'nullable|string',
            'days_after_activity' => 'required|integer|min:1',
            'checklist_items' => 'nullable|array',
            'rating_criteria' => 'nullable|array',
            'is_auto_reminder' => 'nullable|boolean',
            'reminder_days_before' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
        $data['is_auto_reminder'] = $request->boolean('is_auto_reminder', true);
        $data['created_by'] = Auth::id();

        if (!$request->filled('checklist_items')) {
            $data['checklist_items'] = ClubFollowUpConfig::defaultChecklistItems();
        }
        if (!$request->filled('rating_criteria')) {
            $data['rating_criteria'] = ClubFollowUpConfig::defaultRatingCriteria();
        }

        ClubFollowUpConfig::create($data);

        return redirect()->route('admin.club-follow-up-configs.index')
            ->with('success', 'Club follow-up configuration created successfully.');
    }

    public function edit(ClubFollowUpConfig $clubFollowUpConfig)
    {
        $clubs = Club::active()->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();

        return view('admin.club-follow-up-configs.edit', compact(
            'clubFollowUpConfig', 'clubs', 'branches'
        ));
    }

    public function update(Request $request, ClubFollowUpConfig $clubFollowUpConfig)
    {
        $request->validate([
            'club_id' => 'nullable|exists:clubs,id',
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'required|string|max:255',
            'follow_up_type' => 'required|in:regular,post_event,monthly,quarterly,annual',
            'description' => 'nullable|string',
            'days_after_activity' => 'required|integer|min:1',
            'checklist_items' => 'nullable|array',
            'rating_criteria' => 'nullable|array',
            'is_auto_reminder' => 'nullable|boolean',
            'reminder_days_before' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->all();
        $data['is_auto_reminder'] = $request->boolean('is_auto_reminder', true);
        $data['is_active'] = $request->boolean('is_active', true);

        $clubFollowUpConfig->update($data);

        return redirect()->route('admin.club-follow-up-configs.index')
            ->with('success', 'Club follow-up configuration updated successfully.');
    }

    public function destroy(ClubFollowUpConfig $clubFollowUpConfig)
    {
        $clubFollowUpConfig->delete();
        return back()->with('success', 'Configuration deleted.');
    }
}
