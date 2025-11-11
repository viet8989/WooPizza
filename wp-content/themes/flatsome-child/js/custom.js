document.addEventListener('DOMContentLoaded', function() {

    const button = document.querySelector('.button-reservation');
    if (button) {
        button.addEventListener('click', function() {
            const terms = document.querySelector('.terms-condition-text');
            if (terms) {
                terms.style.display = 'none';
            }

            const form = document.querySelector('.form-reservation');
            if (form) {
                form.style.display = 'block';
            }
        });
    }

    const menuButton = document.querySelector('.menu-button');
    const menuClose = document.querySelector('.menu-close');
    const menuOpen = document.querySelector('.menu-open');
    const header = document.querySelector('.header');
    const body = document.body;

    if (menuButton && menuOpen) {
        menuButton.addEventListener('click', function() {
            menuOpen.classList.add('show');
            header.classList.add('show-menu');
            // body.classList.add('hidden-show');
            menuOpen.classList.remove('hide'); // Xóa class hide nếu có
        });
    }

    if (menuClose && menuOpen) {
        menuClose.addEventListener('click', function() {
            menuOpen.classList.add('hide');
            body.classList.remove('hidden-show');
            menuOpen.classList.remove('show'); // Xóa class show nếu có
            header.classList.remove('show-menu'); // Xóa class show nếu có
        });
    }

    // Center flickity slider container on delivery page
    const rowSlider = document.querySelector('.row-slider');
    if (rowSlider) {
        // Center the entire carousel container
        rowSlider.style.display = 'flex';
        rowSlider.style.justifyContent = 'center';
        rowSlider.style.alignItems = 'center';

        // Get the flickity slider and center its content
        const flickitySlider = rowSlider.querySelector('.flickity-slider');
        if (flickitySlider) {
            flickitySlider.style.transform = 'translateX(0)';
            flickitySlider.style.left = '0';
            flickitySlider.style.textAlign = 'center';
        }
    }

    // Check current page URL is checkout page
    if (window.location.href.indexOf('/checkout') > -1) {
        // const wpslSearch = document.querySelector('.wpsl-search.wpsl-clearfix');
        // if (wpslSearch) {
        //     wpslSearch.style.display = 'none';
        // }
        const billing_postcode_field = document.getElementById('billing_postcode_field');
        if (billing_postcode_field) {
            billing_postcode_field.style.display = 'none';
        }
    }

    // Check current page URL is delivery page
    if (window.location.href.indexOf('/delivery') > -1) {
        // Make category section sticky at 80px below main menu when scrolling
        // NOTE: Target the second div.section-content.relative element (category carousel)
        // The .relative class provides position:relative by default, which we override when sticky
        const categorySection = document.querySelectorAll('div.section-content.relative')[1];

        if (categorySection) {
            // Get header height dynamically
            const header = document.querySelector('#header');
            const stickyOffset = header ? header.offsetHeight : 80;
            let originalTop = 0;
            let isSticky = false;
            let placeholder = null;

            // Calculate position after page fully loads
            const initSticky = function() {
                originalTop = categorySection.getBoundingClientRect().top + window.pageYOffset;
            };

            // Wait for images and content to load
            if (document.readyState === 'complete') {
                initSticky();
            } else {
                window.addEventListener('load', initSticky);
            }

            window.addEventListener('scroll', function() {
                const scrollY = window.pageYOffset;

                if (scrollY >= originalTop - stickyOffset && !isSticky) {
                    isSticky = true;

                    // Create placeholder to prevent layout shift
                    if (!placeholder) {
                        placeholder = document.createElement('div');
                        placeholder.style.height = categorySection.offsetHeight + 'px';
                        categorySection.parentNode.insertBefore(placeholder, categorySection);
                    }

                    // Remove .relative class and add .fixed class to override position: relative !important
                    categorySection.classList.remove('relative');
                    categorySection.classList.add('fixed');
                    categorySection.style.top = '80px';
                    categorySection.style.left = 'calc(var(--spacing) * 0)';
                    categorySection.style.right = 'calc(var(--spacing) * 0)';
                    categorySection.style.zIndex = '100';
                    categorySection.style.backgroundColor = '#fff';
                    categorySection.style.boxShadow = '0 2px 5px rgba(0,0,0,0.1)';
                } else if (scrollY < originalTop - stickyOffset && isSticky) {
                    isSticky = false;

                    // Remove placeholder
                    if (placeholder && placeholder.parentNode) {
                        placeholder.parentNode.removeChild(placeholder);
                        placeholder = null;
                    }

                    // Restore .relative class and remove .fixed class
                    categorySection.classList.remove('fixed');
                    categorySection.classList.add('relative');
                    categorySection.style.top = 'auto';
                    categorySection.style.left = 'auto';
                    categorySection.style.right = 'auto';
                    categorySection.style.zIndex = '';
                    categorySection.style.backgroundColor = '';
                    categorySection.style.boxShadow = 'none';
                }
            });
        }

        const categoryLinks = document.querySelectorAll('.product-category a');
        console.log('Found category links:', categoryLinks.length);

        categoryLinks.forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default link behavior
                console.log('Category clicked:', this);

                // Remove .selected class from all categories
                document.querySelectorAll('.box-category .box-text .box-text-inner').forEach(function(box) {
                    box.classList.remove('selected');
                });

                // Add .selected class to clicked category
                // The link is inside .col-inner, find .box-text-inner within the same container
                const colInner = this.closest('.col-inner');
                console.log('Found .col-inner:', colInner);

                if (colInner) {
                    const boxTextInner = colInner.querySelector('.box-text .box-text-inner');
                    console.log('Found .box-text-inner:', boxTextInner);

                    if (boxTextInner) {
                        boxTextInner.classList.add('selected');
                        console.log('Added .selected class to:', boxTextInner);
                    } else {
                        console.warn('Could not find .box-text .box-text-inner inside .col-inner');
                    }
                } else {
                    console.warn('Could not find .col-inner parent. Link structure:', this.parentElement);
                }

                // Get text of clicked link
                const categoryName = this.textContent.trim();
                console.log('Category name:', categoryName);

                // Save selected category to sessionStorage
                sessionStorage.setItem('selectedCategory', categoryName);

                fadeOutToGroupCategory(categoryName);
            });
        });

        // Check if redirected from home page with category parameter
        const urlParams = new URLSearchParams(window.location.search);
        const categoryParam = urlParams.get('category');
        if (categoryParam) {
            // Decode the category name and scroll to it
            const categoryName = decodeURIComponent(categoryParam);

            // Save to sessionStorage and set selected class
            sessionStorage.setItem('selectedCategory', categoryName);

            // Use setTimeout to ensure DOM is fully loaded
            setTimeout(function() {
                fadeOutToGroupCategory(categoryName);

                // Set selected class for the category
                setSelectedCategory(categoryName);
            }, 300);
        } else {
            // Restore selected category from sessionStorage if available
            const savedCategory = sessionStorage.getItem('selectedCategory');
            if (savedCategory) {
                setSelectedCategory(savedCategory);
            }
        }
    }

    // Check current page URL is home page (works with query params/hash like ?tab=abc or #section)
    const pathname = window.location.pathname;
    const isHomePage = pathname === '/' || pathname === '' || pathname === '/home' || pathname === '/index.php';

    if (isHomePage) {
        // Reset to PIZZA when loading home page (before DOM manipulation)
        sessionStorage.setItem('selectedCategory', 'PIZZA');
        console.log('Home page loaded - Set default category to PIZZA');

        const categoryLinks = document.querySelectorAll('.product-category a');
        categoryLinks.forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default link behavior
                // get text of clicked link
                const categoryName = this.textContent.trim();
                // Redirect to delivery page with category parameter
                window.location.href = window.location.origin + '/delivery?category=' + encodeURIComponent(categoryName);
            });
        });

        // Check if URL has tab parameter and auto-open that tab
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        if (tabParam) {
            // Wait longer for DOM/images to load before scrolling
            setTimeout(function() {
                fadeOutToGroupOpenMenu(tabParam, 'redirect'); // Called after redirect from another page
            }, 1000);
        }

        // Set visual selected state for PIZZA after DOM loads
        setTimeout(function() {
            setSelectedCategory('PIZZA');
        }, 300);
    }

    // Header menu links - ONLY in the menu overlay, not in tab content
    const menuOpenLinks = document.querySelectorAll('.menu-open .ux-menu > .ux-menu-link a');
    console.log('Found menu links:', menuOpenLinks.length);

    menuOpenLinks.forEach(function(link) {
        link.addEventListener('click', function(event) {
            console.log('Menu link clicked:', this.getAttribute('href'));
            event.preventDefault();

            if (menuClose) {
                menuClose.click();
            }

            const href = this.getAttribute('href').trim();

            if (href === '' || href === '#contact') {
                return;
            }

            const currentPathname = window.location.pathname;
            const currentlyOnHome = currentPathname === '/' || currentPathname === '' || currentPathname === '/home' || currentPathname === '/index.php';

            if (currentlyOnHome) {
                console.log('Calling fadeOutToGroupOpenMenu with:', href);
                fadeOutToGroupOpenMenu(href, 'home'); // Called from home page
            } else {
                window.location.href = window.location.origin + '?tab=' + href.replace('#', '');
            }
        });
    });
});

