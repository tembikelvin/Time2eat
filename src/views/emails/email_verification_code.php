<?php
/**
 * Email Verification Code Template
 */
$user = $data['user'] ?? [];
$userName = $user['first_name'] ?? $user['username'] ?? 'Customer';
$verificationCode = $data['verification_code'] ?? '123456';
$expiryMinutes = $data['expiry_minutes'] ?? 10;
$appUrl = $_ENV['APP_URL'] ?? 'http://localhost';
?>

<h2>Verify Your Email Address 🔐</h2>

<p>Hello <?= htmlspecialchars($userName) ?>,</p>

<p>Thank you for registering with Time2Eat! To complete your account setup, please verify your email address using the code below:</p>

<div style="text-align: center; margin: 30px 0;">
    <div style="display: inline-block; padding: 20px 30px; background: #f8f9fa; border: 2px solid #e74c3c; border-radius: 10px; font-family: 'Courier New', monospace; font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #e74c3c;">
        <?= htmlspecialchars($verificationCode) ?>
    </div>
</div>

<p style="text-align: center; font-size: 14px; color: #666;">
    Enter this code on the verification page to activate your account
</p>

<div style="text-align: center; margin: 30px 0;">
    <a href="<?= $appUrl ?>/register" 
       style="display: inline-block; padding: 12px 24px; background: #e74c3c; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
        Go to Verification Page
    </a>
</div>

<p><strong>Important Information:</strong></p>
<ul>
    <li>This code will expire in <strong><?= $expiryMinutes ?> minutes</strong></li>
    <li>If you didn't request this verification, please ignore this email</li>
    <li>For security reasons, do not share this code with anyone</li>
    <li>If the code doesn't work, you can request a new one from the verification page</li>
</ul>

<p>If you're having trouble with the verification process, please contact our support team.</p>

<p>Welcome to Time2Eat!</p>
<p><strong>The Time2Eat Team</strong></p>
