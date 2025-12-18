<?php
/**
 * Home Page View - Time2Eat Landing Page
 * Semantic HTML5 with mobile-first Tailwind CSS and PWA features
 * Enhanced with Cameroonian & Bamenda African Art
 */

// Include African patterns component
require_once __DIR__ . '/../components/african-patterns.php';
?>

<!-- PWA Install Banner -->
<div id="pwa-install-banner" class="tw-hidden tw-fixed tw-top-0 tw-left-0 tw-right-0 tw-z-50 tw-bg-primary-600 tw-text-white tw-p-4">
    <div class="tw-container tw-mx-auto tw-flex tw-items-center tw-justify-between">
        <div class="tw-flex tw-items-center tw-space-x-3">
            <i data-feather="smartphone" class="tw-w-5 tw-h-5"></i>
            <span class="tw-text-sm tw-font-medium">Install Time2Eat for faster access!</span>
        </div>
        <div class="tw-flex tw-items-center tw-space-x-2">
            <button id="install-pwa-btn" class="tw-bg-white tw-text-primary-600 tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-gray-100 tw-transition-colors">
                Install
            </button>
            <button id="dismiss-pwa-banner" class="tw-text-white hover:tw-text-gray-200 tw-p-1">
                <i data-feather="x" class="tw-w-4 tw-h-4"></i>
            </button>
        </div>
    </div>
</div>

