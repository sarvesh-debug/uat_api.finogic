@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-7xl py-10">
    <!-- Page Header -->
    <div class="text-center mb-8">
        <h2 class="text-3xl font-extrabold text-gray-800">📊 Transaction History</h2>
        <p class="text-gray-500 mt-2">Track all your payout transactions in real-time</p>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white shadow-lg rounded-2xl border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-4">
            <h3 class="text-xl font-semibold text-white">All Transactions</h3>
        </div>

        <div class="p-6 overflow-x-auto">
            <table class="min-w-full table-auto border-collapse border border-gray-200 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 border text-left">#</th>
                        <th class="px-4 py-3 border text-left">Payment ID</th>
                        <th class="px-4 py-3 border text-left">Amount</th>
                        <th class="px-4 py-3 border text-left">Charges</th>
                        <th class="px-4 py-3 border text-left">Closing Balance</th>
                        <th class="px-4 py-3 border text-left">Beneficiary</th>
                        <th class="px-4 py-3 border text-left">UTR</th>
                        <th class="px-4 py-3 border text-left">Status</th>
                        <th class="px-4 py-3 border text-left">Date</th>
                        <th class="px-4 py-3 border text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($txn as $index => $t)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3 border">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 border">{{ $t->payment_id }}</td>
                            <td class="px-4 py-3 border text-green-600 font-bold">₹ {{ number_format($t->amount, 2) }}</td>
                            <td class="px-4 py-3 border">₹ {{ $t->charge }} (TDS: ₹{{ $t->tds }})</td>
                            <td class="px-4 py-3 border">₹ {{ $t->closing_balance }}</td>
                            <td class="px-4 py-3 border">
                                {{ $t->bank_name }}<br>
                                <span class="text-xs text-gray-500">{{ $t->ifsc_code }}</span><br>
                                <span class="font-medium">{{ $t->beneficiary_name }}</span><br>
                                <span class="text-xs text-gray-500">{{ $t->acc_no }}</span>
                            </td>
                            <td class="px-4 py-3 border">{{ $t->bank_ref_no ?? '--' }}</td>
                            <td class="px-4 py-3 border">
                               @php
                                    $status = strtoupper($t->status);
                                @endphp

                                @if($status === 'SUCCESS')
                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-lg text-sm font-semibold">✅ Success</span>
                                @elseif($status === 'INITIATED')
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-lg text-sm font-semibold animate-pulse">⏳ Pending</span>
                                @else
                                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-lg text-sm font-semibold">❌ Failed</span>
                                @endif

                            </td>
                            <td class="px-4 py-3 border">{{ \Carbon\Carbon::parse($t->created_at)->format('d M Y, h:i A') }}</td>
                            <td class="px-4 py-3 border text-center">
                                <!-- Hidden Form -->
                                <form action="" method="POST" id="txnForm-{{ $t->id }}">
                                    @csrf
                                    <input type="hidden" name="payment_id" value="{{ $t->payment_id }}">
                                    <input type="hidden" name="remId" value="{{ $t->remId }}">
                                    <button type="submit" 
                                            class="bg-indigo-600 text-white px-3 py-1 rounded-lg hover:bg-indigo-500 transition">
                                        Status
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-6 text-gray-500">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
