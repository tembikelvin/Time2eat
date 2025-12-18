<!DOCTYPE html>
<html lang="en" class="tw-h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $description ?? 'Time2Eat - Bamenda Food Delivery Platform. Order from local restaurants with real-time tracking.' ?>">
    <meta name="keywords" content="food delivery, Bamenda, Cameroon, restaurants, online ordering">
    <meta name="author" content="Time2Eat">

    <?php
    // Load global map configuration
    require_once __DIR__ . '/../../helpers/MapHelper.php';

    // Ensure database connection is available for MapHelper to load settings
    if (!function_exists('dbConnection')) {
        $dbConfigPath = __DIR__ . '/../../../config/database.php';
        if (file_exists($dbConfigPath)) {
            require_once $dbConfigPath;
        }
    }

    // Pass database connection to MapHelper
    $db = function_exists('dbConnection') ? dbConnection() : null;
    $mapHelper = \helpers\MapHelper::getInstance($db);
    echo $mapHelper->getGlobalConfig();
    echo $mapHelper->getScripts();
    ?>
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= APP_URL ?>">
    <meta property="og:title" content="<?= $title ?? 'Time2Eat - Food Delivery in Bamenda' ?>">
    <meta property="og:description" content="<?= $description ?? 'Order from local restaurants with real-time tracking' ?>">
    <meta property="og:image" content="<?= url('public/images/og-image.jpg') ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= APP_URL ?>">
    <meta property="twitter:title" content="<?= $title ?? 'Time2Eat - Food Delivery in Bamenda' ?>">
    <meta property="twitter:description" content="<?= $description ?? 'Order from local restaurants with real-time tracking' ?>">
    <meta property="twitter:image" content="<?= url('public/images/og-image.jpg') ?>">

    <!-- CSRF Token for AJAX requests -->
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">

    <!-- User Authentication Status for Cart -->
    <meta name="user-authenticated" content="<?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>">
    <meta name="user-role" content="<?= $_SESSION['user_role'] ?? 'guest' ?>">

    <?php
    // Get current page for navigation highlighting - MUST BE FIRST
    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $currentPath = rtrim($currentPath, '/') ?: '/';

    // Remove base path if exists
    if (defined('APP_PATH') && APP_PATH) {
        $basePath = rtrim(APP_PATH, '/');
        if (strpos($currentPath, $basePath) === 0) {
            $currentPath = substr($currentPath, strlen($basePath)) ?: '/';
        }
    }

    // Get current user for authentication status
    $currentUser = currentUser();
    $isAuthenticated = $currentUser !== null;

    // Fallback: if currentUser() fails but session exists, create basic user array
    if (!$isAuthenticated && isset($_SESSION['user_id'])) {
        $currentUser = [
            'id' => $_SESSION['user_id'],
            'role' => $_SESSION['user_role'] ?? 'customer',
            'email' => $_SESSION['user_email'] ?? 'user@example.com',
            'username' => $_SESSION['user_name'] ?? null
        ];
        $isAuthenticated = true;
    }



    // Set page title based on current path for better UX
    $pageTitle = '';
    switch ($currentPath) {
        case '/':
            $pageTitle = 'Home';
            break;
        case '/browse':
            $pageTitle = 'Browse Restaurants';
            break;
        case '/about':
            $pageTitle = 'About Us';
            break;
        case '/contact':
            $pageTitle = 'Contact';
            break;
        case '/login':
            $pageTitle = 'Login';
            break;
        case '/register':
            $pageTitle = 'Sign Up';
            break;
        default:
            $pageTitle = $currentPath ? ucfirst(trim($currentPath, '/')) : 'Page';
    }
    ?>

    <title><?= $title ?? 'Time2Eat - Food Delivery in Bamenda' ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= url('public/favicon.ico') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= url('public/images/icons/icon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= url('public/images/icons/icon-16x16.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= url('public/images/apple-touch-icon.png') ?>">

    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= url('manifest.json') ?>">
    <meta name="theme-color" content="#ea580c">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Time2Eat">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="Time2Eat">
    <meta name="msapplication-TileColor" content="#ea580c">
    <meta name="msapplication-TileImage" content="<?= url('public/images/icons/icon-144x144.png') ?>">
    <meta name="msapplication-config" content="<?= url('browserconfig.xml') ?>">

    <!-- PWA Icons -->
    <link rel="icon" type="image/png" sizes="192x192" href="<?= url('public/images/icons/icon-192x192.png') ?>">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= url('public/images/icons/icon-512x512.png') ?>">
    
    <!-- Tailwind CSS with tw- prefix -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            prefix: 'tw-',
            corePlugins: {
                preflight: true,
            },
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                        },
                        secondary: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            500: '#f97316',
                            600: '#ea580c',
                        }
                    },
                    fontFamily: {
                        'sans': ['Inter', 'Poppins', 'system-ui', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'pulse-slow': 'pulse 3s infinite',
                    },
                    spacing: {
                        '1.5': '0.375rem',
                        '2': '0.5rem',
                    }
                }
            }
        }
    </script>

    <!-- External Resources - Single imports only -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <!-- Custom CSS - Using direct CSS instead of @layer/@apply for CDN compatibility -->
    <style>
        /* CSS Variables for Theme Colors */
        :root {
            --primary-600: #dc2626;
            --primary-700: #b91c1c;
            --secondary-500: #f97316;
            --secondary-600: #ea580c;
        }
        
        /* Button styles using direct CSS for maximum compatibility */
            .tw-btn-primary {
            background-color: var(--primary-600);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s;
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }
        .tw-btn-primary:hover {
            background-color: var(--primary-700);
            transform: scale(1.05);
        }
        
            .tw-btn-secondary {
            background-color: var(--secondary-500);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s;
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }
        .tw-btn-secondary:hover {
            background-color: var(--secondary-600);
            transform: scale(1.05);
        }
        
            .tw-btn-outline {
            border: 2px solid var(--primary-600);
            color: var(--primary-600);
            background: transparent;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s;
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            text-decoration: none;
        }
        .tw-btn-outline:hover {
            background-color: var(--primary-600);
            color: white;
            transform: scale(1.05);
        }
        
        .tw-btn-ghost {
            color: #4b5563;
            background: transparent;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }
        .tw-btn-ghost:hover {
            background-color: #f3f4f6;
            color: #1f2937;
            }
            .tw-card {
                @apply tw-bg-white tw-rounded-xl tw-shadow-lg tw-p-6 tw-transition-all tw-duration-200 hover:tw-shadow-xl;
            }
            .tw-glass {
                @apply tw-backdrop-blur-md tw-bg-white/10 tw-border tw-border-white/20 tw-rounded-xl;
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        /* Loading animation */
        .tw-loading {
            @apply tw-animate-spin tw-rounded-full tw-border-4 tw-border-gray-200 tw-border-t-primary-600;
        }
        
        /* Glassmorphism effects */
        .tw-glass-card {
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
    
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    
    <!-- Authentication Status for JavaScript -->
    <script>
        window.Time2Eat = window.Time2Eat || {};
        window.Time2Eat.isAuthenticated = <?= $isAuthenticated ? 'true' : 'false' ?>;
        window.Time2Eat.user = <?= $isAuthenticated ? json_encode($currentUser) : 'null' ?>;
    </script>
    
</head>

<body class="tw-bg-gray-50 tw-font-sans tw-min-h-screen tw-flex tw-flex-col">
    <?php
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Include required components
    require_once __DIR__ . '/../../helpers/IconHelper.php';
    require_once __DIR__ . '/../../helpers/functions.php';

    // Helper function for escaping output
    if (!function_exists('e')) {
        function e($value) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
    }
    ?>

    <!-- Skip to main content link for accessibility -->
    <a href="#main-content" class="tw-sr-only focus:tw-not-sr-only focus:tw-absolute focus:tw-top-4 focus:tw-left-4 tw-bg-primary-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-z-50">
        Skip to main content
    </a>

    <!-- Include Modern Header Component -->
    <?php require_once __DIR__ . '/../components/header.php'; ?>

    <!-- PWA Install Banner -->
    <div id="pwa-install-banner" class="tw-hidden tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-text-white tw-py-3 tw-px-4 tw-relative tw-z-40">
        <div class="tw-container tw-mx-auto tw-flex tw-items-center tw-justify-between">
            <div class="tw-flex tw-items-center tw-space-x-3">
                <div class="tw-bg-white tw-bg-opacity-20 tw-rounded-full tw-p-2">
                    <i data-feather="smartphone" class="tw-w-5 tw-h-5"></i>
                </div>
                <div>
                    <p class="tw-font-medium tw-text-sm sm:tw-text-base">Install Time2Eat App</p>
                    <p class="tw-text-xs sm:tw-text-sm tw-opacity-90">Get faster access and offline features!</p>
                </div>
            </div>
            <div class="tw-flex tw-items-center tw-space-x-2">
                <button 
                    id="install-pwa-btn" 
                    class="tw-bg-white tw-text-orange-600 tw-px-3 tw-py-1 sm:tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-medium hover:tw-bg-gray-100 tw-transition-colors"
                >
                    <i data-feather="download" class="tw-w-4 tw-h-4 tw-inline tw-mr-1"></i>
                    <span class="tw-hidden sm:tw-inline">Install</span>
                    <span class="tw-inline sm:tw-hidden">Get</span>
                </button>
                <button 
                    id="dismiss-pwa-banner" 
                    class="tw-text-white tw-opacity-75 tw-hover:tw-opacity-100 tw-p-1 tw-rounded tw-transition-opacity"
                    aria-label="Dismiss install banner"
                >
                    <i data-feather="x" class="tw-w-4 tw-h-4"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php
    $flash = $_SESSION['flash'] ?? [];
    foreach ($flash as $type => $message):
        $bgColor = $type === 'success' ? 'tw-bg-green-500' : 
                  ($type === 'error' ? 'tw-bg-red-500' : 
                   ($type === 'warning' ? 'tw-bg-yellow-500' : 'tw-bg-blue-500'));
        $icon = $type === 'success' ? 'check-circle' : 
               ($type === 'error' ? 'x-circle' : 
                ($type === 'warning' ? 'alert-triangle' : 'info'));
    ?>
        <div class="tw-fixed tw-top-4 tw-right-4 tw-z-50 tw-max-w-sm tw-p-4 tw-rounded-lg tw-shadow-lg tw-transition-all tw-duration-300 tw-transform tw-translate-x-0 <?= $bgColor ?> tw-text-white" id="flash-<?= $type ?>">
            <div class="tw-flex tw-items-start tw-space-x-3">
                <div class="tw-flex-shrink-0">
                    <i data-feather="<?= $icon ?>" class="tw-w-5 tw-h-5"></i>
                </div>
                <div class="tw-flex-1">
                    <p class="tw-text-sm tw-font-medium"><?= htmlspecialchars($message) ?></p>
                </div>
                <div class="tw-flex-shrink-0">
                    <button onclick="this.closest('#flash-<?= $type ?>').remove()" class="tw-text-white tw-opacity-75 hover:tw-opacity-100 tw-focus:tw-outline-none">
                        <i data-feather="x" class="tw-w-4 tw-h-4"></i>
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php unset($_SESSION['flash']); ?>

    <!-- Flash Message Auto-dismiss Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-dismiss flash messages after 5 seconds
            const flashMessages = document.querySelectorAll('[id^="flash-"]');
            flashMessages.forEach(function(message) {
                // Initialize Feather icons for the flash message
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
                
                // Auto-dismiss after 5 seconds
                setTimeout(function() {
                    message.style.transform = 'translateX(100%)';
                    setTimeout(function() {
                        if (message.parentElement) {
                            message.parentElement.removeChild(message);
                        }
                    }, 300);
                }, 5000);
            });
        });
    </script>

    <!-- Main Content -->
    <main id="main-content" class="tw-flex-1" role="main">
        <?= $content ?>
    </main>

    <!-- Include Modern Footer Component -->
    <?php require_once __DIR__ . '/../components/footer.php'; ?>

    <!-- Scripts -->
    <script>
        // Initialize Feather Icons
        function initializeFeatherIcons() {
            if (typeof feather !== 'undefined') {
                try {
                    feather.replace();
                    console.log('Feather icons initialized successfully');
                } catch (error) {
                    console.error('Error initializing Feather icons:', error);
                }
            } else {
                console.log('Feather not loaded yet, retrying...');
                setTimeout(initializeFeatherIcons, 100);
            }
        }

        // Initialize application
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Feather icons
            initializeFeatherIcons();

            // Mobile menu functionality
            initializeMobileMenu();

            // PWA functionality
            initializePWA();

            // Lazy loading for images
            initializeLazyLoading();

            // Accessibility enhancements
            initializeAccessibility();
        });

        /**
         * Initialize mobile menu functionality
         */
        function initializeMobileMenu() {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');

            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', function() {
                    const isExpanded = mobileMenuBtn.getAttribute('aria-expanded') === 'true';

                    // Toggle menu visibility
                    mobileMenu.classList.toggle('tw-hidden');

                    // Update ARIA attributes
                    mobileMenuBtn.setAttribute('aria-expanded', !isExpanded);

                    // Update icon
                    const icon = mobileMenuBtn.querySelector('i');
                    if (icon) {
                        icon.setAttribute('data-feather', isExpanded ? 'menu' : 'x');
                        initializeFeatherIcons();
                    }
                });

                // Close menu when clicking outside
                document.addEventListener('click', function(event) {
                    if (!mobileMenuBtn.contains(event.target) && !mobileMenu.contains(event.target)) {
                        mobileMenu.classList.add('tw-hidden');
                        mobileMenuBtn.setAttribute('aria-expanded', 'false');
                        const icon = mobileMenuBtn.querySelector('i');
                        if (icon) {
                            icon.setAttribute('data-feather', 'menu');
                            feather.replace();
                        }
                    }
                });

                // Close menu on escape key
                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape' && !mobileMenu.classList.contains('tw-hidden')) {
                        mobileMenu.classList.add('tw-hidden');
                        mobileMenuBtn.setAttribute('aria-expanded', 'false');
                        mobileMenuBtn.focus();
                    }
                });
            }
        }

        /**
         * Initialize PWA functionality
         */
        function initializePWA() {
            let deferredPrompt;
            const installBtns = document.querySelectorAll('#install-app-btn, #footer-install-btn, #install-pwa-btn');
            const pwaBanner = document.getElementById('pwa-install-banner');
            const dismissBannerBtn = document.getElementById('dismiss-pwa-banner');

            // Handle beforeinstallprompt event
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;

                // Show install buttons
                installBtns.forEach(btn => {
                    if (btn) {
                        btn.classList.remove('tw-hidden');
                        btn.style.display = 'flex';
                    }
                });

                // Show PWA banner
                if (pwaBanner && !localStorage.getItem('pwa-banner-dismissed')) {
                    pwaBanner.classList.remove('tw-hidden');
                }
            });

            // Handle install button clicks
            installBtns.forEach(btn => {
                if (btn) {
                    btn.addEventListener('click', async () => {
                        if (deferredPrompt) {
                            deferredPrompt.prompt();
                            const { outcome } = await deferredPrompt.userChoice;

                            if (outcome === 'accepted') {
                                console.log('PWA installed');
                                // Hide install buttons
                                installBtns.forEach(b => {
                                    if (b) b.style.display = 'none';
                                });
                                if (pwaBanner) pwaBanner.classList.add('tw-hidden');
                            }

                            deferredPrompt = null;
                        }
                    });
                }
            });

            // Handle banner dismiss
            if (dismissBannerBtn) {
                dismissBannerBtn.addEventListener('click', () => {
                    if (pwaBanner) {
                        pwaBanner.classList.add('tw-hidden');
                        localStorage.setItem('pwa-banner-dismissed', 'true');
                    }
                });
            }

            // Handle app installed event
            window.addEventListener('appinstalled', () => {
                console.log('PWA was installed');
                installBtns.forEach(btn => {
                    if (btn) btn.style.display = 'none';
                });
                if (pwaBanner) pwaBanner.classList.add('tw-hidden');
            });
        }

        /**
         * Initialize lazy loading for images
         */
        function initializeLazyLoading() {
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            if (img.dataset.src) {
                                img.src = img.dataset.src;
                                img.classList.remove('lazy-load');
                                observer.unobserve(img);
                            }
                        }
                    });
                });

                document.querySelectorAll('img.lazy-load').forEach(img => {
                    imageObserver.observe(img);
                });
            } else {
                // Fallback for browsers without IntersectionObserver
                document.querySelectorAll('img.lazy-load').forEach(img => {
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                    }
                });
            }
        }

        /**
         * Initialize accessibility enhancements
         */
        function initializeAccessibility() {
            // Add focus management for dropdowns
            const dropdowns = document.querySelectorAll('.tw-group');
            dropdowns.forEach(dropdown => {
                const button = dropdown.querySelector('button');
                const menu = dropdown.querySelector('[role="menu"]');

                if (button && menu) {
                    button.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            menu.classList.toggle('tw-opacity-0');
                            menu.classList.toggle('tw-invisible');
                        }
                    });
                }
            });

            // Announce page changes for screen readers
            const pageTitle = document.title;
            if (pageTitle) {
                const announcement = document.createElement('div');
                announcement.setAttribute('aria-live', 'polite');
                announcement.setAttribute('aria-atomic', 'true');
                announcement.className = 'tw-sr-only';
                announcement.textContent = `Page loaded: ${pageTitle}`;
                document.body.appendChild(announcement);

                // Remove announcement after screen reader has time to read it
                setTimeout(() => {
                    document.body.removeChild(announcement);
                }, 1000);
            }
        }

        /**
         * Show notification to user (unified with showToast)
         * Supports both browser notifications and in-app toasts
         */
        function showNotification(title, message, type = 'info') {
            // Show browser notification if permitted
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification(title, {
                    body: message,
                    icon: '<?= url("public/images/icon-192x192.png") ?>',
                    badge: '<?= url("public/images/icon-72x72.png") ?>'
                });
            }

            // Show in-app toast notification using unified system
            const toastMessage = title && message ? `${title}: ${message}` : (message || title);
            if (typeof showToast === 'function') {
                showToast(toastMessage, type);
            }
        }

        // Make showNotification globally available
        window.showNotification = showNotification;
    </script>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?= url("public/sw.js") ?>')
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful with scope: ', registration.scope);

                        // Check for updates
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // New content is available
                                    if (window.showNotification) {
                                        showNotification(
                                            'Update Available',
                                            'A new version of Time2Eat is available. Refresh to update.',
                                            'info'
                                        );
                                    }
                                }
                            });
                        });
                    })
                    .catch(function(err) {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }

        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                console.log('Notification permission:', permission);
            });
        }
    </script>

    <!-- External Scripts -->
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="<?= url('public/js/app.js') ?>?v=<?= time() ?>"></script>

    <?php
    // Include UI Components
    require_once __DIR__ . '/../components/cart.php';

    $cartComponent = new CartComponent();

    // Render Cart Components
    echo $cartComponent->renderCartSidebar();
    echo $cartComponent->renderCartItemTemplate();
    echo $cartComponent->renderFloatingCartButton();

    // Render Scripts
    echo $cartComponent->renderCartScript();
    ?>

    <!-- QR Code Library - Using cdnjs for proper MIME type -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" 
            integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==" 
            crossorigin="anonymous" 
            referrerpolicy="no-referrer"
            onerror="console.error('QR Code library failed to load')"></script>

    <!-- PWA Manager -->
    <script src="<?= url('public/js/pwa.js') ?>"></script>

    <!-- PWA Configuration -->
    <script>
        // Pass base URL and paths to PWA Manager
        window.PWA_CONFIG = {
            baseUrl: '<?= rtrim(url(''), '/') ?>',
            swPath: '<?= url('sw.js') ?>',
            scope: '<?= rtrim(url(''), '/') ?>/'
        };
    </script>

    <!-- PWA Initialization Script -->
    <script>
        // Generate QR Code for current URL
        document.addEventListener('DOMContentLoaded', function() {
            const qrCodeElement = document.getElementById('qr-code');
            if (qrCodeElement) {
                if (typeof QRCode !== 'undefined') {
                    try {
                        QRCode.toCanvas(qrCodeElement, window.location.origin, {
                            width: 150,
                            height: 150,
                            colorDark: '#1f2937',
                            colorLight: '#ffffff',
                            margin: 2,
                            errorCorrectionLevel: 'M'
                        }, function (error) {
                            if (error) {
                                console.error('QR Code generation failed:', error);
                                qrCodeElement.innerHTML = '<div class="tw-text-gray-500 tw-text-sm">QR Code unavailable</div>';
                            }
                        });
                    } catch (error) {
                        console.error('QR Code library error:', error);
                        qrCodeElement.innerHTML = '<div class="tw-text-gray-500 tw-text-sm">QR Code unavailable</div>';
                    }
                } else {
                    console.warn('QR Code library not loaded');
                    qrCodeElement.innerHTML = '<div class="tw-text-gray-500 tw-text-sm">QR Code unavailable</div>';
                }
            }
        });

        // Share App Function
        function shareApp() {
            if (navigator.share) {
                navigator.share({
                    title: 'Time2Eat - Food Delivery',
                    text: 'Order food from your favorite restaurants in Bamenda',
                    url: window.location.origin
                }).catch(console.error);
            } else {
                // Fallback: Copy to clipboard
                navigator.clipboard.writeText(window.location.origin).then(() => {
                    if (window.pwaManager) {
                        window.pwaManager.showNotification('Link copied to clipboard!', 'success');
                    } else {
                        alert('Link copied to clipboard!');
                    }
                }).catch(() => {
                    // Fallback: Show URL
                    prompt('Copy this link:', window.location.origin);
                });
            }
        }

        // Connection Status Monitoring
        function updateConnectionStatus() {
            const statusElements = document.querySelectorAll('.connection-status');
            const isOnline = navigator.onLine;

            statusElements.forEach(element => {
                if (isOnline) {
                    element.textContent = 'Online';
                    element.className = 'connection-status tw-text-green-600 tw-font-medium';
                } else {
                    element.textContent = 'Offline';
                    element.className = 'connection-status tw-text-red-600 tw-font-medium';
                }
            });
        }

        // Initialize connection monitoring
        window.addEventListener('online', updateConnectionStatus);
        window.addEventListener('offline', updateConnectionStatus);
        document.addEventListener('DOMContentLoaded', updateConnectionStatus);

        // Notification Permission Prompt
        function requestNotificationPermission() {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        if (window.pwaManager) {
                            window.pwaManager.showNotification('Notifications enabled!', 'success');
                        }
                    }
                });
            }
        }

        // Add notification prompt to page if needed
        document.addEventListener('DOMContentLoaded', function() {
            if ('Notification' in window && Notification.permission === 'default') {
                // Show notification prompt after 5 seconds
                setTimeout(() => {
                    const notificationBanner = document.createElement('div');
                    notificationBanner.className = 'notification-prompt tw-fixed tw-bottom-4 tw-right-4 tw-bg-blue-500 tw-text-white tw-p-4 tw-rounded-lg tw-shadow-lg tw-z-50 tw-max-w-sm';
                    notificationBanner.innerHTML = `
                        <div class="tw-flex tw-items-center tw-justify-between">
                            <div class="tw-mr-3">
                                <p class="tw-font-medium tw-mb-1">Enable Notifications</p>
                                <p class="tw-text-sm tw-opacity-90">Get updates on your orders</p>
                            </div>
                            <div class="tw-flex tw-space-x-2">
                                <button onclick="requestNotificationPermission(); this.closest('.notification-prompt').remove();" class="enable-notifications tw-bg-white tw-text-blue-500 tw-px-3 tw-py-1 tw-rounded tw-text-sm tw-font-medium">
                                    Enable
                                </button>
                                <button onclick="this.closest('.notification-prompt').remove();" class="tw-text-white tw-opacity-75 tw-hover:tw-opacity-100">
                                    <i data-feather="x" class="tw-w-4 tw-h-4"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(notificationBanner);

                    // Initialize feather icons for the new elements
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                }, 5000);
            }
        });

        // Performance monitoring
        if ('performance' in window) {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    const perfData = performance.getEntriesByType('navigation')[0];
                    if (perfData) {
                        console.log('[PWA] Page load time:', perfData.loadEventEnd - perfData.fetchStart, 'ms');

                        // Track slow loads
                        if (perfData.loadEventEnd - perfData.fetchStart > 3000) {
                            console.warn('[PWA] Slow page load detected');
                        }
                    }
                }, 0);
            });
        }
    </script>
</body>
</html>
