<?php
/*
Plugin Name: Custom Booking Plugin
Description: Booking plugin with branch selection, party size, time conflict checking, and confirmation email.
Version: 1.0
Author: Duong
*/

defined('ABSPATH') or exit;

require_once plugin_dir_path(__FILE__) . 'admin/admin-page.php';

register_activation_hook(__FILE__, 'cbp_install_plugin');
function cbp_install_plugin()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $orders_table = $wpdb->prefix . 'cbp_orders';
    $branches_table = $wpdb->prefix . 'cbp_branches';
    $reservation_type = $wpdb->prefix . 'cbp_reservations';

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta("CREATE TABLE $branches_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        address varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;");

    dbDelta("CREATE TABLE $orders_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        customer_name varchar(255) NOT NULL,
        customer_email varchar(255) NOT NULL,
        customer_phone varchar(50) NOT NULL,
        customer_message text,
        date date NOT NULL,
        time_slot varchar(50) NOT NULL,
        start_time time NOT NULL,
        end_time time NOT NULL,
        branch_id mediumint(9) NOT NULL,
        party_size varchar(10) NOT NULL,
        reservation_type varchar(50) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;");
}

add_shortcode('custom_order_form', 'cbp_render_order_form');
function cbp_render_order_form()
{
    global $wpdb;
    $branches = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}cbp_branches");
    ob_start(); ?>

    <form id="cbp-order-form">
        <div class="cbp-order-form-w">
            <div class="form-row form-row-time">
                <label>Select time</label>
                <div class="form-flex">
                    <div class="form-date">
                        <input type="date" name="order_date" required>
                    </div>
                    <div class="form-time">
                        <input type="time" name="time_slot_start" id="time_slot_start" required>
                        <input type="hidden" name="time_slot_end" id="time_slot_end">
                        <span id="time_slot_end_display" style="margin-left: 10px; font-weight: bold;"></span>
                    </div>
                </div>
            </div>

            <div class="form-row form-row-select">
                <label>Select branch</label>
                <?php foreach ($branches as $branch): ?>
                    <div>
                        <input type="radio" name="branch_id" value="<?= esc_attr($branch->id) ?>"
                            required> <strong><?= esc_html($branch->name) ?></strong> - <?= esc_html($branch->address) ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="form-row form-row-select">
                <label>Select reservation type</label>
                <div>
                    <input type="radio" name="reservation_type" value="Standard Reservation" required>
                    <strong>Standard Reservation</strong>
                </div>
                <div>
                    <input type="radio" name="reservation_type" value="Reservation Vip">
                    <strong>Reservation Vip</strong>
                </div>
                <div>
                    <input type="radio" name="reservation_type" value="Catering room">
                    <strong>Catering room</strong>
                </div>
            </div>

            <div class="form-row">
                <label>Select party size (people)</label>
                <div id="party-size-buttons">
                    <?php for ($i = 1; $i <= 7; $i++): ?>
                        <button type="button" class="party-btn <?= $i === 1 ? 'selected' : '' ?>"
                            data-value="<?= $i ?>"><?= $i ?></button>
                    <?php endfor; ?>
                    <button type="button" class="party-btn" data-value="7+">7+</button>
                </div>
                <input type="hidden" name="party_size" id="party-size-selected" value="1" required>
            </div>

            <div class="form-row">
                <label>Name</label>
                <input type="text" name="customer_name" required>
                <label>Email</label>
                <input type="email" name="customer_email">
                <label>Phone</label>
                <input type="text" name="customer_phone" required>
                <label>Message</label>
                <textarea name="customer_message" rows="3"></textarea>
            </div>

            <button type="submit">Reserve</button>
            <div id="cbp-response"></div>
        </div>
    </form>

    <script>
        document.querySelector("input[name='time_slot_start']").addEventListener("change", function() {
            const start = this.value;
            if (start) {
                const [hour, min] = start.split(":").map(Number);
                const date = new Date();
                date.setHours(hour);
                date.setMinutes(min + 30);

                const hh = String(date.getHours()).padStart(2, '0');
                const mm = String(date.getMinutes()).padStart(2, '0');
                document.querySelector("input[name='time_slot_end']").value = `${hh}:${mm}`;
            }
        });

        document.querySelectorAll(".party-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                document.querySelectorAll(".party-btn").forEach(b => b.classList.remove("selected"));
                btn.classList.add("selected");
                document.getElementById("party-size-selected").value = btn.getAttribute("data-value");
            });
        });

        document.getElementById("cbp-order-form").addEventListener("submit", function(e) {
            e.preventDefault();
            const form = e.target;
            const partySize = document.getElementById("party-size-selected").value;
            const branch = document.querySelector("input[name='branch_id']:checked");

            if (!partySize || !branch) {
                document.getElementById("cbp-response").innerText = "Please select party size and branch.";
                return;
            }

            const data = new FormData(form);
            data.set('party_size', partySize);
            data.set('branch_id', branch.value);
            data.append('action', 'cbp_handle_order');

            fetch("<?= admin_url('admin-ajax.php') ?>", {
                    method: "POST",
                    body: new URLSearchParams([...data])
                })
                .then(res => res.json())
                .then(response => {
                    document.getElementById("cbp-response").innerText = response.message;
                });
        });
    </script>

    <style>
        #cbp-order-form {
            background-color: #fff;
            margin: 0 auto;
        }

        #cbp-order-form h2 {
            font-size: 24px;
            text-align: center;
            margin-bottom: 20px;
        }

        .cbp-order-form-w {
            max-width: 420px;
            margin: 0 auto;
            font-size: 16px;
        }

        .party-btn {
            padding: 0px 16px;
            cursor: pointer;
            margin: 10px 0px;
            font-weight: 400;
            border-radius: 4px;
        }

        .party-btn.selected {
            background-color: #CD0000;
            color: white;
        }

        .form-row {
            margin-top: 20px;
        }

        .form-row label {
            font-size: 16px;
            font-weight: 500;
            color: #5E5E5E;
            display: block;
            margin-bottom: 10px;
        }

        .form-row input {
            margin-bottom: 10px;
        }

        .form-row-time input,
        .form-row-time input:focus,
        .form-row-time input:active,
        .form-row-time input:focus-visible {
            border: none;
            padding: 0;
            font-size: 18px;
        }

        .form-row input[type="time"] {
            height: 34px;
        }

        .form-row-select div {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            list-style: none;
        }

        .cbp-order-form-w button[type="submit"] {
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
            font-size: 18px;
            text-transform: capitalize;
            font-weight: 400;
            width: 100%;
            border-radius: 4px;
        }

        .form-flex {
            display: flex;
            justify-content: space-between;
        }
    </style>
