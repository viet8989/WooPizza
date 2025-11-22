# Custom Reservation Plugin

A complete restaurant reservation system for WooPizza WordPress site, integrated with WP Store Locator for dynamic branch management.

## Features

✓ **Reservation Form** with the following fields:
- Reservation Date (date picker, defaults to today, Safari-compatible)
- Time Selection (dropdown with 30-minute intervals from 10:00 AM to 11:00 PM)
- Party Size Selection (quantity selector, 1-20 guests)
- Dynamic Branch Selection (shows only available branches based on date/time)
- Reservation Type (Standard, VIP, Catering Room)
- Customer Name, Email, Phone
- Special Requests / Notes
- Real-time availability checking

✓ **WP Store Locator Integration**:
- Automatically loads stores from WP Store Locator plugin
- Checks store operating hours (`wpsl_reservation_hours` postmeta)
- Supports both 12-hour format (9:00 AM, 5:00 PM) and 24-hour format (00:00, 23:45)
- Only shows stores available for selected date/time
- Stores reservation with WP Store Locator post ID

✓ **Smart Validation**:
- **Past datetime validation** - Prevents booking past dates/times with warning message
- **Operating hours validation** - Only shows stores open at selected time
- **Time conflict checking** - Prevents double-booking same slot
- **Real-time feedback** - Instant warnings and branch availability updates

✓ **Admin Management Panel**:
- View all reservations with filtering (All, Confirmed, Pending, Completed, Cancelled)
- **Edit reservations** - Modify customer details, date/time, party size, type, and status
- **Admin notes** - Add internal notes to reservations (not visible to customers)
- Update reservation status with quick action links
- **Audit trail** - Track who last updated each reservation and when
- View detailed reservation information including store details
- Delete reservations
- Manage branches via WP Store Locator

✓ **Browser Compatibility**:
- ✅ **Safari-compatible** - Uses dropdown select instead of time input
- ✅ Chrome, Firefox, Edge fully supported
- ✅ Mobile-responsive design
- ✅ Cross-browser date/time handling

✓ **Automatic Features**:
- Email notification sent to customers with store details
- New reservations start with "pending" status for admin review
- Time conflict checking (prevents double-booking)
- 30-minute default reservation duration
- Automatic timestamp tracking (created_at, updated_at)
- User activity tracking (who made the last update)
- Responsive design for all devices
- AJAX form submission with loading states
- Comprehensive logging for debugging

## Requirements

- WordPress 5.0 or higher
- WooCommerce (optional, for styling compatibility)
- **WP Store Locator plugin** (required for branch management)

## Installation & Activation

### Step 1: Install WP Store Locator

This plugin requires **WP Store Locator** for managing store branches and operating hours:

1. Install and activate WP Store Locator plugin
2. Go to **Store Locator** → **Add New Store**
3. Add your restaurant branches with address and details
4. **Important**: Configure operating hours in the store's custom field `wpsl_reservation_hours`

**Operating Hours Format:**
The plugin supports two time formats:
- **12-hour format**: `9:00 AM,5:00 PM` (open 9 AM to 5 PM)
- **24-hour format**: `00:00,23:45` (open midnight to 11:45 PM)

Example `wpsl_reservation_hours` structure:
```php
array(
    'monday' => array('9:00 AM,5:00 PM'),
    'tuesday' => array('9:00 AM,5:00 PM'),
    // ... other days
    'saturday' => array('00:00,23:45'), // 24-hour operation
    'sunday' => array('9:00 AM,9:00 PM')
)
```

### Step 2: Activate the Reservation Plugin

1. Go to WordPress Admin Dashboard
2. Navigate to **Plugins** → **Installed Plugins**
3. Find "**Custom Reservation Plugin**"
4. Click **Activate**

### Step 3: Create Reservation Page

1. Go to **Pages** → **Add New**
2. Title: `Reservation`
3. In the content area, add the shortcode:
   ```
   [reservation_form]
   ```
4. Click **Publish**

### Step 4: Test the Form

