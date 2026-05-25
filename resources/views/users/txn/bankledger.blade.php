@extends('users.layouts.app')

@section('content')

<div class="mx-auto w-full max-w-[1200px] px-4 text-[13px]">

    <!-- ================= HEADER ================= -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">
                Business Statement
            </h2>
            <p class="text-sm text-gray-500">
                Last Updated: {{ now()->format('d M Y, h:i A') }}
            </p>
        </div>

        <a href="{{ route('ledger.export') }}"
           class="bg-green-600 text-white px-5 py-2 rounded-lg shadow hover:bg-green-700 transition">
            ⬇ Export CSV
        </a>
    </div>

    <!-- ================= FILTER SECTION ================= -->
    <form method="GET" class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

            <div>
                <label class="text-sm text-gray-600">From Date</label>
                <input type="date" name="from_date"
                       value="{{ request('from_date') }}"
                       class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="text-sm text-gray-600">To Date</label>
                <input type="date" name="to_date"
                       value="{{ request('to_date') }}"
                       class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="text-sm text-gray-600">Transaction ID</label>
                <input type="text" name="txn_id"
                       value="{{ request('txn_id') }}"
                       placeholder="Enter TXN ID"
                       class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="text-sm text-gray-600">Service</label>
                <select name="service"
                        class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">All Services</option>
                    <option value="Account Verification" {{ request('service')=='Account Verification' ? 'selected' : '' }}>Account Verification</option>
                    <option value="XpressPayout" {{ request('service')=='XpressPayout' ? 'selected' : '' }}>XpressPayout</option>
                    <option value="UPI Payout" {{ request('service')=='UPI Payout' ? 'selected' : '' }}>UPI Payout</option>
                    <option value="Fund Request" {{ request('service')=='Fund Request' ? 'selected' : '' }}>Fund Request</option>
                    <option value="AEPS" {{ request('service')=='AEPS' ? 'selected' : '' }}>AEPS</option>
                    <option value="DMT" {{ request('service')=='DMT' ? 'selected' : '' }}>DMT</option>
                    <option value="PG" {{ request('service')=='PG' ? 'selected' : '' }}>PG</option>
                    <option value="PG_P1" {{ request('service')=='PG_P1' ? 'selected' : '' }}>PG1</option>
                    <option value="PG_P2" {{ request('service')=='PG_P2' ? 'selected' : '' }}>PG2</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="w-full bg-blue-600 text-white p-2 rounded-lg hover:bg-blue-700 transition">
                    Apply
                </button>

                <a href="{{ route('ledger.index') }}"
                   class="w-full bg-gray-200 text-gray-700 p-2 text-center rounded-lg hover:bg-gray-300 transition">
                    Reset
                </a>
            </div>

        </div>
    </form>

    <!-- ================= SUMMARY CARDS ================= -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-6">

        <div class="bg-white p-5 rounded-2xl shadow border border-gray-100">
            <p class="text-sm text-gray-500">Total Transactions</p>
            <h3 class="text-2xl font-bold text-gray-800">
                {{ $records->count() }}
            </h3>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow border border-gray-100">
            <p class="text-sm text-gray-500">Total Amount</p>
            <h3 class="text-2xl font-bold text-green-600">
                ₹{{ number_format($records->sum('amount'), 2) }}
            </h3>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow border border-gray-100">
            <p class="text-sm text-gray-500">Total Charges</p>
            <h3 class="text-2xl font-bold text-red-600">
                ₹{{ number_format($records->sum('charges'), 2) }}
            </h3>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow border border-gray-100">
            <p class="text-sm text-gray-500">Total TDS</p>
            <h3 class="text-2xl font-bold text-yellow-600">
                ₹{{ number_format($records->sum('tds'), 2) }}
            </h3>
        </div>

    </div>

    <!-- ================= LEDGER TABLE ================= -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-900 text-white">
                    <tr>
                        <th class="p-3 text-left">Date</th>
                        <th class="p-3 text-left">TXN ID</th>
                        <th class="p-3 text-left">Type</th>
                        <th class="p-3 text-left">Service</th>
                        <th class="p-3 text-left">Amount</th>
                        <th class="p-3 text-left">Charges</th>
                        <th class="p-3 text-left">TDS</th>
                        <th class="p-3 text-left">Opening Balance</th>
                        <th class="p-3 text-left">Closing Balance</th>
                    </tr>
                </thead>

              <tbody class="divide-y divide-gray-100">
                @forelse($records as $row)

                @php
                    $isIn = strtolower($row->type) == 'in';
                @endphp

                <tr class="hover:bg-gray-50 transition">

                    <td class="p-3 text-gray-600">
                        {{ \Carbon\Carbon::parse($row->created_at)->format('d M Y, h:i A') }}
                    </td>

                    <td class="p-3 font-semibold text-blue-600">
                        {{ $row->txn_id ?? '-' }}
                    </td>

                    <!-- ✅ TYPE COLUMN HIGHLIGHTED -->
                    <td class="p-3">
                        @if($isIn)
                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700">
                                IN
                            </span>
                        @else
                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-red-100 text-red-700">
                                OUT
                            </span>
                        @endif
                    </td>

                    <td class="p-3">
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">
                            {{ $row->service_name }}
                        </span>
                    </td>

                    <!-- ✅ AMOUNT COLOR BASED ON TYPE -->
                    <td class="p-3 font-bold {{ $isIn ? 'text-green-600' : 'text-red-600' }}">
                        {{ $isIn ? '+' : '-' }} ₹{{ number_format($row->amount ?? 0, 2) }}
                    </td>

                    <td class="p-3 text-red-600">
                        ₹{{ number_format($row->charges ?? 0, 2) }}
                    </td>

                    <td class="p-3 text-yellow-600">
                        ₹{{ number_format($row->tds ?? 0, 2) }}
                    </td>

                    <td class="p-3 text-gray-700">
                        ₹{{ number_format($row->opening_balance ?? 0, 2) }}
                    </td>

                    <td class="p-3 font-bold text-gray-900">
                        ₹{{ number_format($row->closing_balance ?? 0, 2) }}
                    </td>

                </tr>

                @empty
                <tr>
                    <td colspan="9" class="text-center p-6 text-gray-500">
                        No Transactions Found
                    </td>
                </tr>
                @endforelse
                </tbody>

            </table>
            <!-- Pagination -->
            <div class="p-4">
                {{ $records->links('pagination::tailwind') }}
            </div>
        </div>

    </div>

</div>

@endsection