<!-- Hero Section with Cameroonian Art -->
<section class="tw-relative tw-min-h-screen tw-flex tw-items-center tw-justify-center tw-overflow-hidden" role="banner" aria-label="Hero section">
    <!-- Background Image with Lazy Loading -->
    <div class="tw-absolute tw-inset-0 tw-z-0">
        <img
            src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 800'%3E%3Crect fill='%23dc2626' width='1200' height='800'/%3E%3C/svg%3E"
            data-src="<?= url('public/images/hero.webp') ?>"
            alt="Delicious Cameroonian food delivery - ndolé, eru, and local dishes"
            class="tw-w-full tw-h-full tw-object-cover lazy-load"
            loading="lazy"
        >
        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-br tw-from-black/60 tw-to-black/40"></div>
        <!-- Ndop Pattern Overlay -->
        <div class="tw-absolute tw-inset-0 african-pattern-ndop tw-opacity-10"></div>
    </div>

    <!-- African Art Decorative Corners -->
    <div class="tw-absolute tw-inset-0 tw-z-10" aria-hidden="true">
        <div class="african-corner-tl"></div>
        <div class="african-corner-tr"></div>
        <div class="african-corner-bl"></div>
        <div class="african-corner-br"></div>
    </div>

    <!-- Floating African Symbols -->
    <div class="tw-absolute tw-inset-0 tw-z-10" aria-hidden="true">
        <!-- Spider Symbol (Wisdom) -->
        <div class="tw-absolute tw-top-20 tw-left-10 tw-w-24 tw-h-24 tw-text-orange-500 tw-opacity-20 african-symbol-float">
            <svg class="tw-w-full tw-h-full"><use href="#african-spider"/></svg>
        </div>
        <!-- Double Gong (Royalty) -->
        <div class="tw-absolute tw-top-40 tw-right-20 tw-w-20 tw-h-20 tw-text-yellow-500 tw-opacity-20 african-symbol-float" style="animation-delay: 1s;">
            <svg class="tw-w-full tw-h-full"><use href="#african-gong"/></svg>
        </div>
        <!-- Diamond Pattern -->
        <div class="tw-absolute tw-bottom-32 tw-left-1/4 tw-w-16 tw-h-16 tw-text-red-500 tw-opacity-20 african-symbol-float" style="animation-delay: 2s;">
            <svg class="tw-w-full tw-h-full"><use href="#african-diamond"/></svg>
        </div>
        <!-- Frog Symbol (Fertility) -->
        <div class="tw-absolute tw-bottom-20 tw-right-1/3 tw-w-24 tw-h-24 tw-text-green-500 tw-opacity-20 african-symbol-float" style="animation-delay: 0.5s;">
            <svg class="tw-w-full tw-h-full"><use href="#african-frog"/></svg>
        </div>
    </div>

    <!-- Hero Content -->
    <header class="tw-relative tw-z-20 tw-container tw-mx-auto tw-px-4 tw-text-center">
        <div class="tw-max-w-4xl tw-mx-auto">
            <!-- Main Heading -->
            <h1 class="tw-text-4xl md:tw-text-6xl lg:tw-text-7xl tw-font-bold tw-text-white tw-mb-6 tw-animate-fade-in">
                <span class="tw-bg-gradient-to-r tw-from-primary-400 tw-to-secondary-400 tw-bg-clip-text tw-text-transparent">
                    Time2Eat
                </span>
                <br>
                <span class="tw-text-3xl md:tw-text-5xl lg:tw-text-6xl">
                    Bamenda's #1 Food Delivery
                </span>
            </h1>

            <!-- Subtitle -->
            <p class="tw-text-xl md:tw-text-2xl tw-text-gray-200 tw-mb-8 tw-animate-slide-up" style="animation-delay: 0.3s;">
                Order from your favorite local restaurants with real-time tracking
            </p>

            <!-- Search Form -->
            <div class="tw-max-w-2xl tw-mx-auto tw-mb-8 tw-animate-slide-up" style="animation-delay: 0.6s;">
                <div class="tw-glass-card tw-p-6 tw-rounded-2xl">
                    <form action="<?= url('/browse') ?>" method="GET" class="tw-flex tw-flex-col md:tw-flex-row tw-gap-4" role="search" aria-label="Search restaurants and dishes">
                        <div class="tw-flex-1">
                            <label for="hero-search" class="tw-sr-only">Search for restaurants or dishes</label>
                            <input
                                id="hero-search"
                                type="search"
                                name="search"
                                placeholder="Search for restaurants or dishes..."
                                class="tw-w-full tw-px-6 tw-py-4 tw-rounded-xl tw-border-0 tw-bg-white/20 tw-backdrop-blur-sm tw-text-white tw-placeholder-gray-300 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-bg-white/30 tw-transition-all tw-duration-200"
                                aria-describedby="search-help"
                            >
                            <div id="search-help" class="tw-sr-only">Search for restaurants, dishes, or cuisine types in Bamenda</div>
                        </div>
                        <button type="submit" class="tw-btn-primary tw-px-8 tw-py-4 tw-rounded-xl tw-font-semibold tw-text-lg tw-min-h-[56px]" aria-label="Search for food">
                            <i data-feather="search" class="tw-w-5 tw-h-5 tw-mr-2" aria-hidden="true"></i>
                            Find Food
                        </button>
                    </form>
                </div>
            </div>

            <!-- CTA Buttons -->
            <nav class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-4 tw-justify-center tw-animate-slide-up" style="animation-delay: 0.9s;" aria-label="Main actions">
                <a href="<?= url('/browse') ?>" class="tw-btn-primary tw-text-lg tw-px-8 tw-py-4 tw-min-h-[56px]" aria-label="Browse all restaurants in Bamenda">
                    <i data-feather="utensils" class="tw-w-5 tw-h-5 tw-mr-2" aria-hidden="true"></i>
                    Browse Restaurants
                </a>
                <a href="<?= url('/register') ?>" class="tw-btn-outline tw-text-lg tw-px-8 tw-py-4 tw-bg-white/10 tw-backdrop-blur-sm tw-border-white/30 tw-text-white hover:tw-bg-white hover:tw-text-gray-800 tw-min-h-[56px]" aria-label="Create a new Time2Eat account">
                    <i data-feather="user-plus" class="tw-w-5 tw-h-5 tw-mr-2" aria-hidden="true"></i>
                    Join Time2Eat
                </a>
            </nav>
        </div>
    </header>

    <!-- Platform Statistics -->
    <aside class="tw-absolute tw-bottom-8 tw-left-1/2 tw-transform -tw-translate-x-1/2 tw-z-20 tw-w-full tw-max-w-4xl tw-px-4" aria-label="Platform statistics">
        <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4 tw-gap-4">
            <div class="tw-glass-card tw-p-4 tw-text-center tw-animate-slide-up" style="animation-delay: 1.2s;">
                <div class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-white tw-mb-1" aria-label="<?= number_format($stats['restaurants'] ?? 0) ?> restaurants available">
                    <?= number_format($stats['restaurants'] ?? 0) ?>+
                </div>
                <div class="tw-text-sm tw-text-gray-300">Restaurants</div>
            </div>
            <div class="tw-glass-card tw-p-4 tw-text-center tw-animate-slide-up" style="animation-delay: 1.4s;">
                <div class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-white tw-mb-1" aria-label="<?= number_format($stats['orders'] ?? 0) ?> orders completed">
                    <?= number_format($stats['orders'] ?? 0) ?>+
                </div>
                <div class="tw-text-sm tw-text-gray-300">Orders</div>
            </div>
            <div class="tw-glass-card tw-p-4 tw-text-center tw-animate-slide-up" style="animation-delay: 1.6s;">
                <div class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-white tw-mb-1" aria-label="<?= number_format($stats['customers'] ?? 0) ?> happy customers">
                    <?= number_format($stats['customers'] ?? 0) ?>+
                </div>
                <div class="tw-text-sm tw-text-gray-300">Happy Customers</div>
            </div>
            <div class="tw-glass-card tw-p-4 tw-text-center tw-animate-slide-up" style="animation-delay: 1.8s;">
                <div class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-white tw-mb-1" aria-label="<?= $stats['cities'] ?? 1 ?> city served">
                    <?= $stats['cities'] ?? 1 ?>
                </div>
                <div class="tw-text-sm tw-text-gray-300">City Served</div>
            </div>
        </div>
    </aside>

    <!-- Scroll Indicator -->
    <div class="tw-absolute tw-bottom-4 tw-left-1/2 tw-transform -tw-translate-x-1/2 tw-z-20 tw-animate-bounce" aria-hidden="true">
        <i data-feather="chevron-down" class="tw-w-8 tw-h-8 tw-text-white tw-opacity-70"></i>
    </div>
</section>

