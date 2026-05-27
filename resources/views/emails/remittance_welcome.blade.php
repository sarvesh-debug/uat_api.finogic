<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to Finogic  Payout</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #0a22aa;
            color: #ffffff;
            text-align: center;
            padding: 30px 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px 20px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.5;
        }
        .details {
            background-color: #f1f5fb;
            border-radius: 8px;
            padding: 15px 20px;
            margin: 20px 0;
        }
        .details p {
            margin: 8px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 25px;
            background-color: #0a22aa;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .footer {
            text-align: center; 
            font-size: 12px;
            color: #999;
            padding: 20px;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 20px 10px;
            }
            .header h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Welcome to Finogic Xpress Payout</h1>
        </div>
        <div class="content">
            <p>Dear <strong>{{ $brand_name }}</strong>,</p>

            <p>🎉 Your remittance account has been successfully created!</p>

            <div class="details">
                <p><strong>Remittance ID:</strong> {{ $rtid }}</p>
                <p><strong>Email:</strong> {{ $email }}</p>
                <p><strong>Phone:</strong> {{ $phone }}</p>
                <p><strong>Default Password:</strong> {{ $password }}</p>
            </div>

            <p>Get started by logging into your account:</p>
            <a href="{{ url('/') }}" class="button">Login to Dashboard</a>

            <p style="margin-top: 30px;">Regards,<br>Team Finogic</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Finogic. All rights reserved.
        </div>
    </div>
</body>
</html>
