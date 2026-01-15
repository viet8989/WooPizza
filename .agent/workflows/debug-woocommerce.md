---
description: Steps to test, debug, and fix issues specifically for WooCommerce sites
---

1. **Preliminary Checks (WooCommerce)**
   - Action: Go to **WooCommerce > Status**. Check for red alerts (outdated templates, failed cron jobs, database version mismatch).
   - Action: Go to **WooCommerce > Status > Logs**. Look for `fatal-errors.log` or payment gateway specific logs (e.g., `stripe`, `paypal`).

2. **Reproduction & Isolation**
   - Action: Attempt to reproduce the bug on the remote site.
   - Action: Note if the issue is specific to a product type (Simple vs Variable) or user role (Guest vs Customer).
   - **Conflict Test**:
     - Action: Temporarily switch to a default theme (Storefront or Twenty Twenty-Four).
     - Action: Disable all plugins except WooCommerce.
     - Verification: If the bug disappears, re-enable plugins one by one to find the culprit.
     - *Tip: Use the "Health Check & Troubleshooting" plugin to doing this without affecting visitors.*

3. **Flatsome Theme Specifics**
   - Action: If using Flatsome, go to **Flatsome > Advanced** and check specifically for "Global Scripts" or "Custom CSS" that might interfere.
   - Action: Check for outdated template overrides in `wp-content/themes/flatsome-child/woocommerce/`. Compare with correct versions in **WooCommerce > Status > Templates**.

4. **Checkout & Payment Debugging**
   - Action: Enable "Debug Mode" in your payment gateway settings (e.g., WooCommerce > Settings > Payments > Stripe > Manage > Log Debug Messages).
   - Action: Process a test transaction.
   - Action: Check logs for API response errors (e.g., 400 Bad Request, verifying keys).

5. **Fix & Deployment**
   - Action: Implement the fix in your local environment (`wp-content/themes/flatsome-child/` for code fixes).
   - Verification: Verify the fix doesn't break other WooCommerce flows (Cart, My Account).
   - Action: Deploy to staging/production.
   - **Important**: clear WooCommerce transients and cache: **WooCommerce > Status > Tools > Clear transients**.

6. **Final Verification**
   - Verification: Run through a complete purchase flow as a guest and logged-in user.
   - Verification: Check order email receipt and backend order status.
