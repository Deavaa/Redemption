@extends('layouts.admin')
@section('title', 'Email Inbox Settings')
@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.email-inbox.index') }}">Email Inbox</a></li>
                <li class="active">Settings</li>
            </ol></nav>
            <h1 class="modern-page-title">Email Inbox Settings</h1>
            <p class="modern-page-subtitle">Configure Gmail IMAP to display incoming emails for branch managers</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.email-inbox.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i> Back to Inbox
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="modern-alert modern-alert-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Setup Guide --}}
    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div class="modern-form-section">
            <div class="modern-form-section-header">
                <div class="modern-form-section-icon" style="background:#eef2ff;color:#4361ee;">
                    <i class="fas fa-book"></i>
                </div>
                <div>
                    <h3 class="modern-form-section-title">Gmail IMAP Setup Guide</h3>
                    <p class="modern-form-section-desc">How to link your Gmail account to this website</p>
                </div>
            </div>
            <div class="modern-form-section-body">
                <div style="background:#f8fafc;border-radius:12px;padding:1.25rem;border:1px solid #e5e7eb;">
                    <h4 style="margin:0 0 0.75rem;color:#1a1a2e;font-size:0.95rem;">Step-by-Step Gmail IMAP Configuration</h4>
                    <ol style="margin:0;padding-left:1.5rem;color:#374151;font-size:0.88rem;line-height:2;">
                        <li>Log in to your Gmail account</li>
                        <li>Go to <strong>Settings</strong> → <strong>See all settings</strong></li>
                        <li>Click the <strong>Forwarding and POP/IMAP</strong> tab</li>
                        <li>Under "IMAP access", select <strong>Enable IMAP</strong></li>
                        <li>Click <strong>Save Changes</strong></li>
                        <li>Go to <a href="https://myaccount.google.com/security" target="_blank" style="color:#4361ee;">Google Account Security</a></li>
                        <li>Enable <strong>2-Step Verification</strong> if not already enabled</li>
                        <li>Go to <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color:#4361ee;">App Passwords</a></li>
                        <li>Create an App Password named "School ERP Email"</li>
                        <li>Use the generated 16-character password below</li>
                    </ol>
                    <div style="margin-top:0.75rem;padding:0.75rem;background:#fefce8;border-radius:8px;font-size:0.85rem;color:#92400e;">
                        <i class="fas fa-info-circle"></i> <strong>Note:</strong> IMAP must be enabled in Gmail settings AND you must use an App Password (not your regular Gmail password) for authentication.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add New Inbox --}}
    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div class="modern-form-section">
            <div class="modern-form-section-header">
                <div class="modern-form-section-icon modern-form-section-icon-green">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div>
                    <h3 class="modern-form-section-title">Add New Email Inbox</h3>
                    <p class="modern-form-section-desc">Link a Gmail account for a branch</p>
                </div>
            </div>
            <div class="modern-form-section-body">
                <form method="POST" action="{{ route('admin.email-inbox.settings.store') }}">
                    @csrf
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label">Branch</label>
                            <select name="branch_id" class="modern-input modern-select" style="padding-left:0.75rem">
                                <option value="">All Branches (Headquarters)</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Email Address</label>
                            <input type="email" name="email_address" class="modern-input" style="padding-left:0.75rem" placeholder="branch@gmail.com" required>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">IMAP Host</label>
                            <input type="text" name="imap_host" class="modern-input" style="padding-left:0.75rem" value="imap.gmail.com" required>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">IMAP Port</label>
                            <input type="number" name="imap_port" class="modern-input" style="padding-left:0.75rem" value="993" required>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">IMAP Username (usually same as email)</label>
                            <input type="text" name="imap_username" class="modern-input" style="padding-left:0.75rem" placeholder="branch@gmail.com" required>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">IMAP Password (Gmail App Password)</label>
                            <input type="password" name="imap_password" class="modern-input" style="padding-left:0.75rem" placeholder="xxxx-xxxx-xxxx-xxxx" required>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Encryption</label>
                            <select name="imap_encryption" class="modern-input modern-select" style="padding-left:0.75rem">
                                <option value="ssl" selected>SSL (recommended for Gmail)</option>
                                <option value="tls">TLS</option>
                                <option value="none">None</option>
                            </select>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Folder</label>
                            <input type="text" name="folder" class="modern-input" style="padding-left:0.75rem" value="INBOX">
                        </div>
                    </div>
                    <div style="margin-top:1rem;">
                        <button type="submit" class="btn-modern btn-modern-primary">
                            <i class="fas fa-save"></i> Save Inbox Configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Existing Inboxes --}}
    <div class="modern-card">
        <div class="modern-form-section">
            <div class="modern-form-section-header">
                <div class="modern-form-section-icon modern-form-section-icon-blue">
                    <i class="fas fa-inbox"></i>
                </div>
                <div>
                    <h3 class="modern-form-section-title">Configured Email Inboxes</h3>
                    <p class="modern-form-section-desc">{{ count($inboxSettings) }} inbox(es) configured</p>
                </div>
            </div>
            <div style="padding:0 2rem 1.75rem;">
                @if($inboxSettings->count() > 0)
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Branch</th>
                            <th>Email</th>
                            <th>Host</th>
                            <th>Last Synced</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inboxSettings as $inbox)
                        <tr>
                            <td>{{ $inbox->branch?->name ?? 'All Branches' }}</td>
                            <td>{{ $inbox->email_address }}</td>
                            <td>{{ $inbox->imap_host }}:{{ $inbox->imap_port }}</td>
                            <td>{{ $inbox->last_synced_at?->format('M d, H:i') ?? 'Never' }}</td>
                            <td>
                                <span class="modern-badge {{ $inbox->is_active ? 'modern-badge-success' : 'modern-badge-danger' }}">
                                    {{ $inbox->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <form method="POST" action="{{ route('admin.email-inbox.test', $inbox->id) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn-modern btn-modern-outline" style="padding:0.35rem 0.75rem;font-size:0.8rem;" title="Test Connection">
                                        <i class="fas fa-plug"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.email-inbox.sync', $inbox->id) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn-modern btn-modern-outline" style="padding:0.35rem 0.75rem;font-size:0.8rem;" title="Sync Now">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.email-inbox.settings.destroy', $inbox->id) }}" style="display:inline" onsubmit="return confirm('Delete this inbox configuration?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-modern btn-modern-danger" style="padding:0.35rem 0.75rem;font-size:0.8rem;" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="modern-empty-state">
                    <p>No email inboxes configured yet. Add one above.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
