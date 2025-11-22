<?php
/*
Plugin Name: Custom Reservation Plugin
Description: Restaurant reservation system with branch selection, party size, date/time selection, and admin management.
Version: 1.0
Author: Duong
*/

defined('ABSPATH') or exit;

require_once plugin_dir_path(__FILE__) . 'admin/admin-page.php';

// Create database table on plugin activation
register_activation_hook(__FILE__, 'crp_install_plugin');
function crp_install_plugin()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $reservations_table = $wpdb->prefix . 'reservations';
    $branches_table = $wpdb->prefix . 'reservation_branches';

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // Create branches table
    dbDelta("CREATE TABLE $branches_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        address varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;");

    // Create reservations table (branch_id now stores wpsl_stores post ID)
    dbDelta("CREATE TABLE $reservations_table (
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
        PRIMARY KEY  (id)
    ) $charset_collate;");

    // Insert default branches if table is empty
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $branches_table");
    if ($count == 0) {
        $wpdb->insert($branches_table, [
            'name' => 'WooPizza Quận 1',
            'address' => '123 Nguyễn Huệ, Quận 1, TP.HCM'
        ]);
        $wpdb->insert($branches_table, [
            'name' => 'WooPizza Quận 3',
            'address' => '456 Võ Văn Tần, Quận 3, TP.HCM'
        ]);
    }
}

// Helper function to check if store is available for given datetime
function crp_is_store_available($store_id, $reservation_date, $start_time)
{
    $debug_log = "\n  === Checking Store $store_id Availability ===\n";
    $debug_log .= "    Date: $reservation_date, Time: $start_time\n";

    // Get reservation hours from postmeta
    $reservation_hours = get_post_meta($store_id, 'wpsl_reservation_hours', true);

    $debug_log .= "    Reservation hours data: " . print_r($reservation_hours, true) . "\n";

    if (empty($reservation_hours)) {
        $debug_log .= "    ✗ RESULT: No reservation hours set\n";
        file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);
        return false; // No reservation hours set
    }

    // Get day of week from reservation date
    $day_of_week = strtolower(date('l', strtotime($reservation_date))); // monday, tuesday, etc.
    $debug_log .= "    Day of week: $day_of_week\n";

    // Check if store is open on this day
    if (!isset($reservation_hours[$day_of_week]) || empty($reservation_hours[$day_of_week])) {
        $debug_log .= "    ✗ RESULT: Store closed on $day_of_week\n";
        file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);
        return false; // Store closed on this day
    }

    // Get time slots for this day
    $time_slots = $reservation_hours[$day_of_week];
    $debug_log .= "    Time slots for $day_of_week: " . print_r($time_slots, true) . "\n";

    // Parse start time to compare (convert to 24h format)
    $reservation_time = DateTime::createFromFormat('H:i', $start_time);
    $debug_log .= "    Reservation time parsed: " . ($reservation_time ? $reservation_time->format('H:i') : 'FAILED') . "\n";

    foreach ($time_slots as $slot) {
        if (empty($slot)) continue;

        $debug_log .= "    Checking slot: $slot\n";

        // Parse time range - supports both "9:00 AM,5:00 PM" and "00:00,23:45" formats
        $times = explode(',', $slot);
        if (count($times) != 2) {
            $debug_log .= "      ✗ Invalid slot format (expected 2 times, got " . count($times) . ")\n";
            continue;
        }

        $open_str = trim($times[0]);
        $close_str = trim($times[1]);

        // Try parsing as 12-hour format with AM/PM first (e.g., "9:00 AM")
        $open_time = DateTime::createFromFormat('g:i A', $open_str);
        $close_time = DateTime::createFromFormat('g:i A', $close_str);

        // If that fails, try 24-hour format (e.g., "00:00" or "23:45")
        if (!$open_time) {
            $open_time = DateTime::createFromFormat('H:i', $open_str);
        }
        if (!$close_time) {
            $close_time = DateTime::createFromFormat('H:i', $close_str);
        }

        $debug_log .= "      Open: $open_str -> " . ($open_time ? $open_time->format('H:i') : 'PARSE_FAILED') . "\n";
        $debug_log .= "      Close: $close_str -> " . ($close_time ? $close_time->format('H:i') : 'PARSE_FAILED') . "\n";

        if ($open_time && $close_time && $reservation_time) {
            // Check if reservation time is within opening hours
            if ($reservation_time >= $open_time && $reservation_time <= $close_time) {
                $debug_log .= "    ✓ RESULT: Time is within range!\n";
                file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);
                return true;
            } else {
                $debug_log .= "      ✗ Time NOT in range ({$reservation_time->format('H:i')} not between {$open_time->format('H:i')} and {$close_time->format('H:i')})\n";
            }
        } else {
            $debug_log .= "      ✗ Time parsing failed\n";
        }
    }

    $debug_log .= "    ✗ RESULT: No matching time slot found\n";
    file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);
    return false;
}

