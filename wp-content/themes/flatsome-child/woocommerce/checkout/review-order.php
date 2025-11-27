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

<?php if ( WC()->cart && ! WC()->cart->is_empty() ) : ?>

	<ul class="woocommerce-checkout-review-order-items cart_list product_list_widget">
		<?php
		do_action( 'woocommerce_review_order_before_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
				$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				?>
				<li class="woocommerce-checkout-review-order-item checkout-cart-item">

					<?php
					// Check if this is a paired pizza order
					$is_paired = false;
					$paired_icon = '';
					$display_title = $product_name;

					if ( isset( $cart_item['pizza_halves'] ) && ! empty( $cart_item['pizza_halves'] ) ) {
						$halves = $cart_item['pizza_halves'];
						$left_name = isset( $halves['left_half']['name'] ) ? $halves['left_half']['name'] : '';
						$right_name = isset( $halves['right_half']['name'] ) ? $halves['right_half']['name'] : '';

						if ( $left_name && $right_name ) {
							$is_paired = true;
							$icon_url = get_site_url() . '/wp-content/uploads/2025/10/pizza_half_active.png';
							$paired_icon = sprintf(
								'<img src="%s" alt="Paired Pizza" class="paired-pizza-icon" style="width: 20px !important; height: 20px !important; margin-top: 5px !important;" />',
								esc_url( $icon_url )
							);
							$display_title = sprintf(
								'%s <strong style="color: red;">X</strong> %s',
								esc_html( $left_name ),
								esc_html( $right_name )
							);
						}
					}
					?>

					<div class="checkout-title-wrapper">
						<?php if ( $is_paired ) : ?>
							<?php echo $paired_icon; ?>
						<?php endif; ?>

						<?php
						// Get base product price
						$base_price = $_product->get_regular_price();
						if ( empty( $base_price ) ) {
							$base_price = $_product->get_price();
						}
						$base_price_formatted = wc_price( $base_price );
						?>

						<?php if ( empty( $product_permalink ) ) : ?>
							<h3 class="checkout-product-title">
								<?php echo $display_title; ?>
								<?php if ( !$is_paired ) : ?>
									<span class="product-base-price" style="float: right;"><?php echo $base_price_formatted; ?></span>
								<?php endif; ?>
							</h3>
						<?php else : ?>
							<a href="<?php echo esc_url( $product_permalink ); ?>">
								<h3 class="checkout-product-title">
									<?php echo $display_title; ?>
									<?php if ( !$is_paired ) : ?>
										<span class="product-base-price" style="float: right;"><?php echo $base_price_formatted; ?></span>
									<?php endif; ?>
								</h3>
							</a>
						<?php endif; ?>
					</div>

					<div class="checkout-item-content">
						<div class="checkout-item-image">
							<?php if ( empty( $product_permalink ) ) : ?>
								<?php echo $thumbnail; ?>
							<?php else : ?>
								<a href="<?php echo esc_url( $product_permalink ); ?>">
									<?php echo $thumbnail; ?>
								</a>
							<?php endif; ?>
						</div>

						<div class="checkout-item-details">
					<?php
					// Display extra toppings (whole pizza)
					if ( isset( $cart_item['extra_topping_options'] ) && ! empty( $cart_item['extra_topping_options'] ) ) {
						$topping_index = 0;
						foreach ( $cart_item['extra_topping_options'] as $topping ) {
							if ( isset( $topping['name'] ) && isset( $topping['price'] ) ) {
								echo '<div class="pizza-topping-row">';
								if ( $topping_index === 0 ) {
									echo '<span class="topping-label">Add</span>';
								} else {
									echo '<span class="topping-label-spacer"></span>';
								}
								echo '<span class="topping-details">';
								echo '<span class="topping-name">' . esc_html( $topping['name'] ) . '</span> ';
								echo '<span class="topping-price">' . wc_price( $topping['price'] ) . '</span>';
								echo '</span>';
								echo '</div>';
								$topping_index++;
							}
						}
					}

					// Display pizza halves (new structure with per-half toppings)
					if ( isset( $cart_item['pizza_halves'] ) && ! empty( $cart_item['pizza_halves'] ) ) {
						$halves = $cart_item['pizza_halves'];

						// Display left half
						if ( isset( $halves['left_half'] ) && ! empty( $halves['left_half'] ) ) {
							$left = $halves['left_half'];
							if ( isset( $left['name'] ) ) {
								$left_name = esc_html( $left['name'] );
								$left_price = isset( $left['price'] ) ? wc_price( $left['price'] ) : '';

								echo '<div class="pizza-half-section">';
								echo '<div class="pizza-half-title">1/2 ' . $left_name . '<span style="float: right;">' . $left_price . '</span></div>';

								// Display left half toppings
								if ( isset( $left['toppings'] ) && ! empty( $left['toppings'] ) ) {
									$topping_index = 0;
									foreach ( $left['toppings'] as $topping ) {
										if ( isset( $topping['name'] ) && isset( $topping['price'] ) ) {
											echo '<div class="pizza-topping-row">';
											if ( $topping_index === 0 ) {
												echo '<span class="topping-label">Add</span>';
											} else {
												echo '<span class="topping-label-spacer"></span>';
											}
											echo '<span class="topping-details">';
											echo '<span class="topping-name">' . esc_html( $topping['name'] ) . '</span> ';
											echo '<span class="topping-price">' . wc_price( $topping['price'] ) . '</span>';
											echo '</span>';
											echo '</div>';
											$topping_index++;
										}
									}
								}

								echo '</div>';
							}
						}

						// Display right half
						if ( isset( $halves['right_half'] ) && ! empty( $halves['right_half'] ) ) {
							$right = $halves['right_half'];
							if ( isset( $right['name'] ) ) {
								$right_name = esc_html( $right['name'] );
								$right_price = isset( $right['price'] ) ? wc_price( $right['price'] ) : '';

								echo '<div class="pizza-half-section">';
								echo '<div class="pizza-half-title">1/2' . $right_name . '<span style="float: right;">' . $right_price . '</span></div>';

								// Display right half toppings
								if ( isset( $right['toppings'] ) && ! empty( $right['toppings'] ) ) {
									$topping_index = 0;
									foreach ( $right['toppings'] as $topping ) {
										if ( isset( $topping['name'] ) && isset( $topping['price'] ) ) {
											echo '<div class="pizza-topping-row">';
											if ( $topping_index === 0 ) {
												echo '<span class="topping-label">Add</span>';
											} else {
												echo '<span class="topping-label-spacer"></span>';
											}
											echo '<span class="topping-details">';
											echo '<span class="topping-name">' . esc_html( $topping['name'] ) . '</span> ';
											echo '<span class="topping-price">' . wc_price( $topping['price'] ) . '</span>';
											echo '</span>';
											echo '</div>';
											$topping_index++;
										}
									}
								}

								echo '</div>';
							}
						}
					}

					// Display paired products (legacy support)
					if ( isset( $cart_item['paired_products'] ) && ! empty( $cart_item['paired_products'] ) ) {
						foreach ( $cart_item['paired_products'] as $paired ) {
							if ( isset( $paired['name'] ) && isset( $paired['price'] ) ) {
								echo '<div class="pizza-option-row">';
								echo '<div class="pizza-option-label">' . __( 'Paired with', 'flatsome' ) . ':</div>';
								echo '<div class="pizza-option-value">' . sprintf(
									'%s (+%s)',
									esc_html( $paired['name'] ),
									wc_price( $paired['price'] )
								) . '</div>';
								echo '</div>';
							}
						}
					}

					// Display special request
					if ( isset( $cart_item['special_request'] ) && ! empty( $cart_item['special_request'] ) ) {
						echo '<div class="pizza-option-row">';
						echo '<div class="pizza-option-label">' . __( 'Special Request', 'flatsome' ) . ':</div>';
						echo '<div class="pizza-option-value">' . esc_html( $cart_item['special_request'] ) . '</div>';
						echo '</div>';
					}
					?>

							<?php
							// Calculate the correct price including all customizations
							$calculated_price = 0;

							// Check if paired pizza mode
							if ( isset( $cart_item['pizza_halves'] ) && ! empty( $cart_item['pizza_halves'] ) ) {
								$halves = $cart_item['pizza_halves'];
								$has_right_half = isset( $halves['right_half']['product_id'] ) && ! empty( $halves['right_half']['product_id'] );

								if ( $has_right_half ) {
									// TRUE PAIRED MODE
									if ( isset( $halves['left_half']['price'] ) ) {
										$calculated_price += floatval( $halves['left_half']['price'] );
									}
									if ( isset( $halves['left_half']['toppings'] ) && ! empty( $halves['left_half']['toppings'] ) ) {
										foreach ( $halves['left_half']['toppings'] as $topping ) {
											if ( isset( $topping['price'] ) ) {
												$calculated_price += floatval( $topping['price'] );
											}
										}
									}
									if ( isset( $halves['right_half']['price'] ) ) {
										$calculated_price += floatval( $halves['right_half']['price'] );
									}
									if ( isset( $halves['right_half']['toppings'] ) && ! empty( $halves['right_half']['toppings'] ) ) {
										foreach ( $halves['right_half']['toppings'] as $topping ) {
											if ( isset( $topping['price'] ) ) {
												$calculated_price += floatval( $topping['price'] );
											}
										}
									}
								} else {
									// SINGLE HALF MODE - treat as WHOLE pizza
									if ( isset( $halves['left_half']['price'] ) ) {
										$calculated_price += floatval( $halves['left_half']['price'] ) * 2;
									}
									if ( isset( $halves['left_half']['toppings'] ) && ! empty( $halves['left_half']['toppings'] ) ) {
										foreach ( $halves['left_half']['toppings'] as $topping ) {
											if ( isset( $topping['price'] ) ) {
												$calculated_price += floatval( $topping['price'] ) * 2;
											}
										}
									}
								}
							} else {
								// Whole pizza mode
								$calculated_price = floatval( $_product->get_price() );
								if ( isset( $cart_item['extra_topping_options'] ) && ! empty( $cart_item['extra_topping_options'] ) ) {
									foreach ( $cart_item['extra_topping_options'] as $topping ) {
										if ( isset( $topping['price'] ) ) {
											$calculated_price += floatval( $topping['price'] );
										}
									}
								}
							}

							$unit_price_formatted = wc_price( $calculated_price );
							$line_total = $calculated_price * $cart_item['quantity'];
							$line_total_formatted = wc_price( $line_total );
							?>

						</div>
					</div>

					<!-- Quantity and Price Row -->
					<div class="checkout-quantity-price-row">
						<div class="checkout-quantity-display">
							<span class="qty-label">Qty:</span>
							<span class="qty-value"><?php echo esc_html( $cart_item['quantity'] ); ?></span>
						</div>
						<div class="checkout-line-total">
							<span class="amount"><?php echo $line_total_formatted; ?></span>
						</div>
					</div>
				</li>
				<?php
			}
		}

		do_action( 'woocommerce_review_order_after_cart_contents' );
		?>
	</ul>

	<!-- Cart Totals Breakdown -->
	<div class="checkout-totals-breakdown">
		<div class="totals-row subtotal-row">
			<span class="totals-label">Subtotal</span>
			<span class="totals-value"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
		</div>

		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
		<div class="totals-row shipping-row">
			<span class="totals-label">Shipping</span>
			<span class="totals-value"><?php wc_cart_totals_shipping_html(); ?></span>
		</div>
		<?php endif; ?>

		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
		<div class="totals-row tax-row">
			<?php
				$rate_suffix = '';
				$tax_totals = WC()->cart->get_tax_totals();
				if ( ! empty( $tax_totals ) ) {
					$first = reset( $tax_totals );
					if ( ! empty( $first->tax_rate_id ) ) {
						$rate_suffix = ' (' . esc_html( WC_Tax::get_rate_percent( $first->tax_rate_id ) ) . ')';
					}
				}
			?>
			<span class="totals-label"><?php echo esc_html( WC()->countries->tax_or_vat() . $rate_suffix ); ?></span>
			<span class="totals-value"><?php wc_cart_totals_taxes_total_html(); ?></span>
		</div>
		<?php endif; ?>

		<div class="totals-row total-row">
			<span class="totals-label"><strong>Total</strong></span>
			<span class="totals-value"><strong><?php echo WC()->cart->get_total(); ?></strong></span>
		</div>
	</div>

