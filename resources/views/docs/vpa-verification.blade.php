@extends('users.layouts.app')

@section('content')

<div class="min-h-screen bg-gray-50">
<div class="max-w-6xl mx-auto px-6 py-10">

<!-- Page Title -->
<div class="mb-10">
<h1 class="text-3xl font-bold text-gray-800">VPA Verification API</h1>
<p class="text-gray-600 mt-2">
Verify a UPI Virtual Payment Address (VPA) and retrieve the registered account holder name.
</p>
</div>

<!-- Overview -->
<div class="bg-white shadow-sm rounded-xl p-6 mb-8">
<h2 class="text-xl font-semibold text-gray-800 mb-3">Overview</h2>
<p class="text-gray-600">
The VPA Verification API allows merchants to validate a UPI ID before initiating payouts.
It returns the name associated with the provided Virtual Payment Address.
</p>
</div>

<!-- Endpoint -->
<div class="bg-white shadow-sm rounded-xl p-6 mb-8">
<h2 class="text-xl font-semibold text-gray-800 mb-3">Endpoint</h2>

<div class="bg-gray-900 text-green-400 text-sm rounded-lg p-4 font-mono">
POST /api/v2/vpa/verify
</div>

</div>

<!-- Headers -->
<div class="bg-white shadow-sm rounded-xl p-6 mb-8">
<h2 class="text-xl font-semibold text-gray-800 mb-4">Required Headers</h2>

<div class="overflow-x-auto">
<table class="w-full text-sm border">
<thead class="bg-gray-100">
<tr>
<th class="p-3 text-left">Header</th>
<th class="p-3 text-left">Value</th>
</tr>
</thead>

<tbody class="divide-y">
<tr>
<td class="p-3 font-medium">X-API-KEY</td>
<td class="p-3 text-gray-600">Your API Key</td>
</tr>

<tr>
<td class="p-3 font-medium">X-MERCHANT-ID</td>
<td class="p-3 text-gray-600">Your Merchant ID</td>
</tr>

<tr>
<td class="p-3 font-medium">Content-Type</td>
<td class="p-3 text-gray-600">application/json</td>
</tr>
</tbody>
</table>
</div>

</div>

<!-- Request Parameters -->
<div class="bg-white shadow-sm rounded-xl p-6 mb-8">
<h2 class="text-xl font-semibold text-gray-800 mb-4">Request Parameters</h2>

<div class="overflow-x-auto">
<table class="w-full text-sm border">
<thead class="bg-gray-100">
<tr>
<th class="p-3 text-left">Parameter</th>
<th class="p-3 text-left">Type</th>
<th class="p-3 text-left">Required</th>
<th class="p-3 text-left">Description</th>
</tr>
</thead>

<tbody class="divide-y">

<tr>
<td class="p-3 font-medium">accountNumber</td>
<td class="p-3">string</td>
<td class="p-3">Yes</td>
<td class="p-3 text-gray-600">UPI ID (VPA) to verify</td>
</tr>

<tr>
<td class="p-3 font-medium">ifsc</td>
<td class="p-3">string</td>
<td class="p-3">No</td>
<td class="p-3 text-gray-600">Leave blank for VPA verification</td>
</tr>

<tr>
<td class="p-3 font-medium">latitude</td>
<td class="p-3">float</td>
<td class="p-3">Yes</td>
<td class="p-3 text-gray-600">Merchant latitude</td>
</tr>

<tr>
<td class="p-3 font-medium">longitude</td>
<td class="p-3">float</td>
<td class="p-3">Yes</td>
<td class="p-3 text-gray-600">Merchant longitude</td>
</tr>

</tbody>
</table>
</div>

</div>

<!-- Example Request -->
<div class="bg-white shadow-sm rounded-xl p-6 mb-8">

<h2 class="text-xl font-semibold text-gray-800 mb-4">Example Request</h2>

<pre class="bg-gray-900 text-green-400 text-sm rounded-lg p-4 overflow-x-auto">
{
  "accountNumber": "962112215@ybl",
  "ifsc": "",
  "latitude": 28.6139,
  "longitude": 77.2090
}
</pre>

</div>

<!-- Success Response -->
<div class="bg-white shadow-sm rounded-xl p-6 mb-8">

<h2 class="text-xl font-semibold text-gray-800 mb-4">Success Response</h2>

<pre class="bg-gray-900 text-green-400 text-sm rounded-lg p-4 overflow-x-auto">
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

</div>

<!-- Error Response -->
<div class="bg-white shadow-sm rounded-xl p-6">

<h2 class="text-xl font-semibold text-gray-800 mb-4">Error Response</h2>

<pre class="bg-gray-900 text-red-400 text-sm rounded-lg p-4 overflow-x-auto">
{
 "success": false,
 "message": "Invalid VPA"
}
</pre>

</div>

</div>
</div>

@endsection