<!-- Popular Categories Section with Toghu Border -->
<section class="tw-py-20 tw-bg-gray-50 tw-relative" aria-labelledby="categories-heading">
    <!-- Toghu-inspired Top Border -->
    <div class="tw-absolute tw-top-0 tw-left-0 tw-right-0 tw-h-1 african-pattern-flag"></div>

    <div class="tw-container tw-mx-auto tw-px-4">
        <header class="tw-text-center tw-mb-16 tw-relative">
            <!-- Zigzag Decoration -->
            <div class="tw-absolute tw-top-0 tw-left-1/2 tw-transform -tw-translate-x-1/2 -tw-translate-y-8 tw-w-32 tw-h-8 tw-text-orange-500 tw-opacity-30">
                <svg class="tw-w-full tw-h-full"><use href="#african-zigzag"/></svg>
            </div>
            <h2 id="categories-heading" class="tw-text-4xl tw-font-bold tw-text-gray-800 tw-mb-4">Popular Categories</h2>
            <p class="tw-text-xl tw-text-gray-600">Discover your favorite Cameroonian cuisine</p>
        </header>

        <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4 lg:tw-grid-cols-6 tw-gap-6" role="list">
            <?php
            // Default categories if none from database
            $default_categories = [
                ['id' => 1, 'name' => 'Ndolé', 'restaurant_count' => 12, 'icon' => 'utensils'],
                ['id' => 2, 'name' => 'Eru', 'restaurant_count' => 8, 'icon' => 'coffee'],
                ['id' => 3, 'name' => 'Jollof Rice', 'restaurant_count' => 15, 'icon' => 'utensils'],
                ['id' => 4, 'name' => 'Suya', 'restaurant_count' => 6, 'icon' => 'utensils'],
                ['id' => 5, 'name' => 'Plantain', 'restaurant_count' => 20, 'icon' => 'utensils'],
                ['id' => 6, 'name' => 'Fish & Chips', 'restaurant_count' => 10, 'icon' => 'utensils']
            ];
            $categories_to_show = !empty($popular_categories) ? $popular_categories : $default_categories;
            ?>
            <?php foreach ($categories_to_show as $category): ?>
                <article class="tw-group" role="listitem">
                    <a href="<?= url('/browse?category=' . $category['id']) ?>"
                       class="tw-block tw-focus-visible:tw-outline-2 tw-focus-visible:tw-outline-primary-500 tw-rounded-xl"
                       aria-label="Browse <?= e($category['name']) ?> restaurants">
                        <div class="tw-card tw-text-center tw-p-6 tw-transition-all tw-duration-300 group-hover:tw-scale-105 group-hover:tw-shadow-2xl tw-border-2 tw-border-transparent group-hover:tw-border-primary-200">
                            <div class="tw-w-16 tw-h-16 tw-mx-auto tw-mb-4 tw-bg-gradient-to-br tw-from-primary-500 tw-to-secondary-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-shadow-lg">
                                <i data-feather="<?= $category['icon'] ?? 'utensils' ?>" class="tw-w-8 tw-h-8 tw-text-white" aria-hidden="true"></i>
                            </div>
                            <h3 class="tw-font-semibold tw-text-gray-800 tw-mb-2 tw-text-lg"><?= e($category['name']) ?></h3>
                            <p class="tw-text-sm tw-text-gray-600"><?= $category['restaurant_count'] ?> restaurants</p>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Restaurants Section -->
