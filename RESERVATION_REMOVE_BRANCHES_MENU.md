# Reservation System - Remove Branches Submenu

## ✅ Update Complete!

The **"Branches"** submenu has been removed from the Reservations admin panel since we're now using **WP Store Locator** plugin to manage store locations.

---

## 🔄 What Changed

### Before:
```
WordPress Admin Sidebar:
├── Reservations (calendar icon)
│   ├── All Reservations
│   └── Branches              ← Custom branch management
├── Store Locator
│   └── Stores                ← WP Store Locator plugin
```

### After:
```
WordPress Admin Sidebar:
├── Reservations (calendar icon)
│   └── All Reservations      ← Only this submenu now
├── Store Locator
│   └── Stores                ← Use this to manage stores
```

---

## 📋 Admin Panel Changes

### 1. **Removed "Branches" Submenu**
- ❌ Old: Reservations → Branches
- ✅ Now: Use Store Locator → Stores

### 2. **Added "Manage Stores" Button**
- ✅ Added button at top of Reservations page
- ✅ Links directly to Store Locator → Stores
- ✅ Quick access to store management

### 3. **Added Helpful Tip Notice**
- ✅ Blue info box at top of page
- ✅ Explains where to manage stores
- ✅ Direct link to Store Locator

---

## 🎯 New Admin Interface

### Reservations Page Header:
```
┌────────────────────────────────────────────────┐
│  Reservations         [Manage Stores]          │
├────────────────────────────────────────────────┤
│  💡 Tip: To manage store locations and         │
│  reservation hours, go to Store Locator →      │
│  Stores in the WordPress admin menu.           │
└────────────────────────────────────────────────┘
```

---

## 📝 Code Changes

### File: `admin/admin-page.php`

#### Change 1: Removed Branches Submenu (Lines 27-28)

**Before:**
```php
add_submenu_page(
    'crp_reservations',
    'Branches',
    'Branches',
    'manage_options',
    'crp_branches',
    'crp_branches_page'
);
```

**After:**
```php
// Branches submenu removed - now using WP Store Locator plugin
```

---

#### Change 2: Updated Page Header (Lines 86-96)

**Before:**
```php
<h1 class="wp-heading-inline">Reservations</h1>
<a href="<?= admin_url('admin.php?page=crp_branches') ?>" class="page-title-action">Manage Branches</a>
```

**After:**
```php
<h1 class="wp-heading-inline">Reservations</h1>
<a href="<?= admin_url('edit.php?post_type=wpsl_stores') ?>" class="page-title-action">Manage Stores</a>
<hr class="wp-header-end">

<div class="notice notice-info" style="margin-top: 20px;">
    <p>
        <strong>💡 Tip:</strong> To manage store locations and reservation hours, go to
        <a href="<?= admin_url('edit.php?post_type=wpsl_stores') ?>"><strong>Store Locator → Stores</strong></a>
        in the WordPress admin menu.
    </p>
</div>
```

---

#### Change 3: Deprecated Branch Management Function (Lines 277-406)

**Before:**
```php
function crp_branches_page()
{
    // Branch management code...
}
```

**After:**
```php
/*
 * DEPRECATED: Branches management page
 *
 * This function is no longer used as we now use WP Store Locator plugin
 * for managing store/branch locations. Keeping this code for reference
 * but it's not accessible from the admin menu.
 *
 * To manage branches, use: Store Locator menu in WordPress admin
 */
/*
function crp_branches_page()
{
    // Branch management code...
}
*/
```

---

## 🔍 How to Manage Stores Now

### Option 1: From Reservations Page
1. Go to **Reservations** in admin sidebar
2. Click **"Manage Stores"** button at top
3. Opens Store Locator → Stores page

### Option 2: Direct Access
1. Go to **Store Locator** in admin sidebar
2. Click **Stores**
3. Manage all stores here

### Option 3: Use the Tip Link
1. Go to **Reservations** page
2. Click the link in the blue tip box
3. Opens Store Locator → Stores

---

## 📊 Store Management in WP Store Locator

### What You Can Do:
- ✅ Add new stores/branches
- ✅ Edit store details (name, address, city, etc.)
- ✅ Set **Reservation Hours** for each day of the week
- ✅ Delete stores
- ✅ Reorder stores
- ✅ Add store images
- ✅ Set store categories
- ✅ And more...

