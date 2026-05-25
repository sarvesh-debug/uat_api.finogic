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

   <form action="{{ route('apis.update', $api->id) }}" method="POST">
    @csrf
    @method('PUT')

    <input type="text" name="service" value="{{ $api->service }}" class="border p-2 w-full mb-2">

    <input type="text" name="pipe" value="{{ $api->pipe }}" class="border p-2 w-full mb-2">

    <select name="status" class="border p-2 w-full mb-2">
        <option value="1" {{ $api->status ? 'selected' : '' }}>Active</option>
        <option value="0" {{ !$api->status ? 'selected' : '' }}>Inactive</option>
    </select>

    <textarea name="description" class="border p-2 w-full mb-2">{{ $api->description }}</textarea>

    <button class="bg-green-600 text-white px-4 py-2 rounded">Update</button>
</form>
@endsection