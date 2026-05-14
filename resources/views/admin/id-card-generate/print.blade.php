<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student ID Cards</title>
    <style>
        @page { size: auto; margin: 10mm; }
        body { font-family: 'Inter', Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .id-cards-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; max-width: 800px; margin: 0 auto; }
        .id-card {
            width: 340px; height: 210px; background: #fff; border-radius: 12px; overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1); position: relative; page-break-inside: avoid;
        }
        .id-card-header {
            background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff;
            padding: 10px 16px; display: flex; align-items: center; gap: 10px;
        }
        .id-card-header-icon {
            width: 32px; height: 32px; background: rgba(255,255,255,0.2); border-radius: 8px;
            display: flex; align-items: center; justify-content: center; font-size: 0.9rem;
        }
        .id-card-header-text h3 { font-size: 0.82rem; font-weight: 800; margin: 0; letter-spacing: 0.5px; }
        .id-card-header-text p { font-size: 0.6rem; margin: 0; opacity: 0.8; }
        .id-card-body { display: flex; padding: 12px 16px; gap: 14px; }
        .id-card-photo {
            width: 75px; height: 90px; background: #f0f2f5; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            border: 2px dashed #d1d5db; flex-shrink: 0; overflow: hidden;
        }
        .id-card-photo img { width: 100%; height: 100%; object-fit: cover; }
        .id-card-photo i { color: #9ca3af; font-size: 1.5rem; }
        .id-card-info { flex: 1; }
        .id-card-info-row { margin-bottom: 5px; }
        .id-card-info-label { font-size: 0.6rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.3px; }
        .id-card-info-value { font-size: 0.82rem; font-weight: 600; color: #1a1a2e; }
        .id-card-footer {
            position: absolute; bottom: 0; left: 0; right: 0;
            padding: 6px 16px; background: #f8f9fa; border-top: 1px solid #e5e7eb;
            display: flex; justify-content: space-between; align-items: center;
        }
        .id-card-footer span { font-size: 0.6rem; color: #6b7280; }
        .id-card-barcode { height: 20px; font-family: monospace; font-size: 0.55rem; color: #6b7280; letter-spacing: 2px; }
        .no-print { text-align: center; margin: 20px 0; }
        .no-print button { padding: 10px 24px; background: #4361ee; color: #fff; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 0.9rem; }
        @media print { .no-print { display: none; } .id-cards-grid { gap: 10px; } }
    </style>
</head>
<body>
    @php $logoPath = DB::table('settings')->where('key', 'school_logo')->value('value'); $logoUrl = ''; if($logoPath){ if(file_exists(public_path('storage/' . $logoPath))){ $logoUrl = asset('storage/' . $logoPath); } elseif(file_exists(public_path($logoPath))){ $logoUrl = asset($logoPath); } else { $logoUrl = asset($logoPath); } } @endphp
    <div class="no-print"><button onclick="window.print()">Print ID Cards</button></div>
    <div class="id-cards-grid">
        @foreach($students as $student)
            @php $idCard = $student->idCards()->first(); @endphp
            <div class="id-card">
                <div class="id-card-header">
                    <div class="id-card-header-icon"><i class="fas fa-graduation-cap"></i></div>
                    <div class="id-card-header-text">
                        <h3>SCHOOL OF REDEMPTION</h3>
                        <p>Student Identity Card</p>
                    </div>
                </div>
                <div class="id-card-body">
                    <div class="id-card-photo">
                        @if($student->photo)
                            <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->first_name }}">
                        @else
                            <i class="fas fa-user"></i>
                        @endif
                    </div>
                    <div class="id-card-info">
                        <div class="id-card-info-row">
                            <div class="id-card-info-label">Name</div>
                            <div class="id-card-info-value">{{ $student->first_name }} {{ $student->last_name }}</div>
                        </div>
                        <div class="id-card-info-row">
                            <div class="id-card-info-label">Roll No</div>
                            <div class="id-card-info-value">{{ $student->roll_number }}</div>
                        </div>
                        <div class="id-card-info-row">
                            <div class="id-card-info-label">Class / Section</div>
                            <div class="id-card-info-value">{{ $student->classroom->name ?? '-' }} / {{ $student->section->name ?? '-' }}</div>
                        </div>
                        <div class="id-card-info-row">
                            <div class="id-card-info-label">Card No</div>
                            <div class="id-card-info-value">{{ $idCard->card_number ?? 'ID-' . str_pad($student->id, 5, '0', STR_PAD_LEFT) }}</div>
                        </div>
                    </div>
                </div>
                <div class="id-card-footer">
                    <span>Valid: {{ $idCard->issue_date ?? now()->format('Y-m-d') }} - {{ $idCard->valid_until ?? now()->addYear()->format('Y-m-d') }}</span>
                    <span class="id-card-barcode">*{{ $student->admission_number ?? $student->id }}*</span>
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>
