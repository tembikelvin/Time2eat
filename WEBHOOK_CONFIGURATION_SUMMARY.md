# 🔔 Tranzak Webhook Configuration Summary
## Time2Eat Food Delivery Platform

**Date:** 2025-11-01  
**Status:** Ready for Configuration

---

## 📋 Quick Summary

Based on the **official Tranzak documentation**, here's everything you need to know about webhooks:

### What Are Webhooks?
Webhooks are **automatic notifications** from Tranzak to your server when payment events occur. Instead of constantly checking payment status, Tranzak notifies you immediately.

---

## 🌐 Your Webhook URLs

### ✅ Production (Live Site):
```
https://www.time2eat.org/api/payment/tranzak/notify
```

### ⚠️ Development (Local - Cannot Use):
```
http://localhost/eat/api/payment/tranzak/notify
```
**Problem:** Tranzak cannot send webhooks to `localhost` because it's not publicly accessible.

### 💡 Development (Testing Options):

#### Option 1: Use ngrok (Recommended)
1. Download ngrok: https://ngrok.com/download
2. Start WAMP server
3. Run: `ngrok http 80`
4. Copy the HTTPS URL (e.g., `https://abc123.ngrok.io`)
5. Your webhook URL: `https://abc123.ngrok.io/eat/api/payment/tranzak/notify`

#### Option 2: Use a Staging Server
```
https://staging.time2eat.org/api/payment/tranzak/notify
```

---

## 🔧 How to Configure Webhooks in Tranzak Portal

### Step-by-Step Instructions:

1. **Login to Tranzak Developer Portal**
   - URL: https://developer.tranzak.me
   - Use your Tranzak account credentials

2. **Navigate to Webhooks Section**
   - Look for "Webhooks" or "API Configuration" menu

3. **Create New Webhook for Production**
   - **Event Type:** `REQUEST.COMPLETED`
   - **Webhook URL:** `https://www.time2eat.org/api/payment/tranzak/notify`
   - **Auth Key:** Generate a strong random key (see below)
   - **Description:** Time2Eat Production - Payment Notifications
   - **Status:** Active/Enabled

4. **Generate Auth Key**
   - Use a strong random string (32+ characters)
   - Example: `TZ_WH_2025_time2eat_prod_a1b2c3d4e5f6g7h8`
   - **SAVE THIS KEY SECURELY!** You'll need it for configuration

5. **Save the Webhook**
   - Click Save/Create
   - Copy the Webhook ID if provided

---

## 🔐 Webhook Event Types

According to official documentation, subscribe to:

### 1. **REQUEST.COMPLETED** ⭐ (Required for Time2Eat)
- Triggered when payment is SUCCESSFUL or FAILED
- **This is what you need for food orders**

### 2. **TRANSFER.COMPLETED** (Optional)
- Triggered when disbursement/payout completes
- Useful if you pay vendors/riders via Tranzak

### 3. **BULK_PAYMENT.COMPLETED** (Optional)
- Triggered when bulk payment completes
- Useful for batch payments

### 4. **REFUND.COMPLETED** (Optional)
- Triggered when refund is processed
- Useful for order cancellations

**For now, just configure `REQUEST.COMPLETED`**

---

## 📝 After Creating Webhook - Update Configuration

### Step 1: Add Auth Key to Environment File

**For Production (.env.production):**
```env
# Add this line (replace with your actual auth key)
TRANZAK_WEBHOOK_AUTH_KEY=TZ_WH_2025_time2eat_prod_a1b2c3d4e5f6g7h8
```

**For Development (.env):**
```env
# Add this line (use different key for testing)
TRANZAK_WEBHOOK_AUTH_KEY=TZ_WH_2025_time2eat_dev_test123
```

### Step 2: Update Database

Run this SQL query:
```sql
UPDATE site_settings 
SET value = 'TZ_WH_2025_time2eat_prod_a1b2c3d4e5f6g7h8' 
WHERE `key` = 'tranzak_webhook_auth_key';
```

Or use phpMyAdmin:
1. Open phpMyAdmin
2. Select `time2eat` database
3. Open `site_settings` table
4. Find row where `key` = `tranzak_webhook_auth_key`
5. Update `value` with your auth key
6. Save

---

## 🧪 Testing Webhooks

### Method 1: Use ngrok (Best for Local Testing)

1. **Install ngrok:**
   - Download from https://ngrok.com/download
   - Extract to a folder
   - Add to PATH or run from folder

2. **Start ngrok:**
   ```bash
   ngrok http 80
   ```

3. **Copy the HTTPS URL:**
   - Example: `https://abc123.ngrok.io`

4. **Create Test Webhook in Tranzak:**
   - Event: `REQUEST.COMPLETED`
   - URL: `https://abc123.ngrok.io/eat/api/payment/tranzak/notify`
   - Auth Key: `TZ_WH_2025_time2eat_dev_test123`

5. **Make a Test Payment:**
   - Create an order in Time2Eat
   - Pay with Tranzak
   - Watch ngrok console for incoming webhook

6. **Verify:**
   - Check order status updated
   - Check application logs
   - Check Tranzak TPN logs

### Method 2: Use Tranzak's Trigger TPN

1. Login to https://developer.tranzak.me
2. Go to **API Activity** → **Transaction Notifications**
3. Find a completed transaction
4. Click **Trigger TPN** to resend webhook
5. Check your server logs

---

## 📊 Webhook Payload Example

