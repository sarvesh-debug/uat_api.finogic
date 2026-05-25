@extends('layouts.app')

@section('content')
@php $getCommission = DB::table('commissionservices')->where('status', 1)->get(); @endphp
<div class="px-6 py-6">

    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Commission Plans</h1>
            <p class="text-sm text-gray-500">Manage and configure commissions for each package & service.</p>
        </div>
        <a href="{{ url()->previous() }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
            ← Back
        </a>
    </div>

    <!-- Success / Error messages -->
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-100 text-green-700 px-4 py-3 shadow-sm">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-100 text-red-700 px-4 py-3 shadow-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li class="text-sm">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Commission Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border border-gray-100">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">Add / Edit Commission</h2>
        <form action="{{ route('commission.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @csrf

            <!-- Packages -->
            {{-- <div>
                <label class="text-sm font-medium text-gray-600">Package</label>
                <select name="packages" id="packages"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white text-sm shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Select Package --</option>
                    <option value="Retailer">Retailer</option>
                    <option value="Distributor">Distributor</option>
                </select>
            </div> --}}
<input type="hidden" name="packages" id="packages" value="Retailer">
            <!-- Services -->
            <div>
                <label class="text-sm font-medium text-gray-600">Service</label>
                <select name="service" id="service"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white text-sm shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Select Service --</option>
                    @foreach ($getCommission as $item)
                        <option value="{{ $item->CommCode }}">{{ $item->serviceName }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Sub Service -->
            <div id="sub-service-container" class="hidden">
                <label class="text-sm font-medium text-gray-600">Mode</label>
                <select name="sub_service" id="sub_service"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white text-sm shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Select Mode --</option>
                    <option value="RTGS">RTGS</option>
                    <option value="IMPS">IMPS</option>
                    <option value="NEFT">NEFT</option>
                    <option value="ATP">Airtel</option>
                    <option value="BGP">BSNL</option>
                    <option value="BSNL00000NATHL">BSNL (BBPS)</option>
                    <option value="MTNL00000NAT1U">MTNL</option>
                    <option value="RJP">Reliance Jio</option>
                    <option value="VFP">Vi</option>
                </select>
            </div>

            <!-- Amounts -->
            <div>
                <label class="text-sm font-medium text-gray-600">From Amount</label>
                <input type="number" name="from_amount" id="from_amount"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white text-sm shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600">To Amount</label>
                <input type="number" name="to_amount" id="to_amount"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white text-sm shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Charge -->
            <div>
                <label class="text-sm font-medium text-gray-600">Charge In</label>
                <select name="charge_in" id="charge_in"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white text-sm shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Select --</option>
                    <option value="Flat">Flat</option>
                    <option value="Percentage">Percentage</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600">Charge</label>
                <input type="number" step="any" name="charge" id="charge"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white text-sm shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Commission -->
            <div>
                <label class="text-sm font-medium text-gray-600">Commission In</label>
                <select name="commissions_in" id="commission_in"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white text-sm shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Select --</option>
                    <option value="Flat">Flat</option>
                    <option value="Percentage">Percentage</option>
                </select>
            </div>
          <div>
                <label class="text-sm font-medium text-gray-600">Commission</label>
                <input type="number" step="any" name="commissions" id="commission"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white text-sm shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- TDS -->
            <div>
                <label class="text-sm font-medium text-gray-600">GST In</label>
                <select name="tds_in" id="tds_in"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white text-sm shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Select --</option>
                    <option value="Flat">Flat</option>
                    <option value="Percentage">Percentage</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600">GST</label>
                <input type="number" step="any" name="tds" id="tds"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white text-sm shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <input type="hidden" name="packagesId" id="packagesId" value="{{ $packageId }}">

            <!-- Submit / Reset -->
            <div class="flex items-end gap-2 col-span-full">
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg shadow">
                    Save
                </button>
                <button type="reset" onclick="resetForm()"
                    class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm rounded-lg shadow">
                    Reset
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow overflow-x-auto border border-gray-100">
        <table class="min-w-full text-sm text-gray-700">
            <thead class="bg-gray-50 text-gray-800">
                <tr>
                    <th class="px-4 py-2 text-left">#</th>
                  
                    <th class="px-4 py-2 text-left">Service</th>
                    <th class="px-4 py-2 text-left">Sub Service</th>
                    <th class="px-4 py-2 text-left">Range</th>
                    <th class="px-4 py-2 text-left">Charge</th>
                    <th class="px-4 py-2 text-left">Commission</th>
                    <th class="px-4 py-2 text-left">GST</th>
                    <th class="px-4 py-2 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($commissions as $commission)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $loop->iteration }}</td>
                       
                        <td class="px-4 py-2">{{ $commission->service }}</td>
                        <td class="px-4 py-2">{{ $commission->sub_service ?? '-' }}</td>
                        <td class="px-4 py-2">
                            {{ number_format($commission->from_amount, 2) }} - {{ number_format($commission->to_amount, 2) }}
                        </td>
                        <td class="px-4 py-2">{{ $commission->charge }} ({{ $commission->charge_in }})</td>
                        <td class="px-4 py-2">{{ $commission->commissions }} ({{ $commission->commissions_in }})</td>
                        <td class="px-4 py-2">{{ $commission->tds }} ({{ $commission->tds_in }})</td>
                        <td class="px-4 py-2 text-center flex gap-2 justify-center">
                            <button onclick="editCommission({{ json_encode($commission) }})"
                                class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white text-xs rounded-lg">Edit</button>
                            <form action="{{ route('commission.destroy', $commission->id) }}" method="POST"
                                  onsubmit="return confirmDelete();">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded-lg">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-4 text-center text-gray-500">No commissions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    function confirmDelete() {
        return confirm('Are you sure you want to delete this Commission?');
    }
    function resetForm() { location.reload(); }
</script>
@endsection
