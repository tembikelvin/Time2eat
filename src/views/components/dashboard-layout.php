<?php
$user = $user ?? null;
$currentPage = $currentPage ?? '';
$sidebarItems = $sidebarItems ?? [];
$title = $title ?? 'Dashboard - Time2Eat';
?>

<!DOCTYPE html>
<html lang="en" class="tw-h-full tw-bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title><?= e($title) ?></title>

    <?php
    // Load global map configuration
    require_once __DIR__ . '/../../helpers/MapHelper.php';
    $mapHelper = \helpers\MapHelper::getInstance();
    echo $mapHelper->getGlobalConfig();
    echo $mapHelper->getScripts();
    ?>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            prefix: 'tw-',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fff7ed',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c'
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    
    <!-- Chart.js for analytics - Using UMD build for compatibility -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Custom Styles -->
    <style>
        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
            color: #6b7280;
            text-decoration: none;
        }

        .sidebar-item:hover {
            background-color: #f3f4f6;
            color: #111827;
        }

        .sidebar-item.active {
            background-color: #f97316 !important;
            color: white !important;
        }

        .sidebar-item.active:hover {
            background-color: #ea580c !important;
        }

        .sidebar-item.active i,
        .sidebar-item.active svg {
            color: white !important;
        }

        .sidebar-item i,
        .sidebar-item svg {
            margin-right: 0.75rem;
            flex-shrink: 0;
            height: 1.25rem;
            width: 1.25rem;
        }
    </style>
