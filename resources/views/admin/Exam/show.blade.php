@extends('layouts.admin')
@section('content')
<div style="padding:20px;max-width:700px;">
  <h2><i class="fas fa-file-alt"></i> Exam Details</h2>
  <div style="background:#fff;padding:25px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.1);margin-top:15px;">
    <table style="width:100%;border-collapse:collapse;">
      <tr style="border-bottom:1px solid #eee;">
        <td style="padding:12px 0;font-weight:600;width:200px;color:#555;">Exam Name</td>
        <td style="padding:12px 0;">{{ $item->name }}</td>
      </tr>
      <tr style="border-bottom:1px solid #eee;">
        <td style="padding:12px 0;font-weight:600;color:#555;">Type</td>
        <td style="padding:12px 0;text-transform:capitalize;">{{ str_replace('_', ' ', $item->type) }}</td>
      </tr>
      <tr style="border-bottom:1px solid #eee;">
        <td style="padding:12px 0;font-weight:600;color:#555;">Total Marks</td>
        <td style="padding:12px 0;">{{ $item->total_marks }}</td>
      </tr>
      <tr style="border-bottom:1px solid #eee;">
        <td style="padding:12px 0;font-weight:600;color:#555;">Academic Year</td>
        <td style="padding:12px 0;">{{ $item->academicYear->name ?? '-' }}</td>
      </tr>
      <tr style="border-bottom:1px solid #eee;">
        <td style="padding:12px 0;font-weight:600;color:#555;">Term</td>
        <td style="padding:12px 0;">{{ $item->term->name ?? '-' }}</td>
      </tr>
      <tr style="border-bottom:1px solid #eee;">
        <td style="padding:12px 0;font-weight:600;color:#555;">Schedule</td>
        <td style="padding:12px 0;">
          @if($item->start_date)
            {{ $item->start_date->format('M d, Y') }} to {{ $item->end_date ? $item->end_date->format('M d, Y') : '-' }}
          @else
            Not set
          @endif
        </td>
      </tr>
      <tr style="border-bottom:1px solid #eee;">
        <td style="padding:12px 0;font-weight:600;color:#555;">Daily Time</td>
        <td style="padding:12px 0;">
          @if($item->start_time)
            {{ $item->start_time }} - {{ $item->end_time ?? '-' }}
          @else
            Not set
          @endif
        </td>
      </tr>
      @if($item->description)
      <tr>
        <td style="padding:12px 0;font-weight:600;color:#555;">Description</td>
        <td style="padding:12px 0;">{{ $item->description }}</td>
      </tr>
      @endif
    </table>
    <div style="margin-top:20px;padding-top:15px;border-top:1px solid #eee;">
      <div style="background:#ecf0f1;padding:12px;border-radius:5px;font-size:14px;color:#555;">
        <i class="fas fa-globe" style="color:#3498db;"></i>
        This exam is scheduled for <strong>all subjects</strong> across <strong>all classes</strong>.
      </div>
    </div>
    <div style="margin-top:20px;">
      <a href="{{ route('admin.exams.edit', $item->id) }}" class="btn btn-gold"><i class="fas fa-edit"></i> Edit</a>
      <a href="{{ route('admin.exams.index') }}" class="btn" style="background:#95a5a6;color:#fff;padding:8px 18px;border-radius:5px;text-decoration:none;margin-left:8px;">Back to List</a>
    </div>
  </div>
</div>
@endsection
