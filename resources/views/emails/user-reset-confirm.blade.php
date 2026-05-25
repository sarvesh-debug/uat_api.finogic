<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" href="{{ asset('img/aarpiz-fav.png') }}" type="image/png">

    <title>Password Reset Successful</title>
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
            <img src="{{ asset('img/aarpiz-logo.png') }}" alt="aarpiz Logo" class="logo">
        </div>

        <h2 style="color:#0a22aa; text-align:center;">Password Reset Successful</h2>
        <p style="color:#374151; font-size:16px; line-height:1.5;">
            Hello, <br>
            Your aarpiz account password has been successfully reset. You can now log in using your new password.
        </p>

        <div style="text-align:center; margin:30px 0;">
            <a href="{{ route('remittances.login') }}" class="btn">Login Now</a>
        </div>

        <p style="color:#374151; font-size:14px; line-height:1.5;">
            If you did not perform this action, please contact our support team immediately.
        </p>

        <div class="footer">
            &copy; {{ date('Y') }} aarpiz. All rights reserved.
        </div>
    </div>
</body>
</html>
