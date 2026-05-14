@extends('layouts.admin')
@section('title', __('app.db_export_title'))

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">{{ __('app.system') }}</a></li>
                    <li class="active">{{ __('app.db_export_title') }}</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">{{ __('app.db_export_title') }}</h1>
            <p class="modern-page-subtitle">{{ __('app.db_export_subtitle') }}</p>
        </div>
        <div class="modern-page-header-right">
            {{-- Quick Export Button --}}
            <form method="POST" action="{{ route('admin.database-backup.quick-export') }}" style="display:inline">
                @csrf
                <button type="submit" class="btn-modern btn-modern-primary" onclick="return confirm('{{ __("app.db_export_confirm") }}')">
                    <i class="fas fa-paper-plane"></i>
                    <span>{{ __('app.db_export_quick_send') }}</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Success/Error Alerts --}}
    @if(session('success'))
    <div class="modern-alert modern-alert-success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="modern-alert modern-alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ session('error') }}</span>
        <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    {{-- Stats Cards --}}
    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue">
                <i class="fas fa-database"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ strtoupper($driver) }}</span>
                <span class="modern-stat-label">{{ __('app.db_export_driver') }}</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green">
                <i class="fas fa-table"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ count($tables) }}</span>
                <span class="modern-stat-label">{{ __('app.db_export_tables') }}</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-gold">
                <i class="fas fa-rows"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ number_format($totalRecords) }}</span>
                <span class="modern-stat-label">{{ __('app.db_export_total_records') }}</span>
            </div>
        </div>
        @if($lastBackup)
        <div class="modern-stat-card">
            <div class="modern-stat-icon" style="background:#fef2f2;color:#ef4444;border-radius:12px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">
                <i class="fas fa-clock"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ \Carbon\Carbon::parse($lastBackup)->diffForHumans() }}</span>
                <span class="modern-stat-label">{{ __('app.db_export_last_backup') }}</span>
            </div>
        </div>
        @endif
    </div>

    {{-- Export Form --}}
    <div class="modern-card" style="margin-bottom: 1.25rem;">
        <div class="modern-form-section">
            <div class="modern-form-section-header">
                <div class="modern-form-section-icon modern-form-section-icon-purple">
                    <i class="fas fa-envelope"></i>
                </div>
                <div>
                    <h3 class="modern-form-section-title">{{ __('app.db_export_send_via_email') }}</h3>
                    <p class="modern-form-section-desc">{{ __('app.db_export_email_desc') }}</p>
                </div>
            </div>
            <div class="modern-form-section-body">
                <form method="POST" action="{{ route('admin.database-backup.export-send') }}" id="exportForm">
                    @csrf
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label">{{ __('app.db_export_recipient_email') }}</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-envelope modern-input-icon"></i>
                                <input type="email" name="email" class="modern-input" value="dawitac@gmail.com" required>
                            </div>
                            <span class="modern-form-hint">{{ __('app.db_export_recipient_hint') }}</span>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">{{ __('app.db_export_format_label') }}</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-file-code modern-input-icon"></i>
                                <select name="format" class="modern-input modern-select" style="padding-left:2.5rem;">
                                    <option value="sql">SQL Dump (.sql)</option>
                                    <option value="csv">CSV Archive (.zip)</option>
                                </select>
                            </div>
                            <span class="modern-form-hint">{{ __('app.db_export_format_hint') }}</span>
                        </div>
                    </div>

                    {{-- Table Selection --}}
                    <div style="margin-top: 1.25rem;">
                        <label class="modern-form-label" style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" id="selectAllTables" checked style="accent-color: #4361ee;">
                            {{ __('app.db_export_select_tables') }}
                        </label>
                        <div class="table-select-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 6px; margin-top: 0.5rem; max-height: 280px; overflow-y: auto; padding: 12px; background: #f9fafb; border-radius: 10px; border: 1px solid #e5e7eb;">
                            @foreach($tables as $table)
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #374151; cursor: pointer; padding: 4px 0;">
                                <input type="checkbox" name="tables[]" value="{{ $table }}" checked style="accent-color: #4361ee;" class="table-checkbox">
                                <span>{{ $table }}</span>
                                <span style="margin-left: auto; font-size: 11px; color: #9ca3af;">{{ is_numeric($tableCounts[$table] ?? null) ? number_format($tableCounts[$table]) : '-' }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div style="margin-top: 1.25rem; display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        <button type="submit" class="btn-modern btn-modern-primary" onclick="return confirm('{{ __("app.db_export_confirm") }}')">
                            <i class="fas fa-paper-plane"></i>
                            <span>{{ __('app.db_export_send_button') }}</span>
                        </button>
                        <button type="button" class="btn-modern btn-modern-outline" onclick="downloadBackup()">
                            <i class="fas fa-download"></i>
                            <span>{{ __('app.db_export_download_button') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Tables Overview --}}
    <div class="modern-card">
        <div class="modern-form-section">
            <div class="modern-form-section-header">
                <div class="modern-form-section-icon modern-form-section-icon-blue">
                    <i class="fas fa-table"></i>
                </div>
                <div>
                    <h3 class="modern-form-section-title">{{ __('app.db_export_tables_overview') }}</h3>
                    <p class="modern-form-section-desc">{{ count($tables) }} {{ __('app.db_export_tables') }}</p>
                </div>
            </div>
            <div class="modern-card-body">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('app.db_export_table_name') }}</th>
                            <th>{{ __('app.db_export_record_count') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tables as $index => $table)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><code style="background:#f3f4f6;padding:2px 8px;border-radius:4px;font-size:12px;">{{ $table }}</code></td>
                            <td>
                                @if(is_numeric($tableCounts[$table] ?? null))
                                    {{ number_format($tableCounts[$table]) }}
                                @else
                                    <span class="modern-badge modern-badge-red">error</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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
.modern-page-title { font-size: 1.75rem; font-weight: 800; color: #1a1a2e; margin: 0; letter-spacing: -0.5px; }
.modern-page-subtitle { font-size: 0.9rem; color: #6c757d; margin: 0.25rem 0 0; }
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
.modern-stat-value { font-size: 1.5rem; font-weight: 800; color: #1a1a2e; line-height: 1.2; }
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
.modern-alert-success { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
.modern-alert-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.modern-alert-close { margin-left: auto; background: none; border: none; cursor: pointer; color: inherit; opacity: 0.6; transition: opacity 0.2s; }
.modern-alert-close:hover { opacity: 1; }
.modern-table { width: 100%; font-size: 13px; }
.modern-table th { text-align: left; padding: 10px 14px; color: #9ca3af; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #f0f0f0; background: #fafbfc; }
.modern-table td { padding: 10px 14px; border-bottom: 1px solid #f3f4f6; color: #374151; }
.modern-table tbody tr:hover td { background: #f8fafc; }
.modern-badge { display: inline-flex; align-items: center; padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.modern-badge-red { background: #fef2f2; color: #ef4444; }
.btn-modern { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.35rem; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border: none; cursor: pointer; transition: all 0.25s; }
.btn-modern-primary { background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff; box-shadow: 0 2px 8px rgba(67, 97, 238, 0.3); }
.btn-modern-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(67, 97, 238, 0.4); color: #fff; }
.btn-modern-outline { background: #fff; color: #4361ee; border: 1.5px solid #4361ee; }
.btn-modern-outline:hover { background: #eef2ff; }
@media (max-width: 768px) {
    .modern-page-header { flex-direction: column; align-items: stretch; }
    .modern-page-title { font-size: 1.35rem; }
    .modern-stats-row { grid-template-columns: 1fr; }
    .modern-form-grid { grid-template-columns: 1fr; }
    .modern-form-section-body { padding: 1rem 1.25rem 1.5rem; }
    .modern-form-section-header { padding: 1.25rem 1.25rem 0.75rem; }
}
</style>
@endpush

@push('scripts')
<script>
// Select/deselect all tables
document.getElementById('selectAllTables')?.addEventListener('change', function() {
    document.querySelectorAll('.table-checkbox').forEach(cb => {
        cb.checked = this.checked;
    });
});

document.querySelectorAll('.table-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
        const all = document.querySelectorAll('.table-checkbox');
        const checked = document.querySelectorAll('.table-checkbox:checked');
        document.getElementById('selectAllTables').checked = all.length === checked.length;
    });
});

// Download backup (use a separate form)
function downloadBackup() {
    const form = document.getElementById('exportForm');
    const originalAction = form.action;
    const originalMethod = form.method;

    // Create a temporary form for download
    const downloadForm = document.createElement('form');
    downloadForm.method = 'POST';
    downloadForm.action = '{{ route("admin.database-backup.download") }}';
    downloadForm.style.display = 'none';

    // Clone CSRF
    const csrf = form.querySelector('input[name="_token"]').cloneNode();
    downloadForm.appendChild(csrf);

    // Clone format
    const format = form.querySelector('select[name="format"]').cloneNode(true);
    downloadForm.appendChild(format);

    // Clone selected tables
    document.querySelectorAll('.table-checkbox:checked').forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'tables[]';
        input.value = cb.value;
        downloadForm.appendChild(input);
    });

    document.body.appendChild(downloadForm);
    downloadForm.submit();
    document.body.removeChild(downloadForm);
}
</script>
@endpush
@endsection
