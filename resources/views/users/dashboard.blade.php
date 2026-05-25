@extends('users.layouts.app')

@section('content')

<div class="mx-auto w-full max-w-[1200px] py-8 px-4 text-[13px]">

    <!-- Compact Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">
                Welcome, {{ auth('remittance')->user()->name ?? 'Guest' }}
            </h1>
            <p id="liveDateTime" class="text-xs text-gray-500"></p>

<script>
    function updateDateTime() {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric'
        };

        const date = now.toLocaleDateString('en-IN', options);
        const time = now.toLocaleTimeString('en-IN');

        document.getElementById('liveDateTime').innerHTML = 
            date + ' - ' + time;
    }

    updateDateTime();
    setInterval(updateDateTime, 1000);
</script>

        </div>
    </div>


    <!-- TOP KPI STRIP (SMALL CARDS) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">

        <!-- Wallet Balance -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Wallet Balance</p>
                    <h3 class="text-lg font-bold text-gray-800 mt-1">
                        ₹ {{ number_format(auth('remittance')->user()->amount ?? 0, 2) }}
                    </h3>
                </div>
                <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center">
                    <i class="fa-solid fa-wallet text-green-600 text-sm"></i>
                </div>
            </div>
        </div>

        <!-- Today's Transactions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
            <p class="text-xs text-gray-500 uppercase">Today's Txns</p>
            <h3 class="text-lg font-bold text-gray-800 mt-1">
                {{ $summary['total_today']['count'] ?? 0 }}
            </h3>
            <p class="text-xs text-gray-500">
                ₹ {{ number_format($summary['total_today']['amount'] ?? 0) }}
            </p>
        </div>

        <!-- Monthly Transactions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
            <p class="text-xs text-gray-500 uppercase">This Month</p>
            <h3 class="text-lg font-bold text-gray-800 mt-1">
                {{ $summary['total_month']['count'] ?? 0 }}
            </h3>
            <p class="text-xs text-gray-500">
                ₹ {{ number_format($summary['total_month']['amount'] ?? 0) }}
            </p>
        </div>

        <!-- Success Rate -->
        @php
            $totalToday = $summary['total_today']['count'] ?? 0;
            $successToday = $summary['success']['count'] ?? 0;
            $successRate = $totalToday > 0 ? round(($successToday / $totalToday) * 100, 1) : 0;
        @endphp

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Success Rate</p>
                    <h3 class="text-lg font-bold text-gray-800 mt-1">
                        {{ $successRate }}%
                    </h3>
                </div>

                <div class="px-2 py-1 rounded-full text-xs font-semibold
                    {{ $successRate >= 90 ? 'bg-green-100 text-green-700' :
                       ($successRate >= 70 ? 'bg-yellow-100 text-yellow-700' :
                       'bg-red-100 text-red-700') }}">
                    {{ $successRate >= 90 ? 'Excellent' :
                       ($successRate >= 70 ? 'Average' : 'Low') }}
                </div>
            </div>
        </div>

    </div>



    <!-- EXISTING SUMMARY CARDS (COMPACT VERSION) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">

        <!-- Success -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-green-600 uppercase">Success</p>
            <h4 class="text-lg font-bold text-gray-800">
                {{ $summary['success']['count'] ?? 0 }}
            </h4>
            <p class="text-xs text-gray-500">
                ₹ {{ number_format($summary['success']['amount'] ?? 0) }}
            </p>
        </div>

        <!-- Pending -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-yellow-600 uppercase">Pending</p>
            <h4 class="text-lg font-bold text-gray-800">
                {{ $summary['pending']['count'] ?? 0 }}
            </h4>
            <p class="text-xs text-gray-500">
                ₹ {{ number_format($summary['pending']['amount'] ?? 0) }}
            </p>
        </div>

        <!-- Failed -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-red-600 uppercase">Failed</p>
            <h4 class="text-lg font-bold text-gray-800">
                {{ $summary['failed']['count'] ?? 0 }}
            </h4>
            <p class="text-xs text-gray-500">
                ₹ {{ number_format($summary['failed']['amount'] ?? 0) }}
            </p>
        </div>

    </div>



   <!-- Latest Transactions -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">

    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-sm font-semibold text-gray-700 uppercase">
            Latest Transactions
        </h2>

        <a href="{{ route('track.transaction.rem') }}"
           class="text-xs font-medium text-blue-600 hover:text-blue-800 transition">
            View All →
        </a>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-xs text-gray-500 uppercase border-b">
                    <th class="py-2 text-left">Txn ID</th>
                    <th class="py-2 text-left">Amount</th>
                    <th class="py-2 text-left">Status</th>
                    <th class="py-2 text-left">Date</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
            @forelse(($summary['txnvalue']->take(5) ?? collect()) as $txn)
                <tr class="hover:bg-gray-50 transition">
                    <td class="py-2 font-mono text-blue-600">
                        {{ $txn->payment_id }}
                    </td>

                    <td class="py-2">
                        ₹ {{ number_format($txn->amount, 2) }}
                    </td>

                    <td class="py-2">
                        <span class="px-2 py-1 text-xs rounded-full
                            {{ strtolower($txn->status) === 'success' ? 'bg-green-100 text-green-700' :
                               (strtolower($txn->status) === 'pending' ? 'bg-yellow-100 text-yellow-700' :
                               'bg-red-100 text-red-700') }}">
                            {{ ucfirst($txn->status) }}
                        </span>
                    </td>

                    <td class="py-2 text-gray-500 text-xs">
                        {{ \Carbon\Carbon::parse($txn->created_at)->format('d M Y, h:i A') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-3 text-center text-gray-400">
                        No transactions found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

</div>


</div>

@endsection
