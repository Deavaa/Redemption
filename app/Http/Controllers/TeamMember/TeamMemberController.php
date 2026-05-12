<?php
namespace App\Http\Controllers\TeamMember;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeamMember;

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
            'photo' => 'nullable|string|max:500',
            'bio' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        TeamMember::create($r->only(['name','designation','department','qualification','experience','phone','email','photo','bio','sort_order','is_active']));
        return redirect()->route("admin.team-members.index")->with('success','Team member created successfully');
    }

    public function show(TeamMember $item) { return view('admin.TeamMember.show', compact('item')); }
    public function edit(TeamMember $item) { return view('admin.TeamMember.edit', compact('item')); }

    public function update(Request $r, TeamMember $item)
    {
        $r->validate([
            'name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'experience' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'photo' => 'nullable|string|max:500',
            'bio' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $item->update($r->only(['name','designation','department','qualification','experience','phone','email','photo','bio','sort_order','is_active']));
        return redirect()->route("admin.team-members.index")->with('success','Team member updated successfully');
    }

    public function destroy(TeamMember $item) { $item->delete(); return back()->with('success','Team member deleted successfully'); }
}