<section class="tw-py-16 lg:tw-py-20 tw-bg-white" aria-labelledby="featured-heading">
    <div class="tw-container tw-mx-auto tw-px-4">
        <header class="tw-text-center tw-mb-12 lg:tw-mb-16">
            <h2 id="featured-heading" class="tw-text-3xl md:tw-text-4xl tw-font-bold tw-text-gray-800 tw-mb-4">Featured Restaurants</h2>
            <p class="tw-text-lg md:tw-text-xl tw-text-gray-600 tw-max-w-2xl tw-mx-auto">Top-rated restaurants in Bamenda delivering authentic flavors to your doorstep</p>
        </header>

        <!-- Mobile Carousel Container -->
        <div class="tw-relative tw-overflow-hidden">
            <!-- Carousel Wrapper -->
            <div id="featured-carousel" class="tw-flex tw-transition-transform tw-duration-300 tw-ease-in-out md:tw-grid md:tw-grid-cols-2 lg:tw-grid-cols-3 xl:tw-grid-cols-4 md:tw-gap-6 lg:tw-gap-8" role="list">
                <?php
                // Default featured restaurants if none from database
                $default_restaurants = [
                    [
                        'id' => 1,
                        'name' => 'Mama\'s Kitchen',
                        'description' => 'Authentic Cameroonian dishes made with love',
                        'rating' => 4.8,
                        'delivery_time' => '25-35',
                        'image' => url('public/images/fallback-food.jpg'),
                        'cuisine_type' => 'Cameroonian',
                        'is_open' => true,
                        'total_reviews' => 156,
                        'delivery_fee' => 500
                    ],
                [
                    'id' => 2,
                    'name' => 'Spice Garden',
                    'description' => 'Fresh ingredients, bold flavors, fast delivery',
                    'rating' => 4.6,
                    'delivery_time' => '20-30',
                    'image' => url('public/images/fallback-food.jpg'),
                    'cuisine_type' => 'Continental',
                    'is_open' => true
                ],
                [
                    'id' => 3,
                    'name' => 'Bamenda Bites',
                    'description' => 'Local favorites and international cuisine',
                    'rating' => 4.7,
                    'delivery_time' => '30-40',
                    'image' => url('public/images/fallback-food.jpg'),
                    'cuisine_type' => 'Mixed',
                    'is_open' => false
                ]
            ];
            $restaurants_to_show = !empty($featured_restaurants) ? $featured_restaurants : $default_restaurants;
            ?>
            <?php foreach ($restaurants_to_show as $restaurant): ?>
                <article class="tw-restaurant-card tw-group" role="listitem">
                    <div class="tw-card tw-overflow-hidden tw-transition-all tw-duration-300 group-hover:tw-scale-105 group-hover:tw-shadow-2xl tw-border-2 tw-border-transparent group-hover:tw-border-primary-200">
                        <!-- Restaurant Image -->
                        <div class="tw-relative tw-h-48 tw-bg-gray-200 tw-overflow-hidden">
                            <?php
                            $restaurantImage = imageUrl($restaurant['image'] ?? null, url('public/images/fallback-food.jpg'));
                            ?>
                            <img
                                src="<?= e($restaurantImage) ?>"
                                alt="<?= e($restaurant['name']) ?> restaurant interior and food"
                                class="tw-w-full tw-h-full tw-object-cover group-hover:tw-scale-110 tw-transition-transform tw-duration-500 tw-bg-gray-200"
                                loading="lazy"
                                onerror="this.src='<?= url('public/images/fallback-food.jpg') ?>'; this.onerror=null;"
                                style="min-height: 192px;"
                            >

                            <!-- Status Badge -->
                            <div class="tw-absolute tw-top-4 tw-left-4">
                                <?php if ($restaurant['is_open'] ?? true): ?>
                                    <span class="tw-bg-green-500 tw-text-white tw-px-3 tw-py-1 tw-rounded-full tw-text-xs tw-font-semibold tw-shadow-lg">
                                        <i data-feather="clock" class="tw-w-3 tw-h-3 tw-mr-1 tw-inline" aria-hidden="true"></i>
                                        Open
                                    </span>
                                <?php else: ?>
                                    <span class="tw-bg-red-500 tw-text-white tw-px-3 tw-py-1 tw-rounded-full tw-text-xs tw-font-semibold tw-shadow-lg">
                                        <i data-feather="clock" class="tw-w-3 tw-h-3 tw-mr-1 tw-inline" aria-hidden="true"></i>
                                        Closed
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Rating Badge -->
                            <div class="tw-absolute tw-top-4 tw-right-4 tw-bg-white/90 tw-backdrop-blur-sm tw-rounded-full tw-px-3 tw-py-1 tw-flex tw-items-center tw-space-x-1 tw-shadow-lg">
                                <i data-feather="star" class="tw-w-4 tw-h-4 tw-text-yellow-400 tw-fill-current" aria-hidden="true"></i>
                                <span class="tw-text-sm tw-font-semibold" aria-label="Rating: <?= number_format($restaurant['rating'] ?? 4.5, 1) ?> out of 5 stars">
                                    <?= number_format($restaurant['rating'] ?? 4.5, 1) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Restaurant Details -->
                        <div class="tw-p-6">
                            <header class="tw-mb-4">
                                <h3 class="tw-text-xl tw-font-bold tw-text-gray-800 tw-mb-2"><?= e($restaurant['name']) ?></h3>
                                <p class="tw-text-primary-600 tw-font-medium tw-mb-2"><?= e($restaurant['cuisine_type'] ?? 'Cameroonian') ?></p>
                                <p class="tw-text-gray-700 tw-text-sm tw-leading-relaxed"><?= e(substr($restaurant['description'] ?? 'Delicious local cuisine made with fresh ingredients', 0, 80)) ?>...</p>
                            </header>

                            <!-- Restaurant Info -->
                            <div class="tw-flex tw-items-center tw-justify-between tw-mb-6 tw-text-sm tw-text-gray-600">
                                <div class="tw-flex tw-items-center tw-space-x-1" title="Delivery time">
                                    <i data-feather="clock" class="tw-w-4 tw-h-4" aria-hidden="true"></i>
                                    <span><?= $restaurant['delivery_time'] ?? '25-35' ?> min</span>
                                </div>
                                <div class="tw-flex tw-items-center tw-space-x-1" title="Delivery fee">
                                    <i data-feather="truck" class="tw-w-4 tw-h-4" aria-hidden="true"></i>
                                    <span>XAF <?= number_format($restaurant['delivery_fee'] ?? 500) ?></span>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <a
                                href="<?= url('/restaurant/' . $restaurant['id']) ?>"
                                class="tw-btn-primary tw-w-full tw-text-center tw-min-h-[48px] tw-flex tw-items-center tw-justify-center tw-font-semibold tw-transition-all tw-duration-200 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-ring-offset-2"
                                aria-label="View menu for <?= e($restaurant['name']) ?>"
                            >
                                <i data-feather="menu" class="tw-w-4 tw-h-4 tw-mr-2" aria-hidden="true"></i>
                                View Menu
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        
        <footer class="tw-text-center tw-mt-12">
            <a
                href="<?= url('/browse') ?>"
                class="tw-btn-outline tw-text-lg tw-px-8 tw-py-4 tw-min-h-[56px] tw-inline-flex tw-items-center tw-justify-center focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-ring-offset-2"
                aria-label="View all restaurants in Bamenda"
            >
                <i data-feather="utensils" class="tw-w-5 tw-h-5 tw-mr-2" aria-hidden="true"></i>
                View All Restaurants
            </a>
        </footer>
    </div>
</section>