// Helper function to set selected category class
function setSelectedCategory(categoryName) {
    console.log('setSelectedCategory called with:', categoryName);

    // Remove .selected class from all categories
    document.querySelectorAll('.box-category .box-text .box-text-inner').forEach(function(box) {
        box.classList.remove('selected');
    });

    // Find and add .selected class to matching category
    const categoryLinks = document.querySelectorAll('.product-category a');
    console.log('Looking through', categoryLinks.length, 'category links');

    let found = false;
    categoryLinks.forEach(function(link) {
        const linkText = link.textContent.trim();
        if (linkText === categoryName) {
            console.log('Found matching link:', link);
            const colInner = link.closest('.col-inner');
            console.log('colInner:', colInner);

            if (colInner) {
                const boxTextInner = colInner.querySelector('.box-text .box-text-inner');
                console.log('boxTextInner:', boxTextInner);

                if (boxTextInner) {
                    boxTextInner.classList.add('selected');
                    console.log('Successfully added .selected class');
                    found = true;
                }
            }
        }
    });

    if (!found) {
        console.warn('Could not find category:', categoryName);
        console.log('Available categories:', Array.from(categoryLinks).map(l => l.textContent.trim()));
    }
}

function fadeOutToGroupCategory(categoryName) {
    const productTitles = document.querySelectorAll('div.section-content.relative .row.align-middle .col-inner h3');
    productTitles.forEach(function(title) {
        if (title.textContent.trim() === categoryName) {
            // Scroll to the category section with margin offset 260px            
            const offset = 260;
            const target = title.getBoundingClientRect().top + window.scrollY - offset;
            const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            window.scrollTo({ top: target, behavior: prefersReduced ? 'auto' : 'smooth' });
        }
    });
}

