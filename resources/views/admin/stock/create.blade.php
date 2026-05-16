@extends('layouts.admin')
@section('title', __('Add Stock Item'))
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-plus-circle me-2"></i>{{ __('Add New Stock Item') }}</h4>
    <a href="{{ route('admin.stock.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>{{ __('Back') }}</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.stock.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('Item Name') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('Item Code') }}</label>
                    <input type="text" name="code" value="{{ old('code') }}" class="form-control @error('code') is-invalid @enderror" placeholder="e.g., STA-001">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('Category') }} <span class="text-danger">*</span></label>
                    <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                        @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('Unit') }} <span class="text-danger">*</span></label>
                    <select name="unit" class="form-select @error('unit') is-invalid @enderror" required>
                        @foreach($units as $key => $label)
                        <option value="{{ $key }}" {{ old('unit') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('Initial Quantity') }} <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" value="{{ old('quantity', 0) }}" class="form-control @error('quantity') is-invalid @enderror" min="0" required>
                    @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('Minimum Stock Level') }}</label>
                    <input type="number" name="minimum_stock" value="{{ old('minimum_stock', 0) }}" class="form-control" min="0">
                    <small class="text-muted">{{ __('Alert when stock falls below this level') }}</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('Unit Price (ETB)') }}</label>
                    <input type="number" name="unit_price" value="{{ old('unit_price', 0) }}" class="form-control" min="0" step="0.01">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('Storage Location') }}</label>
                    <input type="text" name="location" value="{{ old('location') }}" class="form-control" placeholder="e.g., Store Room A, Shelf 3">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('Branch') }}</label>
                    <select name="branch_id" class="form-select">
                        <option value="">{{ __('All Branches') }}</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">{{ __('Description') }}</label>
                    <textarea name="description" rows="2" class="form-control">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>{{ __('Save Item') }}</button>
                <a href="{{ route('admin.stock.index') }}" class="btn btn-outline-secondary ms-2">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
