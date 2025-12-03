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
	
	<ul class="woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr( $args['list_class'] ); ?>">
		<?php
		do_action( 'woocommerce_before_mini_cart_contents' );

		$item_index = 0;
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				/**
				 * This filter is documented in woocommerce/templates/cart/cart.php.
				 *
				 * @since 2.1.0
				 */
				$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
				$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
				$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );

				$border_style = ( $item_index === 0 ) ? ' style="border-top: 1px solid #fff !important;"' : '';
				$item_index++;
				?>
				<li class="woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>"<?php echo $border_style; ?>>

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

					<div class="mini-cart-title-wrapper">
						<?php if ( $is_paired ) : ?>
							<?php echo $paired_icon; ?>
						<?php endif; ?>

						<?php
						// Get base product price (regular price without modifications)
						$base_price = $_product->get_regular_price();
						if ( empty( $base_price ) ) {
							$base_price = $_product->get_price();
						}
						$base_price_formatted = wc_price( $base_price );
						?>

						<?php if ( empty( $product_permalink ) ) : ?>
							<h3 class="mini-cart-product-title">
								<?php echo $display_title; // Already escaped above ?>
								<?php if ( !$is_paired ) : ?>
									<span class="product-base-price" style="float: right;"><?php echo $base_price_formatted; ?></span>
								<?php endif; ?>
							</h3>
						<?php else : ?>
							<a href="<?php echo esc_url( $product_permalink ); ?>">
								<h3 class="mini-cart-product-title">
									<?php echo $display_title; // Already escaped above ?>
									<?php if ( !$is_paired ) : ?>
										<span class="product-base-price" style="float: right;"><?php echo $base_price_formatted; ?></span>
									<?php endif; ?>
								</h3>
							</a>
						<?php endif; ?>
					</div>

					<div class="mini-cart-item-content">
						<div class="mini-cart-item-image">
							<?php
							// For paired pizzas, show both half images merged
							if ( $is_paired && isset( $cart_item['pizza_halves'] ) ) {
								$halves = $cart_item['pizza_halves'];
								$left_image = isset( $halves['left_half']['image'] ) ? $halves['left_half']['image'] : '';
								$right_image = isset( $halves['right_half']['image'] ) ? $halves['right_half']['image'] : '';
								$left_name = isset( $halves['left_half']['name'] ) ? $halves['left_half']['name'] : '';
								$right_name = isset( $halves['right_half']['name'] ) ? $halves['right_half']['name'] : '';

								// Display merged half images (similar to lightbox)
								if ( $left_image && $right_image ) {
									?>
										<div class="mini-cart-paired-image-container">
											<div class="mini-cart-half-pizza-container" style="justify-content: right !important;">
												<img src="<?php echo esc_url( $left_image ); ?>"
													 class="mini-cart-left-pizza-img"
													 alt="<?php echo esc_attr( $left_name ); ?>"
													 decoding="async">
											</div>
											<div class="mini-cart-half-pizza-container" style="justify-content: left !important;">
												<img src="<?php echo esc_url( $right_image ); ?>"
													 class="mini-cart-right-pizza-img"
													 alt="<?php echo esc_attr( $right_name ); ?>"
													 decoding="async">
											</div>
										</div>
									<?php
								}
							} else {
								// For non-paired pizzas, show normal thumbnail
								echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
						</div>

						<div class="mini-cart-item-details">
					<?php
					// Custom Pizza Options Display (inline logic instead of filter)

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
								// Display pizza name and price
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
								// Display pizza name and price
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
							// Manually calculate the correct price including all customizations
							$calculated_price = 0;

							// Check if paired pizza mode
							if ( isset( $cart_item['pizza_halves'] ) && ! empty( $cart_item['pizza_halves'] ) ) {
								$halves = $cart_item['pizza_halves'];

								// Check if right half has a product (true paired pizza) or is empty (single half = whole pizza)
								$has_right_half = isset( $halves['right_half']['product_id'] ) && ! empty( $halves['right_half']['product_id'] );

								if ( $has_right_half ) {
									// TRUE PAIRED MODE - two different halves
									// Add left half price
									if ( isset( $halves['left_half']['price'] ) ) {
										$calculated_price += floatval( $halves['left_half']['price'] );
									}

									// Add left half toppings
									if ( isset( $halves['left_half']['toppings'] ) && ! empty( $halves['left_half']['toppings'] ) ) {
										foreach ( $halves['left_half']['toppings'] as $topping ) {
											if ( isset( $topping['price'] ) ) {
												$calculated_price += floatval( $topping['price'] );
											}
										}
									}

									// Add right half price
									if ( isset( $halves['right_half']['price'] ) ) {
										$calculated_price += floatval( $halves['right_half']['price'] );
									}

									// Add right half toppings
									if ( isset( $halves['right_half']['toppings'] ) && ! empty( $halves['right_half']['toppings'] ) ) {
										foreach ( $halves['right_half']['toppings'] as $topping ) {
											if ( isset( $topping['price'] ) ) {
												$calculated_price += floatval( $topping['price'] );
											}
										}
									}
								} else {
									// SINGLE HALF MODE - right half is empty, treat as WHOLE pizza
									// Double the left half price to get the full pizza price
									if ( isset( $halves['left_half']['price'] ) ) {
										$calculated_price += floatval( $halves['left_half']['price'] ) * 2;
									}

									// Add left half toppings (also double them for whole pizza)
									if ( isset( $halves['left_half']['toppings'] ) && ! empty( $halves['left_half']['toppings'] ) ) {
										foreach ( $halves['left_half']['toppings'] as $topping ) {
											if ( isset( $topping['price'] ) ) {
												$calculated_price += floatval( $topping['price'] ) * 2;
											}
										}
									}
								}
							} else {
								// Whole pizza mode - start with base price
								$calculated_price = floatval( $_product->get_price() );

								// Add whole pizza toppings
								if ( isset( $cart_item['extra_topping_options'] ) && ! empty( $cart_item['extra_topping_options'] ) ) {
									foreach ( $cart_item['extra_topping_options'] as $topping ) {
										if ( isset( $topping['price'] ) ) {
											$calculated_price += floatval( $topping['price'] );
										}
									}
								}
							}

							// Format unit price (not total)
							$unit_price_formatted = wc_price( $calculated_price );
							$line_total = $calculated_price * $cart_item['quantity'];
							$line_total_formatted = wc_price( $line_total );
							?>


						</div><!-- .mini-cart-item-details -->
					</div><!-- .mini-cart-item-content -->
					<!-- Quantity Controls and Price Row -->
					<div class="mini-cart-quantity-price-row">
						<div class="mini-cart-quantity-display">
							<?php echo $unit_price_formatted . ' x ' . $cart_item['quantity']; ?>
						</div>
						<div class="mini-cart-line-total">
							<span class="amount"><?php echo $line_total_formatted; ?></span>
						</div>
					</div>
				</li>
				<?php
				}
			}

		do_action( 'woocommerce_mini_cart_contents' );
		?>
	</ul>

	<!-- Cart Totals Breakdown -->
	<div class="checkout-totals-breakdown">
		<div class="totals-row subtotal-row">
			<span class="totals-label">Subtotal</span>
			<span class="totals-value"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
		</div>

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

		<?php
		// Custom Shipping/Pickup Fee Row - Replaces WooCommerce default shipping display
		$delivery_method = isset($_POST['selected_delivery_method']) ? sanitize_text_field($_POST['selected_delivery_method']) : 'delivery';
		$selected_store = isset($_POST['selected_store']) ? sanitize_text_field($_POST['selected_store']) : '';
		$selected_ward = isset($_POST['billing_city']) ? sanitize_text_field($_POST['billing_city']) : '';

		if ($delivery_method === 'pickup') {
			// PICKUP - Always 0 fee
			?>
			<div class="totals-row shipping-fee-row">
				<span class="totals-label">Pickup</span>
				<span class="totals-value"><?php echo wc_price(0); ?></span>
			</div>
			<?php
		} else {
			// DELIVERY - Get fee from shipping fees table
			$shipping_fee = 0;
			if (!empty($selected_store) && !empty($selected_ward)) {
				$shipping_fees = get_option('hcm_shipping_fees', array());
				$fee_key = $selected_store . '_' . $selected_ward;

				if (isset($shipping_fees[$fee_key]) && isset($shipping_fees[$fee_key]['fee'])) {
					$shipping_fee = floatval($shipping_fees[$fee_key]['fee']);
				}
			}
			?>
			<div class="totals-row shipping-fee-row">
				<span class="totals-label">Shipping Fee</span>
				<span class="totals-value"><?php echo wc_price($shipping_fee); ?></span>
			</div>
			<?php
		}
		?>

		<div class="totals-row total-row">
			<span class="totals-label"><strong>Total</strong></span>
			<span class="totals-value"><strong><?php
				// Calculate total including fees
				$cart_total = WC()->cart->get_cart_contents_total();
				$cart_tax = WC()->cart->get_cart_contents_tax();
				$fee_total = WC()->cart->get_fee_total();
				$fee_tax = WC()->cart->get_fee_tax();
				$total = $cart_total + $cart_tax + $fee_total + $fee_tax;

				echo wc_price($total);
			?></strong></span>
		</div>
	</div>

