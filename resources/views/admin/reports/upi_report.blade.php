@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
<div class="mx-auto w-full max-w-[1200px] px-4 text-[13px]">

    <!-- ================= HEADER ================= -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">

        <div>
            <h2 class="text-xl font-semibold text-gray-800">
               UPI Payout Transaction Management (Admin)
            </h2>
            <p class="text-gray-500 text-sm">
                Monitor and manage all UPI payout transactions
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.upi.reports.export', request()->query()) }}"
               class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700 transition">
                Export CSV
            </a>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('cron.job')}}"
               class="bg-yellow-800 text-white px-4 py-2 rounded-md text-sm hover:bg-yellow-700 transition">
              Status Update
            </a>
        </div>
        

    </div>

    <!-- ================= SUMMARY CARDS ================= -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

        <div class="bg-white border rounded-lg p-4 shadow-sm">
            <p class="text-gray-500 text-xs uppercase">Total Transactions</p>
            <h3 class="text-lg font-semibold text-gray-800 mt-1">
                {{ $upi->total() }}
            </h3>
        </div>

        <div class="bg-white border rounded-lg p-4 shadow-sm">
            <p class="text-gray-500 text-xs uppercase">Success</p>
            <h3 class="text-lg font-semibold text-green-600 mt-1">
                {{ $upi->where('status','Success')->count() }}
            </h3>
        </div>

        <div class="bg-white border rounded-lg p-4 shadow-sm">
            <p class="text-gray-500 text-xs uppercase">Pending / Initiated</p>
            <h3 class="text-lg font-semibold text-yellow-600 mt-1">
                {{ $upi->whereIn('status',['PENDING','Initiated'])->count() }}
            </h3>
        </div>

        <div class="bg-white border rounded-lg p-4 shadow-sm">
            <p class="text-gray-500 text-xs uppercase">Failed</p>
            <h3 class="text-lg font-semibold text-red-600 mt-1">
                {{ $upi->where('status','Failed')->count() }}
            </h3>
        </div>

    </div>

    <!-- ================= FILTER SECTION ================= -->
    <div class="bg-white shadow-sm border rounded-lg p-5 mb-5">

        <form method="GET" action="{{ route('admin.upi.reports') }}"
              class="grid grid-cols-1 md:grid-cols-5 gap-4">

            <input type="text" name="search"
                   value="{{ request('search') }}"
                   placeholder="Search Payment ID / RemID / UTR"
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
                <option value="INITIATED" {{ request('status')=='INITIATED'?'selected':'' }}>Initiated</option>
                <option value="FAILED" {{ request('status')=='FAILED'?'selected':'' }}>Failed</option>
            </select>

            <div class="flex gap-2">
                <button type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-500 transition w-full">
                    Apply
                </button>

                <a href="{{ route('admin.upi.reports') }}"
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
                    <th class="px-4 py-3 text-left">RemID</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Payment ID</th>
                    <th class="px-4 py-3 text-left">Beneficiary</th>
                    <th class="px-4 py-3 text-left">UTR</th>
                    <th class="px-4 py-3 text-left">Amount</th>
                    <th class="px-4 py-3 text-left">Charges</th>
                    <th class="px-4 py-3 text-left">Opening</th>
                    <th class="px-4 py-3 text-left">Closing</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Pipe</th>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($upi as $row)
                <tr class="border-t hover:bg-gray-50 transition">

                    <td class="px-4 py-3">
                        {{ ($upi->currentPage() - 1) * $upi->perPage() + $loop->iteration }}
                    </td>

                    <td class="px-4 py-3 font-medium break-all">
                        {{ $row->remId }}
                    </td>

                    <!-- <td class="px-4 py-3 font-mono text-indigo-600 break-all">
                        {{ $row->payment_id }}
                    </td> -->
                     <td class="px-4 py-3 font-mono text-indigo-600 whitespace-nowrap">
                        <div>{{ $row->payment_id }}</div>
                        <div class="text-xs text-gray-500">{{ $row->refId }}</div>
                    </td>

                     <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">
                            {{ $row->beneficiary_name }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $row->acc_no }}
                        </div>
                    </td>

                   

                    <td class="px-4 py-3 text-xs break-all">
                        {{ $row->bank_ref_no }}
                    </td>

                    <td class="px-4 py-3 text-green-600 font-semibold">
                        ₹ {{ number_format($row->amount,2) }}
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap">

                        <div class="font-medium text-gray-800">
                            ₹ {{ $row->charge }}
                        </div>
                        <div  class="ml-2 text-xs text-red-500">
                             GST {{ $row->tds }}
                        </div>
                        
                    </td>

                    <td class="px-4 py-3">
                        ₹ {{ number_format($row->opening_balance,2) }}
                    </td>

                    <td class="px-4 py-3 font-semibold">
                        ₹ {{ number_format($row->closing_balance,2) }}
                    </td>

                   <td class="px-4 py-3 text-center">
                        @php
                            $status = strtolower($row->status);
                        @endphp

                        @if($status == 'success')
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                SUCCESS
                            </span>

                        @elseif($status == 'pending')
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                                PENDING
                            </span>

                        @elseif($status == 'refunded')
                            <span class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium">
                                REFUNDED
                            </span>

                        @elseif($status == 'failed')
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">
                                FAILED
                            </span>

                        @else
                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                                INITIATED
                            </span>
                        @endif
                    </td>
                     <td class="px-4 py-3 font-semibold">
                         {{ $row->pipe }}
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap text-gray-500">
                        {{ \Carbon\Carbon::parse($row->created_at)->format('d M Y, h:i A') }}
                    </td>

                    <td class="px-4 py-3 text-center whitespace-nowrap">
                       <button type="button"
                            onclick='openTxnModal(@json($row))'
                            class="bg-gray-800 text-white px-3 py-1.5 rounded-md text-xs font-medium hover:bg-gray-700 transition">
                            View
                        </button>
                        @if ($row->pipe=='Aeronpay')
                         <form id="statusForm-{{ $row->id }}"
                        action="{{ route('chkPayoutApiStatusv2') }}"
                        method="POST"
                        class="hidden">
                        @csrf
                        <input type="hidden" name="client_referenceId" value="{{ $row->payment_id }}">
                        <input type="hidden" name="date_of_transaction" value="{{ \Carbon\Carbon::parse($row->created_at)->format('d-m-Y') }}">
                        <input type="hidden" name="mobile" value="9119110490">
                    </form>

                    <button type="button"
                        onclick="document.getElementById('statusForm-{{ $row->id }}').submit();"
                        class="bg-indigo-600 text-white px-3 py-1.5 rounded-md text-xs font-medium hover:bg-indigo-500 transition whitespace-nowrap">
                        Status Check
                    </button>
                    @else
                    <form id="statusForm-{{ $row->id }}"
                        action="{{ route('chkUpiApiStatus') }}"
                        method="POST"
                        class="hidden">
                        @csrf
                        <input type="hidden" name="payment_id" value="{{ $row->payment_id }}">
                        <input type="hidden" name="remId" value="{{ $row->remId }}">
                        <input type="hidden" name="orderId" value="{{ $row->order_id }}">
                    </form>

                    <button type="button"
                        onclick="document.getElementById('statusForm-{{ $row->id }}').submit();"
                        class="bg-indigo-600 text-white px-3 py-1.5 rounded-md text-xs font-medium hover:bg-indigo-500 transition whitespace-nowrap">
                        Status Check
                    </button>
                      @endif
                      @if(($row->status == 'Initiated' || $row->status == 'Pending'|| $row->status == 'Failed') 
                        // && $row->order_id == null 
                        && $row->opening_balance != $row->closing_balance)
                        <button type="button"
                        data-id="{{ $row->id }}"
                        data-txn="{{ $row->payment_id }}"
                        data-amount="{{ $row->amount }}"
                        class="bg-indigo-600 text-white px-3 py-1.5 rounded-md text-xs font-medium hover:bg-indigo-500 transition whitespace-nowrap mbuBtn">

                        M.B.U

                    </button>
                    <button type="button"
                        data-id="{{ $row->id }}"
                        data-txn="{{ $row->payment_id }}"
                        data-amount="{{ $row->amount }}"
                        class="bg-indigo-600 text-white px-3 py-1.5 rounded-md text-xs font-medium hover:bg-indigo-500 transition whitespace-nowrap refundBtn">

                        Refund

                    </button>

                    @endif

                </td>

                </tr>
                @empty
                <tr>
                    <td colspan="12" class="text-center py-8 text-gray-400">
                        No transactions found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <!-- Transaction Modal -->
