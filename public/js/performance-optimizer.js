/**
 * Time2Eat Performance Optimizer
 * Client-side performance optimization and monitoring
 */

class PerformanceOptimizer {
    constructor() {
        this.metrics = {};
        this.observers = {};
        this.lazyLoadQueue = [];
        this.config = {
            lazyLoadThreshold: '50px',
            imageQuality: 0.8,
            cacheTimeout: 300000, // 5 minutes
            performanceThreshold: 3000 // 3 seconds
        };
        
        this.init();
    }

    /**
     * Initialize performance optimizer
     */
    init() {
        // Start performance monitoring
        this.startPerformanceMonitoring();
        
        // Initialize lazy loading
        this.initializeLazyLoading();
        
        // Optimize images
        this.optimizeImages();
        
        // Preload critical resources
        this.preloadCriticalResources();
        
        // Setup service worker for caching
        this.setupServiceWorker();
        
        // Monitor Core Web Vitals
        this.monitorCoreWebVitals();
        
        console.log('🚀 Performance Optimizer initialized');
    }

    /**
     * Start performance monitoring
     */
    startPerformanceMonitoring() {
        // Monitor page load time
        window.addEventListener('load', () => {
            const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
            this.recordMetric('page_load_time', loadTime, 'ms');
            
            if (loadTime > this.config.performanceThreshold) {
                console.warn(`⚠️ Slow page load: ${loadTime}ms`);
            }
        });

        // Monitor resource loading
        if ('PerformanceObserver' in window) {
            const resourceObserver = new PerformanceObserver((list) => {
                list.getEntries().forEach(entry => {
                    if (entry.duration > 1000) { // Resources taking more than 1s
                        console.warn(`⚠️ Slow resource: ${entry.name} (${Math.round(entry.duration)}ms)`);
                    }
                });
            });
            
            resourceObserver.observe({ entryTypes: ['resource'] });
            this.observers.resource = resourceObserver;
        }

        // Monitor memory usage
        if ('memory' in performance) {
            setInterval(() => {
                const memory = performance.memory;
                this.recordMetric('memory_used', memory.usedJSHeapSize / 1024 / 1024, 'MB');
                
                if (memory.usedJSHeapSize / memory.jsHeapSizeLimit > 0.8) {
                    console.warn('⚠️ High memory usage detected');
                }
            }, 30000); // Check every 30 seconds
        }
    }

    /**
     * Initialize lazy loading for images and content
     */
    initializeLazyLoading() {
        if ('IntersectionObserver' in window) {
            // Lazy load images
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadImage(entry.target);
                        imageObserver.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: this.config.lazyLoadThreshold,
                threshold: 0.01
            });

            // Observe all lazy load images
            document.querySelectorAll('img[data-src], img[loading="lazy"]').forEach(img => {
                imageObserver.observe(img);
            });

            this.observers.image = imageObserver;

