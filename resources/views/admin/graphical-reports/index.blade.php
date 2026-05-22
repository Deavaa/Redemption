@extends('layouts.admin')
@section('title', 'Graphical Reports Dashboard')
@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    .charts-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 1.5rem; }
    .chart-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; }
    .chart-card-header { padding: 1rem 1.25rem; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; gap: 0.75rem; }
    .chart-card-header i { color: #4361ee; }
    .chart-card-header h3 { margin: 0; font-size: 0.95rem; font-weight: 700; color: #1a1a2e; }
    .chart-card-body { padding: 1.25rem; }
    .chart-card-body canvas { max-height: 300px; }
    @media (max-width: 768px) { .charts-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li class="active">Graphical Reports</li>
            </ol></nav>
            <h1 class="modern-page-title">Graphical Reports</h1>
            <p class="modern-page-subtitle">Visual analytics and charts for all system data</p>
        </div>
    </div>

    {{-- ===== STUDENT REPORTS ===== --}}
    <h2 style="font-size:1.1rem;color:#1a1a2e;margin:0 0 1rem;padding:0.5rem 0;border-bottom:2px solid #4361ee;display:flex;align-items:center;gap:0.5rem;">
        <i class="fas fa-user-graduate" style="color:#4361ee;"></i> Student Reports
    </h2>
    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="fas fa-building"></i>
                <h3>Students by Branch</h3>
            </div>
            <div class="chart-card-body">
                <canvas id="studentsByBranchChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="fas fa-venus-mars"></i>
                <h3>Gender Distribution</h3>
            </div>
            <div class="chart-card-body">
                <canvas id="genderChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="fas fa-chalkboard"></i>
                <h3>Students by Class</h3>
            </div>
            <div class="chart-card-body">
                <canvas id="studentsByClassChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="fas fa-user-check"></i>
                <h3>Student Status</h3>
            </div>
            <div class="chart-card-body">
                <canvas id="studentStatusChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ===== FINANCE REPORTS ===== --}}
    <h2 style="font-size:1.1rem;color:#1a1a2e;margin:0 0 1rem;padding:0.5rem 0;border-bottom:2px solid #10b981;display:flex;align-items:center;gap:0.5rem;">
        <i class="fas fa-chart-line" style="color:#10b981;"></i> Financial Reports
    </h2>
    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="fas fa-money-bill-trend-up"></i>
                <h3>Fee Collection Trend (12 Months)</h3>
            </div>
            <div class="chart-card-body">
                <canvas id="feeTrendChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="fas fa-scale-balanced"></i>
                <h3>Fee Expected vs Collected by Term</h3>
            </div>
            <div class="chart-card-body">
                <canvas id="feeByTermChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="fas fa-arrow-right-arrow-left"></i>
                <h3>Income vs Expense Trend</h3>
            </div>
            <div class="chart-card-body">
                <canvas id="incomeExpenseChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="fas fa-credit-card"></i>
                <h3>Payment Methods</h3>
            </div>
            <div class="chart-card-body">
                <canvas id="paymentMethodChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ===== ACADEMIC REPORTS ===== --}}
    <h2 style="font-size:1.1rem;color:#1a1a2e;margin:0 0 1rem;padding:0.5rem 0;border-bottom:2px solid #7c3aed;display:flex;align-items:center;gap:0.5rem;">
        <i class="fas fa-graduation-cap" style="color:#7c3aed;"></i> Academic Reports
    </h2>
    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="fas fa-calendar-check"></i>
                <h3>Attendance Rate (30 Days)</h3>
            </div>
            <div class="chart-card-body">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="fas fa-ranking-star"></i>
                <h3>Average Performance by Class</h3>
            </div>
            <div class="chart-card-body">
                <canvas id="performanceByClassChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="fas fa-book-open"></i>
                <h3>Average Performance by Subject</h3>
            </div>
            <div class="chart-card-body">
                <canvas id="performanceBySubjectChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="fas fa-building-columns"></i>
                <h3>Average Performance by Branch</h3>
            </div>
            <div class="chart-card-body">
                <canvas id="performanceByBranchChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ===== WORKFLOW REPORTS ===== --}}
    <h2 style="font-size:1.1rem;color:#1a1a2e;margin:0 0 1rem;padding:0.5rem 0;border-bottom:2px solid #f59e0b;display:flex;align-items:center;gap:0.5rem;">
        <i class="fas fa-tasks" style="color:#f59e0b;"></i> Workflow & Activity Reports
    </h2>
    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="fas fa-file-alt"></i>
                <h3>Lesson Plan Status</h3>
            </div>
            <div class="chart-card-body">
                <canvas id="lessonPlanStatusChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="fas fa-calendar-week"></i>
                <h3>Lesson Plan by Type</h3>
            </div>
            <div class="chart-card-body">
                <canvas id="lessonPlanByTypeChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="fas fa-question-circle"></i>
                <h3>Exam Question Approval Pipeline</h3>
            </div>
            <div class="chart-card-body">
                <canvas id="examPipelineChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="fas fa-users"></i>
                <h3>Staff by Role</h3>
            </div>
            <div class="chart-card-body">
                <canvas id="staffByRoleChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="fas fa-futbol"></i>
                <h3>Club Activities by Status</h3>
            </div>
            <div class="chart-card-body">
                <canvas id="clubActivityStatusChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="fas fa-trophy"></i>
                <h3>Club Activities by Type</h3>
            </div>
            <div class="chart-card-body">
                <canvas id="clubActivityTypeChart"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const colors = ['#4361ee','#10b981','#f59e0b','#ef4444','#7c3aed','#06b6d4','#ec4899','#84cc16','#f97316','#6366f1'];
    const bgColors = colors.map(c => c + '33');

    // Students by Branch
    new Chart(document.getElementById('studentsByBranchChart'), {
        type: 'bar',
        data: {
            labels: @json($studentsByBranch->pluck('name')),
            datasets: [{
                label: 'Students',
                data: @json($studentsByBranch->pluck('count')),
                backgroundColor: colors.slice(0, $studentsByBranch->count()),
                borderRadius: 6,
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });

    // Gender Distribution
    new Chart(document.getElementById('genderChart'), {
        type: 'doughnut',
        data: {
            labels: @json($genderDist->keys()),
            datasets: [{
                data: @json($genderDist->values()),
                backgroundColor: ['#4361ee','#ec4899','#9ca3af'],
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Students by Class
    new Chart(document.getElementById('studentsByClassChart'), {
        type: 'bar',
        data: {
            labels: @json($studentsByClass->pluck('name')),
            datasets: [{
                label: 'Students',
                data: @json($studentsByClass->pluck('count')),
                backgroundColor: '#4361ee44',
                borderColor: '#4361ee',
                borderWidth: 2,
                borderRadius: 4,
            }]
        },
        options: { responsive: true, indexAxis: 'y', plugins: { legend: { display: false } } }
    });

    // Student Status
    new Chart(document.getElementById('studentStatusChart'), {
        type: 'pie',
        data: {
            labels: @json($studentStatus->keys()),
            datasets: [{
                data: @json($studentStatus->values()),
                backgroundColor: ['#10b981','#f59e0b','#ef4444','#9ca3af','#06b6d4'],
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Fee Collection Trend
    new Chart(document.getElementById('feeTrendChart'), {
        type: 'line',
        data: {
            labels: @json($feeTrend->pluck('month')),
            datasets: [{
                label: 'Fee Collected (ETB)',
                data: @json($feeTrend->pluck('total')),
                borderColor: '#10b981',
                backgroundColor: '#10b98133',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Fee by Term
    new Chart(document.getElementById('feeByTermChart'), {
        type: 'bar',
        data: {
            labels: @json(collect($feeByTerm)->pluck('term')),
            datasets: [
                { label: 'Expected', data: @json(collect($feeByTerm)->pluck('expected')), backgroundColor: '#4361ee44', borderColor: '#4361ee', borderWidth: 2 },
                { label: 'Collected', data: @json(collect($feeByTerm)->pluck('collected')), backgroundColor: '#10b98144', borderColor: '#10b981', borderWidth: 2 },
            ]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Income vs Expense
    new Chart(document.getElementById('incomeExpenseChart'), {
        type: 'line',
        data: {
            labels: @json($incomeExpenseTrend->pluck('month')),
            datasets: [
                { label: 'Income', data: @json($incomeExpenseTrend->pluck('income')), borderColor: '#10b981', tension: 0.3, fill: false },
                { label: 'Expense', data: @json($incomeExpenseTrend->pluck('expense')), borderColor: '#ef4444', tension: 0.3, fill: false },
            ]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Payment Methods
    new Chart(document.getElementById('paymentMethodChart'), {
        type: 'doughnut',
        data: {
            labels: @json($paymentMethods->pluck('method')),
            datasets: [{
                data: @json($paymentMethods->pluck('total')),
                backgroundColor: colors,
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Attendance Trend
    new Chart(document.getElementById('attendanceChart'), {
        type: 'line',
        data: {
            labels: @json($attendanceTrend->pluck('day')),
            datasets: [{
                label: 'Attendance Rate (%)',
                data: @json($attendanceTrend->pluck('rate')),
                borderColor: '#7c3aed',
                backgroundColor: '#7c3aed33',
                fill: true,
                tension: 0.3,
                pointRadius: 2,
            }]
        },
        options: { responsive: true, scales: { y: { min: 0, max: 100 } } }
    });

    // Performance by Class
    new Chart(document.getElementById('performanceByClassChart'), {
        type: 'bar',
        data: {
            labels: @json($performanceByClass->pluck('class')),
            datasets: [{
                label: 'Average Score',
                data: @json($performanceByClass->pluck('average')),
                backgroundColor: '#7c3aed44',
                borderColor: '#7c3aed',
                borderWidth: 2,
                borderRadius: 4,
            }]
        },
        options: { responsive: true, indexAxis: 'y', plugins: { legend: { display: false } } }
    });

    // Performance by Subject
    new Chart(document.getElementById('performanceBySubjectChart'), {
        type: 'bar',
        data: {
            labels: @json($performanceBySubject->pluck('subject')),
            datasets: [{
                label: 'Average Score',
                data: @json($performanceBySubject->pluck('average')),
                backgroundColor: colors.slice(0, $performanceBySubject->count()),
                borderRadius: 4,
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });

    // Performance by Branch
    new Chart(document.getElementById('performanceByBranchChart'), {
        type: 'radar',
        data: {
            labels: @json($performanceByBranch->pluck('branch')),
            datasets: [{
                label: 'Average Score',
                data: @json($performanceByBranch->pluck('average')),
                backgroundColor: '#4361ee33',
                borderColor: '#4361ee',
                borderWidth: 2,
            }]
        },
        options: { responsive: true, scales: { r: { beginAtZero: true } } }
    });

    // Lesson Plan Status
    new Chart(document.getElementById('lessonPlanStatusChart'), {
        type: 'doughnut',
        data: {
            labels: @json($lessonPlanStatus->keys()),
            datasets: [{
                data: @json($lessonPlanStatus->values()),
                backgroundColor: ['#9ca3af','#4361ee','#06b6d4','#10b981','#ef4444'],
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Lesson Plan by Type
    new Chart(document.getElementById('lessonPlanByTypeChart'), {
        type: 'pie',
        data: {
            labels: @json($lessonPlanByType->keys()),
            datasets: [{
                data: @json($lessonPlanByType->values()),
                backgroundColor: ['#4361ee','#7c3aed','#f59e0b'],
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Exam Pipeline
    new Chart(document.getElementById('examPipelineChart'), {
        type: 'bar',
        data: {
            labels: @json($examPipeline->keys()),
            datasets: [{
                label: 'Count',
                data: @json($examPipeline->values()),
                backgroundColor: ['#f59e0b','#4361ee','#10b981','#ef4444','#ef4444','#06b6d4'],
                borderRadius: 4,
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });

    // Staff by Role
    new Chart(document.getElementById('staffByRoleChart'), {
        type: 'doughnut',
        data: {
            labels: @json($staffByRole->keys()),
            datasets: [{
                data: @json($staffByRole->values()),
                backgroundColor: colors,
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Club Activity Status
    new Chart(document.getElementById('clubActivityStatusChart'), {
        type: 'pie',
        data: {
            labels: @json($clubActivityStatus->keys()),
            datasets: [{
                data: @json($clubActivityStatus->values()),
                backgroundColor: ['#4361ee','#f59e0b','#10b981','#ef4444'],
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Club Activity Type
    new Chart(document.getElementById('clubActivityTypeChart'), {
        type: 'bar',
        data: {
            labels: @json($clubActivityType->keys()),
            datasets: [{
                label: 'Activities',
                data: @json($clubActivityType->values()),
                backgroundColor: colors,
                borderRadius: 4,
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
</script>
@endpush
@endsection
