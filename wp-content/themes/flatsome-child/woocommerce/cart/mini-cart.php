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

					<?php if ( empty( $product_permalink ) ) : ?>
						<h3 class="mini-cart-product-title"><?php echo wp_kses_post( $product_name ); ?></h3>
					<?php else : ?>
						<a href="<?php echo esc_url( $product_permalink ); ?>">
							<h3 class="mini-cart-product-title"><?php echo wp_kses_post( $product_name ); ?></h3>
						</a>
					<?php endif; ?>

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
								$left_display = esc_html( $left['name'] );

								// Add left half toppings
								if ( isset( $left['toppings'] ) && ! empty( $left['toppings'] ) ) {
									$left_topping_names = array();
									foreach ( $left['toppings'] as $topping ) {
										if ( isset( $topping['name'] ) && isset( $topping['price'] ) ) {
											$left_topping_names[] = sprintf(
												'%s %s',
												esc_html( $topping['name'] ),
												wc_price( $topping['price'] )
											);
										}
									}
									if ( ! empty( $left_topping_names ) ) {
										$left_display .= '<br>Add ' . implode( '<br>', $left_topping_names );
									}
								}

								echo '<div class="pizza-option-row">';
								echo '<div class="pizza-option-label">' . strtoupper( $left['name'] ) . ':</div>';
								echo '<div class="pizza-option-value">' . $left_display . '</div>';
								echo '</div>';
							}
						}

						// Display right half
						if ( isset( $halves['right_half'] ) && ! empty( $halves['right_half'] ) ) {
							$right = $halves['right_half'];
							if ( isset( $right['name'] ) ) {
								$right_display = esc_html( $right['name'] );

								// Add right half toppings
								if ( isset( $right['toppings'] ) && ! empty( $right['toppings'] ) ) {
									$right_topping_names = array();
									foreach ( $right['toppings'] as $topping ) {
										if ( isset( $topping['name'] ) && isset( $topping['price'] ) ) {
											$right_topping_names[] = sprintf(
												'%s %s',
												esc_html( $topping['name'] ),
												wc_price( $topping['price'] )
											);
										}
									}
									if ( ! empty( $right_topping_names ) ) {
										$right_display .= '<br>Add ' . implode( '<br>', $right_topping_names );
									}
								}

								echo '<div class="pizza-option-row">';
								echo '<div class="pizza-option-label">' . strtoupper( $right['name'] ) . ':</div>';
								echo '<div class="pizza-option-value">' . $right_display . '</div>';
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

							<?php echo apply_filters( 'woocommerce_widget_cart_item_quantity', '<span class="quantity">' . sprintf( '%s &times; %s', $cart_item['quantity'], $product_price ) . '</span>', $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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

/* Mini Cart Product Title - Full Width */
.mini-cart-product-title {
	font-size: 15px;
	font-weight: 600;
	margin: 0 30px 10px 0;
	line-height: 1.4;
	color: #333;
	width: 100%;
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
</style>
