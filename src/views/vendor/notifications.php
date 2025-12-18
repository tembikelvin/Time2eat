<?php
/**
 * Vendor Notifications Page
 * Mobile-first design for viewing and managing notifications
 */

$title = $title ?? 'Notifications - Time2Eat Vendor';
$notifications = $notifications ?? [];
$stats = $stats ?? ['total' => 0, 'unread' => 0, 'urgent_unread' => 0, 'order_updates' => 0, 'system_alerts' => 0];
$recentActivity = $recentActivity ?? [];
$error = $error ?? null;

?>

<div class="tw-min-h-screen tw-bg-gray-50">
    <!-- Mobile Header -->
    <div class="tw-bg-white tw-shadow-sm tw-border-b tw-border-gray-200 tw-sticky tw-top-0 tw-z-10">
        <div class="tw-px-4 tw-py-4">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <button onclick="history.back()" class="tw-p-2 tw-rounded-lg tw-text-gray-600 hover:tw-bg-gray-100 tw-transition-colors">
                        <i data-feather="arrow-left" class="tw-h-5 tw-w-5"></i>
                    </button>
                    <div>
                        <h1 class="tw-text-xl tw-font-bold tw-text-gray-900">Notifications</h1>
                        <p class="tw-text-sm tw-text-gray-500"><?= number_format($stats['total']) ?> total, <?= number_format($stats['unread']) ?> unread</p>
                    </div>
                </div>
                <div class="tw-flex tw-items-center tw-space-x-2">
                    <?php if ($stats['unread'] > 0): ?>
                        <button id="markAllRead" 
                                class="tw-px-3 tw-py-2 tw-text-xs tw-font-medium tw-text-green-600 tw-bg-green-50 tw-border tw-border-green-200 tw-rounded-lg hover:tw-bg-green-100 tw-transition-colors tw-shadow-sm hover:tw-shadow-md"
                                title="Mark All Read">
                            <i data-feather="check" class="tw-h-4 tw-w-4 sm:tw-mr-1"></i>
                            <span class="tw-hidden sm:tw-inline">Mark All Read</span>
                        </button>
                    <?php endif; ?>
                    <button id="refreshNotifications" 
                            class="tw-p-2 tw-rounded-lg tw-text-gray-600 hover:tw-bg-gray-100 tw-transition-colors tw-border tw-border-gray-200 hover:tw-border-gray-300 tw-shadow-sm hover:tw-shadow-md"
                            title="Refresh Notifications">
                        <i data-feather="refresh-cw" class="tw-h-5 tw-w-5"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards - Mobile Optimized -->
    <div class="tw-px-4 tw-py-4">
        <div class="tw-grid tw-grid-cols-2 tw-gap-3 tw-mb-6">
            <!-- Total Notifications -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-4">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-w-10 tw-h-10 tw-bg-green-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                        <i data-feather="bell" class="tw-h-5 tw-w-5 tw-text-green-600"></i>
                    </div>
                    <div>
                        <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total']) ?></p>
                        <p class="tw-text-sm tw-text-gray-500">Total</p>
                    </div>
                </div>
            </div>

            <!-- Unread Notifications -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-4">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-w-10 tw-h-10 tw-bg-red-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                        <i data-feather="alert-circle" class="tw-h-5 tw-w-5 tw-text-red-600"></i>
                    </div>
                    <div>
                        <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= number_format($stats['unread']) ?></p>
                        <p class="tw-text-sm tw-text-gray-500">Unread</p>
                    </div>
                </div>
            </div>

            <!-- New Orders -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-4">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-w-10 tw-h-10 tw-bg-blue-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                        <i data-feather="shopping-bag" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                    </div>
                    <div>
                        <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= number_format($stats['order_updates']) ?></p>
                        <p class="tw-text-sm tw-text-gray-500">Orders</p>
                    </div>
                </div>
            </div>

            <!-- System Alerts -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-4">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-w-10 tw-h-10 tw-bg-yellow-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                        <i data-feather="alert-triangle" class="tw-h-5 tw-w-5 tw-text-yellow-600"></i>
                    </div>
                    <div>
                        <p class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= number_format($stats['system_alerts']) ?></p>
                        <p class="tw-text-sm tw-text-gray-500">Alerts</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="tw-px-4 tw-mb-4">
        <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-1">
            <div class="tw-flex tw-space-x-1">
                <button onclick="filterNotifications('all')" 
                        class="filter-tab tw-flex-1 tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-rounded-lg tw-transition-colors active">
                    All
                </button>
                <button onclick="filterNotifications('unread')" 
                        class="filter-tab tw-flex-1 tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-rounded-lg tw-transition-colors">
                    Unread
                </button>
                <button onclick="filterNotifications('order_update')" 
                        class="filter-tab tw-flex-1 tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-rounded-lg tw-transition-colors">
                    Orders
                </button>
                <button onclick="filterNotifications('system_alert')" 
                        class="filter-tab tw-flex-1 tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-rounded-lg tw-transition-colors">
                    Alerts
                </button>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="tw-px-4 tw-pb-6">
        <?php if (!empty($notifications)): ?>
            <div class="tw-space-y-3" id="notifications-container">
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-4 tw-transition-all tw-duration-200 hover:tw-shadow-md <?= $notification['read_at'] ? 'tw-opacity-75' : 'tw-border-l-4 tw-border-l-green-500' ?>"
                         data-type="<?= e($notification['type']) ?>" 
                         data-read="<?= $notification['read_at'] ? 'true' : 'false' ?>">
                        <div class="tw-flex tw-items-start tw-space-x-3">
                            <!-- Notification Icon -->
                            <div class="tw-flex-shrink-0">
                                <?php
                                $iconClass = match($notification['type']) {
                                    'order_update' => 'tw-text-blue-600 tw-bg-blue-100',
                                    'system_alert' => 'tw-text-red-600 tw-bg-red-100',
                                    'promotion' => 'tw-text-purple-600 tw-bg-purple-100',
                                    'user_action' => 'tw-text-green-600 tw-bg-green-100',
                                    'review' => 'tw-text-yellow-600 tw-bg-yellow-100',
                                    default => 'tw-text-gray-600 tw-bg-gray-100'
                                };
                                $icon = match($notification['type']) {
                                    'order_update' => 'shopping-bag',
                                    'system_alert' => 'alert-triangle',
                                    'promotion' => 'gift',
                                    'user_action' => 'user',
                                    'review' => 'star',
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
                                        <h3 class="tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-1">
                                            <?= e($notification['title']) ?>
                                        </h3>
                                        <p class="tw-text-sm tw-text-gray-600 tw-mb-2">
                                            <?= e($notification['message']) ?>
                                        </p>
                                        <div class="tw-flex tw-items-center tw-space-x-4 tw-text-xs tw-text-gray-500">
                                            <span class="tw-flex tw-items-center">
                                                <i data-feather="clock" class="tw-h-3 tw-w-3 tw-mr-1"></i>
                                                <?= date('M j, Y g:i A', strtotime($notification['created_at'])) ?>
                                            </span>
                                            <?php if ($notification['priority'] === 'urgent'): ?>
                                                <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-red-100 tw-text-red-800">
                                                    <i data-feather="alert-circle" class="tw-h-3 tw-w-3 tw-mr-1"></i>
                                                    Urgent
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="tw-flex tw-items-center tw-space-x-2 tw-ml-4">
                                        <?php if (!$notification['read_at']): ?>
                                            <button onclick="markAsRead(<?= $notification['id'] ?>)" 
                                                    class="tw-p-1 tw-rounded-full tw-text-gray-400 hover:tw-text-green-600 tw-transition-colors"
                                                    title="Mark as read">
                                                <i data-feather="check" class="tw-h-4 tw-w-4"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button onclick="deleteNotification(<?= $notification['id'] ?>)" 
                                                class="tw-p-1 tw-rounded-full tw-text-gray-400 hover:tw-text-red-600 tw-transition-colors"
                                                title="Delete">
                                            <i data-feather="trash-2" class="tw-h-4 tw-w-4"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Action Button -->
                                <?php if (!empty($notification['action_url']) && !empty($notification['action_text'])): ?>
                                    <div class="tw-mt-3">
                                        <a href="<?= e($notification['action_url']) ?>" 
                                           class="tw-inline-flex tw-items-center tw-px-3 tw-py-2 tw-text-xs tw-font-medium tw-text-green-600 tw-bg-green-50 tw-border tw-border-green-200 tw-rounded-lg hover:tw-bg-green-100 tw-transition-colors">
                                            <?= e($notification['action_text']) ?>
                                            <i data-feather="external-link" class="tw-h-3 tw-w-3 tw-ml-1"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-8 tw-text-center">
                <div class="tw-w-16 tw-h-16 tw-bg-gray-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                    <i data-feather="bell" class="tw-h-8 tw-w-8 tw-text-gray-400"></i>
                </div>
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-2">No notifications yet</h3>
                <p class="tw-text-gray-500 tw-mb-4">You'll see new orders, reviews, and important alerts here.</p>
                <a href="<?= url('/vendor/orders') ?>" 
                   class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-green-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-green-700 tw-transition-colors">
                    <i data-feather="shopping-bag" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                    View Orders
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.filter-tab.active {
    @apply tw-bg-green-600 tw-text-white;
}
.filter-tab:not(.active) {
    @apply tw-text-gray-600 hover:tw-text-gray-900 hover:tw-bg-gray-50;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    feather.replace();
    
    // Mark all as read functionality
    document.getElementById('markAllRead')?.addEventListener('click', function() {
        markAllAsRead();
    });
    
    // Refresh notifications
    document.getElementById('refreshNotifications')?.addEventListener('click', function() {
        location.reload();
    });
});

