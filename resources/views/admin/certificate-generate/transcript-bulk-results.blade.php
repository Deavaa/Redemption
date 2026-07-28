@extends('layouts.admin')
@section('title', 'Transcripts Generated')

@push('styles')
<style>
.br-stats { display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:18px; }
.br-stat-card { background:var(--card-bg);border-radius:12px;padding:14px 18px;border:1px solid var(--border);box-shadow:0 1px 2px rgba(0,0,0,0.04); }
.br-stat-num { font-size:1.6rem;font-weight:700;line-height:1.1; }
.br-stat-label { font-size:0.78rem;color:var(--text-muted);font-weight:500;margin-top:4px; }

.br-result-card { background:var(--card-bg);border:1px solid var(--border);border-radius:10px;padding:12px 14px;display:flex;align-items:center;gap:12px;margin-bottom:8px;transition:all .15s; }
.br-result-card:hover { border-color:var(--primary);box-shadow:0 2px 8px rgba(99,102,241,0.08); }
.br-result-card.skipped { background:#fef3c7;border-color:#fcd34d; }
.br-result-card.error { background:#fee2e2;border-color:#fca5a5; }
.br-result-card.reused { background:#f0f9ff;border-color:#bae6fd; }

.br-result-avatar { width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,var(--primary-light),#e0e7ff);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;overflow:hidden; }
.br-result-avatar img { width:100%;height:100%;object-fit:cover; }
.br-result-info { flex:1;min-width:0; }
.br-result-name { font-weight:600;color:var(--text-dark);font-size:0.92rem; }
.br-result-meta { font-size:0.78rem;color:var(--text-muted);margin-top:2px;display:flex;gap:10px;flex-wrap:wrap; }
.br-result-meta .cert-num { font-family:monospace;color:var(--primary);font-weight:600; }
.br-result-actions { display:flex;gap:6px;flex-shrink:0; }
.br-btn-view { background:var(--primary);color:#fff;border:none;padding:6px 14px;border-radius:6px;font-size:0.8rem;font-weight:600;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px; }
.br-btn-view:hover { background:var(--primary-dark);color:#fff; }
.br-btn-print { background:transparent;color:var(--text);border:1px solid var(--border);padding:6px 12px;border-radius:6px;font-size:0.8rem;font-weight:600;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px; }
.br-btn-print:hover { background:var(--bg-hover);color:var(--text-dark); }

.br-badge { padding:2px 8px;border-radius:10px;font-size:0.7rem;font-weight:600; }
.br-badge-new { background:#dcfce7;color:#15803d; }
.br-badge-reused { background:#dbeafe;color:#1d4ed8; }
.br-badge-skipped { background:#fef3c7;color:#a16207; }
.br-badge-error { background:#fee2e2;color:#b91c1c; }

.br-empty-section { padding:14px 18px;background:#f9fafb;border-radius:8px;color:var(--text-muted);font-size:0.85rem;text-align:center;margin-top:8px; }
</style>
@endpush

@section('content')
<div class="modern-page">
    <div class="ti-page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px;">
        <div>
            <nav aria-label="breadcrumb" class="modern-breadcrumb" style="margin:0 0 6px;">
                <ol style="margin:0;">
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.certificate-generate.index') }}">Documents</a></li>
                    <li><a href="{{ route('admin.transcript.index') }}">Academic Transcript</a></li>
                    <li class="active">Bulk Results</li>
                </ol>
            </nav>
            <h1 style="font-size:1.1rem;font-weight:700;color:var(--text-dark);margin:0;">
                <i class="fas fa-check-circle text-success me-2"></i>Transcript Generation Results
            </h1>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('admin.transcript.index') }}" class="br-btn-print">
                <i class="fas fa-arrow-left"></i> Generate More
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="br-stats">
        <div class="br-stat-card">
            <div class="br-stat-num text-success">{{ count($generated) }}</div>
            <div class="br-stat-label">Generated / Reused</div>
        </div>
        <div class="br-stat-card">
            <div class="br-stat-num text-warning">{{ count($skipped) }}</div>
            <div class="br-stat-label">Skipped (no marks)</div>
        </div>
        <div class="br-stat-card">
            <div class="br-stat-num text-danger">{{ count($errors) }}</div>
            <div class="br-stat-label">Errors</div>
        </div>
        <div class="br-stat-card">
            <div class="br-stat-num text-primary">{{ count($generated) + count($skipped) + count($errors) }}</div>
            <div class="br-stat-label">Total Processed</div>
        </div>
    </div>

    {{-- Generated / Reused --}}
    @if(count($generated) > 0)
    <div class="modern-card" style="margin-bottom:14px;">
        <div style="padding:10px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
            <h3 style="margin:0;font-size:0.92rem;font-weight:700;color:var(--text-dark);">
                <i class="fas fa-check-circle text-success me-1"></i>Generated Transcripts ({{ count($generated) }})
            </h3>
            <small class="text-muted">Click "View" to open each transcript in a new tab</small>
        </div>
        <div style="padding:10px 14px;">
            @foreach($generated as $item)
                @php $s = $item['student']; $c = $item['certificate']; @endphp
                <div class="br-result-card {{ $item['reused'] ? 'reused' : '' }}">
                    <div class="br-result-avatar">
                        @if($s->photo)<img src="{{ asset('storage/' . $s->photo) }}" alt="">{{else}}{{$s->full_name[0] ?? '?'}}@endif
                    </div>
                    <div class="br-result-info">
                        <div class="br-result-name">
                            {{ $s->full_name }}
                            <span class="br-badge {{ $item['reused'] ? 'br-badge-reused' : 'br-badge-new' }}" style="margin-left:6px;">
                                {{ $item['reused'] ? 'Reused' : 'New' }}
                            </span>
                        </div>
                        <div class="br-result-meta">
                            <span class="cert-num"><i class="fas fa-hashtag"></i> {{ $c->certificate_number }}</span>
                            @if($s->classroom)<span><i class="fas fa-chalkboard"></i> {{ $s->classroom->name }}</span>@endif
                            @if($s->section)<span><i class="fas fa-layer-group"></i> {{ $s->section->name }}</span>@endif
                            <span><i class="fas fa-calendar"></i> {{ $c->issue_date?->format('M d, Y') }}</span>
                            @if($s->status === 'graduated')<span style="color:#7c3aed;font-weight:600;"><i class="fas fa-mortarboard"></i> Graduated</span>@endif
                        </div>
                    </div>
                    <div class="br-result-actions">
                        <a href="{{ route('admin.transcript.show', $c) }}" target="_blank" class="br-btn-view">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('admin.transcript.show', $c) }}" target="_blank" class="br-btn-print" onclick="window.open('{{ route('admin.transcript.show', $c) }}'); setTimeout(function(){window.print();}, 800); return false;">
                            <i class="fas fa-print"></i> Print
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Skipped --}}
    @if(count($skipped) > 0)
    <div class="modern-card" style="margin-bottom:14px;">
        <div style="padding:10px 14px;border-bottom:1px solid var(--border);">
            <h3 style="margin:0;font-size:0.92rem;font-weight:700;color:#a16207;">
                <i class="fas fa-exclamation-triangle me-1"></i>Skipped ({{ count($skipped) }})
            </h3>
        </div>
        <div style="padding:10px 14px;">
            @foreach($skipped as $item)
                @php $s = $item['student']; @endphp
                <div class="br-result-card skipped">
                    <div class="br-result-avatar" style="background:#fef3c7;color:#a16207;">
                        {{ $s->full_name[0] ?? '?' }}
                    </div>
                    <div class="br-result-info">
                        <div class="br-result-name">{{ $s->full_name }}</div>
                        <div class="br-result-meta">
                            <span class="br-badge br-badge-skipped">{{ $item['reason'] }}</span>
                            @if($s->classroom)<span><i class="fas fa-chalkboard"></i> {{ $s->classroom->name }}</span>@endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Errors --}}
    @if(count($errors) > 0)
    <div class="modern-card" style="margin-bottom:14px;">
        <div style="padding:10px 14px;border-bottom:1px solid var(--border);">
            <h3 style="margin:0;font-size:0.92rem;font-weight:700;color:#b91c1c;">
                <i class="fas fa-times-circle me-1"></i>Errors ({{ count($errors) }})
            </h3>
        </div>
        <div style="padding:10px 14px;">
            @foreach($errors as $item)
                @php $s = $item['student']; @endphp
                <div class="br-result-card error">
                    <div class="br-result-avatar" style="background:#fee2e2;color:#b91c1c;">
                        {{ $s->full_name[0] ?? '?' }}
                    </div>
                    <div class="br-result-info">
                        <div class="br-result-name">{{ $s->full_name }}</div>
                        <div class="br-result-meta">
                            <span class="br-badge br-badge-error">{{ $item['error'] }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- If nothing at all --}}
    @if(count($generated) === 0 && count($skipped) === 0 && count($errors) === 0)
        <div class="modern-card">
            <div class="br-empty-section">
                <i class="fas fa-info-circle me-1"></i>No students were processed. Please go back and select students.
            </div>
        </div>
    @endif
</div>
@endsection
