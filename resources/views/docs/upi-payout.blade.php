@extends('users.layouts.app')

@section('content')

<div class="min-h-screen bg-gray-50">

    <div class="flex">

        <!-- Sidebar -->
        <aside class="hidden xl:flex xl:flex-col w-72 bg-white border-r shadow-sm p-6 sticky top-0 h-screen overflow-y-auto">

            <h2 class="text-2xl font-bold text-indigo-600 mb-6">
                UPI Payout API v1
            </h2>

            <nav class="space-y-2 text-sm">
                <a href="#baseurl" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Base URL</a>
                <a href="#upipayout" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">UPI Intent</a>
                <a href="#statusapi" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">UPI Status</a>
            </nav>

        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6 space-y-12">

            <!-- Base URL -->
            <section id="baseurl" class="bg-white rounded-xl shadow p-6">
                <h1 class="text-2xl font-bold mb-4">Base URL</h1>

                <p class="text-gray-600 text-sm mb-4">
                    All UPI Payout APIs must be called using the following base URL.
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

            <!-- 1️⃣ UPI Intent -->
            <section id="upipayout" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">1. UPI Payout (Intent API)</h2>

                <p class="text-gray-600 text-sm mb-4">
                    Sends instant UPI payout to a beneficiary using UPI ID.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-4">
                    <span class="text-yellow-300 font-bold">POST</span>
                    /cg/v2/upipayout
                </div>

                <h3 class="font-semibold mb-2">Request Body</h3>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm">
{
  "apikey": "YOUR_API_KEY",
  "txnAmount": 100,
  "upiId": "example@upi",
  "name": "John Doe",
  "RefNo": "REF123456"
}
</pre>

                <h3 class="font-semibold mt-6 mb-2">Success Response</h3>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm overflow-x-auto">
{
  "status": true,
  "message": "Payout successful",
  "data": {
      "remId": "RTPX7X3210",
      "email": "user@email.com",
      "payment_id": "XPUPIJX2KF1UPOD",
      "utr": "531540589526",
      "bank_txn_id": "531540589526",
      "amount": 100,
      "charge": 1,
      "gst": 0.18,
      "status": "success",
      "opening_balance": 1323.88,
      "closing_balance": 1222.7,
      "refId": "REF123456",
      "provider_code": 200
  }
}
</pre>

            </section>

            <!-- 2️⃣ Status API -->
            <section id="statusapi" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">2. UPI Payout Status</h2>

                <p class="text-gray-600 text-sm mb-4">
                    Fetch payout transaction status using payment_id.
                </p>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-4">
                    <span class="text-yellow-300 font-bold">POST</span>
                    /v1/payouts/upi/status
                </div>

                <h3 class="font-semibold mb-2">Request Body</h3>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm">
{
  "apikey": "YOUR_API_KEY",
  "payment_id": "XPUPI2E4F0A9C5B"
}
</pre>

                <h3 class="font-semibold mt-6 mb-2">Response</h3>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm">
{
  "status": true,
  "message": "Status fetched successfully",
  "data": {
      "payment_id": "XPUPI2E4F0A9C5B",
      "status": "success",
      "utr": "531540589526"
  }
}
</pre>

            </section>

        </main>

    </div>

</div>

@endsection