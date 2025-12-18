<?php
$title = $title ?? 'Checkout - Time2Eat';
$user = $user ?? null;
$cartItems = $cartItems ?? [];
$cartTotals = $cartTotals ?? [];
$cashOnDeliveryEnabled = $cashOnDeliveryEnabled ?? true;

// Get map provider from global config (set by MapHelper in layout)
// This will be available via window.MAP_CONFIG in JavaScript
?>

<!-- Mobile-First Checkout Page -->
<div class="tw-min-h-screen tw-bg-gray-50 tw-pb-24 md:tw-pb-8">
    <!-- Back Button (Mobile) -->
    <div class="tw-sticky tw-top-0 tw-z-10 tw-bg-white tw-border-b tw-border-gray-200 tw-px-4 tw-py-3 md:tw-hidden">
        <button onclick="window.history.back()" class="tw-flex tw-items-center tw-text-gray-700 tw-font-medium">
            <svg class="tw-w-5 tw-h-5 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back
        </button>
    </div>

    <main class="tw-max-w-7xl tw-mx-auto tw-px-0 md:tw-px-6 lg:tw-px-8 tw-py-0 md:tw-py-8 tw-pb-32 lg:tw-pb-8">

        <!-- Progress Steps Header -->
        <div class="tw-bg-white tw-shadow-sm tw-border-b tw-border-gray-200 tw-mb-6">
            <!-- Mobile Progress Steps -->
            <div class="tw-block md:tw-hidden tw-px-4 tw-py-4">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <div class="tw-flex tw-items-center">
                            <div class="tw-h-10 tw-w-10 tw-bg-green-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-shadow-sm">
                                <svg class="tw-w-5 tw-h-5 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="tw-h-px tw-w-8 tw-bg-gray-300"></div>
                        <div class="tw-flex tw-items-center">
                            <div class="tw-h-10 tw-w-10 tw-bg-orange-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-shadow-sm">
                                <span class="tw-text-sm tw-font-bold tw-text-white">2</span>
                            </div>
                        </div>
                        <div class="tw-h-px tw-w-8 tw-bg-gray-300"></div>
                        <div class="tw-flex tw-items-center">
                            <div class="tw-h-10 tw-w-10 tw-bg-gray-300 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                <span class="tw-text-sm tw-font-medium tw-text-gray-600">3</span>
                            </div>
                        </div>
                    </div>
                    <div class="tw-text-right">
                        <h1 class="tw-text-lg tw-font-bold tw-text-gray-900">Checkout</h1>
                        <p class="tw-text-sm tw-text-gray-600">Step 2 of 3</p>
                    </div>
                </div>
            </div>

            <!-- Desktop Progress Steps -->
            <div class="tw-hidden md:tw-block tw-px-6 tw-py-6">
                <div class="tw-max-w-4xl tw-mx-auto">
                    <div class="tw-flex tw-items-center tw-justify-center tw-gap-4">
                        <!-- Step 1: Cart (Completed) -->
                        <div class="tw-flex tw-items-center tw-gap-3">
                            <div class="tw-h-12 tw-w-12 tw-bg-green-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-shadow-lg">
                                <svg class="tw-w-6 tw-h-6 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="tw-text-left">
                                <h3 class="tw-text-base tw-font-semibold tw-text-gray-900">Cart</h3>
                                <p class="tw-text-sm tw-text-gray-600">Items selected</p>
                            </div>
                        </div>

                        <!-- Connector Line 1 -->
                        <div class="tw-flex-1 tw-h-px tw-bg-gray-300 tw-mx-4"></div>

                        <!-- Step 2: Checkout (Active) -->
                        <div class="tw-flex tw-items-center tw-gap-3">
                            <div class="tw-h-12 tw-w-12 tw-bg-orange-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-shadow-lg tw-ring-4 tw-ring-orange-100">
                                <span class="tw-text-lg tw-font-bold tw-text-white">2</span>
                            </div>
                            <div class="tw-text-left">
                                <h3 class="tw-text-base tw-font-semibold tw-text-orange-600">Checkout</h3>
                                <p class="tw-text-sm tw-text-orange-500">Payment & delivery</p>
                            </div>
                        </div>

                        <!-- Connector Line 2 -->
                        <div class="tw-flex-1 tw-h-px tw-bg-gray-300 tw-mx-4"></div>

                        <!-- Step 3: Order Tracking (Pending) -->
                        <div class="tw-flex tw-items-center tw-gap-3">
                            <div class="tw-h-12 tw-w-12 tw-bg-gray-300 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                <span class="tw-text-lg tw-font-medium tw-text-gray-600">3</span>
                            </div>
                            <div class="tw-text-left">
                                <h3 class="tw-text-base tw-font-semibold tw-text-gray-600">Order Tracking</h3>
                                <p class="tw-text-sm tw-text-gray-500">Track your order</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Order Summary (Above Form) -->
        <div class="tw-block lg:tw-hidden tw-mb-6">
            <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-100 tw-p-4 tw-mx-4">
                <h2 class="tw-text-lg tw-font-bold tw-text-gray-900 tw-mb-4 tw-flex tw-items-center">
                    <svg class="tw-w-5 tw-h-5 tw-mr-2 tw-text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Order Summary
                </h2>

                <!-- Cart Items -->
                <div class="tw-space-y-4 tw-mb-6 tw-max-h-56 tw-overflow-y-auto tw-pr-2">
                    <?php foreach ($cartItems as $item): ?>
                    <div class="tw-flex tw-items-start tw-gap-4 tw-p-3 tw-bg-gray-50 tw-rounded-lg tw-border tw-border-gray-100">
                        <div class="tw-relative tw-flex-shrink-0">
                            <img 
                                src="<?= e($item['item_image'] ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=100&h=100&fit=crop&q=80') ?>" 
                                alt="<?= e($item['item_name']) ?>" 
                                class="tw-w-14 tw-h-14 tw-rounded-lg tw-object-cover tw-shadow-sm"
                                onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=100&h=100&fit=crop&q=80'"
                            >
                        </div>
                        <div class="tw-flex-1 tw-min-w-0">
                            <h3 class="tw-text-sm tw-font-semibold tw-text-gray-900 tw-line-clamp-2 tw-leading-tight"><?= e($item['item_name']) ?></h3>
                            <p class="tw-text-xs tw-text-gray-500 tw-mt-1 tw-font-medium">Qty: <?= $item['quantity'] ?></p>
                            <?php if (!empty($item['item_description'])): ?>
                            <p class="tw-text-xs tw-text-gray-600 tw-mt-1 tw-line-clamp-1"><?= e($item['item_description']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="tw-text-right tw-flex-shrink-0">
                            <span class="tw-text-sm tw-font-bold tw-text-gray-900"><?= number_format($item['total_price'] ?? 0) ?> FCFA</span>
                            <?php if ($item['quantity'] > 1): ?>
                            <p class="tw-text-xs tw-text-gray-500 tw-mt-1"><?= number_format($item['unit_price'] ?? 0) ?> × <?= $item['quantity'] ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Order Totals -->
                <div class="tw-bg-gray-50 tw-rounded-lg tw-p-4 tw-space-y-3">
                    <div class="tw-flex tw-justify-between tw-items-center tw-text-sm">
                        <span class="tw-text-gray-600 tw-font-medium">Subtotal</span>
                        <span class="tw-font-semibold tw-text-gray-900"><?= number_format($cartTotals['subtotal'] ?? 0) ?> FCFA</span>
                    </div>
                    <?php if (($cartTotals['service_fee'] ?? 0) > 0): ?>
                    <div class="tw-flex tw-justify-between tw-items-center tw-text-sm">
                        <span class="tw-text-gray-600 tw-font-medium">Service Fee</span>
                        <span class="tw-font-semibold tw-text-gray-900"><?= number_format($cartTotals['service_fee'] ?? 0) ?> FCFA</span>
                    </div>
                    <?php endif; ?>

                    <!-- Delivery Fee with Breakdown -->
                    <div class="tw-space-y-2">
                        <div class="tw-flex tw-justify-between tw-items-center tw-text-sm">
                            <span class="tw-text-gray-600 tw-font-medium tw-flex tw-items-center">
                                Delivery Fee
                                <span id="delivery-fee-info-icon" class="tw-ml-1 tw-text-blue-500 tw-cursor-help" title="Click for details">
                                    <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </span>
                            </span>
                            <span class="tw-font-semibold tw-text-gray-900" id="delivery-fee-amount"><?= number_format($cartTotals['delivery_fee'] ?? 0) ?> FCFA</span>
                        </div>
                        <!-- Delivery Fee Breakdown (hidden by default, shown after address selection) -->
                        <div id="delivery-fee-breakdown" class="tw-hidden tw-text-xs tw-text-gray-600 tw-pl-4 tw-border-l-2 tw-border-blue-200 tw-ml-2">
                            <div class="tw-space-y-1">
                                <div id="delivery-distance-info"></div>
                                <div id="delivery-zone-info"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="tw-border-t tw-border-gray-300 tw-pt-3 tw-mt-3 tw-flex tw-justify-between tw-items-center">
                        <span class="tw-text-base tw-font-bold tw-text-gray-900">Total</span>
                        <span class="tw-text-xl tw-font-black tw-text-orange-600"><?= number_format($cartTotals['total'] ?? 0) ?> FCFA</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-0 lg:tw-gap-6">
            <!-- Checkout Form -->
            <div class="lg:tw-col-span-2">
                <form id="checkout-form" class="tw-space-y-0 md:tw-space-y-4">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">

                    <!-- Delivery Location -->
                    <div class="tw-bg-white tw-rounded-none md:tw-rounded-lg tw-shadow-none md:tw-shadow-sm tw-p-4 md:tw-p-6 tw-border-b md:tw-border-0 tw-border-gray-200">
                        <h2 class="tw-text-base md:tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4 tw-flex tw-items-center">
                            <svg class="tw-w-5 tw-h-5 tw-mr-2 tw-text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Delivery Location
                        </h2>

                        <!-- Saved Addresses Section -->
                        <div id="saved-addresses-section" class="tw-mb-6 tw-hidden">
                            <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
                                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Saved Addresses</label>
                                <button type="button" onclick="loadSavedAddresses()" class="tw-text-xs tw-text-orange-600 hover:tw-text-orange-700 tw-font-medium">
                                    <svg class="tw-w-4 tw-h-4 tw-inline tw-mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Refresh
                                </button>
                            </div>
                            <div id="saved-addresses-list" class="tw-space-y-2 tw-mb-4">
                                <!-- Saved addresses will be loaded here -->
                                <div class="tw-text-center tw-py-4 tw-text-gray-500">
                                    <svg class="tw-w-8 tw-h-8 tw-mx-auto tw-mb-2 tw-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <p class="tw-text-sm">No saved addresses found</p>
                                    <p class="tw-text-xs tw-text-gray-400">Add an address below to save it for future use</p>
                                </div>
                            </div>
                        </div>

                        <!-- Location Type Selection -->
                        <div class="tw-mb-4">
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-3">Choose location method:</label>
                            <div class="tw-grid tw-grid-cols-3 tw-gap-3">
                                <button type="button" onclick="selectLocationType('saved')" id="btn-saved" class="tw-p-3 md:tw-p-4 tw-border-2 tw-border-gray-300 tw-bg-white tw-rounded-lg tw-text-center tw-transition-all hover:tw-border-gray-400 tw-min-h-[80px] tw-flex tw-flex-col tw-items-center tw-justify-center">
                                    <svg class="tw-w-6 tw-h-6 tw-text-gray-600 tw-mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <div class="tw-text-sm tw-font-semibold tw-text-gray-700">Saved</div>
                                    <div class="tw-text-xs tw-text-gray-600 tw-mt-1">From list</div>
                                </button>
                                <button type="button" onclick="selectLocationType('gps')" id="btn-gps" class="tw-p-3 md:tw-p-4 tw-border-2 tw-border-orange-500 tw-bg-orange-50 tw-rounded-lg tw-text-center tw-transition-all tw-min-h-[80px] tw-flex tw-flex-col tw-items-center tw-justify-center">
                                    <svg class="tw-w-6 tw-h-6 tw-text-orange-600 tw-mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    </svg>
                                    <div class="tw-text-sm tw-font-semibold tw-text-orange-700">Use GPS</div>
                                    <div class="tw-text-xs tw-text-gray-600 tw-mt-1">Current location</div>
                                </button>
                                <button type="button" onclick="selectLocationType('text')" id="btn-text" class="tw-p-3 md:tw-p-4 tw-border-2 tw-border-gray-300 tw-bg-white tw-rounded-lg tw-text-center tw-transition-all hover:tw-border-gray-400 tw-min-h-[80px] tw-flex tw-flex-col tw-items-center tw-justify-center">
                                    <svg class="tw-w-6 tw-h-6 tw-text-gray-600 tw-mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    <div class="tw-text-sm tw-font-semibold tw-text-gray-700">Enter Address</div>
                                    <div class="tw-text-xs tw-text-gray-600 tw-mt-1">Type manually</div>
                                </button>
                            </div>
                        </div>

                        <!-- GPS Location Section -->
                        <div id="gps-section" class="tw-space-y-3">
                            <div id="map" class="tw-h-64 md:tw-h-80 tw-w-full tw-rounded-lg tw-border tw-border-gray-300"></div>
                            <button type="button" onclick="getCurrentLocation()" class="tw-w-full tw-px-4 tw-py-3 tw-bg-orange-500 tw-text-white tw-rounded-lg hover:tw-bg-orange-600 tw-transition-colors tw-flex tw-items-center tw-justify-center tw-font-medium tw-shadow-sm">
                                <svg class="tw-w-5 tw-h-5 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                </svg>
                                Get My Current Location
                            </button>
                            <div id="location-info" class="tw-hidden tw-p-3 tw-bg-green-50 tw-border tw-border-green-200 tw-rounded-lg">
                                <div class="tw-flex tw-items-start">
                                    <svg class="tw-w-5 tw-h-5 tw-text-green-600 tw-mr-2 tw-mt-0.5 tw-flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="tw-flex-1">
                                        <div class="tw-text-sm tw-font-medium tw-text-green-800">Location captured</div>
                                        <div id="location-coords" class="tw-text-xs tw-text-green-700 tw-mt-1"></div>
                                        <button type="button" onclick="showSaveAddressModal()" class="tw-mt-2 tw-text-xs tw-text-orange-600 hover:tw-text-orange-700 tw-font-medium tw-flex tw-items-center">
                                            <svg class="tw-w-3 tw-h-3 tw-mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Save this address
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="latitude" name="latitude">
                            <input type="hidden" id="longitude" name="longitude">
                        </div>

                        <!-- Text Address Section -->
                        <div id="text-section" class="tw-hidden tw-space-y-3">
                            <div>
                                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Search Location *</label>
                                <div class="tw-relative">
                                    <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                                        <svg class="tw-h-5 tw-w-5 tw-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <input type="text" id="address-search" name="address_search" placeholder="Type to search for locations..." class="tw-w-full tw-pl-10 tw-pr-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-transparent" autocomplete="off">
                                    <div id="address-suggestions" class="tw-absolute tw-top-full tw-left-0 tw-right-0 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-shadow-lg tw-z-40 tw-max-h-60 tw-overflow-y-auto tw-hidden tw-mt-1">
                                        <!-- Suggestions will appear here -->
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Street Address *</label>
                                <input type="text" id="street-address" name="street_address" placeholder="e.g., 123 Main Street, Bamenda" class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-transparent">
                            </div>
                            <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 tw-gap-3">
                                <div>
                                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Neighborhood</label>
                                    <input type="text" id="neighborhood" name="neighborhood" placeholder="e.g., Commercial Avenue" class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-transparent">
                                </div>
                                <div>
                                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Landmark</label>
                                    <input type="text" id="landmark" name="landmark" placeholder="e.g., Near City Council" class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-transparent">
                                </div>
                            </div>
                            
                            <!-- Map Picker for Manual Address -->
                            <div class="tw-mt-4">
                                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                                    <span>Select Location on Map *</span>
                                    <span class="tw-text-xs tw-text-gray-500 tw-font-normal tw-ml-2">(Required for delivery fee calculation)</span>
                                </label>
                                <div id="text-address-map" class="tw-w-full tw-h-64 tw-border tw-border-gray-300 tw-rounded-lg tw-overflow-hidden tw-bg-gray-100" style="min-height: 256px;">
                                    <!-- Map will be initialized here -->
                                </div>
                                <p class="tw-text-xs tw-text-gray-500 tw-mt-2 tw-flex tw-items-center">
                                    <svg class="tw-w-4 tw-h-4 tw-mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Click on the map or drag the marker to set your exact delivery location. This is required for accurate delivery fee calculation.
                                </p>
                            </div>
                            
                            <!-- Hidden fields for coordinates -->
                            <input type="hidden" id="text-latitude" name="text_latitude">
                            <input type="hidden" id="text-longitude" name="text_longitude">
                            
                            <div class="tw-flex tw-justify-end tw-mt-4">
                                <button type="button" onclick="showSaveAddressModal()" class="tw-text-xs tw-text-orange-600 hover:tw-text-orange-700 tw-font-medium tw-flex tw-items-center">
                                    <svg class="tw-w-3 tw-h-3 tw-mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Save this address
                                </button>
                            </div>
                        </div>

                        <!-- Delivery Instructions (Always visible) -->
                        <div class="tw-mt-4">
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Delivery Instructions</label>
                            <textarea name="delivery_instructions" id="delivery-instructions" rows="3" placeholder="Any special instructions for the delivery rider (e.g., gate code, building number, call on arrival...)" class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-transparent tw-resize-none"></textarea>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="tw-bg-white tw-rounded-none md:tw-rounded-lg tw-shadow-none md:tw-shadow-sm tw-p-4 md:tw-p-6 tw-border-b md:tw-border-0 tw-border-gray-200">
                        <h2 class="tw-text-base md:tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4 tw-flex tw-items-center">
                            <svg class="tw-w-5 tw-h-5 tw-mr-2 tw-text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            Payment Method
                        </h2>

                        <div class="tw-space-y-3">
                            <?php if ($cashOnDeliveryEnabled): ?>
                            <label class="tw-flex tw-items-start tw-p-4 tw-border-2 tw-border-orange-500 tw-bg-orange-50 tw-rounded-lg tw-cursor-pointer tw-transition-all">
                                <input type="radio" name="payment_method" value="cash_on_delivery" class="tw-mt-1 tw-text-orange-500 focus:tw-ring-orange-500" checked>
                                <div class="tw-ml-3 tw-flex-1">
                                    <div class="tw-flex tw-items-center tw-flex-wrap tw-gap-2">
                                        <svg class="tw-w-5 tw-h-5 tw-text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <h3 class="tw-font-semibold tw-text-gray-900">Cash on Delivery</h3>
                                        <span class="tw-px-2 tw-py-0.5 tw-bg-green-100 tw-text-green-800 tw-text-xs tw-font-medium tw-rounded">Recommended</span>
                                    </div>
                                    <p class="tw-text-sm tw-text-gray-600 tw-mt-1">Pay with cash when your order arrives</p>
                                </div>
                            </label>
                            <?php else: ?>
                            <div class="tw-p-4 tw-bg-yellow-50 tw-border tw-border-yellow-200 tw-rounded-lg">
                                <div class="tw-flex tw-items-start">
                                    <svg class="tw-w-5 tw-h-5 tw-text-yellow-600 tw-mr-2 tw-mt-0.5 tw-flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <div>
                                        <div class="tw-text-sm tw-font-medium tw-text-yellow-800">Cash on Delivery Not Available</div>
                                        <div class="tw-text-xs tw-text-yellow-700 tw-mt-1">Please contact support to enable this payment method for your account.</div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <label class="tw-flex tw-items-start tw-p-4 tw-border-2 tw-border-gray-300 tw-bg-white tw-rounded-lg tw-cursor-pointer hover:tw-border-gray-400 tw-transition-all">
                                <input type="radio" name="payment_method" value="tranzak" class="tw-mt-1 tw-text-orange-500 focus:tw-ring-orange-500" <?= !$cashOnDeliveryEnabled ? 'checked' : '' ?>>
                                <div class="tw-ml-3 tw-flex-1">
                                    <div class="tw-flex tw-items-center tw-flex-wrap tw-gap-2">
                                        <svg class="tw-w-5 tw-h-5 tw-text-purple-600 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                        <h3 class="tw-font-semibold tw-text-gray-900">Tranzak Payment</h3>
                                        <span class="tw-px-2 tw-py-0.5 tw-bg-purple-100 tw-text-purple-800 tw-text-xs tw-font-medium tw-rounded">Secure</span>
                                    </div>
                                    <p class="tw-text-sm tw-text-gray-600 tw-mt-1">MTN Mobile Money, Orange Money, Bank Transfer, Cards</p>
                                </div>
                            </label>

                            <label class="tw-flex tw-items-start tw-p-4 tw-border-2 tw-border-gray-300 tw-bg-white tw-rounded-lg tw-cursor-pointer hover:tw-border-gray-400 tw-transition-all">
                                <input type="radio" name="payment_method" value="card" class="tw-mt-1 tw-text-orange-500 focus:tw-ring-orange-500">
                                <div class="tw-ml-3 tw-flex-1">
                                    <div class="tw-flex tw-items-center">
                                        <svg class="tw-w-5 tw-h-5 tw-text-gray-600 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                        <h3 class="tw-font-semibold tw-text-gray-900">Debit/Credit Card</h3>
                                    </div>
                                    <p class="tw-text-sm tw-text-gray-600 tw-mt-1">Visa, Mastercard</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Place Order Button (Desktop) -->
                    <button type="submit" id="place-order-btn" class="tw-hidden md:tw-flex tw-w-full tw-px-6 tw-py-4 tw-bg-orange-500 tw-text-white tw-font-bold tw-rounded-lg hover:tw-bg-orange-600 tw-transition-colors tw-items-center tw-justify-center tw-text-lg tw-shadow-lg tw-relative tw-cursor-pointer">
                        <svg class="tw-w-6 tw-h-6 tw-mr-2 tw-pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="tw-pointer-events-none">Place Order</span>
                    </button>
                </form>
            </div>

            <!-- Order Summary (Desktop) -->
            <div class="tw-hidden lg:tw-block lg:tw-col-span-1">
                <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-100 tw-p-6 tw-sticky tw-top-6">
                    <h2 class="tw-text-xl tw-font-bold tw-text-gray-900 tw-mb-6 tw-flex tw-items-center">
                        <svg class="tw-w-6 tw-h-6 tw-mr-2 tw-text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Order Summary
                    </h2>

                    <!-- Cart Items -->
                    <div class="tw-space-y-5 tw-mb-8 tw-max-h-96 tw-overflow-y-auto tw-pr-3">
                        <?php foreach ($cartItems as $item): ?>
                        <div class="tw-flex tw-items-start tw-gap-5 tw-p-4 tw-bg-gray-50 tw-rounded-xl tw-border tw-border-gray-100 tw-shadow-sm">
                            <div class="tw-relative tw-flex-shrink-0">
                                <img 
                                    src="<?= e($item['item_image'] ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=120&h=120&fit=crop&q=80') ?>" 
                                    alt="<?= e($item['item_name']) ?>" 
                                    class="tw-w-18 tw-h-18 tw-rounded-xl tw-object-cover tw-shadow-md"
                                    onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=120&h=120&fit=crop&q=80'"
                                >
                            </div>
                            <div class="tw-flex-1 tw-min-w-0">
                                <h3 class="tw-text-sm tw-font-semibold tw-text-gray-900 tw-line-clamp-2 tw-leading-tight tw-mb-1"><?= e($item['item_name']) ?></h3>
                                <p class="tw-text-xs tw-text-gray-500 tw-font-medium tw-mb-1">Qty: <?= $item['quantity'] ?></p>
                                <?php if (!empty($item['item_description'])): ?>
                                <p class="tw-text-xs tw-text-gray-600 tw-line-clamp-1"><?= e($item['item_description']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="tw-text-right tw-flex-shrink-0">
                                <span class="tw-text-sm tw-font-bold tw-text-gray-900"><?= number_format($item['total_price'] ?? 0) ?> FCFA</span>
                                <?php if ($item['quantity'] > 1): ?>
                                <p class="tw-text-xs tw-text-gray-500 tw-mt-1"><?= number_format($item['unit_price'] ?? 0) ?> × <?= $item['quantity'] ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Order Totals -->
                    <div class="tw-bg-gray-50 tw-rounded-lg tw-p-5 tw-space-y-4">
                        <div class="tw-flex tw-justify-between tw-items-center tw-text-sm">
                            <span class="tw-text-gray-600 tw-font-medium">Subtotal</span>
                            <span class="tw-font-semibold tw-text-gray-900"><?= number_format($cartTotals['subtotal'] ?? 0) ?> FCFA</span>
                        </div>
                        <?php if (($cartTotals['service_fee'] ?? 0) > 0): ?>
                        <div class="tw-flex tw-justify-between tw-items-center tw-text-sm">
                            <span class="tw-text-gray-600 tw-font-medium">Service Fee</span>
                            <span class="tw-font-semibold tw-text-gray-900"><?= number_format($cartTotals['service_fee'] ?? 0) ?> FCFA</span>
                        </div>
                        <?php endif; ?>

                        <!-- Delivery Fee with Breakdown -->
                        <div class="tw-space-y-2">
                            <div class="tw-flex tw-justify-between tw-items-center tw-text-sm">
                                <span class="tw-text-gray-600 tw-font-medium tw-flex tw-items-center">
                                    Delivery Fee
                                    <span class="tw-ml-1 tw-text-blue-500 tw-cursor-help" title="Will be calculated based on your delivery address">
                                        <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </span>
                                </span>
                                <span class="tw-font-semibold tw-text-gray-900" id="delivery-fee-amount-desktop"><?= number_format($cartTotals['delivery_fee'] ?? 0) ?> FCFA</span>
                            </div>
                            <!-- Delivery Fee Breakdown (shown after address selection) -->
                            <div id="delivery-fee-breakdown-desktop" class="tw-hidden tw-text-xs tw-bg-blue-50 tw-rounded tw-p-2 tw-border tw-border-blue-100">
                                <div class="tw-space-y-1">
                                    <div id="delivery-distance-info-desktop" class="tw-font-medium tw-text-blue-900"></div>
                                    <div id="delivery-zone-info-desktop" class="tw-text-blue-700"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="tw-border-t tw-border-gray-300 tw-pt-4 tw-mt-4 tw-flex tw-justify-between tw-items-center">
                            <span class="tw-text-lg tw-font-bold tw-text-gray-900">Total</span>
                            <span class="tw-text-2xl tw-font-black tw-text-orange-600"><?= number_format($cartTotals['total'] ?? 0) ?> FCFA</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Order Summary (Fixed Bottom) -->
        <div class="tw-fixed tw-bottom-0 tw-left-0 tw-right-0 tw-bg-white tw-border-t-2 tw-border-gray-200 tw-p-4 tw-shadow-2xl lg:tw-hidden">
            <!-- Summary Header -->
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                <div class="tw-flex tw-items-center">
                    <svg class="tw-w-5 tw-h-5 tw-mr-2 tw-text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <div>
                        <div class="tw-text-xs tw-text-gray-500 tw-font-medium"><?= count($cartItems) ?> item<?= count($cartItems) !== 1 ? 's' : '' ?></div>
                        <div class="tw-text-xl tw-font-black tw-text-orange-600"><?= number_format($cartTotals['total'] ?? 0) ?> FCFA</div>
                    </div>
                </div>
                <button type="button" onclick="toggleMobileSummary()" class="tw-bg-orange-50 tw-text-orange-600 tw-px-3 tw-py-2 tw-rounded-lg tw-font-medium tw-flex tw-items-center tw-transition-colors hover:tw-bg-orange-100">
                    <span id="summary-toggle-text" class="tw-text-sm">View Details</span>
                    <svg id="summary-toggle-icon" class="tw-w-4 tw-h-4 tw-ml-1 tw-transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                    </svg>
                </button>
            </div>

            <!-- Expandable Summary Details -->
            <div id="mobile-summary-details" class="tw-hidden tw-mb-4 tw-pb-4 tw-border-b tw-border-gray-200 tw-relative tw-z-10">
                <!-- Cart Items Preview -->
                <div class="tw-mb-4">
                    <h4 class="tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-3">Order Items</h4>
                    <div class="tw-space-y-3 tw-max-h-36 tw-overflow-y-auto">
                        <?php foreach (array_slice($cartItems, 0, 3) as $item): ?>
                        <div class="tw-flex tw-items-center tw-gap-3 tw-p-3 tw-bg-gray-50 tw-rounded-lg tw-border tw-border-gray-100">
                            <div class="tw-relative tw-flex-shrink-0">
                                <img 
                                    src="<?= e($item['item_image'] ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=80&h=80&fit=crop&q=80') ?>" 
                                    alt="<?= e($item['item_name']) ?>" 
                                    class="tw-w-12 tw-h-12 tw-rounded-lg tw-object-cover tw-shadow-sm"
                                    onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=80&h=80&fit=crop&q=80'"
                                >
                            </div>
                            <div class="tw-flex-1 tw-min-w-0">
                                <h5 class="tw-text-xs tw-font-semibold tw-text-gray-900 tw-truncate"><?= e($item['item_name']) ?></h5>
                                <p class="tw-text-xs tw-text-gray-500 tw-font-medium">Qty: <?= $item['quantity'] ?></p>
                            </div>
                            <span class="tw-text-xs tw-font-bold tw-text-gray-900"><?= number_format($item['total_price'] ?? 0) ?> FCFA</span>
                        </div>
                        <?php endforeach; ?>
                        <?php if (count($cartItems) > 3): ?>
                        <div class="tw-text-xs tw-text-gray-500 tw-text-center tw-py-2 tw-bg-gray-100 tw-rounded-lg">
                            +<?= count($cartItems) - 3 ?> more item<?= (count($cartItems) - 3) !== 1 ? 's' : '' ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Order Breakdown -->
                <div class="tw-bg-gray-50 tw-rounded-lg tw-p-4 tw-space-y-3">
                    <h4 class="tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-3">Order Breakdown</h4>
                    <div class="tw-space-y-3 tw-text-sm">
                        <div class="tw-flex tw-justify-between tw-items-center">
                            <span class="tw-text-gray-600 tw-font-medium">Subtotal</span>
                            <span class="tw-font-semibold tw-text-gray-900"><?= number_format($cartTotals['subtotal'] ?? 0) ?> FCFA</span>
                        </div>
                        <?php if (($cartTotals['service_fee'] ?? 0) > 0): ?>
                        <div class="tw-flex tw-justify-between tw-items-center">
                            <span class="tw-text-gray-600 tw-font-medium">Service Fee</span>
                            <span class="tw-font-semibold tw-text-gray-900"><?= number_format($cartTotals['service_fee'] ?? 0) ?> FCFA</span>
                        </div>
                        <?php endif; ?>

                        <!-- Delivery Fee with Breakdown -->
                        <div class="tw-space-y-2">
                            <div class="tw-flex tw-justify-between tw-items-center">
                                <span class="tw-text-gray-600 tw-font-medium">Delivery Fee</span>
                                <span class="tw-font-semibold tw-text-gray-900" id="delivery-fee-amount-modal"><?= number_format($cartTotals['delivery_fee'] ?? 0) ?> FCFA</span>
                            </div>
                            <!-- Delivery Fee Breakdown -->
                            <div id="delivery-fee-breakdown-modal" class="tw-hidden tw-text-xs tw-bg-blue-50 tw-rounded tw-p-2 tw-border tw-border-blue-100">
                                <div class="tw-space-y-1">
                                    <div id="delivery-distance-info-modal" class="tw-font-medium tw-text-blue-900"></div>
                                    <div id="delivery-zone-info-modal" class="tw-text-blue-700"></div>
                                </div>
                            </div>
                        </div>

                        <div class="tw-border-t tw-border-gray-300 tw-pt-3 tw-mt-3 tw-flex tw-justify-between tw-items-center">
                            <span class="tw-text-base tw-font-bold tw-text-gray-900">Total</span>
                            <span class="tw-text-lg tw-font-black tw-text-orange-600"><?= number_format($cartTotals['total'] ?? 0) ?> FCFA</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Place Order Button -->
            <button type="submit" form="checkout-form" id="place-order-btn-mobile" class="tw-w-full tw-px-6 tw-py-4 tw-bg-gradient-to-r tw-from-orange-500 tw-to-orange-600 tw-text-white tw-font-bold tw-rounded-xl hover:tw-from-orange-600 hover:tw-to-orange-700 tw-transition-all tw-duration-200 tw-flex tw-items-center tw-justify-center tw-shadow-lg hover:tw-shadow-xl tw-relative tw-cursor-pointer tw-z-10">
                <svg class="tw-w-5 tw-h-5 tw-mr-2 tw-pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="tw-pointer-events-none">Place Order - <?= number_format($cartTotals['total'] ?? 0) ?> FCFA</span>
            </button>
        </div>
    </main>
</div>

<!-- Save Address Modal -->
<div id="save-address-modal" class="tw-hidden tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-z-50">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-11/12 md:tw-w-96 tw-shadow-lg tw-rounded-2xl tw-bg-white">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Save Address</h3>
            <button onclick="closeSaveAddressModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                <svg class="tw-h-6 tw-w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form id="save-address-form" class="tw-space-y-4">
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Address Label *</label>
                <input type="text" id="address-label" name="label" placeholder="e.g., Home, Work, Office" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500" required>
            </div>
            
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Address *</label>
                <input type="text" id="address-line-1" name="address_line_1" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500" required>
            </div>
            
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">City *</label>
                <input type="text" id="address-city" name="city" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500" required>
            </div>
            
            <div class="tw-grid tw-grid-cols-2 tw-gap-3">
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">State</label>
                    <input type="text" id="address-state" name="state" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500">
                </div>
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Postal Code</label>
                    <input type="text" id="address-postal-code" name="postal_code" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500">
                </div>
            </div>
            
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Delivery Instructions</label>
                <textarea id="address-delivery-instructions" name="delivery_instructions" rows="2" placeholder="Any special instructions for delivery..." class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-resize-none"></textarea>
            </div>
            
            <div class="tw-flex tw-items-center">
                <input type="checkbox" id="set-as-default" name="is_default" class="tw-h-4 tw-w-4 tw-text-orange-600 tw-border-gray-300 tw-rounded focus:tw-ring-orange-500">
                <label for="set-as-default" class="tw-ml-2 tw-text-sm tw-text-gray-700">Set as default address</label>
            </div>
            
            <!-- Hidden fields for coordinates -->
            <input type="hidden" id="address-latitude" name="latitude">
            <input type="hidden" id="address-longitude" name="longitude">
            
            <div class="tw-flex tw-space-x-3 tw-pt-4">
                <button type="button" onclick="closeSaveAddressModal()" class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-gray-100 tw-text-gray-700 tw-rounded-lg tw-font-medium hover:tw-bg-gray-200 tw-transition-colors">
                    Cancel
                </button>
                <button type="submit" class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-orange-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-orange-700 tw-transition-colors">
                    Save Address
                </button>
            </div>
        </form>
    </div>
</div>

    <!-- Custom Styles for Order Summary -->
    <style>
        .tw-line-clamp-1 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 1;
        }
        .tw-line-clamp-2 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }
        .tw-line-clamp-3 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
        }
        .tw-w-18 {
            width: 4.5rem; /* 72px */
        }
        .tw-h-18 {
            height: 4.5rem; /* 72px */
        }
        
        /* Ensure place order buttons are fully clickable */
        #place-order-btn, #place-order-btn-mobile {
            position: relative;
            cursor: pointer;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }
        
        #place-order-btn:hover, #place-order-btn-mobile:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        #place-order-btn:active, #place-order-btn-mobile:active {
            transform: translateY(0);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        /* Ensure no elements interfere with button clicks */
        #place-order-btn *, #place-order-btn-mobile * {
            pointer-events: none;
        }
        
        /* Debug: Add visual feedback for clickable areas */
        #place-order-btn:hover, #place-order-btn-mobile:hover {
            outline: 2px solid rgba(255, 255, 255, 0.3);
            outline-offset: 2px;
        }
        
        /* Geocoding suggestions styling */
        #address-suggestions {
            border-top: none;
            border-radius: 0 0 0.5rem 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
            background: white;
        }
        
        #address-suggestions .tw-p-3 {
            padding: 0.75rem;
            border-bottom: 1px solid #f3f4f6;
        }
        
        #address-suggestions .tw-p-3:last-child {
            border-bottom: none;
        }
        
        #address-suggestions .tw-p-3:hover {
            background-color: #f9fafb;
        }
        
        #address-suggestions::-webkit-scrollbar {
            width: 6px;
        }
        
        #address-suggestions::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        #address-suggestions::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        #address-suggestions::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* Ensure suggestions are visible above other elements */
        #address-suggestions {
            z-index: 50 !important;
        }
        
        /* Z-Index Management - Proper hierarchy */
        /* Header/Back button - Highest priority */
        .tw-sticky.tw-top-0 {
            z-index: 50 !important;
        }
        
        /* Mobile order summary - Below header, above everything else */
        .tw-fixed.tw-bottom-0 {
            z-index: 40 !important;
        }
        
        /* Progress steps header - Below mobile summary, above map */
        .tw-bg-white.tw-shadow-sm.tw-border-b {
            position: relative;
            z-index: 20 !important;
        }
        
        /* Map container - Lowest priority, below everything */
        #map {
            position: relative;
            z-index: 10 !important;
        }
        
        /* Leaflet map elements - Keep below header but above other content */
        .leaflet-container {
            z-index: 10 !important;
        }
        
        .leaflet-control-container {
            z-index: 10 !important;
        }
        
        .leaflet-popup {
            z-index: 15 !important;
        }
        
        .leaflet-popup-pane {
            z-index: 15 !important;
        }
        
        .leaflet-tooltip-pane {
            z-index: 15 !important;
        }
        
        /* Ensure map attribution stays at bottom */
        .leaflet-control-attribution {
            z-index: 10 !important;
        }
        
    </style>

    <!-- Map Provider Scripts are loaded via MapHelper in the layout -->

    <script>
        // Map variables
        let mapProviderInstance = null;
        let map = null;
        let marker = null;
        
        let textMapProviderInstance = null;
        let textAddressMap = null;
        let textAddressMarker = null;
        
        let currentLocationType = 'gps';
        const mapProvider = window.MAP_CONFIG ? window.MAP_CONFIG.provider : 'leaflet';

        // Initialize Map (uses global MAP_CONFIG)
        async function initMap() {
            if (map) {
                console.log('Map already initialized, skipping...');
                return;
            }

            const container = document.getElementById('map');
            if (!container) return;

            mapProviderInstance = new MapProvider({
                container: container,
                center: [5.9631, 10.1591],
                zoom: 13,
                provider: window.MAP_CONFIG ? window.MAP_CONFIG.provider : 'leaflet',
                apiKey: window.MAP_CONFIG ? window.MAP_CONFIG.apiKey : ''
            });

            map = await mapProviderInstance.init();

            // Add draggable marker
            marker = mapProviderInstance.addMarker('delivery', 5.9631, 10.1591, {
                draggable: true,
                icon: 'delivery-location',
                popup: '<b>Delivery Location</b><br>Drag me to your exact location!',
                onDragEnd: (lat, lng) => {
                    setLocation(lat, lng);
                }
            });

            // Map click
            mapProviderInstance.onMapClick((lat, lng) => {
                setLocation(lat, lng);
            });

            // Invalidate size after a short delay to ensure proper rendering
            setTimeout(() => {
                if (mapProviderInstance.provider === 'leaflet') {
                     map.invalidateSize();
                }
            }, 250);
        }

        // Initialize Map for Text Address Section
        async function initTextAddressMap() {
            if (textAddressMap) {
                console.log('Text address map already initialized, skipping...');
                return;
            }

            const container = document.getElementById('text-address-map');
            if (!container) {
                console.warn('Text address map container not found');
                return;
            }

            textMapProviderInstance = new MapProvider({
                container: container,
                center: [5.9631, 10.1591],
                zoom: 13,
                provider: window.MAP_CONFIG ? window.MAP_CONFIG.provider : 'leaflet',
                apiKey: window.MAP_CONFIG ? window.MAP_CONFIG.apiKey : ''
            });

            textAddressMap = await textMapProviderInstance.init();

            textAddressMarker = textMapProviderInstance.addMarker('text-delivery', 5.9631, 10.1591, {
                draggable: true,
                icon: 'delivery-location',
                popup: '<b>Delivery Location</b><br>Drag me to your exact location!',
                onDragEnd: (lat, lng) => {
                    setTextAddressLocation(lat, lng);
                }
            });

            textMapProviderInstance.onMapClick((lat, lng) => {
                setTextAddressLocation(lat, lng);
            });
            
            // Invalidate size after a short delay
            setTimeout(() => {
                if (textMapProviderInstance.provider === 'leaflet') {
                     textAddressMap.invalidateSize();
                }
            }, 300);
        }
        
        // Function to set location from text address map
        function setTextAddressLocation(lat, lng) {
            // Update hidden fields
            document.getElementById('text-latitude').value = lat;
            document.getElementById('text-longitude').value = lng;
            
            // Update marker position
            if (textMapProviderInstance) {
                textMapProviderInstance.updateMarker('text-delivery', lat, lng);
                textMapProviderInstance.setCenter(lat, lng, 16);
            }
            
            // Reverse geocode to get address
            fetch(`<?= url('/api/geocoding/reverse.php') ?>?lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        const addressParts = data.display_name.split(',');
                        if (addressParts.length > 0 && !document.getElementById('street-address').value) {
                            document.getElementById('street-address').value = addressParts[0].trim();
                        }
                    }
                })
                .catch(error => {
                    console.error('Reverse geocoding error:', error);
                });
        }

        // Select location type
        function selectLocationType(type) {
            currentLocationType = type;
            
            // Update buttons
            const btnSaved = document.getElementById('btn-saved');
            const btnGps = document.getElementById('btn-gps');
            const btnText = document.getElementById('btn-text');
            
            if (btnSaved) {
                btnSaved.className = type === 'saved' ? 
                    'tw-p-3 md:tw-p-4 tw-border-2 tw-border-orange-500 tw-bg-orange-50 tw-rounded-lg tw-text-center tw-transition-all tw-min-h-[80px] tw-flex tw-flex-col tw-items-center tw-justify-center' : 
                    'tw-p-3 md:tw-p-4 tw-border-2 tw-border-gray-300 tw-bg-white tw-rounded-lg tw-text-center tw-transition-all hover:tw-border-gray-400 tw-min-h-[80px] tw-flex tw-flex-col tw-items-center tw-justify-center';
                
                const svg = btnSaved.querySelector('svg');
                const text = btnSaved.querySelector('.tw-font-semibold');
                if (svg) {
                    svg.classList.toggle('tw-text-orange-600', type === 'saved');
                    svg.classList.toggle('tw-text-gray-600', type !== 'saved');
                }
                if (text) {
                    text.classList.toggle('tw-text-orange-700', type === 'saved');
                    text.classList.toggle('tw-text-gray-700', type !== 'saved');
                }
            }
                
            if (btnGps) {
                btnGps.className = type === 'gps' ? 
                    'tw-p-3 md:tw-p-4 tw-border-2 tw-border-orange-500 tw-bg-orange-50 tw-rounded-lg tw-text-center tw-transition-all tw-min-h-[80px] tw-flex tw-flex-col tw-items-center tw-justify-center' : 
                    'tw-p-3 md:tw-p-4 tw-border-2 tw-border-gray-300 tw-bg-white tw-rounded-lg tw-text-center tw-transition-all hover:tw-border-gray-400 tw-min-h-[80px] tw-flex tw-flex-col tw-items-center tw-justify-center';
                
                const svg = btnGps.querySelector('svg');
                const text = btnGps.querySelector('.tw-font-semibold');
                if (svg) {
                    svg.classList.toggle('tw-text-orange-600', type === 'gps');
                    svg.classList.toggle('tw-text-gray-600', type !== 'gps');
                }
                if (text) {
                    text.classList.toggle('tw-text-orange-700', type === 'gps');
                    text.classList.toggle('tw-text-gray-700', type !== 'gps');
                }
            }
                
            if (btnText) {
                btnText.className = type === 'text' ? 
                    'tw-p-3 md:tw-p-4 tw-border-2 tw-border-orange-500 tw-bg-orange-50 tw-rounded-lg tw-text-center tw-transition-all tw-min-h-[80px] tw-flex tw-flex-col tw-items-center tw-justify-center' : 
                    'tw-p-3 md:tw-p-4 tw-border-2 tw-border-gray-300 tw-bg-white tw-rounded-lg tw-text-center tw-transition-all hover:tw-border-gray-400 tw-min-h-[80px] tw-flex tw-flex-col tw-items-center tw-justify-center';
                
                const svg = btnText.querySelector('svg');
                const text = btnText.querySelector('.tw-font-semibold');
                if (svg) {
                    svg.classList.toggle('tw-text-orange-600', type === 'text');
                    svg.classList.toggle('tw-text-gray-600', type !== 'text');
                }
                if (text) {
                    text.classList.toggle('tw-text-orange-700', type === 'text');
                    text.classList.toggle('tw-text-gray-700', type !== 'text');
                }
            }

            // Show/hide sections
            const savedSection = document.getElementById('saved-addresses-section');
            const gpsSection = document.getElementById('gps-section');
            const textSection = document.getElementById('text-section');
            
            if (savedSection) savedSection.classList.toggle('tw-hidden', type !== 'saved');
            if (gpsSection) gpsSection.classList.toggle('tw-hidden', type !== 'gps');
            if (textSection) textSection.classList.toggle('tw-hidden', type !== 'text');
            
            // Initialize components based on type
            if (type === 'gps') {
                setTimeout(initMap, 100);
            } else if (type === 'text') {
                setTimeout(initTextAddressMap, 100);
                if (typeof initGeocoding === 'function' && !geocodingInitialized) {
                    initGeocoding();
                }
            } else if (type === 'saved') {
                if (typeof loadSavedAddresses === 'function') {
                    loadSavedAddresses();
                }
            }
        }

        // Get current location with high precision using watchPosition
        let gpsWatchId = null;
        let bestAccuracy = Infinity;
        let accuracyCircle = null;

        function getCurrentLocation(event) {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser');
                return;
            }

            const e = event || window.event;
            const btn = e ? e.target.closest('button') : document.getElementById('btn-gps');
            const originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg class="tw-w-5 tw-h-5 tw-mr-2 tw-animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="tw-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="tw-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Getting precise location...';

            const accuracyThreshold = 20; // Stop when accuracy ≤ 20 meters
            const maxWaitTime = 15000; // Maximum 15 seconds
            const startTime = Date.now();
            bestAccuracy = Infinity;

            // Set maximum wait time timeout
            const timeoutId = setTimeout(() => {
                if (gpsWatchId !== null) {
                    navigator.geolocation.clearWatch(gpsWatchId);
                    gpsWatchId = null;
                }

                if (bestAccuracy === Infinity) {
                    alert('Unable to get your location. Please try again or select location on map.');
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                } else {
                    console.log(`GPS stopped after ${maxWaitTime}ms with accuracy: ${bestAccuracy.toFixed(1)}m`);
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                }
            }, maxWaitTime);

            // Use watchPosition to get progressively better accuracy
            gpsWatchId = navigator.geolocation.watchPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const accuracy = position.coords.accuracy;
                    const elapsed = Date.now() - startTime;

                    console.log(`[${elapsed}ms] GPS Accuracy: ${accuracy.toFixed(1)}m (lat: ${lat.toFixed(6)}, lng: ${lng.toFixed(6)})`);

                    // Update location if this reading is more accurate
                    if (accuracy < bestAccuracy) {
                        bestAccuracy = accuracy;
                        setLocation(lat, lng);

                        // Show accuracy circle on map
                        if (mapProviderInstance && mapProviderInstance.provider === 'leaflet' && map) {
                            if (accuracyCircle) {
                                map.removeLayer(accuracyCircle);
                            }

                            const circleColor = accuracy < 20 ? '#22c55e' : accuracy < 50 ? '#eab308' : '#ef4444';
                            accuracyCircle = L.circle([lat, lng], {
                                radius: accuracy,
                                color: circleColor,
                                fillColor: circleColor,
                                fillOpacity: 0.15,
                                weight: 2,
                                dashArray: '5, 5'
                            }).addTo(map);

                            accuracyCircle.bindPopup(`<strong>GPS Accuracy</strong><br>±${accuracy.toFixed(1)} meters`);
                        }

                        // Update button text with current accuracy
                        const accuracyText = accuracy < 10 ? 'Excellent' :
                                            accuracy < 30 ? 'Good' :
                                            accuracy < 100 ? 'Fair' : 'Poor';
                        btn.innerHTML = `<svg class="tw-w-5 tw-h-5 tw-mr-2 tw-animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="tw-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="tw-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>${accuracyText}: ±${accuracy.toFixed(0)}m`;
                    }

                    // Stop if we reached desired accuracy
                    if (accuracy <= accuracyThreshold) {
                        console.log(`✅ Reached target accuracy of ${accuracyThreshold}m in ${elapsed}ms`);
                        clearTimeout(timeoutId);
                        navigator.geolocation.clearWatch(gpsWatchId);
                        gpsWatchId = null;
                        btn.disabled = false;
                        btn.innerHTML = originalHTML;

                        // Show success message
                        alert(`Location found with ${accuracyText.toLowerCase()} accuracy (±${accuracy.toFixed(0)}m)`);
                    }
                },
                function(error) {
                    clearTimeout(timeoutId);
                    if (gpsWatchId !== null) {
                        navigator.geolocation.clearWatch(gpsWatchId);
                        gpsWatchId = null;
                    }

                    let errorMsg = 'Unable to get your location';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMsg = 'Location permission denied. Please enable location access in your browser settings.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMsg = 'Location information unavailable. Please check your device settings.';
                            break;
                        case error.TIMEOUT:
                            errorMsg = 'Location request timed out. Please try again.';
                            break;
                    }

                    alert(errorMsg);
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                },
                {
                    enableHighAccuracy: true,
                    timeout: 30000,  // Longer timeout for watchPosition
                    maximumAge: 0
                }
            );
        }

        // Set location on map
        function setLocation(lat, lng) {
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;

            // Update map
            if (mapProviderInstance) {
                mapProviderInstance.updateMarker('delivery', lat, lng);
                mapProviderInstance.setCenter(lat, lng, 16);
                
                if (mapProviderInstance.provider === 'leaflet' && marker) {
                     marker.bindPopup('<b>Delivery Location</b><br>Loading address...').openPopup();
                }
            }

            // Calculate and update delivery fee breakdown
            updateDeliveryFeeBreakdown(lat, lng);

            // Show location info
            const locationInfo = document.getElementById('location-info');
            const locationCoords = document.getElementById('location-coords');
            locationInfo.classList.remove('tw-hidden');

            // Store coordinates in a data attribute for reference
            locationCoords.setAttribute('data-lat', lat);
            locationCoords.setAttribute('data-lng', lng);

            // Show coordinates initially
            locationCoords.innerHTML = `<strong>GPS:</strong> ${lat.toFixed(6)}, ${lng.toFixed(6)}`;

            // Reverse geocode to get address (Leaflet only or if we want to use our proxy for Google too)
            if (mapProviderInstance && mapProviderInstance.provider !== 'google') {
                // Use our proxy for reverse geocoding
                fetch(`<?= url('/api/geocoding/reverse.php') ?>?lat=${lat}&lon=${lng}&zoom=16`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Reverse geocoding failed (${response.status})`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.display_name) {
                            const address = data.display_name;
                            // Show both address and coordinates
                            locationCoords.innerHTML = `
                                <div class="tw-text-xs">
                                    <div class="tw-font-medium tw-text-green-800">${address}</div>
                                    <div class="tw-text-green-600 tw-mt-1">GPS: ${lat.toFixed(6)}, ${lng.toFixed(6)}</div>
                                </div>
                            `;

                            // Update marker popup with address
                            if (mapProviderInstance.provider === 'leaflet' && marker) {
                                marker.bindPopup(`<b>Delivery Location</b><br>${address}<br><small>GPS: ${lat.toFixed(6)}, ${lng.toFixed(6)}</small>`).openPopup();
                            }
                        } else if (data && data.error) {
                            console.warn('Reverse geocoding warning:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Reverse geocoding error:', error);
                        // Keep the coordinates display as fallback
                    });
            }
        }

        // Update delivery fee breakdown based on location
        async function updateDeliveryFeeBreakdown(lat, lng) {
            // Get restaurant IDs from cart
            const cartItems = <?= json_encode($cartItems ?? []) ?>;
            if (!cartItems || cartItems.length === 0) {
                return;
            }

            // Get unique restaurant IDs
            const restaurantIds = [...new Set(cartItems.map(item => item.restaurant_id))];

            // For now, calculate for the first restaurant (multi-restaurant orders handled separately)
            const restaurantId = restaurantIds[0];
            const subtotal = <?= $cartTotals['subtotal'] ?? 0 ?>;

            console.log('=== DELIVERY FEE CALCULATION ===');
            console.log('Customer GPS Coordinates:', { latitude: lat, longitude: lng });
            console.log('Restaurant ID:', restaurantId);
            console.log('Subtotal:', subtotal);

            try {
                const response = await fetch('<?= url('/api/delivery-fee-estimate') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        restaurant_id: restaurantId,
                        latitude: lat,
                        longitude: lng,
                        subtotal: subtotal
                    })
                });

                const data = await response.json();
                console.log('Delivery fee response:', data);

                if (data.success && data.available) {
                    const breakdown = data.breakdown;

                    // Update delivery fee amount
                    const feeAmount = breakdown.total_fee.toLocaleString();
                    document.getElementById('delivery-fee-amount').textContent = feeAmount + ' FCFA';
                    if (document.getElementById('delivery-fee-amount-desktop')) {
                        document.getElementById('delivery-fee-amount-desktop').textContent = feeAmount + ' FCFA';
                    }
                    if (document.getElementById('delivery-fee-amount-modal')) {
                        document.getElementById('delivery-fee-amount-modal').textContent = feeAmount + ' FCFA';
                    }

                    // Create breakdown message
                    let distanceInfo = `📍 Distance: ${breakdown.distance} km`;
                    let zoneInfo = '';

                    if (breakdown.is_free_delivery) {
                        zoneInfo = `🎉 ${breakdown.free_delivery_reason}`;
                    } else if (breakdown.within_free_zone) {
                        zoneInfo = `✅ Within ${breakdown.free_zone_radius} km zone - base fee only`;
                    } else {
                        zoneInfo = `📊 ${breakdown.base_fee.toLocaleString()} FCFA base + ${breakdown.extra_fee.toLocaleString()} FCFA (${breakdown.extra_distance.toFixed(1)} km × ${breakdown.extra_fee_per_km} FCFA/km)`;
                    }

                    // Update all breakdown displays
                    const updateBreakdownDisplay = (distanceId, zoneId, containerId) => {
                        const distanceEl = document.getElementById(distanceId);
                        const zoneEl = document.getElementById(zoneId);
                        const containerEl = document.getElementById(containerId);

                        if (distanceEl && zoneEl && containerEl) {
                            distanceEl.textContent = distanceInfo;
                            zoneEl.textContent = zoneInfo;
                            containerEl.classList.remove('tw-hidden');
                        }
                    };

                    updateBreakdownDisplay('delivery-distance-info', 'delivery-zone-info', 'delivery-fee-breakdown');
                    updateBreakdownDisplay('delivery-distance-info-desktop', 'delivery-zone-info-desktop', 'delivery-fee-breakdown-desktop');
                    updateBreakdownDisplay('delivery-distance-info-modal', 'delivery-zone-info-modal', 'delivery-fee-breakdown-modal');

                } else if (!data.available) {
                    // Show delivery not available warning
                    showNotification(`⚠️ Delivery not available: ${data.reason}. Maximum distance is ${data.max_distance} km.`, 'warning');
                }

            } catch (error) {
                console.error('Error calculating delivery fee:', error);
                // Silently fail - user can still proceed with default fee
            }
        }

        // Toggle mobile summary
        function toggleMobileSummary() {
            const details = document.getElementById('mobile-summary-details');
            const toggleText = document.getElementById('summary-toggle-text');
            const toggleIcon = document.getElementById('summary-toggle-icon');

            if (details.classList.contains('tw-hidden')) {
                details.classList.remove('tw-hidden');
                toggleText.textContent = 'Hide Details';
                toggleIcon.style.transform = 'rotate(180deg)';
            } else {
                details.classList.add('tw-hidden');
                toggleText.textContent = 'View Details';
                toggleIcon.style.transform = 'rotate(0deg)';
            }
        }

        // Handle form submission
        document.getElementById('checkout-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const btn = document.getElementById('place-order-btn');
            const btnMobile = document.getElementById('place-order-btn-mobile');
            btn.disabled = true;
            btnMobile.disabled = true;

            const loadingHTML = '<svg class="tw-w-5 tw-h-5 tw-mr-2 tw-animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="tw-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="tw-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Placing Order...';
            btn.innerHTML = loadingHTML;
            btnMobile.innerHTML = loadingHTML;

            // Prepare form data
            const formData = new FormData(this);
            formData.append('location_type', currentLocationType);

            // Validate location
            if (currentLocationType === 'gps') {
                const lat = document.getElementById('latitude').value;
                const lng = document.getElementById('longitude').value;

                console.log('GPS Location validation - Lat:', lat, 'Lng:', lng);

                if (!lat || !lng || lat === '' || lng === '') {
                    alert('Please select your delivery location on the map or use "Get My Current Location"');
                    btn.disabled = false;
                    btnMobile.disabled = false;
                    const originalHTML = '<svg class="tw-w-5 tw-h-5 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Place Order';
                    btn.innerHTML = originalHTML;
                    btnMobile.innerHTML = originalHTML;
                    return;
                }

                // Ensure latitude and longitude are in the FormData
                // Remove any existing values first
                formData.delete('latitude');
                formData.delete('longitude');
                // Add fresh values
                formData.append('latitude', lat);
                formData.append('longitude', lng);

                console.log('Added GPS coordinates to FormData - Lat:', lat, 'Lng:', lng);
            } else if (currentLocationType === 'text') {
                const streetAddress = document.getElementById('street-address').value;

                console.log('Text Address validation - Street:', streetAddress);

                if (!streetAddress || streetAddress.trim() === '') {
                    alert('Please enter your street address');
                    btn.disabled = false;
                    btnMobile.disabled = false;
                    const originalHTML = '<svg class="tw-w-5 tw-h-5 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Place Order';
                    btn.innerHTML = originalHTML;
                    btnMobile.innerHTML = originalHTML;
                    return;
                }

                // Ensure text address fields are in the FormData
                formData.delete('street_address');
                formData.delete('neighborhood');
                formData.delete('landmark');
                formData.append('street_address', streetAddress);
                formData.append('neighborhood', document.getElementById('neighborhood')?.value || '');
                formData.append('landmark', document.getElementById('landmark')?.value || '');

                console.log('Added text address to FormData');
            } else {
                // Fallback for saved addresses
                console.log('Using saved address or other location type:', currentLocationType);
            }

            try {
                // Generate environment-aware URL for checkout
                const checkoutUrl = '<?= url('/checkout/place-order') ?>';
                console.log('Submitting order to:', checkoutUrl);
                console.log('Form data entries:', Array.from(formData.entries()));

                const response = await fetch(checkoutUrl, {
                    method: 'POST',
                    body: formData
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', Object.fromEntries(response.headers.entries()));

                // Check if response is JSON (both success and error responses should be JSON)
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response received:', text);
                    throw new Error('Server returned non-JSON response. Please contact support.');
                }

                // Parse JSON response (works for both success and error responses)
                const data = await response.json();
                console.log('Response data:', data);

                // Check if response is not ok (400, 403, 500, etc.)
                if (!response.ok) {
                    console.error('Server returned error:', data);
                    // The data object contains the error details, so we'll handle it below
                    // Don't throw here - let the success check handle it
                }

                if (data.success) {
                    // Get the first order ID for confirmation page
                    const orderId = data.order_ids && data.order_ids.length > 0 ? data.order_ids[0] : null;

                    // Check if there's a warning (Tranzak payment failed but order created)
                    if (data.warning) {
                        console.warn('Order placed with warning:', data.warning);
                        showNotification(data.warning, 'warning');
                        // Redirect to confirmation page after 2 seconds
                        setTimeout(() => {
                            if (orderId) {
                                window.location.href = '<?= url('/customer/orders/') ?>' + orderId + '/confirmation';
                            } else {
                                window.location.href = '<?= url('/customer/orders') ?>';
                            }
                        }, 2000);
                    }
                    // Check if Tranzak payment URL is available
                    else if (data.payment_url) {
                        console.log('Redirecting to Tranzak payment URL:', data.payment_url);
                        // Redirect to Tranzak payment page
                        window.location.href = data.payment_url;
                    } else {
                        console.log('Redirecting to order confirmation page');
                        // Redirect to order confirmation page
                        if (orderId) {
                            window.location.href = '<?= url('/customer/orders/') ?>' + orderId + '/confirmation';
                        } else {
                            window.location.href = '<?= url('/customer/orders') ?>';
                        }
                    }
                } else {
                    console.error('Order placement failed:', data);
                    showDetailedError(data);
                    btn.disabled = false;
                    btnMobile.disabled = false;
                    const originalHTML = '<svg class="tw-w-5 tw-h-5 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Place Order';
                    btn.innerHTML = originalHTML;
                    btnMobile.innerHTML = originalHTML;
                }
            } catch (error) {
                console.error('Order placement error:', error);
                
                // Create error object for detailed display
                const errorData = {
                    success: false,
                    message: error.message,
                    error_type: 'network_error',
                    details: {
                        type: error.name || 'Unknown Error',
                        message: error.message,
                        stack: error.stack
                    }
                };
                
                showDetailedError(errorData);
                btn.disabled = false;
                btnMobile.disabled = false;
                const originalHTML = '<svg class="tw-w-5 tw-h-5 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Place Order';
                btn.innerHTML = originalHTML;
                btnMobile.innerHTML = originalHTML;
            }
        });

        // ============================================================================
        // DETAILED ERROR DISPLAY SYSTEM
        // ============================================================================
        
        // Show detailed error information to help users fix issues
        function showDetailedError(errorData) {
            const errorType = errorData.error_type || 'unknown_error';
            const message = errorData.message || 'An unknown error occurred';
            const errors = errorData.errors || {};
            const details = errorData.details || {};
            
            // Create error categories and solutions
            const errorCategories = {
                'validation_error': {
                    title: 'Validation Error',
                    icon: '⚠️',
                    color: 'tw-bg-yellow-50 tw-border-yellow-200 tw-text-yellow-800',
                    description: 'Please check the information you entered and try again.'
                },
                'delivery_error': {
                    title: 'Delivery Not Available',
                    icon: '🚫',
                    color: 'tw-bg-red-50 tw-border-red-200 tw-text-red-800',
                    description: 'The selected delivery location is outside the restaurant\'s delivery zone. Please choose a different location or restaurant.'
                },
                'payment_error': {
                    title: 'Payment Error',
                    icon: '💳',
                    color: 'tw-bg-red-50 tw-border-red-200 tw-text-red-800',
                    description: 'There was an issue processing your payment. Please try again or use a different payment method.'
                },
                'network_error': {
                    title: 'Connection Error',
                    icon: '🌐',
                    color: 'tw-bg-blue-50 tw-border-blue-200 tw-text-blue-800',
                    description: 'Please check your internet connection and try again.'
                },
                'server_error': {
                    title: 'Server Error',
                    icon: '🔧',
                    color: 'tw-bg-gray-50 tw-border-gray-200 tw-text-gray-800',
                    description: 'Our servers are experiencing issues. Please try again in a few minutes.'
                },
                'cart_error': {
                    title: 'Cart Error',
                    icon: '🛒',
                    color: 'tw-bg-orange-50 tw-border-orange-200 tw-text-orange-800',
                    description: 'There was an issue with your cart. Please refresh the page and try again.'
                },
                'location_error': {
                    title: 'Location Error',
                    icon: '📍',
                    color: 'tw-bg-purple-50 tw-border-purple-200 tw-text-purple-800',
                    description: 'Please make sure you have selected a valid delivery location.'
                },
                'unknown_error': {
                    title: 'Unexpected Error',
                    icon: '❌',
                    color: 'tw-bg-gray-50 tw-border-gray-200 tw-text-gray-800',
                    description: 'An unexpected error occurred. Please try again or contact support.'
                }
            };
            
            const category = errorCategories[errorType] || errorCategories['unknown_error'];
            
            // Create detailed error modal
            const errorModal = document.createElement('div');
            errorModal.className = 'tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-z-50 tw-p-4';
            errorModal.innerHTML = `
                <div class="tw-bg-white tw-rounded-lg tw-shadow-xl tw-max-w-md tw-w-full tw-max-h-96 tw-overflow-y-auto">
                    <div class="tw-p-6">
                        <!-- Header -->
                        <div class="tw-flex tw-items-center tw-gap-3 tw-mb-4">
                            <div class="tw-text-2xl">${category.icon}</div>
                            <div>
                                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">${category.title}</h3>
                                <p class="tw-text-sm tw-text-gray-600">${category.description}</p>
                            </div>
                        </div>
                        
                        <!-- Main Error Message -->
                        <div class="tw-mb-4">
                            <div class="tw-p-3 tw-rounded-md ${category.color}">
                                <p class="tw-font-medium">${message}</p>
                            </div>
                        </div>
                        
                        <!-- Specific Field Errors -->
                        ${Object.keys(errors).length > 0 ? `
                            <div class="tw-mb-4">
                                <h4 class="tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">Please fix the following:</h4>
                                <ul class="tw-space-y-1">
                                    ${Object.entries(errors).map(([field, errorMsg]) => `
                                        <li class="tw-text-sm tw-text-red-600 tw-flex tw-items-start tw-gap-2">
                                            <span class="tw-text-red-500 tw-mt-0.5">•</span>
                                            <span><strong>${field}:</strong> ${errorMsg}</span>
                                        </li>
                                    `).join('')}
                                </ul>
                            </div>
                        ` : ''}
                        
                        <!-- Technical Details (Collapsible) -->
                        ${details.type || details.message || details.stack ? `
                            <div class="tw-mb-4">
                                <button onclick="toggleErrorDetails(this)" class="tw-text-sm tw-text-gray-600 hover:tw-text-gray-800 tw-flex tw-items-center tw-gap-1">
                                    <svg class="tw-w-4 tw-h-4 tw-transform tw-transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                    Technical Details
                                </button>
                                <div class="tw-hidden tw-mt-2 tw-p-3 tw-bg-gray-100 tw-rounded tw-text-xs tw-font-mono tw-overflow-x-auto">
                                    ${details.type ? `<div><strong>Type:</strong> ${details.type}</div>` : ''}
                                    ${details.message ? `<div><strong>Message:</strong> ${details.message}</div>` : ''}
                                    ${details.stack ? `<div><strong>Stack:</strong><br><pre class="tw-whitespace-pre-wrap">${details.stack}</pre></div>` : ''}
                                </div>
                            </div>
                        ` : ''}
                        
                        <!-- Action Buttons -->
                        <div class="tw-flex tw-gap-3 tw-justify-end">
                            <button onclick="closeErrorModal()" class="tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 tw-bg-gray-100 tw-rounded-md hover:tw-bg-gray-200 tw-transition-colors">
                                Close
                            </button>
                            <button onclick="retryOrder()" class="tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-white tw-bg-orange-600 tw-rounded-md hover:tw-bg-orange-700 tw-transition-colors">
                                Try Again
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            // Add to page
            document.body.appendChild(errorModal);
            
            // Store reference for closing
            window.currentErrorModal = errorModal;
        }
        
        // Toggle error details visibility
        function toggleErrorDetails(button) {
            const details = button.nextElementSibling;
            const icon = button.querySelector('svg');
            
            if (details.classList.contains('tw-hidden')) {
                details.classList.remove('tw-hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                details.classList.add('tw-hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }
        
        // Close error modal
        function closeErrorModal() {
            if (window.currentErrorModal) {
                window.currentErrorModal.remove();
                window.currentErrorModal = null;
            }
        }
        
        // Retry order placement
        function retryOrder() {
            closeErrorModal();
            // Trigger the place order function again
            const placeOrderBtn = document.getElementById('place-order-btn');
            if (placeOrderBtn) {
                placeOrderBtn.click();
            }
        }
        
        // Make functions globally available
        window.showDetailedError = showDetailedError;
        window.toggleErrorDetails = toggleErrorDetails;
        window.closeErrorModal = closeErrorModal;
        window.retryOrder = retryOrder;

        // ============================================================================
        // NOTIFICATION SYSTEM
        // ============================================================================
        
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `tw-fixed tw-top-4 tw-right-4 tw-px-6 tw-py-3 tw-rounded-lg tw-shadow-lg tw-z-50 tw-transition-all tw-duration-300 ${
                type === 'success' ? 'tw-bg-green-500 tw-text-white' :
                type === 'error' ? 'tw-bg-red-500 tw-text-white' :
                type === 'warning' ? 'tw-bg-yellow-500 tw-text-black' :
                'tw-bg-blue-500 tw-text-white'
            }`;
            notification.textContent = message;
            
            // Add to page
            document.body.appendChild(notification);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }

        // ============================================================================
        // GEOCODING FUNCTIONALITY
        // ============================================================================
        
        let geocodingTimeout;
        let currentGeocodingRequest;
        let geocodingInitialized = false;
        
        // Store handlers for proper cleanup
        const geocodingHandlers = {
            input: null,
            blur: null,
            focus: null
        };
        
        // Initialize geocoding when text section is shown
        function initGeocoding() {
            const addressSearch = document.getElementById('address-search');
            const suggestions = document.getElementById('address-suggestions');
            
            if (!addressSearch) {
                console.warn('Address search input not found');
                return;
            }
            
            // Remove old listeners if already initialized
            if (geocodingInitialized && geocodingHandlers.input) {
                addressSearch.removeEventListener('input', geocodingHandlers.input);
                addressSearch.removeEventListener('blur', geocodingHandlers.blur);
                addressSearch.removeEventListener('focus', geocodingHandlers.focus);
            }
            
            // Create new handlers
            geocodingHandlers.input = handleAddressSearch;
            geocodingHandlers.blur = () => {
                setTimeout(() => {
                    if (suggestions) {
                        suggestions.classList.add('tw-hidden');
                    }
                }, 200);
            };
            geocodingHandlers.focus = () => {
                if (suggestions && suggestions.children.length > 0) {
                    suggestions.classList.remove('tw-hidden');
                }
            };
            
            // Add event listeners
            addressSearch.addEventListener('input', geocodingHandlers.input);
            addressSearch.addEventListener('blur', geocodingHandlers.blur);
            addressSearch.addEventListener('focus', geocodingHandlers.focus);
            
            geocodingInitialized = true;
            console.log('Geocoding initialized');
        }
        
        // Handle address search input
        function handleAddressSearch(event) {
            const query = event.target.value.trim();
            const suggestions = document.getElementById('address-suggestions');
            
            // Clear previous timeout
            if (geocodingTimeout) {
                clearTimeout(geocodingTimeout);
            }
            
            // Clear previous request
            if (currentGeocodingRequest) {
                currentGeocodingRequest.abort();
            }
            
            if (query.length < 3) {
                suggestions.classList.add('tw-hidden');
                return;
            }
            
            // Debounce the search
            geocodingTimeout = setTimeout(() => {
                searchAddresses(query);
            }, 300);
        }
        
        // Search for addresses using Nominatim (OpenStreetMap)
        async function searchAddresses(query) {
            const suggestions = document.getElementById('address-suggestions');
            
            try {
                // Show loading state
                suggestions.innerHTML = `
                    <div class="tw-p-4 tw-text-center tw-text-gray-500">
                        <div class="tw-flex tw-items-center tw-justify-center tw-gap-2">
                            <svg class="tw-w-4 tw-h-4 tw-animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span class="tw-text-sm tw-font-medium">Searching locations...</span>
                        </div>
                    </div>
                `;
                suggestions.classList.remove('tw-hidden');
                
                // Create abort controller for this request
                currentGeocodingRequest = new AbortController();
                
                // Search with focus on Cameroon, Bamenda area using our proxy
                const searchQuery = encodeURIComponent(query + ', Bamenda, Cameroon');
                const apiUrl = `<?= url('/api/geocoding/search.php') ?>?q=${searchQuery}&limit=5&countrycodes=cm`;
                
                console.log('Searching addresses:', query, 'URL:', apiUrl);
                
                const response = await fetch(apiUrl, {
                    signal: currentGeocodingRequest.signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Geocoding API error:', response.status, errorText);
                    throw new Error(`Geocoding service unavailable (${response.status})`);
                }
                
                const results = await response.json();
                
                if (!results) {
                    throw new Error('Invalid response from geocoding service');
                }
                
                if (results.error) {
                    console.error('Geocoding service error:', results.error);
                    throw new Error(results.message || results.error || 'Geocoding service error');
                }
                
                // Handle case where results is an array directly
                const addressResults = Array.isArray(results) ? results : (results.results || []);
                
                console.log('Geocoding results:', addressResults.length, 'locations found');
                
                if (addressResults.length === 0) {
                    suggestions.innerHTML = `
                        <div class="tw-p-4 tw-text-center tw-text-gray-500">
                            <svg class="tw-w-6 tw-h-6 tw-mx-auto tw-mb-2 tw-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <p class="tw-text-sm tw-font-medium tw-mb-1">No locations found</p>
                            <p class="tw-text-xs tw-text-gray-400">Try a different search term or enter address manually</p>
                        </div>
                    `;
                } else {
                    suggestions.innerHTML = addressResults.map((result, index) => {
                        // Escape for JavaScript string (escape quotes and backslashes)
                        const displayName = String(result.display_name || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"');
                        const lat = parseFloat(result.lat || result.latitude || 0);
                        const lon = parseFloat(result.lon || result.longitude || 0);
                        const safeDisplayName = String(result.display_name || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                        
                        return `
                        <div class="tw-p-3 tw-border-b tw-border-gray-100 tw-cursor-pointer hover:tw-bg-gray-50 tw-transition-colors" onclick="selectGeocodedAddress(${lat}, ${lon}, '${displayName}')">
                            <div class="tw-flex tw-items-start">
                                <svg class="tw-w-4 tw-h-4 tw-mr-2 tw-mt-0.5 tw-text-gray-400 tw-flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                </svg>
                                <div class="tw-flex-1 tw-min-w-0">
                                    <div class="tw-text-sm tw-font-medium tw-text-gray-900 tw-truncate">${safeDisplayName}</div>
                                    <div class="tw-text-xs tw-text-gray-500 tw-mt-1">${lat}, ${lon}</div>
                                </div>
                            </div>
                        </div>
                    `;
                    }).join('');
                }
                
            } catch (error) {
                if (error.name === 'AbortError') {
                    return; // Request was cancelled
                }
                
                console.error('Geocoding error:', error);
                suggestions.innerHTML = `
                    <div class="tw-p-4 tw-text-center tw-text-red-500">
                        <svg class="tw-w-6 tw-h-6 tw-mx-auto tw-mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="tw-text-sm tw-font-medium tw-mb-1">Search unavailable</p>
                        <p class="tw-text-xs tw-text-red-400">Please enter your address manually below</p>
                    </div>
                `;
            }
        }
        
        // Select a geocoded address
        function selectGeocodedAddress(lat, lon, displayName) {
            const addressSearch = document.getElementById('address-search');
            const streetAddress = document.getElementById('street-address');
            const textLatitude = document.getElementById('text-latitude');
            const textLongitude = document.getElementById('text-longitude');
            const suggestions = document.getElementById('address-suggestions');
            
            // Set the search input
            addressSearch.value = displayName;
            
            // Set coordinates
            textLatitude.value = lat;
            textLongitude.value = lon;
            
            // Parse the display name to extract street address
            const addressParts = displayName.split(',');
            if (addressParts.length > 0) {
                streetAddress.value = addressParts[0].trim();
            }
            
            // Hide suggestions
            suggestions.classList.add('tw-hidden');
            
            // Update text address map if it exists
            if (textAddressMap && textAddressMarker) {
                if (mapProvider === 'google') {
                    // Google Maps
                    textAddressMarker.setPosition({ lat: lat, lng: lon });
                    textAddressMap.setCenter({ lat: lat, lng: lon });
                    textAddressMap.setZoom(16);
                } else {
                    // Leaflet
                    textAddressMarker.setLatLng([lat, lon]);
                    textAddressMap.setView([lat, lon], 16);
                }
            }
            
            // Show success notification
            showNotification('Location selected successfully!', 'success');
        }

        // ============================================================================
        // SAVED ADDRESSES FUNCTIONALITY
        // ============================================================================

        // Load saved addresses
        async function loadSavedAddresses() {
            try {
                const response = await fetch('<?= url('/api/addresses') ?>', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();
                const addressesList = document.getElementById('saved-addresses-list');

                if (data.success && data.addresses.length > 0) {
                    addressesList.innerHTML = data.addresses.map(address => `
                        <div class="tw-p-3 tw-bg-white tw-border tw-border-gray-200 tw-rounded-lg tw-cursor-pointer hover:tw-border-orange-300 hover:tw-bg-orange-50 tw-transition-all" onclick="selectSavedAddress(${address.id})">
                            <div class="tw-flex tw-items-start tw-justify-between">
                                <div class="tw-flex-1">
                                    <div class="tw-flex tw-items-center tw-gap-2 tw-mb-1">
                                        <h4 class="tw-font-semibold tw-text-gray-900">${address.label}</h4>
                                        ${address.is_default ? '<span class="tw-px-2 tw-py-0.5 tw-bg-orange-100 tw-text-orange-800 tw-text-xs tw-font-medium tw-rounded">Default</span>' : ''}
                                    </div>
                                    <p class="tw-text-sm tw-text-gray-700">${address.address_line_1}</p>
                                    <p class="tw-text-xs tw-text-gray-500">${address.city}${address.state ? ', ' + address.state : ''}</p>
                                </div>
                                <div class="tw-flex tw-items-center tw-gap-1">
                                    <button onclick="event.stopPropagation(); editAddress(${address.id})" class="tw-p-1 tw-text-gray-400 hover:tw-text-orange-600" title="Edit">
                                        <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="event.stopPropagation(); deleteAddress(${address.id})" class="tw-p-1 tw-text-gray-400 hover:tw-text-red-600" title="Delete">
                                        <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    addressesList.innerHTML = `
                        <div class="tw-text-center tw-py-4 tw-text-gray-500">
                            <svg class="tw-w-8 tw-h-8 tw-mx-auto tw-mb-2 tw-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <p class="tw-text-sm">No saved addresses found</p>
                            <p class="tw-text-xs tw-text-gray-400">Add an address below to save it for future use</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading saved addresses:', error);
                document.getElementById('saved-addresses-list').innerHTML = `
                    <div class="tw-text-center tw-py-4 tw-text-red-500">
                        <p class="tw-text-sm">Error loading addresses</p>
                    </div>
                `;
            }
        }

        // Select a saved address
        function selectSavedAddress(addressId) {
            // Load the address details and populate the form
            fetch(`<?= url('/api/addresses') ?>/${addressId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.address) {
                    const address = data.address;
                    
                    // Set the coordinates
                    document.getElementById('latitude').value = address.latitude || '';
                    document.getElementById('longitude').value = address.longitude || '';
                    
                    // Update the coordinates display
                    if (address.latitude && address.longitude) {
                        document.getElementById('location-coords').textContent = 
                            `${address.latitude}, ${address.longitude}`;
                    }
                    
                    // Set the address details
                    document.getElementById('street-address').value = address.address_line_1 || '';
                    document.getElementById('neighborhood').value = address.address_line_2 || '';
                    document.getElementById('landmark').value = address.delivery_instructions || '';
                    
                    // Show success message
                    showNotification('Address selected successfully!', 'success');
                    
                    // Optionally switch to GPS mode to show the location on map
                    if (address.latitude && address.longitude) {
                        selectLocationType('gps');
                        // Center map on the selected address
                        if (map) {
                            map.setView([address.latitude, address.longitude], 16);
                            // Add a marker for the selected address
                            if (marker) {
                                map.removeLayer(marker);
                            }
                            marker = L.marker([address.latitude, address.longitude], {
                                icon: customIcon
                            }).addTo(map);
                        }
                    }
                } else {
                    showNotification('Error loading address details', 'error');
                }
            })
            .catch(error => {
                console.error('Error selecting address:', error);
                showNotification('Error selecting address', 'error');
            });
        }

        // Show save address modal
        function showSaveAddressModal() {
            // Pre-fill form with current location data
            if (currentLocationType === 'gps') {
                const lat = document.getElementById('latitude').value;
                const lng = document.getElementById('longitude').value;
                const coords = document.getElementById('location-coords').textContent;
                
                if (lat && lng) {
                    document.getElementById('address-latitude').value = lat;
                    document.getElementById('address-longitude').value = lng;
                    document.getElementById('address-line-1').value = coords || 'GPS Location';
                    document.getElementById('address-city').value = 'Bamenda'; // Default city
                }
            } else if (currentLocationType === 'text') {
                const streetAddress = document.getElementById('street-address').value;
                const neighborhood = document.getElementById('neighborhood').value;
                const landmark = document.getElementById('landmark').value;
                const textLat = document.getElementById('text-latitude').value;
                const textLng = document.getElementById('text-longitude').value;
                const addressSearch = document.getElementById('address-search').value;
                
                // Use geocoded coordinates if available, otherwise use text input
                if (textLat && textLng) {
                    document.getElementById('address-latitude').value = textLat;
                    document.getElementById('address-longitude').value = textLng;
                }
                
                // Build address line
                let addressLine = '';
                if (addressSearch) {
                    addressLine = addressSearch;
                } else if (streetAddress) {
                    addressLine = streetAddress;
                    if (neighborhood) {
                        addressLine += ', ' + neighborhood;
                    }
                    if (landmark) {
                        addressLine += ' (Near ' + landmark + ')';
                    }
                }
                
                if (addressLine) {
                    document.getElementById('address-line-1').value = addressLine;
                    document.getElementById('address-city').value = 'Bamenda'; // Default city
                }
            }
            
            document.getElementById('save-address-modal').classList.remove('tw-hidden');
        }

        // Close save address modal
        function closeSaveAddressModal() {
            document.getElementById('save-address-modal').classList.add('tw-hidden');
            document.getElementById('save-address-form').reset();
        }

        // Save address form submission
        document.getElementById('save-address-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const addressData = {
                label: formData.get('label'),
                address_line_1: formData.get('address_line_1'),
                city: formData.get('city'),
                state: formData.get('state'),
                postal_code: formData.get('postal_code'),
                delivery_instructions: formData.get('delivery_instructions'),
                latitude: formData.get('latitude'),
                longitude: formData.get('longitude'),
                is_default: formData.get('is_default') === 'on'
            };

            try {
                const response = await fetch('<?= url('/api/addresses') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(addressData)
                });

                const data = await response.json();
                
               if (data.success) {
                   showNotification('Address saved successfully!', 'success');
                   closeSaveAddressModal();
                   loadSavedAddresses(); // Refresh the list
               } else {
                   showNotification('Error saving address: ' + (data.message || 'Unknown error'), 'error');
               }
            } catch (error) {
                console.error('Error saving address:', error);
                showNotification('Error saving address. Please try again.', 'error');
            }
        });

        // Edit address
        function editAddress(addressId) {
            // This will be implemented to edit an existing address
            console.log('Edit address:', addressId);
            alert('Edit functionality will be implemented');
        }

        // Delete address
        async function deleteAddress(addressId) {
            if (!confirm('Are you sure you want to delete this address?')) {
                return;
            }

            try {
                const response = await fetch(`<?= url('/api/addresses') ?>/${addressId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();
                
               if (data.success) {
                   showNotification('Address deleted successfully!', 'success');
                   loadSavedAddresses(); // Refresh the list
               } else {
                   showNotification('Error deleting address: ' + (data.message || 'Unknown error'), 'error');
               }
            } catch (error) {
                console.error('Error deleting address:', error);
                showNotification('Error deleting address. Please try again.', 'error');
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Checkout page loaded - initializing...');
            selectLocationType('gps');

            // Initialize map if Google Maps API is loaded
            if (mapProvider === 'google' && typeof google === 'undefined') {
                // Wait for Google Maps API to load
                window.initMap = initMap;
            } else if (mapProvider !== 'google') {
                // Initialize Leaflet immediately if map doesn't exist
                if (!map) {
                    setTimeout(initMap, 100);
                } else {
                    console.log('Map already initialized, skipping...');
                }
            }

            // Try to get user's current location automatically on page load
            // This makes the checkout process smoother
            setTimeout(function() {
                const latField = document.getElementById('latitude');
                const lngField = document.getElementById('longitude');

                // Only auto-fetch if location is not already set
                if ((!latField.value || !lngField.value) && currentLocationType === 'gps') {
                    console.log('Auto-fetching user location on page load...');
                    getCurrentLocation();
                }
            }, 500); // Small delay to ensure map is initialized

            // Ensure place order buttons are fully clickable
            const placeOrderBtn = document.getElementById('place-order-btn');
            const placeOrderBtnMobile = document.getElementById('place-order-btn-mobile');

            if (placeOrderBtn) {
                placeOrderBtn.addEventListener('click', function(e) {
                    console.log('Desktop Place Order button clicked');
                    // Ensure the click event is not prevented
                    e.stopPropagation();
                });
            }

            if (placeOrderBtnMobile) {
                placeOrderBtnMobile.addEventListener('click', function(e) {
                    console.log('Mobile Place Order button clicked');
                    // Ensure the click event is not prevented
                    e.stopPropagation();
                });
            }
        });
    </script>

