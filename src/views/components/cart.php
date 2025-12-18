<?php
/**
 * Modern Shopping Cart Component - Mobile First Design
 * Professional cart system with header integration
 */

require_once __DIR__ . '/../../traits/UIComponents.php';
require_once __DIR__ . '/../../helpers/IconHelper.php';

// Ensure helper functions are available
if (!function_exists('url')) {
    require_once __DIR__ . '/../../helpers/functions.php';
}

class CartComponent {
    use UIComponents;
    
    /**
     * Render cart icon for header navigation - Mobile First
     */
    public function renderNavCartIcon(int $itemCount = 0): string
    {
        return '
        <button
            class="tw-relative tw-p-2 sm:tw-p-2.5 tw-text-gray-700 hover:tw-text-orange-600 tw-transition-all tw-duration-200 tw-rounded-xl tw-min-h-[44px] tw-min-w-[44px] tw-flex tw-items-center tw-justify-center hover:tw-bg-orange-50 tw-group"
            onclick="openCart()"
            aria-label="Shopping cart"
            type="button"
        >
            <svg class="tw-w-6 tw-h-6 sm:tw-w-7 sm:tw-h-7 group-hover:tw-scale-110 tw-transition-transform tw-duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <span
                class="tw-absolute -tw-top-1 -tw-right-1 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-text-xs tw-font-black tw-rounded-full tw-min-w-[22px] tw-h-[22px] tw-flex tw-items-center tw-justify-center tw-shadow-lg tw-border-2 tw-border-white"
                data-cart-count
                style="display: ' . ($itemCount > 0 ? 'flex' : 'none') . ';"
            >' . ($itemCount > 99 ? '99+' : $itemCount) . '</span>
        </button>';
    }
    
