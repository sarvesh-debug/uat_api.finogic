@php
    $superAdmin = \App\Models\SuperAdmin::find(session('superadmin_id'));
@endphp

@extends('superadmin.layouts.app')

@section('content')
    <h1 class="text-2xl font-bold text-gray-800 mb-6">
        Welcome, {{ $superAdmin->name ?? 'Super Admin' }} 🎉
    </h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow">
            <h2 class="text-lg font-semibold text-gray-700">Users</h2>
            <p class="text-2xl font-bold mt-2">150</p>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow">
            <h2 class="text-lg font-semibold text-gray-700">Transactions</h2>
            <p class="text-2xl font-bold mt-2">₹ 1,20,000</p>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow">
            <h2 class="text-lg font-semibold text-gray-700">KYC Pending</h2>
            <p class="text-2xl font-bold mt-2">32</p>
        </div>
    </div>
@endsection
