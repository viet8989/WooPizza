---
description: Automated steps to test, debug, and fix mini-cart bugs using the browser agent
---

1. **Browser Automation**
   - Action: Use the `browser_subagent` tool to perform the test sequence.
   - **Task for Browser Agent**:
     1. Navigate to `https://terravivapizza.com`.
     2. Wait for the page to load completely.
     3. **Ensure Item in Cart**:
        - Check if cart is empty.
        - If empty, navigate to Menu/Delivery, add a pizza (e.g. Seafood), and return to home.
     4. Click the Mini Cart icon (selector: `.header-nav.header-nav-main ... a`).
     5. **Inject/Execute JavaScript** to capture the "Before" state:
        ```javascript
        (() => {
            const cartItems = document.querySelectorAll('.woocommerce-mini-cart-item');
            const subtotal = document.querySelector('.mini-cart-totals-breakdown .subtotal-row .totals-value')?.textContent.trim();
            const total = document.querySelector('.mini-cart-totals-breakdown .total-row .totals-value')?.textContent.trim();
            console.log('BEFORE_ACTION:', JSON.stringify({ itemCount: cartItems.length, subtotal, total }));
            return { itemCount: cartItems.length, subtotal, total };
        })();
        ```
     6. Click the "plus" (`button.plus`) or "minus" button on the first cart item.
     7. Wait 5-10 seconds for the AJAX update to finish.
     8. **Inject/Execute JavaScript** to capture the "After" state:
        ```javascript
        (() => {
            const cartItems = document.querySelectorAll('.woocommerce-mini-cart-item');
            const subtotal = document.querySelector('.mini-cart-totals-breakdown .subtotal-row .totals-value')?.textContent.trim();
            const total = document.querySelector('.mini-cart-totals-breakdown .total-row .totals-value')?.textContent.trim();
            console.log('AFTER_ACTION:', JSON.stringify({ itemCount: cartItems.length, subtotal, total }));
            return { itemCount: cartItems.length, subtotal, total };
        })();
        ```
      8. **Analyze Console Logs**: The agent reads `console.log` output directly to verify the state transitions.
      9. **IMPORTANT**: The browser agent MUST include the captured state data (item count, subtotal, total) in its final report.

2. **Analyze Results**
   - Action: Compare the `BEFORE_ACTION` and `AFTER_ACTION` data returned by the browser agent.
   - Check: Did the subtotal/total update correctly?
   - Check: Did the price doubling issue occur?

3. **Server-Side Debugging (If needed)**
   - Action: If client-side values are wrong, assume the issue is in PHP (AJAX handler).
   - Reference: Use `check_php_logs` from the manual workflow if needed, but prioritize browser observation first.

4. **Fix & Verify**
   - Action: Edit `wp-content/themes/flatsome-child/functions.php` or relevant JS files.
   - Action: Rerun Step 1 to verify the fix.
