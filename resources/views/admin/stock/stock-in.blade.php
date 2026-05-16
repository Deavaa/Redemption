@extends('layouts.admin')
@section('title', __('Stock In'))
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-plus-circle text-success me-2"></i>{{ __('Stock In - Add Stock') }}</h4>
    <a href="{{ route('admin.stock.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>{{ __('Back') }}</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.stock.store-stock-in') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('Select Item') }} <span class="text-danger">*</span></label>
                    <select name="stock_item_id" id="stockItemSelect" class="form-select @error('stock_item_id') is-invalid @enderror" required>
                        <option value="">{{ __('-- Select Item --') }}</option>
                        @foreach($items as $item)
                        <option value="{{ $item->id }}" data-unit="{{ $item->unit }}" data-price="{{ $item->unit_price }}" data-current="{{ $item->quantity }}">
                            {{ $item->name }} ({{ $item->code ?? 'No Code' }}) - Current: {{ $item->quantity }} {{ $item->unit }}
                        </option>
                        @endforeach
                    </select>
                    @error('stock_item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small id="currentStockInfo" class="text-muted mt-1 d-block"></small>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('Quantity') }} <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" id="quantityInput" value="{{ old('quantity') }}" class="form-control @error('quantity') is-invalid @enderror" min="1" required>
                    @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('Unit Price (ETB)') }}</label>
                    <input type="number" name="unit_price" id="unitPriceInput" value="{{ old('unit_price') }}" class="form-control" min="0" step="0.01">
                    <small class="text-muted">{{ __('Leave blank to use existing price') }}</small>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">{{ __('Transaction Date') }} <span class="text-danger">*</span></label>
                    <input type="date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">{{ __('Reference / Invoice No.') }}</label>
                    <input type="text" name="reference_no" value="{{ old('reference_no') }}" class="form-control" placeholder="e.g., INV-2026-001">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">&nbsp;</label>
                    <div id="newTotalInfo" class="form-control-plaintext fw-bold text-success"></div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">{{ __('Notes') }}</label>
                    <textarea name="notes" rows="2" class="form-control" placeholder="{{ __('Optional notes about this stock entry') }}">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success"><i class="fas fa-plus-circle me-1"></i>{{ __('Add Stock') }}</button>
                <a href="{{ route('admin.stock.index') }}" class="btn btn-outline-secondary ms-2">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('stockItemSelect').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const info = document.getElementById('currentStockInfo');
    if (this.value) {
        const unit = opt.dataset.unit;
        const price = opt.dataset.price;
        const current = opt.dataset.current;
        info.textContent = `Current stock: ${current} ${unit} | Unit price: ${price} ETB`;
        document.getElementById('unitPriceInput').placeholder = price;
    } else {
        info.textContent = '';
    }
});
</script>
@endsection
