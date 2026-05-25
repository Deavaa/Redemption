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
        $branchScope = request()->attributes->get('branch_scope');
        
        $query = ParentModel::with('students')->latest();
        
        // Branch principal: only see parents who have students in their branch
        if ($branchScope) {
            $query->whereHas('students', function ($q) use ($branchScope) {
                $q->where('branch_id', $branchScope);
            });
        }
        
        $data = $query->paginate(20);
        $totalParents = $branchScope 
            ? ParentModel::whereHas('students', function ($q) use ($branchScope) { $q->where('branch_id', $branchScope); })->count()
            : ParentModel::count();
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

        // Normalize phone numbers to Ethiopian format 0XXXXXXXXX
        if (!empty($validated['father_phone'])) {
            $validated['father_phone'] = $this->normalizePhone($validated['father_phone']);
        }
        if (!empty($validated['mother_phone'])) {
            $validated['mother_phone'] = $this->normalizePhone($validated['mother_phone']);
        }
        if (!empty($validated['guardian_phone'])) {
            $validated['guardian_phone'] = $this->normalizePhone($validated['guardian_phone']);
        }

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
        $defaultPassword = '123456'; // Default password for all users

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

        // Assign 'parent' role via custom RBAC
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

    /**
     * Normalize phone number to Ethiopian local format: 0XXXXXXXXX
     */
    private function normalizePhone(string $input): string
    {
        $cleaned = preg_replace('/[\s\-().]/', '', $input);
        if (preg_match('/^(\+251|00251)(\d{9})$/', $cleaned, $m)) {
            return '0' . $m[2];
        }
        if (preg_match('/^251(\d{9})$/', $cleaned, $m)) {
            return '0' . $m[1];
        }
        if (preg_match('/^0\d{9}$/', $cleaned)) {
            return $cleaned;
        }
        return $cleaned;
    }
}
