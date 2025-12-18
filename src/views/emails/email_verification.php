<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email Address - Time2Eat</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 10px;
        }
        .title {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 30px;
        }
        .verification-button {
            display: inline-block;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
            text-align: center;
            margin: 20px 0;
            transition: transform 0.2s;
        }
        .verification-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
        }
        .verification-link {
            word-break: break-all;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #e74c3c;
            margin: 20px 0;
            font-family: monospace;
            font-size: 14px;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
        .social-links {
            margin: 20px 0;
        }
        .social-links a {
            color: #e74c3c;
            text-decoration: none;
            margin: 0 10px;
        }
        .expiry-notice {
            background: #e8f4fd;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🍽️ Time2Eat</div>
            <h1 class="title">Verify Your Email Address</h1>
        </div>

        <div class="content">
            <p>Hello <strong><?php echo htmlspecialchars($user['first_name'] ?? 'there'); ?></strong>,</p>
            
            <p>Welcome to Time2Eat! We're excited to have you join our food delivery platform. To complete your registration and start enjoying our services, please verify your email address by clicking the button below:</p>

            <div style="text-align: center;">
                <a href="<?php echo htmlspecialchars($verification_url); ?>" class="verification-button">
                    ✅ Verify My Email Address
                </a>
            </div>

            <div class="expiry-notice">
                <strong>⏰ Important:</strong> This verification link will expire in <?php echo $expiry_hours; ?> hours for security reasons.
            </div>

            <p>If the button above doesn't work, you can copy and paste the following link into your browser:</p>
            
            <div class="verification-link">
                <?php echo htmlspecialchars($verification_url); ?>
            </div>

            <div class="warning">
                <strong>⚠️ Security Notice:</strong> If you didn't create an account with Time2Eat, please ignore this email. Your email address will not be verified and no account will be created.
            </div>

            <p>Once verified, you'll be able to:</p>
            <ul>
                <li>🍕 Browse and order from local restaurants</li>
                <li>🚚 Track your orders in real-time</li>
                <li>⭐ Rate and review your dining experiences</li>
                <li>💳 Manage your payment methods securely</li>
                <li>🎁 Access exclusive deals and promotions</li>
            </ul>
        </div>

        <div class="footer">
            <p>Need help? Contact our support team:</p>
            <p>
                📧 Email: support@time2eat.com<br>
                📞 Phone: +237 6XX XXX XXX<br>
                🌐 Website: <a href="<?php echo $_ENV['APP_URL'] ?? 'https://time2eat.com'; ?>">time2eat.com</a>
            </p>
            
            <div class="social-links">
                <a href="#">Facebook</a> |
                <a href="#">Twitter</a> |
                <a href="#">Instagram</a>
            </div>
            
            <p style="margin-top: 20px; font-size: 12px; color: #999;">
                This email was sent to <?php echo htmlspecialchars($user['email']); ?>. 
                If you didn't request this, please ignore this email.
            </p>
        </div>
    </div>
</body>
</html>

