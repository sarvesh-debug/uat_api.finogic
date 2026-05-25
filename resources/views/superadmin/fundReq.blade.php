@extends('superadmin.layouts.app')

@section('content')
<div class="container mx-auto mt-5 px-4">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="p-4 bg-gradient-to-r from-blue-900 to-blue-600 text-white">
            <h5 class="text-lg font-semibold">Fund Requests</h5>
        </div>

        <div class="p-4">
            <form class="flex flex-wrap gap-2 mb-4" action="" method="GET">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="border rounded p-2 flex-1">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="border rounded p-2 flex-1">
                <input type="text" name="search" placeholder="Enter Search Value" value="{{ request('search') }}" class="border rounded p-2 flex-1">
                <button type="submit" class="px-4 py-2 rounded text-white bg-gradient-to-r from-blue-900 to-blue-600">Search</button>
                <button type="button" onclick="exportToExcel()" class="px-4 py-2 rounded bg-green-600 text-white">Export</button>
            </form>

            <div class="overflow-x-auto">
                <table id="fundRequestsTable" class="w-full border-collapse border border-gray-300 text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-3 py-2">#</th>
                            <th class="border px-3 py-2">RTID</th>
                            <th class="border px-3 py-2">Name</th>
                            <th class="border px-3 py-2">Company A/c No</th>
                            <th class="border px-3 py-2">Amount</th>
                            <th class="border px-3 py-2">Mode</th>
                            <th class="border px-3 py-2">UTR</th>
                            <th class="border px-3 py-2">Proof</th>
                            <th class="border px-3 py-2">Raise Date</th>
                            <th class="border px-3 py-2">Opening</th>
                            <th class="border px-3 py-2">Closing</th>
                            <th class="border px-3 py-2">Remark</th>
                            <th class="border px-3 py-2">Admin Remark</th>
                            <th class="border px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fundRequests as $request)
                        <tr>
                            <td class="border px-3 py-2">{{ $loop->iteration }}</td>
                            <td class="border px-3 py-2">{{ $request->rid }}</td>
                            <td class="border px-3 py-2">{{ $request->request_by }}</td>
                            <td class="border px-3 py-2">{{ $request->account_no }}</td>
                            <td class="border px-3 py-2">{{ '₹' . number_format($request->amount, 2) }}</td>
                            <td class="border px-3 py-2">{{ $request->mode }}</td>
                            <td class="border px-3 py-2">{{ $request->utr }}</td>
                            <td class="border px-3 py-2">
                                <button onclick="openModal('proofModal{{ $request->id }}')" class="text-blue-600 hover:underline">View Proof</button>
                            </td>
                            <td class="border px-3 py-2">{{ \Carbon\Carbon::parse($request->created_at)->format('d-m-Y') }}</td>
                            <td class="border px-3 py-2">{{ '₹' . number_format($request->openingBalance, 2) }}</td>
                            <td class="border px-3 py-2">{{ '₹' . number_format($request->closingBalance, 2) }}</td>
                            <td class="border px-3 py-2">{{ $request->remark }}</td>
                            <td class="border px-3 py-2">{{ $request->admin_remark }}</td>
                             <td class="px-3 py-2 border">
                                @if ($request->status == 0)
                                    <span class="px-2 py-1 text-xs rounded bg-yellow-400 text-white">Pending</span>
                                @elseif ($request->status == 1)
                                    <span class="px-2 py-1 text-xs rounded bg-green-500 text-white">Done</span>
                                @elseif ($request->status == -1)
                                    <span class="px-2 py-1 text-xs rounded bg-blue-500 text-white">Reject</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-gray-400 text-white">Unknown</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 border">
                                @if($request->status == 0)
                                    <button onclick="openModal('acceptModal{{ $request->id }}')" class="px-3 py-1 text-sm rounded bg-green-600 text-white">Accept</button>
                                    <button onclick="openModal('rejectModal{{ $request->id }}')" class="px-3 py-1 text-sm rounded bg-red-600 text-white">Reject</button>
                                @elseif($request->status == -1)
                                    <span class="px-3 py-1 text-sm rounded bg-gray-400 text-white">Rejected</span>
                                @else
                                    <span class="px-3 py-1 text-sm rounded bg-green-600 text-white">Approved</span>
                                @endif
                            </td>
                        </tr>
                        <!-- Accept Modal -->
                        <div id="acceptModal{{ $request->id }}" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                                <h2 class="text-lg font-semibold mb-4">Accept Fund Request</h2>
                                <p>Are you sure you want to accept request <b>#{{ $request->rid }}</b> of ₹{{ number_format($request->amount, 2) }}?</p>
                                <div class="mt-4 flex justify-end space-x-2">
                                    {{-- {{ route('fund.accept', $request->id) }}  --}}
                                    <form action="{{ route('superadmin.fund.approve', $request->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="amount" value="{{ $request->amount }}">
                                        <input type="hidden" name="rid" value="{{ $request->rid }}">

                                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Confirm</button>
                                    </form>
                                    <button onclick="closeModal('acceptModal{{ $request->id }}')" class="px-4 py-2 bg-gray-400 text-white rounded">Cancel</button>
                                </div>
                            </div>
                        </div>

                        <!-- Reject Modal -->
                        <div id="rejectModal{{ $request->id }}" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                                <h2 class="text-lg font-semibold mb-4">Reject Fund Request</h2>
                                {{-- {{ route('fund.reject', $request->id) }} --}}
                                <form action="{{ route('superadmin.fund.reject', $request->id) }}" method="POST">
                                    @csrf
                                    <label class="block mb-2">Reason</label>
                                    <textarea name="remark" class="w-full border rounded-lg p-2" placeholder="Enter rejection reason"></textarea>
                                    <div class="mt-4 flex justify-end space-x-2">
                                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Reject</button>
                                        <button type="button" onclick="closeModal('rejectModal{{ $request->id }}')" class="px-4 py-2 bg-gray-400 text-white rounded">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>


                        {{-- ✅ Proof Modal for each request --}}
                        <div id="proofModal{{ $request->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                            <div class="bg-white rounded-lg shadow-lg max-w-3xl w-full">
                                <div class="flex justify-between items-center border-b p-4">
                                    <h5 class="text-lg font-semibold">Proof Images for Transaction {{ $request->id }}</h5>
                                    <button onclick="closeModal('proofModal{{ $request->id }}')" class="text-gray-500 hover:text-red-600 text-2xl leading-none">&times;</button>
                                </div>
                                <div class="p-4 space-y-4 max-h-[70vh] overflow-y-auto">
                                    @php
                                        $images = json_decode($request->slip_images, true);
                                    @endphp
                                    @if($images && count($images) > 0)
                                        @foreach ($images as $image)
                                            <div class="flex justify-center">
                                                <img src="{{ $image }}" alt="Proof Image" class="rounded-lg shadow-md max-h-[400px] object-contain" />
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-center text-gray-500">No proof image available</p>
                                    @endif
                                </div>
                                <div class="flex justify-end border-t p-4">
                                    <button onclick="closeModal('proofModal{{ $request->id }}')" class="px-4 py-2 bg-gradient-to-r from-blue-800 to-blue-600 text-white rounded-md hover:opacity-90">Close</button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ✅ Success/Error Modal --}}
@if(session('success') || session('error'))
<div id="statusModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full text-center">
        @if(session('success'))
            <img src="https://cdn-icons-png.flaticon.com/512/5610/5610944.png" alt="Success" class="w-20 mx-auto mb-3">
            <h5 class="text-green-700 font-bold">{{ session('success') }}</h5>
        @elseif(session('error'))
            <img src="https://media.giphy.com/media/TqiwHbFBaZ4ti/giphy.gif" alt="Failed" class="w-20 mx-auto mb-3">
            <h5 class="text-red-600 font-bold">{{ session('error') }}</h5>
        @endif
        <button onclick="document.getElementById('statusModal').remove()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded">Close</button>
    </div>
</div>
@endif

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
