# Reservation System - WP Store Locator Integration

## ✅ Integration Complete!

The reservation system has been updated to integrate with **WP Store Locator** plugin.

---

## 🔄 What Changed

### 1. **Dynamic Branch Loading**
- ✅ Branches now load from WP Store Locator (`post_type='wpsl_stores'`)
- ✅ Old static branch table (`wp_reservation_branches`) is no longer used
- ✅ Branches filter dynamically based on selected date/time

### 2. **Reservation Hours Validation**
- ✅ Checks `wpsl_reservation_hours` meta field for each store
- ✅ Validates day of week (monday, tuesday, etc.)
- ✅ Validates time range (e.g., "9:00 AM,5:00 PM")
- ✅ Only shows stores that are open for selected date/time

### 3. **Database Schema Update**
- ✅ Column `branch_id` renamed to `store_id`
- ✅ Now stores WP Store Locator post ID instead of custom branch ID

### 4. **"No Branch Available" Message**
- ✅ Shows message when no stores match the selected date/time
- ✅ Format: "No branch can make reservation as per your given time: DD/MM/YYYY HH:mm"

---

## 📋 How It Works

### Step 1: User Selects Date & Time
```
User picks: 2025-11-01 at 10:00 AM
```

### Step 2: System Checks wpsl_reservation_hours
```php
// Example wpsl_reservation_hours meta value:
array(
    'monday' => array('9:00 AM,5:00 PM'),
    'tuesday' => array('9:00 AM,5:00 PM'),
    'wednesday' => array('9:00 AM,5:00 PM'),
    'thursday' => array('9:00 AM,5:00 PM'),
    'friday' => array('9:00 AM,5:00 PM'),
    'saturday' => array(),  // Closed
    'sunday' => array()     // Closed
)
```

### Step 3: Filters Available Stores
```
✓ Store A: Open Monday 9AM-5PM → 10:00 AM is within range → SHOW
✓ Store B: Open Monday 1PM-9PM → 10:00 AM is NOT within range → HIDE
✓ Store C: Closed Monday → HIDE
```

### Step 4: Displays Result
- **If stores found**: Show list of available branches
- **If no stores found**: Show "No branch can make reservation" message

---

## 🔧 Database Changes

### Old Schema (before integration):
```sql
CREATE TABLE wp_reservations (
    ...
    branch_id mediumint(9) NOT NULL,  -- Custom branch table ID
    ...
);
```

### New Schema (after integration):
```sql
CREATE TABLE wp_reservations (
    ...
    store_id bigint(20) NOT NULL COMMENT 'WP Store Locator post ID',
    ...
);
```

**Migration Note**: If you have existing reservations, you'll need to migrate `branch_id` → `store_id`.

---

## 🧪 Testing Guide

### Test Scenario 1: Store Open on Selected Date/Time
1. Go to reservation page
2. Select a date (e.g., Monday)
3. Select a time within store hours (e.g., 10:00 AM)
4. **Expected**: Stores open Monday 9AM-5PM should appear

### Test Scenario 2: Store Closed on Selected Day
1. Select a date (e.g., Saturday)
2. Select any time
3. **Expected**: If all stores are closed Saturday, see "No branch can make reservation" message

### Test Scenario 3: Time Outside Operating Hours
1. Select a weekday
2. Select time like 6:00 AM (before opening)
3. **Expected**: Stores not yet open should NOT appear

### Test Scenario 4: Real-Time Branch Updates
1. Change date from Monday → Saturday
2. **Expected**: Branch list updates automatically via AJAX
3. Watch browser console for logs:
   ```
   Loading available stores for: 2025-11-01 10:00
   Available stores response: [...]
   ```

---

## 🐛 Debugging

### Check WP Store Locator Data

**SQL: Get all stores**
```sql
SELECT ID, post_title, post_status
FROM wp_posts
WHERE post_type = 'wpsl_stores'
AND post_status = 'publish';
```

**SQL: Get store reservation hours**
```sql
SELECT post_id, meta_value
FROM wp_postmeta
WHERE meta_key = 'wpsl_reservation_hours';
```