### Successful Payment:
```json
{
  "name": "Tranzak Payment Notification (TPN)",
  "version": "1.0",
  "eventType": "REQUEST.COMPLETED",
  "appId": "aplp1yf70tbaay",
  "resourceId": "REQ231121CIPGSDPMO6J",
  "webhookId": "WHM9G4RW7DDXMLIXU6EBZ5",
  "authKey": "TZ_WH_2025_time2eat_prod_a1b2c3d4e5f6g7h8",
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

### Failed Payment:
```json
{
  "name": "Tranzak Payment Notification (TPN)",
  "version": "1.0",
  "eventType": "REQUEST.COMPLETED",
  "appId": "aplp1yf70tbaay",
  "resourceId": "REQ231121BKKJYX5PLZ8",
  "webhookId": "WHM9G4RW7DDXMLIXU6EBZ5",
  "authKey": "TZ_WH_2025_time2eat_prod_a1b2c3d4e5f6g7h8",
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

---

## 🔒 Security - Auth Key Verification

**CRITICAL:** Your webhook handler MUST verify the auth key!

### Why?
- Prevents fake payment notifications
- Ensures webhook came from Tranzak
- Protects against fraud

### How It Works:
1. Tranzak sends webhook with `authKey` field
2. Your server compares it with configured key
3. If match → Process payment
4. If no match → Reject (return 401)

### Already Implemented:
The webhook handler in `api/payment-tranzak.php` already verifies the auth key automatically.

---

## 🚀 Setup Instructions

### For Development Environment:

1. **Run Database Setup Script:**
   - Open browser: `http://localhost/eat/setup_tranzak_database.php`
   - This will configure all Tranzak settings in database
   - Follow on-screen instructions

2. **Set Up ngrok (for webhook testing):**
   - Download and install ngrok
   - Run: `ngrok http 80`
   - Copy HTTPS URL

3. **Create Test Webhook:**
   - Login to https://developer.tranzak.me
   - Create webhook with ngrok URL
   - Save auth key

4. **Update Configuration:**
   - Add auth key to `.env`
   - Update database with auth key

5. **Test:**
   - Run: `http://localhost/eat/test_tranzak_simple.php`
   - Make test payment
   - Verify webhook received

### For Production Environment:

1. **Run Database Setup Script on Production:**
   - Upload `setup_tranzak_database.php` to production
   - Access: `https://www.time2eat.org/setup_tranzak_database.php`
   - Run the setup
   - **DELETE the file after setup for security**

2. **Create Production Webhook:**
   - Login to https://developer.tranzak.me
   - Create webhook: `https://www.time2eat.org/api/payment/tranzak/notify`
   - Event: `REQUEST.COMPLETED`
   - Generate strong auth key
   - Save securely

3. **Update Production Configuration:**
   - Add auth key to `.env.production`
   - Update production database

4. **Test with Small Payment:**
   - Make test order (100-500 XAF)
   - Verify webhook received
   - Check order status updated

---

## 📋 Configuration Checklist

### Development:
- [ ] Run `setup_tranzak_database.php` in browser
- [ ] Install and start ngrok
- [ ] Create test webhook in Tranzak portal
- [ ] Add auth key to `.env`
- [ ] Update database with auth key
- [ ] Test with `test_tranzak_simple.php`
- [ ] Make test payment
- [ ] Verify webhook received

### Production:
- [ ] Run `setup_tranzak_database.php` on production
- [ ] Create production webhook in Tranzak portal
- [ ] Add auth key to `.env.production`
- [ ] Update production database
- [ ] Test with small real payment
- [ ] Monitor webhook logs
- [ ] Delete setup script from production

---

## 📞 Support & Resources

### Tranzak:
- **Developer Portal:** https://developer.tranzak.me
- **Documentation:** https://docs.developer.tranzak.me
- **Support Email:** support@tranzak.net
- **Support Phone:** +237 674 460 261

### Time2Eat Files:
- **Webhook Setup Guide:** `TRANZAK_WEBHOOK_SETUP.md` (detailed guide)
- **Database Setup Script:** `setup_tranzak_database.php` (run in browser)
- **Test Script:** `test_tranzak_simple.php`
- **Webhook Handler:** `api/payment-tranzak.php`

### Tools:
- **ngrok:** https://ngrok.com
- **Webhook Testing:** https://webhook.site

---

## 🎯 Quick Start (Right Now!)

### 1. Setup Database (2 minutes):
```
Open browser: http://localhost/eat/setup_tranzak_database.php
Click through the setup
```

### 2. Configure Webhook (5 minutes):
```
1. Login: https://developer.tranzak.me
2. Create webhook for REQUEST.COMPLETED
3. URL: https://www.time2eat.org/api/payment/tranzak/notify
4. Generate auth key
5. Save
```

### 3. Update Configuration (1 minute):
```
Add to .env.production:
TRANZAK_WEBHOOK_AUTH_KEY=your_generated_auth_key

Update database:
UPDATE site_settings SET value = 'your_auth_key' WHERE `key` = 'tranzak_webhook_auth_key';
```

### 4. Test (5 minutes):
```
Open: http://localhost/eat/test_tranzak_simple.php
Verify all tests pass
```

**Total Time: ~15 minutes** ⏱️

---

**Last Updated:** 2025-11-01  
**Status:** ✅ Ready for Configuration  
**Next Action:** Run `setup_tranzak_database.php` in browser