<<div id="txnModal"
class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">

<div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl">

<!-- Header -->
<div class="flex items-center justify-between border-b px-6 py-4">

<h3 class="text-lg font-semibold text-gray-800">
Transaction Details
</h3>

<button onclick="closeTxnModal()"
class="text-gray-400 hover:text-red-500 text-xl">
✕
</button>

</div>

<!-- Body -->
<div class="p-6 space-y-6">

<!-- Highlight Section -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">

<div class="bg-gray-50 rounded-lg p-4">
<p class="text-xs text-gray-500">Payment ID</p>
<p id="m_payment_id" class="font-semibold text-indigo-600 break-all"></p>
</div>

<div class="bg-gray-50 rounded-lg p-4">
<p class="text-xs text-gray-500">RemID</p>
<p id="m_remId" class="font-semibold break-all"></p>
</div>

<div class="bg-gray-50 rounded-lg p-4">
<p class="text-xs text-gray-500">Status</p>
<span id="m_status"
class="inline-block px-3 py-1 text-xs rounded-full font-medium bg-gray-200"></span>
</div>

</div>

<!-- Bank Ref Highlight -->
<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 flex justify-between items-center">

<div>
<p class="text-xs text-yellow-700">Bank Reference Number (UTR)</p>

<p id="m_utr"
class="text-lg font-bold text-yellow-800 tracking-wide"></p>
</div>

