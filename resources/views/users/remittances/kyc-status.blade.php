@extends('users.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">

  {{-- Flash alerts --}}
  @if (session('success'))
    <div class="mb-4 rounded-lg border border-green-300 bg-green-50 text-green-800 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif
  @if (session('error'))
    <div class="mb-4 rounded-lg border border-red-300 bg-red-50 text-red-800 px-4 py-3">
      {{ session('error') }}
    </div>
  @endif

  {{-- Status Card --}}
  <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-200 text-center">
    @php
      $isKyc = (int)($remittance->isKyc ?? 0) === 1;
      $status = $isKyc ? 'Verified' : 'Pending Review';
      $badge = $isKyc ? 'bg-green-100 text-green-700 border-green-300' : 'bg-yellow-100 text-yellow-700 border-yellow-300';
      $icon  = $isKyc
        ? 'https://cdn-icons-png.flaticon.com/512/5610/5610944.png'
        : 'https://cdn-icons-png.flaticon.com/512/3500/3500833.png';
      $subtitle = $isKyc
        ? 'Your KYC has been verified successfully.'
        : 'Your KYC was submitted and is under review. You will be notified once verification completes.';
    @endphp

    <img src="{{ $icon }}" alt="status" class="w-24 h-24 mx-auto mb-4">
    <h1 class="text-2xl font-extrabold text-gray-800">KYC {{ $status }}</h1>
    <p class="text-gray-600 mt-2">{{ $subtitle }}</p>

    <span class="inline-block mt-4 px-3 py-1 rounded-full text-sm border {{ $badge }}">
      Status: {{ $status }}
    </span>

    {{-- Next steps --}}
    <div class="mt-6 text-left">
      <h3 class="text-lg font-semibold text-gray-800">What happens next?</h3>
      <ul class="list-disc pl-5 mt-2 text-gray-700 space-y-1">
        <li>If verified, your payout limits are activated and you can start transactions.</li>
        <li>If pending, our team will verify documents typically within 24–48 hours.</li>
        <li>You’ll receive notifications on your registered email and phone.</li>
      </ul>
    </div>

    {{-- Quick summary --}}
    <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-4 text-left">
      <div class="p-4 rounded-lg bg-gray-50 border">
        <p class="text-gray-500 text-sm">Full Name</p>
        <p class="text-gray-800 font-medium mt-1">{{ $remittance->name ?? '-' }}</p>
      </div>
      <div class="p-4 rounded-lg bg-gray-50 border">
        <p class="text-gray-500 text-sm">PAN</p>
        <p class="text-gray-800 font-medium mt-1">{{ $remittance->panno ?? '-' }}</p>
      </div>
      <div class="p-4 rounded-lg bg-gray-50 border">
        <p class="text-gray-500 text-sm">Aadhaar</p>
        <p class="text-gray-800 font-medium mt-1">{{ $remittance->aadhar_no ?? '-' }}</p>
      </div>
      <div class="p-4 rounded-lg bg-gray-50 border">
        <p class="text-gray-500 text-sm">City</p>
        <p class="text-gray-800 font-medium mt-1">{{ $remittance->city ?? '-' }}</p>
      </div>
      <div class="p-4 rounded-lg bg-gray-50 border">
        <p class="text-gray-500 text-sm">Recipient IFSC</p>
        <p class="text-gray-800 font-medium mt-1">{{ $remittance->recipient_ifsc ?? '-' }}</p>
      </div>
      <div class="p-4 rounded-lg bg-gray-50 border">
        <p class="text-gray-500 text-sm">Recipient Account</p>
        <p class="text-gray-800 font-medium mt-1 break-all">{{ $remittance->recipient_account ?? '-' }}</p>
      </div>
    </div>

    {{-- Actions --}}
    <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
      {{-- <a href="{{ route('remittances.show', $remittance->id) }}"
         class="inline-flex items-center justify-center h-11 px-6 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
        View KYC Details
      </a> --}}
      {{-- <a href="{{ route('remittances.kyc.edit') }}"
         class="inline-flex items-center justify-center h-11 px-6 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300">
        Edit/Resubmit KYC
      </a> --}}
      {{-- @if($isKyc)
      <a href="{{ route('dashboard') }}"
         class="inline-flex items-center justify-center h-11 px-6 rounded-lg bg-green-600 text-white hover:bg-green-700">
        Go to Dashboard
      </a>
      @endif --}}
    </div>
  </div>

  {{-- Help --}}
  {{-- <div class="text-center text-gray-500 text-sm mt-6">
    Need help? <a href="{{ route('support') }}" class="text-blue-600 hover:underline">Contact Support</a>
  </div> --}}
</div>
@endsection
