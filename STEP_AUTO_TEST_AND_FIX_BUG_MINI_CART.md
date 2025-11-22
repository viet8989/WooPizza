# Mini Cart Debugging Workflow - Complete Template

## Bug Description
When clicking 'plus' or 'minus' quantity buttons in mini cart, price fields update incorrectly (item total, subtotal, tax, etc.)

---

## Key Features (Reusable for Other Tasks)

✅ **No MySQL/Database Commands Required**
- No need to run `mysql` commands from terminal
- No direct database queries like `SELECT * FROM wp_posts`
- WordPress/WooCommerce functions handle all database operations internally
- We just add `error_log()` to see what WordPress is doing

✅ **Debug via PHP Code Logging**
- Add `error_log()` statements in PHP files
- View results in `wp-content/debug.log`
- No need for database access or phpMyAdmin

✅ **Auto Upload/Download via Python Scripts**
- `auto_upload_ftp.py` - Uploads modified files to production
- `auto_download_file.py` - Downloads log files from production
- One command to sync changes with live server

✅ **Client-Side Logging to Server**
- `writeLogServer()` - JavaScript logs saved to server file
- `clearLogsServer()` - Clear logs before testing
- View browser events in `wp-content/custom-debug.log`

✅ **Automated Testing**
- URL parameter triggers auto-test script (`?autotest=minicart`)
- Script runs all test steps automatically
- Wait 30 seconds, download logs, analyze results

✅ **Complete Workflow Documentation**
- Step-by-step manual process
- Quick reference commands
- Common debugging scenarios
- Troubleshooting guide

---

## Prerequisites

### 1. Python Scripts Setup
Located at: `~/Dropbox/AutoUploadFTPbyGitStatus/`

- **auto_upload_ftp.py** - Upload modified files to production server
- **auto_download_file.py** - Download specific files from production server
- **ftp_config.ini** - FTP credentials configuration

### 2. Server-Side Logging Functions (PHP)
These functions are already available in the production site for debugging:

**Location**: Available globally in WordPress

**Functions**:
```php
// Write logs to server
error_log('Your debug message here');

// For structured data
error_log('Data: ' . print_r($data, true));
```

### 3. Client-Side Logging Functions (JavaScript)
These functions are available in the browser console:

**Functions**:
```javascript
// Write client-side logs to server
writeLogServer(data, logLevel);

// Clear both log files
clearLogsServer();
```

**Example**:
```javascript
writeLogServer({
    event: 'button_clicked',
    timestamp: new Date().toISOString(),
    data: { id: 123, value: 'test' }
}, 'info');
```

---

## Automated Testing Workflow

### Method 1: Manual Step-by-Step (For Understanding)

#### Step 1: Open Site
```bash
open https://terravivapizza.com
```
Wait 5 seconds for page load.

#### Step 2: Open Mini Cart (Browser Console)
```javascript
document.querySelectorAll('.header-nav.header-nav-main.nav.nav-right.nav-size-large.nav-spacing-xlarge.nav-uppercase li a')[0].click();
```
Wait 5 seconds for mini cart to appear.

#### Step 3: Clear Previous Logs (Browser Console)
```javascript
clearLogsServer();
```
Wait 5 seconds for logs to clear.

#### Step 4: Log State Before Action (Browser Console)
```javascript
// Log initial state before quantity change
const cartItems = document.querySelectorAll('.woocommerce-mini-cart-item');
const subtotal = document.querySelector('.mini-cart-totals-breakdown .subtotal-row .totals-value');
const tax = document.querySelector('.mini-cart-totals-breakdown .tax-row .totals-value');
const total = document.querySelector('.mini-cart-totals-breakdown .total-row .totals-value');

const beforeData = {
    event: 'before_quantity_change',
    timestamp: new Date().toISOString(),
    cart_items_count: cartItems.length,
    subtotal: subtotal ? subtotal.textContent.trim() : 'N/A',
    tax: tax ? tax.textContent.trim() : 'N/A',
    total: total ? total.textContent.trim() : 'N/A',
    items: []
};

// Log each item's details
cartItems.forEach(function(item, index) {
    const productName = item.querySelector('.mini-cart-product-title');
    const quantity = item.querySelector('.mini-cart-quantity-controls input.qty');
    const lineTotal = item.querySelector('.mini-cart-line-total .amount');

    beforeData.items.push({
        index: index,
        product: productName ? productName.textContent.trim() : 'Unknown',
        quantity: quantity ? quantity.value : 'N/A',
        line_total: lineTotal ? lineTotal.textContent.trim() : 'N/A'
    });
});

writeLogServer(beforeData, 'info');
```
Wait 5 seconds.