// AJAX handler to get available stores based on date/time
add_action('wp_ajax_crp_get_available_stores', 'crp_get_available_stores');
add_action('wp_ajax_nopriv_crp_get_available_stores', 'crp_get_available_stores');
function crp_get_available_stores()
{
    $reservation_date = sanitize_text_field($_POST['reservation_date']);
    $start_time = sanitize_text_field($_POST['start_time']);

    // Log to file
    $debug_log = "\n=== Get Available Stores at " . date('Y-m-d H:i:s') . " ===\n";
    $debug_log .= "Date: $reservation_date, Time: $start_time\n";
    file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);

    // Get all published WP Store Locator stores
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

            $debug_log = "  ✓ Store {$store->ID}: {$store->post_title} - AVAILABLE\n";
            file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);
        } else {
            $debug_log = "  ✗ Store {$store->ID}: {$store->post_title} - NOT AVAILABLE\n";
            file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);
        }
    }

    // Log available stores to debug file
    $debug_log = "Available stores count: " . count($available_stores) . "\n";
    file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);

    wp_send_json_success($available_stores);
}

// Shortcode to render reservation form
add_shortcode('reservation_form', 'crp_render_reservation_form');
function crp_render_reservation_form()
{
    // Add logging to file
    $debug_log = "Form rendered at " . date('Y-m-d H:i:s') . "\n";
    file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);

    ob_start(); ?>

    <form id="crp-reservation-form">
        <div class="crp-reservation-form-wrapper">
            <h2>Make a Reservation</h2>

            <div class="form-row form-row-time">
                <label>Reservation Date & Time *</label>
                <div class="form-flex">
                    <div class="form-date">
                        <input type="date" name="reservation_date" id="reservation_date" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-time">
                        <select name="start_time" id="start_time" required>
                            <option value="">Select time</option>
                            <?php
                            // Generate time slots from 10:00 AM to 11:00 PM in 30-minute intervals
                            for ($hour = 10; $hour <= 23; $hour++) {
                                foreach ([0, 30] as $minute) {
                                    $time = sprintf('%02d:%02d', $hour, $minute);
                                    echo '<option value="' . $time . '">' . $time . '</option>';
                                }
                            }
                            ?>
                        </select>
                        <span id="end_time_display" style="display: none;"></span>
                        <input type="hidden" name="end_time" id="end_time">
                    </div>
                </div>
                <small style="color: #666;">Operating hours vary by store - select date & time to see available branches</small>
                <div id="datetime-warning" style="display: none; margin-top: 10px; padding: 10px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; color: #856404;">
                    <strong>⚠️ Warning:</strong> Selected date and time is in the past. Please choose a future date/time.
                </div>
            </div>

            <div class="form-row">
                <label>Party Size (Number of Guests) *</label>
                <div class="quantity buttons_added">
                    <button type="button" class="minus button is-form" style="margin-right: 0">-</button>
                    <input type="number" name="party_size" id="party-size-input" class="input-text qty text" value="4" min="1" max="20" step="1" required readonly>
                    <button type="button" class="plus button is-form">+</button>
                </div>
                <small style="color: #666; padding-top: 10px;">Minimum 1 guest, Maximum 20 guests</small>
            </div>

            <div class="form-row form-row-select" id="branch-selection-section" style="display: none;">
                <label>Select Branch *</label>
                <div id="branches-container">
                    <!-- Branches will be loaded here via AJAX -->
                </div>
                <input type="hidden" name="store_id" id="store-id-selected" required>
            </div>

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
            </div>

            <div class="form-row">
                <label>Your Name *</label>
                <input type="text" name="customer_name" required placeholder="Enter your full name">
            </div>

            <div class="form-row">
                <label>Email Address *</label>
                <input type="email" name="customer_email" required placeholder="your.email@example.com">
            </div>

            <div class="form-row">
                <label>Phone Number *</label>
                <input type="tel" name="customer_phone" required placeholder="0901234567">
            </div>

            <div class="form-row">
                <label>Special Requests / Notes</label>
                <textarea name="customer_message" rows="4" placeholder="Any special requests? (allergies, high chair, etc.)"></textarea>
            </div>

            <button type="submit" class="submit-btn">Make Reservation</button>
            <div id="crp-response"></div>

            <div class="cancellation-policy">
                <small><strong>Cancellation Policy:</strong> Please provide 48 hours' notice for cancellations. Deposits are non-refundable for late cancellations.</small>
            </div>
        </div>
    </form>

    <script>
        jQuery(document).ready(function($) {
            console.log('Reservation form loaded');

            // Safari-specific fixes for date input
            // Set min date via JavaScript (Safari doesn't like PHP-generated min attribute)
            var today = new Date();
            var todayString = today.toISOString().split('T')[0]; // Format: YYYY-MM-DD
            $('#reservation_date').attr('min', todayString);

            // Ensure date input is enabled (Safari fix)
            $('#reservation_date').prop('disabled', false).prop('readonly', false).removeAttr('disabled');

            // Ensure time select is enabled
            $('#start_time').prop('disabled', false).removeAttr('disabled');

            console.log('Date input and time select enabled (Safari-compatible)');
            console.log('Min date set to:', todayString);

            // Function to validate if selected datetime is in the past
            function validateDateTime() {
                const reservationDate = $('#reservation_date').val();
                const startTime = $('#start_time').val();

                if (!reservationDate || !startTime) {
                    $('#datetime-warning').hide();
                    return true; // Not yet selected, so not invalid
                }

                // Create Date object from selected date and time
                const selectedDateTime = new Date(reservationDate + ' ' + startTime);
                const now = new Date();

                // Check if selected datetime is in the past
                if (selectedDateTime < now) {
                    $('#datetime-warning').show();
                    $('#branch-selection-section').hide();
                    $('#store-id-selected').val('');
                    console.log('⚠️ Warning: Selected datetime is in the past');
                    return false;
                } else {
                    $('#datetime-warning').hide();
                    return true;
                }
            }

            // Function to load available stores based on date/time
            function loadAvailableStores() {
                const reservationDate = $('#reservation_date').val();
                const startTime = $('#start_time').val();

                // Only load branches when BOTH date AND time are selected
                if (!reservationDate || !startTime) {
                    console.log('Waiting for both date and time to be selected...');
                    console.log('  Date:', reservationDate || '(not selected)');
                    console.log('  Time:', startTime || '(not selected)');

                    // Hide branch selection section
                    $('#branch-selection-section').hide();
                    $('#store-id-selected').val('');
                    return;
                }

                // Validate datetime is not in the past
                if (!validateDateTime()) {
                    console.log('⚠️ Cannot load branches - datetime is in the past');
                    return;
                }

                console.log('✅ Both date and time selected! Loading available stores...');
                console.log('  Date:', reservationDate);
                console.log('  Time:', startTime);

                // Show branch selection section
                $('#branch-selection-section').slideDown(300);

                // Show loading message
                $('#branches-container').html(
                    '<div style="padding: 20px; text-align: center; background: #f0f8ff; border: 1px solid #d0e8ff; border-radius: 4px;">' +
                    '<em>⏳ Loading available branches...</em>' +
                    '</div>'
                );

                $.ajax({
                    url: '<?= admin_url('admin-ajax.php') ?>',
                    type: 'POST',
                    data: {
                        action: 'crp_get_available_stores',
                        reservation_date: reservationDate,
                        start_time: startTime
                    },
                    success: function(response) {
                        console.log('Available stores response:', response);

                        if (response.success && response.data.length > 0) {
                            // Build branches HTML
                            let branchesHtml = '';
                            response.data.forEach(function(store, index) {
                                branchesHtml += '<div class="branch-option">';
                                branchesHtml += '<input type="radio" name="store_id_radio" value="' + store.id + '" ';
                                branchesHtml += 'id="store_' + store.id + '" ';
                                branchesHtml += (index === 0 ? 'checked' : '') + '>';
                                branchesHtml += '<label for="store_' + store.id + '">';
                                branchesHtml += '<strong>' + store.name + '</strong> - ' + store.address;
                                branchesHtml += '</label>';
                                branchesHtml += '</div>';
                            });

                            $('#branches-container').html(branchesHtml);

                            // Set first store as selected
                            if (response.data.length > 0) {
                                $('#store-id-selected').val(response.data[0].id);
                            }

                            // Handle branch selection change
                            $('input[name="store_id_radio"]').on('change', function() {
                                $('#store-id-selected').val($(this).val());
                                console.log('Branch selected:', $(this).val());
                            });

                        } else {
                            // No stores available
                            const dateObj = new Date(reservationDate + ' ' + startTime);
                            const formattedDateTime = dateObj.toLocaleDateString('en-GB', {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric'
                            }) + ' ' + startTime;

                            $('#branches-container').html(
                                '<div class="no-branches-message" style="padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; color: #856404;">' +
                                '<strong>No branch can make reservation as per your given time: ' + formattedDateTime + '</strong><br>' +
                                '<small>Please try a different date or time.</small>' +
                                '</div>'
                            );
                            $('#store-id-selected').val('');

                            console.log('No branches available for selected date/time');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading stores:', error);
                        $('#branches-container').html(
                            '<div style="padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;">' +
                            'Error loading branches. Please try again.' +
                            '</div>'
                        );
                    }
                });
            }

            // Auto-calculate end time (30 minutes after start) and load stores
            $('#start_time').on('change', function() {
                const startTime = $(this).val();
                const selectedDate = $('#reservation_date').val();

                console.log('⏰ Time selected:', startTime);

                if (startTime) {
                    const [hour, min] = startTime.split(":").map(Number);
                    const date = new Date();
                    date.setHours(hour);
                    date.setMinutes(min + 30);

                    const hh = String(date.getHours()).padStart(2, '0');
                    const mm = String(date.getMinutes()).padStart(2, '0');
                    const endTime = hh + ':' + mm;

                    $('#end_time').val(endTime);
                    $('#end_time_display').text('- ' + endTime);

                    console.log('   End time calculated:', endTime);

                    // Check if date is also selected
                    if (selectedDate) {
                        console.log('📅 Date already selected:', selectedDate);
                        console.log('🚀 Loading branches now...');
                    } else {
                        console.log('⏳ Waiting for date selection to load branches...');
                    }

                    // Load available stores when time changes (only if date also selected)
                    loadAvailableStores();
                }
            });

            // Load available stores when date changes
            $('#reservation_date').on('change', function() {
                const selectedDate = $(this).val();
                const selectedTime = $('#start_time').val();

                console.log('📅 Date selected:', selectedDate);

                if (selectedTime) {
                    console.log('⏰ Time already selected:', selectedTime);
                    console.log('🚀 Loading branches now...');
                } else {
                    console.log('⏳ Waiting for time selection to load branches...');
                }

                loadAvailableStores();
            });

            // Party size increment/decrement buttons - Step 1 only
            $('.quantity .plus').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const input = $('#party-size-input');
                let currentValue = parseInt(input.val()) || 4;
                const maxValue = parseInt(input.attr('max')) || 20;

                if (currentValue < maxValue) {
                    currentValue = currentValue + 1; // Increment by exactly 1
                    input.val(currentValue);
                    console.log('Party size increased to:', currentValue);
                }

                return false;
            });

            $('.quantity .minus').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const input = $('#party-size-input');
                let currentValue = parseInt(input.val()) || 4;
                const minValue = parseInt(input.attr('min')) || 1;

                if (currentValue > minValue) {
                    currentValue = currentValue - 1; // Decrement by exactly 1
                    input.val(currentValue);
                    console.log('Party size decreased to:', currentValue);
                }

                return false;
            });

            // Readonly input - users can only use plus/minus buttons

            // Form submission
            $('#crp-reservation-form').on('submit', function(e) {
                e.preventDefault();
                console.log('Form submitted');

                // Validate datetime is not in the past
                if (!validateDateTime()) {
                    $('#crp-response').html('<div class="error-message">Cannot make reservation for past date/time. Please select a future date and time.</div>');
                    return;
                }

                const partySize = $('#party-size-input').val();
                const storeId = $('#store-id-selected').val();

                if (!partySize) {
                    $('#crp-response').html('<div class="error-message">Please select party size.</div>');
                    return;
                }

                if (!storeId) {
                    $('#crp-response').html('<div class="error-message">Please select a valid date and time when branches are available.</div>');
                    return;
                }

                const formData = $(this).serialize();
                console.log('Form data:', formData);

                $.ajax({
                    url: '<?= admin_url('admin-ajax.php') ?>',
                    type: 'POST',
                    data: formData + '&action=crp_handle_reservation',
                    beforeSend: function() {
                        $('#crp-response').html('<div class="loading-message">Processing your reservation...</div>');
                        $('.submit-btn').prop('disabled', true);
                    },
                    success: function(response) {
                        console.log('Response:', response);

                        if (response.success) {
                            $('#crp-response').html('<div class="success-message">' + response.data.message + '</div>');
                            $('#crp-reservation-form')[0].reset();
                            $('#end_time_display').text('');
                        } else {
                            $('#crp-response').html('<div class="error-message">' + response.data.message + '</div>');
                        }

                        $('.submit-btn').prop('disabled', false);
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error);
                        $('#crp-response').html('<div class="error-message">An error occurred. Please try again.</div>');
                        $('.submit-btn').prop('disabled', false);
                    }
                });
            });
        });
    </script>

    <style>
        #crp-reservation-form {
            background-color: #fff;
            margin: 0 auto;
            padding: 20px;
        }

        .crp-reservation-form-wrapper {
            max-width: 600px;
            margin: 0 auto;
            font-size: 16px;
        }

        #crp-reservation-form h2 {
            font-size: 28px;
            text-align: center;
            margin-bottom: 30px;
            color: #CD0000;
        }

        .form-row {
            margin-bottom: 20px;
        }

        .form-row label {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            display: block;
            margin-bottom: 10px;
        }

        .form-row input[type="text"],
        .form-row input[type="email"],
        .form-row input[type="tel"],
        .form-row input[type="date"],
        .form-row input[type="time"],
        .form-row select,
        .form-row textarea {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        /* Safari-specific fixes for date input */
        .form-row input[type="date"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-color: #fff;
            cursor: pointer;
        }

        /* Ensure Safari shows the calendar icon */
        .form-row input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            opacity: 1;
        }

        /* Style select dropdown (time selector) */
        .form-row select {
            cursor: pointer;
            background-color: #fff;
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M5%206l5%205%205-5%202%201-7%207-7-7%202-1z%22%20fill%3D%22%23555%22%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px;
            padding-right: 35px;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .form-row select:focus {
            outline: none;
            border-color: #CD0000;
        }

        .form-row textarea {
            resize: vertical;
        }

        .form-flex {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .form-date,
        .form-time {
            flex: 1;
        }

        .form-row-select .branch-option {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .form-row-select .branch-option:hover {
            background-color: #f9f9f9;
        }

        .form-row-select .branch-option input[type="radio"] {
            margin-right: 10px;
            cursor: pointer;
        }

        .form-row-select .branch-option label {
            cursor: pointer;
            margin: 0;
            flex: 1;
        }

        .form-row-select div:not(.branch-option) {
            margin-bottom: 12px;
        }

        .form-row-select input[type="radio"] {
            margin-right: 10px;
            cursor: pointer;
        }

        /* Party Size Quantity Selector - Using Flatsome/WooCommerce styles */
        .form-row .quantity {
            margin-bottom: 10px;
        }

        /* Custom color override for reservation form quantity buttons */
        #crp-reservation-form .quantity .button {
            border-radius: 100% !important;
            width: 36px;
            height: 36px;
            font-size: 15px;
        }

        /* Party size input - readonly (users can only use +/- buttons) */
        #party-size-input[readonly] {
            cursor: default;
            background-color: #fff;
            color: #333;
            font-weight: 600;
            text-align: center;
            pointer-events: none;
        }

        .submit-btn {
            width: 100%;
            height: 60px;
            background-color: #CD0000;
            color: #fff;
            font-size: 18px;
            font-weight: 600;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 20px;
        }

        .submit-btn:hover {
            background-color: #a00000;
        }

        .submit-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        #crp-response {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #f5c6cb;
        }

        .loading-message {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #ffeeba;
        }

        .cancellation-policy {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #CD0000;
            border-radius: 4px;
        }

        small {
            font-size: 14px;
        }

        /* Branch selection section animation */
        #branch-selection-section {
            overflow: hidden;
            transition: all 0.3s ease-in-out;
        }

        #branch-selection-section.slide-in {
            animation: slideDown 0.3s ease-out;
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

        @media (max-width: 600px) {
            .form-flex {
                flex-direction: column;
            }

            #party-size-buttons {
                justify-content: center;
            }
        }
    </style>
