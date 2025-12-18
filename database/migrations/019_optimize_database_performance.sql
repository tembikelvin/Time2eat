-- ============================================================================
-- Database Performance Optimization Migration
-- Adds missing indexes and optimizes query performance
-- ============================================================================

-- Orders table performance indexes
CREATE INDEX IF NOT EXISTS idx_orders_customer_restaurant ON orders (customer_id, restaurant_id);
CREATE INDEX IF NOT EXISTS idx_orders_status_created ON orders (status, created_at);
CREATE INDEX IF NOT EXISTS idx_orders_restaurant_status_date ON orders (restaurant_id, status, created_at);
CREATE INDEX IF NOT EXISTS idx_orders_rider_status_date ON orders (rider_id, status, created_at);
CREATE INDEX IF NOT EXISTS idx_orders_total_amount ON orders (total_amount);
CREATE INDEX IF NOT EXISTS idx_orders_payment_status ON orders (payment_status);
CREATE INDEX IF NOT EXISTS idx_orders_customer_status_created ON orders (customer_id, status, created_at);

-- Menu items performance indexes
CREATE INDEX IF NOT EXISTS idx_menu_items_restaurant_category_available ON menu_items (restaurant_id, category_id, is_available);
CREATE INDEX IF NOT EXISTS idx_menu_items_price_available ON menu_items (price, is_available);
CREATE INDEX IF NOT EXISTS idx_menu_items_featured_available ON menu_items (is_featured, is_available);
CREATE INDEX IF NOT EXISTS idx_menu_items_name_restaurant ON menu_items (name(50), restaurant_id);
CREATE INDEX IF NOT EXISTS idx_menu_items_restaurant_available_sort ON menu_items (restaurant_id, is_available, sort_order);

-- Users table performance indexes
CREATE INDEX IF NOT EXISTS idx_users_role_status_available ON users (role, status, is_available);
CREATE INDEX IF NOT EXISTS idx_users_created_at ON users (created_at);
CREATE INDEX IF NOT EXISTS idx_users_last_login ON users (last_login_at);
CREATE INDEX IF NOT EXISTS idx_users_email_verified ON users (email_verified_at);

-- Restaurants table performance indexes
CREATE INDEX IF NOT EXISTS idx_restaurants_status_featured_rating ON restaurants (status, is_featured, rating);
CREATE INDEX IF NOT EXISTS idx_restaurants_cuisine_status ON restaurants (cuisine_type, status);
CREATE INDEX IF NOT EXISTS idx_restaurants_location_status ON restaurants (latitude, longitude, status);
CREATE INDEX IF NOT EXISTS idx_restaurants_delivery_radius ON restaurants (delivery_radius);
CREATE INDEX IF NOT EXISTS idx_restaurants_user_status ON restaurants (user_id, status);

-- Reviews table performance indexes
CREATE INDEX IF NOT EXISTS idx_reviews_reviewable_rating_status ON reviews (reviewable_type, reviewable_id, rating, status);
CREATE INDEX IF NOT EXISTS idx_reviews_user_created ON reviews (user_id, created_at);
CREATE INDEX IF NOT EXISTS idx_reviews_rating_created ON reviews (rating, created_at);
CREATE INDEX IF NOT EXISTS idx_reviews_status_created ON reviews (status, created_at);

-- Order items performance indexes
CREATE INDEX IF NOT EXISTS idx_order_items_menu_item_order ON order_items (menu_item_id, order_id);
CREATE INDEX IF NOT EXISTS idx_order_items_quantity_price ON order_items (quantity, unit_price);
CREATE INDEX IF NOT EXISTS idx_order_items_order_created ON order_items (order_id, created_at);

-- Deliveries performance indexes
CREATE INDEX IF NOT EXISTS idx_deliveries_rider_status_created ON deliveries (rider_id, status, created_at);
CREATE INDEX IF NOT EXISTS idx_deliveries_pickup_delivery_time ON deliveries (picked_up_at, delivered_at);
CREATE INDEX IF NOT EXISTS idx_deliveries_order_status ON deliveries (order_id, status);

