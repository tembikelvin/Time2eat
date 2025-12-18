// Time2Eat Service Worker
// Version 1.3.0 - Fixed dashboard and API endpoint caching issues

const CACHE_NAME = 'time2eat-v1.3.0';

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

const OFFLINE_URL = asset('offline.html');
const FALLBACK_IMAGE = asset('images/fallback-food.jpg');

// Resources to cache immediately
const STATIC_CACHE_URLS = [
  url('/'),
  asset('offline.html'),
  url('browse'),
  url('about'),
  url('contact'),
  asset('manifest.json'),
  asset('images/fallback-food.jpg'),
  asset('images/icons/icon-192x192.png'),
  asset('images/icons/icon-512x512.png'),
  // CSS and JS files
  'https://cdn.tailwindcss.com',
  'https://unpkg.com/feather-icons',
  // Fonts
  'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap'
];

// Resources to cache on first visit
const DYNAMIC_CACHE_PATTERNS = [
  /^https:\/\/.*\.(?:png|jpg|jpeg|svg|gif|webp)$/,
  new RegExp('^' + url('api/restaurants')),
  new RegExp('^' + url('api/menu')),
  new RegExp('^' + asset('images/')),
  new RegExp('^' + asset('css/')),
  new RegExp('^' + asset('js/'))
];

// Network-first patterns (NEVER cache these - user-specific data)
const NETWORK_FIRST_PATTERNS = [
  new RegExp(url('api/cart/')),
  new RegExp(url('api/orders/')),
  new RegExp(url('api/user/')),
  new RegExp(url('api/profile/')),
  new RegExp(url('api/checkout/')),
  new RegExp(url('api/payment')),
  new RegExp(url('api/notifications/')),
  new RegExp(url('api/auth/')),
  new RegExp(url('api/affiliate/')),
  new RegExp(url('api/tracking/')),
  new RegExp(url('api/rider/')),
  new RegExp(url('rider/toggle-availability')),
  new RegExp(url('rider/dashboard')),
  new RegExp(url('rider/available')),
  new RegExp(url('rider/deliveries')),
  new RegExp(url('rider/earnings')),
  new RegExp(url('vendor/dashboard')),
  new RegExp(url('customer/dashboard')),
  new RegExp(url('admin/dashboard'))
];

// Cache-first patterns (serve from cache if available)
const CACHE_FIRST_PATTERNS = [
  new RegExp('^' + asset('images/')),
  /^https:\/\/.*\.(?:png|jpg|jpeg|svg|gif|webp)$/,
  /^https:\/\/fonts\./,
  /^https:\/\/cdn\./
];

// Install event - cache static resources
self.addEventListener('install', event => {
  console.log('[SW] Installing service worker...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('[SW] Caching static resources');
        return cache.addAll(STATIC_CACHE_URLS);
      })
      .then(() => {
        console.log('[SW] Static resources cached successfully');
        return self.skipWaiting();
      })
      .catch(error => {
        console.error('[SW] Failed to cache static resources:', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  console.log('[SW] Activating service worker...');
  
  event.waitUntil(
    caches.keys()
      .then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            if (cacheName !== CACHE_NAME) {
              console.log('[SW] Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('[SW] Service worker activated');
        return self.clients.claim();
      })
  );
});

// Fetch event - handle network requests


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
  
  event.respondWith(handleFetch(request));
});

// Handle fetch requests with different strategies
async function handleFetch(request) {
  const url = new URL(request.url);
  
  try {
    // Bypass service worker completely for cart APIs to prevent caching
    if (request.url.includes('/api/cart/')) {
      return fetch(request);
    }
    
    // Network-first strategy for critical API calls
    if (NETWORK_FIRST_PATTERNS.some(pattern => pattern.test(request.url))) {
      return await networkFirst(request);
    }
    
    // Cache-first strategy for static assets
    if (CACHE_FIRST_PATTERNS.some(pattern => pattern.test(request.url))) {
      return await cacheFirst(request);
    }
    
    // Stale-while-revalidate for dynamic content
    if (DYNAMIC_CACHE_PATTERNS.some(pattern => pattern.test(request.url))) {
      return await staleWhileRevalidate(request);
    }
    
    // Default: Network-first with offline fallback
    return await networkFirstWithFallback(request);
    
  } catch (error) {
    console.error('[SW] Fetch error:', error);
    return await getOfflineFallback(request);
  }
}

