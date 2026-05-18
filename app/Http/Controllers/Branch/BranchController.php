<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;

class BranchController extends Controller
{
    public function index()
    {
        $data = Branch::with('principal')->latest()->paginate(20);
        return view('admin.Branch.index', compact('data'));
    }

    public function create()
    {
        $teachers = \App\Models\Teacher::orderBy('full_name')->get();
        return view('admin.Branch.create', compact('teachers'));
    }

    public function store(Request $r)
    {
        $validated = $r->validate([
            'name'           => 'required|string|max:255',
            'address'        => 'required|string',
            'phone'          => 'required|string|max:50',
            'email'          => 'required|email|max:255',
            'gps_lat'        => 'nullable|numeric',
            'gps_lng'        => 'nullable|numeric',
            'map_embed_url'  => 'nullable|url|max:1000',
            'principal_id'   => 'nullable|exists:teachers,id',
            'order'          => 'nullable|integer',
            'is_active'      => 'boolean',
            'is_headquarters'=> 'boolean',
        ]);

        Branch::create($validated);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch created successfully.');
    }

    public function show(Branch $branch)
    {
        $branch->load('principal');
        return view('admin.Branch.show', ['item' => $branch]);
    }

    public function edit(Branch $branch)
    {
        $teachers = \App\Models\Teacher::orderBy('full_name')->get();
        return view('admin.Branch.edit', ['item' => $branch, 'teachers' => $teachers]);
    }

    public function update(Request $r, Branch $branch)
    {
        $validated = $r->validate([
            'name'           => 'required|string|max:255',
            'address'        => 'required|string',
            'phone'          => 'required|string|max:50',
            'email'          => 'required|email|max:255',
            'gps_lat'        => 'nullable|numeric',
            'gps_lng'        => 'nullable|numeric',
            'map_embed_url'  => 'nullable|url|max:1000',
            'principal_id'   => 'nullable|exists:teachers,id',
            'order'          => 'nullable|integer',
            'is_active'      => 'boolean',
            'is_headquarters'=> 'boolean',
        ]);

        $branch->update($validated);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();
        return back()->with('success', 'Branch deleted successfully.');
    }
}
