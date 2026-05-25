{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - aarpiz Payout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .card { background: #fff; padding: 30px; border-radius: 12px; width: 400px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .card h2 { margin-bottom: 20px; color: #1e40af; }
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 6px; font-weight: bold; color: #444; }
        .input-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; }
        .btn { background: linear-gradient(to right, #1a3a8f, #1e40af); color: #fff; border: none; width: 100%; padding: 14px; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .btn:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Reset Password</h2>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="input-group">
                <label>New Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="input-group">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" required>
            </div>

            <button type="submit" class="btn">Reset Password</button>
        </form>
    </div>
</body>
</html> --}}




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - aarpiz Payout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="icon" href="{{ asset('img/aarpiz-fav.png') }}" type="image/png">

    <style>
        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: linear-gradient(135deg, #1a3a8f 0%, #1e40af 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .container {
            display: flex; width: 100%; max-width: 1000px;
            background: white; border-radius: 16px;
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        .brand-section {
            background: 
                linear-gradient(rgba(26, 58, 143, 0.85), rgba(30, 64, 175, 0.85)),
                url('https://images.unsplash.com/photo-1584438784894-089d6a62b8a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=1050&q=80') center/cover;
            color: white; padding: 40px; flex: 1;
            display: flex; flex-direction: column; justify-content: center;
            position: relative;
        }
        .brand-content { position: relative; z-index: 2; }
        .brand-logo { font-size: 32px; font-weight: 800; margin-bottom: 20px; text-shadow: 1px 1px 3px rgba(0,0,0,0.3); }
        .brand-tagline { font-size: 20px; line-height: 1.6; margin-bottom: 30px; text-shadow: 1px 1px 2px rgba(0,0,0,0.3); }
        .features { list-style: none; margin-top: 30px; }
        .features li { display: flex; align-items: center; margin-bottom: 15px; font-size: 16px; }
        .features i { background: rgba(255,255,255,0.2); width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 12px; }
        .form-section { padding: 40px; flex: 1; display: flex; flex-direction: column; justify-content: center; }
        .form-title { font-size: 28px; font-weight: 700; color: #1e40af; margin-bottom: 10px; text-align: center; }
        .form-subtitle { text-align: center; color: #718096; margin-bottom: 30px; }
        .input-group { margin-bottom: 20px; }
        .input-label { display: block; font-size: 14px; font-weight: 500; color: #4a5568; margin-bottom: 8px; }
        .input-field {
            width: 100%; padding: 14px 16px;
            border: 1px solid #e2e8f0; border-radius: 8px;
            font-size: 16px; transition: all 0.3s;
        }
        .input-field:focus {
            outline: none; border-color: #1e40af;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.2);
        }
        .btn-primary {
            background: linear-gradient(to right, #1a3a8f, #1e40af);
            color: white; border: none; border-radius: 8px;
            padding: 16px; font-size: 16px; font-weight: 600;
            width: 100%; cursor: pointer; transition: all 0.3s;
            margin-top: 10px;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(26, 58, 143, 0.3);
        }
        .back-to-login { text-align: center; margin-top: 20px; }
        .back-link { color: #1e40af; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; }
        .back-link i { margin-right: 8px; }
        .back-link:hover { text-decoration: underline; }
        @media (max-width: 768px) {
            .container { flex-direction: column; max-width: 450px; }
            .brand-section { padding: 30px; text-align: center; min-height: 250px; }
            .form-section { padding: 30px; }
        }
        .brand-section::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(to right, #1a3a8f, #1e40af); z-index: 1;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Left Brand Section -->
    <div class="brand-section">
        <div class="brand-content">
            <div class="brand-logo">aarpiz PAYOUT</div>
            <div class="brand-tagline">
                Set Your New Password<br>and Secure Your Account
            </div>
            <ul class="features">
                <li><i class="fas fa-key"></i> Strong Password Encryption</li>
                <li><i class="fas fa-lock"></i> Secure Account Recovery</li>
                <li><i class="fas fa-shield-alt"></i> Bank-grade Protection</li>
            </ul>
        </div>
    </div>

    <!-- Right Reset Form -->
    <div class="form-section">
        <h2 class="form-title">Create New Password</h2>
        <p class="form-subtitle">Enter your details to reset your password</p>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="input-group">
                <label class="input-label">Email</label>
                <input type="email" name="email" class="input-field" placeholder="your.email@example.com" required>
            </div>

            <div class="input-group">
                <label class="input-label">New Password</label>
                <input type="password" name="password" class="input-field" placeholder="********" required>
            </div>

            <div class="input-group">
                <label class="input-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="input-field" placeholder="********" required>
            </div>

            <button type="submit" class="btn-primary">
                <i class="fas fa-sync-alt"></i> Reset Password
            </button>
        </form>

        <div class="back-to-login">
            <a href="{{ route('login.form') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>
</div>
</body>
</html>