</head>
<body class="tw-h-full tw-font-sans tw-antialiased">
    <div class="tw-min-h-full">
        <!-- Mobile sidebar overlay -->
        <div class="tw-fixed tw-inset-0 tw-flex tw-z-40 lg:tw-hidden" id="mobile-sidebar-overlay" style="display: none;">
            <div class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-75" onclick="toggleMobileSidebar()"></div>
            <div class="tw-relative tw-flex-1 tw-flex tw-flex-col tw-max-w-xs tw-w-full tw-bg-white">
                <div class="tw-absolute tw-top-0 tw-right-0 tw--mr-12 tw-pt-2">
                    <button type="button" class="tw-ml-1 tw-flex tw-items-center tw-justify-center tw-h-10 tw-w-10 tw-rounded-full focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-inset focus:tw-ring-white" onclick="toggleMobileSidebar()">
                        <i data-feather="x" class="tw-h-6 tw-w-6 tw-text-white"></i>
                    </button>
                </div>
                <?php include __DIR__ . '/sidebar-content.php'; ?>
            </div>
        </div>

        <!-- Static sidebar for desktop -->
        <div class="tw-hidden lg:tw-flex lg:tw-w-64 lg:tw-flex-col lg:tw-fixed lg:tw-inset-y-0">
            <div class="tw-flex-1 tw-flex tw-flex-col tw-min-h-0 tw-bg-white tw-border-r tw-border-gray-200">
                <?php include __DIR__ . '/sidebar-content.php'; ?>
            </div>
        </div>

        <!-- Main content -->
        <div class="lg:tw-pl-64 tw-flex tw-flex-col tw-flex-1">
            <!-- Top navigation -->
            <div class="tw-sticky tw-top-0 tw-z-10 tw-flex-shrink-0 tw-flex tw-h-16 tw-bg-white tw-shadow">
                <!-- Mobile menu button -->
                <button type="button" class="tw-px-4 tw-border-r tw-border-gray-200 tw-text-gray-500 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-inset focus:tw-ring-primary-500 lg:tw-hidden" onclick="toggleMobileSidebar()">
                    <i data-feather="menu" class="tw-h-6 tw-w-6"></i>
                </button>
                
                    <div class="tw-flex-1 tw-px-2 sm:tw-px-4 tw-flex tw-justify-between">
                    <div class="tw-flex-1 tw-flex">
                        <!-- Search bar -->
                        <div class="tw-w-full tw-flex tw-md:tw-ml-0">
                            <label for="search-field" class="tw-sr-only">Search</label>
                            <div class="tw-relative tw-w-full tw-text-gray-400 focus-within:tw-text-gray-600">
                                <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-flex tw-items-center tw-pointer-events-none tw-pl-2">
                                    <i data-feather="search" class="tw-h-4 tw-w-4 sm:tw-h-5 sm:tw-w-5"></i>
                                </div>
                                <input id="search-field" class="tw-block tw-w-full tw-h-full tw-pl-6 sm:tw-pl-8 tw-pr-2 sm:tw-pr-3 tw-py-2 tw-border-transparent tw-text-gray-900 tw-placeholder-gray-500 focus:tw-outline-none focus:tw-placeholder-gray-400 focus:tw-ring-0 focus:tw-border-transparent tw-text-sm sm:tw-text-base" placeholder="Search..." type="search">
                            </div>
                        </div>
                    </div>
                    
                    <div class="tw-ml-2 sm:tw-ml-4 tw-flex tw-items-center tw-md:tw-ml-6">
                        <!-- Notifications -->
                        <button type="button" class="tw-bg-white tw-p-1 tw-rounded-full tw-text-gray-400 hover:tw-text-gray-500 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-primary-500">
                            <span class="tw-sr-only">View notifications</span>
                            <div class="tw-relative">
                                <i data-feather="bell" class="tw-h-5 tw-w-5 sm:tw-h-6 sm:tw-w-6"></i>
                                <span class="tw-absolute tw-top-0 tw-right-0 tw-block tw-h-2 tw-w-2 tw-rounded-full tw-bg-red-400 tw-ring-2 tw-ring-white"></span>
                            </div>
                        </button>

                        <!-- Profile dropdown -->
                        <div class="tw-ml-2 sm:tw-ml-3 tw-relative">
                            <div>
                                <button type="button" class="tw-max-w-xs tw-bg-white tw-flex tw-items-center tw-text-sm tw-rounded-full focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-primary-500" id="user-menu-button" onclick="toggleUserMenu()">
                                    <span class="tw-sr-only">Open user menu</span>
                                    <div class="tw-h-7 tw-w-7 sm:tw-h-8 sm:tw-w-8 tw-bg-gradient-to-r tw-from-blue-500 tw-to-purple-500 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                        <span class="tw-text-white tw-font-semibold tw-text-xs">
                                            <?= strtoupper(substr((is_object($user) ? $user->first_name : $user['first_name']) ?? 'U', 0, 1)) ?>
                                        </span>
                                    </div>
                                    <i data-feather="chevron-down" class="tw-ml-1 tw-h-3 tw-w-3 sm:tw-h-4 sm:tw-w-4 tw-text-gray-400"></i>
                                </button>
                            </div>

                            <div class="tw-origin-top-right tw-absolute tw-right-0 tw-mt-2 tw-w-48 tw-rounded-md tw-shadow-lg tw-py-1 tw-bg-white tw-ring-1 tw-ring-black tw-ring-opacity-5 focus:tw-outline-none tw-hidden" id="user-menu" role="menu">
                                <form method="POST" action="<?= url('/logout') ?>" class="tw-m-0">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <button type="submit" class="tw-w-full tw-block tw-px-4 tw-py-2 tw-text-sm tw-text-gray-700 hover:tw-bg-gray-100 tw-border-0 tw-bg-transparent tw-cursor-pointer tw-text-left" role="menuitem">
                                        <i data-feather="log-out" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                                        Sign out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <main class="tw-flex-1">
                <div class="tw-py-6">
                    <div class="tw-max-w-7xl tw-mx-auto tw-px-4 sm:tw-px-6 md:tw-px-8">
                        <?php if (isset($content)): ?>
                            <?= $content ?>
                        <?php else: ?>
                            <!-- Default content will be inserted here -->
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Initialize Feather icons
        feather.replace();

        // Mobile sidebar toggle
        function toggleMobileSidebar() {
            const overlay = document.getElementById('mobile-sidebar-overlay');
            if (overlay) {
                overlay.style.display = overlay.style.display === 'none' ? 'flex' : 'none';
                
                // Prevent body scroll when sidebar is open
                if (overlay.style.display === 'flex') {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            }
        }

        // User menu toggle
        function toggleUserMenu() {
            const menu = document.getElementById('user-menu');
            menu.classList.toggle('tw-hidden');
        }

        // Close user menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('user-menu');
            const button = document.getElementById('user-menu-button');
            
            if (menu && button && !menu.contains(event.target) && !button.contains(event.target)) {
                menu.classList.add('tw-hidden');
            }
        });

        // Search functionality
        function handleSearch() {
            const searchField = document.getElementById('search-field');
            if (searchField) {
                searchField.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    console.log('Searching for:', searchTerm);
                    
                    // Basic search functionality - can be enhanced with actual search logic
                    if (searchTerm.length > 2) {
                        // Implement search logic here
                        console.log('Performing search for:', searchTerm);
                    }
                });
                
                // Handle search on Enter key
                searchField.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const searchTerm = e.target.value;
                        if (searchTerm.trim()) {
                            console.log('Search submitted:', searchTerm);
                            // Implement search submission logic here
                        }
                    }
                });
            }
        }

        // Initialize search functionality
        handleSearch();

        // Auto-refresh notifications (disabled - API endpoint not available)
        function checkNotifications() {
            // Disabled: API endpoint /api/notifications/unread not available
            console.log('Notification check disabled - API endpoint not available');
        }

        // Check notifications every 30 seconds (disabled)
        // setInterval(checkNotifications, 30000);
        // checkNotifications(); // Initial check
    </script>
</body>
</html>
