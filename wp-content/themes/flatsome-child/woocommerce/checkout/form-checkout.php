<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

?>

<!-- Delivery Method Selection -->
<div class="delivery-method-selection" style="margin-bottom: 30px;">
	<h3>Chọn phương thức nhận hàng</h3>
	<div class="delivery-options" style="display: flex; gap: 20px; margin-top: 15px;">
		<label class="delivery-option" style="flex: 1; cursor: pointer;">
			<input type="radio" name="delivery_method" value="delivery" id="delivery_delivery">
			<span class="delivery-icon">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M16 3H1V16H16V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M16 8H20L23 11V16H16V8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<circle cx="5.5" cy="19.5" r="2.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<circle cx="18.5" cy="19.5" r="2.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</span>
			<span class="delivery-text" style="font-weight: 600;">DELIVERY - Giao hàng tận nơi</span>
		</label>
		<label class="delivery-option" style="flex: 1; cursor: pointer;">
			<input type="radio" name="delivery_method" value="pickup" id="delivery_pickup">
			<span class="delivery-icon">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</span>
			<span class="delivery-text" style="font-weight: 600;">PICKUP - Đến lấy tại cửa hàng</span>
		</label>
	</div>
</div>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<!-- Hidden field to store delivery method -->
	<input type="hidden" name="selected_delivery_method" id="selected_delivery_method" value="delivery">

	<div class="row pt-0">
		<div class="large-7 col">
			<?php if ( $checkout->get_checkout_fields() ) : ?>

				<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

				<div id="customer_details">
					<div class="clear">
						<?php do_action( 'woocommerce_checkout_billing' ); ?>
					</div>

					<div class="clear">
						<?php do_action( 'woocommerce_checkout_shipping' ); ?>
					</div>
				</div>

				<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

			<?php endif; ?>
		</div>

		<div class="large-5 col">
			<?php if ( function_exists( 'flatsome_sticky_column_open' ) ) { flatsome_sticky_column_open( 'checkout_sticky_sidebar' ); } ?>

				<div class="col-inner">
					<div class="checkout-sidebar sm-touch-scroll">

						<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

						<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>

						<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

						<div id="order_review" class="woocommerce-checkout-review-order">
							<?php do_action( 'woocommerce_checkout_order_review' ); ?>
						</div>
					</div>
				</div>

			<?php if ( function_exists( 'flatsome_sticky_column_close' ) ) { flatsome_sticky_column_close( 'checkout_sticky_sidebar' ); } ?>
		</div>
	</div>

</form>

<style>

.delivery-method-selection {
	background: #f8f8f8;
	padding: 20px;
	border-radius: 5px;
}

.delivery-options {
	display: flex;
	gap: 20px;
	margin-top: 15px;
}

.delivery-options label {
	background: white;
	padding: 20px;
	border: 2px solid #ddd;
	border-radius: 8px;
	transition: all 0.3s ease;
	display: flex;
	align-items: center;
	gap: 12px;
	position: relative;
	flex: 1;
}

/* Hide the radio button */
.delivery-options input[type="radio"] {
	position: absolute;
	opacity: 0;
	pointer-events: none;
}

/* Icon styling */
.delivery-icon {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 48px;
	height: 48px;
	border-radius: 50%;
	background: #f0f0f0;
	color: #666;
	transition: all 0.3s ease;
	flex-shrink: 0;
}

.delivery-icon svg {
	width: 24px;
	height: 24px;
}

/* Hover state */
.delivery-options label:hover {
	border-color: #999;
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.delivery-options label:hover .delivery-icon {
	background: #e0e0e0;
}

/* Active/Selected state */
.delivery-options input[type="radio"]:checked ~ .delivery-icon {
	background: #c44569;
	color: white;
}

.delivery-options input[type="radio"]:checked ~ .delivery-text {
	color: #c44569;
}

.delivery-options label:has(input:checked) {
	border-color: #c44569;
	background: #fff5f7;
	box-shadow: 0 4px 16px rgba(196, 69, 105, 0.2);
}

/* Text styling */
.delivery-text {
	font-weight: 600;
	font-size: 15px;
	color: #333;
	transition: color 0.3s ease;
}

.checkout-sidebar.sm-touch-scroll {
	border: 1px solid #d3d7d3;
    border-radius: 10px;
    padding: 20px;
}

/* Responsive */
@media (max-width: 768px) {
	.delivery-options {
		flex-direction: column;
	}

	.delivery-options label {
		width: 100%;
	}
}
</style>

<script type="text/javascript">
jQuery(document).ready(function($) {
	// Handle delivery method change
	$('input[name="delivery_method"]').on('change', function() {
		var method = $(this).val();
		if (method === 'pickup') {
			$('.shipping__table th').text('Local pickup');
		} else {
			// Set shipping method to flat rate (or your default shipping method)
			$('.shipping__table th').text('Shipping');	
		}

		// Update hidden field
		$('#selected_delivery_method').val(method);

		// Update shipping method
		$(document.body).trigger('update_checkout');
	});

	// Set initial state
	$('input[name="delivery_method"]').eq(0).click();
	// var initialCategory = (initialMethod === 'pickup') ? 'PICKUP' : 'DELIVERY';
});
</script>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

