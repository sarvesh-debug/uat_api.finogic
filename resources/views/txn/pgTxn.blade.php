@extends('layouts.app')

@section('content')
<div class="mx-auto w-full max-w-[1200px] py-8 px-4 text-[13px]">

    <!-- Page Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Payment Gateway Report</h2>
            <p class="text-gray-500 text-sm">Monitor transactions & balances</p>
        </div>

        <a href="{{ route('pg.report.export.admin', request()->all()) }}"
           class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm shadow hover:bg-green-500">
            Export CSV
        </a>

         <a href="{{ route('cron.job.pg') }}"
               class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-500 transition">
                Status Update
            </a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

        <div class="bg-white border rounded-xl p-4 shadow-sm">
            <p class="text-gray-500 text-xs">Total Transactions</p>
            <h3 class="text-lg font-bold">{{ $summary->total_txn ?? 0 }}</h3>
        </div>

        <div class="bg-green-50 border rounded-xl p-4">
            <p class="text-green-600 text-xs">Success Amount</p>
            <h3 class="text-lg font-bold text-green-700">
                ₹ {{ number_format($summary->success_amount ?? 0,2) }}
            </h3>
        </div>

        <div class="bg-yellow-50 border rounded-xl p-4">
            <p class="text-yellow-600 text-xs">Pending Amount</p>
            <h3 class="text-lg font-bold text-yellow-700">
                ₹ {{ number_format($summary->pending_amount ?? 0,2) }}
            </h3>
        </div>

        <div class="bg-red-50 border rounded-xl p-4">
            <p class="text-red-600 text-xs">Failed Amount</p>
            <h3 class="text-lg font-bold text-red-700">
                ₹ {{ number_format($summary->failed_amount ?? 0,2) }}
            </h3>
        </div>

    </div>

    <!-- Filters -->
    <form method="GET" class="bg-white border rounded-xl p-4 mb-6 shadow-sm">
        <div class="grid md:grid-cols-5 gap-4">

            <input type="date" name="from" value="{{ request('from') }}"
                class="border rounded-lg px-3 py-2">

            <input type="date" name="to" value="{{ request('to') }}"
                class="border rounded-lg px-3 py-2">
             <input type="text" name="remId" value="{{ request('remId') }}"
                class="border rounded-lg px-3 py-2" placeholder="Rem Id">

            <select name="status" class="border rounded-lg px-3 py-2">
                <option value="">All Status</option>
                <option value="SUCCESS" {{ request('status')=='SUCCESS'?'selected':'' }}>SUCCESS</option>
                <option value="PENDING" {{ request('status')=='PENDING'?'selected':'' }}>PENDING</option>
                <option value="FAILED" {{ request('status')=='FAILED'?'selected':'' }}>FAILED</option>
            </select>

            {{-- <select name="pgType" class="border rounded-lg px-3 py-2">
                <option value="">All PG</option>
                <option value="RAZORPAY">RAZORPAY</option>
                <option value="PAYTM">PAYTM</option>
            </select> --}}

            <button class="bg-indigo-600 text-white rounded-lg px-4 py-2">
                Apply Filter
            </button>
            <a href="{{ route('pg.report.admin') }}"
                   class="bg-gray-200 px-4 py-2 rounded-md text-sm hover:bg-gray-300 transition w-full text-center">
                    Reset
                </a>

        </div>
    </form>

    <!-- Table Card -->
    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-xs uppercase text-gray-600">
                    <tr>
                        <th class="px-3 py-3 text-left">#</th>
                        <th class="px-3 py-3 text-left">RemId</th>
                        <th class="px-3 py-3 text-left">Txn ID</th>
                        <th class="px-3 py-3 text-left">Order ID</th>
                        <th class="px-3 py-3 text-left">Amount</th>
                        <th class="px-3 py-3 text-left">Charges</th>
                        <th class="px-3 py-3 text-left">Opening</th>
                        <th class="px-3 py-3 text-left">Closing</th>
                        {{-- <th class="px-3 py-3 text-left">PG</th> --}}
                        <th class="px-3 py-3 text-left">Status</th>
                        <th class="px-3 py-3 text-left">Date</th>
                        <th class="px-3 py-3 text-center">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($txn as $index => $t)
                    <tr class="hover:bg-gray-50 transition">

                        <td class="px-3 py-3">
                            {{ $txn->firstItem() + $index }}
                        </td>

                        <td class="px-3 py-3 font-medium break-all">
                            {{ $t->remId }}
                        </td>
                        <td class="px-3 py-3 font-medium break-all">
                            {{ $t->txnId }}
                        </td>

                        <td class="px-3 py-3 break-all">
                            {{ $t->orderId ?? '--' }}
                        </td>

                        <td class="px-3 py-3 font-semibold text-green-600">
                            ₹ {{ number_format($t->amount,2) }}
                        </td>

                        <td class="px-3 py-3 text-xs">
                            Charges: ₹{{ $t->charges }} <br>
                            GST: ₹{{ $t->tds }}
                        </td>

                        <td class="px-3 py-3">
                            ₹ {{ number_format($t->openingBalance,2) }}
                        </td>

                        <td class="px-3 py-3 font-semibold">
                            ₹ {{ number_format($t->closingBalance,2) }}
                        </td>

                        {{-- <td class="px-3 py-3 uppercase text-xs">
                            {{ $t->pgType }}
                        </td> --}}

                        <td class="px-3 py-3">
                            @if($t->status === 'SUCCESS')
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                    SUCCESS
                                </span>
                            @elseif($t->status === 'PENDING')
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">
                                    PENDING
                                </span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">
                                    FAILED
                                </span>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-xs text-gray-500">
                            {{ \Carbon\Carbon::parse($t->created_at)->format('d M Y, h:i A') }}
                        </td>

                        <td class="px-3 py-3 text-center space-x-2">

                            <!-- View Button -->
                            <button onclick="showResponse({{ $t->id }})"
                                class="bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1 rounded-md text-xs">
                                View
                            </button>

                            <!-- Status Check Button -->
                            {{-- {{ route('pg.status.check',$t->id) }} --}}
                          <form id="statusForm-{{ $t->id }}"
                                action="{{ route('chkPGApiStatus') }}"
                                method="POST"
                                class="hidden">
                                @csrf
                               
                                <input type="hidden" name="remId" value="{{ $t->remId }}">
                                <input type="hidden" name="orderId" value="{{ $t->orderId }}">
                            </form>
                             <button type="button"
                        onclick="document.getElementById('statusForm-{{ $t->id }}').submit();"
                        class="bg-indigo-600 text-white px-3 py-1.5 rounded-md text-xs font-medium hover:bg-indigo-500 transition whitespace-nowrap">
                        Status Check
                    </button>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-8 text-gray-400">
                            No transactions found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4">
            {{ $txn->links() }}
        </div>
    </div>