<!-- How It Works Section -->
<section class="tw-py-20 tw-bg-gradient-to-br tw-from-primary-50 tw-to-secondary-50" aria-labelledby="how-it-works-heading">
    <div class="tw-container tw-mx-auto tw-px-4">
        <header class="tw-text-center tw-mb-16">
            <h2 id="how-it-works-heading" class="tw-text-4xl tw-font-bold tw-text-gray-800 tw-mb-4">How It Works</h2>
            <p class="tw-text-xl tw-text-gray-600">Get your favorite food delivered in 3 simple steps</p>
        </header>

        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-8 tw-max-w-5xl tw-mx-auto" role="list">
            <!-- Step 1: Browse & Choose -->
            <article class="tw-text-center tw-group" role="listitem">
                <div class="tw-w-20 tw-h-20 tw-mx-auto tw-mb-6 tw-bg-gradient-to-br tw-from-primary-500 tw-to-secondary-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-shadow-lg group-hover:tw-scale-110 tw-transition-transform tw-duration-300">
                    <i data-feather="search" class="tw-w-10 tw-h-10 tw-text-white" aria-hidden="true"></i>
                </div>
                <h3 class="tw-text-2xl tw-font-bold tw-text-gray-800 tw-mb-4">1. Browse & Choose</h3>
                <p class="tw-text-gray-600 tw-leading-relaxed">Browse through our wide selection of local restaurants and choose your favorite Cameroonian dishes or international cuisine.</p>
            </article>

            <!-- Step 2: Order & Pay -->
            <article class="tw-text-center tw-group" role="listitem">
                <div class="tw-w-20 tw-h-20 tw-mx-auto tw-mb-6 tw-bg-gradient-to-br tw-from-primary-500 tw-to-secondary-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-shadow-lg group-hover:tw-scale-110 tw-transition-transform tw-duration-300">
                    <i data-feather="credit-card" class="tw-w-10 tw-h-10 tw-text-white" aria-hidden="true"></i>
                </div>
                <h3 class="tw-text-2xl tw-font-bold tw-text-gray-800 tw-mb-4">2. Order & Pay</h3>
                <p class="tw-text-gray-600 tw-leading-relaxed">Place your order and pay securely using Mobile Money, Orange Money, or international payment cards.</p>
            </article>

            <!-- Step 3: Track & Enjoy -->
            <article class="tw-text-center tw-group" role="listitem">
                <div class="tw-w-20 tw-h-20 tw-mx-auto tw-mb-6 tw-bg-gradient-to-br tw-from-primary-500 tw-to-secondary-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-shadow-lg group-hover:tw-scale-110 tw-transition-transform tw-duration-300">
                    <i data-feather="truck" class="tw-w-10 tw-h-10 tw-text-white" aria-hidden="true"></i>
                </div>
                <h3 class="tw-text-2xl tw-font-bold tw-text-gray-800 tw-mb-4">3. Track & Enjoy</h3>
                <p class="tw-text-gray-600 tw-leading-relaxed">Track your order in real-time with live updates and enjoy your delicious meal when it arrives at your doorstep.</p>
            </article>
        </div>

        <!-- Additional Features -->
        <div class="tw-mt-16 tw-text-center">
            <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4 tw-gap-6 tw-max-w-4xl tw-mx-auto">
                <div class="tw-flex tw-flex-col tw-items-center tw-space-y-2">
                    <i data-feather="clock" class="tw-w-8 tw-h-8 tw-text-primary-600" aria-hidden="true"></i>
                    <span class="tw-text-sm tw-font-medium tw-text-gray-700">Fast Delivery</span>
                </div>
                <div class="tw-flex tw-flex-col tw-items-center tw-space-y-2">
                    <i data-feather="shield-check" class="tw-w-8 tw-h-8 tw-text-primary-600" aria-hidden="true"></i>
                    <span class="tw-text-sm tw-font-medium tw-text-gray-700">Secure Payment</span>
                </div>
                <div class="tw-flex tw-flex-col tw-items-center tw-space-y-2">
                    <i data-feather="map-pin" class="tw-w-8 tw-h-8 tw-text-primary-600" aria-hidden="true"></i>
                    <span class="tw-text-sm tw-font-medium tw-text-gray-700">Live Tracking</span>
                </div>
                <div class="tw-flex tw-flex-col tw-items-center tw-space-y-2">
                    <i data-feather="headphones" class="tw-w-8 tw-h-8 tw-text-primary-600" aria-hidden="true"></i>
                    <span class="tw-text-sm tw-font-medium tw-text-gray-700">24/7 Support</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PWA Download Section -->
