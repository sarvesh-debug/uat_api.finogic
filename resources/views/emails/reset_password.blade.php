



<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">

  <link rel="icon" href="{{ asset('img/Finogic-fav.png') }}" type="image/png">

  <title>Reset Password</title>
  <style>
    /* Basic reset for email clients */
    body { margin:0; padding:0; -webkit-text-size-adjust:none; -ms-text-size-adjust:none; }
    table { border-collapse:collapse; }
    img { border:0; display:block; }
    a { color:inherit; text-decoration:none; }
    /* Mobile */
    @media screen and (max-width:600px) {
      .container { width:100% !important; padding:16px !important; }
      .hero { font-size:22px !important; }
      .btn { width:100% !important; display:block !important; }
    }
  </style>
</head>
<body style="background:#f2f4f7; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;">

  <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
      <td align="center" style="padding:32px 16px;">
        <!-- Container -->
        <table width="600" cellpadding="0" cellspacing="0" role="presentation" class="container" style="background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 6px 18px rgba(20,20,20,0.08);">
          
          <!-- Header -->
          <!-- Header -->
        <tr>
          <td style="padding:28px 32px; text-align:center; background:linear-gradient(90deg,#5c42e7,#6f5af8); border-radius:12px 12px 0 0;">
            <h1 style="margin:0; font-size:24px; font-weight:700; color:#ffffff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; letter-spacing:1px;">
              Finogic
            </h1>
          </td>
        </tr>


          <!-- Body -->
          <tr>
            <td style="padding:28px 32px; color:#333;">
              <p style="margin:0 0 16px 0; font-size:16px; line-height:1.5;">
                Hello {{ isset($notifiable->name) ? $notifiable->name : '' }},
              </p>

              <p style="margin:0 0 20px 0; color:#555; font-size:15px; line-height:1.6;">
                You are receiving this email because we received a password reset request for your account.
              </p>

              <!-- CTA button -->
              <table cellpadding="0" cellspacing="0" role="presentation" style="margin:22px 0; width:100%;">
                <tr>
                  <td align="center">
                    <a href="{{ $url }}" class="btn" style="background:#5c42e7; color:#ffffff; display:inline-block; text-align:center; padding:12px 22px; border-radius:8px; font-weight:600; font-size:16px; min-width:200px;">
                      Reset Password
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin:8px 0 18px 0; color:#777; font-size:13px;">
                This password reset link will expire in {{ $count }} minutes.
              </p>

              <hr style="border:none; border-top:1px solid #eee; margin:16px 0;">

              <p style="margin:0 0 8px 0; color:#666; font-size:13px;">
                If you did not request a password reset, no further action is required.
              </p>

              <p style="margin:20px 0 0 0; color:#666; font-size:13px;">
                Regards,<br>
                <strong>Finogic_Payout</strong>
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding:18px 32px; background:#fbfbfd; text-align:center; color:#9aa0aa; font-size:12px;">
              <p style="margin:0 0 8px 0;">If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:</p>
              <p style="word-break:break-all; margin:0 0 8px 0;"><a href="{{ $url }}" style="color:#5c42e7;">{{ $url }}</a></p>
              <p style="margin:8px 0 0 0;">© 2026 Finogic. All rights reserved.</p>
            </td>
          </tr>
        </table>

        <!-- small note under container -->
        <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px; margin-top:12px;">
          <tr>
            <td style="text-align:center; color:#9aa0aa; font-size:12px;">
              <p style="margin:10px 0 0 0;">If you didn't request this, you can safely ignore this email.</p>
            </td>
          </tr>
        </table>

      </td>
    </tr>
  </table>

</body>
</html>
