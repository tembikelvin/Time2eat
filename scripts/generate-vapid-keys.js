#!/usr/bin/env node

/**
 * VAPID Key Generator Script
 * 
 * This script generates VAPID keys for push notifications.
 * Run with: node scripts/generate-vapid-keys.js
 * 
 * Requirements:
 * npm install web-push
 */

const webpush = require('web-push');

console.log('🔑 Generating VAPID keys for Time2Eat...\n');

try {
    // Generate VAPID keys
    const vapidKeys = webpush.generateVAPIDKeys();
    
    console.log('✅ VAPID keys generated successfully!\n');
    console.log('📋 Copy these keys to your configuration:\n');
    console.log('Public Key:');
    console.log(vapidKeys.publicKey);
    console.log('\nPrivate Key (keep this secret!):');
    console.log(vapidKeys.privateKey);
    console.log('\n📝 Update config/vapid.php with these keys');
    console.log('🔒 Store the private key securely in production!');
    
    // Generate config snippet
    console.log('\n📄 Config snippet for config/vapid.php:');
    console.log('```php');
    console.log("'public_key' => '" + vapidKeys.publicKey + "',");
    console.log("'private_key' => '" + vapidKeys.privateKey + "',");
    console.log('```');
    
} catch (error) {
    console.error('❌ Error generating VAPID keys:', error.message);
    console.log('\n💡 Make sure web-push is installed:');
    console.log('npm install web-push');
    process.exit(1);
}
