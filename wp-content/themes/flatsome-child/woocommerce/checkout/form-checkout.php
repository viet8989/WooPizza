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



<style>
.available-stores {
	background: #f8f8f8;
	padding: 20px;
	border-radius: 8px;
}

.available-stores .loading-stores {
	text-align: center;
	color: #999;
	padding: 20px;
}

.available-stores .store-item {
	background: white;
	border: 2px solid #e0e0e0;
	border-radius: 8px;
	padding: 15px;
	margin-bottom: 10px;
	cursor: pointer;
	transition: all 0.3s ease;
	position: relative;
}

.available-stores .store-item:hover {
	border-color: #c44569;
	background: #fff5f7;
	transform: translateX(5px);
}

.available-stores .store-item.selected {
	border-color: #c44569;
	background: #fff5f7;
	box-shadow: 0 4px 16px rgba(196, 69, 105, 0.2);
}

.available-stores .store-item input[type="radio"] {
	position: absolute;
	opacity: 0;
	pointer-events: none;
}

.available-stores .store-name {
	font-weight: 600;
	font-size: 16px;
	color: #333;
	margin-bottom: 5px;
}

.available-stores .store-item.selected .store-name {
	color: #c44569;
}

.available-stores .store-address {
	font-size: 14px;
	color: #666;
	margin-bottom: 5px;
}

.available-stores .store-hours {
	font-size: 13px;
	color: #28a745;
	font-weight: 500;
}

.available-stores .no-stores {
	text-align: center;
	padding: 30px;
	color: #999;
}

