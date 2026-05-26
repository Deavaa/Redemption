@extends('layouts.admin')

@section('title', 'Transfer Student')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-exchange-alt"></i> Transfer Student to Another Branch</h1>
    <p class="text-muted">Move <strong>{{ $student->full_name }}</strong> ({{ $student->admission_number }}) to a different branch</p>
</div>

<div class="card" style="max-width:700px;">
    <div class="card-body">
        {{-- Current Info --}}
        <div style="background:#f0f4f8;padding:15px;border-radius:8px;margin-bottom:20px;">
            <h5 style="margin:0 0 10px;font-size:14px;color:#666;">Current Assignment</h5>
            <table style="width:100%;font-size:14px;">
                <tr><td style="width:120px;color:#888;">Branch:</td><td><strong>{{ $student->branch->name ?? 'N/A' }}</strong></td></tr>
                <tr><td style="color:#888;">Class:</td><td><strong>{{ $student->classroom->name ?? 'N/A' }}</strong></td></tr>
                <tr><td style="color:#888;">Section:</td><td><strong>{{ $student->section->name ?? 'N/A' }}</strong></td></tr>
                <tr><td style="color:#888;">Roll No:</td><td><strong>{{ $student->roll_number }}</strong></td></tr>
            </table>
        </div>

        <form method="POST" action="{{ route('admin.students.transfer-store', $student) }}">
            @csrf
            @method('POST')

            <div class="form-group">
                <label for="branch_id"><i class="fas fa-building"></i> Transfer To Branch <span class="text-danger">*</span></label>
                <select name="branch_id" id="branch_id" class="form-control" required>
                    <option value="">-- Select Branch --</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
                @error('branch_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="section_id"><i class="fas fa-users"></i> Assign to Section <span class="text-danger">*</span></label>
                <select name="section_id" id="section_id" class="form-control" required>
                    <option value="">-- Select Branch First --</option>
                </select>
                @error('section_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="transfer_reason"><i class="fas fa-comment"></i> Reason for Transfer</label>
                <textarea name="transfer_reason" id="transfer_reason" class="form-control" rows="3" placeholder="Optional reason for transfer..."></textarea>
                @error('transfer_reason') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div style="display:flex;gap:10px;margin-top:20px;">
                <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to transfer this student?')">
                    <i class="fas fa-exchange-alt"></i> Transfer Student
                </button>
                <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Load sections when branch changes
document.getElementById('branch_id').addEventListener('change', function() {
    const branchId = this.value;
    const sectionSelect = document.getElementById('section_id');

    if (!branchId) {
        sectionSelect.innerHTML = '<option value="">-- Select Branch First --</option>';
        return;
    }

    sectionSelect.innerHTML = '<option value="">Loading...</option>';

    fetch(`{{ route('admin.students.api.sections', 0) }}`.replace('/0/api/sections', `/api/sections-by-branch?branch_id=${branchId}`)
    ).then(r => r.json())
    .then(data => {
        sectionSelect.innerHTML = '<option value="">-- Select Section --</option>';
        data.forEach(s => {
            sectionSelect.innerHTML += `<option value="${s.id}">${s.name} (${s.class_name || ''})</option>`;
        });
    }).catch(() => {
        sectionSelect.innerHTML = '<option value="">Error loading sections</option>';
    });
});
</script>
@endsection
