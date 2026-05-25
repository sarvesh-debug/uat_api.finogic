@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-6xl mt-10">
    <h2 class="text-2xl font-bold mb-6">Manual Fund Records</h2>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto bg-white shadow-md rounded-lg">
        <table class="min-w-full border-collapse border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 border">#</th>
                    <th class="px-4 py-2 border">Amount</th>
                    <th class="px-4 py-2 border">Opening Balance</th>
                    <th class="px-4 py-2 border">Closing Balance</th>
                    <th class="px-4 py-2 border">Remark</th>
                    {{-- <th class="px-4 py-2 border">Added By</th> --}}
                    <th class="px-4 py-2 border">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($funds as $fund)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 border">{{ $loop->iteration + ($funds->currentPage()-1)*$funds->perPage() }}</td>
                        <td class="px-4 py-2 border text-blue-600 font-semibold">₹{{ number_format($fund->amount, 2) }}</td>
                        <td class="px-4 py-2 border">₹{{ number_format($fund->opbalance, 2) }}</td>
                        <td class="px-4 py-2 border">₹{{ number_format($fund->clbalance, 2) }}</td>
                        <td class="px-4 py-2 border">{{ $fund->remark ?? '-' }}</td>
                        {{-- <td class="px-4 py-2 border">{{ $fund->added_by ?? 'N/A' }}</td> --}}
                        <td class="px-4 py-2 border">{{ $fund->created_at->format('d-M-Y h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-gray-500">No records found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $funds->links() }}
    </div>
</div>
@endsection
