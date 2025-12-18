<?php
/**
 * Welcome Email Template
 */
$user = $data['user'] ?? [];
$userName = $user['first_name'] ?? $user['username'] ?? 'Customer';
$appUrl = $_ENV['APP_URL'] ?? 'http://localhost';
?>

<h2>Welcome to Time2Eat! 🎉</h2>

<p>Hello <?= htmlspecialchars($userName) ?>,</p>

<p>Welcome to Time2Eat, Bamenda's premier food delivery platform! We're excited to have you join our community.</p>

<p>With Time2Eat, you can:</p>
<ul>
    <li>🍕 Order from your favorite local restaurants</li>
    <li>🚚 Get fast and reliable delivery</li>
    <li>💳 Pay securely with multiple payment options</li>
    <li>⭐ Rate and review your dining experiences</li>
    <li>🎁 Enjoy exclusive deals and promotions</li>
</ul>

<div style="text-align: center; margin: 30px 0;">
    <a href="<?= $appUrl ?>/browse" 
       style="display: inline-block; padding: 12px 24px; background: #e74c3c; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
        Start Ordering Now
    </a>
</div>

<p>If you have any questions or need assistance, don't hesitate to contact our support team.</p>

<p>Happy eating!</p>
<p><strong>The Time2Eat Team</strong></p>
