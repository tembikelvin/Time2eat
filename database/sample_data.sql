-- ============================================================================
-- Time2Eat Sample Data
-- Complete sample data for testing and demonstration
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- CATEGORIES DATA
-- ============================================================================

INSERT IGNORE INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `icon`, `sort_order`, `is_active`, `is_featured`) VALUES
(1, 'African Cuisine', 'african-cuisine', 'Traditional African dishes including Cameroonian specialties', 'categories/african-cuisine.jpg', 'utensils', 1, 1, 1),
(2, 'Fast Food', 'fast-food', 'Quick and delicious fast food options', 'categories/fast-food.jpg', 'hamburger', 2, 1, 1),
(3, 'Chinese Food', 'chinese-food', 'Authentic Chinese cuisine and Asian fusion', 'categories/chinese-food.jpg', 'dragon', 3, 1, 1),
(4, 'Pizza & Italian', 'pizza-italian', 'Wood-fired pizzas and Italian classics', 'categories/pizza-italian.jpg', 'pizza-slice', 4, 1, 1),
(5, 'Beverages', 'beverages', 'Refreshing drinks, juices, and soft drinks', 'categories/beverages.jpg', 'glass-water', 5, 1, 0),
(6, 'Desserts', 'desserts', 'Sweet treats and desserts', 'categories/desserts.jpg', 'cake', 6, 1, 0),
(7, 'Healthy Options', 'healthy-options', 'Nutritious and healthy meal choices', 'categories/healthy.jpg', 'leaf', 7, 1, 1),
(8, 'Grilled & BBQ', 'grilled-bbq', 'Grilled meats and barbecue specialties', 'categories/grilled-bbq.jpg', 'fire', 8, 1, 1);

-- ============================================================================
-- SITE SETTINGS DATA
-- ============================================================================

INSERT IGNORE INTO `site_settings` (`key`, `value`, `type`, `group`, `description`, `is_public`) VALUES
('site_name', 'Time2Eat', 'string', 'general', 'Website name', 1),
('site_tagline', 'Bamenda\'s Premier Food Delivery Service', 'string', 'general', 'Website tagline', 1),
('site_description', 'Order delicious food from your favorite local restaurants in Bamenda, Cameroon. Fast delivery, great prices, and amazing taste!', 'text', 'general', 'Website description', 1),
('contact_email', 'info@time2eat.com', 'string', 'contact', 'Main contact email', 1),
('contact_phone', '+237 6XX XXX XXX', 'string', 'contact', 'Main contact phone', 1),
('contact_address', 'Commercial Avenue, Bamenda, Northwest Region, Cameroon', 'string', 'contact', 'Business address', 1),
('business_hours', '{"monday": "8:00-22:00", "tuesday": "8:00-22:00", "wednesday": "8:00-22:00", "thursday": "8:00-22:00", "friday": "8:00-23:00", "saturday": "8:00-23:00", "sunday": "10:00-22:00"}', 'json', 'business', 'Business operating hours', 1),
('delivery_fee', '500', 'integer', 'business', 'Default delivery fee in XAF', 1),
('minimum_order', '2000', 'integer', 'business', 'Minimum order amount in XAF', 1),
('commission_rate', '0.15', 'string', 'business', 'Platform commission rate (15%)', 0),
('currency', 'XAF', 'string', 'business', 'Default currency', 1),
('timezone', 'Africa/Douala', 'string', 'general', 'Application timezone', 0),
('social_facebook', 'https://facebook.com/time2eat', 'string', 'social', 'Facebook page URL', 1),
('social_instagram', 'https://instagram.com/time2eat', 'string', 'social', 'Instagram profile URL', 1),
('social_twitter', 'https://twitter.com/time2eat', 'string', 'social', 'Twitter profile URL', 1);

-- ============================================================================
-- SAMPLE USERS DATA
-- ============================================================================

