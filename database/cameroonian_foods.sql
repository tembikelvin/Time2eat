-- ============================================================================
-- Additional Cameroonian Foods for Time2Eat
-- Extracted from food image URLs and added to existing restaurants
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- ADDITIONAL CAMEROONIAN MENU ITEMS
-- ============================================================================

-- Add more Cameroonian foods to Mama Grace's Kitchen (restaurant_id = 101)
INSERT IGNORE INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `image`, `price`, `ingredients`, `preparation_time`, `is_available`, `is_featured`, `is_popular`, `rating`, `total_reviews`, `total_orders`) VALUES
(1501, 101, 1, 'Yellow Soup with Achu', 'yellow-soup-achu', 'Traditional Cameroonian yellow soup served with pounded cocoyam (achu)', 'https://duabaafrofoods.co.uk/wp-content/uploads/2024/11/Yellow-Soup-and-Achu.jpg', 4200.00, 'Palm oil, fish, meat, vegetables, spices, cocoyam', 50, 1, 1, 1, 4.8, 67, 345),
(1502, 101, 1, 'Lentil Stew', 'lentil-stew', 'Hearty Cameroonian-style lentil stew with vegetables and spices', 'https://www.preciouscore.com/wp-content/uploads/2022/01/Lentil-Stew-276x276.jpg', 2500.00, 'Lentils, tomatoes, onions, garlic, palm oil, spices', 35, 1, 0, 1, 4.6, 42, 289),
(1503, 101, 1, 'Cameroonian Pepper Soup', 'cameroonian-pepper-soup', 'Spicy traditional pepper soup with assorted meat and local spices', 'https://www.preciouscore.com/wp-content/uploads/2023/01/Cameroonian-Pepper-Soup-1138x1536.jpg', 3800.00, 'Assorted meat, pepper soup spices, scent leaves, ginger, garlic', 45, 1, 1, 1, 4.9, 89, 456),
(1504, 101, 1, 'African Fried Rice', 'african-fried-rice', 'Delicious fried rice prepared the African way with local seasonings', 'https://www.preciouscore.com/wp-content/uploads/2020/12/African-Fried-Rice-276x276.jpg', 2800.00, 'Rice, mixed vegetables, meat, African spices, palm oil', 30, 1, 1, 1, 4.7, 78, 523),
(1505, 101, 1, 'Beef Fried Rice', 'beef-fried-rice', 'Savory fried rice with tender beef pieces and vegetables', 'https://www.preciouscore.com/wp-content/uploads/2019/12/Beef-Fried-Rice.jpg', 3200.00, 'Rice, beef, carrots, green beans, onions, soy sauce, spices', 35, 1, 0, 1, 4.5, 56, 334),
(1506, 101, 1, 'Stewed Pinto Beans', 'stewed-pinto-beans', 'Traditional Cameroonian stewed beans with palm oil and spices', 'https://www.preciouscore.com/wp-content/uploads/2018/09/Stewed-Pinto-Beans-Recipe.jpg', 2200.00, 'Pinto beans, palm oil, onions, tomatoes, crayfish, spices', 40, 1, 0, 0, 4.4, 34, 267);

-- Add some items to Quick Bites Express (restaurant_id = 102) - with local twist
-- Note: category_id should match menu_categories for restaurant 102 (ID 2 = Main Dishes, ID 8 = Appetizers)
INSERT IGNORE INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `image`, `price`, `ingredients`, `preparation_time`, `is_available`, `is_featured`, `is_popular`, `rating`, `total_reviews`, `total_orders`) VALUES
(1507, 102, 2, 'Lemon Pepper Chicken Drumsticks', 'lemon-pepper-chicken-drumsticks', 'Juicy chicken drumsticks marinated in lemon pepper seasoning', 'https://www.preciouscore.com/wp-content/uploads/2025/02/Lemon-Pepper-Chicken-Drumsticks-thumbnail-1-276x276.jpg', 2800.00, 'Chicken drumsticks, lemon, black pepper, herbs, spices', 25, 1, 1, 1, 4.6, 45, 298),
(1508, 102, 8, 'Fresh Lettuce Salad', 'fresh-lettuce-salad', 'Crisp lettuce salad with tomatoes, cucumbers and local dressing', 'https://www.preciouscore.com/wp-content/uploads/2020/05/lettuce-salad-recipe.jpg', 1500.00, 'Lettuce, tomatoes, cucumbers, carrots, local vinaigrette', 10, 1, 0, 0, 4.3, 23, 156);