**SQL: Get store address**
```sql
SELECT post_id, meta_key, meta_value
FROM wp_postmeta
WHERE post_id = 649
AND meta_key IN ('wpsl_address', 'wpsl_city', 'wpsl_reservation_hours');
```

### Browser Console Debugging

Open F12 → Console tab and you'll see:

```javascript
// When page loads
Reservation form loaded

// When date/time changes
Reservation date changed: 2025-11-01
Start time changed: 10:00
Loading available stores for: 2025-11-01 10:00

// AJAX response
Available stores response: {success: true, data: [{id: 649, name: "Store A", address: "123 Main St"}]}

// When no stores available
Available stores response: {success: true, data: []}
No branches available for selected date/time
```

### Debug File Logging

Check: `wp-content/plugins/custom-reservation-plugin/debug_save.txt`

```
=== Get Available Stores at 2025-10-23 18:30:00 ===
Date: 2025-11-01, Time: 10:00
  ✓ Store 649: Store A - AVAILABLE
  ✗ Store 650: Store B - NOT AVAILABLE
  ✗ Store 651: Store C - NOT AVAILABLE
```

### Add More Debug Logs

**In PHP (custom-reservation-plugin.php):**
```php
// Line 145 (inside crp_get_available_stores function)
$debug_log = "Checking store {$store->ID}: {$store->post_title}\n";
$debug_log .= "  Reservation hours: " . print_r($reservation_hours, true) . "\n";
file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);
```

**In JavaScript (browser console):**
```javascript
// Monitor AJAX calls
jQuery(document).ajaxComplete(function(event, xhr, settings) {
    if (settings.data && settings.data.includes('crp_get_available_stores')) {
        console.log('AJAX Complete:', settings.data);
        console.log('Response:', xhr.responseJSON);
    }
});
```

---

## 📝 Code Locations

### Key Functions

**1. Check Store Availability**
- File: `custom-reservation-plugin.php`
- Function: `crp_is_store_available($store_id, $reservation_date, $start_time)`
- Lines: 66-108
- Does: Checks if store is open on given date/time

**2. Get Available Stores (AJAX)**
- File: `custom-reservation-plugin.php`
- Function: `crp_get_available_stores()`
- Lines: 110-155
- Does: Returns list of stores available for date/time

**3. Dynamic Branch Loading (JavaScript)**
- File: `custom-reservation-plugin.php`
- Function: `loadAvailableStores()`
- Lines: 259-340
- Does: Fetches and displays available branches via AJAX

**4. Save Reservation**
- File: `custom-reservation-plugin.php`
- Function: `crp_handle_reservation()`
- Lines: 643-769
- Does: Validates and saves reservation with store_id

**5. Admin Display**
- File: `admin/admin-page.php`
- Function: `crp_reservations_page()`
- Lines: 38-240
- Does: Displays reservations with store names from WP Store Locator

---

## 🔍 WP Store Locator Meta Fields Used

| Meta Key | Description | Example Value |
|----------|-------------|---------------|
| `wpsl_reservation_hours` | Serialized array of hours by day | `a:7:{s:6:"monday";a:1:{i:0;s:15:"9:00 AM,5:00 PM";}}` |
| `wpsl_address` | Street address | "123 Nguyễn Huệ" |
| `wpsl_city` | City name | "Quận 1, TP.HCM" |

### Reservation Hours Format

**Serialized PHP Array:**
```php
array(
    'monday' => array('9:00 AM,5:00 PM'),
    'tuesday' => array('9:00 AM,5:00 PM', '7:00 PM,11:00 PM'),  // Multiple slots
    'wednesday' => array('9:00 AM,5:00 PM'),
    'thursday' => array('9:00 AM,5:00 PM'),
    'friday' => array('9:00 AM,5:00 PM'),
    'saturday' => array(),  // Empty = Closed
    'sunday' => array()     // Empty = Closed
)
```

**Time Slot Format:**
- Format: `"H:MM AM,H:MM PM"`
- Example: `"9:00 AM,5:00 PM"`
- Multiple slots: `array('9:00 AM,12:00 PM', '1:00 PM,5:00 PM')`

---

