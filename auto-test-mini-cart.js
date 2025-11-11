/**
 * Automated Mini Cart Bug Testing Script
 * This script automatically executes all testing steps with proper timing delays
 *
 * Usage: Copy and paste this entire script into the browser console on https://terravivapizza.com
 */

(function() {
    console.log('🚀 Starting automated mini cart bug testing...');

    // Step 1: Open mini cart (wait 5 seconds after)
    setTimeout(function() {
        console.log('Step 1: Opening mini cart...');
        try {
            const cartIcon = document.querySelectorAll('.header-nav.header-nav-main.nav.nav-right.nav-size-large.nav-spacing-xlarge.nav-uppercase li a')[0];
            if (cartIcon) {
                cartIcon.click();
                console.log('✅ Mini cart opened');
            } else {
                console.error('❌ Cart icon not found');
            }
        } catch (e) {
            console.error('Error opening cart:', e);
        }
    }, 0);

    // Step 2: Clear logs (wait 5 seconds after step 1)
    setTimeout(function() {
        console.log('Step 2: Clearing server logs...');
        try {
            if (typeof clearLogsServer === 'function') {
                clearLogsServer();
                console.log('✅ Logs cleared');
            } else {
                console.error('❌ clearLogsServer function not found');
            }
        } catch (e) {
            console.error('Error clearing logs:', e);
        }
    }, 5000);

    // Step 3: Log before state (wait 5 seconds after step 2)
    setTimeout(function() {
        console.log('Step 3: Logging initial state before quantity change...');
        try {
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

            if (typeof writeLogServer === 'function') {
                writeLogServer(beforeData, 'info');
                console.log('✅ Before state logged:', beforeData);
            } else {
                console.error('❌ writeLogServer function not found');
            }
        } catch (e) {
            console.error('Error logging before state:', e);
        }
    }, 10000);

    // Step 4: Click plus button (wait 5 seconds after step 3)
    setTimeout(function() {
        console.log('Step 4: Clicking plus button on first item...');
        try {
            const plusButton = document.querySelector('button.plus');
            if (plusButton) {
                plusButton.click();
                console.log('✅ Plus button clicked');
            } else {
                console.error('❌ Plus button not found');
            }
        } catch (e) {
            console.error('Error clicking plus button:', e);
        }
    }, 15000);

    // Step 5: Log after state (wait 10 seconds after step 4 for AJAX to complete)
    setTimeout(function() {
        console.log('Step 5: Logging state after quantity change...');
        try {
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

            // Log each item's details
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

            if (typeof writeLogServer === 'function') {
                writeLogServer(afterData, 'info');
                console.log('✅ After state logged:', afterData);
            } else {
                console.error('❌ writeLogServer function not found');
            }
        } catch (e) {
            console.error('Error logging after state:', e);
        }
    }, 25000);

    // Step 6: Final completion message
    setTimeout(function() {
        console.log('✅ ========================================');
        console.log('✅ Automated testing completed!');
        console.log('✅ You can now run the download command to analyze logs:');
        console.log('✅ python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/debug.log && python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/custom-debug.log');
        console.log('✅ ========================================');
    }, 30000);

})();
