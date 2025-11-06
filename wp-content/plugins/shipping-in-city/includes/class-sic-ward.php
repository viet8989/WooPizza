<?php
/**
 * Ward Management (Database Table)
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class SIC_Ward {

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Table name
     */
    private $table_name;

    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'sic_wards';

        // Handle AJAX requests
        add_action('wp_ajax_sic_delete_ward', array($this, 'ajax_delete_ward'));
        add_action('wp_ajax_sic_get_ward', array($this, 'ajax_get_ward'));
    }

    /**
     * Create table
     */
    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sic_wards';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            district_id bigint(20) NOT NULL,
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY slug (slug),
            KEY name (name),
            KEY district_id (district_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Get all wards
     */
    public function get_all($district_id = null, $orderby = 'name', $order = 'ASC') {
        global $wpdb;

        $orderby = in_array($orderby, array('id', 'name', 'created_at')) ? $orderby : 'name';
        $order = in_array($order, array('ASC', 'DESC')) ? $order : 'ASC';

        $query = "SELECT * FROM {$this->table_name}";

        if ($district_id) {
            $query .= $wpdb->prepare(" WHERE district_id = %d", $district_id);
        }

        $query .= " ORDER BY {$orderby} {$order}";

        return $wpdb->get_results($query);
    }

    /**
     * Get ward by ID
     */
    public function get_by_id($id) {
        global $wpdb;
        $query = "SELECT * FROM {$this->table_name} WHERE id = %d";
        return $wpdb->get_row($wpdb->prepare($query, $id));
    }

    /**
     * Get ward by slug
     */
    public function get_by_slug($slug) {
        global $wpdb;
        $query = "SELECT * FROM {$this->table_name} WHERE slug = %s";
        return $wpdb->get_row($wpdb->prepare($query, $slug));
    }

    /**
     * Add new ward
     */
    public function add($data) {
        global $wpdb;

        // Generate slug
        $slug = $this->generate_unique_slug($data['name']);

        $result = $wpdb->insert(
            $this->table_name,
            array(
                'name'        => sanitize_text_field($data['name']),
                'slug'        => $slug,
                'district_id' => absint($data['district_id']),
                'description' => isset($data['description']) ? sanitize_textarea_field($data['description']) : '',
            ),
            array('%s', '%s', '%d', '%s')
        );

        if ($result !== false) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Update ward
     */
    public function update($id, $data) {
        global $wpdb;

        $update_data = array(
            'name'        => sanitize_text_field($data['name']),
            'district_id' => absint($data['district_id']),
        );

        if (isset($data['description'])) {
            $update_data['description'] = sanitize_textarea_field($data['description']);
        }

        // Update slug if name changed
        $existing = $this->get_by_id($id);
        if ($existing && $existing->name !== $data['name']) {
            $update_data['slug'] = $this->generate_unique_slug($data['name'], $id);
        }

        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => absint($id)),
            array_fill(0, count($update_data), '%s'),
            array('%d')
        );

        return $result !== false;
    }

    /**
     * Delete ward
     */
    public function delete($id) {
        global $wpdb;

        // Check if ward is used in shipping prices
        $shipping_table = $wpdb->prefix . 'sic_shipping_prices';
        $shipping_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$shipping_table} WHERE ward_id = %d",
            $id
        ));

        if ($shipping_count > 0) {
            return array('error' => __('Cannot delete ward. It is used in shipping prices.', 'shipping-in-city'));
        }

        $result = $wpdb->delete(
            $this->table_name,
            array('id' => absint($id)),
            array('%d')
        );

        return $result !== false;
    }

    /**
     * Generate unique slug
     */
    private function generate_unique_slug($name, $exclude_id = null) {
        $slug = sanitize_title($name);
        $original_slug = $slug;
        $counter = 1;

        while ($this->slug_exists($slug, $exclude_id)) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     */
    private function slug_exists($slug, $exclude_id = null) {
        global $wpdb;

        $query = "SELECT COUNT(*) FROM {$this->table_name} WHERE slug = %s";
        $params = array($slug);

        if ($exclude_id) {
            $query .= " AND id != %d";
            $params[] = $exclude_id;
        }

        $count = $wpdb->get_var($wpdb->prepare($query, $params));
        return $count > 0;
    }

    /**
     * Get total count
     */
    public function get_count($district_id = null) {
        global $wpdb;

        $query = "SELECT COUNT(*) FROM {$this->table_name}";

        if ($district_id) {
            $query .= $wpdb->prepare(" WHERE district_id = %d", $district_id);
        }

        return $wpdb->get_var($query);
    }

    /**
     * AJAX: Delete ward
     */
    public function ajax_delete_ward() {
        check_ajax_referer('sic_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'shipping-in-city')));
        }

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;

        if ($id) {
            $result = $this->delete($id);
            if ($result === true) {
                wp_send_json_success(array('message' => __('Ward deleted successfully', 'shipping-in-city')));
            } elseif (is_array($result) && isset($result['error'])) {
                wp_send_json_error(array('message' => $result['error']));
            } else {
                wp_send_json_error(array('message' => __('Failed to delete ward', 'shipping-in-city')));
            }
        } else {
            wp_send_json_error(array('message' => __('Invalid ward ID', 'shipping-in-city')));
        }
    }

    /**
     * AJAX: Get ward
     */
    public function ajax_get_ward() {
        check_ajax_referer('sic_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'shipping-in-city')));
        }

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;

        if ($id) {
            $ward = $this->get_by_id($id);
            if ($ward) {
                wp_send_json_success($ward);
            } else {
                wp_send_json_error(array('message' => __('Ward not found', 'shipping-in-city')));
            }
        } else {
            wp_send_json_error(array('message' => __('Invalid ward ID', 'shipping-in-city')));
        }
    }
}
