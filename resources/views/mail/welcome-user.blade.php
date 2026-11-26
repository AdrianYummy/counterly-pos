<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to CaféFlow</title>
</head>
<body style="margin:0;background:#f3f6f3;color:#1d2c23;font-family:Arial,sans-serif;">
    <div style="max-width:600px;margin:0 auto;padding:32px 18px;">
        <div style="overflow:hidden;border:1px solid #d9e2da;border-radius:18px;background:#ffffff;">
            <div style="padding:24px;background:#145b38;color:#ffffff;">
                <h1 style="margin:0;font-size:26px;">Welcome to CaféFlow</h1>
                <p style="margin:8px 0 0;color:#dff4e6;">Your secure staff workspace is ready.</p>
            </div>
            <div style="padding:26px;line-height:1.6;">
                <p>Hello {{ $user->full_name }},</p>
                <p>An administrator created a CaféFlow staff account for you.</p>
                <div style="margin:22px 0;padding:16px;border-radius:12px;background:#f2f8f4;">
                    <strong>Username:</strong> {{ $user->username }}<br>
                    <strong>Role:</strong> {{ ucfirst($user->role) }}
                </div>
                <p>For your security, your password is not included in this email. Ask your administrator for the temporary password through a private channel, then use the password-reset option if needed.</p>
                <p style="margin:26px 0;">
                    <a href="{{ route('login') }}" style="display:inline-block;padding:12px 20px;border-radius:10px;background:#145b38;color:#ffffff;text-decoration:none;font-weight:700;">Sign in to CaféFlow</a>
                </p>
                <p style="margin-bottom:0;color:#607066;font-size:13px;">If you were not expecting this account, contact your CaféFlow administrator.</p>
            </div>
        </div>
    </div>
</body>
</html>
