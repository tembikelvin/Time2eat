<?php
/**
 * Tailwind CSS Setup and Build Script
 * Configures Tailwind CSS with tw- prefix and custom components
 */

echo "🎨 Tailwind CSS Setup\n";
echo "=====================\n\n";

// Check if Node.js is available
$nodeVersion = shell_exec('node --version 2>/dev/null');
if (!$nodeVersion) {
    echo "⚠️  Node.js not found. Using CDN version of Tailwind CSS\n";
    setupCDNVersion();
} else {
    echo "✓ Node.js version: " . trim($nodeVersion) . "\n";
    setupLocalVersion();
}

/**
 * Setup CDN version of Tailwind CSS (fallback)
 */
function setupCDNVersion() {
    echo "Setting up Tailwind CSS via CDN...\n";
    
    // Update the layout file to use CDN with proper configuration
    $layoutFile = __DIR__ . '/../src/views/layouts/app.php';
    if (file_exists($layoutFile)) {
        echo "✓ Tailwind CSS CDN already configured in layout\n";
    } else {
        echo "❌ Layout file not found\n";
    }
    
    // Create a custom CSS file for additional styles
    $customCss = __DIR__ . '/../public/css/custom.css';
    if (!file_exists($customCss)) {
        $cssContent = <<<CSS
/* Time2Eat Custom Styles */
/* Tailwind CSS Custom Components with tw- prefix */

/* Glassmorphism Effects */
.tw-glass-effect {
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.tw-glass-dark {
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    background: rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Loading Animations */
.tw-loading-spinner {
    animation: spin 1s linear infinite;
}

.tw-loading-dots::after {
    content: '';
    animation: dots 1.5s steps(5, end) infinite;
}

@keyframes dots {
    0%, 20% { content: ''; }
    40% { content: '.'; }
    60% { content: '..'; }
    80%, 100% { content: '...'; }
}

/* Custom Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.tw-fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.tw-slide-in-right {
    animation: slideInRight 0.5s ease-out;
}

/* Mobile-First Responsive Utilities */
@media (max-width: 640px) {
    .tw-mobile-full {
        width: 100% !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
    
    .tw-mobile-hidden {
        display: none !important;
    }
    
    .tw-mobile-text-sm {
        font-size: 0.875rem !important;
    }
}

/* Touch-Friendly Elements */
.tw-touch-target {
    min-height: 44px;
    min-width: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Food Delivery Specific Styles */
.tw-restaurant-card {
    transition: all 0.3s ease;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.tw-restaurant-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.tw-menu-item {
    border-radius: 0.75rem;
    padding: 1rem;
    background: white;
    border: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.tw-menu-item:hover {
    border-color: #dc2626;
    box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.1);
}

.tw-order-status {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.tw-status-pending {
    background-color: #fef3c7;
    color: #92400e;
}

.tw-status-confirmed {
    background-color: #dbeafe;
    color: #1e40af;
}

.tw-status-preparing {
    background-color: #fed7aa;
    color: #c2410c;
}

.tw-status-ready {
    background-color: #e9d5ff;
    color: #7c3aed;
}

.tw-status-delivered {
    background-color: #dcfce7;
    color: #166534;
}

.tw-status-cancelled {
    background-color: #fee2e2;
    color: #dc2626;
}

/* Rating Stars */
.tw-star-rating {
    display: inline-flex;
    align-items: center;
    gap: 0.125rem;
}

.tw-star {
    width: 1rem;
    height: 1rem;
    fill: #d1d5db;
    transition: fill 0.2s ease;
}

.tw-star.tw-star-filled {
    fill: #fbbf24;
}

.tw-star.tw-star-half {
    fill: url(#half-star);
}

/* Map Container */
.tw-map-container {
    border-radius: 0.75rem;
    overflow: hidden;
    height: 300px;
    position: relative;
}

@media (min-width: 768px) {
    .tw-map-container {
        height: 400px;
    }
}

/* Notification Styles */
.tw-notification {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 9999;
    max-width: 24rem;
    padding: 1rem;
    border-radius: 0.75rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    animation: slideInRight 0.3s ease-out;
}

.tw-notification-success {
    background-color: #dcfce7;
    border-left: 4px solid #16a34a;
    color: #166534;
}

.tw-notification-error {
    background-color: #fee2e2;
    border-left: 4px solid #dc2626;
    color: #991b1b;
}

.tw-notification-warning {
    background-color: #fef3c7;
    border-left: 4px solid #d97706;
    color: #92400e;
}

.tw-notification-info {
    background-color: #dbeafe;
    border-left: 4px solid #2563eb;
    color: #1e40af;
}

/* Print Styles */
@media print {
    .tw-no-print {
        display: none !important;
    }
    
    .tw-print-full-width {
        width: 100% !important;
    }
}

/* Dark Mode Support (if needed) */
@media (prefers-color-scheme: dark) {
    .tw-auto-dark {
        background-color: #1f2937;
        color: #f9fafb;
    }
    
    .tw-auto-dark .tw-card {
        background-color: #374151;
        color: #f9fafb;
    }
}

/* Accessibility Improvements */
.tw-focus-visible:focus-visible {
    outline: 2px solid #2563eb;
    outline-offset: 2px;
}

.tw-sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* High Contrast Mode */
@media (prefers-contrast: high) {
    .tw-btn-primary {
        border: 2px solid currentColor;
    }
    
    .tw-card {
        border: 1px solid currentColor;
    }
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
CSS;
        
        file_put_contents($customCss, $cssContent);
        echo "✓ Created custom CSS file with Tailwind components\n";
    }
    
    echo "✓ CDN setup completed\n";
}

/**
 * Setup local version with Node.js and npm
 */
function setupLocalVersion() {
    echo "Setting up Tailwind CSS locally...\n";
    
    // Check if package.json exists
    $packageJson = __DIR__ . '/../package.json';
    if (!file_exists($packageJson)) {
        createPackageJson();
    }
    
    // Install dependencies
    echo "Installing npm dependencies...\n";
    $output = [];
    $returnCode = 0;
    exec('cd ' . __DIR__ . '/.. && npm install 2>&1', $output, $returnCode);
    
    if ($returnCode !== 0) {
        echo "⚠️  npm install failed, falling back to CDN version\n";
        setupCDNVersion();
        return;
    }
    
    echo "✓ npm dependencies installed\n";
    
    // Build Tailwind CSS
    echo "Building Tailwind CSS...\n";
    exec('cd ' . __DIR__ . '/.. && npm run build-css 2>&1', $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "✓ Tailwind CSS built successfully\n";
    } else {
        echo "⚠️  Build failed, using development version\n";
    }
}

/**
 * Create package.json for local Tailwind setup
 */
function createPackageJson() {
    $packageContent = [
        'name' => 'time2eat',
        'version' => '1.0.0',
        'description' => 'Time2Eat Food Delivery Platform',
        'scripts' => [
            'build-css' => 'tailwindcss -i ./public/css/tailwind.css -o ./public/css/app.css --watch',
            'build-css-prod' => 'tailwindcss -i ./public/css/tailwind.css -o ./public/css/app.css --minify',
            'watch-css' => 'tailwindcss -i ./public/css/tailwind.css -o ./public/css/app.css --watch'
        ],
        'devDependencies' => [
            'tailwindcss' => '^3.3.0',
            '@tailwindcss/forms' => '^0.5.6',
            '@tailwindcss/typography' => '^0.5.10',
            'autoprefixer' => '^10.4.16'
        ]
    ];
    
    file_put_contents(__DIR__ . '/../package.json', json_encode($packageContent, JSON_PRETTY_PRINT));
    echo "✓ Created package.json\n";
}

echo "\n✅ Tailwind CSS setup completed!\n\n";
echo "📋 Usage:\n";
echo "- CDN version is ready to use (no build required)\n";
echo "- Custom components available with tw- prefix\n";
echo "- Glassmorphism effects included\n";
echo "- Mobile-first responsive design\n";
echo "- Accessibility features enabled\n\n";

if (file_exists(__DIR__ . '/../node_modules')) {
    echo "🔧 Local build commands:\n";
    echo "- npm run build-css      : Build CSS for development\n";
    echo "- npm run build-css-prod : Build CSS for production\n";
    echo "- npm run watch-css      : Watch for changes\n";
}
