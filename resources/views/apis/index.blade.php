@extends('layouts.app')

@section('content')
<div class="p-6">

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">API Management</h2>

        {{-- <a href="{{ route('apis.create') }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">
            + Add API
        </a> --}}
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Table -->
    <div class="bg-white shadow rounded-xl overflow-hidden">
        <table class="min-w-full text-sm text-left text-gray-600">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Message</th>
                    <th class="px-6 py-3 text-center">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse($apis as $api)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-medium text-gray-900">
                        {{ $api->name }}
                    </td>

                    <td class="px-6 py-4">
                        @if($api->status)
                            <span class="px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                Active
                            </span>
                        @else
                            <span class="px-3 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">
                                Inactive
                            </span>
                        @endif
                    </td>

                    <td class="px-6 py-4 text-gray-500">
                        {{ $api->message ?? '-' }}
                    </td>

                    <td class="px-6 py-4 text-center flex justify-center gap-2">

                        <!-- Edit -->
                        <a href="{{ route('apis.edit',$api->id) }}" 
                           class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded-md text-xs shadow">
                            Edit
                        </a>

                        <!-- Delete -->
                        {{-- <form action="{{ route('apis.destroy',$api->id) }}" method="POST"
                              onsubmit="return confirm('Delete this API?')">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-xs shadow">
                                Delete
                            </button>
                        </form> --}}

                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-6 text-gray-400">
                        No APIs Found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection