<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="icon" href="{{ asset('img/aarpiz-fav.png') }}" type="image/png">

</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">

    <div class="bg-white shadow-lg rounded-2xl p-8 max-w-md w-full">
        <!-- Heading -->
        <h2 class="text-2xl font-bold text-gray-800 text-center">OTP Verification</h2>
        <p class="text-gray-500 text-center mt-2">
            Enter the 6-digit code sent to your mobile/email
        </p>

        <!-- OTP Form -->
        <form action="{{ route('superadmin.otp.verify') }}" method="POST" class="mt-6">
            @csrf

            <div class="flex justify-between gap-2">
                @for ($i = 0; $i < 6; $i++)
                    <input type="text" name="otp[]" maxlength="1"
                        class="otp-input w-12 h-12 text-center border border-gray-300 rounded-lg 
                               focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg font-semibold"
                        required />
                @endfor
            </div>

            <!-- Verify Button -->
            <button type="submit"
                class="w-full mt-6 bg-blue-600 text-white font-semibold py-3 rounded-lg shadow-md hover:bg-blue-700 transition">
                Verify OTP
            </button>
        </form>

        <!-- Resend -->
        {{-- <div class="text-center mt-4">
            <p class="text-gray-600">
                Didn’t receive the code?
                <a href="{{ route('otp.resend') }}" class="text-blue-600 font-medium hover:underline">Resend</a>
            </p>
        </div> --}}
    </div>

    <script>
        // Auto move between OTP inputs
        const inputs = document.querySelectorAll('.otp-input');
        inputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                if (input.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === "Backspace" && !input.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });
    </script>

</body>
</html>
