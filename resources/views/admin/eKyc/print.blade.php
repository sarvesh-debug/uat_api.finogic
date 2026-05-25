<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>eKYC PDF</title>

<style>
body {
    font-family: Arial, sans-serif;
    font-size: 12px;
    margin: 20px;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

table, th, td {
    border: 1px solid #000;
}

th, td {
    padding: 8px;
}

/* DOC */
.doc {
    margin-bottom: 30px;
}

/* IMAGE FIX */
.doc img {
    width: 100%;
    max-height: 500px;
    object-fit: contain;
}

/* PAGE BREAK */
.page-break {
    page-break-after: always;
}

/* HIDE BUTTON */
@media print {
    .no-print {
        display: none;
    }
}
</style>

<script>
window.onload = function () {
    window.print();
};
</script>

</head>

<body>

<div class="no-print">
    <button onclick="window.print()">Download PDF</button>
</div>

<h2>eKYC Details</h2>

<!-- ================= TABLE ================= -->
<table>
<tr><th>Name</th><td>{{ $data->name }}</td></tr>
<tr><th>Email</th><td>{{ $data->email }}</td></tr>
<tr><th>Phone</th><td>{{ $data->phone }}</td></tr>
<tr><th>Business Name</th><td>{{ $data->brand_name }}</td></tr>
<tr><th>Legal Name</th><td>{{ $data->legalname }}</td></tr>
<tr><th>Business Type</th><td>{{ $data->businesstype }}</td></tr>
<tr><th>City</th><td>{{ $data->city }}</td></tr>
<tr><th>State</th><td>{{ $data->state }}</td></tr>
<tr><th>Status</th><td>{{ $data->status }}</td></tr>

<tr><th colspan="2">Bank Details</th></tr>

<tr><th>Account Holder</th><td>{{ $data->recipient_name }}</td></tr>
<tr><th>Account Number</th><td>{{ $data->recipient_account }}</td></tr>
<tr><th>IFSC</th><td>{{ $data->recipient_ifsc }}</td></tr>
<tr><th>Monthly Limit</th><td>{{ $data->monthly_limit }}</td></tr>
<tr><th>Daily Limit</th><td>{{ $data->perday_limit }}</td></tr>
</table>

<div class="page-break"></div>

<!-- ================= DOCUMENTS ================= -->
<h2>All Documents</h2>

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
];
@endphp

@foreach($documents as $title => $doc)

    @if($doc)

        @php
            $ext = strtolower(pathinfo($doc, PATHINFO_EXTENSION));
        @endphp

        <div class="doc">
            <h3>{{ $title }}</h3>

            <!-- IMAGE -->
            @if(in_array($ext, ['jpg','jpeg','png','webp']))
                <img src="{{ $doc }}?v={{ time() }}">
            @endif

            <!-- PDF FILE -->
            @if($ext == 'pdf')
                <p><b>PDF Document:</b></p>
                <a href="{{ $doc }}" target="_blank">{{ $doc }}</a>
            @endif

        </div>

        <div class="page-break"></div>

    @endif

@endforeach

</body>
</html>