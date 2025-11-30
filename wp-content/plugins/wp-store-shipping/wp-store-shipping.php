<?php
/*
Plugin Name: WP Store Shipping
Description: Shipping management system for WooCommerce with ward-based shipping fees for Ho Chi Minh City
Author: Terra Viva Pizza
Version: 1.0.0
Text Domain: wp-store-shipping
Domain Path: /languages/
License: GPL v3

WP Store Shipping
Copyright (C) 2025 Terra Viva Pizza

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

@package WP_Store_Shipping
@category Core
@author Terra Viva Pizza
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPSS_VERSION', '1.0.0');
define('WPSS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPSS_PLUGIN_URL', plugin_dir_url(__FILE__));

error_log('WP Store Shipping: Plugin file loaded, WPSS_PLUGIN_DIR = ' . WPSS_PLUGIN_DIR);

// Define legacy WPSL constants for backward compatibility
if (!defined('WPSL_VERSION_NUM'))
    define('WPSL_VERSION_NUM', '1.0.0');
if (!defined('WPSL_URL'))
    define('WPSL_URL', plugin_dir_url(__FILE__));
if (!defined('WPSL_BASENAME'))
    define('WPSL_BASENAME', plugin_basename(__FILE__));
if (!defined('WPSL_PLUGIN_DIR'))
    define('WPSL_PLUGIN_DIR', plugin_dir_path(__FILE__));

error_log('WP Store Shipping: Legacy constants defined, WPSL_PLUGIN_DIR = ' . WPSL_PLUGIN_DIR);

// Include core store locator functionality
error_log('WP Store Shipping: About to include core files');

$core_files = array(
    'inc/wpsl-functions.php',
    'inc/wpsl-exclude-optimization.php',
    'inc/class-templates.php',
    'inc/class-post-types.php',
    'inc/class-i18n.php',
    'frontend/class-frontend.php'
);

foreach ($core_files as $file) {
    $file_path = WPSS_PLUGIN_DIR . $file;
    if (file_exists($file_path)) {
        error_log('WP Store Shipping: Including ' . $file . ' - EXISTS');
        require_once($file_path);
        error_log('WP Store Shipping: Successfully included ' . $file);
    } else {
        error_log('WP Store Shipping: ERROR - File does NOT exist: ' . $file_path);
    }
}

if (is_admin() || (defined('WP_CLI') && WP_CLI)) {
    error_log('WP Store Shipping: Loading admin files');

    $admin_files = array(
        'admin/roles.php',
        'admin/class-admin.php'
    );

    foreach ($admin_files as $file) {
        $file_path = WPSS_PLUGIN_DIR . $file;
        if (file_exists($file_path)) {
            error_log('WP Store Shipping: Including ' . $file . ' - EXISTS');
            require_once($file_path);
            error_log('WP Store Shipping: Successfully included ' . $file);
        } else {
            error_log('WP Store Shipping: ERROR - File does NOT exist: ' . $file_path);
        }
    }
}

// Initialize store locator
error_log('WP Store Shipping: About to initialize core class');

if (!class_exists('WP_Store_Shipping_Core')) {
    error_log('WP Store Shipping: WP_Store_Shipping_Core class does not exist, creating it');

    class WP_Store_Shipping_Core {
        var $post_types;
        var $i18n;
        var $frontend;
        var $templates;

        function __construct() {
            error_log('WP Store Shipping: WP_Store_Shipping_Core __construct() called');

            add_action('init', array($this, 'plugin_settings'));

            // Load classes
            error_log('WP Store Shipping: About to instantiate WPSL_Post_Types');
            $this->post_types = new WPSL_Post_Types();
            error_log('WP Store Shipping: WPSL_Post_Types instantiated');

            error_log('WP Store Shipping: About to instantiate WPSL_i18n');
            $this->i18n       = new WPSL_i18n();
            error_log('WP Store Shipping: WPSL_i18n instantiated');

            error_log('WP Store Shipping: About to instantiate WPSL_Frontend');
            $this->frontend   = new WPSL_Frontend();
            error_log('WP Store Shipping: WPSL_Frontend instantiated');

            error_log('WP Store Shipping: About to instantiate WPSL_Templates');
            $this->templates  = new WPSL_Templates();
            error_log('WP Store Shipping: WPSL_Templates instantiated');

            register_activation_hook(__FILE__, array($this, 'install'));

            error_log('WP Store Shipping: WP_Store_Shipping_Core fully initialized');
        }

        public function plugin_settings() {
            error_log('WP Store Shipping: plugin_settings() called');
            global $wpsl_settings, $wpsl_default_settings;
            $wpsl_settings         = wpsl_get_settings();
            $wpsl_default_settings = wpsl_get_default_settings();
            error_log('WP Store Shipping: Settings loaded');
        }

        public function install($network_wide) {
            error_log('WP Store Shipping: install() called');
            require_once(WPSS_PLUGIN_DIR . 'inc/install.php');
            wpsl_install($network_wide);
        }
    }

    $GLOBALS['wpss_core'] = new WP_Store_Shipping_Core();
    error_log('WP Store Shipping: Core class instance created in $GLOBALS[wpss_core]');

    // Also create $wpsl global for backward compatibility with WPSL functions
    $GLOBALS['wpsl'] = $GLOBALS['wpss_core'];
    error_log('WP Store Shipping: Created $GLOBALS[wpsl] for backward compatibility');
} else {
    error_log('WP Store Shipping: WP_Store_Shipping_Core class already exists, skipping');
}

// Include the shipping functions
error_log('WP Store Shipping: About to include shipping-functions.php');
if (file_exists(WPSS_PLUGIN_DIR . 'shipping-functions.php')) {
    require_once(WPSS_PLUGIN_DIR . 'shipping-functions.php');
    error_log('WP Store Shipping: shipping-functions.php successfully included');
} else {
    error_log('WP Store Shipping: ERROR - shipping-functions.php does NOT exist at: ' . WPSS_PLUGIN_DIR . 'shipping-functions.php');
}

error_log('WP Store Shipping: Plugin initialization complete');
