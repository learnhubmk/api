<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form Submission</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        .container {
            margin: 20px;
            padding: 20px;
        }
        .info {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>New Contact Form Submission</h1>

    <p>You have received a new message from the contact form on your website.</p>

    <div class="info">
        <strong>Name:</strong> {{ $data['first_name'] }} {{ $data['last_name'] }}
    </div>

    <div class="info">
        <strong>Email:</strong> {{ $data['email'] }}
    </div>

    <div class="info">
        <strong>Subject:</strong> {{ $data['subject'] }}
    </div>

    <div class="info">
        <strong>Message:</strong>
        <p>{{ $data['message'] }}</p>
    </div>
</div>
</body>
</html>
