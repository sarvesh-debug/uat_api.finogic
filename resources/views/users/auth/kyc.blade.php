@extends('users.layouts.app')

@section('content')
<main class="pl-3">
    <div class="w-full bg-white p-3 lg:p-8 rounded-xl shadow-lg">
        <h1 class="text-3xl font-extrabold text-center mb-10 text-gray-800">
            Activate Your Account
        </h1>
@if(session('success'))
<div id="kycSuccessModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md text-center animate-fadeIn">
        <div class="text-green-600 text-5xl mb-4">✔</div>
        <h2 class="text-xl font-bold mb-2">KYC Submitted Successfully!</h2>
        <p class="text-gray-600 mb-6">
            Your KYC has been submitted.  
            Our team will review it and get back to you shortly.
        </p>
        <button onclick="closeKycModal()"
            class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700">
            Okay
        </button>
    </div>
</div>
@endif
        {{-- Alerts --}}
        @if (session('success'))
            <div class="mb-4 rounded-lg border border-green-300 bg-green-50 text-green-800 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

      @if ($errors->any())
            <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
                <ul class="text-sm text-red-600 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="activationForm" method="POST" action="{{ route('remittances.kyc.store') }}" enctype="multipart/form-data" novalidate>
            @csrf

           {{-- ================= STEP 1 ================= --}}
