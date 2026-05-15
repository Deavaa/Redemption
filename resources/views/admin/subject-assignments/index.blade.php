@extends('layouts.admin')
@section('title', 'Subject Assignments')
@push('styles')
<style>.type-badge-core{background:#0d6efd;color:#fff;padding:3px 10px;border-radius:12px;font-size:.75rem;font-weight:600}.type-badge-elective{background:#fd7e14;color:#fff;padding:3px 10px;border-radius:12px;font-size:.75rem;font-weight:600}.section-all{background:#e7f1ff;color:#0d6efd;padding:2px 8px;border-radius:8px;font-size:.75rem;font-weight:600}.section-specific{background:#fff3e0;color:#e65100;padding:2px 8px;border-radius:8px;font-size:.75rem;font-weight:600}.row-core{border-left:4px solid #0d6efd!important}.row-elective{border-left:4px solid #fd7e14!important}.bulk-toolbar{display:none;background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:8px 16px;margin-bottom:12px;align-items:center;gap:12px}.bulk-toolbar.active{display:flex}</style>
@endpush
@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1 fw-bold">Subject Assignments</h4><p class="text-muted mb-0">Core = all sections, Elective = specific sections.</p></div>
        <a href="{{ route('admin.subject-assignments.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Assign Subject</a>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 bg-primary bg-opacity-10"><div class="card-body text-center py-3"><div class="fs-3 fw-bold text-primary">{{ $coreAssignments->count() }}</div><div class="text-muted small">Core (All Sections)</div></div></div></div>
        <div class="col-md-4"><div class="card border-0 bg-warning bg-opacity-10"><div class="card-body text-center py-3"><div class="fs-3 fw-bold text-warning">{{ $electiveAssignments->count() }}</div><div class="text-muted small">Elective (Specific)</div></div></div></div>
        <div class="col-md-4"><div class="card border-0 bg-success bg-opacity-10"><div class="card-body text-center py-3"><div class="fs-3 fw-bold text-success">{{ $assignments->count() }}</div><div class="text-muted small">Total</div></div></div></div>
    </div>
    <form method="GET" action="{{ route('admin.subject-assignments.index') }}"><div class="card mb-3"><div class="card-body py-3"><div class="row g-2 align-items-end">
        <div class="col-md-4"><label class="form-label fw-semibold small">Academic Year</label><select name="academic_year_id" class="form-select form-select-sm"><option value="">All Years</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ request('academic_year_id') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label fw-semibold small">Class</label><select name="class_id" class="form-select form-select-sm"><option value="">All Classes</option>@foreach($classes as $c)<option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach</select></div>
        <div class="col-md-4 d-flex gap-2"><button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-funnel me-1"></i> Filter</button><a href="{{ route('admin.subject-assignments.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i></a></div>
    </div></div></div></form>
    <form method="POST" action="{{ route('admin.subject-assignments.bulk-delete') }}" id="bulkForm">@csrf @method('DELETE')

    {{-- Bulk Delete Toolbar --}}
    <div class="bulk-toolbar" id="bulkToolbar">
        <i class="bi bi-check2-square text-warning"></i>
        <span id="selectedCount">0 selected</span>
        <button type="submit" class="btn btn-danger btn-sm" id="bulkDeleteBtn" disabled onclick="return confirm('Delete all selected assignments?')">
            <i class="bi bi-trash me-1"></i> Delete Selected
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearAllChecks()">Cancel</button>
    </div>

    @if($assignments->count() > 0)
    <div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0"><thead class="table-dark"><tr><th style="width:40px"><input type="checkbox" class="form-check-input" id="selectAll"></th><th>#</th><th>Subject</th><th>Class</th><th>Section</th><th>Type</th><th>Teacher</th><th>AY</th><th>Actions</th></tr></thead>
        <tbody>@foreach($assignments as $a)@php $isCore=is_null($a->section_id); @endphp
        <tr class="{{ $isCore ? 'row-core' : 'row-elective' }}">
            <td><input type="checkbox" class="form-check-input bulk-check" name="ids[]" value="{{ $a->id }}"></td>
            <td>{{ $loop->iteration }}</td>
            <td><span class="fw-semibold">{{ $a->subject->name ?? 'N/A' }}</span></td>
            <td>{{ $a->classRoom->name ?? 'N/A' }}</td>
            <td>@if($isCore)<span class="section-all"><i class="bi bi-collection me-1"></i>All</span>@else<span class="section-specific">{{ $a->section->name ?? 'N/A' }}</span>@endif</td>
            <td><span class="{{ $isCore ? 'type-badge-core' : 'type-badge-elective' }}">{{ $isCore ? 'Core' : 'Elective' }}</span></td>
            <td>{{ $a->teacher->full_name ?? 'N/A' }}</td>
            <td class="small">{{ $a->academicYear->name ?? 'N/A' }}</td>
            <td><div class="btn-group btn-group-sm"><a href="{{ route('admin.subject-assignments.edit', $a) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
            <button type="button" class="btn btn-outline-danger btn-delete-single" data-url="{{ route('admin.subject-assignments.destroy', $a) }}"><i class="bi bi-trash"></i></button></div></td>
        </tr>@endforeach</tbody></table>
    </div></div></div>
    @else
    <div class="card"><div class="card-body text-center py-5"><i class="bi bi-link-45deg display-1 text-muted"></i><h5 class="mt-3 text-muted">No Subject Assignments Yet</h5><a href="{{ route('admin.subject-assignments.create') }}" class="btn btn-primary mt-2">Assign First Subject</a></div></div>
    @endif
    </form>
</div>
@endsection
@push('scripts')
<script>
document.getElementById('selectAll')?.addEventListener('change',function(){
    document.querySelectorAll('.bulk-check').forEach(cb=>cb.checked=this.checked);
    updUI();
});
document.querySelectorAll('.bulk-check').forEach(cb=>{
    cb.addEventListener('change',function(){
        const a=document.querySelectorAll('.bulk-check'),
              c=document.querySelectorAll('.bulk-check:checked');
        document.getElementById('selectAll').checked=(a.length===c.length&&a.length>0);
        updUI();
    });
});
function updUI(){
    const c=document.querySelectorAll('.bulk-check:checked');
    const toolbar=document.getElementById('bulkToolbar');
    const countEl=document.getElementById('selectedCount');
    const btnEl=document.getElementById('bulkDeleteBtn');
    if(countEl) countEl.textContent=c.length+' selected';
    if(btnEl) btnEl.disabled=(c.length===0);
    if(toolbar) toolbar.classList.toggle('active',c.length>0);
}
function clearAllChecks(){
    document.querySelectorAll('.bulk-check').forEach(cb=>cb.checked=false);
    const sa=document.getElementById('selectAll');
    if(sa) sa.checked=false;
    updUI();
}
document.querySelectorAll('.btn-delete-single').forEach(btn=>{
    btn.addEventListener('click',function(){
        if(confirm('Remove this subject assignment?')){
            const url=this.dataset.url;
            const f=document.createElement('form');
            f.method='POST';
            f.action=url;
            const t=document.createElement('input');
            t.type='hidden';
            t.name='_token';
            t.value=document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            f.appendChild(t);
            const m=document.createElement('input');
            m.type='hidden';
            m.name='_method';
            m.value='DELETE';
            f.appendChild(m);
            document.body.appendChild(f);
            f.submit();
        }
    });
});
</script>
@endpush
