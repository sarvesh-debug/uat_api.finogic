{{-- <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>aarpiz Certificate</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @page { size: A4; margin: 0; }

    body {
      font-family: 'Inter', sans-serif;
      background-color: #f3f4f6;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: 100vh;
      padding: 2rem 0;
    }

    .certificate {
      width: 90%;
      max-width: 850px;
      background: linear-gradient(to bottom, #fffaf0, #fdf6e3);
      border-radius: 25px;
      border: 6px solid #7c3aed;
      box-shadow: 0 20px 50px rgba(0,0,0,0.25);
      position: relative;
      overflow: hidden;
      padding: 40px 30px;
    }

    /* Watermark */
    .certificate::before {
      content: "aarpiz";
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-30deg);
      font-size: 6rem;
      color: rgba(156, 163, 175, 0.08);
      white-space: nowrap;
      pointer-events: none;
      z-index: 0;
    }

    .logo {
      width: 100px;
      height: 100px;
      border-radius: 9999px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
      z-index: 10;
    }

    .signature-line {
      border-top: 3px solid #7c3aed;
      width: 160px;
      margin: 15px auto 0 auto;
    }

    .info-card {
      border-radius: 15px;
      padding: 15px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.1);
      text-align: left;
      background: linear-gradient(135deg, #f0f5ff, #e0e7ff);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .info-card i {
      font-size: 1.5rem;
      color: #7c3aed;
    }

    .download-btn {
      font-weight: 600;
      background: linear-gradient(to right, #7c3aed, #f59e0b);
      transition: 0.3s;
    }
    .download-btn:hover {
      background: linear-gradient(to right, #f59e0b, #7c3aed);
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<main class="certificate relative z-10 text-center">
  <!-- Header -->
  <div class="bg-gradient-to-r from-purple-600 via-indigo-500 to-indigo-700 text-white py-6 rounded-t-lg shadow-lg px-6">
    <img src="https://via.placeholder.com/100" alt="aarpiz Logo" class="logo mx-auto mb-2">
    <h1 class="text-4xl font-bold uppercase tracking-wider">Authorized Banking Point</h1>
    <p class="text-lg uppercase tracking-wide">Certificate of Authorization</p>
  </div>

  <!-- Main content -->
  <div class="mt-10 px-6 text-gray-800 space-y-6 relative z-10">
    <p class="text-lg italic">This is to certify that</p>
    <p class="text-3xl font-extrabold text-indigo-700">[Retailer Name]</p>
    <p class="text-lg">is an authorized Retailer of <span class="font-bold text-purple-700">aarpiz</span>.</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-8">
      <div class="info-card">
        <i class="fas fa-id-badge"></i>
        <div>
          <p class="font-semibold text-indigo-700">RT No:</p>
          <p>[RT Number]</p>
        </div>
      </div>
      <div class="info-card">
        <i class="fas fa-phone-alt"></i>
        <div>
          <p class="font-semibold text-indigo-700">Mobile No:</p>
          <p>[Mobile Number]</p>
        </div>
      </div>
      <div class="info-card">
        <i class="fas fa-store"></i>
        <div>
          <p class="font-semibold text-indigo-700">Shop Name:</p>
          <p>[Shop Name]</p>
        </div>
      </div>
      <div class="info-card">
        <i class="fas fa-calendar-check"></i>
        <div>
          <p class="font-semibold text-indigo-700">Effective From:</p>
          <p>[Date]</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Signature -->
  <div class="mt-12 relative z-10">
    <div class="signature-line"></div>
    <p class="font-bold mt-2 text-indigo-700">aarpiz</p>
    <p class="text-sm text-gray-500">Chief Sales Officer</p>
  </div>

  <!-- Footer Terms -->
  <div class="mt-6 text-xs text-gray-500 px-4 relative z-10">
    <p>*Terms and conditions: The appointment is subject to acceptance of the terms and conditions of aarpiz. The Banking point shall only function as per the service agreement.</p>
  </div>
</main>

<!-- Download Button -->
<button id="download-btn" class="download-btn fixed bottom-10 left-1/2 transform -translate-x-1/2 text-white px-6 py-3 rounded-lg shadow-lg z-20">
  <i class="fas fa-download mr-2"></i> Download Certificate as PDF
</button>

<script>
  document.getElementById('download-btn').addEventListener('click', function () {
    const element = document.querySelector('main');
    html2pdf().set({
      margin: 0.5,
      filename: 'aarpiz_Certificate.pdf',
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { scale: 2 },
      jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    }).from(element).save();
  });
</script>

</body>
</html> --}}







{{-- final  --}}

{{-- <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Certificate of Recognition</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 20px;
    }
    
    .container {
      max-width: 1200px;
      text-align: center;
    }
    
    h1 {
      color: #2c3e50;
      margin-bottom: 30px;
      font-size: 2.8rem;
      text-shadow: 2px 2px 3px rgba(0,0,0,0.1);
    }
    
    .certificate-container {
      width: 1000px;
      height: 700px;
      margin: 20px auto;
      padding: 50px;
      background: linear-gradient(to bottom right, #fffdf8, #fff9e6);
      border: 20px solid transparent;
      border-image: linear-gradient(45deg, #d4af37, #f9e076, #d4af37);
      border-image-slice: 1;
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15), 0 0 50px rgba(212, 175, 55, 0.2);
      position: relative;
      overflow: hidden;
    }
    
    .certificate-inner {
      border: 5px solid rgba(212, 175, 55, 0.3);
      height: 100%;
      padding: 40px;
      text-align: center;
      position: relative;
      background: 
        radial-gradient(circle at 50% 50%, rgba(212, 175, 55, 0.1) 0%, transparent 20%),
        url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><path fill="%23d4af37" opacity="0.1" d="M50 0 L62 38 L100 38 L69 59 L81 100 L50 75 L19 100 L31 59 L0 38 L38 38 Z"></path></svg>');
    }
    
    /* Decorative corners */
    .corner {
      position: absolute;
      width: 100px;
      height: 100px;
      border: 4px solid #d4af37;
      opacity: 0.5;
    }
    
    .corner-tl {
      top: 15px;
      left: 15px;
      border-right: none;
      border-bottom: none;
    }
    
    .corner-tr {
      top: 15px;
      right: 15px;
      border-left: none;
      border-bottom: none;
    }
    
    .corner-bl {
      bottom: 15px;
      left: 15px;
      border-right: none;
      border-top: none;
    }
    
    .corner-br {
      bottom: 15px;
      right: 15px;
      border-left: none;
      border-top: none;
    }
    
    /* Certificate Number */
    .certificate-number {
      position: absolute;
      top: 25px;
      right: 40px;
      font-size: 14px;
      color: #7f6a1d;
      font-weight: bold;
      letter-spacing: 1px;
    }
    
    /* Header */
    .certificate-header {
      margin-top: 30px;
    }
    
    .certificate-header h1 {
      font-family: 'Times New Roman', serif;
      font-size: 42px;
      letter-spacing: 8px;
      margin: 0;
      color: #7f6a1d;
      text-transform: uppercase;
      font-weight: bold;
    }
    
    .certificate-header h2 {
      font-family: 'Times New Roman', serif;
      font-size: 22px;
      margin: 20px 0;
      color: #555;
      font-weight: normal;
      font-style: italic;
    }
    
    /* Body */
    .certificate-body {
      margin: 50px 0;
    }
    
    .certificate-body p {
      font-size: 20px;
      margin: 15px 0;
      color: #333;
      font-family: 'Times New Roman', serif;
    }
    
    .retailer-name {
      font-size: 42px;
      margin: 25px 0;
      color: #2c3e50;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 2px;
      font-family: 'Times New Roman', serif;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }
    
    .certificate-body h3 {
      font-size: 32px;
      margin: 25px 0;
      color: #7f6a1d;
      font-weight: bold;
      text-transform: uppercase;
      font-family: 'Times New Roman', serif;
    }
    
    .date {
      font-size: 18px;
      margin-top: 30px;
      color: #555;
      font-family: 'Times New Roman', serif;
    }
    
    /* Footer Signatures */
    .certificate-footer {
      display: flex;
      justify-content: space-between;
      margin-top: 70px;
      padding: 0 50px;
    }
    
    .certificate-footer .sign {
      text-align: center;
      width: 40%;
    }
    
    .certificate-footer .sign img {
      width: 180px;
      height: auto;
      margin-bottom: 10px;
    }
    
    .certificate-footer .sign p {
      margin-top: 5px;
      font-size: 16px;
      color: #333;
      font-weight: bold;
      border-top: 1px solid #d4af37;
      padding-top: 10px;
      display: inline-block;
      font-family: 'Times New Roman', serif;
    }
    
    .signature-line {
      height: 2px;
      background: #d4af37;
      width: 200px;
      margin: 5px auto;
    }
    
    /* Download Button */
    .download-btn {
      display: inline-block;
      margin: 40px auto;
      padding: 15px 40px;
      background: linear-gradient(45deg, #d4af37, #f9e076);
      color: #2c3e50;
      text-decoration: none;
      font-size: 18px;
      border-radius: 5px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s;
      box-shadow: 0 5px 15px rgba(212, 175, 55, 0.4);
      border: none;
      font-weight: bold;
    }
    
    .download-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(212, 175, 55, 0.6);
    }
    
    /* Watermark */
    .watermark {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-45deg);
      font-size: 120px;
      opacity: 0.03;
      color: #000;
      font-weight: bold;
      z-index: 0;
      white-space: nowrap;
      user-select: none;
      font-family: 'Times New Roman', serif;
    }
    
    /* Gold seal */
    .gold-seal {
      position: absolute;
      bottom: 100px;
      right: 120px;
      width: 120px;
      height: 120px;
      background: radial-gradient(circle, #d4af37 0%, #f9e076 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 14px;
      font-weight: bold;
      opacity: 0.8;
      box-shadow: 0 0 20px rgba(212, 175, 55, 0.6);
      border: 3px solid #fff;
    }
    
    /* Ribbons */
    .ribbon {
      position: absolute;
      top: -5px;
      right: 100px;
      width: 200px;
      height: 40px;
      background: #d4af37;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      box-shadow: 0 5px 10px rgba(0,0,0,0.1);
    }
    
    .ribbon:before {
      content: "";
      position: absolute;
      left: -20px;
      bottom: 0;
      width: 0;
      height: 0;
      border-right: 20px solid #b8941a;
      border-top: 20px solid transparent;
      border-bottom: 20px solid transparent;
    }
    
    .ribbon:after {
      content: "";
      position: absolute;
      right: -20px;
      bottom: 0;
      width: 0;
      height: 0;
      border-left: 20px solid #b8941a;
      border-top: 20px solid transparent;
      border-bottom: 20px solid transparent;
    }
    
    /* Responsive adjustments */
    @media (max-width: 1100px) {
      .certificate-container {
        width: 90%;
        height: auto;
        padding: 30px;
      }
      
      .certificate-inner {
        padding: 20px;
      }
      
      .certificate-footer {
        flex-direction: column;
        align-items: center;
        gap: 30px;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <h1>Certificate of Recognition</h1>
    
    <div class="certificate-container">
      <div class="certificate-inner">
        <!-- Decorative corners -->
        <div class="corner corner-tl"></div>
        <div class="corner corner-tr"></div>
        <div class="corner corner-bl"></div>
        <div class="corner corner-br"></div>
        
        <!-- Watermark -->
        <div class="watermark">XYZ FINTECH</div>
        
        <!-- Ribbon -->
        <div class="ribbon">OFFICIAL RECOGNITION</div>
        
        <!-- Certificate Number -->
        <div class="certificate-number">Certificate ID: EICT/2023/09/06/10256</div>

        <!-- Header -->
        <div class="certificate-header">
          <h1>CERTIFICATE OF RECOGNITION</h1>
          <h2>This is to certify that</h2>
        </div>

        <!-- Body -->
        <div class="certificate-body">
          <div class="retailer-name">Retailer Name</div>
          <p>has successfully been appointed as an</p>
          <h3>Authorized Retailer</h3>
          <p>of XYZ Fintech Services</p>
          <p class="date">Dated: 06 September 2023</p>
        </div>

        <!-- Gold Seal -->
        <div class="gold-seal">
          OFFICIAL SEAL
        </div>

        <!-- Footer with Signatures -->
        <div class="certificate-footer">
          <div class="sign">
            <div class="signature-line"></div>
            <p>Chief Sales Officer</p>
            <p>XYZ Fintech Services</p>
          </div>
          <div class="sign">
            <div class="signature-line"></div>
            <p>Managing Director</p>
            <p>XYZ Fintech Services</p>
          </div>
        </div>

      </div>
    </div>

    <!-- Download Button -->
    <button class="download-btn" onclick="window.print()">
      <i class="fas fa-download"></i> Download Certificate
    </button>
  </div>

</body>
</html> --}}


















{{-- 1st final  --}}

{{-- <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Authorized Banking Point Certificate</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { 
      margin: 0; 
      padding: 0; 
      box-sizing: border-box; 
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 20px;
    }
    
    .container { 
      max-width: 900px; 
      width: 100%;
      text-align: center; 
    }
    
    h1 {
      color: #2c3e50;
      margin-bottom: 25px;
      font-size: 2.2rem;
      text-shadow: 2px 2px 3px rgba(0,0,0,0.1);
    }
    
    .certificate-container {
      width: 100%;
      max-width: 800px;
      margin: 0 auto;
      padding: 30px;
      background: linear-gradient(to bottom right, #fffdf8, #fff9e6);
      border: 15px solid transparent;
      border-image: linear-gradient(45deg, #d4af37, #f9e076, #d4af37);
      border-image-slice: 1;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15), 0 0 30px rgba(212, 175, 55, 0.2);
      position: relative;
      overflow: hidden;
    }
    
    .certificate-inner {
      border: 4px solid rgba(212, 175, 55, 0.3);
      padding: 30px 25px;
      text-align: center;
      position: relative;
      background: 
        radial-gradient(circle at 50% 50%, rgba(212, 175, 55, 0.1) 0%, transparent 20%),
        url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><path fill="%23d4af37" opacity="0.1" d="M50 0 L62 38 L100 38 L69 59 L81 100 L50 75 L19 100 L31 59 L0 38 L38 38 Z"></path></svg>');
    }

    /* Decorative corners */
    .corner { 
      position: absolute; 
      width: 70px; 
      height: 70px; 
      border: 3px solid #d4af37; 
      opacity: 0.5; 
    }
    .corner-tl { top: 10px; left: 10px; border-right: none; border-bottom: none; }
    .corner-tr { top: 10px; right: 10px; border-left: none; border-bottom: none; }
    .corner-bl { bottom: 10px; left: 10px; border-right: none; border-top: none; }
    .corner-br { bottom: 10px; right: 10px; border-left: none; border-top: none; }

    /* Certificate Number */
    .certificate-number {
      position: absolute;
      top: 20px;
      right: 30px;
      font-size: 12px;
      color: #7f6a1d;
      font-weight: bold;
      letter-spacing: 1px;
    }

    /* Header */
    .certificate-header { margin-top: 15px; }
    .certificate-header h1 {
      font-family: 'Times New Roman', serif;
      font-size: 28px;
      letter-spacing: 3px;
      margin: 0;
      color: #7f6a1d;
      text-transform: uppercase;
      font-weight: bold;
    }
    .certificate-header h2 {
      font-family: 'Times New Roman', serif;
      font-size: 18px;
      margin: 15px 0;
      color: #555;
      font-weight: normal;
      font-style: italic;
    }

    /* Body */
    .certificate-body { 
      margin: 25px 0; 
      font-family: 'Times New Roman', serif; 
      color: #2c3e50; 
    }
    .certificate-body p { 
      font-size: 18px; 
      margin: 12px 0; 
    }
    .retailer-name { 
      font-size: 32px; 
      margin: 20px 0; 
      font-weight: bold; 
      text-transform: uppercase; 
      letter-spacing: 1.5px; 
      text-shadow: 1px 1px 2px rgba(0,0,0,0.1); 
    }
    .company-name { 
      color: #7f6a1d; 
      font-weight: bold; 
      font-style: italic; 
    }

    /* Info Lines */
    .info-lines { 
      margin: 25px 0; 
      font-size: 16px; 
      color: #333; 
    }
    .info-lines p { 
      margin: 6px 0; 
    }

    /* Signature */
    .signature-section {
      margin-top: 30px;
      display: flex;
      justify-content: center;
    }
    .signature {
      text-align: center;
      padding: 15px 30px;
      border-top: 2px solid #d4af37;
      width: 300px;
    }
    .signature-name { 
      font-weight: 700; 
      color: #2c3e50; 
      margin-top: 8px; 
      font-size: 1.1rem; 
    }
    .signature-title { 
      color: #7f8c8d; 
      font-size: 0.9rem; 
    }

    /* Footer Note */
    .footer-note { 
      margin-top: 20px; 
      font-size: 0.8rem; 
      color: #7f8c8d; 
      line-height: 1.5; 
      padding: 0 15px; 
    }

    /* Watermark */
    .watermark {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-45deg);
      font-size: 70px;
      opacity: 0.03;
      color: #000;
      font-weight: bold;
      z-index: 0;
      white-space: nowrap;
      user-select: none;
    }

    /* Gold seal */
    .gold-seal {
      position: absolute;
      bottom: 70px;
      right: 90px;
      width: 90px;
      height: 90px;
      background: radial-gradient(circle, #d4af37 0%, #f9e076 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 12px;
      font-weight: bold;
      opacity: 0.8;
      box-shadow: 0 0 15px rgba(212, 175, 55, 0.6);
      border: 2px solid #fff;
    }

    /* Ribbon */
    .ribbon {
      position: absolute;
      top: -5px;
      right: 70px;
      width: 160px;
      height: 35px;
      background: #d4af37;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      font-size: 14px;
    }
    .ribbon:before {
      content: "";
      position: absolute;
      left: -17px;
      bottom: 0;
      width: 0;
      height: 0;
      border-right: 17px solid #b8941a;
      border-top: 17.5px solid transparent;
      border-bottom: 17.5px solid transparent;
    }
    .ribbon:after {
      content: "";
      position: absolute;
      right: -17px;
      bottom: 0;
      width: 0;
      height: 0;
      border-left: 17px solid #b8941a;
      border-top: 17.5px solid transparent;
      border-bottom: 17.5px solid transparent;
    }

    /* Download Button */
    .download-btn {
      display: inline-block;
      margin: 30px auto;
      padding: 12px 30px;
      background: linear-gradient(45deg, #d4af37, #f9e076);
      color: #2c3e50;
      text-decoration: none;
      font-size: 16px;
      border-radius: 5px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s;
      box-shadow: 0 4px 12px rgba(212, 175, 55, 0.4);
      border: none;
      font-weight: bold;
    }
    .download-btn:hover { 
      transform: translateY(-3px); 
      box-shadow: 0 6px 15px rgba(212, 175, 55, 0.6); 
    }

    /* Responsive adjustments */
    @media (max-width: 900px) {
      .certificate-container {
        padding: 25px;
      }
      
      .certificate-inner {
        padding: 25px 20px;
      }
      
      .certificate-header h1 {
        font-size: 24px;
      }
      
      .retailer-name {
        font-size: 28px;
      }
      
      .certificate-body p {
        font-size: 16px;
      }
      
      .info-lines {
        font-size: 15px;
      }
      
      .gold-seal {
        width: 70px;
        height: 70px;
        font-size: 10px;
        bottom: 60px;
        right: 70px;
      }
      
      .ribbon {
        width: 140px;
        height: 30px;
        font-size: 12px;
        right: 60px;
      }
    }
    
    @media (max-width: 600px) {
      .certificate-container {
        padding: 20px 15px;
      }
      
      .certificate-inner {
        padding: 20px 15px;
      }
      
      .certificate-header h1 {
        font-size: 20px;
        letter-spacing: 2px;
      }
      
      .certificate-header h2 {
        font-size: 16px;
      }
      
      .retailer-name {
        font-size: 24px;
      }
      
      .certificate-body p {
        font-size: 15px;
      }
      
      .info-lines {
        font-size: 14px;
      }
      
      .signature {
        width: 250px;
        padding: 10px 20px;
      }
      
      .gold-seal {
        position: relative;
        margin: 15px auto;
        bottom: 0;
        right: 0;
        width: 60px;
        height: 60px;
      }
      
      .ribbon {
        position: relative;
        margin: 0 auto 15px;
        right: 0;
        top: 0;
        width: 220px;
      }
      
      .corner {
        width: 50px;
        height: 50px;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <h1>Certificate of Authorization</h1>
    
    <div class="certificate-container">
      <div class="certificate-inner">
        <!-- Decorative corners -->
        <div class="corner corner-tl"></div>
        <div class="corner corner-tr"></div>
        <div class="corner corner-bl"></div>
        <div class="corner corner-br"></div>

        <!-- Watermark -->
        <div class="watermark">aarpiz</div>

        <!-- Ribbon -->
        <div class="ribbon">AUTHORIZED BANKING POINT</div>

        <!-- Certificate Number -->
        <div class="certificate-number">RT No: XP-102345</div>

        <!-- Header -->
        <div class="certificate-header">
          <h1>Certificate of Authorization</h1>
          <h2>This is to certify that</h2>
        </div>

        <!-- Body -->
        <div class="certificate-body">
          <p class="retailer-name">MD JASIM</p>
          <p>is an authorized Retailer of <span class="company-name">aarpiz</span>.</p>
          
          <!-- Info Lines -->
          <div class="info-lines">
            <p><strong>RT Number:</strong> XP-102345</p>
            <p><strong>Mobile Number:</strong> +91 9876543870</p>
            <p><strong>Shop Name:</strong> Ramesh Digital Services</p>
            <p><strong>Effective From:</strong> 06 September 2025</p>
          </div>
        </div>

        <!-- Gold Seal -->
        <div class="gold-seal">OFFICIAL SEAL</div>

        <!-- Signature -->
        <div class="signature-section">
          <div class="signature">
            <div class="signature-name">aarpiz</div>
            <div class="signature-title">Chief Sales Officer</div>
          </div>
        </div>

        <!-- Footer Note -->
        <div class="footer-note">
          *Terms and conditions: The appointment is subject to acceptance of the terms and conditions of aarpiz. The Banking point shall only function as per the service agreement.
        </div>

      </div>
    </div>

    <!-- Download Button -->
    <button class="download-btn" onclick="window.print()">
      <i class="fas fa-download"></i> Download Certificate
    </button>
  </div>

</body>
</html> --}}






<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Authorized Banking Point Certificate</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Times New Roman', serif;
      background: #f0f0f0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 20px;
    }
    
    .certificate-container {
      width: 1000px;
      height: 700px;
      margin: 20px auto;
      padding: 50px;
      background: #fff;
      border: 20px solid #d4af37;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
      position: relative;
    }
    
    .certificate-inner {
      border: 2px solid #d4af37;
      height: 100%;
      padding: 40px;
      text-align: center;
      position: relative;
      background: #fff;
    }
    
    /* Decorative corners */
    .corner {
      position: absolute;
      width: 80px;
      height: 80px;
      border: 3px solid #d4af37;
      opacity: 0.7;
    }
    
    .corner-tl {
      top: 10px;
      left: 10px;
      border-right: none;
      border-bottom: none;
    }
    
    .corner-tr {
      top: 10px;
      right: 10px;
      border-left: none;
      border-bottom: none;
    }
    
    .corner-bl {
      bottom: 10px;
      left: 10px;
      border-right: none;
      border-top: none;
    }
    
    .corner-br {
      bottom: 10px;
      right: 10px;
      border-left: none;
      border-top: none;
    }
    
    /* Header */
    .certificate-header {
      margin-top: 30px;
    }
    
    .certificate-header h1 {
      font-size: 36px;
      letter-spacing: 4px;
      margin: 0;
      color: #2c3e50;
      text-transform: uppercase;
      font-weight: bold;
    }
    
    .certificate-header h2 {
      font-size: 20px;
      margin: 20px 0;
      color: #555;
      font-weight: normal;
      font-style: italic;
    }
    
    /* Body */
    .certificate-body {
      margin: 40px 0;
    }
    
    .certificate-body p {
      font-size: 20px;
      margin: 15px 0;
      color: #333;
    }
    
    .retailer-name {
      font-size: 42px;
      margin: 25px 0;
      color: #2c3e50;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 2px;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }
    
    .company-name {
      color: #d4af37;
      font-weight: bold;
      font-style: italic;
    }
    
    /* Info Items - No boxes */
    .info-container {
      margin: 40px 0;
      text-align: center;
    }
    
    .info-item {
      margin: 15px 0;
      font-size: 18px;
      color: #333;
    }
    
    .info-label {
      font-weight: bold;
      color: #2c3e50;
      margin-right: 10px;
    }
    
    /* Signature */
    .signature-section {
      margin-top: 60px;
      display: flex;
      justify-content: center;
    }
    
    .signature {
      text-align: center;
      padding: 20px 40px;
      border-top: 2px solid #d4af37;
      width: 350px;
    }
    
    .signature-name {
      font-weight: 700;
      color: #2c3e50;
      margin-top: 10px;
      font-size: 20px;
    }
    
    .signature-title {
      color: #7f8c8d;
      font-size: 16px;
    }
    
    /* Footer Note */
    .footer-note {
      margin-top: 30px;
      font-size: 14px;
      color: #7f8c8d;
      line-height: 1.6;
      padding: 0 20px;
    }
    
    /* Download Button */
    .download-btn {
      display: inline-block;
      margin: 40px auto;
      padding: 15px 40px;
      background: #d4af37;
      color: #2c3e50;
      text-decoration: none;
      font-size: 18px;
      border-radius: 5px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s;
      border: none;
      font-weight: bold;
    }
    
    .download-btn:hover {
      background: #c19d2a;
      transform: translateY(-2px);
    }
    
    /* Watermark */
    .watermark {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-45deg);
      font-size: 80px;
      opacity: 0.05;
      color: #000;
      font-weight: bold;
      z-index: 0;
      white-space: nowrap;
      user-select: none;
    }
    
    /* Gold seal */
    .gold-seal {
      position: absolute;
      bottom: 100px;
      right: 120px;
      width: 100px;
      height: 100px;
      background: #d4af37;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 12px;
      font-weight: bold;
      box-shadow: 0 0 15px rgba(212, 175, 55, 0.5);
      border: 3px solid #fff;
    }
    
    /* Responsive adjustments */
    @media (max-width: 1100px) {
      .certificate-container {
        width: 90%;
        height: auto;
        padding: 30px;
      }
      
      .certificate-inner {
        padding: 20px;
      }
      
      .retailer-name {
        font-size: 32px;
      }
    }
  </style>
</head>
<body>

  <div class="certificate-container">
    <div class="certificate-inner">
      <!-- Decorative corners -->
      <div class="corner corner-tl"></div>
      <div class="corner corner-tr"></div>
      <div class="corner corner-bl"></div>
      <div class="corner corner-br"></div>
      
      <!-- Watermark -->
      <div class="watermark">aarpiz</div>
      
      <!-- Gold seal -->
      <div class="gold-seal">
        OFFICIAL<br>SEAL
      </div>

      <!-- Header -->
      <div class="certificate-header">
        <h1>Authorized Banking Point</h1>
        <h2>Certificate of Authorization</h2>
      </div>

      <!-- Body -->
      <div class="certificate-body">
        <p>This is to certify that</p>
        <p class="retailer-name">Ramesh Kumar</p>
        <p>is an authorized Retailer of <span class="company-name">aarpiz</span>.</p>
        
        <!-- Info Items - No boxes -->
        <div class="info-container">
          <div class="info-item">
            <span class="info-label">RT Number:</span>
            <span>XP-102345</span>
          </div>
          
          <div class="info-item">
            <span class="info-label">Mobile Number:</span>
            <span>+91 9876543210</span>
          </div>
          
          <div class="info-item">
            <span class="info-label">Shop Name:</span>
            <span>Ramesh Digital Services</span>
          </div>
          
          <div class="info-item">
            <span class="info-label">Effective From:</span>
            <span>2025-09-06</span>
          </div>
        </div>
      </div>

      <!-- Signature -->
      <div class="signature-section">
        <div class="signature">
          <div class="signature-name">aarpiz</div>
          <div class="signature-title">Chief Sales Officer</div>
        </div>
      </div>

      <!-- Footer Note -->
      <div class="footer-note">
        *Terms and conditions: The appointment is subject to acceptance of the terms and conditions of aarpiz. The Banking point shall only function as per the service agreement.
      </div>
    </div>
  </div>

  <!-- Download Button -->
  <button class="download-btn" onclick="window.print()">
    <i class="fas fa-download"></i> Download Certificate
  </button>

</body>
</html>