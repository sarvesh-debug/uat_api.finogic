<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Finogic B2B Onboarding</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <link rel="icon" href="{{ asset('img/Finogic-fav.png') }}" type="image/png">

</head>
<body class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 min-h-screen flex items-center justify-center">
    
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md">
        <!-- Heading -->
        <div class="text-center mb-6">
            <h2 class="text-3xl font-extrabold text-gray-800">🔐 Finogic</h2>
            <p class="text-gray-500 text-sm mt-1">B2B Onboarding Portal</p>
        </div>

        <!-- Error Message -->
        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('superadmin.login.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-gray-700 font-medium mb-1">Email</label>
                <input type="email" name="email" 
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none transition" 
                       placeholder="you@example.com" required>
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-1">Password</label>
                <input type="password" name="password" 
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none transition" 
                       placeholder="••••••••" required>
            </div>
            
            <!-- Forgot + Submit -->
            <div class="flex justify-between items-center text-sm">
                <a href="{{ route('superadmin.forgot.form') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Forgot password?</a>
            </div>

            <button type="submit" 
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-semibold shadow-md hover:bg-indigo-700 hover:shadow-lg transition">
                Sign In
            </button>
        </form>
    </div>
</body>
</html>
