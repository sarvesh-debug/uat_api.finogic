<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>aarpiz Admin Login</title>

    <link rel="icon" href="{{ asset('img/aarpiz-fav.png') }}" type="image/png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body{
            min-height:100vh;
            background:
                radial-gradient(circle at top left,#1e3a8a 0%,transparent 30%),
                radial-gradient(circle at bottom right,#2563eb 0%,transparent 30%),
                #050816;
            display:flex;
            align-items:center;
            justify-content:center;
            overflow:hidden;
            position:relative;
            padding:20px;
        }

        body::before{
            content:'';
            position:absolute;
            width:500px;
            height:500px;
            background:rgba(37,99,235,0.15);
            filter:blur(120px);
            border-radius:50%;
            top:-120px;
            left:-120px;
        }

        body::after{
            content:'';
            position:absolute;
            width:450px;
            height:450px;
            background:rgba(59,130,246,0.12);
            filter:blur(120px);
            border-radius:50%;
            bottom:-120px;
            right:-120px;
        }

        .login-wrapper{
            width:100%;
            max-width:1150px;
            position:relative;
            z-index:2;
        }

        .login-card{
            display:flex;
            background:rgba(10,15,35,0.82);
            backdrop-filter:blur(20px);
            border:1px solid rgba(255,255,255,0.08);
            border-radius:28px;
            overflow:hidden;
            box-shadow:
                0 20px 60px rgba(0,0,0,0.45),
                inset 0 1px 0 rgba(255,255,255,0.05);
        }

        /* LEFT PANEL */

        .left-panel{
            width:48%;
            padding:55px;
            background:
                linear-gradient(180deg,rgba(30,64,175,0.75),rgba(15,23,42,0.95)),
                url('https://images.unsplash.com/photo-1556740749-887f6717d7e4?q=80&w=1200&auto=format&fit=crop') center/cover;
            color:#fff;
            position:relative;
            overflow:hidden;
        }

        .left-panel::before{
            content:'';
            position:absolute;
            width:250px;
            height:250px;
            border:1px solid rgba(255,255,255,0.08);
            border-radius:50%;
            top:-80px;
            right:-80px;
        }

        .brand{
            position:relative;
            z-index:2;
        }

        .logo-box{
            width:65px;
            height:65px;
            border-radius:18px;
            background:rgba(255,255,255,0.12);
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:28px;
            margin-bottom:24px;
            backdrop-filter:blur(10px);
            border:1px solid rgba(255,255,255,0.15);
        }

        .brand h1{
            font-size:42px;
            font-weight:800;
            margin-bottom:15px;
            letter-spacing:1px;
        }

        .brand p{
            color:rgba(255,255,255,0.82);
            font-size:16px;
            line-height:1.8;
            margin-bottom:40px;
        }

        .feature-box{
            display:flex;
            gap:15px;
            align-items:flex-start;
            margin-bottom:22px;
            background:rgba(255,255,255,0.06);
            border:1px solid rgba(255,255,255,0.08);
            padding:16px;
            border-radius:16px;
            backdrop-filter:blur(10px);
        }

        .feature-box i{
            width:45px;
            height:45px;
            border-radius:12px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:rgba(255,255,255,0.12);
            font-size:18px;
        }

        .feature-box h4{
            font-size:16px;
            margin-bottom:4px;
        }

        .feature-box span{
            font-size:13px;
            color:rgba(255,255,255,0.72);
        }

        /* RIGHT PANEL */

        .right-panel{
            width:52%;
            padding:55px;
            background:rgba(7,10,25,0.92);
            color:#fff;
        }

        .top-tag{
            display:inline-flex;
            align-items:center;
            gap:10px;
            background:rgba(37,99,235,0.12);
            border:1px solid rgba(59,130,246,0.18);
            color:#60a5fa;
            padding:10px 18px;
            border-radius:50px;
            font-size:13px;
            margin-bottom:28px;
        }

        .login-title{
            font-size:36px;
            font-weight:800;
            margin-bottom:10px;
        }

        .login-subtitle{
            color:#94a3b8;
            margin-bottom:35px;
            font-size:15px;
        }

        .input-group{
            margin-bottom:22px;
        }

        .input-label{
            display:block;
            margin-bottom:10px;
            color:#cbd5e1;
            font-size:14px;
            font-weight:500;
        }

        .input-box{
            position:relative;
        }

        .input-box i{
            position:absolute;
            left:18px;
            top:50%;
            transform:translateY(-50%);
            color:#64748b;
            font-size:15px;
        }

        .input-field{
            width:100%;
            height:58px;
            border:none;
            outline:none;
            border-radius:16px;
            background:#0f172a;
            border:1px solid #1e293b;
            color:#fff;
            padding:0 50px;
            font-size:15px;
            transition:0.3s;
        }

        .input-field:focus{
            border-color:#2563eb;
            box-shadow:0 0 0 4px rgba(37,99,235,0.12);
        }

        .password-toggle{
            position:absolute;
            right:18px;
            top:50%;
            transform:translateY(-50%);
            color:#94a3b8;
            cursor:pointer;
        }

        .options{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:28px;
        }

        .remember{
            display:flex;
            align-items:center;
            gap:10px;
            color:#cbd5e1;
            font-size:14px;
        }

        .remember input{
            accent-color:#2563eb;
            width:16px;
            height:16px;
        }

        .forgot-link{
            color:#60a5fa;
            text-decoration:none;
            font-size:14px;
            font-weight:500;
        }

        .forgot-link:hover{
            color:#93c5fd;
        }

        .btn-primary{
            width:100%;
            height:58px;
            border:none;
            border-radius:16px;
            background:linear-gradient(135deg,#2563eb,#1d4ed8);
            color:#fff;
            font-size:16px;
            font-weight:700;
            cursor:pointer;
            transition:0.3s;
            box-shadow:0 10px 25px rgba(37,99,235,0.35);
        }

        .btn-primary:hover{
            transform:translateY(-2px);
            box-shadow:0 15px 35px rgba(37,99,235,0.45);
        }

        .divider{
            display:flex;
            align-items:center;
            gap:15px;
            margin:30px 0;
        }

        .divider span{
            color:#64748b;
            font-size:13px;
        }

        .divider::before,
        .divider::after{
            content:'';
            flex:1;
            height:1px;
            background:#1e293b;
        }

        .btn-google{
            width:100%;
            height:56px;
            border-radius:16px;
            border:1px solid #1e293b;
            background:#0f172a;
            color:#fff;
            font-size:15px;
            font-weight:600;
            cursor:pointer;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:12px;
            transition:0.3s;
        }

        .btn-google:hover{
            border-color:#334155;
            background:#111c33;
        }

        .google-icon{
            color:#ea4335;
            font-size:18px;
        }

        @media(max-width:900px){

            .login-card{
                flex-direction:column;
            }

            .left-panel,
            .right-panel{
                width:100%;
            }

            .left-panel{
                padding:40px 30px;
            }

            .right-panel{
                padding:40px 25px;
            }

            .brand h1{
                font-size:32px;
            }

            .login-title{
                font-size:30px;
            }
        }

        @media(max-width:500px){

            .left-panel,
            .right-panel{
                padding:28px 20px;
            }

            .options{
                flex-direction:column;
                align-items:flex-start;
                gap:14px;
            }

            .login-title{
                font-size:26px;
            }
        }
    </style>
</head>

<body>

<div class="login-wrapper">

    <div class="login-card">

        <!-- LEFT SIDE -->
        <div class="left-panel">

            <div class="brand">

                <div class="logo-box">
                    <i class="fa-solid fa-wallet"></i>
                </div>

                <h1>aarpiz</h1>

                <p>
                    Secure fintech dashboard for payout management,
                    AEPS services, wallet control and smart business operations.
                </p>

                <div class="feature-box">
                    <i class="fa-solid fa-shield-halved"></i>
                    <div>
                        <h4>Enterprise Security</h4>
                        <span>Protected access with encrypted sessions</span>
                    </div>
                </div>

                <div class="feature-box">
                    <i class="fa-solid fa-chart-simple"></i>
                    <div>
                        <h4>Realtime Analytics</h4>
                        <span>Track payout and transaction performance instantly</span>
                    </div>
                </div>

                <div class="feature-box">
                    <i class="fa-solid fa-building-columns"></i>
                    <div>
                        <h4>Banking Infrastructure</h4>
                        <span>Built for modern fintech and payout solutions</span>
                    </div>
                </div>

            </div>

        </div>

        <!-- RIGHT SIDE -->
        <div class="right-panel">

            <div class="top-tag">
                <i class="fa-solid fa-lock"></i>
                Secure Admin Access
            </div>

            <h2 class="login-title">
                Welcome Back 
            </h2>

            <p class="login-subtitle">
                Login to continue managing your fintech dashboard.
            </p>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="input-group">

                    <label class="input-label">
                        Email Address
                    </label>

                    <div class="input-box">
                        <i class="fa-regular fa-envelope"></i>

                        <input
                            type="email"
                            name="email"
                            class="input-field"
                            placeholder="Enter your email"
                            required
                        >
                    </div>

                </div>

                <div class="input-group">

                    <label class="input-label">
                        Password
                    </label>

                    <div class="input-box">

                        <i class="fa-solid fa-lock"></i>

                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="input-field"
                            placeholder="Enter your password"
                            required
                        >

                        <span class="password-toggle" onclick="togglePassword()">
                            <i class="far fa-eye"></i>
                        </span>

                    </div>

                </div>

                <div class="options">

                    <label class="remember">
                        <input type="checkbox">
                        Remember me
                    </label>

                    <a href="{{ route('password.request') }}" class="forgot-link">
                        Forgot Password?
                    </a>

                </div>

                <button type="submit" class="btn-primary">
                    Sign In to Dashboard
                </button>

            </form>

            <div class="divider">
                <span>OR CONTINUE WITH</span>
            </div>

            <!-- <button class="btn-google">
                <i class="fab fa-google google-icon"></i>
                Sign in with Google
            </button> -->

        </div>

    </div>

</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.querySelector('.password-toggle i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>

</body>
</html>