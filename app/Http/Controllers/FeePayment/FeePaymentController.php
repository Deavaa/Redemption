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
        $students = Student::orderBy("full_name")->get();
        return view("admin.FeePayment.create", compact("fees","students"));
    }
    public function store(Request $r){
        $r->validate(["fee_id"=>"required|exists:fees,id","student_id"=>"required|exists:students,id","amount_paid"=>"required|numeric|min:0","payment_date"=>"required|date","payment_method"=>"required|in:cash,bank,mobile,cheque,online","status"=>"required|in:paid,partial,pending,overdue"]);
        FeePayment::create($r->only(['fee_id','student_id','amount_paid','payment_date','payment_method','transaction_id','receipt_number','status']));
        return redirect()->route("admin.fee-payments.index")->with("success","Payment recorded");
    }
    public function show(FeePayment $fee_payment){ return view("admin.FeePayment.show", ["item" => $fee_payment]); }
    public function edit(FeePayment $fee_payment){
        $fees = Fee::with("classroom","academicYear")->where("is_active",1)->get();
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