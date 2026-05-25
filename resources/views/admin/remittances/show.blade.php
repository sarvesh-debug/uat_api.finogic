@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
<div class="mx-auto w-full max-w-[1200px] px-4 text-[13px]">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">

        <div>
            <!-- Back Button --> <div class="mb-4"> <a href="{{ route('admin.remittances.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg shadow"> ← Back </a> </div>

            <h1 class="text-2xl font-bold text-gray-900">
                Merchant Profile
            </h1>

            <p class="text-sm text-gray-500">
                Complete remittance account and payout configuration
            </p>
        </div>

        <!-- Status Badge -->
        <div class="mt-4 md:mt-0">
            <span class="px-4 py-2 rounded-full text-sm font-semibold
                {{ $remittance->status == 'success' ? 'bg-green-100 text-green-700'
                : ($remittance->status == 'pending' ? 'bg-yellow-100 text-yellow-700'
                : 'bg-red-100 text-red-700') }}">
                {{ ucfirst($remittance->status) }}
            </span>
        </div>

    </div>


    <!-- Top Cards -->
    <div class="grid md:grid-cols-4 gap-4 mb-6">

        <div class="bg-white rounded-xl shadow-sm border p-4">
            <p class="text-gray-500 text-xs">Available Balance</p>
            <p class="text-xl font-bold text-gray-900">
                ₹ {{ number_format($remittance->amount ?? 0,2) }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-4">
            <p class="text-gray-500 text-xs">Locked Balance</p>
            <p class="text-xl font-bold text-red-600">
                ₹ {{ number_format($remittance->lockBalance ?? 0,2) }}
            </p>
        </div>

      <div class="bg-white rounded-xl shadow-sm border p-4">
            <p class="text-gray-500 text-xs mb-2">Generate eKyc</p>

            <a href="{{ route('merchnat.ekyc', ['id' => $remittance->id]) }}"
            class="inline-block bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-sm font-semibold px-5 py-2.5 rounded-lg shadow-md hover:from-blue-600 hover:to-indigo-700 transition duration-300">
                Proceed eKYC →
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-4">
            <p class="text-gray-500 text-xs">KYC Status</p>
            <p class="text-lg font-bold {{ $remittance->isKyc ? 'text-green-600' : 'text-red-600' }}">
                {{ $remittance->isKyc ? 'Verified' : 'Not Verified' }}
            </p>
        </div>

    </div>


    <div class="grid lg:grid-cols-3 gap-6">

        <!-- LEFT SECTION -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Profile Card -->
            <div class="bg-white rounded-xl shadow-sm border">

                <div class="border-b px-6 py-4">
                    <h2 class="font-semibold text-gray-800">
                        Account Information
                    </h2>
                </div>

                <div class="p-6 grid md:grid-cols-2 gap-6">

                    <div>
                        <label class="label">Full Name</label>
                        <p class="value">{{ $remittance->name }}</p>
                    </div>

                    <div>
                        <label class="label">Email</label>
                        <p class="value">{{ $remittance->email }}</p>
                    </div>

                    <div>
                        <label class="label">Phone</label>
                        <p class="value">{{ $remittance->phone }}</p>
                    </div>

                    <div>
                        <label class="label">Remittance ID</label>
                        <p class="value">{{ $remittance->remId }}</p>
                    </div>

                    <div>
                        <label class="label">City</label>
                        <p class="value">{{ $remittance->city }}</p>
                    </div>

                    <div>
                        <label class="label">Pincode</label>
                        <p class="value">{{ $remittance->pincode }}</p>
                    </div>

                    <div>
                        <label class="label">PAN Number</label>
                        <p class="value">{{ $remittance->panno }}</p>
                    </div>

                    <div>
                        <label class="label">Aadhar Number</label>
                        <p class="value">{{ $remittance->aadhar_no }}</p>
                    </div>

                </div>
            </div>


            <!-- Recipient Card -->
            <div class="bg-white rounded-xl shadow-sm border">

                <div class="border-b px-6 py-4">
                    <h2 class="font-semibold text-gray-800">
                        Recipient Details
                    </h2>
                </div>

                <div class="p-6 grid md:grid-cols-2 gap-6">

                    <div>
                        <label class="label">Recipient Name</label>
                        <p class="value">{{ $remittance->recipient_name }}</p>
                    </div>

                    <div>
                        <label class="label">Account Number</label>
                        <p class="value break-all">
                            {{ $remittance->recipient_account }}
                        </p>
                    </div>

                    <div>
                        <label class="label">IFSC Code</label>
                        <p class="value">{{ $remittance->recipient_ifsc }}</p>
                    </div>

                    <div>
                        <label class="label">Daily Limit</label>
                        <p class="value">
                            ₹ {{ number_format($remittance->perday_limit ?? 0) }}
                        </p>
                    </div>

                </div>
            </div>
<div class="bg-white rounded-xl shadow-sm border">

    <div class="border-b px-6 py-4">
        <h2 class="font-semibold text-gray-800">
            Business Details
        </h2>
    </div>

    <div class="p-6 grid md:grid-cols-2 gap-6">

        <div>
            <label class="label">Brand Name</label>
            <p class="value">{{ $remittance->brand_name }}</p>
        </div>

        <div>
            <label class="label">Business Type</label>
            <p class="value">{{ ucfirst($remittance->businesstype) }}</p>
        </div>

        <div>
            <label class="label">Business Category</label>
            <p class="value">{{ $remittance->businesscategory }}</p>
        </div>

        <div>
            <label class="label">GST / Business PAN</label>
            <p class="value">{{ $remittance->gst_pan ?? $remittance->businesspan }}</p>
        </div>

        <div>
            <label class="label">Udyam Number</label>
            <p class="value">{{ $remittance->udyam_no ?? 'N/A' }}</p>
        </div>

        <div>
            <label class="label">Website</label>

            @if($remittance->websitelink)
                <a href="{{ $remittance->websitelink }}" target="_blank"
                   class="text-indigo-600 hover:underline">
                   Visit Website
                </a>
            @else
                <p class="value">Not Provided</p>
            @endif

        </div>

    </div>

</div>

<div class="bg-white rounded-xl shadow-sm border">

    <div class="border-b px-6 py-4">
        <h2 class="font-semibold text-gray-800">
            Office Details
        </h2>
    </div>

    <div class="p-6 grid md:grid-cols-2 gap-6">

        <div>
            <label class="label">Office Address</label>
            <p class="value">{{ $remittance->office_address }}</p>
        </div>

        <div>
            <label class="label">Office Pincode</label>
            <p class="value">{{ $remittance->pin }}</p>
        </div>

    </div>

</div>

            <!-- Documents -->
           <div class="bg-white rounded-xl shadow-sm border">

<div class="border-b px-6 py-4">
<h2 class="font-semibold text-gray-800">
KYC Documents
</h2>
</div>

<div class="p-6 grid md:grid-cols-2 gap-6">

@php

$docs = [

['PAN Card', $remittance->pan_doc_url],

['Aadhaar Front', $remittance->aadhaar_doc_url],

['Aadhaar Back', $remittance->aadhaar_back_url],

['Cancelled Cheque', $remittance->cancelled_cheque],

['Bank Passbook', $remittance->bank_passbook],

['GST Certificate', $remittance->gst_doc_url],

['Company PAN', $remittance->company_pan_doc],

['COI', $remittance->coi_doc],

['MOA', $remittance->moa_doc],

['AOA', $remittance->coa_doc],

['Partnership Deed', $remittance->partnership_doc],

['Udyam Certificate', $remittance->udyam_doc]

];

@endphp

@foreach($docs as $doc)

<div>

<label class="label">{{ $doc[0] }}</label>

@if($doc[1])

<a href="{{ $doc[1] }}" target="_blank"
class="text-indigo-600 hover:underline">

View Document

</a>

@else

<p class="value text-gray-400">Not Uploaded</p>

@endif

</div>

@endforeach

</div>

</div>

        </div>


        <!-- RIGHT SIDEBAR -->
        <div class="space-y-6">

            <!-- API Key -->
            <div class="bg-white rounded-xl shadow-sm border p-6">

                <label class="label">API Key</label>

                <div class="flex gap-2 mt-2">
                    <input id="apiKeyBox"
                        value="{{ $remittance->apikey }}"
                        readonly
                        class="flex-1 border rounded-lg px-3 py-2 text-sm">

                   <button id="generateKeyBtn"
    data-id="{{ $remittance->remId }}"
    class="flex items-center justify-center gap-2 bg-indigo-600 text-white px-4 rounded-lg hover:bg-indigo-700 min-w-[110px]">

    <span id="keyText">Generate</span>

    <svg id="keyLoader" class="hidden animate-spin h-4 w-4"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24">

        <circle class="opacity-25"
            cx="12" cy="12" r="10"
            stroke="currentColor"
            stroke-width="4"></circle>

        <path class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8v8z"></path>

    </svg>

</button>
                </div>

            </div>

                    <div class="space-y-6">

        <!-- IP Address -->
<div class="bg-white rounded-xl shadow-sm border p-6">

<label class="label">IP Address</label>

<div class="flex gap-2 mt-2">

<input id="ipBox"
value="{{ $remittance->ipAddress }}"
placeholder="Enter allowed IP"
class="flex-1 border rounded-lg px-3 py-2 text-sm">

<button id="registerIpBtn"
data-id="{{ $remittance->remId }}"
class="flex items-center justify-center gap-2 bg-indigo-600 text-white px-4 rounded-lg hover:bg-indigo-700 min-w-[110px]">

<span id="ipText">Register IP</span>

<svg id="ipLoader" class="hidden animate-spin h-4 w-4"
xmlns="http://www.w3.org/2000/svg"
fill="none"
viewBox="0 0 24 24">

<circle class="opacity-25"
cx="12" cy="12" r="10"
stroke="currentColor"
stroke-width="4"></circle>

<path class="opacity-75"
fill="currentColor"
d="M4 12a8 8 0 018-8v8z"></path>

</svg>

</button>

</div>

</div>
<!-- Callback URL -->
<div class="bg-white rounded-xl shadow-sm border p-6">

<label class="label">Callback URL</label>

<div class="flex gap-2 mt-2">

<input id="callbackBox"
value="{{ $remittance->callback_url }}"
placeholder="Enter Callback URL"
class="flex-1 border rounded-lg px-3 py-2 text-sm">

<button id="saveCallbackBtn"
data-id="{{ $remittance->remId }}"
class="flex items-center justify-center gap-2 bg-indigo-600 text-white px-4 rounded-lg hover:bg-indigo-700 min-w-[120px]">

<span id="callbackText">Save URL</span>

<svg id="callbackLoader" class="hidden animate-spin h-4 w-4"
xmlns="http://www.w3.org/2000/svg"
fill="none"
viewBox="0 0 24 24">

<circle class="opacity-25"
cx="12" cy="12" r="10"
stroke="currentColor"
stroke-width="4"></circle>

<path class="opacity-75"
fill="currentColor"
d="M4 12a8 8 0 018-8v8z"></path>

</svg>

</button>

</div>

</div>
            <!-- Services -->
            <div class="bg-white rounded-xl shadow-sm border p-6">

                <h3 class="font-semibold mb-4">
                    Services Control
                </h3>

                <div class="space-y-3">

                    @foreach([
                    'payout1'=>'Payout 1',
                    'payout5'=>'Payout_P2',
                    'upipayout'=>'UPI',
                    'upipayout2'=>'UPI_P2',
                    'pgpayout'=>'Payment Gateway',
                    'pgpayout1'=>'Payment Gateway P1',
                       'pgpayout2'=>'Payment Gateway P2',
                    'isAEPS'=>'AEPS',
                    'isDMT'=>'DMT',
                    'isAcc'=>'Account Verify',
                    'isVPA'=>'UPI Verify',
                    'isBBPS' =>'BBPS',
                    'ccpay' =>'CC_Bill'
                    ] as $key=>$label)

                    <form action="{{ route('remittence.toggleService',$remittance->id) }}"
                          method="POST"
                          class="flex justify-between items-center">

                        @csrf
                        <input type="hidden" name="service" value="{{ $key }}">

                        <span class="text-sm font-medium">
                            {{ $label }}
                        </span>

                        <button type="submit"
                            class="px-3 py-1 rounded-full text-xs font-semibold
                            {{ $remittance->$key ? 'bg-green-100 text-green-700'
                            : 'bg-red-100 text-red-700' }}">
                            {{ $remittance->$key ? 'Active' : 'Inactive' }}
                        </button>

                    </form>

                    @endforeach

                </div>

            </div>


            <!-- Actions -->
            {{-- <div class="bg-white rounded-xl shadow-sm border p-6 space-y-3">

                <a href="{{ route('remittances.approve',$remittance->id) }}"
                   class="block w-full text-center bg-green-600 text-white py-2 rounded-lg hover:bg-green-700">
                   Approve Account
                </a>

                <a href="{{ route('remittances.reject',$remittance->id) }}"
                   class="block w-full text-center bg-red-600 text-white py-2 rounded-lg hover:bg-red-700">
                   Reject Account
                </a>

            </div> --}}

        </div>

    </div>

</div>
</div>


<style>

.label{
font-size:12px;
color:#6b7280;
display:block;
margin-bottom:3px;
}

.value{
font-size:14px;
font-weight:600;
color:#111827;
}

</style>
<script>
        // API KEY GENERATE
document.getElementById("generateKeyBtn").addEventListener("click", function(){

let btn=this;
let loader=document.getElementById("keyLoader");
let text=document.getElementById("keyText");

if(!confirm("Generate new API Key? Old key will stop working."))
return;

btn.disabled=true;
loader.classList.remove("hidden");
text.innerText="Generating...";

fetch(`/admin/remittence/${btn.dataset.id}/generate-key`,{

method:"POST",

headers:{
"X-CSRF-TOKEN":"{{ csrf_token() }}",
"Accept":"application/json"
}

})
.then(res=>res.json())
.then(data=>{

if(data.api_key){

document.getElementById("apiKeyBox").value=data.api_key;

showToast("API Key generated successfully","success");

}else{

showToast("Failed to generate API Key","error");

}

})
.catch(()=>showToast("Server error","error"))
.finally(()=>{

btn.disabled=false;
loader.classList.add("hidden");
text.innerText="Generate";

});

});


// APPROVE ACCOUNT
document.getElementById("approveBtn").addEventListener("click",function(){

let btn=this;
let loader=document.getElementById("approveLoader");
let text=document.getElementById("approveText");

if(!confirm("Approve this account?"))
return;

btn.disabled=true;
loader.classList.remove("hidden");
text.innerText="Approving...";

fetch(`/admin/remittences/${btn.dataset.id}/approve`,{

method:"GET",

headers:{
"X-CSRF-TOKEN":"{{ csrf_token() }}"
}

})
.then(()=>{

showToast("Account Approved","success");

setTimeout(()=>location.reload(),1000);

})
.catch(()=>showToast("Approval failed","error"));

});


// REJECT ACCOUNT
document.getElementById("rejectBtn").addEventListener("click",function(){

let btn=this;
let loader=document.getElementById("rejectLoader");
let text=document.getElementById("rejectText");

if(!confirm("Reject this account?"))
return;

btn.disabled=true;
loader.classList.remove("hidden");
text.innerText="Rejecting...";

fetch(`/admin/remittences/${btn.dataset.id}/reject`,{

method:"GET",

headers:{
"X-CSRF-TOKEN":"{{ csrf_token() }}"
}

})
.then(()=>{

showToast("Account Rejected","success");

setTimeout(()=>location.reload(),1000);

})
.catch(()=>showToast("Reject failed","error"));

});


// TOAST MESSAGE
function showToast(message,type="success"){

let bg= type=="success" ? "bg-green-600" : "bg-red-600";

let toast=document.createElement("div");

toast.className=`fixed top-5 right-5 ${bg} text-white px-5 py-3 rounded-lg shadow-lg z-50`;

toast.innerText=message;

document.body.appendChild(toast);

setTimeout(()=>toast.remove(),3000);

}
</script>
<script>

    document.getElementById("registerIpBtn").addEventListener("click", function(){

let btn=this;
let loader=document.getElementById("ipLoader");
let text=document.getElementById("ipText");
let ip=document.getElementById("ipBox").value;

if(!ip){
showToast("Please enter IP Address","error");
return;
}

btn.disabled=true;
loader.classList.remove("hidden");
text.innerText="Saving...";

fetch(`/admin/remittence/${btn.dataset.id}/register-ip`,{

method:"POST",

headers:{
"X-CSRF-TOKEN":"{{ csrf_token() }}",
"Accept":"application/json",
"Content-Type":"application/json"
},

body:JSON.stringify({
ipAddress:ip
})

})
.then(res=>res.json())
.then(data=>{
console.log(data);
if(data.status){

showToast("IP Address registered successfully","success");

}else{

showToast("Failed to register IP","error");

}

})
.catch(()=>showToast("Server error","error"))
.finally(()=>{

btn.disabled=false;
loader.classList.add("hidden");
text.innerText="Register IP";

});

});
</script>

<script>
    document.getElementById("saveCallbackBtn").addEventListener("click", function(){

let btn=this;
let loader=document.getElementById("callbackLoader");
let text=document.getElementById("callbackText");
let url=document.getElementById("callbackBox").value;

if(!url){
showToast("Please enter Callback URL","error");
return;
}

btn.disabled=true;
loader.classList.remove("hidden");
text.innerText="Saving...";

fetch(`/admin/remittence/${btn.dataset.id}/callback-url`,{

method:"POST",

headers:{
"X-CSRF-TOKEN":"{{ csrf_token() }}",
"Accept":"application/json",
"Content-Type":"application/json"
},

body:JSON.stringify({
callbackUrl:url
})

})
.then(res=>res.json())
.then(data=>{

if(data.status){

showToast("Callback URL saved successfully","success");

}else{

showToast("Failed to save Callback URL","error");

}

})
.catch(()=>showToast("Server error","error"))
.finally(()=>{

btn.disabled=false;
loader.classList.add("hidden");
text.innerText="Save URL";

});

});
</script>

@endsection