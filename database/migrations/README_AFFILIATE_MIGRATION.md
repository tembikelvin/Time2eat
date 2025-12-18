# Affiliate System Migration

This directory contains migration scripts to fix affiliate system synchronization issues.

## Problem Description

Some users had `affiliate_code` in the `users` table but no corresponding record in the `affiliates` table, causing:
- Sidebar shows "Customer • Affiliate" (checks `users.affiliate_code`)
- Affiliate page shows "Join Affiliate Program" (checks `affiliates` table)

Additionally, some users had mismatched affiliate codes between the two tables.

## Migration Scripts

### 1. `fix_missing_affiliate_records.sql`
- **Purpose**: Development/local environment migration
- **Usage**: Run directly in MySQL
- **Features**: 
  - Identifies users with missing affiliate records
  - Creates missing affiliate records
  - Provides verification queries

### 2. `fix_missing_affiliate_records_production.sql`
- **Purpose**: Production environment migration
- **Usage**: Run via PHP script or MySQL
- **Features**:
  - Transaction-based for data integrity
  - Migration logging
  - Safe mode handling
  - Comprehensive error handling

### 3. `fix_affiliate_code_mismatches.sql`
- **Purpose**: Fix mismatches between users.affiliate_code and affiliates.affiliate_code
- **Usage**: Run directly in MySQL
- **Features**:
  - Identifies code mismatches
  - Updates users table to match affiliates table (source of truth)
  - Provides verification queries

### 4. `fix_affiliate_code_mismatches_production.sql`
- **Purpose**: Production version of mismatch fix
- **Usage**: Run via PHP script or MySQL
- **Features**:
  - Transaction-based for data integrity
  - Migration logging
  - Safe mode handling

### 5. `create_migration_log_table.sql`
- **Purpose**: Creates migration tracking table
- **Usage**: Run before production migrations
- **Features**: Tracks migration status and results

## Running the Migration

### Development Environment
```bash
# Fix missing records
mysql -u root -p -e "USE time2eat; source database/migrations/fix_missing_affiliate_records.sql;"

# Fix mismatches
mysql -u root -p -e "USE time2eat; source database/migrations/fix_affiliate_code_mismatches.sql;"
```

### Production Environment
```bash
# Option 1: Using complete PHP script (Recommended)
php scripts/run_complete_affiliate_migration.php

# Option 2: Individual migrations
php scripts/run_affiliate_migration.php
mysql -h your_host -u your_user -p your_database < database/migrations/fix_affiliate_code_mismatches_production.sql
```

## Files Updated

### Database Files
- `database/data.sql` - Added affiliate synchronization to schema
- `database/data_production.sql` - Added affiliate synchronization to production schema

### Application Files
- `src/controllers/CustomerDashboardController.php` - Enhanced affiliate page logic
- `src/traits/AuthTrait.php` - Added affiliate fields to user object
- `src/controllers/AdminAffiliateController.php` - Enhanced admin affiliate management

## Verification

After running the migration, verify the fix:

1. **Check Peter Jones account**:
   - Sidebar should show "Customer • Affiliate"
   - Affiliate page should show affiliate dashboard (not join message)

2. **Database verification**:
   ```sql
   SELECT 
       u.id, u.first_name, u.last_name,
       u.affiliate_code as user_code,
       a.affiliate_code as affiliate_code,
       CASE WHEN u.affiliate_code = a.affiliate_code THEN 'SYNCED' ELSE 'MISMATCH' END as status
   FROM users u
   LEFT JOIN affiliates a ON u.id = a.user_id
   WHERE u.affiliate_code IS NOT NULL;
   ```

## Results

The migration successfully:
- ✅ Fixed Peter Jones affiliate status
- ✅ Created missing affiliate records for 2 users
- ✅ Fixed affiliate code mismatches for 2 users
- ✅ Synchronized affiliate codes between tables
- ✅ Enhanced application logic for future consistency

## Migration Log

The migration was run on 2025-10-16 and fixed:
- **Missing Records**: 2 users (Peter Jones, CLI Test)
- **Code Mismatches**: 2 users (John Doe, abouga erg)
- **Total Records Fixed**: 4 users
- **Final Status**: All affiliate data perfectly synchronized

## Future Prevention

The application now includes:
- Automatic detection and creation of missing affiliate records
- Synchronized updates between `users` and `affiliates` tables
- Enhanced error handling and logging
- Mismatch detection and correction