<?php else : ?>

	<p class="woocommerce-checkout-review-order__empty-message"><?php esc_html_e( 'No products in the cart.', 'woocommerce' ); ?></p>

<?php endif; ?>

<style>

/* Checkout Review Order Items - List Layout (like mini-cart) */
.woocommerce-checkout-review-order-items {
	list-style: none !important;
	padding: 0 !important;
	margin: 0 0 20px 0 !important;
}

.checkout-cart-item {
	position: relative !important;
	padding: 10px !important;
	margin-bottom: 10px !important;
	background-color: #fff !important;
	border-radius: 5px !important;
	overflow: visible !important;
}

/* Title Wrapper - Override Flatsome styles */
.checkout-title-wrapper {
	display: flex !important;
	align-items: center !important;
	gap: 8px !important;
	margin-bottom: 12px !important;
	line-height: 1.4 !important;
	white-space: normal !important;
}

.checkout-title-wrapper > a {
	padding-top: 15px !important;
	width: 100% !important;
}

/* Paired Pizza Icon */
.checkout-title-wrapper .paired-pizza-icon {
	width: 22px !important;
	height: 22px !important;
	flex-shrink: 0 !important;
	vertical-align: middle !important;
}

/* Checkout Product Title */
.checkout-cart-item a:not(.remove) {
	display: inline !important;
	line-height: 1.4 !important;
	margin-bottom: 0 !important;
	overflow: visible !important;
	text-overflow: clip !important;
}

