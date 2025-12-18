/**
 * Time2Eat Service Worker
 * Provides offline functionality and caching for PWA
 */

const CACHE_NAME = 'time2eat-v1.0.0';
const STATIC_CACHE = 'time2eat-static-v1.0.0';
const DYNAMIC_CACHE = 'time2eat-dynamic-v1.0.0';

/**
 * Environment-aware URL helper (JavaScript version)
 * Detects development vs production environment and generates correct URLs
 */
function getAppBasePath() {
    // Check if we're in development (localhost with /eat path)
    const hostname = window.location.hostname;
    const pathname = window.location.pathname;
    
    // Development indicators
    const isDevelopment = hostname === 'localhost' || 
                         hostname === '127.0.0.1' || 
                         hostname.includes('.local') ||
                         hostname.includes('.test') ||
                         hostname.includes('.dev');
    
    if (isDevelopment && pathname.includes('/eat')) {
        return '/eat';
    }
    
    // Production - usually at root
    return '';
}

/**
 * Generate environment-aware URL
 */
function url(path = '') {
    const basePath = getAppBasePath();
    const cleanPath = path.startsWith('/') ? path.substring(1) : path;
    
    if (!cleanPath) {
        return basePath || '/';
    }
    
    return basePath + '/' + cleanPath;
}

/**
 * Generate asset URL (for public files)
 */
function asset(path) {
    return url('public/' + path.replace(/^\/?public\//, ''));
}

// Files to cache immediately (ONLY static assets, NOT HTML pages)
// Using environment-aware URLs
const STATIC_FILES = [
    asset('css/app.css'),
    asset('js/app.js'),
    asset('images/hero.webp'),
    asset('manifest.json'),
    asset('offline.html'),
    'https://cdn.tailwindcss.com',
    'https://unpkg.com/feather-icons',
    'https://fonts.googleapis.com/icon?family=Material+Icons',
    'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap'
];

// Files to cache dynamically
const DYNAMIC_FILES = [
    '/api/',
    '/dashboard',
    '/customer/',
    '/vendor/',
    '/rider/',
    '/admin/'
];

// Install event - cache static files
self.addEventListener('install', event => {
    console.log('Service Worker: Installing...');
    
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => {
                console.log('Service Worker: Caching static files');
                return cache.addAll(STATIC_FILES);
            })
            .then(() => {
                console.log('Service Worker: Static files cached');
                return self.skipWaiting();
            })
            .catch(err => {
                console.error('Service Worker: Error caching static files', err);
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
    console.log('Service Worker: Activating...');
    
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE) {
                            console.log('Service Worker: Deleting old cache', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('Service Worker: Activated');
                return self.clients.claim();
            })
    );
});

// Fetch event - serve from cache or network
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // Skip chrome-extension and other non-http requests
    if (!url.protocol.startsWith('http')) {
        return;
    }
    
    event.respondWith(
        caches.match(request)
            .then(cachedResponse => {
                if (cachedResponse) {
                    console.log('Service Worker: Serving from cache', request.url);
                    return cachedResponse;
                }
                
                // Not in cache, fetch from network
                return fetch(request)
                    .then(networkResponse => {
                        // Check if valid response
                        if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
                            return networkResponse;
                        }
                        
                        // Clone response for caching
                        const responseToCache = networkResponse.clone();
                        
                        // Cache dynamic content
                        if (shouldCacheDynamically(request.url)) {
                            caches.open(DYNAMIC_CACHE)
                                .then(cache => {
                                    console.log('Service Worker: Caching dynamic content', request.url);
                                    cache.put(request, responseToCache);
                                });
                        }
                        
                        return networkResponse;
                    })
                    .catch(err => {
                        console.log('Service Worker: Network fetch failed', err);
                        
                        // Return offline fallback for HTML pages
                        if (request.headers.get('accept').includes('text/html')) {
                            return caches.match('/offline.html') || 
                                   new Response('<h1>Offline</h1><p>Please check your internet connection.</p>', {
                                       headers: { 'Content-Type': 'text/html' }
                                   });
                        }
                        
                        // Return offline fallback for images
                        if (request.headers.get('accept').includes('image')) {
                            return caches.match(asset('images/offline.png')) ||
                                   new Response('', { status: 404 });
                        }
                        
                        throw err;
                    });
            })
    );
});

// Helper function to determine if content should be cached dynamically
function shouldCacheDynamically(requestUrl) {
    const basePath = getAppBasePath();
    return DYNAMIC_FILES.some(pattern => requestUrl.includes(pattern)) ||
           requestUrl.includes(asset('images/')) ||
           requestUrl.includes(url('api/menu/')) ||
           requestUrl.includes(url('api/restaurants/'));
}

