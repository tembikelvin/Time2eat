# Tranzak Payment Gateway Webhook Setup

## Overview

This document describes the Tranzak Payment Notification (TPN) webhook implementation for Time2Eat, based on the official Tranzak documentation: https://docs.developer.tranzak.me

## Implementation Summary

### Files Modified/Created

1. **`src/services/TranzakPaymentService.php`**
   - Updated `handlePaymentNotification()` to handle TPN format
   - Added `handleRequestCompleted()` for payment success/failure events
   - Added `handleRefundCompleted()` for refund events
   - Updated `verifyNotificationSignature()` to verify authKey from .env
   - Added `getWebhookAuthKey()` to retrieve auth key from database or .env
   - Added `mapTranzakStatusToPaymentStatus()` to map Tranzak statuses
   - Added `createPaymentFromTPN()` to create payment records from webhooks
   - Added `processFailedPayment()` to handle failed payments

2. **`api/payment-tranzak.php`**
   - Updated `handlePaymentNotification()` to properly parse TPN JSON
   - Added comprehensive error handling and logging

3. **`src/controllers/PaymentController.php`**
   - Updated `webhook()` method to handle TPN format
   - Added proper JSON parsing and error handling

## Webhook URL

### Production:
```
https://www.time2eat.org/api/payment/tranzak/notify
```

### Development (requires ngrok):
```
https://your-ngrok-url.ngrok.io/eat/api/payment/tranzak/notify
```

## Environment Configuration

### Required Environment Variables

Both `.env` and `.env.production` should contain:

```env
# Tranzak Configuration
TRANZAK_APP_ID=aplp1yf70tbaay
TRANZAK_API_KEY=PROD_1420487AE95C4A8AA2704A0773593E68
TRANZAK_SANDBOX_API_KEY=SAND_538F8F22667B4FF7B061E8B07232B48C
TRANZAK_ENVIRONMENT=production  # or 'sandbox' for development
TRANZAK_WEBHOOK_AUTH_KEY=q@dLi{3>CMd.p-.$cG0Wqg1@EFk8XxRmhf?
```

**Note:** The `TRANZAK_WEBHOOK_AUTH_KEY` must match the auth key configured in the Tranzak Developer Portal for your webhook.

## Webhook Event Types Supported

### 1. REQUEST.COMPLETED
Triggered when a payment request is completed (successful or failed).

**Statuses Handled:**
- `SUCCESSFUL` → Payment status: `paid`, Order status: `confirmed`
- `FAILED` → Payment status: `failed`, Order status: `cancelled`
- `PENDING` → Payment status: `pending`
- `CANCELLED` → Payment status: `cancelled`
- `EXPIRED` → Payment status: `failed`

### 2. REFUND.COMPLETED
Triggered when a refund is processed.

**Statuses Handled:**
- `SUCCESSFUL` → Payment status: `refunded`, Order status: `refunded`

## TPN Payload Format

### Successful Payment Example:
```json
{
  "name": "Tranzak Payment Notification (TPN)",
  "version": "1.0",
  "eventType": "REQUEST.COMPLETED",
  "appId": "aplp1yf70tbaay",
  "resourceId": "REQ231121CIPGSDPMO6J",
  "webhookId": "WHM9G4RW7DDXMLIXU6EBZ5",
  "authKey": "q@dLi{3>CMd.p-.$cG0Wqg1@EFk8XxRmhf?",
  "creationDateTime": "2023-11-21 03:09:52",
  "resource": {
    "requestId": "REQ231121CIPGSDPMO6J",
    "status": "SUCCESSFUL",
    "transactionStatus": "SUCCESSFUL",
    "amount": 1000,
    "currencyCode": "XAF",
    "description": "Order #12345",
    "mchTransactionRef": "ORDER_12345",
    "transactionId": "TX231121OLN76RPLMWFJ",
    "payer": {
      "name": "John Doe",
      "paymentMethod": "MTN Momo",
      "accountId": "237675123456"
    }
  }
}
```

### Failed Payment Example:
```json
{
  "name": "Tranzak Payment Notification (TPN)",
  "version": "1.0",
  "eventType": "REQUEST.COMPLETED",
  "appId": "aplp1yf70tbaay",
  "resourceId": "REQ231121BKKJYX5PLZ8",
  "webhookId": "WHM9G4RW7DDXMLIXU6EBZ5",
  "authKey": "q@dLi{3>CMd.p-.$cG0Wqg1@EFk8XxRmhf?",
  "creationDateTime": "2023-11-21 03:12:39",
  "resource": {
    "requestId": "REQ231121BKKJYX5PLZ8",
    "status": "FAILED",
    "transactionStatus": "FAILED",
    "errorCode": 5002,
    "errorMessage": "SYSTEM_GENERAL_VALIDATION_ERROR"
  }
}
```

