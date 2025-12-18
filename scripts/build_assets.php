<?php
/**
 * Asset Build Script
 * Optimizes and builds frontend assets for production
 */

echo "🏗️  Building Time2Eat Assets\n";
echo "============================\n\n";

// Check if Node.js is available
$nodeVersion = shell_exec('node --version 2>/dev/null');
if (!$nodeVersion) {
    echo "⚠️  Node.js not found. Skipping CSS build.\n";
    optimizeExistingAssets();
    exit(0);
}

echo "✓ Node.js version: " . trim($nodeVersion) . "\n";

// Build Tailwind CSS
echo "\n🎨 Building Tailwind CSS...\n";
if (file_exists(__DIR__ . '/../package.json')) {
    $output = [];
    $returnCode = 0;
    exec('cd ' . __DIR__ . '/.. && npm run build-css-prod 2>&1', $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "✓ Tailwind CSS built successfully\n";
    } else {
        echo "⚠️  Tailwind CSS build failed:\n";
        echo implode("\n", $output) . "\n";
    }
} else {
    echo "⚠️  package.json not found\n";
}

// Optimize images
echo "\n🖼️  Optimizing images...\n";
optimizeImages();

// Minify JavaScript
echo "\n📦 Processing JavaScript files...\n";
processJavaScript();

// Generate asset manifest
echo "\n📋 Generating asset manifest...\n";
generateAssetManifest();

// Create service worker
echo "\n⚙️  Updating service worker...\n";
updateServiceWorker();

echo "\n✅ Asset build completed!\n";

/**
 * Optimize existing assets when Node.js is not available
 */
function optimizeExistingAssets() {
    echo "🔧 Optimizing existing assets...\n";
    
    // Minify existing CSS
    $cssFiles = glob(__DIR__ . '/../public/css/*.css');
    foreach ($cssFiles as $cssFile) {
        if (strpos($cssFile, '.min.css') === false) {
            $content = file_get_contents($cssFile);
            $minified = minifyCSS($content);
            $minFile = str_replace('.css', '.min.css', $cssFile);
            file_put_contents($minFile, $minified);
            echo "✓ Minified: " . basename($cssFile) . "\n";
        }
    }
    
    // Minify existing JavaScript
    $jsFiles = glob(__DIR__ . '/../public/js/*.js');
    foreach ($jsFiles as $jsFile) {
        if (strpos($jsFile, '.min.js') === false) {
            $content = file_get_contents($jsFile);
            $minified = minifyJS($content);
            $minFile = str_replace('.js', '.min.js', $jsFile);
            file_put_contents($minFile, $minified);
            echo "✓ Minified: " . basename($jsFile) . "\n";
        }
    }
}

/**
 * Optimize images using GD library
 */
function optimizeImages() {
    if (!extension_loaded('gd')) {
        echo "⚠️  GD extension not available. Skipping image optimization.\n";
        return;
    }
    
    $imageDir = __DIR__ . '/../public/images';
    $images = glob($imageDir . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    
    foreach ($images as $imagePath) {
        $info = pathinfo($imagePath);
        $extension = strtolower($info['extension']);
        
        // Skip if already optimized
        if (strpos($info['filename'], '_optimized') !== false) {
            continue;
        }
        
        try {
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    $image = imagecreatefromjpeg($imagePath);
                    if ($image) {
                        $optimizedPath = $info['dirname'] . '/' . $info['filename'] . '_optimized.jpg';
                        imagejpeg($image, $optimizedPath, 85);
                        imagedestroy($image);
                        echo "✓ Optimized: " . $info['basename'] . "\n";
                    }
                    break;
                    
                case 'png':
                    $image = imagecreatefrompng($imagePath);
                    if ($image) {
                        $optimizedPath = $info['dirname'] . '/' . $info['filename'] . '_optimized.png';
                        imagepng($image, $optimizedPath, 6);
                        imagedestroy($image);
                        echo "✓ Optimized: " . $info['basename'] . "\n";
                    }
                    break;
            }
        } catch (Exception $e) {
            echo "⚠️  Failed to optimize: " . $info['basename'] . "\n";
        }
    }
}

