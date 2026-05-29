@extends('layouts.admin')
@section('title', 'Teacher Reviews')
@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li class="active">Teacher Reviews</li>
            </ol></nav>
            <h1 class="modern-page-title">Teacher Reviews by Students</h1>
            <p class="modern-page-subtitle">View and manage student feedback about teachers</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.teacher-reviews.summary') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-chart-bar"></i> Summary by Teacher
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="modern-alert modern-alert-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Stats Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;">
        <div class="modern-card" style="padding:1rem;">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="width:42px;height:42px;border-radius:10px;background:#eef2ff;color:#4361ee;display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                    <i class="fas fa-comments"></i>
                </div>
                <div>
                    <div style="font-size:0.8rem;color:#6b7280;">Total Reviews</div>
                    <div style="font-size:1.3rem;font-weight:700;color:#1a1a2e;">{{ $totalReviews }}</div>
                </div>
            </div>
        </div>
        <div class="modern-card" style="padding:1rem;">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="width:42px;height:42px;border-radius:10px;background:#ecfdf5;color:#10b981;display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                    <i class="fas fa-star"></i>
                </div>
                <div>
                    <div style="font-size:0.8rem;color:#6b7280;">Average Score</div>
                    <div style="font-size:1.3rem;font-weight:700;color:#1a1a2e;">{{ $avgScore ? round($avgScore, 1) . '%' : 'N/A' }}</div>
                </div>
            </div>
        </div>
        <div class="modern-card" style="padding:1rem;">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="width:42px;height:42px;border-radius:10px;background:#fef3c7;color:#d97706;display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <div style="font-size:0.8rem;color:#6b7280;">This Term Reviews</div>
                    <div style="font-size:1.3rem;font-weight:700;color:#1a1a2e;">{{ $thisTermReviews }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="modern-card" style="padding:1rem 1.25rem;margin-bottom:1.5rem;">
        <form method="GET" action="{{ route('admin.teacher-reviews.index') }}" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:1;min-width:150px;">
                <label style="font-size:0.8rem;font-weight:600;color:#374151;display:block;margin-bottom:0.25rem;">Term</label>
                <select name="term_id" class="modern-input modern-select" style="padding:0.4rem 0.6rem;font-size:0.85rem;">
                    <option value="">All Terms</option>
                    @foreach($terms as $term)
                    <option value="{{ $term->id }}" {{ request('term_id') == $term->id ? 'selected' : '' }}>{{ $term->name }} ({{ $term->academicYear->name ?? '' }})</option>
                    @endforeach
                </select>
            </div>
            <div style="flex:1;min-width:150px;">
                <label style="font-size:0.8rem;font-weight:600;color:#374151;display:block;margin-bottom:0.25rem;">Teacher</label>
                <select name="teacher_id" class="modern-input modern-select" style="padding:0.4rem 0.6rem;font-size:0.85rem;">
                    <option value="">All Teachers</option>
                    @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex:1;min-width:150px;">
                <label style="font-size:0.8rem;font-weight:600;color:#374151;display:block;margin-bottom:0.25rem;">Grade</label>
                <select name="grade" class="modern-input modern-select" style="padding:0.4rem 0.6rem;font-size:0.85rem;">
                    <option value="">All Grades</option>
                    @foreach(\App\Models\TeacherReview::gradeOptions() as $key => $info)
                    <option value="{{ $key }}" {{ request('grade') == $key ? 'selected' : '' }}>{{ $info['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-modern btn-modern-primary" style="padding:0.4rem 1rem;font-size:0.85rem;">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="{{ route('admin.teacher-reviews.index') }}" class="btn-modern btn-modern-outline" style="padding:0.4rem 1rem;font-size:0.85rem;">Reset</a>
        </form>
    </div>

    {{-- Reviews Table --}}
    <div class="modern-card">
        <div style="padding:1.25rem;">
            @if($reviews->count() > 0)
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Teacher</th>
                        <th>Term</th>
                        <th style="text-align:center;">Score</th>
                        <th style="text-align:center;">Grade</th>
                        <th style="text-align:center;">Anonymous</th>
                        <th style="text-align:center;">Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reviews as $review)
                    @php $gradeInfo = \App\Models\TeacherReview::gradeOptions()[$review->grade] ?? ['label' => ucfirst($review->grade), 'color' => '#6b7280'] @endphp
                    <tr>
                        <td>
                            @if($review->is_anonymous)
                                <span style="color:#9ca3af;font-style:italic;"><i class="fas fa-user-secret"></i> Anonymous</span>
                            @else
                                {{ $review->student->full_name ?? 'Unknown' }}
                            @endif
                        </td>
                        <td>{{ $review->teacher->full_name }}</td>
                        <td>{{ $review->term->name }}</td>
                        <td style="text-align:center;font-weight:600;">{{ $review->overall_score }}%</td>
                        <td style="text-align:center;">
                            <span style="background:{{ $gradeInfo['color'] }}20;color:{{ $gradeInfo['color'] }};padding:0.15rem 0.5rem;border-radius:99px;font-size:0.75rem;font-weight:600;">{{ $gradeInfo['label'] }}</span>
                        </td>
                        <td style="text-align:center;">
                            @if($review->is_anonymous)
                                <i class="fas fa-user-secret" style="color:#9ca3af;"></i>
                            @else
                                <i class="fas fa-user" style="color:#10b981;"></i>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($review->status === 'flagged')
                                <span class="modern-badge modern-badge-danger">Flagged</span>
                            @else
                                <span class="modern-badge modern-badge-success">Submitted</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <a href="{{ route('admin.teacher-reviews.show', $review) }}" class="btn-modern btn-modern-outline" style="padding:0.25rem 0.6rem;font-size:0.8rem;" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($review->status !== 'flagged')
                            <form method="POST" action="{{ route('admin.teacher-reviews.flag', $review) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="btn-modern btn-modern-outline" style="padding:0.25rem 0.6rem;font-size:0.8rem;color:#f59e0b;" title="Flag" onclick="return confirm('Flag this review?')">
                                    <i class="fas fa-flag"></i>
                                </button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('admin.teacher-reviews.unflag', $review) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="btn-modern btn-modern-outline" style="padding:0.25rem 0.6rem;font-size:0.8rem;color:#10b981;" title="Unflag">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('admin.teacher-reviews.destroy', $review) }}" style="display:inline" onsubmit="return confirm('Delete this review? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-modern btn-modern-danger" style="padding:0.25rem 0.6rem;font-size:0.8rem;" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top:1rem;">{{ $reviews->withQueryString()->links() }}</div>
            @else
            <div class="modern-empty-state">
                <i class="fas fa-comment-slash" style="font-size:3rem;color:#d1d5db;"></i>
                <p>No reviews found. Students have not submitted any teacher reviews yet.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
