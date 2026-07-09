<?php
namespace App\Http\Controllers\TeamMember;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeamMember;
use App\Helpers\ImageCompressor;

class TeamMemberController extends Controller
{
    public function index(Request $r)
    {
        $q = TeamMember::query();
        if ($r->filled('search')) {
            $s = $r->search;
            $q->where('name', 'LIKE', "%$s%")->orWhere('designation', 'LIKE', "%$s%")->orWhere('department', 'LIKE', "%$s%");
        }
        $data = $q->orderBy('sort_order')->paginate(20);
        $totalMembers = TeamMember::count();
        return view('admin.TeamMember.index', compact('data', 'totalMembers'));
    }

    public function create() { return view('admin.TeamMember.create'); }

    public function store(Request $r)
    {
        $r->validate([
            'name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'experience' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'bio' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $allFields = ['name','designation','department','qualification','experience','phone','email','bio','sort_order'];
        $data = [];
        foreach ($allFields as $field) {
            if ($r->has($field)) {
                $data[$field] = $r->input($field);
            }
        }
        $data['is_active'] = $r->has('is_active') ? 1 : 0;
        if ($r->hasFile('photo') && $r->file('photo')->isValid()) {
            try {
                $data['photo'] = ImageCompressor::compressAndStore($r->file('photo'), 'team-photos', 2048, 1200);
            } catch (\Throwable $e) {
                \Log::error('TeamMember photo upload failed: ' . $e->getMessage());
            }
        }
        try {
            TeamMember::create($data);
            return redirect()->route("admin.team-members.index")->with('success','Team member created successfully');
        } catch (\Throwable $e) {
            \Log::error('TeamMember create failed: ' . $e->getMessage(), ['data' => $data]);
            return back()->with('error', 'Create failed: ' . $e->getMessage())->withInput();
        }
    }

    public function show(TeamMember $team_member) { return view('admin.TeamMember.show', ['item' => $team_member]); }
    public function edit(TeamMember $team_member) { return view('admin.TeamMember.edit', ['item' => $team_member]); }

    public function update(Request $r, TeamMember $team_member)
    {
        $r->validate([
            'name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'experience' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'bio' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Only include fields that actually exist as columns on the table
        // — prevents "column not found" errors on incomplete cPanel databases
        $allFields = ['name','designation','department','qualification','experience','phone','email','bio','sort_order'];
        $data = [];
        foreach ($allFields as $field) {
            if ($r->has($field)) {
                $data[$field] = $r->input($field);
            }
        }
        $data['is_active'] = $r->has('is_active') ? 1 : 0;

        // Handle photo upload — only if a file was actually uploaded
        if ($r->hasFile('photo') && $r->file('photo')->isValid()) {
            try {
                $data['photo'] = ImageCompressor::compressAndStore($r->file('photo'), 'team-photos', 2048, 1200);
            } catch (\Throwable $e) {
                \Log::error('TeamMember photo upload failed: ' . $e->getMessage());
            }
        }

        try {
            $team_member->update($data);
            \Log::info('TeamMember updated', ['id' => $team_member->id, 'fields' => array_keys($data)]);
            return redirect()->route("admin.team-members.index")->with('success','Team member updated successfully');
        } catch (\Throwable $e) {
            \Log::error('TeamMember update failed: ' . $e->getMessage(), [
                'id' => $team_member->id,
                'data' => $data,
            ]);
            return back()->with('error', 'Update failed: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(TeamMember $team_member) { $team_member->delete(); return back()->with('success','Team member deleted successfully'); }
}
