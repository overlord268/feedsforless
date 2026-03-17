<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FeedsForLess – Create Your Account</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #334155; max-width: 560px; margin: 0 auto; padding: 24px; }
        .brand { font-weight: 800; color: #003366; }
        .cta { display: inline-block; margin: 20px 0; padding: 14px 28px; background: #2962ff; color: #fff !important; text-decoration: none; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; border-radius: 6px; }
        .cta:hover { background: #1a4fc4; }
        .muted { font-size: 13px; color: #64748b; margin-top: 24px; }
        hr { border: none; border-top: 1px solid #e2e8f0; margin: 24px 0; }
    </style>
</head>
<body>
    <p>Hi{{ $contactName ? ' ' . $contactName : '' }},</p>

    <p>Thank you for submitting your quote request. Our team will verify pricing and logistics and send your formal quote to <strong>{{ $guestEmail }}</strong> shortly.</p>

    <p><strong>Create a FeedsForLess account</strong> to track your requests, save addresses, and manage future quotes in one place.</p>

    <p>
        <a href="{{ $registerUrl }}" class="cta">Sign up for free</a>
    </p>

    <p class="muted">If you prefer to continue as a guest, you can always request quotes using your email. Creating an account is optional but makes reordering and tracking easier.</p>

    <hr>
    <p class="muted">FeedsForLess – Industrial Feed Commodity Portal</p>
</body>
</html>
