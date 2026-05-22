<?php
namespace App\Http\Controllers\ParentModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParentModel;
use App\Models\User;
use App\Models\Role;

class ParentModelController extends Controller
{
    public function index(){
        $data = ParentModel::with('students')->latest()->paginate(20);
        $totalParents = ParentModel::count();
        return view("admin.ParentModel.index", compact("data","totalParents"));
    }
    public function create(){ return view("admin.ParentModel.create"); }

    public function store(Request $r){
        $validated = $r->validate([
            "father_name"         => "required|string|max:255",
            "father_phone"        => "required|string|max:20",
            "father_occupation"   => "nullable|string|max:255",
            "mother_name"         => "nullable|string|max:255",
            "mother_phone"        => "nullable|string|max:20",
            "mother_occupation"   => "nullable|string|max:255",
            "guardian_name"       => "nullable|string|max:255",
            "guardian_relation"   => "nullable|string|max:255",
            "guardian_phone"      => "nullable|string|max:20",
        ]);

        // Auto-create a user account for the parent
        $year = date('Y');
        $lastParentUser = User::where('id_number', 'LIKE', "PAR-{$year}-%")
            ->orderBy('id_number', 'desc')->first();
        $nextNum = 1;
        if ($lastParentUser && $lastParentUser->id_number) {
            $parts = explode('-', $lastParentUser->id_number);
            $nextNum = (int)end($parts) + 1;
        }
        $idNumber = "PAR-{$year}-" . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
        $email = $idNumber . '@redemption.edu';
        $defaultPassword = str_replace([' ', '+', '-'], '', $validated['father_phone']); // e.g. 0911998833

        $user = User::create([
            'name'       => $validated['father_name'],
            'email'      => $email,
            'id_number'  => $idNumber,
            'password'   => bcrypt($defaultPassword),
            'role'       => 'parent',
            'phone'      => $validated['father_phone'],
            'is_active'  => true,
        ]);
        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
            $user->save();
        }

        // Assign Spatie 'parent' role
        $parentRole = Role::where('name', 'parent')->first();
        if ($parentRole && !$user->roles()->where('role_id', $parentRole->id)->exists()) {
            $user->roles()->attach($parentRole->id);
        }

        $validated['user_id'] = $user->id;

        ParentModel::create($validated);

        return redirect()->route("admin.parents.index")->with("success","Parent added successfully! Login: {$email} / Password: {$defaultPassword}");
    }

    public function show(ParentModel $parent){
        $parent->load('students');
        return view("admin.ParentModel.show", ["item" => $parent]);
    }
    public function edit(ParentModel $parent){ return view("admin.ParentModel.edit", ["item" => $parent]); }

    public function update(Request $r, ParentModel $parent){
        $validated = $r->validate([
            "father_name"         => "required|string|max:255",
            "father_phone"        => "required|string|max:20",
            "father_occupation"   => "nullable|string|max:255",
            "mother_name"         => "nullable|string|max:255",
            "mother_phone"        => "nullable|string|max:20",
            "mother_occupation"   => "nullable|string|max:255",
            "guardian_name"       => "nullable|string|max:255",
            "guardian_relation"   => "nullable|string|max:255",
            "guardian_phone"      => "nullable|string|max:20",
        ]);

        $parent->update($validated);

        // Update the linked user account name & phone too
        if ($parent->user) {
            $parent->user->update([
                'name'  => $validated['father_name'],
                'phone' => $validated['father_phone'],
            ]);
        }

        return redirect()->route("admin.parents.index")->with("success","Parent updated successfully");
    }

    public function destroy(ParentModel $parent){
        // Also delete the linked user account
        if ($parent->user_id) {
            User::where('id', $parent->user_id)->delete();
        }
        $parent->delete();
        return back()->with("success","Parent deleted");
    }
}
