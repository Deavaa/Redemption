@extends('layouts.admin')
@section('title', 'Team Member Details')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.team-members.index') }}">Team Members</a></li>
                    <li class="active">Details</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Team Member Details</h1>
            <p class="modern-page-subtitle">Viewing profile for <strong>{{ $item->name }}</strong></p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.team-members.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>
    </div>

    {{-- Details Card --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">Member Profile</h2>
                @if($item->is_active)
                    <span class="modern-badge modern-badge-success">Active</span>
                @else
                    <span class="modern-badge modern-badge-danger">Inactive</span>
                @endif
            </div>
            <div class="modern-card-header-right">
                <a href="{{ route('admin.team-members.edit', $item->id) }}" class="btn-modern btn-modern-primary" style="padding:0.5rem 1rem;font-size:0.85rem;">
                    <i class="fas fa-pen"></i>
                    <span>Edit</span>
                </a>
            </div>
        </div>
        <div class="modern-card-body" style="padding:0;">
            {{-- Photo Section --}}
            @if($item->photo)
            <div style="text-align:center;padding:2rem 1.5rem 0;">
                <img src="{{ asset($item->photo) }}" alt="{{ $item->name }}" style="width:120px;height:120px;border-radius:16px;object-fit:cover;border:4px solid #e5e7eb;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
            </div>
            @endif

            <div class="modern-detail-grid" style="margin-top:1.5rem;">
                <div class="modern-detail-item">
                    <div class="modern-detail-label">Full Name</div>
                    <div class="modern-detail-value">{{ $item->name ?? '-' }}</div>
                </div>
                <div class="modern-detail-item">
                    <div class="modern-detail-label">Designation</div>
                    <div class="modern-detail-value">{{ $item->designation ?? '-' }}</div>
                </div>
                <div class="modern-detail-item">
                    <div class="modern-detail-label">Department</div>
                    <div class="modern-detail-value">{{ $item->department ?? '-' }}</div>
                </div>
                <div class="modern-detail-item">
                    <div class="modern-detail-label">Qualification</div>
                    <div class="modern-detail-value">{{ $item->qualification ?? '-' }}</div>
                </div>
                <div class="modern-detail-item">
                    <div class="modern-detail-label">Experience</div>
                    <div class="modern-detail-value">{{ $item->experience ?? '-' }}</div>
                </div>
                <div class="modern-detail-item">
                    <div class="modern-detail-label">Sort Order</div>
                    <div class="modern-detail-value">{{ $item->sort_order ?? 0 }}</div>
                </div>
                <div class="modern-detail-item">
                    <div class="modern-detail-label">Email</div>
                    <div class="modern-detail-value">
                        @if($item->email)
                            <a href="mailto:{{ $item->email }}">{{ $item->email }}</a>
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div class="modern-detail-item">
                    <div class="modern-detail-label">Phone</div>
                    <div class="modern-detail-value">
                        @if($item->phone)
                            <a href="tel:{{ $item->phone }}">{{ $item->phone }}</a>
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div class="modern-detail-item modern-detail-full">
                    <div class="modern-detail-label">Bio</div>
                    <div class="modern-detail-value">{{ $item->bio ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection