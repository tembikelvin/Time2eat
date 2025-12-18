<?php
/**
 * Admin Notifications Page
 * Consistent layout with other admin pages
 */

$title = $title ?? 'Notifications - Time2Eat Admin';
$notifications = $notifications ?? [];
$stats = $stats ?? ['total' => 0, 'unread' => 0, 'urgent_unread' => 0, 'order_updates' => 0, 'system_alerts' => 0];
$recentActivity = $recentActivity ?? [];
$error = $error ?? null;

?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Notifications</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Manage and monitor system notifications and alerts
            </p>
        </div>
        <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-3">
            <?php if ($stats['unread'] > 0): ?>
                <button id="markAllRead" 
                        class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                    <i data-feather="check" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                    Mark All Read
                </button>
            <?php endif; ?>
            <button id="refreshNotifications" 
                    class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                <i data-feather="refresh-cw" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Refresh
            </button>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <!-- Total Notifications -->
    <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
        <div class="tw-flex tw-items-center">
            <div class="tw-flex-shrink-0">
                <div class="tw-w-8 tw-h-8 tw-bg-blue-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                    <i data-feather="bell" class="tw-h-4 tw-w-4 tw-text-blue-600"></i>
                </div>
            </div>
            <div class="tw-ml-4">
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Notifications</p>
                <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= number_format($stats['total']) ?></p>
            </div>
        </div>
    </div>

    <!-- Unread Notifications -->
    <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
        <div class="tw-flex tw-items-center">
            <div class="tw-flex-shrink-0">
                <div class="tw-w-8 tw-h-8 tw-bg-orange-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                    <i data-feather="mail" class="tw-h-4 tw-w-4 tw-text-orange-600"></i>
                </div>
            </div>
            <div class="tw-ml-4">
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Unread</p>
                <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= number_format($stats['unread']) ?></p>
            </div>
        </div>
    </div>

    <!-- Urgent Notifications -->
    <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
        <div class="tw-flex tw-items-center">
            <div class="tw-flex-shrink-0">
                <div class="tw-w-8 tw-h-8 tw-bg-red-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                    <i data-feather="alert-triangle" class="tw-h-4 tw-w-4 tw-text-red-600"></i>
                </div>
            </div>
            <div class="tw-ml-4">
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Urgent</p>
                <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= number_format($stats['urgent_unread']) ?></p>
            </div>
        </div>
    </div>

    <!-- Order Updates -->
    <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
        <div class="tw-flex tw-items-center">
            <div class="tw-flex-shrink-0">
                <div class="tw-w-8 tw-h-8 tw-bg-green-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                    <i data-feather="shopping-bag" class="tw-h-4 tw-w-4 tw-text-green-600"></i>
                </div>
            </div>
            <div class="tw-ml-4">
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Order Updates</p>
                <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= number_format($stats['order_updates']) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-p-6 tw-mb-6">
    <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-4">
        <div class="tw-flex-1">
            <label for="typeFilter" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Filter by Type</label>
            <select id="typeFilter" class="tw-w-full tw-border tw-border-gray-300 tw-rounded-lg tw-px-3 tw-py-2 tw-text-sm tw-text-gray-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-transparent">
                <option value="">All Types</option>
                <option value="order_update">Order Updates</option>
                <option value="system_alert">System Alerts</option>
                <option value="promotion">Promotions</option>
                <option value="user_action">User Actions</option>
            </select>
        </div>
        <div class="tw-flex-1">
            <label for="statusFilter" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Filter by Status</label>
            <select id="statusFilter" class="tw-w-full tw-border tw-border-gray-300 tw-rounded-lg tw-px-3 tw-py-2 tw-text-sm tw-text-gray-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-transparent">
                <option value="">All Status</option>
                <option value="unread">Unread Only</option>
                <option value="read">Read Only</option>
            </select>
        </div>
        <div class="tw-flex-1">
            <label for="priorityFilter" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Filter by Priority</label>
            <select id="priorityFilter" class="tw-w-full tw-border tw-border-gray-300 tw-rounded-lg tw-px-3 tw-py-2 tw-text-sm tw-text-gray-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-transparent">
                <option value="">All Priorities</option>
                <option value="high">High Priority</option>
                <option value="medium">Medium Priority</option>
                <option value="low">Low Priority</option>
            </select>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200">
    <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
        <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Notifications</h3>
        <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
            Manage and monitor all system notifications
        </p>
    </div>
    
    <div class="tw-p-6">
        <?php if (!empty($notifications)): ?>
            <div class="tw-space-y-4">
                <?php foreach ($notifications as $notification): ?>
                    <div class="tw-bg-gray-50 tw-rounded-lg tw-p-4 tw-transition-all tw-duration-200 hover:tw-shadow-md <?= $notification['read_at'] ? 'tw-opacity-75' : 'tw-border-l-4 tw-border-l-blue-500 tw-bg-blue-50' ?>" 
                         data-notification 
                         data-type="<?= htmlspecialchars($notification['type']) ?>" 
                         data-read="<?= $notification['read_at'] ? 'true' : 'false' ?>" 
                         data-priority="<?= htmlspecialchars($notification['priority']) ?>">
                        <div class="tw-flex tw-items-start tw-space-x-4">
                            <!-- Notification Icon -->
                            <div class="tw-flex-shrink-0">
                                <?php
                                $iconClass = match($notification['type']) {
                                    'order_update' => 'tw-text-green-600 tw-bg-green-100',
                                    'system_alert' => 'tw-text-red-600 tw-bg-red-100',
                                    'promotion' => 'tw-text-purple-600 tw-bg-purple-100',
                                    'user_action' => 'tw-text-blue-600 tw-bg-blue-100',
                                    default => 'tw-text-gray-600 tw-bg-gray-100'
                                };
                                $icon = match($notification['type']) {
                                    'order_update' => 'shopping-bag',
                                    'system_alert' => 'alert-triangle',
                                    'promotion' => 'gift',
                                    'user_action' => 'user',
                                    default => 'bell'
                                };
                                ?>
                                <div class="tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center <?= $iconClass ?>">
                                    <i data-feather="<?= $icon ?>" class="tw-h-5 tw-w-5"></i>
                                </div>
                            </div>

                            <!-- Notification Content -->
                            <div class="tw-flex-1 tw-min-w-0">
                                <div class="tw-flex tw-items-start tw-justify-between">
                                    <div class="tw-flex-1">
                                        <div class="tw-flex tw-items-center tw-space-x-2 tw-mb-2">
                                            <h4 class="tw-text-sm tw-font-semibold tw-text-gray-900 tw-truncate">
                                                <?= htmlspecialchars($notification['title']) ?>
                                            </h4>
                                            <?php if (!$notification['read_at']): ?>
                                                <div class="tw-w-2 tw-h-2 tw-bg-blue-500 tw-rounded-full tw-flex-shrink-0"></div>
                                            <?php endif; ?>
                                            <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-gray-100 tw-text-gray-800">
                                                <?= ucfirst($notification['priority']) ?>
                                            </span>
                                        </div>
                                        
                                        <p class="tw-text-sm tw-text-gray-600 tw-mb-3 tw-line-clamp-2">
                                            <?= htmlspecialchars($notification['message']) ?>
                                        </p>
                                        
                                        <div class="tw-flex tw-items-center tw-justify-between tw-text-xs tw-text-gray-500">
                                            <div class="tw-flex tw-items-center tw-space-x-3">
                                                <span><?= date('M j, g:i A', strtotime($notification['created_at'])) ?></span>
                                                <?php if ($notification['created_by_name']): ?>
                                                    <span>•</span>
                                                    <span>by <?= htmlspecialchars($notification['created_by_name']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="tw-flex tw-items-center tw-space-x-2">
                                                <?php if (!$notification['read_at']): ?>
                                                    <button onclick="markAsRead(<?= $notification['id'] ?>)" 
                                                            class="tw-px-3 tw-py-1 tw-text-xs tw-text-blue-600 tw-bg-blue-50 tw-rounded-lg hover:tw-bg-blue-100 tw-transition-colors">
                                                        Mark Read
                                                    </button>
                                                <?php endif; ?>
                                                <button onclick="deleteNotification(<?= $notification['id'] ?>)" 
                                                        class="tw-px-3 tw-py-1 tw-text-xs tw-text-red-600 tw-bg-red-50 tw-rounded-lg hover:tw-bg-red-100 tw-transition-colors">
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="tw-text-center tw-py-12">
                <div class="tw-w-16 tw-h-16 tw-mx-auto tw-mb-4 tw-bg-gray-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                    <i data-feather="bell-off" class="tw-h-8 tw-w-8 tw-text-gray-400"></i>
                </div>
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-2">No Notifications</h3>
                <p class="tw-text-gray-500 tw-mb-4">You're all caught up! New notifications will appear here.</p>
                <button onclick="location.reload()" class="tw-px-4 tw-py-2 tw-bg-blue-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-blue-700 tw-transition-colors">
                    Refresh
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>


<!-- Loading Overlay -->
<div id="loadingOverlay" class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-z-50 tw-hidden">
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-flex tw-items-center tw-space-x-3">
        <div class="tw-animate-spin tw-rounded-full tw-h-6 tw-w-6 tw-border-b-2 tw-border-blue-600"></div>
        <span class="tw-text-gray-700">Processing...</span>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark all as read
    document.getElementById('markAllRead')?.addEventListener('click', function() {
        if (confirm('Mark all notifications as read?')) {
            markAllAsRead();
        }
    });

    // Refresh notifications
    document.getElementById('refreshNotifications')?.addEventListener('click', function() {
        location.reload();
    });

    // Filter functionality
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const priorityFilter = document.getElementById('priorityFilter');

    function applyFilters() {
        const notifications = document.querySelectorAll('[data-notification]');
        const typeValue = typeFilter.value;
        const statusValue = statusFilter.value;
        const priorityValue = priorityFilter.value;

        notifications.forEach(notification => {
            const type = notification.dataset.type;
            const isRead = notification.dataset.read === 'true';
            const priority = notification.dataset.priority;

            let show = true;

            if (typeValue && type !== typeValue) show = false;
            if (statusValue === 'read' && !isRead) show = false;
            if (statusValue === 'unread' && isRead) show = false;
            if (priorityValue && priority !== priorityValue) show = false;

            notification.style.display = show ? 'block' : 'none';
        });
    }

    // Add event listeners for filters
    [typeFilter, statusFilter, priorityFilter].forEach(filter => {
        filter?.addEventListener('change', applyFilters);
    });

    // Auto-refresh every 30 seconds
    setInterval(function() {
        location.reload();
    }, 30000);
});

function showLoading() {
    document.getElementById('loadingOverlay').classList.remove('tw-hidden');
}

function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('tw-hidden');
}

function markAsRead(notificationId) {
    showLoading();
    
    fetch('<?= url('/admin/notifications/mark-read') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'notification_id=' + notificationId
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to mark notification as read'));
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        alert('An error occurred while marking notification as read');
    });
}

function markAllAsRead() {
    showLoading();
    
    fetch('<?= url('/admin/notifications/mark-all-read') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to mark all notifications as read'));
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        alert('An error occurred while marking all notifications as read');
    });
}

function deleteNotification(notificationId) {
    if (!confirm('Are you sure you want to delete this notification?')) {
        return;
    }
    
    showLoading();
    
    fetch('<?= url('/admin/notifications/delete') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'notification_id=' + notificationId
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to delete notification'));
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        alert('An error occurred while deleting notification');
    });
}
</script>

<style>
.tw-line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
