<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>aarpiz | Forgot Password</title>

<link rel="icon" href="{{ asset('img/aarpiz-fav.png') }}">

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
  align-items: center;
  justify-content: center;
  background: linear-gradient(120deg, #020617, #0f172a);
  color: white;
  position: relative;
  overflow: hidden;
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
  text-align: center;
}

/* Logo */
.logo {
  width: 160px;
  margin-bottom: 20px;
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

/* Input */
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

/* Alerts */
.alert {
  padding: 10px;
  border-radius: 6px;
  margin-bottom: 10px;
  font-size: 14px;
}
.success { background: #16a34a20; }
.error { background: #dc262620; }

/* Button */
.btn {
  width: 100%;
  margin-top: 15px;
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

/* Footer */
.footer {
  margin-top: 20px;
  font-size: 13px;
  opacity: 0.7;
}
.footer a {
  color: var(--accent);
}
</style>
</head>

<body>

<!-- Lottie -->
<div id="lottie-bg"></div>

<div class="card">

<img src="{{ asset('img/aarpiz-logo.png') }}" class="logo">

<h2>Forgot Password?</h2>
<p>Enter your email to receive reset instructions</p>

@if(session('status'))
<div class="alert success">{{ session('status') }}</div>
@endif

@if($errors->any())
<div class="alert error">
<ul>
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

<form action="{{ route('user.forgot.password.send') }}" method="POST" id="forgotForm">
@csrf

<div class="input-group">
<input type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required>
</div>

<button class="btn" id="forgotBtn">
<span class="btn-text">Send Reset Link</span>
<span class="loader"></span>
</button>

</form>

<div class="footer">
Remember password? 
<a href="{{ route('remittances.login') }}">Login</a>
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

/* Loader */
const form = document.getElementById("forgotForm");
const btn = document.getElementById("forgotBtn");
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