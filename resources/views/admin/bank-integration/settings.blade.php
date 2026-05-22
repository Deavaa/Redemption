@extends('layouts.admin')
@section('title', 'Bank Integration Settings')
@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.bank-integration.index') }}">Bank Integration</a></li>
                <li class="active">Settings</li>
            </ol></nav>
            <h1 class="modern-page-title">Bank Integration Settings</h1>
            <p class="modern-page-subtitle">Configure Ethiopian bank accounts for automatic fee collection</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.bank-integration.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i> Back to Transactions
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="modern-alert modern-alert-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Add New Bank --}}
    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div class="modern-form-section">
            <div class="modern-form-section-header">
                <div class="modern-form-section-icon modern-form-section-icon-green">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div>
                    <h3 class="modern-form-section-title">Add Bank Account</h3>
                    <p class="modern-form-section-desc">Link an Ethiopian bank account for fee collection</p>
                </div>
            </div>
            <div class="modern-form-section-body">
                <form method="POST" action="{{ route('admin.bank-integration.settings.store') }}">
                    @csrf
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label">Bank Name</label>
                            <select name="bank_name" id="bankNameSelect" class="modern-input modern-select" style="padding-left:0.75rem" required onchange="autoFillBankCode()">
                                <option value="">Select Bank</option>
                                @foreach(\App\Models\BankIntegration::ethiopianBanks() as $code => $name)
                                <option value="{{ $name }}" data-code="{{ $code }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Bank Code</label>
                            <input type="text" name="bank_code" id="bankCodeInput" class="modern-input" style="padding-left:0.75rem" placeholder="CBE" required>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Branch</label>
                            <select name="branch_id" class="modern-input modern-select" style="padding-left:0.75rem">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Account Number</label>
                            <input type="text" name="account_number" class="modern-input" style="padding-left:0.75rem" placeholder="1000-xxxx-xxxx" required>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Account Name</label>
                            <input type="text" name="account_name" class="modern-input" style="padding-left:0.75rem" placeholder="School of Redemption" required>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Integration Type</label>
                            <select name="integration_type" class="modern-input modern-select" style="padding-left:0.75rem" id="integrationType" onchange="toggleApiFields()">
                                @foreach(\App\Models\BankIntegration::integrationTypes() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- API Fields (shown only for API integration type) --}}
                    <div id="apiFields" style="display:none;margin-top:1rem;">
                        <div class="modern-form-grid">
                            <div class="modern-form-group">
                                <label class="modern-form-label">API URL</label>
                                <input type="url" name="api_url" class="modern-input" style="padding-left:0.75rem" placeholder="https://api.bank.com/v1/transactions">
                            </div>
                            <div class="modern-form-group">
                                <label class="modern-form-label">API Key</label>
                                <input type="text" name="api_key" class="modern-input" style="padding-left:0.75rem" placeholder="API Key">
                            </div>
                            <div class="modern-form-group">
                                <label class="modern-form-label">API Secret</label>
                                <input type="password" name="api_secret" class="modern-input" style="padding-left:0.75rem" placeholder="API Secret">
                            </div>
                            <div class="modern-form-group">
                                <label class="modern-form-label">Merchant ID</label>
                                <input type="text" name="merchant_id" class="modern-input" style="padding-left:0.75rem" placeholder="Merchant ID">
                            </div>
                        </div>
                    </div>

                    <div class="modern-form-group" style="margin-top:1rem;">
                        <label class="modern-form-label">Notes</label>
                        <textarea name="notes" class="modern-input" style="padding-left:0.75rem;min-height:60px;" placeholder="Any additional notes about this bank account..."></textarea>
                    </div>

                    <div style="margin-top:1rem;">
                        <button type="submit" class="btn-modern btn-modern-primary">
                            <i class="fas fa-save"></i> Save Bank Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Existing Bank Accounts --}}
    <div class="modern-card">
        <div class="modern-form-section">
            <div class="modern-form-section-header">
                <div class="modern-form-section-icon modern-form-section-icon-blue">
                    <i class="fas fa-university"></i>
                </div>
                <div>
                    <h3 class="modern-form-section-title">Configured Bank Accounts</h3>
                    <p class="modern-form-section-desc">{{ count($bankIntegrations) }} account(s) configured</p>
                </div>
            </div>
            <div style="padding:0 2rem 1.75rem;">
                @if($bankIntegrations->count() > 0)
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Bank</th>
                            <th>Account Number</th>
                            <th>Branch</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bankIntegrations as $bank)
                        <tr>
                            <td>
                                <div style="font-weight:600;">{{ $bank->bank_name }}</div>
                                <div style="font-size:0.75rem;color:#9ca3af;">{{ $bank->account_name }}</div>
                            </td>
                            <td><code style="background:#f3f4f6;padding:2px 8px;border-radius:4px;font-size:12px;">{{ $bank->account_number }}</code></td>
                            <td>{{ $bank->branch?->name ?? 'All Branches' }}</td>
                            <td>
                                <span class="modern-badge {{ $bank->integration_type === 'api' ? 'modern-badge-info' : 'modern-badge-light' }}">
                                    {{ \App\Models\BankIntegration::integrationTypes()[$bank->integration_type] ?? $bank->integration_type }}
                                </span>
                            </td>
                            <td>
                                <span class="modern-badge {{ $bank->is_active ? 'modern-badge-success' : 'modern-badge-danger' }}">
                                    {{ $bank->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <form method="POST" action="{{ route('admin.bank-integration.settings.destroy', $bank->id) }}" style="display:inline" onsubmit="return confirm('Delete this bank account configuration?')">
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
                <div class="modern-empty-state">
                    <p>No bank accounts configured yet. Add one above.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function autoFillBankCode() {
        const select = document.getElementById('bankNameSelect');
        const option = select.options[select.selectedIndex];
        if (option.dataset.code) {
            document.getElementById('bankCodeInput').value = option.dataset.code;
        }
    }

    function toggleApiFields() {
        const type = document.getElementById('integrationType').value;
        document.getElementById('apiFields').style.display = type === 'api' ? 'block' : 'none';
    }
</script>
@endpush
@endsection