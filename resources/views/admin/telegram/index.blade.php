@extends('layouts.admin')
@section('title', 'Telegram Settings')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li class="active">Telegram Settings</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Telegram Settings</h1>
            <p class="modern-page-subtitle">Configure your Telegram bot integration</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.telegram.send') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-paper-plane"></i> Send Message
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="modern-card">
                <form method="POST" action="{{ route('admin.telegram.update-settings') }}">
                    @csrf @method('PUT')
                    <div class="modern-form-section">
                        <div class="modern-form-section-header">
                            <div class="modern-form-section-icon modern-form-section-icon-blue">
                                <i class="fab fa-telegram"></i>
                            </div>
                            <div>
                                <h3 class="modern-form-section-title">Bot Configuration</h3>
                                <p class="modern-form-section-desc">Enter your Telegram bot credentials</p>
                            </div>
                        </div>
                        <div class="modern-form-section-body">
                            <div class="modern-form-grid">
                                <div class="modern-form-group modern-form-span-2">
                                    <label class="modern-form-label">Bot Token</label>
                                    <div class="modern-input-wrapper">
                                        <i class="fas fa-key modern-input-icon"></i>
                                        <input type="text" name="bot_token" class="modern-input" value="{{ old('bot_token', $settings->bot_token) }}" placeholder="123456:ABC-DEF...">
                                    </div>
                                    <small class="text-muted">Get this from @BotFather on Telegram</small>
                                </div>
                                <div class="modern-form-group modern-form-span-2">
                                    <label class="modern-form-label">Default Chat ID</label>
                                    <div class="modern-input-wrapper">
                                        <i class="fas fa-hashtag modern-input-icon"></i>
                                        <input type="text" name="chat_id" class="modern-input" value="{{ old('chat_id', $settings->chat_id) }}" placeholder="-1001234567890">
                                    </div>
                                </div>
                                <div class="modern-form-group modern-form-span-2">
                                    <label class="modern-form-label">Webhook URL</label>
                                    <div class="modern-input-wrapper">
                                        <i class="fas fa-link modern-input-icon"></i>
                                        <input type="url" name="webhook_url" class="modern-input" value="{{ old('webhook_url', $settings->webhook_url) }}" placeholder="https://yourdomain.com/api/telegram/webhook">
                                    </div>
                                </div>
                                <div class="modern-form-group modern-form-span-2">
                                    <label class="modern-form-label">Welcome Message</label>
                                    <textarea name="welcome_message" class="modern-input" rows="3" style="padding-left:2.5rem" placeholder="Welcome to School of Redemption!">{{ old('welcome_message', $settings->welcome_message) }}</textarea>
                                </div>
                                <div class="modern-form-group">
                                    <div class="modern-toggle-wrapper" style="padding-top:0">
                                        <label class="modern-toggle">
                                            <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', $settings->is_enabled) ? 'checked' : '' }}>
                                            <span class="modern-toggle-slider"></span>
                                        </label>
                                        <div class="modern-toggle-info">
                                            <span class="modern-toggle-title">Enable Telegram</span>
                                            <span class="modern-toggle-desc">Activate the bot integration</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="modern-form-group">
                                    <button type="button" class="btn-modern btn-modern-outline" id="testConnectionBtn" onclick="testConnection()">
                                        <i class="fas fa-plug"></i> Test Connection
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modern-form-actions">
                        <button type="submit" class="btn-modern btn-modern-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="modern-card">
                <div class="modern-form-section">
                    <div class="modern-form-section-header">
                        <div class="modern-form-section-icon modern-form-section-icon-green">
                            <i class="fas fa-history"></i>
                        </div>
                        <div>
                            <h3 class="modern-form-section-title">Recent Messages</h3>
                            <p class="modern-form-section-desc">Latest incoming and outgoing messages</p>
                        </div>
                    </div>
                </div>
                <div style="max-height:500px;overflow-y:auto">
                    <table class="table">
                        <thead><tr><th>Direction</th><th>Chat ID</th><th>Message</th><th>Status</th><th>Time</th></tr></thead>
                        <tbody>
                            @forelse($messages as $msg)
                            <tr>
                                <td><span class="badge {{ $msg->direction === 'incoming' ? 'bg-info' : 'bg-primary' }}">{{ $msg->direction }}</span></td>
                                <td class="small">{{ $msg->chat_id }}</td>
                                <td>{{ Str::limit($msg->message, 50) }}</td>
                                <td><span class="badge {{ $msg->status === 'sent' ? 'bg-success' : ($msg->status === 'failed' ? 'bg-danger' : 'bg-warning') }}">{{ $msg->status }}</span></td>
                                <td class="small">{{ $msg->created_at->format('M d, H:i') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No messages yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function testConnection() {
    const btn = document.getElementById('testConnectionBtn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
    btn.disabled = true;
    fetch('{{ route('admin.telegram.test') }}')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Connection successful! Bot: @' + data.bot_name);
            } else {
                alert('Connection failed: ' + data.message);
            }
        })
        .catch(e => alert('Error: ' + e.message))
        .finally(() => {
            btn.innerHTML = '<i class="fas fa-plug"></i> Test Connection';
            btn.disabled = false;
        });
}
</script>
@endpush
@endsection