## ✨ Features

### ✅ Real-Time Branch Filtering
- Branches update automatically when date or time changes
- No page reload needed (AJAX)
- Loading indicator while fetching

### ✅ Day of Week Validation
- Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday
- Checks if store is open on selected day

### ✅ Time Range Validation
- Parses time strings like "9:00 AM,5:00 PM"
- Converts to 24-hour format for comparison
- Checks if reservation time falls within operating hours

### ✅ Multiple Time Slots
- Supports stores with multiple time slots per day
- Example: Lunch (11AM-2PM) and Dinner (6PM-10PM)

### ✅ User-Friendly Messages
- "Please select date and time first to see available branches"
- "Loading available branches..."
- "No branch can make reservation as per your given time: 01/11/2025 10:00"
- "Error loading branches. Please try again."

### ✅ Store Information Display
- Store name (from post_title)
- Full address (wpsl_address + wpsl_city)
- Styled with radio buttons and hover effects

---

## 🚀 Activation Steps

### If Plugin Already Activated:
1. **Deactivate** the plugin
2. **Delete old table** (optional):
   ```sql
   DROP TABLE IF EXISTS wp_reservations;
   ```
3. **Re-activate** the plugin
   - This will create new table with `store_id` column

### If Fresh Installation:
1. Go to **Plugins** → Find "Custom Reservation Plugin"
2. Click **Activate**
3. Database tables created automatically with correct schema

### Verify Installation:
```sql
-- Check column name
DESCRIBE wp_reservations;

-- Should show: store_id bigint(20) NOT NULL
```

---

## 📊 Example Data Flow

### User Action:
```
1. User selects: 01/11/2025 (Monday) at 14:00 (2:00 PM)
```

### System Processing:
```php
2. AJAX call: crp_get_available_stores
   - reservation_date: 2025-11-01
   - start_time: 14:00

3. Get all stores: post_type = 'wpsl_stores', post_status = 'publish'
   - Store 649: "Pizza Quận 1"
   - Store 650: "Pizza Quận 3"
   - Store 651: "Pizza Quận 5"

4. Check each store's wpsl_reservation_hours:

   Store 649 (Monday hours: 9:00 AM - 5:00 PM):
   - Day: Monday ✓
   - Time: 14:00 is between 09:00-17:00 ✓
   - Result: AVAILABLE

   Store 650 (Monday hours: 6:00 PM - 11:00 PM):
   - Day: Monday ✓
   - Time: 14:00 is NOT between 18:00-23:00 ✗
   - Result: NOT AVAILABLE

   Store 651 (Monday: Closed):
   - Day: Monday, but no hours set ✗
   - Result: NOT AVAILABLE

5. Return available stores: [Store 649]
```

### Display Result:
```html
<div class="branch-option">
    <input type="radio" name="store_id_radio" value="649" checked>
    <label>
        <strong>Pizza Quận 1</strong> - 123 Nguyễn Huệ, Quận 1, TP.HCM
    </label>
</div>
```

---

## 💡 Pro Tips

### Tip 1: Set Reservation Hours in WP Store Locator
1. Go to **Store Locator** → **Stores**
2. Edit each store
3. Find **Reservation Hours** section
4. Set hours for each day of the week

### Tip 2: Test with Different Days
- Monday-Friday (weekdays)
- Saturday-Sunday (weekends)
- Verify stores show/hide correctly

### Tip 3: Watch Console Logs
- Keep F12 console open while testing
- See real-time AJAX calls and responses
- Debug issues immediately

### Tip 4: Check debug_save.txt
- Detailed server-side logs
- Shows which stores are checked
- See availability results

---

## 🎯 Summary

✅ **Branches load dynamically** from WP Store Locator
✅ **Hours validated** against wpsl_reservation_hours
✅ **Day of week** checked (Monday-Sunday)
✅ **Time range** validated (9:00 AM-5:00 PM, etc.)
✅ **"No branch available"** message when needed
✅ **store_id saved** in database (WP Store Locator post ID)
✅ **Admin panel** displays store names and addresses
✅ **Full debug logging** for troubleshooting

**Status**: Ready to use! 🎉
