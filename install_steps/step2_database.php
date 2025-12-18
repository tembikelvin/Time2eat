<?php
/**
 * Installation Step 2: Database Configuration
 */
?>

<h3 class="text-lg font-semibold mb-4">Database Configuration</h3>
<p class="text-gray-600 mb-6">Enter your database connection details. The installer will create the database if it doesn't exist.</p>

<form method="POST" class="space-y-6">
    <input type="hidden" name="step" value="2">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="db_host" class="block text-sm font-medium text-gray-700 mb-2">Database Host</label>
            <input type="text" 
                   id="db_host" 
                   name="db_host" 
                   value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                   placeholder="localhost"
                   required>
            <p class="text-xs text-gray-500 mt-1">Usually 'localhost' for most hosting providers</p>
        </div>

        <div>
            <label for="db_port" class="block text-sm font-medium text-gray-700 mb-2">Database Port</label>
            <input type="number" 
                   id="db_port" 
                   name="db_port" 
                   value="<?= htmlspecialchars($_POST['db_port'] ?? '3306') ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                   placeholder="3306"
                   min="1"
                   max="65535">
            <p class="text-xs text-gray-500 mt-1">Default MySQL port is 3306</p>
        </div>

        <div>
            <label for="db_name" class="block text-sm font-medium text-gray-700 mb-2">Database Name</label>
            <input type="text" 
                   id="db_name" 
                   name="db_name" 
                   value="<?= htmlspecialchars($_POST['db_name'] ?? 'time2eat') ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                   placeholder="time2eat"
                   pattern="[a-zA-Z0-9_]+"
                   required>
            <p class="text-xs text-gray-500 mt-1">Will be created if it doesn't exist</p>
        </div>

        <div>
            <label for="db_user" class="block text-sm font-medium text-gray-700 mb-2">Database Username</label>
            <input type="text" 
                   id="db_user" 
                   name="db_user" 
                   value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                   placeholder="root"
                   required>
            <p class="text-xs text-gray-500 mt-1">Database user with CREATE privileges</p>
        </div>
    </div>

    <div>
        <label for="db_pass" class="block text-sm font-medium text-gray-700 mb-2">Database Password</label>
        <input type="password" 
               id="db_pass" 
               name="db_pass" 
               value="<?= htmlspecialchars($_POST['db_pass'] ?? '') ?>"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
               placeholder="Enter database password">
        <p class="text-xs text-gray-500 mt-1">Leave empty if no password is required</p>
    </div>

    <div class="flex justify-between">
        <a href="?step=1" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors">
            Back
        </a>
        <button type="submit" class="bg-red-500 text-white px-6 py-2 rounded-lg hover:bg-red-600 transition-colors">
            Test Connection & Continue
        </button>
    </div>
</form>

<div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
    <h4 class="font-semibold text-blue-800 mb-2">Database Setup Tips</h4>
    <div class="text-sm text-blue-700 space-y-2">
        <p><strong>Shared Hosting:</strong> Use the database credentials provided by your hosting provider. Database name might have a prefix.</p>
        <p><strong>cPanel/Plesk:</strong> Create database and user through your control panel, then enter the details here.</p>
        <p><strong>Local Development:</strong> Default is usually root/root or root/(empty password).</p>
        <p><strong>Cloud Hosting:</strong> Check your cloud provider's documentation for database connection details.</p>
    </div>
</div>

<div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
    <h4 class="font-semibold text-yellow-800 mb-2">Important Notes</h4>
    <div class="text-sm text-yellow-700 space-y-1">
        <p>• The database user must have CREATE, DROP, INSERT, UPDATE, DELETE, and ALTER privileges</p>
        <p>• The installer will create the database if it doesn't exist</p>
        <p>• Existing data in the database will be preserved unless tables conflict</p>
        <p>• Make sure to backup any existing data before proceeding</p>
    </div>
</div>
