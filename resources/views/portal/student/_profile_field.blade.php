@php
    $displayValue = $value ?? null;
    $hasValue = !empty($displayValue) || $displayValue === '0' || $displayValue === 0;
@endphp
<div class="d-flex justify-content-between align-items-start py-2" style="border-bottom: 1px solid #f3f4f6; padding-left:0.25rem; padding-right:0.25rem;">
    <span style="font-size:0.82rem; color:#6b7280; font-weight:600; min-width:40%;">{{ $label }}</span>
    <span style="font-size:0.88rem; color:{{ $hasValue ? '#1a1a2e' : '#d1d5db' }}; font-weight:{{ $hasValue ? '500' : '400' }}; text-align:right;">
        {{ $hasValue ? $displayValue : '—' }}
    </span>
</div>