#### Step 5: Trigger Action (Browser Console)
```javascript
// Click plus button on first item
document.querySelector('button.plus').click();

// OR click minus button
// document.querySelector('button.minus').click();
```
Wait 10 seconds for AJAX to complete and UI to update.

#### Step 6: Log State After Action (Browser Console)
```javascript
// Log state after quantity change (same as step 4 but different event name)
const cartItems = document.querySelectorAll('.woocommerce-mini-cart-item');
const subtotal = document.querySelector('.mini-cart-totals-breakdown .subtotal-row .totals-value');
const tax = document.querySelector('.mini-cart-totals-breakdown .tax-row .totals-value');
const total = document.querySelector('.mini-cart-totals-breakdown .total-row .totals-value');

const afterData = {
    event: 'after_quantity_change',
    timestamp: new Date().toISOString(),
    cart_items_count: cartItems.length,
    subtotal: subtotal ? subtotal.textContent.trim() : 'N/A',
    tax: tax ? tax.textContent.trim() : 'N/A',
    total: total ? total.textContent.trim() : 'N/A',
    items: []
};

cartItems.forEach(function(item, index) {
    const productName = item.querySelector('.mini-cart-product-title');
    const quantity = item.querySelector('.mini-cart-quantity-controls input.qty');
    const lineTotal = item.querySelector('.mini-cart-line-total .amount');

    afterData.items.push({
        index: index,
        product: productName ? productName.textContent.trim() : 'Unknown',
        quantity: quantity ? quantity.value : 'N/A',
        line_total: lineTotal ? lineTotal.textContent.trim() : 'N/A'
    });
});

writeLogServer(afterData, 'info');
```
Wait 5 seconds.

#### Step 7: Download Log Files (Terminal)
```bash
cd ~/Dropbox/WooPizza
python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/debug.log && \
python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/custom-debug.log
```

#### Step 8: Analyze Logs (Terminal/IDE)
```bash
# Read server-side logs
cat wp-content/debug.log

# Read client-side logs
cat wp-content/custom-debug.log

# Or open in IDE to compare side-by-side
```

---

### Method 2: Automated Script (Recommended)

Use the automated test script located at: `auto-test-mini-cart.js`

**To use**:
1. Open browser to: `https://terravivapizza.com?autotest=minicart`
2. The script auto-runs (opens cart, logs before/after, clicks plus button)
3. Wait 30 seconds for completion
4. Download logs from terminal:

```bash
cd ~/Dropbox/WooPizza
sleep 35 && \
python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/debug.log && \
python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/custom-debug.log
```

**One-line command** (open browser + download logs after delay):
```bash
open "https://terravivapizza.com?autotest=minicart" && \
sleep 35 && \
python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/debug.log && \
python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/custom-debug.log
```

---

## Debugging PHP Code (Server-Side)

### Add Debug Logging to PHP
**Location**: `wp-content/themes/flatsome-child/functions.php`

**Example**:
```php
// Log simple message
error_log('Processing cart item: ' . $cart_item_key);

// Log structured data
error_log('Cart item data: ' . print_r($cart_item, true));

// Log variable values
error_log('Price - Regular: ' . $regular_price . ', Current: ' . $current_price);

// Log array with label
error_log('Pizza halves data: ' . print_r($halves, true));
```

