# WooPizza TODO List

## Pending Tasks

### 1. Fix Sticky Header Issue
**Status:** In Progress
**Priority:** Medium
**Description:** The header menu stays fixed at the top when scrolling. Need to make it scroll away with the page content.

**What we've tried so far:**
- ✅ Added CSS overrides in `wp-content/themes/flatsome-child/style.css` (lines 39-55)
- ✅ Added PHP filters in `wp-content/themes/flatsome-child/functions.php` (lines 17-26)
- ✅ Added JavaScript with MutationObserver in `functions.php` (lines 29-85)

**Next steps to try:**
1. Check WordPress Admin → Appearance → Customize → Header → Sticky Header settings
2. Clear all WordPress caching plugins (WP Super Cache, W3 Total Cache, etc.)
3. Check if there's a CDN cache that needs clearing
4. Inspect the live site's computed styles in browser DevTools to see what CSS is actually being applied
5. Consider temporarily disabling the Flatsome parent theme's sticky header JavaScript file

**Files modified:**
- `wp-content/themes/flatsome-child/style.css`
- `wp-content/themes/flatsome-child/functions.php`

**Live Site:** https://terravivapizza.com

---

## Completed Tasks

*(No completed tasks yet)*

---

## Notes

- This TODO file tracks tasks that need to be completed for the WooPizza project
- Update this file as tasks are completed or new tasks are added
