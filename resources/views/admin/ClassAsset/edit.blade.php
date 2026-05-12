@extends('layouts.admin')
@section('title', 'Edit Asset')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">Academics</a></li>
                    <li><a href="{{ route('admin.class-assets.index') }}">Class Assets</a></li>
                    <li class="active">Edit</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Edit Asset</h1>
            <p class="modern-page-subtitle">Update details for {{ $item->name }}</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.class-assets.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="modern-card">
        <form method="POST" action="{{ route('admin.class-assets.update', $item->id) }}">
            @csrf @method('PUT')

            {{-- Asset Information Section --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-gold">
                        <i class="fas fa-box-seam"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Asset Information</h3>
                        <p class="modern-form-section-desc">Update the asset details and classroom assignment</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="name">
                                Asset Name <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-tag modern-input-icon"></i>
                                <input type="text" name="name" id="name"
                                    class="modern-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                    value="{{ old('name', $item->name) }}"
                                    placeholder="e.g. Projector, Whiteboard, Chairs" required>
                            </div>
                            @error('name')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="classSelect">
                                Classroom <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-chalkboard modern-input-icon"></i>
                                <select name="class_id" id="classSelect"
                                    class="modern-input modern-select {{ $errors->has('class_id') ? 'is-invalid' : '' }}"
                                    required>
                                    <option value="">-- Select Classroom --</option>
                                    @foreach($classrooms as $cls)
                                    <option value="{{ $cls->id }}" {{ old('class_id', $item->class_id) == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('class_id')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="sectionSelect">
                                Section
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-layer-group modern-input-icon"></i>
                                <select name="section_id" id="sectionSelect"
                                    class="modern-input modern-select {{ $errors->has('section_id') ? 'is-invalid' : '' }}">
                                    <option value="">-- All Sections --</option>
                                </select>
                            </div>
                            @error('section_id')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="quantity">
                                Quantity <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-hashtag modern-input-icon"></i>
                                <input type="number" name="quantity" id="quantity"
                                    class="modern-input {{ $errors->has('quantity') ? 'is-invalid' : '' }}"
                                    value="{{ old('quantity', $item->quantity) }}" min="1" required>
                            </div>
                            @error('quantity')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="condition">
                                Condition <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-clipboard-check modern-input-icon"></i>
                                <select name="condition" id="condition"
                                    class="modern-input modern-select {{ $errors->has('condition') ? 'is-invalid' : '' }}"
                                    required>
                                    <option value="">-- Select --</option>
                                    <option value="new" {{ old('condition', $item->condition) === 'new' ? 'selected' : '' }}>New</option>
                                    <option value="good" {{ old('condition', $item->condition) === 'good' ? 'selected' : '' }}>Good</option>
                                    <option value="fair" {{ old('condition', $item->condition) === 'fair' ? 'selected' : '' }}>Fair</option>
                                    <option value="poor" {{ old('condition', $item->condition) === 'poor' ? 'selected' : '' }}>Poor</option>
                                    <option value="damaged" {{ old('condition', $item->condition) === 'damaged' ? 'selected' : '' }}>Damaged</option>
                                </select>
                            </div>
                            @error('condition')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="purchase_date">
                                Purchase Date
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-calendar-alt modern-input-icon"></i>
                                <input type="date" name="purchase_date" id="purchase_date"
                                    class="modern-input {{ $errors->has('purchase_date') ? 'is-invalid' : '' }}"
                                    value="{{ old('purchase_date', $item->purchase_date) }}">
                            </div>
                            @error('purchase_date')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="purchase_price">
                                Price (ETB)
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-coins modern-input-icon"></i>
                                <input type="number" step="0.01" min="0" name="purchase_price" id="purchase_price"
                                    class="modern-input {{ $errors->has('purchase_price') ? 'is-invalid' : '' }}"
                                    value="{{ old('purchase_price', $item->purchase_price) }}"
                                    placeholder="0.00">
                            </div>
                            @error('purchase_price')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="description">
                                Description
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-align-left modern-input-icon"></i>
                                <textarea name="description" id="description"
                                    class="modern-input modern-textarea {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                    rows="3"
                                    placeholder="Optional description of the asset...">{{ old('description', $item->description) }}</textarea>
                            </div>
                            @error('description')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="modern-form-actions">
                <a href="{{ route('admin.class-assets.index') }}" class="btn-modern btn-modern-ghost">
                    Cancel
                </a>
                <button type="submit" class="btn-modern btn-modern-primary">
                    <i class="fas fa-check"></i>
                    <span>Update Asset</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
/* Modern Page Layout */
.modern-page { animation: fadeSlideIn 0.4s ease-out; }

@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.modern-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.75rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.modern-page-header-left { flex: 1; }

.modern-page-header-right {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.modern-page-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0;
    letter-spacing: -0.5px;
}

.modern-page-subtitle {
    font-size: 0.9rem;
    color: #6c757d;
    margin: 0.25rem 0 0;
}

/* Breadcrumb */
.modern-breadcrumb ol {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0 0 0.5rem;
    gap: 0.5rem;
    font-size: 0.8rem;
    align-items: center;
}

.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* Card */
.modern-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

/* Form Section */
.modern-form-section { border-bottom: 1px solid #f0f0f0; }
.modern-form-section:last-of-type { border-bottom: none; }

.modern-form-section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem 2rem 0.75rem;
}

.modern-form-section-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.modern-form-section-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-form-section-icon-green { background: #ecfdf5; color: #10b981; }
.modern-form-section-icon-gold { background: #fefce8; color: #d97706; }
.modern-form-section-icon-purple { background: #faf5ff; color: #8b5cf6; }

.modern-form-section-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0;
}

.modern-form-section-desc {
    font-size: 0.82rem;
    color: #9ca3af;
    margin: 0.15rem 0 0;
}

.modern-form-section-body { padding: 1.25rem 2rem 1.75rem; }

/* Form Grid */
.modern-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
}

.modern-form-span-2 { grid-column: span 2; }

/* Form Group */
.modern-form-group { display: flex; flex-direction: column; }

.modern-form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.45rem;
    font-size: 0.88rem;
}

.modern-form-label small {
    font-weight: 400;
    color: #9ca3af;
    font-size: 0.78rem;
}

.modern-required { color: #ef4444; font-weight: 700; }

/* Input */
.modern-input-wrapper { position: relative; }

.modern-input-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 0.85rem;
    pointer-events: none;
    z-index: 1;
}

.modern-input {
    width: 100%;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    padding: 0.7rem 0.9rem 0.7rem 2.5rem;
    font-size: 0.9rem;
    color: #1a1a2e;
    background: #fff;
    transition: all 0.2s;
}

.modern-input:focus {
    outline: none;
    border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.modern-input::placeholder { color: #c5c9d2; }

.modern-input.is-invalid {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.modern-textarea {
    resize: vertical;
    min-height: 80px;
}

.modern-textarea + .modern-input-icon {
    top: 1rem;
    transform: none;
}

.modern-select {
    appearance: none;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.25rem;
    padding-right: 2.5rem;
}

.modern-form-error {
    display: block;
    color: #ef4444;
    font-size: 0.8rem;
    margin-top: 0.35rem;
    font-weight: 500;
}

/* Form Actions */
.modern-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.25rem 2rem;
    border-top: 1px solid #f0f0f0;
    background: #fafbfc;
}

/* Modern Button */
.btn-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.35rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.25s;
}

.btn-modern-primary {
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    color: #fff;
    box-shadow: 0 2px 8px rgba(67, 97, 238, 0.3);
}

.btn-modern-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(67, 97, 238, 0.4);
    color: #fff;
}

.btn-modern-outline {
    background: transparent;
    color: #6b7280;
    border: 1.5px solid #e5e7eb;
}

.btn-modern-outline:hover {
    border-color: #4361ee;
    color: #4361ee;
    background: #f8f9ff;
}

.btn-modern-ghost {
    background: transparent;
    color: #6b7280;
    border: none;
}

.btn-modern-ghost:hover {
    color: #4361ee;
    background: #f3f4f6;
}

/* Responsive */
@media (max-width: 768px) {
    .modern-page-header { flex-direction: column; align-items: stretch; }
    .modern-page-title { font-size: 1.35rem; }
    .modern-form-grid { grid-template-columns: 1fr; }
    .modern-form-span-2 { grid-column: span 1; }
    .modern-form-section-header { padding: 1.25rem 1.25rem 0.75rem; }
    .modern-form-section-body { padding: 1rem 1.25rem 1.5rem; }
    .modern-form-actions { padding: 1rem 1.25rem; flex-direction: column; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('classSelect');
    const sectionSelect = document.getElementById('sectionSelect');
    const currentSectionId = {{ $item->section_id ?? 'null' }};
    const allSections = @json($classrooms->flatMap(fn($c) => $c->sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'class_id' => $s->class_id])));

    function loadSections(classId) {
        sectionSelect.innerHTML = '<option value="">-- All Sections --</option>';
        if (!classId) return;
        const sections = allSections.filter(s => s.class_id == classId);
        if (sections.length === 0) {
            sectionSelect.innerHTML = '<option value="">No sections</option>';
            return;
        }
        sections.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.name;
            if (s.id == currentSectionId) opt.selected = true;
            sectionSelect.appendChild(opt);
        });
    }

    // Load sections for the current classroom on page load
    loadSections(classSelect.value);

    classSelect.addEventListener('change', function() {
        loadSections(this.value);
    });
});
</script>
@endpush
@endsection
