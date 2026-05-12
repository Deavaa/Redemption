<?php

namespace App\Http\Controllers\Fee;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Fee;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index()
    {
        $data = Fee::with('classroom', 'academicYear')->latest()->paginate(20);
        $totalFees = Fee::count();
        $totalAmount = Fee::sum('amount');

        return view('admin.Fee.index', compact('data', 'totalFees', 'totalAmount'));
    }

    public function create()
    {
        $classrooms = Classroom::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('name')->get();

        return view('admin.Fee.create', compact('classrooms', 'academicYears'));
    }

    public function store(Request $r)
    {
        $r->validate(['fee_type' => 'required', 'amount' => 'required|numeric|min:0', 'class_id' => 'required|exists:classes,id', 'academic_year_id' => 'required|exists:academic_years,id', 'type' => 'required|in:tuition,lab,library,transport,sports,other']);
        Fee::create($r->all());

        return redirect()->route('admin.fees.index')->with('success', 'Fee created');
    }

    public function show(Fee $item)
    {
        return view('admin.Fee.show', compact('item'));
    }

    public function edit(Fee $item)
    {
        $classrooms = Classroom::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('name')->get();

        return view('admin.Fee.edit', compact('item', 'classrooms', 'academicYears'));
    }

    public function update(Request $r, Fee $item)
    {
        $r->validate(['fee_type' => 'required', 'amount' => 'required|numeric|min:0', 'class_id' => 'required|exists:classes,id', 'academic_year_id' => 'required|exists:academic_years,id', 'type' => 'required|in:tuition,lab,library,transport,sports,other']);
        $item->update($r->all());

        return redirect()->route('admin.fees.index')->with('success', 'Updated');
    }

    public function destroy(Fee $item)
    {
        $item->delete();

        return back()->with('success', 'Deleted');
    }
}
