<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('app.student_id_cards') }} - Redemption</title>
    <style>
        @page { size: auto; margin: 6mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            padding: 20px; background: #f0f0f0;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }

        .id-cards-grid {
            display: grid; grid-template-columns: repeat(2, 1fr);
            gap: 20px; max-width: 860px; margin: 0 auto;
        }

        /* ===== ID CARD FRONT ===== */
        .id-card {
            width: 355px; height: 215px; position: relative;
            page-break-inside: avoid; border-radius: 12px;
            overflow: hidden; background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        /* Top gradient bar */
        .id-card-top-bar {
            height: 6px;
            background: linear-gradient(90deg, #1a1a2e 0%, #2d2d5e 40%, #c9a84c 60%, #e8c95a 100%);
        }

        /* Header */
        .id-card-header {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 14px 4px; position: relative;
        }

        .id-card-logo {
            width: 42px; height: 42px; flex-shrink: 0; border-radius: 50%;
            border: 2px solid #c9a84c; overflow: hidden; background: #f8f8f8;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }
        .id-card-logo img { width: 100%; height: 100%; object-fit: contain; padding: 2px; }
        .id-card-logo .ghost-logo {
            width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #e8e8e8, #d0d0d0);
        }
        .id-card-logo .ghost-logo svg { width: 22px; height: 22px; opacity: 0.35; }

        .id-card-header-text { flex: 1; }
        .id-card-header-text h3 {
            font-size: 0.82rem; font-weight: 800; color: #1a1a2e;
            letter-spacing: 0.8px; text-transform: uppercase; margin: 0;
            line-height: 1.2;
        }
        .id-card-header-text p {
            font-size: 0.55rem; color: #777; margin: 1px 0 0;
            letter-spacing: 0.3px; font-weight: 500;
        }

        .id-card-header-badge {
            font-size: 0.42rem; font-weight: 700; color: #fff;
            background: linear-gradient(135deg, #1a1a2e, #2d2d5e);
            padding: 3px 8px; border-radius: 3px; letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Gold separator */
        .id-card-separator {
            height: 1.5px; margin: 4px 14px;
            background: linear-gradient(90deg, transparent, #c9a84c, transparent);
        }

        /* Watermark */
        .id-card-watermark {
            position: absolute; top: 50%; left: 55%; transform: translate(-50%, -50%);
            width: 150px; height: 150px; opacity: 0.035; pointer-events: none; z-index: 0;
        }
        .id-card-watermark img { width: 100%; height: 100%; object-fit: contain; }

        /* Body */
        .id-card-body {
            display: flex; padding: 6px 14px 4px; gap: 12px;
            position: relative; z-index: 1;
        }

        /* Photo with ghost */
        .id-card-photo-wrapper {
            position: relative; flex-shrink: 0;
        }
        .id-card-photo {
            width: 72px; height: 88px; background: #f5f5f5; border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            border: 1.5px solid #ddd; overflow: hidden;
        }
        .id-card-photo img { width: 100%; height: 100%; object-fit: cover; }
        .id-card-photo .ghost-photo {
            width: 100%; height: 100%;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(180deg, #e0e4f0 0%, #d0d4e0 100%);
        }
        .id-card-photo .ghost-photo svg { width: 36px; height: 36px; opacity: 0.25; }

        /* Info section */
        .id-card-info { flex: 1; padding-top: 2px; }
        .id-card-info-row { margin-bottom: 3px; display: flex; align-items: baseline; gap: 4px; }
        .id-card-info-label {
            font-size: 0.5rem; color: #999; text-transform: uppercase;
            letter-spacing: 0.4px; font-weight: 600; min-width: 52px;
        }
        .id-card-info-value { font-size: 0.74rem; font-weight: 700; color: #1a1a2e; }

        /* Blood group badge */
        .id-card-blood {
            position: absolute; bottom: 4px; right: 4px;
            width: 22px; height: 22px; border-radius: 50%;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff; font-size: 0.48rem; font-weight: 800;
            display: flex; align-items: center; justify-content: center;
            border: 1.5px solid #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        /* Footer */
        .id-card-footer {
            position: absolute; bottom: 0; left: 0; right: 0;
            height: 26px; padding: 0 14px; display: flex;
            justify-content: space-between; align-items: center;
            background: linear-gradient(90deg, #1a1a2e, #2d2d5e);
        }
        .id-card-footer span { font-size: 0.46rem; color: rgba(255,255,255,0.7); font-weight: 500; }
        .id-card-barcode {
            font-family: 'Courier New', monospace; font-size: 0.5rem;
            color: #c9a84c; letter-spacing: 2px; font-weight: 700;
        }

        /* Inner border decoration */
        .id-card-border-inner {
            position: absolute; inset: 4px;
            border: 1px solid rgba(201,168,76,0.2);
            border-radius: 9px; pointer-events: none; z-index: 3;
        }

        /* ===== ID CARD BACK ===== */
        .id-card-back {
            width: 355px; height: 215px; position: relative;
            page-break-inside: avoid; border-radius: 12px;
            overflow: hidden; background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .id-card-back .id-card-top-bar {
            height: 4px;
            background: linear-gradient(90deg, #c9a84c 0%, #e8c95a 40%, #1a1a2e 60%, #2d2d5e 100%);
        }

        .id-card-back-header {
            text-align: center; padding: 6px 14px 3px;
            border-bottom: 1px solid #eee;
        }
        .id-card-back-header h4 {
            font-size: 0.62rem; font-weight: 800; color: #1a1a2e;
            text-transform: uppercase; letter-spacing: 1px; margin: 0;
        }
        .id-card-back-header p {
            font-size: 0.42rem; color: #999; margin: 1px 0 0;
        }

        .id-card-back-body {
            padding: 5px 14px; display: flex; gap: 12px; height: 145px;
        }

        .id-card-rules {
            flex: 1; overflow: hidden;
        }
        .id-card-rules-title {
            font-size: 0.5rem; font-weight: 700; color: #1a1a2e;
            text-transform: uppercase; letter-spacing: 0.5px;
            margin-bottom: 3px; display: flex; align-items: center; gap: 4px;
        }
        .id-card-rules-title svg { width: 10px; height: 10px; }
        .id-card-rules-list {
            list-style: none; padding: 0; margin: 0;
        }
        .id-card-rules-list li {
            font-size: 0.4rem; color: #444; line-height: 1.45;
            padding: 1px 0 1px 10px; position: relative;
        }
        .id-card-rules-list li::before {
            content: ''; position: absolute; left: 0; top: 5px;
            width: 4px; height: 4px; border-radius: 50%;
            background: #c9a84c;
        }

        .id-card-contact-section {
            width: 115px; flex-shrink: 0;
            border-left: 1px solid #eee; padding-left: 10px;
        }
        .id-card-contact-title {
            font-size: 0.5rem; font-weight: 700; color: #1a1a2e;
            text-transform: uppercase; letter-spacing: 0.5px;
            margin-bottom: 3px; display: flex; align-items: center; gap: 4px;
        }
        .id-card-contact-title svg { width: 10px; height: 10px; }
        .id-card-contact-item {
            font-size: 0.4rem; color: #555; margin-bottom: 3px;
            display: flex; align-items: flex-start; gap: 3px; line-height: 1.3;
        }
        .id-card-contact-item svg { width: 8px; height: 8px; flex-shrink: 0; margin-top: 1px; opacity: 0.5; }

        /* QR placeholder */
        .id-card-qr {
            width: 38px; height: 38px; margin: 4px auto 0;
            border: 1px solid #ddd; border-radius: 4px;
            display: flex; align-items: center; justify-content: center;
            background: #fafafa;
        }
        .id-card-qr svg { width: 24px; height: 24px; opacity: 0.3; }

        /* Back footer */
        .id-card-back-footer {
            position: absolute; bottom: 0; left: 0; right: 0;
            height: 22px; display: flex; align-items: center; justify-content: center;
            background: #fafafa; border-top: 1px solid #eee;
        }
        .id-card-back-footer span {
            font-size: 0.38rem; color: #aaa; letter-spacing: 0.3px;
        }

        .id-card-back-border-inner {
            position: absolute; inset: 4px;
            border: 1px solid rgba(201,168,76,0.15);
            border-radius: 9px; pointer-events: none; z-index: 3;
        }

        /* Print button */
        .no-print { text-align: center; margin: 20px 0; }
        .no-print button {
            padding: 12px 28px; background: #1a1a2e; color: #fff;
            border: none; border-radius: 8px; cursor: pointer; font-weight: 600;
            font-size: 14px; transition: background 0.2s;
        }
        .no-print button:hover { background: #2d2d5e; }

        @media print {
            .no-print { display: none; }
            body { padding: 0; background: #fff; }
            .id-cards-grid { gap: 6px; }
            .id-card, .id-card-back { box-shadow: none; }
        }
    </style>
</head>
<body>
    @php
        $logoUrl = \App\Models\Setting::getLogoUrl();
        $schoolName = \App\Models\Setting::get('school_name', 'School of Redemption');
        $schoolPhone = \App\Models\Setting::get('school_phone', '');
        $schoolEmail = \App\Models\Setting::get('school_email', '');
        $schoolAddress = \App\Models\Setting::get('school_address', '');
        $schoolWebsite = \App\Models\Setting::get('school_website', '');
        $hasLogo = !empty($logoUrl);
    @endphp

    {{-- Ghost SVG definitions --}}
    <svg style="display:none;">
        <defs>
            <symbol id="ghost-person" viewBox="0 0 24 24">
                <circle cx="12" cy="8" r="4" fill="currentColor"/>
                <path d="M12 14c-5 0-8 2.5-8 5v1h16v-1c0-2.5-3-5-8-5z" fill="currentColor"/>
            </symbol>
            <symbol id="ghost-school" viewBox="0 0 24 24">
                <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z" fill="currentColor"/>
            </symbol>
            <symbol id="icon-shield" viewBox="0 0 24 24">
                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z" fill="currentColor" opacity="0.8"/>
            </symbol>
            <symbol id="icon-phone" viewBox="0 0 24 24">
                <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z" fill="currentColor"/>
            </symbol>
            <symbol id="icon-email" viewBox="0 0 24 24">
                <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" fill="currentColor"/>
            </symbol>
            <symbol id="icon-location" viewBox="0 0 24 24">
                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="currentColor"/>
            </symbol>
            <symbol id="icon-web" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" fill="currentColor"/>
            </symbol>
            <symbol id="icon-qr" viewBox="0 0 24 24">
                <path d="M3 11h8V3H3v8zm2-6h4v4H5V5zM3 21h8v-8H3v8zm2-6h4v4H5v-4zM13 3v8h8V3h-8zm6 6h-4V5h4v4zM13 13h2v2h-2zM15 15h2v2h-2zM13 17h2v2h-2zM17 13h2v2h-2zM19 15h2v2h-2zM17 17h2v4h-2zM13 19h2v2h-2zM15 15h2v4h-2z" fill="currentColor"/>
            </symbol>
        </defs>
    </svg>

    <div class="no-print"><button onclick="window.print()">{{ __('app.print') ?? 'Print ID Cards' }}</button></div>

    <div class="id-cards-grid">
        @foreach($students as $student)
            @php $idCard = $student->idCards()->first(); @endphp

            {{-- ===== FRONT SIDE ===== --}}
            <div class="id-card">
                <div class="id-card-border-inner"></div>
                <div class="id-card-top-bar"></div>

                {{-- Header --}}
                <div class="id-card-header">
                    <div class="id-card-logo">
                        @if($hasLogo)
                            <img src="{{ $logoUrl }}" alt="Logo" onerror="this.style.display='none';this.parentElement.innerHTML='<div class=\'ghost-logo\'><svg><use href=\'#ghost-school\'/></svg></div>';">
                        @else
                            <div class="ghost-logo"><svg><use href="#ghost-school"/></svg></div>
                        @endif
                    </div>
                    <div class="id-card-header-text">
                        <h3>{{ strtoupper($schoolName) }}</h3>
                        <p>{{ __('app.student_id_cards') ?? 'Student Identity Card' }}</p>
                    </div>
                    <div class="id-card-header-badge">{{ __('app.academic_year') ?? 'Academic Year' }} {{ now()->year }}</div>
                </div>

                <div class="id-card-separator"></div>

                {{-- Watermark --}}
                @if($hasLogo)
                <div class="id-card-watermark">
                    <img src="{{ $logoUrl }}" alt="" onerror="this.style.display='none';">
                </div>
                @endif

                {{-- Body --}}
                <div class="id-card-body">
                    <div class="id-card-photo-wrapper">
                        <div class="id-card-photo">
                            @if($student->photo)
                                <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->first_name }}" onerror="this.parentElement.innerHTML='<div class=\'ghost-photo\'><svg><use href=\'#ghost-person\'/></svg></div>';">
                            @else
                                <div class="ghost-photo"><svg><use href="#ghost-person"/></svg></div>
                            @endif
                        </div>
                        @if($student->blood_group)
                        <div class="id-card-blood">{{ $student->blood_group }}</div>
                        @endif
                    </div>
                    <div class="id-card-info">
                        <div class="id-card-info-row">
                            <span class="id-card-info-label">{{ __('app.name') ?? 'Name' }}</span>
                            <span class="id-card-info-value">{{ $student->first_name }} {{ $student->last_name }}</span>
                        </div>
                        <div class="id-card-info-row">
                            <span class="id-card-info-label">{{ __('app.roll_number') ?? 'Roll No' }}</span>
                            <span class="id-card-info-value">{{ $student->roll_number ?? '-' }}</span>
                        </div>
                        <div class="id-card-info-row">
                            <span class="id-card-info-label">{{ __('app.class_section') ?? 'Class/Sec' }}</span>
                            <span class="id-card-info-value">{{ $student->classroom->name ?? '-' }} / {{ $student->section->name ?? '-' }}</span>
                        </div>
                        <div class="id-card-info-row">
                            <span class="id-card-info-label">{{ __('app.gender') ?? 'Gender' }}</span>
                            <span class="id-card-info-value">{{ ucfirst($student->gender ?? '-') }}</span>
                        </div>
                        <div class="id-card-info-row">
                            <span class="id-card-info-label">{{ __('app.card_number') ?? 'Card No' }}</span>
                            <span class="id-card-info-value">{{ $idCard->card_number ?? 'ID-' . str_pad($student->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        @if($student->guardian_phone)
                        <div class="id-card-info-row">
                            <span class="id-card-info-label">{{ __('app.guardian') ?? 'Guardian' }}</span>
                            <span class="id-card-info-value">{{ $student->guardian_phone }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div class="id-card-footer">
                    <span>{{ __('app.valid') ?? 'Valid' }}: {{ $idCard->issue_date ?? now()->format('Y-m-d') }} — {{ $idCard->valid_until ?? now()->addYear()->format('Y-m-d') }}</span>
                    <span class="id-card-barcode">*{{ $student->admission_number ?? $student->id }}*</span>
                </div>
            </div>

            {{-- ===== BACK SIDE ===== --}}
            <div class="id-card-back">
                <div class="id-card-back-border-inner"></div>
                <div class="id-card-top-bar"></div>

                <div class="id-card-back-header">
                    <h4>{{ strtoupper($schoolName) }}</h4>
                    <p>{{ __('app.id_card_rules_title') ?? 'Rules & Regulations' }}</p>
                </div>

                <div class="id-card-back-body">
                    {{-- Rules Section --}}
                    <div class="id-card-rules">
                        <div class="id-card-rules-title">
                            <svg><use href="#icon-shield"/></svg>
                            {{ __('app.id_card_rules') ?? 'ID Card Rules' }}
                        </div>
                        <ul class="id-card-rules-list">
                            <li>{{ __('app.rule_1') ?? 'This card must be worn visibly at all times while on school premises.' }}</li>
                            <li>{{ __('app.rule_2') ?? 'Loss of this card must be reported immediately to the school office.' }}</li>
                            <li>{{ __('app.rule_3') ?? 'A replacement fee will be charged for lost or damaged cards.' }}</li>
                            <li>{{ __('app.rule_4') ?? 'This card is non-transferable and remains school property.' }}</li>
                            <li>{{ __('app.rule_5') ?? 'Must be surrendered upon graduation, withdrawal, or expulsion.' }}</li>
                        </ul>

                        <div class="id-card-rules-title" style="margin-top:4px;">
                            <svg><use href="#icon-shield"/></svg>
                            {{ __('app.school_rules') ?? 'School Rules' }}
                        </div>
                        <ul class="id-card-rules-list">
                            <li>{{ __('app.srule_1') ?? 'Maintain punctuality and regular attendance.' }}</li>
                            <li>{{ __('app.srule_2') ?? 'Wear proper school uniform at all times.' }}</li>
                            <li>{{ __('app.srule_3') ?? 'Respect teachers, staff, and fellow students.' }}</li>
                            <li>{{ __('app.srule_4') ?? 'No electronic devices allowed during class hours.' }}</li>
                            <li>{{ __('app.srule_5') ?? 'Maintain discipline and follow school code of conduct.' }}</li>
                        </ul>
                    </div>

                    {{-- Contact Section --}}
                    <div class="id-card-contact-section">
                        <div class="id-card-contact-title">
                            <svg><use href="#icon-phone"/></svg>
                            {{ __('app.contact_info') ?? 'Contact' }}
                        </div>
                        @if($schoolPhone)
                        <div class="id-card-contact-item">
                            <svg><use href="#icon-phone"/></svg>
                            {{ $schoolPhone }}
                        </div>
                        @endif
                        @if($schoolEmail)
                        <div class="id-card-contact-item">
                            <svg><use href="#icon-email"/></svg>
                            {{ $schoolEmail }}
                        </div>
                        @endif
                        @if($schoolAddress)
                        <div class="id-card-contact-item">
                            <svg><use href="#icon-location"/></svg>
                            {{ $schoolAddress }}
                        </div>
                        @endif
                        @if($schoolWebsite)
                        <div class="id-card-contact-item">
                            <svg><use href="#icon-web"/></svg>
                            {{ $schoolWebsite }}
                        </div>
                        @endif

                        {{-- QR Code placeholder --}}
                        <div class="id-card-qr">
                            <svg><use href="#icon-qr"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Back Footer --}}
                <div class="id-card-back-footer">
                    <span>{{ $schoolName }} &bull; {{ __('app.id_card_issued') ?? 'Issued by the school administration' }}</span>
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>
