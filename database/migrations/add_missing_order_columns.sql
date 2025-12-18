-- ============================================================================
-- MIGRATION: Add Missing Order Columns
-- ============================================================================
-- Date: 2025-11-01
-- Purpose: Fix checkout errors due to missing payment and confirmation columns
-- Author: Augment AI Assistant
-- Version: 1.0.0
-- ============================================================================

-- This migration is SAFE to run multiple times (idempotent)
-- It uses "IF NOT EXISTS" to prevent errors if columns already exist

-- ============================================================================
-- STEP 1: Add payment_method column
-- ============================================================================
-- Stores the payment method used for the order
-- Values: 'cash_on_delivery', 'tranzak', 'mobile_money', etc.
-- ============================================================================

SET @dbname = DATABASE();
SET @tablename = 'orders';
SET @columnname = 'payment_method';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 'Column payment_method already exists' AS msg;",
  "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'cash_on_delivery' COMMENT 'Payment method used' AFTER total_amount;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- STEP 2: Add payment_status column
-- ============================================================================
-- Stores the current status of the payment
-- Values: 'pending', 'completed', 'failed', 'refunded', 'cancelled'
-- ============================================================================

SET @columnname = 'payment_status';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 'Column payment_status already exists' AS msg;",
  "ALTER TABLE orders ADD COLUMN payment_status VARCHAR(50) DEFAULT 'pending' COMMENT 'Payment status' AFTER payment_method;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- STEP 3: Add payment_transaction_id column
-- ============================================================================
-- Stores the transaction ID from payment gateway (Tranzak, etc.)
-- NULL for cash on delivery
-- ============================================================================

SET @columnname = 'payment_transaction_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 'Column payment_transaction_id already exists' AS msg;",
  "ALTER TABLE orders ADD COLUMN payment_transaction_id VARCHAR(255) NULL COMMENT 'Transaction ID from payment gateway' AFTER payment_status;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- STEP 4: Add customer_confirmed column
-- ============================================================================
-- Boolean flag indicating if customer confirmed receipt of order
-- Used for order completion workflow
-- ============================================================================

SET @columnname = 'customer_confirmed';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 'Column customer_confirmed already exists' AS msg;",
  "ALTER TABLE orders ADD COLUMN customer_confirmed TINYINT(1) DEFAULT 0 COMMENT 'Customer confirmed receipt' AFTER status;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- STEP 5: Add customer_confirmed_at column
-- ============================================================================
-- Timestamp when customer confirmed receipt
-- NULL if not yet confirmed
-- ============================================================================

SET @columnname = 'customer_confirmed_at';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 'Column customer_confirmed_at already exists' AS msg;",
  "ALTER TABLE orders ADD COLUMN customer_confirmed_at DATETIME NULL COMMENT 'When customer confirmed receipt' AFTER customer_confirmed;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- STEP 6: Update existing orders with default values
-- ============================================================================
-- Set payment_method and payment_status for existing orders
-- ============================================================================

UPDATE orders
SET
    payment_method = COALESCE(payment_method, 'cash_on_delivery'),
    payment_status = COALESCE(payment_status, 'pending')
WHERE payment_method IS NULL OR payment_status IS NULL;

-- ============================================================================
-- STEP 7: Create index on payment_status for faster queries
-- ============================================================================

SET @indexname = 'idx_payment_status';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = @indexname)
  ) > 0,
  "SELECT 'Index idx_payment_status already exists' AS msg;",
  "CREATE INDEX idx_payment_status ON orders(payment_status);"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- STEP 8: Create index on customer_confirmed for faster queries
-- ============================================================================

SET @indexname = 'idx_customer_confirmed';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = @indexname)
  ) > 0,
  "SELECT 'Index idx_customer_confirmed already exists' AS msg;",
  "CREATE INDEX idx_customer_confirmed ON orders(customer_confirmed, customer_confirmed_at);"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- VERIFICATION: Show added columns
-- ============================================================================

SELECT
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'orders'
  AND COLUMN_NAME IN ('payment_method', 'payment_status', 'payment_transaction_id', 'customer_confirmed', 'customer_confirmed_at')
ORDER BY ORDINAL_POSITION;

-- ============================================================================
-- MIGRATION COMPLETE
-- ============================================================================
-- All columns have been added successfully
-- The migration is idempotent and can be run multiple times safely
-- ============================================================================

