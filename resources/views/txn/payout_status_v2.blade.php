@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto mt-10 px-4">

    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ url()->previous() }}"
           class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg shadow hover:bg-gray-700 transition">
            ← Back
        </a>
    </div>

    <div class="bg-white shadow-xl rounded-2xl overflow-hidden">

        <!-- Header -->
        <div class="bg-indigo-600 text-white px-6 py-4">
            <h2 class="text-xl font-semibold">Payout Transaction Status</h2>
        </div>

        <div class="p-6">

            @if(isset($data['status']) && $data['status'] === 'SUCCESS')

                <!-- Success Message -->
                <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-800 border border-green-200">
                    {{ $data['description'] ?? 'Transaction Successful' }}
                </div>

                <!-- Status Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 rounded-lg">

                        <tbody class="divide-y divide-gray-200">

                            <!-- Status -->
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">
                                    Status
                                </th>
                                <td class="px-4 py-3">
                                    <span class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-700 font-medium">
                                        {{ $data['status'] ?? '-' }}
                                    </span>
                                </td>
                            </tr>

                            <!-- Transaction ID -->
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">
                                    Transaction ID
                                </th>
                                <td class="px-4 py-3 font-mono text-gray-800">
                                    {{ $data['transactionId'] ?? '-' }}
                                </td>
                            </tr>

                            <!-- Enquiry ID -->
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">
                                    Enquiry Txn ID
                                </th>
                                <td class="px-4 py-3 font-mono text-gray-800">
                                    {{ $data['enquiryTxnId'] ?? '-' }}
                                </td>
                            </tr>

                            <!-- Client Ref ID -->
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">
                                    Client Reference ID
                                </th>
                                <td class="px-4 py-3 font-mono text-gray-800">
                                    {{ $data['client_referenceId'] ?? '-' }}
                                </td>
                            </tr>

                            <!-- Amount -->
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">
                                    Amount
                                </th>
                                <td class="px-4 py-3 font-semibold text-gray-900">
                                    ₹ {{ $data['amount'] ?? '-' }}
                                </td>
                            </tr>

                            <!-- UTR -->
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">
                                    UTR
                                </th>
                                <td class="px-4 py-3 font-mono text-gray-800">
                                    {{ $data['utr'] ?? '-' }}
                                </td>
                            </tr>

                            <!-- Acknowledged -->
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">
                                    Acknowledged
                                </th>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $data['acknowledged'] == '1' ? 'Yes' : 'No' }}
                                </td>
                            </tr>

                        </tbody>

                    </table>
                </div>

            @else

                <!-- Failed Message -->
                <div class="p-4 rounded-lg bg-red-100 text-red-700 border border-red-200">
                    {{ $data['description'] ?? 'Transaction Failed or Not Found' }}
                </div>

            @endif

        </div>

    </div>

</div>

@endsection