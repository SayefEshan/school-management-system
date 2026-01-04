<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            max-width: 150px;
            height: auto;
        }

        .content {
            background-color: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            margin-top: 0;
            font-size: 24px;
        }

        .code {
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            color: #007bff;
            padding: 15px 0;
            margin: 20px 0;
            background-color: #f0f7ff;
            border-radius: 5px;
            letter-spacing: 5px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            color: #777;
            font-size: 12px;
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
            <p>Thank you for using {{ config('settings.app_name.value') }}. Please use the following verification code
                to
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
