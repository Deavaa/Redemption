@extends('layouts.admin')
@section('title', 'Transfer Teacher')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.teachers.index') }}">Teachers</a></li>
                    <li class="active">Transfer</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="modern-card" style="max-width:600px;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <h3 style="font-size:15px;font-weight:700;color:var(--text-dark);margin:0;"><i class="fas fa-exchange-alt" style="color:var(--primary);"></i> Transfer Teacher to Another Branch</h3>
        </div>
        <form method="POST" action="{{ route('admin.teachers.transfer-store', $teacher) }}" style="padding:16px 20px;">
            @csrf
            <div style="margin-bottom:16px;padding:12px;background:var(--bg);border-radius:8px;border:1px solid var(--border);">
                <div style="font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.3px;margin-bottom:4px;">Current Teacher</div>
                <div style="font-size:14px;font-weight:600;color:var(--text-dark);">{{ $teacher->full_name }}</div>
                <div style="font-size:12px;color:var(--text-muted);">Current Branch: <strong>{{ $teacher->branch?->name ?? 'Not assigned' }}</strong></div>
            </div>
            <div style="margin-bottom:12px;">
                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Transfer To Branch *</label>
                <select name="branch_id" required style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 10px;font-size:13px;">
                    <option value="">Select Branch</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:16px;">
                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Transfer Reason</label>
                <textarea name="transfer_reason" rows="3" style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 10px;font-size:13px;resize:vertical;" placeholder="Optional reason for transfer..."></textarea>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="{{ route('admin.teachers.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
                <button type="submit" class="btn-modern btn-modern-primary"><i class="fas fa-exchange-alt"></i> Transfer Teacher</button>
            </div>
        </form>
    </div>
</div>
@endsection
