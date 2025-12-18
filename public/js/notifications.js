/**
 * PWA Notification Manager
 * Handles push notifications, permission requests, and notification display
 */

class NotificationManager {
    constructor() {
        this.isSupported = 'Notification' in window && 'serviceWorker' in navigator;
        this.permission = this.isSupported ? Notification.permission : 'denied';
        this.registration = null;
        this.subscription = null;
        
        this.init();
    }

    async init() {
        if (!this.isSupported) {
            console.log('Notifications not supported');
            return;
        }

        try {
            // Register service worker
            this.registration = await navigator.serviceWorker.register('/eat/public/sw.js', {
                scope: '/eat/public/'
            });
            console.log('Service Worker registered:', this.registration);

            // Check for existing subscription
            this.subscription = await this.registration.pushManager.getSubscription();
            
            // Set up message event listener
            navigator.serviceWorker.addEventListener('message', this.handleServiceWorkerMessage.bind(this));
            
        } catch (error) {
            console.error('Notification initialization failed:', error);
        }
    }

    async requestPermission() {
        if (!this.isSupported) {
            return false;
        }

        if (this.permission === 'granted') {
            return true;
        }

        try {
            this.permission = await Notification.requestPermission();
            return this.permission === 'granted';
        } catch (error) {
            console.error('Permission request failed:', error);
            return false;
        }
    }

    async subscribeToPush() {
        if (!this.registration || this.permission !== 'granted') {
            return false;
        }

        try {
            // Get VAPID public key from server
            const response = await fetch('/api/notifications/vapid-key');
            const { publicKey } = await response.json();

            // Convert VAPID key
            const applicationServerKey = this.urlBase64ToUint8Array(publicKey);

            // Subscribe to push notifications
            this.subscription = await this.registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: applicationServerKey
            });

            // Send subscription to server
            await this.sendSubscriptionToServer(this.subscription);
            
            console.log('Push subscription successful');
            return true;

        } catch (error) {
            console.error('Push subscription failed:', error);
            return false;
        }
    }

    async sendSubscriptionToServer(subscription) {
        try {
            const response = await fetch('/api/notifications/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    subscription: subscription,
                    userAgent: navigator.userAgent
                })
            });

            if (!response.ok) {
                throw new Error('Failed to send subscription to server');
            }

            console.log('Subscription sent to server successfully');
        } catch (error) {
            console.error('Failed to send subscription to server:', error);
        }
    }

    async unsubscribeFromPush() {
        if (!this.subscription) {
            return false;
        }

        try {
            const success = await this.subscription.unsubscribe();
            
            if (success) {
                // Notify server about unsubscription
                await fetch('/api/notifications/unsubscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        endpoint: this.subscription.endpoint
                    })
                });
                
                this.subscription = null;
                console.log('Unsubscribed from push notifications');
            }
            
            return success;
        } catch (error) {
            console.error('Unsubscribe failed:', error);
            return false;
        }
    }

    showLocalNotification(title, options = {}) {
        if (this.permission !== 'granted') {
            console.log('Notification permission not granted');
            return;
        }

        const notificationOptions = {
            body: options.body || '',
            icon: options.icon || '/public/images/icons/icon-192x192.png',
            badge: options.badge || '/public/images/icons/badge-72x72.png',
            tag: options.tag || 'default',
            data: options.data || {},
            requireInteraction: options.requireInteraction || false,
            silent: options.silent || false,
            vibrate: options.vibrate || [200, 100, 200],
            actions: options.actions || []
        };

        if (this.registration) {
            this.registration.showNotification(title, notificationOptions);
        } else {
            new Notification(title, notificationOptions);
        }
    }

    async checkUnreadMessages() {
        try {
            const response = await fetch('/api/messages/unread-count');
            const data = await response.json();
            
            if (data.success && data.count > 0) {
                // Update badge
                this.updateBadge(data.count);
                
                // Show notification if permission granted
                if (this.permission === 'granted') {
                    this.showLocalNotification(
                        'New Messages',
                        {
                            body: `You have ${data.count} unread message${data.count > 1 ? 's' : ''}`,
                            tag: 'unread-messages',
                            data: { url: '/customer/messages' },
                            actions: [
                                {
                                    action: 'view',
                                    title: 'View Messages'
                                }
                            ]
                        }
                    );
                }
            } else {
                this.updateBadge(0);
            }
        } catch (error) {
            console.error('Failed to check unread messages:', error);
        }
    }

    updateBadge(count) {
        if ('setAppBadge' in navigator) {
            navigator.setAppBadge(count);
        }
    }

    clearBadge() {
        if ('clearAppBadge' in navigator) {
            navigator.clearAppBadge();
        }
    }

    handleServiceWorkerMessage(event) {
        const { type, data } = event.data;
        
        switch (type) {
            case 'NOTIFICATION_CLICKED':
                // Handle notification click
                if (data.url) {
                    window.location.href = data.url;
                }
                break;
                
            case 'MESSAGE_RECEIVED':
                // Handle new message notification
                this.showLocalNotification(
                    'New Message',
                    {
                        body: data.message || 'You have a new message',
                        tag: 'new-message',
                        data: { url: '/customer/messages' }
                    }
                );
                break;
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

    // Public methods for external use
    async enableNotifications() {
        const permissionGranted = await this.requestPermission();
        if (permissionGranted) {
            return await this.subscribeToPush();
        }
        return false;
    }

    async disableNotifications() {
        return await this.unsubscribeFromPush();
    }

    isNotificationEnabled() {
        return this.permission === 'granted' && this.subscription !== null;
    }

    getPermissionStatus() {
        return this.permission;
    }
}

// Initialize notification manager
const notificationManager = new NotificationManager();

// Export for use in other scripts
window.notificationManager = notificationManager;

// Auto-check for unread messages every 30 seconds
setInterval(() => {
    if (notificationManager.isNotificationEnabled()) {
        notificationManager.checkUnreadMessages();
    }
}, 30000);

// Check for unread messages on page load
document.addEventListener('DOMContentLoaded', () => {
    if (notificationManager.isNotificationEnabled()) {
        notificationManager.checkUnreadMessages();
    }
});
