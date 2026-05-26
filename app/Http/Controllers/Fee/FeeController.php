<?php

namespace App\Http\Controllers\Fee;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Classroom;
use App\Models\Fee;
use App\Models\Setting;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index(Request $r)
    {
        $q = Fee::with('classroom', 'academicYear', 'branch');
        if ($r->filled('search')) {
            $s = $r->search;
            $q->where(function ($query) use ($s) {
                $query->where('fee_type', 'LIKE', "%$s%")->orWhere('description', 'LIKE', "%$s%");
            });
        }
        if ($r->filled('academic_year_id')) $q->where('academic_year_id', $r->academic_year_id);
        if ($r->filled('class_id')) $q->where('class_id', $r->class_id);
        if ($r->filled('enrollment_type')) $q->where('enrollment_type', $r->enrollment_type);
        if ($r->filled('branch_id')) $q->where('branch_id', $r->branch_id);
        $data = $q->latest()->paginate(20);
        $totalFees = Fee::count();
        $totalAmount = Fee::sum('amount');
        $academicYears = AcademicYear::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        $enrollmentTypes = Fee::enrollmentTypes();
        return view('admin.Fee.index', compact('data', 'totalFees', 'totalAmount', 'academicYears', 'branches', 'enrollmentTypes'));
    }

    public function create()
    {
        $classrooms = Classroom::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        $enrollmentTypes = Fee::enrollmentTypes();
        $feeDueDay = Setting::where('key', 'fee_due_day')->value('value') ?? 10;
        $nextDueDate = $this->getNextDueDate();
        return view('admin.Fee.create', compact('classrooms', 'academicYears', 'branches', 'enrollmentTypes', 'feeDueDay', 'nextDueDate'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'fee_type' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'class_id' => 'required|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'enrollment_type' => 'nullable|string|in:all,new,transfer,readmission',
            'branch_id' => 'nullable|exists:branches,id',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $r->only(['fee_type','amount','class_id','academic_year_id','enrollment_type','branch_id','due_date','description','is_active']);

        // Default enrollment_type to 'all' if not provided
        if (empty($data['enrollment_type'])) {
            $data['enrollment_type'] = 'all';
        }

        // Auto-set due date if not provided (10th of current Ethiopian month)
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
        $branches = Branch::orderBy('name')->get();
        $enrollmentTypes = Fee::enrollmentTypes();
        $feeDueDay = Setting::where('key', 'fee_due_day')->value('value') ?? 10;
        return view('admin.Fee.edit', ['item' => $fee, 'classrooms' => $classrooms, 'academicYears' => $academicYears, 'branches' => $branches, 'enrollmentTypes' => $enrollmentTypes, 'feeDueDay' => $feeDueDay]);
    }

    public function update(Request $r, Fee $fee)
    {
        $r->validate([
            'fee_type' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'class_id' => 'required|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'enrollment_type' => 'nullable|string|in:all,new,transfer,readmission',
            'branch_id' => 'nullable|exists:branches,id',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $r->only(['fee_type','amount','class_id','academic_year_id','enrollment_type','branch_id','due_date','description','is_active']);

        if (empty($data['enrollment_type'])) {
            $data['enrollment_type'] = 'all';
        }

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
     * Calculate the next fee due date based on the Ethiopian calendar.
     *
     * Ethiopian calendar: 10th day of each month (Meskerem 10, Tikimt 10, etc.)
     * The Ethiopian calendar has 13 months: 12 months of 30 days + 1 month of 5/6 days.
     * Ethiopian months: Meskerem, Tikimt, Hidar, Tahsas, Tir, Yekatit, Megabit, Miazia,
     *                   Ginbot, Sene, Hamle, Nehase, Pagume
     *
     * The Ethiopian calendar is approximately 7-8 years behind the Gregorian calendar.
     * For school fee management, we convert the Ethiopian 10th to the corresponding
     * Gregorian date.
     */
    private function getNextDueDate(): string
    {
        $dueDay = (int) (Setting::where('key', 'fee_due_day')->value('value') ?? 10);
        $calendarType = Setting::where('key', 'calendar_type')->value('value') ?? 'ethiopian';

        if ($calendarType === 'ethiopian') {
            return $this->getNextEthiopianDueDate($dueDay);
        }

        // Gregorian fallback
        return $this->getNextGregorianDueDate($dueDay);
    }

    /**
     * Get the next Ethiopian due date converted to Gregorian.
     *
     * Ethiopian calendar offset: Ethiopian year = Gregorian year - 7 or 8
     * Ethiopian New Year (Meskerem 1) falls on Sept 11 (or Sept 12 in leap year)
     *
     * We compute the current Ethiopian month, then find the 10th of the
     * next Ethiopian month (or current month if the 10th hasn't passed),
     * and convert that to a Gregorian date.
     */
    private function getNextEthiopianDueDate(int $dueDay): string
    {
        $now = now();
        $gregorianDate = $this->ethiopianToGregorian($this->getCurrentEthiopianMonth($now), $dueDay, $this->getCurrentEthiopianYear($now));

        // If the due date has already passed this Ethiopian month, move to next month
        if ($gregorianDate <= $now->toDateString()) {
            $nextMonth = $this->getCurrentEthiopianMonth($now) + 1;
            $nextYear = $this->getCurrentEthiopianYear($now);
            if ($nextMonth > 13) {
                $nextMonth = 1;
                $nextYear++;
            }
            $gregorianDate = $this->ethiopianToGregorian($nextMonth, $dueDay, $nextYear);
        }

        return $gregorianDate;
    }

    /**
     * Get next Gregorian due date (simple month-based).
     */
    private function getNextGregorianDueDate(int $dueDay): string
    {
        $now = now();
        $year = $now->year;
        $month = $now->month;

        if ($now->day > $dueDay) {
            $month++;
            if ($month > 12) {
                $month = 1;
                $year++;
            }
        }

        return sprintf('%04d-%02d-%02d', $year, $month, $dueDay);
    }

    /**
     * Get the current Ethiopian month (1-13) from a Gregorian date.
     */
    private function getCurrentEthiopianMonth($gregorianDate): int
    {
        // Ethiopian New Year starts on Sept 11 (or 12 in Gregorian leap year)
        $year = (int) $gregorianDate->year;
        $month = (int) $gregorianDate->month;
        $day = (int) $gregorianDate->day;

        // Determine if the Gregorian year is a leap year
        $isGregorianLeap = ($year % 4 === 0 && $year % 100 !== 0) || ($year % 400 === 0);
        $ethNewYearDay = $isGregorianLeap ? 12 : 11;

        // Ethiopian month mapping (approximation based on known correlations)
        // Meskerem (1): Sep 11 - Oct 10
        // Tikimt (2): Oct 11 - Nov 9
        // Hidar (3): Nov 10 - Dec 9
        // Tahsas (4): Dec 10 - Jan 8
        // Tir (5): Jan 9 - Feb 7
        // Yekatit (6): Feb 8 - Mar 9
        // Megabit (7): Mar 10 - Apr 8
        // Miazia (8): Apr 9 - May 8
        // Ginbot (9): May 9 - Jun 7
        // Sene (10): Jun 8 - Jul 7
        // Hamle (11): Jul 8 - Aug 6
        // Nehase (12): Aug 7 - Sep 5
        // Pagume (13): Sep 6 - Sep 10/11

        if ($month === 9 && $day >= $ethNewYearDay) return 1;  // Meskerem
        if ($month === 10 && $day <= 10) return 1;              // Meskerem (cont.)
        if ($month === 10 && $day >= 11) return 2;              // Tikimt
        if ($month === 11 && $day <= 9) return 2;               // Tikimt (cont.)
        if ($month === 11 && $day >= 10) return 3;              // Hidar
        if ($month === 12 && $day <= 9) return 3;               // Hidar (cont.)
        if ($month === 12 && $day >= 10) return 4;              // Tahsas
        if ($month === 1 && $day <= 8) return 4;                // Tahsas (cont.)
        if ($month === 1 && $day >= 9) return 5;                // Tir
        if ($month === 2 && $day <= 7) return 5;                // Tir (cont.)
        if ($month === 2 && $day >= 8) return 6;                // Yekatit
        if ($month === 3 && $day <= 9) return 6;                // Yekatit (cont.)
        if ($month === 3 && $day >= 10) return 7;               // Megabit
        if ($month === 4 && $day <= 8) return 7;                // Megabit (cont.)
        if ($month === 4 && $day >= 9) return 8;                // Miazia
        if ($month === 5 && $day <= 8) return 8;                // Miazia (cont.)
        if ($month === 5 && $day >= 9) return 9;                // Ginbot
        if ($month === 6 && $day <= 7) return 9;                // Ginbot (cont.)
        if ($month === 6 && $day >= 8) return 10;               // Sene
        if ($month === 7 && $day <= 7) return 10;               // Sene (cont.)
        if ($month === 7 && $day >= 8) return 11;               // Hamle
        if ($month === 8 && $day <= 6) return 11;               // Hamle (cont.)
        if ($month === 8 && $day >= 7) return 12;               // Nehase
        if ($month === 9 && $day <= 5) return 12;               // Nehase (cont.)
        if ($month === 9 && $day >= 6 && $day < $ethNewYearDay) return 13; // Pagume

        return 1; // Default to Meskerem
    }

    /**
     * Get the current Ethiopian year from a Gregorian date.
     */
    private function getCurrentEthiopianYear($gregorianDate): int
    {
        $year = (int) $gregorianDate->year;
        $month = (int) $gregorianDate->month;

        $isGregorianLeap = ($year % 4 === 0 && $year % 100 !== 0) || ($year % 400 === 0);
        $ethNewYearDay = $isGregorianLeap ? 12 : 11;

        // Ethiopian year = Gregorian year - 7 (after Sept 11) or - 8 (before Sept 11)
        if ($month > 9 || ($month === 9 && $day >= $ethNewYearDay)) {
            return $year - 7;
        }
        return $year - 8;
    }

    /**
     * Convert an Ethiopian date to a Gregorian date string.
     *
     * Uses a well-known algorithm based on the Coptic/Ethiopian calendar.
     * Ethiopian months 1-12 have 30 days each; month 13 has 5 (or 6 in leap year) days.
     */
    private function ethiopianToGregorian(int $ethMonth, int $ethDay, int $ethYear): string
    {
        // Clamp day to valid range
        if ($ethMonth <= 12) {
            $ethDay = min($ethDay, 30);
        } else {
            $isEthLeap = ($ethYear % 4 === 3); // Ethiopian leap year
            $ethDay = min($ethDay, $isEthLeap ? 6 : 5);
        }

        // Convert Ethiopian date to Julian Day Number
        // Ethiopian epoch: Meskerem 1, Year 1 = August 29, 8 AD (Julian)
        // Algorithm: JDN = (ethYear - 1) * 365 + floor((ethYear - 1) / 4) + (ethMonth - 1) * 30 + ethDay + 1724220

        $jdn = ($ethYear - 1) * 365 + intdiv($ethYear - 1, 4) + ($ethMonth - 1) * 30 + $ethDay + 1724220;

        // Convert JDN to Gregorian date
        $z = $jdn + 1;
        $alpha = intdiv(($z - 1867216) * 100 - 25, 3652425);
        $a = $z + 1 + $alpha - intdiv($alpha, 4);
        $b = $a + 1524;
        $c = intdiv(($b - 122) * 100 - 25, 36525);
        $d = intdiv($b - 122 - $c * 36525, 100);
        $e = intdiv(($d * 100 - 25) * 100, 36525);
        $day = $b - 122 - intdiv($c * 36525, 100) - intdiv($d * 36525, 100) + $e;
        $month = $d < 14 ? $d - 1 : $d - 13;
        $year = $month > 2 ? $c - 4716 : $c - 4715;

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }
}
