@extends('superadmin.layouts.app')


@section('content')
<div class="min-h-screen bg-gray-100 flex items-center justify-center py-10 px-4">
    <div class="max-w-md w-full">
        <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-200">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800">👤 User Details</h2>
                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-lg text-sm font-semibold">
                    ID: {{ $user->id }}
                </span>
            </div>

            <!-- Body -->
            <div class="space-y-3">
                <p><span class="font-semibold text-gray-700">Name:</span> {{ $user->name }}</p>
                <p><span class="font-semibold text-gray-700">Email:</span> {{ $user->email }}</p>
                <p><span class="font-semibold text-gray-700">Balance:</span> 
                    <span class="text-green-600 font-bold">₹{{ number_format($user->balance, 2) }}</span>
                </p>
                {{-- <p><span class="font-semibold text-gray-700">Created At:</span> 
                    {{ \Carbon\Carbon::parse($user->created_at)->format('d M Y h:i A') }}
                </p> --}}
            </div>

            <!-- Footer -->
            <div class="mt-6 flex justify-between">
                <a href="{{ route('superadmin.dashboard') }}" 
                   class="px-4 py-2 bg-gray-500 text-white rounded-lg shadow hover:bg-gray-600 transition">
                    ⬅️ Back
                </a>
               
            </div>
        </div>
    </div>
</div>
@endsection
