<?php
/**
 * Admin Popup Notifications Management
 * Create, schedule, and manage popup notifications
 */

$title = $title ?? 'Popup Notifications - Time2Eat Admin';
$user = $user ?? [];
$activeNotifications = $activeNotifications ?? [];
$scheduledNotifications = $scheduledNotifications ?? [];
$notificationStats = $notificationStats ?? [];

?>

<div class="tw-min-h-screen tw-bg-gray-50">
    <!-- Header -->
    <div class="tw-bg-white tw-shadow-sm tw-border-b tw-border-gray-200">
        <div class="tw-max-w-7xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8">
            <div class="tw-flex tw-justify-between tw-items-center tw-py-6">
                <div>
                    <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900">Popup Notifications</h1>
                    <p class="tw-mt-1 tw-text-sm tw-text-gray-500">Create and manage user notifications and announcements</p>
                </div>
                <div class="tw-flex tw-space-x-3">
                    <button id="createNotification" class="tw-bg-blue-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg hover:tw-bg-blue-700 tw-transition-colors tw-flex tw-items-center tw-space-x-2">
                        <i data-feather="plus" class="tw-h-4 tw-w-4"></i>
                        <span>Create Notification</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="tw-max-w-7xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8 tw-py-8">
        <!-- Statistics Cards -->
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
            <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Notifications</p>
                        <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($notificationStats['total_notifications'] ?? 0) ?></p>
                    </div>
                    <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                        <i data-feather="bell" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Active</p>
                        <p class="tw-text-3xl tw-font-bold tw-text-green-600"><?= number_format($notificationStats['active_notifications'] ?? 0) ?></p>
                    </div>
                    <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                        <i data-feather="check-circle" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Dismissed</p>
                        <p class="tw-text-3xl tw-font-bold tw-text-gray-600"><?= number_format($notificationStats['dismissed_notifications'] ?? 0) ?></p>
                    </div>
                    <div class="tw-p-3 tw-bg-gray-100 tw-rounded-full">
                        <i data-feather="x-circle" class="tw-h-6 tw-w-6 tw-text-gray-600"></i>
                    </div>
                </div>
            </div>

            <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Urgent</p>
                        <p class="tw-text-3xl tw-font-bold tw-text-red-600"><?= number_format($notificationStats['urgent_notifications'] ?? 0) ?></p>
                    </div>
                    <div class="tw-p-3 tw-bg-red-100 tw-rounded-full">
                        <i data-feather="alert-triangle" class="tw-h-6 tw-w-6 tw-text-red-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Notifications -->
        <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-mb-8">
            <div class="tw-p-6 tw-border-b tw-border-gray-200">
                <div class="tw-flex tw-justify-between tw-items-center">
                    <div>
                        <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Active Notifications</h2>
                        <p class="tw-text-sm tw-text-gray-500">Currently visible notifications</p>
                    </div>
                    <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-bg-green-100 tw-text-green-800">
                        <?= count($activeNotifications) ?> Active
                    </span>
                </div>
            </div>
            <div class="tw-p-6">
                <?php if (empty($activeNotifications)): ?>
                    <div class="tw-text-center tw-py-12">
                        <i data-feather="bell-off" class="tw-h-12 tw-w-12 tw-text-gray-400 tw-mx-auto tw-mb-4"></i>
                        <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-2">No Active Notifications</h3>
                        <p class="tw-text-gray-500 tw-mb-4">Create your first notification to engage with users.</p>
                        <button class="create-notification-btn tw-bg-blue-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg hover:tw-bg-blue-700 tw-transition-colors">
                            Create Notification
                        </button>
                    </div>
                <?php else: ?>
                    <div class="tw-space-y-4">
                        <?php foreach ($activeNotifications as $notification): ?>
                            <div class="tw-border tw-border-gray-200 tw-rounded-lg tw-p-4">
                                <div class="tw-flex tw-items-start tw-justify-between">
                                    <div class="tw-flex tw-space-x-4 tw-flex-1">
                                        <div class="tw-flex-shrink-0">
                                            <?php
                                            $iconClass = match($notification['type']) {
                                                'success' => 'tw-text-green-600 tw-bg-green-100',
                                                'warning' => 'tw-text-yellow-600 tw-bg-yellow-100',
                                                'error' => 'tw-text-red-600 tw-bg-red-100',
                                                'promotion' => 'tw-text-purple-600 tw-bg-purple-100',
                                                default => 'tw-text-blue-600 tw-bg-blue-100'
                                            };
                                            $icon = match($notification['type']) {
                                                'success' => 'check-circle',
                                                'warning' => 'alert-triangle',
                                                'error' => 'x-circle',
                                                'promotion' => 'gift',
                                                default => 'info'
                                            };
                                            ?>
                                            <div class="tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center <?= $iconClass ?>">
                                                <i data-feather="<?= $icon ?>" class="tw-h-5 tw-w-5"></i>
                                            </div>
                                        </div>
                                        <div class="tw-flex-1">
                                            <div class="tw-flex tw-items-center tw-space-x-2 tw-mb-2">
                                                <h3 class="tw-font-semibold tw-text-gray-900"><?= htmlspecialchars($notification['title']) ?></h3>
                                                <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-<?= $notification['priority'] === 'urgent' ? 'red' : ($notification['priority'] === 'high' ? 'orange' : 'gray') ?>-100 tw-text-<?= $notification['priority'] === 'urgent' ? 'red' : ($notification['priority'] === 'high' ? 'orange' : 'gray') ?>-800">
                                                    <?= ucfirst($notification['priority']) ?>
                                                </span>
                                            </div>
                                            <p class="tw-text-gray-600 tw-text-sm tw-mb-2"><?= htmlspecialchars($notification['message']) ?></p>
                                            <div class="tw-flex tw-items-center tw-space-x-4 tw-text-xs tw-text-gray-500">
                                                <span>Created: <?= date('M j, Y g:i A', strtotime($notification['created_at'])) ?></span>
                                                <?php if (isset($notification['expires_at']) && $notification['expires_at']): ?>
                                                    <span>Expires: <?= date('M j, Y g:i A', strtotime($notification['expires_at'])) ?></span>
                                                <?php endif; ?>
                                                <span>By: <?= htmlspecialchars(($notification['first_name'] ?? '') . ' ' . ($notification['last_name'] ?? '')) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tw-flex tw-space-x-2">
                                        <button class="edit-notification tw-text-blue-600 hover:tw-text-blue-900 tw-p-2 tw-rounded-lg hover:tw-bg-blue-50"
                                                data-id="<?= $notification['id'] ?>" title="Edit">
                                            <i data-feather="edit" class="tw-h-4 tw-w-4"></i>
                                        </button>
                                        <button class="delete-notification tw-text-red-600 hover:tw-text-red-900 tw-p-2 tw-rounded-lg hover:tw-bg-red-50"
                                                data-id="<?= $notification['id'] ?>" title="Delete">
                                            <i data-feather="trash-2" class="tw-h-4 tw-w-4"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Scheduled Notifications -->
        <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
            <div class="tw-p-6 tw-border-b tw-border-gray-200">
                <div class="tw-flex tw-justify-between tw-items-center">
                    <div>
                        <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Scheduled Notifications</h2>
                        <p class="tw-text-sm tw-text-gray-500">Notifications scheduled for future delivery</p>
                    </div>
                    <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-bg-orange-100 tw-text-orange-800">
                        <?= count($scheduledNotifications) ?> Scheduled
                    </span>
                </div>
            </div>
            <div class="tw-p-6">
                <?php if (empty($scheduledNotifications)): ?>
                    <div class="tw-text-center tw-py-8">
                        <i data-feather="clock" class="tw-h-8 tw-w-8 tw-text-gray-400 tw-mx-auto tw-mb-2"></i>
                        <p class="tw-text-gray-500">No scheduled notifications</p>
                    </div>
                <?php else: ?>
                    <div class="tw-space-y-3">
                        <?php foreach ($scheduledNotifications as $notification): ?>
                            <div class="tw-border tw-border-gray-200 tw-rounded-lg tw-p-3">
                                <div class="tw-flex tw-items-center tw-justify-between">
                                    <div>
                                        <h4 class="tw-font-medium tw-text-gray-900"><?= htmlspecialchars($notification['title']) ?></h4>
                                        <p class="tw-text-sm tw-text-gray-600"><?= htmlspecialchars(substr($notification['message'], 0, 100)) ?>...</p>
                                        <p class="tw-text-xs tw-text-gray-500 tw-mt-1">
                                            Scheduled for: <?= isset($notification['expires_at']) && $notification['expires_at'] ? date('M j, Y g:i A', strtotime($notification['expires_at'])) : 'Not set' ?>
                                        </p>
                                    </div>
                                    <div class="tw-flex tw-space-x-2">
                                        <button class="edit-notification tw-text-blue-600 hover:tw-text-blue-900 tw-p-1 tw-rounded"
                                                data-id="<?= $notification['id'] ?>">
                                            <i data-feather="edit" class="tw-h-4 tw-w-4"></i>
                                        </button>
                                        <button class="delete-notification tw-text-red-600 hover:tw-text-red-900 tw-p-1 tw-rounded"
                                                data-id="<?= $notification['id'] ?>">
                                            <i data-feather="trash-2" class="tw-h-4 tw-w-4"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Notification Modal -->
