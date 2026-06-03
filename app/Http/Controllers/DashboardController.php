<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\User;
use App\Models\Classroom;
use App\Models\Branch;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\FeePayment;
use App\Models\Fee;
use App\Models\ContactMessage;

class DashboardController extends Controller
{
    public function index() {
        $totalStudents = Student::count();
        $totalTeachers = User::where('role','teacher')->count();
        $totalClasses = Classroom::count();
        $totalBranches = Branch::count();
        $totalSubjects = Subject::count();
        $unreadMessages = ContactMessage::where('is_read',0)->count();
        $recentPayments = FeePayment::latest()->take(5)->get();
        $currentYear = AcademicYear::where('is_current',1)->first();
        $totalStaff = User::whereNotIn('role', ['student', 'parent'])->count();
        $totalFeeCollected = FeePayment::sum('amount_paid');
        $totalFeeExpected = Fee::sum('amount');
        $pendingFees = max(0, $totalFeeExpected - $totalFeeCollected);
        return view('admin.dashboard', compact(
            'totalStudents','totalTeachers','totalClasses','totalBranches','totalSubjects',
            'unreadMessages','recentPayments','currentYear','totalStaff',
            'totalFeeCollected','totalFeeExpected','pendingFees'
        ));
    }
}
