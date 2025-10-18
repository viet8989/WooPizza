<?php
/**
 * One-time cleanup script to remove duplicate IDs from upsells and cross-sells
 * Run this file once via browser, then delete it
 */

// Load WordPress
require_once __DIR__ . '/wp-load.php';

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied. Must be logged in as administrator.');
}

echo '<h1>Cleanup Duplicate Upsells and Cross-sells</h1>';

// Get all products
$args = array(
    'post_type' => 'product',
    'posts_per_page' => -1,
    'fields' => 'ids',
);

$product_ids = get_posts($args);
$cleaned_count = 0;
$total_products = count($product_ids);

echo '<p>Found ' . $total_products . ' products to check...</p>';
echo '<ul>';

foreach ($product_ids as $product_id) {
    $updated = false;

    // Clean upsells
    $upsells = get_post_meta($product_id, '_upsell_ids', true);
    if (is_array($upsells) && !empty($upsells)) {
        $original_count = count($upsells);
        $upsells_clean = array_values(array_unique(array_map('intval', $upsells)));

        if (count($upsells_clean) !== $original_count) {
            update_post_meta($product_id, '_upsell_ids', $upsells_clean);
            echo '<li>Product #' . $product_id . ': Cleaned upsells (' . $original_count . ' → ' . count($upsells_clean) . ')</li>';
            $updated = true;
        }
    }

    // Clean cross-sells
    $crosssells = get_post_meta($product_id, '_crosssell_ids', true);
    if (is_array($crosssells) && !empty($crosssells)) {
        $original_count = count($crosssells);
        $crosssells_clean = array_values(array_unique(array_map('intval', $crosssells)));

        if (count($crosssells_clean) !== $original_count) {
            update_post_meta($product_id, '_crosssell_ids', $crosssells_clean);
            echo '<li>Product #' . $product_id . ': Cleaned cross-sells (' . $original_count . ' → ' . count($crosssells_clean) . ')</li>';
            $updated = true;
        }
    }

    if ($updated) {
        $cleaned_count++;

        // Clear product cache
        $product = wc_get_product($product_id);
        if ($product) {
            wc_delete_product_transients($product_id);
        }
    }
}

echo '</ul>';
echo '<h2>Summary:</h2>';
echo '<p><strong>Total products checked:</strong> ' . $total_products . '</p>';
echo '<p><strong>Products cleaned:</strong> ' . $cleaned_count . '</p>';
echo '<p style="color: green; font-weight: bold;">✓ Cleanup complete! You can now delete this file.</p>';
