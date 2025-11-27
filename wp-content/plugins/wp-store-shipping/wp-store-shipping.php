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

// Include the shipping functions
require_once(WPSS_PLUGIN_DIR . 'shipping-functions.php');