.checkout-product-title {
	font-size: 16px !important;
	font-weight: 700 !important;
	margin: 0 !important;
	line-height: 1.4 !important;
	color: #000 !important;
	display: inline !important;
}

a .checkout-product-title {
	color: #000 !important;
	transition: color 0.3s ease !important;
}

a:hover .checkout-product-title {
	color: #dc0000 !important;
}

/* Two Column Layout - Image Left, Details Right */
.checkout-item-content {
	display: flex !important;
	gap: 12px !important;
	align-items: center !important;
	width: 100% !important;
	flex-direction: row !important;
}

.checkout-item-image {
	flex: 0 0 90px !important;
	width: 90px !important;
	max-width: 90px !important;
	display: block !important;
	order: 0 !important;
}

.checkout-item-image img {
	width: 100% !important;
	max-width: 90px !important;
	height: auto !important;
	border-radius: 6px !important;
	box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
	display: block !important;
}

.checkout-item-details {
	flex: 1 !important;
	min-width: 0 !important;
	order: 1 !important;
}

/* Pizza Half Section Styling */
.pizza-half-section {
	margin: 8px 0 12px 0 !important;
	font-size: 14px !important;
}

.pizza-half-title {
	font-weight: 700 !important;
	color: #000 !important;
	margin-bottom: 6px !important;
	line-height: 1.5 !important;
	text-transform: uppercase !important;
	font-size: 13px !important;
	letter-spacing: 0.3px !important;
}

