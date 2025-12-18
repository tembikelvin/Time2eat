<?php
/**
 * Admin Database Backups Management
 * Create, download, and restore database backups
 */

$title = $title ?? 'Database Backups - Time2Eat Admin';
$user = $user ?? [];
$backups = $backups ?? [];
$backupSettings = $backupSettings ?? [];

// Calculate backup statistics
$totalBackups = count($backups);
$totalSize = 0;
$latestBackup = null;
foreach ($backups as $backup) {
    $totalSize += $backup['size_bytes'] ?? 0;
    if (!$latestBackup || strtotime($backup['created_at']) > strtotime($latestBackup['created_at'])) {
        $latestBackup = $backup;
    }
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Database Backups</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Create, manage, and restore database backups
            </p>
        </div>
        <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-3">
            <button id="createBackup" 
                    class="tw-bg-blue-600 tw-border tw-border-transparent tw-rounded-lg tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-white hover:tw-bg-blue-700 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                <i data-feather="database" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Create Backup
            </button>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <!-- Total Backups -->
    <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
        <div class="tw-flex tw-items-center">
            <div class="tw-flex-shrink-0">
                <div class="tw-w-8 tw-h-8 tw-bg-blue-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                    <i data-feather="database" class="tw-h-4 tw-w-4 tw-text-blue-600"></i>
                </div>
            </div>
            <div class="tw-ml-4">
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Backups</p>
                <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= number_format($totalBackups) ?></p>
            </div>
        </div>
    </div>

    <!-- Total Size -->
    <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
        <div class="tw-flex tw-items-center">
            <div class="tw-flex-shrink-0">
                <div class="tw-w-8 tw-h-8 tw-bg-green-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                    <i data-feather="hard-drive" class="tw-h-4 tw-w-4 tw-text-green-600"></i>
                </div>
            </div>
            <div class="tw-ml-4">
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Size</p>
                <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= formatBytes($totalSize) ?></p>
            </div>
        </div>
    </div>

    <!-- Auto Backup Status -->
    <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
        <div class="tw-flex tw-items-center">
            <div class="tw-flex-shrink-0">
                <div class="tw-w-8 tw-h-8 tw-bg-purple-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                    <i data-feather="clock" class="tw-h-4 tw-w-4 tw-text-purple-600"></i>
                </div>
            </div>
            <div class="tw-ml-4">
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Auto Backup</p>
                <p class="tw-text-2xl tw-font-semibold tw-text-gray-900">
                    <?= $backupSettings['auto_backup_enabled'] ? 'On' : 'Off' ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Latest Backup -->
    <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
        <div class="tw-flex tw-items-center">
            <div class="tw-flex-shrink-0">
                <div class="tw-w-8 tw-h-8 tw-bg-orange-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                    <i data-feather="calendar" class="tw-h-4 tw-w-4 tw-text-orange-600"></i>
                </div>
            </div>
            <div class="tw-ml-4">
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Latest Backup</p>
                <p class="tw-text-2xl tw-font-semibold tw-text-gray-900">
                    <?= $latestBackup ? date('M j', strtotime($latestBackup['created_at'])) : 'None' ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Backup Settings -->
<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-mb-6">
    <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
        <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Backup Configuration</h3>
        <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
            Current backup settings and preferences
        </p>
    </div>
    <div class="tw-p-6">
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6">
            <div class="tw-bg-gray-50 tw-rounded-lg tw-p-4 tw-text-center">
                <div class="tw-text-2xl tw-font-bold tw-text-blue-600 tw-mb-1">
                    <?= $backupSettings['auto_backup_enabled'] ? 'Enabled' : 'Disabled' ?>
                </div>
                <div class="tw-text-sm tw-text-gray-600">Auto Backup</div>
            </div>
            <div class="tw-bg-gray-50 tw-rounded-lg tw-p-4 tw-text-center">
                <div class="tw-text-2xl tw-font-bold tw-text-green-600 tw-mb-1">
                    <?= ucfirst($backupSettings['backup_frequency'] ?? 'Daily') ?>
                </div>
                <div class="tw-text-sm tw-text-gray-600">Frequency</div>
            </div>
            <div class="tw-bg-gray-50 tw-rounded-lg tw-p-4 tw-text-center">
                <div class="tw-text-2xl tw-font-bold tw-text-orange-600 tw-mb-1">
                    <?= $backupSettings['backup_retention_days'] ?? 30 ?> Days
                </div>
                <div class="tw-text-sm tw-text-gray-600">Retention</div>
            </div>
        </div>
    </div>
</div>

<!-- Existing Backups -->
<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200">
    <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Existing Backups</h3>
                <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                    Manage your database backup files
                </p>
            </div>
            <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-bg-blue-100 tw-text-blue-800">
                <?= count($backups) ?> Backups
            </span>
        </div>
    </div>
    <div class="tw-p-6">
        <?php if (empty($backups)): ?>
            <div class="tw-text-center tw-py-12">
                <div class="tw-w-16 tw-h-16 tw-mx-auto tw-mb-4 tw-bg-gray-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                    <i data-feather="database" class="tw-h-8 tw-w-8 tw-text-gray-400"></i>
                </div>
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-2">No Backups Found</h3>
                <p class="tw-text-gray-500 tw-mb-4">Create your first database backup to get started.</p>
                <button class="create-backup-btn tw-bg-blue-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg hover:tw-bg-blue-700 tw-transition-colors tw-flex tw-items-center tw-justify-center tw-mx-auto">
                    <i data-feather="database" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                    Create First Backup
                </button>
            </div>
        <?php else: ?>
            <div class="tw-overflow-x-auto">
                <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                    <thead class="tw-bg-gray-50">
                        <tr>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                Backup File
                            </th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                Size
                            </th>
                            <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                Created
                            </th>
                            <th class="tw-px-6 tw-py-3 tw-text-right tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                        <?php foreach ($backups as $backup): ?>
                            <tr class="hover:tw-bg-gray-50">
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                    <div class="tw-flex tw-items-center">
                                        <div class="tw-w-8 tw-h-8 tw-bg-blue-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-mr-3">
                                            <i data-feather="file" class="tw-h-4 tw-w-4 tw-text-blue-600"></i>
                                        </div>
                                        <div>
                                            <div class="tw-text-sm tw-font-medium tw-text-gray-900">
                                                <?= htmlspecialchars($backup['filename']) ?>
                                            </div>
                                            <div class="tw-text-sm tw-text-gray-500">
                                                SQL Database Backup
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900">
                                    <?= $backup['size'] ?>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                                    <?= date('M j, Y g:i A', strtotime($backup['created_at'])) ?>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-right tw-text-sm tw-font-medium">
                                    <div class="tw-flex tw-justify-end tw-space-x-2">
                                        <button class="download-backup tw-text-blue-600 hover:tw-text-blue-900 tw-p-2 tw-rounded-lg hover:tw-bg-blue-50 tw-transition-colors"
                                                data-filename="<?= htmlspecialchars($backup['filename']) ?>"
                                                title="Download">
                                            <i data-feather="download" class="tw-h-4 tw-w-4"></i>
                                        </button>
                                        <button class="restore-backup tw-text-green-600 hover:tw-text-green-900 tw-p-2 tw-rounded-lg hover:tw-bg-green-50 tw-transition-colors"
                                                data-filename="<?= htmlspecialchars($backup['filename']) ?>"
                                                title="Restore">
                                            <i data-feather="refresh-cw" class="tw-h-4 tw-w-4"></i>
                                        </button>
                                        <button class="delete-backup tw-text-red-600 hover:tw-text-red-900 tw-p-2 tw-rounded-lg hover:tw-bg-red-50 tw-transition-colors"
                                                data-filename="<?= htmlspecialchars($backup['filename']) ?>"
                                                title="Delete">
                                            <i data-feather="trash-2" class="tw-h-4 tw-w-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Backup Instructions -->
<div class="tw-bg-blue-50 tw-border tw-border-blue-200 tw-rounded-lg tw-p-6 tw-mt-6">
    <div class="tw-flex">
        <div class="tw-w-8 tw-h-8 tw-bg-blue-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-mr-3 tw-flex-shrink-0">
            <i data-feather="info" class="tw-h-4 tw-w-4 tw-text-blue-600"></i>
        </div>
        <div>
            <h3 class="tw-text-sm tw-font-medium tw-text-blue-800">Backup Information</h3>
            <div class="tw-mt-2 tw-text-sm tw-text-blue-700">
                <ul class="tw-list-disc tw-list-inside tw-space-y-1">
                    <li>Backups are stored in the <code class="tw-bg-blue-100 tw-px-1 tw-rounded">storage/backups</code> directory</li>
                    <li>Manual backups can be created at any time using the "Create Backup" button</li>
                    <li>Automatic backups run based on your configured schedule</li>
                    <li>Restore operations will replace the current database - use with caution</li>
                    <li>Always test restored backups in a development environment first</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-96 tw-shadow-lg tw-rounded-md tw-bg-white">
        <div class="tw-mt-3">
            <div class="tw-flex tw-items-center tw-mb-4">
                <i data-feather="alert-triangle" class="tw-h-6 tw-w-6 tw-text-yellow-500 tw-mr-3"></i>
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900" id="modalTitle">Confirm Action</h3>
            </div>
            <p class="tw-text-sm tw-text-gray-600 tw-mb-4" id="modalMessage">Are you sure you want to proceed?</p>
            <div class="tw-flex tw-justify-end tw-space-x-3">
                <button id="cancelAction" class="tw-px-4 tw-py-2 tw-bg-gray-300 tw-text-gray-700 tw-rounded-lg hover:tw-bg-gray-400">
                    Cancel
                </button>
                <button id="confirmAction" class="tw-px-4 tw-py-2 tw-bg-red-600 tw-text-white tw-rounded-lg hover:tw-bg-red-700">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div id="loadingModal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-96 tw-shadow-lg tw-rounded-md tw-bg-white">
        <div class="tw-text-center">
            <div class="tw-animate-spin tw-rounded-full tw-h-12 tw-w-12 tw-border-b-2 tw-border-blue-600 tw-mx-auto tw-mb-4"></div>
            <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-2" id="loadingTitle">Processing...</h3>
            <p class="tw-text-sm tw-text-gray-600" id="loadingMessage">Please wait while we process your request.</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentAction = null;
    let currentFilename = null;

    // Create backup handlers
    document.querySelectorAll('#createBackup, .create-backup-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            createBackup();
        });
    });

    // Download backup handlers
    document.querySelectorAll('.download-backup').forEach(btn => {
        btn.addEventListener('click', function() {
            const filename = this.dataset.filename;
            downloadBackup(filename);
        });
    });

    // Restore backup handlers
    document.querySelectorAll('.restore-backup').forEach(btn => {
        btn.addEventListener('click', function() {
            const filename = this.dataset.filename;
            showConfirmation(
                'Restore Database',
                `Are you sure you want to restore the database from "${filename}"? This will replace all current data and cannot be undone.`,
                'restore_backup',
                filename
            );
        });
    });

    // Delete backup handlers
    document.querySelectorAll('.delete-backup').forEach(btn => {
        btn.addEventListener('click', function() {
            const filename = this.dataset.filename;
            showConfirmation(
                'Delete Backup',
                `Are you sure you want to delete the backup file "${filename}"? This action cannot be undone.`,
                'delete_backup',
                filename
            );
        });
    });

    // Modal handlers
    document.getElementById('cancelAction').addEventListener('click', function() {
        hideConfirmation();
    });

    document.getElementById('confirmAction').addEventListener('click', function() {
        if (currentAction && currentFilename) {
            executeAction(currentAction, currentFilename);
        }
        hideConfirmation();
    });

    function createBackup() {
        showLoading('Creating Backup', 'Please wait while we create a database backup...');
        
        const formData = new FormData();
        formData.append('action', 'create_backup');

        fetch('<?= url('/admin/tools/backups') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            hideLoading();
            if (data.success) {
                showSuccess('Backup created successfully!');
                setTimeout(() => location.reload(), 1500);
            } else {
                showError('Failed to create backup: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showError('An error occurred while creating the backup: ' + error.message);
        });
    }

    function downloadBackup(filename) {
        const formData = new FormData();
        formData.append('action', 'download_backup');
        formData.append('filename', filename);

        // Create a temporary form to trigger download
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/tools/backups';
        form.style.display = 'none';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'download_backup';

        const filenameInput = document.createElement('input');
        filenameInput.type = 'hidden';
        filenameInput.name = 'filename';
        filenameInput.value = filename;

        form.appendChild(actionInput);
        form.appendChild(filenameInput);
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    function executeAction(action, filename) {
        const loadingTitles = {
            'restore_backup': 'Restoring Database',
            'delete_backup': 'Deleting Backup'
        };

        const loadingMessages = {
            'restore_backup': 'Please wait while we restore the database...',
            'delete_backup': 'Please wait while we delete the backup file...'
        };

        showLoading(loadingTitles[action] || 'Processing', loadingMessages[action] || 'Please wait...');

        const formData = new FormData();
        formData.append('action', action);
        formData.append('filename', filename);

        fetch('<?= url('/admin/tools/backups') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showSuccess(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showError('Failed: ' + data.message);
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showError('An error occurred while processing the request.');
        });
    }

    function showConfirmation(title, message, action, filename) {
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalMessage').textContent = message;
        document.getElementById('confirmationModal').classList.remove('tw-hidden');
        currentAction = action;
        currentFilename = filename;
    }

    function hideConfirmation() {
        document.getElementById('confirmationModal').classList.add('tw-hidden');
        currentAction = null;
        currentFilename = null;
    }

    function showLoading(title, message) {
        document.getElementById('loadingTitle').textContent = title;
        document.getElementById('loadingMessage').textContent = message;
        document.getElementById('loadingModal').classList.remove('tw-hidden');
    }

    function hideLoading() {
        document.getElementById('loadingModal').classList.add('tw-hidden');
    }

    function showSuccess(message) {
        // Create a temporary success notification
        const notification = document.createElement('div');
        notification.className = 'tw-fixed tw-top-4 tw-right-4 tw-bg-green-500 tw-text-white tw-px-6 tw-py-3 tw-rounded-lg tw-shadow-lg tw-z-50 tw-flex tw-items-center tw-space-x-2';
        notification.innerHTML = `
            <i data-feather="check-circle" class="tw-h-5 tw-w-5"></i>
            <span>${message}</span>
        `;
        document.body.appendChild(notification);
        
        // Initialize feather icons for the notification
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
        
        // Remove notification after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }

    function showError(message) {
        // Create a temporary error notification
        const notification = document.createElement('div');
        notification.className = 'tw-fixed tw-top-4 tw-right-4 tw-bg-red-500 tw-text-white tw-px-6 tw-py-3 tw-rounded-lg tw-shadow-lg tw-z-50 tw-flex tw-items-center tw-space-x-2';
        notification.innerHTML = `
            <i data-feather="alert-circle" class="tw-h-5 tw-w-5"></i>
            <span>${message}</span>
        `;
        document.body.appendChild(notification);
        
        // Initialize feather icons for the notification
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
        
        // Remove notification after 7 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 7000);
    }
});
</script>


