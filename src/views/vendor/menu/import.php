<?php
/**
 * Vendor Menu CSV Import
 * Bulk import menu items from CSV file
 */

// Ensure user is authenticated and has vendor role
if (!isset($user) || $user['role'] !== 'vendor') {
    header('Location: /login');
    exit;
}

$restaurant = $restaurant ?? null;
$categories = $categories ?? [];
$errors = $errors ?? [];
$results = $results ?? null;
?>

<div class="tw-min-h-screen tw-bg-gray-50">
    <!-- Header -->
    <div class="tw-bg-white tw-shadow-sm tw-border-b">
        <div class="tw-max-w-7xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8">
            <div class="tw-flex tw-justify-between tw-items-center tw-py-6">
                <div>
                    <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Import Menu Items</h1>
                    <p class="tw-text-gray-600 tw-mt-1">
                        Bulk import menu items from CSV file - <?= e($restaurant['name'] ?? 'Your Restaurant') ?>
                    </p>
                </div>
                <div class="tw-flex tw-space-x-3">
                    <a href="/eat/vendor/menu/template" class="tw-bg-blue-500 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium tw-hover:tw-bg-blue-600 tw-transition-colors tw-flex tw-items-center">
                        <i data-feather="download" class="tw-w-4 tw-h-4 tw-mr-2"></i>
                        Download Template
                    </a>
                    <a href="/eat/vendor/menu" class="tw-bg-gray-100 tw-text-gray-700 tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium tw-hover:tw-bg-gray-200 tw-transition-colors">
                        Back to Menu
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="tw-max-w-4xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8 tw-py-8">
        <!-- Import Results -->
        <?php if ($results): ?>
            <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tw-mb-8">
                <h2 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-4">Import Results</h2>
                
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-4 tw-mb-6">
                    <div class="tw-bg-blue-50 tw-p-4 tw-rounded-lg">
                        <div class="tw-flex tw-items-center">
                            <i data-feather="file-text" class="tw-w-5 tw-h-5 tw-text-blue-600 tw-mr-2"></i>
                            <div>
                                <p class="tw-text-sm tw-text-blue-600 tw-font-medium">Total Rows</p>
                                <p class="tw-text-2xl tw-font-bold tw-text-blue-900"><?= $results['total'] ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tw-bg-green-50 tw-p-4 tw-rounded-lg">
                        <div class="tw-flex tw-items-center">
                            <i data-feather="check-circle" class="tw-w-5 tw-h-5 tw-text-green-600 tw-mr-2"></i>
                            <div>
                                <p class="tw-text-sm tw-text-green-600 tw-font-medium">Imported</p>
                                <p class="tw-text-2xl tw-font-bold tw-text-green-900"><?= $results['imported'] ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tw-bg-red-50 tw-p-4 tw-rounded-lg">
                        <div class="tw-flex tw-items-center">
                            <i data-feather="x-circle" class="tw-w-5 tw-h-5 tw-text-red-600 tw-mr-2"></i>
                            <div>
                                <p class="tw-text-sm tw-text-red-600 tw-font-medium">Errors</p>
                                <p class="tw-text-2xl tw-font-bold tw-text-red-900"><?= count($results['errors']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($results['errors'])): ?>
                    <div class="tw-border-t tw-pt-4">
                        <h3 class="tw-text-md tw-font-medium tw-text-gray-900 tw-mb-3">Import Errors</h3>
                        <div class="tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-lg tw-p-4 tw-max-h-64 tw-overflow-y-auto">
                            <ul class="tw-space-y-1">
                                <?php foreach ($results['errors'] as $error): ?>
                                    <li class="tw-text-sm tw-text-red-700"><?= e($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="tw-mt-6 tw-flex tw-justify-end">
                    <a href="/eat/vendor/menu" class="tw-bg-orange-500 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium tw-hover:tw-bg-orange-600 tw-transition-colors">
                        View Menu Items
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Instructions -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tw-mb-8">
            <h2 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-4">Import Instructions</h2>
            
            <div class="tw-space-y-4">
                <div class="tw-flex tw-items-start tw-space-x-3">
                    <div class="tw-flex-shrink-0 tw-w-6 tw-h-6 tw-bg-orange-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                        <span class="tw-text-sm tw-font-medium tw-text-orange-600">1</span>
                    </div>
                    <div>
                        <h3 class="tw-text-sm tw-font-medium tw-text-gray-900">Download the CSV template</h3>
                        <p class="tw-text-sm tw-text-gray-600">Use our template to ensure your data is formatted correctly.</p>
                    </div>
                </div>
                
                <div class="tw-flex tw-items-start tw-space-x-3">
                    <div class="tw-flex-shrink-0 tw-w-6 tw-h-6 tw-bg-orange-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                        <span class="tw-text-sm tw-font-medium tw-text-orange-600">2</span>
                    </div>
                    <div>
                        <h3 class="tw-text-sm tw-font-medium tw-text-gray-900">Fill in your menu items</h3>
                        <p class="tw-text-sm tw-text-gray-600">Add your menu items following the template format. All required fields must be filled.</p>
                    </div>
                </div>
                
                <div class="tw-flex tw-items-start tw-space-x-3">
                    <div class="tw-flex-shrink-0 tw-w-6 tw-h-6 tw-bg-orange-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                        <span class="tw-text-sm tw-font-medium tw-text-orange-600">3</span>
                    </div>
                    <div>
                        <h3 class="tw-text-sm tw-font-medium tw-text-gray-900">Upload your CSV file</h3>
                        <p class="tw-text-sm tw-text-gray-600">Select your completed CSV file and click import to add items to your menu.</p>
                    </div>
                </div>
            </div>

            <div class="tw-mt-6 tw-bg-yellow-50 tw-border tw-border-yellow-200 tw-rounded-lg tw-p-4">
                <div class="tw-flex">
                    <i data-feather="alert-triangle" class="tw-w-5 tw-h-5 tw-text-yellow-600 tw-mr-2 tw-flex-shrink-0 tw-mt-0.5"></i>
                    <div>
                        <h3 class="tw-text-sm tw-font-medium tw-text-yellow-800">Important Notes</h3>
                        <ul class="tw-mt-2 tw-text-sm tw-text-yellow-700 tw-space-y-1">
                            <li>• Categories will be created automatically if they don't exist</li>
                            <li>• Prices should be in XAF (no currency symbol needed)</li>
                            <li>• Boolean fields accept: 1/true/yes/y for true, anything else for false</li>
                            <li>• Customization options should be comma-separated</li>
                            <li>• Maximum file size: 5MB</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Required Fields Reference -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tw-mb-8">
            <h2 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-4">CSV Format Reference</h2>
            
            <div class="tw-overflow-x-auto">
                <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                    <thead class="tw-bg-gray-50">
                        <tr>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Field</th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Required</th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Format</th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Example</th>
                        </tr>
                    </thead>
                    <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                        <tr>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium tw-text-gray-900">name</td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-red-600">Yes</td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">Text</td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">Jollof Rice</td>
                        </tr>
                        <tr>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium tw-text-gray-900">description</td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-red-600">Yes</td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">Text</td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">Delicious Nigerian jollof rice</td>
                        </tr>
                        <tr>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium tw-text-gray-900">price</td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-red-600">Yes</td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">Number</td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">2500</td>
                        </tr>
                        <tr>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium tw-text-gray-900">category_name</td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-red-600">Yes</td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">Text</td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">Main Dishes</td>
                        </tr>
                        <tr>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium tw-text-gray-900">stock_quantity</td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">No</td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">Number</td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">50</td>
                        </tr>
                        <tr>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium tw-text-gray-900">is_vegetarian</td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">No</td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">Boolean</td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">yes/no</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6">
            <h2 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-6">Upload CSV File</h2>
            
            <form method="POST" enctype="multipart/form-data" id="importForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                
                <div class="tw-space-y-6">
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Select CSV File</label>
                        <div class="tw-mt-1 tw-flex tw-justify-center tw-px-6 tw-pt-5 tw-pb-6 tw-border-2 tw-border-gray-300 tw-border-dashed tw-rounded-lg">
                            <div class="tw-space-y-1 tw-text-center">
                                <i data-feather="upload" class="tw-mx-auto tw-h-12 tw-w-12 tw-text-gray-400"></i>
                                <div class="tw-flex tw-text-sm tw-text-gray-600">
                                    <label for="csv_file" class="tw-relative tw-cursor-pointer tw-bg-white tw-rounded-md tw-font-medium tw-text-orange-600 tw-hover:tw-text-orange-500 tw-focus-within:tw-outline-none tw-focus-within:tw-ring-2 tw-focus-within:tw-ring-offset-2 tw-focus-within:tw-ring-orange-500">
                                        <span>Upload CSV file</span>
                                        <input id="csv_file" name="csv_file" type="file" accept=".csv" required class="tw-sr-only" onchange="showFileName(this)">
                                    </label>
                                    <p class="tw-pl-1">or drag and drop</p>
                                </div>
                                <p class="tw-text-xs tw-text-gray-500">CSV files up to 5MB</p>
                            </div>
                        </div>
                        <div id="fileName" class="tw-mt-2 tw-text-sm tw-text-gray-600 tw-hidden"></div>
                        <?php if (isset($errors['csv_file'])): ?>
                            <p class="tw-text-red-500 tw-text-sm tw-mt-1"><?= e($errors['csv_file']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="tw-flex tw-justify-end tw-space-x-4">
                        <a href="/eat/vendor/menu" class="tw-px-6 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-font-medium tw-text-gray-700 tw-bg-white tw-hover:tw-bg-gray-50 tw-transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="tw-px-6 tw-py-3 tw-bg-orange-500 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium tw-hover:tw-bg-orange-600 tw-transition-colors tw-flex tw-items-center" id="importBtn">
                            <i data-feather="upload" class="tw-w-4 tw-h-4 tw-mr-2"></i>
                            Import Items
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showFileName(input) {
    const fileName = document.getElementById('fileName');
    if (input.files && input.files[0]) {
        fileName.textContent = `Selected: ${input.files[0].name}`;
        fileName.classList.remove('tw-hidden');
    } else {
        fileName.classList.add('tw-hidden');
    }
}

document.getElementById('importForm').addEventListener('submit', function() {
    const btn = document.getElementById('importBtn');
    btn.innerHTML = '<i data-feather="loader" class="tw-w-4 tw-h-4 tw-mr-2 tw-animate-spin"></i>Importing...';
    btn.disabled = true;
});
</script>
