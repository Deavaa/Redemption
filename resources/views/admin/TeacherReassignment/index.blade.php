@extends('layouts.admin')
@section('title', 'Teacher Reassignment')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">Academic</a></li>
                    <li class="active">Teacher Reassignment</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Teacher Reassignment</h1>
            <p class="modern-page-subtitle">Assign homeroom and subject teachers for the selected academic year. Teachers are typically reassigned every new academic year.</p>
        </div>
        <div class="modern-page-header-right">
            @if($selectedAy)
            <form method="POST" action="{{ route('admin.teacher-reassignment.clear-all') }}" onsubmit="return confirm('Are you sure you want to clear ALL teacher assignments for {{ $selectedAy->name }}? This will remove all homeroom and subject teacher assignments.')">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $selectedAy->id }}">
                <button type="submit" class="btn-modern btn-modern-danger-outline">
                    <i class="fas fa-user-slash"></i><span>Clear All Teachers</span>
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Flash Messages --}}
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

    {{-- Academic Year Selector --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title"><i class="fas fa-calendar" style="color:#059669;margin-right:0.5rem;"></i>Select Academic Year</h2>
            </div>
        </div>
        <div class="modern-card-body-inner" style="padding:1rem 1.5rem;">
            <form method="GET" action="{{ route('admin.teacher-reassignment.index') }}" class="ay-selector-form">
                <select name="academic_year_id" class="transition-select" onchange="this.form.submit()">
                    @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}" {{ $selectedAyId == $ay->id ? 'selected' : '' }}>
                            {{ $ay->name }} {{ $ay->is_current ? '(Current)' : '' }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    @if($selectedAy)
    {{-- Stats Cards --}}
    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $stats['total_sections'] }}</span>
                <span class="modern-stat-label">Total Sections</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $stats['sections_with_teacher'] }}</span>
                <span class="modern-stat-label">Sections Assigned</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-orange">
                <i class="fas fa-user-slash"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $stats['sections_without_teacher'] }}</span>
                <span class="modern-stat-label">Sections Unassigned</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-purple">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $stats['assignments_without_teacher'] }}</span>
                <span class="modern-stat-label">Subjects Unassigned</span>
            </div>
        </div>
    </div>

    {{-- Progress Bar --}}
    @php
        $totalSlots = $stats['total_sections'] + $stats['total_assignments'];
        $filledSlots = $stats['sections_with_teacher'] + $stats['assignments_with_teacher'];
        $progressPercent = $totalSlots > 0 ? round(($filledSlots / $totalSlots) * 100) : 0;
    @endphp
    <div class="progress-card">
        <div class="progress-card-header">
            <span class="progress-card-label">Assignment Progress for {{ $selectedAy->name }}</span>
            <span class="progress-card-percent">{{ $progressPercent }}%</span>
        </div>
        <div class="progress-bar-track">
            <div class="progress-bar-fill" style="width: {{ $progressPercent }}%;"></div>
        </div>
        <div class="progress-card-detail">
            {{ $filledSlots }} of {{ $totalSlots }} teacher slots filled
        </div>
    </div>

    {{-- TABS --}}
    <div class="tab-container">
        <button class="tab-btn active" onclick="switchTab('homeroom')">
            <i class="fas fa-chalkboard-teacher"></i> Homeroom Teachers
            @if($stats['sections_without_teacher'] > 0)
                <span class="tab-badge">{{ $stats['sections_without_teacher'] }}</span>
            @endif
        </button>
        <button class="tab-btn" onclick="switchTab('subjects')">
            <i class="fas fa-book"></i> Subject Teachers
            @if($stats['assignments_without_teacher'] > 0)
                <span class="tab-badge">{{ $stats['assignments_without_teacher'] }}</span>
            @endif
        </button>
    </div>

    {{-- TAB 1: Homeroom Teachers --}}
    <div id="homeroomTab" class="tab-content active">
        <form method="POST" action="{{ route('admin.teacher-reassignment.save-homeroom') }}">
            @csrf
            <input type="hidden" name="academic_year_id" value="{{ $selectedAy->id }}">

            @foreach($classesWithSections as $class)
            <div class="reassign-class-card">
                <div class="reassign-class-header">
                    <div class="reassign-class-info">
                        <span class="reassign-class-name">{{ $class->name }}</span>
                        @if($class->branch)
                            <span class="reassign-class-branch">{{ $class->branch->name }}</span>
                        @endif
                    </div>
                    <span class="reassign-section-count">{{ $class->sections->count() }} section(s)</span>
                </div>
                <div class="reassign-sections-grid">
                    @foreach($class->sections as $section)
                    <div class="reassign-section-card {{ !$section->teacher_id ? 'reassign-section-unassigned' : '' }}">
                        <div class="reassign-section-label">
                            <span class="reassign-section-name">Section {{ $section->name }}</span>
                            @if($section->teacher)
                                <span class="reassign-current-teacher"><i class="fas fa-user-check"></i> {{ $section->teacher->full_name }}</span>
                            @else
                                <span class="reassign-no-teacher"><i class="fas fa-user-slash"></i> Unassigned</span>
                            @endif
                        </div>
                        <div class="reassign-section-select">
                            <select name="sections[{{ $section->id }}][id]" style="display:none;">
                                <option value="{{ $section->id }}" selected>{{ $section->id }}</option>
                            </select>
                            <select name="sections[{{ $section->id }}][teacher_id]" class="reassign-teacher-select">
                                <option value="">-- No Teacher --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ $section->teacher_id == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            @if($classesWithSections->count() > 0)
            <div class="reassign-submit-bar">
                <span class="reassign-submit-info">
                    <i class="fas fa-info-circle"></i> Assign homeroom teachers to sections, then save.
                </span>
                <button type="submit" class="btn-modern btn-modern-primary">
                    <i class="fas fa-save"></i><span>Save Homeroom Teachers</span>
                </button>
            </div>
            @else
            <div class="modern-empty-state">
                <div class="modern-empty-icon"><i class="fas fa-layer-group"></i></div>
                <h3>No Classes Found</h3>
                <p>No classes exist for {{ $selectedAy->name }}. Use the Academic Year Transition to carry forward classes first.</p>
                <a href="{{ route('admin.academic-years.transition') }}" class="btn-modern btn-modern-primary">
                    <i class="fas fa-exchange-alt"></i> Go to Transition
                </a>
            </div>
            @endif
        </form>
    </div>

    {{-- TAB 2: Subject Teachers --}}
    <div id="subjectsTab" class="tab-content">
        <form method="POST" action="{{ route('admin.teacher-reassignment.save-subjects') }}">
            @csrf
            <input type="hidden" name="academic_year_id" value="{{ $selectedAy->id }}">

            @if($subjectAssignments->count() > 0)
            <div class="modern-card" style="overflow:visible;">
                <div class="modern-card-header">
                    <div class="modern-card-header-left">
                        <h2 class="modern-card-title">Subject Teacher Assignments</h2>
                        <span class="modern-badge modern-badge-light">{{ $subjectAssignments->count() }} assignments</span>
                    </div>
                </div>
                <div class="modern-card-body">
                    <div class="modern-table-wrapper">
                        <table class="modern-table" id="subjectTable">
                            <thead>
                                <tr>
                                    <th class="th-narrow">#</th>
                                    <th>Subject</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Current Teacher</th>
                                    <th>Assign Teacher</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjectAssignments as $index => $assignment)
                                <tr class="modern-table-row {{ !$assignment->teacher_id ? 'row-unassigned' : '' }}">
                                    <td class="td-narrow">
                                        <span class="modern-row-number">{{ $loop->iteration }}</span>
                                    </td>
                                    <td>
                                        <span class="modern-cell-title">{{ $assignment->subject->name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="modern-badge modern-badge-light">{{ $assignment->classRoom->name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        @if($assignment->section)
                                            <span class="modern-badge modern-badge-light">{{ $assignment->section->name }}</span>
                                        @else
                                            <span class="modern-badge" style="background:#eef2ff;color:#4361ee;">All Sections</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($assignment->teacher)
                                            <span class="teacher-name-assigned"><i class="fas fa-user-check"></i> {{ $assignment->teacher->full_name }}</span>
                                        @else
                                            <span class="teacher-name-unassigned"><i class="fas fa-user-slash"></i> Unassigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="hidden" name="assignments[{{ $assignment->id }}][id]" value="{{ $assignment->id }}">
                                        <select name="assignments[{{ $assignment->id }}][teacher_id]" class="reassign-teacher-select reassign-teacher-select-sm">
                                            <option value="">-- No Teacher --</option>
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}" {{ $assignment->teacher_id == $teacher->id ? 'selected' : '' }}>
                                                    {{ $teacher->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="reassign-submit-bar">
                <span class="reassign-submit-info">
                    <i class="fas fa-info-circle"></i> Assign subject teachers, then save.
                </span>
                <button type="submit" class="btn-modern btn-modern-primary">
                    <i class="fas fa-save"></i><span>Save Subject Teachers</span>
                </button>
            </div>
            @else
            <div class="modern-empty-state">
                <div class="modern-empty-icon"><i class="fas fa-book"></i></div>
                <h3>No Subject Assignments</h3>
                <p>No subject teacher assignments exist for {{ $selectedAy->name }}. Use the Academic Year Transition or Subject Assignments page to set up subjects first.</p>
                <a href="{{ route('admin.subject-assignments.index') }}" class="btn-modern btn-modern-primary">
                    <i class="fas fa-link"></i> Go to Subject Assignments
                </a>
            </div>
            @endif
        </form>
    </div>
    @else
    <div class="modern-empty-state">
        <div class="modern-empty-icon"><i class="fas fa-calendar"></i></div>
        <h3>Select an Academic Year</h3>
        <p>Choose an academic year above to view and manage teacher assignments.</p>
    </div>
    @endif
</div>

@push('styles')
<style>
.modern-page { animation: fadeSlideIn 0.4s ease-out; }
@keyframes fadeSlideIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

.modern-page-header {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;
}
.modern-page-header-left { flex: 1; }
.modern-page-header-right { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.modern-page-title { font-size: 1.5rem; font-weight: 800; color: #1a1a2e; margin: 0 0 0.35rem; }
.modern-page-subtitle { font-size: 0.9rem; color: #6b7280; margin: 0; line-height: 1.5; }

.modern-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #059669; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #059669; font-weight: 500; }

/* Stats Row */
.modern-stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.75rem; }
.modern-stat-card { background: #fff; border-radius: 14px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; transition: transform 0.2s, box-shadow 0.2s; }
.modern-stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.modern-stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
.modern-stat-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-stat-icon-green { background: #ecfdf5; color: #059669; }
.modern-stat-icon-orange { background: #fff7ed; color: #f97316; }
.modern-stat-icon-purple { background: #f5f3ff; color: #8b5cf6; }
.modern-stat-info { display: flex; flex-direction: column; }
.modern-stat-value { font-size: 1.5rem; font-weight: 800; color: #1a1a2e; line-height: 1.2; }
.modern-stat-label { font-size: 0.8rem; color: #6c757d; font-weight: 500; }

/* Progress Card */
.progress-card { background: #fff; border-radius: 14px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 1.75rem; }
.progress-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
.progress-card-label { font-weight: 600; color: #1a1a2e; font-size: 0.9rem; }
.progress-card-percent { font-weight: 800; color: #059669; font-size: 1.1rem; }
.progress-bar-track { width: 100%; height: 10px; background: #e5e7eb; border-radius: 10px; overflow: hidden; }
.progress-bar-fill { height: 100%; background: linear-gradient(135deg, #059669, #047857); border-radius: 10px; transition: width 0.6s ease; }
.progress-card-detail { font-size: 0.8rem; color: #6b7280; margin-top: 0.5rem; }

/* Card */
.modern-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; margin-bottom: 1.5rem; }
.modern-card-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #f0f0f0; flex-wrap: wrap; gap: 1rem; }
.modern-card-header-left { display: flex; align-items: center; gap: 0.75rem; }
.modern-card-title { font-size: 1.1rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.modern-card-body { padding: 0; }
.modern-card-body-inner { padding: 0; }

/* Badges */
.modern-badge { display: inline-flex; align-items: center; padding: 0.25rem 0.65rem; border-radius: 50px; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.3px; }
.modern-badge-light { background: #f3f4f6; color: #6b7280; }
.modern-badge-success { background: #ecfdf5; color: #059669; }
.modern-badge-danger { background: #fef2f2; color: #dc2626; }
.modern-badge-warning { background: #fff7ed; color: #ea580c; }

/* Alert */
.modern-alert { display: flex; align-items: center; gap: 0.65rem; padding: 0.85rem 1.25rem; margin-bottom: 1.5rem; border-radius: 10px; font-size: 0.88rem; font-weight: 500; animation: fadeSlideIn 0.3s ease; }
.modern-alert-success { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
.modern-alert-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.modern-alert-close { margin-left: auto; background: none; border: none; cursor: pointer; color: inherit; opacity: 0.6; transition: opacity 0.2s; }
.modern-alert-close:hover { opacity: 1; }

/* Select */
.transition-select {
    width: 100%; max-width: 400px; padding: 0.7rem 1rem; border: 1.5px solid #e5e7eb;
    border-radius: 10px; font-size: 0.9rem; color: #1a1a2e; background: #f9fafb; transition: all 0.2s;
}
.transition-select:focus { outline: none; border-color: #059669; background: #fff; box-shadow: 0 0 0 3px rgba(5,150,105,0.1); }

/* Tabs */
.tab-container { display: flex; gap: 0.25rem; margin-bottom: 0; background: #f3f4f6; border-radius: 12px; padding: 0.3rem; }
.tab-btn {
    flex: 1; padding: 0.75rem 1.5rem; border: none; background: transparent;
    border-radius: 10px; font-weight: 600; font-size: 0.9rem; color: #6b7280;
    cursor: pointer; transition: all 0.25s; display: flex; align-items: center;
    justify-content: center; gap: 0.5rem;
}
.tab-btn:hover { color: #1a1a2e; background: rgba(255,255,255,0.5); }
.tab-btn.active { background: #fff; color: #059669; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.tab-badge {
    background: #f59e0b; color: #fff; border-radius: 50px; padding: 0.1rem 0.5rem;
    font-size: 0.7rem; font-weight: 700;
}
.tab-content { display: none; }
.tab-content.active { display: block; }

/* Reassignment Class Card */
.reassign-class-card {
    background: #fff; border-radius: 14px; border: 1px solid #f0f0f0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 1.25rem; overflow: hidden;
}
.reassign-class-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1rem 1.25rem; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e5e7eb;
}
.reassign-class-name { font-weight: 700; color: #1a1a2e; font-size: 1rem; }
.reassign-class-branch { font-size: 0.75rem; color: #6b7280; background: #e5e7eb; border-radius: 50px; padding: 0.15rem 0.55rem; margin-left: 0.75rem; }
.reassign-section-count { font-size: 0.8rem; color: #6b7280; font-weight: 500; }
.reassign-class-info { display: flex; align-items: center; }

.reassign-sections-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem; padding: 1.25rem;
}
.reassign-section-card {
    padding: 1rem; border: 1.5px solid #e5e7eb; border-radius: 12px;
    transition: all 0.2s; background: #fafbfc;
}
.reassign-section-card:hover { border-color: #059669; }
.reassign-section-unassigned { border-color: #fde68a; background: #fffbeb; }
.reassign-section-unassigned:hover { border-color: #f59e0b; }

.reassign-section-label { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
.reassign-section-name { font-weight: 600; color: #1a1a2e; font-size: 0.9rem; }
.reassign-current-teacher { font-size: 0.78rem; color: #059669; font-weight: 500; }
.reassign-no-teacher { font-size: 0.78rem; color: #f59e0b; font-weight: 600; }

.reassign-teacher-select {
    width: 100%; padding: 0.55rem 0.75rem; border: 1.5px solid #e5e7eb;
    border-radius: 8px; font-size: 0.85rem; color: #1a1a2e; background: #fff;
    transition: all 0.2s;
}
.reassign-teacher-select:focus { outline: none; border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,0.1); }
.reassign-teacher-select-sm { padding: 0.45rem 0.6rem; font-size: 0.82rem; min-width: 180px; }

/* Table */
.modern-table-wrapper { overflow-x: auto; }
.modern-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
.modern-table thead th { background: #f9fafb; padding: 0.85rem 1rem; text-align: left; font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; border-bottom: 2px solid #e5e7eb; white-space: nowrap; }
.th-center, .td-center { text-align: center !important; }
.th-actions, .td-actions { text-align: right !important; }
.th-narrow, .td-narrow { width: 50px; }
.modern-table tbody tr { border-bottom: 1px solid #f3f4f6; transition: background 0.15s; }
.modern-table tbody tr:hover { background: #f8fafc; }
.modern-table tbody tr.row-unassigned { background: #fffbeb; }
.modern-table tbody tr.row-unassigned:hover { background: #fef3c7; }
.modern-table td { padding: 0.9rem 1rem; vertical-align: middle; color: #374151; }
.modern-row-number { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 8px; background: #f3f4f6; color: #6b7280; font-weight: 600; font-size: 0.8rem; }
.modern-cell-title { font-weight: 600; color: #1a1a2e; margin-bottom: 2px; }

.teacher-name-assigned { font-size: 0.85rem; color: #059669; font-weight: 500; }
.teacher-name-unassigned { font-size: 0.85rem; color: #f59e0b; font-weight: 600; }

/* Submit Bar */
.reassign-submit-bar {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1.25rem 1.5rem; background: #fff; border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0;
    margin-top: 1.5rem; position: sticky; bottom: 1rem; z-index: 10;
}
.reassign-submit-info { font-size: 0.85rem; color: #6b7280; }
.reassign-submit-info i { color: #059669; margin-right: 0.35rem; }

/* Buttons */
.btn-modern { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.35rem; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border: none; cursor: pointer; transition: all 0.25s; }
.btn-modern-primary { background: linear-gradient(135deg, #059669, #047857); color: #fff; box-shadow: 0 2px 8px rgba(5,150,105,0.3); }
.btn-modern-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(5,150,105,0.4); color: #fff; }
.btn-modern-danger-outline { background: transparent; color: #dc2626; border: 1.5px solid #fecaca; }
.btn-modern-danger-outline:hover { background: #fef2f2; border-color: #dc2626; }

/* Empty State */
.modern-empty-state { text-align: center; padding: 4rem 2rem; }
.modern-empty-icon { width: 80px; height: 80px; border-radius: 50%; background: #f3f4f6; display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; color: #d1d5db; margin-bottom: 1.25rem; }
.modern-empty-state h3 { font-size: 1.2rem; font-weight: 700; color: #1a1a2e; margin: 0 0 0.5rem; }
.modern-empty-state p { color: #9ca3af; font-size: 0.9rem; margin: 0 0 1.5rem; }

/* AY Selector Form */
.ay-selector-form { display: flex; align-items: center; gap: 1rem; }

/* Responsive */
@media (max-width: 768px) {
    .modern-page-header { flex-direction: column; align-items: stretch; }
    .modern-stats-row { grid-template-columns: 1fr 1fr; }
    .reassign-sections-grid { grid-template-columns: 1fr; }
    .reassign-submit-bar { flex-direction: column; gap: 1rem; }
    .reassign-submit-bar .btn-modern { width: 100%; justify-content: center; }
    .tab-container { flex-direction: column; }
}
</style>
@endpush

@push('scripts')
<script>
function switchTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));

    if (tabName === 'homeroom') {
        document.getElementById('homeroomTab').classList.add('active');
        document.querySelectorAll('.tab-btn')[0].classList.add('active');
    } else {
        document.getElementById('subjectsTab').classList.add('active');
        document.querySelectorAll('.tab-btn')[1].classList.add('active');
    }
}
</script>
@endpush
@endsection
