# Time2Eat Database Schema

## Overview

This directory contains the complete database schema for the Time2Eat food delivery platform. The schema is designed to support a comprehensive multi-role system with advanced features including affiliate programs, real-time tracking, analytics, and performance optimization.

## Database Structure

### Core Tables

#### Users & Authentication
- **`users`** - Main user table with multi-role support (customer, vendor, rider, admin)
- **`user_profiles`** - Extended user information and preferences
- **`payment_methods`** - User payment methods and cards

#### Restaurant & Menu Management
- **`categories`** - Food categories with hierarchical support
- **`restaurants`** - Restaurant information with geolocation and business details
- **`menu_items`** - Menu items with comprehensive product information
- **`menu_item_variants`** - Size options, add-ons, and customizations

#### Order Management
- **`orders`** - Complete order tracking with status history
- **`order_items`** - Individual items within orders
- **`order_status_history`** - Detailed order status tracking
- **`deliveries`** - Delivery-specific information and tracking
- **`rider_schedules`** - Rider availability and working hours
- **`rider_locations`** - Real-time GPS tracking for riders

#### Affiliate System
- **`affiliates`** - Affiliate program management
- **`affiliate_referrals`** - Referral tracking and commissions
- **`affiliate_payouts`** - Payout history and management

#### Payment System
- **`payments`** - All payment transactions and history
- **`coupons`** - Discount coupons and promotions
- **`coupon_usages`** - Coupon usage tracking

#### Review & Rating System
- **`reviews`** - Reviews for restaurants, menu items, and riders
- **`review_votes`** - Helpfulness voting for reviews

#### Communication & Support
- **`popup_notifications`** - System notifications for users
- **`messages`** - Direct messaging between users
- **`disputes`** - Order disputes and resolution tracking

#### User Interaction
- **`wishlists`** - User favorites and wishlists
- **`cart_items`** - Persistent shopping cart

#### System & Analytics
- **`site_settings`** - Application configuration
- **`logs`** - System logs and debugging information
- **`analytics`** - Dynamic analytics data
- **`daily_stats`** - Aggregated daily statistics

## Key Features

### 1. Multi-Role System
- **Customer**: Order food, manage profile, track deliveries
- **Vendor**: Manage restaurant, menu, orders, analytics
- **Rider**: Accept deliveries, track earnings, manage schedule
- **Admin**: System management, user management, reports

### 2. Affiliate Program
- Referral tracking with custom affiliate codes
- Commission-based earnings system
- Automated payout management
- Multi-level affiliate support

### 3. Real-Time Tracking
- GPS-based rider location tracking
- Order status updates with timestamps
- Live delivery tracking for customers
- Performance analytics and monitoring

### 4. Advanced Analytics
- Dynamic query system for custom reports
- Daily aggregated statistics for performance
- Revenue tracking and commission calculations
- User behavior analytics

### 5. Performance Optimizations
- Comprehensive indexing strategy
- Full-text search capabilities
- Connection pooling support
- Query optimization for common operations

## Installation

### Method 1: Using PHP Import Script

```bash
# Command line
php database/import.php --verbose

# Or via web browser
http://your-domain.com/database/import.php
```

### Method 2: Using Migration Script

```bash
# Command line
php database/migrate.php --verbose

# Or via web browser
http://your-domain.com/database/migrate.php
```

### Method 3: Direct MySQL Import

```bash
# Using MySQL command line
mysql -u username -p database_name < database/schema.sql

# Using phpMyAdmin
# 1. Create database 'time2eat'
# 2. Import database/schema.sql file
```

## Configuration

### Environment Variables

```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=time2eat
DB_USERNAME=root
DB_PASSWORD=your_password
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

### Database Requirements

- **MySQL**: 5.7+ or 8.0+ (recommended)
- **MariaDB**: 10.3+ (alternative)
- **Storage Engine**: InnoDB (required for foreign keys)
- **Charset**: utf8mb4 (required for emoji support)
- **Collation**: utf8mb4_unicode_ci

### Recommended MySQL Settings

```sql
-- Performance settings (MySQL 8.0+)
SET GLOBAL innodb_buffer_pool_size = 256M;
SET GLOBAL max_connections = 100;

