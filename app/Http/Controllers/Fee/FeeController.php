<?php

namespace App\Http\Controllers\Fee;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Fee;
use App\Models\Setting;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index(Request $r)
    {
        $q = Fee::with('classroom', 'academicYear');
        if ($r->filled('search')) {
            $s = $r->search;
            $q->where('fee_type', 'LIKE', "%$s%")->orWhere('description', 'LIKE', "%$s%");
        }
        if ($r->filled('academic_year_id')) $q->where('academic_year_id', $r->academic_year_id);
        if ($r->filled('class_id')) $q->where('class_id', $r->class_id);
        $data = $q->latest()->paginate(20);
        $totalFees = Fee::count();
        $totalAmount = Fee::sum('amount');
        $academicYears = AcademicYear::orderBy('name')->get();
        return view('admin.Fee.index', compact('data', 'totalFees', 'totalAmount', 'academicYears'));
    }

    public function create()
    {
        $classrooms = Classroom::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('name')->get();
        $feeDueDay = Setting::where('key', 'fee_due_day')->value('value') ?? 10;
        return view('admin.Fee.create', compact('classrooms', 'academicYears', 'feeDueDay'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'fee_type' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'class_id' => 'required|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $r->only(['fee_type','amount','class_id','academic_year_id','due_date','description','is_active']);

        // Auto-set due date to 10th of current month if not provided
        if (empty($data['due_date'])) {
            $data['due_date'] = $this->getNextDueDate();
        }

        Fee::create($data);
        return redirect()->route('admin.fees.index')->with('success', 'Fee created successfully');
    }

    public function show(Fee $fee)
    {
        $fee->load(['classroom','academicYear','feePayments']);
        return view('admin.Fee.show', ['item' => $fee]);
    }

    public function edit(Fee $fee)
    {
        $classrooms = Classroom::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('name')->get();
        $feeDueDay = Setting::where('key', 'fee_due_day')->value('value') ?? 10;
        return view('admin.Fee.edit', ['item' => $fee, 'classrooms' => $classrooms, 'academicYears' => $academicYears, 'feeDueDay' => $feeDueDay]);
    }

    public function update(Request $r, Fee $fee)
    {
        $r->validate([
            'fee_type' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'class_id' => 'required|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $r->only(['fee_type','amount','class_id','academic_year_id','due_date','description','is_active']);

        if (empty($data['due_date'])) {
            $data['due_date'] = $this->getNextDueDate();
        }

        $fee->update($data);
        return redirect()->route('admin.fees.index')->with('success', 'Fee updated successfully');
    }

    public function destroy(Fee $fee)
    {
        $fee->delete();
        return back()->with('success', 'Fee deleted successfully');
    }

    /**
     * Calculate the next fee due date.
     * Ethiopian calendar: 10th day of each month.
     * Since PHP uses Gregorian internally, we approximate the Ethiopian 10th
     * as the 19th of the Gregorian month (Ethiopian calendar is ~7-8 years behind
     * and the 10th of Ethiopian month roughly corresponds to 19th Gregorian).
     * However, for practical school management, we use the configured due_day setting.
     */
    private function getNextDueDate(): string
    {
        $dueDay = (int) (Setting::where('key', 'fee_due_day')->value('value') ?? 10);
        $now = now();
        $year = $now->year;
        $month = $now->month;

        // If the due day has already passed this month, use next month
        if ($now->day > $dueDay) {
            $month++;
            if ($month > 12) {
                $month = 1;
                $year++;
            }
        }

        return sprintf('%04d-%02d-%02d', $year, $month, $dueDay);
    }
}
