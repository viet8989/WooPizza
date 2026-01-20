<?php
/**
 * Cart totals
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-totals.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.3.6
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="cart_totals <?php echo ( WC()->customer->has_calculated_shipping() ) ? 'calculated_shipping' : ''; ?>">

	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

	<h2><?php esc_html_e( 'Cart totals', 'woocommerce' ); ?></h2>

	<table cellspacing="0" class="shop_table shop_table_responsive">

		<tr class="cart-subtotal">
			<th><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
			<td data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>"><?php wc_cart_totals_subtotal_html(); ?></td>
		</tr>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
				<td data-title="<?php echo esc_attr( wc_cart_totals_coupon_label( $coupon, false ) ); ?>"><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php

		if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) {
			?>
			<style>
				.cart_totals tr.tax-rate, 
				.cart_totals tr.tax-total:not(.custom-tax-total) {
					display: none !important;
				}
			</style>
			<tr class="tax-total custom-tax-total">
				<th>Tax Total</th>
				<td data-title="Tax Total">
				<?php 
					// Calculate total custom tax manually
					$total_custom_tax = 0;
					foreach ( WC()->cart->get_cart() as $cart_item ) {
						$product = $cart_item['data'];
						$line_total = $cart_item['line_total'];
						
						$custom_tax_rate = get_post_meta( $product->get_id(), '_custom_tax_rate', true );
						if ( ! is_numeric( $custom_tax_rate ) ) {
							// Fallback for variations
							if ( $product->is_type( 'variation' ) ) {
								$parent_id = $product->get_parent_id();
								$custom_tax_rate = get_post_meta( $parent_id, '_custom_tax_rate', true );
							}
						}
						
						if ( ! is_numeric( $custom_tax_rate ) ) {
							$custom_tax_rate = 0;
						}
						
						$total_custom_tax += $line_total * ( floatval( $custom_tax_rate ) / 100 );
					}
					echo wc_price( $total_custom_tax );
				?>
				</td>
			</tr>
			<?php
		}
		?>

		<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

		<tr class="order-total">
			<th><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
			<td data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>"><strong><?php 
				/* $total = floatval( WC()->cart->get_subtotal() ) - floatval( WC()->cart->get_discount_total() ) + floatval( WC()->cart->get_fee_total() ) + $total_custom_tax; */
				$total = floatval( WC()->cart->get_subtotal() ) + $total_custom_tax;
				echo wc_price( $total ); 
			?></strong></td>
		</tr>

		<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>

	</table>

	<div class="wc-proceed-to-checkout">
		<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
	</div>

	<?php do_action( 'woocommerce_after_cart_totals' ); ?>

</div>
