@extends('users.layouts.app')

@section('content')
<div class="mx-auto w-full max-w-[1200px] px-4 text-[13px]">

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Charges Slab</h2>
            <p class="text-gray-500 mt-1 text-sm">
                Transparent transaction charge structure based on amount slabs
            </p>
        </div>

        <div class="mt-4 md:mt-0">
            <div class="bg-indigo-50 text-indigo-700 px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
                Total Slabs: {{ $slabs->count() }}
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
            <p class="text-sm text-gray-500">Minimum Charge</p>
            <h3 class="text-2xl font-bold text-gray-800 mt-2">
                ₹ {{ $charges->min_charge ?? '0.00' }}
            </h3>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
            <p class="text-sm text-gray-500">Maximum Charge</p>
            <h3 class="text-2xl font-bold text-gray-800 mt-2">
                ₹ {{ $charges->max_charge ?? '0.00' }}
            </h3>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
            <p class="text-sm text-gray-500">Slab Range</p>
            <h3 class="text-2xl font-bold text-indigo-600 mt-2">
                {{ $slabs->count() }} Active Slabs
            </h3>
        </div>

    </div>

    <!-- Charges Table Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <!-- Section Header -->
        <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-700">
                Charges Details
            </h3>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left">#</th>
                        <th class="px-6 py-3 text-left">Service</th>
                        <th class="px-6 py-3 text-left">From</th>
                        <th class="px-6 py-3 text-left">To</th>
                        <th class="px-6 py-3 text-left">Charge</th>
                        <th class="px-6 py-3 text-left">Type</th>
                        <th class="px-6 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">

                    @foreach($slabs as $index => $slab)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-medium text-gray-700">
                            {{ $index + 1 }}
                        </td>

                        <td class="px-6 py-4 text-gray-600">
                             {{ ($slab['service_name']) }}
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            ₹ {{ number_format($slab['from_amount'], 2) }}
                        </td>

                        <td class="px-6 py-4 text-gray-600">
                            ₹ {{ number_format($slab['to_amount'], 2) }}
                        </td>

                        <td class="px-6 py-4 font-semibold text-gray-800">
                            {{ $slab['charge'] }}
                            {{ $slab['charge_type'] == 'percentage' ? '%' : '₹' }}
                        </td>

                        <td class="px-6 py-4 capitalize text-gray-600">
                            {{ $slab['charge_type'] }}
                        </td>

                        <td class="px-6 py-4">
                            @if($slab['status'] == 'active')
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                    Active
                                </span>
                            @else
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-600">
                                    Inactive
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        <!-- Mobile View -->
        <div class="md:hidden p-4 space-y-4">

            @foreach($slabs as $index => $slab)
            <div class="bg-gray-50 rounded-xl p-4 shadow-sm">

                <div class="flex justify-between items-center mb-3">
                    <span class="text-sm font-semibold text-gray-700">
                        Slab #{{ $index + 1 }}
                    </span>

                    @if($slab['status'] == 'active')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                            Active
                        </span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-600">
                            Inactive
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm text-gray-600">
                    <div>
                        <p class="text-gray-400 text-xs">From</p>
                        <p class="font-medium">₹ {{ number_format($slab['from_amount'], 2) }}</p>
                    </div>

                    <div>
                        <p class="text-gray-400 text-xs">To</p>
                        <p class="font-medium">₹ {{ number_format($slab['to_amount'], 2) }}</p>
                    </div>

                    <div>
                        <p class="text-gray-400 text-xs">Charge</p>
                        <p class="font-medium">
                            {{ $slab['charge'] }}
                            {{ $slab['charge_type'] == 'percentage' ? '%' : '₹' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-400 text-xs">Type</p>
                        <p class="font-medium capitalize">
                            {{ $slab['charge_type'] }}
                        </p>
                    </div>
                </div>

            </div>
            @endforeach

        </div>

    </div>
</div>
@endsection