</div>

<!-- Modal -->
<!-- Modal -->
<div id="responseModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl max-w-2xl w-full p-6 relative">

        <h3 class="text-lg font-semibold mb-4 border-b pb-2">
            Transaction Details
        </h3>

        <div id="responseContent" class="space-y-3 text-sm"></div>

        <button onclick="closeModal()"
            class="absolute top-3 right-3 text-gray-500 hover:text-red-600">
            ✕
        </button>

    </div>
</div>

<script>
function showResponse(id){

    let data = @json($txn->items());
    let row = data.find(r => r.id === id);

    let responseData = {};
    try {
        responseData = JSON.parse(row.responseData ?? '{}');
    } catch(e){
        responseData = {};
    }

    let html = `
        <div class="grid grid-cols-2 gap-3">

            <div>
                <p class="text-gray-500">Transaction ID</p>
                <p class="font-medium">${row.txnId ?? '-'}</p>
            </div>

            <div>
                <p class="text-gray-500">Order ID</p>
                <p class="font-medium">${row.orderId ?? '-'}</p>
            </div>

            <div>
                <p class="text-gray-500">Amount</p>
                <p class="font-semibold text-green-600">₹ ${parseFloat(row.amount).toFixed(2)}</p>
            </div>

            <div>
                <p class="text-gray-500">Status</p>
                <p class="font-semibold">${row.status}</p>
            </div>

            <div>
                <p class="text-gray-500">PG Type</p>
                <p>${row.pgType ?? '-'}</p>
            </div>

            <div>
                <p class="text-gray-500">Charges</p>
                <p>₹ ${row.charges ?? 0}</p>
            </div>

            <div>
                <p class="text-gray-500">GST</p>
                <p>₹ ${row.tds ?? 0}</p>
            </div>

            <div>
                <p class="text-gray-500">Opening Balance</p>
                <p>₹ ${row.openingBalance ?? 0}</p>
            </div>

            <div>
                <p class="text-gray-500">Closing Balance</p>
                <p>₹ ${row.closingBalance ?? 0}</p>
            </div>

            <div class="col-span-2">
                <p class="text-gray-500">Bank Reference No</p>
                <p class="bg-gray-100 p-2 rounded">
                  ${row.bank_ref_no ?? 0}
                </p>
            </div>

        </div>
    `;

    document.getElementById('responseContent').innerHTML = html;
    document.getElementById('responseModal').classList.remove('hidden');
    document.getElementById('responseModal').classList.add('flex');
}

function closeModal(){
    document.getElementById('responseModal').classList.add('hidden');
    document.getElementById('responseModal').classList.remove('flex');
}
</script>

@endsection