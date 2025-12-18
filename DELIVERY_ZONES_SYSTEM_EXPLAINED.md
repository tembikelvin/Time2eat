# Delivery Zones System - Complete Documentation

## 📍 Overview

The Time2Eat platform uses a **per-restaurant delivery zone system** where each restaurant can configure its own delivery radius and fees. There is NO global delivery zone management - zones are managed individually per restaurant.

---

## 🏗️ Current System Architecture

### 1. **Restaurant-Level Configuration**

Each restaurant has 3 key delivery settings in the `restaurants` table:

| Column | Type | Default | Description |
|--------|------|---------|-------------|
| `delivery_radius` | DECIMAL(5,2) | 10.00 km | Free zone radius - base fee applies within this distance |
| `delivery_fee` | DECIMAL(6,2) | 500 XAF | Base delivery fee (within free zone) |
| `delivery_fee_per_extra_km` | DECIMAL(6,2) | 100 XAF | Extra fee per km beyond the free zone |

### 2. **How It Works**

```
Restaurant Location: (5.9631, 10.1591)
Delivery Radius: 10 km
Base Fee: 500 XAF
Extra Fee per KM: 100 XAF

Customer Location: 12 km away

Calculation:
- Distance: 12 km
- Within free zone? NO (12 > 10)
- Extra distance: 12 - 10 = 2 km
- Extra fee: 2 × 100 = 200 XAF
- Total delivery fee: 500 + 200 = 700 XAF

Maximum delivery distance: 10 × 2 = 20 km
(System allows up to 2× the delivery radius)
```

### 3. **Delivery Availability Check**

**File:** `src/services/DeliveryFeeService.php`

```php
public function checkDeliveryAvailability(array $restaurant, float $customerLat, float $customerLon): array
{
    $distance = $this->calculateDistance($restaurantLat, $restaurantLon, $customerLat, $customerLon);
    $maxDeliveryDistance = (float)($restaurant['delivery_radius'] ?? 10) * 2;
    
    if ($distance > $maxDeliveryDistance) {
        return [
            'available' => false,
            'reason' => 'Outside delivery zone',
            'distance' => $distance,
            'max_distance' => $maxDeliveryDistance
        ];
    }
    
    return ['available' => true, 'distance' => $distance];
}
```

---

## 🎯 Where Delivery Zones Are Managed

### ✅ **Currently Available:**

#### 1. **Restaurant Edit Page** (Per-Restaurant)
**URL:** `http://localhost/eat/admin/restaurants/{id}/edit`

**Location:** `src/views/admin/restaurants/edit.php` (Lines 143-165)

Admins can set:
- ✅ Free Zone Radius (km)
- ✅ Base Delivery Fee (XAF)
- ✅ Extra Fee per KM (XAF)
- ✅ Minimum Order Amount (XAF)

**Screenshot of UI:**
```
┌─────────────────────────────────────────────────┐
│ Delivery Settings                               │
├─────────────────────────────────────────────────┤
│ Free Zone Radius (km)    [10.00]               │
│ Base fee applies within this distance           │
│                                                  │
│ Base Delivery Fee (XAF)  [500]                 │
│ Fee charged within free zone                    │
│                                                  │
│ Extra Fee per KM (XAF)   [100]                 │
│ Additional fee beyond free zone                 │
│                                                  │
│ Minimum Order (XAF)      [1000]                │
│ Minimum order amount required                   │
└─────────────────────────────────────────────────┘
```

#### 2. **Global Settings** (Platform Defaults)
**URL:** `http://localhost/eat/admin/settings`

**Location:** `src/Time2Eat/Controllers/Admin/SettingsController.php`

Platform-wide defaults:
- ✅ Default delivery fee: 500 XAF
- ✅ Free delivery threshold: 5000 XAF
- ✅ Max delivery distance: 15 km

---

## ❌ **NOT Currently Available:**

### What's Missing:

1. **❌ Global Delivery Zone Management Page**
   - No centralized page to view all restaurant delivery zones
   - No map visualization of delivery zones
   - No bulk zone editing

2. **❌ Zone Visualization**
   - No map showing delivery zone boundaries
   - No visual radius circles on maps
   - No coverage area heatmaps

3. **❌ Zone Analytics**
   - No reports on delivery zone coverage
   - No analysis of orders outside zones
   - No zone optimization suggestions

---

## 🚀 Proposed: Admin Delivery Zone Management

### **New Page:** `http://localhost/eat/admin/deliveries/zones`

### Features to Add:

#### 1. **Zone Overview Map**
```
┌─────────────────────────────────────────────────┐
│ 🗺️ Delivery Zone Coverage Map                  │
├─────────────────────────────────────────────────┤
│                                                  │
│  [Interactive Map showing all restaurants]      │
│  - Each restaurant shown with circle radius     │
│  - Color-coded by delivery fee                  │
│  - Click to edit zone settings                  │
│                                                  │
└─────────────────────────────────────────────────┘
```