// Network-first strategy (for user-specific data - DO NOT CACHE)
async function networkFirst(request) {
  try {
    // Force fresh request - bypass cache completely
    const networkResponse = await fetch(request, {
      cache: 'no-store',
      headers: {
        'Cache-Control': 'no-cache, no-store, must-revalidate',
        'Pragma': 'no-cache'
      }
    });
    
    // IMPORTANT: Do NOT cache user-specific data (cart, orders, profile, dashboards, etc.)
    // These must always be fresh from the server
    
    return networkResponse;
  } catch (error) {
    // For user-specific data, do NOT serve from cache
    // Return offline error instead
    return new Response(JSON.stringify({
      success: false,
      message: 'Unable to connect to server. Please check your internet connection.',
      offline: true
    }), {
      status: 503,
      headers: { 
        'Content-Type': 'application/json',
        'Cache-Control': 'no-cache, no-store, must-revalidate'
      }
    });
  }
}

// Cache-first strategy
async function cacheFirst(request) {
  const cachedResponse = await caches.match(request);
  
  if (cachedResponse) {
    return cachedResponse;
  }
  
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    return await getOfflineFallback(request);
  }
}

// Stale-while-revalidate strategy
async function staleWhileRevalidate(request) {
  const cachedResponse = await caches.match(request);
  
  const networkResponsePromise = fetch(request)
    .then(networkResponse => {
      if (networkResponse.ok) {
        const cache = caches.open(CACHE_NAME);
        cache.then(c => c.put(request, networkResponse.clone()));
      }
      return networkResponse;
    })
    .catch(() => null);
  
  return cachedResponse || await networkResponsePromise || await getOfflineFallback(request);
}

// Network-first with offline fallback
async function networkFirstWithFallback(request) {
  try {
    // Force fresh request for dashboard and user-specific pages
    const urlObj = new URL(request.url);
    const isDashboard = urlObj.pathname.includes('/dashboard') || 
                       urlObj.pathname.includes('/rider/') ||
                       urlObj.pathname.includes('/vendor/') ||
                       urlObj.pathname.includes('/customer/') ||
                       urlObj.pathname.includes('/admin/');
    
    const fetchOptions = isDashboard ? {
      cache: 'no-store',
      headers: {
        'Cache-Control': 'no-cache, no-store, must-revalidate',
        'Pragma': 'no-cache'
      }
    } : {};
    
    const networkResponse = await fetch(request, fetchOptions);
    
    // CRITICAL: Check Cache-Control header before caching
    // DO NOT cache pages with no-cache directive (user-specific pages like checkout, cart, dashboard)
    const cacheControl = networkResponse.headers.get('cache-control');
    const shouldCache = networkResponse.ok && 
                       !isDashboard &&
                       (!cacheControl || !cacheControl.includes('no-cache'));
    
    if (shouldCache) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    const cachedResponse = await caches.match(request);
    return cachedResponse || await getOfflineFallback(request);
  }
}

// Get appropriate offline fallback
async function getOfflineFallback(request) {
  const url = new URL(request.url);
  
  // HTML pages - return offline page
  if (request.headers.get('accept')?.includes('text/html')) {
    return await caches.match(OFFLINE_URL) || new Response('Offline', { status: 503 });
  }
  
  // Images - return fallback image
  if (request.headers.get('accept')?.includes('image/')) {
    return await caches.match(FALLBACK_IMAGE) || new Response('', { status: 503 });
  }
  
  // API calls - return offline JSON response
  if (url.pathname.startsWith('/api/')) {
    return new Response(JSON.stringify({
      success: false,
      message: 'You are currently offline. Please check your internet connection.',
      offline: true
    }), {
      status: 503,
      headers: { 'Content-Type': 'application/json' }
    });
  }
  
  // Default fallback
  return new Response('Resource not available offline', { status: 503 });
}