.pizza-half-title .amount {
	color: #000 !important;
	font-weight: 700 !important;
}

/* Pizza Topping Row Styling */
.checkout-cart-item .pizza-topping-row {
	display: flex !important;
	align-items: center !important;
	margin: 3px 0 !important;
	padding-left: 0 !important;
	padding-right: 0 !important;
	color: #555 !important;
	line-height: 1.6 !important;
	font-size: 13px !important;
	width: 100% !important;
	flex-wrap: nowrap !important;
}

.checkout-cart-item .pizza-topping-row .topping-label {
	display: inline-block !important;
	min-width: 35px !important;
	max-width: 35px !important;
	font-weight: 600 !important;
	color: #000 !important;
	flex-shrink: 0 !important;
	flex-grow: 0 !important;
}

.checkout-cart-item .pizza-topping-row .topping-label-spacer {
	display: inline-block !important;
	min-width: 35px !important;
	max-width: 35px !important;
	flex-shrink: 0 !important;
	flex-grow: 0 !important;
}

.checkout-cart-item .pizza-topping-row .topping-details {
	flex: 1 !important;
	display: flex !important;
	justify-content: space-between !important;
	align-items: center !important;
	min-width: 0 !important;
	gap: 8px !important;
}

.checkout-cart-item .pizza-topping-row .topping-name {
	color: #555 !important;
	flex: 1 !important;
	white-space: nowrap !important;
	overflow: hidden !important;
	text-overflow: ellipsis !important;
}

