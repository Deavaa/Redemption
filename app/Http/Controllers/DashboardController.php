<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\User;
use App\Models\ClassRoom;
use App\Models\Branch;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\FeePayment;
use App\Models\Fee;
use App\Models\ContactMessage;

class DashboardController extends Controller
{
    public function index() {
        $user = Auth::user();
        $branchId = $user->branch_id;
        $isBranchScoped = in_array($user->role, ['branch_principal', 'finance', 'hr', 'cashier', 'librarian', 'registrar']) && $branchId;

        if ($isBranchScoped) {
            // Branch-scoped: only show data for this user's branch
            $totalStudents = Student::where('branch_id', $branchId)->count();
            $totalTeachers = User::where('role', 'teacher')->where('branch_id', $branchId)->count();
            $totalClasses = ClassRoom::where('branch_id', $branchId)->count();
            $totalBranches = 1; // They can only see their own branch
            $totalSubjects = Subject::count(); // Subjects are global (no branch_id)
            $unreadMessages = ContactMessage::where('is_read', 0)->count(); // Contact messages are global

            // Recent payments for students in this branch only
            $recentPayments = FeePayment::whereHas('student', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })->latest()->take(5)->get();

            $currentYear = AcademicYear::where('is_current', 1)->first();

            // Staff in this branch only
            $totalStaff = User::whereNotIn('role', ['student', 'parent'])
                ->where('branch_id', $branchId)
                ->count();

            // Fee stats for this branch only
            $totalFeeCollected = FeePayment::whereHas('student', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })->sum('amount_paid');

            $totalFeeExpected = Fee::where('branch_id', $branchId)->sum('amount');

            $pendingFees = max(0, $totalFeeExpected - $totalFeeCollected);
        } else {
            // Admin / super_admin / general_manager / teacher: see everything
            $totalStudents = Student::count();
            $totalTeachers = User::where('role', 'teacher')->count();
            $totalClasses = ClassRoom::count();
            $totalBranches = Branch::count();
            $totalSubjects = Subject::count();
            $unreadMessages = ContactMessage::where('is_read', 0)->count();
            $recentPayments = FeePayment::latest()->take(5)->get();
            $currentYear = AcademicYear::where('is_current', 1)->first();
            $totalStaff = User::whereNotIn('role', ['student', 'parent'])->count();
            $totalFeeCollected = FeePayment::sum('amount_paid');
            $totalFeeExpected = Fee::sum('amount');
            $pendingFees = max(0, $totalFeeExpected - $totalFeeCollected);
        }

        // Pass branch info so the view can adapt labels
        $branchName = $isBranchScoped ? Branch::find($branchId)?->name : null;

        return view('admin.dashboard', compact(
            'totalStudents','totalTeachers','totalClasses','totalBranches','totalSubjects',
            'unreadMessages','recentPayments','currentYear','totalStaff',
            'totalFeeCollected','totalFeeExpected','pendingFees',
            'isBranchScoped','branchName'
        ));
    }
}
