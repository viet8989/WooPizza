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
			<input type="radio" name="delivery_method" value="pickup" id="delivery_pickup" style="margin-right: 8px;">
			<span style="font-weight: 600;">PICKUP - Đến lấy tại cửa hàng</span>
		</label>
		<label class="delivery-option" style="flex: 1; cursor: pointer;">
			<input type="radio" name="delivery_method" value="delivery" id="delivery_delivery" checked style="margin-right: 8px;">
			<span style="font-weight: 600;">DELIVERY - Giao hàng tận nơi</span>
		</label>
	</div>
</div>

<!-- Store Locator (only shown for DELIVERY) -->
<div id="wpsl-container" style="margin-bottom: 30px;">
	<?php echo do_shortcode('[wpsl template="default" map_type="roadmap" auto_locate="false" start_marker="red" store_marker="blue" category="DELIVERY" category_selection=""]'); ?>
</div>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<!-- Hidden field to store delivery method -->
	<input type="hidden" name="selected_delivery_method" id="selected_delivery_method" value="delivery">

	<?php if ( $checkout->get_checkout_fields() ) : ?>

		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

		<div class="col2-set" id="customer_details">
			<div class="col-1">
				<?php do_action( 'woocommerce_checkout_billing' ); ?>
			</div>

			<div class="col-2">
				<?php do_action( 'woocommerce_checkout_shipping' ); ?>
			</div>
		</div>

		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

	<?php endif; ?>

	<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

	<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>

	<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

	<div id="order_review" class="woocommerce-checkout-review-order">
		<?php do_action( 'woocommerce_checkout_order_review' ); ?>
	</div>

	<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

</form>

<style>
.delivery-method-selection {
	background: #f8f8f8;
	padding: 20px;
	border-radius: 5px;
}

.delivery-options label {
	background: white;
	padding: 15px;
	border: 2px solid #ddd;
	border-radius: 5px;
	transition: all 0.3s ease;
	display: flex;
	align-items: center;
}

.delivery-options label:hover {
	border-color: #999;
}

.delivery-options input[type="radio"]:checked + span {
	color: #c44569;
}

.delivery-options label:has(input:checked) {
	border-color: #c44569;
	background: #fff5f7;
}
</style>

<script type="text/javascript">
jQuery(document).ready(function($) {
	// Handle delivery method change
	$('input[name="delivery_method"]').on('change', function() {
		var method = $(this).val();
		$('#selected_delivery_method').val(method);

		if (method === 'pickup') {
			// Hide store locator
			$('#wpsl-container').hide();

			// Update shipping method to free
			$(document.body).trigger('update_checkout');
		} else {
			// Show store locator
			$('#wpsl-container').show();

			// Update checkout
			$(document.body).trigger('update_checkout');
		}
	});

	// Set initial state
	var initialMethod = $('input[name="delivery_method"]:checked').val();
	if (initialMethod === 'pickup') {
		$('#wpsl-container').hide();
	}
});
</script>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
