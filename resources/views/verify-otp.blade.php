<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin OTP Verification</title>

<script src="https://cdn.tailwindcss.com"></script>

<link rel="icon" href="{{ asset('img/Finogic-fav.png') }}" type="image/png">

<style>

@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Inter',sans-serif;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
    position:relative;

    background:
        radial-gradient(circle at top left,#1d4ed8 0%,transparent 28%),
        radial-gradient(circle at bottom right,#7c3aed 0%,transparent 28%),
        #020617;
}

/* BACKGROUND GLOW */

body::before{
    content:'';
    position:absolute;
    width:450px;
    height:450px;
    background:rgba(37,99,235,0.18);
    filter:blur(120px);
    border-radius:50%;
    top:-150px;
    left:-120px;
}

body::after{
    content:'';
    position:absolute;
    width:400px;
    height:400px;
    background:rgba(124,58,237,0.16);
    filter:blur(120px);
    border-radius:50%;
    bottom:-120px;
    right:-100px;
}

/* CARD */

.otp-container{
    width:100%;
    max-width:450px;
    padding:42px 36px;
    border-radius:28px;

    background:
        linear-gradient(180deg,
        rgba(15,23,42,0.96),
        rgba(17,24,39,0.98));

    border:1px solid rgba(255,255,255,0.06);

    box-shadow:
        0 25px 60px rgba(0,0,0,0.45),
        inset 0 1px 0 rgba(255,255,255,0.05);

    backdrop-filter:blur(20px);

    position:relative;
    z-index:2;
}

/* TOP ICON */

.icon-box{
    width:78px;
    height:78px;
    margin:auto;
    border-radius:22px;

    display:flex;
    align-items:center;
    justify-content:center;

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    box-shadow:
        0 15px 35px rgba(37,99,235,0.35);

    margin-bottom:24px;
}

.icon-box svg{
    width:34px;
    height:34px;
    color:white;
}

/* TEXT */

.title{
    font-size:30px;
    font-weight:800;
    color:white;
    text-align:center;
    margin-bottom:10px;
}

.subtitle{
    color:#94a3b8;
    text-align:center;
    line-height:1.7;
    font-size:14px;
    margin-bottom:32px;
}

/* ALERT */

.alert{
    padding:14px 16px;
    border-radius:16px;
    margin-bottom:18px;
    font-size:14px;
    display:flex;
    align-items:center;
    justify-content:space-between;
}

.alert-success{
    background:rgba(16,185,129,0.10);
    border:1px solid rgba(16,185,129,0.22);
    color:#6ee7b7;
}

.alert-error{
    background:rgba(239,68,68,0.10);
    border:1px solid rgba(239,68,68,0.20);
    color:#fca5a5;
}

.close-btn{
    cursor:pointer;
    font-size:18px;
    opacity:0.8;
}

/* OTP BOXES */

.otp-inputs{
    gap:12px;
}

.otp-inputs input{
    width:58px;
    height:66px;

    border:none;
    outline:none;

    border-radius:18px;

    background:#0f172a;

    border:1px solid rgba(255,255,255,0.08);

    color:white;

    font-size:24px;
    font-weight:700;
    text-align:center;

    transition:0.3s ease;
}

.otp-inputs input:focus{
    border-color:#3b82f6;

    box-shadow:
        0 0 0 4px rgba(59,130,246,0.15);

    transform:translateY(-2px);
}

/* BUTTON */

.btn{
    width:100%;
    height:58px;

    border:none;
    border-radius:18px;

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    color:white;

    font-size:16px;
    font-weight:700;

    cursor:pointer;

    margin-top:8px;

    transition:0.3s ease;

    box-shadow:
        0 15px 35px rgba(37,99,235,0.28);
}

.btn:hover{
    transform:translateY(-2px);

    box-shadow:
        0 20px 40px rgba(37,99,235,0.40);
}

/* FOOTER */

.footer-text{
    margin-top:26px;
    text-align:center;
    color:#64748b;
    font-size:13px;
}

/* MOBILE */

@media(max-width:500px){

    body{
        padding:20px;
    }

    .otp-container{
        padding:34px 22px;
    }

    .otp-inputs{
        gap:8px;
    }

    .otp-inputs input{
        width:48px;
        height:58px;
        font-size:20px;
    }

    .title{
        font-size:26px;
    }
}

</style>

</head>

<body>

<div class="otp-container">

    <!-- ICON -->

    <div class="icon-box">

        <svg fill="none" stroke="currentColor" stroke-width="1.8"
             viewBox="0 0 24 24">

            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M12 11c0 .552-.448 1-1 1s-1-.448-1-1 .448-1 1-1 1 .448 1 1Zm0 0v2m6-2a9 9 0 11-18 0 9 9 0 0118 0Z" />

        </svg>

    </div>

    <!-- TITLE -->

    <h2 class="title">
        OTP Verification
    </h2>

    <p class="subtitle">
        Enter the 6-digit verification code sent to your registered email or mobile number.
    </p>

    {{-- ERROR MESSAGE --}}
    @if(session('error'))
        <div class="alert alert-error" id="alertBox">
            <span>{{ session('error') }}</span>

            <span class="close-btn" onclick="closeAlert()">
                ×
            </span>
        </div>
    @endif

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success" id="alertBox">
            <span>{{ session('success') }}</span>

            <span class="close-btn" onclick="closeAlert()">
                ×
            </span>
        </div>
    @endif

    <!-- FORM -->

    <form action="{{ route('admin.verifyOtp') }}" method="POST">

        @csrf

        <div class="otp-inputs flex justify-between mb-6">

            <input type="text" maxlength="1" required>
            <input type="text" maxlength="1" required>
            <input type="text" maxlength="1" required>
            <input type="text" maxlength="1" required>
            <input type="text" maxlength="1" required>
            <input type="text" maxlength="1" required>

        </div>

        <input type="hidden" name="otp" id="otp-hidden">

        <button type="submit"
                class="btn"
                onclick="concatOtp(event)">

            Verify OTP

        </button>

    </form>

    <div class="footer-text">
        Secure fintech authentication system
    </div>

</div>

<script>

function concatOtp(e) {

    e.preventDefault();

    const inputs = document.querySelectorAll('.otp-inputs input');

    const otp = Array.from(inputs)
        .map(i => i.value)
        .join('');

    document.getElementById('otp-hidden').value = otp;

    e.target.form.submit();
}

function closeAlert() {

    document.getElementById('alertBox').style.display = 'none';
}

const otpInputs = document.querySelectorAll('.otp-inputs input');

otpInputs.forEach((input, index) => {

    input.addEventListener('input', () => {

        if(input.value && index < otpInputs.length - 1)
            otpInputs[index+1].focus();
    });

    input.addEventListener('keydown', e => {

        if(e.key === 'Backspace' && !input.value && index > 0)
            otpInputs[index-1].focus();
    });
});

</script>

</body>
</html>