-- ============================================================================
-- UPDATE EXISTING CATEGORIES TO INCLUDE MORE AFRICAN FOODS
-- ============================================================================

-- Update African Cuisine category description
UPDATE `categories` SET 
    `description` = 'Traditional African dishes including authentic Cameroonian specialties like Ndolé, Eru, Yellow Soup, Pepper Soup, and more local favorites',
    `is_featured` = 1
WHERE `slug` = 'african-cuisine';

-- ============================================================================
-- UPDATE RESTAURANTS TO HIGHLIGHT CAMEROONIAN CUISINE
-- ============================================================================

-- Update Mama Grace's Kitchen description to emphasize Cameroonian foods
UPDATE `restaurants` SET 
    `description` = 'Authentic Cameroonian cuisine prepared with love and tradition. Specializing in traditional dishes like Ndolé, Eru, Yellow Soup with Achu, Pepper Soup, and other local favorites that remind you of home.',
    `cuisine_type` = 'Cameroonian',
    `tags` = JSON_ARRAY('Traditional', 'Cameroonian', 'Local', 'Authentic', 'Home-style', 'Ndolé', 'Eru', 'Pepper Soup')
WHERE `id` = 101;

-- ============================================================================
-- ADD FEATURED TAGS FOR CAMEROONIAN DISHES
-- ============================================================================

-- Update some items to be featured and popular
UPDATE `menu_items` SET 
    `is_featured` = 1, 
    `is_popular` = 1,
    `rating` = 4.8,
    `total_reviews` = total_reviews + 25,
    `total_orders` = total_orders + 100
WHERE `id` IN (1501, 1503, 1504, 1507);

-- ============================================================================
-- ADD SOME SAMPLE REVIEWS FOR NEW CAMEROONIAN DISHES
-- ============================================================================

INSERT INTO `reviews` (`id`, `user_id`, `reviewable_type`, `reviewable_id`, `rating`, `title`, `comment`, `is_verified`, `is_approved`, `helpful_count`, `created_at`) VALUES

(101, 1001, 'menu_item', 1501, 5, 'Authentic Yellow Soup!', 'This yellow soup with achu takes me back to my village! The taste is so authentic and the achu was perfectly pounded. Mama Grace knows her traditional cooking!', 1, 1, 18, DATE_SUB(NOW(), INTERVAL 8 DAY)),

(102, 1002, 'menu_item', 1503, 5, 'Best Pepper Soup in Bamenda', 'The pepper soup is incredibly spicy and flavorful. You can taste all the local spices. Perfect for cold evenings!', 1, 1, 22, DATE_SUB(NOW(), INTERVAL 12 DAY)),

(103, 1003, 'menu_item', 1504, 5, 'Amazing African Fried Rice', 'This is not your regular fried rice! The African style preparation makes it so much more flavorful. Love the use of local seasonings!', 1, 1, 15, DATE_SUB(NOW(), INTERVAL 6 DAY)),

(104, 1001, 'menu_item', 1507, 4, 'Delicious Lemon Pepper Chicken', 'The chicken drumsticks were well-seasoned and juicy. Great flavor combination with the lemon and pepper!', 1, 1, 11, DATE_SUB(NOW(), INTERVAL 4 DAY)),

(105, 1002, 'menu_item', 1502, 4, 'Hearty Lentil Stew', 'The lentil stew was very filling and nutritious. Good vegetarian option with great local flavors!', 1, 1, 8, DATE_SUB(NOW(), INTERVAL 10 DAY));

-- ============================================================================
-- UPDATE STATISTICS
-- ============================================================================

-- Update restaurant ratings based on new items
UPDATE `restaurants` SET 
    `rating` = (
        SELECT AVG(rating) 
        FROM `menu_items` 
        WHERE `restaurant_id` = 101 AND `is_available` = 1
    ),
    `total_reviews` = total_reviews + 50,
    `total_orders` = total_orders + 200
WHERE `id` = 101;

UPDATE `restaurants` SET 
    `rating` = (
        SELECT AVG(rating) 
        FROM `menu_items` 
        WHERE `restaurant_id` = 102 AND `is_available` = 1
    ),
    `total_reviews` = total_reviews + 15,
    `total_orders` = total_orders + 75
WHERE `id` = 102;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- COMPLETION MESSAGE
-- ============================================================================
SELECT 'Cameroonian foods successfully added to Time2Eat database!' as Message;
