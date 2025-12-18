<?php
/**
 * Customer Favorites Page
 */

$user = $user ?? null;
$favoriteRestaurants = $favoriteRestaurants ?? [];
$favoriteMenuItems = $favoriteMenuItems ?? [];
?>

<!-- Page Header -->
<div class="tw-mb-6">
    <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">My Favorites</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Your favorite restaurants and menu items
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-gap-3">
            <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1.5 tw-rounded-lg tw-text-sm tw-font-medium tw-bg-red-100 tw-text-red-800">
                <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
                <?= count($favoriteRestaurants) + count($favoriteMenuItems) ?> Favorites
            </span>
        </div>
    </div>
</div>

<!-- Tab Navigation -->
<div class="tw-mb-6">
    <div class="tw-border-b tw-border-gray-200">
        <nav class="tw--mb-px tw-flex tw-space-x-8" aria-label="Tabs">
            <button class="tab-btn tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-border-orange-500 tw-font-medium tw-text-sm tw-text-orange-600 tw-flex tw-items-center" data-tab="restaurants">
                <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Restaurants (<?= count($favoriteRestaurants) ?>)
            </button>
            <button class="tab-btn tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-border-transparent tw-font-medium tw-text-sm tw-text-gray-500 hover:tw-text-gray-700 hover:tw-border-gray-300 tw-flex tw-items-center" data-tab="items">
                <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Menu Items (<?= count($favoriteMenuItems) ?>)
            </button>
        </nav>
    </div>
</div>

<!-- Favorite Restaurants Tab -->
<div id="restaurants-tab" class="tab-content">
        <?php if (empty($favoriteRestaurants)): ?>
            <!-- Empty State -->
            <div class="tw-text-center tw-py-12">
                <div class="tw-w-24 tw-h-24 tw-mx-auto tw-mb-4 tw-bg-gray-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                    <i data-feather="heart" class="tw-w-12 tw-h-12 tw-text-gray-400"></i>
                </div>
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2">No Favorite Restaurants</h3>
                <p class="tw-text-gray-600 tw-mb-6">Start adding restaurants to your favorites!</p>
                <a href="<?= url('/browse') ?>" class="tw-inline-flex tw-items-center tw-px-6 tw-py-3 tw-bg-blue-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-blue-700 tw-transition-colors">
                    <i data-feather="search" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                    Browse Restaurants
                </a>
            </div>
        <?php else: ?>
            <div class="tw-space-y-4">
                <?php foreach ($favoriteRestaurants as $restaurant): ?>
                    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-overflow-hidden">
                        <div class="tw-p-4">
                            <div class="tw-flex tw-items-start tw-space-x-4">
                                <!-- Restaurant Image -->
                                <div class="tw-w-16 tw-h-16 tw-rounded-lg tw-overflow-hidden tw-bg-gray-100 tw-flex-shrink-0">
                                    <?php if (!empty($restaurant['logo'])): ?>
                                        <img src="<?= htmlspecialchars($restaurant['logo']) ?>" alt="<?= htmlspecialchars($restaurant['name']) ?>" class="tw-w-full tw-h-full tw-object-cover">
                                    <?php else: ?>
                                        <div class="tw-w-full tw-h-full tw-flex tw-items-center tw-justify-center">
                                            <i data-feather="home" class="tw-w-8 tw-h-8 tw-text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Restaurant Info -->
                                <div class="tw-flex-1 tw-min-w-0">
                                    <div class="tw-flex tw-items-start tw-justify-between">
                                        <div class="tw-flex-1">
                                            <h3 class="tw-font-semibold tw-text-gray-900 tw-truncate"><?= htmlspecialchars($restaurant['name']) ?></h3>
                                            <div class="tw-flex tw-items-center tw-space-x-2 tw-mt-1">
                                                <div class="tw-flex tw-items-center">
                                                    <i data-feather="star" class="tw-w-4 tw-h-4 tw-text-yellow-400 tw-fill-current"></i>
                                                    <span class="tw-text-sm tw-text-gray-600 tw-ml-1"><?= number_format($restaurant['rating'], 1) ?></span>
                                                </div>
                                                <span class="tw-text-gray-300">•</span>
                                                <span class="tw-text-sm tw-text-gray-600"><?= htmlspecialchars($restaurant['cuisine_type'] ?? 'Restaurant') ?></span>
                                            </div>
                                            <div class="tw-flex tw-items-center tw-space-x-2 tw-mt-1">
                                                <i data-feather="truck" class="tw-w-4 tw-h-4 tw-text-gray-400"></i>
                                                <span class="tw-text-sm tw-text-gray-600"><?= number_format($restaurant['delivery_fee'], 0) ?> XAF delivery</span>
                                                <span class="tw-text-gray-300">•</span>
                                                <span class="tw-text-sm tw-text-gray-600"><?= htmlspecialchars($restaurant['delivery_time'] ?? '30-45 mins') ?></span>
                                            </div>
                                        </div>
                                        
                                        <!-- Remove from Favorites Button -->
                                        <button onclick="removeFavoriteRestaurant(<?= $restaurant['id'] ?>)" class="tw-p-2 tw-text-red-600 hover:tw-bg-red-50 tw-rounded-lg tw-transition-colors">
                                            <i data-feather="heart" class="tw-w-5 tw-h-5 tw-fill-current"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="tw-flex tw-space-x-3 tw-mt-4">
                                <a href="<?= url('/restaurant/' . $restaurant['id']) ?>" class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-blue-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium tw-text-center hover:tw-bg-blue-700 tw-transition-colors">
                                    View Menu
                                </a>
                                <button onclick="orderAgain(<?= $restaurant['id'] ?>)" class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-gray-100 tw-text-gray-700 tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-gray-200 tw-transition-colors">
                                    Order Again
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Favorite Menu Items Tab -->
    <div id="items-tab" class="tab-content tw-px-4 tw-py-4 tw-hidden">
        <?php if (empty($favoriteMenuItems)): ?>
            <!-- Empty State -->
            <div class="tw-text-center tw-py-12">
                <div class="tw-w-24 tw-h-24 tw-mx-auto tw-mb-4 tw-bg-gray-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                    <i data-feather="coffee" class="tw-w-12 tw-h-12 tw-text-gray-400"></i>
                </div>
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2">No Favorite Menu Items</h3>
                <p class="tw-text-gray-600 tw-mb-6">Start adding menu items to your favorites!</p>
                <a href="<?= url('/browse') ?>" class="tw-inline-flex tw-items-center tw-px-6 tw-py-3 tw-bg-blue-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-blue-700 tw-transition-colors">
                    <i data-feather="search" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                    Browse Menu Items
                </a>
            </div>
        <?php else: ?>
            <div class="tw-space-y-4">
                <?php foreach ($favoriteMenuItems as $item): ?>
                    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-overflow-hidden">
                        <div class="tw-p-4">
                            <div class="tw-flex tw-items-start tw-space-x-4">
                                <!-- Item Image -->
                                <div class="tw-w-16 tw-h-16 tw-rounded-lg tw-overflow-hidden tw-bg-gray-100 tw-flex-shrink-0">
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="tw-w-full tw-h-full tw-object-cover">
                                    <?php else: ?>
                                        <div class="tw-w-full tw-h-full tw-flex tw-items-center tw-justify-center">
                                            <i data-feather="coffee" class="tw-w-8 tw-h-8 tw-text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Item Info -->
                                <div class="tw-flex-1 tw-min-w-0">
                                    <div class="tw-flex tw-items-start tw-justify-between">
                                        <div class="tw-flex-1">
                                            <h3 class="tw-font-semibold tw-text-gray-900"><?= htmlspecialchars($item['name']) ?></h3>
                                            <p class="tw-text-sm tw-text-gray-600 tw-mt-1"><?= htmlspecialchars($item['restaurant_name']) ?></p>
                                            <?php if (!empty($item['description'])): ?>
                                                <p class="tw-text-sm tw-text-gray-500 tw-mt-1 tw-line-clamp-2"><?= htmlspecialchars($item['description']) ?></p>
                                            <?php endif; ?>
                                            <div class="tw-font-bold tw-text-blue-600 tw-mt-2"><?= number_format($item['price'], 0) ?> XAF</div>
                                        </div>
                                        
                                        <!-- Remove from Favorites Button -->
                                        <button onclick="removeFavoriteItem(<?= $item['id'] ?>)" class="tw-p-2 tw-text-red-600 hover:tw-bg-red-50 tw-rounded-lg tw-transition-colors">
                                            <i data-feather="heart" class="tw-w-5 tw-h-5 tw-fill-current"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="tw-flex tw-space-x-3 tw-mt-4">
                                <button onclick="addToCart(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>', <?= $item['price'] ?>, '<?= htmlspecialchars($item['image'] ?? '', ENT_QUOTES) ?>', <?= $item['restaurant_id'] ?>, '<?= htmlspecialchars($item['restaurant_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($item['category'] ?? '', ENT_QUOTES) ?>')" class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-blue-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-blue-700 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                                    <i data-feather="shopping-cart" class="tw-w-4 tw-h-4 tw-mr-2"></i>
                                    Add to Cart
                                </button>
                                <a href="<?= url('/restaurant/' . $item['restaurant_id']) ?>" class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-gray-100 tw-text-gray-700 tw-rounded-lg tw-text-sm tw-font-medium tw-text-center hover:tw-bg-gray-200 tw-transition-colors">
                                    View Restaurant
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Get CSRF token from session
const csrfToken = '<?= $_SESSION['csrf_token'] ?? '' ?>';