<?php return ob_get_clean();
}

add_action('wp_ajax_cbp_handle_order', 'cbp_handle_order');
add_action('wp_ajax_nopriv_cbp_handle_order', 'cbp_handle_order');
function cbp_handle_order()
{
    global $wpdb;
    $table = $wpdb->prefix . 'cbp_orders';

    $name = sanitize_text_field($_POST['customer_name']);
    $email = sanitize_email($_POST['customer_email']);
    $phone = sanitize_text_field($_POST['customer_phone']);
    $message = sanitize_textarea_field($_POST['customer_message']);
    $date = sanitize_text_field($_POST['order_date']);
    $start_time = sanitize_text_field($_POST['time_slot_start']);
    $end_time = sanitize_text_field($_POST['time_slot_end']);
    $time_slot = $start_time . '-' . $end_time;
    $branch_id = intval($_POST['branch_id']);
    $party_size = sanitize_text_field($_POST['party_size']);
    $reservation_type = sanitize_text_field($_POST['reservation_type']);

    if (!$start_time || !$end_time || strtotime($start_time) === false || strtotime($end_time) === false) {
        wp_send_json(['success' => false, 'message' => 'Invalid time format.']);
    }

    // Check time conflict
    $conflict = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) FROM $table
        WHERE date = %s AND branch_id = %d AND reservation_type = %s
        AND (
            (start_time < %s AND end_time > %s)
        )
    ", $date, $branch_id, $reservation_type, $end_time, $start_time));

    if ($conflict > 0) {
        wp_send_json(['success' => false, 'message' => 'This time slot is already booked for this branch.']);
    }

    $wpdb->insert($table, [
        'customer_name' => $name,
        'customer_email' => $email,
        'customer_phone' => $phone,
        'customer_message' => $message,
        'date' => $date,
        'start_time' => $start_time,
        'end_time' => $end_time,
        'time_slot' => $time_slot,
        'branch_id' => $branch_id,
        'party_size' => $party_size,
        'reservation_type' => $reservation_type
    ]);

    if ($wpdb->last_error) {
        wp_send_json(['success' => false, 'message' => 'Database error: ' . $wpdb->last_error]);
    }

    // Get branch name
    $branch_name = $wpdb->get_var($wpdb->prepare(
        "SELECT name FROM {$wpdb->prefix}cbp_branches WHERE id = %d",
        $branch_id
    ));

    $subject = 'Booking Confirmation';
    $body = "Dear $name,\n\nYour booking has been confirmed:\nDate: $date\nTime: $time_slot\nBranch: $branch_name\nParty size: $party_size\nReservation type: $reservation_type\n\nThank you for choosing us!";

    wp_mail($email, $subject, $body);

    wp_send_json(['success' => true, 'message' => 'Booking successful! A confirmation email has been sent.']);
}
