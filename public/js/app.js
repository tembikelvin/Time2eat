/**
 * Time2Eat Application JavaScript
 * Enhanced UI interactions with icons and animations
 */

class Time2EatApp {
    constructor() {
        this.init();
    }
    
    init() {
        this.initializeFeatherIcons();
        this.initializeModals();
        this.initializeToasts();
        this.initializeDropdowns();
        this.initializeSearch();
        // Cart initialization removed - handled by unified-cart.js and cart component
        this.initializeLazyLoading();
        this.initializeAnimations();
        this.initializeAccessibility();
    }
    
    /**
     * Initialize Feather Icons
     */
    initializeFeatherIcons() {
        if (typeof feather !== 'undefined') {
            feather.replace();
            
            // Re-initialize icons when content is dynamically added
            const observer = new MutationObserver(() => {
                feather.replace();
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    }
    
    /**
     * Initialize Modal System
     */
    initializeModals() {
        // Modal open function
        window.openModal = (modalId) => {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('tw-hidden');
                modal.classList.add('tw-fade-in');
                document.body.style.overflow = 'hidden';
                
                // Focus management
                const firstFocusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
                if (firstFocusable) {
                    firstFocusable.focus();
                }
                
                // Trap focus within modal
                this.trapFocus(modal);
            }
        };
        
        // Modal close function
        window.closeModal = (modalId) => {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('tw-fade-out');
                setTimeout(() => {
                    modal.classList.add('tw-hidden');
                    modal.classList.remove('tw-fade-in', 'tw-fade-out');
                    document.body.style.overflow = '';
                }, 300);
            }
        };
        
        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.tw-modal-overlay:not(.tw-hidden)');
                if (openModal) {
                    const modalId = openModal.id;
                    if (modalId) {
                        closeModal(modalId);
                    }
                }
            }
        });
    }
    
    /**
     * Initialize Toast Notifications
     */
    initializeToasts() {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'tw-fixed tw-top-4 tw-right-4 tw-z-[99999] tw-space-y-3 tw-pointer-events-none';
            toastContainer.style.zIndex = '99999';
            document.body.appendChild(toastContainer);
        }

        window.showToast = (message, type = 'info', duration = 5000) => {
            // Ensure container exists (in case it was removed)
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'tw-fixed tw-top-4 tw-right-4 tw-z-[99999] tw-space-y-3 tw-pointer-events-none';
                container.style.zIndex = '99999';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            const toastId = 'toast-' + Date.now();

            const icons = {
                success: 'check-circle',
                error: 'alert-circle',
                warning: 'alert-triangle',
                info: 'info'
            };

            const colors = {
                success: 'tw-bg-green-500',
                error: 'tw-bg-red-500',
                warning: 'tw-bg-yellow-500',
                info: 'tw-bg-blue-500'
            };

            toast.id = toastId;
            toast.className = `tw-max-w-sm tw-p-4 tw-rounded-lg tw-shadow-lg tw-text-white tw-transition-all tw-duration-300 tw-transform tw-translate-x-full tw-pointer-events-auto ${colors[type] || colors.info}`;
            toast.innerHTML = `
                <div class="tw-flex tw-items-start tw-space-x-3">
                    <i data-feather="${icons[type] || icons.info}" class="tw-w-5 tw-h-5 tw-flex-shrink-0 tw-mt-0.5" aria-hidden="true"></i>
                    <div class="tw-flex-1">
                        <p class="tw-font-medium">${message}</p>
                    </div>
                    <button
                        class="tw-text-current tw-opacity-70 hover:tw-opacity-100 tw-p-1 tw-rounded tw-min-h-[32px] tw-min-w-[32px] tw-flex tw-items-center tw-justify-center"
                        onclick="closeToast('${toastId}')"
                        aria-label="Close notification"
                    >
                        <i data-feather="x" class="tw-w-4 tw-h-4" aria-hidden="true"></i>
                    </button>
                </div>
            `;

            container.appendChild(toast);
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            // Animate in
            setTimeout(() => {
                toast.classList.remove('tw-translate-x-full');
            }, 100);

            // Auto remove
            setTimeout(() => {
                const toastEl = document.getElementById(toastId);
                if (toastEl) {
                    toastEl.classList.add('tw-translate-x-full');
                    setTimeout(() => {
                        if (toastEl.parentElement) {
                            toastEl.parentElement.removeChild(toastEl);
                        }
                    }, 300);
                }
            }, duration);
        };

        window.closeToast = (toastId) => {
            const toast = document.getElementById(toastId);
            if (toast) {
                toast.classList.add('tw-translate-x-full');
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.parentElement.removeChild(toast);
                    }
                }, 300);
            }
        };
    }
    
    /**
     * Initialize Dropdown Menus
     */
    initializeDropdowns() {
        document.addEventListener('click', (e) => {
            const dropdown = e.target.closest('[data-dropdown]');
            
            if (dropdown) {
                e.preventDefault();
                const menu = dropdown.querySelector('.tw-dropdown');
                const isOpen = !menu.classList.contains('tw-hidden');
                
                // Close all dropdowns
                document.querySelectorAll('.tw-dropdown').forEach(d => {
                    d.classList.add('tw-hidden');
                });
                
                // Toggle current dropdown
                if (!isOpen) {
                    menu.classList.remove('tw-hidden');
                    menu.classList.add('tw-fade-in');
                }
                
                // Update ARIA
                dropdown.setAttribute('aria-expanded', !isOpen);
            } else {
                // Close all dropdowns when clicking outside
                document.querySelectorAll('.tw-dropdown').forEach(d => {
                    d.classList.add('tw-hidden');
                });
                document.querySelectorAll('[data-dropdown]').forEach(d => {
                    d.setAttribute('aria-expanded', 'false');
                });
            }
        });
    }
    
    /**
     * Initialize Search Functionality
     */
    initializeSearch() {
        const searchInputs = document.querySelectorAll('[data-search]');
        
        searchInputs.forEach(input => {
            let searchTimeout;
            
            input.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                const query = e.target.value.trim();
                
                if (query.length >= 2) {
                    searchTimeout = setTimeout(() => {
                        this.performSearch(query, input);
                    }, 300);
                } else {
                    this.hideSearchResults(input);
                }
            });
            
            // Hide results when clicking outside
            document.addEventListener('click', (e) => {
                if (!input.contains(e.target)) {
                    this.hideSearchResults(input);
                }
            });
        });
    }
    
    performSearch(query, input) {
        const resultsContainer = input.parentElement.querySelector('.tw-search-results');
        if (!resultsContainer) return;
        
        // Show loading state
        resultsContainer.innerHTML = `
            <div class="tw-px-4 tw-py-3 tw-text-center">
                <div class="tw-flex tw-items-center tw-justify-center tw-space-x-2">
                    <i data-feather="loader" class="tw-w-4 tw-h-4 tw-animate-spin" aria-hidden="true"></i>
                    <span class="tw-text-sm tw-text-gray-600">Searching...</span>
                </div>
            </div>
        `;
        resultsContainer.classList.remove('tw-hidden');
        feather.replace();
        
        // Simulate API call (replace with actual search)
        setTimeout(() => {
            const mockResults = [
                { name: 'Chicken Shawarma', restaurant: 'Bamenda Grill', price: '2500 FCFA' },
                { name: 'Jollof Rice', restaurant: 'Mama\'s Kitchen', price: '2000 FCFA' },
                { name: 'Grilled Fish', restaurant: 'Ocean View', price: '3500 FCFA' }
            ];
            
            if (mockResults.length > 0) {
                resultsContainer.innerHTML = mockResults.map(result => `
                    <div class="tw-search-result-item" onclick="selectSearchResult('${result.name}')">
                        <div class="tw-flex tw-justify-between tw-items-center">
                            <div>
                                <p class="tw-font-medium tw-text-gray-900">${result.name}</p>
                                <p class="tw-text-sm tw-text-gray-600">${result.restaurant}</p>
                            </div>
                            <span class="tw-text-primary-600 tw-font-semibold">${result.price}</span>
                        </div>
                    </div>
                `).join('');
            } else {
                resultsContainer.innerHTML = `
                    <div class="tw-px-4 tw-py-3 tw-text-center tw-text-gray-500">
                        <i data-feather="search" class="tw-w-8 tw-h-8 tw-mx-auto tw-mb-2 tw-text-gray-400" aria-hidden="true"></i>
                        <p>No results found for "${query}"</p>
                    </div>
                `;
            }
            feather.replace();
        }, 500);
    }
    
    hideSearchResults(input) {
        const resultsContainer = input.parentElement.querySelector('.tw-search-results');
        if (resultsContainer) {
            resultsContainer.classList.add('tw-hidden');
        }
    }
    
    // Cart functionality removed - now handled by:
    // 1. unified-cart.js - Core cart logic and API integration
    // 2. Cart component - UI functions (openCart, closeCart, toggleCart)
    
    /**
     * Initialize Lazy Loading
     */
    initializeLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.classList.remove('tw-opacity-0');
                            img.classList.add('tw-fade-in');
                            imageObserver.unobserve(img);
                        }
                    }
                });
            });
            
            document.querySelectorAll('img[data-src]').forEach(img => {
                img.classList.add('tw-opacity-0', 'tw-transition-opacity');
                imageObserver.observe(img);
            });
        }
    }
    
    /**
     * Initialize Animations
     */
    initializeAnimations() {
        // Animate elements when they come into view
        if ('IntersectionObserver' in window) {
            const animationObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const element = entry.target;
                        const animation = element.dataset.animate;
                        const delay = element.dataset.delay || '0';
                        
                        setTimeout(() => {
                            element.classList.add(animation);
                        }, parseInt(delay));
                        
                        animationObserver.unobserve(element);
                    }
                });
            });
            
            document.querySelectorAll('[data-animate]').forEach(el => {
                animationObserver.observe(el);
            });
        }
    }
    
    /**
     * Initialize Accessibility Features
     */
    initializeAccessibility() {
        // Skip to main content
        const skipLink = document.querySelector('a[href="#main-content"]');
        if (skipLink) {
            skipLink.addEventListener('click', (e) => {
                e.preventDefault();
                const mainContent = document.getElementById('main-content');
                if (mainContent) {
                    mainContent.focus();
                    mainContent.scrollIntoView();
                }
            });
        }
        
        // Keyboard navigation for custom elements
        document.addEventListener('keydown', (e) => {
            // Handle Enter and Space for buttons
            if ((e.key === 'Enter' || e.key === ' ') && e.target.hasAttribute('data-button')) {
                e.preventDefault();
                e.target.click();
            }
        });
    }
    
    /**
     * Trap focus within an element
     */
    trapFocus(element) {
        const focusableElements = element.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];
        
        element.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    if (document.activeElement === firstFocusable) {
                        e.preventDefault();
                        lastFocusable.focus();
                    }
                } else {
                    if (document.activeElement === lastFocusable) {
                        e.preventDefault();
                        firstFocusable.focus();
                    }
                }
            }
        });
    }
}

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.time2eatApp = new Time2EatApp();
});

// Global utility functions
window.selectSearchResult = (itemName) => {
    console.log('Selected:', itemName);
    // Implement search result selection logic
};

window.toggleFavorite = (itemId) => {
    const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    const index = favorites.indexOf(itemId);

    if (index > -1) {
        favorites.splice(index, 1);
        showToast('Removed from favorites', 'info');
    } else {
        favorites.push(itemId);
        showToast('Added to favorites', 'success');
    }

    localStorage.setItem('favorites', JSON.stringify(favorites));

    // Update UI
    document.querySelectorAll(`[data-favorite="${itemId}"]`).forEach(btn => {
        const icon = btn.querySelector('i');
        if (favorites.includes(itemId)) {
            icon.setAttribute('data-feather', 'heart');
            btn.classList.add('tw-text-red-500');
        } else {
            icon.setAttribute('data-feather', 'heart');
            btn.classList.remove('tw-text-red-500');
        }
    });

    feather.replace();
};

// Initialize the app when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new Time2EatApp();
    });
} else {
    // DOM already loaded
    new Time2EatApp();
}
