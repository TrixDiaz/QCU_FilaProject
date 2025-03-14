<!DOCTYPE html>
<html>

<head>
    <title>Account Activation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Account Activation Required</h2>

        <p>Hello Technician,</p>

        <p>A user ({{ $userName }} - {{ $userEmail }}) requires account activation. Please review their account
        </p>

        {{-- <p>
            <a href="{{ $activationLink }}" class="btn">Activate Account</a>
        </p>

        <p>If the button doesn't work, copy and paste this URL into your browser:</p>
        <p>{{ $activationLink }}</p> --}}

        <p>Thank you,<br>Your Application Team</p>
    </div>
</body>

</html>
