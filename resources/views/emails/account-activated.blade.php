<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Activated</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            font-size: 24px;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 20px;
        }

        .content {
            margin-bottom: 25px;
        }

        .button-container {
            text-align: center;
            margin: 30px 0;
        }

        .button {
            background-color: #3869D4;
            border-radius: 4px;
            color: #ffffff;
            display: inline-block;
            font-size: 16px;
            font-weight: bold;
            padding: 12px 30px;
            text-decoration: none;
        }

        .footer {
            font-size: 14px;
            color: #718096;
            margin-top: 30px;
            border-top: 1px solid #e8e8e8;
            padding-top: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        Account Activated
    </div>

    <div class="content">
        Hello {{ $user->name }},
        <br><br>
        Your account has been activated successfully. You can now log in and Access System.
    </div>

    <div class="button-container">
        <a href="{{ route('filament.app.auth.login') }}" class="button">Log In</a>
    </div>

    <div class="footer">
        Thanks,<br>
        {{ config('app.name') }}
    </div>
</body>

</html>