<?php else : ?>

	<p class="woocommerce-checkout-review-order__empty-message"><?php esc_html_e( 'No products in the cart.', 'woocommerce' ); ?></p>

<?php endif; ?>

<style>

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
/* Title Wrapper - Override Flatsome styles */
.mini-cart-title-wrapper {
	display: flex;
	align-items: center;
	gap: 8px;
	margin-bottom: 12px;
	line-height: 1.4;
	white-space: normal;
}

.mini-cart-title-wrapper > a {
	padding-top: 15px;
	width: 100% !important;
}

/* Paired Pizza Icon */
.paired-pizza-icon {
	width: 22px;
	height: 22px;
	flex-shrink: 0;
	vertical-align: middle;
}

/* Mini Cart Product Title */
ul.product_list_widget li a:not(.remove) {
	display: inline !important;
	line-height: 1.4 !important;
	margin-bottom: 0 !important;
	overflow: visible !important;
	text-overflow: clip !important;
}

.mini-cart-product-title {
	font-size: 16px;
	font-weight: 700;
	margin: 0;
	line-height: 1.4;
	color: #000;
	display: inline;
}

a .mini-cart-product-title {
	color: #000;
	transition: color 0.3s ease;
}

a:hover .mini-cart-product-title {
	color: #dc0000;
}

