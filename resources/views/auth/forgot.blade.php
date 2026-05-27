{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - Finogic B2B Onboarding</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

</head>
<body class="bg-gradient-to-r from-pink-500 to-red-500 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">🔑 Reset Password</h2>

        @if (session('status'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('forgot.send') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-gray-700">Email</label>
                <input type="email" name="email" class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-pink-300" required>
            </div>
            <button type="submit" class="w-full bg-pink-600 text-white py-2 rounded-lg hover:bg-pink-700">Send Reset Link</button>
        </form>
    </div>
</body>
</html> --}



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Finogic Payout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="icon" href="{{ asset('img/Finogic-fav.png') }}" type="image/png">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #1a3a8f 0%, #1e40af 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .brand-section {
            background: 
                linear-gradient(rgba(26, 58, 143, 0.85), rgba(30, 64, 175, 0.85)), 
                url('https://images.unsplash.com/photo-1554224155-6726b3ff858f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1011&q=80') center/cover;
            color: white;
            padding: 40px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }
        
        .brand-content {
            position: relative;
            z-index: 2;
        }
        
        .brand-logo {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }
        
        .brand-tagline {
            font-size: 20px;
            line-height: 1.6;
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        .features {
            list-style: none;
            margin-top: 30px;
        }
        
        .features li {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .features i {
            background: rgba(255, 255, 255, 0.2);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
        }
        
        .form-section {
            padding: 40px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .form-title {
            font-size: 28px;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .form-subtitle {
            text-align: center;
            color: #718096;
            margin-bottom: 30px;
        }
        
        .input-group {
            margin-bottom: 20px;
        }
        
        .input-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 8px;
        }
        
        .input-field {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .input-field:focus {
            outline: none;
            border-color: #1e40af;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.2);
        }
        
        .btn-primary {
            background: linear-gradient(to right, #1a3a8f, #1e40af);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 16px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(26, 58, 143, 0.3);
        }
        
        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link {
            color: #1e40af;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
        }
        
        .back-link i {
            margin-right: 8px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .alert-success {
            background-color: #f0fff4;
            color: #2f855a;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .alert-success i {
            margin-right: 10px;
        }
        
        .alert-error {
            background-color: #fed7d7;
            color: #c53030;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .alert-error i {
            margin-right: 10px;
        }
        
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                max-width: 450px;
            }
            
            .brand-section {
                padding: 30px;
                text-align: center;
                min-height: 300px;
            }
            
            .form-section {
                padding: 30px;
            }
        }

        /* Fallback if image doesn't load */
        .brand-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to right, #1a3a8f, #1e40af);
            z-index: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left Brand Section with Image -->
        <div class="brand-section">
            <div class="brand-content">
                <div class="brand-logo">Finogic PAYOUT</div>
                <div class="brand-tagline">
                    Secure Account Recovery for<br>
                    Your Payment Solutions
                </div>
                
                <ul class="features">
                    <li><i class="fas fa-shield-alt"></i> Bank-grade Security</li>
                    <li><i class="fas fa-envelope"></i> Instant Email Delivery</li>
                    <li><i class="fas fa-lock"></i> One-Time Reset Links</li>
                </ul>
            </div>
        </div>
        
        <!-- Right Form Section -->
        <div class="form-section">
            <h2 class="form-title">Reset Your Password</h2>
            <p class="form-subtitle">Enter your email to receive a reset link</p>
            
            @if (session('status'))
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i>
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first() }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('forgot.send') }}">
                @csrf
                <div class="input-group">
                    <label class="input-label">Email Address</label>
                    <div class="relative">
                        <input type="email" name="email" class="input-field" placeholder="your.email@example.com" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Reset Link
                </button>
            </form>
            
            <div class="back-to-login">
                <a href="{{ route('login') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
        </div>
    </div>

    <script>
        // Add a fallback in case the image fails to load
        document.addEventListener('DOMContentLoaded', function() {
            const brandSection = document.querySelector('.brand-section');
            const img = new Image();
            img.src = 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1011&q=80';
            
            img.onerror = function() {
                brandSection.style.background = 'linear-gradient(to right, #1a3a8f, #1e40af)';
            };
        });
    </script>
</body>
</html>
=======
    <title>Finogic | Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-r from-blue-50 to-blue-100 min-h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md text-center">
        <!-- Logo -->
        <div class="flex flex-col items-center mb-6">
            <div class="flex items-center space-x-2 bg-gradient-to-r from-blue-600  to-blue-400 px-3 py-1.5 rounded-3xl shadow-md">
                <img src="{{asset('img/rocket--v1.png')}}" alt="logo" class="h-8 w-8">
                <span class="text-lg font-bold text-white">Finogic</span>
            </div>
        </div>

        <!-- Title -->
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Forgot Password?</h2>
        <p class="text-sm text-gray-500 mb-6">
            Enter your registered email as an admin and we’ll send you reset instructions.
        </p>

        <!-- Success -->
        @if (session('status'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('forgot.send') }}" class="space-y-4">
            @csrf
            <input type="email" name="email" placeholder="Email Address"
                   class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-400 outline-none text-gray-700"
                   required>

            <!-- Button -->
            <button type="submit"
                class="w-full bg-gradient-to-r from-blue-600 to-blue-400 text-white py-3 rounded-lg font-semibold hover:opacity-90 transition duration-200 shadow-md">
                Send Reset Link
            </button>
        </form>

        <!-- Footer -->
        <p class="text-sm text-gray-600 mt-6">
            Remembered your password? 
            <a href="{{ route('login.form') }}" class="text-blue-600 font-semibold hover:underline">Login</a>
        </p>
    </div>
</body>
</html>
