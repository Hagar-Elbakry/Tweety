<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Your Password</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">
<table align="center" width="600" style="background-color: #ffffff; margin-top: 30px; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
    <tr>
        <td style="background: #FFA500; color: white; text-align: center; padding: 30px;">
            <h1>Reset Your Password</h1>
            <p style="margin: 5px 0 0; font-size: 16px;">Use the OTP below to reset your password.</p>
        </td>
    </tr>
    <tr>
        <td style="padding: 30px; text-align: center; color: #333;">
            <h2 style="font-size: 28px; letter-spacing: 4px; margin: 20px 0; color: #FFA500;">{{ $otp }}</h2>
            <p style="font-size: 15px;">This OTP is valid for 15 minutes. Do not share it with anyone.</p>
            <p style="margin-top: 20px; font-size: 14px; color: #555;">If you did not request a password reset, you can safely ignore this email.</p>
        </td>
    </tr>
    <tr>
        <td style="padding: 15px; font-size: 12px; color: #999; text-align: center; background: #f9f9f9;">
            Tweety Team 💙
        </td>
    </tr>
</table>
</body>
</html>