/* Two Column Layout - Image Left, Details Right */
.woocommerce-mini-cart .mini-cart-item-content {
	display: flex !important;
	gap: 12px !important;
	align-items: center !important;
	width: 100% !important;
	flex-direction: row !important;
}

.woocommerce-mini-cart .mini-cart-item-image {
	flex: 0 0 90px !important;
	width: 90px !important;
	max-width: 90px !important;
	display: block !important;
	order: 0 !important;
}

.woocommerce-mini-cart .mini-cart-item-image img {
	width: 100% !important;
	max-width: 90px !important;
	height: auto !important;
	border-radius: 6px !important;
	box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
	display: block !important;
}

/* Paired Pizza Image Container */
.mini-cart-paired-image-container {
	display: flex !important;
	width: 90px !important;
	height: 90px !important;
	overflow: hidden !important;
	border-radius: 6px !important;
	box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
}

.mini-cart-half-pizza-container {
	width: 50% !important;
	height: 90px !important;
	overflow: hidden !important;
	background: #f0f0f0 !important;
	display: flex !important;
	align-items: center !important;
}

.woocommerce-mini-cart .mini-cart-item-details {
	flex: 1 !important;
	min-width: 0 !important;
	order: 1 !important;
}

/* Pizza Half Section Styling */
.pizza-half-section {
	margin: 8px 0 12px 0;
	font-size: 14px;
}

