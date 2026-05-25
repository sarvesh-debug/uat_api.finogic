@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-gray-50 py-6">
<div class="mx-auto w-full max-w-[1200px] px-4 text-[13px]">
    <!-- ================= HEADER ================= -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">

        <div>
            <h2 class="text-xl font-semibold text-gray-800">
                Transaction Management
            </h2>
            <p class="text-gray-500 text-sm">
                Monitor and manage Payout IMPS transactions efficiently
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('track.transaction.export.csv', request()->query()) }}"
               class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700 transition">
                Export CSV
            </a>
             {{-- <a href="{{ route('cron.job.payout') }}"
               class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-500 transition">
                Status Update
            </a> --}}
            {{-- <a href="{{ route('track.transaction.export.excel', request()->query()) }}"
               class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-500 transition">
                Export Excel
            </a> --}}
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
                {{ $txn->where('status','INITIATED')->count() }}
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

        <form method="GET" action="{{ route('transaction.admin') }}"
              class="grid grid-cols-1 md:grid-cols-5 gap-4">

            <input type="text" name="search"
                   value="{{ request('search') }}"
                   placeholder="Search Payment ID / UTR"
                   class="border rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-indigo-500">

             <input type="text" name="remId"
                   value="{{ request('remId') }}"
                   placeholder="Search RemId"
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
                <option value="Success" {{ request('status')=='Success'?'selected':'' }}>Success</option>
                <option value="Initiated" {{ request('status')=='Initiated'?'selected':'' }}>Pending</option>
                <option value="Failed" {{ request('status')=='Failed'?'selected':'' }}>Failed</option>
            </select>

            <div class="flex gap-2">
                <button type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-500 transition w-full">
                    Apply
                </button>

                <a href="{{ route('transaction.admin') }}"
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
                    <th class="px-4 py-3 text-left">RemId</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Payment ID</th>
                    <th class="px-4 py-3 text-left">Opening</th>
                    <th class="px-4 py-3 text-left">Amount</th>
                    <th class="px-4 py-3 text-left">Charges</th>
                    <th class="px-4 py-3 text-left">Closing</th>
                    <th class="px-4 py-3 text-left">Beneficiary</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">UTR</th>
                    <th class="px-4 py-3 text-center whitespace-nowrap">Status</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Date</th>
                     <th class="px-4 py-3 text-center whitespace-nowrap">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($txn as $t)
                <tr class="border-t hover:bg-gray-50 transition">

                    <td class="px-4 py-3">
                        {{ ($txn->currentPage() - 1) * $txn->perPage() + $loop->iteration }}
                    </td>
                     <td class="px-4 py-3 font-mono text-indigo-600 whitespace-nowrap">
                        {{ $t->remId }}
                    </td>
                    <td class="px-4 py-3 font-mono text-indigo-600 whitespace-nowrap">
                        <div>{{ $t->payment_id }}</div>
                        <div class="text-xs text-gray-500">{{ $t->refId }}</div>
                    </td>
                    <td class="px-4 py-3  font-semibold">
                        ₹ {{ number_format($t->opening_balance,2) }}
                    </td>
                    <td class="px-4 py-3 text-green-600 font-semibold">
                        ₹ {{ number_format($t->amount,2) }}
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap">

                        <div class="font-medium text-gray-800">
                            ₹ {{ $t->charge }}
                        </div>
                        <div  class="ml-2 text-xs text-red-500">
                             GST {{ $t->tds }}
                        </div>
                        
                    </td>


                    <td class="px-4 py-3">
                        ₹ {{ $t->closing_balance }}
                    </td>

                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">
                            {{ $t->beneficiary_name }}|{{ $t->acc_no }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $t->bank_name }} | {{ $t->ifsc_code }}
                        </div>
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap">
                        {{ $t->bank_ref_no ?? '--' }}
                    </td>

                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        @if($t->status=='Success')
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                SUCCESS
                            </span>
                             @elseif($t->status=='Refunded')
                             <span class="px-2 py-1 bg-red-300 text-red-900 rounded-full text-xs font-medium">
                                REFUNDED
                                
                            </span>
                        @elseif($t->status=='Pending')
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
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        <button type="button"
                       onclick='openTxnModal(@json($t))'
                        class="bg-gray-800 text-white px-3 py-1.5 rounded-md text-xs font-medium hover:bg-gray-700 transition whitespace-nowrap">
                        View
                    </button>
                    @if(($t->status == 'Initiated' || $t->status == 'Pending' || $t->status == 'Failed') 
                        // && $t->orderId == null 
                        && $t->opening_balance != $t->closing_balance)

                    <button type="button"
                        data-id="{{ $t->id }}"
                        data-txn="{{ $t->payment_id }}"
                        data-amount="{{ $t->amount }}"
                        class="bg-indigo-600 text-white px-3 py-1.5 rounded-md text-xs font-medium hover:bg-indigo-500 transition whitespace-nowrap refundBtn">

                        Refund

                    </button>

                    @endif
                    <form id="statusForm-{{ $t->id }}"
                        action="{{ route('chkPayoutApiStatus') }}"
                        method="POST"
                        class="hidden">
                        @csrf
                        <input type="hidden" name="payment_id" value="{{ $t->payment_id }}">
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
<!-- ================= TRANSACTION MODAL ================= -->
<div id="txnModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50">

    <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl border border-gray-200 relative overflow-hidden">

        <!-- HEADER -->
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800 tracking-wide">
                Transaction Details
            </h3>

            <button onclick="closeTxnModal()"
                class="text-gray-400 hover:text-gray-600 text-xl font-light transition">
                ✕
            </button>
        </div>

        <!-- BODY -->
        <div class="p-6 space-y-6 text-sm">

            <!-- Basic Info -->
            <div class="grid grid-cols-2 gap-6">

                <div class="bg-gray-50 border rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase">Payment ID</p>
                    <p id="m_payment_id" class="font-semibold text-indigo-600 mt-1"></p>
                </div>

                <div class="bg-gray-50 border rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase">Amount</p>
                    <p id="m_amount" class="font-semibold text-green-600 mt-1"></p>
                </div>

                <div class="bg-gray-50 border rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase">Charges</p>
                    <p id="m_charge" class="mt-1"></p>
                </div>

                <div class="bg-gray-50 border rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase">GST</p>
                    <p id="m_gst" class="text-red-500 mt-1"></p>
                </div>

                <div class="bg-gray-50 border rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase">Closing Balance</p>
                    <p id="m_closing" class="mt-1"></p>
                </div>

                <div class="bg-gray-50 border rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase">Status</p>
                    <p id="m_status" class="mt-1 font-semibold"></p>
                </div>

            </div>

            <!-- Divider -->
            <div class="border-t"></div>

            <!-- Beneficiary Info -->
            <div class="bg-white border rounded-lg p-4">

                <p class="text-xs text-gray-500 uppercase mb-2">Beneficiary Details</p>

                <p id="m_beneficiary" class="font-semibold text-gray-800"></p>
                <p id="m_bank" class="text-xs text-gray-500 mt-1"></p>

                <div class="grid grid-cols-2 gap-4 mt-4">

                    <div>
                        <p class="text-xs text-gray-500 uppercase">UTR(Reference Id)</p>
                        <p id="m_utr" class="mt-1"></p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 uppercase">Date</p>
                        <p id="m_date" class="mt-1"></p>
                    </div>

                </div>

            </div>

        </div>

    </div>