1. Visit the reservation page
2. Select a date (defaults to today)
3. Select a time from the dropdown
4. Available branches will load automatically based on operating hours

## Usage

### Frontend Usage

Customers can make reservations by:
1. Visiting the Reservation page (e.g., `yoursite.com/reservation/`)
2. Selecting date, time, party size, branch, and reservation type
3. Filling in their contact information
4. Submitting the form
5. Receiving a confirmation email

### Admin Usage

#### View Reservations

1. Go to **Reservations** → **All Reservations**
2. Filter by status: All, Confirmed, Pending, Completed, Cancelled
3. Click **View** to see full details including:
   - Customer information
   - Reservation details
   - Special requests from customer
   - Admin notes (if any)
   - Created timestamp
   - Last updated timestamp and user (if modified)

#### Edit Reservations

1. Go to **Reservations** → **All Reservations**
2. Click **Edit** on any reservation
3. Edit form modal appears with all fields:
   - Customer name, email, phone
   - Reservation date and time
   - Party size
   - Reservation type
   - Status (pending/confirmed/completed/cancelled)
   - Customer's special requests
   - Admin notes (internal use only)
4. Click **Save Changes**
5. System automatically tracks who made the edit and when

#### Add Admin Notes

1. Click **Edit** on a reservation
2. Scroll to **Admin Notes** field
3. Add internal notes such as:
   - "Customer requested window seat"
   - "Called to confirm - customer will arrive 10 minutes late"
   - "VIP guest - prepare special welcome"
   - "Dietary restrictions: gluten-free"
4. Notes are only visible to admin staff, never shown to customers

#### Update Status

1. Use quick action links: **Confirm**, **Complete**, **Cancel**
2. Or use **Edit** to change status manually
3. Status changes are tracked with timestamp and user info

#### Manage Branches

1. Go to **Store Locator** → **Stores** (WP Store Locator plugin)
2. Add, edit, or delete store locations
3. Configure operating hours in `wpsl_reservation_hours` custom field

## Database Tables

The plugin creates one custom table and integrates with WP Store Locator:

1. **`wp_reservations`** - Stores all reservation data
   - `id` - Unique reservation ID
   - `customer_name`, `customer_email`, `customer_phone` - Customer contact info
   - `customer_message` - Special requests/notes from customer
   - `reservation_date`, `start_time`, `end_time` - Reservation datetime
   - `time_slot` - Display format (e.g., "18:00 - 18:30")
   - `store_id` - WP Store Locator post ID (references wpsl_stores)
   - `party_size` - Number of guests (1-20)
   - `reservation_type` - Standard/VIP/Catering Room
   - `status` - pending/confirmed/completed/cancelled (default: pending)
   - `admin_notes` - Internal notes for staff only (not visible to customers)
   - `created_at` - Timestamp when reservation was created
   - `updated_at` - Timestamp when reservation was last modified
   - `updated_by` - WordPress user ID of admin who last updated the record

2. **WP Store Locator Integration** (uses existing tables):
   - Stores managed via WP Store Locator plugin
   - Operating hours stored in `wpsl_reservation_hours` postmeta
   - Store details (name, address, city) from wpsl_stores post type

3. **Legacy Table** (deprecated, no longer created):
   - `wp_reservation_branches` - Removed from installation script
   - All branches now managed via WP Store Locator plugin

## Debugging

### Debug Logging

The plugin includes comprehensive logging:

1. **Browser Console Logs** (F12 → Console tab):
   - Form loading
   - User interactions
   - Form submission
   - AJAX responses

2. **PHP Debug File**: `wp-content/plugins/custom-reservation-plugin/debug_save.txt`
   - Form rendering events
   - Reservation submissions
   - Database operations
   - Error messages

### Enable Console Logging

Open browser console (F12) and you'll see logs like:
```javascript
console.log('Reservation form loaded');
console.log('Start time changed:', '17:30');
console.log('Party size selected:', 4);
console.log('Form submitted');
console.log('Response:', {success: true, ...});
```