    /**
     * Render modern cart sidebar - Mobile First
     */
    public function renderCartSidebar(): string
    {
        return '
        <!-- Cart Overlay -->
        <div 
            id="cart-overlay" 
            class="tw-fixed tw-inset-0 tw-bg-black/60 tw-backdrop-blur-sm tw-z-[60] tw-hidden tw-transition-opacity tw-duration-300"
            onclick="closeCart()"
        ></div>
        
        <!-- Cart Sidebar -->
        <div 
            id="cart-sidebar" 
            class="tw-fixed tw-inset-y-0 tw-right-0 tw-z-[70] tw-w-full sm:tw-w-[420px] tw-bg-white tw-shadow-2xl tw-transform tw-translate-x-full tw-transition-transform tw-duration-300 tw-flex tw-flex-col tw-max-h-screen"
            role="dialog"
            aria-modal="true"
            aria-labelledby="cart-title"
        >
            <!-- Cart Header -->
            <div class="tw-flex tw-items-center tw-justify-between tw-p-4 sm:tw-p-5 tw-border-b tw-border-gray-200 tw-bg-gradient-to-r tw-from-orange-50 tw-to-red-50">
                <h2 id="cart-title" class="tw-text-xl sm:tw-text-2xl tw-font-black tw-text-gray-900 tw-flex tw-items-center">
                    <svg class="tw-w-6 tw-h-6 sm:tw-w-7 sm:tw-h-7 tw-mr-2 tw-text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    My Cart
                </h2>
                <button
                    class="tw-p-2 tw-text-gray-500 hover:tw-text-gray-700 hover:tw-bg-white tw-rounded-full tw-transition-all tw-min-h-[44px] tw-min-w-[44px] tw-flex tw-items-center tw-justify-center"
                    onclick="closeCart()"
                    aria-label="Close cart"
                    type="button"
                >
                    <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Cart Items Container -->
            <div id="cart-items-container" class="tw-flex-1 tw-overflow-y-auto tw-p-4 sm:tw-p-5">
                <!-- Empty State -->
                <div id="cart-empty" class="tw-text-center tw-py-12 sm:tw-py-16">
                    <div class="tw-w-24 tw-h-24 sm:tw-w-32 sm:tw-h-32 tw-mx-auto tw-mb-6 tw-bg-gradient-to-br tw-from-orange-100 tw-to-red-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                        <svg class="tw-w-12 tw-h-12 sm:tw-w-16 sm:tw-h-16 tw-text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="tw-text-xl sm:tw-text-2xl tw-font-black tw-text-gray-900 tw-mb-3">Your cart is empty</h3>
                    <p class="tw-text-sm sm:tw-text-base tw-text-gray-600 tw-mb-6 tw-max-w-xs tw-mx-auto">
                        Start adding delicious items from our restaurants!
                    </p>
                    <a 
                        href="' . url('/browse') . '" 
                        class="tw-inline-flex tw-items-center tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-px-6 tw-py-3 tw-rounded-xl tw-font-bold tw-shadow-lg hover:tw-shadow-xl tw-transition-all"
                    >
                        <svg class="tw-w-5 tw-h-5 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Browse Restaurants
                    </a>
                </div>
                
                <!-- Cart Items List -->
                <div id="cart-content" class="tw-hidden tw-space-y-3">
                    <!-- Items will be populated by JavaScript -->
                </div>
            </div>
            
            <!-- Cart Footer -->
            <div id="cart-footer" class="tw-hidden tw-border-t tw-border-gray-200 tw-p-4 sm:tw-p-5 tw-space-y-4 tw-bg-gray-50">
                <!-- Subtotal -->
                <div class="tw-flex tw-justify-between tw-items-center tw-text-base sm:tw-text-lg">
                    <span class="tw-text-gray-700 tw-font-semibold">Subtotal:</span>
                    <span id="cart-subtotal" class="tw-font-bold tw-text-gray-900">0 FCFA</span>
                </div>
                
                <!-- Delivery Fee -->
                <div class="tw-flex tw-justify-between tw-items-center tw-text-sm tw-text-gray-600">
                    <span class="tw-flex tw-items-center">
                        <svg class="tw-w-4 tw-h-4 tw-mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                        </svg>
                        Base Delivery Fee:
                    </span>
                    <span id="cart-delivery" class="tw-font-semibold">0 FCFA</span>
                </div>
                <div class="tw-text-xs tw-text-gray-500 tw-italic tw-pl-6">
                    *Actual fee calculated at checkout based on distance
                </div>
                
                <!-- Total -->
                <div class="tw-flex tw-justify-between tw-items-center tw-text-xl sm:tw-text-2xl tw-font-black tw-pt-3 tw-border-t-2 tw-border-gray-300">
                    <span class="tw-text-gray-900">Total:</span>
                    <span id="cart-total" class="tw-text-orange-600">0 FCFA</span>
                </div>
                
                <!-- Action Buttons -->
                <div class="tw-space-y-2.5 tw-pt-2">
                    <button
                        onclick="proceedToCheckout()"
                        class="tw-w-full tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-py-4 tw-rounded-xl tw-font-black tw-text-base sm:tw-text-lg tw-shadow-lg hover:tw-shadow-xl tw-transition-all tw-flex tw-items-center tw-justify-center tw-gap-2"
                        id="checkout-btn"
                        type="button"
                    >
                        <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Proceed to Checkout
                    </button>
                    
                    <button
                        onclick="clearCart()"
                        class="tw-w-full tw-bg-white tw-border-2 tw-border-gray-300 tw-text-gray-700 tw-py-3 tw-rounded-xl tw-font-bold tw-text-sm tw-transition-all hover:tw-border-red-500 hover:tw-text-red-600 hover:tw-bg-red-50 tw-flex tw-items-center tw-justify-center tw-gap-2"
                        id="clear-cart-btn"
                        type="button"
                    >
                        <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Clear Cart
                    </button>
                </div>
            </div>
        </div>';
    }
    
