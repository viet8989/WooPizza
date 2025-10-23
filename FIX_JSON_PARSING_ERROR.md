# Fix: JSON Parsing Error in AJAX Response

## ❌ **Error:**
```
Error loading stores: SyntaxError: Unexpected token '<', "<script>co"... is not valid JSON
```

## 🔍 **Root Cause:**

The AJAX handlers were using `echo '<script>console.log(...);</script>';` statements **before** the JSON response. This caused the response to contain HTML/JavaScript instead of pure JSON, breaking the JSON parser.

Example of problematic code:
```php
echo '<script>console.log("Available stores:", ...);</script>';
wp_send_json_success($available_stores);  // This sends JSON

// Browser receives: <script>console.log(...);</script>{"success":true,...}
// This is NOT valid JSON! ❌
```

---

## ✅ **Solution:**

Removed all `echo '<script>console.log(...);</script>';` statements from AJAX handlers and replaced them with file logging.

---

## 📝 **Changes Made:**

### **File:** `custom-reservation-plugin.php`

#### **Change 1: Get Available Stores AJAX Handler (Line 153)**

**Before:**
```php
echo '<script>console.log("Available stores:", ' . json_encode($available_stores) . ');</script>';
wp_send_json_success($available_stores);
```

**After:**
```php
// Log available stores to debug file
$debug_log = "Available stores count: " . count($available_stores) . "\n";
file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);

wp_send_json_success($available_stores);
```

---

#### **Change 2: Invalid Time Format (Line 729)**

**Before:**
```php
echo '<script>console.log("Invalid time format");</script>';
wp_send_json_error(['message' => 'Invalid time format.']);
```

**After:**
```php
$debug_log = "ERROR: Invalid time format\n";
file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);
wp_send_json_error(['message' => 'Invalid time format.']);
```

---

#### **Change 3: Past Date Validation (Line 735)**

**Before:**
```php
echo '<script>console.log("Cannot book past dates");</script>';
wp_send_json_error(['message' => 'Cannot make reservations for past dates.']);
```

**After:**
```php
$debug_log = "ERROR: Cannot book past dates\n";
file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);
wp_send_json_error(['message' => 'Cannot make reservations for past dates.']);
```

---

#### **Change 4: Store Availability Check (Line 741)**

**Before:**
```php
echo '<script>console.log("Store not available for selected date/time");</script>';
wp_send_json_error(['message' => 'This store is not available...']);
```

**After:**
```php
$debug_log = "ERROR: Store $store_id not available for $date $start_time\n";
file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);
wp_send_json_error(['message' => 'This store is not available...']);
```

---

#### **Change 5: Time Slot Conflict (Line 766)**

**Before:**
```php
echo '<script>console.log("Time slot already booked");</script>';
wp_send_json_error(['message' => 'This time slot is already booked...']);
```

**After:**
```php
$debug_log = "ERROR: Time slot already booked\n";
file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);
wp_send_json_error(['message' => 'This time slot is already booked...']);
```

---

#### **Change 6: Database Error (Line 791)**

**Before:**
```php
echo '<script>console.log("Database error: ' . addslashes($wpdb->last_error) . '");</script>';
wp_send_json_error(['message' => 'Database error: ' . $wpdb->last_error]);
```

**After:**
```php
$debug_log = "ERROR: Database error: " . $wpdb->last_error . "\n";
file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);
wp_send_json_error(['message' => 'Database error: ' . $wpdb->last_error]);
```

---

#### **Change 7: Reservation Success (Line 832)**

**Before:**
```php
echo '<script>console.log("Reservation successful, ID: ' . $wpdb->insert_id . '");</script>';
wp_send_json_success(['message' => 'Reservation successful!...']);
```

**After:**
```php
$debug_log = "SUCCESS: Reservation saved. ID: " . $wpdb->insert_id . "\n";
file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);
wp_send_json_success(['message' => 'Reservation successful!...']);
```

---

## 🎯 **Result:**

