<?php
/**
 * Installation Step 5: Final Configuration
 */
?>

<h3 class="text-lg font-semibold mb-4">Final Configuration</h3>
<p class="text-gray-600 mb-6">Configure your application settings and complete the installation.</p>

<form method="POST" class="space-y-6">
    <input type="hidden" name="step" value="5">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
            <label for="app_name" class="block text-sm font-medium text-gray-700 mb-2">Application Name</label>
            <input type="text" 
                   id="app_name" 
                   name="app_name" 
                   value="<?= htmlspecialchars($_POST['app_name'] ?? 'Time2Eat') ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                   placeholder="Time2Eat"
                   required>
            <p class="text-xs text-gray-500 mt-1">This will appear in the browser title and throughout the app</p>
        </div>

        <div class="md:col-span-2">
            <label for="app_url" class="block text-sm font-medium text-gray-700 mb-2">Application URL</label>
            <?php
            // Auto-detect the application URL
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $path = dirname($_SERVER['REQUEST_URI'] ?? '');
            $path = ($path === '/' || $path === '\\') ? '' : $path;
            $detectedUrl = $protocol . $host . $path;
            ?>
            <input type="url"
                   id="app_url"
                   name="app_url"
                   value="<?= htmlspecialchars($_POST['app_url'] ?? $detectedUrl) ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                   placeholder="https://your-domain.com"
                   required>
            <p class="text-xs text-gray-500 mt-1">The full URL where your Time2Eat installation will be accessible</p>
            <p class="text-xs text-blue-600 mt-1">Auto-detected: <?= htmlspecialchars($detectedUrl) ?></p>
        </div>

        <div>
            <label for="app_env" class="block text-sm font-medium text-gray-700 mb-2">Environment</label>
            <select id="app_env" 
                    name="app_env" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                <option value="production" <?= ($_POST['app_env'] ?? 'production') === 'production' ? 'selected' : '' ?>>Production</option>
                <option value="development" <?= ($_POST['app_env'] ?? '') === 'development' ? 'selected' : '' ?>>Development</option>
            </select>
            <p class="text-xs text-gray-500 mt-1">Use 'production' for live sites, 'development' for testing</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
            <select name="app_timezone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                <option value="Africa/Douala" selected>Africa/Douala (Cameroon)</option>
                <option value="Africa/Lagos">Africa/Lagos (Nigeria)</option>
                <option value="UTC">UTC</option>
            </select>
            <p class="text-xs text-gray-500 mt-1">Default timezone for the application</p>
        </div>
    </div>

    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <h4 class="font-semibold text-green-800 mb-2">✅ Installation Progress</h4>
        <div class="text-sm text-green-700 space-y-1">
            <p>• System requirements verified</p>
            <p>• Database connection established</p>
            <p>• Database schema created successfully</p>
            <p>• Admin account created</p>
            <p>• Ready for final configuration</p>
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h4 class="font-semibold text-blue-800 mb-2">What will be configured:</h4>
        <div class="text-sm text-blue-700 space-y-1">
            <p>• Environment configuration file (.env)</p>
            <p>• Security keys and tokens</p>
            <p>• Required directories and permissions</p>
            <p>• Installation lock file</p>
            <p>• Basic application settings</p>
        </div>
    </div>

    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <h4 class="font-semibold text-yellow-800 mb-2">Post-Installation Setup</h4>
        <div class="text-sm text-yellow-700 space-y-1">
            <p>After installation, you'll need to configure:</p>
            <p>• Email settings (SMTP configuration)</p>
            <p>• Payment gateway credentials</p>
            <p>• SMS service settings (Twilio)</p>
            <p>• Google Maps API key</p>
            <p>• Social media links and contact information</p>
        </div>
    </div>

    <div class="flex justify-between">
        <a href="?step=4" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors">
            Back
        </a>
        <button type="submit" class="bg-red-500 text-white px-6 py-2 rounded-lg hover:bg-red-600 transition-colors">
            Complete Installation
        </button>
    </div>
</form>
