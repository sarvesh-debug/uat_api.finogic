{{-- @extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Businesses</h1>
    <a href="{{ route('businesses.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg">+ Add Business</a>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mt-4">
            {{ session('success') }}
        </div>
    @endif

    <table class="w-full mt-6 bg-white shadow rounded-lg">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-3 text-left">Name</th>
                <th class="p-3 text-left">Domain</th>
                <th class="p-3 text-left">Email</th>
                <th class="p-3 text-left">City</th>
                <th class="p-3 text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($businesses as $business)
            <tr class="border-t">
                <td class="p-3">{{ $business->name }}</td>
                <td class="p-3">{{ $business->domain_name }}</td>
                <td class="p-3">{{ $business->business_email }}</td>
                <td class="p-3">{{ $business->city }}</td>
                <td class="p-3 flex space-x-2">
                    <a href="{{ route('businesses.show', $business) }}" class="text-blue-600">View</a>
                    <a href="{{ route('businesses.edit', $business) }}" class="text-yellow-600">Edit</a>
                    <form method="POST" action="{{ route('businesses.destroy', $business) }}" onsubmit="return confirm('Delete this business?');">
                        @csrf @method('DELETE')
                        <button class="text-red-600">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $businesses->links() }}
    </div>
</div>
@endsection --}}






@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <!-- Page Title & Add Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <i class="fa-solid fa-briefcase text-indigo-600"></i> Businesses
        </h1>
        <a href="{{ route('businesses.create') }}"
           class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow-md transition">
            <i class="fa-solid fa-plus mr-2"></i> Add Business
        </a>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4 flex items-center shadow">
            <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Responsive Table Wrapper -->
    <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
        <table class="w-full text-sm text-gray-700">
            <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="p-3 text-left">Name</th>
                    <th class="p-3 text-left">Domain</th>
                    <th class="p-3 text-left">Email</th>
                    <th class="p-3 text-left">City</th>
                    <th class="p-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($businesses as $business)
                <tr class="border-t hover:bg-gray-50 transition">
                    <td class="p-3 font-medium">{{ $business->name }}</td>
                    <td class="p-3">{{ $business->domain_name }}</td>
                    <td class="p-3">{{ $business->business_email }}</td>
                    <td class="p-3">{{ $business->city }}</td>
                    <td class="p-3 text-center space-x-2">
                        <a href="{{ route('businesses.show', $business) }}"
                           class="inline-flex items-center px-2 py-1 text-blue-600 hover:bg-blue-50 rounded-md transition">
                            <i class="fa-solid fa-eye mr-1"></i> View
                        </a>
                        <a href="{{ route('businesses.edit', $business) }}"
                           class="inline-flex items-center px-2 py-1 text-yellow-600 hover:bg-yellow-50 rounded-md transition">
                            <i class="fa-solid fa-pen mr-1"></i> Edit
                        </a>
                        <form method="POST" action="{{ route('businesses.destroy', $business) }}"
                              class="inline"
                              onsubmit="return confirm('Delete this business?');">
                            @csrf @method('DELETE')
                            <button class="inline-flex items-center px-2 py-1 text-red-600 hover:bg-red-50 rounded-md transition">
                                <i class="fa-solid fa-trash mr-1"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-4 text-center text-gray-500">No businesses found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $businesses->links() }}
    </div>
</div>
@endsection
