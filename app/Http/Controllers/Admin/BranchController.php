<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Teacher;
use Illuminate\Http\Request;
class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::with('principal')->orderBy('order')->get();
        return view('admin.branches.index', compact('branches'));
    }
    public function create()
    {
        $teachers = Teacher::where('status','active')->orderBy('first_name')->get();
        return view('admin.branches.create', compact('teachers'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'principal_id' => 'nullable|exists:teachers,id',
            'gps_lat' => 'nullable|numeric',
            'gps_lng' => 'nullable|numeric',
            'map_embed_url' => 'nullable|url',
            'is_headquarters' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);
        $input = $request->all();
        if (!empty($input['is_headquarters'])) {
            Branch::where('is_headquarters', 1)->update(['is_headquarters' => 0]);
        }
        Branch::create($input);
        return redirect()->route('admin.branches.index')->with('success', 'Branch created successfully.');
    }
    public function edit($id)
    {
        $branch = Branch::findOrFail($id);
        $teachers = Teacher::where('status','active')->orderBy('first_name')->get();
        return view('admin.branches.edit', compact('branch','teachers'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'principal_id' => 'nullable|exists:teachers,id',
            'gps_lat' => 'nullable|numeric',
            'gps_lng' => 'nullable|numeric',
            'map_embed_url' => 'nullable|url',
            'is_headquarters' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);
        $branch = Branch::findOrFail($id);
        $input = $request->all();
        if (!empty($input['is_headquarters'])) {
            Branch::where('is_headquarters', 1)->where('id', '!=', $id)->update(['is_headquarters' => 0]);
        }
        $branch->update($input);
        return redirect()->route('admin.branches.index')->with('success', 'Branch updated successfully.');
    }
    public function destroy($id)
    {
        Branch::destroy($id);
        return redirect()->route('admin.branches.index')->with('success', 'Branch deleted.');
    }
}