<div id="notificationModal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden">
    <div class="tw-relative tw-top-10 tw-mx-auto tw-p-5 tw-border tw-w-full tw-max-w-2xl tw-shadow-lg tw-rounded-md tw-bg-white">
        <div class="tw-mt-3">
            <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-4" id="modalTitle">Create Notification</h3>
            <form id="notificationForm" class="tw-space-y-4">
                <input type="hidden" id="notificationId" name="notification_id">
                
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Title</label>
                        <input type="text" id="notificationTitle" name="title" required
                               class="tw-w-full tw-p-3 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-blue-500 focus:tw-border-blue-500">
                    </div>
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Type</label>
                        <select id="notificationType" name="type" required
                                class="tw-w-full tw-p-3 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-blue-500 focus:tw-border-blue-500">
                            <option value="info">Info</option>
                            <option value="success">Success</option>
                            <option value="warning">Warning</option>
                            <option value="error">Error</option>
                            <option value="promotion">Promotion</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Message</label>
                    <textarea id="notificationMessage" name="message" required rows="3"
                              class="tw-w-full tw-p-3 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-blue-500 focus:tw-border-blue-500"></textarea>
                </div>

                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Target</label>
                        <select id="notificationTarget" name="target" required
                                class="tw-w-full tw-p-3 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-blue-500 focus:tw-border-blue-500">
                            <option value="all">All Users</option>
                            <option value="customer">Customers</option>
                            <option value="vendor">Vendors</option>
                            <option value="rider">Riders</option>
                        </select>
                    </div>
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Priority</label>
                        <select id="notificationPriority" name="priority" required
                                class="tw-w-full tw-p-3 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-blue-500 focus:tw-border-blue-500">
                            <option value="low">Low</option>
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>

                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Action URL (Optional)</label>
                        <input type="url" id="notificationActionUrl" name="action_url"
                               class="tw-w-full tw-p-3 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-blue-500 focus:tw-border-blue-500">
                    </div>
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Action Text (Optional)</label>
                        <input type="text" id="notificationActionText" name="action_text"
                               class="tw-w-full tw-p-3 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-blue-500 focus:tw-border-blue-500">
                    </div>
                </div>

                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Expires At (Optional)</label>
                    <input type="datetime-local" id="notificationExpiresAt" name="expires_at"
                           class="tw-w-full tw-p-3 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-blue-500 focus:tw-border-blue-500">
                </div>

                <div class="tw-flex tw-justify-end tw-space-x-3 tw-pt-4">
                    <button type="button" id="cancelNotification" class="tw-px-4 tw-py-2 tw-bg-gray-300 tw-text-gray-700 tw-rounded-lg hover:tw-bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit" class="tw-px-4 tw-py-2 tw-bg-blue-600 tw-text-white tw-rounded-lg hover:tw-bg-blue-700">
                        Save Notification
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Create notification handlers
    document.querySelectorAll('#createNotification, .create-notification-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            openNotificationModal();
        });
    });

    // Edit notification handlers
    document.querySelectorAll('.edit-notification').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            editNotification(id);
        });
    });

    // Delete notification handlers
    document.querySelectorAll('.delete-notification').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            if (confirm('Are you sure you want to delete this notification?')) {
                deleteNotification(id);
            }
        });
    });

    // Modal handlers
    document.getElementById('cancelNotification').addEventListener('click', function() {
        closeNotificationModal();
    });

    document.getElementById('notificationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveNotification();
    });

    function openNotificationModal(notification = null) {
        if (notification) {
            document.getElementById('modalTitle').textContent = 'Edit Notification';
            document.getElementById('notificationId').value = notification.id;
            document.getElementById('notificationTitle').value = notification.title;
            document.getElementById('notificationMessage').value = notification.message;
            document.getElementById('notificationType').value = notification.type;
            document.getElementById('notificationTarget').value = notification.target;
            document.getElementById('notificationPriority').value = notification.priority;
            document.getElementById('notificationActionUrl').value = notification.action_url || '';
            document.getElementById('notificationActionText').value = notification.action_text || '';
            document.getElementById('notificationExpiresAt').value = notification.expires_at ? notification.expires_at.slice(0, 16) : '';
        } else {
            document.getElementById('modalTitle').textContent = 'Create Notification';
            document.getElementById('notificationForm').reset();
            document.getElementById('notificationId').value = '';
        }
        
        document.getElementById('notificationModal').classList.remove('tw-hidden');
    }

    function closeNotificationModal() {
        document.getElementById('notificationModal').classList.add('tw-hidden');
        document.getElementById('notificationForm').reset();
    }

    function saveNotification() {
        const formData = new FormData(document.getElementById('notificationForm'));
        const isEdit = document.getElementById('notificationId').value;
        formData.append('action', isEdit ? 'update_notification' : 'create_notification');

        fetch('<?= url('/admin/tools/notifications') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeNotificationModal();
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to save notification'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving the notification.');
        });
    }

    function editNotification(id) {
        // In a real implementation, you would fetch the notification data
        // For now, we'll just open the modal
        openNotificationModal();
    }

    function deleteNotification(id) {
        if (!confirm('Are you sure you want to delete this notification?')) {
            return;
        }

        const formData = new FormData();
        formData.append('notification_id', id);

        fetch('<?= url('/admin/tools/notifications/delete') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete notification'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the notification.');
        });
    }

    function toggleNotification(id) {
        const formData = new FormData();
        formData.append('notification_id', id);

        fetch('<?= url('/admin/tools/notifications/toggle') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to update notification'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the notification.');
        });
    }

    function createNotification() {
        const formData = new FormData();
        formData.append('title', document.getElementById('notificationTitle').value);
        formData.append('message', document.getElementById('notificationMessage').value);
        formData.append('type', document.getElementById('notificationType').value);
        formData.append('target_audience', document.getElementById('targetAudience').value);
        formData.append('priority', document.getElementById('priority').value);
        formData.append('start_date', document.getElementById('startDate').value);
        formData.append('end_date', document.getElementById('endDate').value);
        formData.append('action_url', document.getElementById('actionUrl').value);
        formData.append('action_text', document.getElementById('actionText').value);
        formData.append('max_displays', document.getElementById('maxDisplays').value);

        fetch('<?= url('/admin/tools/notifications/create') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to create notification'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while creating the notification.');
        });
    }
});
</script>
