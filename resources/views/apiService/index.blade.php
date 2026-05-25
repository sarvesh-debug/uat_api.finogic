@extends('layouts.app')

@section('content')
<div class="p-6">

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">API Switching Management</h2>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- FORM START -->
    <form action="{{ route('apis.updateStatus') }}" method="POST">
        @csrf

        <div class="bg-white shadow rounded-xl p-4 mb-6">
            <h3 class="text-lg font-semibold mb-4">Service Pipes</h3>

            <div class="grid md:grid-cols-3 gap-4">
                @foreach($groupedServices as $service => $items)
                    <div>
                        <label class="text-sm font-medium">{{ $service }}</label>

                        <select name="pipes[{{ $service }}]" 
                                class="w-full border p-2 rounded mt-1">
                            
                            <option value="">Select Pipe</option>

                            @foreach($items->unique('pipe') as $item)
                                <option value="{{ $item->pipe }}"
                                    {{ $item->status ? 'selected' : '' }}>
                                    {{ $item->pipe }}
                                </option>
                            @endforeach

                        </select>
                    </div>
                @endforeach
            </div>

            <!-- SUBMIT BUTTON -->
            <div class="mt-4 text-right">
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Save Changes
                </button>
            </div>
        </div>
    </form>
    <!-- FORM END -->

</div>
@endsection