-- Sample customers
INSERT IGNORE INTO `users` (`id`, `username`, `email`, `email_verified_at`, `password`, `first_name`, `last_name`, `phone`, `role`, `status`, `created_at`) VALUES
(1001, 'john_doe', 'john@example.com', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', '+237 6XX XXX 001', 'customer', 'active', NOW()),
(1002, 'mary_smith', 'mary@example.com', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mary', 'Smith', '+237 6XX XXX 002', 'customer', 'active', NOW()),
(1003, 'peter_jones', 'peter@example.com', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Peter', 'Jones', '+237 6XX XXX 003', 'customer', 'active', NOW());

-- Sample vendors
INSERT IGNORE INTO `users` (`id`, `username`, `email`, `email_verified_at`, `password`, `first_name`, `last_name`, `phone`, `role`, `status`, `created_at`) VALUES
(1004, 'mama_grace', 'grace@mamagrace.com', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Grace', 'Mbah', '+237 6XX XXX 004', 'vendor', 'active', NOW()),
(1005, 'chef_paul', 'paul@quickbites.com', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Paul', 'Nkeng', '+237 6XX XXX 005', 'vendor', 'active', NOW()),
(1006, 'wang_li', 'li@golddragon.com', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Li', 'Wang', '+237 6XX XXX 006', 'vendor', 'active', NOW());

-- Sample riders
INSERT IGNORE INTO `users` (`id`, `username`, `email`, `email_verified_at`, `password`, `first_name`, `last_name`, `phone`, `role`, `status`, `created_at`) VALUES
(1007, 'rider_james', 'james@time2eat.com', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'James', 'Tabi', '+237 6XX XXX 007', 'rider', 'active', NOW()),
(1008, 'rider_sarah', 'sarah@time2eat.com', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Fon', '+237 6XX XXX 008', 'rider', 'active', NOW());

-- Default admin user
INSERT IGNORE INTO `users` (`id`, `username`, `email`, `email_verified_at`, `password`, `first_name`, `last_name`, `phone`, `role`, `status`, `created_at`) VALUES
(1000, 'sample_admin', 'sample_admin@time2eat.com', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sample', 'Admin', '+237 6XX XXX 000', 'admin', 'active', NOW());

-- ============================================================================
-- SAMPLE RESTAURANTS DATA
-- ============================================================================

INSERT IGNORE INTO `restaurants` (`id`, `user_id`, `name`, `slug`, `description`, `image`, `cuisine_type`, `phone`, `email`, `address`, `city`, `latitude`, `longitude`, `delivery_fee`, `minimum_order`, `delivery_time`, `rating`, `total_reviews`, `total_orders`, `status`, `is_featured`, `is_open`) VALUES
(101, 1004, 'Mama Grace Kitchen', 'mama-grace-kitchen', 'Authentic Cameroonian cuisine prepared with love and traditional recipes. Home of the best Ndolé and Eru in Bamenda.', 'restaurants/mama-grace.jpg', 'African Cuisine', '+237 6XX XXX 004', 'grace@mamagrace.com', 'Ntarikon Quarter, Bamenda', 'Bamenda', 5.9597, 10.1463, 500.00, 2000.00, '25-35 mins', 4.8, 156, 1247, 'active', 1, 1),
(102, 1005, 'Quick Bites Express', 'quick-bites-express', 'Fast and delicious meals for busy people. Burgers, sandwiches, and quick African dishes.', 'restaurants/quick-bites.jpg', 'Fast Food', '+237 6XX XXX 005', 'paul@quickbites.com', 'Commercial Avenue, Bamenda', 'Bamenda', 5.9631, 10.1489, 300.00, 1500.00, '15-25 mins', 4.5, 89, 892, 'active', 1, 1),
(103, 1006, 'Golden Dragon Chinese', 'golden-dragon-chinese', 'Authentic Chinese cuisine with a Cameroonian twist. Fresh ingredients and traditional cooking methods.', 'restaurants/golden-dragon.jpg', 'Chinese Food', '+237 6XX XXX 006', 'li@golddragon.com', 'Up Station, Bamenda', 'Bamenda', 5.9654, 10.1512, 600.00, 2500.00, '30-40 mins', 4.6, 73, 634, 'active', 1, 1),
(104, 1004, 'Bamenda Pizza Corner', 'bamenda-pizza-corner', 'Wood-fired pizzas and Italian classics. The best pizza in Northwest Region!', 'restaurants/pizza-corner.jpg', 'Pizza & Italian', '+237 6XX XXX 009', 'info@pizzacorner.com', 'Mile 4, Bamenda', 'Bamenda', 5.9578, 10.1445, 400.00, 1800.00, '20-30 mins', 4.7, 112, 756, 'active', 0, 1),
(105, 1005, 'Healthy Harvest', 'healthy-harvest', 'Fresh, organic, and healthy meal options. Perfect for health-conscious food lovers.', 'restaurants/healthy-harvest.jpg', 'Healthy Options', '+237 6XX XXX 010', 'info@healthyharvest.com', 'Cow Street, Bamenda', 'Bamenda', 5.9612, 10.1478, 450.00, 2200.00, '25-35 mins', 4.4, 67, 423, 'active', 0, 1),
(106, 1006, 'BBQ Masters', 'bbq-masters', 'Grilled perfection! The finest grilled meats and barbecue in Bamenda.', 'restaurants/bbq-masters.jpg', 'Grilled & BBQ', '+237 6XX XXX 011', 'info@bbqmasters.com', 'Food Market, Bamenda', 'Bamenda', 5.9589, 10.1456, 550.00, 2000.00, '35-45 mins', 4.9, 134, 987, 'active', 1, 1);

-- ============================================================================
-- SAMPLE MENU ITEMS DATA
-- ============================================================================

-- Mama Grace Kitchen Menu Items
INSERT IGNORE INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `image`, `price`, `ingredients`, `preparation_time`, `is_available`, `is_featured`, `is_popular`, `rating`, `total_reviews`, `total_orders`) VALUES
(1001, 101, 1, 'Ndolé with Beef', 'ndole-with-beef', 'Traditional Cameroonian dish with groundnuts, bitter leaves, and tender beef', 'menu/ndole-beef.jpg', 3500.00, 'Bitter leaves, groundnuts, beef, crayfish, palm oil, spices', 45, 1, 1, 1, 4.9, 89, 456),
(1002, 101, 1, 'Eru with Stockfish', 'eru-with-stockfish', 'Delicious Eru soup with stockfish and cow skin', 'menu/eru-stockfish.jpg', 4000.00, 'Eru leaves, stockfish, cow skin, palm oil, crayfish, spices', 50, 1, 1, 1, 4.8, 67, 334),
(1003, 101, 1, 'Jollof Rice with Chicken', 'jollof-rice-chicken', 'Spicy Jollof rice served with grilled chicken', 'menu/jollof-chicken.jpg', 2800.00, 'Rice, tomatoes, chicken, onions, spices, vegetables', 35, 1, 0, 1, 4.7, 123, 678),
(1004, 101, 1, 'Plantain with Beans', 'plantain-beans', 'Fried plantain served with seasoned beans', 'menu/plantain-beans.jpg', 1800.00, 'Plantain, beans, palm oil, onions, spices', 25, 1, 0, 0, 4.5, 45, 234),
(1005, 101, 5, 'Palm Wine (Fresh)', 'palm-wine-fresh', 'Fresh palm wine tapped daily', 'menu/palm-wine.jpg', 800.00, 'Fresh palm wine', 5, 1, 0, 0, 4.3, 23, 156);

-- Quick Bites Express Menu Items
INSERT IGNORE INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `image`, `price`, `ingredients`, `preparation_time`, `is_available`, `is_featured`, `is_popular`, `rating`, `total_reviews`, `total_orders`) VALUES
(1006, 102, 2, 'Beef Burger Deluxe', 'beef-burger-deluxe', 'Juicy beef patty with cheese, lettuce, tomato, and special sauce', 'menu/beef-burger.jpg', 2200.00, 'Beef patty, cheese, lettuce, tomato, onion, special sauce, bun', 15, 1, 1, 1, 4.6, 78, 445),
(1007, 102, 2, 'Chicken Shawarma', 'chicken-shawarma', 'Grilled chicken wrapped in pita with vegetables and sauce', 'menu/chicken-shawarma.jpg', 1800.00, 'Chicken, pita bread, vegetables, garlic sauce, spices', 12, 1, 1, 1, 4.5, 92, 567),
(1008, 102, 2, 'Fish and Chips', 'fish-and-chips', 'Crispy fried fish with golden french fries', 'menu/fish-chips.jpg', 2500.00, 'Fresh fish, potatoes, flour, spices, oil', 18, 1, 0, 1, 4.4, 56, 289),
(1009, 102, 1, 'Pepper Soup (Goat)', 'pepper-soup-goat', 'Spicy goat meat pepper soup', 'menu/pepper-soup.jpg', 3200.00, 'Goat meat, pepper soup spices, vegetables', 40, 1, 0, 0, 4.7, 34, 178),
(1010, 102, 5, 'Fresh Orange Juice', 'fresh-orange-juice', 'Freshly squeezed orange juice', 'menu/orange-juice.jpg', 600.00, 'Fresh oranges', 5, 1, 0, 0, 4.2, 28, 134);

-- Golden Dragon Chinese Menu Items
INSERT IGNORE INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `image`, `price`, `ingredients`, `preparation_time`, `is_available`, `is_featured`, `is_popular`, `rating`, `total_reviews`, `total_orders`) VALUES
(1011, 103, 3, 'Sweet and Sour Chicken', 'sweet-sour-chicken', 'Crispy chicken in sweet and sour sauce with vegetables', 'menu/sweet-sour-chicken.jpg', 3800.00, 'Chicken, bell peppers, pineapple, sweet and sour sauce', 25, 1, 1, 1, 4.7, 45, 267),
(1012, 103, 3, 'Fried Rice Special', 'fried-rice-special', 'Chinese fried rice with mixed vegetables and choice of meat', 'menu/fried-rice.jpg', 2800.00, 'Rice, mixed vegetables, eggs, soy sauce, choice of meat', 20, 1, 1, 1, 4.6, 67, 389),
(1013, 103, 3, 'Beef with Black Bean Sauce', 'beef-black-bean', 'Tender beef strips in savory black bean sauce', 'menu/beef-black-bean.jpg', 4200.00, 'Beef, black bean sauce, onions, bell peppers', 30, 1, 0, 0, 4.5, 23, 145),
(1014, 103, 3, 'Spring Rolls (6 pieces)', 'spring-rolls', 'Crispy spring rolls with vegetables', 'menu/spring-rolls.jpg', 1500.00, 'Spring roll wrapper, vegetables, oil', 15, 1, 0, 1, 4.3, 34, 223),
(1015, 103, 5, 'Chinese Tea', 'chinese-tea', 'Traditional Chinese tea', 'menu/chinese-tea.jpg', 500.00, 'Chinese tea leaves', 5, 1, 0, 0, 4.1, 12, 89);

-- Pizza Corner Menu Items
INSERT IGNORE INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `image`, `price`, `ingredients`, `preparation_time`, `is_available`, `is_featured`, `is_popular`, `rating`, `total_reviews`, `total_orders`) VALUES
(1016, 104, 4, 'Margherita Pizza (Large)', 'margherita-pizza-large', 'Classic pizza with tomato sauce, mozzarella, and fresh basil', 'menu/margherita-pizza.jpg', 4500.00, 'Pizza dough, tomato sauce, mozzarella, basil, olive oil', 25, 1, 1, 1, 4.8, 89, 445),
(1017, 104, 4, 'Pepperoni Pizza (Medium)', 'pepperoni-pizza-medium', 'Pepperoni pizza with mozzarella cheese', 'menu/pepperoni-pizza.jpg', 3800.00, 'Pizza dough, tomato sauce, mozzarella, pepperoni', 22, 1, 1, 1, 4.7, 67, 334),
(1018, 104, 4, 'Chicken BBQ Pizza', 'chicken-bbq-pizza', 'BBQ chicken pizza with onions and bell peppers', 'menu/chicken-bbq-pizza.jpg', 4200.00, 'Pizza dough, BBQ sauce, chicken, onions, bell peppers, cheese', 28, 1, 0, 1, 4.6, 45, 267),
(1019, 104, 4, 'Spaghetti Bolognese', 'spaghetti-bolognese', 'Classic spaghetti with meat sauce', 'menu/spaghetti-bolognese.jpg', 3200.00, 'Spaghetti, ground beef, tomato sauce, herbs, parmesan', 20, 1, 0, 0, 4.5, 34, 189),
(1020, 104, 5, 'Italian Soda', 'italian-soda', 'Refreshing Italian soda', 'menu/italian-soda.jpg', 700.00, 'Soda water, Italian syrup', 3, 1, 0, 0, 4.2, 23, 123);

-- ============================================================================
-- SAMPLE REVIEWS DATA
-- ============================================================================

INSERT INTO `reviews` (`id`, `user_id`, `reviewable_type`, `reviewable_id`, `rating`, `title`, `comment`, `is_verified`, `is_approved`, `helpful_count`, `created_at`) VALUES
(1, 1001, 'restaurant', 101, 5, 'Amazing Ndolé!', 'Mama Grace makes the best Ndolé in Bamenda. The taste is authentic and reminds me of my grandmother\'s cooking. Highly recommended!', 1, 1, 12, DATE_SUB(NOW(), INTERVAL 15 DAY)),
(2, 1002, 'restaurant', 102, 4, 'Quick and tasty', 'Great for a quick meal. The burger was juicy and the service was fast. Will definitely order again.', 1, 1, 8, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(3, 1003, 'restaurant', 103, 5, 'Excellent Chinese food', 'The sweet and sour chicken was perfect! Authentic taste and generous portions. Golden Dragon is now my go-to for Chinese food.', 1, 1, 15, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(4, 1001, 'menu_item', 1001, 5, 'Perfect Ndolé', 'This Ndolé is exactly how it should be made. Rich, flavorful, and the beef was so tender. Worth every franc!', 1, 1, 9, DATE_SUB(NOW(), INTERVAL 12 DAY)),
(5, 1002, 'menu_item', 1006, 4, 'Great burger', 'The beef burger was really good. Fresh ingredients and cooked perfectly. The fries could be crispier though.', 1, 1, 6, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(6, 1003, 'restaurant', 104, 5, 'Best pizza in town', 'The Margherita pizza was incredible! Thin crust, fresh basil, and the cheese was perfect. Definitely the best pizza in Bamenda.', 1, 1, 18, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(7, 1001, 'delivery', 1, 4, 'Good delivery service', 'The rider was polite and delivered on time. Food arrived hot and well-packaged. Good service overall.', 1, 1, 4, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(8, 1002, 'restaurant', 106, 5, 'BBQ perfection', 'BBQ Masters lives up to its name! The grilled chicken was smoky and delicious. The sauce was amazing too.', 1, 1, 11, DATE_SUB(NOW(), INTERVAL 2 DAY));

-- ============================================================================
-- SAMPLE COUPONS DATA
-- ============================================================================

INSERT INTO `coupons` (`id`, `code`, `name`, `description`, `type`, `value`, `minimum_order`, `usage_limit`, `usage_limit_per_user`, `is_active`, `starts_at`, `expires_at`, `created_by`) VALUES
(1, 'WELCOME10', 'Welcome Discount', '10% off for new customers', 'percentage', 10.00, 2000.00, 100, 1, 1, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 1000),
(2, 'FREEDELIV', 'Free Delivery', 'Free delivery on orders above 3000 XAF', 'free_delivery', 0.00, 3000.00, 500, 3, 1, NOW(), DATE_ADD(NOW(), INTERVAL 60 DAY), 1000),
(3, 'SAVE500', 'Save 500 XAF', '500 XAF off on orders above 4000 XAF', 'fixed_amount', 500.00, 4000.00, 200, 2, 1, NOW(), DATE_ADD(NOW(), INTERVAL 45 DAY), 1000),
(4, 'WEEKEND20', 'Weekend Special', '20% off on weekends', 'percentage', 20.00, 2500.00, 1000, 1, 1, NOW(), DATE_ADD(NOW(), INTERVAL 90 DAY), 1000);

-- ============================================================================
-- SAMPLE ORDERS DATA
-- ============================================================================

INSERT INTO `orders` (`id`, `order_number`, `customer_id`, `restaurant_id`, `rider_id`, `status`, `payment_status`, `subtotal`, `delivery_fee`, `total_amount`, `delivery_address`, `estimated_delivery_time`, `rating`, `review`, `created_at`) VALUES
(10001, 'ORD-2024-001', 1001, 101, 1007, 'delivered', 'paid', 3500.00, 500.00, 4000.00, '{"address": "Mile 3, Bamenda", "phone": "+237 6XX XXX 001", "instructions": "Call when you arrive"}', DATE_ADD(NOW(), INTERVAL 35 MINUTE), 5, 'Excellent food and fast delivery!', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(10002, 'ORD-2024-002', 1002, 102, 1008, 'delivered', 'paid', 2200.00, 300.00, 2500.00, '{"address": "Commercial Avenue, Bamenda", "phone": "+237 6XX XXX 002", "instructions": "Leave at the gate"}', DATE_ADD(NOW(), INTERVAL 20 MINUTE), 4, 'Good food, quick service', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(10003, 'ORD-2024-003', 1003, 103, 1007, 'on_the_way', 'paid', 3800.00, 600.00, 4400.00, '{"address": "Up Station, Bamenda", "phone": "+237 6XX XXX 003", "instructions": "Ring the bell"}', DATE_ADD(NOW(), INTERVAL 15 MINUTE), NULL, NULL, DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(10004, 'ORD-2024-004', 1001, 104, NULL, 'preparing', 'paid', 4500.00, 400.00, 4900.00, '{"address": "Ntarikon Quarter, Bamenda", "phone": "+237 6XX XXX 001", "instructions": "Apartment 2B"}', DATE_ADD(NOW(), INTERVAL 25 MINUTE), NULL, NULL, DATE_SUB(NOW(), INTERVAL 10 MINUTE));

-- ============================================================================
-- SAMPLE ORDER ITEMS DATA
-- ============================================================================

INSERT INTO `order_items` (`order_id`, `menu_item_id`, `quantity`, `unit_price`, `total_price`) VALUES
(10001, 1001, 1, 3500.00, 3500.00),
(10002, 1006, 1, 2200.00, 2200.00),
(10003, 1011, 1, 3800.00, 3800.00),
(10004, 1016, 1, 4500.00, 4500.00);

-- ============================================================================
-- SAMPLE RIDER SCHEDULES DATA
-- ============================================================================

INSERT INTO `rider_schedules` (`rider_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `max_orders`) VALUES
(1007, 1, '08:00:00', '18:00:00', 1, 5), -- Monday
(1007, 2, '08:00:00', '18:00:00', 1, 5), -- Tuesday
(1007, 3, '08:00:00', '18:00:00', 1, 5), -- Wednesday
(1007, 4, '08:00:00', '18:00:00', 1, 5), -- Thursday
(1007, 5, '08:00:00', '20:00:00', 1, 6), -- Friday
(1007, 6, '09:00:00', '20:00:00', 1, 6), -- Saturday
(1008, 1, '10:00:00', '22:00:00', 1, 4), -- Monday
(1008, 2, '10:00:00', '22:00:00', 1, 4), -- Tuesday
(1008, 3, '10:00:00', '22:00:00', 1, 4), -- Wednesday
(1008, 4, '10:00:00', '22:00:00', 1, 4), -- Thursday
(1008, 5, '10:00:00', '23:00:00', 1, 5), -- Friday
(1008, 6, '10:00:00', '23:00:00', 1, 5), -- Saturday
(1008, 0, '12:00:00', '20:00:00', 1, 3); -- Sunday

-- ============================================================================
-- SAMPLE AFFILIATE DATA
-- ============================================================================

INSERT INTO `affiliates` (`user_id`, `affiliate_code`, `commission_rate`, `total_referrals`, `total_earnings`, `status`) VALUES
(1001, 'JOHN2024', 0.0500, 3, 150.00, 'active'),
(1002, 'MARY2024', 0.0500, 1, 75.00, 'active');

-- ============================================================================
-- SAMPLE PAYMENT METHODS DATA
-- ============================================================================

INSERT INTO `payment_methods` (`user_id`, `type`, `provider`, `name`, `details`, `is_default`, `is_verified`) VALUES
(1001, 'mobile_money', 'mtn_money', 'MTN Mobile Money', '{"number": "+237 6XX XXX 001", "name": "John Doe"}', 1, 1),
(1002, 'mobile_money', 'orange_money', 'Orange Money', '{"number": "+237 6XX XXX 002", "name": "Mary Smith"}', 1, 1),
(1003, 'card', 'stripe', 'Visa Card', '{"last4": "1234", "brand": "visa", "exp_month": 12, "exp_year": 2025}', 1, 1);

-- ============================================================================
-- SAMPLE ANALYTICS DATA
-- ============================================================================

INSERT INTO `analytics` (`event_type`, `event_name`, `user_id`, `data`, `created_at`) VALUES
('page_view', 'homepage', 1001, '{"page": "/", "referrer": "google.com"}', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
('page_view', 'restaurant_view', 1002, '{"restaurant_id": 101, "page": "/restaurant/mama-grace-kitchen"}', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
('order', 'order_placed', 1001, '{"order_id": 10001, "total": 4000, "restaurant_id": 101}', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('search', 'menu_search', 1003, '{"query": "ndole", "results": 3}', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
('cart', 'add_to_cart', 1002, '{"menu_item_id": 1006, "quantity": 1}', DATE_SUB(NOW(), INTERVAL 1 DAY));

-- ============================================================================
-- SAMPLE DAILY STATS DATA
-- ============================================================================

INSERT INTO `daily_stats` (`date`, `restaurant_id`, `total_orders`, `total_revenue`, `total_commission`, `average_order_value`, `customer_satisfaction`) VALUES
(CURDATE(), 101, 5, 17500.00, 2625.00, 3500.00, 4.8),
(CURDATE(), 102, 8, 18000.00, 2700.00, 2250.00, 4.5),
(CURDATE(), 103, 3, 11400.00, 1710.00, 3800.00, 4.6),
(CURDATE(), 104, 4, 18000.00, 2700.00, 4500.00, 4.7),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 101, 7, 24500.00, 3675.00, 3500.00, 4.9),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 102, 12, 27000.00, 4050.00, 2250.00, 4.4),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 103, 6, 22800.00, 3420.00, 3800.00, 4.7);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- END OF SAMPLE DATA
-- ============================================================================
