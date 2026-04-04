<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>New Contact Message</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Georgia', serif; background: #fdf4f3; color: #2d1a1a; padding: 2rem 1rem; }
        .wrapper { max-width: 560px; margin: 0 auto; }
        .header {
            text-align: center; padding: 1.5rem;
            background: #fffafa; border: 1px solid #e5c9c7;
            border-bottom: none; border-radius: 20px 20px 0 0;
        }
        .brand {
            font-family: 'Georgia', serif; font-size: 1.4rem;
            font-style: italic; font-weight: normal; color: #8c4a50;
        }
        .header-sub { font-size: 0.75rem; color: #9a7070; margin-top: 0.3rem; font-family: Arial, sans-serif; }
        .card {
            background: #fffafa; border: 1px solid #e5c9c7;
            border-top: none; padding: 1.75rem 2rem;
        }
        .intro { font-size: 0.95rem; color: #2d1a1a; margin-bottom: 1.25rem; line-height: 1.6; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 1.25rem; }
        .meta-table td { padding: 0.5rem 0.75rem; font-size: 0.88rem; font-family: Arial, sans-serif; }
        .meta-table td:first-child {
            font-weight: 700; font-size: 0.7rem; text-transform: uppercase;
            letter-spacing: 0.07em; color: #8c4a50; width: 100px; vertical-align: top;
        }
        .meta-table tr { border-bottom: 1px solid #f0dcd8; }
        .meta-table tr:last-child { border-bottom: none; }
        .message-box {
            background: #fdf4f3; border: 1px solid #e5c9c7; border-radius: 10px;
            padding: 1rem 1.25rem; margin-bottom: 1.5rem;
        }
        .message-label {
            font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.07em; color: #8c4a50; margin-bottom: 0.6rem;
            font-family: Arial, sans-serif;
        }
        .message-body { font-size: 0.92rem; line-height: 1.7; color: #3d2222; white-space: pre-wrap; }
        .cta-wrap { text-align: center; margin-top: 1.25rem; }
        .cta-btn {
            display: inline-block; padding: 0.7rem 1.75rem; border-radius: 999px;
            background: #a85058; color: #fff !important; text-decoration: none;
            font-family: Arial, sans-serif; font-size: 0.88rem; font-weight: bold;
        }
        .footer {
            background: #fdf4f3; border: 1px solid #e5c9c7; border-top: none;
            border-radius: 0 0 20px 20px; padding: 1rem 1.5rem; text-align: center;
        }
        .footer p { font-size: 0.72rem; color: #9a7070; font-family: Arial, sans-serif; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <p class="brand">Chapter of You</p>
            <p class="header-sub">Admin Notification — New Contact Message</p>
        </div>

        <div class="card">
            <p class="intro">
                A new message has been submitted via the contact form.
            </p>

            <table class="meta-table">
                <tr>
                    <td>From</td>
                    <td>{{ $contactMessage->name }}</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td><a href="mailto:{{ $contactMessage->email }}" style="color:#8c4a50">{{ $contactMessage->email }}</a></td>
                </tr>
                <tr>
                    <td>Subject</td>
                    <td>{{ $contactMessage->subject ?: '(no subject)' }}</td>
                </tr>
                <tr>
                    <td>Received</td>
                    <td>{{ $contactMessage->created_at->format('d M Y, H:i') }}</td>
                </tr>
            </table>

            <div class="message-box">
                <p class="message-label">Message</p>
                <p class="message-body">{{ $contactMessage->message }}</p>
            </div>

            <div class="cta-wrap">
                <a href="{{ config('app.url') }}/admin/messages/{{ $contactMessage->id }}" class="cta-btn">
                    View in Admin Panel
                </a>
            </div>
        </div>

        <div class="footer">
            <p>This is an automated notification from Chapter of You.<br>
            &copy; {{ date('Y') }} Chapter of You.</p>
        </div>
    </div>
</body>
</html>
