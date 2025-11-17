# Product Lightbox - Size Selection & Subtotal Fix

## CRITICAL FIXES (Latest)

### Fix 1: Malformed if-else Structure (Line 1591-1640)
**Problem:** Duplicate else blocks and syntax errors preventing variation handler from running
**Errors Found:**
- Line 1605: Incorrect `} else {` closing wrong if block
- Line 1632-1665: Duplicate else block with broken syntax
- Line 1636: `const variationsData = .data()` - missing variable
- Line 1640: `const  = ;` - completely broken variable declaration

**Fix Applied:**
- Restructured if-else blocks correctly
- Removed duplicate else block
- Added comprehensive logging with `writeLogServer()` for debugging
- Manual variation handler now properly nested inside else clause

### Fix 2: Syntax Error in Button Click Handler (Line 1562)
**Problem:** `selectFound: .length` caused entire button handler to fail
**Fix:** Changed to `selectFound: $hiddenSelect.length`

### Fix 3: Broken Console.log (Line 1651)
**Problem:** Incomplete console.log statement breaking code execution
**Fix:** Removed broken console.log, using writeLogServer() instead

## Issues Fixed (Previous)

### 1. Size S Not Selected by Default
**Problem:** S button not showing as selected when lightbox opens
**Fix:** Added manual button selection in `found_variation` event handler (line 1653-1665)

### 2. Subtotal Not Updating When Changing Sizes
**Root Cause:** WooCommerce variation script (`wc-add-to-cart-variation.js`) not loaded in lightbox
**Fixes Applied:**
- Added manual variation handler as fallback (line 1606-1639)
- Fixed `getCurrentProductPrice()` to use `mainProductPrice` (line 1324-1333)
- Manual handler listens to `.variation-select-hidden` change events
- Parses variation data and updates `mainProductPrice` directly
- Calls `updateSubtotal()` to refresh display
- Added extensive logging to track handler execution

### 3. Paired Mode Subtotal Shows "+0"
**Fix:** Increased sessionStorage check delay to 800ms (line 1199) to ensure variation is selected first

## Files Modified

1. **[content-single-product-lightbox.php](wp-content/themes/flatsome-child/woocommerce/content-single-product-lightbox.php):**
   - Line 201: Removed 'selected' class from PHP (let JS handle it)
   - Line 1324-1333: Fixed `getCurrentProductPrice()`
   - Line 1562: Fixed syntax error `selectFound: .length` → `selectFound: $hiddenSelect.length`
   - Line 1591-1640: **MAJOR FIX** - Restructured malformed if-else blocks
   - Line 1594: Added logging for `setup_variation_form`
   - Line 1598: Added logging for `wc_script_found`
   - Line 1606-1639: Properly structured manual variation handler with extensive logging
   - Line 1610: Added logging for `variations_data_check`
   - Line 1613: Added logging for `attaching_manual_handler`
   - Line 1617: Added logging for `manual_handler_fired` inside change handler
   - Line 1631: Added logging for `manual_price_update` with variation_id
   - Line 1647-1668: Updated `found_variation` event handler
   - Line 1651: Removed broken console.log
   - Line 1653-1665: Added button selection in `found_variation`
   - Line 1680-1688: Fixed `reset_data` to preserve selection
   - Line 1199: Increased paired mode delay

2. **custom.js:**
   - Updated test to directly manipulate hidden select

## Manual Testing Steps

Due to aggressive WordPress/browser caching, please test manually:

### Test 1: Whole Mode Size Change
```javascript
// 1. Open browser console (F12)
// 2. Go to: https://terravivapizza.com
// 3. Click any product quick-view
// 4. Run in console:

// Check initial state
console.log('Subtotal:', document.querySelector('#sub_total').textContent);

// Change to M
document.querySelector('.variation-select-hidden').value = 'M';
jQuery('.variation-select-hidden').trigger('change');

// Wait 1 second, then check:
setTimeout(() => {
  console.log('After M - Subtotal:', document.querySelector('#sub_total').textContent);
}, 1000);

// Change to L
document.querySelector('.variation-select-hidden').value = 'L';
jQuery('.variation-select-hidden').trigger('change');

// Wait 1 second, then check:
setTimeout(() => {
  console.log('After L - Subtotal:', document.querySelector('#sub_total').textContent);
}, 1000);
```

### Test 2: Paired Mode
```javascript
// After opening lightbox:
// 1. Click "Paired pizza" button
// 2. Check if subtotal shows correct price (not 0 or "+0")
```

## Expected Results

**Whole Mode:**
- Initial: S selected (red), Subtotal: 250.000 ₫
- After M: M selected (red), Subtotal: ~300.000 ₫ (or M's price)
- After L: L selected (red), Subtotal: ~350.000 ₫ (or L's price)

**Paired Mode:**
- Should show correct half prices, not "+0"

## Expected Logs (After Fix)

After uploading the fixed file and running the test, you should see these logs in [custom-debug.log](wp-content/custom-debug.log):

**On Lightbox Open:**
```json
{"event":"setup_variation_form","hasWcScript":false}
{"event":"wc_script_not_loaded","message":"Using manual variation handler"}
{"event":"variations_data_check","hasData":true,"dataLength":3}
{"event":"attaching_manual_handler","selector":".variation-select-hidden"}
```

**When Size M is Selected:**
```json
{"event":"manual_handler_fired","selectedValue":"M"}
{"event":"manual_price_update","price":300000,"variation_id":xxx}
```

**When Size L is Selected:**
```json
{"event":"manual_handler_fired","selectedValue":"L"}
{"event":"manual_price_update","price":350000,"variation_id":xxx}
```

If WooCommerce script IS loaded (you'll see different logs):
```json
{"event":"setup_variation_form","hasWcScript":true}
{"event":"wc_script_found","message":"Initializing WC variation form"}
{"event":"found_variation","display_price":xxx,"attributes":{...}}
{"event":"mainProductPrice_updated","mainProductPrice":xxx}
```

## Cache Clearing

If changes don't appear:
1. Hard refresh: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
2. Clear WordPress cache (if plugin installed)
3. Clear browser cache
4. Try incognito/private window

## Technical Details

The manual variation handler works by:
1. Detecting if WooCommerce's `wc_variation_form()` is available
2. If not, attaching to `.variation-select-hidden` change event
3. Parsing `data-product_variations` to find matching variation
4. Updating `mainProductPrice` variable
5. Calling `updateSubtotal()` to update display
