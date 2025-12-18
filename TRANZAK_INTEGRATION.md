# Tranzak Integration Quick Reference

## ✅ Status: WORKING

**Sandbox**: Fully functional ✅  
**Production**: Authentication working, payment API restricted (contact Tranzak Support) ⚠️

---

## Configuration

### Sandbox
- **App ID**: `apzt0jhgumdp32`
- **API Key**: `SAND_12F8318531964368B3932A306B103EEA`
- **Base URL**: `https://sandbox.dsapi.tranzak.me`
- **Status**: ✅ **Ready to use**

### Production
- **App ID**: `apclnmu2spcmz8`
- **API Key**: `PROD_B4C0413513194877B17AF65EAF190B40`
- **Base URL**: `https://dsapi.tranzak.me`
- **Status**: ⚠️ **Authentication works, payment API restricted**

---

## Environment Variables

```ini
# Production App ID
TRANZAK_APP_ID=apclnmu2spcmz8

# Sandbox App ID
TRANZAK_SANDBOX_APP_ID=apzt0jhgumdp32

# API Keys
TRANZAK_API_KEY=PROD_B4C0413513194877B17AF65EAF190B40
TRANZAK_SANDBOX_API_KEY=SAND_12F8318531964368B3932A306B103EEA

# Switch between environments
TRANZAK_ENVIRONMENT=sandbox  # or 'production'

# Webhook authentication
TRANZAK_WEBHOOK_AUTH_KEY=Ji%^WPYKRJ+YSFl9Gkc^z(F2fTbffE$lb+g
```

---

## Required HTTP Headers

### Authentication Request
```
Content-Type: application/json
X-App-ID: {app_id}
X-App-Env: {sandbox|production}
```

### API Requests (with Bearer token)
```
Content-Type: application/json
Authorization: Bearer {token}
X-App-ID: {app_id}
X-App-Key: {api_key}
X-App-Env: {sandbox|production}
```

---

## Switch Environments

### Use Sandbox (for testing)
```bash
# In .env
TRANZAK_ENVIRONMENT=sandbox
```

### Use Production (when activated)
```bash
# In .env
TRANZAK_ENVIRONMENT=production
```

---

## Test Payment Flow

1. **Run Test Script**: http://localhost/eat/test_tranzak_api.php
2. **Get Payment URL**: From response `links.paymentAuthUrl`
3. **Complete Payment**: Visit the payment URL in browser
4. **Webhook**: Receives notification at `/api/payment/tranzak/notify`

---

## Production Account Issue

**Error**: "Access to the payments API has been temporarily restricted"

**Action Required**: Contact Tranzak Support to activate payment API access for production account.

**Contact**: https://developer.tranzak.me

---

## Key Files

- **Service**: `src/services/TranzakPaymentService.php`
- **Config**: `.env` and `.env.production`
- **Test**: `test_tranzak_api.php`
- **Webhook**: `/api/payment/tranzak/notify`

---

## What Was Fixed

**Problem**: Authentication Error 40022  
**Cause**: Missing HTTP headers (X-App-ID, X-App-Key, X-App-Env)  
**Solution**: Added all required headers to authentication and API requests

---

## Documentation

- **API Docs**: https://docs.developer.tranzak.me/
- **Developer Portal**: https://developer.tranzak.me
- **Full Walkthrough**: See `walkthrough.md` artifact

---

## Summary

✅ **Sandbox**: Fully working - use for development  
✅ **Production Auth**: Working  
⚠️ **Production Payments**: Contact Tranzak Support to activate  

**The integration code is complete and ready!** 🎉
