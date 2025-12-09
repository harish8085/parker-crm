<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ env('APP_NAME') }}</title>
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
            color: #28a745;
            margin: 0;
            font-size: 28px;
        }
        .success-icon {
            text-align: center;
            font-size: 64px;
            color: #28a745;
            margin: 20px 0;
        }
        .content {
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .info-box {
            background-color: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ env('APP_NAME') }}</h1>
        </div>
        
        <div class="success-icon">✓</div>
        
        <div class="content">
            <h2>Dear, {{ $user->first_name }}!</h2>
            <p>You are successfully registered as a {{ $user->user_type }} in {{ env('APP_NAME') }}.</p>
            
            <div class="info-box">
                <p style="margin: 0;"><strong>Your Account Details:</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li><strong>Name:</strong> {{ $user->first_name }}</li>
                    <li><strong>Email:</strong> {{ $user->email }}</li>
                    <li><strong>password:</strong> {{ $user->password ?? 'N/A' }}</li>
                </ul>
            </div>
                
            <p>You can now login to your account and access all the features of our platform. Here's what you can do:</p>
            <ul>
                <li>Access your dashboard</li>
                <li>Manage your profile</li>
                <li>View and track your applications</li>
                <li>And much more!</li>
            </ul>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ url('/') }}" class="button">Login to Your Account</a>
            </div>
            
            <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
            
            <p>Thank you for choosing {{ env('APP_NAME') }}!</p>
        </div>
        
        <div class="footer">
            <p>This is an automated email, please do not reply.</p>
            <p>&copy; {{ date('Y') }} {{ env('APP_NAME') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

