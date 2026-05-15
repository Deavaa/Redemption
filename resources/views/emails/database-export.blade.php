<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('app.db_export_email_subject') }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f6f9; padding: 40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">

                    {{-- Header --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #6366f1, #4f46e5); padding: 32px 40px; text-align: center;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="text-align: center;">
                                        <div style="width: 56px; height: 56px; background: rgba(255,255,255,0.2); border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                                            <span style="font-size: 28px; color: #fff;">&#128190;</span>
                                        </div>
                                        <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: -0.3px;">
                                            {{ __('app.db_export_email_subject') }}
                                        </h1>
                                        <p style="margin: 8px 0 0; color: rgba(255,255,255,0.8); font-size: 14px;">
                                            {{ $appName }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 36px 40px;">
                            <p style="margin: 0 0 20px; font-size: 15px; color: #374151; line-height: 1.6;">
                                {{ __('app.db_export_email_greeting') }}
                            </p>

                            <p style="margin: 0 0 24px; font-size: 15px; color: #374151; line-height: 1.6;">
                                {{ __('app.db_export_email_body') }}
                            </p>

                            {{-- Backup Details Card --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f9fafb; border-radius: 10px; border: 1px solid #e5e7eb; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 20px 24px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                                                    <span style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; font-weight: 600;">{{ __('app.db_export_file_name') }}</span><br>
                                                    <span style="font-size: 14px; color: #1f2937; font-weight: 600;">{{ $fileName }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                                                    <span style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; font-weight: 600;">{{ __('app.db_export_format') }}</span><br>
                                                    <span style="font-size: 14px; color: #1f2937; font-weight: 600;">{{ strtoupper($format) }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                                                    <span style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; font-weight: 600;">{{ __('app.db_export_file_size') }}</span><br>
                                                    <span style="font-size: 14px; color: #1f2937; font-weight: 600;">{{ $fileSize }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0;">
                                                    <span style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; font-weight: 600;">{{ __('app.db_export_generated') }}</span><br>
                                                    <span style="font-size: 14px; color: #1f2937; font-weight: 600;">{{ $generatedAt }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 8px; font-size: 14px; color: #6b7280; line-height: 1.6;">
                                <strong style="color: #374151;">{{ __('app.db_export_note_label') }}</strong> {{ __('app.db_export_note_text') }}
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #f9fafb; padding: 24px 40px; border-top: 1px solid #e5e7eb; text-align: center;">
                            <p style="margin: 0 0 4px; font-size: 13px; color: #9ca3af;">
                                {{ $appName }} &mdash; {{ __('app.db_export_auto_generated') }}
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #d1d5db;">
                                &copy; {{ date('Y') }} {{ $appName }}. {{ __('app.all_rights_reserved') }}
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
