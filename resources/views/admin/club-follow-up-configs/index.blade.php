@extends('layouts.admin')
@section('title', 'Club Activity Follow-up Configuration')
@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li>Activities</li>
                <li class="active">Club Follow-up Config</li>
            </ol></nav>
            <h1 class="modern-page-title">Club Follow-up Configuration</h1>
            <p class="modern-page-subtitle">Configure follow-up schedules, checklists, and evaluation criteria for club activities</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.club-follow-up-configs.create') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-plus"></i> New Configuration
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="modern-alert modern-alert-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Stats --}}
    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue"><i class="fas fa-clipboard-list"></i></div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $totalConfigs }}</span>
                <span class="modern-stat-label">Total Configurations</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green"><i class="fas fa-check"></i></div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $activeCount }}</span>
                <span class="modern-stat-label">Active</span>
            </div>
        </div>
    </div>

    {{-- Explanation Card --}}
    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div style="padding:1.25rem 2rem;">
            <h4 style="margin:0 0 0.5rem;color:#1a1a2e;font-size:0.95rem;"><i class="fas fa-info-circle" style="color:#4361ee;margin-right:0.5rem;"></i>How Follow-up Configuration Works</h4>
            <p style="margin:0;font-size:0.88rem;color:#6b7280;line-height:1.7;">
                Follow-up configurations define how and when club activities should be followed up on after they occur. You can configure:
                <strong>Automatic reminders</strong> that notify responsible parties when a follow-up is due,
                <strong>Structured checklists</strong> that guide the follow-up evaluation process, and
                <strong>Rating criteria</strong> for consistent evaluation of activity outcomes.
                Configurations can be assigned to specific clubs or applied globally across all clubs in a branch.
            </p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div style="padding:1rem 1.25rem;">
            <form method="GET" action="{{ route('admin.club-follow-up-configs.index') }}" id="filterForm">
                <div class="modern-form-grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:0.75rem">
                    <div class="modern-form-group">
                        <select name="club_id" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="">All Clubs</option>
                            @foreach($clubs as $club)
                            <option value="{{ $club->id }}" {{ request('club_id') == $club->id ? 'selected' : '' }}>{{ $club->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <select name="branch_id" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <select name="follow_up_type" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="">All Types</option>
                            @foreach(\App\Models\ClubFollowUpConfig::followUpTypeOptions() as $key => $label)
                            <option value="{{ $key }}" {{ request('follow_up_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="modern-card">
        @if($configs->count() > 0)
        <div class="modern-table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Club</th>
                        <th>Branch</th>
                        <th>Follow-up After</th>
                        <th>Auto-Remind</th>
                        <th>Status</th>
                        <th class="th-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($configs as $config)
                    <tr>
                        <td>
                            <div style="font-weight:600;">{{ $config->name }}</div>
                            @if($config->description)
                            <div style="font-size:0.78rem;color:#9ca3af;">{{ Str::limit($config->description, 50) }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="modern-badge modern-badge-{{ $config->follow_up_type === 'regular' ? 'info' : ($config->follow_up_type === 'post_event' ? 'purple' : 'warning') }}">
                                {{ \App\Models\ClubFollowUpConfig::followUpTypeOptions()[$config->follow_up_type] ?? $config->follow_up_type }}
                            </span>
                        </td>
                        <td>{{ $config->club?->name ?? 'All Clubs' }}</td>
                        <td>{{ $config->branch?->name ?? 'All Branches' }}</td>
                        <td>{{ $config->days_after_activity }} day(s)</td>
                        <td>
                            @if($config->is_auto_reminder)
                            <span class="modern-badge modern-badge-success">Yes ({{ $config->reminder_days_before }}d before)</span>
                            @else
                            <span class="modern-badge modern-badge-light">No</span>
                            @endif
                        </td>
                        <td>
                            <span class="modern-badge {{ $config->is_active ? 'modern-badge-success' : 'modern-badge-danger' }}">
                                {{ $config->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="td-actions">
                            <div class="modern-action-group">
                                <a href="{{ route('admin.club-follow-up-configs.edit', $config->id) }}" class="modern-btn-icon modern-btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="{{ route('admin.club-follow-up-configs.destroy', $config->id) }}" style="display:inline" onsubmit="return confirm('Delete this configuration?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="modern-btn-icon modern-btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="modern-pagination-wrapper">
            {{ $configs->withQueryString()->links() }}
        </div>
        @else
        <div class="modern-empty-state">
            <div class="modern-empty-icon"><i class="fas fa-clipboard-list"></i></div>
            <h3>No follow-up configurations yet</h3>
            <p>Create a configuration to set up follow-up schedules for club activities.</p>
            <a href="{{ route('admin.club-follow-up-configs.create') }}" class="btn-modern btn-modern-primary"><i class="fas fa-plus"></i> Create Configuration</a>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('#filterForm select').forEach(sel => {
        sel.addEventListener('change', () => document.getElementById('filterForm').submit());
    });
</script>
@endpush
@endsection
