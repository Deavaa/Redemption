@extends('layouts.admin')
@section('title', 'Send Telegram Message')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.telegram.index') }}">Telegram</a></li>
                    <li class="active">Send Message</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Send Telegram Message</h1>
            <p class="modern-page-subtitle">Send a message via your Telegram bot</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.telegram.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i> Settings
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="modern-card">
                <form method="POST" action="{{ route('admin.telegram.send-message') }}">
                    @csrf
                    <div class="modern-form-section">
                        <div class="modern-form-section-header">
                            <div class="modern-form-section-icon modern-form-section-icon-blue">
                                <i class="fab fa-telegram"></i>
                            </div>
                            <div>
                                <h3 class="modern-form-section-title">Compose Message</h3>
                                <p class="modern-form-section-desc">Write your message and select recipient</p>
                            </div>
                        </div>
                        <div class="modern-form-section-body">
                            <div class="modern-form-grid">
                                <div class="modern-form-group modern-form-span-2">
                                    <label class="modern-form-label">Chat ID <span class="modern-required">*</span></label>
                                    <div class="modern-input-wrapper">
                                        <i class="fas fa-hashtag modern-input-icon"></i>
                                        <input type="text" name="chat_id" class="modern-input {{ $errors->has('chat_id') ? 'is-invalid' : '' }}" value="{{ old('chat_id', $settings->chat_id ?? '') }}" placeholder="-1001234567890 or @username" required>
                                    </div>
                                    @error('chat_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                                </div>
                                <div class="modern-form-group modern-form-span-2">
                                    <label class="modern-form-label">Message <span class="modern-required">*</span></label>
                                    <textarea name="message" class="modern-input" rows="6" style="padding-left:2.5rem" placeholder="Type your message here... Supports HTML formatting." required>{{ old('message') }}</textarea>
                                    <small class="text-muted">Supports HTML: &lt;b&gt;, &lt;i&gt;, &lt;a&gt;, &lt;code&gt;</small>
                                    @error('message')<span class="modern-form-error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modern-form-actions">
                        <a href="{{ route('admin.telegram.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
                        <button type="submit" class="btn-modern btn-modern-primary">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="modern-card">
                <div class="modern-form-section">
                    <div class="modern-form-section-header">
                        <div class="modern-form-section-icon modern-form-section-icon-green">
                            <i class="fas fa-address-book"></i>
                        </div>
                        <div>
                            <h3 class="modern-form-section-title">Recent Chats</h3>
                            <p class="modern-form-section-desc">Click to auto-fill chat ID</p>
                        </div>
                    </div>
                </div>
                <div style="max-height:400px;overflow-y:auto">
                    @forelse($recentChats as $chat)
                        <a href="#" class="d-flex align-items-center gap-2 p-3 border-bottom text-decoration-none text-dark" onclick="document.querySelector('[name=chat_id]').value='{{ $chat->chat_id }}';return false;">
                            <i class="fab fa-telegram text-primary"></i>
                            <div>
                                <div class="fw-semibold small">{{ $chat->from_name ?? 'Unknown' }}</div>
                                <div class="text-muted" style="font-size:0.75rem">{{ $chat->chat_id }}</div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fs-3"></i>
                            <p class="mt-2">No recent chats</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
