<?php
defined('ABSPATH') or exit;

// Add admin menu
add_action('admin_menu', 'crp_admin_menu');
function crp_admin_menu()
{
    add_menu_page(
        'Reservations',
        'Reservations',
        'manage_options',
        'crp_reservations',
        'crp_reservations_page',
        'dashicons-calendar-alt',
        26
    );

    add_submenu_page(
        'crp_reservations',
        'All Reservations',
        'All Reservations',
        'manage_options',
        'crp_reservations',
        'crp_reservations_page'
    );

    // Branches submenu removed - now using WP Store Locator plugin
}

// Reservations listing page
function crp_reservations_page()
{
    global $wpdb;
    $table = $wpdb->prefix . 'reservations';

    // Handle edit update
    if (isset($_POST['edit_reservation']) && isset($_POST['reservation_id'])) {
        $reservation_id = intval($_POST['reservation_id']);
        $current_user_id = get_current_user_id();

        $update_data = [
            'customer_name' => sanitize_text_field($_POST['customer_name']),
            'customer_email' => sanitize_email($_POST['customer_email']),
            'customer_phone' => sanitize_text_field($_POST['customer_phone']),
            'customer_message' => sanitize_textarea_field($_POST['customer_message']),
            'reservation_date' => sanitize_text_field($_POST['reservation_date']),
            'start_time' => sanitize_text_field($_POST['start_time']),
            'end_time' => sanitize_text_field($_POST['end_time']),
            'party_size' => sanitize_text_field($_POST['party_size']),
            'reservation_type' => sanitize_text_field($_POST['reservation_type']),
            'status' => sanitize_text_field($_POST['status']),
            'admin_notes' => sanitize_textarea_field($_POST['admin_notes']),
            'updated_by' => $current_user_id
        ];

        // Update time_slot
        $update_data['time_slot'] = $update_data['start_time'] . ' - ' . $update_data['end_time'];

        $wpdb->update($table, $update_data, ['id' => $reservation_id]);
        echo "<div class='notice notice-success'><p>Reservation #$reservation_id updated successfully.</p></div>";
    }

    // Handle status update
    if (isset($_GET['update_status']) && isset($_GET['reservation_id']) && isset($_GET['new_status'])) {
        $reservation_id = intval($_GET['reservation_id']);
        $new_status = sanitize_text_field($_GET['new_status']);
        $current_user_id = get_current_user_id();

        $wpdb->update($table, [
            'status' => $new_status,
            'updated_by' => $current_user_id
        ], ['id' => $reservation_id]);
        echo "<div class='notice notice-success'><p>Reservation status updated to: $new_status</p></div>";
    }

    // Handle delete
    if (isset($_GET['delete_reservation']) && is_numeric($_GET['delete_reservation'])) {
        $id = intval($_GET['delete_reservation']);
        $wpdb->delete($table, ['id' => $id]);
        echo "<div class='notice notice-success'><p>Reservation deleted successfully.</p></div>";
    }

    // Filter by status
    $status_filter = isset($_GET['status_filter']) ? sanitize_text_field($_GET['status_filter']) : 'all';

    // Get reservations
    $where_clause = "";
    if ($status_filter != 'all') {
        $where_clause = $wpdb->prepare(" WHERE status = %s", $status_filter);
    }

    $reservations = $wpdb->get_results("
        SELECT *
        FROM $table
        $where_clause
        ORDER BY reservation_date DESC, start_time DESC
    ");

    // Enrich with store data from WP Store Locator
    foreach ($reservations as $reservation) {
        $store = get_post($reservation->store_id);
        $reservation->store_name = $store ? $store->post_title : 'Unknown Store';
        $reservation->store_address = get_post_meta($reservation->store_id, 'wpsl_address', true);
        $store_city = get_post_meta($reservation->store_id, 'wpsl_city', true);
        $reservation->store_full_address = $reservation->store_address . ($store_city ? ', ' . $store_city : '');
    }

    // Count by status
    $total_count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
    $confirmed_count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'confirmed'");
    $pending_count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'pending'");
    $cancelled_count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'cancelled'");
    $completed_count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'completed'");

    ?>
    <div class="wrap">
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

        <!-- Status filters -->
        <ul class="subsubsub">
            <li><a href="?page=crp_reservations&status_filter=all" class="<?= $status_filter == 'all' ? 'current' : '' ?>">All <span class="count">(<?= $total_count ?>)</span></a> |</li>
            <li><a href="?page=crp_reservations&status_filter=confirmed" class="<?= $status_filter == 'confirmed' ? 'current' : '' ?>">Confirmed <span class="count">(<?= $confirmed_count ?>)</span></a> |</li>
            <li><a href="?page=crp_reservations&status_filter=pending" class="<?= $status_filter == 'pending' ? 'current' : '' ?>">Pending <span class="count">(<?= $pending_count ?>)</span></a> |</li>
            <li><a href="?page=crp_reservations&status_filter=completed" class="<?= $status_filter == 'completed' ? 'current' : '' ?>">Completed <span class="count">(<?= $completed_count ?>)</span></a> |</li>
            <li><a href="?page=crp_reservations&status_filter=cancelled" class="<?= $status_filter == 'cancelled' ? 'current' : '' ?>">Cancelled <span class="count">(<?= $cancelled_count ?>)</span></a></li>
        </ul>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="12%">Customer Name</th>
                    <th width="15%">Contact</th>
                    <th width="10%">Date</th>
                    <th width="10%">Time</th>
                    <th width="8%">Party Size</th>
                    <th width="15%">Branch</th>
                    <th width="10%">Type</th>
                    <th width="8%">Status</th>
                    <th width="15%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reservations)): ?>
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 20px;">No reservations found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reservations as $r): ?>
                        <tr>
                            <td><?= $r->id ?></td>
                            <td><strong><?= esc_html($r->customer_name) ?></strong></td>
                            <td>
                                <?= esc_html($r->customer_email) ?><br>
                                <small><?= esc_html($r->customer_phone) ?></small>
                            </td>
                            <td><?= date('d/m/Y', strtotime($r->reservation_date)) ?></td>
                            <td><?= esc_html($r->time_slot) ?></td>
                            <td style="text-align: center;"><?= esc_html($r->party_size) ?></td>
                            <td>
                                <strong><?= esc_html($r->store_name) ?></strong><br>
                                <small style="color: #666;"><?= esc_html($r->store_full_address) ?></small>
                            </td>
                            <td><?= esc_html($r->reservation_type) ?></td>
                            <td>
                                <?php
                                $status_colors = [
                                    'confirmed' => '#00a32a',
                                    'pending' => '#dba617',
                                    'cancelled' => '#d63638',
                                    'completed' => '#2271b1'
                                ];
                                $color = isset($status_colors[$r->status]) ? $status_colors[$r->status] : '#666';
                                ?>
                                <span style="background: <?= $color ?>; color: white; padding: 4px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                    <?= esc_html($r->status) ?>
                                </span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <span class="view">
                                        <a href="#" onclick="showReservationDetails(<?= $r->id ?>); return false;">View</a> |
                                    </span>
                                    <span class="edit">
                                        <a href="#" onclick="showEditReservation(<?= $r->id ?>); return false;">Edit</a> |
                                    </span>
                                    <?php if ($r->status != 'confirmed'): ?>
                                        <span class="confirm">
                                            <a href="?page=crp_reservations&update_status=1&reservation_id=<?= $r->id ?>&new_status=confirmed">Confirm</a> |
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($r->status != 'completed'): ?>
                                        <span class="complete">
                                            <a href="?page=crp_reservations&update_status=1&reservation_id=<?= $r->id ?>&new_status=completed">Complete</a> |
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($r->status != 'cancelled'): ?>
                                        <span class="cancel">
                                            <a href="?page=crp_reservations&update_status=1&reservation_id=<?= $r->id ?>&new_status=cancelled" style="color: #d63638;">Cancel</a> |
                                        </span>
                                    <?php endif; ?>
                                    <span class="delete">
                                        <a href="?page=crp_reservations&delete_reservation=<?= $r->id ?>" style="color: #d63638;" onclick="return confirm('Are you sure you want to delete this reservation?')">Delete</a>
                                    </span>
                                </div>

                                <!-- Hidden details for modal -->
                                <div id="reservation-details-<?= $r->id ?>" style="display: none;">
                                    <h3>Reservation Details #<?= $r->id ?></h3>
                                    <table class="form-table">
                                        <tr>
                                            <th>Customer Name:</th>
                                            <td><?= esc_html($r->customer_name) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td><?= esc_html($r->customer_email) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Phone:</th>
                                            <td><?= esc_html($r->customer_phone) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Reservation Date:</th>
                                            <td><?= date('l, F j, Y', strtotime($r->reservation_date)) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Time:</th>
                                            <td><?= esc_html($r->time_slot) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Party Size:</th>
                                            <td><?= esc_html($r->party_size) ?> guests</td>
                                        </tr>
                                        <tr>
                                            <th>Store/Branch:</th>
                                            <td>
                                                <?= esc_html($r->store_name) ?><br>
                                                <small><?= esc_html($r->store_full_address) ?></small><br>
                                                <small style="color: #666;">Store ID: <?= $r->store_id ?></small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Reservation Type:</th>
                                            <td><?= esc_html($r->reservation_type) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td><strong style="color: <?= $color ?>;"><?= strtoupper($r->status) ?></strong></td>
                                        </tr>
                                        <?php if ($r->customer_message): ?>
                                        <tr>
                                            <th>Special Requests:</th>
                                            <td><?= nl2br(esc_html($r->customer_message)) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($r->admin_notes): ?>
                                        <tr>
                                            <th>Admin Notes:</th>
                                            <td style="background: #fff3cd; padding: 10px; border-radius: 4px;"><?= nl2br(esc_html($r->admin_notes)) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <th>Created At:</th>
                                            <td><?= date('Y-m-d H:i:s', strtotime($r->created_at)) ?></td>
                                        </tr>
                                        <?php if (isset($r->updated_at) && $r->updated_at != $r->created_at): ?>
                                        <tr>
                                            <th>Last Updated:</th>
                                            <td>
                                                <strong style="color: #d63638;"><?= date('Y-m-d H:i:s', strtotime($r->updated_at)) ?></strong>
                                                <?php if (isset($r->updated_by) && $r->updated_by > 0): ?>
                                                    <?php $updated_user = get_userdata($r->updated_by); ?>
                                                    <?php if ($updated_user): ?>
                                                        <br>
                                                        <small style="color: #666;">
                                                            by <strong><?= esc_html($updated_user->display_name) ?></strong>
                                                            (<?= esc_html($updated_user->user_login) ?>)
                                                        </small>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>

                                <!-- Hidden edit form for modal -->
                                <div id="reservation-edit-<?= $r->id ?>" style="display: none;">
                                    <h3>Edit Reservation #<?= $r->id ?></h3>
                                    <form method="post" action="">
                                        <input type="hidden" name="edit_reservation" value="1">
                                        <input type="hidden" name="reservation_id" value="<?= $r->id ?>">

                                        <table class="form-table">
                                            <tr>
                                                <th><label for="edit_customer_name_<?= $r->id ?>">Customer Name *</label></th>
                                                <td><input type="text" id="edit_customer_name_<?= $r->id ?>" name="customer_name" value="<?= esc_attr($r->customer_name) ?>" class="regular-text" required></td>
                                            </tr>
                                            <tr>
                                                <th><label for="edit_customer_email_<?= $r->id ?>">Email *</label></th>
                                                <td><input type="email" id="edit_customer_email_<?= $r->id ?>" name="customer_email" value="<?= esc_attr($r->customer_email) ?>" class="regular-text" required></td>
                                            </tr>
                                            <tr>
                                                <th><label for="edit_customer_phone_<?= $r->id ?>">Phone *</label></th>
                                                <td><input type="text" id="edit_customer_phone_<?= $r->id ?>" name="customer_phone" value="<?= esc_attr($r->customer_phone) ?>" class="regular-text" required></td>
                                            </tr>
                                            <tr>
                                                <th><label for="edit_reservation_date_<?= $r->id ?>">Reservation Date *</label></th>
                                                <td><input type="date" id="edit_reservation_date_<?= $r->id ?>" name="reservation_date" value="<?= esc_attr($r->reservation_date) ?>" required></td>
                                            </tr>
                                            <tr>
                                                <th><label for="edit_start_time_<?= $r->id ?>">Start Time *</label></th>
                                                <td><input type="time" id="edit_start_time_<?= $r->id ?>" name="start_time" value="<?= esc_attr($r->start_time) ?>" required></td>
                                            </tr>
                                            <tr>
                                                <th><label for="edit_end_time_<?= $r->id ?>">End Time *</label></th>
                                                <td><input type="time" id="edit_end_time_<?= $r->id ?>" name="end_time" value="<?= esc_attr($r->end_time) ?>" required></td>
                                            </tr>
                                            <tr>
                                                <th><label for="edit_party_size_<?= $r->id ?>">Party Size *</label></th>
                                                <td><input type="number" id="edit_party_size_<?= $r->id ?>" name="party_size" value="<?= esc_attr($r->party_size) ?>" min="1" max="20" required></td>
                                            </tr>
                                            <tr>
                                                <th><label for="edit_reservation_type_<?= $r->id ?>">Reservation Type *</label></th>
                                                <td>
                                                    <select id="edit_reservation_type_<?= $r->id ?>" name="reservation_type" required>
                                                        <option value="Standard Reservation" <?= $r->reservation_type == 'Standard Reservation' ? 'selected' : '' ?>>Standard Reservation</option>
                                                        <option value="VIP Reservation" <?= $r->reservation_type == 'VIP Reservation' ? 'selected' : '' ?>>VIP Reservation</option>
                                                        <option value="Catering Room" <?= $r->reservation_type == 'Catering Room' ? 'selected' : '' ?>>Catering Room</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><label for="edit_status_<?= $r->id ?>">Status *</label></th>
                                                <td>
                                                    <select id="edit_status_<?= $r->id ?>" name="status" required>
                                                        <option value="pending" <?= $r->status == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                        <option value="confirmed" <?= $r->status == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                        <option value="completed" <?= $r->status == 'completed' ? 'selected' : '' ?>>Completed</option>
                                                        <option value="cancelled" <?= $r->status == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><label for="edit_customer_message_<?= $r->id ?>">Special Requests</label></th>
                                                <td><textarea id="edit_customer_message_<?= $r->id ?>" name="customer_message" rows="3" class="large-text"><?= esc_textarea($r->customer_message) ?></textarea></td>
                                            </tr>
                                            <tr>
                                                <th><label for="edit_admin_notes_<?= $r->id ?>">Admin Notes</label></th>
                                                <td>
                                                    <textarea id="edit_admin_notes_<?= $r->id ?>" name="admin_notes" rows="4" class="large-text" placeholder="Internal notes for staff only..."><?= esc_textarea($r->admin_notes) ?></textarea>
                                                    <p class="description">These notes are only visible to administrators and will not be shown to customers.</p>
                                                </td>
                                            </tr>
                                        </table>

                                        <p class="submit">
                                            <button type="submit" class="button button-primary">Save Changes</button>
                                            <button type="button" class="button" onclick="this.closest('div').parentElement.remove()">Cancel</button>
                                        </p>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        function showReservationDetails(id) {
            var content = document.getElementById('reservation-details-' + id).innerHTML;
            console.log('Showing reservation details for ID:', id);

            // Create modal
            var modal = document.createElement('div');
            modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100000; display: flex; align-items: center; justify-content: center;';

            var modalContent = document.createElement('div');
            modalContent.style.cssText = 'background: white; padding: 30px; border-radius: 8px; max-width: 600px; max-height: 80vh; overflow-y: auto; position: relative;';
            modalContent.innerHTML = content + '<br><button onclick="this.closest(\'div\').parentElement.remove()" style="margin-top: 20px;" class="button button-primary">Close</button>';

            modal.appendChild(modalContent);
            document.body.appendChild(modal);

            modal.onclick = function(e) {
                if (e.target === modal) {
                    modal.remove();
                }
            };
        }

        function showEditReservation(id) {
            var content = document.getElementById('reservation-edit-' + id).innerHTML;
            console.log('Showing edit form for reservation ID:', id);

            // Create modal
            var modal = document.createElement('div');
            modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100000; display: flex; align-items: center; justify-content: center; overflow-y: auto; padding: 20px;';

            var modalContent = document.createElement('div');
            modalContent.style.cssText = 'background: white; padding: 30px; border-radius: 8px; max-width: 700px; width: 100%; max-height: 90vh; overflow-y: auto; position: relative; margin: auto;';
            modalContent.innerHTML = content;

            modal.appendChild(modalContent);
            document.body.appendChild(modal);

            modal.onclick = function(e) {
                if (e.target === modal) {
                    modal.remove();
                }
            };
        }
    </script>

    <style>
        .wp-list-table th {
            font-weight: 600;
        }
        .row-actions {
            font-size: 13px;
        }
        .subsubsub {
            margin-top: 15px;
        }
    </style>
    <?php
}

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
    global $wpdb;
    $table = $wpdb->prefix . 'reservation_branches';

    // Handle ADD
    if (!empty($_POST['new_branch_name']) && !empty($_POST['new_branch_address'])) {
        $name = sanitize_text_field($_POST['new_branch_name']);
        $address = sanitize_text_field($_POST['new_branch_address']);
        $wpdb->insert($table, ['name' => $name, 'address' => $address]);
        echo "<div class='notice notice-success'><p>Branch added successfully.</p></div>";
        echo '<script>console.log("Branch added: ' . addslashes($name) . '");</script>';
    }

    // Handle DELETE
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $wpdb->delete($table, ['id' => $id]);
        echo "<div class='notice notice-success'><p>Branch deleted successfully.</p></div>";
        echo '<script>console.log("Branch deleted, ID: ' . $id . '");</script>';
    }

    // Handle UPDATE
    if (!empty($_POST['edit_id']) && !empty($_POST['edit_name']) && !empty($_POST['edit_address'])) {
        $id = intval($_POST['edit_id']);
        $name = sanitize_text_field($_POST['edit_name']);
        $address = sanitize_text_field($_POST['edit_address']);
        $wpdb->update($table, ['name' => $name, 'address' => $address], ['id' => $id]);
        echo "<div class='notice notice-success'><p>Branch updated successfully.</p></div>";
        echo '<script>console.log("Branch updated, ID: ' . $id . '");</script>';
    }

    // Get all branches
    $branches = $wpdb->get_results("SELECT * FROM $table ORDER BY id ASC");

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Manage Branches</h1>
        <a href="<?= admin_url('admin.php?page=crp_reservations') ?>" class="page-title-action">View Reservations</a>
        <hr class="wp-header-end">

        <!-- Add new branch form -->
        <div class="card" style="max-width: 600px; margin-top: 20px;">
            <h2>Add New Branch</h2>
            <form method="post">
                <table class="form-table">
                    <tr>
                        <th><label for="new_branch_name">Branch Name *</label></th>
                        <td>
                            <input type="text" id="new_branch_name" name="new_branch_name" class="regular-text" required placeholder="e.g., WooPizza Quận 1">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="new_branch_address">Address *</label></th>
                        <td>
                            <input type="text" id="new_branch_address" name="new_branch_address" class="regular-text" required placeholder="e.g., 123 Nguyễn Huệ, Quận 1, TP.HCM">
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary">Add Branch</button>
                </p>
            </form>
        </div>

        <!-- Branches list -->
        <h2 style="margin-top: 40px;">Existing Branches</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th width="10%">ID</th>
                    <th width="30%">Branch Name</th>
                    <th width="45%">Address</th>
                    <th width="15%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($branches)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px;">No branches found. Add your first branch above.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($branches as $b): ?>
                        <tr>
                            <td><?= $b->id ?></td>
                            <td>
                                <?php if (isset($_GET['edit']) && $_GET['edit'] == $b->id): ?>
                                    <form method="post" style="margin: 0;">
                                        <input type="hidden" name="edit_id" value="<?= $b->id ?>">
                                        <input type="text" name="edit_name" value="<?= esc_attr($b->name) ?>" class="regular-text" required>
                                <?php else: ?>
                                    <strong><?= esc_html($b->name) ?></strong>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($_GET['edit']) && $_GET['edit'] == $b->id): ?>
                                    <input type="text" name="edit_address" value="<?= esc_attr($b->address) ?>" class="regular-text" required>
                                <?php else: ?>
                                    <?= esc_html($b->address) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($_GET['edit']) && $_GET['edit'] == $b->id): ?>
                                    <button type="submit" class="button button-primary">Save</button>
                                    <a href="?page=crp_branches" class="button">Cancel</a>
                                    </form>
                                <?php else: ?>
                                    <a href="?page=crp_branches&edit=<?= $b->id ?>" class="button">Edit</a>
                                    <a href="?page=crp_branches&delete=<?= $b->id ?>" class="button" style="color: #d63638;" onclick="return confirm('Delete this branch? This cannot be undone.')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
*/
