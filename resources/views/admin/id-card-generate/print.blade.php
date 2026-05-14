<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('app.student_id_cards') }} - Redemption</title>
    <style>
        @page { size: auto; margin: 8mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', 'Segoe UI', Arial, sans-serif; padding: 20px; background: #f5f5f5; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        .id-cards-grid {
            display: grid; grid-template-columns: repeat(2, 1fr);
            gap: 16px; max-width: 820px; margin: 0 auto;
        }

        .id-card {
            width: 355px; height: 215px; position: relative; page-break-inside: avoid;
            border: 2px solid #1a1a2e; border-radius: 10px; overflow: hidden; background: #fff;
        }

        /* Line-pattern header - no solid colors, just lines */
        .id-card-header {
            height: 52px; position: relative; overflow: hidden;
            border-bottom: 2px solid #1a1a2e;
            background:
                repeating-linear-gradient(
                    -45deg,
                    transparent, transparent 3px,
                    rgba(26,26,46,0.08) 3px, rgba(26,26,46,0.08) 4px
                ),
                repeating-linear-gradient(
                    45deg,
                    transparent, transparent 3px,
                    rgba(26,26,46,0.05) 3px, rgba(26,26,46,0.05) 4px
                ),
                #fff;
        }

        .id-card-header-inner {
            position: relative; z-index: 2; display: flex; align-items: center;
            gap: 8px; padding: 6px 14px; height: 100%;
        }

        .id-card-logo {
            width: 36px; height: 36px; flex-shrink: 0; border-radius: 50%;
            border: 1.5px solid #1a1a2e; overflow: hidden; background: #fff;
            display: flex; align-items: center; justify-content: center;
        }
        .id-card-logo img { width: 100%; height: 100%; object-fit: contain; padding: 2px; }
        .id-card-logo i { font-size: 14px; color: #1a1a2e; }

        .id-card-header-text h3 {
            font-size: 0.78rem; font-weight: 800; color: #1a1a2e;
            letter-spacing: 1px; text-transform: uppercase; margin: 0;
        }
        .id-card-header-text p {
            font-size: 0.55rem; color: #555; margin: 0; letter-spacing: 0.5px;
        }

        /* Horizontal line accent under header */
        .id-card-accent-line {
            height: 2px;
            background: repeating-linear-gradient(
                90deg,
                #1a1a2e 0px, #1a1a2e 8px,
                transparent 8px, transparent 12px
            );
        }

        /* Watermark */
        .id-card-watermark {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 140px; height: 140px; opacity: 0.04; pointer-events: none; z-index: 0;
        }
        .id-card-watermark img { width: 100%; height: 100%; object-fit: contain; }

        .id-card-body {
            display: flex; padding: 8px 14px; gap: 10px; position: relative; z-index: 1;
        }

        .id-card-photo {
            width: 70px; height: 84px; background: #fafafa; border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            border: 1.5px solid #ccc; flex-shrink: 0; overflow: hidden;
        }
        .id-card-photo img { width: 100%; height: 100%; object-fit: cover; }
        .id-card-photo i { color: #bbb; font-size: 1.4rem; }

        .id-card-info { flex: 1; padding-top: 2px; }
        .id-card-info-row { margin-bottom: 4px; }
        .id-card-info-label {
            font-size: 0.52rem; color: #888; text-transform: uppercase;
            letter-spacing: 0.5px; font-weight: 600;
        }
        .id-card-info-value { font-size: 0.78rem; font-weight: 700; color: #1a1a2e; }

        .id-card-footer {
            position: absolute; bottom: 0; left: 0; right: 0;
            height: 24px; padding: 0 14px; display: flex;
            justify-content: space-between; align-items: center;
            border-top: 1px dashed #ccc; background: #fafafa;
        }
        .id-card-footer span { font-size: 0.5rem; color: #888; }
        .id-card-barcode {
            font-family: 'Courier New', monospace; font-size: 0.48rem;
            color: #555; letter-spacing: 1.5px;
        }

        /* Dashed border pattern around entire card */
        .id-card-border-pattern {
            position: absolute; inset: 3px; border: 1px dashed rgba(26,26,46,0.15);
            border-radius: 7px; pointer-events: none; z-index: 3;
        }

        .no-print { text-align: center; margin: 20px 0; }
        .no-print button {
            padding: 10px 24px; background: #1a1a2e; color: #fff;
            border: none; border-radius: 8px; cursor: pointer; font-weight: 600;
        }

        @media print {
            .no-print { display: none; }
            body { padding: 0; background: #fff; }
            .id-cards-grid { gap: 8px; }
        }
    </style>
</head>
<body>
    @php
        $logoPath = \App\Models\Setting::get('school_logo');
        $logoUrl = \App\Models\Setting::getLogoUrl();
        $schoolName = \App\Models\Setting::get('school_name', 'School of Redemption');
    @endphp

    <div class="no-print"><button onclick="window.print()">{{ __('app.print') ?? 'Print ID Cards' }}</button></div>

    <div class="id-cards-grid">
        @foreach($students as $student)
            @php $idCard = $student->idCards()->first(); @endphp
            <div class="id-card">
                <div class="id-card-border-pattern"></div>

                {{-- Header with line pattern --}}
                <div class="id-card-header">
                    <div class="id-card-header-inner">
                        <div class="id-card-logo">
                            @if($logoPath && $logoUrl)
                                <img src="{{ $logoUrl }}" alt="Logo">
                            @else
                                <i class="fas fa-graduation-cap"></i>
                            @endif
                        </div>
                        <div class="id-card-header-text">
                            <h3>{{ strtoupper($schoolName) }}</h3>
                            <p>{{ __('app.student_id_cards') ?? 'Student Identity Card' }}</p>
                        </div>
                    </div>
                </div>
                <div class="id-card-accent-line"></div>

                {{-- Watermark --}}
                @if($logoPath && $logoUrl)
                <div class="id-card-watermark">
                    <img src="{{ $logoUrl }}" alt="">
                </div>
                @endif

                {{-- Body --}}
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
                            <div class="id-card-info-label">{{ __('app.name') ?? 'Name' }}</div>
                            <div class="id-card-info-value">{{ $student->first_name }} {{ $student->last_name }}</div>
                        </div>
                        <div class="id-card-info-row">
                            <div class="id-card-info-label">{{ __('app.roll_number') ?? 'Roll No' }}</div>
                            <div class="id-card-info-value">{{ $student->roll_number }}</div>
                        </div>
                        <div class="id-card-info-row">
                            <div class="id-card-info-label">{{ __('app.class_section') ?? 'Class / Section' }}</div>
                            <div class="id-card-info-value">{{ $student->classroom->name ?? '-' }} / {{ $student->section->name ?? '-' }}</div>
                        </div>
                        <div class="id-card-info-row">
                            <div class="id-card-info-label">{{ __('app.card_number') ?? 'Card No' }}</div>
                            <div class="id-card-info-value">{{ $idCard->card_number ?? 'ID-' . str_pad($student->id, 5, '0', STR_PAD_LEFT) }}</div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="id-card-footer">
                    <span>{{ __('app.valid') ?? 'Valid' }}: {{ $idCard->issue_date ?? now()->format('Y-m-d') }} - {{ $idCard->valid_until ?? now()->addYear()->format('Y-m-d') }}</span>
                    <span class="id-card-barcode">*{{ $student->admission_number ?? $student->id }}*</span>
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>
