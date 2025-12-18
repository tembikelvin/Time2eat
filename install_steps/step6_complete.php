<?php
/**
 * Installation Step 6: Installation Complete
 */

// Self-delete installer after successful installation
if (isset($_GET['cleanup']) && $_GET['cleanup'] === '1') {
    // Delete installer files
    $filesToDelete = [
        'install.php',
        'install_steps/step1_requirements.php',
        'install_steps/step2_database.php',
        'install_steps/step3_setup.php',
        'install_steps/step4_admin.php',
        'install_steps/step5_config.php',
        'install_steps/step6_complete.php'
    ];
    
    foreach ($filesToDelete as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    // Remove install_steps directory if empty
    if (is_dir('install_steps') && count(scandir('install_steps')) === 2) {
        rmdir('install_steps');
    }
    
    // Redirect to main application
    header('Location: index.php');
    exit;
}
?>

<div class="text-center">
    <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
    </div>
    
    <h3 class="text-2xl font-bold text-gray-800 mb-4">🎉 Installation Complete!</h3>
    <p class="text-gray-600 mb-8">Time2Eat has been successfully installed and configured.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
        <h4 class="font-semibold text-green-800 mb-3">✅ Successfully Configured</h4>
        <ul class="text-sm text-green-700 space-y-2">
            <li>• Database schema created</li>
            <li>• Admin account set up</li>
            <li>• Environment configuration</li>
            <li>• Security keys generated</li>
            <li>• Required directories created</li>
            <li>• Sample data imported</li>
        </ul>
    </div>
    
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h4 class="font-semibold text-blue-800 mb-3">🚀 Quick Start</h4>
        <ul class="text-sm text-blue-700 space-y-2">
            <li>• Login with your admin account</li>
            <li>• Configure payment gateways</li>
            <li>• Set up email notifications</li>
            <li>• Add your restaurant partners</li>
            <li>• Customize site settings</li>
            <li>• Test the ordering process</li>
        </ul>
    </div>
</div>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
    <h4 class="font-semibold text-yellow-800 mb-3">⚙️ Next Steps</h4>
    <div class="text-sm text-yellow-700 space-y-3">
        <div>
            <p class="font-medium">1. Configure Email Settings</p>
            <p>Update your .env file with SMTP settings for email notifications.</p>
        </div>
        <div>
            <p class="font-medium">2. Set Up Payment Gateways</p>
            <p>Add your Stripe, PayPal, and Tranzak credentials in the admin panel.</p>
        </div>
        <div>
            <p class="font-medium">3. Configure Google Maps</p>
            <p>Add your Google Maps API key for location services.</p>
        </div>
        <div>
            <p class="font-medium">4. Customize Your Site</p>
            <p>Update contact information, social links, and branding in site settings.</p>
        </div>
    </div>
</div>

<div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-8">
    <h4 class="font-semibold text-red-800 mb-3">🔒 Security Recommendations</h4>
    <div class="text-sm text-red-700 space-y-2">
        <p>• Delete the installer files for security (use the button below)</p>
        <p>• Set proper file permissions (644 for files, 755 for directories)</p>
        <p>• Enable HTTPS/SSL for your domain</p>
        <p>• Regularly update your admin password</p>
        <p>• Keep your PHP version updated</p>
        <p>• Monitor the logs directory for any issues</p>
    </div>
</div>

<div class="text-center space-y-4">
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="index.php" 
           class="bg-red-500 text-white px-8 py-3 rounded-lg hover:bg-red-600 transition-colors font-medium">
            🏠 Go to Homepage
        </a>
        
        <a href="login.php" 
           class="bg-blue-500 text-white px-8 py-3 rounded-lg hover:bg-blue-600 transition-colors font-medium">
            👤 Admin Login
        </a>
    </div>
    
    <div class="pt-4 border-t border-gray-200">
        <a href="?step=6&cleanup=1" 
           class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors text-sm"
           onclick="return confirm('This will delete all installer files. Are you sure?')">
            🗑️ Delete Installer Files
        </a>
        <p class="text-xs text-gray-500 mt-2">Recommended for security after installation</p>
    </div>
</div>

<div class="mt-12 text-center text-gray-500">
    <p class="text-sm">
        Thank you for choosing Time2Eat! 
        <br>
        For support and documentation, visit our website or contact support.
    </p>
    <p class="text-xs mt-2">
        Installation completed on <?= date('Y-m-d H:i:s') ?>
    </p>
</div>

<script>
// Auto-redirect after 30 seconds if no action taken
setTimeout(function() {
    if (confirm('Installation complete! Would you like to go to the homepage now?')) {
        window.location.href = 'index.php';
    }
}, 30000);
</script>
