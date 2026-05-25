@extends('layouts.app')

@section('content')
<div class="container mx-auto mt-10 px-4">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Create Package</h2>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('packages.store') }}" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        @csrf

        <div class="mb-4">
            <label for="packageName" class="block text-gray-700 text-sm font-bold mb-2">Package Name</label>
            <input type="text" name="packageName" id="packageName" value="{{ old('packageName') }}"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>

        <div class="mb-6 flex items-center">
            <input type="checkbox" name="status" id="status" value="1" checked
                class="mr-2 leading-tight">
            <label for="status" class="text-gray-700 text-sm font-medium">
                Active
            </label>
        </div>

        <div class="flex items-center space-x-4">
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Create Package
            </button>
            <a href="{{ route('packages.index') }}"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
