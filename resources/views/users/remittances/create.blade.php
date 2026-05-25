<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>aarpiz | Sign Up</title>

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
  height: 100vh;
  display: flex;
  background: linear-gradient(120deg, #020617, #0f172a);
  color: white;
}

/* LEFT */
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
  font-size: 36px;
  font-weight: 700;
}

.left ul {
  margin-top: 20px;
}

.left li {
  margin: 10px 0;
  list-style: none;
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
  padding: 30px;
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
  margin-bottom: 20px;
}

/* Input */
.input-group {
  margin-bottom: 15px;
}

.input-group input,
.input-group select {
  width: 100%;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.03);
  color: white;
  outline: none;
}

.input-group input:focus,
.input-group select:focus {
  border-color: var(--accent);
}

/* Checkbox */
.check {
  font-size: 13px;
  margin-bottom: 15px;
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
  <img src="{{asset('img/aarpiz-logo.png')}}">
  <h1>Join <span class="glow">aarpiz</span></h1>

  <p class="mt-4 text-gray-300">
    Get started with a platform designed for speed, security, and scalability. 
    aarpiz helps you streamline financial operations with ease and confidence.
  </p>

  <ul class="mt-6 space-y-3">
    <li>✔ Quick and seamless onboarding</li>
    <li>✔ Advanced security and data protection</li>
    <li>✔ Ready-to-use developer integrations</li>
    <li>✔ Dedicated support, available 24/7</li>
  </ul>
</div>

<!-- RIGHT -->
<div class="right">

<div id="lottie-bg"></div>

<div class="card">

<h2>Sign Up</h2>
<p>Create your account</p>

<form action="{{ route('remittances.store') }}" method="POST" id="signupForm">
@csrf

<div class="input-group">
<input type="text" name="brand_name" value="{{ old('brand_name') }}" placeholder="Brand Name">
@error('brand_name') <small style="color:red">{{ $message }}</small> @enderror
</div>

<div class="input-group">
<input type="email" name="email" value="{{ old('email') }}" placeholder="Business Email">
@error('email') <small style="color:red">{{ $message }}</small> @enderror
</div>

<div class="input-group">
<input type="tel" name="phone" value="{{ old('phone') }}" placeholder="Mobile Number">
@error('phone') <small style="color:red">{{ $message }}</small> @enderror
</div>

<div class="input-group">
<input type="text" name="name" value="{{ old('name') }}" placeholder="Your Name">
</div>

<div class="input-group">
<select name="services">
<option value="">Select Service</option>
<option value="payouts">Payouts</option>
</select>
</div>

<div class="check">
<input type="checkbox" name="referral"> I have referral code
</div>

<button class="btn" id="signupBtn">
<span class="btn-text">Create Account</span>
<span class="loader"></span>
</button>

</form>

<div class="footer">
Already have account? <a href="{{ route('remittances.login') }}">Login</a>
</div>

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

/* Button Loader */
const form = document.getElementById("signupForm");
const btn = document.getElementById("signupBtn");
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