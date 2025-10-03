<?php
// Add custom Theme Functions here

function enqueue_custom_js()
{
    wp_enqueue_script('custom-js', get_stylesheet_directory_uri() . '/js/custom.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_custom_js');

add_filter('woocommerce_currency_symbol', function ($currency_symbol, $currency) {
    if ($currency === 'VND') {
        $currency_symbol = 'VND'; // Đổi từ ₫ sang VND
    }
    return $currency_symbol;
}, 10, 2);


