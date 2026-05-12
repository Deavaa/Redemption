@extends('layouts.admin')
@section('content')
<div style="padding:20px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <h2><i class="fas fa-file-alt"></i> Exam Schedule</h2>
    <a href="{{ route('admin.exams.create') }}" class="btn btn-gold"><i class="fas fa-plus"></i> Schedule Exam</a>
  </div>
  @if(session('success'))
  <div style="background:#d4edda;color:#155724;padding:10px 15px;border-radius:5px;margin-bottom:15px;">{{ session('success') }}</div>
  @endif
  <div style="background:#d5f5e3;padding:10px 15px;border-radius:5px;margin-bottom:15px;font-size:14px;color:#1e8449;">
    <i class="fas fa-info-circle"></i> All exams are scheduled for <strong>all subjects</strong> and <strong>all classes</strong> simultaneously.
  </div>
  <div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.1);">
      <thead>
        <tr style="background:#2c3e50;color:#fff;">
          <th style="padding:12px 15px;text-align:left;">#</th>
          <th style="padding:12px 15px;text-align:left;">Exam Name</th>
          <th style="padding:12px 15px;text-align:left;">Type</th>
          <th style="padding:12px 15px;text-align:center;">Marks</th>
          <th style="padding:12px 15px;text-align:left;">Academic Year</th>
          <th style="padding:12px 15px;text-align:left;">Term</th>
          <th style="padding:12px 15px;text-align:left;">Schedule</th>
          <th style="padding:12px 15px;text-align:left;">Daily Time</th>
          <th style="padding:12px 15px;text-align:center;">Status</th>
          <th style="padding:12px 15px;text-align:center;">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data as $item)
        <?php
          $today = date('Y-m-d');
          $status = 'upcoming';
          $statusColor = '#3498db';
          $statusLabel = 'Upcoming';
          if ($item->end_date && $today > $item->end_date->format('Y-m-d')) {
            $status = 'completed'; $statusColor = '#27ae60'; $statusLabel = 'Completed';
          } elseif ($item->start_date && $today >= $item->start_date->format('Y-m-d')) {
            $status = 'ongoing'; $statusColor = '#f39c12'; $statusLabel = 'Ongoing';
          }
        ?>
        <tr style="border-bottom:1px solid #eee;">
          <td style="padding:10px 15px;">{{ $item->id }}</td>
          <td style="padding:10px 15px;font-weight:600;">{{ $item->name }}</td>
          <td style="padding:10px 15px;">
            <span style="background:#ecf0f1;padding:3px 10px;border-radius:12px;font-size:13px;text-transform:capitalize;">{{ str_replace('_', ' ', $item->type) }}</span>
          </td>
          <td style="padding:10px 15px;text-align:center;font-weight:600;">{{ $item->total_marks }}</td>
          <td style="padding:10px 15px;">{{ $item->academicYear->name ?? '-' }}</td>
          <td style="padding:10px 15px;">{{ $item->term->name ?? '-' }}</td>
          <td style="padding:10px 15px;font-size:13px;">
            @if($item->start_date)
              {{ $item->start_date->format('M d, Y') }}<br>
              <span style="color:#888;">to {{ $item->end_date ? $item->end_date->format('M d, Y') : '-' }}</span>
            @else
              <span style="color:#aaa;">Not set</span>
            @endif
          </td>
          <td style="padding:10px 15px;font-size:13px;">
            @if($item->start_time)
              {{ $item->start_time }} - {{ $item->end_time ?? '-' }}
            @else
              <span style="color:#aaa;">-</span>
            @endif
          </td>
          <td style="padding:10px 15px;text-align:center;">
            <span style="background:{{ $statusColor }};color:#fff;padding:4px 12px;border-radius:12px;font-size:12px;font-weight:600;">{{ $statusLabel }}</span>
          </td>
          <td style="padding:10px 15px;text-align:center;">
            <a href="{{ route('admin.exams.edit', $item->id) }}" class="btn btn-gold" style="padding:4px 12px;font-size:13px;"><i class="fas fa-edit"></i></a>
            <form action="{{ route('admin.exams.destroy', $item->id) }}" method="POST" style="display:inline;">
              @csrf @method('DELETE')
              <button class="btn" style="background:#e74c3c;color:#fff;padding:4px 12px;font-size:13px;border:none;cursor:pointer;border-radius:4px;" onclick="return confirm('Delete this exam schedule?')"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div style="margin-top:15px;">{{ $data->links() }}</div>
</div>
@endsection
