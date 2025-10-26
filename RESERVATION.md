# WooPizza Reservation System - Complete Documentation

**Plugin Name:** Custom Reservation Plugin
**Version:** 1.0
**Author:** Duong
**Integration:** WP Store Locator Plugin
**Database:** wp_reservations, WP Store Locator (wpsl_stores)

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Features](#features)
3. [Architecture](#architecture)
4. [Installation & Activation](#installation--activation)
5. [How It Works](#how-it-works)
6. [Database Structure](#database-structure)
7. [Integration with WP Store Locator](#integration-with-wp-store-locator)
8. [Frontend - Reservation Form](#frontend---reservation-form)
9. [Backend - Admin Panel](#backend---admin-panel)
10. [AJAX Handlers](#ajax-handlers)
11. [Code Reference](#code-reference)
12. [Debugging](#debugging)
13. [Customization](#customization)
14. [Troubleshooting](#troubleshooting)
15. [Future Updates](#future-updates)

---

## Overview

The WooPizza Reservation System is a complete restaurant booking solution that integrates with the WP Store Locator plugin to provide dynamic, time-based store availability checking.

### Key Capabilities:
- ✅ Dynamic branch loading based on date/time availability
- ✅ Integration with WP Store Locator for store management
- ✅ Reservation hours validation per store per day
- ✅ Email confirmations
- ✅ Time conflict detection
- ✅ Admin management panel
- ✅ Comprehensive debug logging

---

## Features

### Frontend Features

#### 1. **Reservation Form** (`[reservation_form]` shortcode)
- Date selection (future dates only)
- Time selection (no hardcoded restrictions)
- Auto-calculated end time (+30 minutes)
- Party size selection (1-7+ guests)
- Dynamic branch loading (only shows available stores)
- Reservation type selection (Standard/VIP/Catering Room)
- Customer information fields
- Special requests text area
- Real-time AJAX validation

#### 2. **Dynamic Branch Loading**
- Branches hidden until date + time selected
- Smooth slide-down animation when branches appear
- Only shows stores open for selected date/time
- "No branch available" message when appropriate
- Real-time updates when date/time changes

#### 3. **User Experience**
- Progressive disclosure (fields appear when needed)
- Clear validation messages
- Responsive design (mobile-friendly)
- Loading indicators
- Success/error feedback

### Backend Features

#### 1. **Admin Management Panel**
- View all reservations
- Filter by status (All, Confirmed, Pending, Completed, Cancelled)
- Update reservation status
- View detailed reservation information (modal popup)
- Delete reservations
- Color-coded status indicators

#### 2. **Store Management**
- Integrated with WP Store Locator
- Direct link to Store Locator → Stores
- "Manage Stores" button on admin page
- Helpful tip notice with link

#### 3. **Email System**
- Automatic confirmation emails
- Includes all reservation details
- Store name and address from WP Store Locator
- Cancellation policy reminder

---

## Architecture

### Plugin Structure

```
wp-content/plugins/custom-reservation-plugin/
├── custom-reservation-plugin.php    (Main plugin file)
├── admin/
│   └── admin-page.php              (Admin interface)
├── debug_save.txt                  (Auto-generated debug log)
└── README.md                       (Documentation)
```

### Data Flow

```
User Selects Date + Time
    ↓
AJAX Request → crp_get_available_stores()
    ↓
Check each wpsl_stores post
    ↓
Get wpsl_reservation_hours meta
    ↓
Validate day of week + time range
    ↓
Return available stores (JSON)
    ↓
JavaScript displays branches
    ↓
User selects store + fills form
    ↓
AJAX Request → crp_handle_reservation()
    ↓
Validate data + check conflicts
    ↓
Insert into wp_reservations table
    ↓
Send confirmation email
    ↓
Return success message
```

---

## Installation & Activation

### Step 1: Activate Plugin

1. Go to WordPress Admin → **Plugins** → **Installed Plugins**
2. Find **"Custom Reservation Plugin"**
3. Click **Activate**
4. Database tables created automatically:
   - `wp_reservations` (stores reservation data)
   - `wp_reservation_branches` (deprecated, not used)

### Step 2: Create Reservation Page

1. Go to **Pages** → **Add New**
2. **Title:** `Reservation`
3. **Content:** Add shortcode:
   ```
   [reservation_form]
   ```
4. Click **Publish**
5. Note the page URL (e.g., `/reservation/`)

### Step 3: Configure Stores (Optional)

1. Go to **Store Locator** → **Stores**
2. Edit each store
3. Set **Reservation Hours** for each day of week
4. Save

### Step 4: Test

1. Visit reservation page
2. Select date + time
3. Verify branches appear
4. Submit test reservation
5. Check admin panel
6. Verify email received

---

## How It Works

### Reservation Flow

#### 1. **User Opens Reservation Page**

```
Page loads with shortcode: [reservation_form]
    ↓
Form renders (JavaScript initialized)
    ↓
"Select Branch" section is HIDDEN
    ↓
Console: "Reservation form loaded"
```

#### 2. **User Selects Date**

```
User picks date: 2025-11-01
    ↓
Console: "📅 Date selected: 2025-11-01"
Console: "⏳ Waiting for time selection to load branches..."
    ↓
"Select Branch" section stays HIDDEN
    ↓
No AJAX call yet
```

#### 3. **User Selects Time**

```
User picks time: 14:00
    ↓
Auto-calculate end time: 14:30
    ↓
Console: "⏰ Time selected: 14:00"
Console: "📅 Date already selected: 2025-11-01"
Console: "🚀 Loading branches now..."
    ↓
AJAX call to: crp_get_available_stores()
    ↓
"Select Branch" section slides down (300ms animation)
    ↓
Shows: "⏳ Loading available branches..."
```

#### 4. **Server Checks Store Availability**

```php
// For each wpsl_stores post:
$reservation_hours = get_post_meta($store_id, 'wpsl_reservation_hours', true);

// Example:
array(
    'monday' => array('9:00 AM,5:00 PM'),
    'tuesday' => array('9:00 AM,5:00 PM'),
    // ...
)

// Get day of week from date:
$day_of_week = 'monday'; // for 2025-11-01

// Check if store open on Monday:
if (isset($reservation_hours['monday']) && !empty($reservation_hours['monday'])) {
    // Check if 14:00 is between 9:00 AM - 5:00 PM
    if (14:00 >= 09:00 && 14:00 <= 17:00) {
        // Store is AVAILABLE ✅
    }
}
```

#### 5. **Branches Display or "No Branch" Message**

**If stores found:**
```
┌─ Select Branch ───────────────┐
│ ○ Pizza Quận 1                │
│   123 Nguyễn Huệ, Quận 1      │
│                               │
│ ○ Pizza Quận 3                │
│   456 Võ Văn Tần, Quận 3      │
└───────────────────────────────┘
```

**If no stores found:**
```
┌────────────────────────────────────────┐
│ No branch can make reservation as per  │
│ your given time: 01/11/2025 14:00     │
│ Please try a different date or time.   │
└────────────────────────────────────────┘
```

#### 6. **User Submits Reservation**

```
User fills all fields + clicks "Make Reservation"
    ↓
JavaScript validation:
- Check party size selected
- Check store selected
    ↓
AJAX call to: crp_handle_reservation()
    ↓
Server validation:
- Sanitize inputs
- Check time format
- Check date not in past
- Verify store available (crp_is_store_available)
- Check time conflicts in database
    ↓
If valid:
- Insert into wp_reservations
- Send email confirmation
- Return success message
    ↓
If invalid:
- Return error message
```

---

## Database Structure

### Table: `wp_reservations`

Stores all reservation bookings.

```sql
CREATE TABLE wp_reservations (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    customer_name varchar(255) NOT NULL,
    customer_email varchar(255) NOT NULL,
    customer_phone varchar(50) NOT NULL,
    customer_message text,
    reservation_date date NOT NULL,
    start_time time NOT NULL,
    end_time time NOT NULL,
    time_slot varchar(50) NOT NULL,
    store_id bigint(20) NOT NULL COMMENT 'WP Store Locator post ID',
    party_size varchar(10) NOT NULL,
    reservation_type varchar(50) NOT NULL,
    status varchar(20) DEFAULT 'pending' NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
    PRIMARY KEY (id)
);
```

**Key Fields:**
- `store_id`: WP Store Locator post ID (from `wp_posts` where `post_type='wpsl_stores'`)
- `status`: pending, confirmed, cancelled, completed
- `time_slot`: Display format (e.g., "14:00 - 14:30")

### Table: `wp_reservation_branches` (Deprecated)

This table exists but is **NOT USED**. The system uses WP Store Locator instead.

```sql
-- Not used - kept for reference only
CREATE TABLE wp_reservation_branches (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    address varchar(255) NOT NULL,
    PRIMARY KEY (id)
);
```

### WP Store Locator Data

**Posts Table:**
```sql
SELECT ID, post_title, post_status
FROM wp_posts
WHERE post_type = 'wpsl_stores'
AND post_status = 'publish';
```

**Meta Table (Reservation Hours):**
```sql
SELECT post_id, meta_value
FROM wp_postmeta
WHERE meta_key = 'wpsl_reservation_hours';
```

**Example `wpsl_reservation_hours` value:**
```php
// Serialized array
a:7:{
    s:6:"monday";a:1:{i:0;s:15:"9:00 AM,5:00 PM";}
    s:7:"tuesday";a:1:{i:0;s:15:"9:00 AM,5:00 PM";}
    s:9:"wednesday";a:1:{i:0;s:15:"9:00 AM,5:00 PM";}
    s:8:"thursday";a:1:{i:0;s:15:"9:00 AM,5:00 PM";}
    s:6:"friday";a:1:{i:0;s:15:"9:00 AM,5:00 PM";}
    s:8:"saturday";a:0:{}
    s:6:"sunday";a:0:{}
}

// Unserialized:
array(
    'monday' => array('9:00 AM,5:00 PM'),
    'tuesday' => array('9:00 AM,5:00 PM'),
    'wednesday' => array('9:00 AM,5:00 PM'),
    'thursday' => array('9:00 AM,5:00 PM'),
    'friday' => array('9:00 AM,5:00 PM'),
    'saturday' => array(),  // Closed
    'sunday' => array()     // Closed
)
```

---

## Integration with WP Store Locator

### Why WP Store Locator?

The reservation system integrates with WP Store Locator to:
1. **Centralize store management** (single source of truth)
2. **Use existing store data** (name, address, hours)
3. **Leverage advanced features** (maps, search, categories)
4. **Avoid duplicate data entry**

### Data Retrieved from WP Store Locator

For each store:
- **Post ID** → Saved as `store_id` in reservations
- **Post Title** → Store name (e.g., "WooPizza Quận 1")
- **wpsl_address** → Street address
- **wpsl_city** → City name
- **wpsl_reservation_hours** → Operating hours by day

### Key Functions

#### `crp_is_store_available($store_id, $reservation_date, $start_time)`

Checks if a store is available for booking.

```php
function crp_is_store_available($store_id, $reservation_date, $start_time)
{
    // Get reservation hours from postmeta
    $reservation_hours = get_post_meta($store_id, 'wpsl_reservation_hours', true);

    if (empty($reservation_hours)) {
        return false; // No hours set
    }

    // Get day of week (monday, tuesday, etc.)
    $day_of_week = strtolower(date('l', strtotime($reservation_date)));

    // Check if store is open on this day
    if (!isset($reservation_hours[$day_of_week]) || empty($reservation_hours[$day_of_week])) {
        return false; // Closed on this day
    }

    // Parse start time
    $reservation_time = DateTime::createFromFormat('H:i', $start_time);

    // Check each time slot for this day
    foreach ($reservation_hours[$day_of_week] as $slot) {
        if (empty($slot)) continue;

        // Parse time range like "9:00 AM,5:00 PM"
        $times = explode(',', $slot);
        if (count($times) != 2) continue;

        $open_time = DateTime::createFromFormat('g:i A', trim($times[0]));
        $close_time = DateTime::createFromFormat('g:i A', trim($times[1]));

        // Check if reservation time is within opening hours
        if ($reservation_time >= $open_time && $reservation_time <= $close_time) {
            return true; // Store is available!
        }
    }

    return false; // Not available
}
```

**Usage:**
```php
if (crp_is_store_available(649, '2025-11-01', '14:00')) {
    // Store 649 is open on Monday Nov 1 at 2 PM
}
```

---

## Frontend - Reservation Form

### Shortcode

**Usage:**
```
[reservation_form]
```

**Renders:**
- Complete reservation form
- JavaScript for AJAX handling
- CSS for styling and animations

### Form Fields

1. **Reservation Date & Time**
   - Date input (future dates only, `min="<?= date('Y-m-d') ?>"`)
   - Time input (no hardcoded restrictions)
   - Auto-calculated end time display

2. **Party Size**
   - Buttons for 1-7, 7+
   - Hidden input stores selected value

3. **Select Branch** (Hidden initially)
   - Appears only when date + time selected
   - Dynamically loaded via AJAX
   - Radio buttons with store name + address

4. **Reservation Type**
   - Standard Reservation
   - VIP Reservation
   - Catering Room

5. **Customer Information**
   - Name (required)
   - Email (required)
   - Phone (required)
   - Special Requests (optional)

6. **Submit Button**
   - "Make Reservation"
   - Disabled during submission
   - Shows loading state

### JavaScript Functionality

#### Auto-Calculate End Time
```javascript
$('#start_time').on('change', function() {
    const startTime = $(this).val();
    if (startTime) {
        const [hour, min] = startTime.split(":").map(Number);
        const date = new Date();
        date.setHours(hour);
        date.setMinutes(min + 30);

        const endTime = String(date.getHours()).padStart(2, '0') + ':' +
                        String(date.getMinutes()).padStart(2, '0');
        $('#end_time').val(endTime);
        $('#end_time_display').text('- ' + endTime);
    }
});
```

#### Load Available Stores
```javascript
function loadAvailableStores() {
    const reservationDate = $('#reservation_date').val();
    const startTime = $('#start_time').val();

    // Only load if BOTH date AND time selected
    if (!reservationDate || !startTime) {
        $('#branch-selection-section').hide();
        return;
    }

    // Show section with animation
    $('#branch-selection-section').slideDown(300);

    // AJAX call
    $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'crp_get_available_stores',
            reservation_date: reservationDate,
            start_time: startTime
        },
        success: function(response) {
            if (response.success && response.data.length > 0) {
                // Build branches HTML
                let html = '';
                response.data.forEach(function(store, index) {
                    html += '<div class="branch-option">';
                    html += '<input type="radio" name="store_id_radio" value="' + store.id + '" ';
                    html += 'id="store_' + store.id + '" ' + (index === 0 ? 'checked' : '') + '>';
                    html += '<label for="store_' + store.id + '">';
                    html += '<strong>' + store.name + '</strong> - ' + store.address;
                    html += '</label></div>';
                });
                $('#branches-container').html(html);
                $('#store-id-selected').val(response.data[0].id);
            } else {
                // No stores available
                $('#branches-container').html(
                    '<div class="no-branches-message">' +
                    'No branch can make reservation as per your given time: ' + formattedDateTime +
                    '</div>'
                );
            }
        }
    });
}
```

#### Form Submission
```javascript
$('#crp-reservation-form').on('submit', function(e) {
    e.preventDefault();

    const storeId = $('#store-id-selected').val();
    if (!storeId) {
        alert('Please select a valid date and time when branches are available.');
        return;
    }

    $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: $(this).serialize() + '&action=crp_handle_reservation',
        beforeSend: function() {
            $('.submit-btn').prop('disabled', true);
        },
        success: function(response) {
            if (response.success) {
                $('#crp-response').html('<div class="success-message">' +
                                       response.data.message + '</div>');
                $('#crp-reservation-form')[0].reset();
            } else {
                $('#crp-response').html('<div class="error-message">' +
                                       response.data.message + '</div>');
            }
            $('.submit-btn').prop('disabled', false);
        }
    });
});
```

### CSS Animations

```css
/* Branch section slide-down animation */
#branch-selection-section {
    overflow: hidden;
    transition: all 0.3s ease-in-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        max-height: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        max-height: 500px;
        transform: translateY(0);
    }
}
```

---

## Backend - Admin Panel

### Menu Structure

```
Reservations (dashicons-calendar-alt)
└── All Reservations
```

**Note:** "Branches" submenu was removed. Use **Store Locator → Stores** instead.

### Reservations Page

**URL:** `/wp-admin/admin.php?page=crp_reservations`

#### Features:

1. **"Manage Stores" Button**
   - Links to Store Locator → Stores
   - Quick access to store management

2. **Helpful Tip Notice**
   ```
   💡 Tip: To manage store locations and reservation hours,
   go to Store Locator → Stores in the WordPress admin menu.
   ```

3. **Status Filters**
   - All (total count)
   - Confirmed
   - Pending
   - Completed
   - Cancelled

4. **Reservations Table**
   - Customer name
   - Contact (email + phone)
   - Date
   - Time slot
   - Party size
   - Branch (from WP Store Locator)
   - Reservation type
   - Status (color-coded badge)
   - Actions (View, Confirm, Complete, Cancel, Delete)

5. **View Details Modal**
   - Shows all reservation information
   - Store name and address
   - Special requests
   - Created timestamp
   - "Close" button

#### Code Example - Display Store Info

```php
// Get reservations
$reservations = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}reservations");

// Enrich with store data from WP Store Locator
foreach ($reservations as $reservation) {
    $store = get_post($reservation->store_id);
    $reservation->store_name = $store ? $store->post_title : 'Unknown Store';
    $reservation->store_address = get_post_meta($reservation->store_id, 'wpsl_address', true);
    $store_city = get_post_meta($reservation->store_id, 'wpsl_city', true);
    $reservation->store_full_address = $reservation->store_address .
                                      ($store_city ? ', ' . $store_city : '');
}

// Display in table
echo '<td><strong>' . esc_html($reservation->store_name) . '</strong><br>';
echo '<small>' . esc_html($reservation->store_full_address) . '</small></td>';
```

---

## AJAX Handlers

### 1. `crp_get_available_stores()`

**Action:** `crp_get_available_stores`
**Access:** Logged in + Non-logged in users
**Purpose:** Get list of stores available for selected date/time

**Request:**
```javascript
{
    action: 'crp_get_available_stores',
    reservation_date: '2025-11-01',
    start_time: '14:00'
}
```

**Response (Success):**
```json
{
    "success": true,
    "data": [
        {
            "id": 649,
            "name": "WooPizza Quận 1",
            "address": "123 Nguyễn Huệ, Quận 1, TP.HCM"
        },
        {
            "id": 650,
            "name": "WooPizza Quận 3",
            "address": "456 Võ Văn Tần, Quận 3, TP.HCM"
        }
    ]
}
```

**Response (No Stores):**
```json
{
    "success": true,
    "data": []
}
```

**PHP Code:**
```php
function crp_get_available_stores()
{
    $reservation_date = sanitize_text_field($_POST['reservation_date']);
    $start_time = sanitize_text_field($_POST['start_time']);

    // Get all published stores
    $stores = get_posts([
        'post_type' => 'wpsl_stores',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ]);

    $available_stores = [];

    foreach ($stores as $store) {
        if (crp_is_store_available($store->ID, $reservation_date, $start_time)) {
            $address = get_post_meta($store->ID, 'wpsl_address', true);
            $city = get_post_meta($store->ID, 'wpsl_city', true);

            $available_stores[] = [
                'id' => $store->ID,
                'name' => $store->post_title,
                'address' => $address . ($city ? ', ' . $city : '')
            ];
        }
    }

    wp_send_json_success($available_stores);
}
```

---

### 2. `crp_handle_reservation()`

**Action:** `crp_handle_reservation`
**Access:** Logged in + Non-logged in users
**Purpose:** Process and save reservation

**Request:**
```javascript
{
    action: 'crp_handle_reservation',
    customer_name: 'John Doe',
    customer_email: 'john@example.com',
    customer_phone: '0901234567',
    customer_message: 'Window seat please',
    reservation_date: '2025-11-01',
    start_time: '14:00',
    end_time: '14:30',
    store_id: 649,
    party_size: '4',
    reservation_type: 'Standard Reservation'
}
```

**Response (Success):**
```json
{
    "success": true,
    "data": {
        "message": "Reservation successful! A confirmation email has been sent to john@example.com"
    }
}
```

**Response (Error):**
```json
{
    "success": false,
    "data": {
        "message": "This time slot is already booked. Please choose a different time."
    }
}
```

**Validation Checks:**

1. **Time format validation**
2. **Date not in past**
3. **Store availability** (via `crp_is_store_available()`)
4. **Time conflict check** (no overlapping reservations)

**Time Conflict Query:**
```sql
SELECT COUNT(*) FROM wp_reservations
WHERE reservation_date = '2025-11-01'
AND store_id = 649
AND reservation_type = 'Standard Reservation'
AND status != 'cancelled'
AND (
    (start_time < '14:30' AND end_time > '14:00')
    OR (start_time < '14:00' AND end_time > '14:30')
    OR (start_time >= '14:00' AND end_time <= '14:30')
)
```

**Email Sent:**
```
Subject: Reservation Confirmation - WooPizza
Body:
Dear John Doe,

Thank you for your reservation at WooPizza!

Reservation Details:
━━━━━━━━━━━━━━━━━━━━━━
Date: 2025-11-01
Time: 14:00 - 14:30
Branch: WooPizza Quận 1
Address: 123 Nguyễn Huệ, Quận 1, TP.HCM
Party Size: 4 guests
Reservation Type: Standard Reservation
Special Requests: Window seat please
━━━━━━━━━━━━━━━━━━━━━━

Important Reminders:
• Please arrive on time for your reservation
• Cancellations require 48 hours' notice
• For parties of 10+, deposits are non-refundable

We look forward to serving you!

Best regards,
WooPizza Team
```

---

## Code Reference

### Main Plugin File

**Location:** `wp-content/plugins/custom-reservation-plugin/custom-reservation-plugin.php`

**Key Functions:**

| Function | Purpose | Line |
|----------|---------|------|
| `crp_install_plugin()` | Create database tables on activation | 14 |
| `crp_is_store_available()` | Check if store open for date/time | 66 |
| `crp_get_available_stores()` | AJAX: Get available stores | 113 |
| `crp_render_reservation_form()` | Render reservation form shortcode | 161 |
| `crp_handle_reservation()` | AJAX: Process reservation | 704 |

### Admin Page File

**Location:** `wp-content/plugins/custom-reservation-plugin/admin/admin-page.php`

**Key Functions:**

| Function | Purpose | Line |
|----------|---------|------|
| `crp_admin_menu()` | Register admin menu | 6 |
| `crp_reservations_page()` | Display reservations table | 31 |
| `crp_branches_page()` | DEPRECATED - Not used | 287 |

---

## Debugging

### Debug Priorities (As Requested)

1. **Browser Console (F12) - HIGHEST PRIORITY** ⭐
2. **console.log in JavaScript code**
3. **PHP file logging to debug_save.txt**

### 1. Browser Console Logs (F12 → Console)

**JavaScript logs in form:**
```javascript
// Page load
Reservation form loaded

// User selects date
📅 Date selected: 2025-11-01
⏳ Waiting for time selection to load branches...

// User selects time
⏰ Time selected: 14:00
   End time calculated: 14:30
📅 Date already selected: 2025-11-01
🚀 Loading branches now...
✅ Both date and time selected! Loading available stores...

// AJAX response
Available stores response: {success: true, data: [...]}

// Branch selected
Branch selected: 649

// Form submission
Form submitted
Form data: customer_name=...&store_id=649...
```

**Custom debug code to paste in console:**
```javascript
// Monitor all AJAX calls
jQuery(document).ajaxComplete(function(event, xhr, settings) {
    if (settings.data && settings.data.includes('crp_')) {
        console.log('AJAX Complete:', settings.data);
        console.log('Response:', xhr.responseJSON);
    }
});

// Check form state
console.log('Date:', jQuery('#reservation_date').val());
console.log('Time:', jQuery('#start_time').val());
console.log('Store ID:', jQuery('#store-id-selected').val());

// Fill test data
ReservationDebug.fillTestData();  // If debug helper loaded
```

### 2. Debug File Logging

**Location:** `wp-content/plugins/custom-reservation-plugin/debug_save.txt`

**View logs:**
```bash
cat wp-content/plugins/custom-reservation-plugin/debug_save.txt

# Or tail for real-time:
tail -f wp-content/plugins/custom-reservation-plugin/debug_save.txt
```

**Example log output:**
```
Form rendered at 2025-10-23 18:30:00

=== Get Available Stores at 2025-10-23 18:31:15 ===
Date: 2025-11-01, Time: 14:00
  ✓ Store 649: WooPizza Quận 1 - AVAILABLE
  ✗ Store 650: WooPizza Quận 3 - NOT AVAILABLE
Available stores count: 1

=== Reservation Submission at 2025-10-23 18:32:45 ===
POST data: Array
(
    [customer_name] => John Doe
    [customer_email] => john@example.com
    [store_id] => 649
    ...
)
Conflict check result: 0
SUCCESS: Reservation saved. ID: 42
```

**Add custom logs:**
```php
// In any PHP function
$debug_log = "My custom debug message: " . $variable . "\n";
file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);
```

### 3. Network Tab (F12 → Network)

**Check AJAX requests:**
1. Open F12 → Network tab
2. Filter: XHR
3. Select date + time
4. Look for request to `admin-ajax.php`
5. Click on it
6. Check **Response** tab
7. Verify pure JSON (no `<script>` tags)

**Good response:**
```json
{"success":true,"data":[{"id":649,"name":"Store","address":"123 St"}]}
```

**Bad response (old code had this issue):**
```html
<script>console.log(...);</script>{"success":true,...}
```

### 4. Common Debug Scenarios

#### No branches appearing?

**Check:**
1. F12 Console → Any errors?
2. Network tab → AJAX call successful?
3. Response → `data` array empty?
4. `debug_save.txt` → Which stores checked? Why not available?
5. Store Locator → Reservation hours set for that day?

**Debug in console:**
```javascript
// Check AJAX URL
console.log(ajaxurl);

// Manually trigger AJAX
jQuery.ajax({
    url: '/wp-admin/admin-ajax.php',
    type: 'POST',
    data: {
        action: 'crp_get_available_stores',
        reservation_date: '2025-11-01',
        start_time: '14:00'
    },
    success: function(response) {
        console.log('Manual AJAX:', response);
    }
});
```

#### Form not submitting?

**Check:**
1. Console → JavaScript errors?
2. Store ID selected? `console.log($('#store-id-selected').val())`
3. Network tab → AJAX request sent?
4. Response → Error message?

#### Email not received?

**Check:**
1. Spam folder
2. `debug_save.txt` → Reservation saved?
3. PHP mail configuration
4. Install SMTP plugin

---

## Customization

### Change Brand Color

**File:** `custom-reservation-plugin.php`

**Find and replace `#CD0000` with your color:**
```css
#crp-reservation-form h2 {
    color: #YOUR_COLOR;  /* Line ~450 */
}

.party-btn.selected {
    background-color: #YOUR_COLOR;  /* Line ~545 */
}

.submit-btn {
    background-color: #YOUR_COLOR;  /* Line ~575 */
}

.cancellation-policy {
    border-left: 4px solid #YOUR_COLOR;  /* Line ~652 */
}
```

### Change Reservation Duration

**Currently:** 30 minutes

**To change:** Edit line ~374

```javascript
// Current (30 minutes):
date.setMinutes(min + 30);

// Change to 1 hour:
date.setMinutes(min + 60);

// Change to 2 hours:
date.setMinutes(min + 120);
```

### Add More Reservation Types

**File:** `custom-reservation-plugin.php`, Line ~209

```html
<div class="form-row form-row-select">
    <label>Reservation Type *</label>
    <div>
        <input type="radio" name="reservation_type" value="Standard Reservation" checked required>
        <strong>Standard Reservation</strong>
    </div>
    <div>
        <input type="radio" name="reservation_type" value="VIP Reservation">
        <strong>VIP Reservation</strong>
    </div>
    <div>
        <input type="radio" name="reservation_type" value="Catering Room">
        <strong>Catering Room</strong>
    </div>

    <!-- ADD YOUR NEW TYPE HERE -->
    <div>
        <input type="radio" name="reservation_type" value="Private Event">
        <strong>Private Event</strong>
    </div>
</div>
```

### Customize Email Template

**File:** `custom-reservation-plugin.php`, Line ~802

```php
$subject = 'Reservation Confirmation - WooPizza';
$body = "Dear $name,\n\n";
$body .= "Thank you for your reservation at WooPizza!\n\n";
$body .= "Reservation Details:\n";
$body .= "━━━━━━━━━━━━━━━━━━━━━━\n";
$body .= "Date: $date\n";
$body .= "Time: $time_slot\n";
// ... customize this section ...
```

### Add Custom Fields

1. **Add HTML field** in form (line ~220)
2. **Add JavaScript** to capture value
3. **Add sanitization** in AJAX handler (line ~715)
4. **Add database column:**
   ```sql
   ALTER TABLE wp_reservations ADD COLUMN your_field varchar(255);
   ```
5. **Update INSERT** statement (line ~772)
6. **Display in admin** table (admin-page.php)

---

## Troubleshooting

### Issue: Form shows `[reservation_form]` as text

**Cause:** Wrong shortcode or plugin not activated

**Fix:**
1. Check shortcode is `[reservation_form]` not `[custom_order_form]`
2. Verify plugin activated: Plugins → Custom Reservation Plugin → Activate

---

### Issue: JSON parsing error "Unexpected token '<'"

**Cause:** PHP echo statements in AJAX handlers (FIXED in current version)

**Fix:** Already fixed. If you see this, update to latest version.

**What was fixed:**
- Removed all `echo '<script>console.log(...);</script>';` from AJAX handlers
- Replaced with file logging

---

### Issue: No branches appear

**Possible causes:**

1. **No stores published**
   - Check: Store Locator → Stores
   - Verify: At least one store with status = "Publish"

2. **No reservation hours set**
   - Edit store in Store Locator
   - Set reservation hours for each day

3. **Store closed on selected day**
   - Example: Selected Saturday but store closed Saturdays
   - Try different day

4. **Time outside operating hours**
   - Example: Selected 3:00 AM but store opens 9:00 AM
   - Try different time

**Debug:**
```bash
# Check stores exist
SELECT ID, post_title FROM wp_posts WHERE post_type='wpsl_stores' AND post_status='publish';

# Check reservation hours
SELECT post_id, meta_value FROM wp_postmeta WHERE meta_key='wpsl_reservation_hours';

# Check debug log
cat wp-content/plugins/custom-reservation-plugin/debug_save.txt
```

---

### Issue: "This time slot is already booked"

**Cause:** Another reservation exists for same store, date, time, and type

**Check:**
```sql
SELECT * FROM wp_reservations
WHERE reservation_date = '2025-11-01'
AND store_id = 649
AND reservation_type = 'Standard Reservation'
AND status != 'cancelled';
```

**Fix options:**
1. Choose different time
2. Choose different store
3. Choose different reservation type
4. Cancel conflicting reservation (if yours)

---

### Issue: Email not received

**Possible causes:**

1. **Wrong email address** - Check spelling
2. **Spam folder** - Check spam/junk
3. **Server mail not configured** - PHP `mail()` may not work
4. **Firewall blocking** - Some hosts block outgoing mail

**Fix:**
1. Install SMTP plugin: "WP Mail SMTP" or "Easy WP SMTP"
2. Configure with your email provider
3. Test email sending
4. Check email logs

---

### Issue: Branch section hidden/not appearing

**Expected behavior:** Section should be hidden until date + time selected

**If always hidden:**
1. Check both date AND time selected
2. F12 Console → Check for JavaScript errors
3. Check AJAX response in Network tab
4. Verify `$('#branch-selection-section').slideDown(300)` called

**Force show for debugging:**
```javascript
// In console:
jQuery('#branch-selection-section').show();
```

---

## Future Updates

### Potential Enhancements

1. **Admin Notifications**
   - Email admin when new reservation made
   - Configurable email addresses
   - SMS notifications

2. **Reservation Reminders**
   - Auto-email customers 24h before reservation
   - SMS reminders
   - WhatsApp integration

3. **Capacity Management**
   - Set max reservations per time slot
   - Set max party size per store
   - Calculate table availability

4. **Recurring Reservations**
   - Weekly recurring bookings
   - Monthly recurring bookings
   - Subscription-based reservations

5. **Payment Integration**
   - Require deposit for large parties
   - Payment gateway integration (WooCommerce)
   - Refund management

6. **Calendar View**
   - Admin calendar view of all reservations
   - Drag-and-drop rescheduling
   - Print daily schedule

7. **Customer Accounts**
   - Save customer preferences
   - Reservation history
   - Quick re-booking

8. **Export/Import**
   - Export reservations to CSV
   - Import reservations from CSV
   - Integration with Google Calendar

9. **Multi-language**
   - WPML integration
   - Polylang support
   - Translation ready

10. **Advanced Reporting**
    - Reservation analytics
    - Popular times/stores
    - Revenue projections
    - Customer demographics

### How to Modify This System

**Before making changes:**
1. Backup database
2. Backup plugin files
3. Test on staging site first
4. Read this documentation
5. Check `debug_save.txt` logs

**Best practices:**
1. Use child theme/plugin for customizations
2. Comment your code
3. Update this RESERVATION.md file
4. Test thoroughly
5. Keep debug logging

**File structure for updates:**
```
custom-reservation-plugin/
├── custom-reservation-plugin.php       (Main - AJAX, form, core logic)
├── admin/
│   └── admin-page.php                 (Admin - display only)
├── assets/                            (Create if needed)
│   ├── js/
│   │   └── reservation.js            (Move JS here)
│   └── css/
│       └── reservation.css           (Move CSS here)
└── includes/                          (Create if needed)
    ├── class-store-checker.php       (Separate logic)
    ├── class-email-sender.php        (Email logic)
    └── class-conflict-checker.php    (Conflict logic)
```

---

## Quick Reference

### Important Files

| File | Purpose |
|------|---------|
| `custom-reservation-plugin.php` | Main plugin logic |
| `admin/admin-page.php` | Admin interface |
| `debug_save.txt` | Debug logs (auto-generated) |

### Important Functions

| Function | What It Does |
|----------|--------------|
| `crp_is_store_available()` | Check if store open for date/time |
| `crp_get_available_stores()` | Get stores via AJAX |
| `crp_handle_reservation()` | Process reservation via AJAX |
| `crp_render_reservation_form()` | Render form shortcode |

### Database Tables

| Table | Purpose |
|-------|---------|
| `wp_reservations` | Store reservation data |
| `wp_posts` (wpsl_stores) | Store locations (WP Store Locator) |
| `wp_postmeta` | Store hours and details |

### Key Meta Fields

| Meta Key | Description |
|----------|-------------|
| `wpsl_reservation_hours` | Operating hours by day of week |
| `wpsl_address` | Store street address |
| `wpsl_city` | Store city |

### Shortcodes

| Shortcode | Output |
|-----------|--------|
| `[reservation_form]` | Full reservation form |

### AJAX Actions

| Action | Purpose |
|--------|---------|
| `crp_get_available_stores` | Get stores for date/time |
| `crp_handle_reservation` | Submit reservation |

### Console Log Emojis

| Emoji | Meaning |
|-------|---------|
| 📅 | Date selected |
| ⏰ | Time selected |
| 🚀 | Loading branches |
| ✅ | Success/Complete |
| ⏳ | Waiting |
| ❌ | Error |

---

## Support & Documentation

### Documentation Files

1. **RESERVATION.md** (This file) - Complete system documentation
2. **RESERVATION_WPSL_INTEGRATION.md** - WP Store Locator integration details
3. **RESERVATION_HIDE_BRANCH_SECTION.md** - Branch visibility feature
4. **RESERVATION_LOAD_BRANCHES_UPDATE.md** - Branch loading logic
5. **RESERVATION_REMOVE_BRANCHES_MENU.md** - Admin menu cleanup
6. **FIX_JSON_PARSING_ERROR.md** - JSON parsing fix
7. **FIX_RESERVATION_SHORTCODE.md** - Shortcode troubleshooting
8. **REMOVE_HARDCODED_TIME_VALIDATION.md** - Time validation removal

### Where to Get Help

1. **Check this RESERVATION.md file first**
2. **Check debug_save.txt** for server-side logs
3. **Check F12 Console** for client-side logs
4. **Check Network tab** for AJAX issues
5. **Review related .md files** for specific features

### Contact

**Developer:** Duong
**Version:** 1.0
**Last Updated:** 2025-10-23

---

## Summary

The WooPizza Reservation System is a fully-featured restaurant booking solution that:

✅ **Integrates with WP Store Locator** for centralized store management
✅ **Validates reservation hours** dynamically from database
✅ **Shows only available stores** for selected date/time
✅ **Prevents double-booking** with conflict detection
✅ **Sends email confirmations** automatically
✅ **Provides admin management** with status tracking
✅ **Includes comprehensive debugging** tools
✅ **Is fully customizable** and extensible

**Status:** ✅ Production Ready

**Shortcode:** `[reservation_form]`

**Admin:** Reservations → All Reservations

**Store Management:** Store Locator → Stores

---

**End of Documentation** 🎉
