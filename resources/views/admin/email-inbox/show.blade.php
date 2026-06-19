@extends('layouts.admin')
@section('title', 'Email: ' . ($email_message->subject ?: '(No Subject)'))
@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.email-inbox.index') }}">Email Inbox</a></li>
                <li class="active">{{ Str::limit($email_message->subject, 40) }}</li>
            </ol></nav>
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

    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div style="padding:1.5rem 2rem;">
            <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;">
                <h2 style="margin:0;font-size:1.2rem;color:#1a1a2e;">{{ $email_message->subject ?: '(No Subject)' }}</h2>
                <span class="modern-badge modern-badge-{{ match($email_message->category) {
                    'admission' => 'info',
                    'fee' => 'success',
                    'complaint' => 'danger',
                    'academic' => 'purple',
                    'hr' => 'cyan',
                    default => 'light'
                } }}">{{ $email_message->category_label }}</span>
            </div>

            <div style="display:flex;gap:2rem;flex-wrap:wrap;font-size:0.88rem;color:#6b7280;">
                <div><strong>From:</strong> {{ $email_message->from_name }} &lt;{{ $email_message->from_email }}&gt;</div>
                <div><strong>To:</strong> {{ $email_message->to_email }}</div>
                <div><strong>Date:</strong> {{ $email_message->received_at?->format('F j, Y \a\t g:i A') }}</div>
                <div><strong>Branch:</strong> {{ $email_message->inboxSetting?->branch?->name ?? 'All' }}</div>
            </div>
        </div>
    </div>

    {{-- Email Body --}}
    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div style="padding:1.5rem 2rem;">
            @if($email_message->body_html)
            {{-- SECURITY: Render untrusted email HTML inside a sandboxed iframe so any
                embedded <script> or event-handler attributes cannot reach the admin
                session. srcdoc + sandbox="" (no allow-scripts) blocks all JS. --}}
            <iframe srcdoc="{{ htmlspecialchars($email_message->body_html, ENT_QUOTES|ENT_HTML5, 'UTF-8') }}"
                    sandbox=""
                    style="border:1px solid #e5e7eb;border-radius:8px;width:100%;min-height:400px;background:#fff;"
                    loading="lazy"
                    referrerpolicy="no-referrer"></iframe>
            @else
            <div style="white-space:pre-wrap;font-size:0.9rem;color:#374151;line-height:1.7;">
                {{ $email_message->body_text ?: 'No content available.' }}
            </div>
            @endif
        </div>
    </div>

    {{-- Actions --}}
    <div class="modern-card">
        <div style="padding:1.5rem 2rem;">
            <div class="modern-form-grid" style="grid-template-columns:1fr 1fr;">
                <div class="modern-form-group">
                    <label class="modern-form-label">Change Category</label>
                    <form method="POST" action="{{ route('admin.email-inbox.update-category', $email_message->id) }}" style="display:flex;gap:0.5rem;">
                        @csrf
                        <select name="category" class="modern-input modern-select" style="padding-left:0.75rem;flex:1;">
                            @foreach(\App\Models\EmailMessage::categoryOptions() as $key => $label)
                            <option value="{{ $key }}" {{ $email_message->category === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-modern btn-modern-primary" style="padding:0.5rem 1rem;">
                            <i class="fas fa-save"></i>
                        </button>
                    </form>
                </div>
                <div class="modern-form-group">
                    <label class="modern-form-label">Assign To</label>
                    <form method="POST" action="{{ route('admin.email-inbox.assign', $email_message->id) }}" style="display:flex;gap:0.5rem;">
                        @csrf
                        <input type="text" name="notes" class="modern-input" style="padding-left:0.75rem;flex:1;" placeholder="Add notes..." value="{{ $email_message->notes }}">
                        <button type="submit" class="btn-modern btn-modern-secondary" style="padding:0.5rem 1rem;">
                            <i class="fas fa-user-plus"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
