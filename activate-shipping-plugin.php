<?php
/**
 * Manual Plugin Activation Script
 * This script will activate the Shipping in City plugin and create database tables
 *
 * Access at: http://yoursite.com/activate-shipping-plugin.php
 *
 * IMPORTANT: Delete this file after use for security!
 */

// Load WordPress
require_once( dirname(__FILE__) . '/wp-load.php' );

// Check if user is admin
if ( ! current_user_can( 'activate_plugins' ) ) {
    die( '<h1>Error: You must be logged in as an administrator to run this script.</h1>' );
}

echo '<html><head><style>body{font-family: Arial; margin: 40px;} h1{color: #23282d;} .success{color: green;} .error{color: red;} code{background: #f1f1f1; padding: 2px 6px;}</style></head><body>';
echo '<h1>Shipping in City Plugin - Manual Activation</h1>';
echo '<hr>';

// Step 1: Check if plugin file exists
echo '<h2>Step 1: Checking Plugin Files</h2>';
$plugin_file = 'shipping-in-city/shipping-in-city.php';
$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;

if ( file_exists( $plugin_path ) ) {
    echo '<p class="success">✓ Plugin file found: <code>' . $plugin_path . '</code></p>';
} else {
    echo '<p class="error">✗ Plugin file NOT found: <code>' . $plugin_path . '</code></p>';
    echo '<p>Please ensure the plugin is uploaded correctly.</p>';
    die('</body></html>');
}

// Step 2: Activate the plugin
echo '<h2>Step 2: Activating Plugin</h2>';

$current = get_option( 'active_plugins', array() );

if ( in_array( $plugin_file, $current ) ) {
    echo '<p class="success">✓ Plugin is already active</p>';
} else {
    $current[] = $plugin_file;
    sort( $current );
    update_option( 'active_plugins', $current );
    echo '<p class="success">✓ Plugin activated successfully!</p>';
}

// Step 3: Include plugin files manually
echo '<h2>Step 3: Loading Plugin Classes</h2>';

require_once $plugin_path;

if ( class_exists( 'SIC_District' ) ) {
    echo '<p class="success">✓ SIC_District class loaded</p>';
} else {
    echo '<p class="error">✗ SIC_District class NOT loaded</p>';
}

if ( class_exists( 'SIC_Ward' ) ) {
    echo '<p class="success">✓ SIC_Ward class loaded</p>';
} else {
    echo '<p class="error">✗ SIC_Ward class NOT loaded</p>';
}

// Step 4: Create database tables using class methods
echo '<h2>Step 4: Creating Database Tables</h2>';

// Try using the class methods first
if ( class_exists( 'SIC_District' ) ) {
    echo '<p>Calling SIC_District::create_table()...</p>';
    SIC_District::create_table();
} else {
    echo '<p class="error">✗ SIC_District class not found, cannot call create_table()</p>';
}

if ( class_exists( 'SIC_Ward' ) ) {
    echo '<p>Calling SIC_Ward::create_table()...</p>';
    SIC_Ward::create_table();
} else {
    echo '<p class="error">✗ SIC_Ward class not found, cannot call create_table()</p>';
}

global $wpdb;
$charset_collate = $wpdb->get_charset_collate();

// Also create shipping prices table directly
$shipping_table = $wpdb->prefix . 'sic_shipping_prices';
$sql = "CREATE TABLE $shipping_table (
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

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
dbDelta( $sql );

echo '<p class="success">✓ Shipping Prices table creation attempted: <code>' . $shipping_table . '</code></p>';

// Step 5: Verify tables exist
echo '<h2>Step 5: Verifying Tables</h2>';

$tables = array(
    'Districts' => $districts_table,
    'Wards' => $wards_table,
    'Shipping Prices' => $shipping_table,
);

foreach ( $tables as $label => $table ) {
    $exists = $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) === $table;
    if ( $exists ) {
        echo '<p class="success">✓ ' . $label . ' table exists</p>';
    } else {
        echo '<p class="error">✗ ' . $label . ' table NOT found</p>';
    }
}

// Step 6: Final instructions
echo '<h2>✅ Activation Complete!</h2>';
echo '<hr>';
echo '<h3>Next Steps:</h3>';
echo '<ol>';
echo '<li>Go to <strong>WordPress Admin → Shipping in City</strong></li>';
echo '<li>Add some <strong>Districts</strong></li>';
echo '<li>Add some <strong>Wards</strong> (assign them to districts)</li>';
echo '<li>Test the checkout page</li>';
echo '<li><strong style="color: red;">IMPORTANT: Delete this file (activate-shipping-plugin.php) for security!</strong></li>';
echo '</ol>';

echo '<p><a href="' . admin_url( 'admin.php?page=shipping-in-city' ) . '" style="background: #2271b1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;">Go to Plugin Settings →</a></p>';
echo '<p><a href="' . site_url( 'test-district-ward.php' ) . '" style="background: #50575e; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;">Run Test Again →</a></p>';

echo '</body></html>';
?>
