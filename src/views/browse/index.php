<?php
/**
 * Browse Restaurants Page - Time2Eat
 * Professional Mobile-First Design with Advanced Filtering
 * Enhanced with Cameroonian & Bamenda African Art
 */

// Include African patterns component
require_once __DIR__ . '/../components/african-patterns.php';
?>

<!-- Hero Section with Search and African Art -->
<section class="tw-relative tw-bg-gradient-to-br tw-from-orange-500 tw-via-red-600 tw-to-pink-600 tw-py-12 sm:tw-py-16 md:tw-py-20 tw-overflow-hidden">
    <!-- Bamileke Geometric Pattern Background -->
    <div class="tw-absolute tw-inset-0 tw-opacity-10 african-pattern-geometric"></div>

    <!-- African Decorative Corners -->
    <div class="tw-absolute tw-inset-0" aria-hidden="true">
        <div class="african-corner-tl"></div>
        <div class="african-corner-tr"></div>
    </div>

    <div class="tw-container tw-mx-auto tw-px-4 tw-relative tw-z-10">
        <!-- Header -->
        <div class="tw-text-center tw-text-white tw-mb-8 sm:tw-mb-10">
            <h1 class="tw-text-3xl sm:tw-text-4xl md:tw-text-5xl lg:tw-text-6xl tw-font-black tw-mb-3 sm:tw-mb-4">
                <span class="tw-bg-gradient-to-r tw-from-yellow-300 tw-to-orange-200 tw-bg-clip-text tw-text-transparent tw-drop-shadow-lg">
                    Browse Restaurants
                </span>
            </h1>
            <p class="tw-text-base sm:tw-text-lg md:tw-text-xl tw-opacity-90 tw-max-w-2xl tw-mx-auto tw-px-4">
                Discover amazing food from Bamenda's finest restaurants
            </p>
            <?php if (!empty($total_restaurants)): ?>
            <p class="tw-mt-3 tw-text-sm sm:tw-text-base tw-font-semibold tw-opacity-80">
                <?= $total_restaurants ?> restaurant<?= $total_restaurants != 1 ? 's' : '' ?> available
            </p>
            <?php endif; ?>
        </div>

        <!-- Mobile-First Search Bar -->
        <div class="tw-max-w-4xl tw-mx-auto tw-mb-6">
            <form method="GET" action="<?= url('/browse') ?>" class="tw-relative">
                <div class="tw-relative">
                    <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-4 tw-flex tw-items-center tw-pointer-events-none">
                        <svg class="tw-w-5 tw-h-5 tw-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="<?= e($search ?? '') ?>"
                        placeholder="Search restaurants, dishes, cuisines..."
                        class="tw-w-full tw-pl-12 tw-pr-12 tw-py-4 tw-bg-white tw-border-2 tw-border-gray-200 tw-rounded-2xl tw-text-gray-900 tw-placeholder-gray-500 focus:tw-outline-none focus:tw-border-orange-500 focus:tw-ring-2 focus:tw-ring-orange-100 tw-transition-all tw-duration-200 tw-text-base tw-shadow-lg"
                        autocomplete="off"
                    >
                    <?php if (!empty($search)): ?>
                    <button
                        type="button"
                        onclick="window.location.href='<?= url('/browse') ?>'"
                        class="tw-absolute tw-inset-y-0 tw-right-0 tw-pr-4 tw-flex tw-items-center tw-text-gray-400 hover:tw-text-red-500 tw-transition-colors"
                        title="Clear search"
                    >
                        <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Mobile-First Filter & Sort Section -->
        <div class="tw-max-w-4xl tw-mx-auto">
            <form method="GET" action="<?= url('/browse') ?>" class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-border tw-border-gray-100 tw-p-4 sm:tw-p-6">
                <!-- Hidden search input to preserve search when filtering -->
                <?php if (!empty($search)): ?>
                <input type="hidden" name="search" value="<?= e($search) ?>">
                <?php endif; ?>

                <!-- Filter Header -->
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <h3 class="tw-text-lg tw-font-bold tw-text-gray-800 tw-flex tw-items-center">
                        <svg class="tw-w-5 tw-h-5 tw-mr-2 tw-text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                        </svg>
                        Filter & Sort
                    </h3>
                    <?php if (!empty($search) || !empty($category) || (!empty($sort) && $sort !== 'rating')): ?>
                    <a href="<?= url('/browse') ?>" class="tw-text-sm tw-font-medium tw-text-red-600 hover:tw-text-red-700 tw-transition-colors tw-flex tw-items-center tw-bg-red-50 tw-px-3 tw-py-1.5 tw-rounded-lg hover:tw-bg-red-100">
                        <svg class="tw-w-4 tw-h-4 tw-mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Clear
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Filter Controls -->
                <div class="tw-space-y-4">
                    <!-- Category Filter -->
                    <div>
                        <label for="category" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">
                            Category
                        </label>
                        <select
                            id="category"
                            name="category"
                            onchange="this.form.submit()"
                            class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-bg-white tw-text-gray-900 tw-text-sm focus:tw-outline-none focus:tw-border-orange-500 focus:tw-ring-2 focus:tw-ring-orange-100 tw-transition-all tw-appearance-none tw-cursor-pointer"
                        >
                            <option value="">All Categories</option>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat['id']) ?>" <?= ($category ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['icon'] ?? '🍽️') ?> <?= e($cat['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Sort Filter -->
                    <div>
                        <label for="sort" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">
                            Sort By
                        </label>
                        <select
                            id="sort"
                            name="sort"
                            onchange="this.form.submit()"
                            class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-bg-white tw-text-gray-900 tw-text-sm focus:tw-outline-none focus:tw-border-orange-500 focus:tw-ring-2 focus:tw-ring-orange-100 tw-transition-all tw-appearance-none tw-cursor-pointer"
                        >
                            <option value="rating" <?= ($sort ?? 'rating') === 'rating' ? 'selected' : '' ?>>⭐ Highest Rated</option>
                            <option value="popular" <?= ($sort ?? '') === 'popular' ? 'selected' : '' ?>>🔥 Most Popular</option>
                            <option value="delivery_time" <?= ($sort ?? '') === 'delivery_time' ? 'selected' : '' ?>>⚡ Fastest Delivery</option>
                            <option value="delivery_fee" <?= ($sort ?? '') === 'delivery_fee' ? 'selected' : '' ?>>💰 Lowest Fee</option>
                            <option value="newest" <?= ($sort ?? '') === 'newest' ? 'selected' : '' ?>>🆕 Newest</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Results Section -->
