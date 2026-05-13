@extends('layouts.admin')
@section('title', 'Parent Details')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">Students</a></li>
                    <li><a href="{{ route('admin.parents.index') }}">Parents</a></li>
                    <li class="active">{{ $item->father_name }}</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">{{ $item->father_name }}</h1>
            <p class="modern-page-subtitle">Parent details and contact information</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.parents.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
            <a href="{{ route('admin.parents.edit', $item->id) }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-pen"></i>
                <span>Edit</span>
            </a>
        </div>
    </div>

    <div class="modern-detail-grid">
        {{-- Main Info Card --}}
        <div class="modern-card modern-detail-main">
            {{-- Hero Section --}}
            <div class="modern-detail-hero">
                <div class="modern-detail-hero-icon">
                    {{ strtoupper(substr($item->father_name, 0, 1)) }}
                </div>
                <div class="modern-detail-hero-info">
                    <h2 class="modern-detail-hero-title">{{ $item->father_name }}</h2>
                    <div class="modern-detail-hero-badges">
                        @if($item->father_phone)
                            <span class="modern-badge modern-badge-info"><i class="fas fa-phone"></i> {{ $item->father_phone }}</span>
                        @endif
                        @if($item->father_occupation)
                            <span class="modern-badge modern-badge-light"><i class="fas fa-briefcase"></i> {{ $item->father_occupation }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Father Information --}}
            <div class="modern-detail-section">
                <div class="modern-detail-section-header">
                    <i class="fas fa-user"></i> Father Information
                </div>
                <div class="modern-detail-body">
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-user"></i> Father Name
                        </div>
                        <div class="modern-detail-value">{{ $item->father_name }}</div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-phone"></i> Father Phone
                        </div>
                        <div class="modern-detail-value">
                            @if($item->father_phone)
                                <a href="tel:{{ $item->father_phone }}" class="modern-link">{{ $item->father_phone }}</a>
                            @else
                                <span class="modern-muted">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-briefcase"></i> Father Occupation
                        </div>
                        <div class="modern-detail-value">{{ $item->father_occupation ?? '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- Mother Information --}}
            <div class="modern-detail-section">
                <div class="modern-detail-section-header">
                    <i class="fas fa-user"></i> Mother Information
                </div>
                <div class="modern-detail-body">
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-user"></i> Mother Name
                        </div>
                        <div class="modern-detail-value">{{ $item->mother_name ?? '-' }}</div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-phone"></i> Mother Phone
                        </div>
                        <div class="modern-detail-value">
                            @if($item->mother_phone)
                                <a href="tel:{{ $item->mother_phone }}" class="modern-link">{{ $item->mother_phone }}</a>
                            @else
                                <span class="modern-muted">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-briefcase"></i> Mother Occupation
                        </div>
                        <div class="modern-detail-value">{{ $item->mother_occupation ?? '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- Guardian Information --}}
            <div class="modern-detail-section">
                <div class="modern-detail-section-header">
                    <i class="fas fa-shield-alt"></i> Guardian Information
                </div>
                <div class="modern-detail-body">
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-user-shield"></i> Guardian Name
                        </div>
                        <div class="modern-detail-value">{{ $item->guardian_name ?? '-' }}</div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-link"></i> Guardian Relation
                        </div>
                        <div class="modern-detail-value">{{ $item->guardian_relation ?? '-' }}</div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-phone"></i> Guardian Phone
                        </div>
                        <div class="modern-detail-value">
                            @if($item->guardian_phone)
                                <a href="tel:{{ $item->guardian_phone }}" class="modern-link">{{ $item->guardian_phone }}</a>
                            @else
                                <span class="modern-muted">-</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Linked Students --}}
            @if($item->relationLoaded('students') && $item->students->count() > 0)
            <div class="modern-detail-section">
                <div class="modern-detail-section-header">
                    <i class="fas fa-user-graduate"></i> Linked Students
                </div>
                <div class="modern-detail-body">
                    @foreach($item->students as $student)
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-user-graduate"></i> {{ $student->first_name ?? '' }} {{ $student->last_name ?? '' }}
                        </div>
                        <div class="modern-detail-value">
                            @if($student->pivot && $student->pivot->relation)
                                <span class="modern-badge modern-badge-info">{{ ucfirst($student->pivot->relation) }}</span>
                            @else
                                <span class="modern-badge modern-badge-light">Student</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="modern-detail-sidebar">
            {{-- Quick Actions Card --}}
            <div class="modern-card">
                <div class="modern-card-header-simple">
                    <i class="fas fa-bolt"></i> Quick Actions
                </div>
                <div class="modern-quick-actions">
                    <a href="{{ route('admin.parents.edit', $item->id) }}" class="modern-quick-action">
                        <i class="fas fa-pen"></i>
                        <span>Edit Parent</span>
                    </a>
                    @if($item->father_phone)
                    <a href="tel:{{ $item->father_phone }}" class="modern-quick-action">
                        <i class="fas fa-phone"></i>
                        <span>Call Father</span>
                    </a>
                    @endif
                    @if($item->mother_phone)
                    <a href="tel:{{ $item->mother_phone }}" class="modern-quick-action">
                        <i class="fas fa-phone"></i>
                        <span>Call Mother</span>
                    </a>
                    @endif
                    <form method="POST" action="{{ route('admin.parents.destroy', $item->id) }}" onsubmit="return confirm('Are you sure you want to delete this parent? This action cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="modern-quick-action modern-quick-action-danger">
                            <i class="fas fa-trash-alt"></i>
                            <span>Delete Parent</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Timestamps Card --}}
            <div class="modern-card">
                <div class="modern-card-header-simple">
                    <i class="fas fa-clock"></i> Timestamps
                </div>
                <div class="modern-timestamps">
                    <div class="modern-timestamp">
                        <span class="modern-timestamp-label">Created</span>
                        <span class="modern-timestamp-value">{{ $item->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div class="modern-timestamp">
                        <span class="modern-timestamp-label">Updated</span>
                        <span class="modern-timestamp-value">{{ $item->updated_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Modern Page Layout */
.modern-page { animation: fadeSlideIn 0.4s ease-out; }

@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.modern-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.75rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.modern-page-header-left { flex: 1; }

.modern-page-header-right {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.modern-page-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0;
    letter-spacing: -0.5px;
}

.modern-page-subtitle {
    font-size: 0.9rem;
    color: #6c757d;
    margin: 0.25rem 0 0;
}

/* Breadcrumb */
.modern-breadcrumb ol {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0 0 0.5rem;
    gap: 0.5rem;
    font-size: 0.8rem;
    align-items: center;
}

.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* Detail Grid */
.modern-detail-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 1.25rem;
    align-items: start;
}

/* Card */
.modern-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

/* Detail Hero */
.modern-detail-hero {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.75rem 2rem;
    background: linear-gradient(135deg, #f8f9ff 0%, #eef2ff 100%);
    border-bottom: 1px solid #e5e8ff;
}

.modern-detail-hero-icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
    font-weight: 800;
}

.modern-detail-hero-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0 0 0.5rem;
}

.modern-detail-hero-badges {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* Badges */
.modern-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.25rem 0.65rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
}

.modern-badge-success { background: #ecfdf5; color: #059669; }
.modern-badge-danger { background: #fef2f2; color: #dc2626; }
.modern-badge-info { background: #eff6ff; color: #2563eb; }
.modern-badge-warning { background: #fefce8; color: #b45309; }
.modern-badge-light { background: #f3f4f6; color: #6b7280; }
.modern-badge-gold { background: #fefce8; color: #d97706; }

/* Detail Sections */
.modern-detail-section {
    border-bottom: 1px solid #f0f0f0;
}

.modern-detail-section:last-child {
    border-bottom: none;
}

.modern-detail-section-header {
    padding: 0.85rem 2rem;
    font-weight: 600;
    color: #4361ee;
    font-size: 0.88rem;
    background: #fafbfc;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modern-detail-section-header i {
    font-size: 0.82rem;
}

/* Detail Body */
.modern-detail-body { padding: 0.5rem 0; }

.modern-detail-row {
    display: flex;
    padding: 0.9rem 2rem;
    border-bottom: 1px solid #f8f9fa;
    transition: background 0.15s;
}

.modern-detail-row:last-child { border-bottom: none; }
.modern-detail-row:hover { background: #fafbfc; }

.modern-detail-label {
    width: 180px;
    flex-shrink: 0;
    font-weight: 600;
    color: #6b7280;
    font-size: 0.88rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modern-detail-label i { color: #9ca3af; font-size: 0.82rem; width: 16px; text-align: center; }

.modern-detail-value {
    color: #1a1a2e;
    font-size: 0.9rem;
}

.modern-link { color: #4361ee; text-decoration: none; font-weight: 500; }
.modern-link:hover { text-decoration: underline; }

.modern-muted { color: #d1d5db; }

/* Sidebar */
.modern-detail-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.modern-card-header-simple {
    padding: 1rem 1.25rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.9rem;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modern-card-header-simple i { color: #4361ee; font-size: 0.85rem; }

/* Quick Actions */
.modern-quick-actions {
    padding: 0.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.modern-quick-action {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.7rem 0.85rem;
    border-radius: 10px;
    color: #374151;
    text-decoration: none;
    font-size: 0.88rem;
    font-weight: 500;
    transition: all 0.15s;
    border: none;
    background: none;
    width: 100%;
    cursor: pointer;
    text-align: left;
}

.modern-quick-action i { color: #6b7280; width: 18px; text-align: center; }

.modern-quick-action:hover {
    background: #f3f4f6;
    color: #1a1a2e;
}

.modern-quick-action:hover i { color: #4361ee; }

.modern-quick-action-danger { color: #dc2626; }
.modern-quick-action-danger i { color: #f87171; }
.modern-quick-action-danger:hover { background: #fef2f2; color: #b91c1c; }
.modern-quick-action-danger:hover i { color: #dc2626; }

/* Timestamps */
.modern-timestamps { padding: 0.85rem 1.25rem; }

.modern-timestamp {
    display: flex;
    justify-content: space-between;
    padding: 0.45rem 0;
}

.modern-timestamp + .modern-timestamp { border-top: 1px solid #f3f4f6; }

.modern-timestamp-label { color: #9ca3af; font-size: 0.82rem; }
.modern-timestamp-value { color: #374151; font-size: 0.82rem; font-weight: 500; }

/* Modern Button */
.btn-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.35rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.25s;
}

.btn-modern-primary {
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    color: #fff;
    box-shadow: 0 2px 8px rgba(67, 97, 238, 0.3);
}

.btn-modern-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(67, 97, 238, 0.4);
    color: #fff;
}

.btn-modern-outline {
    background: transparent;
    color: #6b7280;
    border: 1.5px solid #e5e7eb;
}

.btn-modern-outline:hover {
    border-color: #4361ee;
    color: #4361ee;
    background: #f8f9ff;
}

/* Responsive */
@media (max-width: 992px) {
    .modern-detail-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .modern-page-header { flex-direction: column; align-items: stretch; }
    .modern-page-title { font-size: 1.35rem; }
    .modern-detail-hero { padding: 1.25rem; flex-direction: column; text-align: center; }
    .modern-detail-hero-badges { justify-content: center; }
    .modern-detail-row { flex-direction: column; gap: 0.25rem; padding: 0.75rem 1.25rem; }
    .modern-detail-label { width: auto; }
    .modern-detail-section-header { padding: 0.75rem 1.25rem; }
}
</style>
@endpush
@endsection
