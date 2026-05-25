<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>aarpiz | Privacy Policy</title>
  <style>
     :root {
      --brand-blue: #2a48f2ff;
      --brand-red: #032b40ff;
      --tick-green: #22c55e;
    }

    * { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif; }

    body {
      background: #f9fafb;
      color: #333;
      line-height: 1.7;
    }

    header {
      background: linear-gradient(135deg, var(--brand-blue), var(--brand-red));
      color: white;
      padding: 50px 20px;
      text-align: center;
    }

    header h1 {
      font-size: 36px;
      margin-bottom: 10px;
    }

    header p {
      font-size: 16px;
      opacity: 0.9;
    }

    main {
      max-width: 900px;
      margin: 40px auto;
      padding: 0 20px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 6px 25px rgba(0,0,0,0.1);
    }

    main h2 {
      font-size: 24px;
      margin-top: 30px;
      margin-bottom: 15px;
      color: var(--brand-blue);
    }

    main p, main li {
      font-size: 15px;
      margin-bottom: 12px;
    }

    main ul {
      padding-left: 20px;
      margin-bottom: 20px;
    }

    footer {
      text-align: center;
      padding: 30px 20px;
      font-size: 14px;
      color: #555;
    }

    footer a {
      color: var(--brand-blue);
      text-decoration: none;
      font-weight: bold;
      margin: 0 8px;
    }

    footer a:hover {
      text-decoration: underline;
    }

    @media (max-width: 600px){
      header h1 { font-size: 28px; }
      main h2 { font-size: 20px; }
    }
  </style>
</head>
<body>

<header>
  <h1>Privacy Policy</h1>
  <p>Your privacy is important to us at aarpiz. This policy explains how we handle your personal information.</p>
</header>

<main>
  <h2>1. Information We Collect</h2>
  <p>We may collect the following information when you use our services:</p>
  <ul>
    <li>Name, email, phone number, and account credentials.</li>
    <li>Transaction and payment details.</li>
    <li>Device and usage data for analytics and security.</li>
  </ul>

  <h2>2. How We Use Your Information</h2>
  <ul>
    <li>To process transactions and provide services.</li>
    <li>To communicate account-related updates.</li>
    <li>To improve our platform and user experience.</li>
    <li>To comply with legal and regulatory requirements.</li>
  </ul>

  <h2>3. Data Sharing & Security</h2>
  <p>We do not sell or rent your personal data. We may share information with trusted partners to provide our services. All data is stored securely with industry-standard measures.</p>

  <h2>4. Cookies & Analytics</h2>
  <p>We use cookies and similar technologies to improve our website functionality and analyze usage patterns. You can manage cookies via your browser settings.</p>

  <h2>5. Your Rights</h2>
  <ul>
    <li>Access, update, or delete your personal information.</li>
    <li>Opt-out of marketing communications.</li>
    <li>Request a copy of your data for compliance purposes.</li>
  </ul>

  <h2>6. Changes to Privacy Policy</h2>
  <p>We may update this policy from time to time. Updates will be posted on this page with the date of the latest revision.</p>
</main>

<footer>
  <p>&copy; {{ date('Y') }} aarpiz. All rights reserved.</p>
  <p>
    <a href="/terms">Terms & Conditions</a> | 
    <a href="/privacy">Privacy Policy</a>
  </p>
</footer>

</body>
</html>
