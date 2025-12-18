<?php
/**
 * Food Item Components
 * Reusable food display components with icons
 */

require_once __DIR__ . '/../../traits/UIComponents.php';
require_once __DIR__ . '/../../helpers/IconHelper.php';

class FoodItemComponents {
    use UIComponents;
    
    /**
     * Render food item card
     */
    public function renderFoodCard(array $item): string
    {
        $isAvailable = $item['available'] ?? true;
        $hasDiscount = isset($item['original_price']) && $item['original_price'] > $item['price'];
        $rating = $item['rating'] ?? 4.5;
        $reviewCount = $item['review_count'] ?? 0;
        
        $cardClass = $isAvailable ? 'tw-food-item' : 'tw-food-item tw-opacity-60';
        $overlayClass = $isAvailable ? '' : 'tw-absolute tw-inset-0 tw-bg-gray-900 tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-rounded-lg';
        
        $itemImageUrl = imageUrl($item['image'] ?? null, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&h=600&fit=crop&q=80');

        return "
        <div class=\"$cardClass\" data-item-id=\"{$item['id']}\">
            <div class=\"tw-relative tw-overflow-hidden tw-rounded-t-lg\">
                <img
                    src=\"" . htmlspecialchars($itemImageUrl) . "\"
                    alt=\"" . htmlspecialchars($item['name']) . "\"
                    class=\"tw-w-full tw-h-48 tw-object-cover tw-transition-transform tw-duration-200 hover:tw-scale-105\"
                    loading=\"lazy\"
                    onerror=\"this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&h=600&fit=crop&q=80'\"
                >
                
                <!-- Discount Badge -->
                " . ($hasDiscount ? "
                <div class=\"tw-absolute tw-top-2 tw-left-2 tw-bg-red-500 tw-text-white tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-bold\">
                    " . IconHelper::feather('tag', ['size' => 'tw-w-3 tw-h-3', 'class' => 'tw-mr-1 tw-inline']) . "
                    " . round((($item['original_price'] - $item['price']) / $item['original_price']) * 100) . "% OFF
                </div>" : '') . "
                
                <!-- Favorite Button -->
                <button 
                    class=\"tw-absolute tw-top-2 tw-right-2 tw-bg-white tw-bg-opacity-90 tw-backdrop-blur-sm tw-rounded-full tw-p-2 tw-transition-all tw-duration-200 hover:tw-bg-opacity-100 hover:tw-scale-110\"
                    onclick=\"toggleFavorite('{$item['id']}')\"
                    data-favorite=\"{$item['id']}\"
                    aria-label=\"Add to favorites\"
                >
                    " . IconHelper::feather('heart', ['size' => 'tw-w-4 tw-h-4', 'class' => 'tw-text-gray-600']) . "
                </button>
                
                <!-- Quick Add Button -->
                " . ($isAvailable ? "
                <button 
                    class=\"tw-absolute tw-bottom-2 tw-right-2 tw-bg-primary-600 tw-text-white tw-rounded-full tw-p-2 tw-transition-all tw-duration-200 hover:tw-bg-primary-700 hover:tw-scale-110 tw-shadow-lg\"
                    onclick=\"addToCart('{$item['id']}', '" . htmlspecialchars($item['name']) . "', {$item['price']}, '{$item['image']}')\"
                    aria-label=\"Quick add to cart\"
                >
                    " . IconHelper::feather('add', ['size' => 'tw-w-4 tw-h-4']) . "
                </button>" : '') . "
                
                <!-- Unavailable Overlay -->
                " . (!$isAvailable ? "
                <div class=\"$overlayClass\">
                    <div class=\"tw-text-center tw-text-white\">
                        " . IconHelper::feather('error', ['size' => 'tw-w-8 tw-h-8', 'class' => 'tw-mx-auto tw-mb-2']) . "
                        <p class=\"tw-font-semibold\">Currently Unavailable</p>
                    </div>
                </div>" : '') . "
            </div>
            
            <div class=\"tw-p-4\">
                <!-- Food Name and Category -->
                <div class=\"tw-mb-2\">
                    <h3 class=\"tw-font-semibold tw-text-gray-800 tw-text-lg tw-mb-1 tw-line-clamp-1\">" . htmlspecialchars($item['name']) . "</h3>
                    <p class=\"tw-text-sm tw-text-gray-600 tw-flex tw-items-center\">
                        " . IconHelper::feather('food', ['size' => 'tw-w-4 tw-h-4', 'class' => 'tw-mr-1']) . "
                        " . htmlspecialchars($item['category'] ?? 'Main Course') . "
                    </p>
                </div>
                
                <!-- Description -->
                <p class=\"tw-text-sm tw-text-gray-600 tw-mb-3 tw-line-clamp-2\">" . htmlspecialchars($item['description'] ?? '') . "</p>
                
                <!-- Rating and Reviews -->
                <div class=\"tw-flex tw-items-center tw-justify-between tw-mb-3\">
                    " . $this->ratingStars($rating, ['size' => 'tw-w-4 tw-h-4']) . "
                    <span class=\"tw-text-xs tw-text-gray-500\">({$reviewCount} reviews)</span>
                </div>
                
                <!-- Price and Add to Cart -->
                <div class=\"tw-flex tw-items-center tw-justify-between\">
                    <div class=\"tw-flex tw-items-center tw-space-x-2\">
                        <span class=\"tw-food-price\">{$item['price']} FCFA</span>
                        " . ($hasDiscount ? "<span class=\"tw-food-original-price\">{$item['original_price']} FCFA</span>" : '') . "
                    </div>
                    
                    " . ($isAvailable ? "
                    <button 
                        class=\"tw-btn-primary tw-px-4 tw-py-2 tw-text-sm\"
                        onclick=\"addToCart('{$item['id']}', '" . htmlspecialchars($item['name']) . "', {$item['price']}, '{$item['image']}')\"
                    >
                        " . IconHelper::feather('cart', ['size' => 'tw-w-4 tw-h-4', 'class' => 'tw-mr-1']) . "
                        Add
                    </button>" : "
                    <button class=\"tw-btn-outline tw-px-4 tw-py-2 tw-text-sm tw-opacity-50\" disabled>
                        " . IconHelper::feather('error', ['size' => 'tw-w-4 tw-h-4', 'class' => 'tw-mr-1']) . "
                        Unavailable
                    </button>") . "
                </div>
                
                <!-- Additional Info -->
                <div class=\"tw-flex tw-items-center tw-justify-between tw-mt-3 tw-pt-3 tw-border-t tw-border-gray-100 tw-text-xs tw-text-gray-500\">
                    <span class=\"tw-flex tw-items-center\">
                        " . IconHelper::feather('time', ['size' => 'tw-w-3 tw-h-3', 'class' => 'tw-mr-1']) . "
                        " . ($item['prep_time'] ?? '15-20') . " min
                    </span>
                    <span class=\"tw-flex tw-items-center\">
                        " . IconHelper::feather('delivery', ['size' => 'tw-w-3 tw-h-3', 'class' => 'tw-mr-1']) . "
                        " . ($item['delivery_fee'] ?? 'Free') . "
                    </span>
                </div>
            </div>
        </div>";
    }
    
