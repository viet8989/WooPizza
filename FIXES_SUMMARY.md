# Product Lightbox - Size Selection & Subtotal Fix

## CRITICAL FIXES (Latest - Session 3)

### Fix 6: Manual Handler Always Attached as Fallback
**Problem:** Manual variation handler was only attached when WooCommerce script was NOT available:
- Handler was inside `else` block (old line 1621-1654)
- When WooCommerce script exists but fails to match variations, no fallback handler
- Result: Subtotal never updates even though handler code exists

**Fix Applied:**
- **Moved manual handler OUTSIDE if-else block** (line 1623-1661)
- Now ALWAYS attaches as fallback, even when WooCommerce script exists
- Logs `attaching_manual_fallback` event
- Result: If WooCommerce fails, manual handler catches size changes

### Fix 7: Paired Mode Left Half Price Not Updating
**Problem:** When changing sizes in paired mode, left half price stays at initial value:
- `selectedLeftHalf.price` set once when paired mode activated (line 1157)
- Manual handler updates `mainProductPrice` but not `selectedLeftHalf.price`
- WooCommerce's `found_variation` handler had this logic (line 1693-1698), but manual handler didn't
- Result: Subtotal stuck at initial paired price

**Fix Applied:**
- **Added paired mode price update to manual handler** (line 1647-1653)
- Now checks if paired mode active: `$('#btn-paired').hasClass('active')`
- Updates `selectedLeftHalf.price = mainProductPrice / 2`
- Logs `paired_left_half_price_updated` event
- Mirrors exact logic from WooCommerce's `found_variation` handler

## CRITICAL FIXES (Session 2)

### Fix 4: Manual Variation Handler Not Executing
**Problem:** The manual variation handler was never attached because:
- `setupVariationForm()` was only called at page load (line 1690)
- At page load, the lightbox doesn't exist yet, so `$('.variations_form')` returns empty
- The `mfpOpen` event handler (lines 1694-1702) was commented out
- Result: Handler never attached, subtotal never updates

**Fix Applied:**
- **Uncommented `mfpOpen` event handler** (line 1694-1707)
- Now calls `setupVariationForm()` when lightbox actually opens
- Added logging to track: `mfpOpen`, `mfpOpen_setup`, `setupVariationForm_start`
- Added detailed logging in `setDefaultVariation()` function
- This ensures manual handler gets attached to the actual form when it exists

### Fix 5: Improved Logging Throughout
**Changes:**
- Line 1573: Added `setupVariationForm_start` log
- Line 1586: Added `isAlreadyInitialized` check in logs
- Line 1695-1707: Added `mfpOpen` and `mfpOpen_setup` logs
- Line 1712-1741: Converted all console.log to writeLogServer in `setDefaultVariation()`

## CRITICAL FIXES (Session 1)

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
// 2. Select a right half pizza
// 3. Change left half size (S → M → L)
// 4. Check if subtotal updates correctly

// OR use this automated test:
document.querySelectorAll('a.quick-view')[0].click();
// Wait 3 seconds, then run:
document.querySelector('#btn-paired').click();
// Select any right half pizza card
document.querySelector('.pizza-card').click();
// Wait 2 seconds, then test size changes:
document.querySelector('.variation-select-hidden').value = 'M';
jQuery('.variation-select-hidden').trigger('change');
// Check subtotal increased...
```

## Expected Results

**Whole Mode:**
- Initial: S selected (red), Subtotal: 250.000 ₫
- After M: M selected (red), Subtotal: 290.000 ₫
- After L: L selected (red), Subtotal: 350.000 ₫

**Paired Mode (with left half only, no toppings):**
- Initial S: Left half (S/2) = 125.000 ₫, Right half = varies, Subtotal updates
- After M: Left half (M/2) = 145.000 ₫, Right half = varies, Subtotal updates
- After L: Left half (L/2) = 175.000 ₫, Right half = varies, Subtotal updates

## Complete Flow After Fix

**When Lightbox Opens:**
1. `mfpOpen` event fires → logs `{"event":"mfpOpen"}`
2. After 100ms delay → logs `{"event":"mfpOpen_setup"}`
3. Calls `setupVariationForm()` → logs `{"event":"setupVariationForm_start","formFound":1}`
4. Checks if already initialized → logs `{"event":"setup_variation_form","isAlreadyInitialized":false,"hasWcScript":false}`
5. WC script not found → logs `{"event":"wc_script_not_loaded"}`
6. Checks variation data → logs `{"event":"variations_data_check","hasData":true,"dataLength":3}`
7. Attaches manual handler → logs `{"event":"attaching_manual_handler"}`
8. After 500ms → calls `setDefaultVariation()`
9. Sets S button selected → logs `{"event":"setDefaultVariation_called"}`, `{"event":"setDefault_changeTriggered","value":"S"}`
10. Manual handler catches change → logs `{"event":"manual_handler_fired","selectedValue":"S"}`
11. Updates price → logs `{"event":"manual_price_update","price":250000}`
12. Calls `updateSubtotal()` → updates display

**When User Changes to Size M:**
1. Select value changes to M
2. Change event fires
3. Manual handler catches → logs `{"event":"manual_handler_fired","selectedValue":"M"}`
4. Finds matching variation → logs `{"event":"manual_price_update","price":300000}`
5. Calls `updateSubtotal()` → `#sub_total` text updates to "300.000 ₫"

**When User Changes to Size L:**
1. Select value changes to L
2. Change event fires
3. Manual handler catches → logs `{"event":"manual_handler_fired","selectedValue":"L"}`
4. Finds matching variation → logs `{"event":"manual_price_update","price":350000}`
5. Calls `updateSubtotal()` → `#sub_total` text updates to "350.000 ₫"

## Expected Logs (After Fix)

After uploading the fixed file and running the test, you should see these logs in [custom-debug.log](wp-content/custom-debug.log):

**On Lightbox Open (Whole Mode):**
```json
{"event":"setupVariationForm_start","formFound":1}
{"event":"setup_variation_form","hasWcScript":true}
{"event":"wc_script_found","message":"Initializing WC variation form","variationsCount":3}
{"event":"attaching_manual_fallback","hasData":true,"dataLength":3}
{"event":"attaching_wc_listeners"}
{"event":"setDefaultVariation_called"}
{"event":"manual_handler_fired","selectedValue":"S"}
{"event":"manual_price_update","price":250000,"variation_id":862}
```

**When Size M is Selected (Whole Mode):**
```json
{"event":"button_click","value":"M","attribute":"size"}
{"event":"button_select_value_set","selectName":"attribute_size","value":"M","selectFound":1}
{"event":"manual_handler_fired","selectedValue":"M"}
{"event":"manual_price_update","price":290000,"variation_id":863}
{"event":"updateSubtotal_called","currentPrice":290000}
```

**When Size L is Selected (Whole Mode):**
```json
{"event":"manual_handler_fired","selectedValue":"L"}
{"event":"manual_price_update","price":350000,"variation_id":864}
{"event":"updateSubtotal_called","currentPrice":350000}
```

**When Size M is Selected (Paired Mode):**
```json
{"event":"manual_handler_fired","selectedValue":"M"}
{"event":"manual_price_update","price":290000,"variation_id":863}
{"event":"paired_left_half_price_updated","price":145000}
{"event":"updateSubtotal_called","currentPrice":290000}
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
