<?php
/**
 * District Management (Database Table)
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class SIC_District {

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
        $this->table_name = $wpdb->prefix . 'sic_districts';

        // Handle AJAX requests
        add_action('wp_ajax_sic_delete_district', array($this, 'ajax_delete_district'));
        add_action('wp_ajax_sic_get_district', array($this, 'ajax_get_district'));
    }

    /**
     * Create table
     */
    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sic_districts';
        $charset_collate = $wpdb->get_charset_collate();

        // Use dbDelta with proper formatting (TWO spaces after PRIMARY KEY)
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY slug (slug),
            KEY name (name)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Log the result
        error_log('SIC_District::create_table() executed. Table: ' . $table_name);

        // Verify table was created
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        error_log('SIC_District table exists after creation: ' . ($table_exists ? 'YES' : 'NO'));
    }

    /**
     * Get all districts
     */
    public function get_all($orderby = 'name', $order = 'ASC') {
        global $wpdb;

        $orderby = in_array($orderby, array('id', 'name', 'created_at')) ? $orderby : 'name';
        $order = in_array($order, array('ASC', 'DESC')) ? $order : 'ASC';

        $query = "SELECT * FROM {$this->table_name} ORDER BY {$orderby} {$order}";
        return $wpdb->get_results($query);
    }

    /**
     * Get district by ID
     */
    public function get_by_id($id) {
        global $wpdb;
        $query = "SELECT * FROM {$this->table_name} WHERE id = %d";
        return $wpdb->get_row($wpdb->prepare($query, $id));
    }

    /**
     * Get district by slug
     */
    public function get_by_slug($slug) {
        global $wpdb;
        $query = "SELECT * FROM {$this->table_name} WHERE slug = %s";
        return $wpdb->get_row($wpdb->prepare($query, $slug));
    }

    /**
     * Add new district
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
                'description' => isset($data['description']) ? sanitize_textarea_field($data['description']) : '',
            ),
            array('%s', '%s', '%s')
        );

        if ($result !== false) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Update district
     */
    public function update($id, $data) {
        global $wpdb;

        $update_data = array(
            'name' => sanitize_text_field($data['name']),
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
     * Delete district
     */
    public function delete($id) {
        global $wpdb;

        // Check if district is used in wards
        $ward_table = $wpdb->prefix . 'sic_wards';
        $ward_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$ward_table} WHERE district_id = %d",
            $id
        ));

        if ($ward_count > 0) {
            return array('error' => __('Cannot delete district. It is used by wards.', 'shipping-in-city'));
        }

        // Check if district is used in shipping prices
        $shipping_table = $wpdb->prefix . 'sic_shipping_prices';
        $shipping_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$shipping_table} WHERE district_id = %d",
            $id
        ));

        if ($shipping_count > 0) {
            return array('error' => __('Cannot delete district. It is used in shipping prices.', 'shipping-in-city'));
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
    public function get_count() {
        global $wpdb;
        return $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
    }

    /**
     * AJAX: Delete district
     */
    public function ajax_delete_district() {
        check_ajax_referer('sic_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'shipping-in-city')));
        }

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;

        if ($id) {
            $result = $this->delete($id);
            if ($result === true) {
                wp_send_json_success(array('message' => __('District deleted successfully', 'shipping-in-city')));
            } elseif (is_array($result) && isset($result['error'])) {
                wp_send_json_error(array('message' => $result['error']));
            } else {
                wp_send_json_error(array('message' => __('Failed to delete district', 'shipping-in-city')));
            }
        } else {
            wp_send_json_error(array('message' => __('Invalid district ID', 'shipping-in-city')));
        }
    }

    /**
     * AJAX: Get district
     */
    public function ajax_get_district() {
        check_ajax_referer('sic_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'shipping-in-city')));
        }

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;

        if ($id) {
            $district = $this->get_by_id($id);
            if ($district) {
                wp_send_json_success($district);
            } else {
                wp_send_json_error(array('message' => __('District not found', 'shipping-in-city')));
            }
        } else {
            wp_send_json_error(array('message' => __('Invalid district ID', 'shipping-in-city')));
        }
    }
}