**Common debugging patterns**:
```php
// Before/after comparison
error_log('BEFORE calculation - price: ' . $price);
// ... calculation code ...
error_log('AFTER calculation - price: ' . $price);

// Conditional logging
if ($is_paired_mode) {
    error_log('PAIRED MODE - both halves present');
} else {
    error_log('WHOLE PIZZA MODE - single product');
}

// Loop debugging
foreach ($items as $index => $item) {
    error_log('Item ' . $index . ': ' . print_r($item, true));
}
```

### Upload Modified PHP Files
```bash
cd ~/Dropbox/WooPizza

# Upload all modified files (detected by git status)
python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_upload_ftp.py

# The script shows:
# - Which files changed
# - Upload progress
# - Success/failure status
```

---

## Complete Debug & Fix Cycle

### 1. Add Debug Logging
Edit the PHP file where you suspect the issue:
```bash
# Example: Edit functions.php
nano wp-content/themes/flatsome-child/functions.php

# Add error_log() statements at key points
```

### 2. Upload Changes
```bash
cd ~/Dropbox/WooPizza
python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_upload_ftp.py
```

### 3. Run Automated Test
```bash
open "https://terravivapizza.com?autotest=minicart" && sleep 35
```

### 4. Download Logs
```bash
python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/debug.log && \
python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/custom-debug.log
```

### 5. Analyze Results
```bash
# View server logs
cat wp-content/debug.log

# View client logs
cat wp-content/custom-debug.log

# Or use grep to filter specific events
grep "PAIRED MODE" wp-content/debug.log
grep "before_quantity_change" wp-content/custom-debug.log
```

### 6. Fix Code
Based on log analysis, edit the code:
```bash
nano wp-content/themes/flatsome-child/functions.php
# Make fixes
```

### 7. Test Fix
Repeat steps 2-5 to verify the fix works.

---

## Log File Locations

### Server Logs (PHP)
**Path**: `wp-content/debug.log`

**Content**: Server-side PHP operations
- Cart calculations
- Price modifications
- Database queries
- WooCommerce hooks execution

**Format**:
```
[11-Nov-2025 13:13:46 UTC] Processing cart item: abc123
[11-Nov-2025 13:13:46 UTC] Price: 150000
```

### Client Logs (JavaScript)
**Path**: `wp-content/custom-debug.log`

**Content**: Browser-side JavaScript events
- User interactions
- AJAX responses
- UI state before/after actions
- Cart totals from DOM

**Format**:
```json
[2025-11-11T13:13:46.000Z] [INFO] [https://terravivapizza.com] {"event":"before_quantity_change","subtotal":"1.000.000 ₫"}
```

---

## Common Debugging Scenarios

### Scenario 1: Price Calculation Issues

**Add logging in functions.php**:
```php
// In add_custom_pizza_options_price() function
error_log('=== PRICE CALCULATION START ===');
error_log('Product ID: ' . $cart_item['product_id']);
error_log('Regular price: ' . $cart_item['data']->get_regular_price());
error_log('Current price: ' . $cart_item['data']->get_price());
error_log('Has pizza_halves: ' . (isset($cart_item['pizza_halves']) ? 'YES' : 'NO'));
error_log('Calculated price: ' . $new_total_price);
error_log('=== PRICE CALCULATION END ===');
```

### Scenario 2: Cart Item Data Structure

**Add logging to see cart item structure**:
```php
error_log('Full cart item data: ' . print_r($cart_item, true));
```

### Scenario 3: AJAX Response Issues

**Add logging in AJAX handler**:
```php
// In custom AJAX handler
error_log('AJAX Request - Action: ' . $_POST['action']);
error_log('AJAX Request - Data: ' . print_r($_POST, true));
error_log('AJAX Response: ' . json_encode($response));
```

### Scenario 4: WooCommerce Hook Execution

**Track hook execution order**:
```php
add_action('woocommerce_before_calculate_totals', function() {
    error_log('Hook fired: woocommerce_before_calculate_totals - Count: ' . did_action('woocommerce_before_calculate_totals'));
}, 1);
```

---

## Tips & Best Practices

### 1. Use Descriptive Log Messages
```php
// Bad
error_log($price);

// Good
error_log('Unit price after toppings: ' . $price);
```

