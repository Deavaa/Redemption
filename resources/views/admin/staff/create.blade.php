@extends('layouts.admin')
@section('title', 'Add Staff Member')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.staff.index') }}">Staff & Users</a></li>
                    <li class="active">Add New</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.staff.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="modern-card">
        <form method="POST" action="{{ route('admin.staff.store') }}">@csrf
            {{-- Personal Info Section --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Personal Information</h3>
                        <p class="modern-form-section-desc">Enter the staff member's personal details</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="name">Full Name <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-user modern-input-icon"></i>
                                <input type="text" name="name" id="name" class="modern-input {{ $errors->has('name') ? 'is-invalid' : '' }}" value="{{ old('name') }}" placeholder="e.g. Abebe Kebede" required autofocus autocomplete="off">
                            </div>
                            @error('name')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="email">Email <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-envelope modern-input-icon"></i>
                                <input type="email" name="email" id="email" class="modern-input {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email') }}" placeholder="e.g. abebe@school.com" required autocomplete="off">
                            </div>
                            @error('email')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="employee_id_preview">Employee ID <small>(auto-generated)</small></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-id-card modern-input-icon"></i>
                                <input type="text" id="employee_id_preview" class="modern-input modern-select-locked" value="" placeholder="Select a branch to preview ID" readonly tabindex="-1" data-lpignore="true" data-form-type="other">
                            </div>
                            <input type="hidden" name="employee_id" id="employee_id_hidden" value="">
                            <div class="modern-input-hint"><i class="fas fa-magic"></i> Employee ID will be auto-generated based on the selected branch</div>
                            @error('employee_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="phone">Phone</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-phone modern-input-icon"></i>
                                <input type="tel" name="phone" id="phone" class="modern-input {{ $errors->has('phone') ? 'is-invalid' : '' }}" value="{{ old('phone') }}" placeholder="+251-XXX-XXXXXX" autocomplete="tel">
                            </div>
                            @error('phone')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="gender">Gender</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-venus-mars modern-input-icon"></i>
                                <select name="gender" id="gender" class="modern-input modern-select {{ $errors->has('gender') ? 'is-invalid' : '' }}">
                                    <option value="">Select Gender</option>
                                    <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                            @error('gender')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="qualification">Qualification</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-graduation-cap modern-input-icon"></i>
                                <input type="text" name="qualification" id="qualification" class="modern-input {{ $errors->has('qualification') ? 'is-invalid' : '' }}" value="{{ old('qualification') }}" placeholder="BA, BSc, MA, PhD">
                            </div>
                            @error('qualification')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Role & Branch Section --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-purple">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Role & Branch Assignment</h3>
                        <p class="modern-form-section-desc">Select the user's role and branch if applicable</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="role">Role <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-user-tag modern-input-icon"></i>
                                <select name="role" id="roleSelect" class="modern-input modern-select {{ $errors->has('role') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Role --</option>
                                    @if($isAdmin)
                                    <optgroup label="Administration">
                                        @foreach(['admin' => 'Admin', 'general_manager' => 'General Manager'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('role') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </optgroup>
                                    @endif
                                    @if($isAdmin)
                                    <optgroup label="Academic">
                                        @foreach(['branch_principal' => 'Branch Principal', 'teacher' => 'Teacher', 'registrar' => 'Registrar'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('role') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </optgroup>
                                    @else
                                    <optgroup label="Academic">
                                        @foreach(['teacher' => 'Teacher', 'registrar' => 'Registrar'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('role') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </optgroup>
                                    @endif
                                    @if($isAdmin)
                                    <optgroup label="Finance & HR">
                                        @foreach(['finance' => 'Finance Officer', 'hr' => 'HR Officer', 'cashier' => 'Cashier'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('role') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </optgroup>
                                    @else
                                    <optgroup label="Finance">
                                        @foreach(['cashier' => 'Cashier'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('role') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </optgroup>
                                    @endif
                                    <optgroup label="Operations">
                                        @foreach(['librarian' => 'Librarian', 'staff' => 'Staff'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('role') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            <div class="modern-input-hint" id="roleHint" style="display:none;">
                                <i class="fas fa-info-circle"></i> <span id="roleHintText"></span>
                            </div>
                            @error('role')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group" id="branchField" @if(!$isBranchPrincipal && !in_array(old('role'), $branchRoles)) style="display:none;" @endif>
                            <label class="modern-form-label" for="branch_id">Branch <span class="modern-required">*</span></label>
                            @if($isBranchPrincipal)
                            <div class="modern-input-wrapper">
                                <i class="fas fa-building modern-input-icon"></i>
                                <select name="branch_id" id="branchSelect" class="modern-input modern-select modern-select-locked" disabled>
                                    @foreach($branches as $branch)
                                    @if($branch->id == $authBranchId)
                                    <option value="{{ $branch->id }}" selected>
                                        {{ $branch->name }}@if($branch->is_headquarters) (HQ)@endif
                                    </option>
                                    @endif
                                    @endforeach
                                </select>
                                <input type="hidden" name="branch_id" value="{{ $authBranchId }}">
                            </div>
                            <div class="modern-input-hint modern-input-hint-locked"><i class="fas fa-lock"></i> Locked to your branch — branch principals can only assign staff to their own branch</div>
                            @else
                            <div class="modern-input-wrapper">
                                <i class="fas fa-building modern-input-icon"></i>
                                <select name="branch_id" id="branchSelect" class="modern-input modern-select {{ $errors->has('branch_id') ? 'is-invalid' : '' }}">
                                    <option value="">-- All Branches --</option>
                                    @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}@if($branch->is_headquarters) (HQ)@endif
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="modern-input-hint"><i class="fas fa-info-circle"></i> Branch-scoped roles are limited to their assigned branch</div>
                            @error('branch_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Security Section --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-green">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Account Security</h3>
                        <p class="modern-form-section-desc">Set the login password for this account</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="password">Password <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-key modern-input-icon"></i>
                                <input type="password" name="password" id="password" class="modern-input {{ $errors->has('password') ? 'is-invalid' : '' }}" required minlength="6" placeholder="Minimum 6 characters" autocomplete="new-password">
                            </div>
                            @error('password')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="password_confirmation">Confirm Password <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-check-double modern-input-icon"></i>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="modern-input" required placeholder="Re-enter password" autocomplete="new-password">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Address Section --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-gray">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Address</h3>
                        <p class="modern-form-section-desc">Optional address information</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="address">Address</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-map-marker-alt modern-input-icon" style="top:18px;transform:none;"></i>
                                <textarea name="address" id="address" class="modern-input modern-textarea {{ $errors->has('address') ? 'is-invalid' : '' }}" rows="3" placeholder="e.g. Bole, Addis Ababa">{{ old('address') }}</textarea>
                            </div>
                            @error('address')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="modern-form-actions">
                <a href="{{ route('admin.staff.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
                <button type="submit" class="btn-modern btn-modern-primary">
                    <i class="fas fa-check"></i>
                    <span>Create User</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
.modern-page { animation: fadeSlideIn 0.4s ease-out; }
@keyframes fadeSlideIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

.modern-page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.modern-page-header-left { flex: 1; }
.modern-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }

.modern-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; }

.modern-form-section { border-bottom: 1px solid #f0f0f0; }
.modern-form-section:last-of-type { border-bottom: none; }
.modern-form-section-header { display: flex; align-items: center; gap: 1rem; padding: 1.5rem 2rem 0.75rem; }
.modern-form-section-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
.modern-form-section-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-form-section-icon-green { background: #ecfdf5; color: #10b981; }
.modern-form-section-icon-purple { background: #faf5ff; color: #8b5cf6; }
.modern-form-section-icon-gray { background: #f3f4f6; color: #6b7280; }
.modern-form-section-title { font-size: 1.05rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.modern-form-section-desc { font-size: 0.82rem; color: #9ca3af; margin: 0.15rem 0 0; }
.modern-form-section-body { padding: 1.25rem 2rem 1.75rem; }

.modern-form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; }
.modern-form-span-2 { grid-column: span 2; }
.modern-form-group { display: flex; flex-direction: column; }
.modern-form-label { font-weight: 600; color: #374151; margin-bottom: 0.45rem; font-size: 0.88rem; }
.modern-form-label small { font-weight: 400; color: #9ca3af; font-size: 0.78rem; }
.modern-required { color: #ef4444; font-weight: 700; }

.modern-input-wrapper { position: relative; }
.modern-input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 0.85rem; pointer-events: none; z-index: 1; }
.modern-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.7rem 0.9rem 0.7rem 2.5rem; font-size: 0.9rem; color: #1a1a2e; background: #fff; transition: all 0.2s; box-sizing: border-box; }
.modern-input:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.modern-input::placeholder { color: #c5c9d2; }
.modern-input.is-invalid { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.1); }
.modern-textarea { resize: vertical; min-height: 80px; }
.modern-select { appearance: none; cursor: pointer; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.75rem center; background-repeat: no-repeat; background-size: 1.25rem; padding-right: 2.5rem; }

.modern-input-hint { font-size: 0.78rem; color: #9ca3af; margin-top: 0.3rem; }
.modern-input-hint i { margin-right: 3px; }
.modern-input-hint-locked { color: #d97706; font-weight: 500; }
.modern-select-locked { background: #f3f4f6 !important; cursor: not-allowed !important; opacity: 0.85; border-color: #d1d5db !important; }
.modern-form-error { display: block; color: #ef4444; font-size: 0.8rem; margin-top: 0.35rem; font-weight: 500; }

.modern-form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1.5rem 2rem; border-top: 1px solid #f0f0f0; background: #fafbfc; }

.btn-modern { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.35rem; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border: none; cursor: pointer; transition: all 0.25s; }
.btn-modern-primary { background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff; box-shadow: 0 2px 8px rgba(67,97,238,0.3); }
.btn-modern-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(67,97,238,0.4); color: #fff; }
.btn-modern-outline { background: #fff; color: #4361ee; border: 1.5px solid #4361ee; }
.btn-modern-outline:hover { background: #4361ee; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 16px rgba(67,97,238,0.3); }
.btn-modern-ghost { background: transparent; color: #6b7280; padding: 0.65rem 1rem; }
.btn-modern-ghost:hover { background: #f3f4f6; color: #374151; }

@media (max-width: 768px) {
    .modern-page-header { flex-direction: column; align-items: stretch; }
    .modern-form-section-header { padding: 1.25rem 1.25rem 0.75rem; }
    .modern-form-section-body { padding: 1rem 1.25rem 1.5rem; }
    .modern-form-grid { grid-template-columns: 1fr; }
    .modern-form-span-2 { grid-column: span 1; }
    .modern-form-actions { padding: 1rem 1.25rem; flex-direction: column; }
    .btn-modern { width: 100%; justify-content: center; min-height: 44px; }
}
@media (max-width: 480px) {
    .modern-form-section-header { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
    .modern-input { font-size: 16px; }
    .modern-select { font-size: 16px; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var roleSelect = document.getElementById('roleSelect');
    var branchField = document.getElementById('branchField');
    var roleHint = document.getElementById('roleHint');
    var roleHintText = document.getElementById('roleHintText');
    var branchRoles = @json($branchRoles);

    var roleDescriptions = {
        'admin': 'Full system access to all modules and settings',
        'general_manager': 'Broad access across all branches (no system settings)',
        'branch_principal': 'Manages academic & staff for their assigned branch only',
        'teacher': 'Can enter marks and manage assigned class records',
        'registrar': 'Manages student enrollment, records, and fee payments',
        'finance': 'Full access to fees, budgets, payroll, and financial reports',
        'hr': 'Manages staff, leaves, payroll, and employee assets',
        'cashier': 'Processes fee payments only',
        'librarian': 'Manages the digital library collection',
        'janitor': 'Building maintenance and cleaning staff',
        'guard': 'Campus security and access control',
        'nurse': 'Student and staff health services',
        'secretary': 'Administrative support and office management',
        'staff': 'General staff with basic access'
    };

    var isBranchPrincipal = @json($isBranchPrincipal);
    var authBranchId = @json($authBranchId);
    var employeeIdPreview = document.getElementById('employee_id_preview');
    var employeeIdHidden = document.getElementById('employee_id_hidden');

    function toggleBranchField() {
        var selected = roleSelect.value;
        // Branch principal: always show branch field (locked)
        if (isBranchPrincipal) {
            branchField.style.display = '';
            // Ensure the hidden input always has the correct branch ID
            var hiddenInput = document.querySelector('input[name="branch_id"][type="hidden"]');
            if (hiddenInput) {
                hiddenInput.value = authBranchId;
            }
            // Preview employee ID for the locked branch
            previewEmployeeId(authBranchId);
            return;
        }
        // Show branch field for branch-scoped roles
        if (branchRoles.includes(selected)) {
            branchField.style.display = '';
        } else {
            branchField.style.display = 'none';
        }
        // Show role description hint
        if (selected && roleDescriptions[selected]) {
            roleHintText.textContent = roleDescriptions[selected];
            roleHint.style.display = '';
        } else {
            roleHint.style.display = 'none';
        }
    }

    // Preview employee ID via AJAX when branch changes
    function previewEmployeeId(branchId) {
        if (!branchId) {
            employeeIdPreview.value = '';
            employeeIdPreview.placeholder = 'Select a branch to preview ID';
            employeeIdHidden.value = '';
            return;
        }
        employeeIdPreview.placeholder = 'Loading...';
        fetch('{{ route("admin.staff.api.employee-id-preview") }}?branch_id=' + encodeURIComponent(branchId), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.employee_id) {
                employeeIdPreview.value = data.employee_id;
                employeeIdPreview.placeholder = data.employee_id;
                employeeIdHidden.value = data.employee_id;
            } else {
                employeeIdPreview.value = '';
                employeeIdPreview.placeholder = 'Could not generate preview';
                employeeIdHidden.value = '';
            }
        })
        .catch(function() {
            employeeIdPreview.value = '';
            employeeIdPreview.placeholder = 'Preview unavailable';
            employeeIdHidden.value = '';
        });
    }

    // Listen for branch selection changes
    var branchSelect = document.getElementById('branchSelect');
    if (branchSelect && !branchSelect.disabled) {
        branchSelect.addEventListener('change', function() {
            previewEmployeeId(this.value);
        });
    }

    // If branch principal, preview immediately
    if (isBranchPrincipal && authBranchId) {
        previewEmployeeId(authBranchId);
    }

    roleSelect.addEventListener('change', toggleBranchField);
    toggleBranchField(); // Initial state
});
</script>
@endpush
@endsection
