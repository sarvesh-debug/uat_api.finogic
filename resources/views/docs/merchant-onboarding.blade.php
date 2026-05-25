@extends('users.layouts.app')

@section('content')

<div class="min-h-screen bg-gray-50">

    <div class="flex">

        <!-- Sidebar -->
        <aside class="hidden xl:flex xl:flex-col w-72 bg-white border-r shadow-sm p-6 sticky top-0 h-screen">

            <h2 class="text-2xl font-bold text-indigo-600 mb-6">
                Merchant Onboarding
            </h2>

            <nav class="space-y-2 text-sm">
                <a href="#signup" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Merchant Boarding</a>
                <a href="#verify" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Verify OTP</a>
                <a href="#clientlist" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Client List</a>
            </nav>

        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6 space-y-12">

            <!-- ========================= -->
            <!-- 1️⃣ MERCHANT BOARDING -->
            <!-- ========================= -->
            <section id="signup" class="bg-white rounded-xl shadow p-6">

                <h1 class="text-2xl font-bold mb-4">Merchant Boarding</h1>

                <p class="text-gray-600 text-sm mb-4">
                    This API is used to onboard a new merchant into the system.
                    Merchant KYC details such as Aadhaar, PAN, bank account details,
                    and geo-location must be provided.
                    An OTP will be sent to the registered mobile number for verification.
                </p>

                <!-- Endpoint -->
                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-6">
                    <span class="text-yellow-300 font-bold">POST</span>
                    https://api.yourdomain.com/api/user/onboard/signup
                </div>

                <!-- Headers -->
                <h3 class="font-semibold mb-2">Headers</h3>
                <p class="text-gray-600 text-sm mb-2">
                    All onboarding APIs require the following authentication headers.
                </p>
                <pre class="bg-gray-100 p-4 rounded text-sm">
Content-Type: application/json
Accept: application/json
X-API-KEY: YOUR_API_KEY_HERE
X-MERCHANT-ID: YOUR_MERCHANT_ID_HERE
                </pre>

                <!-- Request Body -->
                <h3 class="font-semibold mt-6 mb-2">Request Body</h3>
                <p class="text-gray-600 text-sm mb-3">
                    Provide merchant personal details, banking information,
                    location coordinates, and consent flag.
                </p>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm overflow-x-auto">
{
  "mobile": "9876543210",
  "email": "user@example.com",
  "aadhaar": "123412341234",
  "pan": "ABCDE1234F",
  "bankAccountNo": "123456789012",
  "bankIfsc": "HDFC0001234",
  "latitude": "28.6139",
  "longitude": "77.2090",
  "consent": "Y"
}
</pre>

            </section>

            <!-- ========================= -->
            <!-- 2️⃣ VERIFY OTP -->
            <!-- ========================= -->
            <section id="verify" class="bg-white rounded-xl shadow p-6">

                <h1 class="text-2xl font-bold mb-4">Merchant Boarding Verify</h1>

                <p class="text-gray-600 text-sm mb-4">
                    After successful signup request, an OTP is sent to the merchant's
                    registered mobile number. This API verifies the OTP and completes
                    the onboarding process.
                </p>

                <!-- Endpoint -->
                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-6">
                    <span class="text-yellow-300 font-bold">POST</span>
                    https://api.yourdomain.com/api/user/onboard/signup/verify
                </div>

                <!-- Headers -->
                <h3 class="font-semibold mb-2">Headers</h3>
                <pre class="bg-gray-100 p-4 rounded text-sm">
Content-Type: application/json
Accept: application/json
X-API-KEY: YOUR_API_KEY_HERE
X-MERCHANT-ID: YOUR_MERCHANT_ID_HERE
                </pre>

                <!-- Request Body -->
                <h3 class="font-semibold mt-6 mb-2">Request Body</h3>
                <p class="text-gray-600 text-sm mb-3">
                    Provide the OTP reference ID received during signup,
                    the OTP entered by the user, and the hash value for validation.
                </p>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm overflow-x-auto">
{
  "otpReferenceID": "OTP123456789",
  "otp": "123456",
  "hash": "xyz123hashvalue"
}
</pre>

            </section>

            <!-- ========================= -->
            <!-- 3️⃣ CLIENT LIST -->
            <!-- ========================= -->
            <section id="clientlist" class="bg-white rounded-xl shadow p-6">

                <h1 class="text-2xl font-bold mb-4">Merchant Client List</h1>

                <p class="text-gray-600 text-sm mb-4">
                    This API returns the list of all onboarded clients under
                    the authenticated merchant. Useful for dashboard display,
                    reporting, and management purposes.
                </p>

                <!-- Endpoint -->
                <div class="bg-gray-900 text-blue-400 p-4 rounded-lg text-sm mb-6">
                    <span class="text-blue-300 font-bold">GET</span>
                    https://yourdomain.com/api/v1/merchant/client-list
                </div>

                <!-- Headers -->
                <h3 class="font-semibold mb-2">Headers</h3>
                <pre class="bg-gray-100 p-4 rounded text-sm">
Accept: application/json
X-API-KEY: YOUR_API_KEY_HERE
X-MERCHANT-ID: YOUR_MERCHANT_ID_HERE
                </pre>

            </section>

        </main>

    </div>

</div>

@endsection