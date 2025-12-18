<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Write Review - Time2Eat') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .star-rating {
            cursor: pointer;
        }
        .star-rating .star {
            color: #d1d5db;
            transition: color 0.2s ease;
        }
        .star-rating .star.active,
        .star-rating .star:hover {
            color: #fbbf24;
        }
        .star-rating:hover .star:hover ~ .star {
            color: #d1d5db;
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
                
                <nav class="flex items-center space-x-6">
                    <a href="<?= url('/customer/orders') ?>" class="text-gray-600 hover:text-red-500 transition-colors">My Orders</a>
                    <a href="<?= url('/customer/dashboard') ?>" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">Dashboard</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="flex items-center space-x-2 text-sm text-gray-600 mb-6">
                <a href="<?= url('/customer/dashboard') ?>" class="hover:text-red-500">Dashboard</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <a href="<?= url('/customer/orders') ?>" class="hover:text-red-500">Orders</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-gray-800">Write Review</span>
            </nav>

            <!-- Order Information -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-2xl font-bold text-gray-800">Write Review</h1>
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                        Order #<?= htmlspecialchars($order['order_number']) ?>
                    </span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                    <div>
                        <span class="font-medium">Restaurant:</span>
                        <p class="text-gray-800"><?= htmlspecialchars($restaurant['name']) ?></p>
                    </div>
                    <div>
                        <span class="font-medium">Order Date:</span>
                        <p class="text-gray-800"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></p>
                    </div>
                    <div>
                        <span class="font-medium">Total Amount:</span>
                        <p class="text-gray-800"><?= number_format($order['total_amount']) ?> XAF</p>
                    </div>
                </div>
            </div>

            <!-- Review Form -->
            <form method="POST" action="<?= url('/reviews/store') ?>" class="space-y-8">
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                <input type="hidden" name="restaurant_id" value="<?= $restaurant['id'] ?>">
                <?php if (!empty($order['rider_id'])): ?>
                    <input type="hidden" name="rider_id" value="<?= $order['rider_id'] ?>">
                <?php endif; ?>

                <!-- Restaurant Review -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center mb-6">
                        <img 
                            src="<?= $restaurant['image'] ?? '/images/restaurant-placeholder.jpg' ?>" 
                            alt="<?= htmlspecialchars($restaurant['name']) ?>"
                            class="w-16 h-16 rounded-lg object-cover mr-4"
                        >
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($restaurant['name']) ?></h2>
                            <p class="text-gray-600"><?= htmlspecialchars($restaurant['cuisine_type']) ?></p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Rate this restaurant <span class="text-red-500">*</span>
                            </label>
                            <div class="star-rating flex items-center space-x-1" data-rating="0" data-name="restaurant_rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star star text-2xl" data-value="<?= $i ?>"></i>
                                <?php endfor; ?>
                                <span class="ml-3 text-gray-600 rating-text">Click to rate</span>
                            </div>
                            <input type="hidden" name="restaurant_rating" id="restaurant_rating" required>
                        </div>

                        <div>
                            <label for="restaurant_comment" class="block text-sm font-medium text-gray-700 mb-2">
                                Tell us about your experience
                            </label>
                            <textarea 
                                name="restaurant_comment" 
                                id="restaurant_comment"
                                rows="4"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                placeholder="How was the food quality, service, and overall experience?"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Menu Items Review -->
                <?php if (!empty($orderItems)): ?>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-6">Rate Individual Items</h2>
                        
                        <div class="space-y-6">
                            <?php foreach ($orderItems as $item): ?>
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center mb-4">
                                        <img 
                                            src="<?= $item['image'] ?? '/images/food-placeholder.jpg' ?>" 
                                            alt="<?= htmlspecialchars($item['name']) ?>"
                                            class="w-12 h-12 rounded-lg object-cover mr-3"
                                        >
                                        <div class="flex-1">
                                            <h3 class="font-medium text-gray-800"><?= htmlspecialchars($item['name']) ?></h3>
                                            <p class="text-sm text-gray-600">
                                                Qty: <?= $item['quantity'] ?> × <?= number_format($item['price']) ?> XAF
                                            </p>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Rate this item</label>
                                            <div class="star-rating flex items-center space-x-1" data-rating="0" data-name="item_reviews[<?= $item['menu_item_id'] ?>][rating]">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star star text-xl" data-value="<?= $i ?>"></i>
                                                <?php endfor; ?>
                                                <span class="ml-3 text-gray-600 rating-text">Optional</span>
                                            </div>
                                            <input type="hidden" name="item_reviews[<?= $item['menu_item_id'] ?>][rating]" class="item-rating">
                                        </div>

                                        <div>
                                            <textarea 
                                                name="item_reviews[<?= $item['menu_item_id'] ?>][comment]"
                                                rows="2"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                                placeholder="How was this specific item?"
                                            ></textarea>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Delivery Review -->
                <?php if (!empty($order['rider_id'])): ?>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-6">Rate Delivery Service</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rate delivery service</label>
                                <div class="star-rating flex items-center space-x-1" data-rating="0" data-name="rider_rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star star text-2xl" data-value="<?= $i ?>"></i>
                                    <?php endfor; ?>
                                    <span class="ml-3 text-gray-600 rating-text">Optional</span>
                                </div>
                                <input type="hidden" name="rider_rating" id="rider_rating">
                            </div>

                            <div>
                                <textarea 
                                    name="rider_comment"
                                    rows="3"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="How was the delivery experience? Was the rider professional and on time?"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-between">
                    <a href="<?= url('/customer/orders') ?>" class="text-gray-600 hover:text-gray-800 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Orders
                    </a>
                    
                    <div class="flex items-center space-x-4">
                        <button 
                            type="button" 
                            onclick="saveDraft()"
                            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Save Draft
                        </button>
                        <button 
                            type="submit" 
                            class="px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors"
                        >
                            Submit Review
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Star rating functionality
        document.querySelectorAll('.star-rating').forEach(rating => {
            const stars = rating.querySelectorAll('.star');
            const ratingText = rating.querySelector('.rating-text');
            const hiddenInput = rating.dataset.name.includes('[') 
                ? rating.parentElement.querySelector('.item-rating')
                : document.getElementById(rating.dataset.name);
            
            stars.forEach((star, index) => {
                star.addEventListener('click', () => {
                    const value = index + 1;
                    rating.dataset.rating = value;
                    if (hiddenInput) hiddenInput.value = value;
                    
                    // Update visual state
                    stars.forEach((s, i) => {
                        s.classList.toggle('active', i < value);
                    });
                    
                    // Update text
                    const ratingTexts = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
                    ratingText.textContent = ratingTexts[value];
                });
                
                star.addEventListener('mouseenter', () => {
                    const value = index + 1;
                    stars.forEach((s, i) => {
                        s.style.color = i < value ? '#fbbf24' : '#d1d5db';
                    });
                });
            });
            
            rating.addEventListener('mouseleave', () => {
                const currentRating = parseInt(rating.dataset.rating);
                stars.forEach((s, i) => {
                    s.style.color = i < currentRating ? '#fbbf24' : '#d1d5db';
                });
            });
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const restaurantRating = document.getElementById('restaurant_rating').value;
            
            if (!restaurantRating) {
                e.preventDefault();
                alert('Please rate the restaurant before submitting your review.');
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Submitting...';
        });

        // Save draft functionality
        function saveDraft() {
            const formData = new FormData(document.querySelector('form'));
            formData.append('save_draft', '1');
            
            fetch('<?= url('/reviews/draft') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Draft saved successfully!', 'success');
                } else {
                    showNotification('Failed to save draft', 'error');
                }
            })
            .catch(error => {
                console.error('Error saving draft:', error);
                showNotification('Failed to save draft', 'error');
            });
        }

        // Auto-save draft every 2 minutes
        setInterval(() => {
            const restaurantRating = document.getElementById('restaurant_rating').value;
            const restaurantComment = document.getElementById('restaurant_comment').value;
            
            if (restaurantRating || restaurantComment.trim()) {
                saveDraft();
            }
        }, 120000); // 2 minutes

        // Notification function
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Character count for textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            const maxLength = 500;
            const wrapper = document.createElement('div');
            wrapper.className = 'relative';
            
            textarea.parentNode.insertBefore(wrapper, textarea);
            wrapper.appendChild(textarea);
            
            const counter = document.createElement('div');
            counter.className = 'absolute bottom-2 right-2 text-xs text-gray-500';
            counter.textContent = `0/${maxLength}`;
            wrapper.appendChild(counter);
            
            textarea.addEventListener('input', function() {
                const length = this.value.length;
                counter.textContent = `${length}/${maxLength}`;
                counter.className = `absolute bottom-2 right-2 text-xs ${length > maxLength ? 'text-red-500' : 'text-gray-500'}`;
            });
        });
    </script>
</body>
</html>
