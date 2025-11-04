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
								'<img src="%s" alt="Paired Pizza" class="paired-pizza-icon" style="width: 20px !important; height: 20px !important;" />',
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

						<?php if ( empty( $product_permalink ) ) : ?>
							<h3 class="mini-cart-product-title"><?php echo $display_title; // Already escaped above ?></h3>
						<?php else : ?>
							<a href="<?php echo esc_url( $product_permalink ); ?>" style="padding-top: 15px;">
								<h3 class="mini-cart-product-title"><?php echo $display_title; // Already escaped above ?></h3>
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
							</div>

							<!-- Edit/Delete Actions -->
							<div class="mini-cart-item-actions">
								<a href="<?php echo esc_url( wc_get_cart_url() ); ?>"
								   class="edit-item"
								   title="<?php esc_attr_e( 'Edit item', 'woocommerce' ); ?>"
								   aria-label="<?php esc_attr_e( 'Edit item', 'woocommerce' ); ?>">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
										<path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
									</svg>
								</a>
							</div>

						</div><!-- .mini-cart-item-details -->
					</div><!-- .mini-cart-item-content -->
				</li>
				<?php
				}
			}

		do_action( 'woocommerce_mini_cart_contents' );
		?>
	</ul>

	<p class="woocommerce-mini-cart__total total">
		<?php
		/**
		 * Hook: woocommerce_widget_shopping_cart_total.
		 *
		 * @hooked woocommerce_widget_shopping_cart_subtotal - 10
		 */
		do_action( 'woocommerce_widget_shopping_cart_total' );
		?>
	</p>

	<?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

	<p class="woocommerce-mini-cart__buttons buttons"><?php do_action( 'woocommerce_widget_shopping_cart_buttons' ); ?></p>

	<?php do_action( 'woocommerce_widget_shopping_cart_after_buttons' ); ?>

<?php else : ?>

	<p class="woocommerce-mini-cart__empty-message"><?php esc_html_e( 'No products in the cart.', 'woocommerce' ); ?></p>

<?php endif; ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>

<style>
/* Force styles to apply in all cart contexts */
.woocommerce-mini-cart-item,
.widget_shopping_cart .woocommerce-mini-cart-item,
.cart-sidebar .woocommerce-mini-cart-item,
.off-canvas .woocommerce-mini-cart-item {
	position: relative !important;
	padding: 20px 0 !important;
	border-bottom: 1px solid #efefef !important;
}

/* Remove Button - Absolute Position */
.woocommerce-mini-cart-item .remove {
	position: absolute;
	top: 15px;
	right: 0;
	font-size: 28px;
	line-height: 1;
	color: #ccc;
	z-index: 10;
	text-decoration: none;
	font-weight: 300;
	transition: all 0.3s ease;
}

.woocommerce-mini-cart-item .remove:hover {
	color: #dc0000;
	transform: scale(1.1);
}

/* Title Wrapper - Override Flatsome styles */
.mini-cart-title-wrapper {
	display: flex;
	align-items: center;
	gap: 8px;
	margin-bottom: 12px;
	margin-right: 35px;
	line-height: 1.4;
	white-space: normal;
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
.mini-cart-item-content {
	display: flex !important;
	gap: 12px !important;
	align-items: flex-start !important;
	width: 100% !important;
}

.mini-cart-item-image {
	flex: 0 0 90px !important;
	width: 90px !important;
	display: block !important;
}

.mini-cart-item-image img {
	width: 100% !important;
	height: auto !important;
	border-radius: 6px !important;
	box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
	display: block !important;
}

.mini-cart-item-details {
	flex: 1 !important;
	min-width: 0 !important;
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
.pizza-topping-row {
	display: flex !important;
	align-items: flex-start !important;
	margin: 3px 0 !important;
	padding-left: 0 !important;
	color: #555 !important;
	line-height: 1.6 !important;
	font-size: 13px !important;
	width: 100% !important;
}

.pizza-topping-row .topping-label {
	display: inline-block !important;
	min-width: 35px !important;
	font-weight: 600 !important;
	color: #777 !important;
	flex-shrink: 0 !important;
}

.pizza-topping-row .topping-label-spacer {
	display: inline-block !important;
	min-width: 35px !important;
	flex-shrink: 0 !important;
}

.pizza-topping-row .topping-details {
	flex: 1 !important;
	display: flex !important;
	justify-content: space-between !important;
	align-items: center !important;
	width: 100% !important;
}

.pizza-topping-row .topping-name {
	color: #555 !important;
	flex: 1 !important;
}

.pizza-topping-row .topping-price {
	font-weight: 600 !important;
	color: #000 !important;
	margin-left: 8px !important;
}

.pizza-topping-row .topping-price .amount {
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
	width: 45px !important;
	height: 32px !important;
	text-align: center !important;
	border: 1px solid #ddd !important;
	border-radius: 4px !important;
	font-weight: 600 !important;
	font-size: 14px !important;
	padding: 0 !important;
	margin: 0 !important;
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
	position: absolute !important;
	top: 15px !important;
	right: 35px !important;
	display: flex !important;
	gap: 6px !important;
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

/* Widget Shopping Cart Overrides - Force override Flatsome defaults */
ul.product_list_widget li,
.widget_shopping_cart ul.product_list_widget li,
.cart-sidebar ul.product_list_widget li,
.off-canvas ul.product_list_widget li {
	position: relative !important;
	padding: 20px 0 !important;
	padding-left: 0 !important;
	min-height: 0 !important;
	overflow: visible !important;
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
</style>

<script type="text/javascript">
jQuery(document).ready(function($) {
	// Handle quantity increase
	$(document).on('click', '.mini-cart-quantity-controls .plus', function(e) {
		e.preventDefault();
		var $button = $(this);
		var $input = $button.siblings('input.qty');
		var currentVal = parseInt($input.val());
		var max = parseInt($input.attr('max'));

		if (currentVal < max) {
			$input.val(currentVal + 1);
			updateCartQuantity($input.data('cart-item-key'), currentVal + 1);
		}
	});

	// Handle quantity decrease
	$(document).on('click', '.mini-cart-quantity-controls .minus', function(e) {
		e.preventDefault();
		var $button = $(this);
		var $input = $button.siblings('input.qty');
		var currentVal = parseInt($input.val());
		var min = parseInt($input.attr('min'));

		if (currentVal > min) {
			$input.val(currentVal - 1);
			updateCartQuantity($input.data('cart-item-key'), currentVal - 1);
		}
	});

	// Update cart via AJAX
	function updateCartQuantity(cartItemKey, quantity) {
		$.ajax({
			type: 'POST',
			url: wc_add_to_cart_params.ajax_url,
			data: {
				action: 'update_mini_cart_quantity',
				cart_item_key: cartItemKey,
				quantity: quantity,
				security: wc_add_to_cart_params.wc_ajax_url.split('security=')[1]
			},
			beforeSend: function() {
				// Add loading state
				$('.mini-cart-quantity-controls').addClass('updating');
			},
			success: function(response) {
				if (response.success) {
					// Trigger cart update
					$(document.body).trigger('wc_fragment_refresh');
				}
			},
			complete: function() {
				$('.mini-cart-quantity-controls').removeClass('updating');
			}
		});
	}
});
</script>
