/**
 * Time2Eat PWA Manager
 * Handles PWA installation, service worker registration, and offline functionality
 */

class PWAManager {
    constructor() {
        this.deferredPrompt = null;
        this.isInstalled = false;
        this.isOnline = navigator.onLine;
        this.swRegistration = null;
        
        // Get configuration from window.PWA_CONFIG (set by PHP url() helper)
        this.config = window.PWA_CONFIG || this.getDefaultConfig();
        
        this.init();
    }
    
    // Get default configuration if not provided by server
    getDefaultConfig() {
        console.warn('[PWA] Using fallback configuration. PWA_CONFIG not found.');
        const path = window.location.pathname;
        const match = path.match(/^(\/[^\/]+)/);
        const basePath = match ? match[1] : '';
        
        return {
            baseUrl: window.location.origin + basePath,
            swPath: basePath ? `${basePath}/sw.js` : '/sw.js',
            scope: basePath ? `${basePath}/` : '/'
        };
    }

    async init() {
        this.checkInstallation();
        this.setupEventListeners();
        await this.registerServiceWorker();
        this.setupPushNotifications();
        this.initOfflineSupport();
    }

    // Check if app is already installed
    checkInstallation() {
        // Check if running in standalone mode
        this.isInstalled = window.matchMedia('(display-mode: standalone)').matches ||
                          window.navigator.standalone ||
                          document.referrer.includes('android-app://');
        
        if (this.isInstalled) {
            this.hideInstallPrompts();
        }
    }

