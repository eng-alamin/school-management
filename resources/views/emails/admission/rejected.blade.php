<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admission Application Update</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f4f4f5; padding:30px; margin:0;">
    <div style="max-width:520px;margin:0 auto;background:#fff;border-radius:12px;padding:30px;">
        <h2 style="color:#dc2626;margin-top:0;">Admission Application Update</h2>

        <p>Dear {{ $admission->guardian_name ?? $admission->applicant_name }},</p>

        <p>
            We regret to inform you that the admission application submitted for
            <strong>{{ $admission->applicant_name }}</strong> has not been approved at this time.
        </p>

        @if($admission->rejection_reason)
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:15px;margin:20px 0;">
                <strong>Reason:</strong>
                <p style="margin:8px 0 0;">{{ $admission->rejection_reason }}</p>
            </div>
        @endif

        <p>
            If you believe some information needs correction, you can update your application
            and resubmit it for review using the button below.
        </p>

        <div style="text-align:center;margin:28px 0;">
            <a href="{{ $editUrl }}"
               style="background:#dc2626;color:#fff;text-decoration:none;padding:12px 28px;border-radius:8px;font-weight:bold;display:inline-block;">
                Update Your Application
            </a>
        </div>

        <p style="color:#6b7280;font-size:13px;">
            If the button doesn't work, copy and paste this link into your browser:<br>
            <span style="word-break:break-all;">{{ $editUrl }}</span>
        </p>

        <p style="margin-top:20px;color:#6b7280;font-size:13px;">
            This is an automated email, please do not reply directly to this message.
        </p>
    </div>
</body>
</html><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admission Application Update</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f4f4f5; padding:30px; margin:0;">
    <div style="max-width:520px;margin:0 auto;background:#fff;border-radius:12px;padding:30px;">
        <h2 style="color:#dc2626;margin-top:0;">Admission Application Update</h2>

        <p>Dear {{ $admission->guardian_name ?? $admission->applicant_name }},</p>

        <p>
            We regret to inform you that the admission application submitted for
            <strong>{{ $admission->applicant_name }}</strong> has not been approved at this time.
        </p>

        @if($admission->rejection_reason)
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:15px;margin:20px 0;">
                <strong>Reason:</strong>
                <p style="margin:8px 0 0;">{{ $admission->rejection_reason }}</p>
            </div>
        @endif

        <p>
            If you believe some information needs correction, you can update your application
            and resubmit it for review using the button below.
        </p>

        <div style="text-align:center;margin:28px 0;">
            <a href="{{ $editUrl }}"
               style="background:#dc2626;color:#fff;text-decoration:none;padding:12px 28px;border-radius:8px;font-weight:bold;display:inline-block;">
                Update Your Application
            </a>
        </div>

        <p style="color:#6b7280;font-size:13px;">
            If the button doesn't work, copy and paste this link into your browser:<br>
            <span style="word-break:break-all;">{{ $editUrl }}</span>
        </p>

        <p style="margin-top:20px;color:#6b7280;font-size:13px;">
            This is an automated email, please do not reply directly to this message.
        </p>
    </div>
</body>
</html>