# Header Menu System Documentation

## Overview
This document explains how the custom header menu system works in the WooPizza project, including the database structure, JavaScript handlers, and rendering flow.

---

## 1. Database Structure

### Theme Modifications Storage
- **Table**: `R6vhU_options`
- **Option Name**: `theme_mods_flatsome`
- **Data Format**: Serialized PHP array

### Key Fields in theme_mods_flatsome

#### header_elements_right
```php
array(
    0 => 'cart',
    1 => 'html'
)
```
- Defines which elements appear in the header right area
- `cart`: WooCommerce cart icon
- `html`: Custom HTML element

#### topbar_left (HTML Content)
```html
<div class="menu-button"></div>
<div class="menu-close"></div>
```
- This HTML is displayed when `html` element is in `header_elements_right`
- Provides the menu button and close button

---

## 2. Code Flow

### 2.1 Template Rendering

**File**: `/wp-content/themes/flatsome/template-parts/header/header-main.php`

**Line 36**:
```php
<?php flatsome_header_elements('header_elements_right'); ?>
```

### 2.2 Function Call Chain

**File**: `/wp-content/themes/flatsome/inc/structure/structure-header.php`

**Function**: `flatsome_header_elements()` (Line 72-121)

```php
function flatsome_header_elements( $options, $type = '' ) {
    // Get options from database
    $get_options = get_theme_mod( $options );

    // Loop through elements array
    foreach ( $get_options as $key => $value ) {
        if ( $value == 'html' || $value == 'html-2' || ... ) {
            flatsome_get_header_html_element( $value );
        }
        // ... other elements
    }
}
```

**Function**: `flatsome_get_header_html_element()` (Line 123+)

```php
function flatsome_get_header_html_element( $value ) {
    if ( $value == 'html' ) {
        $mod['name'] = 'topbar_left';  // Maps to database field
    }

    echo '<li class="html custom html_' . $mod['name'] . '">'
         . do_shortcode( get_theme_mod( $mod['name'] ) )
         . '</li>';
}
```

---

## 3. JavaScript Handler

**File**: `/wp-content/themes/flatsome-child/js/custom.js`

**Lines 17-39**: Menu Toggle Logic

```javascript
const menuButton = document.querySelector('.menu-button');
const menuClose = document.querySelector('.menu-close');
const menuOpen = document.querySelector('.menu-open');
const header = document.querySelector('.header');
const body = document.body;

// Open menu
if (menuButton && menuOpen) {
    menuButton.addEventListener('click', function() {
        menuOpen.classList.add('show');
        header.classList.add('show-menu');
        body.classList.add('hidden-show');
        menuOpen.classList.remove('hide');
    });
}

// Close menu
if (menuClose && menuOpen) {
    menuClose.addEventListener('click', function() {
        menuOpen.classList.add('hide');
        body.classList.remove('hidden-show');
        menuOpen.classList.remove('show');
        header.classList.remove('show-menu');
    });
}
```

---

## 4. Menu Content (ux_menu)

### 4.1 Menu Overlay Container

**File**: `/wp-content/themes/flatsome-child/functions.php`

**Lines 2495-2515**: Hook to add menu overlay

```php
/**
 * Add menu-open overlay to header
 * This div is controlled by .menu-button click in custom.js
 */
add_action('flatsome_header_wrapper', 'add_menu_open_overlay');
function add_menu_open_overlay() {
    ?>
    <div class="menu-open">
        <?php
        // Output ux_menu shortcode with your custom menu items
        echo do_shortcode('[ux_menu class="header-custom-menu"]
            [ux_menu_link text="Home" link="/"]
            [ux_menu_link text="Delivery" link="/delivery"]
            [ux_menu_link text="Menu" link="/menu"]
            [ux_menu_link text="About" link="/about"]
            [ux_menu_link text="Contact" link="/contact"]
        [/ux_menu]');
        ?>
    </div>
    <?php
}
```

### 4.2 UX Menu Shortcode

**File**: `/wp-content/themes/flatsome/inc/shortcodes/ux_menu.php`

**Function**: `flatsome_render_ux_menu_shortcode()`

```php
function flatsome_render_ux_menu_shortcode( $atts, $content, $tag ) {
    $classes = array( 'ux-menu', 'stack', 'stack-col', 'justify-start' );

    // ... class processing

    return '<div class="' . implode(' ', $classes) . '">'
           . do_shortcode( $content )
           . '</div>';
}
add_shortcode( 'ux_menu', 'flatsome_render_ux_menu_shortcode' );
```

**Template**: `/wp-content/themes/flatsome/inc/builder/shortcodes/templates/ux_menu.html`

---

## 5. CSS Styling

**File**: `/wp-content/themes/flatsome-child/style.css`

### Menu Button (Lines 69-78)
```css
.menu-button {
    width: 50px;
    height: 50px;
    border-radius: 4px;
    background-color: #000;
    background-image: url("images/icon-menu.svg");
    background-repeat: no-repeat;
    background-position: center;
    cursor: pointer;
}
```