<button onclick="copyUTR()"
class="bg-yellow-600 text-white text-xs px-3 py-1 rounded hover:bg-yellow-500">
Copy
</button>

</div>

<!-- Details -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-sm">

<div>
<p class="text-gray-500 text-xs">Beneficiary Name</p>
<p id="m_beneficiary" class="font-medium"></p>
</div>

<div>
<p class="text-gray-500 text-xs">Account Number</p>
<p id="m_account" class="font-medium"></p>
</div>

<div>
<p class="text-gray-500 text-xs">Amount</p>
<p id="m_amount" class="text-green-600 font-bold text-lg"></p>
</div>

<div>
<p class="text-gray-500 text-xs">Charges</p>
<p id="m_charge"></p>
</div>

<div>
<p class="text-gray-500 text-xs">Opening Balance</p>
<p id="m_opening"></p>
</div>

<div>
<p class="text-gray-500 text-xs">Closing Balance</p>
<p id="m_closing" class="font-semibold"></p>
</div>

<div>
<p class="text-gray-500 text-xs">Transaction Date</p>
<p id="m_date"></p>
</div>

</div>

</div>
     {{-- messge --}}
            <div class="bg-white border rounded-lg p-4">

            

                    <div>
                        <p class="text-xs text-gray-500 uppercase">Message</p>
                        <p id="m_message" class="mt-1"></p>
                    </div>

                    

            </div>
<!-- Footer -->
<div class="border-t px-6 py-4 flex justify-end">

<button onclick="closeTxnModal()"
class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700">
Close
</button>

</div>

</div>
</div>

    </div>

    <!-- ================= PAGINATION ================= -->
    <div class="mt-6 flex justify-between items-center text-sm text-gray-600">

        <div>
            Showing
            <span class="font-medium">{{ $upi->firstItem() }}</span>
            to
            <span class="font-medium">{{ $upi->lastItem() }}</span>
            of
            <span class="font-medium">{{ $upi->total() }}</span>
            results
        </div>

        <div>
            {{ $upi->links() }}
        </div>

    </div>

</div>
</div>

<!-- Refund Modal -->
<div id="refundModal" class="fixed inset-0 bg-black bg-opacity-40 hidden flex items-center justify-center z-50">

    <div class="bg-white w-full max-w-md rounded-xl shadow-lg p-6">

        <h2 class="text-xl font-semibold mb-4">Refund Transaction</h2>

        <form id="refundForm" method="POST" action="{{ route('refund.process.upi') }}">
            @csrf

            <input type="hidden" name="txn_id" id="txn_id">
            <input type="hidden" name="servce" value="UPI_PAYOUT">

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

