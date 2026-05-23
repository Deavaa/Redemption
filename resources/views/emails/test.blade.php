<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family:Arial,sans-serif;background:#f8f9fa;margin:0;padding:0;">
    <div style="max-width:600px;margin:40px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.1);">
        <div style="background:linear-gradient(135deg,#0d0d2b,#1a1a5e);padding:30px;text-align:center;">
            <h1 style="color:#c9a84c;margin:0;font-size:1.5rem;">{{ config('app.name', 'School of Redemption') }}</h1>
            <p style="color:rgba(255,255,255,0.7);margin:8px 0 0;">Email Configuration Test</p>
        </div>
        <div style="padding:30px;">
            <h2 style="color:#059669;margin:0 0 15px;">✅ Email Delivery Successful!</h2>
            <p style="color:#374151;line-height:1.6;">This is a test email from your School Management System. If you received this email, your SMTP configuration is working correctly.</p>
            <table style="width:100%;border-collapse:collapse;margin-top:20px;">
                <tr>
                    <td style="padding:10px 15px;border-bottom:1px solid #e5e7eb;color:#6b7280;font-weight:600;">Sent At</td>
                    <td style="padding:10px 15px;border-bottom:1px solid #e5e7eb;text-align:right;">{{ now()->format('F d, Y \a\t h:i A') }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 15px;border-bottom:1px solid #e5e7eb;color:#6b7280;font-weight:600;">Timezone</td>
                    <td style="padding:10px 15px;border-bottom:1px solid #e5e7eb;text-align:right;">{{ config('app.timezone', 'UTC') }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 15px;color:#6b7280;font-weight:600;">Application</td>
                    <td style="padding:10px 15px;text-align:right;">{{ config('app.name') }}</td>
                </tr>
            </table>
            <div style="margin-top:25px;padding:15px;background:#f0fdf4;border-radius:8px;border:1px solid #bbf7d0;">
                <p style="margin:0;color:#166534;font-size:0.9rem;"><strong>What this means:</strong> Your mail server (SMTP) is properly configured. Database backups and notifications will be delivered to the specified email addresses.</p>
            </div>
        </div>
        <div style="background:#f8f9fa;padding:20px;text-align:center;border-top:1px solid #e5e7eb;">
            <p style="color:#9ca3af;margin:0;font-size:0.8rem;">{{ config('app.name') }} &mdash; School Management System</p>
        </div>
    </div>
</body>
</html>
