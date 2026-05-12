@extends('layouts.admin')
@section('title', 'Add Team Member')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.team-members.index') }}">Team Members</a></li>
                    <li class="active">Add New</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Add New Team Member</h1>
            <p class="modern-page-subtitle">Create a new team member profile for the website</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.team-members.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="modern-card">
        <form method="POST" action="{{ route('admin.team-members.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- Personal Information --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Personal Information</h3>
                        <p class="modern-form-section-desc">Enter the team member's name and professional details</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="name">
                                Full Name <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-user modern-input-icon"></i>
                                <input type="text"
                                    name="name"
                                    id="name"
                                    class="modern-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                    value="{{ old('name') }}"
                                    placeholder="e.g. John Doe"
                                    required
                                    autofocus>
                            </div>
                            @error('name')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="designation">
                                Designation <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-briefcase modern-input-icon"></i>
                                <input type="text"
                                    name="designation"
                                    id="designation"
                                    class="modern-input {{ $errors->has('designation') ? 'is-invalid' : '' }}"
                                    value="{{ old('designation') }}"
                                    placeholder="e.g. Principal, Head of Department"
                                    required>
                            </div>
                            @error('designation')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="department">
                                Department <small>(optional)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-building modern-input-icon"></i>
                                <input type="text"
                                    name="department"
                                    id="department"
                                    class="modern-input"
                                    value="{{ old('department') }}"
                                    placeholder="e.g. Science, Administration">
                            </div>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="qualification">
                                Qualification <small>(optional)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-graduation-cap modern-input-icon"></i>
                                <input type="text"
                                    name="qualification"
                                    id="qualification"
                                    class="modern-input"
                                    value="{{ old('qualification') }}"
                                    placeholder="e.g. M.Ed, PhD">
                            </div>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="experience">
                                Experience <small>(optional)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-clock modern-input-icon"></i>
                                <input type="text"
                                    name="experience"
                                    id="experience"
                                    class="modern-input"
                                    value="{{ old('experience') }}"
                                    placeholder="e.g. 10 years">
                            </div>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="photo">
                                Photo <small>(optional - upload an image)</small>
                            </label>
                            <input type="file"
                                name="photo"
                                id="photo"
                                class="modern-input {{ $errors->has('photo') ? 'is-invalid' : '' }}"
                                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                            <small class="text-muted mt-1">Recommended: max 5MB (jpeg, png, gif, webp)</small>
                            @error('photo')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-green">
                        <i class="fas fa-address-book"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Contact Information</h3>
                        <p class="modern-form-section-desc">Optional contact details for the team member</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="email">
                                Email <small>(optional)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-envelope modern-input-icon"></i>
                                <input type="email"
                                    name="email"
                                    id="email"
                                    class="modern-input"
                                    value="{{ old('email') }}"
                                    placeholder="e.g. john@school.com">
                            </div>
                            @error('email')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="phone">
                                Phone <small>(optional)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-phone modern-input-icon"></i>
                                <input type="text"
                                    name="phone"
                                    id="phone"
                                    class="modern-input"
                                    value="{{ old('phone') }}"
                                    placeholder="e.g. +251 911 234 567">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bio & Settings --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-purple">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Bio & Display Settings</h3>
                        <p class="modern-form-section-desc">Add a bio and configure visibility on the website</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="bio">
                                Bio <small>(optional)</small>
                            </label>
                            <textarea name="bio"
                                id="bio"
                                class="modern-input"
                                rows="4"
                                placeholder="Brief biography or description..."
                                style="padding-left:2.5rem;resize:vertical;">{{ old('bio') }}</textarea>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="sort_order">
                                Sort Order <small>(optional)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-sort modern-input-icon"></i>
                                <input type="number"
                                    name="sort_order"
                                    id="sort_order"
                                    class="modern-input"
                                    value="{{ old('sort_order', 0) }}"
                                    min="0"
                                    placeholder="0">
                            </div>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label">Active Status</label>
                            <div style="display:flex;align-items:center;gap:0.75rem;margin-top:0.5rem;">
                                <label class="modern-toggle">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                                    <span class="modern-toggle-slider"></span>
                                </label>
                                <span style="font-size:0.85rem;color:#6b7280;">Show on website</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="modern-form-actions">
                <a href="{{ route('admin.team-members.index') }}" class="btn-modern btn-modern-ghost">
                    Cancel
                </a>
                <button type="submit" class="btn-modern btn-modern-primary">
                    <i class="fas fa-check"></i>
                    <span>Create Member</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection