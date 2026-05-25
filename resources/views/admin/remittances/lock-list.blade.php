@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
<div class="mx-auto w-full max-w-[1200px] px-4 text-[13px]">

    <!-- Page Header -->
<div class="mb-10">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        
        <div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 tracking-tight">
                Locked Amount Transactions
            </h1>
            <p class="mt-2 text-sm md:text-base text-gray-500">
                Monitor all lock and release activities with complete balance tracking
            </p>
            <div class="mt-4 w-20 h-1 bg-blue-600 rounded-full"></div>
        </div>

        <div class="bg-blue-50 text-blue-700 px-4 py-2 rounded-xl text-sm font-semibold shadow-sm">
            Total Records: {{ $transactions->total() ?? $transactions->count() }}
        </div>

    </div>
</div>
    <!-- Success / Error Alerts -->
    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-800 px-4 py-3 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Search -->
    <div class="mb-4 relative">
        <input type="text" id="searchInput" 
            placeholder="🔍 Search by Remitter ID or Admin..."
            class="w-full sm:w-1/3 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition duration-150">
    </div>

    <!-- Table -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse text-sm" id="lockTable">
                <thead class="bg-gradient-to-r from-blue-500 to-blue-600 text-white sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Date</th>
                        <th class="px-4 py-3 text-left font-semibold">Remitter ID</th>
                        <th class="px-4 py-3 text-left font-semibold">Amount</th>
                        <th class="px-4 py-3 text-left font-semibold">Before Available</th>
                        <th class="px-4 py-3 text-left font-semibold">After Available</th>
                        <th class="px-4 py-3 text-left font-semibold">Before Locked</th>
                        <th class="px-4 py-3 text-left font-semibold">After Locked</th>
                        <th class="px-4 py-3 text-left font-semibold">Type</th>
                        <th class="px-4 py-3 text-left font-semibold">Remark</th>
                        <th class="px-4 py-3 text-left font-semibold">Admin</th>
                        <th class="px-4 py-3 text-left font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transactions as $txn)
                        <tr class="hover:bg-blue-50 transition duration-100">
                            <td class="px-4 py-3 text-gray-700">{{ \Carbon\Carbon::parse($txn->created_at)->format('d M Y, h:i A') }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $txn->remId }}</td>
                            <td class="px-4 py-3 font-semibold text-green-600">₹{{ number_format($txn->amount, 2) }}</td>
                            <td class="px-4 py-3">₹{{ number_format($txn->before_available, 2) }}</td>
                            <td class="px-4 py-3">₹{{ number_format($txn->after_available, 2) }}</td>
                            <td class="px-4 py-3">₹{{ number_format($txn->before_locked, 2) }}</td>
                            <td class="px-4 py-3">₹{{ number_format($txn->after_locked, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs font-semibold rounded 
                                    {{ $txn->type == 'LOCK' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $txn->type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $txn->remark ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $txn->created_by ?? 'Admin' }}</td>
                            <td class="px-4 py-3">
                                @if($txn->after_locked > 0 && $txn->type == 'LOCK')
                                <button type="button" 
                                    onclick="openReleaseModal('{{ $txn->remId }}', {{ $txn->after_locked ?? 0 }})"
                                    class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg shadow text-sm">
                                    Release
                                </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-6 text-gray-500">No lock transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t bg-gray-50">
            {{ $transactions->links('pagination::tailwind') }}
        </div>
    </div>
</div>

<!-- Release Modal -->
<div id="releaseModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-96 shadow-xl">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Release Locked Amount</h3>
        <form method="POST" action="{{ route('admin.releaseLockedAmount') }}">
            @csrf

            <div class="mb-3">
                <label class="block text-gray-700 mb-1">Remitter ID</label>
                <input type="text" name="remId" id="releaseRemId" 
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400" 
                       readonly required>
            </div>

            <div class="mb-3">
                <label class="block text-gray-700 mb-1">Amount to Release</label>
                <input type="number" name="amount" id="releaseAmount" step="0.01" min="0" 
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400" 
                       required>
                <p class="text-gray-500 text-sm mt-1">Max: <span id="maxReleaseAmount">0</span></p>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 mb-1">Remark</label>
                <textarea name="remark" rows="2" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400" 
                          placeholder="Enter Remark (optional)"></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeReleaseModal()" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Release</button>
            </div>
        </form>
    </div>
</div>

<script>
// Open Release Modal and fill data
function openReleaseModal(remId, lockedBalance) {
    document.getElementById('releaseRemId').value = remId;
    document.getElementById('releaseAmount').value = lockedBalance;
    document.getElementById('releaseAmount').max = lockedBalance;
    document.getElementById('maxReleaseAmount').textContent = lockedBalance.toFixed(2);

    document.getElementById('releaseModal').classList.remove('hidden');
    document.getElementById('releaseModal').classList.add('flex');
}

// Close modal
function closeReleaseModal() {
    document.getElementById('releaseModal').classList.add('hidden');
    document.getElementById('releaseModal').classList.remove('flex');
}

// Search Filter
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#lockTable tbody tr');

    rows.forEach(row => {
        let remId = row.cells[1].textContent.toLowerCase();
        let admin = row.cells[9].textContent.toLowerCase();
        if (remId.includes(filter) || admin.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
@endsection
