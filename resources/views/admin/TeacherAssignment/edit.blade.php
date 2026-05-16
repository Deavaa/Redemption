@extends('layouts.admin')
@section('title', 'Edit Teacher Assignment')

@push('styles')
<style>
.ta-page{animation:taIn .4s ease-out}
@keyframes taIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.ta-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden;margin-bottom:1.5rem}
.ta-card-head{display:flex;align-items:center;gap:.75rem;padding:1rem 1.5rem;border-bottom:1px solid #f0f0f0;background:#fafbfc}
.ta-card-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;background:#eef2ff;color:#4361ee}
.ta-card-title{font-size:1rem;font-weight:700;color:#1a1a2e;margin:0}
.ta-card-body{padding:1.25rem 1.5rem}
.ta-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.ta-group{display:flex;flex-direction:column}
.ta-label{font-weight:600;color:#374151;margin-bottom:.4rem;font-size:.85rem}
.ta-select{width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:.6rem 2.2rem .6rem .8rem;font-size:.88rem;color:#1a1a2e;background:#fff;appearance:none;cursor:pointer;transition:all .2s;background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");background-position:right .6rem center;background-repeat:no-repeat;background-size:1.15rem}
.ta-select:focus{outline:none;border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1)}
.ta-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.25rem;border-radius:10px;font-weight:600;font-size:.88rem;border:none;cursor:pointer;transition:all .25s;color:#fff;background:linear-gradient(135deg,#4361ee,#3a0ca3);box-shadow:0 2px 8px rgba(67,97,238,.3)}
.ta-btn:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(67,97,238,.4)}
.ta-btn-outline{background:transparent;color:#6b7280;border:1.5px solid #e5e7eb;box-shadow:none}
.ta-btn-outline:hover{border-color:#4361ee;color:#4361ee;background:#f8f9ff;transform:none;box-shadow:none}
.ta-actions{display:flex;justify-content:flex-end;gap:.75rem;padding:1rem 1.5rem;border-top:1px solid #f0f0f0;background:#fafbfc}
.ta-info{background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:.75rem 1.25rem;margin-bottom:1rem;display:flex;align-items:center;gap:.75rem;color:#1e40af;font-weight:500;font-size:.88rem}
.ta-error{font-size:.8rem;color:#ef4444;margin-top:.25rem}
@media(max-width:768px){.ta-grid{grid-template-columns:1fr}.ta-group[style]{grid-column:1!important}}
</style>
@endpush

@section('content')
<div class="ta-page">
    <div class="ta-card">
        <div class="ta-card-head">
            <div class="ta-card-icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <div><h3 class="ta-card-title">Edit Teacher Assignment</h3></div>
        </div>
        <form method="POST" action="{{ route('admin.teacher-assignments.update', $item->id) }}">
            @csrf
            @method('PUT')
            <div class="ta-card-body">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="ta-info">
                    <i class="fas fa-info-circle"></i>
                    <span>Update the assignment details. Changing the class will reload available sections and subjects.</span>
                </div>

                <div class="ta-grid">
                    <div class="ta-group">
                        <label class="ta-label">Academic Year <span style="color:#ef4444">*</span></label>
                        <select name="academic_year_id" class="ta-select" required>
                            <option value="">-- Select Academic Year --</option>
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ old('academic_year_id', $item->academic_year_id) == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                            @endforeach
                        </select>
                        @error('academic_year_id')<div class="ta-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="ta-group">
                        <label class="ta-label">Teacher <span style="color:#ef4444">*</span></label>
                        <select name="teacher_id" class="ta-select" required>
                            <option value="">-- Select Teacher --</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}" {{ old('teacher_id', $item->teacher_id) == $t->id ? 'selected' : '' }}>{{ $t->first_name }} {{ $t->last_name }}@if($t->email) ({{ $t->email }})@endif</option>
                            @endforeach
                        </select>
                        @error('teacher_id')<div class="ta-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="ta-group">
                        <label class="ta-label">Class <span style="color:#ef4444">*</span></label>
                        <select name="class_id" id="taClassSelect" class="ta-select" required>
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ old('class_id', $item->class_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('class_id')<div class="ta-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="ta-group">
                        <label class="ta-label">Section</label>
                        <select name="section_id" id="taSectionSelect" class="ta-select">
                            <option value="">-- All Sections --</option>
                            @foreach($sections as $s)
                                <option value="{{ $s->id }}" {{ old('section_id', $item->section_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('section_id')<div class="ta-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="ta-group" style="grid-column:1/-1">
                        <label class="ta-label">Subject <span style="color:#ef4444">*</span></label>
                        <select name="subject_id" id="taSubjectSelect" class="ta-select" required>
                            <option value="">-- Select Subject --</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}" {{ old('subject_id', $item->subject_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id')<div class="ta-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="ta-actions">
                <a href="{{ route('admin.teacher-assignments.index') }}" class="ta-btn ta-btn-outline"><i class="fas fa-arrow-left"></i> Cancel</a>
                <button type="submit" class="ta-btn"><i class="fas fa-save"></i> Update Assignment</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    var classSel = document.getElementById('taClassSelect');
    var secSel = document.getElementById('taSectionSelect');
    var subjSel = document.getElementById('taSubjectSelect');

    if(classSel){
        classSel.addEventListener('change', function(){
            var classId = this.value;
            if(!classId){ secSel.innerHTML='<option value="">-- All Sections --</option>'; subjSel.innerHTML='<option value="">-- Select Subject --</option>'; return; }

            // Load sections
            fetch('{{ route("admin.teacher-assignments.api.sections") }}?class_id='+classId, {credentials:'same-origin'})
            .then(function(r){ return r.json(); })
            .then(function(data){
                var currentSection = '{{ old("section_id", $item->section_id) }}';
                secSel.innerHTML = '<option value="">-- All Sections --</option>';
                data.forEach(function(s){
                    var sel = (s.id == currentSection) ? ' selected' : '';
                    secSel.innerHTML += '<option value="'+s.id+'"'+sel+'>'+s.name+'</option>';
                });
            });

            // Load subjects
            fetch('{{ route("admin.teacher-assignments.api.subjects") }}?class_id='+classId, {credentials:'same-origin'})
            .then(function(r){ return r.json(); })
            .then(function(data){
                var currentSubject = '{{ old("subject_id", $item->subject_id) }}';
                subjSel.innerHTML = '<option value="">-- Select Subject --</option>';
                data.forEach(function(s){
                    var sel = (s.id == currentSubject) ? ' selected' : '';
                    subjSel.innerHTML += '<option value="'+s.id+'"'+sel+'>'+s.name+'</option>';
                });
            });
        });
    }
})();
</script>
@endpush
