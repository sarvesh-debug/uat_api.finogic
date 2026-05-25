<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>
body {
    font-family: DejaVu Sans;
    font-size: 12px;
    margin: 0;
    padding: 0 10px;
}

/* HEADER */
.header {
    background: #f1f1f1;
    padding: 10px;
    text-align: right;
}

/* TITLE */
.title {
    text-align: center;
    font-weight: bold;
    font-size: 16px;
    margin: 10px 0;
}

/* SECTION TITLE */
.section-title {
    background: #e5e5e5;
    padding: 6px;
    font-weight: bold;
    text-align: center;
    margin-top: 15px;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 5px;
}

td {
    border: 1px solid #ccc;
    padding: 6px;
}

.label {
    width: 35%;
    background: #f5f5f5;
    font-weight: bold;
}

/* TEXT BLOCK */
.text-block {
    padding: 10px;
    line-height: 1.6;
    text-align: justify;
}

/* SIGN */
.sign-box {
    text-align: right;
    margin-top: 40px;
}

/* FOOTER */
.footer {
    position: fixed;
    bottom: 5px;
    width: 100%;
    text-align: center;
    font-size: 10px;
}

/* PAGE BREAK SAFE */
.section {
    page-break-inside: avoid;
}
</style>
</head>

<body>

<!-- HEADER -->
<div class="header">
    <strong>aarpiz</strong><br>
    Agreement Date: {{ \Carbon\Carbon::now()->format('d-M-Y') }}
    
</div>

<div class="title">
    API Merchant CIF (Customer Information Form)
</div>

<!-- SIGNATORY -->
<div class="section">
<div class="section-title">Authorized Signatory Detail</div>
<table>
<tr><td class="label">Name</td><td>{{ $data->name }}</td></tr>
<!-- <tr>
    <td class="label">Pic</td>
    <td>
        @if($data->live_photo)
            <img src="{{ $data->pan_doc_url }}" 
                 alt="Live Photo" 
                 width="120" 
                 style="border-radius:8px; border:1px solid #ccc;">
        @else
            N/A
        @endif
    </td>
</tr> -->
<tr><td class="label">Email</td><td>{{ $data->email }}</td></tr>
<tr><td class="label">Phone</td><td>{{ $data->phone }}</td></tr>
<tr><td class="label">PAN</td><td>{{ $data->panno }}</td></tr>
<tr><td class="label">Aadhaar</td><td>{{ $data->aadhar_no }}</td></tr>
</table>
</div>

<!-- COMPANY -->
<div class="section">
<div class="section-title">Company Detail</div>
<table>
<tr><td class="label">Company Name</td><td>{{ $data->brand_name }}</td></tr>
<tr><td class="label">Legal Name</td><td>{{ $data->legalname }}</td></tr>
<tr><td class="label">Business Type</td><td>{{ $data->businesstype }}</td></tr>
<tr><td class="label">City</td><td>{{ $data->city }}</td></tr>
<tr><td class="label">State</td><td>{{ $data->state }}</td></tr>
<tr><td class="label">GST</td><td>{{ $data->gst_pan ?? '-' }}</td></tr>
<tr><td class="label">PAN</td><td>{{ $data->businesspan ?? '-' }}</td></tr>
<tr><td class="label">CIN/Udhyam</td><td>{{ $data->cin ??  $data->udhyam_no ?? '-' }}</td></tr>
</table>
</div>

<!-- BANK -->
<div class="section">
<div class="section-title">Bank Details</div>
<table>
<tr><td class="label">Account Holder</td><td>{{ $data->recipient_name }}</td></tr>
<tr><td class="label">Account Number</td><td>{{ $data->recipient_account }}</td></tr>
<tr><td class="label">IFSC</td><td>{{ $data->recipient_ifsc }}</td></tr>
</table>
</div>

<!-- AGREEMENT -->
<div class="section">
<div class="section-title">Merchant Agreement Summary</div>
<div class="text-block">

<strong>Services Included:</strong><br>
Fund Payout, Utility/BBPS, UPI Collect, Virtual Account, AEPS, Penny Drop Verification<br><br>

<strong>Key Terms:</strong><br><br>

<strong>1. Non-exclusive Platform Access:</strong><br>
The User is granted a non-exclusive right to use the aarpiz platform. The same services may be offered to other users.<br><br>

<strong>2. User Responsibility for Transactions:</strong><br>
The User shall be solely responsible for all transactions, including any errors, fraud, or misuse. aarpiz acts only as a facilitator.<br><br>

<strong>3. Charges & Pricing:</strong><br>
Service charges may change from time to time depending on market conditions or banking partners.<br><br>

<strong>4. Dispute Handling:</strong><br>
Any disputed transaction amount may be adjusted in subsequent settlements after investigation.<br>

</div>
</div>

<!-- COMPLIANCE -->
<div class="section">
<div class="section-title">Compliance & Legal Highlights</div>
<div class="text-block">

<strong>User Responsibilities:</strong><br>
• Follow IT Act, 2000<br>
• Maintain proper data security<br>
• Ensure only lawful transactions<br><br>

<strong>Restricted Activities:</strong><br>
• No crypto / gambling / illegal transactions<br>
• No fraud / terrorism / blacklisted entities<br><br>

<strong>AML Compliance:</strong><br>
• KYC mandatory<br>
• Continuous transaction monitoring<br>
• Minimum 5-year record maintenance<br>

</div>
</div>

<!-- DURATION -->
<div class="section">
<div class="section-title">Agreement Duration</div>
<div class="text-block">
<strong>Term:</strong> 3 Years<br>
Auto-renewed annually unless terminated with prior notice
</div>
</div>

<!-- JURISDICTION -->
<div class="section">
<div class="section-title">Legal Jurisdiction</div>
<div class="text-block">
Jaipur, Rajasthan (India)
</div>
</div>

<!-- DECLARATION -->
<div class="section">
<div class="section-title">Final Declaration</div>
<div class="text-block">
• All information provided is true and verified<br>
• The company complies with applicable legal and AML regulations<br>
• Services will be used strictly for lawful business purposes
</div>
</div>

<!-- SIGN -->
<div class="sign-box">
    <p><strong>Authorized Signatory</strong></p>
    <p>{{ $data->name }}</p>
</div>

<!-- FOOTER -->
<div class="footer">
    aarpiz | eKYC System
</div>

</body>
</html>