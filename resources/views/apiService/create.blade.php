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

   <form action="{{ route('apis.store') }}" method="POST">
    @csrf

    <input type="text" name="service" placeholder="Service" class="border p-2 w-full mb-2">

    <input type="text" name="pipe" placeholder="Pipe" class="border p-2 w-full mb-2">

    <select name="status" class="border p-2 w-full mb-2">
        <option value="1">Active</option>
        <option value="0">Inactive</option>
    </select>

    <textarea name="description" placeholder="Description" class="border p-2 w-full mb-2"></textarea>

    <button class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
</form>
@endsection