<?php
/**
 * Restaurant Detail Page - Time2Eat
 * Shows restaurant information, menu items, and reviews
 */
?>

<!-- Back Button -->
<div class="tw-bg-white tw-border-b tw-border-gray-200 tw-shadow-sm">
    <div class="tw-container tw-mx-auto tw-px-4 tw-py-3">
        <a href="<?= url('/browse') ?>" class="tw-inline-flex tw-items-center tw-text-gray-600 hover:tw-text-orange-600 tw-transition-colors tw-font-semibold">
            <svg class="tw-w-5 tw-h-5 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Browse
        </a>
    </div>
</div>

<!-- Restaurant Header -->
<section class="tw-relative tw-bg-gradient-to-br tw-from-orange-500 tw-via-red-600 tw-to-pink-600 tw-overflow-hidden">
    <!-- Background Pattern -->
    <div class="tw-absolute tw-inset-0 tw-opacity-10">
        <div class="tw-absolute tw-inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    </div>

    <div class="tw-container tw-mx-auto tw-px-4 tw-py-8 sm:tw-py-12 tw-relative tw-z-10">
        <div class="tw-flex tw-flex-col lg:tw-flex-row tw-items-start lg:tw-items-center tw-gap-6 sm:tw-gap-8">
            <!-- Restaurant Image -->
            <div class="tw-w-full lg:tw-w-80 xl:tw-w-96 tw-h-56 sm:tw-h-64 lg:tw-h-80 tw-bg-white tw-rounded-2xl sm:tw-rounded-3xl tw-overflow-hidden tw-shadow-2xl tw-border-4 tw-border-white/20">
                <?php
                $restaurantImage = imageUrl($restaurant['image'] ?? null, 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&h=600&fit=crop&q=80');
                ?>
                <img
                    src="<?= e($restaurantImage) ?>"
                    alt="<?= e($restaurant['name']) ?>"
                    class="tw-w-full tw-h-full tw-object-cover"
                    onerror="this.src='https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&h=600&fit=crop&q=80'"
                >
            </div>

            <!-- Restaurant Info -->
            <div class="tw-flex-1 tw-text-white tw-w-full">
                <!-- Status Badge -->
                <div class="tw-mb-4">
                    <?php if (!empty($restaurant['is_open'])): ?>
                        <span class="tw-inline-flex tw-items-center tw-bg-green-500 tw-text-white tw-px-4 tw-py-2 tw-rounded-full tw-font-bold tw-text-sm tw-shadow-lg">
                            <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Open Now
                        </span>
                    <?php else: ?>
                        <span class="tw-inline-flex tw-items-center tw-bg-red-500 tw-text-white tw-px-4 tw-py-2 tw-rounded-full tw-font-bold tw-text-sm tw-shadow-lg">
                            <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            Currently Closed
                        </span>
                    <?php endif; ?>
                </div>

                <h1 class="tw-text-3xl sm:tw-text-4xl lg:tw-text-5xl tw-font-black tw-mb-3 sm:tw-mb-4 tw-leading-tight">
                    <?= e($restaurant['name']) ?>
                </h1>

                <p class="tw-text-lg sm:tw-text-xl tw-font-semibold tw-mb-3 sm:tw-mb-4 tw-text-orange-100">
                    <?= e($restaurant['cuisine_type']) ?>
                </p>

                <p class="tw-text-base sm:tw-text-lg tw-mb-6 sm:tw-mb-8 tw-leading-relaxed tw-text-white/90 tw-max-w-2xl">
                    <?= e($restaurant['description']) ?>
                </p>

                <!-- Restaurant Stats -->
                <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-3 tw-gap-4 sm:tw-gap-6">
                    <div class="tw-bg-white/10 tw-backdrop-blur-sm tw-rounded-xl tw-p-4 tw-border tw-border-white/20">
                        <div class="tw-flex tw-items-center tw-gap-3">
                            <div class="tw-bg-yellow-400 tw-rounded-lg tw-p-2">
                                <svg class="tw-w-6 tw-h-6 tw-text-yellow-900" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="tw-text-2xl tw-font-black"><?= number_format($restaurant['rating'] ?? 4.5, 1) ?></div>
                                <div class="tw-text-xs tw-text-white/80"><?= $restaurant['total_reviews'] ?? 0 ?> reviews</div>
                            </div>
                        </div>
                    </div>

                    <div class="tw-bg-white/10 tw-backdrop-blur-sm tw-rounded-xl tw-p-4 tw-border tw-border-white/20">
                        <div class="tw-flex tw-items-center tw-gap-3">
                            <div class="tw-bg-blue-400 tw-rounded-lg tw-p-2">
                                <svg class="tw-w-6 tw-h-6 tw-text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="tw-text-2xl tw-font-black"><?= e($restaurant['delivery_time'] ?? '30-45') ?></div>
                                <div class="tw-text-xs tw-text-white/80">minutes</div>
                            </div>
                        </div>
                    </div>

                    <div class="tw-bg-white/10 tw-backdrop-blur-sm tw-rounded-xl tw-p-4 tw-border tw-border-white/20 tw-cursor-pointer hover:tw-bg-white/20 tw-transition-all" onclick="showDeliveryInfo()">
                        <div class="tw-flex tw-items-center tw-gap-3">
                            <div class="tw-bg-green-400 tw-rounded-lg tw-p-2">
                                <svg class="tw-w-6 tw-h-6 tw-text-green-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="tw-text-2xl tw-font-black"><?= number_format($restaurant['delivery_fee'] ?? 500) ?></div>
                                <div class="tw-text-xs tw-text-white/80">FCFA base fee</div>
                            </div>
                        </div>
                        <div class="tw-mt-2 tw-text-xs tw-text-white/70 tw-flex tw-items-center">
                            <svg class="tw-w-3 tw-h-3 tw-mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Click for details
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Delivery Zone Information Banner -->
<section class="tw-bg-gradient-to-r tw-from-blue-50 tw-to-indigo-50 tw-border-b tw-border-blue-200">
    <div class="tw-container tw-mx-auto tw-px-4 tw-py-6">
        <div class="tw-flex tw-flex-col md:tw-flex-row tw-items-start md:tw-items-center tw-gap-4">
            <div class="tw-flex-shrink-0">
                <div class="tw-bg-blue-500 tw-rounded-full tw-p-3">
                    <svg class="tw-w-6 tw-h-6 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="tw-flex-1">
                <h3 class="tw-text-lg tw-font-bold tw-text-gray-900 tw-mb-1">Delivery Zone Information</h3>
                <p class="tw-text-sm tw-text-gray-700">
                    <strong class="tw-text-blue-700"><?= number_format($restaurant['delivery_fee'] ?? 500) ?> FCFA</strong> within
                    <strong class="tw-text-blue-700"><?= number_format($restaurant['delivery_radius'] ?? 10, 1) ?> km</strong>,
                    +<strong class="tw-text-blue-700"><?= number_format($restaurant['delivery_fee_per_extra_km'] ?? 100) ?> FCFA/km</strong> beyond
                    (max <strong class="tw-text-blue-700"><?= number_format(($restaurant['delivery_radius'] ?? 10) * 2, 1) ?> km</strong>)
                </p>
                <div class="tw-mt-2 tw-flex tw-flex-wrap tw-gap-2 tw-text-xs">
                    <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-bg-green-100 tw-text-green-800 tw-rounded-full tw-font-medium">
                        <span class="tw-w-2 tw-h-2 tw-bg-green-500 tw-rounded-full tw-mr-1.5"></span>
                        Within <?= number_format($restaurant['delivery_radius'] ?? 10, 1) ?>km: Base fee only
                    </span>
                    <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-bg-yellow-100 tw-text-yellow-800 tw-rounded-full tw-font-medium">
                        <span class="tw-w-2 tw-h-2 tw-bg-yellow-500 tw-rounded-full tw-mr-1.5"></span>
                        Beyond: Base + extra fee
                    </span>
                    <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-bg-red-100 tw-text-red-800 tw-rounded-full tw-font-medium">
                        <span class="tw-w-2 tw-h-2 tw-bg-red-500 tw-rounded-full tw-mr-1.5"></span>
                        Beyond <?= number_format(($restaurant['delivery_radius'] ?? 10) * 2, 1) ?>km: Not available
                    </span>
                </div>
            </div>
            <div class="tw-flex-shrink-0">
                <button onclick="showDeliveryInfo()" class="tw-bg-blue-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-font-semibold tw-text-sm hover:tw-bg-blue-700 tw-transition-colors tw-shadow-md">
                    <svg class="tw-w-4 tw-h-4 tw-inline tw-mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Calculate Fee
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Restaurant Location Map -->
<?php if (!empty($restaurant['latitude']) && !empty($restaurant['longitude'])): ?>
<section class="tw-bg-white tw-border-b tw-border-gray-200">
    <div class="tw-container tw-mx-auto tw-px-4 tw-py-6">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <h3 class="tw-text-lg tw-font-bold tw-text-gray-900 tw-flex tw-items-center">
                <svg class="tw-w-5 tw-h-5 tw-mr-2 tw-text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Restaurant Location
            </h3>
            <button onclick="toggleLocationMap()" class="tw-text-sm tw-text-blue-600 hover:tw-text-blue-700 tw-font-semibold">
                <span id="map-toggle-text">Show Map</span>
                <svg id="map-toggle-icon" class="tw-w-4 tw-h-4 tw-inline tw-ml-1 tw-transform tw-transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>

        <!-- Map Container (Hidden by default) -->
        <div id="restaurant-location-map-container" class="tw-hidden tw-rounded-lg tw-overflow-hidden tw-border tw-border-gray-300 tw-shadow-md">
            <div id="restaurant-location-map" style="height: 400px; width: 100%;"></div>
            <div class="tw-bg-gray-50 tw-p-3 tw-border-t tw-border-gray-200">
                <div class="tw-flex tw-items-start tw-gap-3">
                    <svg class="tw-w-5 tw-h-5 tw-text-orange-600 tw-flex-shrink-0 tw-mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="tw-flex-1">
                        <p class="tw-text-sm tw-font-semibold tw-text-gray-900"><?= e($restaurant['name']) ?></p>
                        <p class="tw-text-xs tw-text-gray-600 tw-mt-1">
                            <?= e($restaurant['address'] ?? 'Address not available') ?>
                            <?php if (!empty($restaurant['city'])): ?>
                                , <?= e($restaurant['city']) ?>
                            <?php endif; ?>
                        </p>
                        <p class="tw-text-xs tw-text-gray-500 tw-mt-1">
                            Coordinates: <?= number_format($restaurant['latitude'], 6) ?>, <?= number_format($restaurant['longitude'], 6) ?>
                        </p>
                    </div>
                    <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $restaurant['latitude'] ?>,<?= $restaurant['longitude'] ?>"
                       target="_blank"
                       class="tw-flex-shrink-0 tw-bg-blue-600 tw-text-white tw-px-3 tw-py-2 tw-rounded-lg tw-text-xs tw-font-semibold hover:tw-bg-blue-700 tw-transition-colors tw-flex tw-items-center tw-gap-1">
                        <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                        </svg>
                        Get Directions
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Menu Section -->
<section class="tw-py-8 sm:tw-py-12 tw-bg-gray-50">
    <div class="tw-container tw-mx-auto tw-px-4">
        <!-- Section Header -->
        <div class="tw-mb-8 sm:tw-mb-12">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
                <h2 class="tw-text-2xl sm:tw-text-3xl lg:tw-text-4xl tw-font-black tw-text-gray-900">
                    <svg class="tw-w-8 tw-h-8 tw-inline tw-mr-2 tw-text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    Our Menu
                </h2>
            </div>
            <p class="tw-text-base sm:tw-text-lg tw-text-gray-600">
                Delicious dishes from <span class="tw-font-bold tw-text-orange-600"><?= e($restaurant['name']) ?></span>
            </p>
        </div>

        <?php if (!empty($menu_items)): ?>
            <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-3 xl:tw-grid-cols-4 tw-gap-4 sm:tw-gap-6">
                <?php foreach ($menu_items as $item): ?>
                    <article class="tw-group tw-bg-white tw-rounded-2xl tw-shadow-md tw-overflow-hidden tw-border tw-border-gray-100 tw-transition-all tw-duration-300 hover:tw-shadow-2xl hover:-tw-translate-y-2 hover:tw-border-orange-200">
                        <!-- Menu Item Image -->
                        <div class="tw-relative tw-h-44 sm:tw-h-52 tw-overflow-hidden tw-bg-gradient-to-br tw-from-gray-100 tw-to-gray-200">
                            <?php
                            $menuItemImage = imageUrl($item['image'] ?? null, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&h=600&fit=crop&q=80');
                            ?>
                            <img
                                src="<?= e($menuItemImage) ?>"
                                alt="<?= e($item['name']) ?>"
                                class="tw-w-full tw-h-full tw-object-cover tw-transition-transform tw-duration-500 group-hover:tw-scale-110"
                                loading="lazy"
                                onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&h=600&fit=crop&q=80'"
                            >
                            <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-t tw-from-black/50 tw-via-transparent tw-to-transparent"></div>

                            <!-- Rating Badge -->
                            <?php if (!empty($item['rating']) && $item['rating'] > 0): ?>
                            <div class="tw-absolute tw-top-3 tw-right-3 tw-bg-white tw-rounded-full tw-px-2.5 tw-py-1 tw-flex tw-items-center tw-gap-1 tw-shadow-lg">
                                <svg class="tw-w-4 tw-h-4 tw-text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <span class="tw-text-sm tw-font-bold tw-text-gray-900"><?= number_format($item['rating'], 1) ?></span>
                            </div>
                            <?php endif; ?>

                            <!-- Category Badge -->
                            <?php if (!empty($item['category_name'])): ?>
                            <div class="tw-absolute tw-top-3 tw-left-3 tw-bg-orange-500 tw-text-white tw-rounded-lg tw-px-2.5 tw-py-1 tw-text-xs tw-font-bold tw-shadow-lg">
                                <?= e($item['category_name']) ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Menu Item Details -->
                        <div class="tw-p-4 sm:tw-p-5">
                            <h3 class="tw-text-lg sm:tw-text-xl tw-font-black tw-text-gray-900 tw-mb-2 tw-line-clamp-1 group-hover:tw-text-orange-600 tw-transition-colors">
                                <?= e($item['name']) ?>
                            </h3>

                            <p class="tw-text-sm tw-text-gray-600 tw-mb-4 tw-line-clamp-2 tw-leading-relaxed">
                                <?= e($item['description'] ?? 'Delicious dish prepared with care') ?>
                            </p>

                            <!-- Price -->
                            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4 tw-pb-4 tw-border-b tw-border-gray-100">
                                <div>
                                    <div class="tw-text-xs tw-text-gray-500 tw-mb-0.5">Price</div>
                                    <div class="tw-text-2xl tw-font-black tw-text-orange-600">
                                        <?= number_format($item['price']) ?> <span class="tw-text-sm tw-text-gray-500">FCFA</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Add to Cart Button -->
                            <button
                                class="tw-w-full tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-py-3 tw-rounded-xl tw-font-bold tw-transition-all tw-duration-200 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-orange-200 tw-flex tw-items-center tw-justify-center tw-gap-2 tw-shadow-md hover:tw-shadow-lg tw-text-sm sm:tw-text-base"
                                onclick="addToCart(<?= $item['id'] ?>, '<?= e($item['name']) ?>', <?= $item['price'] ?>, '<?= e($item['image'] ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&h=600&fit=crop&q=80') ?>', <?= $restaurant['id'] ?>, '<?= e($restaurant['name']) ?>', '<?= e($item['category_name'] ?? 'Food') ?>')"
                            >
                                <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <span>Add to Cart</span>
                            </button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="tw-text-center tw-py-12 sm:tw-py-16">
                <div class="tw-w-20 tw-h-20 sm:tw-w-24 sm:tw-h-24 tw-mx-auto tw-mb-6 tw-bg-gradient-to-br tw-from-orange-100 tw-to-red-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                    <svg class="tw-w-10 tw-h-10 sm:tw-w-12 sm:tw-h-12 tw-text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <h3 class="tw-text-xl sm:tw-text-2xl tw-font-black tw-text-gray-900 tw-mb-3">No Menu Items Available</h3>
                <p class="tw-text-sm sm:tw-text-base tw-text-gray-600 tw-mb-6 tw-max-w-md tw-mx-auto">
                    This restaurant hasn't added any menu items yet. Please check back later!
                </p>
                <a href="<?= url('/browse') ?>" class="tw-inline-flex tw-items-center tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-px-6 tw-py-3 tw-rounded-xl tw-font-bold tw-shadow-lg hover:tw-shadow-xl tw-transition-all">
                    <svg class="tw-w-5 tw-h-5 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Browse Other Restaurants
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Delivery Fee Calculator Modal -->
<div id="deliveryInfoModal" class="tw-hidden tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-z-50 tw-p-4">
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-2xl tw-max-w-2xl tw-w-full tw-max-h-[90vh] tw-overflow-y-auto">
        <!-- Modal Header -->
        <div class="tw-bg-gradient-to-r tw-from-blue-600 tw-to-indigo-600 tw-p-6 tw-rounded-t-2xl">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div class="tw-flex tw-items-center tw-gap-3">
                    <div class="tw-bg-white/20 tw-rounded-full tw-p-2">
                        <svg class="tw-w-6 tw-h-6 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="tw-text-xl tw-font-bold tw-text-white">Delivery Fee Calculator</h3>
                        <p class="tw-text-sm tw-text-blue-100"><?= e($restaurant['name']) ?></p>
                    </div>
                </div>
                <button onclick="closeDeliveryInfo()" class="tw-text-white hover:tw-bg-white/20 tw-rounded-full tw-p-2 tw-transition-colors">
                    <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="tw-p-6 tw-space-y-6">
            <!-- Delivery Zone Settings -->
            <div class="tw-bg-gradient-to-br tw-from-blue-50 tw-to-indigo-50 tw-rounded-xl tw-p-5 tw-border tw-border-blue-200">
                <h4 class="tw-text-lg tw-font-bold tw-text-gray-900 tw-mb-4 tw-flex tw-items-center">
                    <svg class="tw-w-5 tw-h-5 tw-mr-2 tw-text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Delivery Zone Settings
                </h4>
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                    <div class="tw-bg-white tw-rounded-lg tw-p-4 tw-shadow-sm">
                        <div class="tw-text-sm tw-text-gray-600 tw-mb-1">Free Zone Radius</div>
                        <div class="tw-text-2xl tw-font-black tw-text-blue-600"><?= number_format($restaurant['delivery_radius'] ?? 10, 1) ?> km</div>
                        <div class="tw-text-xs tw-text-gray-500 tw-mt-1">Base fee applies within this distance</div>
                    </div>
                    <div class="tw-bg-white tw-rounded-lg tw-p-4 tw-shadow-sm">
                        <div class="tw-text-sm tw-text-gray-600 tw-mb-1">Base Delivery Fee</div>
                        <div class="tw-text-2xl tw-font-black tw-text-green-600"><?= number_format($restaurant['delivery_fee'] ?? 500) ?> FCFA</div>
                        <div class="tw-text-xs tw-text-gray-500 tw-mt-1">Fee within free zone</div>
                    </div>
                    <div class="tw-bg-white tw-rounded-lg tw-p-4 tw-shadow-sm">
                        <div class="tw-text-sm tw-text-gray-600 tw-mb-1">Extra Fee per KM</div>
                        <div class="tw-text-2xl tw-font-black tw-text-orange-600"><?= number_format($restaurant['delivery_fee_per_extra_km'] ?? 100) ?> FCFA</div>
                        <div class="tw-text-xs tw-text-gray-500 tw-mt-1">Added beyond free zone</div>
                    </div>
                    <div class="tw-bg-white tw-rounded-lg tw-p-4 tw-shadow-sm">
                        <div class="tw-text-sm tw-text-gray-600 tw-mb-1">Maximum Distance</div>
                        <div class="tw-text-2xl tw-font-black tw-text-red-600"><?= number_format(($restaurant['delivery_radius'] ?? 10) * 2, 1) ?> km</div>
                        <div class="tw-text-xs tw-text-gray-500 tw-mt-1">Beyond this: Not available</div>
                    </div>
                </div>
            </div>

            <!-- How It Works -->
            <div class="tw-bg-gray-50 tw-rounded-xl tw-p-5 tw-border tw-border-gray-200">
                <h4 class="tw-text-lg tw-font-bold tw-text-gray-900 tw-mb-4 tw-flex tw-items-center">
                    <svg class="tw-w-5 tw-h-5 tw-mr-2 tw-text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    How Delivery Fees Work
                </h4>
                <div class="tw-space-y-3">
                    <div class="tw-flex tw-items-start tw-gap-3">
                        <div class="tw-flex-shrink-0 tw-w-8 tw-h-8 tw-bg-green-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-white tw-font-bold">1</div>
                        <div class="tw-flex-1">
                            <div class="tw-font-semibold tw-text-gray-900">Within Free Zone (≤ <?= number_format($restaurant['delivery_radius'] ?? 10, 1) ?> km)</div>
                            <div class="tw-text-sm tw-text-gray-600">You pay only the base fee of <strong><?= number_format($restaurant['delivery_fee'] ?? 500) ?> FCFA</strong></div>
                        </div>
                    </div>
                    <div class="tw-flex tw-items-start tw-gap-3">
                        <div class="tw-flex-shrink-0 tw-w-8 tw-h-8 tw-bg-yellow-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-white tw-font-bold">2</div>
                        <div class="tw-flex-1">
                            <div class="tw-font-semibold tw-text-gray-900">Beyond Free Zone (> <?= number_format($restaurant['delivery_radius'] ?? 10, 1) ?> km)</div>
                            <div class="tw-text-sm tw-text-gray-600">Base fee + <strong><?= number_format($restaurant['delivery_fee_per_extra_km'] ?? 100) ?> FCFA</strong> for each extra kilometer</div>
                        </div>
                    </div>
                    <div class="tw-flex tw-items-start tw-gap-3">
                        <div class="tw-flex-shrink-0 tw-w-8 tw-h-8 tw-bg-red-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-white tw-font-bold">3</div>
                        <div class="tw-flex-1">
                            <div class="tw-font-semibold tw-text-gray-900">Beyond Maximum (> <?= number_format(($restaurant['delivery_radius'] ?? 10) * 2, 1) ?> km)</div>
                            <div class="tw-text-sm tw-text-gray-600">Delivery not available - outside our delivery zone</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Example Calculations -->
            <div class="tw-bg-gradient-to-br tw-from-purple-50 tw-to-pink-50 tw-rounded-xl tw-p-5 tw-border tw-border-purple-200">
                <h4 class="tw-text-lg tw-font-bold tw-text-gray-900 tw-mb-4 tw-flex tw-items-center">
                    <svg class="tw-w-5 tw-h-5 tw-mr-2 tw-text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                    Example Calculations
                </h4>
                <div class="tw-space-y-3">
                    <?php
                    $radius = (float)($restaurant['delivery_radius'] ?? 10);
                    $baseFee = (float)($restaurant['delivery_fee'] ?? 500);
                    $extraFee = (float)($restaurant['delivery_fee_per_extra_km'] ?? 100);

                    // Example 1: Within zone
                    $ex1Distance = $radius * 0.7;
                    $ex1Fee = $baseFee;

                    // Example 2: Just beyond zone
                    $ex2Distance = $radius + 3;
                    $ex2ExtraDistance = $ex2Distance - $radius;
                    $ex2Fee = $baseFee + ($ex2ExtraDistance * $extraFee);

                    // Example 3: Near max
                    $ex3Distance = $radius * 1.8;
                    $ex3ExtraDistance = $ex3Distance - $radius;
                    $ex3Fee = $baseFee + ($ex3ExtraDistance * $extraFee);
                    ?>

                    <div class="tw-bg-white tw-rounded-lg tw-p-4 tw-shadow-sm">
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                            <span class="tw-font-semibold tw-text-gray-900">📍 <?= number_format($ex1Distance, 1) ?> km away</span>
                            <span class="tw-px-3 tw-py-1 tw-bg-green-100 tw-text-green-800 tw-rounded-full tw-text-sm tw-font-bold"><?= number_format($ex1Fee) ?> FCFA</span>
                        </div>
                        <div class="tw-text-sm tw-text-gray-600">Within free zone - base fee only</div>
                    </div>

                    <div class="tw-bg-white tw-rounded-lg tw-p-4 tw-shadow-sm">
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                            <span class="tw-font-semibold tw-text-gray-900">📍 <?= number_format($ex2Distance, 1) ?> km away</span>
                            <span class="tw-px-3 tw-py-1 tw-bg-yellow-100 tw-text-yellow-800 tw-rounded-full tw-text-sm tw-font-bold"><?= number_format($ex2Fee) ?> FCFA</span>
                        </div>
                        <div class="tw-text-sm tw-text-gray-600">
                            <?= number_format($baseFee) ?> base + <?= number_format($ex2ExtraDistance, 1) ?> km × <?= number_format($extraFee) ?> = <?= number_format($ex2Fee) ?> FCFA
                        </div>
                    </div>

                    <div class="tw-bg-white tw-rounded-lg tw-p-4 tw-shadow-sm">
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                            <span class="tw-font-semibold tw-text-gray-900">📍 <?= number_format($ex3Distance, 1) ?> km away</span>
                            <span class="tw-px-3 tw-py-1 tw-bg-orange-100 tw-text-orange-800 tw-rounded-full tw-text-sm tw-font-bold"><?= number_format($ex3Fee) ?> FCFA</span>
                        </div>
                        <div class="tw-text-sm tw-text-gray-600">
                            <?= number_format($baseFee) ?> base + <?= number_format($ex3ExtraDistance, 1) ?> km × <?= number_format($extraFee) ?> = <?= number_format($ex3Fee) ?> FCFA
                        </div>
                    </div>
                </div>
            </div>

            <!-- Note -->
            <div class="tw-bg-blue-50 tw-border-l-4 tw-border-blue-500 tw-p-4 tw-rounded">
                <div class="tw-flex tw-items-start">
                    <svg class="tw-w-5 tw-h-5 tw-text-blue-500 tw-mr-2 tw-flex-shrink-0 tw-mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="tw-text-sm tw-text-blue-800">
                        <strong>Note:</strong> The exact delivery fee will be calculated based on your delivery address during checkout.
                        All fees are rounded to the nearest 50 FCFA for convenience.
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="tw-p-6 tw-border-t tw-border-gray-200 tw-bg-gray-50 tw-rounded-b-2xl">
            <button onclick="closeDeliveryInfo()" class="tw-w-full tw-bg-blue-600 tw-text-white tw-px-6 tw-py-3 tw-rounded-lg tw-font-semibold hover:tw-bg-blue-700 tw-transition-colors tw-shadow-md">
                Got it, thanks!
            </button>
        </div>
    </div>
</div>

<script>
function showDeliveryInfo() {
    document.getElementById('deliveryInfoModal').classList.remove('tw-hidden');
    document.body.style.overflow = 'hidden';
}

function closeDeliveryInfo() {
    document.getElementById('deliveryInfoModal').classList.add('tw-hidden');
    document.body.style.overflow = 'auto';
}

// Close modal on outside click
document.getElementById('deliveryInfoModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeliveryInfo();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeliveryInfo();
    }
});

