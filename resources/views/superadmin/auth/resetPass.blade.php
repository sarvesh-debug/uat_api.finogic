<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <!-- ✅ Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="icon" href="{{ asset('img/Finogic-fav.png') }}" type="image/png">

</head>
<body class="bg-gradient-to-r from-indigo-500 to-blue-600 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md bg-white shadow-2xl rounded-2xl p-8 border border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Reset Password</h2>

        <!-- ✅ Success / Status message -->
        @if(session('status'))
            <div class="mb-4 p-3 rounded-lg border border-green-400 bg-green-100 text-green-700 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <!-- ✅ Validation errors -->
        @if($errors->any())
            <div class="mb-4 p-3 rounded-lg border border-red-400 bg-red-100 text-red-700 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('superadmin.password.reset') }}" class="space-y-5">
            @csrf

            <!-- New Password -->
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700">New Password</label>
                <input type="password" name="password" id="password" required
                       class="mt-1 block w-full rounded-lg border border-gray-300 p-3 text-sm
                              focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 outline-none transition">
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                       class="mt-1 block w-full rounded-lg border border-gray-300 p-3 text-sm
                              focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400 outline-none transition">
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold 
                       rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-1 transition">
                Reset Password
            </button>
        </form>

        <div class="text-center mt-6">
            <a href="{{ route('superadmin.login.form') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 transition">
                ← Back to Login
            </a>
        </div>
    </div>

</body>
</html>
