<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Account Deletion</title>
</head>

<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.5;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
        <h2 style="text-align: center; color: #333;">Confirm Your Account Deletion Request</h2>
        <p>Hello!</p>
        <p>We have received a request to delete your account associated with this email address. If you did not make this request, please ignore this email, and no changes will be made to your account.</p>
        <p>To confirm the account deletion, please click the button below:</p>
        <p style="text-align: center;">
            <a href="{{$url}}"
                style="display: inline-block; padding: 10px 20px; background-color: #f44336; color: white; text-decoration: none; border-radius: 5px;">
                Delete My Account
            </a>
        </p>
        <p>Please note that once your account is deleted, all your data associated with it will be permanently removed and cannot be recovered.</p>
        <p>Regards,<br>{{config('app.name')}}</p>
        <br />
        <hr />
        <br />
        <p>If you’re having trouble clicking the "Delete My Account" button, copy and paste the URL below into your web browser:</p>
        <a target="_blank" href="{{$url}}" style="color: #555;">{{$url}}</a>
        <p>This link will expire in 30 minutes. After that, you will need to request a new account deletion.</p>
    </div>
</body>

</html>