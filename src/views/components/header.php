<?php
/**
 * Modern Header Component - Mobile First Design
 * Professional, organized header with cart integration
 */

// Get current path for navigation highlighting
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Remove trailing slash except for root
if ($currentPath !== '/') {
    $currentPath = rtrim($currentPath, '/');
}

// Remove /eat base path if it exists
if (strpos($currentPath, '/eat') === 0) {
    $currentPath = substr($currentPath, 4) ?: '/';
}

// Get current user
$currentUser = currentUser();
$isAuthenticated = $currentUser !== null;

// Fallback: if currentUser() fails but session exists
if (!$isAuthenticated && isset($_SESSION['user_id'])) {
    $currentUser = [
        'id' => $_SESSION['user_id'],
        'role' => $_SESSION['user_role'] ?? 'customer',
        'email' => $_SESSION['user_email'] ?? 'user@example.com',
        'username' => $_SESSION['user_name'] ?? null,
        'first_name' => $_SESSION['user_first_name'] ?? null
    ];
    $isAuthenticated = true;
}

// Include cart component
if (!class_exists('CartComponent')) {
    require_once __DIR__ . '/cart.php';
}
$cartComponent = new CartComponent();

// Helper function for active link
function isActivePath($path, $currentPath) {
    // Normalize paths
    $path = rtrim($path, '/') ?: '/';
    $currentPath = rtrim($currentPath, '/') ?: '/';

    // Exact match
    if ($currentPath === $path) {
        return true;
    }

    // For non-home paths, check if current path starts with the menu path
    if ($path !== '/' && strpos($currentPath, $path) === 0) {
        // Make sure it's a real path match (not just prefix)
        $nextChar = substr($currentPath, strlen($path), 1);
        if ($nextChar === '' || $nextChar === '/') {
            return true;
        }
    }

    return false;
}
?>

