<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Search - Time2Eat') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .star-rating {
            color: #fbbf24;
        }
        .search-suggestion {
            transition: all 0.2s ease;
        }
        .search-suggestion:hover {
            background-color: #f3f4f6;
        }
        .filter-badge {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="<?= url('/') ?>" class="flex items-center space-x-2">
                    <i class="fas fa-utensils text-red-500 text-2xl"></i>
                    <span class="text-xl font-bold text-gray-800">Time2Eat</span>
                </a>
                
                <!-- Search Bar -->
                <div class="flex-1 max-w-2xl mx-8 relative">
                    <form method="GET" action="<?= url('/search') ?>" class="relative">
                        <input 
                            type="text" 
                            name="q" 
                            value="<?= htmlspecialchars($query) ?>"
                            placeholder="Search for restaurants, dishes, or cuisines..."
                            class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            id="searchInput"
                            autocomplete="off"
                        >
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-red-500 text-white px-4 py-1.5 rounded-md hover:bg-red-600 transition-colors">
                            Search
                        </button>
                    </form>
                    
                    <!-- Search Suggestions -->
                    <div id="searchSuggestions" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg mt-1 hidden z-50">
                        <!-- Suggestions will be populated by JavaScript -->
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <a href="<?= url('/cart') ?>" class="relative">
                        <i class="fas fa-shopping-cart text-gray-600 text-xl"></i>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center" id="cartCount">0</span>
                    </a>
                    <a href="<?= url('/login') ?>" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">
                        Login
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Filters Sidebar -->
            <div class="lg:w-1/4">
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Filters</h3>
                    
                    <form method="GET" action="<?= url('/search') ?>" id="filterForm">
                        <input type="hidden" name="q" value="<?= htmlspecialchars($query) ?>">
                        
                        <!-- Category Filter -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select name="category" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= $filters['category'] == $category['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Cuisine Filter -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cuisine Type</label>
                            <select name="cuisine" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500">
                                <option value="">All Cuisines</option>
                                <?php foreach ($cuisineTypes as $cuisine): ?>
                                    <option value="<?= htmlspecialchars($cuisine) ?>" <?= $filters['cuisine'] === $cuisine ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cuisine) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
                            <div class="space-y-2">
                                <?php foreach ($priceRanges as $range): ?>
                                    <label class="flex items-center">
                                        <input 
                                            type="radio" 
                                            name="price_range" 
                                            value="<?= $range['min'] ?>-<?= $range['max'] ?? 'max' ?>"
                                            <?= ($filters['price_min'] == $range['min']) ? 'checked' : '' ?>
                                            class="text-red-500 focus:ring-red-500"
                                        >
                                        <span class="ml-2 text-sm text-gray-700"><?= $range['label'] ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Rating Filter -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Rating</label>
                            <select name="min_rating" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500">
                                <option value="">Any Rating</option>
                                <option value="4" <?= $filters['min_rating'] == '4' ? 'selected' : '' ?>>4+ Stars</option>
                                <option value="3" <?= $filters['min_rating'] == '3' ? 'selected' : '' ?>>3+ Stars</option>
                                <option value="2" <?= $filters['min_rating'] == '2' ? 'selected' : '' ?>>2+ Stars</option>
                            </select>
                        </div>

                        <!-- Dietary Options -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dietary Options</label>
                            <select name="dietary" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500">
                                <option value="">All Options</option>
                                <option value="vegetarian" <?= $filters['dietary'] === 'vegetarian' ? 'selected' : '' ?>>Vegetarian</option>
                                <option value="vegan" <?= $filters['dietary'] === 'vegan' ? 'selected' : '' ?>>Vegan</option>
                                <option value="gluten_free" <?= $filters['dietary'] === 'gluten_free' ? 'selected' : '' ?>>Gluten Free</option>
                            </select>
                        </div>

                        <!-- Sort Options -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                            <select name="sort" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500">
                                <option value="relevance" <?= $filters['sort'] === 'relevance' ? 'selected' : '' ?>>Relevance</option>
                                <option value="rating" <?= $filters['sort'] === 'rating' ? 'selected' : '' ?>>Rating</option>
                                <option value="delivery_fee" <?= $filters['sort'] === 'delivery_fee' ? 'selected' : '' ?>>Delivery Fee</option>
                                <option value="delivery_time" <?= $filters['sort'] === 'delivery_time' ? 'selected' : '' ?>>Delivery Time</option>
                                <option value="price_low" <?= $filters['sort'] === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                                <option value="price_high" <?= $filters['sort'] === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-600 transition-colors">
                            Apply Filters
                        </button>
                        
                        <a href="<?= url('/search?q=' . urlencode($query)) ?>" class="block w-full text-center text-gray-600 py-2 px-4 border border-gray-300 rounded-lg mt-2 hover:bg-gray-50 transition-colors">
                            Clear Filters
                        </a>
                    </form>
                </div>
            </div>

            <!-- Search Results -->
            <div class="lg:w-3/4">
                <!-- Search Header -->
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <?php if (!empty($query)): ?>
                                <h1 class="text-2xl font-bold text-gray-800">
                                    Search results for "<?= htmlspecialchars($query) ?>"
                                </h1>
                            <?php else: ?>
                                <h1 class="text-2xl font-bold text-gray-800">Browse All</h1>
                            <?php endif; ?>
                            <p class="text-gray-600 mt-1"><?= $totalResults ?> results found</p>
                        </div>
                        
                        <!-- View Toggle -->
                        <div class="flex items-center space-x-2">
                            <button id="gridView" class="p-2 text-gray-600 hover:text-red-500 transition-colors">
                                <i class="fas fa-th-large"></i>
                            </button>
                            <button id="listView" class="p-2 text-gray-600 hover:text-red-500 transition-colors">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Active Filters -->
                    <?php if (!empty(array_filter($filters))): ?>
                        <div class="flex flex-wrap gap-2 mt-4">
                            <?php foreach ($filters as $key => $value): ?>
                                <?php if (!empty($value) && $key !== 'sort' && $key !== 'limit'): ?>
                                    <span class="filter-badge text-white px-3 py-1 rounded-full text-sm flex items-center">
                                        <?= ucfirst(str_replace('_', ' ', $key)) ?>: <?= htmlspecialchars($value) ?>
                                        <button class="ml-2 hover:text-gray-200" onclick="removeFilter('<?= $key ?>')">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Results Container -->
                <div id="resultsContainer">
                    <?php if (empty($results['restaurants']) && empty($results['menu_items'])): ?>
                        <!-- No Results -->
                        <div class="text-center py-12">
                            <i class="fas fa-search text-gray-300 text-6xl mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-600 mb-2">No results found</h3>
                            <p class="text-gray-500 mb-6">Try adjusting your search terms or filters</p>
                            <a href="<?= url('/browse') ?>" class="bg-red-500 text-white px-6 py-3 rounded-lg hover:bg-red-600 transition-colors">
                                Browse All Restaurants
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Restaurants Section -->
                        <?php if (!empty($results['restaurants'])): ?>
                            <div class="mb-8">
                                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-store text-red-500 mr-2"></i>
                                    Restaurants (<?= count($results['restaurants']) ?>)
                                </h2>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="restaurantGrid">
                                    <?php foreach ($results['restaurants'] as $restaurant): ?>
                                        <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                                            <div class="relative">
                                                <img 
                                                    src="<?= $restaurant['image'] ?? '/images/restaurant-placeholder.jpg' ?>" 
                                                    alt="<?= htmlspecialchars($restaurant['name']) ?>"
                                                    class="w-full h-48 object-cover"
                                                    loading="lazy"
                                                >
                                                <?php if ($restaurant['delivery_fee'] == 0): ?>
                                                    <span class="absolute top-2 left-2 bg-green-500 text-white px-2 py-1 rounded text-xs font-medium">
                                                        Free Delivery
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="p-4">
                                                <h3 class="font-semibold text-gray-800 mb-1">
                                                    <a href="<?= url('/restaurants/' . $restaurant['slug']) ?>" class="hover:text-red-500 transition-colors">
                                                        <?= htmlspecialchars($restaurant['name']) ?>
                                                    </a>
                                                </h3>
                                                
                                                <p class="text-gray-600 text-sm mb-2 line-clamp-2">
                                                    <?= htmlspecialchars($restaurant['description']) ?>
                                                </p>
                                                
                                                <div class="flex items-center justify-between text-sm text-gray-500 mb-3">
                                                    <div class="flex items-center">
                                                        <div class="flex items-center star-rating mr-2">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <i class="fas fa-star <?= $i <= round($restaurant['average_rating']) ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                                                            <?php endfor; ?>
                                                        </div>
                                                        <span><?= number_format($restaurant['average_rating'], 1) ?> (<?= $restaurant['review_count'] ?>)</span>
                                                    </div>
                                                    <span><?= $restaurant['cuisine_type'] ?></span>
                                                </div>
                                                
                                                <div class="flex items-center justify-between text-sm">
                                                    <div class="flex items-center text-gray-600">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        <?= $restaurant['delivery_time'] ?> min
                                                    </div>
                                                    <div class="flex items-center text-gray-600">
                                                        <i class="fas fa-truck mr-1"></i>
                                                        <?= $restaurant['delivery_fee'] == 0 ? 'Free' : number_format($restaurant['delivery_fee']) . ' XAF' ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Menu Items Section -->
                        <?php if (!empty($results['menu_items'])): ?>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-utensils text-red-500 mr-2"></i>
                                    Menu Items (<?= count($results['menu_items']) ?>)
                                </h2>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="menuItemGrid">
                                    <?php foreach ($results['menu_items'] as $item): ?>
                                        <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                                            <div class="relative">
                                                <img 
                                                    src="<?= $item['image'] ?? '/images/food-placeholder.jpg' ?>" 
                                                    alt="<?= htmlspecialchars($item['name']) ?>"
                                                    class="w-full h-48 object-cover"
                                                    loading="lazy"
                                                >
                                                <div class="absolute top-2 right-2 bg-white bg-opacity-90 px-2 py-1 rounded text-sm font-semibold text-gray-800">
                                                    <?= number_format($item['price']) ?> XAF
                                                </div>
                                            </div>
                                            
                                            <div class="p-4">
                                                <h3 class="font-semibold text-gray-800 mb-1">
                                                    <?= htmlspecialchars($item['name']) ?>
                                                </h3>
                                                
                                                <p class="text-gray-600 text-sm mb-2">
                                                    From <a href="<?= url('/restaurants/' . $item['restaurant_slug']) ?>" class="text-red-500 hover:underline">
                                                        <?= htmlspecialchars($item['restaurant_name']) ?>
                                                    </a>
                                                </p>
                                                
                                                <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                                                    <?= htmlspecialchars($item['description']) ?>
                                                </p>
                                                
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center text-sm text-gray-500">
                                                        <div class="flex items-center star-rating mr-2">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <i class="fas fa-star <?= $i <= round($item['average_rating']) ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                                                            <?php endfor; ?>
                                                        </div>
                                                        <span><?= number_format($item['average_rating'], 1) ?></span>
                                                    </div>
                                                    
                                                    <button
                                                        class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors text-sm"
                                                        onclick="addToCart(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>', <?= $item['price'] ?>, '<?= htmlspecialchars($item['image'] ?? '', ENT_QUOTES) ?>', <?= $item['restaurant_id'] ?>, '<?= htmlspecialchars($item['restaurant_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($item['category'] ?? '', ENT_QUOTES) ?>')"
                                                    >
                                                        <i class="fas fa-plus mr-1"></i>
                                                        Add to Cart
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Search suggestions functionality
        const searchInput = document.getElementById('searchInput');
        const suggestionsContainer = document.getElementById('searchSuggestions');
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                suggestionsContainer.classList.add('hidden');
                return;
            }
            
            searchTimeout = setTimeout(() => {
                fetchSuggestions(query);
            }, 300);
        });

        function fetchSuggestions(query) {
            fetch(`/api/search/suggestions?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.suggestions.length > 0) {
                        displaySuggestions(data.suggestions);
                    } else {
                        suggestionsContainer.classList.add('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error fetching suggestions:', error);
                    suggestionsContainer.classList.add('hidden');
                });
        }

        function displaySuggestions(suggestions) {
            const html = suggestions.map(suggestion => `
                <div class="search-suggestion px-4 py-2 cursor-pointer border-b border-gray-100 last:border-b-0" 
                     onclick="selectSuggestion('${suggestion.name}', '${suggestion.url}')">
                    <div class="flex items-center">
                        <i class="fas fa-${suggestion.type === 'restaurant' ? 'store' : suggestion.type === 'menu_item' ? 'utensils' : 'tag'} text-gray-400 mr-3"></i>
                        <span class="text-gray-800">${suggestion.name}</span>
                        <span class="text-xs text-gray-500 ml-auto">${suggestion.type.replace('_', ' ')}</span>
                    </div>
                </div>
            `).join('');
            
            suggestionsContainer.innerHTML = html;
            suggestionsContainer.classList.remove('hidden');
        }

        function selectSuggestion(name, url) {
            if (url.startsWith('/search')) {
                searchInput.value = name;
                document.querySelector('form').submit();
            } else {
                window.location.href = url;
            }
        }

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                suggestionsContainer.classList.add('hidden');
            }
        });



        // Filter removal
        function removeFilter(filterName) {
            const url = new URL(window.location);
            url.searchParams.delete(filterName);
            window.location.href = url.toString();
        }

        // Auto-submit filter form on change
        document.getElementById('filterForm').addEventListener('change', function() {
            this.submit();
        });

        // View toggle functionality
        document.getElementById('gridView').addEventListener('click', function() {
            document.getElementById('restaurantGrid').className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6';
            document.getElementById('menuItemGrid').className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6';
        });

        document.getElementById('listView').addEventListener('click', function() {
            document.getElementById('restaurantGrid').className = 'space-y-4';
            document.getElementById('menuItemGrid').className = 'space-y-4';
        });

        // Utility functions
        function updateCartCount() {
            fetch('<?= url('/api/cart/count.php') ?>?_=' + Date.now(), {
                cache: 'no-cache',
                headers: {
                    'Cache-Control': 'no-cache',
                    'Pragma': 'no-cache'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('cartCount').textContent = data.count;
                    }
                });
        }

        function showNotification(message, type) {
            // Simple notification implementation
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Initialize cart count on page load
        updateCartCount();
    </script>
</body>
</html>
