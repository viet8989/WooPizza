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
			<input type="radio" name="delivery_method" value="pickup" id="delivery_pickup">
			<span class="delivery-icon">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</span>
			<span class="delivery-text" style="font-weight: 600;">PICKUP - Đến lấy tại cửa hàng</span>
		</label>
		<label class="delivery-option" style="flex: 1; cursor: pointer;">
			<input type="radio" name="delivery_method" value="delivery" id="delivery_delivery" checked>
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
	</div>
</div>

<!-- Store Locator (custom checkout layout) -->
<div id="wpsl-container" style="margin-bottom: 30px;">
	<?php echo do_shortcode('[wpsl template="checkout" map_type="roadmap" auto_locate="false" start_marker="red" store_marker="blue" category="DELIVERY" category_selection=""]'); ?>
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
	console.log('═══════════════════════════════════════════════════════');
	console.log('🚀 WPSL CHECKOUT DEBUG - Page Ready');
	console.log('═══════════════════════════════════════════════════════');

	// Check if WPSL is loaded
	console.log('📋 WPSL Objects Available:');
	console.log('  - wpslMap:', typeof wpslMap !== 'undefined' ? 'YES ✓' : 'NO ✗');
	console.log('  - wpslSettings:', typeof wpslSettings !== 'undefined' ? 'YES ✓' : 'NO ✗');
	console.log('  - google:', typeof google !== 'undefined' ? 'YES ✓' : 'NO ✗ (Google Maps not loaded)');

	// Check DOM elements
	console.log('📋 DOM Elements:');
	console.log('  - #wpsl-container:', $('#wpsl-container').length ? 'YES ✓' : 'NO ✗');
	console.log('  - #wpsl-search-btn:', $('#wpsl-search-btn').length ? 'YES ✓' : 'NO ✗');
	console.log('  - #wpsl-category-filter:', $('#wpsl-category-filter').length ? 'YES ✓' : 'NO ✗');
	console.log('  - delivery method radios:', $('input[name="delivery_method"]').length);

	// Google Maps error handling
	if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
		console.warn('⚠️ WARNING: Google Maps API not loaded!');
		console.log('   This is expected if API key is not configured yet.');
		console.log('   Store list filtering will still work, only map will be affected.');
	}

	// Force initialize wpslMap if it doesn't exist (fallback for when Google Maps fails)
	if (typeof wpslMap === 'undefined') {
		console.warn('⚠️ wpslMap not initialized, creating fallback...');
		window.wpslMap = [{ storeData: [] }];
	}

	// Function to update WPSL category filter
	function updateWPSLCategory(category) {
		console.log('───────────────────────────────────────────────────────');
		console.log('🔄 updateWPSLCategory() called with:', category);

		// Update hidden category filter field
		var filterInput = $('#wpsl-category-filter');
		if (filterInput.length) {
			filterInput.val(category);
			console.log('✓ Updated #wpsl-category-filter value to:', category);
		} else {
			console.error('✗ #wpsl-category-filter not found!');
		}

		// Check wpslMap
		if (typeof wpslMap !== 'undefined' && wpslMap.length > 0) {
			console.log('✓ wpslMap exists, clearing store data');
			wpslMap[0].storeData = [];

			// Update the category filter in wpslSettings
			if (typeof wpslSettings !== 'undefined') {
				wpslSettings.categoryIds = category;
				console.log('✓ Updated wpslSettings.categoryIds to:', category);
			}

			// Trigger WPSL search directly via AJAX
			console.log('✓ Triggering WPSL search via AJAX...');

			// Get current search parameters safely
			var lat = 0;
			var lng = 0;

			try {
				if (wpslMap[0] && wpslMap[0].settings && wpslMap[0].settings.startLatLng) {
					lat = wpslMap[0].settings.startLatLng.lat || 0;
					lng = wpslMap[0].settings.startLatLng.lng || 0;
				}
			} catch(e) {
				console.warn('Could not get coordinates from wpslMap, using 0,0');
			}

			var searchData = {
				action: 'store_search',
				lat: lat,
				lng: lng,
				max_results: wpslSettings.maxResults || 25,
				search_radius: wpslSettings.searchRadius || 50,
				autoload: 1,
				wpsl_custom_filter: category,
				skip_cache: 1
			};

			console.log('Search data:', searchData);

			// Perform the search
			$.get(wpslSettings.ajaxurl, searchData, function(response) {
				console.log('✓ Manual AJAX search completed, response:', response);
				// The ajaxComplete handler will render the stores automatically
			}).fail(function(xhr, status, error) {
				console.error('✗ AJAX search failed:', status, error);
			});
		} else {
			console.warn('⚠️ wpslMap not available, attempting manual search trigger');
			// Try to trigger search anyway
			if ($('#wpsl-search-btn').length) {
				console.log('✓ Triggering search button click (fallback)...');
				$('#wpsl-search-btn').trigger('click');
			}
		}
		console.log('───────────────────────────────────────────────────────');
	}

	// Handle delivery method change
	$('input[name="delivery_method"]').on('change', function() {
		console.log('═══════════════════════════════════════════════════════');
		console.log('🔄 DELIVERY METHOD CHANGED EVENT');

		var method = $(this).val();
		var category = (method === 'pickup') ? 'PICKUP' : 'DELIVERY';

		console.log('  Selected method:', method);
		console.log('  Category to filter:', category);

		// Update hidden field
		$('#selected_delivery_method').val(method);
		console.log('  Updated #selected_delivery_method to:', method);

		// Update WPSL category filter
		updateWPSLCategory(category);

		// Update shipping method
		console.log('  Triggering WooCommerce checkout update...');
		$(document.body).trigger('update_checkout');

		console.log('═══════════════════════════════════════════════════════');
	});

	// Set initial state
	var initialMethod = $('input[name="delivery_method"]:checked').val();
	var initialCategory = (initialMethod === 'pickup') ? 'PICKUP' : 'DELIVERY';

	console.log('📌 Initial State:');
	console.log('  - Method:', initialMethod);
	console.log('  - Category:', initialCategory);

	// Set initial category
	if ($('#wpsl-category-filter').length) {
		$('#wpsl-category-filter').val(initialCategory);
		console.log('  ✓ Set initial category filter to:', initialCategory);
	}

	// Override WPSL category filter when search is triggered
	$(document).on('click', '#wpsl-search-btn', function(e) {
		console.log('───────────────────────────────────────────────────────');
		console.log('🔍 SEARCH BUTTON CLICKED');

		var selectedMethod = $('input[name="delivery_method"]:checked').val();
		var category = (selectedMethod === 'pickup') ? 'PICKUP' : 'DELIVERY';

		console.log('  Selected method:', selectedMethod);
		console.log('  Category:', category);

		// Update wpslSettings before search
		if (typeof wpslSettings !== 'undefined') {
			wpslSettings.categoryIds = category;
			console.log('  ✓ Updated wpslSettings.categoryIds to:', category);
		} else {
			console.warn('  ⚠️ wpslSettings not available');
		}

		console.log('───────────────────────────────────────────────────────');
	});

	// Intercept WPSL AJAX request to add category filter
	$(document).ajaxSend(function(event, jqxhr, settings) {
		if (settings.url && settings.url.indexOf('store_search') !== -1) {
			console.log('═══════════════════════════════════════════════════════');
			console.log('📡 AJAX REQUEST INTERCEPTED (store_search)');

			var selectedMethod = $('input[name="delivery_method"]:checked').val();
			var category = (selectedMethod === 'pickup') ? 'PICKUP' : 'DELIVERY';

			console.log('  Method:', selectedMethod);
			console.log('  Category:', category);
			console.log('  Original URL:', settings.url);
			console.log('  Original Data:', settings.data);

			// Use custom parameter 'wpsl_custom_filter' instead of 'filter'
			// This prevents WPSL from trying to filter natively
			if (settings.url.indexOf('?') !== -1) {
				settings.url += '&wpsl_custom_filter=' + category + '&skip_cache=1';
			} else {
				settings.url += '?wpsl_custom_filter=' + category + '&skip_cache=1';
			}

			console.log('  ✓ Modified URL:', settings.url);

			// Also add to data if it exists
			if (settings.data) {
				settings.data += '&wpsl_custom_filter=' + category + '&skip_cache=1';
				console.log('  ✓ Modified Data:', settings.data);
			} else {
				console.warn('  ⚠️ No data property in AJAX settings');
			}

			console.log('═══════════════════════════════════════════════════════');
		}
	});

	// Monitor AJAX responses and manually render store list
	$(document).ajaxComplete(function(event, xhr, settings) {
		if (settings.url && settings.url.indexOf('store_search') !== -1) {
			console.log('═══════════════════════════════════════════════════════');
			console.log('📨 AJAX RESPONSE RECEIVED (store_search)');
			console.log('  Status:', xhr.status);
			console.log('  Response:', xhr.responseText.substring(0, 200) + '...');

			try {
				var response = JSON.parse(xhr.responseText);
				console.log('  Stores returned:', response.length || 0);

				if (response.length) {
					// Manually render store list
					var storeListHtml = '';
					response.forEach(function(store, index) {
						console.log('    Store ' + (index + 1) + ':', store.store || store.title);

						storeListHtml += '<li data-store-id="' + store.id + '">';
						storeListHtml += '<div class="wpsl-store-location">';
						storeListHtml += '<p><strong>' + store.store + '</strong></p>';
						if (store.address) storeListHtml += '<p>' + store.address + '</p>';
						if (store.city) storeListHtml += '<p>' + store.city + ', ' + (store.zip || '') + '</p>';
						if (store.phone) storeListHtml += '<p>Phone: ' + store.phone + '</p>';
						if (store.distance) storeListHtml += '<p><small>Distance: ' + parseFloat(store.distance).toFixed(1) + ' km</small></p>';
						storeListHtml += '</div>';
						storeListHtml += '</li>';
					});

					// Update store list
					$('#wpsl-stores ul').html(storeListHtml);
					$('#wpsl-stores').removeClass('wpsl-not-loaded');

					console.log('  ✓ Store list manually rendered with ' + response.length + ' stores');
				} else {
					$('#wpsl-stores ul').html('<li><p>Không tìm thấy cửa hàng nào.</p></li>');
					console.warn('  ⚠️ No stores returned!');
				}
			} catch(e) {
				console.error('  ✗ Error parsing response:', e);
			}

			console.log('═══════════════════════════════════════════════════════');
		}
	});

	console.log('✅ Debug script loaded successfully');
});
</script>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
