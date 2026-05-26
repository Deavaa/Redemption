@extends('layouts.admin')
@section('title', 'Add News')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-newspaper me-2"></i>Add News</h2>
        <a href="{{ route('admin.news.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.news.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Content</label>
                    <textarea name="content" class="form-control" rows="6">{{ old('content') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Active</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                            <label class="form-check-label">Show on website</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Show Until</label>
                        <input type="datetime-local" name="show_until" class="form-control" value="{{ old('show_until') }}">
                        <small class="text-muted">Leave empty to show indefinitely</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Priority</label>
                        <input type="number" name="priority" class="form-control" value="{{ old('priority', 0) }}" min="0">
                        <small class="text-muted">Higher = shown first</small>
                    </div>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save News</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
