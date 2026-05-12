<?php
namespace App\Http\Controllers\TeamMember;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeamMember;

class TeamMemberController extends Controller
{
    public function index() { $data = TeamMember::latest()->paginate(20); return view('admin.TeamMember.index', compact('data')); }
    public function create() { return view('admin.TeamMember.create'); }
    public function store(Request $r) { TeamMember::create($r->all()); return redirect()->route("admin.team-members.index")->with('success','Created successfully'); }
    public function show(TeamMember $item) { return view('admin.TeamMember.show', compact('item')); }
    public function edit(TeamMember $item) { return view('admin.TeamMember.edit', compact('item')); }
    public function update(Request $r, TeamMember $item) { $item->update($r->all()); return redirect()->route("admin.team-members.index")->with('success','Updated successfully'); }
    public function destroy(TeamMember $item) { $item->delete(); return back()->with('success','Deleted successfully'); }
}