</div>
<!-- Refund Modal -->
<div id="refundModal" class="fixed inset-0 bg-black bg-opacity-40 hidden flex items-center justify-center z-50">

    <div class="bg-white w-full max-w-md rounded-xl shadow-lg p-6">

        <h2 class="text-xl font-semibold mb-4">Refund Transaction</h2>

        <form id="refundForm" method="POST" action="{{ route('refund.processv2') }}">
            @csrf

            <input type="hidden" name="txn_id" id="txn_id">

            <div class="mb-3">
                <label class="text-sm text-gray-600">Transaction ID</label>
                <input type="text" id="txn_number" class="w-full border rounded p-2 bg-gray-100" readonly>
            </div>

            <div class="mb-3">
                <label class="text-sm text-gray-600">Amount</label>
                <input type="text" id="txn_amount" class="w-full border rounded p-2 bg-gray-100" readonly>
            </div>

            <div class="mb-3">
                <label class="text-sm text-gray-600">Refund Reason</label>
                <textarea name="reason" class="w-full border rounded p-2" required></textarea>
            </div>
            <div class="mb-3">
                <label class="text-sm text-gray-600">Refund with Charges & GST(Optional)</label>
                <input type="checkbox" name="refund_charges" value="1" class="ml-2">
            </div>
            <div class="flex justify-end gap-3 mt-4">

                <button type="button" id="closeModal"
                    class="px-4 py-2 bg-gray-400 text-white rounded">
                    Cancel
                </button>

                <button type="submit"
                    class="px-4 py-2 bg-red-500 text-white rounded">
                    Process Refund
                </button>

            </div>

        </form>

    </div>

</div>

<script>
function openTxnModal(txn) {

    document.getElementById('m_payment_id').innerText = txn.payment_id;
    document.getElementById('m_amount').innerText = '₹ ' + txn.amount;
    document.getElementById('m_charge').innerText = '₹ ' + txn.charge;
    document.getElementById('m_gst').innerText = '₹ ' + txn.tds;
    document.getElementById('m_closing').innerText = '₹ ' + txn.closing_balance;
    document.getElementById('m_beneficiary').innerText = txn.beneficiary_name + ' | ' + txn.acc_no;
    document.getElementById('m_bank').innerText = txn.bank_name + ' | ' + txn.ifsc_code;
    document.getElementById('m_utr').innerText = txn.bank_ref_no ?? '--';
    document.getElementById('m_date').innerText = txn.created_at;

    // Status badge color
    let statusText = txn.status;
    let statusClass = 'text-gray-600';

    if(statusText === 'Success'){
        statusClass = 'text-green-600 font-semibold';
    } else if(statusText === 'Initiated'){
        statusClass = 'text-yellow-600 font-semibold';
    } else {
        statusClass = 'text-red-600 font-semibold';
    }

    let statusEl = document.getElementById('m_status');
    statusEl.innerText = statusText.toUpperCase();
    statusEl.className = statusClass;

    document.getElementById('txnModal').classList.remove('hidden');
    document.getElementById('txnModal').classList.add('flex');
}

function closeTxnModal() {
    document.getElementById('txnModal').classList.add('hidden');
}
</script>

{{-- //refund modal script --}}
<script>

document.querySelectorAll('.refundBtn').forEach(btn => {

    btn.addEventListener('click', function(){

        const modal = document.getElementById('refundModal');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.getElementById('txn_id').value = this.dataset.id;
        document.getElementById('txn_number').value = this.dataset.txn;
        document.getElementById('txn_amount').value = this.dataset.amount;

    });

});

document.getElementById('closeModal').addEventListener('click', function(){

    const modal = document.getElementById('refundModal');

    modal.classList.add('hidden');
    modal.classList.remove('flex');

});

</script>
@endsection