-- Note: query_cache was removed in MySQL 8.0 and deprecated since 5.7.20
-- For MySQL 5.7 and earlier only:
-- SET GLOBAL query_cache_size = 128M;

-- Security settings
SET GLOBAL sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';
```

## Sample Data

The schema includes sample data for:
- **~78 site settings** (contact info, business rules, payment gateways, currency, PWA config, etc.)
- **3 promo codes** (WELCOME10, SAVE20, FIXED500)
- **Default system configuration** for immediate testing

**Note**: Categories, restaurants, menu items, and user accounts are NOT included in the base schema and should be added through the application interface or separate seed data files.

## Database Triggers

The schema includes 4 automatic triggers for data consistency:

1. **`update_restaurant_rating_after_review_insert`** - Automatically updates restaurant average rating when a new review is added
2. **`update_order_total_after_item_insert`** - Recalculates order totals when order items are added
3. **`update_restaurant_stats_after_order_complete`** - Updates restaurant statistics (total orders, revenue) when orders are completed
4. **`update_affiliate_earnings_after_order_complete`** - Updates affiliate earnings when orders are completed

**Note**: Menu item ratings are calculated via application logic, not database triggers.

## Indexes and Performance

### Primary Indexes
- All tables have optimized primary keys
- Foreign key relationships properly indexed
- Unique constraints on critical fields

### Composite Indexes
- `orders(customer_id, status, created_at)` - Customer order history
- `orders(restaurant_id, status, created_at)` - Restaurant order management
- `menu_items(restaurant_id, is_available, sort_order)` - Menu display
- `reviews(reviewable_type, reviewable_id, rating, status)` - Review queries

### Full-Text Search
- `restaurants(name, description, cuisine_type, tags)` - Restaurant search
- `menu_items(name, description, ingredients)` - Menu item search
- `categories(name, description)` - Category search

## Security Features

### Data Protection
- **Soft deletes** on critical tables (users, restaurants, orders)
- **Audit trails** with created_at/updated_at timestamps
- **Input validation** through database constraints
- **Foreign key constraints** for data integrity

### Access Control
- **Role-based permissions** built into schema
- **User status tracking** (active, inactive, suspended)
- **Payment method encryption** support
- **Secure session management**

## Backup and Maintenance

### Automated Backups
```bash
# Daily backup script
mysqldump -u username -p time2eat > backup_$(date +%Y%m%d).sql

# Compressed backup
mysqldump -u username -p time2eat | gzip > backup_$(date +%Y%m%d).sql.gz
```

### Maintenance Tasks
- **Daily**: Update analytics and statistics tables
- **Weekly**: Clean up old logs and temporary data
- **Monthly**: Optimize tables and rebuild indexes
- **Quarterly**: Archive old orders and reviews

## Troubleshooting

### Common Issues

1. **Foreign Key Errors**
   - Ensure InnoDB storage engine is used
   - Check foreign key constraints are properly defined

2. **Character Set Issues**
   - Use utf8mb4 charset for emoji support
   - Set proper collation (utf8mb4_unicode_ci)

3. **Performance Issues**
   - Check index usage with EXPLAIN queries
   - Monitor slow query log
   - Optimize MySQL configuration

### Verification Queries

```sql
-- Check table creation
SHOW TABLES;

-- Verify foreign keys
SELECT * FROM information_schema.KEY_COLUMN_USAGE 
WHERE REFERENCED_TABLE_SCHEMA = 'time2eat';

-- Check indexes
SHOW INDEX FROM orders;

-- Verify sample data
SELECT COUNT(*) FROM site_settings;  -- Should return ~78
SELECT COUNT(*) FROM promo_codes;    -- Should return 3

-- Check if tables exist
SELECT TABLE_NAME FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'time2eat' 
ORDER BY TABLE_NAME;
```

## Support

For database-related issues:
1. Check the error logs in `storage/logs/database.log`
2. Verify MySQL configuration and permissions
3. Ensure all required extensions are installed (PDO, MySQL)
4. Contact system administrator for server-level issues

## Order Status Workflow

### Status Flow Across Dashboards

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  CUSTOMER   │────▶│   VENDOR    │────▶│    RIDER    │────▶│  CUSTOMER   │
│   Places    │     │  Prepares   │     │  Delivers   │     │  Receives   │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
      │                   │                   │                   │
      ▼                   ▼                   ▼                   ▼
   pending ──▶ preparing ──▶ ready ──▶ assigned ──▶ picked_up ──▶ on_the_way ──▶ delivered
```

