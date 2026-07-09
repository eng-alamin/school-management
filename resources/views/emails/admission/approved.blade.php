<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admission Application Approved</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f4f4f5; padding:30px; margin:0;">
    <div style="max-width:520px;margin:0 auto;background:#fff;border-radius:12px;padding:30px;">
        <h2 style="color:#16a34a;margin-top:0;">Admission Approved 🎉</h2>

        <p>Dear {{ $admission->guardian_name ?? $admission->applicant_name }},</p>

        <p>
            Congratulations! We are pleased to inform you that the admission application
            submitted for <strong>{{ $admission->applicant_name }}</strong> has been
            <strong style="color:#16a34a;">approved</strong>.
        </p>

        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:15px;margin:20px 0;">
            <table style="width:100%;font-size:14px;">
                <tr>
                    <td style="padding:4px 0;color:#6b7280;">Student Name</td>
                    <td style="padding:4px 0;font-weight:bold;text-align:right;">{{ $admission->applicant_name }}</td>
                </tr>
                <tr>
                    <td style="padding:4px 0;color:#6b7280;">Class</td>
                    <td style="padding:4px 0;font-weight:bold;text-align:right;">{{ $admission->appliedClass?->name ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:4px 0;color:#6b7280;">Session</td>
                    <td style="padding:4px 0;font-weight:bold;text-align:right;">{{ $admission->appliedSession?->name ?? '—' }}</td>
                </tr>
            </table>
        </div>

        @if(!empty($credentials['student']))
            <h3 style="color:#111827;font-size:15px;margin-bottom:8px;">Student Login Details</h3>
            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:15px;margin-bottom:20px;">
                <table style="width:100%;font-size:14px;">
                    <tr>
                        <td style="padding:4px 0;color:#6b7280;">Username</td>
                        <td style="padding:4px 0;font-weight:bold;text-align:right;">{{ $credentials['student']['username'] }}</td>
                    </tr>
                    @if(!empty($credentials['student']['email']))
                        <tr>
                            <td style="padding:4px 0;color:#6b7280;">Email</td>
                            <td style="padding:4px 0;font-weight:bold;text-align:right;">{{ $credentials['student']['email'] }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding:4px 0;color:#6b7280;">Password</td>
                        <td style="padding:4px 0;font-weight:bold;text-align:right;">{{ $credentials['student']['password'] }}</td>
                    </tr>
                </table>
            </div>
        @endif

        @if(!empty($credentials['guardian']))
            <h3 style="color:#111827;font-size:15px;margin-bottom:8px;">Guardian Login Details</h3>
            <div style="background:#fefce8;border:1px solid #fde68a;border-radius:8px;padding:15px;margin-bottom:20px;">
                <table style="width:100%;font-size:14px;">
                    <tr>
                        <td style="padding:4px 0;color:#6b7280;">Username</td>
                        <td style="padding:4px 0;font-weight:bold;text-align:right;">{{ $credentials['guardian']['username'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0;color:#6b7280;">Email</td>
                        <td style="padding:4px 0;font-weight:bold;text-align:right;">{{ $credentials['guardian']['email'] }}</td>
                    </tr>
                    @if(!empty($credentials['guardian']['password']))
                        <tr>
                            <td style="padding:4px 0;color:#6b7280;">Password</td>
                            <td style="padding:4px 0;font-weight:bold;text-align:right;">{{ $credentials['guardian']['password'] }}</td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="2" style="padding:6px 0;color:#6b7280;font-size:12px;">
                                You already have a Guardian Portal account — please use your existing password to log in.
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
        @endif

        <p>
            Please visit the institution office with the original documents to complete the
            enrollment process.
        </p>

        <p style="margin-top:30px;color:#6b7280;font-size:13px;">
            This is an automated email, please do not reply directly to this message.
            Please change your password after first login.
        </p>
    </div>
</body>
</html>