### Check Debug File

View the debug file:
```bash
cat wp-content/plugins/custom-reservation-plugin/debug_save.txt
```

### PHP Error Logging

Add this code to view PHP logs in the file:
```php
// In custom-reservation-plugin.php, add this anywhere:
echo '<script>console.log("Debug message here");</script>';

// Or write to debug file:
$log = "Debug message at " . date('Y-m-d H:i:s') . "\n";
file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $log, FILE_APPEND);
```

## Shortcode

Use `[reservation_form]` shortcode anywhere to display the reservation form.

## Troubleshooting

### No Branches Showing Up

**Problem**: When selecting date/time, no branches appear or it says "No branch can make reservation"

**Solutions**:
1. **Check WP Store Locator stores exist**:
   - Go to **Store Locator** → **All Stores**
   - Ensure you have at least one published store

2. **Verify operating hours are configured**:
   - Edit a store in WP Store Locator
   - Check if `wpsl_reservation_hours` custom field exists
   - Ensure hours are in correct format (see Installation section)

3. **Check debug logs**:
   ```bash
   cat wp-content/plugins/custom-reservation-plugin/debug_save.txt
   ```
   Look for messages like:
   - `✓ Store X: [Name] - AVAILABLE`
   - `✗ Store X: [Name] - NOT AVAILABLE`
   - Time parsing errors

4. **Time format issues**:
   - Ensure operating hours use either `9:00 AM,5:00 PM` (12-hour) or `00:00,23:45` (24-hour)
   - Don't mix formats in the same store
   - Check for typos (e.g., `9:00AM` missing space)

### Date/Time Fields Not Working in Safari

**Problem**: Can't select date or time in Safari browser

**Solution**: This has been fixed! The plugin now uses:
- Date picker for date selection (Safari-compatible)
- Dropdown select for time selection (works in all browsers)
- If issues persist, hard refresh: Cmd+Shift+R

### Past Date/Time Warning Showing Incorrectly

**Problem**: Warning shows even for future dates

**Solution**: Check your server time:
- The validation compares against server's current datetime
- Ensure WordPress timezone is set correctly: **Settings** → **General** → **Timezone**
- Server time should match your local timezone

### Branches Show But Can't Submit

**Problem**: Form submission fails even after selecting a branch

**Validation checks**:
1. Date/time must be in the future
2. Branch must be selected (one should auto-select)
3. Party size must be between 1-20
4. All required fields must be filled

**Check browser console** (F12 → Console) for JavaScript errors

### Email Confirmations Not Sending

**Problem**: Reservations save but customers don't receive emails

**Solutions**:
1. Check WordPress can send emails:
   ```php
   wp_mail('test@example.com', 'Test', 'Test message');
   ```
2. Install SMTP plugin (e.g., WP Mail SMTP)
3. Check spam/junk folders
4. Verify customer email address is valid

### Time Conflicts Not Detected

**Problem**: Double-booking same time slot

**Check**:
1. Ensure `store_id` is being saved correctly
2. Verify `reservation_type` matches (Standard ≠ VIP ≠ Catering)
3. Check database for existing reservations:
   ```sql
   SELECT * FROM wp_reservations WHERE reservation_date = '2025-11-22' AND store_id = 649;
   ```

### Advanced Debugging

**Enable detailed logging**:

The plugin already logs detailed information to:
- **PHP logs**: `wp-content/plugins/custom-reservation-plugin/debug_save.txt`
- **Browser console**: Open DevTools (F12) → Console tab

**What's logged**:
- Store availability checks with time parsing details
- AJAX request/response data
- Form validation results
- Datetime comparison logic

**Example log output**:
```
=== Checking Store 649 Availability ===
  Date: 2025-11-23, Time: 18:00
  Day of week: saturday
  Checking slot: 9:00 AM,5:00 PM
    Open: 9:00 AM -> 09:00
    Close: 5:00 PM -> 17:00
    ✗ Time NOT in range (18:00 not between 09:00 and 17:00)
  ✗ RESULT: No matching time slot found
```

