<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.temp_password.subject') }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f7;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div style="max-width:520px;margin:0 auto;padding:24px;">
        <div style="background:#ffffff;border-radius:10px;padding:32px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
            <h2 style="margin:0 0 20px;font-size:20px;color:#111827;">{{ config('app.name') }}</h2>

            <p style="margin:0 0 16px;font-size:15px;">{{ __('emails.temp_password.greeting', ['name' => $name]) }}</p>
            <p style="margin:0 0 8px;font-size:15px;">{{ __('emails.temp_password.intro') }}</p>

            <div style="font-size:24px;font-weight:bold;letter-spacing:2px;text-align:center;background:#f0f4ff;border:1px dashed #c7d2fe;border-radius:8px;padding:18px;margin:20px 0;color:#1e3a8a;">
                {{ $password }}
            </div>

            <p style="margin:0 0 8px;font-size:14px;color:#4b5563;">{{ __('emails.temp_password.advice') }}</p>
            <p style="margin:0;font-size:13px;color:#9ca3af;">{{ __('emails.temp_password.expiry') }}</p>
        </div>
    </div>
</body>
</html>
