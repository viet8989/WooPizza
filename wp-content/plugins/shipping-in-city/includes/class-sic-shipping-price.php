<?php
/**
 * Shipping Price Management
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class SIC_Shipping_Price {

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
        $this->table_name = $wpdb->prefix . 'sic_shipping_prices';

        // Handle AJAX requests
        add_action('wp_ajax_sic_delete_shipping_price', array($this, 'ajax_delete_shipping_price'));
        add_action('wp_ajax_sic_get_wards_by_district', array($this, 'ajax_get_wards_by_district'));
    }

    /**
     * Get all shipping prices
     */
    public function get_all_shipping_prices($limit = 20, $offset = 0) {
        global $wpdb;

        $query = "SELECT * FROM {$this->table_name} ORDER BY id DESC LIMIT %d OFFSET %d";
        $results = $wpdb->get_results($wpdb->prepare($query, $limit, $offset));

        $shipping_prices = array();
        foreach ($results as $row) {
            $store_name = $this->get_store_name($row->store_id);

            // Get district and ward names from our custom tables
            $district = SIC_District::get_instance()->get_by_id($row->district_id);
            $ward = SIC_Ward::get_instance()->get_by_id($row->ward_id);

            $district_name = $district ? $district->name : __('N/A', 'shipping-in-city');
            $ward_name = $ward ? $ward->name : __('N/A', 'shipping-in-city');

            $shipping_prices[] = array(
                'id'            => $row->id,
                'store_id'      => $row->store_id,
                'store_name'    => $store_name,
                'district_id'   => $row->district_id,
                'district_name' => $district_name,
                'ward_id'       => $row->ward_id,
                'ward_name'     => $ward_name,
                'price'         => $row->price,
                'created_at'    => $row->created_at,
                'updated_at'    => $row->updated_at,
            );
        }

        return $shipping_prices;
    }

    /**
     * Get total count
     */
    public function get_total_count() {
        global $wpdb;
        return $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
    }

    /**
     * Get shipping price by ID
     */
    public function get_shipping_price($id) {
        global $wpdb;

        $query = "SELECT * FROM {$this->table_name} WHERE id = %d";
        $result = $wpdb->get_row($wpdb->prepare($query, $id));

        if ($result) {
            return array(
                'id'          => $result->id,
                'store_id'    => $result->store_id,
                'district_id' => $result->district_id,
                'ward_id'     => $result->ward_id,
                'price'       => $result->price,
                'created_at'  => $result->created_at,
                'updated_at'  => $result->updated_at,
            );
        }

        return null;
    }

    /**
     * Add new shipping price
     */
    public function add_shipping_price($data) {
        global $wpdb;

        $result = $wpdb->insert(
            $this->table_name,
            array(
                'store_id'    => absint($data['store_id']),
                'district_id' => absint($data['district_id']),
                'ward_id'     => absint($data['ward_id']),
                'price'       => floatval($data['price']),
            ),
            array('%d', '%d', '%d', '%f')
        );

        return $result !== false;
    }

    /**
     * Update shipping price
     */
    public function update_shipping_price($id, $data) {
        global $wpdb;

        $result = $wpdb->update(
            $this->table_name,
            array(
                'store_id'    => absint($data['store_id']),
                'district_id' => absint($data['district_id']),
                'ward_id'     => absint($data['ward_id']),
                'price'       => floatval($data['price']),
            ),
            array('id' => absint($id)),
            array('%d', '%d', '%d', '%f'),
            array('%d')
        );

        return $result !== false;
    }

    /**
     * Delete shipping price
     */
    public function delete_shipping_price($id) {
        global $wpdb;

        $result = $wpdb->delete(
            $this->table_name,
            array('id' => absint($id)),
            array('%d')
        );

        return $result !== false;
    }

    /**
     * Get store name from Store Locator plugin
     */
    private function get_store_name($store_id) {
        // Try to get store name from Store Locator plugin
        // This assumes the Store Locator plugin uses a custom post type
        $store = get_post($store_id);

        if ($store) {
            return $store->post_title;
        }

        return __('Unknown Store', 'shipping-in-city');
    }

    /**
     * Get all stores from Store Locator plugin
     */
    public function get_all_stores() {
        // Try common Store Locator post types
        $post_types = array('store_locator', 'store', 'sl_store', 'wpsl_stores');
        $stores = array();

        foreach ($post_types as $post_type) {
            if (post_type_exists($post_type)) {
                $args = array(
                    'post_type'      => $post_type,
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                );

                $query = get_posts($args);

                foreach ($query as $store) {
                    $stores[] = array(
                        'id'   => $store->ID,
                        'name' => $store->post_title,
                    );
                }

                // If we found stores, break the loop
                if (!empty($stores)) {
                    break;
                }
            }
        }

        return $stores;
    }

    /**
     * AJAX: Delete shipping price
     */
    public function ajax_delete_shipping_price() {
        check_ajax_referer('sic_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'shipping-in-city')));
        }

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;

        if ($id && $this->delete_shipping_price($id)) {
            wp_send_json_success(array('message' => __('Shipping price deleted successfully', 'shipping-in-city')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete shipping price', 'shipping-in-city')));
        }
    }

    /**
     * AJAX: Get wards by district
     */
    public function ajax_get_wards_by_district() {
        check_ajax_referer('sic_nonce', 'nonce');

        $district_id = isset($_POST['district_id']) ? absint($_POST['district_id']) : 0;

        if ($district_id) {
            $wards = SIC_Ward::get_instance()->get_all($district_id);
            wp_send_json_success($wards);
        } else {
            wp_send_json_error(array('message' => __('Invalid district ID', 'shipping-in-city')));
        }
    }

    /**
     * Get shipping price by location
     */
    public function get_price_by_location($store_id, $district_id, $ward_id) {
        global $wpdb;

        $query = "SELECT price FROM {$this->table_name}
                  WHERE store_id = %d AND district_id = %d AND ward_id = %d
                  LIMIT 1";

        $price = $wpdb->get_var($wpdb->prepare($query, $store_id, $district_id, $ward_id));

        return $price !== null ? floatval($price) : null;
    }
}
