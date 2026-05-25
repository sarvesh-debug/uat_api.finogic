<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Forgot Password - aarpiz Admin</title>

    <link rel="icon" href="{{ asset('img/aarpiz-fav.png') }}" type="image/png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
        }

        body{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:20px;
            overflow:hidden;
            position:relative;
            background:
                radial-gradient(circle at top left,#1d4ed8 0%,transparent 30%),
                radial-gradient(circle at bottom right,#2563eb 0%,transparent 30%),
                #050816;
        }

        body::before{
            content:'';
            position:absolute;
            width:500px;
            height:500px;
            background:rgba(37,99,235,0.12);
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

        .wrapper{
            width:100%;
            max-width:1100px;
            position:relative;
            z-index:2;
        }

        .card{
            display:flex;
            overflow:hidden;
            border-radius:28px;
            background:rgba(10,15,35,0.82);
            backdrop-filter:blur(18px);
            border:1px solid rgba(255,255,255,0.08);
            box-shadow:
                0 20px 60px rgba(0,0,0,0.45),
                inset 0 1px 0 rgba(255,255,255,0.05);
        }

        /* LEFT SIDE */

        .left-side{
            width:48%;
            padding:55px;
            color:#fff;
            position:relative;

            background:
                linear-gradient(rgba(15,23,42,0.75),rgba(30,64,175,0.92)),
                url('https://images.unsplash.com/photo-1554224155-6726b3ff858f?q=80&w=1200&auto=format&fit=crop') center/cover;
        }

        .left-side::before{
            content:'';
            position:absolute;
            width:260px;
            height:260px;
            border:1px solid rgba(255,255,255,0.08);
            border-radius:50%;
            right:-90px;
            top:-90px;
        }

        .brand-content{
            position:relative;
            z-index:2;
        }

        .logo-box{
            width:70px;
            height:70px;
            border-radius:20px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:rgba(255,255,255,0.12);
            border:1px solid rgba(255,255,255,0.15);
            backdrop-filter:blur(10px);
            margin-bottom:24px;
            font-size:28px;
        }

        .brand-title{
            font-size:42px;
            font-weight:800;
            margin-bottom:16px;
        }

        .brand-subtitle{
            color:rgba(255,255,255,0.82);
            line-height:1.8;
            font-size:16px;
            margin-bottom:38px;
        }

        .feature-box{
            display:flex;
            gap:14px;
            align-items:flex-start;
            margin-bottom:20px;
            padding:16px;
            border-radius:16px;
            background:rgba(255,255,255,0.06);
            border:1px solid rgba(255,255,255,0.08);
            backdrop-filter:blur(12px);
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
            font-size:15px;
            margin-bottom:4px;
        }

        .feature-box p{
            color:rgba(255,255,255,0.72);
            font-size:13px;
            line-height:1.5;
        }

        /* RIGHT SIDE */

        .right-side{
            width:52%;
            padding:55px;
            background:rgba(7,10,25,0.95);
            color:#fff;
            display:flex;
            flex-direction:column;
            justify-content:center;
        }

        .top-badge{
            display:inline-flex;
            align-items:center;
            gap:10px;
            width:max-content;
            padding:10px 18px;
            border-radius:50px;
            background:rgba(37,99,235,0.12);
            border:1px solid rgba(59,130,246,0.18);
            color:#60a5fa;
            font-size:13px;
            margin-bottom:26px;
        }

        .form-title{
            font-size:36px;
            font-weight:800;
            margin-bottom:10px;
        }

        .form-subtitle{
            color:#94a3b8;
            font-size:15px;
            line-height:1.7;
            margin-bottom:35px;
        }

        .alert-success{
            background:rgba(34,197,94,0.12);
            border:1px solid rgba(34,197,94,0.25);
            color:#86efac;
            padding:14px 18px;
            border-radius:14px;
            margin-bottom:20px;
            display:flex;
            align-items:center;
            gap:12px;
        }

        .alert-error{
            background:rgba(239,68,68,0.12);
            border:1px solid rgba(239,68,68,0.25);
            color:#fca5a5;
            padding:14px 18px;
            border-radius:14px;
            margin-bottom:20px;
            display:flex;
            align-items:center;
            gap:12px;
        }

        .input-group{
            margin-bottom:24px;
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
            padding:0 18px 0 50px;
            font-size:15px;
            transition:0.3s;
        }

        .input-field:focus{
            border-color:#2563eb;
            box-shadow:0 0 0 4px rgba(37,99,235,0.12);
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
            box-shadow:0 12px 30px rgba(37,99,235,0.35);
        }

        .btn-primary:hover{
            transform:translateY(-2px);
            box-shadow:0 18px 40px rgba(37,99,235,0.42);
        }

        .btn-primary i{
            margin-right:10px;
        }

        .back-login{
            margin-top:24px;
            text-align:center;
        }

        .back-link{
            text-decoration:none;
            color:#60a5fa;
            font-size:14px;
            font-weight:600;
            transition:0.3s;
            display:inline-flex;
            align-items:center;
            gap:10px;
        }

        .back-link:hover{
            color:#93c5fd;
        }

        @media(max-width:900px){

            .card{
                flex-direction:column;
            }

            .left-side,
            .right-side{
                width:100%;
            }

            .left-side{
                padding:40px 30px;
            }

            .right-side{
                padding:40px 25px;
            }

            .brand-title{
                font-size:32px;
            }

            .form-title{
                font-size:30px;
            }
        }

        @media(max-width:500px){

            .left-side,
            .right-side{
                padding:28px 20px;
            }

            .form-title{
                font-size:26px;
            }
        }

    </style>

</head>

<body>

<div class="wrapper">

    <div class="card">

        <!-- LEFT SECTION -->

        <div class="left-side">

            <div class="brand-content">

                <div class="logo-box">
                    <i class="fa-solid fa-key"></i>
                </div>

                <h1 class="brand-title">
                    aarpiz
                </h1>

                <p class="brand-subtitle">
                    Secure password recovery system for your fintech dashboard,
                    payout management and banking operations.
                </p>

                <div class="feature-box">

                    <i class="fa-solid fa-shield-halved"></i>

                    <div>
                        <h4>Advanced Security</h4>
                        <p>Encrypted password reset system with secure verification.</p>
                    </div>

                </div>

                <div class="feature-box">

                    <i class="fa-solid fa-envelope-open-text"></i>

                    <div>
                        <h4>Instant Mail Delivery</h4>
                        <p>Receive reset instructions directly on your registered email.</p>
                    </div>

                </div>

                <div class="feature-box">

                    <i class="fa-solid fa-lock"></i>

                    <div>
                        <h4>Protected Reset Access</h4>
                        <p>One-time reset links for maximum account protection.</p>
                    </div>

                </div>

            </div>

        </div>

        <!-- RIGHT SECTION -->

        <div class="right-side">

            <div class="top-badge">
                <i class="fa-solid fa-fingerprint"></i>
                Secure Account Recovery
            </div>

            <h2 class="form-title">
                Forgot Password?
            </h2>

            <p class="form-subtitle">
                Enter your registered email address and we’ll send you a secure password reset link.
            </p>

            @if (session('status'))
                <div class="alert-success">
                    <i class="fas fa-circle-check"></i>
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert-error">
                    <i class="fas fa-circle-exclamation"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('forgot.send') }}">
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
                            placeholder="Enter your registered email"
                            required
                        >

                    </div>

                </div>

                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-paper-plane"></i>
                    Send Reset Link
                </button>

            </form>

            <div class="back-login">

                <a href="{{ route('login.form') }}" class="back-link">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Login
                </a>

            </div>

        </div>

    </div>

</div>

<script>

    document.addEventListener('DOMContentLoaded', function(){

        const leftSide = document.querySelector('.left-side');

        const img = new Image();

        img.src = 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?q=80&w=1200&auto=format&fit=crop';

        img.onerror = function(){
            leftSide.style.background = '#0f172a';
        };

    });

</script>

</body>
</html>