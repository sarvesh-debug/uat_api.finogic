@extends('users.layouts.app')

@section('content')
<div class="mx-auto w-full max-w-[1200px] px-4 text-[13px]">

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Fund Requests</h2>
            <p class="text-gray-500 text-sm mt-1">
                View and track your submitted fund requests
            </p>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <!-- Filter Section -->
        <div class="p-6 border-b bg-gray-50">
            <form class="grid md:grid-cols-5 gap-4" action="{{ route('remittances.add.fund.his') }}" method="GET">

                <input type="date" name="start_date"
                    value="{{ request('start_date') }}"
                    class="border border-gray-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500">

                <input type="date" name="end_date"
                    value="{{ request('end_date') }}"
                    class="border border-gray-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500">

                <input type="text" name="search"
                    placeholder="Search by UTR / Amount"
                    value="{{ request('search') }}"
                    class="border border-gray-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 md:col-span-2">

                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl shadow-sm transition">
                        Search
                    </button>

                    <button type="button"
                        onclick="exportToExcel()"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl shadow-sm transition">
                        Export
                    </button>
                </div>

            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table id="fundRequestsTable" class="min-w-full text-sm">

                <thead class="bg-gray-100 text-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left">#</th>
                        <th class="px-6 py-3 text-left">Company A/c</th>
                        <th class="px-6 py-3 text-left">Amount</th>
                        <th class="px-6 py-3 text-left">Mode</th>
                        <th class="px-6 py-3 text-left">UTR</th>
                        <th class="px-6 py-3 text-left">Proof</th>
                        <th class="px-6 py-3 text-left">Date</th>
                        <th class="px-6 py-3 text-left">Opening</th>
                        <th class="px-6 py-3 text-left">Closing</th>
                        <th class="px-6 py-3 text-left">Remark</th>
                        <th class="px-6 py-3 text-left">Admin Remark</th>
                        <th class="px-6 py-3 text-left">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @foreach ($fundRequests as $request)
                    <tr class="hover:bg-gray-50 transition">

                        <td class="px-6 py-4 font-medium text-gray-700">
                            {{ $loop->iteration }}
                        </td>

                        <td class="px-6 py-4 text-gray-600">
                            {{ $request->account_no }}
                        </td>

                        <td class="px-6 py-4 font-semibold text-gray-800">
                            ₹ {{ number_format($request->amount, 2) }}
                        </td>

                        <td class="px-6 py-4 text-gray-600">
                            {{ $request->mode }}
                        </td>

                        <td class="px-6 py-4 text-gray-600">
                            {{ $request->utr }}
                        </td>

                        <td class="px-6 py-4">
                            <button onclick="openModal('proofModal{{ $request->id }}')"
                                class="text-indigo-600 hover:underline font-medium">
                                View
                            </button>
                        </td>

                        <td class="px-6 py-4 text-gray-600">
                            {{ \Carbon\Carbon::parse($request->created_at)->format('d M Y') }}
                        </td>

                        <td class="px-6 py-4 text-gray-600">
                            ₹ {{ number_format($request->openingBalance, 2) }}
                        </td>

                        <td class="px-6 py-4 text-gray-600">
                            ₹ {{ number_format($request->closingBalance, 2) }}
                        </td>

                        <td class="px-6 py-4 text-gray-600">
                            {{ $request->remark ?? '-' }}
                        </td>

                        <td class="px-6 py-4 text-gray-600">
                            {{ $request->admin_remark ?? '-' }}
                        </td>

                        <td class="px-6 py-4">
                            @if ($request->status == 0)
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700">
                                    Pending
                                </span>
                            @elseif ($request->status == 1)
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                    Approved
                                </span>
                            @elseif ($request->status == -1)
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-600">
                                    Rejected
                                </span>
                            @else
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                    Unknown
                                </span>
                            @endif
                        </td>

                    </tr>

                    <!-- Modal -->
                    <div id="proofModal{{ $request->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
                        <div class="bg-white rounded-2xl shadow-xl max-w-3xl w-full">

                            <div class="flex justify-between items-center border-b px-6 py-4">
                                <h5 class="font-semibold text-gray-700">
                                    Transaction Proof - #{{ $request->id }}
                                </h5>
                                <button onclick="closeModal('proofModal{{ $request->id }}')"
                                    class="text-gray-400 hover:text-red-600 text-xl">&times;</button>
                            </div>

                            <div class="p-6 max-h-[60vh] overflow-y-auto space-y-4">
                                @php $images = json_decode($request->slip_images, true); @endphp
                                @if($images && count($images) > 0)
                                    @foreach ($images as $image)
                                        <div class="flex justify-center">
                                            <img src="{{ $image }}"
                                                class="rounded-xl shadow-md max-h-[300px] object-contain">
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-center text-gray-500">
                                        No proof image available
                                    </p>
                                @endif
                            </div>

                            <div class="flex justify-end border-t px-6 py-4">
                                <button onclick="closeModal('proofModal{{ $request->id }}')"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-xl shadow-sm transition">
                                    Close
                                </button>
                            </div>

                        </div>
                    </div>

                    @endforeach

                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}
function exportToExcel() {
    let table = document.getElementById('fundRequestsTable');
    let workbook = XLSX.utils.table_to_book(table, { sheet: "Fund Requests" });
    XLSX.writeFile(workbook, 'FundRequests.xlsx');
}
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

@endsection