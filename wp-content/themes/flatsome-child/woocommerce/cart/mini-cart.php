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
						$topping_names = array();
						foreach ( $cart_item['extra_topping_options'] as $topping ) {
							if ( isset( $topping['name'] ) && isset( $topping['price'] ) ) {
								$topping_names[] = sprintf(
									'%s %s',
									esc_html( $topping['name'] ),
									wc_price( $topping['price'] )
								);
							}
						}
						if ( ! empty( $topping_names ) ) {
							echo '<div class="pizza-option-row">';
							echo '<div class="pizza-option-label">' . __( 'Add', 'flatsome' ) . ':</div>';
							echo '<div class="pizza-option-value">' . implode( '<br/>', $topping_names ) . '</div>';
							echo '</div>';
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

							echo '<script>';
							echo 'console.log("=== PRICE CALCULATION DEBUG ===");';
							echo 'console.log("Product: ' . esc_js( $product_name ) . '");';
							echo 'console.log("Base Price: ' . esc_js( $_product->get_price() ) . '");';

							// Check if paired pizza mode
							if ( isset( $cart_item['pizza_halves'] ) && ! empty( $cart_item['pizza_halves'] ) ) {
								echo 'console.log("Mode: PAIRED PIZZA");';
								$halves = $cart_item['pizza_halves'];

								// Add left half price
								if ( isset( $halves['left_half']['price'] ) ) {
									$left_price = floatval( $halves['left_half']['price'] );
									$calculated_price += $left_price;
									echo 'console.log("  Left Half Price: ' . esc_js( $left_price ) . '");';
								}

								// Add left half toppings
								if ( isset( $halves['left_half']['toppings'] ) && ! empty( $halves['left_half']['toppings'] ) ) {
									echo 'console.log("  Left Half Toppings:");';
									foreach ( $halves['left_half']['toppings'] as $topping ) {
										if ( isset( $topping['price'] ) ) {
											$topping_price = floatval( $topping['price'] );
											$calculated_price += $topping_price;
											echo 'console.log("    - ' . esc_js( $topping['name'] ) . ': ' . esc_js( $topping_price ) . '");';
										}
									}
								}

								// Add right half price
								if ( isset( $halves['right_half']['price'] ) ) {
									$right_price = floatval( $halves['right_half']['price'] );
									$calculated_price += $right_price;
									echo 'console.log("  Right Half Price: ' . esc_js( $right_price ) . '");';
								}

								// Add right half toppings
								if ( isset( $halves['right_half']['toppings'] ) && ! empty( $halves['right_half']['toppings'] ) ) {
									echo 'console.log("  Right Half Toppings:");';
									foreach ( $halves['right_half']['toppings'] as $topping ) {
										if ( isset( $topping['price'] ) ) {
											$topping_price = floatval( $topping['price'] );
											$calculated_price += $topping_price;
											echo 'console.log("    - ' . esc_js( $topping['name'] ) . ': ' . esc_js( $topping_price ) . '");';
										}
									}
								}
							} else {
								// Whole pizza mode - start with base price
								$calculated_price = floatval( $_product->get_price() );
								echo 'console.log("Mode: WHOLE PIZZA");';
								echo 'console.log("  Starting with base price: ' . esc_js( $calculated_price ) . '");';

								// Add whole pizza toppings
								if ( isset( $cart_item['extra_topping_options'] ) && ! empty( $cart_item['extra_topping_options'] ) ) {
									echo 'console.log("  Extra Toppings:");';
									echo 'console.log("  Toppings array:", ' . json_encode( $cart_item['extra_topping_options'] ) . ');';
									foreach ( $cart_item['extra_topping_options'] as $topping ) {
										if ( isset( $topping['price'] ) ) {
											$topping_price = floatval( $topping['price'] );
											$calculated_price += $topping_price;
											echo 'console.log("    - ' . esc_js( $topping['name'] ) . ': ' . esc_js( $topping_price ) . '");';
										}
									}
								} else {
									echo 'console.log("  No extra toppings");';
								}
							}

							echo 'console.log("Unit Price (per item): ' . esc_js( $calculated_price ) . '");';
							echo 'console.log("Quantity: ' . esc_js( $cart_item['quantity'] ) . '");';

							$item_total = $calculated_price * $cart_item['quantity'];
							echo 'console.log("Line Total (Unit × Qty): ' . esc_js( $item_total ) . '");';
							echo 'console.log("===============================");';
							echo '</script>';

							// Format unit price (not total)
							$unit_price_formatted = wc_price( $calculated_price );

							echo apply_filters( 'woocommerce_widget_cart_item_quantity', '<span class="quantity">' . sprintf( '%s &times; %s', $cart_item['quantity'], $unit_price_formatted ) . '</span>', $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
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
/* Mini Cart Item Container */
.woocommerce-mini-cart-item {
	position: relative;
	padding: 15px 0;
	border-bottom: 1px solid #eee;
}

/* Remove Button - Absolute Position */
.woocommerce-mini-cart-item .remove {
	position: absolute;
	top: 10px;
	right: 0;
	font-size: 24px;
	line-height: 1;
	color: #999;
	z-index: 10;
}

.woocommerce-mini-cart-item .remove:hover {
	color: #dc0000;
}

/* Title Wrapper - Override Flatsome styles */
.mini-cart-title-wrapper {
	display: flex;
	align-items: center;
	gap: 6px;
	margin-bottom: 10px;
	margin-right: 30px;
	line-height: 1.4;
	white-space: normal;
}

/* Paired Pizza Icon */
.paired-pizza-icon {
	width: 20px;
	height: 20px;
	flex-shrink: 0;
}

/* Mini Cart Product Title */
ul.product_list_widget li a:not(.remove) {
	display: inline !important;
	line-height: 1.3 !important;
	margin-bottom: 0 !important;
	overflow: visible !important;
	text-overflow: clip !important;
}

.mini-cart-product-title {
	font-size: 15px;
	font-weight: 600;
	margin: 0;
	line-height: 1.4;
	color: #333;
	display: inline;
}

a .mini-cart-product-title {
	color: #333;
	transition: color 0.3s ease;
}

a:hover .mini-cart-product-title {
	color: #dc0000;
}

/* Two Column Layout - Image Left, Details Right */
.mini-cart-item-content {
	display: flex;
	gap: 15px;
	align-items: flex-start;
}

.mini-cart-item-image {
	flex: 0 0 80px;
	width: 80px;
}

.mini-cart-item-image img {
	width: 100%;
	height: auto;
	border-radius: 4px;
}

.mini-cart-item-details {
	flex: 1;
	min-width: 0;
}

/* Custom Pizza Options Display Styling */
.pizza-option-row {
	display: flex;
	margin: 8px 0;
	font-size: 13px;
	line-height: 1.6;
}

.pizza-option-label {
	flex: 0 0 auto;
	min-width: 30px;
	font-weight: 600;
	color: #333;
	padding-right: 10px;
}

.pizza-option-value {
	flex: 1;
	color: #666;
}

.pizza-option-value .amount {
	font-weight: 500;
}

/* Pizza Half Section Styling */
.pizza-half-section {
	margin: 10px 0;
	font-size: 13px;
}

.pizza-half-title {
	font-weight: 600;
	color: #333;
	margin-bottom: 5px;
	line-height: 1.4;
}

.pizza-topping-row {
	margin: 4px 0;
	padding-left: 10px;
	color: #666;
	line-height: 1.4;
}

.pizza-topping-row .topping-label {
	display: inline-block;
	width: 35px;
	font-weight: 500;
	color: #555;
}

.pizza-topping-row .topping-label-spacer {
	display: inline-block;
	width: 38px;
}

.pizza-topping-row .topping-name {
	color: #666;
}

.pizza-topping-row .topping-price {
	font-weight: 500;
}

.pizza-topping-row .topping-price .amount {
	font-weight: 500;
}

.widget_shopping_cart ul.product_list_widget li {
	position: relative;
    padding: 10px 0 10px;
}

.widget_shopping_cart ul.product_list_widget li h3 {
	color: #000;
}

.widget_shopping_cart ul.product_list_widget li img {
	position: relative;
}

.widget_shopping_cart ul.product_list_widget li .quantity{
	color: #000;
    opacity: 1.0;
    font-weight: 500;
}

.woocommerce-mini-cart__total .amount {
	color: #cd0000;
	font-weight: 600;
}
</style>
