-- Create menu categories table for vendor menu management
-- This table stores restaurant menu categories for organizing menu items

CREATE TABLE IF NOT EXISTS menu_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    restaurant_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    parent_id INT NULL, -- For nested categories (future feature)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraints
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES menu_categories(id) ON DELETE SET NULL,
    
    -- Indexes for performance
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_sort_order (sort_order),
    INDEX idx_is_active (is_active),
    INDEX idx_parent_id (parent_id),
    
    -- Unique constraint to prevent duplicate category names per restaurant
    UNIQUE KEY unique_category_per_restaurant (restaurant_id, name)
);

-- Update menu_items table to add missing fields for vendor management
ALTER TABLE menu_items 
ADD COLUMN IF NOT EXISTS stock_quantity INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS min_stock_level INT DEFAULT 5,
ADD COLUMN IF NOT EXISTS calories INT NULL,
ADD COLUMN IF NOT EXISTS ingredients TEXT NULL,
ADD COLUMN IF NOT EXISTS allergens VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS is_vegetarian TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS is_vegan TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS is_gluten_free TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS customization_options JSON NULL,
ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;

-- Add indexes for menu_items performance
ALTER TABLE menu_items 
ADD INDEX IF NOT EXISTS idx_stock_quantity (stock_quantity),
ADD INDEX IF NOT EXISTS idx_is_available (is_available),
ADD INDEX IF NOT EXISTS idx_deleted_at (deleted_at),
ADD INDEX IF NOT EXISTS idx_restaurant_category (restaurant_id, category_id);

-- Insert default categories for existing restaurants
INSERT IGNORE INTO menu_categories (restaurant_id, name, description, sort_order)
SELECT 
    r.id,
    'Main Dishes',
    'Primary dishes and entrees',
    1
FROM restaurants r
WHERE r.deleted_at IS NULL;

INSERT IGNORE INTO menu_categories (restaurant_id, name, description, sort_order)
SELECT 
    r.id,
    'Appetizers',
    'Starters and small plates',
    2
FROM restaurants r
WHERE r.deleted_at IS NULL;

INSERT IGNORE INTO menu_categories (restaurant_id, name, description, sort_order)
SELECT 
    r.id,
    'Beverages',
    'Drinks and refreshments',
    3
FROM restaurants r
WHERE r.deleted_at IS NULL;

INSERT IGNORE INTO menu_categories (restaurant_id, name, description, sort_order)
SELECT 
    r.id,
    'Desserts',
    'Sweet treats and desserts',
    4
FROM restaurants r
WHERE r.deleted_at IS NULL;

-- Update existing menu items to use the new category system
-- This assumes there's a categories table that needs to be mapped to menu_categories
UPDATE menu_items mi
JOIN categories c ON mi.category_id = c.id
JOIN restaurants r ON mi.restaurant_id = r.id
JOIN menu_categories mc ON mc.restaurant_id = r.id AND mc.name = c.name
SET mi.category_id = mc.id
WHERE mi.deleted_at IS NULL;

-- Create trigger to auto-disable items when stock reaches zero
DELIMITER //
CREATE TRIGGER IF NOT EXISTS auto_disable_out_of_stock
    AFTER UPDATE ON menu_items
    FOR EACH ROW
BEGIN
    IF NEW.stock_quantity = 0 AND OLD.stock_quantity > 0 THEN
        UPDATE menu_items 
        SET is_available = 0 
        WHERE id = NEW.id;
    ELSEIF NEW.stock_quantity > 0 AND OLD.stock_quantity = 0 AND NEW.is_available = 0 THEN
        UPDATE menu_items 
        SET is_available = 1 
        WHERE id = NEW.id;
    END IF;
END//
DELIMITER ;

-- Create trigger to update restaurant's updated_at when menu items change
DELIMITER //
CREATE TRIGGER IF NOT EXISTS update_restaurant_on_menu_change
    AFTER UPDATE ON menu_items
    FOR EACH ROW
BEGIN
    UPDATE restaurants 
    SET updated_at = CURRENT_TIMESTAMP 
    WHERE id = NEW.restaurant_id;
END//
DELIMITER ;

-- Create view for menu items with category information
CREATE OR REPLACE VIEW menu_items_with_categories AS
SELECT 
    mi.*,
    mc.name as category_name,
    mc.sort_order as category_sort_order,
    r.name as restaurant_name,
    r.user_id as vendor_id,
    CASE 
        WHEN mi.stock_quantity <= mi.min_stock_level THEN 1 
        ELSE 0 
    END as is_low_stock,
    CASE 
        WHEN mi.stock_quantity = 0 THEN 1 
        ELSE 0 
    END as is_out_of_stock
FROM menu_items mi
JOIN menu_categories mc ON mi.category_id = mc.id
JOIN restaurants r ON mi.restaurant_id = r.id
WHERE mi.deleted_at IS NULL
ORDER BY mc.sort_order ASC, mi.name ASC;

-- Create view for category statistics
CREATE OR REPLACE VIEW category_stats AS
SELECT 
    mc.id,
    mc.restaurant_id,
    mc.name,
    mc.sort_order,
    COUNT(mi.id) as total_items,
    COUNT(CASE WHEN mi.is_available = 1 THEN 1 END) as available_items,
    COUNT(CASE WHEN mi.stock_quantity = 0 THEN 1 END) as out_of_stock_items,
    COUNT(CASE WHEN mi.stock_quantity <= mi.min_stock_level THEN 1 END) as low_stock_items,
    AVG(mi.price) as avg_price,
    MIN(mi.price) as min_price,
    MAX(mi.price) as max_price,
    SUM(mi.stock_quantity) as total_stock
FROM menu_categories mc
LEFT JOIN menu_items mi ON mc.id = mi.category_id AND mi.deleted_at IS NULL
WHERE mc.is_active = 1
GROUP BY mc.id, mc.restaurant_id, mc.name, mc.sort_order;

-- Insert sample menu categories for demo purposes (optional)
-- These will be ignored if categories already exist due to IGNORE clause
INSERT IGNORE INTO menu_categories (restaurant_id, name, description, sort_order) VALUES
(1, 'Signature Dishes', 'Our chef special recommendations', 0),
(1, 'Soups & Salads', 'Fresh soups and healthy salads', 5),
(1, 'Sides', 'Perfect accompaniments to your meal', 6);

-- Add comments to tables for documentation
ALTER TABLE menu_categories COMMENT = 'Restaurant menu categories for organizing menu items';
ALTER TABLE menu_items COMMENT = 'Restaurant menu items with inventory management';

-- Create indexes for better query performance
CREATE INDEX IF NOT EXISTS idx_menu_items_restaurant_available ON menu_items(restaurant_id, is_available);
CREATE INDEX IF NOT EXISTS idx_menu_items_category_available ON menu_items(category_id, is_available);
CREATE INDEX IF NOT EXISTS idx_menu_categories_restaurant_active ON menu_categories(restaurant_id, is_active);

COMMIT;
