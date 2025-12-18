# Production Migration: Restaurant Category System

This migration adds the missing `category_id` column to the `restaurants` table and sets up the restaurant categorization system.

## Files Included

1. **`020_add_restaurant_category_id_production.sql`** - Pure SQL migration file
2. **`run_production_migration.php`** - PHP script for easy execution
3. **`021_fix_restaurant_coordinates_nullable_production.sql`** - Coordinates fix SQL migration
4. **`run_production_coordinates_migration.php`** - Coordinates fix PHP script
5. **Updated `data_production.sql`** - Updated production schema
6. **Updated `data.sql`** - Updated development schema

## What This Migration Does

### 1. Database Schema Changes
- Adds `category_id` column to `restaurants` table
- Creates foreign key constraint linking restaurants to categories
- Adds performance index for category queries
- Makes `latitude` and `longitude` fields nullable to prevent creation errors

### 2. Default Categories
Creates 9 default restaurant categories:
- **Cameroonian** (featured) - Traditional Cameroonian cuisine
- **Fast Food** (featured) - Quick service restaurants  
- **Pizza** (featured) - Pizza and Italian cuisine
- **Chinese** - Chinese cuisine
- **Indian** - Indian cuisine
- **Continental** - Continental cuisine
- **Bakery** - Bakery and pastry shops
- **Beverages** - Drinks and beverages
- **Other** - Miscellaneous restaurants

### 3. Automatic Categorization
Intelligently assigns existing restaurants to categories based on their `cuisine_type`:
- Cameroonian/African/Traditional → Cameroonian
- Fast/Quick → Fast Food
- Pizza/Italian → Pizza
- Chinese/Asian → Chinese
- Indian/Curry → Indian
- Continental/European → Continental
- Bakery/Pastry/Bread → Bakery
- Beverage/Drink/Bar → Beverages
- Everything else → Other

### 4. Coordinates Fix
- Makes `latitude` and `longitude` fields nullable to prevent restaurant creation errors
- Assigns default Bamenda coordinates (5.9631, 10.1591) to restaurants without coordinates
- Ensures all restaurants can be created and appear on maps

## How to Run the Migration

### Option 1: Using PHP Scripts (Recommended)

**For Category Migration:**
```bash
# Set your database credentials
export DB_HOST="your_host"
export DB_NAME="your_database"
export DB_USER="your_username"
export DB_PASS="your_password"

# Run the category migration
php database/run_production_migration.php
```

**For Coordinates Fix:**
```bash
# Run the coordinates migration
php database/run_production_coordinates_migration.php
```

**Or create a .env.production file:**
```bash
# Create environment file
cp database/production.env.example .env.production
# Edit .env.production with your production values
# Then run migrations
php database/run_production_migration.php
php database/run_production_coordinates_migration.php
```

### Option 2: Using SQL Files
```bash
# Import the SQL files directly
mysql -u your_username -p your_database < database/migrations/020_add_restaurant_category_id_production.sql
mysql -u your_username -p your_database < database/migrations/021_fix_restaurant_coordinates_nullable_production.sql
```

### Option 3: Manual Execution
1. Connect to your MySQL database
2. Copy and paste the contents of `020_add_restaurant_category_id_production.sql`
3. Execute the SQL commands

## Safety Features

### Idempotent Design
- Can be run multiple times safely
- Checks if column already exists before adding
- Uses `INSERT IGNORE` for categories
- Only updates restaurants with `NULL` category_id

### Error Handling
- Graceful handling of missing tables
- Detailed logging of migration progress
- Rollback-safe operations

### Production Ready
- No data loss
- Minimal downtime
- Backward compatible
- Performance optimized

## Verification

After running the migration, verify it worked:

```sql
-- Check if column exists
DESCRIBE restaurants;

-- Check categories were created
SELECT * FROM categories;

-- Check restaurants have categories
SELECT COUNT(*) FROM restaurants WHERE category_id IS NOT NULL;

-- Check specific restaurant categories
SELECT r.name, c.name as category 
FROM restaurants r 
LEFT JOIN categories c ON r.category_id = c.id 
LIMIT 10;
```

## Troubleshooting

### Common Issues

1. **Foreign Key Constraint Error**
   - Ensure `categories` table exists first
   - Check if categories have been inserted

2. **Permission Denied**
   - Ensure database user has ALTER and CREATE privileges
   - Run as database administrator if needed

3. **Column Already Exists**
   - This is normal if migration was run before
   - The script will skip column creation

### Rollback (if needed)
```sql
-- Remove foreign key constraint
ALTER TABLE restaurants DROP FOREIGN KEY restaurants_category_id_foreign;

-- Remove index
ALTER TABLE restaurants DROP INDEX idx_restaurants_category;

-- Remove column
ALTER TABLE restaurants DROP COLUMN category_id;
```

## Benefits After Migration

1. **Category-Based Browsing** - Users can filter restaurants by category
2. **Improved Search** - Better search results with category filtering
3. **Analytics** - Category-based reporting and statistics
4. **SEO** - Better URL structure with category slugs
5. **Performance** - Optimized queries with proper indexing

## Support

If you encounter any issues:
1. Check the error logs
2. Verify database permissions
3. Ensure all required tables exist
4. Contact the development team

---

**Migration Version:** 020  
**Created:** October 2025  
**Compatibility:** MySQL 5.7+, MariaDB 10.2+  
**Estimated Runtime:** 1-5 minutes (depending on restaurant count)