## Features Comparison with Terra Viva Pizza

| Feature | Terra Viva | WooPizza Plugin | Status |
|---------|-----------|----------------|--------|
| Date Selection | ✓ | ✓ | ✓ |
| Time Selection | ✓ | ✓ | ✓ |
| Auto End Time (30 min) | ✓ | ✓ | ✓ |
| Party Size Buttons | ✓ | ✓ | ✓ |
| Branch Selection | ✓ | ✓ | ✓ |
| Reservation Types | ✓ | ✓ | ✓ |
| Customer Info | ✓ | ✓ | ✓ |
| Special Requests | ✓ | ✓ | ✓ |
| Email Confirmation | ✓ | ✓ | ✓ |
| Time Conflict Check | ✓ | ✓ | ✓ |
| Admin Management | ✓ | ✓ | ✓ |
| Deposit Notice (10+) | ✓ | ✓ | ✓ |
| Cancellation Policy | ✓ | ✓ | ✓ |

## Support

For issues or questions, check:
1. Browser console for JavaScript errors
2. `debug_save.txt` file for server-side logs
3. WordPress debug log if enabled

## Version History

### v1.3 (Current) - November 2025
**New Features:**
- ✅ **Edit Reservation Functionality** - Full edit modal to modify all reservation details
- ✅ **Admin Notes Field** - Internal staff notes visible only in admin panel
- ✅ **Audit Trail** - Track who updated reservations and when
- ✅ **Updated Tracking** - `updated_at` and `updated_by` fields added to database
- ✅ **Pending by Default** - New reservations save as "pending" for admin review
- ✅ **Code Cleanup** - Removed deprecated `wp_reservation_branches` table code

**Improved Email Notifications:**
- Updated subject line to "Reservation Request Received"
- Clarified that reservations are pending confirmation
- Better messaging for customer expectations

**Bug Fixes:**
- Fixed default status (was "confirmed", now correctly "pending")
- Improved email wording to reflect pending workflow

**Database Changes:**
- Added `admin_notes TEXT` field
- Added `updated_at DATETIME` field with auto-update
- Added `updated_by BIGINT` field to track admin user

### v1.2 - November 2025
**Major Updates:**
- ✅ **Safari Compatibility Fix** - Replaced `<input type="time">` with dropdown select
- ✅ **WP Store Locator Integration** - Dynamic branch loading from WP Store Locator plugin
- ✅ **Dual Time Format Support** - Handles both 12-hour (9:00 AM) and 24-hour (00:00) formats
- ✅ **Past Datetime Validation** - Real-time warning for past dates/times with yellow alert box
- ✅ **Default Date** - Form now defaults to today's date
- ✅ **Enhanced Debugging** - Detailed availability check logging with time parsing details
- ✅ **Time Range** - Time slots from 10:00 AM to 11:00 PM in 30-minute intervals
- ✅ **Hidden End Time Display** - Cleaner UI without showing end time
- ✅ **Real-time Branch Loading** - AJAX-based availability checking
- ✅ **Improved UX** - Loading states, clear error messages, better validation feedback

**Bug Fixes:**
- Fixed Safari date/time input not working
- Fixed stores always showing as unavailable (time format parsing issue)
- Fixed validation to check full datetime instead of just date
- Fixed party size field reference in form submission

**Technical Improvements:**
- JavaScript validation before AJAX calls
- Server-side datetime validation for security
- Comprehensive error logging for troubleshooting
- Safari-specific CSS fixes
- Cross-browser compatible form controls

### v1.0 - Initial Release
- Full reservation system
- Admin management panel
- Email confirmations
- Comprehensive logging
- Basic branch management

## Credits

**Developed by**: Duong
**Inspired by**: Terra Viva Pizza Reservation System
**For**: WooPizza Restaurant
**Integration**: WP Store Locator plugin
**Browser Compatibility**: Chrome, Safari, Firefox, Edge
**Version**: 1.3
**Last Updated**: November 22, 2025
