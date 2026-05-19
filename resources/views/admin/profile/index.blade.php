@extends('layouts.admin')

@section('title', 'Profile & Password')

@push('styles')
<style>
.profile-page { max-width: 700px; margin: 0 auto; animation: profileFadeIn 0.4s ease-out; }
@keyframes profileFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

.profile-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.profile-card-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    background: #fafbfc;
}
.profile-card-icon {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0;
}
.profile-card-icon.icon-user { background: #eef2ff; color: #4361ee; }
.profile-card-icon.icon-lock { background: #fef3c7; color: #d97706; }
.profile-card-title { font-size: 1.1rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.profile-card-desc { font-size: 0.82rem; color: #9ca3af; margin: 0.1rem 0 0; }
.profile-card-body { padding: 1.5rem; }

.profile-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
}
.profile-info-item label {
    display: block; font-size: 0.78rem; font-weight: 600;
    color: #9ca3af; text-transform: uppercase; letter-spacing: 0.3px;
    margin-bottom: 0.3rem;
}
.profile-info-value {
    font-size: 0.95rem; font-weight: 600; color: #1a1a2e;
}

.password-form .form-group { margin-bottom: 1.25rem; }
.password-form label {
    display: block; font-weight: 600; color: #374151;
    margin-bottom: 0.4rem; font-size: 0.88rem;
}
.password-form input[type="password"] {
    width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px;
    padding: 0.65rem 1rem; font-size: 0.9rem; color: #1a1a2e;
    background: #fff; transition: all 0.2s;
}
.password-form input[type="password"]:focus {
    outline: none; border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67,97,238,0.1);
}
.password-form .invalid-feedback { font-size: 0.8rem; color: #ef4444; margin-top: 0.3rem; }

.btn-change-password {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.6rem 1.5rem; border-radius: 10px; font-size: 0.9rem;
    font-weight: 600; border: none; cursor: pointer;
    background: linear-gradient(135deg, #4361ee, #6366f1); color: #fff;
    transition: all 0.2s; box-shadow: 0 2px 8px rgba(67,97,238,0.25);
}
.btn-change-password:hover {
    background: linear-gradient(135deg, #3b52e0, #5b5ee8);
    box-shadow: 0 4px 12px rgba(67,97,238,0.35);
    transform: translateY(-1px);
}

@media (max-width: 576px) {
    .profile-info-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="profile-page">
    {{-- Page Header --}}
    <div style="margin-bottom: 1.5rem;">
        <nav aria-label="breadcrumb" style="margin-bottom: 0.5rem;">
            <ol style="display: flex; list-style: none; padding: 0; margin: 0; gap: 0.5rem; font-size: 0.8rem; align-items: center;">
                <li><a href="{{ route('admin.dashboard') }}" style="color: #6c757d; text-decoration: none;"><i class="fas fa-home"></i></a></li>
                <li style="color: #4361ee; font-weight: 500;">Profile & Password</li>
            </ol>
        </nav>
        <h1 style="font-size: 1.75rem; font-weight: 800; color: #1a1a2e; margin: 0;">Profile & Password</h1>
        <p style="font-size: 0.9rem; color: #6c757d; margin: 0.25rem 0 0;">View your account details and change your password</p>
    </div>

    {{-- Account Information Card --}}
    <div class="profile-card">
        <div class="profile-card-header">
            <div class="profile-card-icon icon-user"><i class="fas fa-user"></i></div>
            <div>
                <h3 class="profile-card-title">Account Information</h3>
                <p class="profile-card-desc">Your current account details</p>
            </div>
        </div>
        <div class="profile-card-body">
            <div class="profile-info-grid">
                <div class="profile-info-item">
                    <label>Name</label>
                    <div class="profile-info-value">{{ $user->name }}</div>
                </div>
                <div class="profile-info-item">
                    <label>Email</label>
                    <div class="profile-info-value">{{ $user->email }}</div>
                </div>
                <div class="profile-info-item">
                    <label>Role</label>
                    <div class="profile-info-value" style="text-transform: capitalize;">{{ $user->getDisplayRoleAttribute() ?? $user->role }}</div>
                </div>
                <div class="profile-info-item">
                    <label>Status</label>
                    <div class="profile-info-value">
                        @if($user->is_active)
                            <span style="color: #059669;"><i class="fas fa-check-circle"></i> Active</span>
                        @else
                            <span style="color: #dc2626;"><i class="fas fa-times-circle"></i> Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Change Password Card --}}
    <div class="profile-card">
        <div class="profile-card-header">
            <div class="profile-card-icon icon-lock"><i class="fas fa-lock"></i></div>
            <div>
                <h3 class="profile-card-title">Change Password</h3>
                <p class="profile-card-desc">Update your password to keep your account secure</p>
            </div>
        </div>
        <div class="profile-card-body">
            <form class="password-form" method="POST" action="{{ route('admin.profile.password') }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required autocomplete="current-password" placeholder="Enter your current password">
                    @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password" placeholder="Enter new password (min 4 characters)">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm your new password">
                    @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-change-password">
                    <i class="fas fa-key"></i> Change Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
