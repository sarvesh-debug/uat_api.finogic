<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - aarpiz B2B Onboarding</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <link rel="icon" href="{{ asset('img/aarpiz-fav.png') }}" type="image/png">

</head>
<body class="bg-gradient-to-r from-pink-500 to-red-500 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Forgot Password</h2>

        {{-- ✅ Success Message --}}
        @if (session('status'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                {{ session('status') }}
            </div>
        @endif

        {{-- ❌ Error Messages --}}
        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('superadmin.forgot.send') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-gray-700 font-medium">Email</label>
                <input type="email" name="email" class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-pink-300" required>
            </div>
            <button type="submit" class="w-full bg-pink-600 text-white py-2 rounded-lg hover:bg-pink-700 transition duration-200">
                Send Reset Link
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="{{ route('superadmin.login.form') }} " class="text-sm text-pink-600 hover:text-pink-800">Back to Login</a>
        </div>
    </div>
</body>
</html>
