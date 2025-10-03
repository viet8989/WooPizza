<?php
add_action('admin_menu', 'cbp_admin_menu');
function cbp_admin_menu()
{
    add_menu_page('Booking Orders', 'Booking Orders', 'manage_options', 'cbp_orders', 'cbp_orders_page');
    add_submenu_page('cbp_orders', 'Branches', 'Branches', 'manage_options', 'cbp_branches', 'cbp_branches_page');
}

function cbp_orders_page()
{
    global $wpdb;
    $table = $wpdb->prefix . 'cbp_orders';
    $branches_table = $wpdb->prefix . 'cbp_branches';
    $orders = $wpdb->get_results("SELECT o.*, b.name AS branch_name FROM $table o LEFT JOIN $branches_table b ON o.branch_id = b.id ORDER BY o.created_at DESC");

    echo "<h2>Booking Orders</h2>";
    echo "<table class='widefat'>";
    echo "<thead><tr>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Message</th>
            <th>Date</th>
            <th>Time Slot</th>
            <th>Branch</th>
            <th>Reservation Type</th>
            <th>Party Size</th>
          </tr></thead><tbody>";

    foreach ($orders as $o) {
        echo "<tr>
            <td>{$o->customer_name}</td>
            <td>{$o->customer_email}</td>
            <td>{$o->customer_phone}</td>
            <td>{$o->customer_message}</td>
            <td>{$o->date}</td>
            <td>{$o->time_slot}</td>
            <td>{$o->branch_name}</td>
            <td>{$o->reservation_type}</td>
            <td>{$o->party_size}</td>
        </tr>";
    }

    echo "</tbody></table>";
}

function cbp_branches_page()
{
    global $wpdb;
    $table = $wpdb->prefix . 'cbp_branches';

    // Handle ADD
    if (!empty($_POST['new_branch_name']) && !empty($_POST['new_branch_address'])) {
        $name = sanitize_text_field($_POST['new_branch_name']);
        $address = sanitize_text_field($_POST['new_branch_address']);
        $wpdb->insert($table, ['name' => $name, 'address' => $address]);
        echo "<div class='updated'><p>Branch added successfully.</p></div>";
    }

    // Handle DELETE
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $wpdb->delete($table, ['id' => $id]);
        echo "<div class='updated'><p>Branch deleted.</p></div>";
    }

    // Handle UPDATE
    if (!empty($_POST['edit_id']) && !empty($_POST['edit_name']) && !empty($_POST['edit_address'])) {
        $id = intval($_POST['edit_id']);
        $name = sanitize_text_field($_POST['edit_name']);
        $address = sanitize_text_field($_POST['edit_address']);
        $wpdb->update($table, ['name' => $name, 'address' => $address], ['id' => $id]);
        echo "<div class='updated'><p>Branch updated.</p></div>";
    }

    // Get data
    $branches = $wpdb->get_results("SELECT * FROM $table");

    echo "<h2>Manage Branches</h2>";

    // Add form
    echo "<form method='post' style='margin-bottom: 20px;'>
            <input type='text' name='new_branch_name' placeholder='Branch name' required style='margin-right:10px;'>
            <input type='text' name='new_branch_address' placeholder='Branch address' required style='margin-right:10px;'>
            <button type='submit' class='button button-primary'>Add Branch</button>
          </form>";

    echo "<table class='widefat'>
            <thead><tr><th>#</th><th>Branch Name</th><th>Address</th><th>Actions</th></tr></thead>
            <tbody>";
    $i = 1;
    foreach ($branches as $b) {
        echo "<tr><td>{$i}</td><td>";

        if (isset($_GET['edit']) && $_GET['edit'] == $b->id) {
            echo "<form method='post'>
                    <input type='hidden' name='edit_id' value='{$b->id}'>
                    <input type='text' name='edit_name' value='" . esc_attr($b->name) . "' required>
                  </td><td>
                    <input type='text' name='edit_address' value='" . esc_attr($b->address) . "' required>
                  </td><td>
                    <button type='submit' class='button'>Save</button>
                    <a href='?page=cbp_branches' class='button'>Cancel</a>
                  </form>";
        } else {
            echo esc_html($b->name) . "</td>
                  <td>" . esc_html($b->address) . "</td>
                  <td>
                    <a href='?page=cbp_branches&edit={$b->id}' class='button'>Edit</a>
                    <a href='?page=cbp_branches&delete={$b->id}' class='button' onclick=\"return confirm('Delete this branch?')\">Delete</a>
                  </td>";
        }

        echo "</tr>";
        $i++;
    }

    echo "</tbody></table>";
}
