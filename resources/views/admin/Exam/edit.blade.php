@extends('layouts.admin')
@section('content')
<div style="padding:20px;">
  <h2><i class="fas fa-edit"></i> Edit Exam</h2>
  <p style="color:#888;margin:5px 0 15px;font-size:14px;">This exam applies to <strong>all subjects</strong> and <strong>all classes</strong>.</p>
  <div style="background:#fff;padding:25px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.1);">
    <form method="POST" action="{{ route('admin.exams.update', $item->id) }}">
      @csrf @method('PUT')
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
        <div>
          <label style="display:block;font-weight:600;margin-bottom:5px;">Exam Name *</label>
          <input type="text" name="name" value="{{ old('name', $item->name) }}" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;box-sizing:border-box;">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
          <div>
            <label style="display:block;font-weight:600;margin-bottom:5px;">Type *</label>
            <select name="type" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;box-sizing:border-box;">
              <option value="">-- Select --</option>
              <option value="quiz" {{ old('type', $item->type)=='quiz'?'selected':'' }}>Quiz</option>
              <option value="test" {{ old('type', $item->type)=='test'?'selected':'' }}>Test</option>
              <option value="mid_term" {{ old('type', $item->type)=='mid_term'?'selected':'' }}>Mid-Term Exam</option>
              <option value="final_exam" {{ old('type', $item->type)=='final_exam'?'selected':'' }}>Final Exam</option>
              <option value="assignment" {{ old('type', $item->type)=='assignment'?'selected':'' }}>Assignment</option>
              <option value="other" {{ old('type', $item->type)=='other'?'selected':'' }}>Other</option>
            </select>
          </div>
          <div>
            <label style="display:block;font-weight:600;margin-bottom:5px;">Total Marks *</label>
            <input type="number" name="total_marks" value="{{ old('total_marks', $item->total_marks) }}" required min="0" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;box-sizing:border-box;">
          </div>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
        <div>
          <label style="display:block;font-weight:600;margin-bottom:5px;">Academic Year *</label>
          <select name="academic_year_id" id="exam_ay" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;box-sizing:border-box;">
            <option value="">-- Select Year --</option>
            @foreach($academicYears as $ay)
            <option value="{{ $ay->id }}" {{ old('academic_year_id', $item->academic_year_id)==$ay->id?'selected':'' }}>{{ $ay->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label style="display:block;font-weight:600;margin-bottom:5px;">Term *</label>
          <select name="term_id" id="exam_term" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;box-sizing:border-box;">
            <option value="">-- Loading --</option>
          </select>
        </div>
      </div>
      <div style="background:#ecf0f1;padding:15px 18px;border-radius:6px;margin-bottom:15px;">
        <h4 style="margin:0 0 12px 0;color:#2c3e50;font-size:15px;"><i class="fas fa-calendar-alt"></i> Exam Schedule</h4>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;">
          <div>
            <label style="display:block;font-weight:600;margin-bottom:5px;font-size:13px;">Start Date *</label>
            <input type="date" name="start_date" value="{{ old('start_date', $item->start_date ? $item->start_date->format('Y-m-d') : '') }}" required style="width:100%;padding:9px;border:1px solid #ddd;border-radius:5px;box-sizing:border-box;">
          </div>
          <div>
            <label style="display:block;font-weight:600;margin-bottom:5px;font-size:13px;">End Date *</label>
            <input type="date" name="end_date" value="{{ old('end_date', $item->end_date ? $item->end_date->format('Y-m-d') : '') }}" required style="width:100%;padding:9px;border:1px solid #ddd;border-radius:5px;box-sizing:border-box;">
          </div>
          <div>
            <label style="display:block;font-weight:600;margin-bottom:5px;font-size:13px;">Start Time</label>
            <input type="time" name="start_time" value="{{ old('start_time', $item->start_time) }}" style="width:100%;padding:9px;border:1px solid #ddd;border-radius:5px;box-sizing:border-box;">
          </div>
          <div>
            <label style="display:block;font-weight:600;margin-bottom:5px;font-size:13px;">End Time</label>
            <input type="time" name="end_time" value="{{ old('end_time', $item->end_time) }}" style="width:100%;padding:9px;border:1px solid #ddd;border-radius:5px;box-sizing:border-box;">
          </div>
        </div>
      </div>
      <div style="margin-bottom:15px;">
        <label style="display:block;font-weight:600;margin-bottom:5px;">Description / Instructions</label>
        <textarea name="description" rows="2" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;box-sizing:border-box;">{{ old('description', $item->description) }}</textarea>
      </div>
      <div style="display:flex;gap:10px;">
        <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Update Exam</button>
        <a href="{{ route('admin.exams.index') }}" class="btn" style="background:#95a5a6;color:#fff;padding:8px 18px;border-radius:5px;text-decoration:none;">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  var allTerms = {!! $allTerms->toJson() !!};
  var selAY = document.getElementById('exam_ay');
  var selTerm = document.getElementById('exam_term');

  function filterTerms(ayId) {
    selTerm.innerHTML = '<option value="">-- Select Term --</option>';
    if (!ayId) return;
    for (var i = 0; i < allTerms.length; i++) {
      if (allTerms[i].academic_year_id == ayId) {
        var opt = document.createElement('option');
        opt.value = allTerms[i].id;
        opt.textContent = allTerms[i].name;
        selTerm.appendChild(opt);
      }
    }
  }

  selAY.addEventListener('change', function(){ filterTerms(this.value); });

  // Pre-select current values
  selAY.value = '{{ $item->academic_year_id }}';
  filterTerms(selAY.value);
  selTerm.value = '{{ $item->term_id }}';
})();
</script>
@endpush
