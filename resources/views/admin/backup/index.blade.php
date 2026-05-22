@extends('layouts.admin')
@section('title', 'Scheduled Database Backup')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">{{ __('app.system') }}</a></li>
                    <li class="active">Scheduled Database Backup</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <form method="POST" action="{{ route('admin.backup.now') }}" style="display:inline">
                @csrf
                <input type="hidden" name="send_email" value="1">
                <button type="submit" class="btn-modern btn-modern-primary" onclick="return confirm('Create a database backup now and send via email?')">
                    <i class="fas fa-paper-plane"></i>
                    <span>Backup & Email Now</span>
                </button>
            </form>
            <form method="POST" action="{{ route('admin.backup.now') }}" style="display:inline">
                @csrf
                <input type="hidden" name="send_email" value="0">
                <button type="submit" class="btn-modern btn-modern-outline" onclick="return confirm('Create a database backup now (no email)?')">
                    <i class="fas fa-download"></i>
                    <span>Backup Now</span>
                </button>
            </form>
            <form method="POST" action="{{ route('admin.backup.test-email') }}" style="display:inline">
                @csrf
                <input type="hidden" name="email" value="{{ $scheduleSettings['backup_email'] }}">
                <button type="submit" class="btn-modern btn-modern-secondary" onclick="return confirm('Send a test email to {{ $scheduleSettings['backup_email'] }}?')">
                    <i class="fas fa-envelope"></i>
                    <span>Test Email</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue">
                <i class="fas fa-database"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ strtoupper($driver) }}</span>
                <span class="modern-stat-label">Database Driver</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green">
                <i class="fas fa-server"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $database }}</span>
                <span class="modern-stat-label">Database Name</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon {{ $scheduleSettings['backup_enabled'] === '1' ? 'modern-stat-icon-green' : 'modern-stat-icon-gold' }}">
                <i class="fas fa-clock"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $scheduleSettings['backup_enabled'] === '1' ? 'Enabled' : 'Disabled' }}</span>
                <span class="modern-stat-label">Auto Backup</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon {{ $mailConfigured ? 'modern-stat-icon-green' : 'modern-stat-icon-gold' }}">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $mailConfigured ? 'Configured' : 'Not Configured' }}</span>
                <span class="modern-stat-label">Email ({{ $mailMailer }})</span>
            </div>
        </div>
    </div>

    @if(!$mailConfigured)
    <div class="modern-alert modern-alert-danger" style="margin-bottom: 1.25rem;">
        <i class="fas fa-exclamation-triangle"></i>
        <span>
            <strong>Email not configured!</strong> The current mail driver is <code>{{ $mailMailer }}</code> which only writes to log files.
            To send backups via email, configure SMTP in your <code>.env</code> file:
            <code>MAIL_MAILER=smtp</code>, <code>MAIL_HOST</code>, <code>MAIL_USERNAME</code>, <code>MAIL_PASSWORD</code>.
            For Gmail, use an <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color:#dc2626;text-decoration:underline;">App Password</a>.
        </span>
    </div>
    @endif

    {{-- Mail Configuration Guide --}}
    <div class="modern-card" style="margin-bottom: 1.25rem;">
        <div class="modern-form-section">
            <div class="modern-form-section-header" style="cursor:pointer;" onclick="toggleMailGuide()">
                <div class="modern-form-section-icon" style="background:#fefce8;color:#d97706;">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <div style="flex:1;">
                    <h3 class="modern-form-section-title">Mail Configuration Guide</h3>
                    <p class="modern-form-section-desc">Step-by-step instructions to fix email delivery for backups</p>
                </div>
                <i class="fas fa-chevron-down" id="mailGuideChevron" style="color:#9ca3af;transition:transform 0.3s;"></i>
            </div>
            <div class="modern-form-section-body" id="mailGuideBody" style="display:none;">
                <div style="background:#f8fafc;border-radius:12px;padding:1.25rem;border:1px solid #e5e7eb;">
                    <h4 style="margin:0 0 0.75rem;color:#1a1a2e;font-size:0.95rem;"><i class="fas fa-shield-alt" style="color:#4361ee;margin-right:0.5rem;"></i>Gmail App Password Setup (Required for Backup Emails)</h4>
                    <ol style="margin:0;padding-left:1.5rem;color:#374151;font-size:0.88rem;line-height:1.8;">
                        <li>Go to <a href="https://myaccount.google.com/security" target="_blank" style="color:#4361ee;">Google Account Security</a></li>
                        <li>Enable <strong>2-Step Verification</strong> if not already enabled</li>
                        <li>Go to <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color:#4361ee;">App Passwords</a></li>
                        <li>Select "Mail" as the app and "Other" as the device, name it "School ERP"</li>
                        <li>Click <strong>Generate</strong> - Google will show a 16-character password</li>
                        <li>Copy that password and paste it in your <code>.env</code> file as <code>MAIL_PASSWORD</code></li>
                        <li>Set <code>MAIL_USERNAME</code> to your Gmail address</li>
                        <li>Set <code>MAIL_FROM_ADDRESS</code> to your Gmail address</li>
                        <li>Run: <code>php artisan config:clear</code> to refresh the configuration</li>
                        <li>Click <strong>"Test Email"</strong> above to verify the setup</li>
                    </ol>
                    <div style="margin-top:1rem;background:#1a1a2e;border-radius:8px;padding:0.85rem 1rem;font-family:monospace;font-size:0.82rem;color:#a5f3fc;">
                        <div style="color:#9ca3af;margin-bottom:0.35rem;"># Required .env settings for Gmail:</div>
                        <div>MAIL_MAILER=smtp</div>
                        <div>MAIL_HOST=smtp.gmail.com</div>
                        <div>MAIL_PORT=587</div>
                        <div>MAIL_USERNAME=your-email@gmail.com</div>
                        <div>MAIL_PASSWORD=xxxx-xxxx-xxxx-xxxx</div>
                        <div>MAIL_FROM_ADDRESS="your-email@gmail.com"</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Schedule Settings Card --}}
    <div class="modern-card" style="margin-bottom: 1.25rem;">
        <div class="modern-form-section">
            <div class="modern-form-section-header">
                <div class="modern-form-section-icon modern-form-section-icon-purple">
                    <i class="fas fa-cog"></i>
                </div>
                <div>
                    <h3 class="modern-form-section-title">Backup Schedule Settings</h3>
                    <p class="modern-form-section-desc">Configure automatic backup frequency, time, and email delivery</p>
                </div>
            </div>
            <div class="modern-form-section-body">
                <form method="POST" action="{{ route('admin.backup.schedule') }}">
                    @csrf
                    @method('PUT')
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label">Auto Backup</label>
                            <div class="modern-input-wrapper">
                                <select name="backup_enabled" class="modern-input modern-select">
                                    <option value="1" {{ $scheduleSettings['backup_enabled'] === '1' ? 'selected' : '' }}>Enabled</option>
                                    <option value="0" {{ $scheduleSettings['backup_enabled'] === '0' ? 'selected' : '' }}>Disabled</option>
                                </select>
                            </div>
                            <span class="modern-form-hint">Enable or disable automatic scheduled backups</span>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Frequency</label>
                            <div class="modern-input-wrapper">
                                <select name="backup_frequency" class="modern-input modern-select">
                                    <option value="daily" {{ $scheduleSettings['backup_frequency'] === 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ $scheduleSettings['backup_frequency'] === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ $scheduleSettings['backup_frequency'] === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                            </div>
                            <span class="modern-form-hint">How often the backup runs automatically</span>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Backup Time</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-clock modern-input-icon"></i>
                                <input type="time" name="backup_time" class="modern-input" value="{{ $scheduleSettings['backup_time'] }}" required>
                            </div>
                            <span class="modern-form-hint">Time to run the backup (Africa/Addis_Ababa timezone)</span>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Backup Email</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-envelope modern-input-icon"></i>
                                <input type="email" name="backup_email" class="modern-input" value="{{ $scheduleSettings['backup_email'] }}" required>
                            </div>
                            <span class="modern-form-hint">Email address to receive backup files</span>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Compress with Gzip</label>
                            <div class="modern-input-wrapper">
                                <select name="backup_compress" class="modern-input modern-select">
                                    <option value="1" {{ $scheduleSettings['backup_compress'] === '1' ? 'selected' : '' }}>Yes (recommended)</option>
                                    <option value="0" {{ $scheduleSettings['backup_compress'] === '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <span class="modern-form-hint">Compress backup files to save disk space</span>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Keep Count</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-layer-group modern-input-icon"></i>
                                <input type="number" name="backup_keep_count" class="modern-input" value="{{ $scheduleSettings['backup_keep_count'] }}" min="1" max="100" required>
                            </div>
                            <span class="modern-form-hint">Number of recent backups to keep (older are auto-deleted)</span>
                        </div>
                    </div>
                    <div style="margin-top: 1.25rem;">
                        <button type="submit" class="btn-modern btn-modern-primary">
                            <i class="fas fa-save"></i>
                            <span>Save Schedule Settings</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Existing Backups Card --}}
    <div class="modern-card">
        <div class="modern-form-section">
            <div class="modern-form-section-header">
                <div class="modern-form-section-icon modern-form-section-icon-blue">
                    <i class="fas fa-archive"></i>
                </div>
                <div>
                    <h3 class="modern-form-section-title">Existing Backups</h3>
                    <p class="modern-form-section-desc">{{ count($backups) }} backup file(s) in storage/app/backups/</p>
                </div>
            </div>
            <div class="modern-card-body" style="padding: 0 2rem 1.75rem;">
                @if(count($backups) > 0)
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Filename</th>
                            <th>Size</th>
                            <th>Date</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($backups as $index => $backup)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <code style="background:#f3f4f6;padding:2px 8px;border-radius:4px;font-size:12px;">
                                    {{ $backup['filename'] }}
                                </code>
                                @if(str_ends_with($backup['filename'], '.gz'))
                                <span class="modern-badge" style="background:#eef2ff;color:#4361ee;margin-left:4px;">compressed</span>
                                @endif
                            </td>
                            <td>{{ $backup['size_human'] }}</td>
                            <td>{{ $backup['date'] }}</td>
                            <td style="text-align:right;">
                                <a href="{{ route('admin.backup.download', $backup['filename']) }}" class="btn-modern btn-modern-outline" style="padding:0.35rem 0.75rem;font-size:0.8rem;" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.backup.delete', $backup['filename']) }}" style="display:inline" onsubmit="return confirm('Delete this backup file?')">
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
                <div style="text-align:center;padding:3rem 1rem;color:#9ca3af;">
                    <i class="fas fa-box-open" style="font-size:2.5rem;margin-bottom:0.75rem;display:block;opacity:0.3;"></i>
                    <p style="margin:0;font-size:0.95rem;">No backups yet.</p>
                    <p style="margin:0.25rem 0 0;font-size:0.82rem;">Click "Backup Now" above to create your first backup.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Cron Reminder --}}
    <div class="modern-card" style="margin-top:1.25rem;">
        <div class="modern-form-section">
            <div class="modern-form-section-header">
                <div class="modern-form-section-icon" style="background:#fefce8;color:#d97706;">
                    <i class="fas fa-terminal"></i>
                </div>
                <div>
                    <h3 class="modern-form-section-title">Cron Job Setup</h3>
                    <p class="modern-form-section-desc">Ensure this cron entry is configured on your server for scheduled backups to work</p>
                </div>
            </div>
            <div class="modern-form-section-body">
                <div style="background:#1a1a2e;border-radius:10px;padding:1rem 1.25rem;font-family:'Fira Code',monospace;font-size:0.85rem;color:#a5f3fc;overflow-x:auto;">
                    <div style="color:#9ca3af;margin-bottom:0.5rem;"># Add to your crontab (crontab -e):</div>
                    <div>* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</div>
                </div>
                <div style="margin-top:1rem;display:flex;gap:0.75rem;flex-wrap:wrap;">
                    <button type="button" class="btn-modern btn-modern-outline" style="font-size:0.85rem;" onclick="copyCron()">
                        <i class="fas fa-copy"></i> Copy Cron Command
                    </button>
                    <button type="button" class="btn-modern btn-modern-outline" style="font-size:0.85rem;" onclick="testBackup()">
                        <i class="fas fa-play"></i> Test Backup Command
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.modern-page { animation: fadeSlideIn 0.4s ease-out; }
@keyframes fadeSlideIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.modern-page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.modern-page-header-left { flex: 1; }
.modern-page-header-right { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.modern-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }
.modern-stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.75rem; }
.modern-stat-card { background: #fff; border-radius: 14px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04); border: 1px solid #f0f0f0; transition: transform 0.2s, box-shadow 0.2s; }
.modern-stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.modern-stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
.modern-stat-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-stat-icon-green { background: #ecfdf5; color: #10b981; }
.modern-stat-icon-gold { background: #fefce8; color: #d97706; }
.modern-stat-info { display: flex; flex-direction: column; }
.modern-stat-value { font-size: 1.25rem; font-weight: 800; color: #1a1a2e; line-height: 1.2; }
.modern-stat-label { font-size: 0.8rem; color: #6c757d; font-weight: 500; }
.modern-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04); border: 1px solid #f0f0f0; overflow: hidden; }
.modern-form-section-header { display: flex; align-items: center; gap: 1rem; padding: 1.5rem 2rem 0.75rem; }
.modern-form-section-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
.modern-form-section-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-form-section-icon-purple { background: #f5f3ff; color: #7c3aed; }
.modern-form-section-title { font-size: 1.05rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.modern-form-section-desc { font-size: 0.82rem; color: #9ca3af; margin: 0.15rem 0 0; }
.modern-form-section-body { padding: 1.25rem 2rem 1.75rem; }
.modern-form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; }
.modern-form-group { display: flex; flex-direction: column; }
.modern-form-label { font-weight: 600; color: #374151; margin-bottom: 0.45rem; font-size: 0.88rem; }
.modern-form-hint { display: block; color: #9ca3af; font-size: 0.78rem; margin-top: 0.3rem; }
.modern-input-wrapper { position: relative; }
.modern-input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 0.85rem; pointer-events: none; z-index: 1; }
.modern-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.7rem 0.9rem 0.7rem 2.5rem; font-size: 0.9rem; color: #1a1a2e; background: #fff; transition: all 0.2s; }
.modern-input:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1); }
.modern-select { appearance: none; cursor: pointer; }
.modern-alert { display: flex; align-items: center; gap: 0.65rem; padding: 0.85rem 1.25rem; margin-bottom: 1.25rem; border-radius: 10px; font-size: 0.88rem; font-weight: 500; animation: fadeSlideIn 0.3s ease; }
.modern-alert-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.modern-table { width: 100%; font-size: 13px; }
.modern-table th { text-align: left; padding: 10px 14px; color: #9ca3af; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #f0f0f0; background: #fafbfc; }
.modern-table td { padding: 10px 14px; border-bottom: 1px solid #f3f4f6; color: #374151; }
.modern-table tbody tr:hover td { background: #f8fafc; }
.modern-badge { display: inline-flex; align-items: center; padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.btn-modern { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.35rem; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border: none; cursor: pointer; transition: all 0.25s; }
.btn-modern-primary { background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff; box-shadow: 0 2px 8px rgba(67, 97, 238, 0.3); }
.btn-modern-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(67, 97, 238, 0.4); color: #fff; }
.btn-modern-outline { background: #fff; color: #4361ee; border: 1.5px solid #4361ee; }
.btn-modern-outline:hover { background: #eef2ff; }
.btn-modern-danger { background: #fff; color: #ef4444; border: 1.5px solid #fca5a5; }
.btn-modern-danger:hover { background: #fef2f2; border-color: #ef4444; }
@media (max-width: 768px) {
    .modern-page-header { flex-direction: column; align-items: stretch; }
    .modern-stats-row { grid-template-columns: 1fr; }
    .modern-form-grid { grid-template-columns: 1fr; }
    .modern-form-section-body { padding: 1rem 1.25rem 1.5rem; }
    .modern-form-section-header { padding: 1.25rem 1.25rem 0.75rem; }
    .modern-table { font-size: 12px; }
    .modern-table th, .modern-table td { padding: 8px 10px; }
}
</style>
@endpush

@push('scripts')
<script>
function copyCron() {
    const cron = '* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1';
    navigator.clipboard.writeText(cron).then(() => {
        const btn = event.target.closest('button');
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(() => btn.innerHTML = orig, 2000);
    });
}

function toggleMailGuide() {
    const body = document.getElementById('mailGuideBody');
    const chevron = document.getElementById('mailGuideChevron');
    if (body.style.display === 'none') {
        body.style.display = 'block';
        chevron.style.transform = 'rotate(180deg)';
    } else {
        body.style.display = 'none';
        chevron.style.transform = 'rotate(0deg)';
    }
}

function testBackup() {
    if (confirm('Run a test backup now? This will execute the artisan command.')) {
        // We'll just trigger the backup now endpoint
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.backup.now") }}';
        form.style.display = 'none';
        form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="send_email" value="0">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection
