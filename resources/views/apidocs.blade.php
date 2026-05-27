

@extends('users.layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Mobile Header - Show on screens up to 1024px (xl) -->
    <div class="xl:hidden bg-white shadow-md p-4 sticky top-0 z-100">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-indigo-600 flex items-center gap-2">
                <i class="fa-solid fa-rocket text-indigo-500"></i> Finogic API
            </h1>
            <button id="menuToggle" class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors">
                <i class="fa-solid fa-bars text-gray-600"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Sidebar - Show on screens up to 1024px (xl) -->
    <div id="mobileSidebar" class="xl:hidden fixed inset-0 bg-white z-40 transform -translate-x-full transition-transform duration-300">
        <div class="p-4 border-b bg-indigo-50">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-bold text-indigo-600 flex items-center gap-2">
                    <i class="fa-solid fa-bars text-indigo-500"></i> Navigation
                </h2>
                <button id="closeMobileMenu" class="p-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50">
                    <i class="fa-solid fa-times text-gray-600"></i>
                </button>
            </div>
        </div>
        <nav class="p-4 space-y-1">
            <a href="#about" class="flex items-center gap-3 p-3 text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200 group border border-transparent hover:border-indigo-100">
                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-200 transition-colors">
                    <i class="fa-solid fa-circle-info text-indigo-500 text-sm"></i>
                </div>
                <span class="font-medium">About</span>
            </a>
            <a href="#get-sender" class="flex items-center gap-3 p-3 text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200 group border border-transparent hover:border-indigo-100">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                    <i class="fa-solid fa-user-tie text-blue-500 text-sm"></i>
                </div>
                <span class="font-medium">Get Sender</span>
            </a>
            <a href="#add-bene" class="flex items-center gap-3 p-3 text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200 group border border-transparent hover:border-indigo-100">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors">
                    <i class="fa-solid fa-user-plus text-green-500 text-sm"></i>
                </div>
                <span class="font-medium">Add Beneficiary</span>
            </a>
            <a href="#send-payout" class="flex items-center gap-3 p-3 text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200 group border border-transparent hover:border-indigo-100">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                    <i class="fa-solid fa-paper-plane text-purple-500 text-sm"></i>
                </div>
                <span class="font-medium">Send Payout</span>
            </a>
            <a href="#status" class="flex items-center gap-3 p-3 text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200 group border border-transparent hover:border-indigo-100">
                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center group-hover:bg-yellow-200 transition-colors">
                    <i class="fa-solid fa-clock text-yellow-500 text-sm"></i>
                </div>
                <span class="font-medium">Payout Status</span>
            </a>
            <a href="#callback" class="flex items-center gap-3 p-3 text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200 group border border-transparent hover:border-indigo-100">
                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center group-hover:bg-red-200 transition-colors">
                    <i class="fa-solid fa-exchange-alt text-red-500 text-sm"></i>
                </div>
                <span class="font-medium">Callback</span>
            </a>
        </nav>
    </div>

    <!-- Overlay for mobile - Show on screens up to 1024px (xl) -->
    <div id="mobileOverlay" class="xl:hidden fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>

    <div class="flex flex-col xl:flex-row">
        <!-- Desktop Sidebar - Show only on screens 1025px and above (xl) -->
        <aside class="hidden xl:flex xl:flex-col w-72 bg-white border-r shadow-md p-5 sticky top-0 h-screen">
            <h1 class="text-2xl font-bold text-indigo-600 mb-6 flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-rocket text-white"></i>
                </div>
                Finogic API
            </h1>
            <nav class="space-y-1 flex-1">
                <a href="#about" class="flex items-center gap-3 p-3 text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all duration-200 group border border-transparent hover:border-indigo-100">
                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-200 transition-colors">
                        <i class="fa-solid fa-circle-info text-indigo-500 text-sm"></i>
                    </div>
                    <span class="font-medium">About</span>
                </a>
                <a href="#get-sender" class="flex items-center gap-3 p-3 text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all duration-200 group border border-transparent hover:border-indigo-100">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                        <i class="fa-solid fa-user-tie text-blue-500 text-sm"></i>
                    </div>
                    <span class="font-medium">Get Sender</span>
                </a>
                <a href="#add-bene" class="flex items-center gap-3 p-3 text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all duration-200 group border border-transparent hover:border-indigo-100">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors">
                        <i class="fa-solid fa-user-plus text-green-500 text-sm"></i>
                    </div>
                    <span class="font-medium">Add Beneficiary</span>
                </a>
                <a href="#send-payout" class="flex items-center gap-3 p-3 text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all duration-200 group border border-transparent hover:border-indigo-100">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                        <i class="fa-solid fa-paper-plane text-purple-500 text-sm"></i>
                    </div>
                    <span class="font-medium">Send Payout</span>
                </a>
                <a href="#status" class="flex items-center gap-3 p-3 text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all duration-200 group border border-transparent hover:border-indigo-100">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center group-hover:bg-yellow-200 transition-colors">
                        <i class="fa-solid fa-clock text-yellow-500 text-sm"></i>
                    </div>
                    <span class="font-medium">Payout Status</span>
                </a>
                <a href="#callback" class="flex items-center gap-3 p-3 text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all duration-200 group border border-transparent hover:border-indigo-100">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center group-hover:bg-red-200 transition-colors">
                        <i class="fa-solid fa-exchange-alt text-red-500 text-sm"></i>
                    </div>
                    <span class="font-medium">Callback</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 min-w-0 p-4 xl:p-8 space-y-6 xl:space-y-8">
            <!-- About Section -->
            <section id="about" class="bg-white shadow-lg rounded-2xl p-6 xl:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-circle-info text-white text-lg"></i>
                    </div>
                    <h2 class="text-2xl xl:text-3xl font-bold text-gray-800">About Finogic</h2>
                </div>
                <p class="text-gray-700 leading-relaxed text-sm xl:text-base mb-4">
                    Finogic is a next-generation remittance and payout engine, designed to make money transfers,
                    account verifications, and beneficiary management seamless, fast, and reliable.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    <div class="flex items-center gap-2 p-3 bg-green-50 rounded-lg">
                        <i class="fa-solid fa-check-circle text-green-500 text-lg"></i>
                        <span class="text-gray-700 font-medium">Instant payouts across India</span>
                    </div>
                    <div class="flex items-center gap-2 p-3 bg-green-50 rounded-lg">
                        <i class="fa-solid fa-check-circle text-green-500 text-lg"></i>
                        <span class="text-gray-700 font-medium">Account verification before transfer</span>
                    </div>
                    <div class="flex items-center gap-2 p-3 bg-green-50 rounded-lg">
                        <i class="fa-solid fa-check-circle text-green-500 text-lg"></i>
                        <span class="text-gray-700 font-medium">Real-time transaction status</span>
                    </div>
                    <div class="flex items-center gap-2 p-3 bg-green-50 rounded-lg">
                        <i class="fa-solid fa-check-circle text-green-500 text-lg"></i>
                        <span class="text-gray-700 font-medium">RBI & Banking compliant</span>
                    </div>
                </div>
                <p class="text-gray-600 text-sm xl:text-base">
                    Our APIs are lightweight, REST-based, and developer-friendly, making integration simple
                    for fintech apps, B2B panels, salary disbursement platforms, and more.
                </p>
            </section>

            <!-- Get Sender Section -->
            <section id="get-sender" class="bg-white shadow-lg rounded-2xl p-6 xl:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-user-tie text-white text-lg"></i>
                    </div>
                    <h2 class="text-2xl xl:text-3xl font-bold text-gray-800">Get Sender & Beneficiaries</h2>
                </div>
                <p class="text-gray-700 mb-4 text-sm xl:text-base">Fetch sender details and their beneficiaries. Generates a
                    <code class="bg-gray-100 px-2 py-1 rounded text-xs xl:text-sm font-mono">reference_key</code>
                    (valid for 15 minutes) required for adding beneficiaries.</p>

                <div class="space-y-4">
                    <!-- Endpoint -->
                    <div>
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-link text-blue-500"></i> Endpoint
                        </h3>
                        <div class="bg-gray-900 text-green-400 p-3 rounded-lg text-xs xl:text-sm overflow-x-auto">
                            <span class="font-bold text-yellow-300">POST</span> api/get/remittance
                        </div>
                    </div>

                    <!-- Request Parameters -->
                    <div>
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-list text-blue-500"></i> Request Parameters
                        </h3>
                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="w-full text-xs xl:text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="p-3 text-left font-semibold text-gray-600">Field</th>
                                        <th class="p-3 text-left font-semibold text-gray-600">Type</th>
                                        <th class="p-3 text-left font-semibold text-gray-600">Required</th>
                                        <th class="p-3 text-left font-semibold text-gray-600">Description</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-3 font-mono text-blue-600">apikey</td>
                                        <td class="p-3">string</td>
                                        <td class="p-3">
                                            <span class="inline-flex items-center gap-1 text-green-600 font-medium">
                                                <i class="fa-solid fa-check"></i> Yes
                                            </span>
                                        </td>
                                        <td class="p-3 text-gray-600">Your API key for authentication</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- cURL Example -->
                    <div>
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-terminal text-blue-500"></i> Example cURL
                        </h3>
                        <pre class="bg-gray-900 text-green-400 p-4 rounded-lg text-xs xl:text-sm overflow-x-auto">
curl -X POST https://yourdomain.com/api/get-sender \
  -H "Content-Type: application/json" \
  -d '{"apikey": "YOUR_API_KEY"}'</pre>
                    </div>

                    <!-- Success Response -->
                    <div>
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-check-circle text-green-500"></i> Success Response
                        </h3>
                        <pre class="bg-gray-900 text-green-400 p-4 rounded-lg text-xs xl:text-sm overflow-x-auto">
{
  "status": true,
  "message": "Beneficiaries fetched successfully.",
  "remittance_name": "John Doe",
  "remittance_id": "REM123456",
  "phone": "9876543210",
  "reference_key": "ABCD1234EFGH",
  "expires_at": "2025-09-08 12:30:00",
  "beneficiaries": [...]
}</pre>
                    </div>
                </div>
            </section>

            <!-- Add Beneficiary Section -->
            <section id="add-bene" class="bg-white shadow-lg rounded-2xl p-6 xl:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-user-plus text-white text-lg"></i>
                    </div>
                    <h2 class="text-2xl xl:text-3xl font-bold text-gray-800">Add Beneficiary</h2>
                </div>
                <p class="text-gray-700 mb-4 text-sm xl:text-base">Add a new beneficiary to the system.</p>

                <div class="space-y-4">
                    <!-- Endpoint -->
                    <div>
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-link text-blue-500"></i> Endpoint
                        </h3>
                        <div class="bg-gray-900 text-green-400 p-3 rounded-lg text-xs xl:text-sm overflow-x-auto">
                            <span class="font-bold text-yellow-300">POST</span> /api/add-beneficiary
                        </div>
                    </div>

                    <!-- Request Parameters -->
                    <div>
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-list text-blue-500"></i> Request Parameters
                        </h3>
                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="w-full text-xs xl:text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="p-3 text-left font-semibold text-gray-600">Field</th>
                                        <th class="p-3 text-left font-semibold text-gray-600">Type</th>
                                        <th class="p-3 text-left font-semibold text-gray-600">Required</th>
                                        <th class="p-3 text-left font-semibold text-gray-600">Description</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr class="hover:bg-gray-50"><td class="p-3 font-mono text-blue-600">remId</td><td class="p-3">string</td><td class="p-3"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Yes</span></td><td class="p-3 text-gray-600">Remitter ID from /get-sender</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="p-3 font-mono text-blue-600">reference_key</td><td class="p-3">string</td><td class="p-3"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Yes</span></td><td class="p-3 text-gray-600">Valid key from /get-sender</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="p-3 font-mono text-blue-600">benename</td><td class="p-3">string</td><td class="p-3"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Yes</span></td><td class="p-3 text-gray-600">Beneficiary full name</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="p-3 font-mono text-blue-600">beneMobile</td><td class="p-3">string</td><td class="p-3"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Yes</span></td><td class="p-3 text-gray-600">Beneficiary mobile number</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="p-3 font-mono text-blue-600">accno</td><td class="p-3">string</td><td class="p-3"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Yes</span></td><td class="p-3 text-gray-600">Bank account number</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="p-3 font-mono text-blue-600">bank_name</td><td class="p-3">string</td><td class="p-3"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Yes</span></td><td class="p-3 text-gray-600">Beneficiary bank name</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="p-3 font-mono text-blue-600">ifsc</td><td class="p-3">string</td><td class="p-3"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Yes</span></td><td class="p-3 text-gray-600">IFSC code (11 characters)</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="p-3 font-mono text-blue-600">latitude</td><td class="p-3">string</td><td class="p-3"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Yes</span></td><td class="p-3 text-gray-600">Current location latitude</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="p-3 font-mono text-blue-600">longitude</td><td class="p-3">string</td><td class="p-3"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Yes</span></td><td class="p-3 text-gray-600">Current location longitude</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- cURL Example -->
                    <div>
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-terminal text-blue-500"></i> Example cURL
                        </h3>
                        <pre class="bg-gray-900 text-green-400 p-4 rounded-lg text-xs xl:text-sm overflow-x-auto">
curl -X POST https://yourdomain.com/api/beneficiary/add \
  -H "Content-Type: application/json" \
  -d '{
    "remId": "REM123456",
    "reference_key": "ABCD1234EFGH",
    "benename": "Alice Kumar",
    "beneMobile": "9876543210",
    "accno": "1234567890",
    "bank_name": "HDFC Bank",
    "ifsc": "HDFC0001234",
    "latitude": "28.6139",
    "longitude": "77.2090"
  }'</pre>
                    </div>
                </div>
            </section>

            <!-- Send Payout Section -->
            <section id="send-payout" class="bg-white shadow-lg rounded-2xl p-6 xl:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-paper-plane text-white text-lg"></i>
                    </div>
                    <h2 class="text-2xl xl:text-3xl font-bold text-gray-800">Send Payout</h2>
                </div>
                <p class="text-gray-700 mb-4 text-sm xl:text-base">Send money to a beneficiary's bank account. The system validates balance, charges, and GST before initiating the transfer.</p>

                <div class="space-y-4">
                    <!-- Endpoint -->
                    <div>
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-link text-blue-500"></i> Endpoint
                        </h3>
                        <div class="bg-gray-900 text-green-400 p-3 rounded-lg text-xs xl:text-sm overflow-x-auto">
                            <span class="font-bold text-yellow-300">POST</span> api/initiate-transaction
                        </div>
                    </div>

                    <!-- Request Parameters -->
                    <div>
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-list text-blue-500"></i> Request Parameters
                        </h3>
                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="w-full text-xs xl:text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="p-3 text-left font-semibold text-gray-600">Field</th>
                                        <th class="p-3 text-left font-semibold text-gray-600">Type</th>
                                        <th class="p-3 text-left font-semibold text-gray-600">Required</th>
                                        <th class="p-3 text-left font-semibold text-gray-600">Description</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr class="hover:bg-gray-50"><td class="p-3 font-mono text-blue-600">apikey</td><td class="p-3">string</td><td class="p-3"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Yes</span></td><td class="p-3 text-gray-600">API Key</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="p-3 font-mono text-blue-600">mobileNo</td><td class="p-3">string</td><td class="p-3"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Yes</span></td><td class="p-3 text-gray-600">Remitter mobile number</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="p-3 font-mono text-blue-600">txnAmount</td><td class="p-3">number</td><td class="p-3"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Yes</span></td><td class="p-3 text-gray-600">Transaction amount</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="p-3 font-mono text-blue-600">accountNo</td><td class="p-3">string</td><td class="p-3"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Yes</span></td><td class="p-3 text-gray-600">Beneficiary account number</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="p-3 font-mono text-blue-600">ifscCode</td><td class="p-3">string</td><td class="p-3"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Yes</span></td><td class="p-3 text-gray-600">IFSC code (11 chars)</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="p-3 font-mono text-blue-600">bankName</td><td class="p-3">string</td><td class="p-3"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Yes</span></td><td class="p-3 text-gray-600">Bank name</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="p-3 font-mono text-blue-600">accountHolderName</td><td class="p-3">string</td><td class="p-3"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Yes</span></td><td class="p-3 text-gray-600">Account holder name</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="p-3 font-mono text-blue-600">RefNo</td><td class="p-3">string</td><td class="p-3"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Yes</span></td><td class="p-3 text-gray-600">Unique reference number</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="p-3 font-mono text-blue-600">web</td><td class="p-3">string</td><td class="p-3"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Yes</span></td><td class="p-3 text-gray-600">YES</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- cURL Example -->
                    <div>
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-terminal text-blue-500"></i> Example cURL
                        </h3>
                        <pre class="bg-gray-900 text-green-400 p-4 rounded-lg text-xs xl:text-sm overflow-x-auto">
