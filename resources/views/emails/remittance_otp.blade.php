<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>OTP Verification</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f9f9f9; padding:20px;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table width="500" cellpadding="20" cellspacing="0" style="background:white; border-radius:10px; box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                    <tr>
                        <td align="center" style="border-bottom:1px solid #eee;">
                            <h2 style="margin:0; color:#333;">🔐 OTP Verification</h2>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p style="font-size:16px; color:#555; margin-bottom:20px;">
                                Hello,<br><br>
                                Please use the following OTP to complete your login:
                            </p>
                            <p style="font-size:28px; font-weight:bold; color:#2c3e50; letter-spacing:4px; text-align:center; margin:30px 0;">
                                {{ $otp }}
                            </p>
                            <p style="font-size:14px; color:#999;">
                                This OTP is valid for <strong>5 minutes</strong>. Do not share it with anyone.
                            </p>
                            <hr> <h4>Login Details:</h4> <p> 📍 Location: {{ $location ?? 'N/A' }}<br> 🌐 IP: {{ $ip ?? 'N/A' }}<br> 💻 Device: {{ $device ?? 'N/A' }} </p> <p style="color:red;"> If this wasn't you, please secure your account. </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="border-top:1px solid #eee; font-size:12px; color:#aaa;">
                            &copy; {{ date('Y') }} Your Finogic. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
