@extends('users.layouts.app')

@section('content')

@php
    use Illuminate\Support\Str;

    $mask = function($v, $prefix=2, $suffix=2) {
        if (empty($v)) return '-';
        $len = Str::length($v);
        if ($len <= ($prefix + $suffix)) return $v;
        return Str::substr($v,0,$prefix) . str_repeat('•', $len-$prefix-$suffix) . Str::substr($v,-$suffix);
    };

    $isKyc = (int)($user->isKyc ?? 0) === 1;
    $isActive = ($user->status ?? 'pending') === 'success';

    $apiKey = $user->apikey ?? 'Not Generated';
    $maskedKey = $apiKey !== 'Not Generated'
        ? substr($apiKey, 0, 6) . '••••••••' . substr($apiKey, -4)
        : 'Not Generated';
@endphp

<div class="mx-auto w-full max-w-[1200px] px-4 text-[13px]">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-10">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Account Profile</h2>
            <p class="text-gray-500 text-sm mt-1">
                Complete overview of your account, KYC & API integration access
            </p>
        </div>

        <div class="mt-3 md:mt-0 text-sm text-gray-500 bg-gray-100 px-4 py-2 rounded-lg">
            {{ \Carbon\Carbon::now()->format('l, d F Y - h:i A') }}
        </div>
    </div>


    <!-- STATUS CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

        <div class="bg-white shadow-lg rounded-xl p-6 border">
            <p class="text-sm text-gray-500">KYC Status</p>
            @if($isKyc)
                <span class="mt-3 inline-block px-4 py-2 bg-green-100 text-green-700 rounded-lg font-semibold">
                    Verified
                </span>
            @else
                <span class="mt-3 inline-block px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg font-semibold">
                    Pending Verification
                </span>
            @endif
        </div>

        <div class="bg-white shadow-lg rounded-xl p-6 border">
            <p class="text-sm text-gray-500">Account Status</p>
            @if($isActive)
                <span class="mt-3 inline-block px-4 py-2 bg-green-100 text-green-700 rounded-lg font-semibold">
                    Active
                </span>
            @else
                <span class="mt-3 inline-block px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg font-semibold">
                    Under Review
                </span>
            @endif
        </div>

        <div class="bg-white shadow-lg rounded-xl p-6 border">
            <p class="text-sm text-gray-500">Wallet Balance</p>
            <p class="text-3xl font-bold text-gray-800 mt-3">
                ₹ {{ number_format($user->amount ?? 0, 2) }}
            </p>
        </div>

    </div>


    <!-- API KEY SECTION -->
    <div class="bg-white shadow-xl rounded-2xl p-8 border mb-10">

        <div class="flex justify-between items-center flex-wrap">
            <div>
                <h4 class="text-xl font-semibold text-gray-800">
                    API Key Access
                </h4>
                <p class="text-sm text-gray-500 mt-1">
                    Securely use this key for API integrations
                </p>
            </div>

            <span class="px-4 py-1 text-xs font-semibold rounded-full
                {{ $apiKey !== 'Not Generated' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                {{ $apiKey !== 'Not Generated' ? 'Active' : 'Not Generated' }}
            </span>
        </div>

        <div class="mt-6 bg-gray-50 border rounded-lg p-4 flex flex-col md:flex-row md:items-center md:justify-between">

            <code id="apiKeyText" class="font-mono text-gray-800 break-all text-sm">
                {{ $maskedKey }}
            </code>

            <div class="flex gap-3 mt-4 md:mt-0">
                <button onclick="toggleKey()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                    Show
                </button>

                <button onclick="copyKey(this)"
                    class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-black transition text-sm">
                    Copy
                </button>
            </div>
        </div>

        <p class="text-xs text-gray-400 mt-3">
            ⚠ Keep your API key confidential. Do not share it publicly.
        </p>
    </div>


    <!-- PROFILE MAIN SECTION -->
    <div class="bg-white rounded-2xl shadow-xl border p-10 space-y-12">

        <!-- BASIC DETAILS -->
        <div>
            <h4 class="text-xl font-semibold text-gray-700 border-b pb-4 mb-6">
                Basic Details
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">

                <div>
                    <p class="text-gray-500">Full Name</p>
                    <p class="font-semibold text-gray-800">{{ $user->name ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-gray-500">PAN Number</p>
                    <p class="font-semibold text-gray-800">{{ $user->panno ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-gray-500">Aadhaar Number</p>
                    <p class="font-semibold text-gray-800">
                        {{ $mask($user->aadhar_no,4,4) }}
                    </p>
                </div>

                <div>
                    <p class="text-gray-500">City</p>
                    <p class="font-semibold text-gray-800">{{ $user->city ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-gray-500">Pincode</p>
                    <p class="font-semibold text-gray-800">{{ $user->pincode ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Merchant Id</p>
                    <p class="font-semibold text-gray-800">{{ $user->remId ?? '-' }}</p>
                </div>

            </div>
        </div>


        <!-- BUSINESS DETAILS -->
       <!-- BUSINESS DETAILS -->
<div>
<h4 class="text-xl font-semibold text-gray-700 border-b pb-4 mb-6">
Business Details
</h4>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">

<div>
<p class="text-gray-500">Brand Name</p>
<p class="font-semibold text-gray-800">{{ $user->brand_name ?? '-' }}</p>
</div>

<div>
<p class="text-gray-500">Business Type</p>
<p class="font-semibold text-gray-800">{{ ucfirst($user->businesstype ?? '-') }}</p>
</div>

<div>
<p class="text-gray-500">Business Category</p>
<p class="font-semibold text-gray-800">{{ $user->businesscategory ?? '-' }}</p>
</div>

<div>
<p class="text-gray-500">Company PAN</p>
<p class="font-semibold text-gray-800">{{ $user->businesspan ?? '-' }}</p>
</div>

<div>
<p class="text-gray-500">GST Number</p>
<p class="font-semibold text-gray-800">{{ $user->gst_no ?? '-' }}</p>
</div>

<div>
<p class="text-gray-500">Udyam Number</p>
<p class="font-semibold text-gray-800">{{ $user->udyam_no ?? '-' }}</p>
</div>

<div>
<p class="text-gray-500">Website / App</p>

@if(!empty($user->websitelink))
<a href="{{ $user->websitelink }}"
target="_blank"
class="text-blue-600 hover:underline break-all">

{{ $user->websitelink }}

</a>
@else
-
@endif

</div>

</div>
</div>
<!-- OFFICE DETAILS -->
<div>
<h4 class="text-xl font-semibold text-gray-700 border-b pb-4 mb-6">
Office Details
</h4>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">

<div>
<p class="text-gray-500">Office Address</p>
<p class="font-semibold text-gray-800">{{ $user->office_address ?? '-' }}</p>
</div>

<div>
<p class="text-gray-500">Office Pincode</p>
<p class="font-semibold text-gray-800">{{ $user->office_pin ?? '-' }}</p>
</div>

</div>
</div>


        <!-- BANK DETAILS -->
        <div>
            <h4 class="text-xl font-semibold text-gray-700 border-b pb-4 mb-6">
                Bank Details
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">

                <div>
                    <p class="text-gray-500">Recipient Name</p>
                    <p class="font-semibold text-gray-800">{{ $user->recipient_name ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-gray-500">Account Number</p>
                    <p class="font-mono text-gray-800">
                        {{ $mask($user->recipient_account,2,3) }}
                    </p>
                </div>

                <div>
                    <p class="text-gray-500">IFSC Code</p>
                    <p class="font-semibold text-gray-800">{{ $user->recipient_ifsc ?? '-' }}</p>
                </div>

            </div>
        </div>


        <!-- DOCUMENT PREVIEW SECTION -->
      <!-- DOCUMENT PREVIEW SECTION -->
<div>

<h4 class="text-xl font-semibold text-gray-700 border-b pb-4 mb-6">
Uploaded Documents
</h4>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

@php

$docs = [

['PAN Card',$user->pan_doc_url],

['Aadhaar Card',$user->aadhaar_doc_url],

['Cancelled Cheque',$user->cancelled_cheque],

['Bank Passbook',$user->bank_passbook],

['GST Certificate',$user->gst_doc],

['Company PAN',$user->company_pan_doc],

['Certificate of Incorporation',$user->coi_doc],

['MOA',$user->moa_doc],

['AOA',$user->coa_doc],

['Partnership Deed',$user->partnership_doc],

['Udyam Certificate',$user->udyam_doc],

];

@endphp

@foreach($docs as $doc)

<div class="border rounded-xl p-4 text-center">

<p class="text-sm text-gray-500 mb-3">{{ $doc[0] }}</p>

@if(!empty($doc[1]))

<a href="{{ $doc[1] }}" target="_blank">

<img src="{{ $doc[1] }}"
class="h-40 mx-auto object-contain rounded-lg shadow cursor-pointer">

</a>

<p class="text-xs text-blue-600 mt-2">Click to view</p>

@else

<p class="text-gray-400 text-sm">Not Uploaded</p>

@endif

</div>

@endforeach

</div>

</div>
    </div>

</div>


<script>
    let realKey = "{{ $apiKey }}";
    let maskedKey = "{{ $maskedKey }}";
    let visible = false;

    function toggleKey() {
        let el = document.getElementById("apiKeyText");
        if (visible) {
            el.innerText = maskedKey;
            visible = false;
        } else {
            el.innerText = realKey;
            visible = true;
        }
    }

    function copyKey(btn) {
        navigator.clipboard.writeText(realKey).then(() => {
            let original = btn.innerText;
            btn.innerText = "Copied ✓";
            setTimeout(() => {
                btn.innerText = original;
            }, 1500);
        });
    }
</script>

@endsection