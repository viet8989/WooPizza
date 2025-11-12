<?php
/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/mini-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_mini_cart' ); ?>

<?php if ( WC()->cart && ! WC()->cart->is_empty() ) : ?>

	<ul class="woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr( $args['list_class'] ); ?>">
		<?php
		do_action( 'woocommerce_before_mini_cart_contents' );

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
				?>
				<li class="woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">

					<?php
					echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'woocommerce_cart_item_remove_link',
						sprintf(
							'<a role="button" href="%s" class="remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s" data-success_message="%s">&times;</a>',
							esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
							/* translators: %s is the product name */
							esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
							esc_attr( $product_id ),
							esc_attr( $cart_item_key ),
							esc_attr( $_product->get_sku() ),
							/* translators: %s is the product name */
							esc_attr( sprintf( __( '&ldquo;%s&rdquo; has been removed from your cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) )
						),
						$cart_item_key
					);
					?>

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
								'<img src="%s" alt="Paired Pizza" class="paired-pizza-icon" style="width: 20px !important; height: 20px !important; margin-top: 15px !important;" />',
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
							<?php if ( empty( $product_permalink ) ) : ?>
								<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php else : ?>
								<a href="<?php echo esc_url( $product_permalink ); ?>">
									<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</a>
							<?php endif; ?>
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
								echo '<div class="pizza-half-title">' . $left_name . ' ' . $left_price . '</div>';

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
								echo '<div class="pizza-half-title">' . $right_name . ' ' . $right_price . '</div>';

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
						<div class="mini-cart-quantity-controls">
							<button type="button" class="minus" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">−</button>
							<input type="number"
									class="qty"
									value="<?php echo esc_attr( $cart_item['quantity'] ); ?>"
									min="1"
									max="99"
									data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>"
									readonly>
							<button type="button" class="plus" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">+</button>
						</div>
						<div class="mini-cart-line-total">
							<span class="amount"><?php echo $line_total_formatted; ?></span>
						</div>
						<div class="mini-cart-remove-action">
							<?php
							echo apply_filters(
								'woocommerce_cart_item_remove_link',
								sprintf(
									'<a href="%s" class="remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">&times;</a>',
									esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
									esc_attr__( 'Remove this item', 'woocommerce' ),
									esc_attr( $product_id ),
									esc_attr( $cart_item_key ),
									esc_attr( $_product->get_sku() )
								),
								$cart_item_key
							);
							?>
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
	<div class="mini-cart-totals-breakdown">
		<div class="totals-row subtotal-row">
			<span class="totals-label">Subtotal</span>
			<span class="totals-value"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
		</div>

		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
		<div class="totals-row tax-row">
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
			<span class="totals-label"><?php echo esc_html( WC()->countries->tax_or_vat() . $rate_suffix ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="totals-value"><?php wc_cart_totals_taxes_total_html(); ?></span>
		</div>
		<?php endif; ?>

		<div class="totals-row total-row">
			<span class="totals-label"><strong>Total</strong></span>
			<span class="totals-value"><strong><?php echo WC()->cart->get_total(); ?></strong></span>
		</div>
	</div>

	<?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

	<p class="woocommerce-mini-cart__buttons buttons"><?php do_action( 'woocommerce_widget_shopping_cart_buttons' ); ?></p>

	<?php do_action( 'woocommerce_widget_shopping_cart_after_buttons' ); ?>

<?php else : ?>

	<p class="woocommerce-mini-cart__empty-message"><?php esc_html_e( 'No products in the cart.', 'woocommerce' ); ?></p>

<?php endif; ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>

<style>

/* Hide default WooCommerce remove button at top */
.woocommerce-mini-cart-item > .remove {
	display: none !important;
}

/* Remove Button in Quantity Row */
.mini-cart-remove-action {
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
	margin-left: auto !important;
}

.mini-cart-remove-action .remove {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	width: 32px !important;
	height: 32px !important;
	border-radius: 4px !important;
	background-color: #f5f5f5 !important;
	color: #666 !important;
	font-size: 24px !important;
	line-height: 1 !important;
	text-decoration: none !important;
	transition: all 0.3s ease !important;
	font-weight: 300 !important;
	right: 10px !important;
}

.mini-cart-remove-action .remove:hover {
	background-color: #dc0000 !important;
	color: #fff !important;
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
.woocommerce-mini-cart .mini-cart-item-content,
.widget_shopping_cart .mini-cart-item-content,
.cart-sidebar .mini-cart-item-content,
.off-canvas .mini-cart-item-content {
	display: flex !important;
	gap: 12px !important;
	align-items: center !important;
	width: 100% !important;
	flex-direction: row !important;
}

.woocommerce-mini-cart .mini-cart-item-image,
.widget_shopping_cart .mini-cart-item-image,
.cart-sidebar .mini-cart-item-image,
.off-canvas .mini-cart-item-image {
	flex: 0 0 90px !important;
	width: 90px !important;
	max-width: 90px !important;
	display: block !important;
	order: 0 !important;
}

.woocommerce-mini-cart .mini-cart-item-image img,
.widget_shopping_cart .mini-cart-item-image img,
.cart-sidebar .mini-cart-item-image img,
.off-canvas .mini-cart-item-image img {
	width: 100% !important;
	max-width: 90px !important;
	height: auto !important;
	border-radius: 6px !important;
	box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
	display: block !important;
}

.woocommerce-mini-cart .mini-cart-item-details,
.widget_shopping_cart .mini-cart-item-details,
.cart-sidebar .mini-cart-item-details,
.off-canvas .mini-cart-item-details {
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
.woocommerce-mini-cart .pizza-topping-row,
.widget_shopping_cart .pizza-topping-row,
.cart-sidebar .pizza-topping-row,
.off-canvas .pizza-topping-row {
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

.woocommerce-mini-cart .pizza-topping-row .topping-label,
.widget_shopping_cart .pizza-topping-row .topping-label,
.cart-sidebar .pizza-topping-row .topping-label,
.off-canvas .pizza-topping-row .topping-label {
	display: inline-block !important;
	min-width: 35px !important;
	max-width: 35px !important;
	font-weight: 600 !important;
	color: #000 !important;
	flex-shrink: 0 !important;
	flex-grow: 0 !important;
}

.woocommerce-mini-cart .pizza-topping-row .topping-label-spacer,
.widget_shopping_cart .pizza-topping-row .topping-label-spacer,
.cart-sidebar .pizza-topping-row .topping-label-spacer,
.off-canvas .pizza-topping-row .topping-label-spacer {
	display: inline-block !important;
	min-width: 35px !important;
	max-width: 35px !important;
	flex-shrink: 0 !important;
	flex-grow: 0 !important;
}

.woocommerce-mini-cart .pizza-topping-row .topping-details,
.widget_shopping_cart .pizza-topping-row .topping-details,
.cart-sidebar .pizza-topping-row .topping-details,
.off-canvas .pizza-topping-row .topping-details {
	flex: 1 !important;
	display: flex !important;
	justify-content: space-between !important;
	align-items: center !important;
	min-width: 0 !important;
	gap: 8px !important;
}

.woocommerce-mini-cart .pizza-topping-row .topping-name,
.widget_shopping_cart .pizza-topping-row .topping-name,
.cart-sidebar .pizza-topping-row .topping-name,
.off-canvas .pizza-topping-row .topping-name {
	color: #555 !important;
	flex: 1 !important;
	white-space: nowrap !important;
	overflow: hidden !important;
	text-overflow: ellipsis !important;
}

.woocommerce-mini-cart .pizza-topping-row .topping-price,
.widget_shopping_cart .pizza-topping-row .topping-price,
.cart-sidebar .pizza-topping-row .topping-price,
.off-canvas .pizza-topping-row .topping-price {
	font-weight: 600 !important;
	color: #000 !important;
	flex-shrink: 0 !important;
	white-space: nowrap !important;
}

.woocommerce-mini-cart .pizza-topping-row .topping-price .amount,
.widget_shopping_cart .pizza-topping-row .topping-price .amount,
.cart-sidebar .pizza-topping-row .topping-price .amount,
.off-canvas .pizza-topping-row .topping-price .amount {
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

.mini-cart-quantity-controls {
	display: flex !important;
	align-items: center !important;
	gap: 8px !important;
}

.mini-cart-quantity-controls button.minus {
	margin-right: 0 !important;
}

.mini-cart-quantity-controls button.minus,
.mini-cart-quantity-controls button.plus {
	width: 32px !important;
	height: 32px !important;
	border-radius: 50% !important;
	border: 1px solid #ddd !important;
	background-color: #fff !important;
	color: #333 !important;
	font-size: 18px !important;
	font-weight: 300 !important;
	cursor: pointer !important;
	transition: all 0.3s ease !important;
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
	line-height: 1 !important;
	padding: 0 !important;
}

.mini-cart-quantity-controls button.minus:hover,
.mini-cart-quantity-controls button.plus:hover {
	background-color: #cd0000 !important;
	color: #fff !important;
	border-color: #cd0000 !important;
}

.mini-cart-quantity-controls input.qty {
	width: 35px !important;
	height: 32px !important;
	text-align: center !important;
	border: 1px solid #ddd !important;
	border-radius: 4px !important;
	font-weight: 600 !important;
	font-size: 15px !important;
	padding: 0 !important;
	margin: 0 0 16px 0 !important;
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
ul.product_list_widget li,
.widget_shopping_cart ul.product_list_widget li,
.cart-sidebar ul.product_list_widget li,
.off-canvas ul.product_list_widget li {
	position: relative !important;
	padding: 10px !important;
	min-height: 0 !important;
	overflow: visible !important;
	background-color: #fff;
    border-radius: 5px;
}

.widget_shopping_cart ul.product_list_widget li h3 {
	color: #000 !important;
	font-weight: 700 !important;
}

ul.product_list_widget li img,
.widget_shopping_cart ul.product_list_widget li img,
.cart-sidebar ul.product_list_widget li img,
.off-canvas ul.product_list_widget li img {
	position: relative !important;
	left: auto !important;
	top: auto !important;
	height: auto !important;
	width: 100% !important;
	object-fit: cover !important;
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

<script type="text/javascript">
jQuery(document).ready(function($) {
	$('.woocommerce-mini-cart__buttons.buttons .button.wc-forward').eq(0).hide();
	$('.woocommerce-mini-cart__buttons.buttons .button.wc-forward').eq(1).text('Process your order');

	console.log('=== MINI CART INITIALIZATION ===');

	// Log all cart items at initialization
	logAllCartItems();

	function logAllCartItems() {
		console.log('--- Current Cart Items ---');
		$('.woocommerce-mini-cart-item').each(function(index) {
			var $item = $(this);
			var productName = $item.find('.mini-cart-product-title').text().trim();
			var quantity = $item.find('.mini-cart-quantity-controls input.qty').val();
			var lineTotal = $item.find('.mini-cart-line-total .amount').text().trim();

			console.log('Item ' + (index + 1) + ':');
			console.log('  Product:', productName);
			console.log('  Quantity:', quantity);
			console.log('  Line Total:', lineTotal);

			// Log pizza halves if exists
			var halves = [];
			$item.find('.pizza-half-title').each(function() {
				var halfTitle = $(this).text().trim();
				halves.push(halfTitle);
			});
			if (halves.length > 0) {
				console.log('  Pizza Halves:', halves);
			}

			// Log toppings with better detail
			var toppings = [];
			$item.find('.pizza-topping-row').each(function() {
				var $row = $(this);
				var label = $row.find('.topping-label').text().trim();
				var name = $row.find('.topping-name').text().trim();
				var price = $row.find('.topping-price').text().trim();

				if (name) {
					toppings.push({
						label: label || '',
						name: name,
						price: price
					});
				}
			});
			if (toppings.length > 0) {
				console.log('  Toppings:');
				toppings.forEach(function(t) {
					console.log('    ' + (t.label ? t.label + ' ' : '') + t.name + ': ' + t.price);
				});
			}

			console.log('---');
		});

		// Log totals
		var subtotal = $('.mini-cart-totals-breakdown .subtotal-row .totals-value').text().trim();
		var shipping = $('.mini-cart-totals-breakdown .shipping-row .totals-value').text().trim();
		var tax = $('.mini-cart-totals-breakdown .tax-row .totals-value').text().trim();
		var total = $('.mini-cart-totals-breakdown .total-row .totals-value').text().trim();

		console.log('--- Cart Totals ---');
		console.log('  Subtotal:', subtotal);
		console.log('  Shipping:', shipping);
		console.log('  VAT(8%):', tax);
		console.log('  Total:', total);
		console.log('=== END CART INITIALIZATION ===');
	}

	// Handle quantity increase
	$(document).on('click', '.mini-cart-quantity-controls .plus', function(e) {
		e.preventDefault();
		console.log('Plus button clicked');

		var $button = $(this);
		var $input = $button.siblings('input.qty');
		var currentVal = parseInt($input.val());
		var max = parseInt($input.attr('max'));

		console.log('Current value:', currentVal, 'Max:', max);

		if (currentVal < max) {
			var newVal = currentVal + 1;
			console.log('Increasing quantity to:', newVal);
			updateCartQuantity($input.data('cart-item-key'), newVal, $input);
		} else {
			console.log('Already at maximum quantity');
		}
	});

	// Handle quantity decrease
	$(document).on('click', '.mini-cart-quantity-controls .minus', function(e) {
		e.preventDefault();
		console.log('Minus button clicked');

		var $button = $(this);
		var $input = $button.siblings('input.qty');
		var currentVal = parseInt($input.val());
		var min = parseInt($input.attr('min'));

		console.log('Current value:', currentVal, 'Min:', min);

		if (currentVal > min) {
			var newVal = currentVal - 1;
			console.log('Decreasing quantity to:', newVal);
			updateCartQuantity($input.data('cart-item-key'), newVal, $input);
		} else {
			console.log('Already at minimum quantity');
		}
	});

	// Update cart via AJAX
	function updateCartQuantity(cartItemKey, quantity, $quantityInput) {
		var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';

		console.log('Updating cart - Item Key:', cartItemKey, 'Quantity:', quantity);
		console.log('AJAX URL:', ajaxUrl);

		// Find elements to update
		var $quantityRow = $('button[data-cart-item-key="' + cartItemKey + '"]').closest('.mini-cart-quantity-price-row');
		var $lineTotal = $quantityRow.find('.mini-cart-line-total .amount');
		var $subtotal = $('.mini-cart-totals-breakdown .subtotal-row .totals-value');
		var $tax = $('.mini-cart-totals-breakdown .tax-row .totals-value');
		var $total = $('.mini-cart-totals-breakdown .total-row .totals-value');

		$.ajax({
			type: 'POST',
			url: ajaxUrl,
			data: {
				action: 'update_mini_cart_quantity',
				cart_item_key: cartItemKey,
				quantity: quantity
			},
			beforeSend: function() {
				console.log('AJAX request starting...');
				$('.mini-cart-quantity-controls').addClass('updating');
				$lineTotal.css('opacity', '0.5');
				$subtotal.css('opacity', '0.5');
				$tax.css('opacity', '0.5');
				$total.css('opacity', '0.5');
			},
			success: function(response) {
				console.log('AJAX success response:', response);
				if (response.success) {
					console.log('=== CART UPDATE SUCCESS ===');
					console.log('Cart updated successfully');

					// Log detailed cart data if available
					if (response.data && response.data.cart_details) {
						console.log('--- Server-Side Cart Details ---');
						response.data.cart_details.forEach(function(item, idx) {
							console.log('Item ' + (idx + 1) + ':');
							console.log('  Product:', item.product_name);
							console.log('  Quantity:', item.quantity);
							console.log('  Base Price:', item.base_price_formatted);

							// Log extra toppings (whole pizza)
							if (item.extra_toppings && item.extra_toppings.length > 0) {
								console.log('  Extra Toppings:');
								item.extra_toppings.forEach(function(t) {
									console.log('    ' + t.name + ': ' + t.price_formatted);
								});
							}

							// Log pizza halves
							if (item.pizza_halves) {
								if (item.pizza_halves.left_half) {
									console.log('  Left Half:', item.pizza_halves.left_half.name);
									console.log('    RAW STORED PRICE:', item.pizza_halves.left_half.price_raw);
									console.log('    CALCULATED PRICE:', item.pizza_halves.left_half.price, '(' + item.pizza_halves.left_half.price_formatted + ')');
									if (item.pizza_halves.left_half.toppings && item.pizza_halves.left_half.toppings.length > 0) {
										console.log('    Left Half Toppings:');
										item.pizza_halves.left_half.toppings.forEach(function(t) {
											console.log('      ' + t.name + ': ' + t.price_formatted);
										});
									}
								}
								if (item.pizza_halves.right_half) {
									console.log('  Right Half:', item.pizza_halves.right_half.name);
									console.log('    RAW STORED PRICE:', item.pizza_halves.right_half.price_raw);
									console.log('    CALCULATED PRICE:', item.pizza_halves.right_half.price, '(' + item.pizza_halves.right_half.price_formatted + ')');
									if (item.pizza_halves.right_half.toppings && item.pizza_halves.right_half.toppings.length > 0) {
										console.log('    Right Half Toppings:');
										item.pizza_halves.right_half.toppings.forEach(function(t) {
											console.log('      ' + t.name + ': ' + t.price_formatted);
										});
									}
								}
							}

							console.log('  CALCULATED Unit Price:', item.unit_price_formatted);
							console.log('  CALCULATED Line Total:', item.line_total_formatted);
							console.log('---');
						});
					}

					// Update line total
					if (response.data && response.data.line_total) {
						console.log('Updating line total to:', response.data.line_total);
						$lineTotal.html(response.data.line_total);
					}

					// Update subtotal
					if (response.data && response.data.subtotal) {
						console.log('Updating subtotal to:', response.data.subtotal);
						$subtotal.html(response.data.subtotal);
					}

					// Update tax
					if (response.data && response.data.tax) {
						console.log('Updating tax to:', response.data.tax);
						$tax.html(response.data.tax);
					}

					// Update total
					if (response.data && response.data.total) {
						console.log('Updating total to:', response.data.total);
						$total.html('<strong>' + response.data.total + '</strong>');
					}

					// Update the quantity input field AFTER successful save
					if ($quantityInput) {
						$quantityInput.val(quantity);
						console.log('Updated quantity input to:', quantity);
					}

					// Store in localStorage as backup (for guest users without login)
					try {
						var cartBackup = JSON.parse(localStorage.getItem('woo_mini_cart_backup') || '{}');
						cartBackup[cartItemKey] = {
							quantity: quantity,
							timestamp: new Date().getTime()
						};
						localStorage.setItem('woo_mini_cart_backup', JSON.stringify(cartBackup));
						console.log('Saved quantity to localStorage backup:', cartItemKey, quantity);
					} catch (e) {
						console.warn('Could not save to localStorage:', e);
					}

					// Don't trigger wc_fragment_refresh - we're manually updating everything
					// and it causes race condition with session save
					// $(document.body).trigger('wc_fragment_refresh');

					// Restore opacity
					$lineTotal.css('opacity', '1');
					$subtotal.css('opacity', '1');
					$tax.css('opacity', '1');
					$total.css('opacity', '1');

					// Log all cart items after update
					setTimeout(function() {
						console.log('=== AFTER QUANTITY CHANGE ===');
						logAllCartItems();
					}, 500);
				} else {
					console.error('Cart update failed:', response);
					// Restore opacity on error
					$lineTotal.css('opacity', '1');
					$subtotal.css('opacity', '1');
					$tax.css('opacity', '1');
					$total.css('opacity', '1');
				}
			},
			error: function(xhr, status, error) {
				console.error('AJAX error - Status:', status, 'Error:', error);
				console.error('XHR:', xhr);
				// Restore opacity on error
				$lineTotal.css('opacity', '1');
				$subtotal.css('opacity', '1');
				$tax.css('opacity', '1');
				$total.css('opacity', '1');
			},
			complete: function() {
				console.log('AJAX request completed');
				$('.mini-cart-quantity-controls').removeClass('updating');
			}
		});
	}
});
</script>
