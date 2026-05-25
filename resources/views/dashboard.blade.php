@extends('layouts.app')

@section('content')

<div class="space-y-10">



    <!-- 🔷 Top Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

        <!-- Total Balance -->
        <div class="group bg-white rounded-3xl p-7 shadow-xl hover:shadow-2xl transition duration-300 border border-gray-100">
            <div class="flex justify-between items-center">
               <a href="{{ route('admin.remittances.index') }}"> <div class="bg-indigo-100 text-indigo-600 p-4 rounded-2xl">
                    <i class="fa fa-inr text-xl"></i>
                </div>
                <i class="fa fa-arrow-up text-green-500"></i>
            </div>
          </a>
            <p class="text-xs uppercase text-gray-400 mt-5 tracking-widest">Total Balance</p>
            <h2 class="text-3xl font-bold text-gray-900 mt-2">
                ₹ {{ number_format($totalBalance ?? 0, 2) }}
            </h2>

            <div class="mt-5 text-sm space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-500">Locked</span>
                    <span>₹ {{ number_format($totalLockbalance ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Available</span>
                    <span class="font-semibold text-green-600">
                        ₹ {{ number_format($availableBalance ?? 0, 2) }}
                    </span>
                </div>
            </div>
        </div>


        <!-- API Users -->
        <div class="group bg-white rounded-3xl p-7 shadow-xl hover:shadow-2xl transition duration-300 border border-gray-100">
            <div class="flex justify-between items-center">
                <a href="{{ route('admin.remittances.index') }}">
                    <div class="bg-blue-100 text-blue-600 p-4 rounded-2xl">
                        <i class="fa fa-users text-xl"></i>
                    </div>
                </a>
                <i class="fa fa-signal text-blue-500"></i>
            </div>

            <p class="text-xs uppercase text-gray-400 mt-5 tracking-widest">API Users</p>
            <h2 class="text-3xl font-bold text-gray-900 mt-2">
                {{ $apiUsersCount ?? 0 }}
            </h2>

            <div class="mt-5 text-sm space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-500">Active</span>
                    <span class="text-green-600 font-semibold">{{ $activeUsers ?? 0 }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Inactive</span>
                    <span class="text-red-500 font-semibold">{{ $deactiveUsers ?? 0 }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Pending</span>
                    <span class="text-red-500 font-semibold">{{ $pendingUsers ?? 0 }}</span>
                </div>
            </div>
        </div>


        <!-- New Users -->
        <div class="group bg-white rounded-3xl p-7 shadow-xl hover:shadow-2xl transition duration-300 border border-gray-100">
            <div class="flex justify-between items-center">
                <a href="{{ route('admin.remittances.index') }}">
                    <div class="bg-purple-100 text-purple-600 p-4 rounded-2xl">
                        <i class="fa fa-user-plus text-xl"></i>
                    </div>
                </a>
                <i class="fa fa-line-chart text-purple-500"></i>
            </div>

            <p class="text-xs uppercase text-gray-400 mt-5 tracking-widest">New Users</p>
            <h2 class="text-3xl font-bold text-purple-600 mt-2">
                {{ $newUsers ?? 0 }}
            </h2>
        </div>


        <!-- Fund Requests -->
        <div class="group bg-white rounded-3xl p-7 shadow-xl hover:shadow-2xl transition duration-300 border border-gray-100">
            <div class="flex justify-between items-center">
              <a href="{{ route('banks.fund.request') }}">   <div class="bg-red-100 text-red-600 p-4 rounded-2xl">
                    <i class="fa fa-credit-card text-xl"></i>
                </div>  
                </a>
                
                <i class="fa fa-exclamation-circle text-red-500"></i>
            </div>

            <p class="text-xs uppercase text-gray-400 mt-5 tracking-widest">Fund Requests</p>
            <h2 class="text-3xl font-bold text-red-600 mt-2">
                {{ $fundRequests ?? 0 }}
            </h2>

            <p class="text-xs text-gray-500 mt-3">
                <i class="fa fa-id-card"></i> KYC Pending: {{ $kycPending ?? 0 }}
            </p>
        </div>

    </div>

<!-- 💳 Transaction Volume Section -->
<div class="bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">

    <div class="px-10 py-6 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <i class="fa fa-bar-chart"></i>
                Transaction Volume Overview
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Real-time performance breakdown
            </p>
        </div>
        <div class="flex gap-2">

<a href="?filter=yesterday"
class="px-4 py-2 text-sm rounded-lg border {{ $filter=='yesterday' ? 'bg-blue-600 text-white' : 'bg-white' }}">
Previous Day
</a>

<a href="?filter=today"
class="px-4 py-2 text-sm rounded-lg border {{ $filter=='today' ? 'bg-blue-600 text-white' : 'bg-white' }}">
Today
</a>

<a href="?filter=month"
class="px-4 py-2 text-sm rounded-lg border {{ $filter=='month' ? 'bg-blue-600 text-white' : 'bg-white' }}">
This Month
</a>

<a href="?filter=total"
class="px-4 py-2 text-sm rounded-lg border {{ $filter=='total' ? 'bg-blue-600 text-white' : 'bg-white' }}">
Total
</a>

</div>
    </div>
  
   <div class="p-10 grid grid-cols-1 md:grid-cols-4 gap-8">

        <!-- Pending -->
        <div class="relative rounded-2xl p-6 bg-yellow-50 border border-yellow-200 hover:shadow-lg transition">

            <p class="text-xs uppercase text-yellow-700 flex items-center gap-2">
                <i class="fa fa-clock"></i> Pending
            </p>

            <p class="text-sm font-bold text-yellow-800 mt-3">
                {{ $txnPending ?? 0 }}
            </p>

            <p class="text-3xl font-bold text-yellow-700 mt-2">
                ₹ {{ number_format($txnPendingSum ?? 0, 2) }}
            </p>

            <div class="absolute right-5 top-6 text-yellow-600 text-sm">
                <i class="fa fa-hourglass-half"></i>
            </div>

        </div>


        <!-- Failed -->
        <div class="relative rounded-2xl p-6 bg-red-50 border border-red-200 hover:shadow-lg transition">

            <p class="text-xs uppercase text-red-700 flex items-center gap-2">
                <i class="fa fa-times-circle"></i> Failed
            </p>

            <p class="text-sm font-bold text-red-800 mt-3">
                {{ $txnFailed ?? 0 }}
            </p>

            <p class="text-3xl font-bold text-red-700 mt-2">
                ₹ {{ number_format($txnFailedSum ?? 0, 2) }}
            </p>

            <div class="absolute right-5 top-6 text-red-600 text-sm">
                <i class="fa fa-arrow-down"></i>
            </div>

        </div>


        <!-- Success -->
        <div class="relative rounded-2xl p-6 bg-green-50 border border-green-200 hover:shadow-lg transition">

            <p class="text-xs uppercase text-green-700 flex items-center gap-2">
                <i class="fa fa-check-circle"></i> Success
            </p>

            <p class="text-sm font-bold text-green-800 mt-3">
                {{ $txnSuccess ?? 0 }}
            </p>

            <p class="text-3xl font-bold text-green-700 mt-2">
                ₹ {{ number_format($txnSuccessSum ?? 0, 2) }}
            </p>

            <div class="absolute right-5 top-6 text-green-600 text-sm">
                <i class="fa fa-arrow-up"></i>
            </div>

        </div>


        <!-- Refund (NEW CARD) -->
        <div class="relative rounded-2xl p-6 bg-indigo-50 border border-indigo-200 hover:shadow-lg transition">

            <p class="text-xs uppercase text-indigo-700 flex items-center gap-2">
                <i class="fa fa-undo"></i> Refund
            </p>

            <p class="text-sm font-bold text-indigo-800 mt-3">
                {{ $txnRefunded ?? 0 }}
            </p>

            <p class="text-3xl font-bold text-indigo-700 mt-2">
                ₹ {{ number_format($txnRefundedSum ?? 0, 2) }}
            </p>

            <div class="absolute right-5 top-6 text-indigo-600 text-sm">
                <i class="fa fa-refresh"></i>
            </div>

        </div>


    </div>
</div>

{{-- fund request --}}

<div class="bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden mt-10">

<div class="px-10 py-6 border-b border-gray-200 bg-gray-50 flex justify-between items-center">

<div>
<h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
<i class="fa fa-money-bill-wave"></i>
Fund Request Overview
</h2>
<p class="text-sm text-gray-500 mt-1">
Fund request performance breakdown
</p>
</div>

<div class="flex gap-2">

<a href="?filter=yesterday"
class="px-4 py-2 text-sm rounded-lg border {{ $filter=='yesterday' ? 'bg-blue-600 text-white' : 'bg-white' }}">
Previous Day
</a>

<a href="?filter=today"
class="px-4 py-2 text-sm rounded-lg border {{ $filter=='today' ? 'bg-blue-600 text-white' : 'bg-white' }}">
Today
</a>

<a href="?filter=month"
class="px-4 py-2 text-sm rounded-lg border {{ $filter=='month' ? 'bg-blue-600 text-white' : 'bg-white' }}">
This Month
</a>

<a href="?filter=total"
class="px-4 py-2 text-sm rounded-lg border {{ $filter=='total' ? 'bg-blue-600 text-white' : 'bg-white' }}">
Total
</a>

</div>

</div>


<div class="p-10 grid grid-cols-1 md:grid-cols-3 gap-8">

<!-- Pending -->
<div class="relative rounded-2xl p-6 bg-yellow-50 border border-yellow-200">

<p class="text-xs uppercase text-yellow-700 flex items-center gap-2">
<i class="fa fa-clock"></i> Pending
</p>

<p class="text-sm font-bold text-yellow-800 mt-3">
{{ $fundPending ?? 0 }}
</p>

<p class="text-3xl font-bold text-yellow-700 mt-2">
₹ {{ number_format($fundPendingSum ?? 0,2) }}
</p>

</div>


<!-- Accept -->
<div class="relative rounded-2xl p-6 bg-green-50 border border-green-200">

<p class="text-xs uppercase text-green-700 flex items-center gap-2">
<i class="fa fa-check-circle"></i> Accept
</p>

<p class="text-sm font-bold text-green-800 mt-3">
{{ $fundAccept ?? 0 }}
</p>

<p class="text-3xl font-bold text-green-700 mt-2">
₹ {{ number_format($fundAcceptSum ?? 0,2) }}
</p>

</div>


<!-- Reject -->
<div class="relative rounded-2xl p-6 bg-red-50 border border-red-200">

<p class="text-xs uppercase text-red-700 flex items-center gap-2">
<i class="fa fa-times-circle"></i> Reject
</p>

<p class="text-sm font-bold text-red-800 mt-3">
{{ $fundReject ?? 0 }}
</p>

<p class="text-3xl font-bold text-red-700 mt-2">
₹ {{ number_format($fundRejectSum ?? 0,2) }}
</p>

</div>

</div>

</div>
  <!-- ================= LATEST TRANSACTIONS ================= -->
<div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">

    <div class="flex justify-between items-center p-6 border-b">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">
                Recent Transaction Activity
            </h2>
            <p class="text-xs text-gray-500 mt-1">
                Latest 5 transactions across all services
            </p>
        </div>

        <a href="{{ route('admin.ledger.index') }}"
           class="text-sm text-indigo-600 hover:underline font-medium">
            View All →
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-900 text-white">
                <tr>
                    <th class="p-3 text-left">Date</th>
                    <th class="p-3 text-left">Merchant</th>
                    <th class="p-3 text-left">Service</th>
                    <th class="p-3 text-left">TXN ID</th>
                    <th class="p-3 text-left">Amount</th>
                    <th class="p-3 text-left">Status</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

                @forelse($latestTransactions as $txn)

                <tr class="hover:bg-gray-50 transition">

                    <td class="p-3 text-gray-600">
                        {{ \Carbon\Carbon::parse($txn->created_at)->format('d M Y, h:i A') }}
                    </td>

                    <td class="p-3 font-semibold text-indigo-600">
                        {{ $txn->remId }}
                    </td>

                    <td class="p-3">
                        <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-700 font-semibold">
                            {{ $txn->service }}
                        </span>
                    </td>

                    <td class="p-3 text-gray-700">
                        {{ $txn->txn_id }}
                    </td>

                    <td class="p-3 font-bold text-gray-900">
                        ₹{{ number_format($txn->amount,2) }}
                    </td>

                    <td class="p-3">
                        @if(strtolower($txn->status) == 'success')
                            <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-700 font-semibold">
                                Success
                            </span>
                        @elseif(strtolower($txn->status) == 'pending')
                            <span class="px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700 font-semibold">
                                Pending
                            </span>
                        @else
                            <span class="px-3 py-1 text-xs rounded-full bg-red-100 text-red-700 font-semibold">
                                Failed
                            </span>
                        @endif
                    </td>

                </tr>

                @empty
                <tr>
                    <td colspan="6" class="text-center p-6 text-gray-500">
                        No Recent Transactions Found
                    </td>
                </tr>
                @endforelse

            </tbody>
        </table>
    </div>

</div>

</div>

@endsection