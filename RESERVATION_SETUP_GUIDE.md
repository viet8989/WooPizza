# WooPizza Reservation System - Setup Guide

## 🎉 Plugin Successfully Created!

The Custom Reservation Plugin has been created at:
`wp-content/plugins/custom-reservation-plugin/`

## 📋 Quick Setup (3 Steps)

### Step 1: Activate the Plugin

1. Login to WordPress Admin: `http://your-site.com/wp-admin`
2. Go to **Plugins** → **Installed Plugins**
3. Find "**Custom Reservation Plugin**"
4. Click **Activate**

✅ This will automatically create the database tables and add 2 default branches.

### Step 2: Create Reservation Page

1. Go to **Pages** → **Add New**
2. **Title**: `Reservation`
3. **Content**: Add this shortcode:
   ```
   [reservation_form]
   ```
4. Click **Publish**
5. Note the page URL (e.g., `http://your-site.com/reservation/`)

### Step 3: View Admin Panel

1. In WordPress admin sidebar, you'll see a new menu: **Reservations** (with calendar icon)
2. Click **Reservations** → **All Reservations** to view booking records
3. Click **Reservations** → **Branches** to manage locations

## ✨ Features Included

### Frontend Reservation Form

- **Date Picker**: Select future dates only
- **Time Selection**: Operating hours 5:00 PM - 9:45 PM
- **Auto End Time**: Automatically adds 30 minutes
- **Party Size**: Buttons for 1-7+ guests
- **Branch Selection**: Radio buttons for each location
- **Reservation Types**:
  - Standard Reservation
  - VIP Reservation
  - Catering Room
- **Customer Information**: Name, Email, Phone
- **Special Requests**: Text area for notes (allergies, high chair, etc.)
- **Policies**: Deposit notice for 10+ guests, 48-hour cancellation policy

### Admin Management Panel

**Reservations Page** (`/wp-admin/admin.php?page=crp_reservations`):
- View all reservations in a sortable table
- Filter by status: All, Confirmed, Pending, Completed, Cancelled
- Quick actions:
  - **View**: See full reservation details in modal
  - **Confirm**: Mark as confirmed
  - **Complete**: Mark as completed
  - **Cancel**: Mark as cancelled
  - **Delete**: Remove reservation
- Color-coded status badges
- Display customer info, date/time, branch, party size

**Branches Page** (`/wp-admin/admin.php?page=crp_branches`):
- Add new branches (name + address)
- Edit existing branches
- Delete branches
- Default branches included:
  - WooPizza Quận 1: 123 Nguyễn Huệ, Quận 1, TP.HCM
  - WooPizza Quận 3: 456 Võ Văn Tần, Quận 3, TP.HCM

## 🔧 Debugging Tools (As Requested)

### Option 1: Browser Console (Recommended - Easiest)

1. Open the reservation page
2. Press **F12** to open Developer Tools
3. Click **Console** tab
4. You'll see logs like:
   ```javascript
   Reservation form loaded
   Start time changed: 17:30
   End time calculated: 18:00
   Party size selected: 4
   Form submitted
   Form data: customer_name=John&customer_email=...
   Response: {success: true, data: {...}}
   ```

**Copy this code to test in console:**
```javascript
jQuery(document).ready(function($) {
    console.log('=== RESERVATION FORM DEBUG ===');
    console.log('Form exists:', $('#crp-reservation-form').length);
    console.log('Date input:', $('#reservation_date').val());
    console.log('Start time:', $('#start_time').val());
    console.log('Party size:', $('#party-size-selected').val());
    console.log('Selected branch:', $('input[name="branch_id"]:checked').val());
});
```

### Option 2: PHP Debug File

Check server-side logs at:
```
wp-content/plugins/custom-reservation-plugin/debug_save.txt
```

View the file:
```bash
cat wp-content/plugins/custom-reservation-plugin/debug_save.txt
```

### Option 3: Add Console Logs in PHP

Edit `custom-reservation-plugin.php` and add:
```php
echo '<script>console.log("Your debug message here");</script>';
```

Example locations:
- Line 60: After form renders
- Line 267: When AJAX receives data
- Line 313: After database insert

### Option 4: File Logging in PHP

Already implemented! The plugin automatically logs to `debug_save.txt`:
- When form is rendered
- When reservation is submitted
- All POST data received
- Database operation results

## 🧪 Testing Checklist

### Test the Reservation Form:

