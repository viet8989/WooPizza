<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;
?>
<table class="shop_table woocommerce-checkout-review-order-table">
	<thead>
		<tr>
			<th class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
			<th class="product-total"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		do_action( 'woocommerce_review_order_before_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				?>
				<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
					<td class="product-name">
						<?php
						// Check if this is a paired pizza
						$is_paired = false;
						$paired_icon = '';
						$display_title = $_product->get_name();

						if ( isset( $cart_item['pizza_halves'] ) && ! empty( $cart_item['pizza_halves'] ) ) {
							$halves = $cart_item['pizza_halves'];
							$left_name = isset( $halves['left_half']['name'] ) ? $halves['left_half']['name'] : '';
							$right_name = isset( $halves['right_half']['name'] ) ? $halves['right_half']['name'] : '';

							if ( $left_name && $right_name ) {
								$is_paired = true;
								$icon_url = get_site_url() . '/wp-content/uploads/2025/10/pizza_half_active.png';
								$paired_icon = sprintf(
									'<img src="%s" alt="Paired Pizza" class="paired-pizza-icon">',
									esc_url( $icon_url )
								);
								$display_title = sprintf(
									'%s <strong style="color: red;">X</strong> %s',
									esc_html( $left_name ),
									esc_html( $right_name )
								);
							}
						}

						if ( $is_paired ) {
							echo '<div class="checkout-title-wrapper">';
							echo $paired_icon;
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $display_title, $cart_item, $cart_item_key ) ) . '&nbsp;';
							echo '</div>';
						} else {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ) . '&nbsp;';
						}
						?>
						<?php echo apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf( '&times;&nbsp;%s', $cart_item['quantity'] ) . '</strong>', $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

						<?php
						// Custom Pizza Options Display
						?>
						<div class="checkout-item-details">
							<?php
							// Display extra toppings (whole pizza)
							if ( isset( $cart_item['extra_topping_options'] ) && ! empty( $cart_item['extra_topping_options'] ) ) {
								$topping_index = 0;
								foreach ( $cart_item['extra_topping_options'] as $topping ) {
									echo '<div class="pizza-topping-row">';
									if ( $topping_index === 0 ) {
										echo '<span class="topping-label">Add:</span> ';
									} else {
										echo '<span class="topping-label-spacer"></span>';
									}
									echo '<span class="topping-name">' . esc_html( $topping['name'] ) . '</span> ';
									echo '<span class="topping-price">' . wc_price( $topping['price'] ) . '</span>';
									echo '</div>';
									$topping_index++;
								}
							}

							// Display pizza halves (paired pizza)
							if ( isset( $cart_item['pizza_halves'] ) && ! empty( $cart_item['pizza_halves'] ) ) {
								$halves = $cart_item['pizza_halves'];

								// Left half
								if ( isset( $halves['left_half'] ) ) {
									$left = $halves['left_half'];
									$left_name = isset( $left['name'] ) ? $left['name'] : '';
									$left_price = isset( $left['price'] ) ? wc_price( $left['price'] ) : '';

									echo '<div class="pizza-half-section">';
									echo '<div class="pizza-half-title">' . esc_html( $left_name ) . ' ' . $left_price . '</div>';

									if ( isset( $left['toppings'] ) && ! empty( $left['toppings'] ) ) {
										$topping_index = 0;
										foreach ( $left['toppings'] as $topping ) {
											echo '<div class="pizza-topping-row">';
											if ( $topping_index === 0 ) {
												echo '<span class="topping-label">Add:</span> ';
											} else {
												echo '<span class="topping-label-spacer"></span>';
											}
											echo '<span class="topping-name">' . esc_html( $topping['name'] ) . '</span> ';
											echo '<span class="topping-price">' . wc_price( $topping['price'] ) . '</span>';
											echo '</div>';
											$topping_index++;
										}
									}
									echo '</div>';
								}

								// Right half
								if ( isset( $halves['right_half'] ) ) {
									$right = $halves['right_half'];
									$right_name = isset( $right['name'] ) ? $right['name'] : '';
									$right_price = isset( $right['price'] ) ? wc_price( $right['price'] ) : '';

									echo '<div class="pizza-half-section">';
									echo '<div class="pizza-half-title">' . esc_html( $right_name ) . ' ' . $right_price . '</div>';

									if ( isset( $right['toppings'] ) && ! empty( $right['toppings'] ) ) {
										$topping_index = 0;
										foreach ( $right['toppings'] as $topping ) {
											echo '<div class="pizza-topping-row">';
											if ( $topping_index === 0 ) {
												echo '<span class="topping-label">Add:</span> ';
											} else {
												echo '<span class="topping-label-spacer"></span>';
											}
											echo '<span class="topping-name">' . esc_html( $topping['name'] ) . '</span> ';
											echo '<span class="topping-price">' . wc_price( $topping['price'] ) . '</span>';
											echo '</div>';
											$topping_index++;
										}
									}
									echo '</div>';
								}
							}

							// Display special request
							if ( isset( $cart_item['special_request'] ) && ! empty( $cart_item['special_request'] ) ) {
								echo '<div class="pizza-option-row">';
								echo '<span class="pizza-option-label">Special Request:</span> ';
								echo '<span class="pizza-option-value">' . esc_html( $cart_item['special_request'] ) . '</span>';
								echo '</div>';
							}
							?>
						</div>
					</td>
					<td class="product-total">
						<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</td>
				</tr>
				<?php
			}
		}

		do_action( 'woocommerce_review_order_after_cart_contents' );
		?>
	</tbody>
	<tfoot>

		<tr class="cart-subtotal">
			<th><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
			<td><?php wc_cart_totals_subtotal_html(); ?></td>
		</tr>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
				<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

			<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>

			<?php wc_cart_totals_shipping_html(); ?>

			<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>

		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<tr class="fee">
				<th><?php echo esc_html( $fee->name ); ?></th>
				<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
					<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<?php
						// Try to get the percent for this tax rate id if available
						$rate_suffix = '';
						if ( ! empty( $tax->tax_rate_id ) ) {
							$rate_suffix = ' (' . esc_html( WC_Tax::get_rate_percent( $tax->tax_rate_id ) ) . ')';
						}
						?>
						<th><?php echo esc_html( $tax->label . $rate_suffix ) ; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></th>
						<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="tax-total">
					<?php
					// For non-itemized display, try to append the first tax rate percent if available
					$rate_suffix = '';
					$tax_totals = WC()->cart->get_tax_totals();
					if ( ! empty( $tax_totals ) ) {
						$first = reset( $tax_totals );
						if ( ! empty( $first->tax_rate_id ) ) {
							$rate_suffix = ' (' . esc_html( WC_Tax::get_rate_percent( $first->tax_rate_id ) ) . ')';
						}
					}
					?>
					<th><?php echo esc_html( WC()->countries->tax_or_vat() . $rate_suffix ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></th>
					<td><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

		<tr class="order-total">
			<th><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
			<td><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>

		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

	</tfoot>
</table>

<style>
/* Checkout Item Details Display Styling */
.checkout-item-details {
	margin-top: 10px;
	font-size: 13px;
}

/* Checkout Title Wrapper for Paired Pizza */
.checkout-title-wrapper {
	display: flex;
	align-items: center;
	gap: 6px;
	margin-bottom: 5px;
}

.checkout-title-wrapper .paired-pizza-icon {
	width: 20px;
	height: 20px;
	flex-shrink: 0;
}

/* Custom Pizza Options Display Styling */
.checkout-item-details .pizza-option-row {
	display: flex;
	margin: 8px 0;
	font-size: 13px;
	line-height: 1.6;
}

.checkout-item-details .pizza-option-label {
	flex: 0 0 auto;
	min-width: 120px;
	font-weight: 600;
	color: #333;
	padding-right: 10px;
}

.checkout-item-details .pizza-option-value {
	flex: 1;
	color: #666;
}

.checkout-item-details .pizza-option-value .amount {
	font-weight: 500;
}

/* Pizza Half Section Styling */
.checkout-item-details .pizza-half-section {
	margin: 10px 0;
	font-size: 13px;
}

.checkout-item-details .pizza-half-title {
	font-weight: 600;
	color: #333;
	margin-bottom: 5px;
	line-height: 1.4;
}

.checkout-item-details .pizza-topping-row {
	margin: 4px 0;
	padding-left: 10px;
	color: #666;
	line-height: 1.4;
}

.checkout-item-details .pizza-topping-row .topping-label {
	display: inline-block;
	width: 35px;
	font-weight: 500;
	color: #555;
}

.checkout-item-details .pizza-topping-row .topping-label-spacer {
	display: inline-block;
	width: 35px;
}

.checkout-item-details .pizza-topping-row .topping-name {
	color: #666;
}

.checkout-item-details .pizza-topping-row .topping-price {
	font-weight: 500;
}

.checkout-item-details .pizza-topping-row .topping-price .amount {
	font-weight: 500;
}
</style>
