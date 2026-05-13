<?php

namespace App\Http\Controllers\Term;

use App\Http\Controllers\Controller;
use App\Models\Term;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class TermController extends Controller
{
    public function index()
    {
        $data = Term::with('academicYear')->latest()->paginate(20);
        return view('admin.Term.index', compact('data'));
    }

    public function create()
    {
        $academicYears = AcademicYear::orderByDesc('id')->get();
        return view('admin.Term.create', compact('academicYears'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_number' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            
        ]);

        if ($r->boolean('is_active')) {
            Term::where('is_active', true)->update(['is_active' => false]);
        }

        Term::create($r->only(['name', 'academic_year_id', 'term_number', 'start_date', 'end_date', 'is_active']));
        return redirect()->route('admin.terms.index')->with('success', 'Term created.');
    }

    public function show(Term $item)
    {
        $item->load('academicYear');
        return view('admin.Term.show', compact('item'));
    }

    public function edit(Term $term)
    {
        $academicYears = AcademicYear::orderByDesc('id')->get();
        return view('admin.Term.edit', compact('term', 'academicYears'));
    }

    public function update(Request $r, Term $term)
    {
        $r->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_number' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            
        ]);

        if ($r->boolean('is_active')) {
            Term::where('is_active', true)->where('id', '!=', $term->id)->update(['is_active' => false]);
        }

        $term->update($r->only(['name', 'academic_year_id', 'term_number', 'start_date', 'end_date', 'is_active']));
        return redirect()->route('admin.terms.index')->with('success', 'Term updated.');
    }

    public function destroy(Term $term)
    {
        $term->delete();
        return back()->with('success', 'Term deleted.');
    }
}