1. ✅ Visit `/reservation/` page
2. ✅ Select a future date
3. ✅ Select start time (e.g., 18:00) - end time should auto-populate (18:30)
4. ✅ Click different party size buttons
5. ✅ Select a branch
6. ✅ Choose reservation type
7. ✅ Fill in name, email, phone
8. ✅ Add special request note
9. ✅ Submit form
10. ✅ Check for success message
11. ✅ Check email inbox for confirmation

### Test the Admin Panel:

1. ✅ Go to **Reservations** menu in admin
2. ✅ Verify reservation appears in list
3. ✅ Click **View** to see details modal
4. ✅ Change status to **Confirmed**
5. ✅ Filter by status
6. ✅ Go to **Branches** submenu
7. ✅ Add a new branch
8. ✅ Edit a branch
9. ✅ Go back to reservation form and verify new branch appears

### Test Conflict Detection:

1. ✅ Make a reservation for tomorrow at 18:00
2. ✅ Try to make another reservation for same date, time, branch, and type
3. ✅ Should see error: "This time slot is already booked"
4. ✅ Change to different time - should succeed

## 🎨 Customization

### Change Colors

Edit `custom-reservation-plugin.php` around line 300-350:

```css
/* Change primary color from red to blue */
#crp-reservation-form h2 {
    color: #CD0000; /* Change this */
}

.party-btn.selected {
    background-color: #CD0000; /* Change this */
}

.submit-btn {
    background-color: #CD0000; /* Change this */
}
```

### Change Operating Hours

Edit line 71 in `custom-reservation-plugin.php`:

```html
<input type="time" name="start_time" id="start_time" required min="17:00" max="21:45">
<small>Operating hours: 5:00 PM – 9:45 PM</small>
```

### Add More Reservation Types

Edit lines 92-104:

```html
<div>
    <input type="radio" name="reservation_type" value="Your New Type">
    <strong>Your New Type</strong>
</div>
```

## 📧 Email Configuration

Emails are sent using WordPress `wp_mail()` function. To improve email delivery:

1. Install SMTP plugin (e.g., WP Mail SMTP)
2. Configure with your email provider
3. Emails will automatically use SMTP settings

## 🗄️ Database Structure

**Table: `wp_reservations`**
```sql
- id (auto increment)
- customer_name (varchar 255)
- customer_email (varchar 255)
- customer_phone (varchar 50)
- customer_message (text)
- reservation_date (date)
- start_time (time)
- end_time (time)
- time_slot (varchar 50)
- branch_id (int)
- party_size (varchar 10)
- reservation_type (varchar 50)
- status (varchar 20) - default: 'pending'
- created_at (datetime)
```

**Table: `wp_reservation_branches`**
```sql
- id (auto increment)
- name (varchar 255)
- address (varchar 255)
```

## 🐛 Troubleshooting

### Form doesn't appear
- Check if shortcode `[reservation_form]` is added to page
- Check if plugin is activated
- Check browser console for JavaScript errors

### Email not received
- Check spam folder
- Verify email address is correct
- Install SMTP plugin for better delivery

### "Database error" message
- Check MySQL user has permissions
- Verify tables were created (check phpMyAdmin)
- Check `debug_save.txt` for SQL errors

### Reservations not showing in admin
- Clear browser cache
- Check if reservations exist in database
- Check for PHP errors in debug log

## 📁 File Structure

```
wp-content/plugins/custom-reservation-plugin/
├── custom-reservation-plugin.php  (Main plugin file)
├── admin/
│   └── admin-page.php            (Admin panel pages)
├── debug_save.txt                (Debug log file)
└── README.md                     (Documentation)
```

## 🚀 Next Steps

1. **Activate the plugin** in WordPress admin
2. **Create the Reservation page** with shortcode
3. **Customize branches** to match your locations
4. **Test the complete flow** from form to admin
5. **Style customization** if needed
6. **Add to menu** (Appearance → Menus → Add Reservation page)

## 💡 Pro Tips

- Add reservation page to main navigation menu
- Test email delivery before going live
- Set up email notifications for new bookings (can be added)
- Use browser console for real-time debugging
- Check `debug_save.txt` for server-side issues
- Take regular database backups

---

**Plugin Status**: ✅ Ready to Use
**Location**: `wp-content/plugins/custom-reservation-plugin/`
**Shortcode**: `[reservation_form]`
**Admin URL**: `/wp-admin/admin.php?page=crp_reservations`

Enjoy your new reservation system! 🍕