### Menu Overlay (Lines 110-138)
```css
.menu-open {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    display: none;
    z-index: 999;
}

.menu-open.show {
    display: flex;
    align-items: end;
}

.menu-open .ux-menu {
    justify-content: center;
    margin-bottom: 100px;
}
```

### Header State Changes (Lines 80-106)
```css
.header.show-menu .header-bg-color {
    background-color: rgb(248, 240, 224);
}

.header.show-menu .header-main {
    border-bottom: none;
}

.header.show-menu .cart-item {
    opacity: 0;
    visibility: hidden;
}

.header.show-menu .menu-close {
    width: 50px;
    height: 50px;
    border-radius: 4px;
    background-color: #F5F5F5;
    background-image: url("images/icon-close.svg");
    background-repeat: no-repeat;
    background-position: center;
    cursor: pointer;
}

.header.show-menu .menu-button {
    display: none;
}
```

---

## 6. Complete Execution Flow

### User Interaction Flow:

```
1. Page Load
   ↓
2. Template renders header (header-main.php:36)
   ↓
3. flatsome_header_elements('header_elements_right') called
   ↓
4. Retrieves ['cart', 'html'] from database
   ↓
5. For 'html': flatsome_get_header_html_element('html')
   ↓
6. Gets topbar_left content from database
   ↓
7. Outputs: <div class="menu-button"></div>
             <div class="menu-close"></div>
   ↓
8. flatsome_header_wrapper hook fires
   ↓
9. add_menu_open_overlay() adds hidden .menu-open div
   ↓
10. JavaScript attaches click handlers
   ↓
11. User clicks .menu-button
   ↓
12. JavaScript adds .show class to .menu-open
   ↓
13. CSS makes overlay visible (display: flex)
   ↓
14. ux_menu shortcode content is displayed
   ↓
15. User clicks .menu-close
   ↓
16. JavaScript removes .show class
   ↓
17. Overlay hidden again
```

---

## 7. Key Files Reference

### Theme Files (Parent - DO NOT EDIT)
- `/wp-content/themes/flatsome/template-parts/header/header-main.php` (Line 36)
- `/wp-content/themes/flatsome/inc/structure/structure-header.php` (Lines 72-121)
- `/wp-content/themes/flatsome/inc/shortcodes/ux_menu.php`
- `/wp-content/themes/flatsome/inc/builder/shortcodes/templates/ux_menu.html`

### Child Theme Files (Customizable)
- `/wp-content/themes/flatsome-child/functions.php` (Lines 2495-2515)
- `/wp-content/themes/flatsome-child/js/custom.js` (Lines 17-39)
- `/wp-content/themes/flatsome-child/style.css` (Lines 69-138)

### Database
- Table: `R6vhU_options`
- Option: `theme_mods_flatsome`

---

## 8. How to Customize

### Change Menu Items

Edit: `/wp-content/themes/flatsome-child/functions.php` (Lines 2505-2511)

```php
echo do_shortcode('[ux_menu class="header-custom-menu"]
    [ux_menu_link text="Home" link="/"]
    [ux_menu_link text="Delivery" link="/delivery"]
    [ux_menu_link text="New Page" link="/new-page"]
[/ux_menu]');
```

### Change Button Styles

Edit: `/wp-content/themes/flatsome-child/style.css`

Modify `.menu-button` (Line 69+) or `.menu-close` (Line 93+)

### Change Overlay Behavior

Edit: `/wp-content/themes/flatsome-child/js/custom.js` (Lines 17-39)

### Change Database Content

Via WordPress Admin:
- Go to: Appearance → Customize → Header → Header Builder
- Drag/drop elements in the Header Main - Right Area

Or via SQL:
```sql
UPDATE R6vhU_options
SET option_value = 'new_serialized_data'
WHERE option_name = 'theme_mods_flatsome';
```

---

## 9. Debugging

### View Database Content

```sql
SELECT option_value
FROM R6vhU_options
WHERE option_name = 'theme_mods_flatsome';
```

### Add Debug Logging

In `/wp-content/themes/flatsome/inc/structure/structure-header.php`:

```php
function flatsome_header_elements( $options, $type = '' ) {
    $get_options = get_theme_mod( $options );

    // Debug
    if ( $options === 'header_elements_right' ) {
        error_log( 'Array Values: ' . json_encode( $get_options ) );
    }

    // ... rest of function
}
```

Enable WordPress debugging in `wp-config.php`:
```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

View logs: `wp-content/debug.log`

---

## 10. Notes

- The `.menu-open` div is added via PHP hook, not stored in database
- The menu button HTML (`topbar_left`) IS stored in database
- The `ux_menu` shortcode is a Flatsome theme feature
- All customizations should be in the child theme
- The system uses CSS classes to toggle visibility (no display: none/block in JS)

---

**Last Updated**: 2025-01-07
**Created by**: Claude Code Analysis
