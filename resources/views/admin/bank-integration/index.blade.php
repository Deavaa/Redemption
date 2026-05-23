@extends('layouts.admin')
@section('title', 'Bank Integration - Fee Collection')
@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li>Finance</li>
                <li class="active">Bank Integration</li>
            </ol></nav>
            <h1 class="modern-page-title">Bank Integration</h1>
            <p class="modern-page-subtitle">Automatically collect fee payment information from Ethiopian banks</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.bank-integration.settings') }}" class="btn-modern btn-modern-secondary">
                <i class="fas fa-cog"></i> Bank Settings
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
            <div class="modern-stat-icon modern-stat-icon-orange"><i class="fas fa-clock"></i></div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $pendingCount }}</span>
                <span class="modern-stat-label">Pending Review</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green"><i class="fas fa-check-circle"></i></div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $matchedCount }}</span>
                <span class="modern-stat-label">Matched</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-red"><i class="fas fa-times-circle"></i></div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $unmatchedCount }}</span>
                <span class="modern-stat-label">Unmatched</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue"><i class="fas fa-money-bill-wave"></i></div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ number_format($totalAmount, 2) }} ETB</span>
                <span class="modern-stat-label">Total Collected</span>
            </div>
        </div>
    </div>

    {{-- CSV Upload Card --}}
    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div class="modern-form-section">
            <div class="modern-form-section-header">
                <div class="modern-form-section-icon" style="background:#eef2ff;color:#4361ee;">
                    <i class="fas fa-upload"></i>
                </div>
                <div>
                    <h3 class="modern-form-section-title">Upload Bank Statement (CSV)</h3>
                    <p class="modern-form-section-desc">Import transactions from your bank's CSV export file</p>
                </div>
            </div>
            <div class="modern-form-section-body">
                <form method="POST" action="{{ route('admin.bank-integration.upload-csv') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modern-form-grid" style="grid-template-columns:1fr 1fr auto;">
                        <div class="modern-form-group">
                            <label class="modern-form-label">Bank Account</label>
                            <select name="bank_integration_id" class="modern-input modern-select" style="padding-left:0.75rem" required>
                                <option value="">Select Bank Account</option>
                                @foreach($bankIntegrations as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->bank_name }} - {{ $bank->account_number }} ({{ $bank->branch?->name ?? 'All' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">CSV File</label>
                            <input type="file" name="csv_file" class="modern-input" style="padding-left:0.75rem" accept=".csv,.txt" required>
                        </div>
                        <div class="modern-form-group" style="align-self:end;">
                            <button type="submit" class="btn-modern btn-modern-primary">
                                <i class="fas fa-upload"></i> Import
                            </button>
                        </div>
                    </div>
                    <div style="margin-top:0.75rem;font-size:0.82rem;color:#6b7280;">
                        <i class="fas fa-info-circle"></i> Supported formats: CBE, Awash, Dashen, and other Ethiopian bank CSV exports. The system auto-detects column names and matches transactions to students.
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- How Bank Integration Works --}}
    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div class="modern-form-section">
            <div class="modern-form-section-header" style="cursor:pointer;" onclick="toggleBankGuide()">
                <div class="modern-form-section-icon" style="background:#fefce8;color:#d97706;">
                    <i class="fas fa-question-circle"></i>
                </div>
                <div style="flex:1;">
                    <h3 class="modern-form-section-title">How Ethiopian Bank Integration Works</h3>
                    <p class="modern-form-section-desc">Step-by-step guide for linking your school bank account</p>
                </div>
                <i class="fas fa-chevron-down" id="bankGuideChevron" style="color:#9ca3af;transition:transform 0.3s;"></i>
            </div>
            <div class="modern-form-section-body" id="bankGuideBody" style="display:none;">
                <div style="background:#f8fafc;border-radius:12px;padding:1.25rem;border:1px solid #e5e7eb;">
                    <h4 style="margin:0 0 0.75rem;color:#1a1a2e;font-size:0.95rem;"><i class="fas fa-university" style="color:#4361ee;margin-right:0.5rem;"></i>Supported Ethiopian Banks &amp; Integration Methods</h4>
                    
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:1rem;">
                            <h5 style="margin:0 0 0.5rem;color:#4361ee;font-size:0.88rem;">Method 1: CSV Upload (All Banks)</h5>
                            <ol style="margin:0;padding-left:1.25rem;font-size:0.82rem;color:#374151;line-height:1.8;">
                                <li>Log in to your bank's internet banking portal</li>
                                <li>Download the transaction statement as CSV</li>
                                <li>Upload the CSV file above</li>
                                <li>The system auto-detects columns and imports transactions</li>
                                <li>Auto-matching tries to link payments to students</li>
                            </ol>
                        </div>
                        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:1rem;">
                            <h5 style="margin:0 0 0.5rem;color:#10b981;font-size:0.88rem;">Method 2: Bank API (CBE &amp; Awash)</h5>
                            <ol style="margin:0;padding-left:1.25rem;font-size:0.82rem;color:#374151;line-height:1.8;">
                                <li>Contact your bank for API access credentials</li>
                                <li>Configure the API settings in Bank Settings</li>
                                <li>The system periodically fetches transactions</li>
                                <li>Automatic matching runs on new transactions</li>
                                <li>Fee payments are auto-recorded when matched</li>
                            </ol>
                        </div>
                    </div>

                    <h5 style="margin:0 0 0.5rem;font-size:0.88rem;color:#1a1a2e;">Supported Banks:</h5>
                    <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                        @foreach(\App\Models\BankIntegration::ethiopianBanks() as $code => $name)
                        <span class="modern-badge modern-badge-light" style="font-size:0.78rem;">{{ $name }}</span>
                        @endforeach
                    </div>

                    <div style="margin-top:1rem;padding:0.75rem;background:#fefce8;border-radius:8px;font-size:0.85rem;color:#92400e;">
                        <i class="fas fa-lightbulb"></i> <strong>Tip:</strong> Ask parents to include the student's admission number in the payment description/reference for automatic matching. Example: "ADM-2024-001" or "Fee payment for student ID 1234"
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div style="padding:1rem 1.25rem;">
            <form method="GET" action="{{ route('admin.bank-integration.index') }}" id="filterForm">
                <div class="modern-form-grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:0.75rem">
                    <div class="modern-form-group">
                        <select name="status" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="">All Status</option>
                            @foreach(\App\Models\BankTransaction::statusOptions() as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <select name="bank_integration_id" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="">All Banks</option>
                            @foreach($bankIntegrations as $bank)
                            <option value="{{ $bank->id }}" {{ request('bank_integration_id') == $bank->id ? 'selected' : '' }}>{{ $bank->bank_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <input type="text" name="search" class="modern-input" style="padding-left:0.75rem" placeholder="Search sender, reference..." value="{{ request('search') }}">
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <span class="modern-card-title">Bank Transactions</span>
                <span class="modern-badge modern-badge-light">{{ $transactions->total() }}</span>
            </div>
        </div>

        @if($transactions->count() > 0)
        <div class="modern-table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Sender</th>
                        <th>Amount (ETB)</th>
                        <th>Bank</th>
                        <th>Status</th>
                        <th>Matched To</th>
                        <th class="th-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $txn)
                    <tr>
                        <td>{{ $txn->transaction_date?->format('M d') }}</td>
                        <td><code style="background:#f3f4f6;padding:2px 6px;border-radius:4px;font-size:11px;">{{ $txn->transaction_reference }}</code></td>
                        <td>{{ $txn->sender_name ?? '-' }}</td>
                        <td style="font-weight:600;color:#059669;">{{ number_format($txn->amount, 2) }}</td>
                        <td>{{ $txn->bankIntegration?->bank_name ?? '-' }}</td>
                        <td>
                            <span class="modern-badge {{ \App\Models\BankTransaction::statusBadgeClass($txn->status) }}">
                                {{ $txn->status_label }}
                            </span>
                        </td>
                        <td>
                            @if($txn->student)
                            {{ $txn->student->full_name }}
                            @else
                            <span style="color:#9ca3af;">-</span>
                            @endif
                        </td>
                        <td class="td-actions">
                            @if($txn->status === 'pending' || $txn->status === 'unmatched')
                            <button type="button" class="btn-modern btn-modern-outline" style="padding:0.3rem 0.65rem;font-size:0.78rem;" onclick="openMatchModal({{ $txn->id }}, '{{ $txn->sender_name }}', {{ $txn->amount }})" title="Match to Student">
                                <i class="fas fa-link"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.bank-integration.reject', $txn->id) }}" style="display:inline" onsubmit="return confirm('Reject this transaction?')">
                                @csrf
                                <button type="submit" class="btn-modern btn-modern-danger" style="padding:0.3rem 0.65rem;font-size:0.78rem;" title="Reject">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="modern-pagination-wrapper">
            {{ $transactions->withQueryString()->links() }}
        </div>
        @else
        <div class="modern-empty-state">
            <div class="modern-empty-icon"><i class="fas fa-university"></i></div>
            <h3>No bank transactions yet</h3>
            <p>Upload a bank statement CSV or configure bank API integration.</p>
            <a href="{{ route('admin.bank-integration.settings') }}" class="btn-modern btn-modern-primary"><i class="fas fa-cog"></i> Configure Bank</a>
        </div>
        @endif
    </div>
</div>

{{-- Match Modal --}}
<div id="matchModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;justify-content:center;align-items:center;">
    <div style="background:#fff;border-radius:16px;padding:2rem;max-width:500px;width:90%;max-height:80vh;overflow-y:auto;">
        <h3 style="margin:0 0 1rem;color:#1a1a2e;">Match Transaction to Student</h3>
        <div id="matchInfo" style="margin-bottom:1rem;font-size:0.88rem;color:#6b7280;"></div>
        <form method="POST" id="matchForm" action="">
            @csrf
            <input type="hidden" name="student_id" id="matchStudentId">
            <div class="modern-form-group">
                <label class="modern-form-label">Search Student</label>
                <input type="text" id="studentSearch" class="modern-input" style="padding-left:0.75rem" placeholder="Type student name or admission number..." oninput="searchStudents(this.value)">
                <div id="studentResults" style="margin-top:0.5rem;max-height:200px;overflow-y:auto;"></div>
            </div>
            <div class="modern-form-group">
                <label class="modern-form-label">Match Notes</label>
                <textarea name="match_notes" class="modern-input" style="padding-left:0.75rem;min-height:60px;" placeholder="Optional notes about this match..."></textarea>
            </div>
            <div style="display:flex;gap:0.75rem;margin-top:1rem;">
                <button type="submit" class="btn-modern btn-modern-primary"><i class="fas fa-check"></i> Confirm Match</button>
                <button type="button" class="btn-modern btn-modern-outline" onclick="closeMatchModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('#filterForm select').forEach(sel => {
        sel.addEventListener('change', () => document.getElementById('filterForm').submit());
    });

    function toggleBankGuide() {
        const body = document.getElementById('bankGuideBody');
        const chevron = document.getElementById('bankGuideChevron');
        body.style.display = body.style.display === 'none' ? 'block' : 'none';
        chevron.style.transform = body.style.display === 'none' ? 'rotate(0deg)' : 'rotate(180deg)';
    }

    function openMatchModal(txnId, senderName, amount) {
        document.getElementById('matchInfo').innerHTML = `<strong>Sender:</strong> ${senderName || '-'}<br><strong>Amount:</strong> ${amount} ETB`;
        document.getElementById('matchForm').action = `{{ url('admin/bank-integration') }}/${txnId}/match`;
        document.getElementById('matchModal').style.display = 'flex';
    }

    function closeMatchModal() {
        document.getElementById('matchModal').style.display = 'none';
        document.getElementById('studentSearch').value = '';
        document.getElementById('studentResults').innerHTML = '';
        document.getElementById('matchStudentId').value = '';
    }

    function searchStudents(query) {
        if (query.length < 2) {
            document.getElementById('studentResults').innerHTML = '';
            return;
        }
        fetch(`{{ route('admin.bank-integration.search-students') }}?q=${encodeURIComponent(query)}`)
            .then(r => r.json())
            .then(students => {
                let html = '';
                students.forEach(s => {
                    html += `<div style="padding:0.5rem;border-bottom:1px solid #f3f4f6;cursor:pointer;" onclick="selectStudent(${s.id}, '${s.full_name.replace(/'/g, "\\'")}')">
                        <div style="font-weight:600;color:#1a1a2e;">${s.full_name}</div>
                        <div style="font-size:0.78rem;color:#9ca3af;">Adm: ${s.admission_number || '-'} | Roll: ${s.roll_number || '-'}</div>
                    </div>`;
                });
                document.getElementById('studentResults').innerHTML = html || '<div style="padding:0.5rem;color:#9ca3af;">No students found</div>';
            });
    }

    function selectStudent(id, name) {
        document.getElementById('matchStudentId').value = id;
        document.getElementById('studentSearch').value = name;
        document.getElementById('studentResults').innerHTML = '';
    }
</script>
@endpush
@endsection