<?php
/**
 * Vendor Sidebar Navigation
 */

// Get current page from passed variable or detect from URL
$currentPage = $currentPage ?? '';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';

// Remove base path and query parameters for comparison
$currentPath = parse_url($requestUri, PHP_URL_PATH);
$currentPath = str_replace('/eat', '', $currentPath); // Remove base path

// Helper function to check if current page is active
function isActivePage($path, $currentPath, $currentPage = '') {
    // Check exact match first
    if ($currentPath === $path) return true;
    
    // Check if current page variable matches
    if (!empty($currentPage)) {
        if ($path === '/vendor/dashboard' && $currentPage === 'dashboard') return true;
        if (strpos($path, '/vendor/menu') !== false && $currentPage === 'menu') return true;
        if (strpos($path, '/vendor/orders') !== false && $currentPage === 'orders') return true;
        if (strpos($path, '/vendor/analytics') !== false && $currentPage === 'analytics') return true;
        if (strpos($path, '/vendor/earnings') !== false && $currentPage === 'earnings') return true;
        if (strpos($path, '/vendor/reviews') !== false && $currentPage === 'reviews') return true;
        if (strpos($path, '/vendor/messages') !== false && $currentPage === 'messages') return true;
        if (strpos($path, '/vendor/categories') !== false && $currentPage === 'categories') return true;
        if (strpos($path, '/vendor/profile') !== false && $currentPage === 'profile') return true;
        if (strpos($path, '/vendor/settings') !== false && $currentPage === 'settings') return true;
    }
    
    // Check if current path starts with the menu path (for sub-pages)
    if (strpos($path, '/vendor/menu') !== false && strpos($currentPath, '/vendor/menu') !== false) return true;
    if (strpos($path, '/vendor/orders') !== false && strpos($currentPath, '/vendor/orders') !== false) return true;
    
    return false;
}
?>

