# Product Lightbox - Size Selection & Subtotal Fix

## Issues Fixed

### 1. Size S Not Selected by Default
**Problem:** S button not showing as selected when lightbox opens
**Fix:** Added manual button selection in `found_variation` event handler (line 1603-1614)

### 2. Subtotal Not Updating When Changing Sizes
**Root Cause:** WooCommerce variation script (`wc-add-to-cart-variation.js`) not loaded in lightbox
**Fixes Applied:**
- Added manual variation handler as fallback (line 1596-1621)
- Fixed `getCurrentProductPrice()` to use `mainProductPrice` (line 1324-1333)
- Manual handler listens to `.variation-select-hidden` change events
- Parses variation data and updates `mainProductPrice` directly
- Calls `updateSubtotal()` to refresh display

### 3. Paired Mode Subtotal Shows "+0"
**Fix:** Increased sessionStorage check delay to 800ms (line 1199) to ensure variation is selected first

## Files Modified

1. **content-single-product-lightbox.php:**
   - Line 201: Removed 'selected' class from PHP (let JS handle it)
   - Line 1324-1333: Fixed `getCurrentProductPrice()`
   - Line 1596-1621: Added manual variation handler
   - Line 1603-1614: Added button selection in `found_variation`
   - Line 1638-1642: Fixed `reset_data` to preserve selection
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
