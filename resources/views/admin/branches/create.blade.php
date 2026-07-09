@extends('layouts.admin')
@section('title','Add Branch')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0"><i class="fas fa-map-marker-alt me-2 text-danger"></i>Add New Branch</h4>
<a href="{{route('admin.branches.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{session('success')}}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
<div class="sc">
<form method="POST" action="{{route('admin.branches.store')}}">
@csrf
<div class="row">
<div class="col-lg-8">
<div class="card border-0 shadow-sm mb-4">
<div class="card-header bg-danger bg-opacity-10 border-0"><h5 class="mb-0"><i class="fas fa-building text-danger me-2"></i>Branch Information</h5></div>
<div class="card-body">
<div class="row g-3">
<div class="col-md-8">
<label class="form-label fw-semibold">Branch Name <span class="text-danger">*</span></label>
<div class="input-group"><span class="input-group-text"><i class="fas fa-tag"></i></span>
<input type="text" name="name" class="form-control" placeholder="e.g. Main Campus" required value="{{old('name')}}"></div>
</div>
<div class="col-md-4">
<label class="form-label fw-semibold">Display Order</label>
<div class="input-group"><span class="input-group-text"><i class="fas fa-sort-numeric-down"></i></span>
<input type="number" name="order" class="form-control" min="0" value="{{old('order',0)}}"></div>
</div>
</div>
<div class="row g-3 mt-1">
<div class="col-md-6">
<label class="form-label fw-semibold">Phone</label>
<div class="input-group"><span class="input-group-text"><i class="fas fa-phone"></i></span>
<input type="tel" name="phone" class="form-control" placeholder="+251-XX-XXX-XXXX" value="{{old('phone')}}"></div>
</div>
<div class="col-md-6">
<label class="form-label fw-semibold">Email</label>
<div class="input-group"><span class="input-group-text"><i class="fas fa-envelope"></i></span>
<input type="email" name="email" class="form-control" placeholder="branch@school.com" value="{{old('email')}}"></div>
</div>
</div>
<div class="row g-3 mt-1">
<div class="col-12">
<label class="form-label fw-semibold">Branch Principal</label>
<div class="d-flex gap-2">
<div class="flex-grow-1">
<div class="input-group">
<span class="input-group-text"><i class="fas fa-user-tie"></i></span>
<select name="principal_id" id="principalSelect" class="form-select">
<option value="">-- Select Principal --</option>
@foreach($teachers as $t)
<option value="{{$t->id}}" {{old('principal_id')==$t->id?'selected':''}}>{{$t->full_name}} @if($t->department)({{$t->department}})@endif</option>
@endforeach
</select>
<button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addTeacherModal" title="Add New Teacher"><i class="fas fa-plus"></i></button>
</div>
</div>
</div>
<small class="text-muted">Select the branch principal or add a new teacher</small>
</div>
</div>
<div class="row g-3 mt-1">
<div class="col-12">
<label class="form-label fw-semibold">Full Address</label>
<div class="input-group"><span class="input-group-text"><i class="fas fa-map-pin"></i></span>
<textarea name="address" class="form-control" rows="2" placeholder="Full physical address">{{old('address')}}</textarea></div>
</div>
</div>
</div>
</div>
<div class="card border-0 shadow-sm mb-4">
<div class="card-header bg-primary bg-opacity-10 border-0"><h5 class="mb-0"><i class="fas fa-globe text-primary me-2"></i>Location and Map</h5></div>
<div class="card-body">
<div class="row g-3">
<div class="col-md-6">
<label class="form-label fw-semibold">Latitude</label>
<div class="input-group"><span class="input-group-text"><i class="fas fa-arrows-alt-v"></i></span>
<input type="text" name="gps_lat" class="form-control" id="latInput" placeholder="e.g. 9.0222" value="{{old('gps_lat')}}" oninput="updateMapPreview()"></div>
</div>
<div class="col-md-6">
<label class="form-label fw-semibold">Longitude</label>
<div class="input-group"><span class="input-group-text"><i class="fas fa-arrows-alt-h"></i></span>
<input type="text" name="gps_lng" class="form-control" id="lngInput" placeholder="e.g. 38.7469" value="{{old('gps_lng')}}" oninput="updateMapPreview()"></div>
</div>
<div class="col-12">
<label class="form-label fw-semibold">Google Maps Embed URL or Iframe Code</label>
<div class="input-group"><span class="input-group-text"><i class="fab fa-google"></i></span>
<input type="text" name="map_embed_url" class="form-control" id="mapUrl" placeholder="Paste embed URL or full iframe code" value="{{old('map_embed_url')}}" oninput="updateMapPreview()"></div>
<small class="text-muted">On Google Maps: click Share → Embed a map → copy the iframe code or just the src URL</small>
</div>
<div class="col-12" id="mapPreviewContainer" style="display:none">
<label class="form-label fw-semibold">Map Preview</label>
<div class="ratio ratio-21x9 rounded border overflow-hidden">
<iframe id="mapPreview" src="" width="100%" height="300" style="border:0" allowfullscreen loading="lazy"></iframe>
</div>
</div>
</div>
</div>
</div>
<div class="card border-0 shadow-sm mb-4">
<div class="card-header bg-success bg-opacity-10 border-0"><h5 class="mb-0"><i class="fas fa-cog text-success me-2"></i>Settings</h5></div>
<div class="card-body">
<div class="row g-3">
<div class="col-md-6">
<div class="form-check form-switch mt-2">
<input type="checkbox" class="form-check-input" name="is_active" id="isActive" value="1" {{old('is_active',1)?'checked':''}}>
<label class="form-check-label fw-semibold" for="isActive"><i class="fas fa-toggle-on me-1 text-success"></i>Active Branch</label>
<div class="form-text">Inactive branches hidden from website</div>
</div>
</div>
<div class="col-md-6">
<div class="form-check form-switch mt-2">
<input type="checkbox" class="form-check-input" name="is_headquarters" id="isHQ" value="1" {{old('is_headquarters')?'checked':''}}>
<label class="form-check-label fw-semibold" for="isHQ"><i class="fas fa-star me-1 text-warning"></i>Main Headquarters</label>
<div class="form-text">Mark as the main campus</div>
</div>
</div>
</div>
</div>
</div>
<div class="mb-4">
<button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Save Branch</button>
<a href="{{route('admin.branches.index')}}" class="btn btn-secondary px-4"><i class="fas fa-times me-2"></i>Cancel</a>
</div>
</div>
<div class="col-lg-4">
<div class="card border-0 shadow-sm bg-light">
<div class="card-body text-center">
<i class="fas fa-school fa-3x text-danger mb-3 opacity-50"></i>
<h6>Quick Help</h6>
<p class="text-muted small mb-2">Add a new school branch with a principal assigned.</p>
<hr>
<h6 class="text-start">Tips:</h6>
<ul class="text-muted small text-start">
<li><i class="fas fa-check text-success me-1"></i>Select principal from existing teachers</li>
<li><i class="fas fa-check text-success me-1"></i>Click + to add a new teacher as principal</li>
<li><i class="fas fa-check text-success me-1"></i>Only one branch should be headquarters</li>
</ul>
</div>
</div>
</div>
</div>
</form>
</div>
<div class="modal fade" id="addTeacherModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header bg-primary text-white">
<h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New Teacher</h5>
<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<form id="addTeacherForm">
<div class="modal-body">
<div class="row g-3">
<div class="col-md-12"><label class="form-label">Full Name *</label><input type="text" name="full_name" class="form-control" required></div>
<div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
<div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
<div class="col-md-6"><label class="form-label">Qualification</label><input type="text" name="qualification" class="form-control"></div>
<div class="col-md-6"><label class="form-label">Department</label><input type="text" name="department" class="form-control"></div>
</div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save and Select</button>
</div>
</form>
</div>
</div>
</div>
@section('scripts')
<script>
/**
 * Extract the src URL from an <iframe> embed code,
 * or return the value as-is if it is already a plain URL.
 */
