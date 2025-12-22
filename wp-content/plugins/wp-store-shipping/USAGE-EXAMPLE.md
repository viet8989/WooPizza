# Usage Example - Store List with Map Shortcode

## Quick Start

### Step 1: Create a New Page
1. Go to WordPress Admin Dashboard
2. Navigate to **Pages → Add New**
3. Give your page a title (e.g., "Store Locations", "Our Stores", "Find Us")

### Step 2: Add the Shortcode
In the page editor, add one of these shortcodes:

#### Basic Example (Default Settings)
```
[wpsl_stores_with_map]
```
This will display:
- Store list: 300px width, 600px max height with scroll
- Map: Remaining width, 600px height
- Zoom level: 12
- Shows all stores

#### Custom Heights Example
```
[wpsl_stores_with_map map_height="500" list_height="500"]
```
Better for pages with less vertical space.

#### Category Filter Example
```
[wpsl_stores_with_map category="restaurant"]
```
Only shows stores in the "restaurant" category.

#### Full Custom Example
```
[wpsl_stores_with_map category="restaurant,cafe" map_height="700" list_height="600" zoom="14"]
```
Full control over display and filtering.

### Step 3: Publish
Click **Publish** button to make the page live.

## Visual Layout

```
┌────────────────────────────────────────────────────┐
│                    Page Content                     │
├───────────────┬────────────────────────────────────┤
│   Store List  │                                    │
│   (300px)     │                                    │
│               │                                    │
│  ┌─────────┐  │                                    │
│  │ Store 1 │  │                                    │
│  │ Store 2 │  │         OpenStreetMap             │
│  │ Store 3 │  │         (Flexible width)          │
│  │ Store 4 │  │                                    │
│  │ Store 5 │  │                                    │
│  │    ▼    │  │                                    │
│  └─────────┘  │                                    │
│   Scrollable  │                                    │
└───────────────┴────────────────────────────────────┘
```

## Mobile Layout (< 768px)

```
┌────────────────────────────┐
│      Store List (Full)     │
│      ┌──────────────┐      │
│      │   Store 1    │      │
│      │   Store 2    │      │
│      │   Store 3    │      │
│      │      ▼       │      │
│      └──────────────┘      │
│       (300px height)       │
├────────────────────────────┤
│                            │
│      OpenStreetMap         │
│       (Full width)         │
│                            │
└────────────────────────────┘
```

## Sample Page Setup

### Example 1: Simple Store Locator Page

**Page Title:** Find Our Locations

**Content:**
```
<h2>Visit Our Stores</h2>
<p>We have multiple locations to serve you better. Click on any store below to view its location on the map.</p>

[wpsl_stores_with_map]

<p>Can't find a store near you? <a href="/contact">Contact us</a> for more information.</p>
```

### Example 2: Restaurant Locations Only

**Page Title:** Restaurant Locations

**Content:**
```
<h2>Our Restaurants</h2>
<p>Find the nearest Terra Viva Pizza restaurant to enjoy fresh, authentic Italian pizza.</p>

[wpsl_stores_with_map category="restaurant" zoom="13"]

<h3>Opening Hours</h3>
<p>All restaurants open daily from 11:00 AM to 10:00 PM</p>
```

### Example 3: Compact Version for Sidebar/Widget

**Using HTML Widget:**
```
<h3>Quick Store Finder</h3>
[wpsl_stores_with_map map_height="400" list_height="400" zoom="11"]
```

## Testing the Page

After publishing, test these interactions:

1. **Click on a store** in the list
   - The store should be highlighted
   - Map should pan to the store location
   - Marker popup should open
   - Map should zoom to level 15

2. **Click on a map marker**
   - Popup should show store details
   - Store information should match the list

3. **Scroll the store list**
   - List should scroll independently from the map
   - Map should stay in view

4. **Test on mobile**
   - Open the page on a mobile device
   - Verify the layout stacks vertically
   - Check that phone numbers are clickable
   - Ensure map is usable with touch

## Common Page Layouts

### Full Width Page
```
[wpsl_stores_with_map map_height="700" list_height="700"]
```
Best for dedicated store locator pages.

### Two-Column Layout (Store locator in main column)
```
[wpsl_stores_with_map map_height="600" list_height="600"]
```
Works well with sidebar layouts.

### Above the Fold (Visible without scrolling)
```
[wpsl_stores_with_map map_height="500" list_height="500" zoom="12"]
```
Good for homepage or landing pages.

## Advanced: Multiple Shortcodes on Same Page

You can use multiple instances on the same page:

```
<h2>Our Restaurants</h2>
[wpsl_stores_with_map category="restaurant" map_height="400"]

<h2>Our Cafes</h2>
[wpsl_stores_with_map category="cafe" map_height="400"]
```

Each instance is independent with unique IDs.

## Embedding in Theme Template

For developers who want to add this to a theme template:

```php
<?php
// In your theme template file (e.g., page-stores.php)
echo do_shortcode('[wpsl_stores_with_map map_height="600" list_height="600"]');
?>
```

## Tips for Best Results

1. **Store Data Quality**
   - Ensure all stores have complete addresses
   - Verify latitude/longitude coordinates are accurate
   - Add phone numbers for better user experience

2. **Page Performance**
   - The shortcode loads Leaflet library (lightweight)
   - OpenStreetMap tiles load progressively
   - Use browser caching for better performance

3. **SEO Considerations**
   - Add descriptive text above/below the shortcode
   - Include relevant keywords about store locations
   - Use proper heading structure (H2, H3)

4. **Accessibility**
   - Provide text descriptions of store locations
   - Ensure phone numbers are marked up correctly
   - Add alt text to any images on the page

## Support

If you encounter any issues:
1. Check the browser console for JavaScript errors
2. Verify stores are published and have coordinates
3. Test on different browsers
4. Check theme compatibility

For additional help, refer to [SHORTCODE-STORES-MAP.md](SHORTCODE-STORES-MAP.md) for detailed documentation.