## Security Features

### 1. Auth Key Verification
- Webhook payload includes `authKey` field
- System compares received `authKey` with `TRANZAK_WEBHOOK_AUTH_KEY` from .env
- Uses `hash_equals()` for timing-attack-safe comparison
- Returns 400 if auth key doesn't match

### 2. App ID Verification
- Verifies that `appId` in TPN matches configured `TRANZAK_APP_ID`
- Prevents processing webhooks from other applications

### 3. TPN Format Validation
- Validates that payload has `name` field equal to "Tranzak Payment Notification (TPN)"
- Validates JSON structure before processing

## Database Updates

The webhook handler updates the following tables:

1. **`payments` table:**
   - Updates `status` based on TPN status
   - Stores full TPN data in `response_data` JSON field
   - Updates `updated_at` timestamp

2. **`orders` table:**
   - Updates `payment_status` to 'paid' for successful payments
   - Updates `status` to 'confirmed' for successful payments
   - Updates `status` to 'cancelled' for failed payments
   - Updates `status` to 'refunded' for refunds

## Error Handling

### Development Mode
- More verbose error messages in responses
- Detailed error logging
- Auth key verification is optional (allows testing without key)

### Production Mode
- Generic error messages in responses
- Detailed error logging (server-side only)
- Strict auth key verification required

## Logging

All webhook events are logged with:
- Raw webhook input
- Parsed TPN data
- Processing results
- Error details (if any)

Logs can be found in:
- PHP error log (configured in php.ini)
- Application error logs (if configured)

## Testing

### Local Testing with ngrok:

1. **Start ngrok:**
   ```bash
   ngrok http 80
   ```

2. **Copy HTTPS URL:**
   - Example: `https://abc123.ngrok.io`

3. **Configure webhook in Tranzak Portal:**
   - URL: `https://abc123.ngrok.io/eat/api/payment/tranzak/notify`
   - Event: `REQUEST.COMPLETED`
   - Auth Key: Generate and save

4. **Update .env:**
   ```env
   TRANZAK_WEBHOOK_AUTH_KEY=your_generated_auth_key
   ```

5. **Make test payment:**
   - Create order in Time2Eat
   - Pay with Tranzak
   - Check ngrok console for webhook
   - Verify order status updated

### Production Testing:

1. **Configure webhook in Tranzak Portal:**
   - URL: `https://www.time2eat.org/api/payment/tranzak/notify`
   - Event: `REQUEST.COMPLETED`
   - Auth Key: Generate strong key

2. **Update .env.production:**
   ```env
   TRANZAK_WEBHOOK_AUTH_KEY=your_production_auth_key
   ```

3. **Make small test payment:**
   - Create test order (100-500 XAF)
   - Pay with Tranzak
   - Verify webhook received
   - Check order status updated

## Troubleshooting

### Webhook Not Received

1. **Check webhook URL:**
   - Must be publicly accessible (HTTPS)
   - Must match exactly in Tranzak portal

2. **Check server logs:**
   - Look for webhook requests in access logs
   - Check error logs for processing errors

3. **Test webhook endpoint:**
   ```bash
   curl -X POST https://www.time2eat.org/api/payment/tranzak/notify \
     -H "Content-Type: application/json" \
     -d '{"test": "data"}'
   ```

### Auth Key Mismatch

1. **Verify auth key in .env:**
   - Check `TRANZAK_WEBHOOK_AUTH_KEY` value
   - Ensure no extra spaces or quotes

2. **Verify auth key in Tranzak Portal:**
   - Check webhook configuration
   - Regenerate if needed

3. **Check logs:**
   - Look for "Auth key mismatch" messages
   - Compare expected vs received keys (first 10 chars)

### Payment Status Not Updating

1. **Check TPN processing:**
   - Look for "Tranzak TPN received" in logs
   - Verify event type is `REQUEST.COMPLETED`

2. **Check payment record:**
   - Verify payment exists in database
   - Check `transaction_id` matches TPN

3. **Check order status:**
   - Verify order exists
   - Check `order_id` matches `mchTransactionRef`

## Support

### Tranzak Support:
- **Documentation:** https://docs.developer.tranzak.me
- **Developer Portal:** https://developer.tranzak.me
- **Support Email:** support@tranzak.net

### Time2Eat Files:
- **Service:** `src/services/TranzakPaymentService.php`
- **Webhook Endpoint:** `api/payment-tranzak.php`
- **Controller:** `src/controllers/PaymentController.php`

## Last Updated

**Date:** 2025-01-05  
**Status:** ✅ Production Ready  
**Version:** 1.0.0

