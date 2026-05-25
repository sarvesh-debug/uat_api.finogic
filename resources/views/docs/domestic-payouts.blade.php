@extends('users.layouts.app')

@section('content')

<div class="min-h-screen bg-gray-50">

    <div class="flex">

        <!-- Sidebar -->
        <aside class="hidden xl:flex xl:flex-col w-72 bg-white border-r shadow-sm p-6 sticky top-0 h-screen overflow-y-auto">

            <h2 class="text-2xl font-bold text-indigo-600 mb-6">
                Domestic Payout API v1
            </h2>

            <nav class="space-y-2 text-sm">
                <a href="#baseurl" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Base URL</a>
                <a href="#payoutapi" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Send Payout</a>
                <a href="#statusapi" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Payout Status</a>
                <a href="#webhook" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Webhook Callback</a>
                <a href="#balanceapi" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Wallet Balance</a>
            </nav>

        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6 space-y-12">

            <!-- Base URL -->
            <section id="baseurl" class="bg-white rounded-xl shadow p-6">

                <h1 class="text-2xl font-bold mb-4">Base URL</h1>

                <p class="text-gray-600 text-sm mb-4">
                    All Domestic Payout APIs must be called using the following base URL.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm">
                    https://yourdomain.com/api
                </div>

                <h3 class="font-semibold mt-6 mb-2">Required Headers (All APIs)</h3>

<pre class="bg-gray-100 p-4 rounded text-sm">
Content-Type: application/json
Accept: application/json
</pre>

            </section>

            <!-- 1️⃣ Send Payout -->
            <section id="payoutapi" class="bg-white rounded-xl shadow p-6">

                <h2 class="text-xl font-bold mb-4">1. Send Domestic Payout</h2>

                <p class="text-gray-600 text-sm mb-4">
                    Initiate a direct payout to a beneficiary’s bank account using verified account details and your API key.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-4">
                    <span class="text-yellow-300 font-bold">POST</span>
                    /initiate-transaction
                </div>

                <h3 class="font-semibold mb-2">Request Body</h3>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm overflow-x-auto">
{
  "apikey": "YOUR_API_KEY",
  "mobileNo": "9876543210",
  "txnAmount": 1500,
  "accountNo": "1234567890",
  "ifscCode": "HDFC0001234",
  "bankName": "HDFC Bank",
  "accountHolderName": "Alice Kumar",
  "RefNo": "TXNREF001",
  "web": "OWN"
}
</pre>

                <h3 class="font-semibold mt-6 mb-2">Success Response</h3>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm overflow-x-auto">
{
 "status": "Success",
 "message": "Payout Transaction successfully",
 "remId": "REM123456",
 "email": "demo@example.com",
 "payment_id": "PAY1234567890",
 "utr": "123456789012",
 "amount": "1000",
 "charge": 10,
 "gst": 5,
 "opening_balance": "50000.00",
 "closing_balance": 48985.00,
 "bank_name": "DEMO BANK LTD",
 "ifsc_code": "DEMO0123456",
 "acc_no": "123456789012",
 "beneficiary_name": "John Doe",
 "refId": "REF9876543210",
 "created_at": "2025-09-14T15:45:41Z"
}
</pre>

            </section>

            <!-- 2️⃣ Transaction Status -->
            <section id="statusapi" class="bg-white rounded-xl shadow p-6">

                <h2 class="text-xl font-bold mb-4">2. Check Payout Status</h2>

                <p class="text-gray-600 text-sm mb-4">
                    Fetch payout transaction status using payment_id returned during payout initiation.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-4">
                    <span class="text-yellow-300 font-bold">POST</span>
                    /get-transaction-status
                </div>

                <h3 class="font-semibold mb-2">Request Body</h3>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm">
{
  "apikey": "YOUR_API_KEY",
  "payment_id": "XPYT8A9BC123D"
}
</pre>

                <h3 class="font-semibold mt-6 mb-2">Success Response</h3>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm overflow-x-auto">
{
 "status": true,
 "message": "Transaction status fetched successfully.",
 "data": {
   "payment_id": "XPYT8A9BC123D",
   "amount": 1500,
   "charges": 15,
   "gst": 0.3,
   "status": "SUCCESS",
   "opening_balance": 5000,
   "closing_balance": 3484.7,
   "bank_name": "HDFC Bank",
   "ifsc": "HDFC0001234",
   "account_no": "1234567890",
   "beneficiary_name": "Alice Kumar",
   "ref_no": "TXNREF001",
   "created_at": "2025-09-08 12:00:00"
 }
}
</pre>

            </section>

            <!-- 3️⃣ Webhook -->
            <section id="webhook" class="bg-white rounded-xl shadow p-6">

                <h2 class="text-xl font-bold mb-4">3. Callback Notification (Webhook)</h2>

                <p class="text-gray-600 text-sm mb-4">
                    CodeGraphi automatically triggers a webhook when the payout transaction status changes.
                </p>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm overflow-x-auto">
{
 "service": "PAYOUT",
 "amount": 1500,
 "status": "SUCCESS",
 "utr": "HDFCREF987654",
 "orderId": "TXNREF001",
 "txnId": "TXN1233",
 "updated_at": "2025-09-08T12:35:00+05:30"
}
</pre>

                <h3 class="font-semibold mt-6 mb-2">Expected Client Response</h3>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm">
{
 "acknowledged": true
}
</pre>

            </section>

            <!-- 4️⃣ Balance API -->
            <section id="balanceapi" class="bg-white rounded-xl shadow p-6">

                <h2 class="text-xl font-bold mb-4">4. Fetch Wallet Balance</h2>

                <p class="text-gray-600 text-sm mb-4">
                    Retrieve the live wallet balance of a registered CodeGraphi user.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-4">
                    <span class="text-yellow-300 font-bold">POST</span>
                    /cg/payout/v2/fetch-balance
                </div>

                <h3 class="font-semibold mb-2">Request Body</h3>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm">
{
 "email": "user@example.com"
}
</pre>

                <h3 class="font-semibold mt-6 mb-2">Success Response</h3>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm overflow-x-auto">
{
 "status": true,
 "code": 200,
 "message": "Balance fetched successfully",
 "data": {
   "email": "user@example.com",
   "available_balance": "15430.50",
   "wallet_type": "main_wallet",
   "currency": "INR",
   "last_updated": "2025-10-04T11:30:45Z"
 }
}
</pre>

            </section>

        </main>

    </div>

</div>

@endsection