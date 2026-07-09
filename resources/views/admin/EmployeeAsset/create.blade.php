@extends('layouts.admin')
@section('title', 'Add Employee Asset')
@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li>HR</li>
                    <li><a href="{{ route('admin.employee-assets.index') }}">Employee Assets</a></li>
                    <li class="active">Add New</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.employee-assets.index') }}" class="btn-modern btn-modern-ghost">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="modern-alert modern-alert-success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()">&times;</button>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.employee-assets.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="modern-form-section">
            <div class="modern-form-section-header">
                <div class="modern-form-section-icon modern-form-section-icon-blue">
                    <i class="fas fa-box"></i>
                </div>
                <div>
                    <h3 class="modern-form-section-title">Asset Information</h3>
                    <p class="modern-form-section-desc">Enter the asset details and assignment info</p>
                </div>
            </div>
            <div class="modern-form-section-body">
                <div class="modern-form-grid">
                    <div class="modern-form-group">
                        <label class="modern-form-label">Asset Name <span class="modern-required">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-tag modern-input-icon"></i>
                            <input type="text" name="name" class="modern-input" value="{{ old('name') }}" placeholder="Enter asset name" required>
                        </div>
                        @error('name')
                            <div class="modern-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label">Employee <span class="modern-required">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-user modern-input-icon"></i>
                            <select name="employee_id" class="modern-select" required>
                                <option value="" disabled selected>Select employee</option>
                                @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }} ({{ $emp->role ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                        </div>
                        @error('employee_id')
                            <div class="modern-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label">Quantity <span class="modern-required">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-hashtag modern-input-icon"></i>
                            <input type="number" name="quantity" class="modern-input" value="{{ old('quantity') }}" placeholder="Enter quantity" required min="1">
                        </div>
                        @error('quantity')
                            <div class="modern-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label">Condition <span class="modern-required">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-clipboard-check modern-input-icon"></i>
                            <select name="condition" class="modern-select" required>
                                <option value="" disabled selected>Select condition</option>
                                <option value="New" {{ old('condition') == 'New' ? 'selected' : '' }}>New</option>
                                <option value="Good" {{ old('condition') == 'Good' ? 'selected' : '' }}>Good</option>
                                <option value="Fair" {{ old('condition') == 'Fair' ? 'selected' : '' }}>Fair</option>
                                <option value="Poor" {{ old('condition') == 'Poor' ? 'selected' : '' }}>Poor</option>
                                <option value="Damaged" {{ old('condition') == 'Damaged' ? 'selected' : '' }}>Damaged</option>
                            </select>
                        </div>
                        @error('condition')
                            <div class="modern-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label">Issue Date <span class="modern-required">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-calendar-plus modern-input-icon"></i>
                            <input type="date" name="issue_date" class="modern-input" value="{{ old('issue_date') }}" required>
                        </div>
                        @error('issue_date')
                            <div class="modern-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label">Return Date</label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-calendar-minus modern-input-icon"></i>
                            <input type="date" name="return_date" class="modern-input" value="{{ old('return_date') }}">
                        </div>
                        @error('return_date')
                            <div class="modern-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modern-form-group modern-form-span-2">
                        <label class="modern-form-label">Description</label>
                        <textarea name="description" class="modern-textarea" rows="3" placeholder="Enter asset description">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="modern-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="modern-form-actions">
            <a href="{{ route('admin.employee-assets.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
            <button type="submit" class="btn-modern btn-modern-primary">
                <i class="fas fa-save"></i> Save Asset
            </button>
        </div>
    </form>
</div>

@push('styles')
<style>
@keyframes fadeSlideIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.modern-page{animation:fadeSlideIn .4s ease-out;padding:1.5rem}
.modern-page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;gap:1rem}
.modern-page-header-left{flex:1}
.modern-page-header-right{display:flex;align-items:center;gap:.75rem;flex-shrink:0}
.modern-breadcrumb{margin-bottom:.5rem}
.modern-breadcrumb ol{display:flex;align-items:center;list-style:none;padding:0;margin:0;gap:.25rem;font-size:.8rem}
.modern-breadcrumb li{color:#94a3b8}
.modern-breadcrumb li:not(:last-child)::after{content:'/';margin-left:.25rem;color:#cbd5e1}
.modern-breadcrumb li a{color:#64748b;text-decoration:none;transition:color .2s}
.modern-breadcrumb li a:hover{color:#4361ee}
.modern-breadcrumb li.active{color:#4361ee;font-weight:600}
.modern-form-section{background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:1.5rem}
.modern-form-section-header{display:flex;align-items:center;gap:1rem;padding:1.25rem 1.5rem;border-bottom:1px solid #f1f5f9;background:#f8fafc}
.modern-form-section-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.modern-form-section-icon-blue{background:rgba(67,97,238,.1);color:#4361ee}
.modern-form-section-icon-green{background:rgba(16,185,129,.1);color:#10b981}
.modern-form-section-icon-gold{background:rgba(245,158,11,.1);color:#f59e0b}
.modern-form-section-icon-purple{background:rgba(139,92,246,.1);color:#8b5cf6}
.modern-form-section-title{font-size:1.05rem;font-weight:600;color:#1e293b;margin:0}
.modern-form-section-desc{font-size:.8rem;color:#94a3b8;margin:.2rem 0 0}
.modern-form-section-body{padding:1.5rem}
.modern-form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1.25rem}
.modern-form-span-2{grid-column:span 2}
.modern-form-group{display:flex;flex-direction:column;gap:.375rem}
.modern-form-label{font-size:.85rem;font-weight:500;color:#475569}
.modern-required{color:#ef4444}
.modern-input-wrapper{position:relative;display:flex;align-items:center}
.modern-input-icon{position:absolute;left:.875rem;color:#94a3b8;font-size:.85rem;pointer-events:none;z-index:1}
.modern-input{width:100%;padding:.65rem .875rem .65rem 2.5rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.875rem;outline:none;transition:border-color .2s,box-shadow .2s;background:#fff;color:#1e293b}
.modern-input:focus{border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1)}
.modern-input::placeholder{color:#94a3b8}
.modern-textarea{width:100%;padding:.65rem .875rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.875rem;outline:none;transition:border-color .2s,box-shadow .2s;background:#fff;color:#1e293b;resize:vertical;font-family:inherit}
.modern-textarea:focus{border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1)}
.modern-textarea::placeholder{color:#94a3b8}
.modern-select{width:100%;padding:.65rem .875rem .65rem 2.5rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.875rem;outline:none;transition:border-color .2s,box-shadow .2s;background:#fff;color:#1e293b;appearance:none;cursor:pointer}
.modern-select:focus{border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1)}
.modern-form-error{font-size:.8rem;color:#ef4444;margin-top:.25rem}
.modern-form-actions{display:flex;justify-content:flex-end;gap:.75rem;padding-top:.5rem}
.btn-modern{display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.25rem;border-radius:8px;font-size:.875rem;font-weight:500;cursor:pointer;transition:all .2s;border:none;text-decoration:none;line-height:1.4}
.btn-modern-primary{background:#4361ee;color:#fff;box-shadow:0 1px 3px rgba(67,97,238,.3)}
.btn-modern-primary:hover{background:#3a0ca3;box-shadow:0 4px 12px rgba(67,97,238,.4)}
.btn-modern-outline{background:transparent;color:#4361ee;border:1px solid #4361ee}
.btn-modern-outline:hover{background:#4361ee;color:#fff}
.btn-modern-ghost{background:transparent;color:#64748b}
.btn-modern-ghost:hover{background:#f1f5f9;color:#1e293b}
.modern-alert{display:flex;align-items:center;gap:.75rem;padding:1rem 1.25rem;border-radius:10px;margin-bottom:1.5rem;font-size:.9rem;animation:fadeSlideIn .3s ease-out}
.modern-alert-success{background:#ecfdf5;color:#059669;border:1px solid #a7f3d0}
.modern-alert-close{margin-left:auto;background:none;border:none;font-size:1.2rem;cursor:pointer;color:inherit;opacity:.7;transition:opacity .2s}
.modern-alert-close:hover{opacity:1}
@media(max-width:768px){
.modern-page{padding:1rem}
.modern-page-header{flex-direction:column;gap:.75rem}
.modern-page-header-right{width:100%;justify-content:flex-start}
.modern-form-grid{grid-template-columns:1fr}
.modern-form-span-2{grid-column:span 1}
.modern-form-actions{flex-direction:column}
.modern-form-actions .btn-modern{width:100%;justify-content:center}
}
@media(max-width:480px){
.btn-modern{padding:.5rem 1rem;font-size:.8rem}
}
</style>
@endpush
@push('scripts')
    <script src="{{ asset('js/client-compress.js') }}"></script>
@endpush
@endsection
