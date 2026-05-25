@extends('layouts.app')

@section('content')

<div class="container mx-auto max-w-7xl py-10">
    <div class="text-center mb-8">
        <h2 class="text-3xl font-extrabold text-gray-800"> Fund Requests</h2>
        <p class="text-gray-500 mt-2">All fund request details</p>
    </div>

    <div class="bg-white shadow-lg rounded-2xl border border-gray-200 overflow-x-auto">
        <table class="min-w-full table-auto border-collapse border border-gray-200 text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 border">ID</th>
                    <th class="px-4 py-2 border">Request By</th>
                    <th class="px-4 py-2 border">Phone</th>
                    <th class="px-4 py-2 border">RID</th>
                    <th class="px-4 py-2 border">Bank ID</th>
                    <th class="px-4 py-2 border">IFSC</th>
                    <th class="px-4 py-2 border">Account No</th>
                    <th class="px-4 py-2 border">Amount</th>
                    <th class="px-4 py-2 border">Opening Balance</th>
                    <th class="px-4 py-2 border">Closing Balance</th>
                    <th class="px-4 py-2 border">UTR</th>
                    <th class="px-4 py-2 border">Date</th>
                    <th class="px-4 py-2 border">Status</th>
                    <th class="px-4 py-2 border">Mode</th>
                    <th class="px-4 py-2 border">Slip Images</th>
                    <th class="px-4 py-2 border">Remark</th>
                    <th class="px-4 py-2 border">Admin Remark</th>
                    <th class="px-4 py-2 border">Employee Name</th>
                    <th class="px-4 py-2 border">Employee ID</th>
                    <th class="px-4 py-2 border">Created At</th>
                    <th class="px-4 py-2 border">Updated At</th>
                    <th class="px-4 py-2 border">Total Amount</th>
                    <th class="px-4 py-2 border">TDS</th>
                    <th class="px-4 py-2 border">Charges</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fundRequests as $f)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2 border">{{ $f->id }}</td>
                        <td class="px-4 py-2 border">{{ $f->request_by }}</td>
                        <td class="px-4 py-2 border">{{ $f->phone }}</td>
                        <td class="px-4 py-2 border">{{ $f->rid }}</td>
                        <td class="px-4 py-2 border">{{ $f->bank_id }}</td>
                        <td class="px-4 py-2 border">{{ $f->ifsc }}</td>
                        <td class="px-4 py-2 border">{{ $f->account_no }}</td>
                        <td class="px-4 py-2 border">₹ {{ number_format($f->amount,2) }}</td>
                        <td class="px-4 py-2 border">₹ {{ number_format($f->openingBalance,2) }}</td>
                        <td class="px-4 py-2 border">₹ {{ number_format($f->closingBalance,2) }}</td>
                        <td class="px-4 py-2 border">{{ $f->utr }}</td>
                        <td class="px-4 py-2 border">{{ $f->date }}</td>
                        <td class="px-4 py-2 border">{{ ucfirst($f->status) }}</td>
                        <td class="px-4 py-2 border">{{ $f->mode }}</td>
                        <td class="px-4 py-2 border">
                            @if($f->slip_images)
                                <a href="{{ asset('storage/slips/'.$f->slip_images) }}" target="_blank" class="text-blue-600 underline">View</a>
                            @else
                                --
                            @endif
                        </td>
                        <td class="px-4 py-2 border">{{ $f->remark }}</td>
                        <td class="px-4 py-2 border">{{ $f->admin_remark }}</td>
                        <td class="px-4 py-2 border">{{ $f->employeeName }}</td>
                        <td class="px-4 py-2 border">{{ $f->employeeId }}</td>
                        <td class="px-4 py-2 border">{{ $f->created_at }}</td>
                        <td class="px-4 py-2 border">{{ $f->updated_at }}</td>
                        <td class="px-4 py-2 border">₹ {{ number_format($f->totalAmount,2) }}</td>
                        <td class="px-4 py-2 border">₹ {{ number_format($f->tds,2) }}</td>
                        <td class="px-4 py-2 border">₹ {{ number_format($f->charges,2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="25" class="text-center py-6 text-gray-500">No fund requests found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
