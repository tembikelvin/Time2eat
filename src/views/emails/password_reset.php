<?php
/**
 * Password Reset Email Template
 */
$user = $data['user'] ?? [];
$userName = $user['first_name'] ?? $user['username'] ?? 'Customer';
$resetUrl = $data['reset_url'] ?? '';
$resetToken = $data['reset_token'] ?? '';
$appUrl = $_ENV['APP_URL'] ?? 'http://localhost';
?>

<h2>Password Reset Request 🔐</h2>

<p>Hello <?= htmlspecialchars($userName) ?>,</p>

<p>We received a request to reset your password for your Time2Eat account.</p>

<p>If you made this request, click the button below to reset your password:</p>

<div style="text-align: center; margin: 30px 0;">
    <a href="<?= htmlspecialchars($resetUrl) ?>" 
       style="display: inline-block; padding: 12px 24px; background: #e74c3c; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
        Reset My Password
    </a>
</div>

<p><strong>Or copy and paste this link into your browser:</strong><br>
<code style="background: #f5f5f5; padding: 5px; border-radius: 3px; word-break: break-all;"><?= htmlspecialchars($resetUrl) ?></code></p>

<p><strong>Security Information:</strong></p>
<ul>
    <li>This link will expire in 1 hour for security reasons</li>
    <li>If you didn't request this reset, please ignore this email</li>
    <li>Your password will remain unchanged until you create a new one</li>
</ul>

<p>If you're having trouble with the button above, you can also use this reset token: <code><?= htmlspecialchars($resetToken) ?></code></p>

<p>For security reasons, if you didn't request this password reset, please contact our support team immediately.</p>

<p>Best regards,<br><strong>The Time2Eat Team</strong></p>