/* Hide WooCommerce default shipping methods table since we use custom selection */
.shipping-row .shipping__table {
	display: none !important;
}
</style>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<!-- Hidden field to store delivery method -->
	<input type="hidden" name="selected_delivery_method" id="selected_delivery_method" value="delivery">
	<!-- Hidden field for store category filter (43=DELIVERY, 42=PICKUP) -->
	<input type="hidden" id="wpsl-category-filter" name="wpsl-category" value="43">

	<div class="row pt-0">
		<div class="large-7 col">
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

			<!-- Store Locator Section -->
			<div class="store-locator-section" style="margin-bottom: 30px;">
				<h3 style="margin-left: 20px">Chọn cửa hàng</h3>
				<div id="available-stores-list" class="available-stores">
					<p class="loading-stores">Đang tải danh sách cửa hàng...</p>
				</div>
			</div>

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

						<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
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
	var selectedStoreId = null;
	var loadedWards = []; // Store loaded wards to persist across checkout updates

	// Function to load available stores
	function loadAvailableStores(method) {
		// Category IDs: 42=PICKUP, 43=DELIVERY
		var categoryId = (method === 'pickup') ? '42' : '43';
		var categoryName = (method === 'pickup') ? 'PICKUP' : 'DELIVERY';

		console.log('Loading stores for: ' + categoryName + ' (ID: ' + categoryId + ') at ' + new Date().toLocaleTimeString());

		// Show loading message
		$('#available-stores-list').html('<p class="loading-stores">Đang tải danh sách cửa hàng...</p>');

		// Make AJAX request to get stores
		$.ajax({
			url: '<?php echo admin_url('admin-ajax.php'); ?>',
			type: 'GET',
			data: {
				action: 'store_search',
				filter: categoryId,
				autoload: 1
			},
			success: function(response) {
				displayStores(response, categoryName);
			},
			error: function() {
				$('#available-stores-list').html('<p class="no-stores">Không thể tải danh sách cửa hàng. Vui lòng thử lại.</p>');
			}
		});
	}

	// Function to display stores
	function displayStores(stores, categoryName) {
		console.log('📍 displayStores() called - Category: ' + categoryName + ', Stores: ' + (stores ? stores.length : 0));
		var html = '';

		if (!stores || stores.length === 0) {
			html = '<p class="no-stores">Hiện không có cửa hàng nào đang mở cửa cho ' + categoryName + '.</p>';
		} else {
			stores.forEach(function(store, index) {
				var isSelected = (index === 0 && !selectedStoreId) || (store.id == selectedStoreId);
				var selectedClass = isSelected ? 'selected' : '';

				if (isSelected) {
					selectedStoreId = store.id;
					console.log('📍 Auto-selected store: ' + store.id + ' (' + store.store + ')');
				}

				html += '<div class="store-item ' + selectedClass + '" data-store-id="' + store.id + '">';
				html += '  <input type="radio" name="selected_store" value="' + store.id + '" ' + (isSelected ? 'checked' : '') + '>';
				html += '  <div class="store-name">' + store.store + '</div>';
				html += '  <div class="store-address">' + store.address + ', ' + store.city + '</div>';
				if (store.hours) {
					html += '  <div class="store-hours">🕒 Đang mở cửa</div>';
				}
				html += '</div>';
			});
		}

		$('#available-stores-list').html(html);
		console.log('📍 Stores HTML rendered');

		// Handle store selection
		$('.store-item').on('click', function() {
			var storeId = $(this).data('store-id');
			selectedStoreId = storeId;

			$('.store-item').removeClass('selected');
			$(this).addClass('selected');
			$(this).find('input[type="radio"]').prop('checked', true);

			console.log('📍 Store clicked: ' + storeId);

			// Load available wards for this store
			loadStoreWards(storeId);
		});

		// Load wards for the first selected store
		if (selectedStoreId) {
			console.log('📍 About to load wards for selected store: ' + selectedStoreId);
			loadStoreWards(selectedStoreId);
		} else {
			console.log('📍 No store selected, skipping ward loading');
		}
	}

	// Function to load available wards for selected store
	function loadStoreWards(storeId) {
		console.log('🔄 loadStoreWards() called - Store ID: ' + storeId);
		console.log('🔄 Making AJAX request to: <?php echo admin_url('admin-ajax.php'); ?>');
		console.log('🔄 AJAX data: action=get_store_wards, store_id=' + storeId);

		$.ajax({
			url: '<?php echo admin_url('admin-ajax.php'); ?>',
			type: 'GET',
			data: {
				action: 'get_store_wards',
				store_id: storeId
			},
			beforeSend: function() {
				console.log('🔄 AJAX request sent...');
			},
			success: function(response) {
				console.log('✅ AJAX response received:', response);
				if (response.success && response.data.wards) {
					console.log('✅ Wards data found:', response.data.wards.length + ' wards');
					updateWardDropdown(response.data.wards);
				} else {
					console.error('❌ Failed to load wards:', response);
				}
			},
			error: function(xhr, status, error) {
				console.error('❌ AJAX error loading wards:', error);
				console.error('❌ XHR:', xhr);
				console.error('❌ Status:', status);
			}
		});
	}

	// Function to update ward dropdown with available wards
	function updateWardDropdown(wards) {
		var $wardSelect = $('#billing_city');

		if ($wardSelect.length === 0) {
			console.warn('Ward dropdown not found');
			return;
		}

		// Store wards globally to restore after WooCommerce updates
		loadedWards = wards;

		// Clear existing options except the first placeholder
		$wardSelect.find('option:not(:first)').remove();

		// Add new ward options
		wards.forEach(function(ward) {
			$wardSelect.append(
				$('<option></option>')
					.attr('value', ward.id)
					.text(ward.name)
					.data('fee', ward.fee)
			);
		});

		// Don't auto-select - let user choose manually
		console.log('Loaded ' + wards.length + ' ward(s) for selection');

		// Trigger change event to update WooCommerce
		$wardSelect.trigger('change');

		console.log('Ward dropdown updated with ' + wards.length + ' wards');
	}

	// Function to update shipping fee display
	function updateShippingFeeDisplay() {
		var deliveryMethod = $('input[name="delivery_method"]:checked').val();
		var $feeRow = $('.shipping-fee-row');

		if ($feeRow.length === 0) {
			console.log('Shipping fee row not found');
			return;
		}

		var $feeLabel = $feeRow.find('.totals-label');
		var $feeValue = $feeRow.find('.totals-value');
		var fee = 0;

		if (deliveryMethod === 'pickup') {
			// PICKUP - Show "Pickup" with 0 fee
			$feeLabel.text('Pickup');
			$feeValue.html('<span class="woocommerce-Price-amount amount"><bdi>0&nbsp;<span class="woocommerce-Price-currencySymbol">₫</span></bdi></span>');
			console.log('Updated to Pickup mode');
			fee = 0;
		} else {
			// DELIVERY - Show "Shipping Fee" with calculated fee
			$feeLabel.text('Shipping Fee');

			var selectedWard = $('#billing_city').val();
			var selectedWardOption = $('#billing_city option:selected');
			fee = selectedWardOption.data('fee') || 0;

			console.log('Ward selected:', selectedWard, 'Fee:', fee);

			// Format fee as Vietnamese currency
			var formattedFee = new Intl.NumberFormat('vi-VN').format(fee);
			$feeValue.html('<span class="woocommerce-Price-amount amount"><bdi>' + formattedFee + '&nbsp;<span class="woocommerce-Price-currencySymbol">₫</span></bdi></span>');
			console.log('Updated to Delivery mode, fee:', formattedFee);
		}

		// Update the Total to include the fee
		updateTotalDisplay(fee);
	}

	// Function to update Total display to include shipping fee
	function updateTotalDisplay(shippingFee) {
		var $totalRow = $('.total-row');
		var $totalValue = $totalRow.find('.totals-value strong');

		if ($totalValue.length === 0) {
			console.log('Total row not found');
			return;
		}

		// Get subtotal (cart contents without tax or fees)
		var $subtotalRow = $('.totals-row:not(.shipping-fee-row):not(.total-row)').first();
		var subtotalText = $subtotalRow.find('.totals-value').text().replace(/[^\d]/g, '');
		var subtotal = parseInt(subtotalText) || 0;

		// Calculate tax (8% VAT)
		var tax = Math.round(subtotal * 0.08);

		// Calculate new total: subtotal + tax + shipping fee
		var newTotal = subtotal + tax + shippingFee;

		console.log('Total update - Subtotal:', subtotal, 'Tax (8%):', tax, 'Fee:', shippingFee, 'New Total:', newTotal);

		// Format and update total
		var formattedTotal = new Intl.NumberFormat('vi-VN').format(newTotal);
		$totalValue.html('<span class="woocommerce-Price-amount amount"><bdi>' + formattedTotal + '&nbsp;<span class="woocommerce-Price-currencySymbol">₫</span></bdi></span>');
	}

	// Handle delivery method change
	$('input[name="delivery_method"]').on('change', function() {
		var method = $(this).val();

		// Update shipping label
		if (method === 'pickup') {
			$('.shipping__table th').text('Local pickup');
		} else {
			$('.shipping__table th').text('Shipping');
		}

		// Update hidden field
		$('#selected_delivery_method').val(method);

		// Update field requirements based on delivery method
		if (method === 'pickup') {
			// PICKUP: Ward and address are not required
			$('#billing_city').prop('required', false).removeClass('validate-required');
			$('#billing_address_1').prop('required', false).removeClass('validate-required');
			$('#billing_city_field').removeClass('validate-required');
			$('#billing_address_1_field').removeClass('validate-required');
		} else {
			// DELIVERY: Ward and address are required
			$('#billing_city').prop('required', true).addClass('validate-required');
			$('#billing_address_1').prop('required', true).addClass('validate-required');
			$('#billing_city_field').addClass('validate-required');
			$('#billing_address_1_field').addClass('validate-required');
		}

		// Load stores for the selected method
		loadAvailableStores(method);

		// Update shipping fee display immediately
		updateShippingFeeDisplay();

		// Trigger WooCommerce to recalculate fees and totals
		$(document.body).trigger('update_checkout');
	});

	// Handle ward/commune change
	$(document).on('change', '#billing_city', function() {
		// Update shipping fee display immediately
		updateShippingFeeDisplay();

		// Trigger checkout update to recalculate fees
		$(document.body).trigger('update_checkout');
	});

	// Update shipping fee display after WooCommerce updates
	// Also restore ward options that were cleared by WooCommerce re-rendering
	$(document.body).on('updated_checkout', function() {
		console.log('🔄 WooCommerce updated_checkout fired');

		// Restore previously loaded wards if any
		if (loadedWards.length > 0) {
			console.log('🔄 Restoring ' + loadedWards.length + ' wards after checkout update');
			var $wardSelect = $('#billing_city');
			var selectedWard = $wardSelect.val(); // Save current selection

			// Clear and repopulate
			$wardSelect.find('option:not(:first)').remove();
			loadedWards.forEach(function(ward) {
				$wardSelect.append(
					$('<option></option>')
						.attr('value', ward.id)
						.text(ward.name)
						.data('fee', ward.fee)
				);
			});

			// Restore previous selection if any (don't auto-select)
			if (selectedWard) {
				$wardSelect.val(selectedWard);
			}
		}

		// Enable billing_state to ensure it's included in form submission
		$('#billing_state').prop('disabled', false);

		updateShippingFeeDisplay();
	});

	// Set initial state to DELIVERY and load stores
	setTimeout(function() {
		$('input[name="delivery_method"][value="delivery"]').prop('checked', true);
		// Enable billing_state immediately so it's never disabled during form submission
		$('#billing_state').prop('disabled', false);

		// Trigger change event on pre-filled billing fields to update WooCommerce validation
		// This ensures pre-filled values from logged-in users are recognized
		$('#billing_address_1').trigger('change');
		$('#billing_last_name').trigger('change');
		$('#billing_phone').trigger('change');
		$('#billing_email').trigger('change');

		loadAvailableStores('delivery');
	}, 500);

	// Load wards for pre-selected store after stores are loaded
	setTimeout(function() {
		var preSelectedStore = $('input[name="selected_store"]:checked').val();
		console.log('Checking for pre-selected store:', preSelectedStore);
		if (preSelectedStore) {
			console.log('Loading wards for pre-selected store:', preSelectedStore);
			loadStoreWards(preSelectedStore);
		} else {
			console.log('No pre-selected store found');
		}
	}, 2000);

	// Validate and prepare form before submission
	$('form.checkout').on('checkout_place_order', function() {
		// Enable billing_state before form submission so value gets sent
		$('#billing_state').prop('disabled', false);

		// Validate store selection
		var selectedStore = $('input[name="selected_store"]:checked').val();
		if (!selectedStore) {
			alert('Please select a store.');
			return false;
		}

		// Validate ward selection for delivery method
		var deliveryMethod = $('input[name="delivery_method"]:checked').val();
		if (deliveryMethod === 'delivery') {
			var selectedWard = $('#billing_city').val();
			if (!selectedWard) {
				alert('Please select a Ward/Commune.');
				return false;
			}
		}

		console.log('Form validation passed - Store:', selectedStore, 'Method:', deliveryMethod);
		return true;
	});
});
</script>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>