// Restaurant Location Map Toggle
let restaurantLocationMap = null;
let restaurantLocationMapInitialized = false;

function toggleLocationMap() {
    const container = document.getElementById('restaurant-location-map-container');
    const toggleText = document.getElementById('map-toggle-text');
    const toggleIcon = document.getElementById('map-toggle-icon');

    if (container.classList.contains('tw-hidden')) {
        container.classList.remove('tw-hidden');
        toggleText.textContent = 'Hide Map';
        toggleIcon.classList.add('tw-rotate-180');

        // Initialize map if not already done
        if (!restaurantLocationMapInitialized) {
            initRestaurantLocationMap();
        }
    } else {
        container.classList.add('tw-hidden');
        toggleText.textContent = 'Show Map';
        toggleIcon.classList.remove('tw-rotate-180');
    }
}

function initRestaurantLocationMap() {
    <?php if (!empty($restaurant['latitude']) && !empty($restaurant['longitude'])): ?>
    const restaurantLat = <?= $restaurant['latitude'] ?>;
    const restaurantLon = <?= $restaurant['longitude'] ?>;
    const restaurantName = <?= json_encode($restaurant['name']) ?>;
    const deliveryRadius = <?= $restaurant['delivery_radius'] ?? 10 ?>;
    const maxDeliveryDistance = deliveryRadius * 2;

    // Initialize map
    restaurantLocationMap = L.map('restaurant-location-map').setView([restaurantLat, restaurantLon], 13);

    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(restaurantLocationMap);

    // Add restaurant marker
    const restaurantIcon = L.divIcon({
        className: 'custom-restaurant-marker',
        html: `
            <div style="position: relative;">
                <div style="background: #ea580c; width: 40px; height: 40px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;">
                    <svg style="width: 20px; height: 20px; transform: rotate(45deg); color: white;" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                    </svg>
                </div>
            </div>
        `,
        iconSize: [40, 40],
        iconAnchor: [20, 40],
        popupAnchor: [0, -40]
    });

    const restaurantMarker = L.marker([restaurantLat, restaurantLon], { icon: restaurantIcon })
        .addTo(restaurantLocationMap)
        .bindPopup(`
            <div style="text-align: center; padding: 8px;">
                <strong style="font-size: 14px; color: #ea580c;">${restaurantName}</strong><br>
                <span style="font-size: 12px; color: #666;">Restaurant Location</span>
            </div>
        `);

    // Add delivery radius circles
    // Free zone (green)
    L.circle([restaurantLat, restaurantLon], {
        color: '#10b981',
        fillColor: '#10b981',
        fillOpacity: 0.1,
        radius: deliveryRadius * 1000, // Convert km to meters
        weight: 2,
        dashArray: '5, 5'
    }).addTo(restaurantLocationMap).bindPopup(`
        <div style="text-align: center; padding: 8px;">
            <strong style="color: #10b981;">Free Zone</strong><br>
            <span style="font-size: 12px;">Within ${deliveryRadius} km - Base fee only</span>
        </div>
    `);

    // Extended zone (yellow)
    L.circle([restaurantLat, restaurantLon], {
        color: '#f59e0b',
        fillColor: '#f59e0b',
        fillOpacity: 0.05,
        radius: maxDeliveryDistance * 1000, // Convert km to meters
        weight: 2,
        dashArray: '10, 5'
    }).addTo(restaurantLocationMap).bindPopup(`
        <div style="text-align: center; padding: 8px;">
            <strong style="color: #f59e0b;">Extended Zone</strong><br>
            <span style="font-size: 12px;">Up to ${maxDeliveryDistance} km - Base + extra fee</span>
        </div>
    `);

    restaurantLocationMapInitialized = true;

    // Fit bounds to show both circles
    const bounds = L.latLngBounds([
        [restaurantLat, restaurantLon]
    ]).pad(0.5);
    restaurantLocationMap.fitBounds(bounds);

    <?php endif; ?>
}
</script>

