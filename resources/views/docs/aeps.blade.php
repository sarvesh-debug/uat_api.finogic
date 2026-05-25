@extends('users.layouts.app')

@section('content')

<div class="min-h-screen bg-gray-50">

    <div class="flex">

        <!-- Sidebar -->
        <aside class="hidden xl:flex xl:flex-col w-72 bg-white border-r shadow-sm p-6 sticky top-0 h-screen overflow-y-auto">

            <h2 class="text-2xl font-bold text-indigo-600 mb-6">
                AEPS API v1
            </h2>

            <nav class="space-y-2 text-sm">
                <a href="#loginstatus" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Outlet Login Status</a>
                <a href="#login" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Outlet Login</a>
                <a href="#withdrawal" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Cash Withdrawal</a>
                <a href="#balance" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Balance Inquiry</a>
                <a href="#mini" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Mini Statement</a>
                <a href="#banks" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Banks List</a>
            </nav>

        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6 space-y-12">

            <!-- Base URL Section -->
            <section class="bg-white rounded-xl shadow p-6">
                <h1 class="text-2xl font-bold mb-4">Base URL</h1>

                <p class="text-gray-600 mb-4 text-sm">
                    All AEPS API requests must be sent to the following base URL.
                    Append the endpoint path shown in each section below.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm">
                    https://yourdomain.com/api/v1/aeps
                </div>

                <h3 class="font-semibold mt-6 mb-2">Required Headers (All APIs)</h3>

                <p class="text-gray-600 text-sm mb-3">
                    Every request must include these headers for authentication and authorization.
                </p>

<pre class="bg-gray-100 p-4 rounded text-sm">
X-API-KEY: your_api_key
X-MERCHANT-ID: your_merchant_id
Content-Type: application/json
</pre>
            </section>

            <!-- 1️⃣ Outlet Login Status -->
            <section id="loginstatus" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">1. Outlet Login Status</h2>

                <p class="text-gray-600 text-sm mb-4">
                    This API checks whether the outlet is currently logged in for AEPS transactions.
                    Always call this API before performing withdrawal or balance operations
                    to verify the session status.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-6">
                    <span class="text-yellow-300 font-bold">POST</span>
                    /outletLoginStatus
                </div>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm overflow-x-auto">
{
  "outLet": "OUT12345"
}
</pre>
            </section>

            <!-- 2️⃣ Outlet Login -->
            <section id="login" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">2. Outlet Login (Biometric)</h2>

                <p class="text-gray-600 text-sm mb-4">
                    This API logs in an outlet using biometric authentication.
                    Aadhaar number and encoded PID biometric data are required.
                    Location coordinates must also be provided.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-6">
                    <span class="text-yellow-300 font-bold">POST</span>
                    /outletLogin
                </div>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm overflow-x-auto">
{
  "outLet": "OUT12345",
  "type": "BIOMETRIC",
  "latitude": "28.6139",
  "longitude": "77.2090",
  "aadhaar": "123456789012",
  "biometricData": "encoded_pid_data_here"
}
</pre>
            </section>

            <!-- 3️⃣ Cash Withdrawal -->
            <section id="withdrawal" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">3. Cash Withdrawal</h2>

                <p class="text-gray-600 text-sm mb-4">
                    Use this API to perform AEPS cash withdrawal transactions.
                    Requires Aadhaar number, bank IIN, biometric data, and transaction amount.
                    The outlet must be logged in before initiating withdrawal.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-6">
                    <span class="text-yellow-300 font-bold">POST</span>
                    /cashWithdrawal
                </div>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm overflow-x-auto">
{
  "outLet": "OUT12345",
  "bankiin": "607152",
  "latitude": "28.6139",
  "longitude": "77.2090",
  "mobile": "9876543210",
  "amount": 1000,
  "aadhaar": "123456789012",
  "biometricData": "encoded_pid_data_here"
}
</pre>
            </section>

            <!-- 4️⃣ Balance Inquiry -->
            <section id="balance" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">4. Balance Inquiry</h2>

                <p class="text-gray-600 text-sm mb-4">
                    This API retrieves the available account balance using Aadhaar-based authentication.
                    Biometric verification is mandatory for security compliance.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-6">
                    <span class="text-yellow-300 font-bold">POST</span>
                    /balanceInquiry
                </div>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm overflow-x-auto">
{
  "outLet": "OUT12345",
  "bankiin": "607152",
  "latitude": "28.6139",
  "longitude": "77.2090",
  "mobile": "9876543210",
  "aadhaar": "123456789012",
  "biometricData": "encoded_pid_data_here"
}
</pre>
            </section>

            <!-- 5️⃣ Mini Statement -->
            <section id="mini" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">5. Mini Statement</h2>

                <p class="text-gray-600 text-sm mb-4">
                    Use this API to fetch the customer's recent transactions (mini statement).
                    Aadhaar and biometric authentication are required.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-6">
                    <span class="text-yellow-300 font-bold">POST</span>
                    /miniStatement
                </div>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm overflow-x-auto">
{
  "outLet": "OUT12345",
  "bankiin": "607152",
  "latitude": "28.6139",
  "longitude": "77.2090",
  "mobile": "9876543210",
  "aadhaar": "123456789012",
  "biometricData": "encoded_pid_data_here"
}
</pre>
            </section>

            <!-- 6️⃣ Banks List -->
            <section id="banks" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">6. AEPS Banks List</h2>

                <p class="text-gray-600 text-sm mb-4">
                    This API returns the list of all supported AEPS banks along with their bank IIN codes.
                    Use the bank IIN from this list when performing withdrawal, balance inquiry,
                    or mini statement transactions.
                </p>

                <div class="bg-gray-900 text-blue-400 p-4 rounded-lg text-sm mb-6">
                    <span class="text-blue-300 font-bold">GET</span>
                    /banks
                </div>

            </section>

        </main>

    </div>

</div>

@endsection