    // Setup event listeners
    setupEventListeners() {
        // PWA install prompt
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('[PWA] Install prompt available');
            e.preventDefault();
            this.deferredPrompt = e;
            this.showInstallButton();
        });

        // App installed
        window.addEventListener('appinstalled', () => {
            console.log('[PWA] App installed successfully');
            this.isInstalled = true;
            this.hideInstallPrompts();
            this.showNotification('Time2Eat installed successfully!', 'success');
        });

        // Online/offline events
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.handleOnline();
        });

        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.handleOffline();
        });

        // Service worker updates
        document.addEventListener('swUpdated', () => {
            this.showUpdateAvailable();
        });
    }

    // Register service worker
    async registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                this.swRegistration = await navigator.serviceWorker.register(this.config.swPath, {
                    scope: this.config.scope
                });

                console.log('[PWA] Service worker registered:', this.swRegistration, 'Path:', this.config.swPath, 'Scope:', this.config.scope);

                // Check for updates
                this.swRegistration.addEventListener('updatefound', () => {
                    const newWorker = this.swRegistration.installing;
                    
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            // New content available
                            document.dispatchEvent(new CustomEvent('swUpdated'));
                        }
                    });
                });

                // Handle messages from service worker
                navigator.serviceWorker.addEventListener('message', (event) => {
                    this.handleServiceWorkerMessage(event.data);
                });

            } catch (error) {
                console.error('[PWA] Service worker registration failed:', error);
            }
        }
    }

    // Show install button
    showInstallButton() {
        const installButtons = document.querySelectorAll('.pwa-install-button');
        installButtons.forEach(button => {
            button.style.display = 'block';
            button.addEventListener('click', () => this.installApp());
        });
    }

    // Hide install prompts
    hideInstallPrompts() {
        const installButtons = document.querySelectorAll('.pwa-install-button');
        const installSections = document.querySelectorAll('.pwa-install-section');
        
        installButtons.forEach(button => button.style.display = 'none');
        installSections.forEach(section => section.style.display = 'none');
    }

    // Install the app
    async installApp() {
        if (!this.deferredPrompt) {
            this.showNotification('Installation not available', 'error');
            return;
        }

        try {
            this.deferredPrompt.prompt();
            const { outcome } = await this.deferredPrompt.userChoice;
            
            if (outcome === 'accepted') {
                console.log('[PWA] User accepted install prompt');
                this.trackEvent('pwa_install_accepted');
            } else {
                console.log('[PWA] User dismissed install prompt');
                this.trackEvent('pwa_install_dismissed');
            }
            
            this.deferredPrompt = null;
        } catch (error) {
            console.error('[PWA] Install failed:', error);
            this.showNotification('Installation failed', 'error');
        }
    }

    // Setup push notifications
    async setupPushNotifications() {
        if (!('Notification' in window) || !('serviceWorker' in navigator)) {
            console.log('[PWA] Push notifications not supported');
            return;
        }

        // Check current permission
        if (Notification.permission === 'granted') {
            await this.subscribeToPush();
        } else if (Notification.permission !== 'denied') {
            this.showNotificationPrompt();
        }
    }

    // Show notification permission prompt
    showNotificationPrompt() {
        const notificationPrompts = document.querySelectorAll('.notification-prompt');
        notificationPrompts.forEach(prompt => {
            prompt.style.display = 'block';
            
            const enableButton = prompt.querySelector('.enable-notifications');
            if (enableButton) {
                enableButton.addEventListener('click', () => this.requestNotificationPermission());
            }
        });
    }

    // Request notification permission
    async requestNotificationPermission() {
        try {
            const permission = await Notification.requestPermission();
            
            if (permission === 'granted') {
                console.log('[PWA] Notification permission granted');
                await this.subscribeToPush();
                this.hideNotificationPrompts();
                this.showNotification('Notifications enabled!', 'success');
                this.trackEvent('notifications_enabled');
            } else {
                console.log('[PWA] Notification permission denied');
                this.trackEvent('notifications_denied');
            }
        } catch (error) {
            console.error('[PWA] Notification permission request failed:', error);
        }
    }

    // Subscribe to push notifications
    async subscribeToPush() {
        if (!this.swRegistration) {
            console.log('[PWA] Service worker not registered');
            return;
        }

        try {
            // Fetch VAPID public key from server
            const vapidKey = await this.fetchVapidPublicKey();
            if (!vapidKey) {
                console.error('[PWA] Failed to get VAPID public key');
                return;
            }

            const subscription = await this.swRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(vapidKey)
            });

            console.log('[PWA] Push subscription created:', subscription);

            // Send subscription to server
            await this.sendSubscriptionToServer(subscription);

        } catch (error) {
            console.error('[PWA] Push subscription failed:', error);
        }
    }

    // Hide notification prompts
    hideNotificationPrompts() {
        const notificationPrompts = document.querySelectorAll('.notification-prompt');
        notificationPrompts.forEach(prompt => prompt.style.display = 'none');
    }

    // Handle online event
    handleOnline() {
        console.log('[PWA] Back online');
        this.showNotification('Connection restored', 'success');
        this.syncOfflineData();
        this.updateOnlineStatus(true);
    }

    // Handle offline event
    handleOffline() {
        console.log('[PWA] Gone offline');
        this.showNotification('You are now offline', 'warning');
        this.updateOnlineStatus(false);
    }

    // Update online status in UI
    updateOnlineStatus(isOnline) {
        const statusIndicators = document.querySelectorAll('.connection-status');
        statusIndicators.forEach(indicator => {
            if (isOnline) {
                indicator.classList.remove('offline');
                indicator.classList.add('online');
                indicator.textContent = 'Online';
            } else {
                indicator.classList.remove('online');
                indicator.classList.add('offline');
                indicator.textContent = 'Offline';
            }
        });
    }

    // Initialize offline support
    initOfflineSupport() {
        // Cache important data for offline use
        this.cacheEssentialData();
        
        // Setup offline form handling
        this.setupOfflineForms();
        
        // Setup background sync
        this.setupBackgroundSync();
    }

    // Cache essential data
    async cacheEssentialData() {
        if (!this.swRegistration) return;

        try {
            // Cache restaurant data
            const restaurants = await this.fetchWithFallback('/api/restaurants');
            if (restaurants) {
                localStorage.setItem('cached_restaurants', JSON.stringify(restaurants));
            }

            // Cache user's favorite restaurants
            const favorites = await this.fetchWithFallback('/api/user/favorites');
            if (favorites) {
                localStorage.setItem('cached_favorites', JSON.stringify(favorites));
            }

        } catch (error) {
            console.error('[PWA] Failed to cache essential data:', error);
        }
    }

    // Setup offline form handling
    setupOfflineForms() {
        const forms = document.querySelectorAll('form[data-offline-sync]');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.isOnline) {
                    e.preventDefault();
                    this.handleOfflineFormSubmission(form);
                }
            });
        });
    }

    // Handle offline form submission
    handleOfflineFormSubmission(form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // Store form data for later sync
        const offlineData = JSON.parse(localStorage.getItem('offline_forms') || '[]');
        offlineData.push({
            id: Date.now(),
            action: form.action,
            method: form.method,
            data: data,
            timestamp: new Date().toISOString()
        });
        
        localStorage.setItem('offline_forms', JSON.stringify(offlineData));
        
        this.showNotification('Form saved. Will sync when online.', 'info');
        
        // Register background sync
        if (this.swRegistration && 'sync' in this.swRegistration) {
            this.swRegistration.sync.register('background-sync-forms');
        }
    }

    // Setup background sync
    setupBackgroundSync() {
        if (!this.swRegistration || !('sync' in this.swRegistration)) {
            console.log('[PWA] Background sync not supported');
            return;
        }

        // Register sync events
        this.swRegistration.sync.register('background-sync-cart');
        this.swRegistration.sync.register('background-sync-orders');
    }

    // Sync offline data when back online
    async syncOfflineData() {
        try {
            // Sync offline forms
            const offlineForms = JSON.parse(localStorage.getItem('offline_forms') || '[]');
            
            for (const formData of offlineForms) {
                try {
                    const response = await fetch(formData.action, {
                        method: formData.method,
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(formData.data)
                    });
                    
                    if (response.ok) {
                        console.log('[PWA] Offline form synced:', formData.id);
                    }
                } catch (error) {
                    console.error('[PWA] Failed to sync form:', error);
                }
            }
            
            // Clear synced forms
            localStorage.removeItem('offline_forms');
            
        } catch (error) {
            console.error('[PWA] Offline data sync failed:', error);
        }
    }

    // Show update available notification
    showUpdateAvailable() {
        const updateBanner = document.createElement('div');
        updateBanner.className = 'pwa-update-banner';
        updateBanner.innerHTML = `
            <div class="tw-bg-blue-500 tw-text-white tw-p-4 tw-fixed tw-top-0 tw-left-0 tw-right-0 tw-z-50">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <span>A new version is available!</span>
                    <button onclick="pwaManager.updateApp()" class="tw-bg-white tw-text-blue-500 tw-px-4 tw-py-2 tw-rounded tw-font-medium">
                        Update Now
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(updateBanner);
    }

    // Update the app
    updateApp() {
        if (this.swRegistration && this.swRegistration.waiting) {
            this.swRegistration.waiting.postMessage({ type: 'SKIP_WAITING' });
            window.location.reload();
        }
    }

    // Handle service worker messages
    handleServiceWorkerMessage(data) {
        switch (data.type) {
            case 'CACHE_UPDATED':
                console.log('[PWA] Cache updated');
                break;
            case 'OFFLINE_FALLBACK':
                this.showNotification('Loading offline content', 'info');
                break;
            default:
                console.log('[PWA] Service worker message:', data);
        }
    }

    // Utility functions
    async fetchWithFallback(url) {
        try {
            const response = await fetch(url);
            return response.ok ? await response.json() : null;
        } catch (error) {
            console.error('[PWA] Fetch failed:', url, error);
            return null;
        }
    }

    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    async fetchVapidPublicKey() {
        try {
            const response = await fetch('/api/notifications/vapid-key');
            if (!response.ok) {
                throw new Error('Failed to fetch VAPID key');
            }
            const data = await response.json();
            if (data.success && data.publicKey) {
                console.log('[PWA] VAPID public key fetched from server');
                return data.publicKey;
            }
            throw new Error('Invalid VAPID key response');
        } catch (error) {
            console.error('[PWA] Failed to fetch VAPID key:', error);
            // Fallback to hardcoded key (should match config/vapid.php)
            return 'BHjc8OwI15SDjS5aJI9M-jZ3vRDJKAFSy2-H0r8l4dY8z8vyPZop2SRyJMXsh8_pmx498dNvdMrLXGP6SFqHjE8';
        }
    }

    async sendSubscriptionToServer(subscription) {
        try {
            const response = await fetch('/api/push/subscribe', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(subscription)
            });
            
            if (response.ok) {
                console.log('[PWA] Subscription sent to server');
            }
        } catch (error) {
            console.error('[PWA] Failed to send subscription to server:', error);
        }
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `pwa-notification tw-fixed tw-top-4 tw-right-4 tw-p-4 tw-rounded-lg tw-shadow-lg tw-z-50 ${
            type === 'success' ? 'tw-bg-green-500' : 
            type === 'error' ? 'tw-bg-red-500' : 
            type === 'warning' ? 'tw-bg-yellow-500' : 'tw-bg-blue-500'
        } tw-text-white`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    trackEvent(eventName, data = {}) {
        // Track PWA events for analytics
        if (typeof gtag !== 'undefined') {
            gtag('event', eventName, data);
        }
        console.log('[PWA] Event tracked:', eventName, data);
    }
}

// Initialize PWA Manager
let pwaManager;

document.addEventListener('DOMContentLoaded', () => {
    pwaManager = new PWAManager();
});

// Export for global access
window.pwaManager = pwaManager;