// Initialize Feather Icons
if (typeof feather !== 'undefined') {
    feather.replace();
}

// Tab functionality
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tabName = this.dataset.tab;
        
        // Update active tab button
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('tw-bg-blue-600', 'tw-text-white');
            b.classList.add('tw-bg-gray-100', 'tw-text-gray-700');
        });
        this.classList.remove('tw-bg-gray-100', 'tw-text-gray-700');
        this.classList.add('tw-bg-blue-600', 'tw-text-white');
        
        // Show/hide tab content
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('tw-hidden');
        });
        document.getElementById(`${tabName}-tab`).classList.remove('tw-hidden');
    });
});

// Remove favorite restaurant
function removeFavoriteRestaurant(restaurantId) {
    if (confirm('Remove this restaurant from your favorites?')) {
        fetch(`<?= url('/customer/favorites/restaurants') ?>/${restaurantId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to remove from favorites');
            }
        })
        .catch(error => {
            console.error('Error removing favorite:', error);
            alert('Failed to remove from favorites');
        });
    }
}

// Remove favorite menu item
function removeFavoriteItem(itemId) {
    if (confirm('Remove this item from your favorites?')) {
        fetch(`<?= url('/customer/favorites/items') ?>/${itemId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to remove from favorites');
            }
        })
        .catch(error => {
            console.error('Error removing favorite:', error);
            alert('Failed to remove from favorites');
        });
    }
}

// Order again from restaurant
function orderAgain(restaurantId) {
    window.location.href = `/restaurant/${restaurantId}`;
}
</script>
