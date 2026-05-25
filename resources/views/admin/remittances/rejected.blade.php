@extends('layouts.app')

@section('title', 'Rejected Remittances')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
<div class="mx-auto w-full max-w-[1200px] px-4 text-[13px]">

    <h2 class="text-3xl font-bold mb-6 text-gray-800 text-center">Rejected Remittances</h2>

    <div class="bg-white shadow-xl rounded-2xl overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Rejected</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($remittances as $index => $remittance)
                <tr class="{{ $index % 2 == 0 ? '' : 'bg-gray-50' }}">
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800">{{ $index + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800">{{ $remittance->brand_name ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800">{{ $remittance->email ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800">{{ $remittance->brand_name ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800">{{ $remittance->amount ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-red-600 font-medium">{{ $remittance->remarks ?? 'Rejected by Admin' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $remittance->updated_at->format('Y-m-d') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No rejected remittances found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