#### 2. **Zone Management Table**
```
┌──────────────────────────────────────────────────────────────┐
│ Restaurant      │ Radius │ Base Fee │ Extra/km │ Max Distance│
├──────────────────────────────────────────────────────────────┤
│ Mama Grace      │ 10 km  │ 500 XAF  │ 100 XAF  │ 20 km      │
│ Pizza Palace    │ 15 km  │ 600 XAF  │ 150 XAF  │ 30 km      │
│ Burger King     │ 8 km   │ 400 XAF  │ 80 XAF   │ 16 km      │
└──────────────────────────────────────────────────────────────┘
```

#### 3. **Bulk Zone Editor**
- Select multiple restaurants
- Apply same zone settings to all
- Useful for chain restaurants

#### 4. **Zone Analytics**
- Orders outside delivery zones (rejected)
- Average delivery distance per restaurant
- Zone coverage vs demand heatmap
- Suggested zone expansions

---

## 📊 Configuration Files

### 1. **config/maps.php**
Contains predefined delivery zones for cities:

```php
'delivery_zones' => [
    'default_radius' => 10,
    'max_radius' => 25,
    'zones' => [
        'bamenda_center' => [
            'name' => 'Bamenda Center',
            'center' => [5.9631, 10.1591],
            'radius' => 5
        ],
        'bamenda_extended' => [
            'name' => 'Bamenda Extended',
            'center' => [5.9631, 10.1591],
            'radius' => 15
        ]
    ]
]
```

**Note:** These are NOT currently used in the system. They're just configuration placeholders.

### 2. **config/app.php**
Platform-wide delivery settings:

```php
'business' => [
    'delivery_fee' => 500, // XAF
    'free_delivery_threshold' => 5000, // XAF
    'max_delivery_distance' => 15, // kilometers
]
```

---

## 🔧 How to Manage Delivery Zones (Current Process)

### For Admins:

1. **Go to:** `http://localhost/eat/admin/restaurants`
2. **Click:** "Edit" on any restaurant
3. **Scroll to:** "Delivery Settings" section
4. **Configure:**
   - Free Zone Radius (km)
   - Base Delivery Fee (XAF)
   - Extra Fee per KM (XAF)
   - Minimum Order (XAF)
5. **Click:** "Save Changes"

### For Restaurant Owners:

Restaurant owners can also edit their own delivery settings from their dashboard.

---

## 📝 Database Schema

### `restaurants` Table (Relevant Columns)

```sql
CREATE TABLE `restaurants` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `latitude` decimal(10,8) DEFAULT NULL,
    `longitude` decimal(11,8) DEFAULT NULL,
    `delivery_fee` decimal(6,2) DEFAULT 500.00 
        COMMENT 'Base delivery fee within delivery_radius (in XAF)',
    `delivery_radius` decimal(5,2) DEFAULT 10.00 
        COMMENT 'Free delivery zone radius in KM',
    `delivery_fee_per_extra_km` decimal(6,2) DEFAULT 100.00 
        COMMENT 'Extra fee per km beyond delivery_radius (in XAF)',
    `minimum_order` decimal(10,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (`id`),
    KEY `idx_restaurants_delivery_settings` (`delivery_radius`, `delivery_fee`, `delivery_fee_per_extra_km`)
);
```

---

## 🎨 Implementation Plan for Zone Management Page

### Phase 1: Basic Zone Management (Recommended)

**File to Create:** `src/views/admin/delivery-zones.php`

**Features:**
1. ✅ List all restaurants with their zone settings
2. ✅ Quick edit zone settings inline
3. ✅ Bulk update zones for multiple restaurants
4. ✅ Zone statistics (coverage, orders, etc.)

**Estimated Time:** 4-6 hours

### Phase 2: Map Visualization (Advanced)

**Features:**
1. ✅ Interactive map showing all delivery zones
2. ✅ Visual radius circles for each restaurant
3. ✅ Click to edit zones on map
4. ✅ Drag to adjust radius visually

**Estimated Time:** 8-12 hours

### Phase 3: Analytics & Optimization (Advanced)

**Features:**
1. ✅ Delivery zone coverage reports
2. ✅ Orders outside zones analysis
3. ✅ Zone optimization suggestions
4. ✅ Demand heatmaps

**Estimated Time:** 12-16 hours

---

## 🔍 Key Files Reference

| File | Purpose |
|------|---------|
| `src/services/DeliveryFeeService.php` | Delivery fee calculation & zone checking |
| `src/views/admin/restaurants/edit.php` | Restaurant delivery settings UI |
| `src/controllers/CheckoutController.php` | Order placement & zone validation |
| `config/maps.php` | Map & zone configuration |
| `config/app.php` | Platform-wide delivery settings |

---

## ✅ Summary

**Current State:**
- ✅ Delivery zones are managed per-restaurant
- ✅ Admins can edit zones in restaurant edit page
- ✅ System validates delivery availability on checkout
- ✅ Dynamic fee calculation based on distance

**Missing:**
- ❌ Centralized delivery zone management page
- ❌ Map visualization of zones
- ❌ Bulk zone editing
- ❌ Zone analytics and reporting

**Recommendation:**
Create a new admin page at `/admin/deliveries/zones` with:
1. Table view of all restaurant zones
2. Quick inline editing
3. Bulk update functionality
4. Basic statistics

This would give admins a centralized place to manage delivery zones across all restaurants.

