<?php
namespace App\Http\Controllers\FeePayment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FeePayment;
use App\Models\Fee;
use App\Models\Student;
use App\Models\StudentEnrollment;
class FeePaymentController extends Controller
{
    public function index(Request $request){
        $branchScope = $request->attributes->get('branch_scope');

        $query = FeePayment::with("fee","student");
        
        // Branch scope: principals only see payments from their branch students
        if ($branchScope) {
            $query->whereHas('student', function($q) use ($branchScope) {
                $q->where('branch_id', $branchScope);
            });
        }

        $data = $query->latest()->paginate(20);

        // Stats also branch-scoped
        $baseQuery = FeePayment::query();
        if ($branchScope) {
            $baseQuery->whereHas('student', function($q) use ($branchScope) {
                $q->where('branch_id', $branchScope);
            });
        }

        $totalPayments = (clone $baseQuery)->count();
        $totalCollected = (clone $baseQuery)->sum("amount_paid");

        return view("admin.FeePayment.index", compact("data","totalPayments","totalCollected"));
    }
    public function create(Request $request){
        $branchScope = $request->attributes->get('branch_scope');
        
        $fees = Fee::with("classroom","academicYear","branch")->where("is_active",1)
            ->when($branchScope, function($q) use ($branchScope) {
                $q->whereHas('classroom', function($q2) use ($branchScope) {
                    $q2->where('branch_id', $branchScope);
                });
            })
            ->get();
        $students = Student::orderBy("full_name")
            ->when($branchScope, function($q) use ($branchScope) {
                $q->where('branch_id', $branchScope);
            })
            ->get();
        return view("admin.FeePayment.create", compact("fees","students"));
    }

    /**
     * Get applicable fees for a specific student based on their enrollment.
     * Filters fees by enrollment_type and branch_id matching the student's enrollment.
     */
    public function getApplicableFees(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        $academicYearId = $request->input('academic_year_id');

        // Find the student's enrollment for the given academic year
        $enrollment = StudentEnrollment::where('student_id', $studentId)
            ->when($academicYearId, function($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            })
            ->latest()
            ->first();

        $enrollmentType = $enrollment?->enrollment_type ?? 'new';
        $branchId = $student->branch_id;

        // Get fees that match the student's enrollment type OR have enrollment_type = 'all'
        // AND match the student's branch OR have no branch specified
        $fees = Fee::with('classroom', 'academicYear', 'branch')
            ->where('is_active', 1)
            ->where(function ($q) use ($enrollmentType) {
                $q->where('enrollment_type', 'all')
                  ->orWhere('enrollment_type', $enrollmentType);
            })
            ->where(function ($q) use ($branchId) {
                $q->whereNull('branch_id')
                  ->orWhere('branch_id', $branchId);
            })
            ->when($academicYearId, function($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            })
            ->get();

        return response()->json([
            'enrollment_type' => $enrollmentType,
            'enrollment' => $enrollment,
            'fees' => $fees->map(function ($fee) {
                return [
                    'id' => $fee->id,
                    'fee_type' => $fee->fee_type,
                    'amount' => $fee->amount,
                    'due_date' => $fee->due_date?->format('Y-m-d'),
                    'class_name' => $fee->classroom?->name,
                    'academic_year' => $fee->academicYear?->name,
                    'enrollment_type_label' => $fee->enrollment_type_label,
                    'branch_name' => $fee->branch?->name ?? 'All Branches',
                ];
            }),
        ]);
    }

    public function store(Request $r){
        $r->validate(["fee_id"=>"required|exists:fees,id","student_id"=>"required|exists:students,id","amount_paid"=>"required|numeric|min:0","payment_date"=>"required|date","payment_method"=>"required|in:cash,bank,mobile,cheque,online","status"=>"required|in:paid,partial,pending,overdue"]);
        
        $payment = FeePayment::create($r->only(['fee_id','student_id','amount_paid','payment_date','payment_method','transaction_id','receipt_number','status']));

        // Notify about fee payment
        try {
            $student = Student::find($r->student_id);
            if ($student) {
                \App\Services\AlertService::notifyFeePayment(
                    $student->branch_id,
                    $student->full_name,
                    (float) $r->amount_paid
                );
            }
        } catch (\Exception $e) {
            \Log::warning('Fee payment notification failed: ' . $e->getMessage());
        }

        return redirect()->route("admin.fee-payments.index")->with("success","Payment recorded");
    }
    public function show(FeePayment $fee_payment){ return view("admin.FeePayment.show", ["item" => $fee_payment]); }
    public function edit(FeePayment $fee_payment){
        $fees = Fee::with("classroom","academicYear","branch")->where("is_active",1)->get();
        $students = Student::orderBy("full_name")->get();
        return view("admin.FeePayment.edit", ['item' => $fee_payment, 'fees' => $fees, 'students' => $students]);
    }
    public function update(Request $r, FeePayment $fee_payment){
        $r->validate(["fee_id"=>"required|exists:fees,id","student_id"=>"required|exists:students,id","amount_paid"=>"required|numeric|min:0","payment_date"=>"required|date","payment_method"=>"required|in:cash,bank,mobile,cheque,online","status"=>"required|in:paid,partial,pending,overdue"]);
        $fee_payment->update($r->only(['fee_id','student_id','amount_paid','payment_date','payment_method','transaction_id','receipt_number','status']));
        return redirect()->route("admin.fee-payments.index")->with("success","Updated");
    }
    public function destroy(FeePayment $fee_payment){ $fee_payment->delete(); return back()->with("success","Deleted"); }
}
