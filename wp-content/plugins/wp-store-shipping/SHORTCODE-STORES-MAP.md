# Store List with Map Shortcode

## Overview

The `[wpsl_stores_with_map]` shortcode displays a responsive layout with:
- **Left side (300px width)**: Scrollable list of stores with fixed height
- **Right side (remaining width)**: Interactive OpenStreetMap using Leaflet library

## Basic Usage

Simply add the shortcode to any page or post:

```
[wpsl_stores_with_map]
```

## Shortcode Attributes

### category
Filter stores by category slug(s). Multiple categories can be separated by commas.

**Default:** (empty - shows all stores)

**Example:**
```
[wpsl_stores_with_map category="restaurant"]
[wpsl_stores_with_map category="restaurant,cafe"]
```

### map_height
Set the height of the map in pixels.

**Default:** `600`

**Example:**
```
[wpsl_stores_with_map map_height="500"]
```

### list_height
Set the maximum height of the store list in pixels. The list will scroll if content exceeds this height.

**Default:** `600`

**Example:**
```
[wpsl_stores_with_map list_height="400"]
```

### zoom
Set the initial zoom level of the map (1-19).

**Default:** `12`

**Example:**
```
[wpsl_stores_with_map zoom="14"]
```

## Complete Examples

### Example 1: Basic usage with custom heights
```
[wpsl_stores_with_map map_height="500" list_height="500"]
```

### Example 2: Filter by category with custom zoom
```
[wpsl_stores_with_map category="restaurant" zoom="13"]
```

### Example 3: All options combined
```
[wpsl_stores_with_map category="restaurant,cafe" map_height="700" list_height="600" zoom="14"]
```

## Features

### Interactive Store List
- Click any store in the list to:
  - Highlight the selected store
  - Pan the map to the store location
  - Open a popup with store details
  - Zoom to level 15 for better detail

### Store Information Displayed
- Store name (title)
- Full address (address, address2, city, state, zip)
- Phone number (clickable to call on mobile devices)

### Map Features
- **OpenStreetMap tiles**: Free, open-source map data
- **Markers**: Each store is marked on the map
- **Popups**: Click markers to view store details
- **Auto-fit bounds**: Map automatically adjusts to show all stores
- **Zoom controls**: Standard zoom in/out controls
- **Responsive**: Mobile-friendly layout

## Responsive Design

### Desktop (> 768px)
- Store list: Fixed 300px width on left
- Map: Flexible width on right
- Both elements displayed side-by-side

### Mobile (≤ 768px)
- Store list: Full width on top with 300px max height
- Map: Full width below the list
- Stacked vertical layout

## Technical Notes

### Requirements
- WordPress with WP Store Shipping plugin installed
- Published stores with valid latitude/longitude coordinates
- jQuery (included with WordPress)
- Internet connection (to load Leaflet library and map tiles)

### External Dependencies
The shortcode automatically loads:
- Leaflet CSS (v1.9.4) from unpkg.com
- Leaflet JS (v1.9.4) from unpkg.com
- OpenStreetMap tiles

### Styling
All CSS is scoped with `.wpsl-stores-with-map-container` prefix to avoid conflicts with theme styles.

### Browser Support
Works in all modern browsers that support:
- Flexbox layout
- ES5 JavaScript
- SVG graphics

## Troubleshooting

### No stores appearing
- Check that stores are published (not draft)
- Verify stores have valid latitude/longitude coordinates
- Check category slug if using category filter

### Map not displaying
- Check browser console for JavaScript errors
- Verify internet connection (needed for map tiles)
- Ensure jQuery is loaded

### Layout issues
- Check for theme CSS conflicts
- Verify the page content area has sufficient width
- Test on different screen sizes

## Support

For issues or feature requests, contact the plugin developer or check the plugin documentation.
