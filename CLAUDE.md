# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

WooPizza is a WordPress-based e-commerce site built on WooCommerce for a pizza restaurant. The site features a custom pizza ordering system with advanced customization options including whole pizzas, paired half-and-half pizzas, extra toppings, and special requests.

**Tech Stack:**
- WordPress (core installation)
- WooCommerce (e-commerce platform)
- Flatsome Theme (parent theme)
- Flatsome Child Theme (customizations)
- PHP 7.4+ (server-side)
- JavaScript/jQuery (client-side)
- Vietnamese currency (VND ₫)

## High-Level Architecture

### Custom Pizza Ordering System

The core functionality revolves around a sophisticated pizza customization system implemented across multiple layers:

**1. Product Display & Customization (Frontend)**
- Custom quick-view template: `wp-content/themes/flatsome-child/woocommerce/content-single-product-lightbox.php`
- Two ordering modes:
  - **Whole Pizza Mode**: Single pizza with optional extra toppings (cheese & cold cuts from categories 25 & 26)
  - **Paired Pizza Mode**: Two different half pizzas, each with independent topping selections
- Product pairing based on WooCommerce `product_brand` taxonomy
- Half pizzas are priced at 50% of full product price
- JavaScript handles real-time price calculation and option management

**2. Cart & Order Processing (Backend)**
- Custom functions in: `wp-content/themes/flatsome-child/functions.php`
- Data structures:
  - `extra_topping_options`: Array of whole-pizza toppings `[{name, price, product_id}]`
  - `pizza_halves`: Object containing left_half and right_half, each with `{product_id, name, price, image, toppings[]}`
  - `special_request`: Text field for customer notes
  - `paired_products`: Legacy support for backward compatibility
- Cart item uniqueness enforced via MD5 hash of serialized cart data
- Price calculations in `add_custom_pizza_options_price()` run before cart totals
- Order metadata saved with both internal (`_` prefixed) and display keys

**3. Data Flow**
```
Quick View Modal (JavaScript)
  ↓ (Collects user selections)
Hidden Form Inputs (JSON stringified)
  ↓ (POST to add-to-cart endpoint)
WooCommerce Cart Hook (woocommerce_add_cart_item_data)
  ↓ (Parses and sanitizes JSON)
Cart Item Metadata
  ↓ (Displayed in cart/checkout)
Order Line Item Metadata
  ↓ (Saved to order and visible in admin)
```

### Theme Customization Structure

**Parent-Child Theme Relationship:**
- Parent: `wp-content/themes/flatsome/` (Flatsome premium theme - do not modify)
- Child: `wp-content/themes/flatsome-child/` (all customizations go here)

**Key Child Theme Files:**
- `functions.php`: PHP hooks for cart/order processing, currency symbol customization, sticky mini-cart
- `style.css`: Custom CSS overrides (menu styles, product cards, animations, cart styling)
- `js/custom.js`: Menu open/close interactions, form toggles
- `woocommerce/content-single-product-lightbox.php`: Complete quick-view template override

### Category Structure

Special product categories used by the system:
- **Category 25**: Extra cheese options (Add extra cheese toppings)
- **Category 26**: Extra cold cuts options (Add extra cold cuts toppings)
- **product_brand taxonomy**: Used to group pizzas for pairing options

## Development Commands

### WordPress/PHP Development

**Database Configuration:**
- Database credentials in `wp-config.php` (DB_NAME, DB_USER, DB_PASSWORD, DB_HOST)
- Table prefix: `R6vhU_`
- Debug mode: Currently disabled (`WP_DEBUG = false`)

**To enable WordPress debugging:**
Edit `wp-config.php` and change:
```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```
Then check `wp-content/debug.log` for errors.

**Clear WooCommerce transients (if cart/product issues):**
```bash
wp transient delete --all
```

**Flush rewrite rules (if permalinks break):**
```bash
wp rewrite flush
```

### Testing Changes

**Test the pizza ordering flow:**
1. Navigate to homepage or delivery page
2. Click quick-view on any pizza product
3. Test whole pizza mode: Select toppings → Add to cart
4. Test paired mode: Click "Paired pizza" → Select right half → Add toppings to both halves → Add to cart
5. View cart to verify pricing and metadata display
6. Complete checkout and verify order data in admin

**Verify pricing calculations:**
- Whole pizza: base_price + sum(topping_prices)
- Paired pizza: (left_half_price / 2) + (right_half_price / 2) + sum(left_toppings) + sum(right_toppings)