    /**
     * Render cart item template
     */
    public function renderCartItemTemplate(): string
    {
        return '
        <template id="cart-item-template">
            <div class="tw-cart-item tw-flex tw-items-start tw-gap-3 tw-p-3 sm:tw-p-4 tw-bg-white tw-rounded-xl tw-border-2 tw-border-gray-100 tw-shadow-sm hover:tw-shadow-md tw-transition-all" data-item-id="">
                <!-- Item Image -->
                <div class="tw-w-20 tw-h-20 sm:tw-w-24 sm:tw-h-24 tw-bg-gradient-to-br tw-from-gray-100 tw-to-gray-200 tw-rounded-lg tw-overflow-hidden tw-flex-shrink-0">
                    <img 
                        src="" 
                        alt="" 
                        class="tw-w-full tw-h-full tw-object-cover"
                        loading="lazy"
                        onerror="this.src=\'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&h=400&fit=crop&q=80\'"
                    >
                </div>
                
                <!-- Item Details -->
                <div class="tw-flex-1 tw-min-w-0">
                    <h4 class="tw-font-bold tw-text-gray-900 tw-text-sm sm:tw-text-base tw-mb-1 tw-line-clamp-1"></h4>
                    <p class="tw-text-xs sm:tw-text-sm tw-text-gray-600 tw-mb-2 tw-line-clamp-1"></p>
                    <p class="tw-text-orange-600 tw-font-black tw-text-base sm:tw-text-lg"></p>
                    
                    <!-- Quantity Controls -->
                    <div class="tw-flex tw-items-center tw-gap-2 tw-mt-3">
                        <button 
                            class="tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-bg-gray-100 hover:tw-bg-red-100 tw-text-gray-700 hover:tw-text-red-600 tw-rounded-lg tw-transition-all tw-font-bold"
                            onclick="updateCartQuantity(this.dataset.itemId, parseInt(this.parentElement.querySelector(\'.quantity-input\').value) - 1)"
                            aria-label="Decrease quantity"
                            type="button"
                        >
                            <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"></path>
                            </svg>
                        </button>
                        
                        <input 
                            type="number" 
                            class="quantity-input tw-w-14 tw-text-center tw-border-2 tw-border-gray-300 tw-rounded-lg tw-py-1.5 tw-text-sm tw-font-bold tw-text-gray-900 focus:tw-border-orange-500 focus:tw-outline-none" 
                            value="1" 
                            min="0" 
                            max="99"
                            onchange="updateCartQuantity(this.dataset.itemId, parseInt(this.value))"
                        >
                        
                        <button 
                            class="tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-bg-gray-100 hover:tw-bg-green-100 tw-text-gray-700 hover:tw-text-green-600 tw-rounded-lg tw-transition-all tw-font-bold"
                            onclick="updateCartQuantity(this.dataset.itemId, parseInt(this.parentElement.querySelector(\'.quantity-input\').value) + 1)"
                            aria-label="Increase quantity"
                            type="button"
                        >
                            <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                        
                        <button 
                            class="tw-ml-auto tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-text-gray-400 hover:tw-text-red-600 hover:tw-bg-red-50 tw-rounded-lg tw-transition-all"
                            onclick="removeFromCart(this.dataset.itemId)"
                            aria-label="Remove item"
                            type="button"
                        >
                            <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>';
    }
    
    /**
     * Render floating cart button for mobile
     */
    public function renderFloatingCartButton(): string
    {
        return '
        <button
            id="floating-cart-btn"
            class="tw-fixed tw-bottom-6 tw-right-6 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-rounded-full tw-w-16 tw-h-16 sm:tw-w-18 sm:tw-h-18 tw-flex tw-items-center tw-justify-center tw-shadow-2xl hover:tw-shadow-3xl tw-transition-all tw-duration-300 tw-z-50 tw-hidden hover:tw-scale-110"
            onclick="openCart()"
            aria-label="Open shopping cart"
            type="button"
        >
            <div class="tw-relative">
                <svg class="tw-w-8 tw-h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span
                    id="floating-cart-badge"
                    class="tw-absolute -tw-top-2 -tw-right-2 tw-bg-yellow-400 tw-text-gray-900 tw-text-xs tw-font-black tw-rounded-full tw-min-w-[24px] tw-h-6 tw-flex tw-items-center tw-justify-center tw-shadow-lg tw-border-2 tw-border-white tw-animate-pulse"
                    data-cart-count
                    style="display: none;"
                >0</span>
            </div>
        </button>';
    }

