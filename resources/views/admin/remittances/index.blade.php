



@extends('layouts.app')

@section('content')
<?php use App\Models\Package;
$packages = Package::all();
?>
<div class="min-h-screen bg-gray-50 py-6">
<div class="mx-auto w-full max-w-[1200px] px-4 text-[13px]">
      <!-- Header Section -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-semibold text-gray-800 tracking-tight">
                 Business Management
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                 Monitor, approve and manage all remittance accounts efficiently
            </p>
        </div>

        <div class="bg-white px-5 py-2 rounded-xl border border-gray-200 shadow-sm text-sm text-gray-600">
            Total Approved:
            <span class="font-semibold text-green-600">
                {{ count($remittances) }}
            </span>
        </div>
    </div>
    {{-- Flash Messages --}}
@if (session('success'))
    <div class="max-w-7xl mx-auto p-4 mb-4 text-green-800 bg-green-100 border border-green-300 rounded-lg shadow-sm">
        <strong>✅ Success:</strong> {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="max-w-7xl mx-auto p-4 mb-4 text-red-800 bg-red-100 border border-red-300 rounded-lg shadow-sm">
        <strong>❌ Error:</strong> {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="max-w-7xl mx-auto p-4 mb-4 text-yellow-800 bg-yellow-100 border border-yellow-300 rounded-lg shadow-sm">
        <strong>⚠️ Validation Errors:</strong>
        <ul class="list-disc ml-5 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    @if(session('success'))
    <div class="max-w-7xl mx-auto p-4 mb-4 text-green-800 bg-green-100 rounded-lg">
        {{ session('success') }}
    </div>
@endif



    <div class="bg-white shadow-xl rounded-2xl overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Package</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">KYC</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($remittances as $remittance)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $remittance->name }}||{{$remittance->remId}}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                               
                            @if($remittance->packageId == 0)
                                <button onclick="openPackageModal({{ $remittance->id }}, '{{ $remittance->name }}')" class="text-blue-600 hover:underline">No Package Assigned</button>
                            @else
                                {{ $remittance->package ? $remittance->package->packageName : '-' }}
                            <button onclick="openPackageModal({{ $remittance->id }}, '{{ $remittance->name }}')" class="text-blue-600 hover:underline">🖊</button>

                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">₹{{ number_format($remittance->amount, 2) }}</td>

                       
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($remittance->isKyc)
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                        Verified
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-600">
                                        Pending
                                    </span>
                                @endif
                            </td>                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $remittance->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : ($remittance->status == 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($remittance->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                            <button type="button" onclick="openLockModal({{ $remittance->id }}, '{{ $remittance->name }}')" 
                                class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-lg shadow text-sm">
                                Lock Amount
                            </button>

                            <a href="{{ route('admin.remittances.show', $remittance->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-lg shadow text-sm">View</a>
                            <a href="{{ route('remittances.approve', $remittance->id) }}" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg shadow text-sm">Approve</a>
                            {{-- <a href="{{ route('remittances.reject', $remittance->id) }}" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg shadow text-sm">Reject</a> --}}
                            <button type="button" onclick="openRejectModal({{ $remittance->id }}, '{{ $remittance->name }}')" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg shadow text-sm">Reject</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>



<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-96 relative">
        <h3 class="text-xl font-bold mb-4 text-gray-700">Reject Remittance</h3>
        <p id="rejectName" class="mb-4 text-gray-600"></p>
        <form id="rejectForm" method="POST">
    @csrf
    @method('POST') <!-- Optional, POST is default -->
    <label class="block mb-2 text-gray-700">Reason</label>
    <textarea name="remarks" class="w-full border rounded-lg p-2 mb-4" placeholder="Enter rejection reason" required></textarea>
    <div class="flex justify-end space-x-2">
        <button type="button" onclick="closeRejectModal()" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Reject</button>
    </div>
</form>

    </div>
</div>

<script>
function openRejectModal(id, name) {
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectName').innerText = "Reject: " + name;
    document.getElementById('rejectForm').action = '/admin/remittances/' + id + '/reject';
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}
</script>

<!-- Package Modal -->
<div id="packageModal" class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-96 relative">
        <h3 class="text-xl font-bold mb-4 text-gray-700">Assign Package</h3>
        <p id="packageRemittanceName" class="mb-4 text-gray-600"></p>
        <form id="packageForm" method="POST">
            @csrf
            <label class="block mb-2 text-gray-700">Select Package</label>
            <select name="packageId" class="w-full border rounded-lg p-2 mb-4" required>
                <option value="">-- Select Package --</option>
                @foreach($packages as $package)
                    <option value="{{ $package->id }}">{{ $package->packageName }}</option>
                @endforeach
            </select>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closePackageModal()" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Assign</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPackageModal(id, name) {
    document.getElementById('packageModal').classList.remove('hidden');
    document.getElementById('packageRemittanceName').innerText = "Assign Package for: " + name;
    document.getElementById('packageForm').action = '/admin/remittances/' + id + '/assign-package';
}

function closePackageModal() {
    document.getElementById('packageModal').classList.add('hidden');
}
</script>

<!-- Lock Amount Modal -->
<div id="lockModal" class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-96 relative">
        <h3 class="text-xl font-bold mb-4 text-gray-700">Lock Amount</h3>
        <p id="lockRemittanceName" class="mb-4 text-gray-600"></p>
        <form id="lockForm" method="POST">
            @csrf
            <label class="block mb-2 text-gray-700">Amount to Lock</label>
            <input type="number" name="amount" class="w-full border rounded-lg p-2 mb-4" placeholder="Enter amount" step="0.01" required>

            <label class="block mb-2 text-gray-700">Remark</label>
            <textarea name="remark" class="w-full border rounded-lg p-2 mb-4" placeholder="Enter remark (optional)"></textarea>

            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeLockModal()" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-yellow-500 text-white hover:bg-yellow-600">Lock</button>
            </div>
        </form>
    </div>
</div>

<script>
function openLockModal(id, name) {
    document.getElementById('lockModal').classList.remove('hidden');
    document.getElementById('lockRemittanceName').innerText = "Lock Amount for: " + name;
    document.getElementById('lockForm').action = '/admin/remittances/' + id + '/lock-amount';
}

function closeLockModal() {
    document.getElementById('lockModal').classList.add('hidden');
}
</script>
<script>
    setTimeout(() => {
        document.querySelectorAll('.max-w-7xl.mx-auto.p-4.mb-4').forEach(el => {
            el.style.transition = "opacity 0.5s ease";
            el.style.opacity = "0";
            setTimeout(() => el.remove(), 500);
        });
    }, 4000);
</script>

@endsection