.checkout-cart-item .pizza-topping-row .topping-price {
	font-weight: 600 !important;
	color: #000 !important;
	flex-shrink: 0 !important;
	white-space: nowrap !important;
}

.checkout-cart-item .pizza-topping-row .topping-price .amount {
	font-weight: 600 !important;
	color: #000 !important;
}

/* Custom Pizza Options Display Styling (legacy) */
.pizza-option-row {
	display: flex !important;
	margin: 8px 0 !important;
	font-size: 13px !important;
	line-height: 1.6 !important;
}

.pizza-option-label {
	flex: 0 0 auto !important;
	min-width: 35px !important;
	font-weight: 600 !important;
	color: #777 !important;
	padding-right: 10px !important;
}

.pizza-option-value {
	flex: 1 !important;
	color: #555 !important;
}

.pizza-option-value .amount {
	font-weight: 600 !important;
	color: #000 !important;
}

/* Quantity and Price Row */
.checkout-quantity-price-row {
	display: flex !important;
	justify-content: space-between !important;
	align-items: center !important;
	margin-top: 12px !important;
	padding-top: 8px !important;
	border-top: 1px solid #f0f0f0 !important;
}

.checkout-quantity-display {
	display: flex !important;
	align-items: center !important;
	gap: 8px !important;
	font-size: 14px !important;
	font-weight: 600 !important;
	color: #666 !important;
}

.checkout-quantity-display .qty-label {
	color: #666 !important;
}

.checkout-quantity-display .qty-value {
	color: #000 !important;
	font-weight: 700 !important;
}

.checkout-line-total {
	font-size: 16px !important;
	font-weight: 700 !important;
	color: #000 !important;
}

.checkout-line-total .amount {
	color: #000 !important;
	font-weight: 700 !important;
}

/* Cart Totals Breakdown */
.checkout-totals-breakdown {
	margin: 15px 0 !important;
	padding: 0 !important;
}

.checkout-totals-breakdown .totals-row {
	display: flex !important;
	justify-content: space-between !important;
	align-items: center !important;
	padding: 8px 0 !important;
	font-size: 18px !important;
	color: #666 !important;
}

.checkout-totals-breakdown .totals-row.total-row {
	border-top: 2px solid #000 !important;
	padding-top: 12px !important;
	margin-top: 8px !important;
	font-size: 18px !important;
	color: #000 !important;
}

.checkout-totals-breakdown .totals-label {
	font-weight: 600 !important;
	color: #000 !important;
}

.checkout-totals-breakdown .total-row .totals-label {
	font-weight: 700 !important;
	font-size: 18px !important;
}

.checkout-totals-breakdown .totals-value {
	font-weight: 600 !important;
}

.checkout-totals-breakdown .total-row .totals-value {
	color: #cd0000 !important;
	font-weight: 700 !important;
	font-size: 18px !important;
}

.checkout-totals-breakdown .totals-value .amount {
	font-weight: inherit !important;
	color: inherit !important;
}

/* Shipping row special styling */
.checkout-totals-breakdown .shipping-row .totals-value {
	color: #666 !important;
}

.checkout-totals-breakdown .shipping-row .totals-value ul {
	list-style: none !important;
	padding: 0 !important;
	margin: 0 !important;
}

.checkout-totals-breakdown .shipping-row .totals-value li {
	margin: 0 !important;
}

.checkout-totals-breakdown .shipping-row .totals-value label {
	font-weight: 600 !important;
	color: #000 !important;
	display: inline !important;
}

.checkout-totals-breakdown .shipping-row .totals-value .amount {
	font-weight: 600 !important;
	color: #000 !important;
}
</style>