<!-- Vendor Sidebar -->
<div class="tw-fixed tw-inset-y-0 tw-left-0 tw-z-50 tw-w-64 tw-bg-white tw-shadow-lg tw-transform tw--translate-x-full lg:tw-translate-x-0 tw-transition-transform tw-duration-300 tw-ease-in-out" id="vendor-sidebar">
    <div class="tw-flex tw-items-center tw-justify-between tw-h-16 tw-px-6 tw-bg-orange-600">
        <div class="tw-flex tw-items-center">
            <h1 class="tw-text-xl tw-font-bold tw-text-white">Time2Eat</h1>
            <span class="tw-ml-2 tw-text-xs tw-bg-orange-500 tw-text-white tw-px-2 tw-py-1 tw-rounded">Vendor</span>
        </div>
        <button type="button" class="lg:tw-hidden tw-text-white" onclick="toggleSidebar()">
            <i data-feather="x" class="tw-h-6 tw-w-6"></i>
        </button>
    </div>

    <nav class="tw-mt-8">
        <div class="tw-px-6 tw-mb-6">
            <div class="tw-flex tw-items-center tw-space-x-3">
                <div class="tw-h-10 tw-w-10 tw-bg-gray-300 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                    <span class="tw-text-sm tw-font-medium tw-text-gray-700">
                        <?= strtoupper(substr(($user->first_name ?? $user['first_name'] ?? 'V'), 0, 1)) ?>
                    </span>
                </div>
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-gray-900">
                        <?= e((($user->first_name ?? $user['first_name'] ?? '') . ' ' . ($user->last_name ?? $user['last_name'] ?? ''))) ?>
                    </p>
                    <p class="tw-text-xs tw-text-gray-500"><?= e(($restaurant->name ?? $restaurant['name'] ?? 'Restaurant')) ?></p>
                </div>
            </div>
        </div>

        <div class="tw-space-y-1">
            <!-- Dashboard -->
            <a href="<?= url('/vendor/dashboard') ?>" 
               class="vendor-nav-item tw-flex tw-items-center tw-px-6 tw-py-3 tw-text-sm tw-font-medium tw-transition-colors tw-duration-200 <?= isActivePage('/vendor/dashboard', $currentPath, $currentPage) ? 'tw-bg-orange-50 tw-text-orange-700 tw-border-r-4 tw-border-orange-500 tw-font-semibold' : 'tw-text-gray-700 hover:tw-bg-gray-50 hover:tw-text-gray-900' ?>">
                <i data-feather="home" class="tw-mr-3 tw-h-5 tw-w-5 <?= isActivePage('/vendor/dashboard', $currentPath, $currentPage) ? 'tw-text-orange-600' : '' ?>"></i>
                Dashboard
            </a>

            <!-- Restaurant Profile -->
            <a href="<?= url('/vendor/profile') ?>" 
               class="vendor-nav-item tw-flex tw-items-center tw-px-6 tw-py-3 tw-text-sm tw-font-medium tw-transition-colors tw-duration-200 <?= isActivePage('/vendor/profile', $currentPath, $currentPage) ? 'tw-bg-orange-50 tw-text-orange-700 tw-border-r-4 tw-border-orange-500 tw-font-semibold' : 'tw-text-gray-700 hover:tw-bg-gray-50 hover:tw-text-gray-900' ?>">
                <i data-feather="shopping-bag" class="tw-mr-3 tw-h-5 tw-w-5 <?= isActivePage('/vendor/profile', $currentPath, $currentPage) ? 'tw-text-orange-600' : '' ?>"></i>
                Restaurant Profile
            </a>

            <!-- Menu Items -->
            <a href="<?= url('/vendor/menu') ?>" 
               class="vendor-nav-item tw-flex tw-items-center tw-px-6 tw-py-3 tw-text-sm tw-font-medium tw-transition-colors tw-duration-200 <?= isActivePage('/vendor/menu', $currentPath, $currentPage) ? 'tw-bg-orange-50 tw-text-orange-700 tw-border-r-4 tw-border-orange-500 tw-font-semibold' : 'tw-text-gray-700 hover:tw-bg-gray-50 hover:tw-text-gray-900' ?>">
                <i data-feather="menu" class="tw-mr-3 tw-h-5 tw-w-5 <?= isActivePage('/vendor/menu', $currentPath, $currentPage) ? 'tw-text-orange-600' : '' ?>"></i>
                Menu Items
            </a>

            <!-- Categories -->
            <a href="<?= url('/vendor/categories') ?>" 
               class="vendor-nav-item tw-flex tw-items-center tw-px-6 tw-py-3 tw-text-sm tw-font-medium tw-transition-colors tw-duration-200 <?= isActivePage('/vendor/categories', $currentPath, $currentPage) ? 'tw-bg-orange-50 tw-text-orange-700 tw-border-r-4 tw-border-orange-500 tw-font-semibold' : 'tw-text-gray-700 hover:tw-bg-gray-50 hover:tw-text-gray-900' ?>">
                <i data-feather="folder" class="tw-mr-3 tw-h-5 tw-w-5 <?= isActivePage('/vendor/categories', $currentPath, $currentPage) ? 'tw-text-orange-600' : '' ?>"></i>
                Categories
            </a>

            <!-- Orders -->
            <a href="<?= url('/vendor/orders') ?>" 
               class="vendor-nav-item tw-flex tw-items-center tw-px-6 tw-py-3 tw-text-sm tw-font-medium tw-transition-colors tw-duration-200 <?= isActivePage('/vendor/orders', $currentPath, $currentPage) ? 'tw-bg-orange-50 tw-text-orange-700 tw-border-r-4 tw-border-orange-500 tw-font-semibold' : 'tw-text-gray-700 hover:tw-bg-gray-50 hover:tw-text-gray-900' ?>">
                <i data-feather="shopping-bag" class="tw-mr-3 tw-h-5 tw-w-5 <?= isActivePage('/vendor/orders', $currentPath, $currentPage) ? 'tw-text-orange-600' : '' ?>"></i>
                Orders
                <?php if (isset($stats) && (($stats['pendingOrders'] ?? 0) > 0)): ?>
                <span class="tw-ml-auto tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-red-100 tw-text-red-800">
                    <?= $stats['pendingOrders'] ?>
                </span>
                <?php endif; ?>
            </a>

            <!-- Analytics -->
            <a href="<?= url('/vendor/analytics') ?>" 
               class="vendor-nav-item tw-flex tw-items-center tw-px-6 tw-py-3 tw-text-sm tw-font-medium tw-transition-colors tw-duration-200 <?= isActivePage('/vendor/analytics', $currentPath, $currentPage) ? 'tw-bg-orange-50 tw-text-orange-700 tw-border-r-4 tw-border-orange-500 tw-font-semibold' : 'tw-text-gray-700 hover:tw-bg-gray-50 hover:tw-text-gray-900' ?>">
                <i data-feather="bar-chart-2" class="tw-mr-3 tw-h-5 tw-w-5 <?= isActivePage('/vendor/analytics', $currentPath, $currentPage) ? 'tw-text-orange-600' : '' ?>"></i>
                Analytics
            </a>

            <!-- Earnings -->
            <a href="<?= url('/vendor/earnings') ?>" 
               class="vendor-nav-item tw-flex tw-items-center tw-px-6 tw-py-3 tw-text-sm tw-font-medium tw-transition-colors tw-duration-200 <?= isActivePage('/vendor/earnings', $currentPath, $currentPage) ? 'tw-bg-orange-50 tw-text-orange-700 tw-border-r-4 tw-border-orange-500 tw-font-semibold' : 'tw-text-gray-700 hover:tw-bg-gray-50 hover:tw-text-gray-900' ?>">
                <i data-feather="dollar-sign" class="tw-mr-3 tw-h-5 tw-w-5 <?= isActivePage('/vendor/earnings', $currentPath, $currentPage) ? 'tw-text-orange-600' : '' ?>"></i>
                Earnings
            </a>

            <!-- Reviews -->
            <a href="<?= url('/vendor/reviews') ?>" 
               class="vendor-nav-item tw-flex tw-items-center tw-px-6 tw-py-3 tw-text-sm tw-font-medium tw-transition-colors tw-duration-200 <?= isActivePage('/vendor/reviews', $currentPath, $currentPage) ? 'tw-bg-orange-50 tw-text-orange-700 tw-border-r-4 tw-border-orange-500 tw-font-semibold' : 'tw-text-gray-700 hover:tw-bg-gray-50 hover:tw-text-gray-900' ?>">
                <i data-feather="star" class="tw-mr-3 tw-h-5 tw-w-5 <?= isActivePage('/vendor/reviews', $currentPath, $currentPage) ? 'tw-text-orange-600' : '' ?>"></i>
                Reviews
            </a>

            <!-- Messages -->
            <a href="<?= url('/vendor/messages') ?>" 
               class="vendor-nav-item tw-flex tw-items-center tw-px-6 tw-py-3 tw-text-sm tw-font-medium tw-transition-colors tw-duration-200 <?= isActivePage('/vendor/messages', $currentPath, $currentPage) ? 'tw-bg-orange-50 tw-text-orange-700 tw-border-r-4 tw-border-orange-500 tw-font-semibold' : 'tw-text-gray-700 hover:tw-bg-gray-50 hover:tw-text-gray-900' ?>">
                <i data-feather="message-circle" class="tw-mr-3 tw-h-5 tw-w-5 <?= isActivePage('/vendor/messages', $currentPath, $currentPage) ? 'tw-text-orange-600' : '' ?>"></i>
                Messages
                <?php if (isset($stats) && (($stats->unread ?? $stats['unread'] ?? 0) > 0)): ?>
                <span class="tw-ml-auto tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-blue-100 tw-text-blue-800">
                    <?= ($stats->unread ?? $stats['unread'] ?? 0) ?>
                </span>
                <?php endif; ?>
            </a>

            <!-- Settings -->
            <a href="<?= url('/vendor/settings') ?>" 
               class="vendor-nav-item tw-flex tw-items-center tw-px-6 tw-py-3 tw-text-sm tw-font-medium tw-transition-colors tw-duration-200 <?= isActivePage('/vendor/settings', $currentPath, $currentPage) ? 'tw-bg-orange-50 tw-text-orange-700 tw-border-r-4 tw-border-orange-500 tw-font-semibold' : 'tw-text-gray-700 hover:tw-bg-gray-50 hover:tw-text-gray-900' ?>">
                <i data-feather="settings" class="tw-mr-3 tw-h-5 tw-w-5 <?= isActivePage('/vendor/settings', $currentPath, $currentPage) ? 'tw-text-orange-600' : '' ?>"></i>
                Settings
            </a>
        </div>

        <!-- Restaurant Status Toggle -->
        <div class="tw-px-6 tw-py-4 tw-mt-8 tw-border-t tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <span class="tw-text-sm tw-font-medium tw-text-gray-700">Restaurant Status</span>
                <button type="button" onclick="toggleRestaurantStatus()" 
                        class="tw-relative tw-inline-flex tw-flex-shrink-0 tw-h-6 tw-w-11 tw-border-2 tw-border-transparent tw-rounded-full tw-cursor-pointer tw-transition-colors tw-ease-in-out tw-duration-200 tw-focus:tw-outline-none tw-focus:tw-ring-2 tw-focus:tw-ring-offset-2 tw-focus:tw-ring-orange-500 <?= (($restaurant->is_open ?? $restaurant['is_open'] ?? false)) ? 'tw-bg-green-600' : 'tw-bg-gray-200' ?>">
                    <span class="tw-sr-only">Toggle restaurant status</span>
                    <span class="tw-pointer-events-none tw-inline-block tw-h-5 tw-w-5 tw-rounded-full tw-bg-white tw-shadow tw-transform tw-ring-0 tw-transition tw-ease-in-out tw-duration-200 <?= (($restaurant->is_open ?? $restaurant['is_open'] ?? false)) ? 'tw-translate-x-5' : 'tw-translate-x-0' ?>"></span>
                </button>
            </div>
            <p class="tw-text-xs tw-text-gray-500 tw-mt-1">
                <?= (($restaurant->is_open ?? $restaurant['is_open'] ?? false)) ? 'Currently accepting orders' : 'Currently closed' ?>
            </p>
        </div>

        <!-- Logout -->
        <div class="tw-px-6 tw-py-4">
            <form method="POST" action="<?= url('/logout') ?>" class="tw-m-0">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <button type="submit"
                   class="tw-w-full tw-flex tw-items-center tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 hover:tw-text-gray-900 tw-rounded-md tw-border-0 tw-bg-transparent tw-cursor-pointer">
                    <i data-feather="log-out" class="tw-mr-3 tw-h-5 tw-w-5"></i>
                    Logout
                </button>
            </form>
        </div>
    </nav>