function filterNotifications(type) {
    const items = document.querySelectorAll('.notification-item');
    const tabs = document.querySelectorAll('.filter-tab');
    
    // Update active tab
    tabs.forEach(tab => tab.classList.remove('active'));
    event.target.classList.add('active');
    
    // Filter notifications
    items.forEach(item => {
        const itemType = item.dataset.type;
        const isRead = item.dataset.read === 'true';
        
        let show = false;
        
        switch(type) {
            case 'all':
                show = true;
                break;
            case 'unread':
                show = !isRead;
                break;
            default:
                show = itemType === type;
        }
        
        item.style.display = show ? 'block' : 'none';
    });
}

async function markAsRead(notificationId) {
    try {
        const response = await fetch('<?= url('/api/notifications/mark-read') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ notification_id: notificationId })
        });
        
        if (response.ok) {
            location.reload();
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
}

async function markAllAsRead() {
    try {
        const response = await fetch('<?= url('/api/notifications/mark-all-read') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        if (response.ok) {
            location.reload();
        }
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    }
}

async function deleteNotification(notificationId) {
    if (!confirm('Are you sure you want to delete this notification?')) {
        return;
    }
    
    try {
        const response = await fetch('<?= url('/api/notifications/delete') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ notification_id: notificationId })
        });
        
        if (response.ok) {
            location.reload();
        }
    } catch (error) {
        console.error('Error deleting notification:', error);
    }
}
</script>