/**
 * Process and minify JavaScript files
 */
function processJavaScript() {
    $jsDir = __DIR__ . '/../public/js';
    
    // Create app.js if it doesn't exist
    $appJs = $jsDir . '/app.js';
    if (!file_exists($appJs)) {
        $jsContent = <<<JS
/**
 * Time2Eat Main JavaScript
 */

// Initialize application
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('tw-hidden');
        });
    }
    
    // Initialize notifications
    initializeNotifications();
    
    // Initialize PWA features
    initializePWA();
    
    // Initialize real-time features
    if (window.location.pathname.includes('/dashboard')) {
        initializeWebSocket();
    }
});

/**
 * Initialize notification system
 */
function initializeNotifications() {
    // Request notification permission
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
}

/**
 * Initialize PWA features
 */
function initializePWA() {
    // Service worker registration
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
            .then(function(registration) {
                console.log('ServiceWorker registered');
            })
            .catch(function(err) {
                console.log('ServiceWorker registration failed');
            });
    }
    
    // Install prompt
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        
        // Show install button
        const installBtn = document.getElementById('install-app-btn');
        if (installBtn) {
            installBtn.style.display = 'block';
            installBtn.addEventListener('click', () => {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    deferredPrompt = null;
                    installBtn.style.display = 'none';
                });
            });
        }
    });
}

/**
 * Initialize WebSocket connection for real-time features
 */
function initializeWebSocket() {
    const wsUrl = 'ws://localhost:8080';
    let ws;
    let reconnectAttempts = 0;
    const maxReconnectAttempts = 5;
    
    function connect() {
        try {
            ws = new WebSocket(wsUrl);
            
            ws.onopen = function() {
                console.log('WebSocket connected');
                reconnectAttempts = 0;
                
                // Authenticate
                const userId = document.querySelector('meta[name="user-id"]')?.content;
                const token = document.querySelector('meta[name="csrf-token"]')?.content;
                
                if (userId && token) {
                    ws.send(JSON.stringify({
                        type: 'auth',
                        user_id: userId,
                        token: token
                    }));
                }
            };
            
            ws.onmessage = function(event) {
                const data = JSON.parse(event.data);
                handleWebSocketMessage(data);
            };
            
            ws.onclose = function() {
                console.log('WebSocket disconnected');
                
                // Attempt to reconnect
                if (reconnectAttempts < maxReconnectAttempts) {
                    setTimeout(() => {
                        reconnectAttempts++;
                        connect();
                    }, 1000 * Math.pow(2, reconnectAttempts));
                }
            };
            
            ws.onerror = function(error) {
                console.error('WebSocket error:', error);
            };
            
        } catch (error) {
            console.error('Failed to connect to WebSocket:', error);
        }
    }
    
    // Start connection
    connect();
    
    // Heartbeat
    setInterval(() => {
        if (ws && ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify({ type: 'heartbeat' }));
        }
    }, 30000);
    
    // Expose WebSocket for other scripts
    window.time2eatWS = ws;
}

/**
 * Handle WebSocket messages
 */
function handleWebSocketMessage(data) {
    switch (data.type) {
        case 'order_status_updated':
            updateOrderStatus(data);
            showNotification('Order Update', `Order #\${data.order_id} is now \${data.status}`);
            break;
            
        case 'rider_location':
            updateRiderLocation(data);
            break;
            
        case 'chat_message':
            displayChatMessage(data);
            break;
            
        case 'notification':
            showNotification(data.title, data.message);
            break;
    }
}

/**
 * Show browser notification
 */
function showNotification(title, message) {
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(title, {
            body: message,
            icon: '/public/images/icon-192x192.png'
        });
    }
}

/**
 * Update order status in UI
 */
