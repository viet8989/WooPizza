<?php
/**
 * Loop Price
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/price.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

function flatsome_child_lightbox_button2() {
    if ( get_theme_mod( 'disable_quick_view', 0 ) ) {
        return;
    }
    wp_enqueue_script( 'wc-add-to-cart-variation' );
    global $product;
    echo '  <a class="quick-view" data-prod="' . $product->get_id() . '" href="#quick-view" onclick="event.preventDefault(); sessionStorage.setItem(\'pizza_view\', \'paired\');"><img src="/wp-content/uploads/images/half_pizza.png" /></a>';
}
add_action('flatsome_product_box_actions2', 'flatsome_child_lightbox_button2', 50);
?>

<?php if ( $price_html = $product->get_price_html() ) : ?>
	<span class="price" style="float: left">From <?php echo $price_html; ?></span>
	<?php do_action( 'flatsome_product_box_actions' ); ?>
<?php endif; ?>
