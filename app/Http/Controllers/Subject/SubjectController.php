<?php

namespace App\Http\Controllers\Subject;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;

class SubjectController extends Controller
{
    public function index(Request $r)
    {
        $q = Subject::ordered();
        if ($r->filled('search')) {
            $s = $r->search;
            $q->where(function ($query) use ($s) {
                $query->where('name', 'LIKE', "%$s%")
                      ->orWhere('code', 'LIKE', "%$s%");
            });
        }
        if ($r->filled('type')) $q->where('type', $r->type);
        $data = $q->paginate(20);
        $totalSubjects = Subject::count();
        return view('admin.Subject.index', compact('data', 'totalSubjects'));
    }

    public function create()
    {
        $typeOptions = Subject::typeOptions();
        return view('admin.Subject.create', compact('typeOptions'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'name'       => 'required|string|max:255',
            'code'       => 'nullable|string|max:50|unique:subjects,code',
            'type'       => 'required|in:compulsory,elective,optional',
            'priority'   => 'nullable|integer|min:0|max:999',
            'is_active'  => 'nullable|boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $data = $r->only(['name', 'code', 'type', 'priority', 'is_active', 'description']);
        $data['is_active'] = $r->has('is_active') ? true : false;
        if (empty($data['priority'])) $data['priority'] = 0;

        Subject::create($data);
        return redirect()->route('admin.subjects.index')->with('success', 'Subject created successfully');
    }

    public function show(Subject $subject)
    {
        $subject->load('assignments');
        return view('admin.Subject.show', compact('subject'));
    }

    public function edit(Subject $subject)
    {
        $typeOptions = Subject::typeOptions();
        return view('admin.Subject.edit', ['data' => $subject, 'typeOptions' => $typeOptions]);
    }

    public function update(Request $r, Subject $subject)
    {
        $r->validate([
            'name'       => 'required|string|max:255',
            'code'       => 'nullable|string|max:50|unique:subjects,code,' . $subject->id,
            'type'       => 'required|in:compulsory,elective,optional',
            'priority'   => 'nullable|integer|min:0|max:999',
            'is_active'  => 'nullable|boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $data = $r->only(['name', 'code', 'type', 'priority', 'is_active', 'description']);
        $data['is_active'] = $r->has('is_active') ? true : false;
        if (empty($data['priority'])) $data['priority'] = 0;

        $subject->update($data);
        return redirect()->route('admin.subjects.index')->with('success', 'Subject updated successfully');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return back()->with('success', 'Subject deleted successfully');
    }
}