### Order Status Definitions

| Status | Description | Controlled By | Next Status |
|--------|-------------|---------------|-------------|
| `pending` | Customer placed order, waiting for vendor confirmation | Vendor | `preparing`, `cancelled` |
| `preparing` | Vendor is actively preparing the food | Vendor | `ready`, `cancelled` |
| `ready` | **Food is ready for pickup** - Appears in rider's available orders | Vendor | `assigned` (when rider accepts) |
| `assigned` | Rider has accepted the order | Rider | `picked_up`, `cancelled` |
| `picked_up` | Rider has collected the order from restaurant | Rider | `on_the_way` |
| `on_the_way` | Rider is en route to customer | Rider | `delivered` |
| `delivered` | Order successfully delivered to customer | Rider | (final state) |
| `cancelled` | Order was cancelled by vendor, customer, or system | Any | (final state) |

### Delivery Status (Parallel to Orders)

The `deliveries` table tracks rider-specific information with statuses:
- `assigned` - Rider has accepted the order
- `accepted` - Rider confirmed acceptance (optional)
- `picked_up` - Order picked up from restaurant
- `on_the_way` - En route to customer
- `delivered` - Successfully delivered
- `cancelled` - Delivery cancelled

**Important Notes:**
1. **Delivery records are auto-created** when riders accept orders or when admins assign riders
2. Both `orders.status` and `deliveries.status` are updated simultaneously
3. Delivery timestamps use `pickup_time` and `delivery_time` columns (NOT `picked_up_at` or `delivered_at`)

### Rider Available Orders Query

For an order to appear in the rider's available orders list (`/rider/available`), it must meet **both** criteria:

```sql
WHERE o.status = 'ready' AND o.rider_id IS NULL
```

This means:
- ✅ Vendor must mark order as `'ready'` (not just `'preparing'`)
- ✅ Order must not be assigned to any rider yet
- ❌ Orders stuck at `'pending'` or `'preparing'` won't show
- ❌ Orders already assigned to riders won't show

**Common Issue**: If riders see no available orders, check that vendors have clicked the "Ready" button to move orders from `'preparing'` to `'ready'` status.

### Required Fields for Delivery Records

When creating delivery records, these fields are **required** (NOT NULL in schema):
- `order_id`, `rider_id`
- `pickup_address` (JSON) - Restaurant location
- `delivery_address` (JSON) - Customer location
- `delivery_fee` (decimal)
- `rider_earnings` (decimal) - Typically 80% of delivery fee
- `platform_commission` (decimal) - Typically 20% of delivery fee

## Static File Serving

### Image Upload System

Menu images and delivery proof photos are stored in:
- **Menu Images**: `public/images/menu/`
- **Delivery Proof**: `public/images/delivery/`

The `.htaccess` configuration ensures static files are served directly:

```apache
# Serve static files directly from public directory
RewriteCond %{REQUEST_URI} \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|webp)$ [NC]
RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} !-f
RewriteCond %{DOCUMENT_ROOT}/eat/public/$1 -f
RewriteRule ^(.*)$ public/$1 [L]
```

**Image URL Format:**
- Database stores: `/images/menu/filename.png`
- Application prepends base URL: `/eat/images/menu/filename.png`
- Apache serves from: `public/images/menu/filename.png`

## Version History

- **v1.0.0** - Initial schema with complete feature set
- **v1.1.0** - Enhanced delivery tracking with auto-creation logic
  - Delivery records auto-created for legacy orders
  - Fixed status validation (`on_the_way` instead of `in_transit`)
  - Corrected column names (`pickup_time`, `delivery_time`)
  - Static file serving optimization
- Supports all Time2Eat platform features
- Optimized for performance and scalability
- Production-ready with comprehensive testing