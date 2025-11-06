<?php
/**
 * Test file to check if District/Ward plugin is working
 * Access this file at: http://yoursite.com/test-district-ward.php
 */

// Load WordPress
require_once( dirname(__FILE__) . '/wp-load.php' );

echo '<h1>District/Ward Plugin Test</h1>';
echo '<hr>';

// Test 1: Check if classes exist
echo '<h2>1. Class Existence Test</h2>';
echo 'SIC_District class exists: ' . (class_exists('SIC_District') ? '<strong style="color: green;">YES ✓</strong>' : '<strong style="color: red;">NO ✗</strong>') . '<br>';
echo 'SIC_Ward class exists: ' . (class_exists('SIC_Ward') ? '<strong style="color: green;">YES ✓</strong>' : '<strong style="color: red;">NO ✗</strong>') . '<br>';
echo '<hr>';

// Test 2: Get Districts
if (class_exists('SIC_District')) {
    echo '<h2>2. Districts Test</h2>';
    $district_obj = SIC_District::get_instance();
    $districts = $district_obj->get_all();
    echo 'Total Districts: <strong>' . count($districts) . '</strong><br>';
    if (!empty($districts)) {
        echo '<ul>';
        foreach ($districts as $district) {
            echo '<li>ID: ' . $district->id . ' - Name: ' . $district->name . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p style="color: orange;">No districts found. Please add some in admin.</p>';
    }
    echo '<hr>';
}

// Test 3: Get Wards
if (class_exists('SIC_Ward')) {
    echo '<h2>3. Wards Test</h2>';
    $ward_obj = SIC_Ward::get_instance();
    $wards = $ward_obj->get_all();
    echo 'Total Wards: <strong>' . count($wards) . '</strong><br>';
    if (!empty($wards)) {
        echo '<ul>';
        foreach ($wards as $ward) {
            $district_id = $ward->district_id;
            $district_name = 'Unknown';
            if (class_exists('SIC_District')) {
                $dist_obj = SIC_District::get_instance();
                $dist = $dist_obj->get_by_id($district_id);
                $district_name = $dist ? $dist->name : 'Unknown';
            }
            echo '<li>Ward ID: ' . $ward->id . ' - Name: ' . $ward->name . ' (District: ' . $district_name . ')</li>';
        }
        echo '</ul>';
    } else {
        echo '<p style="color: orange;">No wards found. Please add some in admin.</p>';
    }
    echo '<hr>';
}

// Test 4: Database tables
echo '<h2>4. Database Tables Test</h2>';
global $wpdb;

$district_table = $wpdb->prefix . 'sic_districts';
$ward_table = $wpdb->prefix . 'sic_wards';
$shipping_table = $wpdb->prefix . 'sic_shipping_prices';

$tables = array(
    'Districts' => $district_table,
    'Wards' => $ward_table,
    'Shipping Prices' => $shipping_table,
);

foreach ($tables as $label => $table) {
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
    echo $label . ' table (' . $table . '): ' . ($exists ? '<strong style="color: green;">EXISTS ✓</strong>' : '<strong style="color: red;">NOT FOUND ✗</strong>') . '<br>';
}

echo '<hr>';
echo '<p><strong>Next Steps:</strong></p>';
echo '<ol>';
echo '<li>If plugin not activated: Go to WordPress Admin → Plugins → Activate "Shipping in City"</li>';
echo '<li>If tables don\'t exist: Deactivate and reactivate the plugin</li>';
echo '<li>Add some districts: Admin → Shipping in City → Districts</li>';
echo '<li>Add some wards: Admin → Shipping in City → Wards</li>';
echo '<li>Then test the checkout page</li>';
echo '</ol>';
?>
