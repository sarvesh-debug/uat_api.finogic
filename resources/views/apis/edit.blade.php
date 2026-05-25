@extends('layouts.app')

@section('content')
<div class="p-6 max-w-3xl mx-auto">

    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Edit API {{ $api->name }}</h2>
        <p class="text-gray-500 text-sm">Update API details below</p>
    </div>

    <!-- Card -->
    <div class="bg-white shadow rounded-xl p-6">

        <form action="{{ route('apis.update',$api->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div> 
                
                <input type="text" name="name" hidden value="{{ $api->name }}"
                  >
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Status
                </label>
                <select name="status"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="1" {{ $api->status ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$api->status ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <!-- Message -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Message
                </label>
                <textarea name="message" rows="4"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    placeholder="Enter message...">{{ $api->message }}</textarea>
            </div>

            <!-- Buttons -->
            <div class="flex justify-between items-center pt-4">

                <a href="{{ route('apis.index') }}"
                   class="text-gray-600 hover:text-gray-800 text-sm">
                    ← Back
                </a>

                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow">
                    Update API
                </button>

            </div>

        </form>

    </div>

</div>
@endsection