<div class="step" id="step-1">
    <h2 class="text-lg font-semibold mb-6">Contact & PAN Details</h2>

    @php
        $user = auth('remittance')->user();
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        {{-- Name --}}
        <div>
            <label class="block text-sm font-medium mb-1">Full Name</label>
            <input type="text"
                name="name"
                value="{{ old('name', $user->name ?? '') }}"
                class="w-full border border-gray-300 focus:border-red-500 focus:ring-2 focus:ring-red-200 px-4 py-2 rounded-lg transition cursor-not-allowed" readonly
                pattern="[a-zA-Z\s]+"
                required>
        </div>

        {{-- Email --}}
        <div>
            <label class="block text-sm font-medium mb-1">Email Address</label>
            <input type="email"
                name="email"
                value="{{ old('email', $user->email ?? '') }}"
                class="w-full border border-gray-300 focus:border-red-500 focus:ring-2 focus:ring-red-200 px-4 py-2 rounded-lg transition cursor-not-allowed" readonly
                required>
        </div>

        {{-- Phone --}}
        <div>
            <label class="block text-sm font-medium mb-1">Mobile Number</label>
            <input type="tel"
                name="phone"
                value="{{ old('phone', $user->phone ?? '') }}"
                class="w-full border border-gray-300 focus:border-red-500 focus:ring-2 focus:ring-red-200 px-4 py-2 rounded-lg transition cursor-not-allowed" readonly
                pattern="^[6-9]\d{9}$"
                required>
        </div>

    </div>

    {{-- PAN --}}
    <div class="bg-gray-50 p-4 rounded border mb-6">
        <label class="block font-medium mb-2">PAN Number</label>
        <input type="text" name="panno"
            class="w-full border px-4 py-2 rounded required"
            pattern="^[A-Z]{5}[0-9]{4}[A-Z]{1}$"
            oninput="this.value=this.value.toUpperCase()"
            placeholder="ABCDE1234F" required>

        <p class="text-xs text-gray-500 mt-1">
            Format: 5 letters, 4 numbers, 1 letter
        </p>
    </div>

    {{-- EXTRA FIELDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

        <div class="md:col-span-2">
            <label class="block text-sm font-semibold mb-1">
                Full Name (as per PAN)
            </label>
            <input type="text" name="fullname"
                class="w-full border px-4 py-2 rounded required"
                required>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">
                Aadhaar Number
            </label>
            <input type="text" name="aadhar_no"
                class="w-full border px-4 py-2 rounded required"
                pattern="^[0-9]\d{11}$"
                placeholder="12 digit Aadhaar" required>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">
                Pincode
            </label>
            <input type="text" name="pincode"
                class="w-full border px-4 py-2 rounded required"
                pattern="^\d{6}$" required>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">
                City
            </label>
            <input type="text" name="city"
                class="w-full border px-4 py-2 rounded required"
                pattern="[a-zA-Z\s]+" required>
        </div>
        <div>
    <label class="block text-sm font-semibold mb-1">
        Website
    </label>

            <input type="url" 
                name="websitelink"
                placeholder="https://example.com"
                class="w-full border px-4 py-2 rounded"
                required>
        </div>

    </div>

    <div class="text-center">
        <button type="button"
            onclick="nextStep(2)"
            class="bg-red-600 text-white px-8 py-2 rounded hover:bg-red-700">
            Save & Continue
        </button>
    </div>
</div>

            {{-- ================= STEP 2 ================= --}}
            <div class="step hidden" id="step-2">
<h2 class="text-lg font-semibold mb-6">Business Details</h2>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

<input type="text" name="brandname"
placeholder="Brand Name"
class="w-full border px-4 py-2 rounded required"
required>

<select name="businesstype" id="businesstype"
class="w-full border px-4 py-2 rounded required" required>

<option value="">Select Business Type</option>
<option value="private">Private Limited</option>
<option value="proprietorship">Proprietorship</option>
<option value="partnership">Partnership</option>
<option value="llp">LLP</option>
<option value="individual">Individual</option>

</select>

<select name="businesscategory"
class="w-full border px-4 py-2 rounded required" required>

<option value="">Select Category</option>
<option>Finance</option>
<option>Education</option>
<option>Retail</option>
<option>Other</option>

</select>

</div>

<!-- Dynamic Document Fields -->

<div id="business-docs">

<!-- Private Limited -->
<div class="business-doc hidden" id="docs-private">

<h3 class="font-semibold mb-2">Private Limited Documents</h3>

<input type="text" name="gst_no"
placeholder="GST Number"
class="w-full border px-4 py-2 rounded mb-3">

<input type="file" name="gst_doc"
class="w-full border p-2 rounded mb-3">

<input type="text" name="company_pan"
placeholder="Company PAN Number"
class="w-full border px-4 py-2 rounded mb-3">

<input type="file" name="company_pan_doc"
class="w-full border p-2 rounded mb-3">

<label>COI</label>
<input type="file" name="coi_doc" class="w-full border p-2 rounded mb-3">

<label>COA</label>
<input type="file" name="coa_doc" class="w-full border p-2 rounded mb-3">

<label>MOA</label>
<input type="file" name="moa_doc" class="w-full border p-2 rounded mb-3">

</div>


<!-- Proprietorship -->
<div class="business-doc hidden" id="docs-proprietorship">

<h3 class="font-semibold mb-2">Proprietorship Documents</h3>

<input type="text" name="gst_prop"
placeholder="GST Number"
class="w-full border px-4 py-2 rounded mb-3">

<input type="file" name="gst_doc_prop"
class="w-full border p-2 rounded mb-3">

</div>


<!-- Partnership -->
<div class="business-doc hidden" id="docs-partnership">

<h3 class="font-semibold mb-2">Partnership Documents</h3>

<input type="text" name="pan_partner"
placeholder="PAN Card Number"
class="w-full border px-4 py-2 rounded mb-3">

<input type="file" name="pan_doc_partner"
class="w-full border p-2 rounded mb-3">

<label>Partnership Agreement</label>
<input type="file" name="partnership_doc"
class="w-full border p-2 rounded mb-3">

</div>


<!-- LLP -->
<div class="business-doc hidden" id="docs-llp">

<h3 class="font-semibold mb-2">LLP Documents</h3>

<input type="text" name="gst_llp"
placeholder="GST Number"
class="w-full border px-4 py-2 rounded mb-3">

<input type="file" name="gst_doc_llp"
class="w-full border p-2 rounded mb-3">

<input type="text" name="pan_llp"
placeholder="Company PAN Number"
class="w-full border px-4 py-2 rounded mb-3">

<input type="file" name="pan_doc_llp"
class="w-full border p-2 rounded mb-3">

<label>COI</label>
<input type="file" name="coi_llp" class="w-full border p-2 rounded mb-3">

<label>COA</label>
<input type="file" name="coa_llp" class="w-full border p-2 rounded mb-3">

<label>MOA</label>
<input type="file" name="moa_llp" class="w-full border p-2 rounded mb-3">

</div>


<!-- Individual -->
<div class="business-doc hidden" id="docs-individual">

<h3 class="font-semibold mb-2">Individual Documents</h3>

<input type="text" name="udyam_no"
placeholder="Udyam Number"
class="w-full border px-4 py-2 rounded mb-3">

<input type="file" name="udyam_doc"
class="w-full border p-2 rounded mb-3">

</div>

</div>
<div class="flex justify-between mt-6">
</div> <!-- business-docs -->

<div class="flex justify-between mt-6">
<button type="button" onclick="prevStep(1)" class="bg-gray-300 px-6 py-2 rounded">
Back
</button>

<button type="button" onclick="nextStep(3)" class="bg-red-600 text-white px-8 py-2 rounded">
Save & Continue
</button>
</div>

</div> <!-- STEP 2 END -->
            {{-- ================= STEP 3 ================= --}}
            {{-- ================= STEP 3 ================= --}}
<div class="step hidden" id="step-3">
    <h2 class="text-lg font-semibold mb-6">Office & Bank Details</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

        <input type="text" name="officeaddress"
            placeholder="Office Address"
            class="w-full border px-4 py-2 rounded required"
            required>

        <input type="text" name="pin"
            placeholder="Pincode"
            class="w-full border px-4 py-2 rounded required"
            pattern="^\d{6}$" required>

        <input type="text" name="accountno"
            placeholder="Account Number"
            class="w-full border px-4 py-2 rounded required"
            pattern="^\d{9,18}$" required>

        <input type="text" name="ifsccode"
            placeholder="IFSC Code"
            class="w-full border px-4 py-2 rounded required"
            pattern="^[A-Z]{4}0[A-Z0-9]{6}$"
            oninput="this.value=this.value.toUpperCase()" required>

    </div>

    {{-- Optional Bank Documents --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

        <div>
            <label class="block text-sm font-semibold mb-1">
                Cancelled Cheque
            </label>
            <input type="file"
                name="cancelled_cheque"
                id="cancelled_cheque"
                class="w-full border p-3 rounded">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">
                Bank Passbook
            </label>
            <input type="file"
                name="bank_passbook"
                id="bank_passbook"
                class="w-full border p-3 rounded">
        </div>

        <p class="text-xs text-gray-500 col-span-2">
            Upload **any one document** (Cancelled Cheque OR Bank Passbook)
        </p>

    </div>

    <div class="flex justify-between">
        <button type="button"
            onclick="prevStep(2)"
            class="bg-gray-300 px-6 py-2 rounded">
            Back
        </button>

        <button type="button"
            onclick="nextStep(4)"
            class="bg-red-600 text-white px-8 py-2 rounded">
            Continue
        </button>
    </div>
</div>

            {{-- ================= STEP 4 ================= --}}
            <div class="step hidden" id="step-4">
                <h2 class="text-lg font-semibold mb-6">Upload Documents</h2>
                
                 <label class="block text-sm font-semibold mb-1">
                    PAN 
                </label>
                <input type="file" name="panupload"
                    class="w-full border p-3 rounded mb-4" required>
                 <label class="block text-sm font-semibold mb-1">
                    Aadhar
                </label>
                <input type="file" name="aadharupload"
                    class="w-full border p-3 rounded mb-4" required>
                    <label class="block text-sm font-semibold mb-1">
    Live Photo (Capture)
</label>

            <div class="mb-4">
                <video id="camera" width="100%" height="200" autoplay class="border rounded"></video>
                <canvas id="canvas" style="display:none;"></canvas>

                <button type="button" onclick="startCamera()" 
                    class="bg-blue-600 text-white px-4 py-2 mt-2 rounded">
                    Open Camera
                </button>

                <button type="button" onclick="capturePhoto()" 
                    class="bg-green-600 text-white px-4 py-2 mt-2 rounded">
                    Capture Photo
                </button>

                <input type="hidden" name="live_photo" id="live_photo">
            </div>
                <div class="flex justify-between">
                    <button type="button"
                        onclick="prevStep(3)"
                        class="bg-gray-300 px-6 py-2 rounded">
                        Back
                    </button>

                    <button type="submit"
                        class="bg-red-600 text-white px-8 py-2 rounded hover:bg-red-700">
                        Finish & Activate
                    </button>
                </div>
            </div>
        </form>
    </div>
</main>

<script>

let currentStep = 1;

function showStep(step){
    document.querySelectorAll('.step').forEach(el=>{
        el.classList.add('hidden');
    });

    document.getElementById('step-'+step).classList.remove('hidden');
    currentStep = step;
}

function nextStep(step){
    if(validateStep(currentStep)){
        showStep(step);
    }
}

function prevStep(step){
    showStep(step);
}

function validateStep(step){

    let valid = true;

    document.querySelectorAll('#step-'+step+' [required]').forEach(el=>{

        if(!el.checkValidity()){
            el.classList.add('border-red-500');
            valid = false;
        }else{
            el.classList.remove('border-red-500');
        }

    });

    // STEP 3 validation (Cheque OR Passbook)
    if(step == 3){

        let cheque = document.getElementById('cancelled_cheque').files.length;
        let passbook = document.getElementById('bank_passbook').files.length;

        if(cheque === 0 && passbook === 0){

            alert("Please upload Cancelled Cheque OR Bank Passbook");

            valid = false;

        }

    }

    return valid;

}

function closeKycModal(){
    document.getElementById('kycSuccessModal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function(){

    showStep(1);

    // Business type document toggle
    const businessType = document.getElementById('businesstype');

    if(businessType){

        businessType.addEventListener('change', function(){

            const type = this.value;

            document.querySelectorAll('.business-doc').forEach(el=>{
                el.classList.add('hidden');
            });

            if(type){

                const target = document.getElementById('docs-'+type);

                if(target){
                    target.classList.remove('hidden');
                }

            }

        });

    }

});

let stream;

function startCamera() {
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(function(s) {
            stream = s;
            document.getElementById('camera').srcObject = stream;
        })
        .catch(function(err) {
            alert("Camera access denied!");
        });
}

function capturePhoto() {
    let video = document.getElementById('camera');
    let canvas = document.getElementById('canvas');

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    let ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0);

    let imageData = canvas.toDataURL('image/png');

    document.getElementById('live_photo').value = imageData;

    alert("Photo Captured Successfully!");
}

</script>
@endsection