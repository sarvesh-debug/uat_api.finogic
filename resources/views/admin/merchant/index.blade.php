@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="mx-auto w-full max-w-[1200px] px-4 text-sm">

        <div class="bg-white shadow rounded-xl p-5">

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-700">
            Merchant List
        </h2>

        <a href="{{ route('reports.v1') }}"
           class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm shadow">
            View Reports
        </a>
    </div>
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden">
                    
                    <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">RemID</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-left">Lock Amount</th>
                            <th class="px-4 py-3 text-left">Balance</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody class="text-gray-700 divide-y">
                        @foreach($merchantDetails as $row)
                        <tr class="hover:bg-gray-50 transition">

                            <td class="px-4 py-3 font-medium text-blue-600">
                                {{ $row->remId }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $row->email }}
                            </td>

                            <td class="px-4 py-3">
                                ₹{{ $row->lockBalance ?? 0 }}
                            </td>

                            <td class="px-4 py-3 font-semibold text-green-600">
                                ₹{{ $row->amount }}
                            </td>

                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    {{ $row->status == 'success' ? 'bg-green-100 text-green-700' : 
                                       ($row->status == 'pending' ? 'bg-yellow-100 text-yellow-700' : 
                                       'bg-red-100 text-red-700') }}">
                                    {{ ucfirst($row->status) }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                            <div class="flex flex-col gap-2">

                                <!-- CREDIT -->
                                <form action="{{ route('merchant.credit') }}" method="POST" class="flex gap-1 items-center">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $row->remId }}">

                                    <input type="number" name="amount" placeholder="Amt"
                                        class="w-20 px-2 py-1 border rounded focus:outline-none focus:ring-1 focus:ring-green-400"
                                        min="1" required>

                                    <input type="text" name="remark" placeholder="Remark"
                                        class="w-32 px-2 py-1 border rounded focus:outline-none focus:ring-1 focus:ring-green-400"
                                        required>

                                    <button class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs">
                                        Credit
                                    </button>
                                </form>

                                <!-- DEBIT -->
                                <form action="{{ route('merchant.debit') }}" method="POST" class="flex gap-1 items-center">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $row->remId }}">

                                    <input type="number" name="amount" placeholder="Amt"
                                        class="w-20 px-2 py-1 border rounded focus:outline-none focus:ring-1 focus:ring-red-400"
                                        min="1" required>

                                    <input type="text" name="remark" placeholder="Remark"
                                        class="w-32 px-2 py-1 border rounded focus:outline-none focus:ring-1 focus:ring-red-400"
                                        required>

                                    <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                                        Debit
                                    </button>
                                </form>

                            </div>
                        </td>

                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

        </div>

    </div>
</div>
@endsection