-- Messages performance indexes
CREATE INDEX IF NOT EXISTS idx_messages_conversation_created ON messages (conversation_id, created_at);
CREATE INDEX IF NOT EXISTS idx_messages_sender_recipient_read ON messages (sender_id, recipient_id, is_read);
CREATE INDEX IF NOT EXISTS idx_messages_recipient_read_created ON messages (recipient_id, is_read, created_at);

-- Payments performance indexes
CREATE INDEX IF NOT EXISTS idx_payments_user_status_created ON payments (user_id, status, created_at);
CREATE INDEX IF NOT EXISTS idx_payments_order_status ON payments (order_id, status);
CREATE INDEX IF NOT EXISTS idx_payments_transaction_status ON payments (transaction_id, status);

-- Analytics performance indexes
CREATE INDEX IF NOT EXISTS idx_analytics_event_user_date ON analytics (event_type, user_id, created_at);
CREATE INDEX IF NOT EXISTS idx_analytics_session_date ON analytics (session_id, created_at);
CREATE INDEX IF NOT EXISTS idx_analytics_event_name_date ON analytics (event_name, created_at);

-- Disputes performance indexes
CREATE INDEX IF NOT EXISTS idx_disputes_status_priority_created ON disputes (status, priority, created_at);
CREATE INDEX IF NOT EXISTS idx_disputes_type_status ON disputes (type, status);
CREATE INDEX IF NOT EXISTS idx_disputes_initiator_status ON disputes (initiator_id, status);
CREATE INDEX IF NOT EXISTS idx_disputes_order_status ON disputes (order_id, status);

-- Affiliate performance indexes
CREATE INDEX IF NOT EXISTS idx_affiliate_referrals_status_created ON affiliate_referrals (status, created_at);
CREATE INDEX IF NOT EXISTS idx_affiliate_payouts_status_created ON affiliate_payouts (status, created_at);
CREATE INDEX IF NOT EXISTS idx_affiliate_referrals_affiliate_status ON affiliate_referrals (affiliate_id, status);

-- Wishlists performance indexes
CREATE INDEX IF NOT EXISTS idx_wishlists_user_created ON wishlists (user_id, created_at);
CREATE INDEX IF NOT EXISTS idx_wishlists_menu_item_user ON wishlists (menu_item_id, user_id);

-- Cart items performance indexes
CREATE INDEX IF NOT EXISTS idx_cart_items_user_updated ON cart_items (user_id, updated_at);
CREATE INDEX IF NOT EXISTS idx_cart_items_menu_item_user ON cart_items (menu_item_id, user_id);

-- Notifications performance indexes
CREATE INDEX IF NOT EXISTS idx_popup_notifications_target_active ON popup_notifications (target_user_id, is_active);
CREATE INDEX IF NOT EXISTS idx_popup_notifications_audience_dates ON popup_notifications (target_audience, start_date, end_date);
CREATE INDEX IF NOT EXISTS idx_popup_notifications_active_dates ON popup_notifications (is_active, start_date, end_date);

-- Coupons performance indexes
CREATE INDEX IF NOT EXISTS idx_coupons_active_dates ON coupons (is_active, starts_at, expires_at);
CREATE INDEX IF NOT EXISTS idx_coupons_code_active ON coupons (code, is_active);
CREATE INDEX IF NOT EXISTS idx_coupon_usages_coupon_user ON coupon_usages (coupon_id, user_id);

-- Site settings performance indexes
CREATE INDEX IF NOT EXISTS idx_site_settings_group_public ON site_settings (group, is_public);

-- Logs performance indexes
CREATE INDEX IF NOT EXISTS idx_logs_level_user_date ON logs (level, user_id, created_at);
CREATE INDEX IF NOT EXISTS idx_logs_user_date ON logs (user_id, created_at);

-- Daily stats performance indexes
CREATE INDEX IF NOT EXISTS idx_daily_stats_date_restaurant ON daily_stats (date, restaurant_id);
CREATE INDEX IF NOT EXISTS idx_daily_stats_date_user ON daily_stats (date, user_id);

