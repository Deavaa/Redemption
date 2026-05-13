<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class StaffController extends Controller
{
    public function index() { $staff=User::whereIn('role',['teacher','admin'])->orderBy('name','asc')->paginate(20); return view('admin.staff.index',compact('staff')); }
    public function create() { return view('admin.staff.create'); }
    public function store(Request $request) {
        $request->validate(['name'=>'required|string|max:255','email'=>'required|email|unique:users,email','phone'=>'nullable|string|max:20','role'=>'required|in:teacher,admin','password'=>'required|string|min:6|confirmed','gender'=>'nullable|string|max:10','qualification'=>'nullable|string|max:255','address'=>'nullable|string|max:500']);
        User::create(['name'=>$request->name,'email'=>$request->email,'phone'=>$request->phone,'role'=>$request->role,'password'=>Hash::make($request->password),'gender'=>$request->gender,'qualification'=>$request->qualification,'address'=>$request->address]);
        return redirect()->route('admin.staff.index')->with('success','Staff member added.');
    }
    public function edit(User $user) { return view('admin.staff.edit',compact('user')); }
    public function update(Request $request, User $user) {
        $request->validate(['name'=>'required|string|max:255','email'=>'required|email|unique:users,email,'.$user->id,'phone'=>'nullable|string|max:20','role'=>'required|in:teacher,admin','password'=>'nullable|string|min:6|confirmed','gender'=>'nullable|string|max:10','qualification'=>'nullable|string|max:255','address'=>'nullable|string|max:500']);
        $data=$request->except('password');
        if ($request->filled('password')) $data['password']=Hash::make($request->password);
        $user->update($data);
        return redirect()->route('admin.staff.index')->with('success','Staff member updated.');
    }
    public function destroy(User $user) {
        if ($user->role==='admin' && User::where('role','admin')->count()<=1) return redirect()->route('admin.staff.index')->with('error','Cannot delete last admin.');
        $user->delete();
        return redirect()->route('admin.staff.index')->with('success','Staff member removed.');
    }
}