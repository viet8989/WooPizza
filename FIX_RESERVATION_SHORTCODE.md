# Fix Reservation Page Shortcode

## ❌ Problem

The reservation page is showing `[custom_order_form]` as plain text instead of the reservation form.

## 🔍 Root Cause

The page is using the **wrong shortcode**:
- ❌ Current: `[custom_order_form]` (from old booking plugin)
- ✅ Should be: `[reservation_form]` (from new reservation plugin)

---

## ✅ Solution

### Option 1: Edit Page in WordPress Admin (Recommended)

1. **Go to WordPress Admin**
2. **Click Pages** → **All Pages**
3. **Find the "Reservation" page**
4. **Click Edit**
5. **Change the shortcode:**
   - Remove: `[custom_order_form]`
   - Add: `[reservation_form]`
6. **Click Update**
7. **Visit the page** - should now show the form!

---

### Option 2: Quick SQL Update

If you have database access, run this SQL:

```sql
-- Find the reservation page
SELECT ID, post_title, post_content
FROM wp_posts
WHERE post_type = 'page'
AND (post_title LIKE '%reservation%' OR post_content LIKE '%custom_order_form%');

-- Update the shortcode (replace POST_ID with actual page ID)
UPDATE wp_posts
SET post_content = '[reservation_form]'
WHERE ID = POST_ID;
```

---

## 🔧 Shortcode Reference

### Two Different Plugins:

| Plugin | Shortcode | Purpose |
|--------|-----------|---------|
| **Custom Booking Plugin** | `[custom_order_form]` | Old booking system (not integrated with Store Locator) |
| **Custom Reservation Plugin** | `[reservation_form]` | New reservation system (integrated with Store Locator) ✅ |

---

## ✨ What You Should See After Fix

### Before (showing shortcode as text):
```
Page content:
[custom_order_form]
```

### After (showing the form):
```
┌───────────────────────────────────────┐
│  Make a Reservation                   │
├───────────────────────────────────────┤
│  Reservation Date & Time *            │
│  [Date Input]  [Time Input]           │
│                                       │
│  Party Size (Number of Guests) *      │
│  [1] [2] [3] [4] [5] [6] [7] [7+]    │
│                                       │
│  (Branch section appears after        │
│   selecting date & time)              │
│                                       │
│  Reservation Type *                   │
│  ○ Standard Reservation               │
│  ○ VIP Reservation                    │
│  ○ Catering Room                      │
│                                       │
│  Your Name *                          │
│  [Input]                              │
│                                       │
│  Email Address *                      │
│  [Input]                              │
│                                       │
│  Phone Number *                       │
│  [Input]                              │
│                                       │
│  Special Requests / Notes             │
│  [Textarea]                           │
│                                       │
│  [Make Reservation]                   │
└───────────────────────────────────────┘
```

---

## 🧪 Verification Steps

After updating the shortcode:

1. ✅ **Visit the reservation page**
2. ✅ **Verify**: You see the form (not `[custom_order_form]` text)
3. ✅ **Select date**: e.g., tomorrow
4. ✅ **Select time**: e.g., 14:00
5. ✅ **Verify**: "Select Branch" section slides down
6. ✅ **Verify**: Branches load or "no branch available" message
7. ✅ **Press F12**: Check console logs (📅⏰🚀✅)

---

## ❓ Common Questions

### Q: Can I have both plugins active?
**A:** Yes, but use different pages:
- Page 1: Use `[custom_order_form]` (old booking plugin)
- Page 2: Use `[reservation_form]` (new reservation plugin)

### Q: Which plugin should I use?
**A:** Use **Custom Reservation Plugin** (`[reservation_form]`) because:
- ✅ Integrated with WP Store Locator
- ✅ Validates reservation hours
- ✅ Dynamic branch loading
- ✅ Better user experience

### Q: What if the form still doesn't show?
**A:** Check:
1. Plugin is activated (Plugins → Custom Reservation Plugin → Activate)
2. Correct shortcode: `[reservation_form]` (not `[custom_order_form]`)
3. Page is published (not draft)
4. No theme conflicts (try default WordPress theme)

---

## 🎯 Quick Fix Command

If you know the page ID (e.g., 123), run this in your database:

```sql
UPDATE wp_posts
SET post_content = '[reservation_form]'
WHERE ID = 123
AND post_type = 'page';
```

Or if you want to find and replace all instances:

```sql
UPDATE wp_posts
SET post_content = REPLACE(post_content, '[custom_order_form]', '[reservation_form]')
WHERE post_type = 'page'
AND post_content LIKE '%custom_order_form%';
```

---

## ✅ Summary

**Problem:** Page shows `[custom_order_form]` as text
**Cause:** Wrong shortcode for new reservation plugin
**Fix:** Change to `[reservation_form]`
**How:** Edit page in WordPress admin or update via SQL

**Correct Shortcode:** `[reservation_form]` ✅

Done! 🎉
