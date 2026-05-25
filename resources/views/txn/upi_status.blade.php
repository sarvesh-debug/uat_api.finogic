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
            <h2 class="text-xl font-semibold">UPI Payout Transaction Status</h2>
        </div>

        <div class="p-6">

            @if(isset($data['success']) && $data['success'])

                <!-- Message -->
                <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-800 border border-green-200">
                    {{ $data['message']   ?? '-'}}
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 rounded-lg">

                        <tbody class="divide-y divide-gray-200">

                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-sm text-gray-600 text-left">Status</th>
                                <td class="px-4 py-3">
                                    <span class="px-3 py-1 rounded-full text-sm font-medium
                                    {{ $data['status'] == 'SUCCESS' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $data['status']  ?? '-' }}
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <th class="px-4 py-3 text-sm text-gray-600 text-left">Order ID</th>
                                <td class="px-4 py-3 font-mono text-gray-800">
                                    {{ $data['orderId']  ?? '-' }}
                                </td>
                            </tr>

                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-sm text-gray-600 text-left">Amount</th>
                                <td class="px-4 py-3 font-semibold text-gray-900">
                                    ₹ {{ $data['amount']   ?? '-'}}
                                </td>
                            </tr>

                            <tr>
                                <th class="px-4 py-3 text-sm text-gray-600 text-left">RRN / UTR</th>
                                <td class="px-4 py-3 font-mono text-gray-800">
                                    {{ $data['rrn']   ?? '-'}}
                                </td>
                            </tr>

                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-sm text-gray-600 text-left">Code</th>
                                <td class="px-4 py-3">
                                    {{ $data['code']  ?? '-' }}
                                </td>
                            </tr>

                            <tr>
                                <th class="px-4 py-3 text-sm text-gray-600 text-left">Timestamp</th>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $data['timestamp']  ?? '-' }}
                                </td>
                            </tr>

                        </tbody>

                    </table>
                </div>

            @else

                <div class="p-4 rounded-lg bg-red-100 text-red-700 border border-red-200">
                     {{ $data['message'] ?? 'Transaction Failed or Not Found'}}
                </div>

            @endif

        </div>

    </div>

</div>

@endsection