<section class="tw-py-20 tw-bg-gradient-to-r tw-from-primary-600 tw-to-secondary-600 tw-relative tw-overflow-hidden" aria-labelledby="download-heading">
    <!-- Background Pattern -->
    <div class="tw-absolute tw-inset-0 tw-opacity-10" aria-hidden="true">
        <div class="tw-absolute tw-top-10 tw-left-10 tw-w-32 tw-h-32 tw-border-2 tw-border-white tw-rounded-full"></div>
        <div class="tw-absolute tw-top-20 tw-right-20 tw-w-24 tw-h-24 tw-border-2 tw-border-white tw-rounded-full"></div>
        <div class="tw-absolute tw-bottom-20 tw-left-1/4 tw-w-20 tw-h-20 tw-border-2 tw-border-white tw-rounded-full"></div>
    </div>

    <div class="tw-container tw-mx-auto tw-px-4 tw-text-center tw-relative tw-z-10">
        <header class="tw-mb-12">
            <h2 id="download-heading" class="tw-text-4xl tw-font-bold tw-text-white tw-mb-4">Get the Time2Eat App</h2>
            <p class="tw-text-xl tw-text-white tw-opacity-90 tw-mb-2">Faster ordering, exclusive deals, and offline access</p>
            <p class="tw-text-lg tw-text-white tw-opacity-80">Join thousands of satisfied customers in Bamenda</p>
        </header>

        <!-- PWA Features -->
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6 tw-mb-12 tw-max-w-4xl tw-mx-auto">
            <div class="tw-flex tw-flex-col tw-items-center tw-space-y-3">
                <div class="tw-w-16 tw-h-16 tw-bg-white/20 tw-backdrop-blur-sm tw-rounded-full tw-flex tw-items-center tw-justify-center">
                    <i data-feather="zap" class="tw-w-8 tw-h-8 tw-text-white" aria-hidden="true"></i>
                </div>
                <h3 class="tw-text-lg tw-font-semibold tw-text-white">Lightning Fast</h3>
                <p class="tw-text-sm tw-text-white tw-opacity-80">Instant loading and smooth performance</p>
            </div>
            <div class="tw-flex tw-flex-col tw-items-center tw-space-y-3">
                <div class="tw-w-16 tw-h-16 tw-bg-white/20 tw-backdrop-blur-sm tw-rounded-full tw-flex tw-items-center tw-justify-center">
                    <i data-feather="wifi-off" class="tw-w-8 tw-h-8 tw-text-white" aria-hidden="true"></i>
                </div>
                <h3 class="tw-text-lg tw-font-semibold tw-text-white">Works Offline</h3>
                <p class="tw-text-sm tw-text-white tw-opacity-80">Browse menus even without internet</p>
            </div>
            <div class="tw-flex tw-flex-col tw-items-center tw-space-y-3">
                <div class="tw-w-16 tw-h-16 tw-bg-white/20 tw-backdrop-blur-sm tw-rounded-full tw-flex tw-items-center tw-justify-center">
                    <i data-feather="bell" class="tw-w-8 tw-h-8 tw-text-white" aria-hidden="true"></i>
                </div>
                <h3 class="tw-text-lg tw-font-semibold tw-text-white">Push Notifications</h3>
                <p class="tw-text-sm tw-text-white tw-opacity-80">Get real-time order updates</p>
            </div>
        </div>

        <!-- Install Buttons -->
        <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-4 tw-justify-center tw-items-center tw-mb-8">
            <!-- PWA Install Button -->
            <button
                id="install-app-btn"
                class="tw-bg-white tw-text-primary-600 tw-px-8 tw-py-4 tw-rounded-lg tw-font-semibold tw-text-lg tw-transition-all tw-duration-200 hover:tw-bg-gray-100 hover:tw-scale-105 tw-shadow-lg tw-min-h-[56px] tw-flex tw-items-center tw-justify-center tw-hidden"
                aria-label="Install Time2Eat app on your device"
            >
                <i data-feather="download" class="tw-w-5 tw-h-5 tw-mr-2" aria-hidden="true"></i>
                Add to Home Screen
            </button>

            <!-- Alternative Actions -->
            <a
                href="<?= url('/register') ?>"
                class="tw-bg-white tw-text-primary-600 tw-px-8 tw-py-4 tw-rounded-lg tw-font-semibold tw-text-lg tw-transition-all tw-duration-200 hover:tw-bg-gray-100 hover:tw-scale-105 tw-shadow-lg tw-min-h-[56px] tw-inline-flex tw-items-center tw-justify-center"
                aria-label="Create a new Time2Eat account"
            >
                <i data-feather="user-plus" class="tw-w-5 tw-h-5 tw-mr-2" aria-hidden="true"></i>
                Sign Up Now
            </a>

            <a
                href="<?= url('/browse') ?>"
                class="tw-border-2 tw-border-white tw-text-white tw-px-8 tw-py-4 tw-rounded-lg tw-font-semibold tw-text-lg tw-transition-all tw-duration-200 hover:tw-bg-white hover:tw-text-primary-600 hover:tw-scale-105 tw-min-h-[56px] tw-inline-flex tw-items-center tw-justify-center"
                aria-label="Browse all restaurants without signing up"
            >
                <i data-feather="utensils" class="tw-w-5 tw-h-5 tw-mr-2" aria-hidden="true"></i>
                Browse Restaurants
            </a>
        </div>

        <!-- App Info -->
        <div class="tw-text-center tw-text-white tw-opacity-80">
            <p class="tw-text-sm tw-mb-2">
                <i data-feather="smartphone" class="tw-w-4 tw-h-4 tw-inline tw-mr-1" aria-hidden="true"></i>
                Works on all devices - Android, iOS, and Desktop
            </p>
            <p class="tw-text-xs">
                No app store required • Instant updates • Secure & Private
            </p>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="tw-py-20 tw-bg-gray-50" aria-labelledby="testimonials-heading">
    <div class="tw-container tw-mx-auto tw-px-4">
        <header class="tw-text-center tw-mb-16">
            <h2 id="testimonials-heading" class="tw-text-4xl tw-font-bold tw-text-gray-800 tw-mb-4">What Our Customers Say</h2>
            <p class="tw-text-xl tw-text-gray-600">Real reviews from real customers in Bamenda</p>
        </header>

        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-8 tw-max-w-6xl tw-mx-auto">
            <!-- Testimonial 1 -->
            <article class="tw-card tw-p-6 tw-text-center">
                <div class="tw-flex tw-justify-center tw-mb-4">
                    <div class="tw-flex tw-space-x-1">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <i data-feather="star" class="tw-w-5 tw-h-5 tw-text-yellow-400 tw-fill-current" aria-hidden="true"></i>
                        <?php endfor; ?>
                    </div>
                </div>
                <blockquote class="tw-text-gray-700 tw-mb-6 tw-italic">
                    "Time2Eat has made ordering food so convenient! The delivery is always fast and the food arrives hot. My favorite is the ndolé from Mama's Kitchen."
                </blockquote>
                <footer>
                    <div class="tw-w-12 tw-h-12 tw-bg-primary-500 tw-rounded-full tw-mx-auto tw-mb-3 tw-flex tw-items-center tw-justify-center">
                        <span class="tw-text-white tw-font-bold">AM</span>
                    </div>
                    <cite class="tw-font-semibold tw-text-gray-800">Amina Mbah</cite>
                    <p class="tw-text-sm tw-text-gray-600">Regular Customer</p>
                </footer>
            </article>

            <!-- Testimonial 2 -->
            <article class="tw-card tw-p-6 tw-text-center">
                <div class="tw-flex tw-justify-center tw-mb-4">
                    <div class="tw-flex tw-space-x-1">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <i data-feather="star" class="tw-w-5 tw-h-5 tw-text-yellow-400 tw-fill-current" aria-hidden="true"></i>
                        <?php endfor; ?>
                    </div>
                </div>
                <blockquote class="tw-text-gray-700 tw-mb-6 tw-italic">
                    "As a busy student, Time2Eat is a lifesaver! I can track my order in real-time and the payment with Mobile Money is so easy. Highly recommended!"
                </blockquote>
                <footer>
                    <div class="tw-w-12 tw-h-12 tw-bg-secondary-500 tw-rounded-full tw-mx-auto tw-mb-3 tw-flex tw-items-center tw-justify-center">
                        <span class="tw-text-white tw-font-bold">JT</span>
                    </div>
                    <cite class="tw-font-semibold tw-text-gray-800">Jean Tabi</cite>
                    <p class="tw-text-sm tw-text-gray-600">University Student</p>
                </footer>
            </article>

            <!-- Testimonial 3 -->
            <article class="tw-card tw-p-6 tw-text-center">
                <div class="tw-flex tw-justify-center tw-mb-4">
                    <div class="tw-flex tw-space-x-1">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <i data-feather="star" class="tw-w-5 tw-h-5 tw-text-yellow-400 tw-fill-current" aria-hidden="true"></i>
                        <?php endfor; ?>
                    </div>
                </div>
                <blockquote class="tw-text-gray-700 tw-mb-6 tw-italic">
                    "The variety of restaurants is amazing! From local Cameroonian dishes to international cuisine, Time2Eat has it all. Great service and friendly delivery riders."
                </blockquote>
                <footer>
                    <div class="tw-w-12 tw-h-12 tw-bg-primary-600 tw-rounded-full tw-mx-auto tw-mb-3 tw-flex tw-items-center tw-justify-center">
                        <span class="tw-text-white tw-font-bold">GN</span>
                    </div>
                    <cite class="tw-font-semibold tw-text-gray-800">Grace Nkeng</cite>
                    <p class="tw-text-sm tw-text-gray-600">Food Enthusiast</p>
                </footer>
            </article>
        </div>
    </div>
