<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Finogic | Secure Login</title>

<link rel="icon" href="{{ asset('img/Finogic-fav.png') }}">

<!-- Lottie CDN -->
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
  height: 100vh;
  display: flex;
  background: linear-gradient(120deg, #020617, #0f172a);
  color: white;
}

/* LEFT SIDE */
.left {
  width: 55%;
  padding: 60px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.left img {
  width: 220px;
  margin-bottom: 40px;
}

.left h1 {
  font-size: 48px;
  font-weight: 700;
}

.left p {
  margin-top: 20px;
  opacity: 0.7;
  max-width: 450px;
}

.glow {
  background: linear-gradient(90deg, #3b82f6, #06b6d4);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* RIGHT */
.right {
  width: 45%;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

/* Lottie BG */
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
}

/* Text */
.card h2 {
  font-size: 26px;
  margin-bottom: 10px;
}

.card p {
  font-size: 14px;
  opacity: 0.6;
  margin-bottom: 25px;
}

/* Input */
.input-group {
  margin-bottom: 18px;
}

.input-group input {
  width: 100%;
  padding: 14px;
  border-radius: 8px;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.03);
  color: white;
  outline: none;
}

.input-group input:focus {
  border-color: var(--accent);
}

/* Row */
.row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  margin-bottom: 20px;
}

.row a {
  color: var(--accent);
  text-decoration: none;
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
  transition: 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.btn:hover {
  transform: translateY(-2px);
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

/* Footer */
.footer {
  text-align: center;
  margin-top: 20px;
  font-size: 13px;
  opacity: 0.7;
}

.footer a {
  color: var(--accent);
}

/* Responsive */
@media(max-width:900px){
  .left{ display:none; }
  .right{ width:100%; }
}
</style>
</head>

<body>

<!-- LEFT -->
<div class="left">
  <img src="{{asset('img/Finogic-logo.png')}}">
  <h1>Welcome to <span class="glow">Finogic</span></h1>
  <p>
    Simplify your financial operations with a secure, scalable, and real-time platform built for modern businesses. 
    From seamless transaction processing to reliable infrastructure, Finogic ensures speed, accuracy, and trust at every step.
  </p>
</div>

<!-- RIGHT -->
<div class="right">

<!-- Lottie -->
<div id="lottie-bg"></div>

<div class="card">

<h2>Login</h2>
<p>Secure access to your account</p>

<!-- Flash -->
@if(session('success'))
<div style="background:#16a34a20;padding:10px;border-radius:6px;margin-bottom:10px;">
{{ session('success') }}
</div>
@endif

@if(session('error'))
<div style="background:#dc262620;padding:10px;border-radius:6px;margin-bottom:10px;">
{{ session('error') }}
</div>
@endif

@if($errors->any())
<div style="background:#dc262620;padding:10px;border-radius:6px;margin-bottom:10px;">
<ul>
@foreach($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

<!-- FORM -->
<form method="POST" action="{{ route('remittances.login')}}" id="loginForm">
@csrf

<div class="input-group">
<input type="text" name="login" value="{{ old('login') }}" placeholder="Email or Phone">
</div>

<div class="input-group">
<input type="password" name="password" placeholder="Password">
</div>

<div class="row">
<label><input type="checkbox" name="remember"> Remember</label>
<a href="{{ route('user.forgot.password')}}">Forgot?</a>
</div>

<button class="btn" id="loginBtn">
<span class="btn-text">Login Securely</span>
<span class="loader"></span>
</button>

</form>

<div class="footer">
Don’t have account? <a href="{{ route('remittances.create') }}">Register</a>
</div>

</div>
</div>

<!-- JS -->
<script>
/* Lottie */
lottie.loadAnimation({
  container: document.getElementById('lottie-bg'),
  renderer: 'svg',
  loop: true,
  autoplay: true,
  path: 'https://assets9.lottiefiles.com/packages/lf20_jcikwtux.json'
});

/* Button Loader */
const form = document.getElementById("loginForm");
const btn = document.getElementById("loginBtn");
const text = btn.querySelector(".btn-text");
const loader = btn.querySelector(".loader");

form.addEventListener("submit", function() {
  text.style.display = "none";
  loader.style.display = "inline-block";
  btn.disabled = true;
});
</script>

</body>
</html>