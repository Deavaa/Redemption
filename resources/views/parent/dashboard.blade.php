@extends('parent.layout')

@section('title', 'Parent Dashboard')

@section('content')
<div class="dash-welcome">
    <h2><i class="fas fa-hand-sparkles me-2" style="color: var(--primary);"></i> Welcome, {{ $parent->father_name ?? $parent->mother_name ?? 'Parent' }}!</h2>
    <p>
        @if($activeAy)
            Academic Year: <strong>{{ $activeAy->name }}</strong>
            @if($activeTerm)
                &nbsp;&bull;&nbsp; Term: <strong>{{ $activeTerm->name }}</strong>
            @endif
        @else
            No active academic year set.
        @endif
    </p>
</div>

@if($children->count() === 0)
<div class="empty-state">
    <i class="fas fa-user-friends"></i>
    <h5>No Children Linked</h5>
    <p>No students are currently linked to your account. Please contact the school administration.</p>
</div>
@else

{{-- Stats Overview --}}
<div class="stat-cards" style="grid-template-columns: repeat({{ min($children->count(), 4) }}, 1fr);">
    @foreach($children as $child)
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-user-graduate"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $child->first_name }}</h3>
            <p>{{ $child->classroom->name ?? 'No Class' }} - {{ $child->section->name ?? 'N/A' }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Children Cards --}}
<div class="row g-4">
@foreach($children as $child)
    @php
        $childData = $childrenMarks[$child->id] ?? null;
        $avg = $childData['average'] ?? null;
        $totalFees = $childData['totalFees'] ?? 0;
        $totalPaid = $childData['totalPaid'] ?? 0;
        $feeBalance = $totalFees - $totalPaid;
    @endphp
    <div class="col-md-6 col-xl-4">
        <div class="child-card">
            <div class="child-card-header">
                <div class="child-avatar">
                    {{ strtoupper(substr($child->first_name, 0, 1)) }}
                </div>
                <div>
                    <h5>{{ $child->first_name }} {{ $child->last_name }}</h5>
                    <small>
                        {{ $child->classroom->name ?? 'No Class' }}
                        @if($child->section) &mdash; {{ $child->section->name }}@endif
                        &bull; Roll: {{ $child->roll_number ?? 'N/A' }}
                    </small>
                </div>
            </div>
            <div class="child-card-body">
                <div class="child-stats">
                    <div class="child-stat">
                        <div class="child-stat-value" style="color: var(--primary);">
                            {{ $avg !== null ? $avg : '—' }}
                        </div>
                        <div class="child-stat-label">Term Average</div>
                    </div>
                    <div class="child-stat">
                        <div class="child-stat-value" style="color: var(--success);">
                            {{ number_format($totalPaid, 0) }}
                        </div>
                        <div class="child-stat-label">Fees Paid</div>
                    </div>
                    <div class="child-stat">
                        <div class="child-stat-value" style="color: {{ $feeBalance > 0 ? 'var(--danger)' : 'var(--success)' }};">
                            {{ number_format($feeBalance, 0) }}
                        </div>
                        <div class="child-stat-label">Balance</div>
                    </div>
                </div>
            </div>
            <div class="child-card-footer">
                <a href="{{ route('parent.child.marks', $child->id) }}"><i class="fas fa-pen"></i> Marks</a>
                <a href="{{ route('parent.child.progress', $child->id) }}"><i class="fas fa-chart-line"></i> Progress</a>
                <a href="{{ route('parent.child.fees', $child->id) }}"><i class="fas fa-money-bill-wave"></i> Fees</a>
                <a href="{{ route('parent.child.profile', $child->id) }}"><i class="fas fa-id-card"></i> Profile</a>
            </div>
        </div>
    </div>
@endforeach
</div>

@endif
@endsection
