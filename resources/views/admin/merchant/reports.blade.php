@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4">

        <div class="bg-white shadow rounded-xl p-6">

            <h2 class="text-xl font-semibold mb-4">Transaction Reports</h2>

            <!-- FILTER -->
            <form method="GET" class="flex flex-wrap gap-3 mb-5">

                <select name="type" class="border px-3 py-2 rounded">
                    <option value="">All Type</option>
                    <option value="credit">Credit</option>
                    <option value="debit">Debit</option>
                </select>

                <input type="date" name="from" class="border px-3 py-2 rounded">
                <input type="date" name="to" class="border px-3 py-2 rounded">

                <button class="bg-blue-500 text-white px-4 py-2 rounded">
                    Filter
                </button>
            </form>

            <!-- TABLE -->
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200">

                    <thead class="bg-gray-100 text-xs uppercase text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">RemID</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="px-4 py-3 text-left">Amount</th>
                            <th class="px-4 py-3 text-left">Opening</th>
                            <th class="px-4 py-3 text-left">Closing</th>
                            <th class="px-4 py-3 text-left">Remark</th>
                            <th class="px-4 py-3 text-left">Date</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y text-sm">
                        @foreach($reports as $row)
                        <tr class="hover:bg-gray-50">

                            <td class="px-4 py-3 text-blue-600 font-medium">
                                {{ $row->remId }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $row->email }}
                            </td>

                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded 
                                    {{ $row->type == 'credit' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ ucfirst($row->type) }}
                                </span>
                            </td>

                            <td class="px-4 py-3 font-semibold">
                                ₹{{ $row->amount }}
                            </td>

                            <td class="px-4 py-3">
                                ₹{{ $row->opening_balance }}
                            </td>

                            <td class="px-4 py-3">
                                ₹{{ $row->closing_balance }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $row->remark }}
                            </td>

                            <td class="px-4 py-3 text-gray-500">
                                {{ date('d M Y, h:i A', strtotime($row->created_at)) }}
                            </td>

                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

            <!-- PAGINATION -->
            <div class="mt-4">
                {{ $reports->links() }}
            </div>

        </div>

    </div>
</div>
@endsection