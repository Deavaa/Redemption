@extends('layouts.admin')
@section('title', 'Full Mark Sheet')

@push('styles')
<style>
.msf-page{animation:msfIn .4s ease-out}
@keyframes msfIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.msf-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem}
.msf-header-left{flex:1}
.msf-title{font-size:1.75rem;font-weight:800;color:#1a1a2e;margin:0;letter-spacing:-.5px}
.msf-subtitle{font-size:.9rem;color:#6c757d;margin:.25rem 0 0}
.msf-breadcrumb ol{display:flex;list-style:none;padding:0;margin:0 0 .5rem;gap:.5rem;font-size:.8rem;align-items:center}
.msf-breadcrumb li{color:#adb5bd}
.msf-breadcrumb li a{color:#6c757d;text-decoration:none;transition:color .2s}
.msf-breadcrumb li a:hover{color:#4361ee}
.msf-breadcrumb li+li::before{content:'/';margin-right:.5rem;color:#dee2e6}
.msf-breadcrumb li.active{color:#4361ee;font-weight:500}

.msf-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden;margin-bottom:1.25rem}
.msf-card-head{display:flex;align-items:center;gap:.75rem;padding:1rem 1.5rem;border-bottom:1px solid #f0f0f0;background:#fafbfc}
.msf-card-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.msf-card-icon.blue{background:#eef2ff;color:#4361ee}
.msf-card-icon.green{background:#ecfdf5;color:#10b981}
.msf-card-title{font-size:1rem;font-weight:700;color:#1a1a2e;margin:0}
.msf-card-desc{font-size:.82rem;color:#9ca3af;margin:.1rem 0 0}
.msf-card-body{padding:1.25rem 1.5rem}
.msf-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem}
.msf-group{display:flex;flex-direction:column}
.msf-label{font-weight:600;color:#374151;margin-bottom:.4rem;font-size:.85rem}
.msf-select{width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:.6rem 2.2rem .6rem .8rem;font-size:.88rem;color:#1a1a2e;background:#fff;appearance:none;cursor:pointer;transition:all .2s;background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");background-position:right .6rem center;background-repeat:no-repeat;background-size:1.15rem}
.msf-select:focus{outline:none;border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1)}
.msf-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.25rem;border-radius:10px;font-weight:600;font-size:.88rem;border:none;cursor:pointer;transition:all .25s;color:#fff;background:linear-gradient(135deg,#4361ee,#3a0ca3);box-shadow:0 2px 8px rgba(67,97,238,.3)}
.msf-btn:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(67,97,238,.4)}
.msf-btn-outline{background:transparent;color:#6b7280;border:1.5px solid #e5e7eb;box-shadow:none}
.msf-btn-outline:hover{border-color:#4361ee;color:#4361ee;background:#f8f9ff;transform:none;box-shadow:none}
.msf-actions{display:flex;justify-content:flex-end;gap:.75rem;padding:1rem 1.5rem;border-top:1px solid #f0f0f0;background:#fafbfc}

/* Report Header */
.msf-report-head{background:linear-gradient(135deg,#1e3a5f,#264b73);color:#fff;padding:1.25rem 1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem}
.msf-report-title{font-size:1.2rem;font-weight:800;margin:0}
.msf-report-meta{display:flex;gap:.75rem;flex-wrap:wrap}
.msf-report-chip{font-size:.78rem;background:rgba(255,255,255,.13);padding:.15rem .6rem;border-radius:6px}

/* Mark Sheet Table with rotated headers */
.msf-table-wrap{overflow-x:auto;overflow-y:visible;max-height:none}
.msf-table{width:100%;border-collapse:collapse;font-size:.78rem}
.msf-table th,.msf-table td{border:1px solid #d1d5db;padding:4px 6px;text-align:center}
.msf-table thead th{background:#f0f4ff;color:#1a1a2e;font-weight:700;vertical-align:bottom;position:relative}
.msf-table thead th.fixed-col{position:sticky;left:0;z-index:3;background:#f0f4ff;min-width:32px}
.msf-table thead th.name-col{position:sticky;left:32px;z-index:3;background:#f0f4ff;min-width:140px;text-align:left}

/* Rotated subject headers */
.msf-rotated-header{height:140px;min-width:38px;position:relative;writing-mode:vertical-rl;transform:rotate(180deg);text-align:left;white-space:nowrap;padding:4px 2px}
.msf-rotated-header span{display:inline-block;font-size:.72rem;font-weight:700;letter-spacing:.3px;line-height:1.2}

/* Term divider headers */
.msf-term-header{background:#4361ee;color:#fff;font-weight:700;font-size:.75rem;padding:6px 4px;text-align:center;min-width:38px}
.msf-term-header.term1{background:#3b82f6}
.msf-term-header.term2{background:#8b5cf6}
.msf-term-header.annual{background:#10b981}
.msf-term-header.total{background:#f59e0b;color:#1a1a2e}

/* Student rows */
.msf-table tbody td{vertical-align:middle}
.msf-table tbody tr:nth-child(even){background:#f9fafb}
.msf-table tbody tr:hover{background:#eef2ff}
.msf-table .stu-serial{font-weight:600;color:#6b7280;position:sticky;left:0;z-index:2;background:inherit}
.msf-table .stu-name{text-align:left;white-space:nowrap;font-weight:600;color:#1a1a2e;position:sticky;left:32px;z-index:2;background:inherit;min-width:140px}
.msf-table .mark-cell{min-width:38px;font-weight:500}
.msf-table .mark-cell.grade-fail{color:#ef4444;font-weight:700}
.msf-table .mark-cell.grade-pass{color:#10b981}
.msf-table .total-cell{font-weight:700;color:#4361ee;background:#f0f4ff}
.msf-table .annual-total-cell{font-weight:700;color:#10b981;background:#ecfdf5}
.msf-table .rank-cell{font-weight:700;color:#7c3aed}

/* Grade legend */
.msf-legend{display:flex;gap:.5rem;flex-wrap:wrap;padding:.75rem 1.5rem;border-top:1px solid #e5e7eb;background:#fafbfc;font-size:.72rem;color:#6b7280}
.msf-legend-item{display:inline-flex;align-items:center;gap:.25rem}
.msf-legend-dot{width:8px;height:8px;border-radius:2px;display:inline-block}

/* No data */
.msf-empty{text-align:center;padding:3rem 1rem;color:#9ca3af}
.msf-empty i{font-size:2.5rem;margin-bottom:.75rem;display:block}
.msf-empty p{margin:0;font-size:.95rem}

/* Print styles */
@media print{
    .msf-header,.msf-card,.msf-actions,.msf-legend{display:none!important}
    .msf-table{font-size:8pt}
    .msf-table th{background:#eee!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}
    .msf-table thead th.fixed-col,.msf-table thead th.name-col,.msf-table .stu-serial,.msf-table .stu-name{position:static!important}
}

/* Responsive */
@media(max-width:768px){.msf-grid{grid-template-columns:1fr 1fr}.msf-title{font-size:1.35rem}}
@media(max-width:480px){.msf-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="msf-page">
    <div class="msf-header">
        <div class="msf-header-left">
            <nav aria-label="breadcrumb" class="msf-breadcrumb"><ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li class="active">Full Mark Sheet</li></ol></nav>
            <h1 class="msf-title">Full Mark Sheet</h1>
            <p class="msf-subtitle">Complete mark sheet with Term 1, Term 2, and Annual results</p>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="msf-card">
        <div class="msf-card-head">
            <div class="msf-card-icon blue"><i class="fas fa-filter"></i></div>
            <div><h3 class="msf-card-title">Select Filters</h3><p class="msf-card-desc">Choose academic year and class</p></div>
        </div>
        <form method="POST" action="{{ route('admin.mark-sheet-full.generate') }}">
            @csrf
            <div class="msf-card-body">
                <div class="msf-grid">
                    <div class="msf-group">
                        <label class="msf-label">Academic Year <span style="color:#ef4444">*</span></label>
                        <select name="academic_year_id" class="msf-select" required>
                            <option value="">-- Select Year --</option>
                            @foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ old('academic_year_id') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="msf-group">
                        <label class="msf-label">Class <span style="color:#ef4444">*</span></label>
                        <select name="class_id" id="filterClass" class="msf-select" required>
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $c)<option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="msf-group">
                        <label class="msf-label">Section</label>
                        <select name="section_id" id="filterSection" class="msf-select">
                            <option value="">-- All Sections --</option>
                        </select>
                    </div>
                    <div class="msf-group" style="align-self:flex-end">
                        <button type="submit" class="msf-btn"><i class="fas fa-table"></i> Generate Sheet</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Results --}}
    @isset($roster)
    @if(count($roster) > 0)
    <div class="msf-card">
        <div class="msf-report-head">
            <h2 class="msf-report-title">Full Mark Sheet</h2>
            <div class="msf-report-meta">
                <span class="msf-report-chip">{{ $academicYear->name ?? '' }}</span>
                <span class="msf-report-chip">{{ $class->name ?? '' }}</span>
                @if($section)<span class="msf-report-chip">{{ $section->name }}</span>@endif
                @if($term1)<span class="msf-report-chip">{{ $term1->name }}</span>@endif
                @if($term2)<span class="msf-report-chip">{{ $term2->name }}</span>@endif
                <span class="msf-report-chip">{{ count($roster) }} Students</span>
                <span class="msf-report-chip">{{ $subjects->count() }} Subjects</span>
            </div>
        </div>

        <div class="msf-table-wrap">
            <table class="msf-table">
                <thead>
                    {{-- Row 1: Term group headers --}}
                    <tr>
                        <th class="fixed-col" rowspan="2">#</th>
                        <th class="name-col" rowspan="2">Student Name</th>

                        {{-- Term 1 group --}}
                        @foreach($subjects as $subj)
                        <th class="msf-term-header term1">T1</th>
                        @endforeach
                        <th class="msf-term-header term1" style="min-width:42px">T1 Tot</th>

                        {{-- Term 2 group --}}
                        @if($term2)
                        @foreach($subjects as $subj)
                        <th class="msf-term-header term2">T2</th>
                        @endforeach
                        <th class="msf-term-header term2" style="min-width:42px">T2 Tot</th>
                        @endif

                        {{-- Annual group --}}
                        @foreach($subjects as $subj)
                        <th class="msf-term-header annual">Ann</th>
                        @endforeach
                        <th class="msf-term-header total" style="min-width:42px">Total</th>
                        <th class="msf-term-header total" style="min-width:42px">Rank</th>
                    </tr>

                    {{-- Row 2: Subject name headers (rotated 90°) --}}
                    <tr>
                        {{-- Term 1 subjects --}}
                        @foreach($subjects as $subj)
                        <th class="msf-rotated-header"><span>{{ $subj->name }}</span></th>
                        @endforeach
                        <th class="msf-rotated-header" style="background:#dbeafe"><span>Term 1 Total</span></th>

                        {{-- Term 2 subjects --}}
                        @if($term2)
                        @foreach($subjects as $subj)
                        <th class="msf-rotated-header"><span>{{ $subj->name }}</span></th>
                        @endforeach
                        <th class="msf-rotated-header" style="background:#ede9fe"><span>Term 2 Total</span></th>
                        @endif

                        {{-- Annual subjects --}}
                        @foreach($subjects as $subj)
                        <th class="msf-rotated-header"><span>{{ $subj->name }}</span></th>
                        @endforeach
                        <th class="msf-rotated-header" style="background:#d1fae5"><span>Annual Total</span></th>
                        <th class="msf-rotated-header" style="background:#fef3c7"><span>Rank</span></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($roster as $i => $row)
                    <tr>
                        <td class="stu-serial">{{ $i + 1 }}</td>
                        <td class="stu-name">{{ $row['student']->first_name ?? '' }} {{ $row['student']->last_name ?? '' }}</td>

                        {{-- Term 1 marks --}}
                        @foreach($subjects as $subj)
                            @php $t1 = $row['term1'][$subj->id] ?? null @endphp
                            <td class="mark-cell @if($t1 && $t1['grand_total'] !== null && floatval($t1['grand_total']) < 40) grade-fail @elseif($t1 && $t1['grand_total'] !== null) grade-pass @endif">
                                @if($t1 && $t1['grand_total'] !== null)
                                    {{ $t1['grand_total'] }}
                                    @if($t1['grade'])<br><small style="font-size:.65rem;color:#6b7280">{{ $t1['grade'] }}</small>@endif
                                @else
                                    <span style="color:#d1d5db">-</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="total-cell">{{ $row['term1_total'] ?: '-' }}</td>

                        {{-- Term 2 marks --}}
                        @if($term2)
                        @foreach($subjects as $subj)
                            @php $t2 = $row['term2'][$subj->id] ?? null @endphp
                            <td class="mark-cell @if($t2 && $t2['grand_total'] !== null && floatval($t2['grand_total']) < 40) grade-fail @elseif($t2 && $t2['grand_total'] !== null) grade-pass @endif">
                                @if($t2 && $t2['grand_total'] !== null)
                                    {{ $t2['grand_total'] }}
                                    @if($t2['grade'])<br><small style="font-size:.65rem;color:#6b7280">{{ $t2['grade'] }}</small>@endif
                                @else
                                    <span style="color:#d1d5db">-</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="total-cell">{{ $row['term2_total'] ?: '-' }}</td>
                        @endif

                        {{-- Annual marks --}}
                        @foreach($subjects as $subj)
                            @php $ann = $row['annual'][$subj->id] ?? null @endphp
                            <td class="mark-cell @if($ann && $ann['grand_total'] !== null && floatval($ann['grand_total']) < 40) grade-fail @elseif($ann && $ann['grand_total'] !== null) grade-pass @endif">
                                @if($ann && $ann['grand_total'] !== null)
                                    {{ $ann['grand_total'] }}
                                    @if($ann['grade'])<br><small style="font-size:.65rem;color:#6b7280">{{ $ann['grade'] }}</small>@endif
                                @else
                                    <span style="color:#d1d5db">-</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="annual-total-cell">{{ $row['annual_total'] ?: '-' }}</td>
                        <td class="rank-cell">{{ $row['rank'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Grade Legend --}}
        <div class="msf-legend">
            <span style="font-weight:700;margin-right:.5rem">Grading Scale:</span>
            <span class="msf-legend-item"><span class="msf-legend-dot" style="background:#10b981"></span> A+ (90+)</span>
            <span class="msf-legend-item"><span class="msf-legend-dot" style="background:#22c55e"></span> A (80-89)</span>
            <span class="msf-legend-item"><span class="msf-legend-dot" style="background:#84cc16"></span> B+ (70-79)</span>
            <span class="msf-legend-item"><span class="msf-legend-dot" style="background:#eab308"></span> C (50-69)</span>
            <span class="msf-legend-item"><span class="msf-legend-dot" style="background:#ef4444"></span> F (&lt;40)</span>
            <span style="margin-left:1rem;font-weight:600">T1 = Term 1</span>
            <span style="font-weight:600">T2 = Term 2</span>
            <span style="font-weight:600">Ann = Annual (Avg of T1+T2)</span>
        </div>

        <div class="msf-actions">
            <button onclick="window.print()" class="msf-btn msf-btn-outline"><i class="fas fa-print"></i> Print</button>
            <button onclick="exportCSV()" class="msf-btn msf-btn-outline"><i class="fas fa-file-csv"></i> Export CSV</button>
        </div>
    </div>
    @else
    <div class="msf-card">
        <div class="msf-empty">
            <i class="fas fa-clipboard-list"></i>
            <p>No marks found for the selected filters.</p>
            <p style="font-size:.82rem;margin-top:.5rem">Please make sure marks have been entered for the selected academic year and class.</p>
        </div>
    </div>
    @endif
    @endisset
</div>
@endsection

@push('scripts')
<script>
(function(){
    var cls=document.getElementById('filterClass');
    var sec=document.getElementById('filterSection');
    if(cls){
        cls.addEventListener('change',function(){
            if(!this.value){sec.innerHTML='<option value="">-- All Sections --</option>';return;}
            fetch('{{ route("admin.mark-sheet-full.sections") }}?class_id='+this.value,{credentials:'same-origin'})
            .then(function(r){return r.json();})
            .then(function(data){
                sec.innerHTML='<option value="">-- All Sections --</option>';
                data.forEach(function(s){sec.innerHTML+='<option value="'+s.id+'">'+s.name+'</option>';});
            });
        });
    }
})();

function exportCSV(){
    var table=document.querySelector('.msf-table');
    if(!table)return;
    var rows=table.querySelectorAll('tr');
    var csv=[];
    rows.forEach(function(row){
        var cols=row.querySelectorAll('td,th');
        var rowData=[];
        cols.forEach(function(col){
            var text=col.innerText.replace(/"/g,'""').replace(/\n/g,' ');
            rowData.push('"'+text+'"');
        });
        csv.push(rowData.join(','));
    });
    var blob=new Blob([csv.join('\n')],{type:'text/csv;charset=utf-8;'});
    var link=document.createElement('a');
    link.href=URL.createObjectURL(blob);
    link.download='mark_sheet_full.csv';
    link.click();
}
</script>
@endpush
