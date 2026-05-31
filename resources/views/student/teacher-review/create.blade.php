@extends('student.layout')
@section('title', 'Review Teacher')

@section('content')
<div style="padding:1.5rem 2rem;">
    <div style="margin-bottom:1.5rem;">
        <a href="{{ route('student.teacher-review.index') }}" style="color:var(--primary);text-decoration:none;font-weight:600;">
            <i class="fas fa-arrow-left"></i> Back to Reviews
        </a>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
        <div>
            <h2 style="font-size:1.5rem;font-weight:700;color:var(--text-dark);margin:0;">Review Teacher</h2>
            <p style="color:var(--text-muted);margin:0.25rem 0 0;">
                Rate <strong>{{ $teacher->full_name }}</strong> for <strong>{{ $term->name }}</strong>
            </p>
        </div>
    </div>

    @if($errors->any())
    <div style="background:var(--danger-light);color:#991b1b;border:1px solid #fecaca;border-radius:var(--radius);padding:0.75rem 1rem;margin-bottom:1rem;">
        <i class="fas fa-exclamation-circle"></i> Please fix the errors below.
    </div>
    @endif

    <form method="POST" action="{{ route('student.teacher-review.store') }}">
        @csrf
        <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
        <input type="hidden" name="term_id" value="{{ $term->id }}">

        {{-- Rating Criteria --}}
        <div style="background:var(--card-bg);border-radius:var(--radius);padding:1.25rem;margin-bottom:1.5rem;box-shadow:var(--shadow);">
            <h3 style="font-size:1.1rem;font-weight:700;color:var(--text-dark);margin:0 0 1rem;">
                <i class="fas fa-star-half-alt" style="color:var(--primary);"></i> Rating Criteria
            </h3>

            @foreach($criteriaOptions as $field => $label)
            <div style="margin-bottom:1.25rem;padding-bottom:1.25rem;border-bottom:1px solid var(--border);">
                <label style="font-weight:600;color:var(--text-dark);display:block;margin-bottom:0.5rem;">
                    {{ $label }}
                    @if($errors->has($field))
                        <span style="color:var(--danger);font-weight:400;font-size:0.8rem;"> — {{ $errors->first($field) }}</span>
                    @endif
                </label>
                <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                    @foreach($ratingScale as $value => $scaleLabel)
                    <label style="cursor:pointer;display:flex;align-items:center;gap:0.3rem;padding:0.4rem 0.75rem;border:1px solid var(--border);border-radius:var(--radius-sm);transition:var(--transition);"
                           onmouseover="this.style.borderColor='var(--primary)'"
                           onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='var(--border)'">
                        <input type="radio" name="{{ $field }}" value="{{ $value }}"
                               {{ old($field) == $value ? 'checked' : '' }}
                               style="accent-color:var(--primary);"
                               onchange="this.closest('label').style.borderColor='var(--primary)';this.closest('label').style.background='var(--primary-light)';">
                        <span style="font-size:0.85rem;font-weight:500;">{{ $value }}</span>
                        <span style="font-size:0.75rem;color:var(--text-muted);">{{ $scaleLabel }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        {{-- Text Feedback --}}
        <div style="background:var(--card-bg);border-radius:var(--radius);padding:1.25rem;margin-bottom:1.5rem;box-shadow:var(--shadow);">
            <h3 style="font-size:1.1rem;font-weight:700;color:var(--text-dark);margin:0 0 1rem;">
                <i class="fas fa-comment-dots" style="color:var(--accent);"></i> Written Feedback
            </h3>

            <div style="margin-bottom:1rem;">
                <label style="font-weight:600;color:var(--text-dark);display:block;margin-bottom:0.35rem;">Strengths</label>
                <textarea name="strengths" rows="3" placeholder="What does this teacher do well?" style="width:100%;padding:0.6rem 0.75rem;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:0.9rem;resize:vertical;font-family:inherit;">{{ old('strengths') }}</textarea>
            </div>

            <div style="margin-bottom:1rem;">
                <label style="font-weight:600;color:var(--text-dark);display:block;margin-bottom:0.35rem;">Areas for Improvement</label>
                <textarea name="areas_for_improvement" rows="3" placeholder="What could this teacher improve?" style="width:100%;padding:0.6rem 0.75rem;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:0.9rem;resize:vertical;font-family:inherit;">{{ old('areas_for_improvement') }}</textarea>
            </div>

            <div style="margin-bottom:1rem;">
                <label style="font-weight:600;color:var(--text-dark);display:block;margin-bottom:0.35rem;">Additional Comments</label>
                <textarea name="additional_comments" rows="3" placeholder="Any other comments you'd like to share..." style="width:100%;padding:0.6rem 0.75rem;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:0.9rem;resize:vertical;font-family:inherit;">{{ old('additional_comments') }}</textarea>
            </div>
        </div>

        {{-- Privacy Setting --}}
        <div style="background:var(--card-bg);border-radius:var(--radius);padding:1.25rem;margin-bottom:1.5rem;box-shadow:var(--shadow);">
            <h3 style="font-size:1.1rem;font-weight:700;color:var(--text-dark);margin:0 0 1rem;">
                <i class="fas fa-shield-alt" style="color:var(--warning);"></i> Privacy
            </h3>
            <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                <input type="checkbox" name="is_anonymous" value="1" {{ old('is_anonymous', '1') ? 'checked' : '' }} style="accent-color:var(--primary);width:18px;height:18px;">
                <div>
                    <span style="font-weight:600;color:var(--text-dark);">Submit anonymously</span>
                    <p style="font-size:0.8rem;color:var(--text-muted);margin:0;">If checked, the teacher will not see your name. Admins can still see who submitted each review.</p>
                </div>
            </label>
        </div>

        {{-- Submit --}}
        <div style="display:flex;gap:0.75rem;">
            <button type="submit" style="padding:0.6rem 1.5rem;background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);font-size:0.95rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:0.5rem;">
                <i class="fas fa-paper-plane"></i> Submit Review
            </button>
            <a href="{{ route('student.teacher-review.index') }}" style="padding:0.6rem 1.5rem;background:var(--border);color:var(--text);border-radius:var(--radius-sm);font-size:0.95rem;text-decoration:none;font-weight:600;">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
