<?php
/**
 * Installation Step 3: Database Setup
 */
?>

<h3 class="text-lg font-semibold mb-4">Database Setup</h3>
<p class="text-gray-600 mb-6">Configure your database schema and sample data.</p>

<form method="POST" class="space-y-6">
    <input type="hidden" name="step" value="3">
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <h4 class="font-semibold text-green-800 mb-2">Database Connection Successful</h4>
        <p class="text-green-700 text-sm">
            Connected to: <strong><?= htmlspecialchars($_SESSION['db_config']['name']) ?></strong> 
            on <strong><?= htmlspecialchars($_SESSION['db_config']['host']) ?></strong>
        </p>
    </div>

    <div class="space-y-4">
        <div class="border border-gray-200 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-2">Database Schema</h4>
            <p class="text-gray-600 text-sm mb-3">
                The installer will create all necessary tables, indexes, and triggers for Time2Eat.
            </p>
            <div class="bg-gray-50 rounded p-3">
                <p class="text-sm text-gray-700 font-medium mb-2">Tables to be created:</p>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs text-gray-600">
                    <span>• users</span>
                    <span>• restaurants</span>
                    <span>• menu_items</span>
                    <span>• orders</span>
                    <span>• order_items</span>
                    <span>• deliveries</span>
                    <span>• payments</span>
                    <span>• reviews</span>
                    <span>• categories</span>
                    <span>• affiliates</span>
                    <span>• site_settings</span>
                    <span>• and 15+ more...</span>
                </div>
            </div>
        </div>

        <div class="border border-gray-200 rounded-lg p-4">
            <div class="flex items-start">
                <input type="checkbox" 
                       id="import_sample_data" 
                       name="import_sample_data" 
                       value="1"
                       class="mt-1 mr-3 h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                       checked>
                <div class="flex-1">
                    <label for="import_sample_data" class="font-semibold text-gray-800 cursor-pointer">
                        Import Sample Data
                    </label>
                    <p class="text-gray-600 text-sm mt-1">
                        Includes sample restaurants, menu items, categories, and system settings. 
                        Recommended for testing and getting started quickly.
                    </p>
                    <div class="mt-2 bg-blue-50 rounded p-3">
                        <p class="text-sm text-blue-700 font-medium mb-1">Sample data includes:</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-1 text-xs text-blue-600">
                            <span>• 8 Food categories (African, Fast Food, etc.)</span>
                            <span>• 6 Sample restaurants with menus</span>
                            <span>• 30+ Menu items with images</span>
                            <span>• System settings and configuration</span>
                            <span>• Sample customer reviews</span>
                            <span>• Payment methods and coupons</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <h4 class="font-semibold text-yellow-800 mb-2">⚠️ Important Warning</h4>
        <p class="text-yellow-700 text-sm">
            This will create database tables and import data. If tables already exist, 
            the process may fail or overwrite existing data. Make sure to backup your database first.
        </p>
    </div>

    <div class="flex justify-between">
        <a href="?step=2" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors">
            Back
        </a>
        <button type="submit" class="bg-red-500 text-white px-6 py-2 rounded-lg hover:bg-red-600 transition-colors">
            Setup Database
        </button>
    </div>
</form>

<div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
    <h4 class="font-semibold text-blue-800 mb-2">What happens next?</h4>
    <div class="text-sm text-blue-700 space-y-2">
        <p>1. <strong>Schema Creation:</strong> All database tables, indexes, and relationships will be created</p>
        <p>2. <strong>Triggers Setup:</strong> Automatic calculations for ratings, statistics, and order totals</p>
        <p>3. <strong>Sample Data:</strong> If selected, sample restaurants and menu items will be imported</p>
        <p>4. <strong>System Settings:</strong> Default configuration values will be set</p>
        <p>5. <strong>Verification:</strong> The installer will verify all tables were created successfully</p>
    </div>
</div>
