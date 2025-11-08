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
        categoryLinks.forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default link behavior
                // get text of clicked link
                const categoryName = this.textContent.trim();
                fadeOutToGroupCategory(categoryName);
            });
        });

        // Check if redirected from home page with category parameter
        const urlParams = new URLSearchParams(window.location.search);
        const categoryParam = urlParams.get('category');
        if (categoryParam) {
            // Decode the category name and scroll to it
            const categoryName = decodeURIComponent(categoryParam);
            // Use setTimeout to ensure DOM is fully loaded
            setTimeout(function() {
                fadeOutToGroupCategory(categoryName);
            }, 300);
        }
    }

    // Check current page URL is home page (works with query params/hash like ?tab=abc or #section)
    const pathname = window.location.pathname;
    const isHomePage = pathname === '/' || pathname === '' || pathname === '/home' || pathname === '/index.php';

    if (isHomePage) {
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
            setTimeout(function() {
                fadeOutToGroupOpenMenu(tabParam);
            }, 300);
        }
    }

    // Header menu links - works on all pages
    const menuOpenLinks = document.querySelectorAll('.ux-menu-link.flex.menu-item a.ux-menu-link__link.flex');
    console.log('Found menu links:', menuOpenLinks.length);

    if (menuOpenLinks.length === 0) {
        // Try alternative selector
        const altLinks = document.querySelectorAll('.menu-open .ux-menu a');
        console.log('Found alternative menu links:', altLinks.length);

        altLinks.forEach(function(link) {
            link.addEventListener('click', function(event) {
                console.log('Menu link clicked:', this.getAttribute('href'));
                event.preventDefault();

                if (menuClose) {
                    menuClose.click();
                }

                const href = this.getAttribute('href').trim();
                console.log('Processing href:', href);

                if (href === '' || href === '#contact') {
                    console.log('Skipping empty or contact link');
                    return;
                }

                const currentPathname = window.location.pathname;
                const currentlyOnHome = currentPathname === '/' || currentPathname === '' || currentPathname === '/home' || currentPathname === '/index.php';
                console.log('Currently on home?', currentlyOnHome);

                if (currentlyOnHome) {
                    console.log('Calling fadeOutToGroupOpenMenu with:', href);
                    fadeOutToGroupOpenMenu(href);
                } else {
                    window.location.href = window.location.origin + '?tab=' + href.replace('#', '');
                }
            });
        });
    } else {
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
                    fadeOutToGroupOpenMenu(href);
                } else {
                    window.location.href = window.location.origin + '?tab=' + href.replace('#', '');
                }
            });
        });
    }
});

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

function fadeOutToGroupOpenMenu(hash) {
    // Remove # from hash if present
    const cleanHash = hash.replace('#', '');

    const item = document.querySelector('.tabbed-content.tab-service');
    if (!item) {
        console.warn('Tab container .tabbed-content.tab-service not found');
        return;
    }

    // Scroll to the menu item section with margin offset 90px
    const offset = 90;
    const target = item.getBoundingClientRect().top + window.scrollY - offset;
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    window.scrollTo({ top: target, behavior: prefersReduced ? 'auto' : 'smooth' });

    // Wait for scroll, then click the tab - try multiple selectors
    setTimeout(function() {
        // Try different selectors to find the tab link
        let tabLink = document.querySelector('a[href="#' + cleanHash + '"]');

        if (!tabLink) {
            // Try with aria-controls
            tabLink = document.querySelector('a[aria-controls="' + cleanHash + '"]');
        }

        if (!tabLink) {
            // Try finding by ID on parent li
            const tabLi = document.getElementById(cleanHash.replace('tab_', 'tab-'));
            if (tabLi) {
                tabLink = tabLi.querySelector('a');
            }
        }

        if (tabLink) {
            console.log('Clicking tab:', cleanHash);
            tabLink.click();
        } else {
            console.warn('Tab link not found for:', cleanHash);
            console.log('Available tabs:', Array.from(document.querySelectorAll('.tabbed-content.tab-service a[role="tab"]')).map(a => a.getAttribute('href')));
        }
    }, 500);
}