-- Rider specific indexes
CREATE INDEX IF NOT EXISTS idx_rider_schedules_rider_day_available ON rider_schedules (rider_id, day_of_week, is_available);
CREATE INDEX IF NOT EXISTS idx_rider_locations_rider_online_time ON rider_locations (rider_id, is_online, created_at);
CREATE INDEX IF NOT EXISTS idx_rider_assignments_rider_status_assigned ON rider_assignments (rider_id, status, assigned_at);

-- Menu categories performance indexes
CREATE INDEX IF NOT EXISTS idx_menu_categories_restaurant_active_sort ON menu_categories (restaurant_id, is_active, sort_order);
CREATE INDEX IF NOT EXISTS idx_menu_categories_parent_active ON menu_categories (parent_id, is_active);

-- Payment methods performance indexes
CREATE INDEX IF NOT EXISTS idx_payment_methods_user_type_active ON payment_methods (user_id, type, is_default);
CREATE INDEX IF NOT EXISTS idx_payment_methods_type_provider ON payment_methods (type, provider);

-- Review votes performance indexes
CREATE INDEX IF NOT EXISTS idx_review_votes_review_vote ON review_votes (review_id, vote);

-- User profiles performance indexes
CREATE INDEX IF NOT EXISTS idx_user_profiles_user_updated ON user_profiles (user_id, updated_at);

-- Promo codes performance indexes (if table exists)
CREATE INDEX IF NOT EXISTS idx_promo_codes_code_active_dates ON promo_codes (code, is_active, expires_at);

-- ============================================================================
-- Query Cache Configuration
-- ============================================================================

-- Enable query cache if not already enabled
SET GLOBAL query_cache_type = ON;
SET GLOBAL query_cache_size = 67108864; -- 64MB

-- ============================================================================
-- Table Optimization
-- ============================================================================

-- Optimize all main tables
OPTIMIZE TABLE users;
OPTIMIZE TABLE restaurants;
OPTIMIZE TABLE menu_items;
OPTIMIZE TABLE orders;
OPTIMIZE TABLE order_items;
OPTIMIZE TABLE reviews;
OPTIMIZE TABLE deliveries;
OPTIMIZE TABLE payments;
OPTIMIZE TABLE messages;
OPTIMIZE TABLE wishlists;
OPTIMIZE TABLE cart_items;
OPTIMIZE TABLE disputes;
OPTIMIZE TABLE analytics;
OPTIMIZE TABLE affiliates;
OPTIMIZE TABLE affiliate_referrals;
OPTIMIZE TABLE popup_notifications;

-- ============================================================================
-- Update Table Statistics
-- ============================================================================

-- Analyze tables to update statistics for query optimizer
ANALYZE TABLE users;
ANALYZE TABLE restaurants;
ANALYZE TABLE menu_items;
ANALYZE TABLE orders;
ANALYZE TABLE order_items;
ANALYZE TABLE reviews;
ANALYZE TABLE deliveries;
ANALYZE TABLE payments;
ANALYZE TABLE messages;
ANALYZE TABLE wishlists;
ANALYZE TABLE cart_items;
ANALYZE TABLE disputes;
ANALYZE TABLE analytics;
ANALYZE TABLE affiliates;
ANALYZE TABLE affiliate_referrals;
ANALYZE TABLE popup_notifications;

-- ============================================================================
-- Performance Monitoring Setup
-- ============================================================================

-- Enable slow query log for monitoring
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2; -- Log queries taking more than 2 seconds

-- Log queries not using indexes
SET GLOBAL log_queries_not_using_indexes = 'ON';

-- ============================================================================
-- Full-Text Search Indexes (if not already exist)
-- ============================================================================

-- Add full-text search capabilities
ALTER TABLE restaurants ADD FULLTEXT(name, description, cuisine_type);
ALTER TABLE menu_items ADD FULLTEXT(name, description, ingredients);
ALTER TABLE categories ADD FULLTEXT(name, description);

-- ============================================================================
-- Performance Optimization Complete
-- ============================================================================

-- Insert optimization log entry
INSERT INTO logs (level, message, context, created_at) 
VALUES ('info', 'Database performance optimization completed', 
        JSON_OBJECT('migration', '019_optimize_database_performance', 'indexes_added', 'multiple', 'tables_optimized', 'all'), 
        NOW());