// Background sync for offline orders
self.addEventListener('sync', event => {
    console.log('Service Worker: Background sync', event.tag);
    
    if (event.tag === 'order-sync') {
        event.waitUntil(syncOrders());
    }
    
    if (event.tag === 'cart-sync') {
        event.waitUntil(syncCart());
    }
});

// Sync offline orders when connection is restored
async function syncOrders() {
    try {
        const orders = await getOfflineOrders();
        
        for (const order of orders) {
            try {
                const response = await fetch(url('api/orders'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(order)
                });
                
                if (response.ok) {
                    await removeOfflineOrder(order.id);
                    console.log('Service Worker: Order synced successfully', order.id);
                }
            } catch (err) {
                console.error('Service Worker: Failed to sync order', order.id, err);
            }
        }
    } catch (err) {
        console.error('Service Worker: Error syncing orders', err);
    }
}

// Sync cart data
async function syncCart() {
    try {
        const cart = await getOfflineCart();
        
        if (cart && cart.items.length > 0) {
            const response = await fetch(url('api/cart/sync'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(cart)
            });
            
            if (response.ok) {
                await clearOfflineCart();
                console.log('Service Worker: Cart synced successfully');
            }
        }
    } catch (err) {
        console.error('Service Worker: Error syncing cart', err);
    }
}

// Push notification handling
self.addEventListener('push', event => {
    console.log('Service Worker: Push notification received', event);

    let notificationData = {
        title: 'Time2Eat',
        body: 'Your order status has been updated!',
        icon: asset('images/icons/icon-192x192.png'),
        badge: asset('images/icons/badge-72x72.png'),
        tag: 'time2eat-notification',
        requireInteraction: false,
        vibrate: [200, 100, 200],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1,
            url: url('dashboard')
        },
        actions: [
            {
                action: 'view',
                title: 'View Order',
                icon: asset('images/icons/action-view.png')
            },
            {
                action: 'close',
                title: 'Close',
                icon: asset('images/icons/action-close.png')
            }
        ]
    };

    // Parse notification data from push event
    if (event.data) {
        try {
            const data = event.data.json();
            notificationData.title = data.title || notificationData.title;
            notificationData.body = data.body || data.message || notificationData.body;
            notificationData.icon = data.icon || notificationData.icon;
            notificationData.tag = data.tag || notificationData.tag;
            notificationData.data = { ...notificationData.data, ...data };

            // Set custom actions based on notification type
            if (data.type === 'order') {
                notificationData.icon = asset('images/icons/order-icon.png');
                notificationData.badge = asset('images/icons/order-badge.png');
                notificationData.data.url = url('dashboard');
            } else if (data.type === 'delivery') {
                notificationData.icon = asset('images/icons/delivery-icon.png');
                notificationData.badge = asset('images/icons/delivery-badge.png');
                notificationData.data.url = url('track/' + (data.orderId || ''));
            } else if (data.type === 'message') {
                notificationData.icon = asset('images/icons/message-icon.png');
                notificationData.badge = asset('images/icons/message-badge.png');
                notificationData.data.url = url('messages');
            }
        } catch (error) {
            console.error('Service Worker: Error parsing push data', error);
        }
    }

    event.waitUntil(
        self.registration.showNotification(notificationData.title, {
            body: notificationData.body,
            icon: notificationData.icon,
            badge: notificationData.badge,
            tag: notificationData.tag,
            requireInteraction: notificationData.requireInteraction,
            vibrate: notificationData.vibrate,
            data: notificationData.data,
            actions: notificationData.actions
        })
    );
});

// Notification click handling
self.addEventListener('notificationclick', event => {
    console.log('Service Worker: Notification clicked', event.action);

    event.notification.close();

    // Handle action buttons
    if (event.action === 'close') {
        // Just close the notification
        return;
    }

    // Determine URL to open
    let urlToOpen = url('/');

    if (event.action === 'view') {
        urlToOpen = event.notification.data?.url || url('dashboard');
    } else {
        // Default action (clicking notification body)
        urlToOpen = event.notification.data?.url || url('dashboard');
    }

    // Open or focus existing window
    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        })
        .then(windowClients => {
            // Check if there's already a window open
            for (let client of windowClients) {
                if (client.url.includes(urlToOpen) && 'focus' in client) {
                    return client.focus();
                }
            }

            // No existing window, open new one
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});

// Helper functions for offline storage
async function getOfflineOrders() {
    // Implementation would use IndexedDB
    return [];
}

async function removeOfflineOrder(orderId) {
    // Implementation would use IndexedDB
    return true;
}

async function getOfflineCart() {
    // Implementation would use IndexedDB
    return null;
}

async function clearOfflineCart() {
    // Implementation would use IndexedDB
    return true;
}
