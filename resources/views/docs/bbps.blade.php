@extends('users.layouts.app')

@section('content')

<div class="min-h-screen bg-gray-50">

    <div class="flex">

        <!-- SIDEBAR -->
        <aside class="hidden xl:flex xl:flex-col w-72 bg-white border-r shadow-sm p-6 sticky top-0 h-screen overflow-y-auto">

            <h2 class="text-2xl font-bold text-indigo-600 mb-6">
                BBPS API v1
            </h2>

            <nav class="space-y-2 text-sm">

                <a href="#base" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Base URL</a>
                <a href="#headers" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">Headers</a>

                <a href="#telecom" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">
                    Get Telecom Circle
                </a>

                <a href="#recharge" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">
                    Get Recharge Plan
                </a>

                <a href="#category" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">
                    Get Category
                </a>

                <a href="#billers" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">
                    Get Billers
                </a>

                <a href="#billerdetails" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">
                    Biller Details
                </a>

                <a href="#enquiry" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">
                    Bill Enquiry
                </a>

                <a href="#validate" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">
                    Validate Billers
                </a>

                <a href="#payment" class="block px-4 py-2 rounded-lg hover:bg-indigo-50">
                    Payment Biller
                </a>

            </nav>

        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 p-6 space-y-12">

            <!-- TITLE -->
            <section class="bg-white rounded-xl shadow p-6">
                <h1 class="text-2xl font-bold mb-2">
                    BBPS API Documentation
                </h1>
                <p class="text-gray-500 text-sm">
                    Bharat Bill Payment System APIs for telecom, recharge, billers & payments.
                </p>
            </section>

            <!-- BASE URL -->
            <section id="base" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">Base URL</h2>

                <div class="bg-gray-900 text-green-400 p-4 rounded text-sm">
                    {{ config('app.url') }}/api
                </div>
            </section>

            <!-- HEADERS -->
            <section id="headers" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">Headers</h2>

<pre class="bg-black text-green-400 p-4 rounded text-sm">
X-API-KEY: YOUR_API_KEY
X-MERCHANT-ID: YOUR_MERCHANT_ID
Content-Type: application/json
</pre>
            </section>

            <!-- 1 -->
            <section id="telecom" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">1. Get Telecom Circle</h2>

                <div class="bg-gray-900 text-green-400 p-4 rounded mb-4 text-sm">
                    <span class="text-yellow-300 font-bold">POST</span>
                    /v1/bbps/getTelecomCircle
                </div>

<pre class="bg-gray-900 text-white p-4 rounded text-sm">
{
  "outLet": "OUT123"
}
</pre>
            </section>

            <!-- 2 -->
            <section id="recharge" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">2. Get Recharge Plan</h2>

                <div class="bg-gray-900 text-green-400 p-4 rounded mb-4 text-sm">
                    <span class="text-yellow-300 font-bold">POST</span>
                    /v1/bbps/getRechargePlan
                </div>

<pre class="bg-gray-900 text-white p-4 rounded text-sm">
{
  "outLet": "496699",
  "subProductCode": "MOB",
  "telecomCircle": "DEL",
  "latitude": "28.7041",
  "longitude": "77.1025",
  "externalRef": "REF123"
}
</pre>
            </section>

            <!-- 3 -->
            <section id="category" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">3. Get Category</h2>

                <div class="bg-gray-900 text-green-400 p-4 rounded mb-4 text-sm">
                    <span class="text-yellow-300 font-bold">POST</span>
                    /v1/bbps/getCategory
                </div>

<pre class="bg-gray-900 text-white p-4 rounded text-sm">
{
  "outLet": "OUT123"
}
</pre>
            </section>

            <!-- 4 -->
            <section id="billers" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">4. Get Billers</h2>

                <div class="bg-gray-900 text-green-400 p-4 rounded mb-4 text-sm">
                    <span class="text-yellow-300 font-bold">POST</span>
                    /v1/bbps/getBillers
                </div>

<pre class="bg-gray-900 text-white p-4 rounded text-sm">
{
  "outLet": "OUT123",
  "pageNumber": 1,
  "recordsPerPage": 10,
  "categoryKey": "C00"
}
</pre>
            </section>

            <!-- 5 -->
            <section id="billerdetails" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">5. Get Biller Details</h2>

                <div class="bg-gray-900 text-green-400 p-4 rounded mb-4 text-sm">
                    <span class="text-yellow-300 font-bold">POST</span>
                    /v1/bbps/getBillerDetails
                </div>

<pre class="bg-gray-900 text-white p-4 rounded text-sm">
{
  "outLet": "OUT123",
  "billerId": "BILLER001"
}
</pre>
            </section>

            <!-- 6 -->
            <section id="enquiry" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">6. Bill Enquiry</h2>

                <div class="bg-gray-900 text-green-400 p-4 rounded mb-4 text-sm">
                    <span class="text-yellow-300 font-bold">POST</span>
                    /v1/bbps/billEnquiry
                </div>

<pre class="bg-gray-900 text-white p-4 rounded text-sm">
{
  "outLet": "496699",
  "billerId": "ATPOST000NAT01",
  "externalRef": "REF123",
  "transactionAmount": 100
}
</pre>
            </section>

            <!-- 7 -->
            <section id="validate" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">7. Validate Billers</h2>

                <div class="bg-gray-900 text-green-400 p-4 rounded mb-4 text-sm">
                    <span class="text-yellow-300 font-bold">POST</span>
                    /v1/bbps/validateBillers
                </div>

<pre class="bg-gray-900 text-white p-4 rounded text-sm">
{
  "outLet": "496699",
  "billerId": "ATPOST000NAT01",
  "externalRef": "REF123",
  "transactionAmount": 100
}
</pre>
            </section>

            <!-- 8 -->
            <section id="payment" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold mb-4">8. Payment Biller</h2>

                <div class="bg-gray-900 text-green-400 p-4 rounded mb-4 text-sm">
                    <span class="text-yellow-300 font-bold">POST</span>
                    /v1/bbps/paymentBiller
                </div>

<pre class="bg-gray-900 text-white p-4 rounded text-sm">
{
  "outLet": "OUT123",
  "billerId": "BILLER001",
  "externalRef": "REF123",
  "transactionAmount": 100
}
</pre>
            </section>

        </main>

    </div>

</div>

@endsection