# Mini Cart Debugging Workflow

## Bug Description
When clicking 'plus' or 'minus' quantity buttons in mini cart, price fields update incorrectly (item total, subtotal, tax, etc.)

## Automated Debugging Steps

### 1. Open Site and Show Mini Cart
```bash
# Open browser to production site
open https://terravivapizza.com
```
Wait 5 seconds for page load.

### 2. Execute JavaScript to Open Mini Cart
```javascript
// Click first cart icon to show mini cart
document.querySelectorAll('.header-nav.header-nav-main.nav.nav-right.nav-size-large.nav-spacing-xlarge.nav-uppercase li a')[0].click();
```
Wait 5 seconds for mini cart to appear.

### 3. Clear Previous Logs
```javascript
clearLogsServer();
```
Wait 5 seconds for logs to clear.

### 4. Add Debug Logging (Before Action)
```javascript
// Log initial state before quantity change
writeLogServer({
    event: 'before_quantity_change',
    cart_items: document.querySelectorAll('.cart-item').length,
    subtotal: document.querySelector('.amount')?.textContent,
    // Add any other relevant data for debugging
});
```
Wait 5 seconds.

### 5. Trigger Quantity Change
```javascript
// Test plus button
document.querySelector('button.plus').click();

// OR test minus button
document.querySelector('button.minus').click();
```
Wait 5 seconds for AJAX and UI updates.

### 6. Add Debug Logging (After Action)
```javascript
// Log state after quantity change
writeLogServer({
    event: 'after_quantity_change',
    cart_items: document.querySelectorAll('.cart-item').length,
    subtotal: document.querySelector('.amount')?.textContent,
    // Add any other relevant data for debugging
});
```
Wait 5 seconds.

### 7. Download Log Files
```bash
python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/debug.log && \
python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/custom-debug.log
```

### 8. Analyze Logs and Fix
- Read `wp-content/debug.log` (server-side logs)
- Read `wp-content/custom-debug.log` (client-side logs from writeLogServer)
- Identify the issue from log comparison
- Edit relevant code to fix the bug
- Test the fix

## Log Files
- **Server logs**: `/wp-content/debug.log` - PHP/WordPress backend operations
- **Client logs**: `/wp-content/custom-debug.log` - JavaScript frontend operations

## Available JavaScript Functions
- `writeLogServer(data, logLevel)` - Write client-side logs to server
- `clearLogsServer()` - Clear both log files before testing