</section>

<!-- Download Section -->
<section class="tw-py-20 tw-bg-gradient-to-br tw-from-gray-900 tw-to-gray-800">
    <div class="tw-container tw-mx-auto tw-px-4">
        <div class="tw-text-center tw-mb-12">
            <h2 class="tw-text-3xl tw-font-bold tw-text-white tw-mb-4">Get the Time2Eat App</h2>
            <p class="tw-text-xl tw-text-gray-300 tw-mb-8">Install our Progressive Web App for the best experience</p>
        </div>

        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-12 tw-items-center">
            <!-- App Features -->
            <div class="tw-space-y-6">
                <div class="tw-flex tw-items-start tw-space-x-4">
                    <div class="tw-flex-shrink-0 tw-w-12 tw-h-12 tw-bg-orange-500 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                        <i data-feather="zap" class="tw-w-6 tw-h-6 tw-text-white"></i>
                    </div>
                    <div>
                        <h3 class="tw-text-lg tw-font-semibold tw-text-white tw-mb-2">Lightning Fast</h3>
                        <p class="tw-text-gray-300">Instant loading with offline support. Browse menus even without internet.</p>
                    </div>
                </div>

                <div class="tw-flex tw-items-start tw-space-x-4">
                    <div class="tw-flex-shrink-0 tw-w-12 tw-h-12 tw-bg-blue-500 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                        <i data-feather="bell" class="tw-w-6 tw-h-6 tw-text-white"></i>
                    </div>
                    <div>
                        <h3 class="tw-text-lg tw-font-semibold tw-text-white tw-mb-2">Push Notifications</h3>
                        <p class="tw-text-gray-300">Get real-time updates on your order status and special offers.</p>
                    </div>
                </div>

                <div class="tw-flex tw-items-start tw-space-x-4">
                    <div class="tw-flex-shrink-0 tw-w-12 tw-h-12 tw-bg-green-500 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                        <i data-feather="map-pin" class="tw-w-6 tw-h-6 tw-text-white"></i>
                    </div>
                    <div>
                        <h3 class="tw-text-lg tw-font-semibold tw-text-white tw-mb-2">Live Tracking</h3>
                        <p class="tw-text-gray-300">Track your order in real-time with live map updates.</p>
                    </div>
                </div>

                <div class="tw-flex tw-items-start tw-space-x-4">
                    <div class="tw-flex-shrink-0 tw-w-12 tw-h-12 tw-bg-purple-500 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                        <i data-feather="smartphone" class="tw-w-6 tw-h-6 tw-text-white"></i>
                    </div>
                    <div>
                        <h3 class="tw-text-lg tw-font-semibold tw-text-white tw-mb-2">Native App Feel</h3>
                        <p class="tw-text-gray-300">Works like a native app with home screen installation.</p>
                    </div>
                </div>
            </div>

            <!-- Installation Options -->
            <div class="tw-bg-white tw-rounded-2xl tw-p-8 tw-shadow-2xl">
                <div class="tw-text-center tw-mb-8">
                    <div class="tw-w-20 tw-h-20 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                        <i data-feather="download" class="tw-w-10 tw-h-10 tw-text-white"></i>
                    </div>
                    <h3 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-2">Install Time2Eat</h3>
                    <p class="tw-text-gray-600">Choose your preferred installation method</p>
                </div>

                <!-- PWA Install Button -->
                <div class="pwa-install-section tw-mb-6">
                    <button class="pwa-install-button tw-w-full tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-text-white tw-py-4 tw-px-6 tw-rounded-xl tw-font-semibold tw-text-lg tw-flex tw-items-center tw-justify-center tw-space-x-3 tw-hover:tw-from-orange-600 tw-hover:tw-to-red-600 tw-transition-all tw-duration-300 tw-transform tw-hover:tw-scale-105" style="display: none;">
                        <i data-feather="plus-circle" class="tw-w-6 tw-h-6"></i>
                        <span>Add to Home Screen</span>
                    </button>
                </div>

                <!-- QR Code Section -->
                <div class="tw-border-t tw-pt-6">
                    <div class="tw-text-center tw-mb-4">
                        <h4 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2">Scan QR Code</h4>
                        <p class="tw-text-sm tw-text-gray-600">Scan with your phone camera to open Time2Eat</p>
                    </div>

                    <div class="tw-flex tw-justify-center tw-mb-4">
                        <div id="qr-code" class="tw-bg-white tw-p-4 tw-rounded-lg tw-shadow-inner"></div>
                    </div>

                    <div class="tw-text-center">
                        <button onclick="shareApp()" class="tw-text-orange-500 tw-hover:tw-text-orange-600 tw-font-medium tw-flex tw-items-center tw-justify-center tw-space-x-2 tw-mx-auto">
                            <i data-feather="share-2" class="tw-w-4 tw-h-4"></i>
                            <span>Share App</span>
                        </button>
                    </div>
                </div>

                <!-- Manual Installation Instructions -->
                <div class="tw-mt-6 tw-p-4 tw-bg-gray-50 tw-rounded-lg">
                    <h5 class="tw-font-semibold tw-text-gray-900 tw-mb-2">Manual Installation:</h5>
                    <div class="tw-text-sm tw-text-gray-600 tw-space-y-1">
                        <p><strong>Chrome/Edge:</strong> Click menu → "Install Time2Eat"</p>
                        <p><strong>Safari:</strong> Tap share → "Add to Home Screen"</p>
                        <p><strong>Firefox:</strong> Tap menu → "Install"</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Browser Support -->
        <div class="tw-mt-12 tw-text-center">
            <p class="tw-text-gray-400 tw-mb-4">Supported on all modern browsers</p>
            <div class="tw-flex tw-justify-center tw-space-x-6 tw-opacity-60">
                <div class="tw-text-center">
                    <div class="tw-w-8 tw-h-8 tw-bg-gray-600 tw-rounded tw-mx-auto tw-mb-1"></div>
                    <span class="tw-text-xs tw-text-gray-400">Chrome</span>
                </div>
                <div class="tw-text-center">
                    <div class="tw-w-8 tw-h-8 tw-bg-gray-600 tw-rounded tw-mx-auto tw-mb-1"></div>
                    <span class="tw-text-xs tw-text-gray-400">Safari</span>
                </div>
                <div class="tw-text-center">
                    <div class="tw-w-8 tw-h-8 tw-bg-gray-600 tw-rounded tw-mx-auto tw-mb-1"></div>
                    <span class="tw-text-xs tw-text-gray-400">Firefox</span>
                </div>
                <div class="tw-text-center">
                    <div class="tw-w-8 tw-h-8 tw-bg-gray-600 tw-rounded tw-mx-auto tw-mb-1"></div>
                    <span class="tw-text-xs tw-text-gray-400">Edge</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="tw-py-20 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500">
    <div class="tw-container tw-mx-auto tw-px-4 tw-text-center">
        <h2 class="tw-text-3xl tw-font-bold tw-text-white tw-mb-4">Ready to Order?</h2>
        <p class="tw-text-xl tw-text-orange-100 tw-mb-8">Join thousands of satisfied customers in Bamenda</p>
        <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-4 tw-justify-center">
            <a href="<?= url('/browse') ?>" class="tw-bg-white tw-text-orange-500 tw-px-8 tw-py-3 tw-rounded-lg tw-font-semibold tw-hover:tw-bg-orange-50 tw-transition-colors">
                Browse Restaurants
            </a>
            <a href="<?= url('/register') ?>" class="tw-bg-transparent tw-border-2 tw-border-white tw-text-white tw-px-8 tw-py-3 tw-rounded-lg tw-font-semibold tw-hover:tw-bg-white tw-hover:tw-text-orange-500 tw-transition-colors">
                Sign Up Now
            </a>
        </div>
    </div>
</section>
