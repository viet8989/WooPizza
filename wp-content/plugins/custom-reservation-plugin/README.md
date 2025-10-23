# Custom Reservation Plugin

A complete restaurant reservation system for WooPizza WordPress site, inspired by Terra Viva Pizza's reservation system.

## Features

✓ **Reservation Form** with the following fields:
- Reservation Date (with date picker)
- Time Selection (start time with auto-calculated 30-minute duration)
- Party Size Selection (1-7+ guests)
- Branch Selection (radio buttons)
- Reservation Type (Standard, VIP, Catering Room)
- Customer Name, Email, Phone
- Special Requests / Notes
- Operating Hours: 5:00 PM – 9:45 PM

✓ **Admin Management Panel**:
- View all reservations with filtering (All, Confirmed, Pending, Completed, Cancelled)
- Update reservation status
- View detailed reservation information
- Delete reservations
- Manage branches (Add, Edit, Delete)

✓ **Automatic Features**:
- Email confirmation sent to customers
- Time conflict checking (prevents double-booking)
- 30-minute default reservation duration
- Responsive design
- AJAX form submission
- Comprehensive logging for debugging

## Installation & Activation

### Step 1: Activate the Plugin

1. Go to WordPress Admin Dashboard
2. Navigate to **Plugins** → **Installed Plugins**
3. Find "**Custom Reservation Plugin**"
4. Click **Activate**

### Step 2: Create Reservation Page

1. Go to **Pages** → **Add New**
2. Title: `Reservation`
3. In the content area, add the shortcode:
   ```
   [reservation_form]
   ```
4. Click **Publish**

### Step 3: Configure Branches (Optional)

The plugin comes with 2 default branches. To manage branches:

1. Go to **Reservations** → **Branches** in the admin menu
2. Add, edit, or delete branches as needed

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
3. Click **View** to see full details
4. Update status using action links

#### Manage Branches

1. Go to **Reservations** → **Branches**
2. Add new branches using the form
3. Edit or delete existing branches

## Database Tables

The plugin creates two tables:

1. **`wp_reservations`** - Stores all reservation data
   - id, customer_name, customer_email, customer_phone
   - customer_message, reservation_date, start_time, end_time
   - time_slot, branch_id, party_size, reservation_type
   - status, created_at

2. **`wp_reservation_branches`** - Stores branch information
   - id, name, address

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

**v1.0**
- Initial release
- Full reservation system
- Admin management panel
- Email confirmations
- Comprehensive logging

## Credits

Developed by: Duong
Inspired by: Terra Viva Pizza Reservation System
For: WooPizza Restaurant
