<!DOCTYPE html>
<html>

<head>
    <title>Password Changed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #4CAF50;
            color: white;
            padding: 10px 0;
            text-align: center;
        }

        .content {
            margin: 20px 0;
        }

        .footer {
            text-align: center;
            color: #777;
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Password Changed Successfully</h1>
        </div>
        <div class="content">
            <p>Dear {{ $user->name }},</p>
            <p>We wanted to let you know that your password has been changed successfully.</p>
            <p>Thank you for being a valued member of our community. We are committed to ensuring the security of your
                account.</p>
        </div>
        <div class="footer">
            <p>If you did not change your password, please contact our support team immediately.</p>
            <p>&copy; {{ date('Y') }} {{ config('settings.app_name.value') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
