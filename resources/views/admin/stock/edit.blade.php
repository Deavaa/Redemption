@extends('layouts.admin')
@section('title', __('Edit Stock Item'))
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>{{ __('Edit Stock Item') }}</h4>
    <a href="{{ route('admin.stock.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>{{ __('Back') }}</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.stock.update', $stock) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('Item Name') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $stock->name) }}" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('Item Code') }}</label>
                    <input type="text" name="code" value="{{ old('code', $stock->code) }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('Category') }} <span class="text-danger">*</span></label>
                    <select name="category" class="form-select" required>
                        @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ old('category', $stock->category) == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('Unit') }}</label>
                    <select name="unit" class="form-select">
                        @foreach($units as $key => $label)
                        <option value="{{ $key }}" {{ old('unit', $stock->unit) == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('Current Quantity') }}</label>
                    <input type="text" value="{{ $stock->quantity }} ({{ __('Use Stock In/Out to adjust') }})" class="form-control" disabled>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('Minimum Stock Level') }}</label>
                    <input type="number" name="minimum_stock" value="{{ old('minimum_stock', $stock->minimum_stock) }}" class="form-control" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('Unit Price (ETB)') }}</label>
                    <input type="number" name="unit_price" value="{{ old('unit_price', $stock->unit_price) }}" class="form-control" min="0" step="0.01">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('Storage Location') }}</label>
                    <input type="text" name="location" value="{{ old('location', $stock->location) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">{{ __('Branch') }}</label>
                    <select name="branch_id" class="form-select">
                        <option value="">{{ __('All Branches') }}</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', $stock->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" {{ old('is_active', $stock->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">{{ __('Active') }}</label>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">{{ __('Description') }}</label>
                    <textarea name="description" rows="2" class="form-control">{{ old('description', $stock->description) }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>{{ __('Update Item') }}</button>
                <a href="{{ route('admin.stock.index') }}" class="btn btn-outline-secondary ms-2">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