curl -X POST https://yourdomain.com/api/initiate-transaction \
  -H "Content-Type: application/json" \
  -d '{
    "apikey": "YOUR_API_KEY",
    "mobileNo": "9876543210",
    "txnAmount": 1500,
    "accountNo": "1234567890",
    "ifscCode": "HDFC0001234",
    "bankName": "HDFC Bank",
    "accountHolderName": "Alice Kumar",
    "RefNo": "TXNREF001",
    "web": "YES"
  }'</pre>
                    </div>

                    <!-- Success Response -->
                    <div>
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-check-circle text-green-500"></i> Success Response
                        </h3>
                        <pre class="bg-gray-900 text-green-400 p-4 rounded-lg text-xs xl:text-sm overflow-x-auto">
{
  "status": true,
  "message": "Payout Transaction successfully",
  "remId": 101,
  "email": "user@example.com",
  "payment_id": "XPYTABCDE1234",
  "utr": "UTR1234567890",
  "amount": 1000,
  "charge": 10,
  "GST": 2,
  "status": "Success",
  "opening_balance": 5000,
  "closing_balance": 4988,
  "bank_name": "State Bank of India",
  "ifsc_code": "SBIN0000001",
  "acc_no": "400012345678",
  "beneficiary_name": "John Doe",
  "refId": "REF123456",
  "requestBody": "{...original request payload...}",
  "created_at": "2025-09-30T13:00:00",
  "updated_at": "2025-09-30T13:01:00"
}

}</pre>
                    </div>
                </div>
            </section>

            <!-- Payout Status Section -->
            <section id="status" class="bg-white shadow-lg rounded-2xl p-6 xl:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-clock text-white text-lg"></i>
                    </div>
                    <h2 class="text-2xl xl:text-3xl font-bold text-gray-800">Payout Status</h2>
                </div>
                <p class="text-gray-700 mb-4 text-sm xl:text-base">Check the current status of a payout transaction.</p>

                <div class="space-y-4">
                    <!-- Endpoint -->
                    <div>
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-link text-blue-500"></i> Endpoint
                        </h3>
                        <div class="bg-gray-900 text-green-400 p-3 rounded-lg text-xs xl:text-sm overflow-x-auto">
                            <span class="font-bold text-yellow-300">POST</span> /api/get-transaction-status
                        </div>
                    </div>

                    <!-- Request Parameters -->
                    <div>
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-list text-blue-500"></i> Request Parameters
                        </h3>
                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="w-full text-xs xl:text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="p-3 text-left font-semibold text-gray-600">Field</th>
                                        <th class="p-3 text-left font-semibold text-gray-600">Type</th>
                                        <th class="p-3 text-left font-semibold text-gray-600">Required</th>
                                        <th class="p-3 text-left font-semibold text-gray-600">Description</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr class="hover:bg-gray-50"><td class="p-3 font-mono text-blue-600">apikey</td><td class="p-3">string</td><td class="p-3"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Yes</span></td><td class="p-3 text-gray-600">API Key</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="p-3 font-mono text-blue-600">payment_id</td><td class="p-3">string</td><td class="p-3"><span class="text-green-600 font-medium"><i class="fa-solid fa-check"></i> Yes</span></td><td class="p-3 text-gray-600">Payout payment ID</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- cURL Example -->
                    <div>
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-terminal text-blue-500"></i> Example cURL
                        </h3>
                        <pre class="bg-gray-900 text-green-400 p-4 rounded-lg text-xs xl:text-sm overflow-x-auto">
