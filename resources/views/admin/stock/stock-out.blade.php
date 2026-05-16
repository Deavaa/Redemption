@extends('layouts.admin')
@section('title', __('Stock Out / Issue'))
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-minus-circle text-warning me-2"></i>{{ __('Stock Out - Issue Materials') }}</h4>
    <a href="{{ route('admin.stock.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>{{ __('Back') }}</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.stock.store-stock-out') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('Select Item') }} <span class="text-danger">*</span></label>
                    <select name="stock_item_id" id="stockItemSelect" class="form-select @error('stock_item_id') is-invalid @enderror" required>
                        <option value="">{{ __('-- Select Item --') }}</option>
                        @foreach($items as $item)
                        <option value="{{ $item->id }}" data-unit="{{ $item->unit }}" data-price="{{ $item->unit_price }}" data-current="{{ $item->quantity }}" data-item-name="{{ $item->name }}">
                            {{ $item->name }} ({{ $item->code ?? 'No Code' }}) - Available: {{ $item->quantity }} {{ $item->unit }}
                        </option>
                        @endforeach
                    </select>
                    @error('stock_item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small id="availableInfo" class="text-muted mt-1 d-block"></small>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('Quantity') }} <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" id="quantityInput" value="{{ old('quantity') }}" class="form-control @error('quantity') is-invalid @enderror" min="1" required>
                    @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('Reason') }} <span class="text-danger">*</span></label>
                    <select name="reason" id="reasonSelect" class="form-select @error('reason') is-invalid @enderror" required>
                        @foreach($outReasons as $key => $label)
                        <option value="{{ $key }}" {{ old('reason') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Recipient - shown when reason is issue_employee or issue_class --}}
                <div class="col-md-6" id="recipientField">
                    <label class="form-label fw-bold">{{ __('Recipient') }} <span class="text-danger">*</span></label>
                    <select name="recipient_id" id="recipientSelect" class="form-select">
                        <option value="">{{ __('-- Select --') }}</option>
                    </select>
                    <small id="recipientHint" class="text-muted"></small>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('Transaction Date') }} <span class="text-danger">*</span></label>
                    <input type="date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" class="form-control" required>
                </div>
                <div class="col-md-3" id="remainingField">
                    <label class="form-label fw-bold">&nbsp;</label>
                    <div id="remainingInfo" class="form-control-plaintext fw-bold"></div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">{{ __('Notes') }}</label>
                    <textarea name="notes" rows="2" class="form-control" placeholder="{{ __('Optional notes about this stock issue') }}">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-warning"><i class="fas fa-minus-circle me-1"></i>{{ __('Issue Stock') }}</button>
                <a href="{{ route('admin.stock.index') }}" class="btn btn-outline-secondary ms-2">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>

<script>
const employees = @json($employees);
const classrooms = @json($classrooms);

function updateRecipientField() {
    const reason = document.getElementById('reasonSelect').value;
    const field = document.getElementById('recipientField');
    const select = document.getElementById('recipientSelect');
    const hint = document.getElementById('recipientHint');

    select.innerHTML = '<option value="">{{ __("-- Select --") }}</option>';

    if (reason === 'issue_employee') {
        field.style.display = '';
        employees.forEach(e => {
            const opt = document.createElement('option');
            opt.value = e.id;
            opt.textContent = e.name + (e.role ? ' (' + e.role + ')' : '');
            select.appendChild(opt);
        });
        hint.textContent = '{{ __("Select the employee receiving this item") }}';
    } else if (reason === 'issue_class') {
        field.style.display = '';
        classrooms.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.name + (c.section ? ' - ' + c.section : '');
            select.appendChild(opt);
        });
        hint.textContent = '{{ __("Select the classroom receiving this item") }}';
    } else {
        field.style.display = 'none';
        hint.textContent = '';
    }
}

document.getElementById('reasonSelect').addEventListener('change', updateRecipientField);
updateRecipientField();

document.getElementById('stockItemSelect').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const info = document.getElementById('availableInfo');
    if (this.value) {
        info.textContent = `Available: ${opt.dataset.current} ${opt.dataset.unit} | Unit price: ${opt.dataset.price} ETB`;
    } else {
        info.textContent = '';
    }
    updateRemaining();
});

document.getElementById('quantityInput').addEventListener('input', updateRemaining);

function updateRemaining() {
    const itemSel = document.getElementById('stockItemSelect');
    const qtyInput = document.getElementById('quantityInput');
    const remaining = document.getElementById('remainingInfo');

    if (itemSel.value && qtyInput.value) {
        const opt = itemSel.options[itemSel.selectedIndex];
        const current = parseInt(opt.dataset.current);
        const qty = parseInt(qtyInput.value);
        const after = current - qty;
        remaining.textContent = `Remaining: ${after} ${opt.dataset.unit}`;
        remaining.className = 'form-control-plaintext fw-bold ' + (after < 0 ? 'text-danger' : (after <= parseInt(opt.dataset.min || 0) ? 'text-warning' : 'text-success'));
    }
}
</script>
@endsection
