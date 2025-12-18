<?php
/**
 * Installation Step 4: Admin Account Creation
 */
?>

<h3 class="text-lg font-semibold mb-4">Create Admin Account</h3>
<p class="text-gray-600 mb-6">Create the main administrator account for managing Time2Eat.</p>

<form method="POST" class="space-y-6">
    <input type="hidden" name="step" value="4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
            <label for="admin_name" class="block text-sm font-medium text-gray-700 mb-2">Admin Name</label>
            <input type="text" 
                   id="admin_name" 
                   name="admin_name" 
                   value="<?= htmlspecialchars($_POST['admin_name'] ?? '') ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                   placeholder="Administrator"
                   required>
            <p class="text-xs text-gray-500 mt-1">This will be displayed in the admin dashboard</p>
        </div>

        <div class="md:col-span-2">
            <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-2">Admin Email</label>
            <input type="email" 
                   id="admin_email" 
                   name="admin_email" 
                   value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                   placeholder="admin@time2eat.com"
                   required>
            <p class="text-xs text-gray-500 mt-1">Used for login and system notifications</p>
        </div>

        <div>
            <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
            <input type="password" 
                   id="admin_password" 
                   name="admin_password" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                   placeholder="Enter secure password"
                   minlength="8"
                   required>
            <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
        </div>

        <div>
            <label for="admin_confirm" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
            <input type="password" 
                   id="admin_confirm" 
                   name="admin_confirm" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                   placeholder="Confirm password"
                   minlength="8"
                   required>
            <p class="text-xs text-gray-500 mt-1">Must match the password above</p>
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h4 class="font-semibold text-blue-800 mb-2">Admin Account Privileges</h4>
        <div class="text-sm text-blue-700 space-y-1">
            <p>• Full access to all system features and settings</p>
            <p>• User and restaurant management</p>
            <p>• Order and payment oversight</p>
            <p>• Analytics and reporting</p>
            <p>• System configuration and maintenance</p>
        </div>
    </div>

    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <h4 class="font-semibold text-yellow-800 mb-2">Security Recommendations</h4>
        <div class="text-sm text-yellow-700 space-y-1">
            <p>• Use a strong, unique password with mixed characters</p>
            <p>• Enable two-factor authentication after installation</p>
            <p>• Use a secure email address that you control</p>
            <p>• Consider using a password manager</p>
        </div>
    </div>

    <div class="flex justify-between">
        <a href="?step=3" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors">
            Back
        </a>
        <button type="submit" class="bg-red-500 text-white px-6 py-2 rounded-lg hover:bg-red-600 transition-colors">
            Create Admin Account
        </button>
    </div>
</form>

<script>
// Password confirmation validation
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('admin_password');
    const confirm = document.getElementById('admin_confirm');
    
    function validatePassword() {
        if (password.value !== confirm.value) {
            confirm.setCustomValidity('Passwords do not match');
        } else {
            confirm.setCustomValidity('');
        }
    }
    
    password.addEventListener('input', validatePassword);
    confirm.addEventListener('input', validatePassword);
});
</script>
