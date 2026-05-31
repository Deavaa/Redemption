@extends('student.layout')
@section('title', 'Review Teachers')

@section('content')
<div style="padding:1.5rem 2rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
        <div>
            <h2 style="font-size:1.5rem;font-weight:700;color:var(--text-dark);margin:0;">Review Teachers</h2>
            <p style="color:var(--text-muted);margin:0.25rem 0 0;">Share your feedback about your teachers for each term</p>
        </div>
    </div>

    @if(session('success'))
    <div style="background:var(--success-light);color:#065f46;border:1px solid #a7f3d0;border-radius:var(--radius);padding:0.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div style="background:var(--danger-light);color:#991b1b;border:1px solid #fecaca;border-radius:var(--radius);padding:0.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Term Selector --}}
    @if($terms->count() > 0)
    <div style="background:var(--card-bg);border-radius:var(--radius);padding:1rem 1.25rem;margin-bottom:1.5rem;box-shadow:var(--shadow);">
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
            <label style="font-weight:600;color:var(--text-dark);white-space:nowrap;">
                <i class="fas fa-calendar-alt" style="color:var(--primary);"></i> Select Term:
            </label>
            <form method="GET" action="{{ route('student.teacher-review.index') }}" id="termForm" style="display:flex;gap:0.5rem;flex:1;min-width:200px;">
                <select name="term_id" onchange="this.form.submit()" style="flex:1;padding:0.5rem 0.75rem;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:0.9rem;">
                    @foreach($terms as $term)
                    <option value="{{ $term->id }}" {{ $term->id == $selectedTermId ? 'selected' : '' }}>
                        {{ $term->name }} ({{ $term->term_number }})
                    </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
    @else
    <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:var(--radius);padding:1rem 1.25rem;margin-bottom:1.5rem;">
        <i class="fas fa-exclamation-triangle" style="color:#d97706;"></i>
        No terms found for the current academic year. Please contact the administrator.
    </div>
    @endif

    {{-- Teachers to Review --}}
    @if($selectedTerm && $assignedTeachers->count() > 0)
    <div style="background:var(--card-bg);border-radius:var(--radius);padding:1.25rem;margin-bottom:1.5rem;box-shadow:var(--shadow);">
        <h3 style="font-size:1.1rem;font-weight:700;color:var(--text-dark);margin:0 0 1rem;">
            <i class="fas fa-user-tie" style="color:var(--primary);"></i>
            Teachers for {{ $selectedTerm->name }}
        </h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:0.75rem;">
            @foreach($assignedTeachers as $teacher)
            @php $alreadyReviewed = $submittedReviews->has($teacher->id) @endphp
            <div style="border:1px solid var(--border);border-radius:var(--radius);padding:1rem;display:flex;align-items:center;gap:0.75rem;{{ $alreadyReviewed ? 'opacity:0.7;' : '' }}">
                <div style="width:42px;height:42px;border-radius:50%;background:var(--primary-light);display:flex;align-items:center;justify-content:center;color:var(--primary);font-weight:700;font-size:1.1rem;flex-shrink:0;">
                    {{ strtoupper(substr($teacher->full_name, 0, 1)) }}
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;color:var(--text-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $teacher->full_name }}
                    </div>
                    <div style="font-size:0.8rem;color:var(--text-muted);">
                        @if($alreadyReviewed)
                            <span style="color:var(--success);"><i class="fas fa-check-circle"></i> Reviewed</span>
                            — Score: {{ $submittedReviews[$teacher->id]->overall_score }}%
                        @else
                            <span style="color:var(--warning);"><i class="fas fa-clock"></i> Pending Review</span>
                        @endif
                    </div>
                </div>
                @if($alreadyReviewed)
                    <a href="{{ route('student.teacher-review.show', $submittedReviews[$teacher->id]) }}" style="padding:0.35rem 0.75rem;background:var(--primary-light);color:var(--primary);border-radius:var(--radius-sm);font-size:0.8rem;text-decoration:none;font-weight:600;">
                        View
                    </a>
                @else
                    <form method="GET" action="{{ route('student.teacher-review.create') }}" style="margin:0;">
                        <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                        <input type="hidden" name="term_id" value="{{ $selectedTerm->id }}">
                        <button type="submit" style="padding:0.35rem 0.75rem;background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);font-size:0.8rem;cursor:pointer;font-weight:600;">
                            <i class="fas fa-star"></i> Review
                        </button>
                    </form>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @elseif($selectedTerm)
    <div style="background:var(--card-bg);border-radius:var(--radius);padding:2rem;text-align:center;box-shadow:var(--shadow);margin-bottom:1.5rem;">
        <i class="fas fa-user-slash" style="font-size:2.5rem;color:var(--text-muted);"></i>
        <p style="color:var(--text-muted);margin-top:0.75rem;">No teachers found for your class in this term.</p>
    </div>
    @endif

    {{-- Review History --}}
    @if($allReviews->count() > 0)
    <div style="background:var(--card-bg);border-radius:var(--radius);padding:1.25rem;box-shadow:var(--shadow);">
        <h3 style="font-size:1.1rem;font-weight:700;color:var(--text-dark);margin:0 0 1rem;">
            <i class="fas fa-history" style="color:var(--accent);"></i> My Review History
        </h3>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:var(--primary-light);">
                        <th style="padding:0.6rem 0.75rem;text-align:left;font-weight:600;color:var(--text-dark);font-size:0.8rem;border-bottom:2px solid var(--primary);">Teacher</th>
                        <th style="padding:0.6rem 0.75rem;text-align:left;font-weight:600;color:var(--text-dark);font-size:0.8rem;border-bottom:2px solid var(--primary);">Term</th>
                        <th style="padding:0.6rem 0.75rem;text-align:center;font-weight:600;color:var(--text-dark);font-size:0.8rem;border-bottom:2px solid var(--primary);">Score</th>
                        <th style="padding:0.6rem 0.75rem;text-align:center;font-weight:600;color:var(--text-dark);font-size:0.8rem;border-bottom:2px solid var(--primary);">Grade</th>
                        <th style="padding:0.6rem 0.75rem;text-align:center;font-weight:600;color:var(--text-dark);font-size:0.8rem;border-bottom:2px solid var(--primary);">Anonymous</th>
                        <th style="padding:0.6rem 0.75rem;text-align:right;font-weight:600;color:var(--text-dark);font-size:0.8rem;border-bottom:2px solid var(--primary);">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allReviews as $review)
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:0.6rem 0.75rem;color:var(--text);">{{ $review->teacher->full_name }}</td>
                        <td style="padding:0.6rem 0.75rem;color:var(--text);">{{ $review->term->name }}</td>
                        <td style="padding:0.6rem 0.75rem;text-align:center;color:var(--text);font-weight:600;">{{ $review->overall_score }}%</td>
                        <td style="padding:0.6rem 0.75rem;text-align:center;">
                            @php $gradeInfo = \App\Models\TeacherReview::gradeOptions()[$review->grade] ?? ['label' => ucfirst($review->grade), 'color' => '#6b7280'] @endphp
                            <span style="background:{{ $gradeInfo['color'] }}20;color:{{ $gradeInfo['color'] }};padding:0.15rem 0.5rem;border-radius:99px;font-size:0.75rem;font-weight:600;">{{ $gradeInfo['label'] }}</span>
                        </td>
                        <td style="padding:0.6rem 0.75rem;text-align:center;">
                            @if($review->is_anonymous)
                                <i class="fas fa-user-secret" style="color:var(--text-muted);" title="Anonymous"></i>
                            @else
                                <i class="fas fa-user" style="color:var(--primary);" title="Identified"></i>
                            @endif
                        </td>
                        <td style="padding:0.6rem 0.75rem;text-align:right;">
                            <a href="{{ route('student.teacher-review.show', $review) }}" style="color:var(--primary);text-decoration:none;font-weight:600;font-size:0.85rem;">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">
            {{ $allReviews->withQueryString()->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