<!-- manual modal -->
<!-- manual modal -->
<div id="manualModal" class="fixed inset-0 bg-black bg-opacity-40 hidden flex items-center justify-center z-50">

    <div class="bg-white w-full max-w-md rounded-xl shadow-lg p-6">

        <h2 class="text-xl font-semibold mb-4">Manual Update Transaction</h2>

        <form method="POST" action="{{ route('manual.process.v1') }}">
            @csrf

            <input type="hidden" name="txn_id" id="manual_txn_id">
            <input type="hidden" name="servce" value="UPI_PAYOUT">

            <div class="mb-3">
                <label class="text-sm text-gray-600">Transaction ID</label>
                <input type="text" id="manual_txn_number" class="w-full border rounded p-2 bg-gray-100" readonly>
            </div>

            <div class="mb-3">
                <label class="text-sm text-gray-600">Amount</label>
                <input type="text" id="manual_txn_amount" class="w-full border rounded p-2 bg-gray-100" readonly>
            </div>

            <!-- ✅ UTR FIELD -->
            <div class="mb-3">
                <label class="text-sm text-gray-600">UTR Number</label>
                <input type="text" name="utr" class="w-full border rounded p-2" placeholder="Enter UTR">
            </div>

            <!-- ✅ STATUS SELECT -->
            <div class="mb-3">
                <label class="text-sm text-gray-600">Status</label>
                <select name="status" class="w-full border rounded p-2" required>
                    <option value="">Select Status</option>
                    <option value="SUCCESS">Success</option>
                    <option value="FAILED">Failed</option>
                    <option value="PENDING">Pending</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="text-sm text-gray-600">Reason</label>
                <textarea name="reason" class="w-full border rounded p-2" required></textarea>
            </div>

            <div class="flex justify-end gap-3 mt-4">

                <button type="button" id="closeManualModal"
                    class="px-4 py-2 bg-gray-400 text-white rounded">
                    Cancel
                </button>

                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded">
                    Update
                </button>

            </div>

        </form>

    </div>
</div>

<script>

function openTxnModal(data)
{
    document.getElementById('txnModal').classList.remove('hidden');
    document.getElementById('txnModal').classList.add('flex');

    document.getElementById('m_payment_id').innerText = data.payment_id;
    document.getElementById('m_remId').innerText = data.remId;
    document.getElementById('m_beneficiary').innerText = data.beneficiary_name;
    document.getElementById('m_account').innerText = data.acc_no;
    document.getElementById('m_utr').innerText = data.bank_ref_no;

    document.getElementById('m_amount').innerText = "₹ " + data.amount;
    document.getElementById('m_charge').innerText = "₹ " + data.charge;

    document.getElementById('m_opening').innerText = "₹ " + data.opening_balance;
    document.getElementById('m_closing').innerText = "₹ " + data.closing_balance;

    document.getElementById('m_status').innerText = data.status;
    document.getElementById('m_date').innerText = data.created_at;

    let messageBox = document.getElementById('m_message');

try {
    let res = JSON.parse(data.responseBody);
    let message = '';

    // ✅ CASE 1: Changan type
    if (res?.data?.result?.status) {
        let status = res.data.result.status;
        let amt = res.data.result.amount;
        let utr = res.data.result.utr;

        message = `${status} | ₹${amt} | UTR: ${utr}`;
    }

    // ✅ CASE 2: AeronPay type
    else if (res?.status && res?.transactionId) {
        message = `${res.status} | ₹${res.txn_amount || res.amount} | UTR: ${res.utr}`;
    }

    // ✅ CASE 3: FAILED / PENDING with message
    else if (res?.status && res?.message) {
        message = `${res.status} | ${res.message}`;
    }

    // ✅ Fallback
    else {
        message = res.message || 'Unknown Response';
    }

    messageBox.innerText = message;

} catch (e) {
    // अगर JSON parse fail हो जाए
    messageBox.innerText = txn.responseBody;
}
}

function closeTxnModal()
{
    document.getElementById('txnModal').classList.add('hidden');
}

let statusEl = document.getElementById("m_status");

statusEl.innerText = data.status;

statusEl.className =
"inline-block px-3 py-1 text-xs rounded-full font-medium " +
(
data.status === "Success" ? "bg-green-100 text-green-700" :
data.status === "Failed" ? "bg-red-100 text-red-700" :
data.status === "Initiated" ? "bg-blue-100 text-blue-700" :
"bg-yellow-100 text-yellow-700"
);

function copyUTR()
{
let utr = document.getElementById("m_utr").innerText;
navigator.clipboard.writeText(utr);

alert("Bank Ref No Copied");
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
{{-- manual update --}}
<script>

document.querySelectorAll('.mbuBtn').forEach(btn => {

    btn.addEventListener('click', function(){

        const modal = document.getElementById('manualModal');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.getElementById('manual_txn_id').value = this.dataset.id;
        document.getElementById('manual_txn_number').value = this.dataset.txn;
        document.getElementById('manual_txn_amount').value = this.dataset.amount;

    });

});

document.getElementById('closeManualModal').addEventListener('click', function(){

    const modal = document.getElementById('manualModal');

    modal.classList.add('hidden');
    modal.classList.remove('flex');

});

</script>
@endsection