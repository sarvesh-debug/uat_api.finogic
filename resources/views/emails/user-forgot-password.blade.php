<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" href="{{ asset('img/Finogic-fav.png') }}" type="image/png">
    
    <title>Reset Your Password</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 30px; }
        .btn { display: inline-block; padding: 12px 24px; background: linear-gradient(to right, #0a22aa, #b62512); color: #fff; text-decoration: none; border-radius: 8px; font-weight: bold; }
        .footer { font-size: 12px; color: #6b7280; text-align: center; margin-top: 20px; }
        img.logo { max-height: 50px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo -->
        <div style="text-align:center;">
            <img src="{{ asset('img/Finogic-logo.png') }}" alt="Finogic Logo" class="logo">
        </div>

        <h2 style="color:#0a22aa; text-align:center;">Reset Your Password</h2>
        <p style="color:#374151; font-size:16px; line-height:1.5;">
            Hello, <br>
            You recently requested to reset your password for your Finogic account. Click the button below to reset it. This password reset link is valid for 60 minutes.
        </p>

        <div style="text-align:center; margin:30px 0;">
            <a href="{{ $url }}" class="btn">Reset Password</a>
        </div>

        <p style="color:#374151; font-size:14px; line-height:1.5;">
            If you did not request a password reset, please ignore this email or contact support if you have questions.
        </p>

        <div class="footer">
            &copy; {{ date('Y') }} Finogic. All rights reserved.
        </div>
    </div>
</body>
</html>
