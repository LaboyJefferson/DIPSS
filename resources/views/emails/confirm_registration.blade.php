<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Your Email</title>
</head>
<body>
    <h1>Hi {{ $user->first_name }}</h1>
    <p>Please click the button below to confirm your email address. The link will be valid for 24 hours from the time of sending this message.:</p>
    <a href="{{ route('confirm.email', $user->user_id) }}" style="background-color: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
        Confirm Email
    </a>
    <p>If you did not create an account, no further action is required.</p>
    <p>Best regards,<br>Dumpstreet</p>
</body>
</html>