<section class="tw-py-8 sm:tw-py-12 tw-bg-gray-50">
    <div class="tw-container tw-mx-auto tw-px-4">
        <!-- Results Header -->
        <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between tw-items-start sm:tw-items-center tw-mb-6 sm:tw-mb-8 tw-gap-4">
            <div class="tw-flex-1">
                <h2 class="tw-text-2xl sm:tw-text-3xl tw-font-black tw-text-gray-900 tw-mb-2">
                    <?php if (!empty($search)): ?>
                        Search Results
                    <?php elseif (!empty($category)): ?>
                        <?php
                        $cat_name = 'Restaurants';
                        foreach ($categories as $cat) {
                            if ($cat['id'] == $category) {
                                $cat_name = $cat['name'];
                                break;
                            }
                        }
                        echo e($cat_name);
                        ?>
                    <?php else: ?>
                        All Restaurants
                    <?php endif; ?>
                </h2>
                <p class="tw-text-sm sm:tw-text-base tw-text-gray-600">
                    <?php if (!empty($total_restaurants)): ?>
                        <span class="tw-font-semibold tw-text-orange-600"><?= $total_restaurants ?></span> restaurant<?= $total_restaurants != 1 ? 's' : '' ?> in Bamenda
                    <?php else: ?>
                        No restaurants found
                    <?php endif; ?>
                </p>
            </div>

            <!-- View Toggle (Desktop Only) -->
            <div class="tw-hidden sm:tw-flex tw-items-center tw-gap-2 tw-bg-white tw-rounded-xl tw-p-1 tw-shadow-md">
                <button
                    id="grid-view-btn"
                    class="tw-p-2 tw-rounded-lg tw-bg-orange-500 tw-text-white tw-transition-all"
                    aria-label="Grid view"
                >
                    <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                </button>
                <button
                    id="list-view-btn"
                    class="tw-p-2 tw-rounded-lg tw-bg-gray-100 tw-text-gray-600 hover:tw-bg-gray-200 tw-transition-all"
                    aria-label="List view"
                >
                    <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Restaurant Grid -->
        <div id="restaurants-grid" class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-4 sm:tw-gap-6 lg:tw-gap-8">
            <?php if (!empty($restaurants)): ?>
                <?php foreach ($restaurants as $restaurant): ?>
                    <a href="<?= url('/browse/restaurant/' . $restaurant['id']) ?>" class="tw-group tw-block">
                        <article class="tw-bg-white tw-rounded-2xl tw-shadow-md tw-overflow-hidden tw-border tw-border-gray-100 tw-transition-all tw-duration-300 hover:tw-shadow-2xl hover:-tw-translate-y-2 hover:tw-border-orange-200">
                            <!-- Restaurant Image -->
                            <div class="tw-relative tw-h-44 sm:tw-h-52 tw-overflow-hidden tw-bg-gradient-to-br tw-from-gray-100 tw-to-gray-200">
                                <?php
                                $restaurantImage = imageUrl($restaurant['image'] ?? null, 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&h=600&fit=crop&q=80');
                                ?>
                                <img
                                    src="<?= e($restaurantImage) ?>"
                                    alt="<?= e($restaurant['name']) ?>"
                                    class="tw-w-full tw-h-full tw-object-cover tw-transition-transform tw-duration-500 group-hover:tw-scale-110"
                                    loading="lazy"
                                    onerror="this.src='https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&h=600&fit=crop&q=80'"
                                >
                                <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-t tw-from-black/40 tw-via-transparent tw-to-transparent"></div>

                                <!-- Badges -->
                                <div class="tw-absolute tw-top-3 tw-left-3 tw-right-3 tw-flex tw-justify-between tw-items-start tw-gap-2">
                                    <!-- Status Badge -->
                                    <?php if (!empty($restaurant['is_open'])): ?>
                                        <span class="tw-bg-green-500 tw-text-white tw-px-2.5 tw-py-1 tw-rounded-full tw-text-xs tw-font-bold tw-shadow-lg tw-flex tw-items-center tw-gap-1">
                                            <svg class="tw-w-3 tw-h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                            </svg>
                                            Open
                                        </span>
                                    <?php else: ?>
                                        <span class="tw-bg-red-500 tw-text-white tw-px-2.5 tw-py-1 tw-rounded-full tw-text-xs tw-font-bold tw-shadow-lg tw-flex tw-items-center tw-gap-1">
                                            <svg class="tw-w-3 tw-h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                            </svg>
                                            Closed
                                        </span>
                                    <?php endif; ?>

                                    <!-- Rating Badge -->
                                    <div class="tw-bg-white tw-rounded-full tw-px-2.5 tw-py-1 tw-flex tw-items-center tw-gap-1 tw-shadow-lg">
                                        <svg class="tw-w-4 tw-h-4 tw-text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                        <span class="tw-text-sm tw-font-bold tw-text-gray-900">
                                            <?= number_format($restaurant['rating'] ?? 4.5, 1) ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Free Delivery Badge -->
                                <?php if (!empty($restaurant['delivery_fee']) && $restaurant['delivery_fee'] == 0): ?>
                                    <div class="tw-absolute tw-bottom-3 tw-left-3">
                                        <span class="tw-bg-green-500 tw-text-white tw-px-2.5 tw-py-1 tw-rounded-full tw-text-xs tw-font-bold tw-shadow-lg tw-flex tw-items-center tw-gap-1">
                                            <svg class="tw-w-3 tw-h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Free Delivery
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Restaurant Details -->
                            <div class="tw-p-4 sm:tw-p-5">
                                <h3 class="tw-text-lg sm:tw-text-xl tw-font-bold tw-text-gray-900 tw-mb-1 tw-line-clamp-1 group-hover:tw-text-orange-600 tw-transition-colors">
                                    <?= e($restaurant['name']) ?>
                                </h3>

                                <?php if (!empty($restaurant['cuisine_type'])): ?>
                                <p class="tw-text-sm tw-text-orange-600 tw-font-semibold tw-mb-2">
                                    <?= e($restaurant['cuisine_type']) ?>
                                </p>
                                <?php endif; ?>

                                <?php if (!empty($restaurant['description'])): ?>
                                <p class="tw-text-sm tw-text-gray-600 tw-mb-4 tw-line-clamp-2 tw-leading-relaxed">
                                    <?= e($restaurant['description']) ?>
                                </p>
                                <?php endif; ?>

                                <!-- Restaurant Info Grid -->
                                <div class="tw-grid tw-grid-cols-2 tw-gap-3 tw-mb-4 tw-pt-4 tw-border-t tw-border-gray-100">
                                    <div class="tw-flex tw-items-center tw-gap-2 tw-text-gray-600">
                                        <svg class="tw-w-4 tw-h-4 tw-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="tw-text-xs sm:tw-text-sm tw-font-medium">
                                            <?= e($restaurant['delivery_time'] ?? '30-45') ?> min
                                        </span>
                                    </div>
                                    <div class="tw-flex tw-items-center tw-gap-2 tw-text-gray-600">
                                        <svg class="tw-w-4 tw-h-4 tw-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                                        </svg>
                                        <span class="tw-text-xs sm:tw-text-sm tw-font-medium">
                                            <?php if (!empty($restaurant['delivery_fee']) && $restaurant['delivery_fee'] == 0): ?>
                                                Free
                                            <?php else: ?>
                                                <?= number_format($restaurant['delivery_fee'] ?? 500) ?> FCFA
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- View Menu Button -->
                                <div class="tw-flex tw-items-center tw-justify-between tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-px-4 tw-py-3 tw-rounded-xl tw-font-bold tw-text-sm tw-shadow-md group-hover:tw-shadow-lg tw-transition-all">
                                    <span>View Menu</span>
                                    <svg class="tw-w-5 tw-h-5 tw-transition-transform group-hover:tw-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </article>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- No Results -->
                <div class="tw-col-span-full tw-text-center tw-py-12 sm:tw-py-16">
                    <div class="tw-w-20 tw-h-20 sm:tw-w-24 sm:tw-h-24 tw-mx-auto tw-mb-6 tw-bg-gradient-to-br tw-from-orange-100 tw-to-red-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                        <svg class="tw-w-10 tw-h-10 sm:tw-w-12 sm:tw-h-12 tw-text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <h3 class="tw-text-xl sm:tw-text-2xl tw-font-black tw-text-gray-900 tw-mb-3">No Restaurants Found</h3>
                    <p class="tw-text-sm sm:tw-text-base tw-text-gray-600 tw-mb-6 tw-max-w-md tw-mx-auto tw-px-4">
                        We couldn't find any restaurants matching your criteria. Try adjusting your filters or search terms.
                    </p>
                    <a href="<?= url('/browse') ?>" class="tw-inline-flex tw-items-center tw-justify-center tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-px-6 tw-py-3 tw-rounded-xl tw-font-bold tw-text-sm sm:tw-text-base tw-shadow-lg hover:tw-shadow-xl tw-transition-all hover:tw-scale-105">
                        <svg class="tw-w-5 tw-h-5 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        View All Restaurants
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if (!empty($total_pages) && $total_pages > 1): ?>
            <div class="tw-flex tw-justify-center tw-items-center tw-gap-2 tw-mt-8 sm:tw-mt-12">
                <?php if ($current_page > 1): ?>
                    <a href="<?= url('/browse?page=' . ($current_page - 1) . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($category) ? '&category=' . urlencode($category) : '') . (!empty($sort) ? '&sort=' . urlencode($sort) : '')) ?>"
                       class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors">
                        <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                <?php endif; ?>

                <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                    <a href="<?= url('/browse?page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($category) ? '&category=' . urlencode($category) : '') . (!empty($sort) ? '&sort=' . urlencode($sort) : '')) ?>"
                       class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-lg tw-font-semibold tw-text-sm tw-transition-all <?= $i == $current_page ? 'tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-shadow-md' : 'tw-bg-white tw-border tw-border-gray-300 tw-text-gray-700 hover:tw-bg-gray-50' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="<?= url('/browse?page=' . ($current_page + 1) . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($category) ? '&category=' . urlencode($category) : '') . (!empty($sort) ? '&sort=' . urlencode($sort) : '')) ?>"
                       class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors">
                        <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Feather icons if available
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // View toggle functionality
    const gridViewBtn = document.getElementById('grid-view-btn');
    const listViewBtn = document.getElementById('list-view-btn');
    const restaurantsGrid = document.getElementById('restaurants-grid');

    if (gridViewBtn && listViewBtn && restaurantsGrid) {
        gridViewBtn.addEventListener('click', function() {
            restaurantsGrid.classList.remove('tw-grid-cols-1');
            restaurantsGrid.classList.add('sm:tw-grid-cols-2', 'lg:tw-grid-cols-3');
            gridViewBtn.classList.add('tw-bg-orange-500', 'tw-text-white');
            gridViewBtn.classList.remove('tw-bg-gray-100', 'tw-text-gray-600');
            listViewBtn.classList.remove('tw-bg-orange-500', 'tw-text-white');
            listViewBtn.classList.add('tw-bg-gray-100', 'tw-text-gray-600');
        });

        listViewBtn.addEventListener('click', function() {
            restaurantsGrid.classList.add('tw-grid-cols-1');
            restaurantsGrid.classList.remove('sm:tw-grid-cols-2', 'lg:tw-grid-cols-3');
            listViewBtn.classList.add('tw-bg-orange-500', 'tw-text-white');
            listViewBtn.classList.remove('tw-bg-gray-100', 'tw-text-gray-600');
            gridViewBtn.classList.remove('tw-bg-orange-500', 'tw-text-white');
            gridViewBtn.classList.add('tw-bg-gray-100', 'tw-text-gray-600');
        });
    }

    // Smooth scroll to results on filter change (mobile)
    const filterSelects = document.querySelectorAll('select[name="category"], select[name="sort"]');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Smooth scroll to results on mobile
            if (window.innerWidth < 768) {
                setTimeout(() => {
                    const resultsSection = document.querySelector('#restaurants-grid');
                    if (resultsSection) {
                        resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 100);
            }
        });
    });

    // Mobile-friendly touch interactions
    document.addEventListener('touchstart', function() {}, {passive: true});
});
</script>
