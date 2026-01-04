<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
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
            text-align: center;
            padding: 10px 0;
        }

        .header img {
            max-width: 100px;
        }

        .content {
            padding: 20px;
            text-align: center;
        }

        .content h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .content p {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .code {
            display: inline-block;
            font-size: 24px;
            font-weight: bold;
            padding: 10px 20px;
            background-color: #f0f0f0;
            border-radius: 5px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #888888;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <img src="{{ config('settings.app_logo.value') }}" alt="Company Logo">
    </div>
    <div class="content">
        <h1>Verification Code</h1>
        <p>Thank you for using {{ config('settings.app_name.value') }}. Please use the following verification code to
            complete your
            verification:</p>
        <div class="code">{{ $code }}</div>
        <p>This code is valid for 30 minutes.</p>
    </div>
    <div class="footer">
        <p>If you did not request this code, please ignore this email.</p>
        <p>&copy; {{ date('Y') }} {{ config('settings.app_name.value') }}. All rights reserved.</p>
    </div>
</div>
</body>
</html>