curl -X POST https://yourdomain.com/api/get-transaction-status \
  -H "Content-Type: application/json" \
  -d '{"apikey": "YOUR_API_KEY","payment_id": "XPYT8A9BC123D"}'</pre>
                    </div>

                    <!-- Success Response -->
                    <div>
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-check-circle text-green-500"></i> Success Response
                        </h3>
                        <pre class="bg-gradient-to-r from-gray-900 to-blue-900 text-green-400 p-4 rounded-lg text-xs xl:text-sm overflow-x-auto">
{
  "status": true,
  "message": "Transaction status fetched successfully.",
  "data": {
    "payment_id": "XPYT8A9BC123D",
    "amount": 1500,
    "charges": 15,
    "GST": 0.3,
    "status": "Initiated",
    "opening_balance": 5000,
    "closing_balance": 3484.7,
    "bank_name": "HDFC Bank",
    "ifsc": "HDFC0001234",
    "account_no": "1234567890",
    "beneficiary_name": "Alice Kumar",
    "ref_no": "TXNREF001",
    "created_at": "2025-09-08 12:00:00",
    "updated_at": "2025-09-08 12:05:00"
  }
}</pre>
                    </div>
                </div>
            </section>

            <!-- Callback Section -->
            <section id="callback" class="bg-white shadow-lg rounded-2xl p-6 xl:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-red-600 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-exchange-alt text-white text-lg"></i>
                    </div>
                    <h2 class="text-2xl xl:text-3xl font-bold text-gray-800">Callback Notification (Webhook)</h2>
                </div>
                <p class="text-gray-700 mb-4 text-sm xl:text-base">Whenever a payout status changes (Initiated → Success/Failed), the system notifies your server instantly.</p>

                <div class="space-y-4">
                    <!-- Callback Request -->
                    <div>
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-link text-blue-500"></i> Callback Request
                        </h3>
                        <div class="bg-gray-900 text-green-400 p-3 rounded-lg text-xs xl:text-sm overflow-x-auto">
                            <span class="font-bold text-yellow-300">POST</span> {client_callback_url}
                        </div>
                    </div>

                    <!-- Example Payload -->
                    <div>
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-code text-blue-500"></i> Example Payload
                        </h3>
                        <pre class="bg-gray-900 text-green-400 p-4 rounded-lg text-xs xl:text-sm overflow-x-auto">
{
  "payment_id": "XPYT8A9BC123D",
  "amount": 1500,
  "status": "SUCCESS",
  "bank_ref_no": "HDFCREF987654",
  "account_no": "1234567890",
  "ifsc": "HDFC0001234",
  "beneficiary_name": "Alice Kumar",
  "ref_no": "TXNREF001",
  "updated_at": "2025-09-08T12:35:00+05:30"
}</pre>
                    </div>

                    <!-- Expected Response -->
                    <div>
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-reply text-blue-500"></i> Expected Client Response
                        </h3>
                        <pre class="bg-gray-900 text-green-400 p-4 rounded-lg text-xs xl:text-sm overflow-x-auto">
{ "acknowledged": true }</pre>
                        <div class="flex items-center gap-2 mt-2 p-3 bg-yellow-50 rounded-lg">
                            <i class="fa-solid fa-exclamation-triangle text-yellow-500"></i>
                            <span class="text-yellow-700 text-sm font-medium">Must respond within 10 seconds. Retries up to 3 times with exponential backoff if failed.</span>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<script>
    // Mobile menu functionality
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menuToggle');
        const closeMobileMenu = document.getElementById('closeMobileMenu');
        const mobileSidebar = document.getElementById('mobileSidebar');
        const mobileOverlay = document.getElementById('mobileOverlay');

        function openMobileMenu() {
            mobileSidebar.classList.remove('-translate-x-full');
            mobileOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileMenuFunc() {
            mobileSidebar.classList.add('-translate-x-full');
            mobileOverlay.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        menuToggle.addEventListener('click', openMobileMenu);
        closeMobileMenu.addEventListener('click', closeMobileMenuFunc);
        mobileOverlay.addEventListener('click', closeMobileMenuFunc);

        // Close menu when clicking on nav links on mobile
        document.querySelectorAll('#mobileSidebar nav a').forEach(link => {
            link.addEventListener('click', closeMobileMenuFunc);
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    // Close mobile menu if open (for screens up to 1024px)
                    if (window.innerWidth < 1280) { // xl breakpoint
                        closeMobileMenuFunc();
                    }
                    
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Close menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileMenuFunc();
            }
        });

        // Handle window resize - close menu when reaching desktop breakpoint
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1280) { // xl breakpoint
                closeMobileMenuFunc();
            }
        });
    });
</script>
@endsection