### **Before (Broken):**
```
AJAX Response:
<script>console.log("Available stores:", ...);</script>{"success":true,"data":[...]}

Browser tries to parse as JSON: ❌ ERROR!
SyntaxError: Unexpected token '<'
```

### **After (Fixed):**
```
AJAX Response:
{"success":true,"data":[{"id":649,"name":"Store A","address":"..."}]}

Browser parses successfully: ✅ WORKS!
JavaScript receives: {success: true, data: [...]}
```

---

## 🔍 **How to Debug Now:**

### **Option 1: Check debug_save.txt File**
```bash
cat wp-content/plugins/custom-reservation-plugin/debug_save.txt
```

**Example Output:**
```
=== Get Available Stores at 2025-10-23 18:30:00 ===
Date: 2025-11-01, Time: 14:00
  ✓ Store 649: Pizza Store A - AVAILABLE
  ✗ Store 650: Pizza Store B - NOT AVAILABLE
Available stores count: 1

SUCCESS: Reservation saved. ID: 42
```

---

### **Option 2: Browser Console Logs (JavaScript)**

The JavaScript in the form already has console logs:
```javascript
console.log('📅 Date selected:', selectedDate);
console.log('⏰ Time selected:', startTime);
console.log('🚀 Loading branches now...');
console.log('Available stores response:', response);
console.log('Branch selected:', storeId);
```

These logs still work because they're in JavaScript, not in PHP AJAX responses!

---

### **Option 3: Network Tab (F12)**

1. Open F12 → **Network** tab
2. Select date & time
3. Look for AJAX request to `admin-ajax.php`
4. Click on it → **Response** tab
5. **Verify**: Pure JSON (no `<script>` tags)

**Good Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 649,
      "name": "Pizza Store",
      "address": "123 Main St"
    }
  ]
}
```

**Bad Response (old code):**
```html
<script>console.log(...);</script>{"success":true,...}
```

---

## ✅ **Testing:**

### **Test 1: Load Branches**
1. Visit reservation page
2. Select date: Tomorrow
3. Select time: 14:00
4. **Verify**: Branches appear (no JSON error) ✅
5. **F12 Console**: See JavaScript logs ✅
6. **F12 Network**: Response is pure JSON ✅

### **Test 2: Submit Reservation**
1. Fill all fields
2. Click "Make Reservation"
3. **Verify**: Success or error message appears ✅
4. **F12 Console**: See "Form submitted" log ✅
5. **F12 Network**: Response is pure JSON ✅

### **Test 3: Check Debug File**
```bash
cat wp-content/plugins/custom-reservation-plugin/debug_save.txt
```
**Verify**: See logs like:
```
=== Get Available Stores at 2025-10-23 18:30:00 ===
Available stores count: 2
SUCCESS: Reservation saved. ID: 5
```

---

## 📚 **Best Practice:**

### ❌ **DON'T Do This in AJAX Handlers:**
```php
function my_ajax_handler() {
    echo '<script>console.log("Debug");</script>';  // ❌ BREAKS JSON!
    echo 'Some HTML';  // ❌ BREAKS JSON!
    wp_send_json_success($data);
}
```

### ✅ **DO This Instead:**
```php
function my_ajax_handler() {
    // Use file logging
    error_log('Debug message');  // ✅ Logs to PHP error log
    file_put_contents('debug.txt', 'Debug message', FILE_APPEND);  // ✅ Logs to file

    // Only send JSON
    wp_send_json_success($data);  // ✅ Pure JSON response
}
```

---

## 🎯 **Summary:**

✅ **Removed all `echo '<script>...'` from AJAX handlers**
✅ **Replaced with file logging to `debug_save.txt`**
✅ **AJAX responses now return pure JSON**
✅ **JavaScript can parse responses correctly**
✅ **No more "Unexpected token '<'" errors**
✅ **Branches load successfully**

**Status:** ✅ Fixed! Branches should load without JSON errors now! 🎉

---

**Try it now:**
1. Refresh the reservation page
2. Select date + time
3. Watch branches appear smoothly! ✨