<!-- Reviews Section -->
<?php if (!empty($reviews)): ?>
<section class="tw-py-8 sm:tw-py-12 tw-bg-white">
    <div class="tw-container tw-mx-auto tw-px-4">
        <!-- Section Header -->
        <div class="tw-mb-8 sm:tw-mb-12">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
                <h2 class="tw-text-2xl sm:tw-text-3xl lg:tw-text-4xl tw-font-black tw-text-gray-900">
                    <svg class="tw-w-8 tw-h-8 tw-inline tw-mr-2 tw-text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                    </svg>
                    Customer Reviews
                </h2>
            </div>
            <p class="tw-text-base sm:tw-text-lg tw-text-gray-600">
                What customers say about <span class="tw-font-bold tw-text-orange-600"><?= e($restaurant['name']) ?></span>
            </p>
        </div>

        <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-4 sm:tw-gap-6">
            <?php foreach ($reviews as $review): ?>
                <article class="tw-bg-gradient-to-br tw-from-gray-50 tw-to-white tw-rounded-2xl tw-p-5 sm:tw-p-6 tw-shadow-md tw-border tw-border-gray-100 tw-transition-all tw-duration-300 hover:tw-shadow-xl hover:-tw-translate-y-1">
                    <!-- Review Header -->
                    <div class="tw-flex tw-items-start tw-mb-4">
                        <div class="tw-w-12 tw-h-12 tw-bg-gradient-to-br tw-from-orange-500 tw-to-red-600 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mr-3 tw-flex-shrink-0 tw-shadow-md">
                            <span class="tw-text-white tw-font-black tw-text-lg">
                                <?= strtoupper(substr($review['customer_name'] ?? 'U', 0, 1)) ?>
                            </span>
                        </div>
                        <div class="tw-flex-1">
                            <h4 class="tw-font-bold tw-text-gray-900 tw-mb-1">
                                <?= e($review['customer_name'] ?? 'Anonymous') ?>
                            </h4>
                            <!-- Star Rating -->
                            <div class="tw-flex tw-items-center tw-gap-0.5">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <svg class="tw-w-4 tw-h-4 <?= $i <= ($review['rating'] ?? 5) ? 'tw-text-yellow-400' : 'tw-text-gray-300' ?>" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Review Comment -->
                    <p class="tw-text-gray-700 tw-leading-relaxed tw-mb-4 tw-text-sm sm:tw-text-base">
                        "<?= e($review['comment']) ?>"
                    </p>

                    <!-- Review Date -->
                    <div class="tw-flex tw-items-center tw-text-xs tw-text-gray-500 tw-pt-4 tw-border-t tw-border-gray-100">
                        <svg class="tw-w-4 tw-h-4 tw-mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <?= date('M j, Y', strtotime($review['created_at'])) ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
// Initialize Feather icons
feather.replace();

// Cart functionality is handled by app.js

// Back to browse button
function goBack() {
    window.history.back();
}
</script>
