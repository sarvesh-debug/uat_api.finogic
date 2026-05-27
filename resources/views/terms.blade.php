<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Finogic | Terms & Conditions</title>
  <style>
    :root {
      --brand-blue: #2a48f2ff;
      --brand-red: #032b40ff;
      --text-color: #333;
    }

    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background: #f9fafb;
      color: var(--text-color);
    }

    header {
      background: linear-gradient(90deg, var(--brand-blue), var(--brand-red));
      color: white;
      padding: 40px 20px;
      text-align: center;
    }

    header h1 {
      margin: 0;
      font-size: 32px;
    }

    main {
      max-width: 900px;
      margin: 40px auto;
      padding: 0 20px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }

    main h2 {
      color: var(--brand-blue);
      margin-top: 30px;
      margin-bottom: 15px;
      font-size: 24px;
    }

    main p, main li {
      line-height: 1.7;
      font-size: 16px;
      margin-bottom: 12px;
    }

    main ul {
      margin-left: 20px;
      margin-bottom: 20px;
    }

    footer {
      text-align: center;
      padding: 20px;
      font-size: 14px;
      color: #666;
    }

    @media (max-width: 768px) {
      header h1 {
        font-size: 24px;
      }
      main {
        margin: 20px 10px;
        padding: 20px;
      }
      main h2 {
        font-size: 20px;
      }
    }
  </style>
</head>
<body>

  <header>
    <h1>Terms & Conditions</h1>
  </header>

  <main>
    <p>Welcome to Finogic! By accessing or using our services, you agree to comply with the following terms and conditions. Please read them carefully.</p>

    <h2>1. Account Usage</h2>
    <p>All users must provide accurate and complete information when creating an account. You are responsible for maintaining the confidentiality of your account credentials.</p>

    <h2>2. Services</h2>
    <p>Finogic offers multiple financial services including payouts, UPI collections, and bank account integrations. The availability of services may vary and are subject to applicable laws and regulations.</p>

    <h2>3. User Responsibilities</h2>
    <ul>
      <li>Ensure all transactions comply with applicable laws.</li>
      <li>Maintain accurate and up-to-date information.</li>
      <li>Notify Finogic immediately of any unauthorized use of your account.</li>
    </ul>

    <h2>4. Fees and Charges</h2>
    <p>All fees and charges for services will be disclosed transparently. Finogic reserves the right to modify fees upon notice.</p>

    <h2>5. Privacy</h2>
    <p>We respect your privacy. Please review our <a href="/privacy" style="color: var(--brand-blue); text-decoration: underline;">Privacy Policy</a> for details on how your data is handled.</p>

    <h2>6. Limitation of Liability</h2>
    <p>Finogic is not liable for any indirect or consequential damages arising from the use of our services. Liability is limited to the maximum extent permitted by law.</p>

    <h2>7. Governing Law</h2>
    <p>These terms are governed by the laws of India. Any disputes will be subject to the exclusive jurisdiction of Indian courts.</p>

    <p>By using Finogic services, you acknowledge that you have read and agree to these Terms & Conditions.</p>
  </main>

  <footer>
    <p>&copy; {{ date('Y') }} Finogic. All rights reserved.</p>
  </footer>

</body>
</html>
