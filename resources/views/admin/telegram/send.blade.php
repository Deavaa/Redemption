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
            <p class="modern-page-subtitle">Send a message via your Telegram bot — to a specific chat ID or branch group</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.telegram.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i> Settings
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="modern-alert modern-alert-success" style="display:flex;align-items:center;gap:.65rem;padding:.85rem 1.25rem;margin-bottom:1.25rem;border-radius:10px;font-size:.88rem;font-weight:500;background:#ecfdf5;color:#059669;border:1px solid #a7f3d0">
        <i class="fas fa-check-circle"></i><span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="modern-alert modern-alert-danger" style="display:flex;align-items:center;gap:.65rem;padding:.85rem 1.25rem;margin-bottom:1.25rem;border-radius:10px;font-size:.88rem;font-weight:500;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5">
        <i class="fas fa-exclamation-circle"></i><span>{{ session('error') }}</span>
    </div>
    @endif

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
                                {{-- Send Method Toggle --}}
                                <div class="modern-form-group modern-form-span-2">
                                    <label class="modern-form-label">Send To</label>
                                    <div style="display:flex;gap:1rem;flex-wrap:wrap">
                                        <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-weight:600;color:#374151;font-size:.88rem">
                                            <input type="radio" name="send_method" value="chat_id" id="methodChatId" checked style="accent-color:#4361ee;width:16px;height:16px">
                                            Chat ID (Direct)
                                        </label>
                                        <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-weight:600;color:#374151;font-size:.88rem">
                                            <input type="radio" name="send_method" value="branch" id="methodBranch" style="accent-color:#4361ee;width:16px;height:16px">
                                            Branch Group
                                        </label>
                                        <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-weight:600;color:#374151;font-size:.88rem">
                                            <input type="radio" name="send_method" value="all_branches" id="methodAllBranches" style="accent-color:#4361ee;width:16px;height:16px">
                                            All Branches
                                        </label>
                                    </div>
                                </div>

                                {{-- Chat ID input --}}
                                <div class="modern-form-group modern-form-span-2" id="chatIdGroup">
                                    <label class="modern-form-label">Chat ID <span class="modern-required">*</span></label>
                                    <div class="modern-input-wrapper">
                                        <i class="fas fa-hashtag modern-input-icon"></i>
                                        <input type="text" name="chat_id" class="modern-input" value="{{ old('chat_id', $settings->chat_id ?? '') }}" placeholder="-1001234567890 or @username">
                                    </div>
                                </div>

                                {{-- Branch selector --}}
                                <div class="modern-form-group modern-form-span-2" id="branchGroup" style="display:none">
                                    <label class="modern-form-label">Select Branch <span class="modern-required">*</span></label>
                                    <div class="modern-input-wrapper">
                                        <i class="fas fa-map-marker-alt modern-input-icon"></i>
                                        <select name="branch_id" class="modern-input" style="padding-left:2.5rem">
                                            <option value="">-- Select Branch --</option>
                                            @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Message --}}
                                <div class="modern-form-group modern-form-span-2">
                                    <label class="modern-form-label">Message <span class="modern-required">*</span></label>
                                    <textarea name="message" class="modern-input" rows="6" style="padding-left:2.5rem" placeholder="Type your message here... Supports HTML formatting." required>{{ old('message') }}</textarea>
                                    <small style="color:#9ca3af;font-size:.75rem">Supports HTML: &lt;b&gt;, &lt;i&gt;, &lt;a&gt;, &lt;code&gt;</small>
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
            {{-- Branch Status --}}
            <div class="modern-card" style="margin-bottom:1rem">
                <div class="modern-form-section">
                    <div class="modern-form-section-header">
                        <div class="modern-form-section-icon modern-form-section-icon-purple">
                            <i class="fas fa-sitemap"></i>
                        </div>
                        <div>
                            <h3 class="modern-form-section-title">Branch Telegram Status</h3>
                            <p class="modern-form-section-desc">Branches with Telegram configured</p>
                        </div>
                    </div>
                </div>
                <div style="max-height:200px;overflow-y:auto">
                    @foreach($branches as $branch)
                    @php $bs = \App\Models\BranchTelegramSetting::getForBranch($branch->id) @endphp
                    <div class="d-flex align-items-center gap-2 p-3 border-bottom">
                        <i class="fas fa-map-marker-alt text-muted"></i>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">{{ $branch->name }}</div>
                            <div class="text-muted" style="font-size:.72rem">{{ $bs && $bs->chat_id ? $bs->chat_id : 'Not configured' }}</div>
                        </div>
                        @if($bs && $bs->is_enabled)
                        <span style="font-size:.7rem;padding:.15rem .45rem;border-radius:4px;background:#ecfdf5;color:#059669;font-weight:600">Active</span>
                        @else
                        <span style="font-size:.7rem;padding:.15rem .45rem;border-radius:4px;background:#fef2f2;color:#dc2626;font-weight:600">Inactive</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Recent Chats --}}
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
                <div style="max-height:300px;overflow-y:auto">
                    @forelse($recentChats as $chat)
                        <a href="#" class="d-flex align-items-center gap-2 p-3 border-bottom text-decoration-none text-dark" onclick="document.querySelector('[name=chat_id]').value='{{ $chat->chat_id }}';document.getElementById('methodChatId').checked=true;toggleSendMethod();return false;">
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

@push('scripts')
<script>
function toggleSendMethod() {
    var method = document.querySelector('input[name="send_method"]:checked').value;
    var chatGroup = document.getElementById('chatIdGroup');
    var branchGroup = document.getElementById('branchGroup');

    if (method === 'chat_id') {
        chatGroup.style.display = '';
        branchGroup.style.display = 'none';
    } else if (method === 'branch') {
        chatGroup.style.display = 'none';
        branchGroup.style.display = '';
    } else {
        chatGroup.style.display = 'none';
        branchGroup.style.display = 'none';
    }
}

document.querySelectorAll('input[name="send_method"]').forEach(function(radio) {
    radio.addEventListener('change', toggleSendMethod);
});
</script>
@endpush
