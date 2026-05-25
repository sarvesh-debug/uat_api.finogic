@extends('layouts.app')

@section('content')

<div class="w-full px-4 mt-4">
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">

        <!-- Header -->
        <div class="bg-white px-4 py-3 border-b">
            <h5 class="text-lg font-semibold mb-0">BBPS Service Charges Configuration</h5>
            <small class="text-gray-500">
                Configure slab-wise charges, commission & TDS (Flat / Percentage)
            </small>
        </div>

        <!-- PACKAGE DROPDOWN -->
        <form method="GET" action="{{ url()->current() }}">
            <div class="flex mt-3 mb-2">
                <div class="w-full md:w-1/3 md:ml-[16.66%]">
                    <select name="packages"
                            class="w-full border border-gray-300 rounded-md px-3 py-2"
                            onchange="this.form.submit()">
                        @foreach($packages as $pkg)
                            <option value="{{ $pkg->id }}"
                                {{ $selectedPackage == $pkg->id ? 'selected' : '' }}>
                                {{ $pkg->packageName }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <!-- SAVE FORM -->
        <form method="POST" action="{{ route('bbps.charges.save.new') }}">
            @csrf

            <input type="hidden" name="packages" value="{{ $selectedPackage }}">

            <div class="p-0">
                <div class="overflow-auto" style="max-height:70vh;">
                    <table class="min-w-full border border-gray-200 text-sm">
                        <thead class="bg-gray-100 text-center sticky top-0 z-10">
                            <tr>
                                <th class="min-w-[220px] px-3 py-2 border">Service</th>
                                <th class="px-3 py-2 border">From (₹)</th>
                                <th class="px-3 py-2 border">To (₹)</th>
                                <th class="px-3 py-2 border">Charges</th>
                                <th class="px-3 py-2 border">Type</th>
                                <th class="px-3 py-2 border">Commission</th>
                                <th class="px-3 py-2 border">Type</th>
                                <th class="px-3 py-2 border">TDS/GST</th>
                                <th class="px-3 py-2 border">Type</th>
                            </tr>
                        </thead>

                        <tbody>
                        @foreach($services as $service)

                            @php
                                $plan = $existingPlans[$service->category_code] ?? null;
                            @endphp

                            <tr class="hover:bg-gray-50">
                                <!-- Service -->
                                <td class="px-3 py-2 border font-semibold text-blue-600">
                                    {{ $service->category_name }}
                                    <div class="text-xs text-gray-500">
                                        {{ $service->category_code }}
                                    </div>
                                    <input type="hidden"
                                           name="service_code[]"
                                           value="{{ $service->category_code }}">
                                </td>

                                <!-- From -->
                                <td class="px-3 py-2 border">
                                    <div class="flex">
                                        <span class="px-2 py-2 border border-r-0 bg-gray-100 rounded-l">₹</span>
                                        <input type="number" step="0.01"
                                               name="from_amount[]"
                                               class="w-full border border-gray-300 rounded-r px-2 py-1"
                                               value="{{ $plan->from_amount ?? '' }}">
                                    </div>
                                </td>

                                <!-- To -->
                                <td class="px-3 py-2 border">
                                    <div class="flex">
                                        <span class="px-2 py-2 border border-r-0 bg-gray-100 rounded-l">₹</span>
                                        <input type="number" step="0.01"
                                               name="to_amount[]"
                                               class="w-full border border-gray-300 rounded-r px-2 py-1"
                                               value="{{ $plan->to_amount ?? '' }}">
                                    </div>
                                </td>

                                <!-- Charges -->
                                <td class="px-3 py-2 border">
                                    <input type="number" step="0.01"
                                           name="charges[]"
                                           class="w-full border border-gray-300 rounded px-2 py-1"
                                           value="{{ $plan->charge ?? '' }}">
                                </td>

                                <td class="px-3 py-2 border">
                                    <select name="charges_type[]" class="w-full border border-gray-300 rounded px-2 py-1">
                                        <option value="Flat"
                                            {{ ($plan->charge_in ?? '') == 'Flat' ? 'selected' : '' }}>
                                            Flat (₹)
                                        </option>
                                        <option value="Percentage"
                                            {{ ($plan->charge_in ?? '') == 'Percentage' ? 'selected' : '' }}>
                                            Percentage (%)
                                        </option>
                                    </select>
                                </td>

                                <!-- Commission -->
                                <td class="px-3 py-2 border">
                                    <input type="number" step="0.01"
                                           name="commission[]"
                                           class="w-full border border-gray-300 rounded px-2 py-1"
                                           value="{{ $plan->commissions ?? '' }}">
                                </td>

                                <td class="px-3 py-2 border">
                                    <select name="commission_type[]" class="w-full border border-gray-300 rounded px-2 py-1">
                                        <option value="Flat"
                                            {{ ($plan->commissions_in ?? '') == 'Flat' ? 'selected' : '' }}>
                                            Flat (₹)
                                        </option>
                                        <option value="Percentage"
                                            {{ ($plan->commissions_in ?? '') == 'Percentage' ? 'selected' : '' }}>
                                            Percentage (%)
                                        </option>
                                    </select>
                                </td>

                                <!-- TDS -->
                                <td class="px-3 py-2 border">
                                    <input type="number" step="0.01"
                                           name="tds[]"
                                           class="w-full border border-gray-300 rounded px-2 py-1"
                                           value="{{ $plan->tds ?? '' }}">
                                </td>

                                <td class="px-3 py-2 border">
                                    <select name="tds_type[]" class="w-full border border-gray-300 rounded px-2 py-1">
                                        <option value="Flat"
                                            {{ ($plan->tds_in ?? '') == 'Flat' ? 'selected' : '' }}>
                                            Flat (₹)
                                        </option>
                                        <option value="Percentage"
                                            {{ ($plan->tds_in ?? '') == 'Percentage' ? 'selected' : '' }}>
                                            Percentage (%)
                                        </option>
                                    </select>
                                </td>
                            </tr>

                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-white px-4 py-3 flex justify-between items-center border-t">
                <small class="text-gray-500">
                    Tip: Percentage values are calculated on transaction amount
                </small>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Save Configuration
                </button>
            </div>
        </form>

    </div>
</div>

@endsection