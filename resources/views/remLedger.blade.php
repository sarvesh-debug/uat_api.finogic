@extends('users.layouts.app')

@section('content')
<div class="container mx-auto max-w-7xl py-10">
    <!-- Page Header -->
    <div class="text-center mb-8">
        <h2 class="text-3xl font-extrabold text-gray-800">📒 Ledger</h2>
        <p class="text-gray-500 mt-2">View complete details of funds, verifications & payouts</p>
    </div>

    <!-- Ledger Table -->
    <div class="bg-white shadow-lg rounded-2xl border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-semibold text-white">All Transactions</h3>
            <span class="text-sm text-indigo-100">Real-time Updated</span>
        </div>

        <div class="p-6 overflow-x-auto">
            <table class="min-w-full table-auto border-collapse border border-gray-200 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 border text-left">#</th>
                        <th class="px-4 py-3 border text-left">Txn Type</th>
                        <th class="px-4 py-3 border text-left">Txn ID</th>
                        <th class="px-4 py-3 border text-left">Amount</th>
                        <th class="px-4 py-3 border text-left">Charges</th>
                        <th class="px-4 py-3 border text-left">Opening Balance</th>
                        <th class="px-4 py-3 border text-left">Closing Balance</th>
                        <th class="px-4 py-3 border text-left">Details</th>
                        <th class="px-4 py-3 border text-left">Status</th>
                        <th class="px-4 py-3 border text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledgerData as $index => $txn)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3 border">{{ $index + 1 }}</td>

                            <!-- Transaction Type -->
                            <td class="px-4 py-3 border">
                                @if($txn->type === 'fund')
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-lg text-xs font-medium">💰 Fund Load</span>
                                @elseif($txn->type === 'verification')
                                    <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-lg text-xs font-medium">🔍 Verification</span>
                                @elseif($txn->type === 'payout')
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-medium">🏦 Payout</span>
                                @endif
                            </td>

                            <!-- Transaction ID -->
                            <td class="px-4 py-3 border">
                                {{ $txn->payment_id ?? $txn->txn_id ?? '—' }}
                            </td>

                            <!-- Amount -->
                            <td class="px-4 py-3 border text-green-600 font-bold">
                                ₹ {{ number_format($txn->amount, 2) }}
                            </td>

                            <!-- Charges -->
                            <td class="px-4 py-3 border">
                                ₹ {{ $txn->charge ?? 0 }} 
                                @if(!empty($txn->tds)) (TDS: ₹{{ $txn->tds }}) @endif
                            </td>

                            <!-- Opening Balance -->
                            <td class="px-4 py-3 border">₹ {{ $txn->opening_balance ?? '—' }}</td>

                            <!-- Closing Balance -->
                            <td class="px-4 py-3 border">₹ {{ $txn->closing_balance ?? '—' }}</td>

                            <!-- Details -->
                            <td class="px-4 py-3 border">
                                @if($txn->type === 'fund')
                                    Loaded via {{ $txn->mode ?? 'Manual' }}
                                @elseif($txn->type === 'verification')
                                    {{ $txn->account_no ?? 'A/c Verification' }} <br>
                                    <span class="text-xs text-gray-500">{{ $txn->ifsc ?? '' }}</span>
                                @elseif($txn->type === 'payout')
                                    {{ $txn->beneficiary_name }} <br>
                                    <span class="text-xs text-gray-500">{{ $txn->acc_no ?? '' }}</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3 border">
                                @if($txn->status === 'SUCCESS')
                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-lg text-sm font-semibold">✅ Success</span>
                                @elseif($txn->status === 'Initiated')
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-lg text-sm font-semibold animate-pulse">⏳ Pending</span>
                                @else
                                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-lg text-sm font-semibold">❌ Failed</span>
                                @endif
                            </td>

                            <!-- Date -->
                            <td class="px-4 py-3 border">{{ \Carbon\Carbon::parse($txn->created_at)->format('d M Y, h:i A') }}</td>
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