<!-- Main Navigation Header -->
<nav class="tw-bg-white tw-shadow-lg tw-sticky tw-top-0 tw-z-40" role="navigation" aria-label="Main navigation">
    <div class="tw-container tw-mx-auto tw-px-4">
        <div class="tw-flex tw-justify-between tw-items-center tw-h-16 sm:tw-h-18">
            
            <!-- Logo Section -->
            <div class="tw-flex tw-items-center tw-flex-shrink-0">
                <a 
                    href="<?= url('/') ?>" 
                    class="tw-flex tw-items-center tw-gap-2 sm:tw-gap-3 tw-p-1 tw-rounded-lg tw-transition-all hover:tw-opacity-80 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-ring-offset-2" 
                    aria-label="Time2Eat - Go to homepage"
                >
                    <div class="tw-w-9 tw-h-9 sm:tw-w-10 sm:tw-h-10 tw-bg-gradient-to-br tw-from-orange-500 tw-to-red-600 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-shadow-lg">
                        <svg class="tw-w-5 tw-h-5 sm:tw-w-6 sm:tw-h-6 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <span class="tw-text-xl sm:tw-text-2xl tw-font-black tw-text-gray-900">
                        Time<span class="tw-text-orange-600">2</span>Eat
                    </span>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="tw-hidden md:tw-flex tw-items-center tw-gap-2 lg:tw-gap-4" role="menubar">
                <!-- Home Link -->
                <a
                    href="<?= url('/') ?>"
                    class="tw-px-3 lg:tw-px-4 tw-py-2 tw-text-gray-700 hover:tw-text-orange-600 tw-transition-all tw-duration-200 tw-rounded-xl tw-font-semibold tw-min-h-[44px] tw-flex tw-items-center <?= isActivePath('/', $currentPath) ? 'tw-text-orange-600 tw-bg-orange-50' : 'hover:tw-bg-gray-50' ?>"
                    role="menuitem"
                    aria-current="<?= isActivePath('/', $currentPath) ? 'page' : 'false' ?>"
                >
                    <svg class="tw-w-5 tw-h-5 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="tw-hidden lg:tw-inline">Home</span>
                </a>

                <!-- Browse Link -->
                <a
                    href="<?= url('/browse') ?>"
                    class="tw-px-3 lg:tw-px-4 tw-py-2 tw-text-gray-700 hover:tw-text-orange-600 tw-transition-all tw-duration-200 tw-rounded-xl tw-font-semibold tw-min-h-[44px] tw-flex tw-items-center <?= isActivePath('/browse', $currentPath) ? 'tw-text-orange-600 tw-bg-orange-50' : 'hover:tw-bg-gray-50' ?>"
                    role="menuitem"
                    aria-current="<?= isActivePath('/browse', $currentPath) ? 'page' : 'false' ?>"
                >
                    <svg class="tw-w-5 tw-h-5 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Browse
                </a>

                <!-- About Link -->
                <a
                    href="<?= url('/about') ?>"
                    class="tw-px-3 lg:tw-px-4 tw-py-2 tw-text-gray-700 hover:tw-text-orange-600 tw-transition-all tw-duration-200 tw-rounded-xl tw-font-semibold tw-min-h-[44px] tw-flex tw-items-center <?= isActivePath('/about', $currentPath) ? 'tw-text-orange-600 tw-bg-orange-50' : 'hover:tw-bg-gray-50' ?>"
                    role="menuitem"
                    aria-current="<?= isActivePath('/about', $currentPath) ? 'page' : 'false' ?>"
                >
                    <svg class="tw-w-5 tw-h-5 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="tw-hidden lg:tw-inline">About</span>
                </a>

                <!-- Divider -->
                <div class="tw-h-8 tw-w-px tw-bg-gray-300 tw-mx-2"></div>

                <!-- Cart Icon -->
                <?= $cartComponent->renderNavCartIcon(0) ?>

                <?php if ($isAuthenticated): ?>
                    <!-- Dashboard Button -->
                    <a
                        href="<?= url('/' . ($currentUser['role'] ?? 'customer') . '/dashboard') ?>"
                        class="tw-px-4 tw-py-2 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-rounded-xl tw-font-bold tw-min-h-[44px] tw-flex tw-items-center tw-gap-2 tw-shadow-lg hover:tw-shadow-xl tw-transition-all hover:tw-scale-105"
                        role="menuitem"
                    >
                        <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        <span class="tw-hidden lg:tw-inline">Dashboard</span>
                    </a>

                    <!-- User Menu Dropdown -->
                    <div class="tw-relative tw-group">
                        <button
                            class="tw-flex tw-items-center tw-gap-2 tw-px-3 tw-py-2 tw-text-gray-700 hover:tw-text-orange-600 tw-transition-all tw-rounded-xl tw-min-h-[44px] hover:tw-bg-gray-50"
                            aria-expanded="false"
                            aria-haspopup="true"
                            type="button"
                        >
                            <div class="tw-w-9 tw-h-9 tw-bg-gradient-to-br tw-from-orange-100 tw-to-red-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-border-2 tw-border-orange-200">
                                <span class="tw-text-orange-600 tw-font-black tw-text-sm">
                                    <?= strtoupper(substr($currentUser['first_name'] ?? $currentUser['username'] ?? $currentUser['email'] ?? 'U', 0, 1)) ?>
                                </span>
                            </div>
                            <span class="tw-hidden xl:tw-inline tw-font-semibold tw-text-sm">
                                <?= htmlspecialchars($currentUser['first_name'] ?? $currentUser['username'] ?? 'User') ?>
                            </span>
                            <svg class="tw-w-4 tw-h-4 tw-transition-transform group-hover:tw-rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div class="tw-absolute tw-right-0 tw-mt-2 tw-w-56 tw-bg-white tw-rounded-xl tw-shadow-2xl tw-opacity-0 tw-invisible group-hover:tw-opacity-100 group-hover:tw-visible tw-transition-all tw-duration-200 tw-border tw-border-gray-200 tw-overflow-hidden">
                            <!-- User Info -->
                            <div class="tw-px-4 tw-py-3 tw-bg-gradient-to-r tw-from-orange-50 tw-to-red-50 tw-border-b tw-border-gray-200">
                                <p class="tw-font-bold tw-text-gray-900 tw-text-sm">
                                    <?= htmlspecialchars($currentUser['first_name'] ?? $currentUser['username'] ?? 'User') ?>
                                </p>
                                <p class="tw-text-xs tw-text-gray-600">
                                    <?= htmlspecialchars($currentUser['email'] ?? '') ?>
                                </p>
                                <p class="tw-text-xs tw-text-orange-600 tw-font-semibold tw-mt-1">
                                    <?= ucfirst($currentUser['role'] ?? 'Customer') ?>
                                </p>
                            </div>

                            <!-- Menu Items -->
                            <div class="tw-py-2">
                                <a
                                    href="<?= url('/' . ($currentUser['role'] ?? 'customer') . '/dashboard') ?>"
                                    class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-2.5 tw-text-gray-700 hover:tw-bg-orange-50 hover:tw-text-orange-600 tw-transition-all"
                                >
                                    <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                    </svg>
                                    <span class="tw-font-semibold">Dashboard</span>
                                </a>
                                <a
                                    href="<?= url('/' . ($currentUser['role'] ?? 'customer') . '/profile') ?>"
                                    class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-2.5 tw-text-gray-700 hover:tw-bg-orange-50 hover:tw-text-orange-600 tw-transition-all"
                                >
                                    <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span class="tw-font-semibold">Profile</span>
                                </a>
                                <?php if (($currentUser['role'] ?? 'customer') === 'customer'): ?>
                                <a
                                    href="<?= url('/customer/orders') ?>"
                                    class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-2.5 tw-text-gray-700 hover:tw-bg-orange-50 hover:tw-text-orange-600 tw-transition-all"
                                >
                                    <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                    <span class="tw-font-semibold">My Orders</span>
                                </a>
                                <?php endif; ?>
                            </div>

                            <!-- Logout -->
                            <div class="tw-border-t tw-border-gray-200">
                                <form method="POST" action="<?= url('/logout') ?>" class="tw-m-0">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <button
                                        type="submit"
                                        class="tw-w-full tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-text-red-600 hover:tw-bg-red-50 tw-transition-all tw-font-semibold"
                                    >
                                        <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Guest Actions -->
                    <a
                        href="<?= url('/login') ?>"
                        class="tw-px-4 tw-py-2 tw-text-gray-700 hover:tw-text-orange-600 tw-transition-all tw-rounded-xl tw-font-bold tw-min-h-[44px] tw-flex tw-items-center tw-gap-2 hover:tw-bg-gray-50"
                    >
                        <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        <span class="tw-hidden lg:tw-inline">Login</span>
                    </a>
                    <a
                        href="<?= url('/register') ?>"
                        class="tw-px-4 tw-py-2 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-rounded-xl tw-font-bold tw-min-h-[44px] tw-flex tw-items-center tw-gap-2 tw-shadow-lg hover:tw-shadow-xl tw-transition-all hover:tw-scale-105"
                    >
                        <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Sign Up
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:tw-hidden tw-flex tw-items-center tw-gap-2">
                <!-- Cart Icon for Mobile -->
                <?= $cartComponent->renderNavCartIcon(0) ?>

                <!-- Hamburger Menu -->
                <button
                    id="mobile-menu-btn"
                    class="tw-p-2 tw-text-gray-700 hover:tw-text-orange-600 tw-rounded-xl tw-min-h-[44px] tw-min-w-[44px] tw-flex tw-items-center tw-justify-center hover:tw-bg-gray-50 tw-transition-all"
                    aria-expanded="false"
                    aria-controls="mobile-menu"
                    aria-label="Toggle mobile menu"
                    type="button"
                >
                    <i data-feather="menu" class="tw-w-6 tw-h-6"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Menu -->
    <div
        id="mobile-menu"
        class="md:tw-hidden tw-hidden tw-bg-white tw-border-t tw-border-gray-200 tw-shadow-lg"
        role="menu"
    >
        <div class="tw-px-4 tw-py-3 tw-space-y-1">
            <!-- Navigation Links -->
            <a
                href="<?= url('/') ?>"
                class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-text-gray-700 hover:tw-text-orange-600 hover:tw-bg-orange-50 tw-rounded-xl tw-transition-all tw-font-semibold <?= isActivePath('/', $currentPath) ? 'tw-text-orange-600 tw-bg-orange-50' : '' ?>"
            >
                <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Home
            </a>
            <a
                href="<?= url('/browse') ?>"
                class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-text-gray-700 hover:tw-text-orange-600 hover:tw-bg-orange-50 tw-rounded-xl tw-transition-all tw-font-semibold <?= isActivePath('/browse', $currentPath) ? 'tw-text-orange-600 tw-bg-orange-50' : '' ?>"
            >
                <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Browse Restaurants
            </a>
            <a
                href="<?= url('/about') ?>"
                class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-text-gray-700 hover:tw-text-orange-600 hover:tw-bg-orange-50 tw-rounded-xl tw-transition-all tw-font-semibold <?= isActivePath('/about', $currentPath) ? 'tw-text-orange-600 tw-bg-orange-50' : '' ?>"
            >
                <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                About
            </a>

            <?php if ($isAuthenticated): ?>
                <!-- User Section -->
                <div class="tw-pt-3 tw-mt-3 tw-border-t tw-border-gray-200">
                    <div class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-bg-gradient-to-r tw-from-orange-50 tw-to-red-50 tw-rounded-xl tw-mb-2">
                        <div class="tw-w-12 tw-h-12 tw-bg-gradient-to-br tw-from-orange-100 tw-to-red-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-border-2 tw-border-orange-200">
                            <span class="tw-text-orange-600 tw-font-black tw-text-lg">
                                <?= strtoupper(substr($currentUser['first_name'] ?? $currentUser['username'] ?? 'U', 0, 1)) ?>
                            </span>
                        </div>
                        <div>
                            <p class="tw-font-bold tw-text-gray-900">
                                <?= htmlspecialchars($currentUser['first_name'] ?? $currentUser['username'] ?? 'User') ?>
                            </p>
                            <p class="tw-text-sm tw-text-gray-600">
                                <?= htmlspecialchars($currentUser['email'] ?? '') ?>
                            </p>
                            <p class="tw-text-xs tw-text-orange-600 tw-font-semibold">
                                <?= ucfirst($currentUser['role'] ?? 'Customer') ?>
                            </p>
                        </div>
                    </div>

                    <a
                        href="<?= url('/' . ($currentUser['role'] ?? 'customer') . '/dashboard') ?>"
                        class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-text-gray-700 hover:tw-text-orange-600 hover:tw-bg-orange-50 tw-rounded-xl tw-transition-all tw-font-semibold"
                    >
                        <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        Dashboard
                    </a>
                    <?php if (($currentUser['role'] ?? 'customer') === 'customer'): ?>
                    <a
                        href="<?= url('/customer/orders') ?>"
                        class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-text-gray-700 hover:tw-text-orange-600 hover:tw-bg-orange-50 tw-rounded-xl tw-transition-all tw-font-semibold"
                    >
                        <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        My Orders
                    </a>
                    <?php endif; ?>
                    <form method="POST" action="<?= url('/logout') ?>" class="tw-m-0">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <button
                            type="submit"
                            class="tw-w-full tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-text-red-600 hover:tw-bg-red-50 tw-rounded-xl tw-transition-all tw-font-semibold"
                        >
                            <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <!-- Guest Actions -->
                <div class="tw-pt-3 tw-mt-3 tw-border-t tw-border-gray-200 tw-space-y-2">
                    <a
                        href="<?= url('/login') ?>"
                        class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-text-gray-700 hover:tw-text-orange-600 hover:tw-bg-gray-50 tw-rounded-xl tw-transition-all tw-font-bold tw-border-2 tw-border-gray-200"
                    >
                        <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        <div>
                            <div class="tw-font-bold">Login</div>
                            <div class="tw-text-xs tw-text-gray-500">Access your account</div>
                        </div>
                    </a>
                    <a
                        href="<?= url('/register') ?>"
                        class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-rounded-xl tw-transition-all tw-font-bold tw-shadow-lg"
                    >
                        <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        <div>
                            <div class="tw-font-bold">Sign Up</div>
                            <div class="tw-text-xs tw-text-white/80">Join Time2Eat today</div>
                        </div>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
