<?php
namespace App\Http\Controllers\Certificate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Certificate;
use App\Models\Student;

class CertificateController extends Controller
{
    public function index(Request $r)
    {
        $q = Certificate::with('student');
        if ($r->filled('search')) {
            $s = $r->search;
            $q->whereHas('student', function($x) use ($s) {
                $x->where('first_name', 'LIKE', "%$s%")->orWhere('last_name', 'LIKE', "%$s%");
            })->orWhere('certificate_number', 'LIKE', "%$s%");
        }
        if ($r->filled('type')) $q->where('type', $r->type);
        $data = $q->latest()->paginate(20);
        $totalCertificates = Certificate::count();
        return view('admin.Certificate.index', compact('data', 'totalCertificates'));
    }

    public function create()
    {
        $students = Student::where('status', 'active')->orderBy('first_name')->get();
        return view('admin.Certificate.create', compact('students'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:transfer,completion,character,bonafide,other',
            'certificate_number' => 'required|string|max:255|unique:certificates,certificate_number',
            'issue_date' => 'required|date',
            'content' => 'nullable|string',
            'template' => 'nullable|string|max:255',
        ]);
        Certificate::create($r->only(['student_id','type','certificate_number','issue_date','content','template']));
        return redirect()->route("admin.certificates.index")->with('success','Certificate created successfully');
    }

    public function show(Certificate $item)
    {
        $item->load('student');
        return view('admin.Certificate.show', compact('item'));
    }

    public function edit(Certificate $item)
    {
        $students = Student::where('status', 'active')->orderBy('first_name')->get();
        return view('admin.Certificate.edit', compact('item', 'students'));
    }

    public function update(Request $r, Certificate $item)
    {
        $r->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:transfer,completion,character,bonafide,other',
            'certificate_number' => 'required|string|max:255|unique:certificates,certificate_number,' . $item->id,
            'issue_date' => 'required|date',
            'content' => 'nullable|string',
            'template' => 'nullable|string|max:255',
        ]);
        $item->update($r->only(['student_id','type','certificate_number','issue_date','content','template']));
        return redirect()->route("admin.certificates.index")->with('success','Certificate updated successfully');
    }

    public function destroy(Certificate $item)
    {
        $item->delete();
        return back()->with('success','Certificate deleted successfully');
    }
}
