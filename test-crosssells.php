<?php
/**
 * Test file to debug cross-sell saving and loading
 * Access this file directly via browser to see debug info
 */

// Load WordPress
require_once __DIR__ . '/wp-load.php';

// Get a pizza product ID from URL parameter
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if (!$product_id) {
    echo '<h1>Cross-sell Debug Tool</h1>';
    echo '<p>Add ?product_id=XXX to the URL to test a specific product</p>';

    // Show all pizza products
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 10,
        'orderby' => 'ID',
        'order' => 'DESC',
    );
    $products = new WP_Query($args);

    echo '<h2>Recent Products:</h2>';
    echo '<ul>';
    while ($products->have_posts()) {
        $products->the_post();
        echo '<li><a href="?product_id=' . get_the_ID() . '">' . get_the_title() . ' (ID: ' . get_the_ID() . ')</a></li>';
    }
    echo '</ul>';
    wp_reset_postdata();
    exit;
}

// Debug specific product
echo '<h1>Cross-sell Debug for Product ID: ' . $product_id . '</h1>';

$product = wc_get_product($product_id);
if (!$product) {
    echo '<p style="color: red;">Product not found!</p>';
    exit;
}

echo '<h2>Product Info:</h2>';
echo '<p><strong>Name:</strong> ' . $product->get_name() . '</p>';
echo '<p><strong>Type:</strong> ' . $product->get_type() . '</p>';

echo '<h2>Cross-sell Data (via get_post_meta):</h2>';
$crosssells_meta = get_post_meta($product_id, '_crosssell_ids', true);
echo '<pre>';
var_dump($crosssells_meta);
echo '</pre>';

echo '<h2>Cross-sell Data (via WC_Product object):</h2>';
$crosssells_obj = $product->get_cross_sell_ids();
echo '<pre>';
var_dump($crosssells_obj);
echo '</pre>';

echo '<h2>Upsell Data (via get_post_meta):</h2>';
$upsells_meta = get_post_meta($product_id, '_upsell_ids', true);
echo '<pre>';
var_dump($upsells_meta);
echo '</pre>';

echo '<h2>Upsell Data (via WC_Product object):</h2>';
$upsells_obj = $product->get_upsell_ids();
echo '<pre>';
var_dump($upsells_obj);
echo '</pre>';

echo '<h2>Product Categories:</h2>';
$categories = wp_get_post_terms($product_id, 'product_cat');
echo '<pre>';
foreach ($categories as $cat) {
    echo 'ID: ' . $cat->term_id . ' | Name: ' . $cat->name . "\n";
}
echo '</pre>';

echo '<h2>Is Pizza Product?</h2>';
echo '<p>' . (is_pizza_product($product_id) ? 'YES' : 'NO') . '</p>';
