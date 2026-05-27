<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Finogic | Forgot Password</title>

  <link rel="icon" href="{{ asset('img/Finogic-fav.png') }}" type="image/png">

  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    :root {
      --brand-blue: #0a22aa;
      --brand-red: #b62512;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn {
      animation: fadeIn 0.8s ease-out forwards;
    }
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
      <img src="{{ asset('img/Finogic-logo.png') }}" alt="Finogic Logo" class="h-14 w-auto drop-md">
    </div>

    <!-- Header -->
    <div class="text-center mb-8">
      <h2 class="mt-2 text-3xl font-bold text-gray-800">Forgot Password?</h2>
      <p class="text-gray-500 text-sm mt-2">Enter your registered email and we’ll send you reset instructions.</p>
    </div>

    <!-- Form -->
    <form action="#" method="POST" class="space-y-6">
      <!-- Email Input -->
      <div class="relative">
        <input type="email" id="email" name="email" required
               class="peer w-full px-4 pt-5 pb-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)] focus:outline-none transition"
               placeholder=" " />
        <label for="email"
               class="absolute left-3 top-2 text-gray-500 text-sm transition-all duration-200 peer-placeholder-shown:top-5 peer-placeholder-shown:text-gray-400 peer-placeholder-shown:text-base peer-focus:top-2 peer-focus:text-sm peer-focus:text-[var(--brand-blue)]">
          Email Address
        </label>
      </div>

      <!-- Submit Button -->
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
</html>
