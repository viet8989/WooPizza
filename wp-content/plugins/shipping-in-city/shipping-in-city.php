<?php
/**
 * Plugin Name: Shipping in City
 * Plugin URI: https://example.com/shipping-in-city
 * Description: Manage districts, wards, and shipping prices by location
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: shipping-in-city
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SIC_VERSION', '1.0.0');
define('SIC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SIC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SIC_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once SIC_PLUGIN_DIR . 'includes/class-sic-district.php';
require_once SIC_PLUGIN_DIR . 'includes/class-sic-ward.php';
require_once SIC_PLUGIN_DIR . 'includes/class-sic-shipping-price.php';
require_once SIC_PLUGIN_DIR . 'includes/class-sic-admin-menu.php';

/**
 * Main plugin class
 */
class Shipping_In_City {

    /**
     * Single instance of the class
     */
    private static $instance = null;

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
        // Activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));

        // Deactivation hook
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Initialize plugin
        add_action('plugins_loaded', array($this, 'init'));
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Initialize district and ward classes
        SIC_District::get_instance();
        SIC_Ward::get_instance();

        // Initialize shipping price functionality
        SIC_Shipping_Price::get_instance();

        // Initialize admin menu
        if (is_admin()) {
            SIC_Admin_Menu::get_instance();
        }

        // Load text domain
        load_plugin_textdomain('shipping-in-city', false, dirname(SIC_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        SIC_District::create_table();
        SIC_Ward::create_table();

        // Create shipping prices table
        global $wpdb;
        $table_name = $wpdb->prefix . 'sic_shipping_prices';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            store_id bigint(20) NOT NULL,
            district_id bigint(20) NOT NULL,
            ward_id bigint(20) NOT NULL,
            price decimal(10,2) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY store_id (store_id),
            KEY district_id (district_id),
            KEY ward_id (ward_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Nothing to do on deactivation
    }
}

// Initialize plugin
Shipping_In_City::get_instance();