function updateOrderStatus(data) {
    const statusElement = document.querySelector(`[data-order-id="\${data.order_id}"] .order-status`);
    if (statusElement) {
        statusElement.textContent = data.status;
        statusElement.className = `order-status tw-status-\${data.status}`;
    }
}

/**
 * Update rider location on map
 */
function updateRiderLocation(data) {
    if (window.map && window.riderMarker) {
        const newPosition = { lat: data.latitude, lng: data.longitude };
        window.riderMarker.setPosition(newPosition);
        window.map.panTo(newPosition);
    }
}

/**
 * Display chat message
 */
function displayChatMessage(data) {
    const chatContainer = document.getElementById('chat-messages');
    if (chatContainer) {
        const messageElement = document.createElement('div');
        messageElement.className = 'chat-message';
        messageElement.innerHTML = `
            <div class="tw-flex tw-items-start tw-space-x-2 tw-mb-2">
                <div class="tw-font-semibold tw-text-sm">\${data.sender_name}:</div>
                <div class="tw-text-sm">\${data.message}</div>
            </div>
        `;
        chatContainer.appendChild(messageElement);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
}
JS;
        
        file_put_contents($appJs, $jsContent);
        echo "✓ Created app.js\n";
    }
    
    // Minify JavaScript files
    $jsFiles = glob($jsDir . '/*.js');
    foreach ($jsFiles as $jsFile) {
        if (strpos($jsFile, '.min.js') === false) {
            $content = file_get_contents($jsFile);
            $minified = minifyJS($content);
            $minFile = str_replace('.js', '.min.js', $jsFile);
            file_put_contents($minFile, $minified);
            echo "✓ Minified: " . basename($jsFile) . "\n";
        }
    }
}

/**
 * Generate asset manifest for cache busting
 */
function generateAssetManifest() {
    $manifest = [];
    
    // CSS files
    $cssFiles = glob(__DIR__ . '/../public/css/*.css');
    foreach ($cssFiles as $cssFile) {
        $relativePath = str_replace(__DIR__ . '/../public/', '', $cssFile);
        $manifest[$relativePath] = $relativePath . '?v=' . filemtime($cssFile);
    }
    
    // JavaScript files
    $jsFiles = glob(__DIR__ . '/../public/js/*.js');
    foreach ($jsFiles as $jsFile) {
        $relativePath = str_replace(__DIR__ . '/../public/', '', $jsFile);
        $manifest[$relativePath] = $relativePath . '?v=' . filemtime($jsFile);
    }
    
    // Image files
    $imageFiles = glob(__DIR__ . '/../public/images/*.{jpg,jpeg,png,gif,webp,svg}', GLOB_BRACE);
    foreach ($imageFiles as $imageFile) {
        $relativePath = str_replace(__DIR__ . '/../public/', '', $imageFile);
        $manifest[$relativePath] = $relativePath . '?v=' . filemtime($imageFile);
    }
    
    file_put_contents(__DIR__ . '/../public/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
    echo "✓ Asset manifest generated\n";
}

/**
 * Update service worker with new cache version
 */
function updateServiceWorker() {
    $swFile = __DIR__ . '/../sw.js';
    if (file_exists($swFile)) {
        $content = file_get_contents($swFile);
        $newVersion = 'time2eat-v' . date('Y.m.d.His');
        $content = preg_replace('/const CACHE_NAME = \'[^\']+\'/', "const CACHE_NAME = '{$newVersion}'", $content);
        file_put_contents($swFile, $content);
        echo "✓ Service worker updated with version: {$newVersion}\n";
    }
}

/**
 * Simple CSS minification
 */
function minifyCSS($css) {
    // Remove comments
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
    
    // Remove whitespace
    $css = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $css);
    
    return $css;
}

/**
 * Simple JavaScript minification
 */
function minifyJS($js) {
    // Remove single-line comments
    $js = preg_replace('/\/\/.*$/m', '', $js);
    
    // Remove multi-line comments
    $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js);
    
    // Remove extra whitespace
    $js = preg_replace('/\s+/', ' ', $js);
    
    return trim($js);
}