    /**
     * Render compact food item (for lists)
     */
    public function renderCompactFoodItem(array $item): string
    {
        $isAvailable = $item['available'] ?? true;
        $hasDiscount = isset($item['original_price']) && $item['original_price'] > $item['price'];
        
        return "
        <div class=\"tw-flex tw-items-center tw-space-x-4 tw-p-4 tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-transition-all tw-duration-200 hover:tw-shadow-md\" data-item-id=\"{$item['id']}\">
            <!-- Image -->
            <div class=\"tw-w-16 tw-h-16 tw-bg-gray-200 tw-rounded-lg tw-overflow-hidden tw-flex-shrink-0\">
                <img 
                    src=\"{$item['image']}\" 
                    alt=\"" . htmlspecialchars($item['name']) . "\"
                    class=\"tw-w-full tw-h-full tw-object-cover\"
                    loading=\"lazy\"
                >
            </div>
            
            <!-- Content -->
            <div class=\"tw-flex-1 tw-min-w-0\">
                <div class=\"tw-flex tw-items-start tw-justify-between\">
                    <div class=\"tw-flex-1\">
                        <h4 class=\"tw-font-medium tw-text-gray-800 tw-truncate\">" . htmlspecialchars($item['name']) . "</h4>
                        <p class=\"tw-text-sm tw-text-gray-600 tw-truncate\">" . htmlspecialchars($item['description'] ?? '') . "</p>
                        
                        <div class=\"tw-flex tw-items-center tw-space-x-4 tw-mt-2\">
                            <div class=\"tw-flex tw-items-center tw-space-x-1\">
                                <span class=\"tw-font-semibold tw-text-primary-600\">{$item['price']} FCFA</span>
                                " . ($hasDiscount ? "<span class=\"tw-text-xs tw-text-gray-500 tw-line-through\">{$item['original_price']} FCFA</span>" : '') . "
                            </div>
                            
