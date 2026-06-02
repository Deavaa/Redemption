@extends('layouts.admin')
@section('title', 'Mark Entry Disallowals')

@push('styles')
<style>
.med-page{animation:medIn .4s ease-out}
@keyframes medIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.med-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem}
.med-header-left{flex:1}
.med-title{font-size:1.75rem;font-weight:800;color:#1a1a2e;margin:0;letter-spacing:-.5px}
.med-subtitle{font-size:.9rem;color:#6c757d;margin:.25rem 0 0}

.med-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden;margin-bottom:1.5rem}
.med-card-head{display:flex;align-items:center;gap:.75rem;padding:1rem 1.5rem;border-bottom:1px solid #f0f0f0;background:#fafbfc}
.med-card-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.med-card-icon.red{background:#fef2f2;color:#ef4444}
.med-card-icon.blue{background:#eef2ff;color:#4361ee}
.med-card-icon.green{background:#ecfdf5;color:#10b981}
.med-card-title{font-size:1rem;font-weight:700;color:#1a1a2e;margin:0}
.med-card-desc{font-size:.82rem;color:#9ca3af;margin:.1rem 0 0}
.med-card-body{padding:1.25rem 1.5rem}

.med-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem}
.med-group{display:flex;flex-direction:column}
.med-label{font-weight:600;color:#374151;margin-bottom:.4rem;font-size:.85rem}
.med-select{width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:.6rem 2.2rem .6rem .8rem;font-size:.88rem;color:#1a1a2e;background:#fff;appearance:none;cursor:pointer;transition:all .2s;background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");background-position:right .6rem center;background-repeat:no-repeat;background-size:1.15rem}
.med-select:focus{outline:none;border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1)}
.med-input{width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:.6rem .8rem;font-size:.88rem;color:#1a1a2e;transition:all .2s}
.med-input:focus{outline:none;border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1)}
.med-textarea{width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:.6rem .8rem;font-size:.88rem;color:#1a1a2e;resize:vertical;min-height:60px;transition:all .2s}
.med-textarea:focus{outline:none;border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1)}
.med-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.25rem;border-radius:10px;font-weight:600;font-size:.88rem;border:none;cursor:pointer;transition:all .25s;color:#fff;background:linear-gradient(135deg,#ef4444,#dc2626);box-shadow:0 2px 8px rgba(239,68,68,.3)}
.med-btn:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(239,68,68,.4)}
.med-btn-outline{background:transparent;color:#6b7280;border:1.5px solid #e5e7eb;box-shadow:none}
.med-btn-outline:hover{border-color:#ef4444;color:#ef4444;background:#fef2f2;transform:none;box-shadow:none}
.med-btn-blue{background:linear-gradient(135deg,#4361ee,#3a0ca3);box-shadow:0 2px 8px rgba(67,97,238,.3)}
.med-btn-blue:hover{box-shadow:0 4px 16px rgba(67,97,238,.4)}
.med-btn-green{background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 2px 8px rgba(16,185,129,.3)}
.med-btn-green:hover{box-shadow:0 4px 16px rgba(16,185,129,.4)}

/* Disallowal table */
.med-table{width:100%;border-collapse:collapse;font-size:.85rem}
.med-table th{background:#f9fafb;padding:.65rem .75rem;text-align:left;font-weight:700;color:#374151;border-bottom:2px solid #e5e7eb;font-size:.8rem;text-transform:uppercase;letter-spacing:.3px}
.med-table td{padding:.55rem .75rem;border-bottom:1px solid #f0f0f0;vertical-align:middle}
.med-table tbody tr:hover{background:#fef2f2}
.med-table .badge-scope{display:inline-block;padding:.15rem .5rem;border-radius:6px;font-size:.72rem;font-weight:600}
.med-table .badge-all{background:#fee2e2;color:#991b1b}
.med-table .badge-subject{background:#fef3c7;color:#92400e}
.med-table .badge-section{background:#dbeafe;color:#1e40af}

/* Checkbox grid */
.med-check-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.5rem;max-height:200px;overflow-y:auto;padding:.5rem;border:1px solid #e5e7eb;border-radius:10px}
.med-check-item{display:flex;align-items:center;gap:.5rem;padding:.35rem .5rem;border-radius:6px;font-size:.85rem;cursor:pointer;transition:background .15s}
.med-check-item:hover{background:#f3f4f6}
.med-check-item input[type="checkbox"]{accent-color:#ef4444;width:16px;height:16px}

/* Scope radio */
.med-scope-options{display:flex;gap:.75rem;flex-wrap:wrap}
.med-scope-option{display:flex;align-items:center;gap:.4rem;padding:.5rem 1rem;border:1.5px solid #e5e7eb;border-radius:10px;cursor:pointer;font-size:.85rem;font-weight:600;transition:all .2s}
.med-scope-option:hover{border-color:#ef4444;background:#fef2f2}
.med-scope-option.active{border-color:#ef4444;background:#fee2e2;color:#991b1b}
.med-scope-option input[type="radio"]{accent-color:#ef4444}

/* Info banner */
.med-info{display:flex;align-items:flex-start;gap:.75rem;padding:1rem 1.25rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;margin-bottom:1.5rem;font-size:.88rem;color:#1e40af}
.med-info i{font-size:1.1rem;margin-top:2px;flex-shrink:0}

/* Empty state */
.med-empty{text-align:center;padding:3rem 1rem;color:#9ca3af}
.med-empty i{font-size:2.5rem;margin-bottom:.75rem;display:block}
.med-empty p{margin:0;font-size:.95rem}

@media(max-width:768px){.med-grid{grid-template-columns:1fr 1fr}.med-title{font-size:1.35rem}}
@media(max-width:480px){.med-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="med-page">
    <div class="med-header">
        <div class="med-header-left">
            <h1 class="med-title"><i class="fas fa-ban" style="color:#ef4444"></i> Mark Entry Disallowals</h1>
            <p class="med-subtitle">Restrict teachers from entering marks for specific classes, sections, or subjects. By default, assigned teachers have mark entry permission — use this to override.</p>
        </div>
    </div>

    {{-- Info Banner --}}
    <div class="med-info">
        <i class="fas fa-info-circle"></i>
        <div>
            <strong>How it works:</strong> Teachers assigned to a class/section/subject automatically have permission to enter marks for the current academic year and term. Use this page to <strong>disallow</strong> specific teachers from entering marks. Only branch principals (for their branch) and administrators can manage disallowals.
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="med-card">
        <div class="med-card-head">
            <div class="med-card-icon blue"><i class="fas fa-filter"></i></div>
            <div><h3 class="med-card-title">Filter</h3><p class="med-card-desc">Select academic year and term to view/manage disallowals</p></div>
        </div>
        <form method="GET" action="{{ route('admin.mark-entry-disallowals.index') }}">
            <div class="med-card-body">
                <div class="med-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr))">
                    @if(!$userBranch)
                    <div class="med-group">
                        <label class="med-label">Branch</label>
                        <select name="branch_id" class="med-select">
                            <option value="">-- All Branches --</option>
                            @foreach($branches as $b)<option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>@endforeach
                        </select>
                    </div>
                    @endif
                    <div class="med-group">
                        <label class="med-label">Academic Year</label>
                        <select name="academic_year_id" class="med-select">
                            @foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ ($selectedAy->id ?? null) == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="med-group">
                        <label class="med-label">Term</label>
                        <select name="term_id" class="med-select">
                            @foreach($terms as $t)<option value="{{ $t->id }}" {{ ($selectedTerm->id ?? null) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="med-group" style="align-self:flex-end">
                        <button type="submit" class="med-btn med-btn-blue"><i class="fas fa-search"></i> Filter</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Add Disallowal Card --}}
    <div class="med-card">
        <div class="med-card-head">
            <div class="med-card-icon red"><i class="fas fa-ban"></i></div>
            <div><h3 class="med-card-title">Add Disallowal</h3><p class="med-card-desc">Prevent a teacher from entering marks</p></div>
        </div>
        <form method="POST" action="{{ route('admin.mark-entry-disallowals.store') }}" id="disallowalForm">
            @csrf
            <div class="med-card-body">
                <input type="hidden" name="academic_year_id" value="{{ $selectedAy->id ?? '' }}">
                <input type="hidden" name="term_id" value="{{ $selectedTerm->id ?? '' }}">
                @if($userBranch)<input type="hidden" name="branch_id" value="{{ $userBranch->id }}">@endif

                <div class="med-grid" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr));margin-bottom:1rem">
                    <div class="med-group">
                        <label class="med-label">Teacher <span style="color:#ef4444">*</span></label>
                        <select name="teacher_id" id="disallowTeacher" class="med-select" required>
                            <option value="">-- Select Teacher --</option>
                            @foreach($teachers as $t)
                            <option value="{{ $t->id }}" data-user-id="{{ $t->user_id }}">{{ $t->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="med-group">
                        <label class="med-label">Class</label>
                        <select name="class_id" id="disallowClass" class="med-select">
                            <option value="">-- All Classes --</option>
                            @foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                </div>

                {{-- Scope Selection --}}
                <div class="med-group" style="margin-bottom:1rem">
                    <label class="med-label">Disallow Scope <span style="color:#ef4444">*</span></label>
                    <div class="med-scope-options">
                        <label class="med-scope-option active">
                            <input type="radio" name="scope" value="all" checked onchange="updateScopeUI()">
                            All Mark Entry
                        </label>
                        <label class="med-scope-option">
                            <input type="radio" name="scope" value="subject" onchange="updateScopeUI()">
                            Specific Subject(s)
                        </label>
                        <label class="med-scope-option">
                            <input type="radio" name="scope" value="section" onchange="updateScopeUI()">
                            Specific Section(s)
                        </label>
                    </div>
                </div>

                {{-- Subject checkboxes (hidden by default) --}}
                <div class="med-group" id="subjectCheckGroup" style="display:none;margin-bottom:1rem">
                    <label class="med-label">Subjects to Disallow</label>
                    <div class="med-check-grid" id="subjectCheckboxes">
                        <p style="color:#9ca3af;font-size:.85rem;padding:.5rem">Select a teacher and class first...</p>
                    </div>
                </div>

                {{-- Section checkboxes (hidden by default) --}}
                <div class="med-group" id="sectionCheckGroup" style="display:none;margin-bottom:1rem">
                    <label class="med-label">Sections to Disallow</label>
                    <div class="med-check-grid" id="sectionCheckboxes">
                        <p style="color:#9ca3af;font-size:.85rem;padding:.5rem">Select a class first...</p>
                    </div>
                </div>

                <div class="med-group" style="margin-bottom:1rem">
                    <label class="med-label">Reason (optional)</label>
                    <textarea name="reason" class="med-textarea" placeholder="Why is mark entry being disallowed for this teacher?"></textarea>
                </div>

                <div style="display:flex;justify-content:flex-end">
                    <button type="submit" class="med-btn"><i class="fas fa-ban"></i> Disallow Mark Entry</button>
                </div>
            </div>
        </form>
    </div>

    {{-- Active Disallowals --}}
    <div class="med-card">
        <div class="med-card-head">
            <div class="med-card-icon green"><i class="fas fa-list"></i></div>
            <div><h3 class="med-card-title">Active Disallowals</h3><p class="med-card-desc">Teachers currently disallowed from entering marks</p></div>
        </div>
        <div class="med-card-body" style="padding:0">
            @if($disallowals->count() > 0)
            <div style="overflow-x:auto">
                <table class="med-table">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Scope</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Subject</th>
                            <th>Academic Year</th>
                            <th>Term</th>
                            <th>Reason</th>
                            <th>Disallowed By</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($disallowals as $d)
                        <tr>
                            <td><strong>{{ $d->teacher->full_name ?? 'Unknown' }}</strong></td>
                            <td>
                                @if(!$d->class_id && !$d->section_id && !$d->subject_id)
                                <span class="badge-scope badge-all">All Entry</span>
                                @elseif($d->subject_id)
                                <span class="badge-scope badge-subject">Subject</span>
                                @else
                                <span class="badge-scope badge-section">Section</span>
                                @endif
                            </td>
                            <td>{{ $d->classRoom->name ?? '<em>Any</em>' }}</td>
                            <td>{{ $d->section->name ?? '<em>Any</em>' }}</td>
                            <td>{{ $d->subject->name ?? '<em>Any</em>' }}</td>
                            <td>{{ $d->academicYear->name ?? 'Any' }}</td>
                            <td>{{ $d->term->name ?? 'Any' }}</td>
                            <td>{{ $d->reason ? Str::limit($d->reason, 40) : '-' }}</td>
                            <td>{{ $d->disallowedBy->name ?? '-' }}</td>
                            <td>{{ $d->created_at->format('M d, Y') }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.mark-entry-disallowals.revoke', $d->id) }}" style="display:inline" onsubmit="return confirm('Revoke this disallowal? The teacher will be able to enter marks again.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="med-btn med-btn-green" style="padding:.35rem .75rem;font-size:.78rem"><i class="fas fa-undo"></i> Revoke</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="med-empty">
                <i class="fas fa-check-circle" style="color:#10b981"></i>
                <p>No active disallowals. All assigned teachers can enter marks.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    var teacherEl=document.getElementById('disallowTeacher');
    var classEl=document.getElementById('disallowClass');
    var subjectGroup=document.getElementById('subjectCheckGroup');
    var sectionGroup=document.getElementById('sectionCheckGroup');
    var subjectBoxes=document.getElementById('subjectCheckboxes');
    var sectionBoxes=document.getElementById('sectionCheckboxes');

    function updateScopeUI(){
        var scope=document.querySelector('input[name="scope"]:checked').value;
        var opts=document.querySelectorAll('.med-scope-option');
        opts.forEach(function(o){o.classList.remove('active')});
        document.querySelector('input[name="scope"][value="'+scope+'"]').closest('.med-scope-option').classList.add('active');

        subjectGroup.style.display=scope==='subject'?'flex':'none';
        sectionGroup.style.display=scope==='section'?'flex':'none';

        if(scope==='subject') loadSubjects();
        if(scope==='section') loadSections();
    }

    function loadSubjects(){
        var tid=teacherEl.value;
        var cid=classEl.value;
        if(!tid||!cid){subjectBoxes.innerHTML='<p style="color:#9ca3af;font-size:.85rem;padding:.5rem">Select a teacher and class first...</p>';return;}
        var url='{{ route("admin.mark-entry-disallowals.api.subjects") }}?teacher_id='+tid+'&class_id='+cid;
        fetch(url,{credentials:'same-origin'}).then(function(r){return r.json()}).then(function(data){
            if(!data.length){subjectBoxes.innerHTML='<p style="color:#9ca3af;font-size:.85rem;padding:.5rem">No subjects found for this teacher in this class.</p>';return;}
            var html='';
            data.forEach(function(s){
                html+='<label class="med-check-item"><input type="checkbox" name="subject_ids[]" value="'+s.id+'"> '+s.name+'</label>';
            });
            subjectBoxes.innerHTML=html;
        });
    }

    function loadSections(){
        var cid=classEl.value;
        if(!cid){sectionBoxes.innerHTML='<p style="color:#9ca3af;font-size:.85rem;padding:.5rem">Select a class first...</p>';return;}
        var url='{{ route("admin.mark-entry-disallowals.api.sections") }}?class_id='+cid;
        fetch(url,{credentials:'same-origin'}).then(function(r){return r.json()}).then(function(data){
            if(!data.length){sectionBoxes.innerHTML='<p style="color:#9ca3af;font-size:.85rem;padding:.5rem">No sections found for this class.</p>';return;}
            var html='';
            data.forEach(function(s){
                html+='<label class="med-check-item"><input type="checkbox" name="section_ids[]" value="'+s.id+'"> '+s.name+'</label>';
            });
            sectionBoxes.innerHTML=html;
        });
    }

    if(teacherEl) teacherEl.addEventListener('change',function(){if(document.querySelector('input[name="scope"]:checked').value==='subject')loadSubjects();});
    if(classEl) classEl.addEventListener('change',function(){
        if(document.querySelector('input[name="scope"]:checked').value==='subject')loadSubjects();
        if(document.querySelector('input[name="scope"]:checked').value==='section')loadSections();
    });

    // Expose to global for inline onchange
    window.updateScopeUI=updateScopeUI;

    // Form validation
    var form=document.getElementById('disallowalForm');
    if(form){
        form.addEventListener('submit',function(e){
            var scope=document.querySelector('input[name="scope"]:checked').value;
            if(scope==='subject'){
                var checked=document.querySelectorAll('input[name="subject_ids[]"]:checked');
                if(checked.length===0){
                    e.preventDefault();
                    alert('Please select at least one subject to disallow.');
                }
            }
            if(scope==='section'){
                var checked=document.querySelectorAll('input[name="section_ids[]"]:checked');
                if(checked.length===0){
                    e.preventDefault();
                    alert('Please select at least one section to disallow.');
                }
            }
        });
    }
})();
</script>
@endpush
