<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification OTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 28px;
        }
        .otp-box {
            background-color: #f8f9fa;
            border: 2px dashed #007bff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            color: #007bff;
            letter-spacing: 8px;
            margin: 10px 0;
        }
        .content {
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ env('APP_NAME') }}</h1>
        </div>
        
        <div class="content">
            <h2>Email Verification</h2>
            <p>Hello {{ $user->first_name }},</p>
            <p>Thank you for registering with {{ env('APP_NAME') }}. Please use the verification code below to verify your email address:</p>
            
            <div class="otp-box">
                <p style="margin: 0 0 10px 0; color: #666;">Your Verification Code</p>
                <div class="otp-code">{{ $otp }}</div>
            </div>
            
            <p>Enter this code on the verification page to complete your registration.</p>
            
            <div class="warning">
                <strong>⚠️ Important:</strong> This code will expire in 10 minutes. Please use it before it expires.
            </div>
            
            <p>If you didn't request this verification code, please ignore this email.</p>
        </div>
        
        <div class="footer">
            <p>This is an automated email, please do not reply.</p>
            <p>&copy; {{ date('Y') }} {{ env('APP_NAME') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