            // Lazy load content sections
            const contentObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadContent(entry.target);
                        contentObserver.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: '100px',
                threshold: 0.1
            });

            document.querySelectorAll('[data-lazy-content]').forEach(element => {
                contentObserver.observe(element);
            });

            this.observers.content = contentObserver;
        } else {
            // Fallback for browsers without IntersectionObserver
            this.loadAllLazyContent();
        }
    }

    /**
     * Load lazy image
     */
    loadImage(img) {
        const startTime = performance.now();
        
        // Handle responsive images
        if (img.dataset.srcset) {
            img.srcset = img.dataset.srcset;
        }
        
        if (img.dataset.src) {
            img.src = img.dataset.src;
        }

        // Add loading animation
        img.classList.add('tw-opacity-0', 'tw-transition-opacity', 'tw-duration-300');

        img.onload = () => {
            img.classList.remove('tw-opacity-0');
            img.classList.add('tw-opacity-100');
            
            const loadTime = performance.now() - startTime;
            this.recordMetric('image_load_time', loadTime, 'ms');
            
            // Remove data attributes to prevent reloading
            delete img.dataset.src;
            delete img.dataset.srcset;
        };

        img.onerror = () => {
            img.classList.add('tw-opacity-50');
            console.warn(`Failed to load image: ${img.dataset.src || img.src}`);
        };
    }

    /**
     * Load lazy content
     */
    async loadContent(element) {
        const contentUrl = element.dataset.lazyContent;
        if (!contentUrl) return;

        try {
            element.innerHTML = '<div class="tw-animate-pulse tw-bg-gray-200 tw-h-20 tw-rounded"></div>';
            
            const response = await fetch(contentUrl);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const content = await response.text();
            element.innerHTML = content;
            
            // Initialize any new lazy images in the loaded content
            element.querySelectorAll('img[data-src]').forEach(img => {
                if (this.observers.image) {
                    this.observers.image.observe(img);
                }
            });
            
        } catch (error) {
            element.innerHTML = '<div class="tw-text-red-500 tw-p-4">Failed to load content</div>';
            console.error('Failed to load lazy content:', error);
        }
    }

    /**
     * Load all lazy content (fallback)
     */
    loadAllLazyContent() {
        document.querySelectorAll('img[data-src]').forEach(img => {
            this.loadImage(img);
        });

        document.querySelectorAll('[data-lazy-content]').forEach(element => {
            this.loadContent(element);
        });
    }

    /**
     * Optimize images based on device capabilities
     */
    optimizeImages() {
        // Check WebP support
        const supportsWebP = this.checkWebPSupport();
        
        // Check device pixel ratio
        const pixelRatio = window.devicePixelRatio || 1;
        
        // Check connection speed
        const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        const isSlowConnection = connection && (connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g');

        document.querySelectorAll('img').forEach(img => {
            // Skip if already optimized
            if (img.dataset.optimized) return;

            // Use WebP if supported
            if (supportsWebP && img.dataset.webp) {
                img.src = img.dataset.webp;
            }

            // Adjust quality for slow connections
            if (isSlowConnection && img.dataset.lowQuality) {
                img.src = img.dataset.lowQuality;
            }

            // Handle high DPI displays
            if (pixelRatio > 1 && img.dataset.highDpi) {
                img.src = img.dataset.highDpi;
            }

            img.dataset.optimized = 'true';
        });
    }

    /**
     * Check WebP support
     */
    checkWebPSupport() {
        const canvas = document.createElement('canvas');
        canvas.width = 1;
        canvas.height = 1;
        return canvas.toDataURL('image/webp').indexOf('data:image/webp') === 0;
    }

    /**
     * Preload critical resources
     */
    preloadCriticalResources() {
        const criticalResources = [
            { href: '/css/critical.css', as: 'style' },
            { href: '/js/app.min.js', as: 'script' },
            { href: '/fonts/main.woff2', as: 'font', type: 'font/woff2', crossorigin: 'anonymous' }
        ];

        criticalResources.forEach(resource => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.href = resource.href;
            link.as = resource.as;
            
            if (resource.type) link.type = resource.type;
            if (resource.crossorigin) link.crossOrigin = resource.crossorigin;
            
            document.head.appendChild(link);
        });
    }

    /**
     * Setup service worker for caching
     */
    async setupServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('/sw.js');
                console.log('✅ Service Worker registered:', registration);
                
                // Listen for updates
                registration.addEventListener('updatefound', () => {
                    console.log('🔄 Service Worker update found');
                });
                
            } catch (error) {
                console.error('❌ Service Worker registration failed:', error);
            }
        }
    }

    /**
     * Monitor Core Web Vitals
     */
    monitorCoreWebVitals() {
        if ('PerformanceObserver' in window) {
            // Largest Contentful Paint (LCP)
            const lcpObserver = new PerformanceObserver((list) => {
                list.getEntries().forEach(entry => {
                    this.recordMetric('lcp', entry.startTime, 'ms');
                    
                    if (entry.startTime > 2500) {
                        console.warn(`⚠️ Poor LCP: ${Math.round(entry.startTime)}ms`);
                    }
                });
            });
            
            try {
                lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });
                this.observers.lcp = lcpObserver;
            } catch (e) {
                // LCP not supported
            }

            // First Input Delay (FID)
            const fidObserver = new PerformanceObserver((list) => {
                list.getEntries().forEach(entry => {
                    this.recordMetric('fid', entry.processingStart - entry.startTime, 'ms');
                    
                    if (entry.processingStart - entry.startTime > 100) {
                        console.warn(`⚠️ Poor FID: ${Math.round(entry.processingStart - entry.startTime)}ms`);
                    }
                });
            });
            
            try {
                fidObserver.observe({ entryTypes: ['first-input'] });
                this.observers.fid = fidObserver;
            } catch (e) {
                // FID not supported
            }

            // Cumulative Layout Shift (CLS)
            let clsValue = 0;
            const clsObserver = new PerformanceObserver((list) => {
                list.getEntries().forEach(entry => {
                    if (!entry.hadRecentInput) {
                        clsValue += entry.value;
                        this.recordMetric('cls', clsValue, 'score');
                        
                        if (clsValue > 0.1) {
                            console.warn(`⚠️ Poor CLS: ${clsValue.toFixed(3)}`);
                        }
                    }
                });
            });
            
            try {
                clsObserver.observe({ entryTypes: ['layout-shift'] });
                this.observers.cls = clsObserver;
            } catch (e) {
                // CLS not supported
            }
        }
    }

    /**
     * Record performance metric
     */
    recordMetric(name, value, unit) {
        if (!this.metrics[name]) {
            this.metrics[name] = [];
        }
        
        this.metrics[name].push({
            value: value,
            unit: unit,
            timestamp: Date.now()
        });

        // Keep only last 100 measurements
        if (this.metrics[name].length > 100) {
            this.metrics[name] = this.metrics[name].slice(-100);
        }

        // Send to server periodically
        if (this.metrics[name].length % 10 === 0) {
            this.sendMetricsToServer();
        }
    }

    /**
     * Send metrics to server
     */
    async sendMetricsToServer() {
        try {
            const metricsToSend = {};
            
            Object.keys(this.metrics).forEach(key => {
                const measurements = this.metrics[key];
                if (measurements.length > 0) {
                    const latest = measurements[measurements.length - 1];
                    metricsToSend[key] = {
                        value: latest.value,
                        unit: latest.unit,
                        timestamp: latest.timestamp
                    };
                }
            });

            await fetch('/api/performance/metrics', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    metrics: metricsToSend,
                    url: window.location.pathname,
                    user_agent: navigator.userAgent
                })
            });
            
        } catch (error) {
            console.error('Failed to send metrics:', error);
        }
    }

    /**
     * Get performance report
     */
    getPerformanceReport() {
        const report = {
            page_load_time: this.getAverageMetric('page_load_time'),
            lcp: this.getAverageMetric('lcp'),
            fid: this.getAverageMetric('fid'),
            cls: this.getAverageMetric('cls'),
            memory_usage: this.getLatestMetric('memory_used'),
            image_load_time: this.getAverageMetric('image_load_time'),
            timestamp: new Date().toISOString()
        };

        return report;
    }

    /**
     * Get average metric value
     */
    getAverageMetric(name) {
        if (!this.metrics[name] || this.metrics[name].length === 0) {
            return null;
        }

        const values = this.metrics[name].map(m => m.value);
        const average = values.reduce((a, b) => a + b, 0) / values.length;
        
        return {
            value: Math.round(average * 100) / 100,
            unit: this.metrics[name][0].unit,
            count: values.length
        };
    }

    /**
     * Get latest metric value
     */
    getLatestMetric(name) {
        if (!this.metrics[name] || this.metrics[name].length === 0) {
            return null;
        }

        const latest = this.metrics[name][this.metrics[name].length - 1];
        return {
            value: latest.value,
            unit: latest.unit,
            timestamp: latest.timestamp
        };
    }

    /**
     * Optimize for mobile devices
     */
    optimizeForMobile() {
        if (window.innerWidth <= 768) {
            // Reduce image quality on mobile
            document.querySelectorAll('img').forEach(img => {
                if (img.dataset.mobile) {
                    img.src = img.dataset.mobile;
                }
            });

            // Disable expensive animations on mobile
            document.documentElement.style.setProperty('--animation-duration', '0s');
            
            // Reduce polling intervals
            this.config.cacheTimeout = 600000; // 10 minutes
        }
    }

    /**
     * Clean up observers
     */
    destroy() {
        Object.values(this.observers).forEach(observer => {
            if (observer && observer.disconnect) {
                observer.disconnect();
            }
        });
        
        this.observers = {};
        this.metrics = {};
    }
}

// Initialize performance optimizer when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.performanceOptimizer = new PerformanceOptimizer();
    
    // Optimize for mobile if needed
    if (window.innerWidth <= 768) {
        window.performanceOptimizer.optimizeForMobile();
    }
});

// Handle page visibility changes
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        // Page is hidden, reduce activity
        if (window.performanceOptimizer) {
            window.performanceOptimizer.sendMetricsToServer();
        }
    }
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PerformanceOptimizer;
}