function fadeOutToGroupOpenMenu(hash, calledFrom = 'redirect') {
    // Remove # from hash if present
    const cleanHash = hash.replace('#', '');

    // Determine offset based on where function was called from
    // 'home' = called from home page menu (90px)
    // 'redirect' = called after redirect from another page (120px)
    const offset = calledFrom === 'home' ? 90 : 150;

    // Wait for DOM/images to fully load before calculating position
    setTimeout(function() {
        const item = document.querySelector('.tabbed-content.tab-service');
        if (!item) {
            console.warn('Tab container .tabbed-content.tab-service not found');
            return;
        }

        // Scroll with offset based on call origin
        const target = item.getBoundingClientRect().top + window.scrollY - offset;
        const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        window.scrollTo({ top: target, behavior: prefersReduced ? 'auto' : 'smooth' });

        // Wait for scroll to complete, then click the tab
        setTimeout(function() {
            // Find the tab link within .tabbed-content only
            const tabLink = document.querySelector('.tabbed-content.tab-service a[href="#' + cleanHash + '"]');

            if (tabLink) {
                console.log('Clicking tab:', cleanHash, '| Offset:', offset + 'px');
                tabLink.click();
            } else {
                console.warn('Tab link not found for:', cleanHash);
                console.log('Available tabs:', Array.from(document.querySelectorAll('.tabbed-content.tab-service a[role="tab"]')).map(a => a.getAttribute('href')));
            }
        }, 500);
    }, 300);
}

/**
 * Write log data to server
 * @param {*} data - Any data to log (string, object, array, etc.)
 * @param {string} logLevel - Log level: 'info', 'warning', 'error', 'debug'
 */
function writeLogServer(data, logLevel = 'info') {
    // Check if customJsParams is available (localized from PHP)
    if (typeof customJsParams === 'undefined') {
        console.error('customJsParams not found. Make sure script is properly enqueued.');
        return;
    }

    // Prepare log data
    const logData = {
        action: 'write_custom_log',
        nonce: customJsParams.nonce,
        log_level: logLevel,
        log_data: typeof data === 'object' ? JSON.stringify(data) : String(data),
        page_url: window.location.href,
        timestamp: new Date().toISOString()
    };

    // Send AJAX request to WordPress
    fetch(customJsParams.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(logData)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            console.log('Log written to server:', result.data);
        } else {
            console.error('Failed to write log:', result.data);
        }
    })
    .catch(error => {
        console.error('Error writing log to server:', error);
    });
}

/**
 * Clear both debug.log and custom-debug.log files on server
 * Usage: clearLogsServer()
 */
function clearLogsServer() {
    // Check if customJsParams is available (localized from PHP)
    if (typeof customJsParams === 'undefined') {
        console.error('customJsParams not found. Make sure script is properly enqueued.');
        return;
    }

    // Prepare request data
    const requestData = {
        action: 'clear_debug_logs',
        nonce: customJsParams.nonce
    };

    console.log('Clearing server logs...');

    // Send AJAX request to WordPress
    fetch(customJsParams.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(requestData)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            console.log('✅ Logs cleared successfully:', result.data);
        } else {
            console.error('❌ Failed to clear logs:', result.data);
        }
    })
    .catch(error => {
        console.error('Error clearing logs:', error);
    });
}

// Auto-test mini cart bug (only run when URL has ?autotest=minicart parameter)
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('autotest') === 'minicart') {
        console.log('🚀 Starting automated mini cart bug testing...');

        // Step 1: Open mini cart (wait 2 seconds for page load)
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
        }, 2000);

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
        }, 7000);

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
        }, 12000);

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
        }, 17000);

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
        }, 27000);

        // Step 6: Final completion message
        setTimeout(function() {
            console.log('✅ ========================================');
            console.log('✅ Automated testing completed!');
            console.log('✅ Logs have been written to server');
            console.log('✅ Run download command to analyze:');
            console.log('✅ python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/debug.log && python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/custom-debug.log');
            console.log('✅ ========================================');
        }, 32000);
    }
});