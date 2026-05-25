@extends('layouts.app')

@section('content')
<div class="container mx-auto mt-10 px-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">All Packages</h2>
        <a href="{{ route('packages.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium">+ Create Package</a>
    </div>

    {{-- Flash Message --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Package Table --}}
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        @if ($packages->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Package Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated At</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($packages as $package)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $package->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $package->packageName }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $package->created_by }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($package->status)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ \Carbon\Carbon::parse($package->created_at)->format('d M Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ \Carbon\Carbon::parse($package->updated_at)->format('d M Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end gap-2">
                                <a href="{{ route('packages.comm', $package->id) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                <a href="{{ route('packages.edit', $package->id) }}" class="text-yellow-500 hover:text-yellow-700">Edit</a>
                                
                                {{-- Delete Form --}}
                                <form action="{{ route('packages.destroy', $package->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this package?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6 text-center text-gray-500">
                No packages found. Click "Create Package" to add one.
            </div>
        @endif
    </div>
</div>

{{-- Status Modal --}}
<div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-80">
        <div class="p-6 text-center">
            @if(session('success'))
                <img src="https://cdn-icons-png.flaticon.com/512/5610/5610944.png" alt="Success" class="mx-auto w-20">
                <h5 class="mt-4 text-green-600">{{ session('success') }}</h5>
            @elseif(session('error'))
                <img src="https://media.istockphoto.com/id/1904567040/vector/red-cross-wrong-icon.jpg?s=612x612&w=0&k=20&c=gmkYwAWonQIwrqd1J4C-z2eV11CRyvpr5XyspjaG2KQ=" alt="Failed" class="mx-auto w-20">
                <h5 class="mt-4 text-red-600">{{ session('error') }}</h5>
            @endif
        </div>
        <div class="p-4 text-center">
            <button onclick="document.getElementById('statusModal').classList.add('hidden')" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Close</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        @if(session('success') || session('error'))
            document.getElementById('statusModal').classList.remove('hidden');
        @endif
    });
</script>
@endsection