                            <span class=\"tw-text-xs tw-text-gray-500 tw-flex tw-items-center\">
                                " . IconHelper::feather('time', ['size' => 'tw-w-3 tw-h-3', 'class' => 'tw-mr-1']) . "
                                " . ($item['prep_time'] ?? '15-20') . " min
                            </span>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class=\"tw-flex tw-items-center tw-space-x-2 tw-ml-4\">
                        " . IconHelper::button('heart', [
                            'class' => 'tw-btn-icon tw-w-8 tw-h-8 tw-text-gray-400 hover:tw-text-red-500',
                            'onclick' => "toggleFavorite('{$item['id']}')",
                            'data-favorite' => $item['id'],
                            'aria-label' => 'Add to favorites'
                        ]) . "
                        
                        " . ($isAvailable ? 
                            IconHelper::button('add', [
                                'class' => 'tw-btn-icon tw-btn-icon-primary tw-w-8 tw-h-8',
                                'onclick' => "addToCart('{$item['id']}', '" . htmlspecialchars($item['name']) . "', {$item['price']}, '{$item['image']}')",
                                'aria-label' => 'Add to cart'
                            ]) : 
                            IconHelper::button('error', [
                                'class' => 'tw-btn-icon tw-w-8 tw-h-8 tw-text-gray-400 tw-cursor-not-allowed',
                                'disabled' => true,
                                'aria-label' => 'Unavailable'
                            ])
                        ) . "
                    </div>
                </div>
            </div>
        </div>";
    }
    
    /**
     * Render food category filter
     */
    public function renderCategoryFilter(array $categories, string $activeCategory = ''): string
    {
        $html = '<div class="tw-flex tw-flex-wrap tw-gap-2 tw-mb-6">';
        
        // All categories button
        $allActive = empty($activeCategory) ? 'tw-bg-primary-600 tw-text-white' : 'tw-bg-white tw-text-gray-700 hover:tw-bg-gray-50';
        $html .= "
        <button 
            class=\"tw-px-4 tw-py-2 tw-rounded-full tw-border tw-border-gray-300 tw-transition-all tw-duration-200 tw-flex tw-items-center tw-space-x-2 $allActive\"
            onclick=\"filterByCategory('')\"
        >
            " . IconHelper::feather('food', ['size' => 'tw-w-4 tw-h-4']) . "
            <span>All</span>
        </button>";
        
        foreach ($categories as $category) {
            $isActive = $activeCategory === $category['slug'];
            $buttonClass = $isActive ? 'tw-bg-primary-600 tw-text-white' : 'tw-bg-white tw-text-gray-700 hover:tw-bg-gray-50';
            
            $html .= "
            <button 
                class=\"tw-px-4 tw-py-2 tw-rounded-full tw-border tw-border-gray-300 tw-transition-all tw-duration-200 tw-flex tw-items-center tw-space-x-2 $buttonClass\"
                onclick=\"filterByCategory('{$category['slug']}')\"
            >
                " . IconHelper::feather($category['icon'] ?? 'food', ['size' => 'tw-w-4 tw-h-4']) . "
                <span>{$category['name']}</span>
                <span class=\"tw-bg-gray-200 tw-text-gray-600 tw-px-2 tw-py-1 tw-rounded-full tw-text-xs\">{$category['count']}</span>
            </button>";
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Render food search results
     */
    public function renderSearchResults(array $items, string $query): string
    {
        if (empty($items)) {
            return "
            <div class=\"tw-text-center tw-py-12\">
                " . IconHelper::feather('search', ['size' => 'tw-w-16 tw-h-16', 'class' => 'tw-mx-auto tw-text-gray-300 tw-mb-4']) . "
                <h3 class=\"tw-text-lg tw-font-semibold tw-text-gray-800 tw-mb-2\">No results found</h3>
                <p class=\"tw-text-gray-600 tw-mb-4\">We couldn't find any items matching \"" . htmlspecialchars($query) . "\"</p>
                " . $this->primaryButton('Browse All Items', [
                    'href' => '/browse',
                    'icon' => 'browse'
                ]) . "
            </div>";
        }
        
        $html = "
        <div class=\"tw-mb-6\">
            <h2 class=\"tw-text-xl tw-font-semibold tw-text-gray-800 tw-mb-2\">
                Search Results for \"" . htmlspecialchars($query) . "\"
            </h2>
            <p class=\"tw-text-gray-600\">" . count($items) . " items found</p>
        </div>
        
        <div class=\"tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6\">";
        
        foreach ($items as $item) {
            $html .= $this->renderFoodCard($item);
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Render food loading skeleton
     */
    public function renderLoadingSkeleton(int $count = 6): string
    {
        $html = '<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6">';
        
        for ($i = 0; $i < $count; $i++) {
            $html .= '
            <div class="tw-card tw-animate-pulse">
                <div class="tw-skeleton tw-h-48 tw-w-full tw-rounded-t-lg tw-mb-4"></div>
                <div class="tw-p-4">
                    <div class="tw-skeleton-text tw-h-6 tw-w-3/4 tw-mb-2"></div>
                    <div class="tw-skeleton-text tw-h-4 tw-w-1/2 tw-mb-3"></div>
                    <div class="tw-skeleton-text tw-h-4 tw-w-full tw-mb-2"></div>
                    <div class="tw-skeleton-text tw-h-4 tw-w-2/3 tw-mb-4"></div>
                    <div class="tw-flex tw-justify-between tw-items-center">
                        <div class="tw-skeleton tw-h-6 tw-w-20"></div>
                        <div class="tw-skeleton tw-h-10 tw-w-16 tw-rounded-lg"></div>
                    </div>
                </div>
            </div>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Render JavaScript for food interactions
     */
    public function renderFoodScript(): string
    {
        return '
        <script>
        // Food category filtering
        function filterByCategory(category) {
            const items = document.querySelectorAll("[data-item-id]");
            const buttons = document.querySelectorAll("[onclick*=\"filterByCategory\"]");
            
            // Update button states
            buttons.forEach(btn => {
                btn.classList.remove("tw-bg-primary-600", "tw-text-white");
                btn.classList.add("tw-bg-white", "tw-text-gray-700");
            });
            
            event.target.classList.remove("tw-bg-white", "tw-text-gray-700");
            event.target.classList.add("tw-bg-primary-600", "tw-text-white");
            
            // Filter items (this would typically make an API call)
            console.log("Filtering by category:", category);
            showToast(`Showing ${category || "all"} items`, "info");
        }
        
        // Food search
        function searchFood(query) {
            if (query.length < 2) return;
            
            // This would typically make an API call
            console.log("Searching for:", query);
            showToast(`Searching for "${query}"...`, "info");
        }
        
        // Initialize food interactions
        document.addEventListener("DOMContentLoaded", function() {
            // Add search functionality to food search inputs
            const searchInputs = document.querySelectorAll("input[data-food-search]");
            searchInputs.forEach(input => {
                let searchTimeout;
                input.addEventListener("input", function(e) {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        searchFood(e.target.value);
                    }, 300);
                });
            });
        });
        </script>';
    }
}
?>
