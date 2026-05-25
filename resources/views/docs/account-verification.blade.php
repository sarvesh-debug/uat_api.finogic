@extends('users.layouts.app')

@section('content')

<div class="min-h-screen bg-gray-50">

<div class="flex">

<!-- Sidebar -->
<aside class="hidden xl:flex xl:flex-col w-72 bg-white border-r shadow-sm p-6 sticky top-0 h-screen overflow-y-auto">

<h2 class="text-2xl font-bold text-indigo-600 mb-6">
Verification API
</h2>

<nav class="space-y-2 text-sm">

<a href="#baseurl" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">
Base URL
</a>

<a href="#accountverify" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">
Account Verification
</a>

<a href="#vpaverify" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">
VPA Verification
</a>

</nav>

</aside>

<!-- Main Content -->
<main class="flex-1 p-6 space-y-12">

<!-- Base URL -->
<section id="baseurl" class="bg-white rounded-xl shadow p-6">

<h1 class="text-2xl font-bold mb-4">
Base URL
</h1>

<p class="text-gray-600 mb-4 text-sm">
All verification APIs use API-Key based authentication.
Requests must be sent to the following base URL.
</p>

<div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm">
https://yourdomain.com/api
</div>

<h3 class="font-semibold mt-6 mb-2">Required Headers</h3>

<p class="text-gray-600 text-sm mb-3">
These headers must be included in every API request.
</p>

<pre class="bg-gray-100 p-4 rounded text-sm">
X-API-KEY: your_api_key_here
X-MERCHANT-ID: your_merchant_id_here
Content-Type: application/json
</pre>

</section>


<!-- Account Verification -->
<section id="accountverify" class="bg-white rounded-xl shadow p-6">

<h2 class="text-xl font-bold mb-4">
1. Account Verification
</h2>

<p class="text-gray-600 text-sm mb-4">
This API verifies a bank account using the account number and IFSC code.
It returns the registered account holder name along with balance details
and verification status.
</p>

<div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-6">
<span class="text-yellow-300 font-bold">POST</span>
/v2/account/verify
</div>

<h3 class="font-semibold mb-2">Request Body</h3>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm overflow-x-auto">
{
  "outlet": "1001",
  "accountNumber": "123456789012",
  "ifsc": "SBIN0001234",
  "latitude": 28.6139,
  "longitude": 77.2090
}
</pre>

<h3 class="font-semibold mt-6 mb-2">Success Response</h3>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm overflow-x-auto">
{
 "success": true,
 "message": "Account verified successfully",
 "data": {
     "payee_name": "SARVESH PAL",
     "account_no": "123456789012",
     "ifsc": "",
     "deducted_amount": 11,
     "opening_balance": 3513.26,
     "closing_balance": 3502.26
 },
 "meta": {
     "request_id": "req_b9ddd662-fa6f-46a1-972d-741319209aae",
     "statuscode": "TXN",
     "ipay_uuid": "h000a139d388-f807-4776-8172-9df3d2b5b07c-m4gO2iWpldmE",
     "timestamp": "2026-03-05 13:25:42"
 }
}
</pre>

</section>


<!-- VPA Verification -->
<section id="vpaverify" class="bg-white rounded-xl shadow p-6">

<h2 class="text-xl font-bold mb-4">
2. VPA Verification
</h2>

<p class="text-gray-600 text-sm mb-4">
This API verifies a UPI Virtual Payment Address (VPA).
It returns the registered account holder name associated with the UPI ID.
</p>

<div class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm mb-6">
<span class="text-yellow-300 font-bold">POST</span>
/v2/vpa/verify
</div>

<h3 class="font-semibold mb-2">Request Body</h3>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm overflow-x-auto">
{
  "accountNumber": "962112215@ybl",
  "ifsc": "",
  "latitude": 28.6139,
  "longitude": 77.2090
}
</pre>

<h3 class="font-semibold mt-6 mb-2">Success Response</h3>

<pre class="bg-gray-900 text-green-400 p-4 rounded text-sm overflow-x-auto">
{
 "success": true,
 "message": "Account verified successfully",
 "data": {
     "payee_name": "SARVESH PAL",
     "account_no": "962112215@ybl",
     "ifsc": ""
 }
}
</pre>

</section>

</main>

</div>

</div>

@endsection