// Push notification event
self.addEventListener('push', event => {
  console.log('[SW] Push notification received');
  
  let notificationData = {
    title: 'Time2Eat',
    body: 'You have a new message!',
    icon: asset('images/icons/icon-192x192.png'),
    badge: asset('images/icons/badge-72x72.png'),
    vibrate: [200, 100, 200, 100, 200],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1,
      url: url('customer/messages')
    },
    actions: [
      {
        action: 'view',
        title: 'View Messages',
        icon: asset('images/icons/action-view.png')
      },
      {
        action: 'close',
        title: 'Close',
        icon: asset('images/icons/action-close.png')
      }
    ],
    requireInteraction: false,
    silent: false,
    tag: 'message-notification',
    timestamp: Date.now(),
    renotify: true,
    dir: 'ltr',
    lang: 'en'
  };
  
  if (event.data) {
    try {
      const data = event.data.json();
      notificationData.title = data.title || 'Time2Eat';
      notificationData.body = data.body || notificationData.body;
      notificationData.icon = data.icon || notificationData.icon;
      notificationData.data = { ...notificationData.data, ...data.data };
      
      // Set appropriate URL based on notification type
      if (data.type === 'message') {
        notificationData.data.url = url('customer/messages');
        notificationData.tag = 'message-notification';
        notificationData.icon = asset('images/icons/message-icon.png');
        notificationData.badge = asset('images/icons/message-badge.png');
      } else if (data.type === 'order') {
        notificationData.data.url = url('customer/orders');
        notificationData.tag = 'order-notification';
        notificationData.icon = asset('images/icons/order-icon.png');
        notificationData.badge = asset('images/icons/order-badge.png');
      } else if (data.type === 'delivery') {
        notificationData.data.url = url('customer/orders');
        notificationData.tag = 'delivery-notification';
        notificationData.icon = asset('images/icons/delivery-icon.png');
        notificationData.badge = asset('images/icons/delivery-badge.png');
      } else if (data.url) {
        notificationData.data.url = url(data.url);
      }
      
      // Update actions based on type
      if (data.type === 'message') {
        notificationData.actions[0].title = 'View Messages';
        notificationData.actions[0].icon = asset('images/icons/action-message.png');
      } else if (data.type === 'order') {
        notificationData.actions[0].title = 'View Orders';
        notificationData.actions[0].icon = asset('images/icons/action-order.png');
      } else if (data.type === 'delivery') {
        notificationData.actions[0].title = 'Track Order';
        notificationData.actions[0].icon = asset('images/icons/action-track.png');
      }
      
      // Customize vibration pattern based on type
      if (data.type === 'message') {
        notificationData.vibrate = [200, 100, 200];
      } else if (data.type === 'order') {
        notificationData.vibrate = [300, 100, 300, 100, 300];
      } else if (data.type === 'delivery') {
        notificationData.vibrate = [100, 50, 100, 50, 100, 50, 100];
      }
      
    } catch (error) {
      console.error('[SW] Error parsing push data:', error);
    }
  }
  
  event.waitUntil(
    self.registration.showNotification(notificationData.title, notificationData)
  );
});

// Notification click event
self.addEventListener('notificationclick', event => {
  console.log('[SW] Notification clicked:', event.action);
  
  event.notification.close();
  
  let urlToOpen = url('/');
  
  // Handle action buttons
  if (event.action === 'view') {
    urlToOpen = event.notification.data?.url || url('customer/messages');
  } else if (event.action === 'close') {
    return; // Just close the notification
  } else {
    // Default click behavior
    urlToOpen = event.notification.data?.url || url('customer/messages');
  }
  
  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true })
      .then(clientList => {
        // Check if there's already a window/tab open with the target URL
        for (const client of clientList) {
          if (client.url.includes(urlToOpen.split('/')[1]) && 'focus' in client) {
            return client.focus();
          }
        }
        
        // If no existing window/tab, open a new one
        if (self.clients.openWindow) {
          return self.clients.openWindow(urlToOpen);
        }
      })
  );
});

// Background sync event
self.addEventListener('sync', event => {
  console.log('[SW] Background sync triggered:', event.tag);
  
  if (event.tag === 'background-sync-orders') {
    event.waitUntil(syncOrders());
  }
  
  if (event.tag === 'background-sync-cart') {
    event.waitUntil(syncCart());
  }
});

// Sync pending orders when back online
async function syncOrders() {
  try {
    // Get pending orders from IndexedDB or localStorage
    const pendingOrders = await getPendingOrders();
    
    for (const order of pendingOrders) {
      try {
        const response = await fetch(url('api/orders'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(order)
        });
        
        if (response.ok) {
          await removePendingOrder(order.id);
          console.log('[SW] Order synced successfully:', order.id);
        }
      } catch (error) {
        console.error('[SW] Failed to sync order:', error);
      }
    }
  } catch (error) {
    console.error('[SW] Background sync failed:', error);
  }
}

// Sync cart when back online
async function syncCart() {
  try {
    const cartData = await getOfflineCart();
    
    if (cartData) {
      const response = await fetch(url('api/cart/sync'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(cartData)
      });
      
      if (response.ok) {
        await clearOfflineCart();
        console.log('[SW] Cart synced successfully');
      }
    }
  } catch (error) {
    console.error('[SW] Cart sync failed:', error);
  }
}

// Helper functions for offline data management
async function getPendingOrders() {
  // Implementation would use IndexedDB or localStorage
  return [];
}

async function removePendingOrder(orderId) {
  // Implementation would remove from IndexedDB or localStorage
}

async function getOfflineCart() {
  // Implementation would get cart from IndexedDB or localStorage
  return null;
}

async function clearOfflineCart() {
  // Implementation would clear cart from IndexedDB or localStorage
}

// Message event for communication with main thread
self.addEventListener('message', event => {
  console.log('[SW] Message received:', event.data);
  
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'GET_VERSION') {
    event.ports[0].postMessage({ version: CACHE_NAME });
  }
});
