@extends('layouts.app')

@section('content')

<div class="container mx-auto px-4 py-6">


<!-- ============================= -->
<!-- TOP STATS -->
<!-- ============================= -->

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">

<!-- API Users -->
<div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-lg transition">

<div class="flex justify-between items-center mb-3">
<div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center">
<i class="fas fa-users text-indigo-600"></i>
</div>
</div>

<p class="text-xs text-gray-400 uppercase">API Users</p>

<h2 class="text-3xl font-bold mt-2">
{{ $apiUsersCount }}
</h2>

<div class="flex justify-between text-sm mt-3">

<span class="text-green-600 font-semibold">
Active : {{ $activeUsers }}
</span>

<span class="text-red-500 font-semibold">
Inactive : {{ $deactiveUsers }}
</span>

</div>

</div>



<!-- New Users -->
<div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-lg transition">

<div class="flex justify-between items-center mb-3">
<div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
<i class="fas fa-user-plus text-purple-600"></i>
</div>
</div>

<p class="text-xs text-gray-400 uppercase">New Users</p>

<h2 class="text-3xl font-bold text-purple-600 mt-2">
{{ $newUsers }}
</h2>

</div>



<!-- Fund Requests -->
<div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-lg transition">

<div class="flex justify-between items-center mb-3">
<div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center">
<i class="fas fa-credit-card text-red-600"></i>
</div>
</div>

<p class="text-xs text-gray-400 uppercase">Fund Requests</p>

<h2 class="text-3xl font-bold text-red-600 mt-2">
{{ $fundRequests }}
</h2>

</div>



<!-- KYC Pending -->
<div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-lg transition">

<div class="flex justify-between items-center mb-3">
<div class="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center">
<i class="fas fa-file-alt text-yellow-600"></i>
</div>
</div>

<p class="text-xs text-gray-400 uppercase">KYC Pending</p>

<h2 class="text-3xl font-bold text-yellow-600 mt-2">
{{ $kycPending }}
</h2>

</div>

</div>


<!-- ============================= -->
<!-- TRANSACTION VOLUME -->
<!-- ============================= -->

<div class="bg-white rounded-2xl shadow-md mb-8">

<div class="p-6 border-b flex justify-between items-center">

<div>

<h2 class="text-lg font-semibold flex items-center gap-2">
<i class="fas fa-chart-bar text-gray-600"></i>
Transaction Volume Overview
</h2>

<p class="text-sm text-gray-500">
Real-time performance breakdown
</p>

</div>

<!-- FILTER BUTTONS -->

<div class="flex gap-2 text-sm">

<a href="{{ route('admin.dashboard',['range'=>'previous']) }}"
class="px-3 py-1 rounded-lg border {{ $range=='previous' ? 'bg-gray-900 text-white' : 'bg-white' }}">
Previous Day
</a>

<a href="{{ route('admin.dashboard',['range'=>'today']) }}"
class="px-3 py-1 rounded-lg border {{ $range=='today' ? 'bg-gray-900 text-white' : 'bg-white' }}">
Today
</a>

<a href="{{ route('admin.dashboard',['range'=>'week']) }}"
class="px-3 py-1 rounded-lg border {{ $range=='week' ? 'bg-gray-900 text-white' : 'bg-white' }}">
This Week
</a>

<a href="{{ route('admin.dashboard',['range'=>'month']) }}"
class="px-3 py-1 rounded-lg border {{ $range=='month' ? 'bg-gray-900 text-white' : 'bg-white' }}">
This Month
</a>


<a href="{{ route('admin.dashboard',['range'=>'year']) }}"
class="px-3 py-1 rounded-lg border {{ $range=='year' ? 'bg-gray-900 text-white' : 'bg-white' }}">
This Year
</a>


</div>

</div>


<div class="p-6 grid md:grid-cols-3 gap-6">

<!-- Pending -->
<div class="rounded-xl border border-yellow-300 bg-yellow-50 p-6">

<p class="text-xs text-yellow-600 uppercase font-semibold mb-1">
Pending
</p>

<h3 class="text-3xl font-bold text-yellow-700">
{{ $txnPending }}
</h3>

<p class="text-sm text-yellow-600 mt-1">
₹ {{ number_format($pendingAmount,2) }}
</p>

</div>



<!-- Failed -->
<div class="rounded-xl border border-red-300 bg-red-50 p-6">

<p class="text-xs text-red-600 uppercase font-semibold mb-1">
Failed
</p>

<h3 class="text-3xl font-bold text-red-700">
{{ $txnFailed }}
</h3>

<p class="text-sm text-red-600 mt-1">
₹ {{ number_format($failedAmount,2) }}
</p>

</div>



<!-- Success -->
<div class="rounded-xl border border-green-300 bg-green-50 p-6">

<p class="text-xs text-green-600 uppercase font-semibold mb-1">
Success
</p>

<h3 class="text-3xl font-bold text-green-700">
{{ $txnSuccess }}
</h3>

<p class="text-sm text-green-600 mt-1">
₹ {{ number_format($successAmount,2) }}
</p>

</div>

</div>

</div>

<!-- ============================= -->
<!-- RECENT TRANSACTIONS -->
<!-- ============================= -->

@endsection