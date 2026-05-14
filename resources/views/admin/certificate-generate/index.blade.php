@extends('layouts.admin')
@section('title', __('app.generate') . ' ' . __('app.certificates'))

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.certificates.index') }}">{{ __('app.certificates') }}</a></li>
                    <li class="active">{{ __('app.generate') }}</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">{{ __('app.generate') }} {{ __('app.certificates') }}</h1>
            <p class="modern-page-subtitle">{{ __('app.cert_generate_subtitle') ?? 'Create academic certificates for students' }}</p>
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
                        <h3 class="modern-form-section-title">{{ __('app.cert_details') ?? 'Certificate Details' }}</h3>
                        <p class="modern-form-section-desc">{{ __('app.cert_select_student') ?? 'Select student and certificate type' }}</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label">{{ __('app.classes') }}</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-building modern-input-icon"></i>
                                <select id="certClass" class="modern-input modern-select">
                                    <option value="">-- {{ __('app.select_class') ?? 'Select Class' }} --</option>
                                    @foreach($classes as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">{{ __('app.students') }} <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-user modern-input-icon"></i>
                                <select name="student_id" id="certStudent" class="modern-input modern-select" required>
                                    <option value="">-- {{ __('app.select_class_first') ?? 'Select Class First' }} --</option>
                                </select>
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">{{ __('app.cert_type') ?? 'Certificate Type' }} <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-scroll modern-input-icon"></i>
                                <select name="type" class="modern-input modern-select" required>
                                    <option value="academic">{{ __('app.cert_academic') ?? 'Academic Certificate' }}</option>
                                    <option value="completion">{{ __('app.cert_completion') ?? 'Completion Certificate' }}</option>
                                    <option value="transfer">{{ __('app.cert_transfer') ?? 'Transfer Certificate' }}</option>
                                    <option value="character">{{ __('app.cert_character') ?? 'Character Certificate' }}</option>
                                    <option value="foldable">{{ __('app.cert_foldable') ?? 'Foldable Certificate (Report Card)' }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modern-form-actions">
                <a href="{{ route('admin.certificates.index') }}" class="btn-modern btn-modern-ghost">{{ __('app.cancel') }}</a>
                <button type="submit" class="btn-modern btn-modern-primary">
                    <i class="fas fa-certificate"></i> {{ __('app.generate') }} {{ __('app.certificates') }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('certClass')?.addEventListener('change', function() {
    const classId = this.value;
    const studentSel = document.getElementById('certStudent');

    if (!classId) {
        studentSel.innerHTML = '<option value="">-- {{ __("app.select_class_first") ?? "Select Class First" }} --</option>';
        return;
    }

    studentSel.innerHTML = '<option value="">Loading...</option>';

    fetch('{{ route("admin.certificate-generate.students") }}?class_id=' + encodeURIComponent(classId))
        .then(r => {
            if (!r.ok) throw new Error('Network error');
            return r.json();
        })
        .then(data => {
            studentSel.innerHTML = '<option value="">-- {{ __("app.select_student") ?? "Select Student" }} --</option>';
            if (Array.isArray(data) && data.length > 0) {
                data.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.first_name + ' ' + s.last_name + (s.roll_number ? ' (' + s.roll_number + ')' : '');
                    studentSel.appendChild(opt);
                });
            } else {
                studentSel.innerHTML = '<option value="">No students found</option>';
            }
        })
        .catch(err => {
            console.error('Error loading students:', err);
            studentSel.innerHTML = '<option value="">Error loading students</option>';
        });
});
</script>
@endpush
@endsection
