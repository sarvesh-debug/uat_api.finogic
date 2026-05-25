{{-- @extends('./layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6 bg-gray-50 min-h-screen">

    <h2 class="text-3xl font-bold mb-6 text-gray-800 text-center">Rejected Users</h2>

    <div class="bg-white shadow-xl rounded-2xl overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Rejected</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800">1</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800">Alice Johnson</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800">alice@example.com</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800">example.com</td>
                    <td class="px-6 py-4 whitespace-nowrap text-red-600 font-medium">Incomplete Documents</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">2025-09-22</td>
                </tr>

              
                <tr class="bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800">2</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800">Bob Smith</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800">bob@example.com</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800">mybusiness.com</td>
                    <td class="px-6 py-4 whitespace-nowrap text-red-600 font-medium">Invalid Email</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">2025-09-21</td>
                </tr>

                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800">3</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800">Charlie Davis</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800">charlie@example.com</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800">startup.io</td>
                    <td class="px-6 py-4 whitespace-nowrap text-red-600 font-medium">Rejected by Admin</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">2025-09-20</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection --}}







@extends('./layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6 bg-gray-50 min-h-screen">

    <h2 class="text-3xl font-bold mb-6 text-gray-800 text-center">Rejected Remittances</h2>

    <div class="bg-white shadow-xl rounded-2xl overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business</th>
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
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800">{{ $remittance->business_name ?? $remittance->brand_name ?? '-' }}</td>
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