function extractIframeSrc(value) {
    if (!value || !value.trim()) return '';
    value = value.trim();
    var match = value.match(/<iframe[^>]+src=["'](https?:\/\/[^"']+)["']/i);
    if (match) return match[1];
    return value;
}

function updateMapPreview(){
    var raw=document.getElementById('mapUrl').value;
    var url=extractIframeSrc(raw);
    // Auto-replace field with extracted URL
    if (url !== raw.trim() && url) {
        document.getElementById('mapUrl').value = url;
    }
    var lat=document.getElementById('latInput').value.trim();
    var lng=document.getElementById('lngInput').value.trim();
    var c=document.getElementById('mapPreviewContainer');
    var f=document.getElementById('mapPreview');
    if(url){f.src=url;c.style.display='block';}
    else if(lat&&lng){f.src='https://maps.google.com/maps?q='+lat+','+lng+'&z=15&output=embed';c.style.display='block';}
    else{c.style.display='none';}
}

// Auto-extract on paste
document.getElementById('mapUrl').addEventListener('paste', function() {
    setTimeout(updateMapPreview, 50);
});

 $('#addTeacherForm').on('submit',function(e){
    e.preventDefault();
    var btn=$(this).find('button[type=submit]').prop('disabled',true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
    $.ajax({
        url:'{{url("admin/teachers")}}',
        method:'POST',
        data:$(this).serialize()+'&_token={{csrf_token()}}',
        success:function(res){
            var sel=$('#principalSelect');
            sel.append('<option value="'+res.id+'" selected>'+res.full_name+(res.department?' ('+res.department+')':'')+'</option>');
            sel.trigger('change');
            bootstrap.Modal.getInstance(document.getElementById('addTeacherModal')).hide();
            $('#addTeacherForm')[0].reset();
            toastr.success(res.full_name+' added as principal');
        },
        error:function(xhr){
            var msg=xhr.responseJSON&&xhr.responseJSON.message?xhr.responseJSON.message:'Error saving teacher';
            toastr.error(msg);
        },
        complete:function(){btn.prop('disabled',false).html('<i class="fas fa-save me-1"></i>Save and Select');}
    });
});
</script>
@push('scripts')
    <script src="{{ asset('js/client-compress.js') }}"></script>
@endpush
@endsection
@endsection