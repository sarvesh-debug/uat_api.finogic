<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Finogic | OTP Verification</title>

<link rel="icon" href="{{ asset('img/Finogic-fav.png') }}">

<!-- Lottie -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>

<style>
:root {
  --primary: #020617;
  --accent: #3b82f6;
  --accent2: #06b6d4;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Inter', sans-serif;
}

body {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(120deg, #020617, #0f172a);
  color: white;
  overflow: hidden;
  position: relative;
}

/* Lottie */
#lottie-bg {
  position: absolute;
  width: 100%;
  height: 100%;
  opacity: 0.08;
  z-index: 0;
}

/* Card */
.card {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 420px;
  padding: 35px;
  border-radius: 16px;
  background: rgba(255,255,255,0.05);
  backdrop-filter: blur(18px);
  border: 1px solid rgba(255,255,255,0.1);
  text-align: center;
}

/* Heading */
.card h2 {
  font-size: 26px;
  margin-bottom: 10px;
}

.card p {
  font-size: 14px;
  opacity: 0.6;
  margin-bottom: 20px;
}

/* OTP Inputs */
.otp-inputs {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
}

.otp-inputs input {
  width: 50px;
  height: 60px;
  border-radius: 10px;
  text-align: center;
  font-size: 22px;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.03);
  color: white;
}

.otp-inputs input:focus {
  border-color: var(--accent);
  outline: none;
}

/* Button */
.btn {
  width: 100%;
  padding: 14px;
  border-radius: 8px;
  border: none;
  background: linear-gradient(90deg, #3b82f6, #06b6d4);
  color: white;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
}

/* Loader */
.loader {
  width: 18px;
  height: 18px;
  border: 2px solid white;
  border-top: 2px solid transparent;
  border-radius: 50%;
  display: none;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Alerts */
.msg {
  font-size: 14px;
  margin-bottom: 10px;
}
.success { color: #22c55e; }
.error { color: #ef4444; }

/* Resend */
.resend {
  margin-top: 15px;
  font-size: 14px;
  color: var(--accent);
  cursor: pointer;
}

/* Timer */
.timer {
  opacity: 0.6;
}

/* Responsive */
@media(max-width:500px){
  .otp-inputs input { width: 40px; height: 50px; }
}
</style>
</head>

<body>

<div id="lottie-bg"></div>

<div class="card">

<h2>OTP Verification</h2>
<p>Enter the 6-digit code sent to your email/phone</p>

@if(session('error'))
<div class="msg error">{{ session('error') }}</div>
@endif

@if(session('success'))
<div class="msg success">{{ session('success') }}</div>
@endif

<form action="{{ route('remittances.verifyOtp') }}" method="POST" id="otpForm">
@csrf

<div class="otp-inputs">
<input type="text" maxlength="1">
<input type="text" maxlength="1">
<input type="text" maxlength="1">
<input type="text" maxlength="1">
<input type="text" maxlength="1">
<input type="text" maxlength="1">
</div>

<input type="hidden" name="otp" id="otp-hidden">

<button class="btn" id="verifyBtn">
<span class="btn-text">Verify OTP</span>
<span class="loader"></span>
</button>

</form>

<div class="resend">
<span id="resendText">Resend OTP</span>
<span class="timer" id="timer"></span>
</div>

</div>

<script>
/* Lottie */
lottie.loadAnimation({
  container: document.getElementById('lottie-bg'),
  renderer: 'svg',
  loop: true,
  autoplay: true,
  path: 'https://assets9.lottiefiles.com/packages/lf20_jcikwtux.json'
});

/* OTP UX */
const inputs = document.querySelectorAll('.otp-inputs input');

inputs.forEach((input, index) => {
  input.addEventListener('input', () => {
    if(input.value && index < inputs.length - 1) inputs[index+1].focus();
  });

  input.addEventListener('keydown', (e) => {
    if(e.key === 'Backspace' && !input.value && index > 0){
      inputs[index-1].focus();
    }
  });
});

/* Paste OTP */
document.addEventListener('paste', (e) => {
  const paste = e.clipboardData.getData('text').slice(0,6);
  inputs.forEach((input, i) => input.value = paste[i] || '');
});

/* Submit */
const form = document.getElementById("otpForm");
const btn = document.getElementById("verifyBtn");
const text = btn.querySelector(".btn-text");
const loader = btn.querySelector(".loader");

form.addEventListener("submit", function(e) {
  const otp = Array.from(inputs).map(i => i.value).join('');
  document.getElementById("otp-hidden").value = otp;

  text.style.display = "none";
  loader.style.display = "inline-block";
  btn.disabled = true;
});

/* Resend Timer */
let time = 30;
const timer = document.getElementById("timer");
const resend = document.getElementById("resendText");

function startTimer(){
  resend.style.pointerEvents = "none";
  const interval = setInterval(() => {
    time--;
    timer.innerText = ` (${time}s)`;

    if(time <= 0){
      clearInterval(interval);
      resend.style.pointerEvents = "auto";
      timer.innerText = "";
      time = 30;
    }
  },1000);
}
startTimer();
</script>

</body>
</html>