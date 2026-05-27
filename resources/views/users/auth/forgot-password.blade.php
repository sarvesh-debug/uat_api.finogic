{{-- <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Finogic | Forgot Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    :root { --brand-blue: #0a22aa; --brand-red: #b62512; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fadeIn { animation: fadeIn 0.8s ease-out forwards; }
  </style>
</head>
<body class="min-h-screen bg-gradient-to-r from-indigo-50 via-blue-50 to-cyan-50 flex items-center justify-center relative overflow-hidden">

  <!-- Decorative blurred circles -->
  <div class="absolute -top-32 -left-32 w-72 h-72 bg-[var(--brand-blue)] rounded-full blur-3xl opacity-20 animate-pulse"></div>
  <div class="absolute -bottom-32 -right-32 w-72 h-72 bg-[var(--brand-red)] rounded-full blur-3xl opacity-20 animate-pulse"></div>

  <!-- Card -->
  <div class="w-full max-w-md bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl p-8 relative z-10 animate-fadeIn">

    <!-- Logo -->
    <div class="flex justify-center mb-8">
      <img src="{{ asset('img/Finogic-logo.png') }}" alt="Finogic Logo" class="h-14 w-auto">
    </div>

    <!-- Header -->
    <div class="text-center mb-8">
      <h2 class="mt-2 text-3xl font-bold text-gray-800">Forgot Password?</h2>
      <p class="text-gray-500 text-sm mt-2">Enter your registered email and we’ll send you reset instructions.</p>
    </div>

    <!-- Success/Errors -->
    @if(session('status'))
        <div class="bg-green-100 text-green-800 p-3 rounded-lg mb-4 border-l-4 border-green-500 shadow-sm">
            {{ session('status') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-3 rounded-lg mb-4 border-l-4 border-red-500 shadow-sm">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form -->
    <form action="{{ route('user.forgot.password.send') }}" method="POST" class="space-y-6">
        @csrf
        <div class="relative">
            <input type="email" id="email" name="email" required
                   class="peer w-full px-4 pt-5 pb-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)] focus:outline-none transition"
                   placeholder=" " value="{{ old('email') }}" />
            <label for="email"
                   class="absolute left-3 top-2 text-gray-500 text-sm transition-all duration-200 peer-placeholder-shown:top-5 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-base peer-focus:top-2 peer-focus:text-sm peer-focus:text-[var(--brand-blue)]">
                Email Address
            </label>
        </div>

        <button type="submit"
                class="w-full bg-gradient-to-r from-[var(--brand-blue)] to-[var(--brand-red)] text-white py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition duration-300">
            Send Reset Link
        </button>
    </form>

    <!-- Footer -->
    <p class="text-center text-gray-500 text-sm mt-6">
      Remembered your password?
      <a href="{{ route('remittances.login') }}" class="text-[var(--brand-blue)] font-medium hover:underline">Login</a>
    </p>

  </div>
</body>
</html> --}}




<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password | Finogic</title>
<script src="https://cdn.tailwindcss.com"></script>

<link rel="icon" href="{{ asset('img/Finogic-fav.png') }}" type="image/png">

</head>
<body class="min-h-screen bg-gradient-to-r from-indigo-50 via-blue-50 to-cyan-50 flex items-center justify-center">

<div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
    <div class="text-center mb-6">
        <img src="{{ asset('img/Finogic-logo.png') }}" class="h-14 mx-auto">
        <h2 class="text-2xl font-bold mt-4">Forgot Password?</h2>
        <p class="text-gray-500 mt-2">Enter your registered email to get reset instructions.</p>
    </div>

    @if(session('status'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('user.forgot.password.send') }}" method="POST" class="space-y-6">
        @csrf
        <input type="email" name="email" required value="{{ old('email') }}">
        <button type="submit">Send Reset Link</button>
    </form>


    <p class="text-center text-gray-500 text-sm mt-6">
        Remembered your password? <a href="{{ route('remittances.login') }}" class="text-blue-600 hover:underline">Login</a>
    </p>
</div>
</body>
</html>
