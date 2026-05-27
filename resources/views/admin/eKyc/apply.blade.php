@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
<div class="mx-auto w-full max-w-[1200px] px-4 text-[13px]">

<div class="max-w-7xl mx-auto">

    <!-- Header -->
    <h1 class="text-2xl font-bold mb-6">eKYC Details</h1>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Basic Info -->
        <div class="bg-white p-5 rounded-xl shadow">
            <h2 class="font-semibold text-lg mb-4 border-b pb-2">Basic Information</h2>
            
            <div class="space-y-2 text-sm">
                <p><b>Name:</b> {{ $data->name }}</p>
                <p><b>Email:</b> {{ $data->email }}</p>
                <p><b>Phone:</b> {{ $data->phone }}</p>
                <p><b>Business Name:</b> {{ $data->brand_name }}</p>
                <p><b>Legal Name:</b> {{ $data->legalname }}</p>
                <p><b>Business Type:</b> {{ $data->businesstype }}</p>
                <p><b>City:</b> {{ $data->city }}</p>
                <p><b>State:</b> {{ $data->state }}</p>
                <p><b>Status:</b> 
                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                        {{ $data->status }}
                    </span>
                </p>
            </div>
        </div>

        <!-- Bank Info -->
        <div class="bg-white p-5 rounded-xl shadow">
            <h2 class="font-semibold text-lg mb-4 border-b pb-2">Bank Details</h2>

            <div class="space-y-2 text-sm">
                <p><b>Account Holder:</b> {{ $data->recipient_name }}</p>
                <p><b>Account Number:</b> {{ $data->recipient_account }}</p>
                <p><b>IFSC:</b> {{ $data->recipient_ifsc }}</p>
                <p><b>Monthly Limit:</b> ₹{{ $data->monthly_limit }}</p>
                <p><b>Daily Limit:</b> ₹{{ $data->perday_limit }}</p>
            </div>
        </div>

    </div>

    <!-- Documents Section -->
    <div class="mt-8 bg-white p-5 rounded-xl shadow">
        <h2 class="font-semibold text-lg mb-4 border-b pb-2">All Documents</h2>

        @php
            $documents = [
                'PAN Card' => $data->pan_doc_url,
                'Aadhaar Front' => $data->aadhaar_doc_url,
                'Aadhaar Back' => $data->aadhaar_back_url,
                'Signature' => $data->signatory_url,
                'Bank Proof' => $data->bank_doc_url,
                'Cancelled Cheque' => $data->cancelled_cheque,
                'Bank Passbook' => $data->bank_passbook,
                'Udyam Certificate' => $data->udyam_doc,
                'GST Document' => $data->gst_doc_url ?? null,
                'Company PAN' => $data->company_pan_doc ?? null,
                'COI Document' => $data->coi_doc ?? null,
                'MOA Document' => $data->moa_doc ?? null,
                'COA Document' => $data->coa_doc ?? null,
                'Live Photo' => $data->live_photo ?? null,
            ];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">

            @foreach($documents as $title => $doc)
                <div class="border rounded-xl p-3 bg-gray-50 hover:shadow-md transition">

                    <p class="text-sm font-semibold mb-2">{{ $title }}</p>

                    @if($doc)

                        @php
                            $extension = strtolower(pathinfo($doc, PATHINFO_EXTENSION));
                        @endphp

                        @if(in_array($extension, ['jpg','jpeg','png','webp']))
                            <a href="{{ $doc }}" target="_blank">
                                <img src="{{ $doc }}" 
                                     class="rounded-lg border h-40 w-full object-cover">
                            </a>

                        @elseif($extension == 'pdf')
                            <iframe src="{{ $doc }}" class="w-full h-40 rounded-lg border"></iframe>

                        @else
                            <a href="{{ $doc }}" target="_blank" class="text-blue-600 underline text-sm">
                                View Document
                            </a>
                        @endif

                    @else
                        <div class="h-40 flex items-center justify-center text-gray-400 text-sm border rounded">
                            Not Uploaded
                        </div>
                    @endif

                </div>
            @endforeach

        </div>
    </div>

</div>

<!-- ✅ Download Button -->
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">eKYC Details</h1>

    <button onclick="downloadPDF('{{ route('ekyc.pdf', $data->id) }}')"
        class="bg-blue-600 text-white px-5 py-2 rounded-lg shadow hover:bg-blue-700 transition">
        ⬇ Download PDF
    </button>
</div>

</div>
</div>

<!-- ✅ Progress Overlay -->
<div id="downloadOverlay"
     class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-md flex items-center justify-center z-50 hidden">

    <div class="bg-white p-6 rounded-xl shadow w-80 text-center">
        <p class="font-semibold mb-3">Downloading PDF...</p>

        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
            <div id="progressBar"
                 class="bg-blue-600 h-3 rounded-full transition-all"
                 style="width:0%"></div>
        </div>

        <p id="progressText" class="text-sm mt-2">0%</p>
    </div>
</div>

<script>
function downloadPDF(url) {

    let overlay = document.getElementById('downloadOverlay');
    let bar = document.getElementById('progressBar');
    let text = document.getElementById('progressText');

    overlay.classList.remove('hidden');

    let xhr = new XMLHttpRequest();
    xhr.open("GET", url, true);
    xhr.responseType = "blob";

    xhr.onprogress = function(e) {
        if (e.lengthComputable) {
            let percent = Math.round((e.loaded / e.total) * 100);
            bar.style.width = percent + "%";
            text.innerText = percent + "%";
        }
    };

    xhr.onload = function() {

        let blob = new Blob([xhr.response], { type: 'application/pdf' });

        let link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);

        // ✅ Dynamic file name
        link.download = "{{ $data->brand_name }}_Finogic.pdf";

        link.click();

        bar.style.width = "100%";
        text.innerText = "100%";

        setTimeout(() => {
            overlay.classList.add('hidden');
        }, 800);
    };

    xhr.onerror = function() {
        alert("Download failed!");
        overlay.classList.add('hidden');
    };

    xhr.send();
}
</script>

@endsection