### Key Fields:
- **Title**: Store name (e.g., "WooPizza Quận 1")
- **Address**: Street address
- **City**: City name
- **Reservation Hours**: Operating hours by day of week
  - Monday: 9:00 AM - 5:00 PM
  - Tuesday: 9:00 AM - 5:00 PM
  - etc.

---

## ✨ Benefits

### 1. **Centralized Management**
- ✅ All store data in one place (WP Store Locator)
- ✅ No duplicate management systems
- ✅ Easier to maintain

### 2. **More Features**
- ✅ WP Store Locator has advanced features
- ✅ Map integration
- ✅ Store locator widget for frontend
- ✅ Better store organization

### 3. **Cleaner Admin**
- ✅ Less clutter in admin menu
- ✅ Single source of truth for stores
- ✅ Clear navigation

### 4. **Better Integration**
- ✅ Reservation system reads directly from Store Locator
- ✅ Automatic sync (no manual updates needed)
- ✅ Consistent data across site

---

## 🧪 Testing

### Test 1: Verify Submenu Removed
1. Go to WordPress Admin
2. Look at **Reservations** menu
3. **Verify**: No "Branches" submenu ✅

### Test 2: Verify "Manage Stores" Button
1. Click **Reservations** → **All Reservations**
2. **Verify**: "Manage Stores" button at top right ✅
3. Click the button
4. **Verify**: Opens Store Locator → Stores page ✅

### Test 3: Verify Tip Notice
1. Go to **Reservations** page
2. **Verify**: Blue info box visible ✅
3. **Verify**: Shows tip about Store Locator ✅
4. Click the link in the tip
5. **Verify**: Opens Store Locator → Stores ✅

### Test 4: Verify Old URL Doesn't Work
1. Try to access old Branches page:
   ```
   /wp-admin/admin.php?page=crp_branches
   ```
2. **Verify**: Page not found or redirects ✅

---

## 📸 Visual Comparison

### Before - Admin Menu:
```
Reservations
├─ All Reservations
└─ Branches          ← This submenu
```

### After - Admin Menu:
```
Reservations
└─ All Reservations  ← Only this now
```

### New - Reservations Page Header:
```
┌─────────────────────────────────────┐
│ Reservations      [Manage Stores]   │
├─────────────────────────────────────┤
│ 💡 Tip: To manage store locations   │
│ and reservation hours, go to        │
│ Store Locator → Stores              │
└─────────────────────────────────────┘
```

---

## 🔄 Migration Notes

### No Data Migration Needed!
Since we're using WP Store Locator's stores:
- ✅ No need to migrate data
- ✅ Stores already exist in WP Store Locator
- ✅ Reservations already use `store_id` (WP Store Locator post ID)

### Old Branches Table
The `wp_reservation_branches` table still exists but is **not used**:
- 📦 Can be kept for historical reference
- 🗑️ Can be deleted if you want to clean up database
- ⚠️ Deleting it won't affect current functionality

### To Delete Old Table (Optional):
```sql
DROP TABLE IF EXISTS wp_reservation_branches;
```

---

## 💡 Best Practices

### 1. **Always Use Store Locator**
- ✅ Don't create stores manually in database
- ✅ Use Store Locator → Stores interface
- ✅ Set reservation hours for each store

### 2. **Keep Stores Organized**
- ✅ Use clear, consistent naming (e.g., "WooPizza Quận 1")
- ✅ Fill in all fields (address, city, etc.)
- ✅ Set reservation hours for all days

### 3. **Test After Changes**
- ✅ After adding/editing stores in Store Locator
- ✅ Visit reservation page
- ✅ Select date/time and verify store appears

---

## 🎯 Summary

✅ **"Branches" submenu removed** from Reservations menu
✅ **"Manage Stores" button added** to Reservations page
✅ **Helpful tip notice added** with link to Store Locator
✅ **Old branch management function deprecated** (commented out)
✅ **Cleaner admin interface** - single source of truth
✅ **Better integration** with WP Store Locator

**Status:** ✅ Ready to use! 🎉

---

## 📚 Related Documentation

- **[RESERVATION_WPSL_INTEGRATION.md](RESERVATION_WPSL_INTEGRATION.md)** - How WP Store Locator integration works
- **[RESERVATION_HIDE_BRANCH_SECTION.md](RESERVATION_HIDE_BRANCH_SECTION.md)** - Branch section visibility
- **[RESERVATION_LOAD_BRANCHES_UPDATE.md](RESERVATION_LOAD_BRANCHES_UPDATE.md)** - Branch loading logic

---

**Use Store Locator → Stores to manage all store locations and reservation hours!** 🏪