.pizza-half-title {
	font-weight: 700;
	color: #000;
	margin-bottom: 6px;
	line-height: 1.5;
	text-transform: uppercase;
	font-size: 13px;
	letter-spacing: 0.3px;
}

.pizza-half-title .amount {
	color: #000;
	font-weight: 700;
}

/* Pizza Topping Row Styling */
.woocommerce-mini-cart .pizza-topping-row {
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

.woocommerce-mini-cart .pizza-topping-row .topping-label {
	display: inline-block !important;
	min-width: 35px !important;
	max-width: 35px !important;
	font-weight: 600 !important;
	color: #000 !important;
	flex-shrink: 0 !important;
	flex-grow: 0 !important;
}

.woocommerce-mini-cart .pizza-topping-row .topping-label-spacer {
	display: inline-block !important;
	min-width: 35px !important;
	max-width: 35px !important;
	flex-shrink: 0 !important;
	flex-grow: 0 !important;
}

.woocommerce-mini-cart .pizza-topping-row .topping-details {
	flex: 1 !important;
	display: flex !important;
	justify-content: space-between !important;
	align-items: center !important;
	min-width: 0 !important;
	gap: 8px !important;
}

.woocommerce-mini-cart .pizza-topping-row .topping-name {
	color: #555 !important;
	flex: 1 !important;
	white-space: nowrap !important;
	overflow: hidden !important;
	text-overflow: ellipsis !important;
}

.woocommerce-mini-cart .pizza-topping-row .topping-price {
	font-weight: 600 !important;
	color: #000 !important;
	flex-shrink: 0 !important;
	white-space: nowrap !important;
}

.woocommerce-mini-cart .pizza-topping-row .topping-price .amount {
	font-weight: 600 !important;
	color: #000 !important;
}

/* Custom Pizza Options Display Styling (legacy) */
.pizza-option-row {
	display: flex;
	margin: 8px 0;
	font-size: 13px;
	line-height: 1.6;
}

.pizza-option-label {
	flex: 0 0 auto;
	min-width: 35px;
	font-weight: 600;
	color: #777;
	padding-right: 10px;
}

.pizza-option-value {
	flex: 1;
	color: #555;
}

.pizza-option-value .amount {
	font-weight: 600;
	color: #000;
}

/* Quantity Controls and Price Row */
.mini-cart-quantity-price-row {
	display: flex !important;
	justify-content: space-between !important;
	align-items: center !important;
	margin-top: 12px !important;
	padding-top: 8px !important;
	border-top: 1px solid #f0f0f0 !important;
}

.mini-cart-line-total {
	font-size: 16px !important;
	font-weight: 700 !important;
	color: #000 !important;
}

.mini-cart-line-total .amount {
	color: #000 !important;
	font-weight: 700 !important;
}

/* Edit/Delete Action Icons */
.mini-cart-item-actions {
	display: flex !important;
	justify-content: flex-end !important;
	gap: 6px !important;
	margin-top: -35px !important;
	margin-bottom: 10px !important;
	position: relative !important;
	z-index: 5 !important;
}

.mini-cart-item-actions a {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	width: 28px !important;
	height: 28px !important;
	border-radius: 4px !important;
	background-color: #f5f5f5 !important;
	color: #666 !important;
	transition: all 0.3s ease !important;
	text-decoration: none !important;
	padding: 5px !important;
}

.mini-cart-item-actions a:hover {
	background-color: #cd0000 !important;
	color: #fff !important;
}

.mini-cart-item-actions svg {
	width: 14px !important;
	height: 14px !important;
}

.inner-padding {
    padding: 20px !important;
}

/* Widget Shopping Cart Overrides - Force override Flatsome defaults */
ul.product_list_widget li {
	position: relative !important;
	padding: 10px !important;
	min-height: 0 !important;
	overflow: visible !important;
	background-color: #fff;
    margin-top: 10px;
    border-top: 1px solid #000 !important;
}

.widget_shopping_cart ul.product_list_widget li h3 {
	color: #000 !important;
	font-weight: 700 !important;
}

ul.product_list_widget li img {
	position: relative !important;
	left: auto !important;
	top: auto !important;
	height: auto !important;
	width: 100% !important;
	object-fit: cover !important;
}

/* Override for paired pizza images - must come AFTER generic rules */
ul.product_list_widget li .mini-cart-left-pizza-img {
	position: static !important;
	left: 0 !important;
	top: 0 !important;
	width: 200% !important;
	height: 90px !important;
	min-height: 90px !important;
	object-fit: cover !important;
	object-position: right center !important;
	transform: translateX(50%) !important;
	display: block !important;
	border-radius: 0 !important;
	box-shadow: none !important;
}

ul.product_list_widget li .mini-cart-right-pizza-img {
	position: static !important;
	left: 0 !important;
	top: 0 !important;
	width: 200% !important;
	height: 90px !important;
	min-height: 90px !important;
	object-fit: cover !important;
	object-position: left center !important;
	transform: translateX(-50%) !important;
	display: block !important;
	border-radius: 0 !important;
	box-shadow: none !important;
}

.widget_shopping_cart ul.product_list_widget li .quantity {
	color: #000;
	opacity: 1.0;
	font-weight: 600;
	font-size: 15px;
	margin-top: 8px;
	display: inline-block;
}

.widget_shopping_cart ul.product_list_widget li .quantity .amount {
	font-weight: 700;
	color: #000;
}

/* Mini Cart Total */
.woocommerce-mini-cart__total .amount {
	color: #cd0000;
	font-weight: 700;
	font-size: 18px;
}

/* Special Request Styling */
.pizza-option-row:has(.pizza-option-label:contains("Special Request")) {
	background-color: #f9f9f9;
	padding: 8px;
	border-radius: 4px;
	margin-top: 10px;
}

/* Delivery Tabs */
.mini-cart-delivery-tabs {
	display: flex !important;
	gap: 0 !important;
	margin: 20px 0 15px 0 !important;
	border-radius: 6px !important;
	overflow: hidden !important;
}

.mini-cart-delivery-tabs .delivery-tab {
	flex: 1 !important;
	padding: 10px 20px !important;
	background-color: #fff !important;
	border: 1px solid #ddd !important;
	color: #666 !important;
	font-size: 14px !important;
	font-weight: 600 !important;
	cursor: pointer !important;
	transition: all 0.3s ease !important;
}

.mini-cart-delivery-tabs .delivery-tab:first-child {
	border-right: none !important;
	border-radius: 6px 0 0 6px !important;
}

.mini-cart-delivery-tabs .delivery-tab:last-child {
	border-radius: 0 6px 6px 0 !important;
}

.mini-cart-delivery-tabs .delivery-tab.active {
	background-color: #cd0000 !important;
	border-color: #cd0000 !important;
	color: #fff !important;
}

.mini-cart-delivery-tabs .delivery-tab:hover:not(.active) {
	background-color: #f5f5f5 !important;
}

/* Cart Totals Breakdown */
.mini-cart-totals-breakdown {
	margin: 15px 0 !important;
	padding: 0 !important;
}

.mini-cart-totals-breakdown .totals-row {
	display: flex !important;
	justify-content: space-between !important;
	align-items: center !important;
	padding: 8px 0 !important;
	font-size: 18px !important;
	color: #666 !important;
}

.mini-cart-totals-breakdown .totals-row.total-row {
	border-top: 2px solid #000 !important;
	padding-top: 12px !important;
	margin-top: 8px !important;
	font-size: 18px !important;
	color: #000 !important;
}

.mini-cart-totals-breakdown .totals-label {
	font-weight: 600 !important;
	color: #000 !important;
}

.mini-cart-totals-breakdown .total-row .totals-label {
	font-weight: 700 !important;
	font-size: 18px !important;
}

.mini-cart-totals-breakdown .totals-value {
	font-weight: 600 !important;
}

.mini-cart-totals-breakdown .total-row .totals-value {
	color: #cd0000 !important;
	font-weight: 700 !important;
	font-size: 18px !important;
}

.mini-cart-totals-breakdown .totals-value .amount {
	font-weight: inherit !important;
	color: inherit !important;
}
</style>
