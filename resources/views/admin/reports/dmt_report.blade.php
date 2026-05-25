@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
<div class="mx-auto w-full max-w-[1200px] px-4 text-[13px]">

    <!-- ================= HEADER ================= -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">

        <div>
            <h2 class="text-xl font-semibold text-gray-800">
               DMT Transaction Management (Admin)
            </h2>
            <p class="text-gray-500 text-sm">
                Monitor and manage all Domestic Money Transfer transactions
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.dmt.reports.export', request()->query()) }}"
               class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700 transition">
                Export CSV
            </a>
        </div>

    </div>

    <!-- ================= SUMMARY CARDS ================= -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

        <div class="bg-white border rounded-lg p-4 shadow-sm">
            <p class="text-gray-500 text-xs uppercase">Total Transactions</p>
            <h3 class="text-lg font-semibold text-gray-800 mt-1">
                {{ $txn->total() }}
            </h3>
        </div>

        <div class="bg-white border rounded-lg p-4 shadow-sm">
            <p class="text-gray-500 text-xs uppercase">Success</p>
            <h3 class="text-lg font-semibold text-green-600 mt-1">
                {{ $txn->where('status','SUCCESS')->count() }}
            </h3>
        </div>

        <div class="bg-white border rounded-lg p-4 shadow-sm">
            <p class="text-gray-500 text-xs uppercase">Pending</p>
            <h3 class="text-lg font-semibold text-yellow-600 mt-1">
                {{ $txn->where('status','PENDING')->count() }}
            </h3>
        </div>

        <div class="bg-white border rounded-lg p-4 shadow-sm">
            <p class="text-gray-500 text-xs uppercase">Failed</p>
            <h3 class="text-lg font-semibold text-red-600 mt-1">
                {{ $txn->where('status','FAILED')->count() }}
            </h3>
        </div>

    </div>

    <!-- ================= FILTER SECTION ================= -->
    <div class="bg-white shadow-sm border rounded-lg p-5 mb-5">

        <form method="GET" action="{{ route('admin.dmt.reports') }}"
              class="grid grid-cols-1 md:grid-cols-5 gap-4">

            <input type="text" name="search"
                   value="{{ request('search') }}"
                   placeholder="Search Payment ID / Beneficiary / Merchant"
                   class="border rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-indigo-500">

            <input type="date" name="from_date"
                   value="{{ request('from_date') }}"
                   class="border rounded-md px-3 py-2 text-sm">

            <input type="date" name="to_date"
                   value="{{ request('to_date') }}"
                   class="border rounded-md px-3 py-2 text-sm">

            <select name="status"
                    class="border rounded-md px-3 py-2 text-sm">
                <option value="">All Status</option>
                <option value="SUCCESS" {{ request('status')=='SUCCESS'?'selected':'' }}>Success</option>
                <option value="PENDING" {{ request('status')=='PENDING'?'selected':'' }}>Pending</option>
                <option value="FAILED" {{ request('status')=='FAILED'?'selected':'' }}>Failed</option>
            </select>

            <div class="flex gap-2">
                <button type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-500 transition w-full">
                    Apply
                </button>

                <a href="{{ route('admin.dmt.reports') }}"
                   class="bg-gray-200 px-4 py-2 rounded-md text-sm hover:bg-gray-300 transition w-full text-center">
                    Reset
                </a>
            </div>

        </form>
    </div>

    <!-- ================= TABLE ================= -->
    <div class="bg-white shadow-sm border rounded-lg overflow-x-auto">

        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-gray-600 uppercase text-[11px]">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Merchant</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Payment ID</th>
                    <th class="px-4 py-3 text-left">Beneficiary</th>
                    <th class="px-4 py-3 text-left">Amount</th>
                    <th class="px-4 py-3 text-left">Charges</th>
                    <th class="px-4 py-3 text-left">Closing</th>
                    <th class="px-4 py-3 text-center whitespace-nowrap">Status</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Date</th>
                </tr>
            </thead>

            <tbody>
                @forelse($txn as $t)
                <tr class="border-t hover:bg-gray-50 transition">

                    <td class="px-4 py-3">
                        {{ ($txn->currentPage() - 1) * $txn->perPage() + $loop->iteration }}
                    </td>

                    <td class="px-4 py-3 font-medium">
                        {{ $t->merchant_id }}
                    </td>

                    <td class="px-4 py-3 font-mono text-indigo-600 whitespace-nowrap">
                        {{ $t->externalRef }}
                    </td>

                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">
                            {{ $t->beneficiaryName }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $t->accountNumber }} | {{ $t->ifsc }}| {{ $t->outlet_id }}
                        </div>
                    </td>

                    <td class="px-4 py-3 text-green-600 font-semibold">
                        ₹ {{ number_format($t->amount,2) }}
                    </td>

                   <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                    ₹ {{ $t->charges }}
                     <span class="ml-2 text-xs text-red-500">
                            GST {{ $t->tds }}
                        </span>
                </td>

                    <td class="px-4 py-3 font-semibold">
                        ₹ {{ number_format($t->closing_balance,2) }}
                    </td>

                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        @if($t->status=='SUCCESS')
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                SUCCESS
                            </span>
                        @elseif($t->status=='PENDING')
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">
                                PENDING
                            </span>
                        @else
                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                                FAILED
                            </span>
                        @endif
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap text-gray-500">
                        {{ \Carbon\Carbon::parse($t->created_at)->format('d M Y, h:i A') }}
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-8 text-gray-400">
                        No transactions found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    <!-- ================= PAGINATION ================= -->
    <div class="mt-6 flex justify-between items-center text-sm text-gray-600">

        <div>
            Showing
            <span class="font-medium">{{ $txn->firstItem() }}</span>
            to
            <span class="font-medium">{{ $txn->lastItem() }}</span>
            of
            <span class="font-medium">{{ $txn->total() }}</span>
            results
        </div>

        <div>
            {{ $txn->links() }}
        </div>

    </div>

</div>
</div>
@endsection