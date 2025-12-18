<?php
/**
 * Professional Home Page - Mobile First Design
 * Redesigned with professional excellence and mobile-first approach
 */
?>

<main id="main-content" role="main">
    <!-- Hero Section with Background Image -->
    <section class="tw-relative tw-min-h-screen tw-flex tw-items-center tw-justify-center tw-overflow-hidden">
        <!-- Hero Background Image -->
        <div class="tw-absolute tw-inset-0 tw-z-0">
            <img
                src="<?= url('public/images/hero.webp') ?>"
                alt="Delicious Cameroonian food delivery - Time2Eat"
                class="tw-w-full tw-h-full tw-object-cover"
                loading="eager"
            >
            <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-br tw-from-black/75 tw-via-black/60 tw-to-black/75"></div>
        </div>

        <!-- Animated Background Elements -->
        <div class="tw-absolute tw-inset-0 tw-z-10 tw-opacity-20">
            <div class="tw-absolute tw-top-20 tw-left-10 tw-w-72 tw-h-72 tw-bg-orange-500 tw-rounded-full tw-blur-3xl tw-animate-pulse"></div>
            <div class="tw-absolute tw-bottom-20 tw-right-10 tw-w-96 tw-h-96 tw-bg-red-500 tw-rounded-full tw-blur-3xl tw-animate-pulse" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Hero Content -->
        <div class="tw-relative tw-z-20 tw-container tw-mx-auto tw-px-4 tw-py-20 tw-text-center">
            <div class="tw-max-w-5xl tw-mx-auto">
                <!-- Badge -->
                <div class="tw-inline-flex tw-items-center tw-gap-2 tw-bg-white/10 tw-backdrop-blur-md tw-border tw-border-white/20 tw-rounded-full tw-px-6 tw-py-3 tw-mb-8">
                    <span class="tw-relative tw-flex tw-h-3 tw-w-3">
                        <span class="tw-animate-ping tw-absolute tw-inline-flex tw-h-full tw-w-full tw-rounded-full tw-bg-green-400 tw-opacity-75"></span>
                        <span class="tw-relative tw-inline-flex tw-rounded-full tw-h-3 tw-w-3 tw-bg-green-500"></span>
                    </span>
                    <span class="tw-text-white tw-font-semibold tw-text-sm">Now Delivering in Bamenda</span>
                </div>

                <!-- Main Heading -->
                <h1 class="tw-text-4xl sm:tw-text-5xl md:tw-text-6xl lg:tw-text-7xl tw-font-black tw-text-white tw-mb-6 tw-leading-tight">
                    <span class="tw-block tw-mb-2">Craving Something</span>
                    <span class="tw-bg-gradient-to-r tw-from-orange-400 tw-via-red-500 tw-to-pink-500 tw-bg-clip-text tw-text-transparent">
                        Delicious?
                    </span>
                </h1>

                <!-- Subtitle -->
                <p class="tw-text-lg sm:tw-text-xl md:tw-text-2xl tw-text-gray-200 tw-mb-12 tw-max-w-3xl tw-mx-auto tw-leading-relaxed tw-px-4">
                    Order from Bamenda's best restaurants and get authentic Cameroonian cuisine delivered to your doorstep in minutes
                </p>

                <!-- CTA Buttons -->
                <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-4 tw-justify-center tw-mb-16 tw-px-4">
                    <a href="<?= url('/browse') ?>" class="tw-group tw-inline-flex tw-items-center tw-justify-center tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-px-8 sm:tw-px-10 tw-py-4 sm:tw-py-5 tw-rounded-2xl tw-text-base sm:tw-text-lg tw-font-bold tw-shadow-2xl hover:tw-shadow-orange-500/50 tw-transition-all tw-duration-300 hover:tw-scale-105 hover:-tw-translate-y-1">
                        <svg class="tw-w-5 tw-h-5 sm:tw-w-6 tw-h-6 tw-mr-2 tw-transition-transform group-hover:tw-rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Explore Restaurants
                    </a>
                    <a href="<?= url('/register') ?>" class="tw-group tw-inline-flex tw-items-center tw-justify-center tw-bg-white/10 tw-backdrop-blur-md tw-border-2 tw-border-white/30 tw-text-white tw-px-8 sm:tw-px-10 tw-py-4 sm:tw-py-5 tw-rounded-2xl tw-text-base sm:tw-text-lg tw-font-bold hover:tw-bg-white hover:tw-text-gray-900 tw-transition-all tw-duration-300 hover:tw-scale-105">
                        <svg class="tw-w-5 tw-h-5 sm:tw-w-6 tw-h-6 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Sign Up Free
                    </a>
                </div>

                <!-- Stats Grid -->
                <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4 tw-gap-4 sm:tw-gap-6 tw-max-w-4xl tw-mx-auto tw-px-4">
                    <div class="tw-bg-white/10 tw-backdrop-blur-md tw-border tw-border-white/20 tw-rounded-2xl tw-p-4 sm:tw-p-6 tw-transition-all tw-duration-300 hover:tw-bg-white/20 hover:tw-scale-105">
                        <div class="tw-text-3xl sm:tw-text-4xl tw-font-black tw-text-white tw-mb-2">
                            <?= number_format($stats['restaurants'] ?? 50) ?>+
                        </div>
                        <div class="tw-text-xs sm:tw-text-sm tw-text-gray-300 tw-font-medium">Restaurants</div>
                    </div>
                    <div class="tw-bg-white/10 tw-backdrop-blur-md tw-border tw-border-white/20 tw-rounded-2xl tw-p-4 sm:tw-p-6 tw-transition-all tw-duration-300 hover:tw-bg-white/20 hover:tw-scale-105">
                        <div class="tw-text-3xl sm:tw-text-4xl tw-font-black tw-text-white tw-mb-2">
                            <?= number_format($stats['orders'] ?? 10000) ?>+
                        </div>
                        <div class="tw-text-xs sm:tw-text-sm tw-text-gray-300 tw-font-medium">Orders Delivered</div>
                    </div>
                    <div class="tw-bg-white/10 tw-backdrop-blur-md tw-border tw-border-white/20 tw-rounded-2xl tw-p-4 sm:tw-p-6 tw-transition-all tw-duration-300 hover:tw-bg-white/20 hover:tw-scale-105">
                        <div class="tw-text-3xl sm:tw-text-4xl tw-font-black tw-text-white tw-mb-2">
                            <?= number_format($stats['customers'] ?? 5000) ?>+
                        </div>
                        <div class="tw-text-xs sm:tw-text-sm tw-text-gray-300 tw-font-medium">Happy Customers</div>
                    </div>
                    <div class="tw-bg-white/10 tw-backdrop-blur-md tw-border tw-border-white/20 tw-rounded-2xl tw-p-4 sm:tw-p-6 tw-transition-all tw-duration-300 hover:tw-bg-white/20 hover:tw-scale-105">
                        <div class="tw-text-3xl sm:tw-text-4xl tw-font-black tw-text-white tw-mb-2">
                            25<span class="tw-text-2xl">min</span>
                        </div>
                        <div class="tw-text-xs sm:tw-text-sm tw-text-gray-300 tw-font-medium">Avg Delivery</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="tw-absolute tw-bottom-8 tw-left-1/2 tw-transform -tw-translate-x-1/2 tw-z-20 tw-animate-bounce">
            <svg class="tw-w-6 tw-h-6 tw-text-white tw-opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="tw-py-16 sm:tw-py-20 tw-bg-gradient-to-b tw-from-gray-50 tw-to-white">
        <div class="tw-container tw-mx-auto tw-px-4">
            <div class="tw-text-center tw-mb-12 sm:tw-mb-16">
                <span class="tw-inline-block tw-bg-orange-100 tw-text-orange-600 tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-bold tw-mb-4">
                    SIMPLE PROCESS
                </span>
                <h2 class="tw-text-3xl sm:tw-text-4xl md:tw-text-5xl tw-font-black tw-text-gray-900 tw-mb-4">
                    How It Works
                </h2>
                <p class="tw-text-base sm:tw-text-lg md:tw-text-xl tw-text-gray-600 tw-max-w-2xl tw-mx-auto tw-px-4">
                    Get your favorite food delivered in three easy steps
                </p>
            </div>

            <div class="tw-max-w-6xl tw-mx-auto">
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-8 md:tw-gap-6 lg:tw-gap-8">
                    <?php foreach ($how_it_works_steps as $index => $step): ?>
                    <div class="tw-relative tw-group">
                        <!-- Card -->
                        <div class="tw-bg-white tw-rounded-3xl tw-p-6 sm:tw-p-8 tw-shadow-lg tw-border tw-border-gray-100 tw-transition-all tw-duration-300 hover:tw-shadow-2xl hover:-tw-translate-y-2">
                            <!-- Step Number Badge -->
                            <div class="tw-absolute -tw-top-4 -tw-left-4 tw-w-12 tw-h-12 tw-bg-gradient-to-br tw-from-orange-500 tw-to-red-600 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-shadow-xl tw-transform tw-rotate-12 group-hover:tw-rotate-0 tw-transition-transform tw-duration-300">
                                <span class="tw-text-white tw-font-black tw-text-xl"><?= $step['step'] ?></span>
                            </div>

                            <!-- Icon -->
                            <div class="tw-w-16 tw-h-16 sm:tw-w-20 sm:tw-h-20 <?= $step['color'] ?> tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-6 tw-shadow-lg tw-transform tw-transition-all tw-duration-300 group-hover:tw-scale-110">
                                <svg class="tw-w-8 tw-h-8 sm:tw-w-10 sm:tw-h-10 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <?php if ($step['icon'] === 'search'): ?>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    <?php elseif ($step['icon'] === 'shopping-cart'): ?>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    <?php else: ?>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                                    <?php endif; ?>
                                </svg>
                            </div>

                            <!-- Content -->
                            <h3 class="tw-text-xl sm:tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-3 tw-text-center">
                                <?= e($step['title']) ?>
                            </h3>
                            <p class="tw-text-sm sm:tw-text-base tw-text-gray-600 tw-leading-relaxed tw-text-center">
                                <?= e($step['description']) ?>
                            </p>
                        </div>

                        <!-- Connector Arrow (hidden on mobile) -->
                        <?php if ($index < count($how_it_works_steps) - 1): ?>
                        <div class="tw-hidden md:tw-block tw-absolute tw-top-1/2 tw-left-full tw-transform -tw-translate-y-1/2 tw-z-10" style="width: calc(100% - 2rem); margin-left: 1rem;">
                            <svg class="tw-w-full tw-h-8 tw-text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 100 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 12h90m0 0l-6-6m6 6l-6 6" stroke-dasharray="4 4"></path>
                            </svg>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Restaurants Section -->
    <section class="tw-py-16 sm:tw-py-20 tw-bg-white">
        <div class="tw-container tw-mx-auto tw-px-4">
            <div class="tw-text-center tw-mb-12 sm:tw-mb-16">
                <?php 
                // Check if we have featured restaurants or high-rated ones
                $hasTopRated = false;
                foreach ($featured_restaurants as $restaurant) {
                    if (!empty($restaurant['is_featured']) || ($restaurant['rating'] ?? 0) >= 4.0) {
                        $hasTopRated = true;
                        break;
                    }
                }
                ?>
                <span class="tw-inline-block tw-bg-red-100 tw-text-red-600 tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-bold tw-mb-4">
                    <?= $hasTopRated ? 'FEATURED RESTAURANTS' : 'RESTAURANTS' ?>
                </span>
                <h2 class="tw-text-3xl sm:tw-text-4xl md:tw-text-5xl tw-font-black tw-text-gray-900 tw-mb-4">
                    <?= $hasTopRated ? 'Top Rated Restaurants' : 'Restaurants' ?>
                </h2>
                <p class="tw-text-base sm:tw-text-lg md:tw-text-xl tw-text-gray-600 tw-max-w-2xl tw-mx-auto tw-px-4">
                    <?= $hasTopRated ? 'Discover Bamenda\'s finest dining experiences, handpicked for you' : 'Explore our collection of restaurants in Bamenda' ?>
                </p>
            </div>

            <?php if (!empty($featured_restaurants)): ?>
            <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6 sm:tw-gap-8 tw-max-w-7xl tw-mx-auto tw-mb-12">
                <?php foreach ($featured_restaurants as $restaurant): ?>
                <a href="<?= url('/browse/restaurant/' . $restaurant['id']) ?>" class="tw-group tw-block">
                    <div class="tw-bg-white tw-rounded-3xl tw-shadow-lg tw-overflow-hidden tw-border tw-border-gray-100 tw-transition-all tw-duration-300 hover:tw-shadow-2xl hover:-tw-translate-y-2 hover:tw-border-orange-200">
                        <!-- Restaurant Image -->
                        <div class="tw-relative tw-h-48 sm:tw-h-56 tw-overflow-hidden tw-bg-gray-100">
                            <img
                                src="<?= e($restaurant['image']) ?>"
                                alt="<?= e($restaurant['name']) ?> - Restaurant"
                                class="tw-w-full tw-h-full tw-object-cover tw-transition-transform tw-duration-500 group-hover:tw-scale-110"
                                loading="lazy"
                                onerror="this.src='https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=600&h=400&fit=crop'"
                            >
                            <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-t tw-from-black/40 tw-via-transparent tw-to-transparent"></div>

                            <!-- Featured Badge -->
                            <?php if (!empty($restaurant['is_featured'])): ?>
                            <div class="tw-absolute tw-top-4 tw-left-4">
                                <span class="tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-px-3 tw-py-1 tw-rounded-full tw-text-xs tw-font-bold tw-shadow-lg">
                                    ⭐ Featured
                                </span>
                            </div>
                            <?php endif; ?>

                            <!-- Rating Badge -->
                            <div class="tw-absolute tw-top-4 tw-right-4">
                                <div class="tw-bg-white tw-rounded-full tw-px-3 tw-py-1.5 tw-flex tw-items-center tw-shadow-lg">
                                    <svg class="tw-w-4 tw-h-4 tw-text-yellow-400 tw-mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                    <span class="tw-text-sm tw-font-bold tw-text-gray-900"><?= number_format($restaurant['rating'], 1) ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Restaurant Info -->
                        <div class="tw-p-5 sm:tw-p-6">
                            <h3 class="tw-text-lg sm:tw-text-xl tw-font-bold tw-text-gray-900 tw-mb-2 tw-line-clamp-1 group-hover:tw-text-orange-600 tw-transition-colors">
                                <?= e($restaurant['name']) ?>
                            </h3>
                            <p class="tw-text-sm sm:tw-text-base tw-text-gray-600 tw-mb-4 tw-line-clamp-2 tw-leading-relaxed">
                                <?= e($restaurant['description'] ?? 'Delicious food awaits you') ?>
                            </p>

                            <div class="tw-flex tw-items-center tw-justify-between tw-pt-4 tw-border-t tw-border-gray-100">
                                <div class="tw-flex tw-items-center tw-text-gray-500">
                                    <svg class="tw-w-4 tw-h-4 tw-mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="tw-text-xs sm:tw-text-sm tw-font-medium"><?= e($restaurant['delivery_time']) ?> min</span>
                                </div>
                                <div class="tw-flex tw-items-center tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-full tw-text-xs sm:tw-text-sm tw-font-bold tw-shadow-md group-hover:tw-shadow-lg tw-transition-all">
                                    Order Now
                                    <svg class="tw-w-4 tw-h-4 tw-ml-1 tw-transition-transform group-hover:tw-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="tw-text-center tw-py-12">
                <div class="tw-text-gray-400 tw-mb-4">
                    <svg class="tw-w-16 tw-h-16 tw-mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <h3 class="tw-text-xl tw-font-semibold tw-text-gray-700 tw-mb-2">No Restaurants Available</h3>
                <p class="tw-text-gray-600 tw-text-lg">We're working on adding more restaurants to serve you better.</p>
            </div>
            <?php endif; ?>

            <!-- View All Button -->
            <div class="tw-text-center">
                <a href="<?= url('/browse') ?>" class="tw-inline-flex tw-items-center tw-justify-center tw-bg-gray-900 tw-text-white tw-px-8 tw-py-4 tw-rounded-2xl tw-text-base sm:tw-text-lg tw-font-bold hover:tw-bg-gray-800 tw-transition-all tw-duration-300 tw-shadow-lg hover:tw-shadow-xl hover:tw-scale-105">
                    View All Restaurants
                    <svg class="tw-w-5 tw-h-5 tw-ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Popular Dishes Section -->
    <?php if (!empty($popular_dishes)): ?>
    <section class="tw-py-16 sm:tw-py-20 tw-bg-gradient-to-b tw-from-gray-50 tw-to-white">
        <div class="tw-container tw-mx-auto tw-px-4">
            <div class="tw-text-center tw-mb-12 sm:tw-mb-16">
                <span class="tw-inline-block tw-bg-green-100 tw-text-green-600 tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-bold tw-mb-4">
                    TRENDING NOW
                </span>
                <h2 class="tw-text-3xl sm:tw-text-4xl md:tw-text-5xl tw-font-black tw-text-gray-900 tw-mb-4">
                    Popular Dishes
                </h2>
                <p class="tw-text-base sm:tw-text-lg md:tw-text-xl tw-text-gray-600 tw-max-w-2xl tw-mx-auto tw-px-4">
                    Most ordered dishes this month
                </p>
            </div>

            <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-max-w-7xl tw-mx-auto">
                <?php foreach (array_slice($popular_dishes, 0, 8) as $dish): ?>
                <div class="tw-group tw-bg-white tw-rounded-2xl tw-shadow-md tw-overflow-hidden tw-border tw-border-gray-100 tw-transition-all tw-duration-300 hover:tw-shadow-xl hover:-tw-translate-y-1">
                    <!-- Dish Image -->
                    <div class="tw-relative tw-h-40 sm:tw-h-48 tw-overflow-hidden tw-bg-gray-100">
                        <img
                            src="<?= e($dish['image']) ?>"
                            alt="<?= e($dish['name']) ?>"
                            class="tw-w-full tw-h-full tw-object-cover tw-transition-transform tw-duration-500 group-hover:tw-scale-110"
                            loading="lazy"
                            onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&h=400&fit=crop'"
                        >
                        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-t tw-from-black/30 tw-to-transparent"></div>

                        <!-- Price Badge -->
                        <div class="tw-absolute tw-bottom-3 tw-left-3">
                            <span class="tw-bg-white tw-text-gray-900 tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-bold tw-shadow-lg">
                                <?= number_format($dish['price']) ?> FCFA
                            </span>
                        </div>
                    </div>

                    <!-- Dish Info -->
                    <div class="tw-p-4">
                        <h3 class="tw-text-base sm:tw-text-lg tw-font-bold tw-text-gray-900 tw-mb-1 tw-line-clamp-1">
                            <?= e($dish['name']) ?>
                        </h3>
                        <p class="tw-text-xs sm:tw-text-sm tw-text-gray-500 tw-mb-2">
                            <?= e($dish['restaurant_name']) ?>
                        </p>
                        <div class="tw-flex tw-items-center tw-justify-between">
                            <div class="tw-flex tw-items-center">
                                <svg class="tw-w-4 tw-h-4 tw-text-yellow-400 tw-mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <span class="tw-text-sm tw-font-semibold tw-text-gray-700"><?= number_format($dish['rating'], 1) ?></span>
                            </div>
                            <button 
                                class="tw-text-orange-600 hover:tw-text-orange-700 tw-font-medium tw-text-sm tw-transition-colors add-to-cart-btn"
                                data-menu-item-id="<?= $dish['id'] ?>"
                                data-dish-name="<?= e($dish['name']) ?>"
                                data-price="<?= $dish['price'] ?>"
                                data-restaurant-id="<?= $dish['restaurant_id'] ?>"
                                data-restaurant-name="<?= e($dish['restaurant_name']) ?>"
                            >
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA Section -->
    <section class="tw-py-16 sm:tw-py-20 tw-bg-gradient-to-br tw-from-orange-500 tw-via-red-600 tw-to-pink-600 tw-relative tw-overflow-hidden">
        <!-- Background Pattern -->
        <div class="tw-absolute tw-inset-0 tw-opacity-10">
            <div class="tw-absolute tw-inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>

        <div class="tw-container tw-mx-auto tw-px-4 tw-relative tw-z-10">
            <div class="tw-max-w-4xl tw-mx-auto tw-text-center">
                <h2 class="tw-text-3xl sm:tw-text-4xl md:tw-text-5xl tw-font-black tw-text-white tw-mb-6">
                    Ready to Order?
                </h2>
                <p class="tw-text-lg sm:tw-text-xl tw-text-white/90 tw-mb-10 tw-max-w-2xl tw-mx-auto tw-leading-relaxed tw-px-4">
                    Join thousands of satisfied customers in Bamenda. Your next delicious meal is just a few clicks away!
                </p>

                <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-4 tw-justify-center tw-mb-12">
                    <a href="<?= url('/browse') ?>" class="tw-inline-flex tw-items-center tw-justify-center tw-bg-white tw-text-gray-900 tw-px-8 sm:tw-px-10 tw-py-4 sm:tw-py-5 tw-rounded-2xl tw-text-base sm:tw-text-lg tw-font-bold tw-shadow-2xl hover:tw-bg-gray-100 tw-transition-all tw-duration-300 hover:tw-scale-105">
                        <svg class="tw-w-5 tw-h-5 sm:tw-w-6 tw-h-6 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Start Ordering Now
                    </a>
                    <a href="<?= url('/about') ?>" class="tw-inline-flex tw-items-center tw-justify-center tw-bg-white/10 tw-backdrop-blur-md tw-border-2 tw-border-white/30 tw-text-white tw-px-8 sm:tw-px-10 tw-py-4 sm:tw-py-5 tw-rounded-2xl tw-text-base sm:tw-text-lg tw-font-bold hover:tw-bg-white/20 tw-transition-all tw-duration-300">
                        <svg class="tw-w-5 tw-h-5 sm:tw-w-6 tw-h-6 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Learn More
                    </a>
                </div>

                <!-- App Features -->
                <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4 tw-gap-4 sm:tw-gap-6 tw-max-w-3xl tw-mx-auto">
                    <div class="tw-text-center">
                        <div class="tw-w-12 tw-h-12 sm:tw-w-16 sm:tw-h-16 tw-bg-white/20 tw-backdrop-blur-sm tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-3">
                            <svg class="tw-w-6 tw-h-6 sm:tw-w-8 sm:tw-h-8 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="tw-text-white tw-font-bold tw-text-sm sm:tw-text-base tw-mb-1">Fast Delivery</h3>
                        <p class="tw-text-white/80 tw-text-xs">25 min avg</p>
                    </div>
                    <div class="tw-text-center">
                        <div class="tw-w-12 tw-h-12 sm:tw-w-16 sm:tw-h-16 tw-bg-white/20 tw-backdrop-blur-sm tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-3">
                            <svg class="tw-w-6 tw-h-6 sm:tw-w-8 sm:tw-h-8 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h3 class="tw-text-white tw-font-bold tw-text-sm sm:tw-text-base tw-mb-1">Safe & Secure</h3>
                        <p class="tw-text-white/80 tw-text-xs">100% verified</p>
                    </div>
                    <div class="tw-text-center">
                        <div class="tw-w-12 tw-h-12 sm:tw-w-16 sm:tw-h-16 tw-bg-white/20 tw-backdrop-blur-sm tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-3">
                            <svg class="tw-w-6 tw-h-6 sm:tw-w-8 sm:tw-h-8 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="tw-text-white tw-font-bold tw-text-sm sm:tw-text-base tw-mb-1">Easy Payment</h3>
                        <p class="tw-text-white/80 tw-text-xs">Multiple options</p>
                    </div>
                    <div class="tw-text-center">
                        <div class="tw-w-12 tw-h-12 sm:tw-w-16 sm:tw-h-16 tw-bg-white/20 tw-backdrop-blur-sm tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-3">
                            <svg class="tw-w-6 tw-h-6 sm:tw-w-8 sm:tw-h-8 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <h3 class="tw-text-white tw-font-bold tw-text-sm sm:tw-text-base tw-mb-1">24/7 Support</h3>
                        <p class="tw-text-white/80 tw-text-xs">Always here</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Feather Icons if available
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Add to Cart functionality for popular dishes
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Check if user is logged in
            const isLoggedIn = <?= isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'customer' ? 'true' : 'false' ?>;
            
            if (!isLoggedIn) {
                // Redirect to login page
                window.location.href = '<?= url('/login') ?>';
                return;
            }
            
            const menuItemId = this.getAttribute('data-menu-item-id');
            const dishName = this.getAttribute('data-dish-name');
            const price = this.getAttribute('data-price');
            const restaurantId = this.getAttribute('data-restaurant-id');
            const restaurantName = this.getAttribute('data-restaurant-name');
            
            // Disable button and show loading state
            const originalText = this.textContent;
            this.textContent = 'Adding...';
            this.disabled = true;
            this.classList.add('tw-opacity-50');
            
            // Prepare data for API
            const cartData = {
                menu_item_id: parseInt(menuItemId),
                quantity: 1,
                customizations: [],
                special_instructions: ''
            };
            
            // Make API call to add item to cart
            fetch('<?= url('/api/cart/add.php') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(cartData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showNotification(`${dishName} added to cart!`, 'success');
                    
                    // Update cart count if cart count element exists
                    updateCartCount();
                    
                    // Optional: Update button to show "Added" state temporarily
                    this.textContent = 'Added!';
                    this.classList.remove('tw-text-orange-600', 'hover:tw-text-orange-700');
                    this.classList.add('tw-text-green-600');
                    
                    // Reset button after 2 seconds
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.disabled = false;
                        this.classList.remove('tw-opacity-50', 'tw-text-green-600');
                        this.classList.add('tw-text-orange-600', 'hover:tw-text-orange-700');
                    }, 2000);
                } else {
                    // Show error message
                    showNotification(data.message || 'Failed to add item to cart', 'error');
                    
                    // Reset button
                    this.textContent = originalText;
                    this.disabled = false;
                    this.classList.remove('tw-opacity-50');
                }
            })
            .catch(error => {
                console.error('Error adding to cart:', error);
                showNotification('Network error. Please try again.', 'error');
                
                // Reset button
                this.textContent = originalText;
                this.disabled = false;
                this.classList.remove('tw-opacity-50');
            });
        });
    });
    
    // Function to show notifications
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `tw-fixed tw-top-4 tw-right-4 tw-px-6 tw-py-3 tw-rounded-lg tw-text-white tw-font-medium tw-shadow-lg tw-z-50 tw-transition-all tw-duration-300 tw-transform tw-translate-x-full`;
        
        // Set background color based on type
        if (type === 'success') {
            notification.classList.add('tw-bg-green-500');
        } else if (type === 'error') {
            notification.classList.add('tw-bg-red-500');
        } else {
            notification.classList.add('tw-bg-blue-500');
        }
        
        notification.textContent = message;
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('tw-translate-x-full');
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.classList.add('tw-translate-x-full');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
    
    // Function to update cart count
    function updateCartCount() {
        const cartCountElement = document.querySelector('.cart-count');
        if (cartCountElement) {
            fetch('<?= url('/api/cart/count.php') ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        cartCountElement.textContent = data.count;
                        cartCountElement.style.display = data.count > 0 ? 'block' : 'none';
                    }
                })
                .catch(error => {
                    console.error('Error updating cart count:', error);
                });
        }
    }
});
</script>
