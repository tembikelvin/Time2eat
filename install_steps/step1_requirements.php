<?php
/**
 * Installation Step 1: System Requirements Check
 */

$requirements = checkSystemRequirements();
?>

<h3 class="text-lg font-semibold mb-4">System Requirements Check</h3>
<p class="text-gray-600 mb-6">Please ensure your server meets the following requirements:</p>

<div class="space-y-4">
    <?php foreach ($requirements as $req): ?>
        <div class="flex items-center justify-between p-4 border rounded-lg <?= $req['status'] ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' ?>">
            <div class="flex items-center">
                <div class="w-6 h-6 rounded-full flex items-center justify-center mr-3 <?= $req['status'] ? 'bg-green-500' : 'bg-red-500' ?>">
                    <?php if ($req['status']): ?>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    <?php else: ?>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="font-medium <?= $req['status'] ? 'text-green-800' : 'text-red-800' ?>">
                        <?= htmlspecialchars($req['name']) ?>
                    </div>
                    <div class="text-sm <?= $req['status'] ? 'text-green-600' : 'text-red-600' ?>">
                        Current: <?= htmlspecialchars($req['current']) ?>
                    </div>
                </div>
            </div>
            <div class="text-sm font-medium <?= $req['status'] ? 'text-green-600' : 'text-red-600' ?>">
                <?= $req['status'] ? 'PASS' : 'FAIL' ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
$all_passed = true;
foreach ($requirements as $req) {
    if (!$req['status']) {
        $all_passed = false;
        break;
    }
}
?>

<?php if (!$all_passed): ?>
    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
        <h4 class="font-semibold text-yellow-800 mb-2">Requirements Not Met</h4>
        <p class="text-yellow-700 text-sm mb-3">Please fix the following issues before continuing:</p>
        <ul class="text-sm text-yellow-700 list-disc list-inside space-y-1">
            <li>Ensure PHP <?= $config['min_php_version'] ?>+ is installed</li>
            <li>Install missing PHP extensions via your hosting control panel or contact your host</li>
            <li>Set proper file permissions (755 for directories, 644 for files)</li>
            <li>Ensure the web server can write to the application directory</li>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" class="mt-6">
    <div class="flex justify-between">
        <div></div>
        <button type="submit" 
                class="bg-red-500 text-white px-6 py-2 rounded-lg hover:bg-red-600 transition-colors <?= !$all_passed ? 'opacity-50 cursor-not-allowed' : '' ?>"
                <?= !$all_passed ? 'disabled' : '' ?>>
            Continue to Database Setup
        </button>
    </div>
</form>

<div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
    <h4 class="font-semibold text-blue-800 mb-2">Hosting Environment Notes</h4>
    <div class="text-sm text-blue-700 space-y-2">
        <p><strong>Shared Hosting:</strong> Most requirements should be met by default. Contact your host if extensions are missing.</p>
        <p><strong>VPS/Cloud:</strong> You may need to install PHP extensions manually using your package manager.</p>
        <p><strong>Local Development:</strong> Use XAMPP, WAMP, or similar for easy setup.</p>
    </div>
</div>
