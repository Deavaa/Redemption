<?php
namespace App\Http\Controllers\FeePayment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FeePayment;
use App\Models\Fee;
use App\Models\Student;
class FeePaymentController extends Controller
{
    public function index(){
        $data = FeePayment::with("fee","student")->latest()->paginate(20);
        $totalPayments = FeePayment::count();
        $totalCollected = FeePayment::sum("amount_paid");
        return view("admin.FeePayment.index", compact("data","totalPayments","totalCollected"));
    }
    public function create(){
        $fees = Fee::with("classroom","academicYear")->where("is_active",1)->get();
        $students = Student::orderBy("first_name")->get();
        return view("admin.FeePayment.create", compact("fees","students"));
    }
    public function store(Request $r){
        $r->validate(["fee_id"=>"required|exists:fees,id","student_id"=>"required|exists:students,id","amount_paid"=>"required|numeric|min:0","payment_date"=>"required|date","payment_method"=>"required|in:cash,bank,mobile,cheque,online","status"=>"required|in:paid,partial,pending,overdue"]);
        FeePayment::create($r->all());
        return redirect()->route("admin.fee-payments.index")->with("success","Payment recorded");
    }
    public function show(FeePayment $item){ return view("admin.FeePayment.show", compact("item")); }
    public function edit(FeePayment $item){
        $fees = Fee::with("classroom","academicYear")->where("is_active",1)->get();
        $students = Student::orderBy("first_name")->get();
        return view("admin.FeePayment.edit", compact("item","fees","students"));
    }
    public function update(Request $r, FeePayment $item){
        $r->validate(["fee_id"=>"required|exists:fees,id","student_id"=>"required|exists:students,id","amount_paid"=>"required|numeric|min:0","payment_date"=>"required|date","payment_method"=>"required|in:cash,bank,mobile,cheque,online","status"=>"required|in:paid,partial,pending,overdue"]);
        $item->update($r->all());
        return redirect()->route("admin.fee-payments.index")->with("success","Updated");
    }
    public function destroy(FeePayment $item){ $item->delete(); return back()->with("success","Deleted"); }
}