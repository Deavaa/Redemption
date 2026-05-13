@extends('layouts.admin')
@section('title', 'Mark Roster')

@push('styles')
<style>
.mr-page{animation:mrIn .4s ease-out}@keyframes mrIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.mr-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem}
.mr-header-left{flex:1}
.mr-title{font-size:1.75rem;font-weight:800;color:#1a1a2e;margin:0;letter-spacing:-.5px}
.mr-subtitle{font-size:.9rem;color:#6c757d;margin:.25rem 0 0}
.mr-breadcrumb ol{display:flex;list-style:none;padding:0;margin:0 0 .5rem;gap:.5rem;font-size:.8rem;align-items:center}
.mr-breadcrumb li{color:#adb5bd}.mr-breadcrumb li a{color:#6c757d;text-decoration:none;transition:color .2s}.mr-breadcrumb li a:hover{color:#4361ee}
.mr-breadcrumb li+li::before{content:'/';margin-right:.5rem;color:#dee2e6}.mr-breadcrumb li.active{color:#4361ee;font-weight:500}
.mr-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden;margin-bottom:1.25rem}
.mr-card-head{display:flex;align-items:center;gap:.75rem;padding:1rem 1.5rem;border-bottom:1px solid #f0f0f0;background:#fafbfc}
.mr-card-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.mr-card-icon.blue{background:#eef2ff;color:#4361ee}.mr-card-icon.green{background:#ecfdf5;color:#10b981}.mr-card-icon.gold{background:#fefce8;color:#d97706}.mr-card-icon.purple{background:#f5f3ff;color:#7c3aed}
.mr-card-title{font-size:1rem;font-weight:700;color:#1a1a2e;margin:0}.mr-card-desc{font-size:.82rem;color:#9ca3af;margin:.1rem 0 0}
.mr-card-body{padding:1.25rem 1.5rem}
.mr-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem}
.mr-group{display:flex;flex-direction:column}
.mr-label{font-weight:600;color:#374151;margin-bottom:.4rem;font-size:.85rem}
.mr-select{width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:.6rem 2.2rem .6rem .8rem;font-size:.88rem;color:#1a1a2e;background:#fff;appearance:none;cursor:pointer;transition:all .2s;background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");background-position:right .6rem center;background-repeat:no-repeat;background-size:1.15rem}
.mr-select:focus{outline:none;border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1)}
.mr-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.25rem;border-radius:10px;font-weight:600;font-size:.88rem;border:none;cursor:pointer;transition:all .25s;color:#fff;background:linear-gradient(135deg,#4361ee,#3a0ca3);box-shadow:0 2px 8px rgba(67,97,238,.3)}
.mr-btn:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(67,97,238,.4)}
.mr-btn-outline{background:transparent;color:#6b7280;border:1.5px solid #e5e7eb;box-shadow:none}.mr-btn-outline:hover{border-color:#4361ee;color:#4361ee;background:#f8f9ff;transform:none;box-shadow:none}
.mr-actions{display:flex;justify-content:flex-end;gap:.75rem;padding:1rem 1.5rem;border-top:1px solid #f0f0f0;background:#fafbfc}

/* Report Header */
.mr-report-head{background:linear-gradient(135deg,#1e3a5f,#264b73);color:#fff;padding:1.25rem 1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem}
.mr-report-title{font-size:1.2rem;font-weight:800;margin:0}.mr-report-meta{display:flex;gap:.75rem;flex-wrap:wrap}
.mr-report-chip{font-size:.78rem;background:rgba(255,255,255,.13);padding:.15rem .6rem;border-radius:6px}

/* Roster Table */
.mr-table-wrap{overflow-x:auto}
.mr-table{width:100%;border-collapse:collapse;font-size:.82rem}
.mr-table th{background:#f8fafc;color:#374151;font-weight:700;padding:.55rem .5rem;border:1px solid #e5e7eb;white-space:nowrap;text-align:center;position:sticky;top:0}
.mr-table td{padding:.45rem .5rem;border:1px solid #e5e7eb;text-align:center}
.mr-table tbody tr:nth-child(even){background:#f9fafb}
.mr-table tbody tr:hover{background:#eef2ff}
.mr-table .stu-name{text-align:left;white-space:nowrap;font-weight:600;color:#1a1a2e;min-width:140px}
.mr-table .total-col{font-weight:700;color:#4361ee;background:#f0f4ff}
.mr-table .rank-col{font-weight:700}

/* Print */
@media print{.mr-header,.mr-card,.mr-actions,.mr-report-head{display:none!important}.mr-table{font-size:10pt}.mr-table th{background:#eee!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}}

/* Responsive */
@media(max-width:768px){.mr-grid{grid-template-columns:1fr 1fr}.mr-title{font-size:1.35rem}}
@media(max-width:480px){.mr-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="mr-page">
    <div class="mr-header">
        <div class="mr-header-left">
            <nav aria-label="breadcrumb" class="mr-breadcrumb"><ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li class="active">Mark Roster</li></ol></nav>
            <h1 class="mr-title">Mark Roster</h1>
            <p class="mr-subtitle">Class-wise mark roster showing all students and subjects</p>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="mr-card">
        <div class="mr-card-head">
            <div class="mr-card-icon blue"><i class="fas fa-filter"></i></div>
            <div><h3 class="mr-card-title">Select Filters</h3><p class="mr-card-desc">Choose academic year, term, and class</p></div>
        </div>
        <form method="POST" action="{{ route('admin.mark-roster.generate') }}">
            @csrf
            <div class="mr-card-body">
                <div class="mr-grid">
                    <div class="mr-group">
                        <label class="mr-label">Academic Year <span style="color:#ef4444">*</span></label>
                        <select name="academic_year_id" class="mr-select" required>
                            <option value="">-- Select Year --</option>
                            @foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ old('academic_year_id') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mr-group">
                        <label class="mr-label">Term <span style="color:#ef4444">*</span></label>
                        <select name="term_id" class="mr-select" required>
                            <option value="">-- Select Term --</option>
                            @foreach($terms as $t)<option value="{{ $t->id }}" {{ old('term_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mr-group">
                        <label class="mr-label">Class <span style="color:#ef4444">*</span></label>
                        <select name="class_id" id="filterClass" class="mr-select" required>
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $c)<option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mr-group">
                        <label class="mr-label">Section</label>
                        <select name="section_id" id="filterSection" class="mr-select">
                            <option value="">-- All Sections --</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="mr-actions">
                <button type="submit" class="mr-btn"><i class="fas fa-table"></i> Generate Roster</button>
            </div>
        </form>
    </div>

    {{-- Roster Results --}}
    @isset($roster)
    <div class="mr-card">
        <div class="mr-report-head">
            <h2 class="mr-report-title">Mark Roster</h2>
            <div class="mr-report-meta">
                <span class="mr-report-chip">{{ $academicYear->name ?? '' }}</span>
                <span class="mr-report-chip">{{ $term->name ?? '' }}</span>
                <span class="mr-report-chip">{{ $class->name ?? '' }}</span>
                @if($section)<span class="mr-report-chip">{{ $section->name }}</span>@endif
            </div>
        </div>
        <div class="mr-table-wrap">
            <table class="mr-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th style="text-align:left">Student Name</th>
                        <th>Roll No</th>
                        @foreach($subjects as $subj)<th>{{ $subj->name }}</th>@endforeach
                        <th class="total-col">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roster as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="stu-name">{{ $row['student']->first_name ?? '' }} {{ $row['student']->last_name ?? '' }}</td>
                        <td>{{ $row['student']->roll_number ?? '-' }}</td>
                        @foreach($subjects as $subj)
                            @php $m = $row['subjectMarks'][$subj->id] ?? null @endphp
                            <td>{{ $m ? ($m['grand_total'] ?? '-') : '-' }}</td>
                        @endforeach
                        <td class="total-col">{{ $row['grandTotal'] }}</td>
                    </tr>
                    @endforeach
                    @if($roster->isEmpty())
                    <tr><td colspan="{{ 3 + $subjects->count() + 1 }}" style="text-align:center;padding:2rem;color:#9ca3af">No marks found for the selected filters</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="mr-actions">
            <button onclick="window.print()" class="mr-btn mr-btn-outline"><i class="fas fa-print"></i> Print</button>
        </div>
    </div>
    @endisset
</div>
@endsection

@push('scripts')
<script>
(function(){
    var cls=document.getElementById('filterClass');
    var sec=document.getElementById('filterSection');
    cls.addEventListener('change',function(){
        if(!this.value){sec.innerHTML='<option value="">-- All Sections --</option>';return;}
        fetch('{{ route("admin.mark-roster.sections") }}?class_id='+this.value,{credentials:'same-origin'}).then(function(r){return r.json();}).then(function(data){
            sec.innerHTML='<option value="">-- All Sections --</option>';
            data.forEach(function(s){sec.innerHTML+='<option value="'+s.id+'">'+s.name+'</option>';});
        });
    });
})();
</script>
@endpush