### 2. Log Before and After Critical Operations
```php
error_log('BEFORE set_price: ' . $cart_item['data']->get_price());
$cart_item['data']->set_price($new_price);
error_log('AFTER set_price: ' . $cart_item['data']->get_price());
```

### 3. Use Separators for Clarity
```php
error_log('========================================');
error_log('Processing item: ' . $item_id);
error_log('========================================');
```

### 4. Clean Up Debug Logs Regularly
```bash
# Remove old debug logging after fixing
# Comment out or remove error_log() calls that are no longer needed
```

### 5. Git Ignore Log Files
Already configured in `.gitignore`:
```
wp-content/debug.log
wp-content/custom-debug.log
```

---

## Troubleshooting

### Issue: Logs Not Appearing

**Solution 1**: Check PHP error logging is enabled
```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

**Solution 2**: Check file permissions
```bash
# Log files should be writable
chmod 644 wp-content/debug.log
chmod 644 wp-content/custom-debug.log
```

### Issue: Upload Script Fails

**Check**:
```bash
# Verify script exists
ls -la ~/Dropbox/AutoUploadFTPbyGitStatus/auto_upload_ftp.py

# Check FTP config
cat ~/Dropbox/AutoUploadFTPbyGitStatus/ftp_config.ini

# Run with error output
python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_upload_ftp.py 2>&1
```

### Issue: Download Script Fails

**Check**:
```bash
# Verify remote file exists
# Check in browser or FTP client

# Try with verbose output
python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/debug.log -v
```

---

## Summary of Tools

| Tool | Purpose | Command |
|------|---------|---------|
| **error_log()** | PHP server-side logging | `error_log('message');` |
| **writeLogServer()** | JavaScript client-side logging | `writeLogServer(data, 'info');` |
| **clearLogsServer()** | Clear both log files | `clearLogsServer();` |
| **auto_upload_ftp.py** | Upload modified files | `python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_upload_ftp.py` |
| **auto_download_file.py** | Download specific file | `python3 ~/Dropbox/AutoUploadFTPbyGitStatus/auto_download_file.py /path/to/file` |
| **auto-test-mini-cart.js** | Automated browser test | Open `?autotest=minicart` |

---

## Quick Reference Commands

```bash
# Full automated test + download logs
open "https://terravivapizza.com?autotest=minicart" && \
sleep 35 && \
python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/debug.log && \
python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/custom-debug.log

# Upload changes
cd /home/vietnhq/Documents/WooCommerce/WooPizza && \
python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_upload_ftp.py

# View logs
cat wp-content/debug.log
cat wp-content/custom-debug.log

# Search logs for specific events
grep "PAIRED MODE" wp-content/debug.log
grep "price" wp-content/debug.log | grep -i "error"
```

---

## Next Steps for Tomorrow

### Current Bug Status
- ✅ Fixed VAT calculation (customer location set to VN/HOCHIMINH)
- ✅ Fixed paired pizza price doubling logic (empty right_half detection)
- ✅ Added variation price listener (updates when size changes)
- ✅ Added "Add to Cart" validation (disabled when right_half not selected)
- ❌ **STILL BUGGY**: Old cart items with wrong half price (50k instead of 75k for size M)

### To Continue Tomorrow
1. **Clear existing cart** - Remove old items with incorrect prices
2. **Test fresh add-to-cart** - Verify new items use correct variation price (75k for size M)
3. **Verify all calculations** - Check unit price, line total, subtotal, tax, total
4. **Remove debug logging** - Clean up excessive error_log() calls
5. **Test edge cases** - Size changes, multiple items, different pizzas

### Known Issue to Address
The variation price listener needs to be initialized **before** clicking "Paired pizza", otherwise `mainProductPrice` stays at the base value (100k instead of 150k for size M).

**Possible solutions**:
1. Get selected variation price immediately when modal opens
2. Disable "Paired pizza" button until variation is selected
3. Force variation selection before any pizza mode selection

---

*Last updated: 2025-11-11 20:35 UTC*
*Template ready for reuse on other debugging tasks*
