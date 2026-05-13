@extends('layouts.admin')
@section('title', 'Generate Certificate')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.certificates.index') }}">Certificates</a></li>
                    <li class="active">Generate</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Generate Certificate</h1>
            <p class="modern-page-subtitle">Create academic certificates for students</p>
        </div>
    </div>

    <div class="modern-card">
        <form method="POST" action="{{ route('admin.certificate-generate.generate') }}" target="_blank">
            @csrf
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-purple">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Certificate Details</h3>
                        <p class="modern-form-section-desc">Select student and certificate type</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label">Class</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-building modern-input-icon"></i>
                                <select id="certClass" class="modern-input modern-select">
                                    <option value="">-- Select Class --</option>
                                    @foreach($classes as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Student <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-user modern-input-icon"></i>
                                <select name="student_id" id="certStudent" class="modern-input modern-select" required>
                                    <option value="">-- Select Class First --</option>
                                </select>
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Certificate Type <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-scroll modern-input-icon"></i>
                                <select name="type" class="modern-input modern-select" required>
                                    <option value="academic">Academic Certificate</option>
                                    <option value="completion">Completion Certificate</option>
                                    <option value="transfer">Transfer Certificate</option>
                                    <option value="character">Character Certificate</option>
                                    <option value="foldable">Foldable Certificate (Report Card)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modern-form-actions">
                <a href="{{ route('admin.certificates.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
                <button type="submit" class="btn-modern btn-modern-primary">
                    <i class="fas fa-certificate"></i> Generate Certificate
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('certClass')?.addEventListener('change', function() {
    fetch('{{ route("admin.certificate-generate.students") }}?class_id=' + this.value)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('certStudent');
            sel.innerHTML = '<option value="">-- Select Student --</option>';
            data.forEach(s => sel.innerHTML += `<option value="${s.id}">${s.first_name} ${s.last_name} (${s.roll_number})</option>`);
        });
});
</script>
@endpush
@endsection
