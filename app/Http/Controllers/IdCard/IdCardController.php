<?php
namespace App\Http\Controllers\IdCard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IdCard;
use App\Models\Student;

class IdCardController extends Controller
{
    public function index(Request $r)
    {
        $q = IdCard::with('student');
        if ($r->filled('search')) {
            $s = $r->search;
            $q->where('card_number', 'LIKE', "%$s%")->orWhereHas('student', function($x) use ($s) {
                $x->where('first_name', 'LIKE', "%$s%")->orWhere('last_name', 'LIKE', "%$s%");
            });
        }
        if ($r->filled('status')) $q->where('status', $r->status);
        $data = $q->latest()->paginate(20);
        $totalCards = IdCard::count();
        return view('admin.IdCard.index', compact('data', 'totalCards'));
    }

    public function create()
    {
        $students = Student::where('status', 'active')->orderBy('first_name')->get();
        return view('admin.IdCard.create', compact('students'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'student_id' => 'required|exists:students,id',
            'card_number' => 'required|string|max:255|unique:id_cards,card_number',
            'issue_date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:issue_date',
            'status' => 'required|in:active,expired,revoked',
        ]);
        IdCard::create($r->only(['student_id','card_number','issue_date','valid_until','status']));
        return redirect()->route("admin.id-cards.index")->with('success','ID Card created successfully');
    }

    public function show(IdCard $item)
    {
        $item->load('student');
        return view('admin.IdCard.show', compact('item'));
    }

    public function edit(IdCard $item)
    {
        $students = Student::where('status', 'active')->orderBy('first_name')->get();
        return view('admin.IdCard.edit', compact('item', 'students'));
    }

    public function update(Request $r, IdCard $item)
    {
        $r->validate([
            'student_id' => 'required|exists:students,id',
            'card_number' => 'required|string|max:255|unique:id_cards,card_number,' . $item->id,
            'issue_date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:issue_date',
            'status' => 'required|in:active,expired,revoked',
        ]);
        $item->update($r->only(['student_id','card_number','issue_date','valid_until','status']));
        return redirect()->route("admin.id-cards.index")->with('success','ID Card updated successfully');
    }

    public function destroy(IdCard $item) { $item->delete(); return back()->with('success','ID Card deleted successfully'); }
}
