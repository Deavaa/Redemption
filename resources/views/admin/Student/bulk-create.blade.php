@extends('layouts.admin')
@section('title', 'Bulk Student Enrollment')

@section('content')
<div class="sl-page">
    {{-- Page Header --}}
    <div class="sl-header">
        <div class="sl-header-left">
            <nav aria-label="breadcrumb" class="sl-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.students.index') }}">Students</a></li>
                    <li class="active">Bulk Enrollment</li>
                </ol>
            </nav>
        </div>
        <div class="sl-header-right">
            <a href="{{ route('admin.students.index') }}" class="sl-btn sl-btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Student List
            </a>
        </div>
    </div>

    {{-- Info Box --}}
    <div class="sl-info-box">
        <div class="sl-info-icon"><i class="fas fa-info-circle"></i></div>
        <div>
            <h4 class="sl-info-title">Bulk Student Enrollment</h4>
            <p class="sl-info-desc">Enroll multiple students at once. All students will share the same branch, class/section, and academic year. Admission numbers, roll numbers, and student IDs will be auto-generated. Default password for all students is <strong>123456</strong>.</p>
        </div>
    </div>

    {{-- Excel Download/Upload Section --}}
    <div class="sl-card" style="margin-top:0.75rem;">
        <div class="sl-form-section">
            <div class="sl-form-section-head">
                <div class="sl-form-section-icon sl-form-icon-purple"><i class="fas fa-file-csv"></i></div>
                <div>
                    <h3 class="sl-form-section-title">File Upload</h3>
                    <p class="sl-form-section-desc">Download the template, fill in student data, and upload the completed file (CSV or XLSX)</p>
                </div>
            </div>
            <div class="sl-form-section-body">
                <div class="sl-excel-upload-area">
                    {{-- Step 1: Download Template --}}
                    <div class="sl-excel-step">
                        <div class="sl-excel-step-num">1</div>
                        <div class="sl-excel-step-content">
                            <h4 class="sl-excel-step-title">Download Template</h4>
                            <p class="sl-excel-step-desc">Get the template with the correct column headers (CSV format by default; XLSX if PhpSpreadsheet is available)</p>
                            <a href="{{ route('admin.students.download-template') }}" class="sl-btn sl-btn-outline sl-btn-green">
                                <i class="fas fa-download"></i> Download Template
                            </a>
                        </div>
                    </div>

                    {{-- Step 2: Upload Filled File --}}
                    <div class="sl-excel-step">
                        <div class="sl-excel-step-num">2</div>
                        <div class="sl-excel-step-content">
                            <h4 class="sl-excel-step-title">Upload Filled File</h4>
                            <p class="sl-excel-step-desc">Select the enrollment settings and upload your completed CSV or XLSX file</p>
                            <form method="POST" action="{{ route('admin.students.upload-students') }}" enctype="multipart/form-data" id="uploadForm" style="margin-top:0.5rem;">
                                @csrf
                                {{-- Shared enrollment settings for upload --}}
                                <div class="sl-upload-settings">
                                    <div class="sl-form-grid" style="margin-bottom:0.75rem;">
                                        <div class="sl-form-group">
                                            <label class="sl-form-label" for="upload_branch_id">Branch <span class="sl-required">*</span></label>
                                            <div class="sl-input-wrap">
                                                <i class="fas fa-building sl-input-icon"></i>
                                                <select name="branch_id" id="upload_branch_id" class="sl-input sl-select" required>
                                                    <option value="">-- Select Branch --</option>
                                                    @foreach($branches as $branch)
                                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="sl-form-group">
                                            <label class="sl-form-label" for="upload_academic_year_id">Academic Year <span class="sl-required">*</span></label>
                                            <div class="sl-input-wrap">
                                                <i class="fas fa-calendar sl-input-icon"></i>
                                                <select name="academic_year_id" id="upload_academic_year_id" class="sl-input sl-select" required>
                                                    <option value="">-- Select Academic Year --</option>
                                                    @foreach($academicYears as $ay)
                                                        <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="sl-form-group">
                                            <label class="sl-form-label" for="upload_section_id">Section / Class <span class="sl-required">*</span></label>
                                            <div class="sl-input-wrap">
                                                <i class="fas fa-chalkboard sl-input-icon"></i>
                                                <select name="section_id" id="upload_section_id" class="sl-input sl-select" required>
                                                    <option value="">-- Select Branch First --</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="sl-form-group">
                                            <label class="sl-form-label" for="upload_admission_date">Admission Date</label>
                                            <div class="sl-input-wrap">
                                                <i class="fas fa-calendar-check sl-input-icon"></i>
                                                <input type="date" name="admission_date" id="upload_admission_date"
                                                    class="sl-input" value="{{ date('Y-m-d') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="sl-file-upload-row">
                                        <div class="sl-file-input-wrap">
                                            <input type="file" name="file" id="excel_file"
                                                class="sl-file-input" accept=".csv,.txt,.xlsx,.xls" required>
                                            <label for="excel_file" class="sl-file-label">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <span id="file_label_text">Choose file (.csv, .xlsx)</span>
                                            </label>
                                        </div>
                                        <button type="submit" class="sl-btn sl-btn-primary" onclick="return confirm('Are you sure you want to upload and enroll students from this file?')">
                                            <i class="fas fa-upload"></i> Upload & Enroll
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="sl-card" style="margin-top:0.75rem;">
        <form method="POST" action="{{ route('admin.students.bulk-store') }}" id="bulkEnrollForm">
            @csrf

            {{-- Common Settings Section --}}
            <div class="sl-form-section">
                <div class="sl-form-section-head">
                    <div class="sl-form-section-icon sl-form-icon-blue"><i class="fas fa-cogs"></i></div>
                    <div>
                        <h3 class="sl-form-section-title">Enrollment Settings</h3>
                        <p class="sl-form-section-desc">These settings apply to all students in this batch</p>
                    </div>
                </div>
                <div class="sl-form-section-body">
                    <div class="sl-form-grid">
                        <div class="sl-form-group">
                            <label class="sl-form-label" for="branch_id">Branch <span class="sl-required">*</span></label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-building sl-input-icon"></i>
                                <select name="branch_id" id="branch_id" class="sl-input sl-select {{ $errors->has('branch_id') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Branch --</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('branch_id')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="sl-form-group">
                            <label class="sl-form-label" for="academic_year_id">Academic Year <span class="sl-required">*</span></label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-calendar sl-input-icon"></i>
                                <select name="academic_year_id" id="academic_year_id" class="sl-input sl-select {{ $errors->has('academic_year_id') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Academic Year --</option>
                                    @foreach($academicYears as $ay)
                                        <option value="{{ $ay->id }}" {{ old('academic_year_id') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('academic_year_id')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="sl-form-group">
                            <label class="sl-form-label" for="section_id">Section / Class <span class="sl-required">*</span></label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-chalkboard sl-input-icon"></i>
                                <select name="section_id" id="section_id" class="sl-input sl-select {{ $errors->has('section_id') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Branch First --</option>
                                </select>
                            </div>
                            @error('section_id')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="sl-form-group">
                            <label class="sl-form-label" for="admission_date">Admission Date</label>
                            <div class="sl-input-wrap">
                                <i class="fas fa-calendar-check sl-input-icon"></i>
                                <input type="date" name="admission_date" id="admission_date"
                                    class="sl-input {{ $errors->has('admission_date') ? 'is-invalid' : '' }}"
                                    value="{{ old('admission_date', date('Y-m-d')) }}">
                            </div>
                            @error('admission_date')<span class="sl-form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Student List Section --}}
            <div class="sl-form-section">
                <div class="sl-form-section-head">
                    <div class="sl-form-section-icon sl-form-icon-green"><i class="fas fa-users"></i></div>
                    <div>
                        <h3 class="sl-form-section-title">Student List</h3>
                        <p class="sl-form-section-desc">Add student details below. Click "Add Row" to add more students.</p>
                    </div>
                </div>
                <div class="sl-form-section-body" style="overflow-x:auto;">
                    @if($errors->has('students'))
                        <div class="sl-form-error" style="margin-bottom:10px;">{{ $errors->first('students') }}</div>
                    @endif

                    <table class="sl-table" id="studentTable">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                <th style="min-width:200px;">Full Name <span class="sl-required">*</span></th>
                                <th style="width:100px;">Gender</th>
                                <th style="min-width:140px;">Phone</th>
                                <th style="min-width:180px;">Guardian Name</th>
                                <th style="min-width:140px;">Guardian Phone</th>
                                <th style="width:150px;">Date of Birth</th>
                                <th style="width:50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="studentRows">
                            @for ($i = 0; $i < 5; $i++)
                            <tr class="student-row">
                                <td class="row-num">{{ $i + 1 }}</td>
                                <td>
                                    <input type="text" name="students[{{ $i }}][full_name]"
                                        class="sl-input-sm" placeholder="Student full name"
                                        value="{{ old("students.$i.full_name") }}" required>
                                </td>
                                <td>
                                    <select name="students[{{ $i }}][gender]" class="sl-input-sm sl-select-sm">
                                        <option value="">--</option>
                                        <option value="male" {{ old("students.$i.gender") === 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old("students.$i.gender") === 'female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="students[{{ $i }}][phone]"
                                        class="sl-input-sm" placeholder="0900000000"
                                        value="{{ old("students.$i.phone") }}" maxlength="10">
                                </td>
                                <td>
                                    <input type="text" name="students[{{ $i }}][guardian_name]"
                                        class="sl-input-sm" placeholder="Guardian name"
                                        value="{{ old("students.$i.guardian_name") }}">
                                </td>
                                <td>
                                    <input type="text" name="students[{{ $i }}][guardian_phone]"
                                        class="sl-input-sm" placeholder="0900000000"
                                        value="{{ old("students.$i.guardian_phone") }}" maxlength="10">
                                </td>
                                <td>
                                    <input type="date" name="students[{{ $i }}][date_of_birth]"
                                        class="sl-input-sm"
                                        value="{{ old("students.$i.date_of_birth") }}">
                                </td>
                                <td>
                                    <button type="button" class="sl-btn-remove" onclick="removeRow(this)" title="Remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>

                    <div style="margin-top:10px;display:flex;gap:8px;align-items:center;">
                        <button type="button" class="sl-btn sl-btn-outline" onclick="addRow()">
                            <i class="fas fa-plus"></i> Add Row
                        </button>
                        <button type="button" class="sl-btn sl-btn-outline" onclick="addRows(5)">
                            <i class="fas fa-plus-circle"></i> Add 5 Rows
                        </button>
                        <span class="sl-help-text" id="rowCount">5 students in list</span>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="sl-form-actions">
                <a href="{{ route('admin.students.index') }}" class="sl-btn sl-btn-ghost">Cancel</a>
                <button type="submit" class="sl-btn sl-btn-primary" onclick="return confirm('Are you sure you want to enroll these students?')">
                    <i class="fas fa-user-plus"></i> Enroll All Students
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
.sl-page { animation: slIn 0.3s ease-out; }
@keyframes slIn { from { opacity: 0; } to { opacity: 1; } }

.sl-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;
}
.sl-header-left { flex: 1; }
.sl-header-right { display: flex; gap: 0.4rem; flex-wrap: wrap; }

.sl-breadcrumb ol {
    display: flex; list-style: none; padding: 0; margin: 0;
    gap: 0.3rem; font-size: 0.72rem; align-items: center;
}
.sl-breadcrumb li { color: #adb5bd; }
.sl-breadcrumb li a { color: #6c757d; text-decoration: none; }
.sl-breadcrumb li a:hover { color: #4361ee; }
.sl-breadcrumb li + li::before { content: '/'; margin-right: 0.3rem; color: #dee2e6; }
.sl-breadcrumb li.active { color: #4361ee; font-weight: 500; }

.sl-btn {
    display: inline-flex; align-items: center; gap: 0.3rem;
    padding: 0.3rem 0.65rem; border-radius: 6px; font-weight: 600;
    font-size: 0.73rem; text-decoration: none; border: none; cursor: pointer;
    transition: all 0.2s; white-space: nowrap;
}
.sl-btn-primary { background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff; box-shadow: 0 1px 4px rgba(67,97,238,0.3); }
.sl-btn-primary:hover { color: #fff; box-shadow: 0 2px 8px rgba(67,97,238,0.4); }
.sl-btn-outline { background: #fff; color: #4361ee; border: 1px solid #4361ee; }
.sl-btn-outline:hover { background: #4361ee; color: #fff; }
.sl-btn-ghost { background: transparent; color: #6b7280; }
.sl-btn-ghost:hover { color: #1a1a2e; background: #f3f4f6; }
.sl-btn-green { background: #fff; color: #10b981; border-color: #10b981; }
.sl-btn-green:hover { background: #10b981; color: #fff; }

.sl-btn-remove {
    background: #fee2e2; color: #ef4444; border: none; border-radius: 4px;
    width: 28px; height: 28px; cursor: pointer; display: flex; align-items: center;
    justify-content: center; font-size: 0.7rem; transition: all 0.2s;
}
.sl-btn-remove:hover { background: #ef4444; color: #fff; }

.sl-info-box {
    display: flex; gap: 0.75rem; padding: 0.75rem 1rem;
    background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px;
    align-items: flex-start;
}
.sl-info-icon { color: #2563eb; font-size: 1rem; flex-shrink: 0; margin-top: 0.1rem; }
.sl-info-title { font-size: 0.85rem; font-weight: 700; color: #1e40af; margin: 0 0 0.25rem; }
.sl-info-desc { font-size: 0.75rem; color: #1e40af; margin: 0; line-height: 1.5; }

.sl-card {
    background: #fff; border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #f0f0f0;
    overflow: hidden;
}

.sl-form-section { border-bottom: 1px solid #f0f0f0; }
.sl-form-section:last-of-type { border-bottom: none; }
.sl-form-section-head {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 1rem 1.25rem 0.5rem;
}
.sl-form-section-icon {
    width: 36px; height: 36px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.9rem; flex-shrink: 0;
}
.sl-form-icon-blue { background: #eef2ff; color: #4361ee; }
.sl-form-icon-green { background: #ecfdf5; color: #10b981; }
.sl-form-icon-purple { background: #f5f3ff; color: #7c3aed; }
.sl-form-section-title { font-size: 0.9rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.sl-form-section-desc { font-size: 0.72rem; color: #9ca3af; margin: 0.1rem 0 0; }
.sl-form-section-body { padding: 0.75rem 1.25rem 1.25rem; }

.sl-form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
.sl-form-group { display: flex; flex-direction: column; }
.sl-form-label { font-weight: 600; color: #374151; margin-bottom: 0.3rem; font-size: 0.78rem; }
.sl-form-label small { font-weight: 400; color: #9ca3af; font-size: 0.7rem; }
.sl-required { color: #ef4444; font-weight: 700; }

.sl-input-wrap { position: relative; }
.sl-input-icon {
    position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
    color: #9ca3af; font-size: 0.75rem; pointer-events: none; z-index: 1;
}
.sl-input {
    width: 100%; border: 1px solid #e5e7eb; border-radius: 7px;
    padding: 0.45rem 0.7rem 0.45rem 2rem; font-size: 0.82rem;
    color: #1a1a2e; background: #fff; transition: all 0.2s;
}
.sl-input:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.sl-input::placeholder { color: #c5c9d2; }
.sl-input.is-invalid { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.1); }
.sl-select {
    appearance: none; cursor: pointer;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.6rem center; background-repeat: no-repeat; background-size: 1rem;
    padding-right: 2rem;
}
.sl-form-error { display: block; color: #ef4444; font-size: 0.72rem; margin-top: 0.25rem; font-weight: 500; }

/* Table inputs */
.sl-table {
    width: 100%; border-collapse: collapse; font-size: 0.78rem;
}
.sl-table th {
    background: #f8fafc; padding: 0.5rem 0.4rem; text-align: left;
    font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb;
    font-size: 0.72rem; white-space: nowrap;
}
.sl-table td {
    padding: 0.3rem 0.25rem; border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}
.sl-table tr:hover { background: #fafbfc; }
.sl-input-sm {
    width: 100%; border: 1px solid #e5e7eb; border-radius: 5px;
    padding: 0.35rem 0.5rem; font-size: 0.78rem; color: #1a1a2e;
    transition: border 0.2s;
}
.sl-input-sm:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 2px rgba(67,97,238,0.1); }
.sl-select-sm {
    appearance: none; cursor: pointer;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.3rem center; background-repeat: no-repeat; background-size: 0.8rem;
    padding-right: 1.2rem;
}
.row-num { color: #9ca3af; font-weight: 600; text-align: center; font-size: 0.72rem; }

.sl-help-text { font-size: 0.68rem; color: #9ca3af; margin: 0; }

.sl-form-actions {
    display: flex; justify-content: flex-end; gap: 0.5rem;
    padding: 1rem 1.25rem; border-top: 1px solid #f0f0f0; background: #fafbfc;
}

@media (max-width: 768px) {
    .sl-header { flex-direction: column; align-items: stretch; }
    .sl-form-grid { grid-template-columns: 1fr; }
    .sl-form-section-body { padding: 0.5rem 0.75rem 1rem; }
    .sl-form-section-head { padding: 0.75rem 0.75rem 0.4rem; }
    .sl-form-actions { padding: 0.75rem; flex-direction: column; }
    .sl-btn { justify-content: center; width: 100%; }
    .sl-table { font-size: 0.7rem; }
    .sl-excel-step { flex-direction: column; text-align: center; }
    .sl-file-upload-row { flex-direction: column; }
}

/* Excel upload section styles */
.sl-excel-upload-area {
    display: flex; flex-direction: column; gap: 1.25rem;
}
.sl-excel-step {
    display: flex; gap: 1rem; align-items: flex-start;
    padding: 1rem; border: 1px solid #e5e7eb; border-radius: 8px;
    background: #fafbfc;
}
.sl-excel-step-num {
    width: 32px; height: 32px; border-radius: 50%;
    background: linear-gradient(135deg, #7c3aed, #4361ee);
    color: #fff; font-weight: 700; font-size: 0.85rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.sl-excel-step-content { flex: 1; }
.sl-excel-step-title {
    font-size: 0.85rem; font-weight: 700; color: #1a1a2e; margin: 0 0 0.25rem;
}
.sl-excel-step-desc {
    font-size: 0.72rem; color: #6b7280; margin: 0 0 0.5rem;
}
.sl-file-upload-row {
    display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;
}
.sl-file-input-wrap {
    flex: 1; min-width: 200px;
}
.sl-file-input {
    display: none;
}
.sl-file-label {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.5rem 1rem; border: 2px dashed #d1d5db;
    border-radius: 8px; cursor: pointer; transition: all 0.2s;
    font-size: 0.8rem; color: #6b7280; background: #fff;
}
.sl-file-label:hover {
    border-color: #4361ee; color: #4361ee; background: #eef2ff;
}
.sl-file-label i { font-size: 1.1rem; }
</style>
@endpush

@push('scripts')
<script>
let rowIndex = 5; // Start after initial 5 rows

function addRow() {
    const tbody = document.getElementById('studentRows');
    const tr = document.createElement('tr');
    tr.className = 'student-row';
    tr.innerHTML = `
        <td class="row-num">${rowIndex + 1}</td>
        <td><input type="text" name="students[${rowIndex}][full_name]" class="sl-input-sm" placeholder="Student full name" required></td>
        <td>
            <select name="students[${rowIndex}][gender]" class="sl-input-sm sl-select-sm">
                <option value="">--</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
        </td>
        <td><input type="text" name="students[${rowIndex}][phone]" class="sl-input-sm" placeholder="0900000000" maxlength="10"></td>
        <td><input type="text" name="students[${rowIndex}][guardian_name]" class="sl-input-sm" placeholder="Guardian name"></td>
        <td><input type="text" name="students[${rowIndex}][guardian_phone]" class="sl-input-sm" placeholder="0900000000" maxlength="10"></td>
        <td><input type="date" name="students[${rowIndex}][date_of_birth]" class="sl-input-sm"></td>
        <td><button type="button" class="sl-btn-remove" onclick="removeRow(this)" title="Remove"><i class="fas fa-times"></i></button></td>
    `;
    tbody.appendChild(tr);
    rowIndex++;
    updateRowCount();
}

function addRows(count) {
    for (let i = 0; i < count; i++) {
        addRow();
    }
}

function removeRow(btn) {
    const tr = btn.closest('tr');
    tr.remove();
    renumberRows();
    updateRowCount();
}

function renumberRows() {
    const rows = document.querySelectorAll('#studentRows .student-row');
    rows.forEach((row, idx) => {
        row.querySelector('.row-num').textContent = idx + 1;
    });
}

function updateRowCount() {
    const count = document.querySelectorAll('#studentRows .student-row').length;
    document.getElementById('rowCount').textContent = count + ' student(s) in list';
}

// Load sections when branch changes
document.getElementById('branch_id').addEventListener('change', function() {
    const branchId = this.value;
    const sectionSelect = document.getElementById('section_id');

    if (!branchId) {
        sectionSelect.innerHTML = '<option value="">-- Select Branch First --</option>';
        return;
    }

    sectionSelect.innerHTML = '<option value="">Loading...</option>';

    fetch('{{ route("admin.students.api.sections-by-branch") }}?branch_id=' + branchId)
        .then(res => res.json())
        .then(data => {
            sectionSelect.innerHTML = '<option value="">-- Select Section --</option>';
            data.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.class_name + ' - ' + s.name;
                sectionSelect.appendChild(opt);
            });
        })
        .catch(err => {
            sectionSelect.innerHTML = '<option value="">Error loading sections</option>';
            console.error(err);
        });
});

// Upload form: Load sections when branch changes
document.getElementById('upload_branch_id').addEventListener('change', function() {
    const branchId = this.value;
    const sectionSelect = document.getElementById('upload_section_id');

    if (!branchId) {
        sectionSelect.innerHTML = '<option value="">-- Select Branch First --</option>';
        return;
    }

    sectionSelect.innerHTML = '<option value="">Loading...</option>';

    fetch('{{ route("admin.students.api.sections-by-branch") }}?branch_id=' + branchId)
        .then(res => res.json())
        .then(data => {
            sectionSelect.innerHTML = '<option value="">-- Select Section --</option>';
            data.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.class_name + ' - ' + s.name;
                sectionSelect.appendChild(opt);
            });
        })
        .catch(err => {
            sectionSelect.innerHTML = '<option value="">Error loading sections</option>';
            console.error(err);
        });
});

// Update file label text when a file is selected
document.getElementById('excel_file').addEventListener('change', function() {
    const label = document.getElementById('file_label_text');
    if (this.files && this.files.length > 0) {
        label.textContent = this.files[0].name;
    } else {
        label.textContent = 'Choose file (.csv, .xlsx)';
    }
});
</script>
@endpush
@endsection