### File Locations for Common Tasks

**Modify pizza customization UI:**
- Edit: `wp-content/themes/flatsome-child/woocommerce/content-single-product-lightbox.php`
- Contains both HTML structure and inline CSS/JavaScript

**Modify cart/checkout behavior:**
- Edit: `wp-content/themes/flatsome-child/functions.php`
- Key functions: `save_custom_pizza_options_to_cart()`, `display_custom_pizza_options_in_cart()`, `add_custom_pizza_options_price()`

**Modify site-wide styling:**
- Edit: `wp-content/themes/flatsome-child/style.css`
- Never edit parent theme CSS directly

**Modify menu behavior:**
- Edit: `wp-content/themes/flatsome-child/js/custom.js`

## Important Implementation Details

### Cart Item Uniqueness
Cart items with different customization options are treated as separate line items via the `unique_key` meta field generated from MD5 hash. This prevents merging of items with different toppings/halves.

### Price Calculation Timing
The `woocommerce_before_calculate_totals` hook runs multiple times per page load. The function includes a guard (`did_action >= 2`) to prevent duplicate calculations. When modifying pricing logic, ensure you don't break this protection.

### Metadata Visibility
The system stores two copies of custom options:
1. Internal metadata (keys starting with `_`): Raw data structures for programmatic access
2. Display metadata (human-readable keys): Formatted strings for cart/order display

The `customize_pizza_options_meta_key()` filter hides internal metadata from order displays.

### Backward Compatibility
The `paired_products` field is maintained for legacy support but replaced by the newer `pizza_halves` structure which supports per-half toppings. Don't remove `paired_products` handling without data migration.

### Sticky Mini Cart Widget
A custom floating cart widget appears on certain pages (controlled by `custom_sticky_mini_cart_widget()` in functions.php). It auto-hides after 10 seconds and responds to WooCommerce cart update events. The widget uses AJAX to refresh cart fragments.

### Currency Handling
Vietnamese Dong (₫) symbol is set via filter in functions.php. The JavaScript price formatting uses `Intl.NumberFormat('vi-VN')` for consistency.

## Code Patterns

### When Adding New Topping Categories
1. Query products by category ID using `wc_get_products()` with `tax_query`
2. Pass category products to template as array: `[{id, name, price}]`
3. Add checkbox group in quick-view template with classes: `.topping-checkbox`, `.whole-topping`, `.left-topping`, or `.right-topping`
4. Price calculation automatically includes checked toppings via jQuery selectors

### When Modifying Cart Data Structure
1. Update JSON structure in JavaScript (quick-view template)
2. Update parsing in `save_custom_pizza_options_to_cart()`
3. Update display in `display_custom_pizza_options_in_cart()`
4. Update price calculation in `add_custom_pizza_options_price()`
5. Update order save in `save_custom_pizza_options_to_order_items()`

### When Debugging Cart Issues
1. Check browser console for JavaScript errors and logged data
2. Check `woocommerce_add_cart_item_data` filter execution with `error_log()`
3. Verify JSON parsing with `json_decode()` error handling
4. Check cart item structure with `var_dump($cart_item)` in hooks
5. Verify WooCommerce AJAX endpoints are not blocked

## Plugins Installed

- **WooCommerce**: Core e-commerce functionality
- **Contact Form 7**: Contact form management
- **Duplicate Page**: Quick page duplication
- **Classic Editor**: Classic WordPress editor
- **Classic Widgets**: Classic widget interface
- **Custom Booking Plugin**: Custom booking functionality (in `wp-content/plugins/custom_booking_plugin/`)

## WordPress Admin Access

The site uses WordPress multisite-style authentication keys and salts. When working with user sessions or authentication, be aware these are configured in `wp-config.php`.

## Git Workflow

**Recent commits focus on:**
- Cart update functionality improvements
- Mini cart display fixes
- Special request feature addition
- Quick cart updates (numbered 9-12 in recent history)

**Branch:** main (also the default branch for PRs)

## Notes for AI Assistants

- Never modify `wp-config.php` database credentials or authentication keys without explicit user request
- Test all WooCommerce customizations in a staging environment first
- When modifying the quick-view template, remember it contains inline CSS and JavaScript
- Price calculations must handle both whole and paired modes correctly
- Always sanitize user input from special requests and topping selections
- The Flatsome parent theme is a premium theme - modifications must go in the child theme
- Vietnamese language support is partial - some strings use English, some use Vietnamese in theme files
