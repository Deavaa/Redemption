@extends('layouts.admin')
@section('title', 'Add Staff Member')
@push('styles')
<style>
.role-group-label { font-size: .7rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .5px; padding: 6px 12px 2px; }
.role-option { padding: 6px 12px; }
.role-option:hover { background: #f0f4ff; }
#branchField { transition: all .3s ease; }
#branchField.hidden-field { opacity: 0; max-height: 0; overflow: hidden; margin: 0; padding: 0; }
#branchField.visible-field { opacity: 1; max-height: 80px; }
</style>
@endpush
@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Add Staff Member</h4>
            <p class="text-muted mb-0">Create a new user account with a specific role</p>
        </div>
        <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-light fw-semibold"><i class="bi bi-person-plus me-1"></i> User Information</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.staff.store') }}">@csrf
                        <div class="row g-3">
                            {{-- Personal Info --}}
                            <div class="col-12"><hr class="mt-0 mb-2"><span class="badge bg-light text-dark border px-2 py-1"><i class="fas fa-user me-1"></i> Personal Information</span></div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">ID Number</label>
                                <input type="text" name="id_number" class="form-control" value="{{ old('id_number') }}" placeholder="Employee ID">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+251-XXX-XXXXXX">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="">-- Select --</option>
                                    <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Qualification</label>
                                <input type="text" name="qualification" class="form-control" value="{{ old('qualification') }}" placeholder="BA, BSc, MA, PhD">
                            </div>

                            {{-- Role & Branch --}}
                            <div class="col-12"><hr class="mt-3 mb-2"><span class="badge bg-light text-dark border px-2 py-1"><i class="fas fa-shield-alt me-1"></i> Role & Branch</span></div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                                <select name="role" id="roleSelect" class="form-select" required>
                                    <option value="">-- Select Role --</option>
                                    <optgroup label="Administration">
                                        @foreach(['admin' => 'Admin', 'general_manager' => 'General Manager'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('role') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Academic">
                                        @foreach(['branch_principal' => 'Branch Principal', 'teacher' => 'Teacher', 'registrar' => 'Registrar'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('role') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Finance & HR">
                                        @foreach(['finance' => 'Finance Officer', 'hr' => 'HR Officer', 'cashier' => 'Cashier'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('role') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Operations">
                                        @foreach(['librarian' => 'Librarian', 'staff' => 'Staff'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('role') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-md-6" id="branchField">
                                <label class="form-label fw-semibold">Branch <span class="text-muted small">(for branch-scoped roles)</span></label>
                                <select name="branch_id" id="branchSelect" class="form-select">
                                    <option value="">-- All Branches --</option>
                                    @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}@if($branch->is_headquarters) (HQ)@endif
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Security --}}
                            <div class="col-12"><hr class="mt-3 mb-2"><span class="badge bg-light text-dark border px-2 py-1"><i class="fas fa-lock me-1"></i> Security</span></div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required minlength="6">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>

                            {{-- Address --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">Address</label>
                                <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Create User</button>
                            <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var roleSelect = document.getElementById('roleSelect');
    var branchField = document.getElementById('branchField');
    var branchRoles = @json($branchRoles);

    function toggleBranchField() {
        var selected = roleSelect.value;
        if (branchRoles.includes(selected)) {
            branchField.classList.remove('hidden-field');
            branchField.classList.add('visible-field');
        } else {
            branchField.classList.remove('visible-field');
            branchField.classList.add('hidden-field');
        }
    }

    roleSelect.addEventListener('change', toggleBranchField);
    toggleBranchField(); // Initial state
});
</script>
@endpush
@endsection
