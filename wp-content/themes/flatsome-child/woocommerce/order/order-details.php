<?php
/**
 * Order details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.6.0
 */

defined( 'ABSPATH' ) || exit;

$order = wc_get_order( $order_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

if ( ! $order ) {
	return;
}

$order_items           = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
$show_purchase_note    = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
$show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
$downloads             = $order->get_downloadable_items();
$show_downloads        = $order->has_downloadable_item() && $order->is_download_permitted();

if ( $show_downloads ) {
	wc_get_template(
		'order/order-downloads.php',
		array(
			'downloads'  => $downloads,
			'show_title' => true,
		)
	);
}
?>
<section class="woocommerce-order-details">
	<?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>

	<h2 class="woocommerce-order-details__title"><?php esc_html_e( 'Order details', 'woocommerce' ); ?></h2>

	<ul class="woocommerce-order-details-items order_details cart_list product_list_widget">
		<?php
		do_action( 'woocommerce_order_details_before_order_table_items', $order );

		foreach ( $order_items as $item_id => $item ) {
			$product = $item->get_product();

			if ( ! $product ) {
				continue;
			}

			$product_id        = $product->get_id();
			$product_name      = $item->get_name();
			$quantity          = $item->get_quantity();
			$thumbnail         = $product->get_image( 'thumbnail' );
			$product_permalink = $product->is_visible() ? $product->get_permalink() : '';

			// Get item metadata
			$item_meta_data = $item->get_meta_data();
			$pizza_halves = null;
			$extra_toppings = array();
			$special_request = '';

			foreach ( $item_meta_data as $meta ) {
				$key = $meta->key;
				$value = $meta->value;

				if ( $key === '_pizza_halves' && ! empty( $value ) ) {
					$pizza_halves = maybe_unserialize( $value );
				} elseif ( $key === '_extra_topping_options' && ! empty( $value ) ) {
					$extra_toppings = maybe_unserialize( $value );
				} elseif ( $key === '_special_request' && ! empty( $value ) ) {
					$special_request = $value;
				}
			}

			// Check if paired pizza
			$is_paired = false;
			$paired_icon = '';
			$display_title = $product_name;

			if ( $pizza_halves && ! empty( $pizza_halves ) ) {
				$left_name = isset( $pizza_halves['left_half']['name'] ) ? $pizza_halves['left_half']['name'] : '';
				$right_name = isset( $pizza_halves['right_half']['name'] ) ? $pizza_halves['right_half']['name'] : '';

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

			// Get base product price
			$base_price = $product->get_regular_price();
			if ( empty( $base_price ) ) {
				$base_price = $product->get_price();
			}
			$base_price_formatted = wc_price( $base_price );
			?>
			<li class="woocommerce-order-details-item order-details-item">

				<!-- Line 1: Image + Name + Base Price -->
				<div class="order-details-first-row">
					<div class="order-details-item-image">
						<?php if ( empty( $product_permalink ) ) : ?>
							<?php echo $thumbnail; ?>
						<?php else : ?>
							<a href="<?php echo esc_url( $product_permalink ); ?>">
								<?php echo $thumbnail; ?>
							</a>
						<?php endif; ?>
					</div>

					<div class="order-details-name-price">
						<div class="order-details-title-wrapper">
							<?php if ( $is_paired ) : ?>
								<?php echo $paired_icon; ?>
							<?php endif; ?>

							<?php if ( empty( $product_permalink ) ) : ?>
								<h3 class="order-details-product-title">
									<?php echo $display_title; ?>
								</h3>
							<?php else : ?>
								<a href="<?php echo esc_url( $product_permalink ); ?>">
									<h3 class="order-details-product-title">
										<?php echo $display_title; ?>
									</h3>
								</a>
							<?php endif; ?>
						</div>

						<?php if ( ! $is_paired ) : ?>
							<div class="order-details-base-price">
								<?php echo $base_price_formatted; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<!-- Pizza customization details -->
				<div class="order-details-item-details">
						<?php
						// Display extra toppings (whole pizza)
						if ( ! empty( $extra_toppings ) ) {
							$topping_index = 0;
							foreach ( $extra_toppings as $topping ) {
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

						// Display pizza halves
						if ( $pizza_halves && ! empty( $pizza_halves ) ) {
							// Display left half
							if ( isset( $pizza_halves['left_half'] ) && ! empty( $pizza_halves['left_half'] ) ) {
								$left = $pizza_halves['left_half'];
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
							if ( isset( $pizza_halves['right_half'] ) && ! empty( $pizza_halves['right_half'] ) ) {
								$right = $pizza_halves['right_half'];
								if ( isset( $right['name'] ) ) {
									$right_name = esc_html( $right['name'] );
									$right_price = isset( $right['price'] ) ? wc_price( $right['price'] ) : '';

									echo '<div class="pizza-half-section">';
									echo '<div class="pizza-half-title">1/2 ' . $right_name . '<span style="float: right;">' . $right_price . '</span></div>';

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

						// Display special request
						if ( ! empty( $special_request ) ) {
							echo '<div class="pizza-option-row">';
							echo '<div class="pizza-option-label">' . __( 'Special Request', 'flatsome' ) . ':</div>';
							echo '<div class="pizza-option-value">' . esc_html( $special_request ) . '</div>';
							echo '</div>';
						}

						// Calculate unit price
						$calculated_price = 0;
						if ( $pizza_halves && ! empty( $pizza_halves ) ) {
							$has_right_half = isset( $pizza_halves['right_half']['product_id'] ) && ! empty( $pizza_halves['right_half']['product_id'] );

							if ( $has_right_half ) {
								// Paired mode
								if ( isset( $pizza_halves['left_half']['price'] ) ) {
									$calculated_price += floatval( $pizza_halves['left_half']['price'] );
								}
								if ( isset( $pizza_halves['left_half']['toppings'] ) ) {
									foreach ( $pizza_halves['left_half']['toppings'] as $topping ) {
										if ( isset( $topping['price'] ) ) {
											$calculated_price += floatval( $topping['price'] );
										}
									}
								}
								if ( isset( $pizza_halves['right_half']['price'] ) ) {
									$calculated_price += floatval( $pizza_halves['right_half']['price'] );
								}
								if ( isset( $pizza_halves['right_half']['toppings'] ) ) {
									foreach ( $pizza_halves['right_half']['toppings'] as $topping ) {
										if ( isset( $topping['price'] ) ) {
											$calculated_price += floatval( $topping['price'] );
										}
									}
								}
							} else {
								// Single half mode
								if ( isset( $pizza_halves['left_half']['price'] ) ) {
									$calculated_price += floatval( $pizza_halves['left_half']['price'] ) * 2;
								}
								if ( isset( $pizza_halves['left_half']['toppings'] ) ) {
									foreach ( $pizza_halves['left_half']['toppings'] as $topping ) {
										if ( isset( $topping['price'] ) ) {
											$calculated_price += floatval( $topping['price'] ) * 2;
										}
									}
								}
							}
						} else {
							// Whole pizza
							$calculated_price = floatval( $product->get_price() );
							if ( ! empty( $extra_toppings ) ) {
								foreach ( $extra_toppings as $topping ) {
									if ( isset( $topping['price'] ) ) {
										$calculated_price += floatval( $topping['price'] );
									}
								}
							}
						}

						$unit_price_formatted = wc_price( $calculated_price );
						$line_total = $calculated_price * $quantity;
						$line_total_formatted = wc_price( $line_total );
						?>
				</div><!-- .order-details-item-details -->

				<!-- Line 2: Price × Quantity = Total -->
				<div class="order-details-quantity-price-row">
					<div class="order-details-quantity-display">
						<span class="unit-price"><?php echo $unit_price_formatted; ?></span>
						<span class="qty-separator">×</span>
						<span class="qty-value"><?php echo esc_html( $quantity ); ?></span>
					</div>
					<div class="order-details-line-total">
						<span class="amount"><?php echo $line_total_formatted; ?></span>
					</div>
				</div>
			</li>
			<?php
		}

		do_action( 'woocommerce_order_details_after_order_table_items', $order );
		?>
	</ul>

	<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
</section>

<?php
/**
 * Action hook fired after the order details.
 *
 * @since 4.4.0
 * @param WC_Order $order Order object.
 */
do_action( 'woocommerce_after_order_details', $order );

if ( $show_customer_details ) {
	wc_get_template( 'order/order-details-customer.php', array( 'order' => $order ) );
}
?>

<style>
/* Order Details Items - List Layout (like mini-cart) */
.woocommerce-order-details-items {
	list-style: none !important;
	padding: 0 !important;
	margin: 0 0 20px 0 !important;
}

.order-details-item {
	position: relative !important;
	padding: 10px !important;
	margin-bottom: 10px !important;
	background-color: #fff !important;
	border-radius: 5px !important;
	overflow: visible !important;
}

/* Title Wrapper */
.order-details-title-wrapper {
	display: flex !important;
	align-items: center !important;
	gap: 8px !important;
	margin-bottom: 12px !important;
	line-height: 1.4 !important;
	white-space: normal !important;
}

.order-details-title-wrapper > a {
	padding-top: 15px !important;
	width: 100% !important;
}

/* Paired Pizza Icon */
.order-details-title-wrapper .paired-pizza-icon {
	width: 22px !important;
	height: 22px !important;
	flex-shrink: 0 !important;
	vertical-align: middle !important;
}

/* Order Details Product Title */
.order-details-item a:not(.remove) {
	display: inline !important;
	line-height: 1.4 !important;
	margin-bottom: 0 !important;
	overflow: visible !important;
	text-overflow: clip !important;
}

.order-details-product-title {
	font-size: 16px !important;
	font-weight: 700 !important;
	margin: 0 !important;
	line-height: 1.4 !important;
	color: #000 !important;
	display: inline !important;
}

a .order-details-product-title {
	color: #000 !important;
	transition: color 0.3s ease !important;
}

a:hover .order-details-product-title {
	color: #dc0000 !important;
}

/* Line 1: Image + Name + Price Layout */
.order-details-first-row {
	display: flex !important;
	gap: 12px !important;
	align-items: flex-start !important;
	width: 100% !important;
	margin-bottom: 8px !important;
}

.order-details-first-row .order-details-item-image {
	flex: 0 0 90px !important;
	width: 90px !important;
	max-width: 90px !important;
	display: block !important;
}

.order-details-first-row .order-details-item-image img {
	width: 100% !important;
	max-width: 90px !important;
	height: auto !important;
	border-radius: 6px !important;
	box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
	display: block !important;
}

.order-details-name-price {
	flex: 1 !important;
	display: flex !important;
	justify-content: space-between !important;
	align-items: flex-start !important;
	min-width: 0 !important;
}

.order-details-base-price {
	font-size: 16px !important;
	font-weight: 700 !important;
	color: #000 !important;
	white-space: nowrap !important;
	margin-left: 8px !important;
}

/* Pizza Details Section (below image+name) */
.order-details-item-details {
	width: 100% !important;
	padding-left: 0 !important;
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
.order-details-item .pizza-topping-row {
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

.order-details-item .pizza-topping-row .topping-label {
	display: inline-block !important;
	min-width: 35px !important;
	max-width: 35px !important;
	font-weight: 600 !important;
	color: #000 !important;
	flex-shrink: 0 !important;
	flex-grow: 0 !important;
}

.order-details-item .pizza-topping-row .topping-label-spacer {
	display: inline-block !important;
	min-width: 35px !important;
	max-width: 35px !important;
	flex-shrink: 0 !important;
	flex-grow: 0 !important;
}

.order-details-item .pizza-topping-row .topping-details {
	flex: 1 !important;
	display: flex !important;
	justify-content: space-between !important;
	align-items: center !important;
	min-width: 0 !important;
	gap: 8px !important;
}

.order-details-item .pizza-topping-row .topping-name {
	color: #555 !important;
	flex: 1 !important;
	white-space: nowrap !important;
	overflow: hidden !important;
	text-overflow: ellipsis !important;
}

.order-details-item .pizza-topping-row .topping-price {
	font-weight: 600 !important;
	color: #000 !important;
	flex-shrink: 0 !important;
	white-space: nowrap !important;
}

.order-details-item .pizza-topping-row .topping-price .amount {
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

/* Price × Quantity Row */
.order-details-quantity-price-row {
	display: flex !important;
	justify-content: space-between !important;
	align-items: center !important;
	margin-top: 12px !important;
	padding-top: 8px !important;
	border-top: 1px solid #f0f0f0 !important;
}

.order-details-quantity-display {
	display: flex !important;
	align-items: center !important;
	gap: 6px !important;
	font-size: 14px !important;
	font-weight: 600 !important;
	color: #000 !important;
}

.order-details-quantity-display .unit-price {
	color: #000 !important;
	font-weight: 600 !important;
}

.order-details-quantity-display .qty-separator {
	color: #666 !important;
	font-weight: 400 !important;
	margin: 0 2px !important;
}

.order-details-quantity-display .qty-value {
	color: #000 !important;
	font-weight: 700 !important;
}

.order-details-line-total {
	font-size: 16px !important;
	font-weight: 700 !important;
	color: #000 !important;
}

.order-details-line-total .amount {
	color: #000 !important;
	font-weight: 700 !important;
}
</style>
