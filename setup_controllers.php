<?php

echo "=== Creating Controllers ===\n\n";

require __DIR__ . '/vendor/autoload.php';
 $app = require_once __DIR__ . '/bootstrap/app.php';
 $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
 $kernel->bootstrap();

 $dir = app_path('Http/Controllers');

function mk($path, $code) {
    global $dir;
    $full = "$dir/$path";
    $folder = dirname($full);
    if (!is_dir($folder)) mkdir($folder, 0755, true);
    file_put_contents($full, $code);
    echo "  [OK] $path\n";
}

mk('AuthController.php', <<<'P'
<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }

    public function login(Request $r) {
        $r->validate(['email'=>'required|email','password'=>'required']);
        if (Auth::attempt($r->only('email','password'), $r->boolean('remember'))) {
            $r->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }
        throw ValidationException::withMessages(['email'=>'Invalid credentials']);
    }

    public function logout(Request $r) {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect('/');
    }
}
P);

mk('HomeController.php', <<<'P'
<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Slider;
use App\Models\TeamMember;
use App\Models\GalleryImage;
use App\Models\Branch;
use App\Models\Setting;

class HomeController extends Controller
{
    public function index() {
        $sliders = Slider::where('is_active',1)->orderBy('sort_order')->get();
        $team = TeamMember::where('is_active',1)->orderBy('sort_order')->take(8)->get();
        $gallery = GalleryImage::where('is_active',1)->orderBy('sort_order')->take(8)->get();
        $branches = Branch::where('is_active',1)->get();
        $schoolName = Setting::get('school_name','School of Redemption');
        $schoolMotto = Setting::get('school_motto','Nurturing Excellence');
        return view('home', compact('sliders','team','gallery','branches','schoolName','schoolMotto'));
    }

    public function about() {
        $team = TeamMember::where('is_active',1)->orderBy('sort_order')->get();
        $schoolName = Setting::get('school_name','School of Redemption');
        return view('about', compact('team','schoolName'));
    }

    public function gallery() {
        $images = GalleryImage::where('is_active',1)->orderBy('sort_order')->get();
        return view('gallery', compact('images'));
    }

    public function contact() {
        $branches = Branch::where('is_active',1)->get();
        return view('contact', compact('branches'));
    }

    public function team() {
        $team = TeamMember::where('is_active',1)->orderBy('sort_order')->get();
        return view('team', compact('team'));
    }

    public function contactSubmit(Request $r) {
        $r->validate(['name'=>'required','email'=>'required|email','subject'=>'required','message'=>'required']);
        \App\Models\ContactMessage::create($r->only('name','email','phone','subject','message','branch_id'));
        return back()->with('success','Message sent successfully!');
    }
}
P);

mk('DashboardController.php', <<<'P'
<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\User;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\FeePayment;
use App\Models\ContactMessage;

class DashboardController extends Controller
{
    public function index() {
        $totalStudents = Student::count();
        $totalTeachers = User::where('role','teacher')->count();
        $totalClasses = Classroom::count();
        $unreadMessages = ContactMessage::where('is_read',0)->count();
        $recentPayments = FeePayment::latest()->take(5)->get();
        $ay = AcademicYear::where('is_current',1)->first();
        return view('admin.dashboard', compact('totalStudents','totalTeachers','totalClasses','unreadMessages','recentPayments','ay'));
    }
}
P);

// ---- ERP CRUD Controllers ----

function crud($name, $model) {
    global $dir;
    $code = <<<"P"
<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\\$model;

class {$name}Controller extends Controller
{
    public function index() { \$data = $model::latest()->paginate(20); return view('admin.$name.index', compact('data')); }
    public function create() { return view('admin.$name.create'); }
    public function store(Request \$r) { $model::create(\$r->all()); return redirect()->route("admin.$name.index")->with('success','Created successfully'); }
    public function show($model \$item) { return view('admin.$name.show', compact('item')); }
    public function edit($model \$item) { return view('admin.$name.edit', compact('item')); }
    public function update(Request \$r, $model \$item) { \$item->update(\$r->all()); return redirect()->route("admin.$name.index")->with('success','Updated successfully'); }
    public function destroy($model \$item) { \$item->delete(); return back()->with('success','Deleted successfully'); }
}
P;
    mk("$name/{$name}Controller.php", $code);
}

crud('AcademicYear','AcademicYear');
crud('Term','Term');
crud('Classroom','Classroom');
crud('Section','Section');
crud('Subject','Subject');
crud('Student','Student');
crud('ParentModel','ParentModel');
crud('TeacherAssignment','TeacherAssignment');
crud('Exam','Exam');
crud('MarkEntry','MarkEntry');
crud('Certificate','Certificate');
crud('IdCard','IdCard');
crud('ProgressReport','ProgressReport');
crud('PerformanceReport','PerformanceReport');
crud('ClassAsset','ClassAsset');
crud('EmployeeAsset','EmployeeAsset');
crud('Fee','Fee');
crud('FeePayment','FeePayment');
crud('Leave','Leave');
crud('Payroll','Payroll');
crud('Budget','Budget');
crud('IncomeExpense','IncomeExpense');
crud('FinanceStatement','FinanceStatement');
crud('Audit','Audit');
crud('Branch','Branch');
crud('TeamMember','TeamMember');
crud('GalleryImage','GalleryImage');
crud('GalleryVideo','GalleryVideo');
crud('Slider','Slider');
crud('Setting','Setting');
crud('ContactMessage','ContactMessage');

echo "\n=== DONE! All controllers created ===\n";