    /**
     * Render cart JavaScript functionality
     */
    public function renderCartScript(): string
    {
        return '
        <script>
        function openCart() {
            const sidebar = document.getElementById("cart-sidebar");
            const overlay = document.getElementById("cart-overlay");
            const floatingBtn = document.getElementById("floating-cart-btn");

            if (sidebar && overlay) {
                sidebar.classList.remove("tw-translate-x-full");
                overlay.classList.remove("tw-hidden");
                if (floatingBtn) floatingBtn.classList.add("tw-hidden");
                document.body.style.overflow = "hidden";
                updateCartDisplay();
            }
        }

        function closeCart() {
            const sidebar = document.getElementById("cart-sidebar");
            const overlay = document.getElementById("cart-overlay");

            if (sidebar && overlay) {
                sidebar.classList.add("tw-translate-x-full");
                overlay.classList.add("tw-hidden");
                document.body.style.overflow = "";
            }
        }

        function toggleCart() {
            const sidebar = document.getElementById("cart-sidebar");
            if (sidebar && sidebar.classList.contains("tw-translate-x-full")) {
                openCart();
            } else {
                closeCart();
            }
        }

        function showLoginModal() {
            const modal = document.createElement("div");
            modal.className = "tw-fixed tw-inset-0 tw-z-[9999] tw-flex tw-items-center tw-justify-center tw-p-4";
            modal.innerHTML = `
                <div class="tw-absolute tw-inset-0 tw-bg-black/60 tw-backdrop-blur-sm" onclick="this.parentElement.remove()"></div>
                <div class="tw-relative tw-bg-white tw-rounded-2xl tw-shadow-2xl tw-max-w-md tw-w-full tw-p-6 sm:tw-p-8 tw-transform tw-scale-95 tw-opacity-0 tw-transition-all tw-duration-300">
                    <div class="tw-text-center">
                        <div class="tw-w-16 tw-h-16 tw-mx-auto tw-mb-4 tw-bg-gradient-to-br tw-from-orange-100 tw-to-red-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                            <svg class="tw-w-8 tw-h-8 tw-text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <h3 class="tw-text-2xl tw-font-black tw-text-gray-900 tw-mb-2">Login Required</h3>
                        <p class="tw-text-gray-600 tw-mb-6">Please login to your customer account to add items to cart and place orders.</p>
                        <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-3">
                            <a href="' . url('/login') . '" class="tw-flex-1 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-px-6 tw-py-3 tw-rounded-xl tw-font-bold tw-shadow-lg hover:tw-shadow-xl tw-transition-all tw-text-center">
                                Login Now
                            </a>
                            <button onclick="this.closest(\'.tw-fixed\').remove()" class="tw-flex-1 tw-bg-gray-100 tw-text-gray-700 tw-px-6 tw-py-3 tw-rounded-xl tw-font-bold hover:tw-bg-gray-200 tw-transition-all">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            document.body.style.overflow = "hidden";

            setTimeout(() => {
                const content = modal.querySelector(".tw-relative");
                content.classList.remove("tw-scale-95", "tw-opacity-0");
                content.classList.add("tw-scale-100", "tw-opacity-100");
            }, 10);

            modal.addEventListener("click", (e) => {
                if (e.target === modal || e.target.classList.contains("tw-bg-black\\/60")) {
                    document.body.style.overflow = "";
                }
            });
        }

        async function updateCartDisplay() {
            const isAuth = document.querySelector("meta[name=\"user-authenticated\"]")?.content === "true";
            const userRole = document.querySelector("meta[name=\"user-role\"]")?.content;

            if (!isAuth || userRole !== "customer") {
                return;
            }

            let cartItems = [];
            let cartTotals = null;

            try {
                const cartUrl = "' . url('/api/cart/get.php') . '?_=" + Date.now();
                const response = await fetch(cartUrl, {
                    method: "GET",
                    cache: "no-cache",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Cache-Control": "no-cache",
                        "Pragma": "no-cache"
                    }
                });

                if (!response.ok) {
                    console.error("Cart API error - Status:", response.status, "URL:", cartUrl);
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                if (data.success && data.items) {
                    cartItems = data.items.map(item => ({
                        id: item.id,
                        name: item.item_name || item.name,
                        price: item.unit_price || item.price,
                        image: item.item_image || item.image,
                        restaurant: item.restaurant_name || item.restaurant,
                        quantity: item.quantity
                    }));
                    cartTotals = data.cart_totals || null;
                } else if (!data.success) {
                    console.warn("Cart API returned error:", data.message);
                }
            } catch (error) {
                console.error("Failed to fetch cart:", error);
                console.error("Cart URL:", "' . url('/api/cart/get.php') . '");
            }

            const cartEmpty = document.getElementById("cart-empty");
            const cartContent = document.getElementById("cart-content");
            const cartFooter = document.getElementById("cart-footer");

            updateCartBadges(cartItems.length);

            if (cartItems.length === 0) {
                if (cartEmpty) cartEmpty.classList.remove("tw-hidden");
                if (cartContent) cartContent.classList.add("tw-hidden");
                if (cartFooter) cartFooter.classList.add("tw-hidden");
            } else {
                if (cartEmpty) cartEmpty.classList.add("tw-hidden");
                if (cartContent) cartContent.classList.remove("tw-hidden");
                if (cartFooter) cartFooter.classList.remove("tw-hidden");

                renderCartItems(cartItems);
                updateCartTotals(cartItems, cartTotals);
            }
        }

        function updateCartBadges(itemCount) {
            const badges = document.querySelectorAll("[data-cart-count]");
            badges.forEach(badge => {
                if (itemCount > 0) {
                    badge.style.display = "flex";
                    badge.textContent = itemCount > 99 ? "99+" : itemCount;
                } else {
                    badge.style.display = "none";
                }
            });

            const floatingBtn = document.getElementById("floating-cart-btn");
            if (floatingBtn) {
                floatingBtn.classList.toggle("tw-hidden", itemCount === 0);
            }
        }

        function renderCartItems(items) {
            const container = document.getElementById("cart-content");
            const template = document.getElementById("cart-item-template");

            if (!container) {
                console.error("Cart content container not found");
                return;
            }

            if (!template) {
                console.error("Cart item template not found");
                return;
            }

            container.innerHTML = "";

            items.forEach(item => {
                try {
                    const clone = template.content.cloneNode(true);
                    const itemElement = clone.querySelector(".tw-cart-item");

                    if (!itemElement) {
                        console.error("Cart item element not found in template");
                        return;
                    }

                    itemElement.dataset.itemId = item.id;

                    const imgElement = itemElement.querySelector("img");
                    if (imgElement) {
                        imgElement.src = item.image || "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&h=400&fit=crop&q=80";
                        imgElement.alt = item.name;
                    }

                    const nameElement = itemElement.querySelector("h4");
                    if (nameElement) nameElement.textContent = item.name;

                    const restaurantElement = itemElement.querySelector("p");
                    if (restaurantElement) restaurantElement.textContent = item.restaurant || "Restaurant";

                    const priceElement = itemElement.querySelector(".tw-text-orange-600");
                    if (priceElement) priceElement.textContent = `${Number(item.price).toLocaleString()} FCFA`;

                    const quantityInput = itemElement.querySelector(".quantity-input");
                    if (quantityInput) {
                        quantityInput.value = item.quantity;
                        quantityInput.dataset.itemId = item.id;
                    }

                    itemElement.querySelectorAll("button").forEach(btn => {
                        btn.dataset.itemId = item.id;
                    });

                    container.appendChild(clone);
                } catch (error) {
                    console.error("Error rendering cart item:", error, item);
                }
            });
        }

        function updateCartTotals(items, cartTotals = null) {
            let subtotal, delivery, total;

            if (cartTotals) {
                // Use totals from API response
                subtotal = cartTotals.subtotal || 0;
                delivery = cartTotals.delivery_fee || 0;
                total = cartTotals.total || 0;
            } else {
                // Fallback calculation
                subtotal = items.reduce((sum, item) => sum + (Number(item.price) * Number(item.quantity)), 0);
                delivery = 500; // Default base fee
                total = subtotal + delivery;
            }

            const subtotalEl = document.getElementById("cart-subtotal");
            const deliveryEl = document.getElementById("cart-delivery");
            const totalEl = document.getElementById("cart-total");

            if (subtotalEl) subtotalEl.textContent = `${subtotal.toLocaleString()} FCFA`;
            if (deliveryEl) deliveryEl.textContent = `${delivery.toLocaleString()} FCFA`;
            if (totalEl) totalEl.textContent = `${total.toLocaleString()} FCFA`;
        }

        async function addToCart(itemId, itemName, itemPrice, itemImage, restaurantId, restaurantName, category) {
            const isAuth = document.querySelector("meta[name=\"user-authenticated\"]")?.content === "true";
            const userRole = document.querySelector("meta[name=\"user-role\"]")?.content;

            if (!isAuth) {
                showLoginModal();
                return;
            }

            if (userRole !== "customer") {
                if (typeof showToast === "function") {
                    showToast("Only customers can add items to cart", "error");
                }
                return;
            }

            try {
                const addUrl = "' . url('/api/cart/add.php') . '?_=" + Date.now();
                const response = await fetch(addUrl, {
                    method: "POST",
                    cache: "no-cache",
                    headers: {
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        "Cache-Control": "no-cache",
                        "Pragma": "no-cache"
                    },
                    body: JSON.stringify({
                        menu_item_id: itemId,
                        quantity: 1,
                        customizations: [],
                        special_instructions: ""
                    })
                });

                if (!response.ok) {
                    console.error("Add to cart API error - Status:", response.status, "URL:", addUrl);
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                if (data.success) {
                    await updateCartDisplay();
                    if (typeof showToast === "function") {
                        showToast(`${itemName} added to cart`, "success");
                    }
                } else {
                    console.warn("Add to cart returned error:", data.message);
                    if (typeof showToast === "function") {
                        showToast(data.message || "Failed to add item to cart", "error");
                    }
                }
            } catch (error) {
                console.error("Add to cart error:", error);
                console.error("Add URL:", "' . url('/api/cart/add.php') . '");
                if (typeof showToast === "function") {
                    showToast("Failed to add item to cart", "error");
                }
            }
        }

        async function removeFromCart(itemId) {
            try {
                const removeUrl = "' . url('/api/cart/remove.php') . '?_=" + Date.now();
                const response = await fetch(removeUrl, {
                    method: "POST",
                    cache: "no-cache",
                    headers: {
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        "Cache-Control": "no-cache",
                        "Pragma": "no-cache"
                    },
                    body: JSON.stringify({ cart_item_id: itemId })
                });

                if (!response.ok) {
                    console.error("Remove from cart API error - Status:", response.status, "URL:", removeUrl);
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                if (data.success) {
                    updateCartDisplay();
                    if (typeof showToast === "function") {
                        showToast("Item removed from cart", "info");
                    }
                } else {
                    console.warn("Remove from cart returned error:", data.message);
                    if (typeof showToast === "function") {
                        showToast(data.message || "Failed to remove item", "error");
                    }
                }
            } catch (error) {
                console.error("Remove from cart error:", error);
                console.error("Remove URL:", "' . url('/api/cart/remove.php') . '");
                if (typeof showToast === "function") {
                    showToast("Failed to remove item", "error");
                }
            }
        }

        async function updateCartQuantity(itemId, newQuantity) {
            if (newQuantity <= 0) {
                removeFromCart(itemId);
                return;
            }

            try {
                const updateUrl = "' . url('/api/cart/update.php') . '?_=" + Date.now();
                const response = await fetch(updateUrl, {
                    method: "POST",
                    cache: "no-cache",
                    headers: {
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        "Cache-Control": "no-cache",
                        "Pragma": "no-cache"
                    },
                    body: JSON.stringify({
                        cart_item_id: itemId,
                        quantity: Math.max(1, Math.min(99, newQuantity))
                    })
                });

                if (!response.ok) {
                    console.error("Update cart API error - Status:", response.status, "URL:", updateUrl);
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                if (data.success) {
                    updateCartDisplay();
                } else {
                    console.warn("Update cart returned error:", data.message);
                    if (typeof showToast === "function") {
                        showToast(data.message || "Failed to update quantity", "error");
                    }
                }
            } catch (error) {
                console.error("Update cart quantity error:", error);
                console.error("Update URL:", "' . url('/api/cart/update.php') . '");
                if (typeof showToast === "function") {
                    showToast("Failed to update quantity", "error");
                }
            }
        }

        async function proceedToCheckout() {
            const isAuth = document.querySelector("meta[name=\"user-authenticated\"]")?.content === "true";
            const userRole = document.querySelector("meta[name=\"user-role\"]")?.content;

            if (!isAuth) {
                showLoginModal();
                return;
            }

            if (userRole !== "customer") {
                if (typeof showToast === "function") {
                    showToast("Only customers can access checkout", "error");
                }
                return;
            }

            try {
                const cartUrl = "' . url('/api/cart/get.php') . '?_=" + Date.now();
                const response = await fetch(cartUrl, {
                    method: "GET",
                    cache: "no-cache",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Cache-Control": "no-cache",
                        "Pragma": "no-cache"
                    }
                });

                if (!response.ok) {
                    console.error("Checkout cart check failed - Status:", response.status, "URL:", cartUrl);
                    // Still proceed to checkout, let server handle it
                    window.location.href = "' . url('/checkout') . '";
                    return;
                }

                const data = await response.json();
                if (data.success && data.items && data.items.length > 0) {
                    window.location.href = "' . url('/checkout') . '";
                } else {
                    console.warn("Cart is empty or API returned error:", data.message);
                    if (typeof showToast === "function") {
                        showToast("Your cart is empty", "warning");
                    }
                }
            } catch (error) {
                console.error("Checkout error:", error);
                console.error("Cart URL:", "' . url('/api/cart/get.php') . '");
                // Still proceed to checkout on error
                window.location.href = "' . url('/checkout') . '";
            }
        }

        async function clearCart() {
            if (!confirm("Are you sure you want to clear your cart?")) {
                return;
            }

            try {
                const response = await fetch("' . url('/api/cart/clear.php') . '?_=" + Date.now(), {
                    method: "POST",
                    cache: "no-cache",
                    headers: {
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        "Cache-Control": "no-cache",
                        "Pragma": "no-cache"
                    }
                });

                const data = await response.json();
                if (data.success) {
                    updateCartDisplay();
                    if (typeof showToast === "function") {
                        showToast("Cart cleared", "info");
                    }
                } else {
                    if (typeof showToast === "function") {
                        showToast(data.message || "Failed to clear cart", "error");
                    }
                }
            } catch (error) {
                console.error("Clear cart error:", error);
                if (typeof showToast === "function") {
                    showToast("Failed to clear cart", "error");
                }
            }
        }

        window.openCart = openCart;
        window.closeCart = closeCart;
        window.toggleCart = toggleCart;
        window.addToCart = addToCart;
        window.removeFromCart = removeFromCart;
        window.updateCartQuantity = updateCartQuantity;
        window.proceedToCheckout = proceedToCheckout;
        window.clearCart = clearCart;

        document.addEventListener("DOMContentLoaded", async function() {
            await updateCartDisplay();

            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get("cart") === "open") {
                openCart();
                const cleanParams = new URLSearchParams(window.location.search);
                cleanParams.delete("cart");
                const newUrl = window.location.pathname + (cleanParams.toString() ? "?" + cleanParams.toString() : "");
                window.history.replaceState({}, "", newUrl);
            }
        });
        </script>';
    }
}
?>