<?php
    return ob_get_clean();
}

// Handle AJAX reservation submission
add_action('wp_ajax_crp_handle_reservation', 'crp_handle_reservation');
add_action('wp_ajax_nopriv_crp_handle_reservation', 'crp_handle_reservation');
function crp_handle_reservation()
{
    global $wpdb;
    $table = $wpdb->prefix . 'reservations';

    // Log to file
    $debug_log = "\n\n=== Reservation Submission at " . date('Y-m-d H:i:s') . " ===\n";
    $debug_log .= "POST data: " . print_r($_POST, true) . "\n";
    file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);

    // Sanitize inputs
    $name = sanitize_text_field($_POST['customer_name']);
    $email = sanitize_email($_POST['customer_email']);
    $phone = sanitize_text_field($_POST['customer_phone']);
    $message = sanitize_textarea_field($_POST['customer_message']);
    $date = sanitize_text_field($_POST['reservation_date']);
    $start_time = sanitize_text_field($_POST['start_time']);
    $end_time = sanitize_text_field($_POST['end_time']);
    $time_slot = $start_time . ' - ' . $end_time;
    $store_id = intval($_POST['store_id']);
    $party_size = sanitize_text_field($_POST['party_size']);
    $reservation_type = sanitize_text_field($_POST['reservation_type']);

    // Validation
    if (!$start_time || !$end_time || strtotime($start_time) === false || strtotime($end_time) === false) {
        $debug_log = "ERROR: Invalid time format\n";
        file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);
        wp_send_json_error(['message' => 'Invalid time format.']);
    }

    // Check if reservation datetime is in the past
    $reservation_datetime = strtotime($date . ' ' . $start_time);
    $current_datetime = strtotime('now');

    if ($reservation_datetime < $current_datetime) {
        $debug_log = "ERROR: Cannot book past datetime - Selected: $date $start_time, Current: " . date('Y-m-d H:i') . "\n";
        file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);
        wp_send_json_error(['message' => 'Cannot make reservations for past date/time. Please select a future date and time.']);
    }

    // Verify store is available for selected date/time
    if (!crp_is_store_available($store_id, $date, $start_time)) {
        $debug_log = "ERROR: Store $store_id not available for $date $start_time\n";
        file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);
        wp_send_json_error(['message' => 'This store is not available for the selected date and time. Please choose a different time.']);
    }

    // Check for time conflicts
    $conflict = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) FROM $table
        WHERE reservation_date = %s
        AND store_id = %d
        AND reservation_type = %s
        AND status != 'cancelled'
        AND (
            (start_time < %s AND end_time > %s)
            OR (start_time < %s AND end_time > %s)
            OR (start_time >= %s AND end_time <= %s)
        )
    ", $date, $store_id, $reservation_type, $end_time, $start_time, $start_time, $end_time, $start_time, $end_time));

    $debug_log = "Conflict check result: " . $conflict . "\n";
    file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);

    if ($conflict > 0) {
        $debug_log = "ERROR: Time slot already booked\n";
        file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);
        wp_send_json_error(['message' => 'This time slot is already booked. Please choose a different time.']);
    }

    // Insert reservation
    $result = $wpdb->insert($table, [
        'customer_name' => $name,
        'customer_email' => $email,
        'customer_phone' => $phone,
        'customer_message' => $message,
        'reservation_date' => $date,
        'start_time' => $start_time,
        'end_time' => $end_time,
        'time_slot' => $time_slot,
        'store_id' => $store_id,
        'party_size' => $party_size,
        'reservation_type' => $reservation_type,
        'status' => 'confirmed'
    ]);

    if ($wpdb->last_error) {
        $debug_log = "ERROR: Database error: " . $wpdb->last_error . "\n";
        file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);
        wp_send_json_error(['message' => 'Database error: ' . $wpdb->last_error]);
    }

    // Get store details from WP Store Locator
    $store = get_post($store_id);
    $store_name = $store ? $store->post_title : 'Unknown Store';
    $store_address = get_post_meta($store_id, 'wpsl_address', true);
    $store_city = get_post_meta($store_id, 'wpsl_city', true);
    $full_address = $store_address . ($store_city ? ', ' . $store_city : '');

    // Send confirmation email
    $subject = 'Reservation Confirmation - WooPizza';
    $body = "Dear $name,\n\n";
    $body .= "Thank you for your reservation at WooPizza!\n\n";
    $body .= "Reservation Details:\n";
    $body .= "━━━━━━━━━━━━━━━━━━━━━━\n";
    $body .= "Date: $date\n";
    $body .= "Time: $time_slot\n";
    $body .= "Branch: {$store_name}\n";
    $body .= "Address: {$full_address}\n";
    $body .= "Party Size: $party_size guests\n";
    $body .= "Reservation Type: $reservation_type\n";
    if ($message) {
        $body .= "Special Requests: $message\n";
    }
    $body .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";
    $body .= "Important Reminders:\n";
    $body .= "• Please arrive on time for your reservation\n";
    $body .= "• Cancellations require 48 hours' notice\n";
    $body .= "• For parties of 10+, deposits are non-refundable\n\n";
    $body .= "We look forward to serving you!\n\n";
    $body .= "Best regards,\n";
    $body .= "WooPizza Team";

    $headers = ['Content-Type: text/plain; charset=UTF-8'];
    wp_mail($email, $subject, $body, $headers);

    $debug_log = "SUCCESS: Reservation saved. ID: " . $wpdb->insert_id . "\n";
    file_put_contents(plugin_dir_path(__FILE__) . 'debug_save.txt', $debug_log, FILE_APPEND);

    wp_send_json_success(['message' => 'Reservation successful! A confirmation email has been sent to ' . $email]);
}