</div>

<!-- Mobile sidebar overlay -->
<div class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-z-40 lg:tw-hidden tw-hidden" id="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- Mobile menu button -->
<div class="lg:tw-hidden tw-fixed tw-top-4 tw-left-4 tw-z-50">
    <button type="button" onclick="toggleSidebar()" 
            class="tw-inline-flex tw-items-center tw-justify-center tw-p-2 tw-rounded-md tw-text-gray-400 hover:tw-text-gray-500 hover:tw-bg-gray-100 tw-focus:tw-outline-none tw-focus:tw-ring-2 tw-focus:tw-ring-inset tw-focus:tw-ring-orange-500">
        <i data-feather="menu" class="tw-h-6 tw-w-6"></i>
    </button>
</div>

<script>
// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}

// Toggle sidebar for mobile
function toggleSidebar() {
    const sidebar = document.getElementById('vendor-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    sidebar.classList.toggle('tw--translate-x-full');
    overlay.classList.toggle('tw-hidden');
}

// Toggle restaurant status
function toggleRestaurantStatus() {
    const toggle = event.target.closest('button');
    const isOpen = toggle.classList.contains('tw-bg-green-600');
    
    fetch('<?= url('/vendor/toggle-availability') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ is_open: !isOpen })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update toggle appearance
            if (data.is_open) {
                toggle.classList.remove('tw-bg-gray-200');
                toggle.classList.add('tw-bg-green-600');
                toggle.querySelector('span:last-child').classList.remove('tw-translate-x-0');
                toggle.querySelector('span:last-child').classList.add('tw-translate-x-5');
            } else {
                toggle.classList.remove('tw-bg-green-600');
                toggle.classList.add('tw-bg-gray-200');
                toggle.querySelector('span:last-child').classList.remove('tw-translate-x-5');
                toggle.querySelector('span:last-child').classList.add('tw-translate-x-0');
            }
            
            // Update status text
            const statusText = toggle.parentElement.nextElementSibling;
            statusText.textContent = data.is_open ? 'Currently accepting orders' : 'Currently closed';
        } else {
            alert('Failed to update restaurant status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating restaurant status');
    });
}

// Add margin to main content to account for sidebar
document.addEventListener('DOMContentLoaded', function() {
    const mainContent = document.querySelector('.tw-min-h-screen');
    if (mainContent && window.innerWidth >= 1024) {
        mainContent.style.marginLeft = '16rem'; // 64 * 0.25rem = 16rem
    }
});
</script>
