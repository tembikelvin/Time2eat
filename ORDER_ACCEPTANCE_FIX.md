# Order Acceptance Bug Fix

## Problem

Order **T2E2511164460** (and potentially others) disappeared from the RiderDashboard's "Available Orders" list after a failed acceptance attempt.

### Root Cause

The `acceptOrder()` method in `RiderDashboardController` had a critical flaw:

1. **Step 1**: It would assign the rider to the order (`assignRider()` sets `rider_id` and changes status to `'assigned'`)
2. **Step 2**: It would then try to create a delivery record
3. **Problem**: If Step 2 failed, the order was already assigned in Step 1, but there was no delivery record

This left the order in a "stuck" state:
- `rider_id` is set (so it won't show in available orders - query requires `rider_id IS NULL`)
- `status` is `'assigned'` (not `'ready'`, so it won't show in available orders)
- No delivery record exists (so it won't show in active deliveries)

### Why Orders Disappear

The `getAvailableOrdersForRider()` query in `Order.php` only returns orders where:
```sql
WHERE o.status = 'ready' AND o.rider_id IS NULL
```

Once an order is assigned (even if delivery creation fails), it no longer meets these criteria and disappears from the available orders list.

## Solution

### 1. Transaction-Based Fix

Modified `acceptOrder()` in `RiderDashboardController.php` to use database transactions:

- **Before**: Order assignment and delivery creation were separate operations
- **After**: Both operations are wrapped in a transaction
- **If delivery creation fails**: The entire transaction is rolled back, restoring the order to its original state

### 2. Recovery Tools

Created two recovery scripts:

#### `check_order_status.php`
- Checks the status of a specific order
- Shows order details, delivery record status, and whether it should be available
- Provides SQL to fix stuck orders

**Usage:**
```bash
php check_order_status.php T2E2511164460
```

#### `recover_stuck_order.php`
- Lists all stuck orders (assigned but no delivery record)
- Can fix a specific order or all stuck orders at once

**Usage:**
```bash
# List all stuck orders
php recover_stuck_order.php

# Fix a specific order
php recover_stuck_order.php T2E2511164460

# Fix all stuck orders
php recover_stuck_order.php --fix-all
```

### 3. New Method: `unassignRider()`

Added `unassignRider()` method to `Order.php` model:
- Resets `rider_id` to NULL
- Changes status back to `'ready'`
- Makes the order available again

## Files Modified

1. **src/controllers/RiderDashboardController.php**
   - Updated `acceptOrder()` to use transactions
   - Added proper error handling and rollback logic

2. **src/models/Order.php**
   - Added `unassignRider()` method for recovery

3. **check_order_status.php** (new)
   - Diagnostic script to check order status

4. **recover_stuck_order.php** (new)
   - Recovery script to fix stuck orders

## How to Fix Order T2E2511164460

Run the recovery script:
```bash
php recover_stuck_order.php T2E2511164460
```

This will:
1. Check if the order is stuck
2. Reset `rider_id` to NULL
3. Change status back to `'ready'`
4. Make it available again in the RiderDashboard

## Prevention

The transaction-based fix ensures this won't happen again:
- If delivery creation fails, the order assignment is automatically rolled back
- The order remains in `'ready'` status with `rider_id = NULL`
- It will continue to appear in available orders

## Testing

To test the fix:
1. Try accepting an order
2. If delivery creation fails (simulate by temporarily breaking the delivery model), the order should remain available
3. The order should still appear in the available orders list

## Related Code

- **Available Orders Query**: `src/models/Order.php::getAvailableOrdersForRider()`
- **Order Acceptance**: `src/controllers/RiderDashboardController.php::acceptOrder()`
- **Rider Assignment**: `src/models/Order.php::assignRider()`

