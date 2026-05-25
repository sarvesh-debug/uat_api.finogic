@extends('users.layouts.app')

@section('content')

<div class="min-h-screen bg-gray-50">

    <div class="flex">

        <!-- Sidebar -->
        <aside class="hidden xl:flex xl:flex-col w-72 bg-white border-r shadow-sm p-6 sticky top-0 h-screen overflow-y-auto">

            <h2 class="text-2xl font-bold text-indigo-600 mb-6">
                DMT API v1
            </h2>

            <nav class="space-y-2 text-sm">
                <a href="#bank" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Bank Details</a>
                <a href="#remitter-profile" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Remitter Profile</a>
                <a href="#remitter-register" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Remitter Registration</a>
                <a href="#verify-remitter" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Verify Remitter</a>
                <a href="#kyc" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Remitter KYC</a>
                <a href="#beneficiary" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Add Beneficiary</a>
                <a href="#verify-beneficiary" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Verify Beneficiary</a>
                <a href="#delete-beneficiary" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Delete Beneficiary</a>
                <a href="#txn-otp" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Generate OTP</a>
                <a href="#transaction" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">DMT Transaction</a>
            </nav>

        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6 space-y-12">

            <!-- Base URL -->
            <section class="bg-white rounded-xl shadow p-6">
                <h1 class="text-2xl font-bold mb-4">Base URL</h1>

                <p class="text-gray-600 text-sm mb-4">
                    All DMT APIs must be called using the base URL below.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm">
                   /v1/dmt
                </div>

                <h3 class="font-semibold mt-6 mb-2">Headers (Required)</h3>

<pre class="bg-gray-100 p-4 rounded text-sm">
X-API-KEY: your_api_key
X-MERCHANT-ID: your_merchant_id
Content-Type: application/json
</pre>
            </section>

            <!-- 1 Bank Details -->
            <section id="bank" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">1. Bank Details</h2>

                <p class="text-gray-600 text-sm mb-4">
                    Get list of supported banks for DMT transactions.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-4">
                    POST /BankDetails
                </div>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm">
{
  "outLet": "1"
}
</pre>
            </section>

            <!-- 2 Remitter Profile -->
            <section id="remitter-profile" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">2. Remitter Profile</h2>

                <p class="text-gray-600 text-sm mb-4">
                    Check if remitter exists and fetch details.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-4">
                    POST /remitterProfile
                </div>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm">
{
  "outlet": "1",
  "mobileNumber": "9876543210",
  "txnMode": "IMPS"
}
</pre>
            </section>

            <!-- 3 Remitter Registration -->
            <section id="remitter-register" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">3. Remitter Registration</h2>

                <p class="text-gray-600 text-sm mb-4">
                    Register a new remitter using Aadhaar.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-4">
                    POST /remitterRegistration
                </div>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm">
{
  "outlet": "1",
  "mobileNumber": "9876543210",
  "aadhaarNumber": "123412341234",
  "referenceKey": "ref123"
}
</pre>
            </section>

            <!-- 4 Verify Remitter -->
            <section id="verify-remitter" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">4. Verify Remitter</h2>

                <p class="text-gray-600 text-sm mb-4">
                    Verify remitter using OTP.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-4">
                    POST /verifyRemitterRegistration
                </div>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm">
{
  "outlet": "1",
  "mobileNumber": "9876543210",
  "otp": "123456",
  "referenceKey": "ref123"
}
</pre>
            </section>

            <!-- 5 KYC -->
            <section id="kyc" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">5. Remitter KYC</h2>

                <p class="text-gray-600 text-sm mb-4">
                    Complete KYC using biometric & location data.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-4">
                    POST /remitterKyc
                </div>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm">
{
  "outlet": "1",
  "mobileNumber": "9876543210",
  "referenceKey": "ref123",
  "latitude": "28.6139",
  "longitude": "77.2090",
  "externalRef": "ext123",
  "biometricData": "{}"
}
</pre>
            </section>

            <!-- 6 Add Beneficiary -->
            <section id="beneficiary" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">6. Add Beneficiary</h2>

                <p class="text-gray-600 text-sm mb-4">
                    Add new beneficiary account for transfer.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-4">
                    POST /beneficiaryRegistration
                </div>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm">
{
  "outlet": "1",
  "beneficiaryMobileNumber": "9876543210",
  "remitterMobileNumber": "9876543210",
  "accountNumber": "1234567890",
  "ifsc": "SBIN0001234",
  "bankId": "SBI",
  "name": "Test User"
}
</pre>
            </section>

            <!-- 7 Verify Beneficiary -->
            <section id="verify-beneficiary" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">7. Verify Beneficiary</h2>

                <p class="text-gray-600 text-sm mb-4">
                    Verify beneficiary using OTP.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-4">
                    POST /verifyBeneficiaryRegistration
                </div>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm">
{
  "outlet": "1",
  "remitterMobileNumber": "9876543210",
  "otp": "123456",
  "beneficiaryId": "bene123",
  "referenceKey": "ref123"
}
</pre>
            </section>

            <!-- 8 Delete Beneficiary -->
            <section id="delete-beneficiary" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">8. Delete Beneficiary</h2>

                <p class="text-gray-600 text-sm mb-4">
                    Delete beneficiary account.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-4">
                    POST /deleteBeneficiary
                </div>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm">
{
  "outlet": "1",
  "remitterMobileNumber": "9876543210",
  "beneficiaryId": "bene123"
}
</pre>
            </section>

            <!-- 9 OTP -->
            <section id="txn-otp" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">9. Generate Transaction OTP</h2>

                <p class="text-gray-600 text-sm mb-4">
                    Generate OTP before transaction.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-4">
                    POST /generateTransactionOtp
                </div>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm">
{
  "outlet": "1",
  "remitterMobileNumber": "9876543210",
  "amount": "500",
  "referenceKey": "ref123"
}
</pre>
            </section>

            <!-- 10 Transaction -->
            <section id="transaction" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">10. DMT Transaction</h2>

                <p class="text-gray-600 text-sm mb-4">
                    Perform final money transfer using OTP verification.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-4">
                    POST /dmtTransaction
                </div>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm">
{
  "outlet": "1",
  "remitterMobileNumber": "9876543210",
  "accountNumber": "1234567890",
  "ifsc": "SBIN0001234",
  "transferMode": "IMPS",
  "transferAmount": "500",
  "latitude": "28.6139",
  "longitude": "77.2090",
  "referenceKey": "ref123",
  "otp": "123456",
  "externalRef": "ext123"
}
</pre>
            </section>

        </main>

    </div>

</div>

@endsection