/**
 * Enhanced Home Page JavaScript
 * Mobile-first carousel, lazy loading, animations, and interactions
 */

class Time2EatHome {
    constructor() {
        this.currentSlide = 0;
        this.totalSlides = 0;
        this.slideWidth = 320;
        this.autoPlayInterval = null;
        this.isTouch = false;
        
        this.init();
    }
    
    init() {
        this.initializeFeatherIcons();
        this.initializeCarousel();
        this.initializeLazyLoading();
        this.initializeAnimations();
        this.initializeSmoothScroll();
        this.initializeAddToCart();
        this.initializeSearchSuggestions();
    }
    
    initializeFeatherIcons() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
    
    initializeCarousel() {
        const carousel = document.getElementById('featured-carousel');
        const prevBtn = document.getElementById('prev-restaurant');
        const nextBtn = document.getElementById('next-restaurant');
        const indicators = document.querySelectorAll('.carousel-indicator');
        
        if (!carousel || !prevBtn || !nextBtn) return;
        
        this.totalSlides = carousel.children.length;
        
        // Update carousel position
        this.updateCarousel = () => {
            const translateX = -this.currentSlide * this.slideWidth;
            carousel.style.transform = `translateX(${translateX}px)`;
            
            // Update indicators
            indicators.forEach((indicator, index) => {
                indicator.classList.toggle('tw-bg-red-500', index === this.currentSlide);
                indicator.classList.toggle('tw-bg-gray-300', index !== this.currentSlide);
            });
        };
        
        // Navigation buttons
        nextBtn.addEventListener('click', () => {
            this.nextSlide();
        });
        
        prevBtn.addEventListener('click', () => {
            this.prevSlide();
        });
        
        // Indicator clicks
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                this.goToSlide(index);
            });
        });
        
        // Touch/swipe support
        this.initializeTouchSupport(carousel);
        
        // Auto-play on mobile
        this.startAutoPlay();
        
        // Pause auto-play on hover
        carousel.addEventListener('mouseenter', () => this.stopAutoPlay());
        carousel.addEventListener('mouseleave', () => this.startAutoPlay());
    }
    
    nextSlide() {
        this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
        this.updateCarousel();
    }
    
    prevSlide() {
        this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
        this.updateCarousel();
    }
    
    goToSlide(index) {
        this.currentSlide = index;
        this.updateCarousel();
    }
    
    startAutoPlay() {
        if (window.innerWidth < 768) { // Only on mobile
            this.autoPlayInterval = setInterval(() => {
                this.nextSlide();
            }, 5000);
        }
    }
    
    stopAutoPlay() {
        if (this.autoPlayInterval) {
            clearInterval(this.autoPlayInterval);
            this.autoPlayInterval = null;
        }
    }
    
    initializeTouchSupport(carousel) {
        let startX = 0;
        let startY = 0;
        let isDragging = false;
        
        carousel.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            isDragging = true;
            this.isTouch = true;
        }, { passive: true });
        
        carousel.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            
            const currentX = e.touches[0].clientX;
            const currentY = e.touches[0].clientY;
            const diffX = Math.abs(currentX - startX);
            const diffY = Math.abs(currentY - startY);
            
            // If horizontal swipe is more significant than vertical, prevent default
            if (diffX > diffY && diffX > 10) {
                e.preventDefault();
            }
        }, { passive: false });
        
        carousel.addEventListener('touchend', (e) => {
            if (!isDragging) return;
            isDragging = false;
            
            const endX = e.changedTouches[0].clientX;
            const diffX = startX - endX;
            
            if (Math.abs(diffX) > 50) { // Minimum swipe distance
                if (diffX > 0) {
                    this.nextSlide();
                } else {
                    this.prevSlide();
                }
            }
        }, { passive: true });
    }
    
    initializeLazyLoading() {
        const lazyImages = document.querySelectorAll('.lazy-load');
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.classList.remove('lazy-load');
                            img.classList.add('tw-opacity-100');
                            observer.unobserve(img);
                        }
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.01
            });
            
            lazyImages.forEach(img => {
                img.classList.add('tw-opacity-0', 'tw-transition-opacity', 'tw-duration-300');
                imageObserver.observe(img);
            });
        } else {
            // Fallback for older browsers
            lazyImages.forEach(img => {
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                }
            });
        }
    }
    
    initializeAnimations() {
        if ('IntersectionObserver' in window) {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('tw-animate-fade-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);
            
            // Observe cards and sections
            document.querySelectorAll('.tw-card, section > div').forEach(el => {
                observer.observe(el);
            });
        }
    }
    
    initializeSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }
    
    initializeAddToCart() {
        window.addToCart = (dishId, dishName = 'item') => {
            const button = event.target.closest('button');
            if (!button || button.disabled) return;
            
            const originalText = button.innerHTML;
            
            // Show loading state
            button.innerHTML = '<i data-feather="loader" class="tw-w-4 tw-h-4 tw-mr-2 tw-animate-spin"></i>Adding...';
            button.disabled = true;
            feather.replace();
            
            // Simulate API call
            setTimeout(() => {
                button.innerHTML = '<i data-feather="check" class="tw-w-4 tw-h-4 tw-mr-2"></i>Added!';
                button.classList.remove('tw-btn-primary');
                button.classList.add('tw-bg-green-500', 'tw-text-white');
                feather.replace();
                
                // Show success notification
                this.showNotification(`${dishName} added to cart!`, 'success');
                
                // Reset button after delay
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.add('tw-btn-primary');
                    button.classList.remove('tw-bg-green-500', 'tw-text-white');
                    button.disabled = false;
                    feather.replace();
                }, 2000);
            }, 1000);
        };
    }
    
    initializeSearchSuggestions() {
        const searchInput = document.querySelector('input[type="search"]');
        if (!searchInput) return;
        
        let searchTimeout;
        
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            
            if (query.length < 2) {
                this.hideSuggestions();
                return;
            }
            
            searchTimeout = setTimeout(() => {
                this.fetchSearchSuggestions(query);
            }, 300);
        });
        
        // Hide suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-container')) {
                this.hideSuggestions();
            }
        });
    }
    
    fetchSearchSuggestions(query) {
        // Mock suggestions - replace with actual API call
        const mockSuggestions = [
            { type: 'restaurant', name: 'Mama\'s Kitchen', cuisine: 'Cameroonian' },
            { type: 'dish', name: 'Ndolé with Plantain', restaurant: 'Mama\'s Kitchen' },
            { type: 'dish', name: 'Jollof Rice Special', restaurant: 'Spice Garden' },
            { type: 'restaurant', name: 'Urban Fusion', cuisine: 'Fusion' }
        ];
        
        const filteredSuggestions = mockSuggestions.filter(item => 
            item.name.toLowerCase().includes(query.toLowerCase())
        );
        
        this.showSuggestions(filteredSuggestions);
    }
    
    showSuggestions(suggestions) {
        let suggestionsContainer = document.querySelector('.search-suggestions');
        
        if (!suggestionsContainer) {
            suggestionsContainer = document.createElement('div');
            suggestionsContainer.className = 'search-suggestions tw-absolute tw-top-full tw-left-0 tw-right-0 tw-bg-white tw-border tw-border-gray-200 tw-rounded-lg tw-shadow-lg tw-z-50 tw-mt-1';
            document.querySelector('.search-container')?.appendChild(suggestionsContainer);
        }
        
        if (suggestions.length === 0) {
            suggestionsContainer.innerHTML = '<div class="tw-p-4 tw-text-gray-500 tw-text-center">No suggestions found</div>';
            return;
        }
        
        suggestionsContainer.innerHTML = suggestions.map(item => `
            <div class="tw-p-3 tw-border-b tw-border-gray-100 hover:tw-bg-gray-50 tw-cursor-pointer tw-flex tw-items-center">
                <i data-feather="${item.type === 'restaurant' ? 'home' : 'utensils'}" class="tw-w-4 tw-h-4 tw-mr-3 tw-text-gray-400"></i>
                <div>
                    <div class="tw-font-medium tw-text-gray-800">${item.name}</div>
                    <div class="tw-text-sm tw-text-gray-500">
                        ${item.type === 'restaurant' ? item.cuisine : `at ${item.restaurant}`}
                    </div>
                </div>
            </div>
        `).join('');
        
        feather.replace();
    }
    
    hideSuggestions() {
        const suggestionsContainer = document.querySelector('.search-suggestions');
        if (suggestionsContainer) {
            suggestionsContainer.remove();
        }
    }
    
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `tw-fixed tw-top-4 tw-right-4 tw-z-50 tw-p-4 tw-rounded-lg tw-shadow-lg tw-transition-all tw-duration-300 tw-transform tw-translate-x-full ${
            type === 'success' ? 'tw-bg-green-500 tw-text-white' : 
            type === 'error' ? 'tw-bg-red-500 tw-text-white' : 
            'tw-bg-blue-500 tw-text-white'
        }`;
        
        notification.innerHTML = `
            <div class="tw-flex tw-items-center">
                <i data-feather="${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info'}" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                <span>${message}</span>
                <button class="tw-ml-4 tw-text-white hover:tw-text-gray-200" onclick="this.parentElement.parentElement.remove()">
                    <i data-feather="x" class="tw-w-4 tw-h-4"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        feather.replace();
        
        // Slide in
        setTimeout(() => {
            notification.classList.remove('tw-translate-x-full');
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('tw-translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new Time2EatHome();
});

// Handle window resize
window.addEventListener('resize', () => {
    // Restart auto-play based on screen size
    const homeInstance = window.time2eatHome;
    if (homeInstance) {
        homeInstance.stopAutoPlay();
        homeInstance.startAutoPlay();
    }
});
