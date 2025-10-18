<?php
/**
 * Quick View Template - Refactored
 *
 * @package          Flatsome/WooCommerce/Templates
 * @flatsome-version 3.16.0
 */

defined( 'ABSPATH' ) || exit;

global $post, $product;

// Early validation
if ( ! $product instanceof WC_Product ) {
	return;
}

$product_price = $product->get_price();
$product_id    = $product->get_id();
echo '<script>console.log("Quick View loaded for product ID:", ' . esc_js( $product_id ) . ');</script>';
do_action( 'wc_quick_view_before_single_product' );
?>

<div class="product-quick-view-container">
	<div class="row row-collapse mb-0 product" id="product-<?php the_ID(); ?>" <?php post_class(); ?>>
		
		<!-- Left Column: Product Gallery & Options -->
		<div class="product-gallery large-6 col qv-left-column">
			<?php do_action( 'woocommerce_before_single_product_lightbox_summary' ); ?>

			<!-- Custom Options Section -->
			<div class="product-custom-options">
				
				<!-- Pizza Size Selection -->
				<div class="pizza-size-options">
					<div class="size-button-group">
						<button id="btn-whole" class="size-option active" data-size="whole" type="button">
							<img src="<?php echo esc_url( get_site_url() . '/wp-content/uploads/images/pizza-icon-white.png' ); ?>" 
								 alt="<?php esc_attr_e( 'Whole pizza', 'flatsome' ); ?>" 
								 class="size-icon">
							<span><?php esc_html_e( 'Whole pizza', 'flatsome' ); ?></span>
						</button>
						
						<button id="btn-paired" class="size-option" data-size="paired" type="button">
							<img src="<?php echo esc_url( get_site_url() . '/wp-content/uploads/images/pizza-icon-black.png' ); ?>" 
								 alt="<?php esc_attr_e( 'Paired pizza', 'flatsome' ); ?>" 
								 class="size-icon">
							<span><?php esc_html_e( 'Paired pizza', 'flatsome' ); ?></span>
						</button>
					</div>

					<!-- Whole Pizza Display -->
					<div id="pizza-whole" class="pizza-display">
						<?php
						// Product Image
						if ( has_post_thumbnail() ) {
							$image_id    = get_post_thumbnail_id();
							$image_title = esc_attr( get_the_title( $image_id ) );
							$image_url   = wp_get_attachment_url( $image_id );
							printf(
								'<img src="%s" alt="%s" class="pizza-main-image">',
								esc_url( $image_url ),
								esc_attr( $image_title )
							);
						} else {
							printf(
								'<img src="%s" alt="%s" class="pizza-main-image">',
								esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ),
								esc_attr__( 'Awaiting product image', 'woocommerce' )
							);
						}
						?>
						
						<h3 class="pizza-title"><?php the_title(); ?></h3>
						
						<div class="pizza-description">
							<?php
							echo wp_kses_post( $product->get_short_description() );
							?>
							<br>
							<a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>" 
							   class="read-more-toggle">
								<?php esc_html_e( 'Read more', 'flatsome' ); ?>
							</a>
						</div>
					</div>

					<!-- Paired Pizza Display -->
					<div id="pizza-paired" class="paired-pizza-container">
						<div class="header-section">
							<div class="half-pizza-container">
								<img id="left-pizza" 
									 class="left-pizza-img" 
									 src="<?php echo esc_url( wp_get_attachment_url( $product->get_image_id() ) ); ?>"
									 alt="<?php esc_attr_e( 'Left Pizza Upgrade', 'flatsome' ); ?>">
							</div>
							<div class="half-pizza-container">
								<img id="right-pizza" 
									 class="icon-row" 
									 src="<?php echo esc_url( get_site_url() . '/wp-content/uploads/images/half_pizza.png' ); ?>"
									 alt="<?php esc_attr_e( 'Right Pizza Upgrade', 'flatsome' ); ?>">
							</div>
						</div>

						<div class="title-section">
							<h1><?php printf( esc_html__( '%s with', 'flatsome' ), esc_html( get_the_title() ) ); ?></h1>
						</div>

						<div class="pizza-grid-wrapper">
							<div class="pizza-grid">
								<?php
								// Get paired products from upsells
								$upsell_ids = $product->get_upsell_ids();
								
								if ( ! empty( $upsell_ids ) ) {
									$args = array(
										'include' => $upsell_ids,
										'limit'   => -1,
										'orderby' => 'rand',
										'status'  => 'publish'
									);
									
									$paired_products = wc_get_products( $args );

									if ( ! empty( $paired_products ) ) {
										foreach ( $paired_products as $paired_prod ) {
											// Skip current product
											if ( $paired_prod->get_id() === $product_id ) {
												continue;
											}

											$paired_id       = $paired_prod->get_id();
											$paired_name     = $paired_prod->get_name();
											$paired_price    = $paired_prod->get_price();
											$paired_image_id = get_post_thumbnail_id( $paired_id );
											$paired_image    = wp_get_attachment_image_src( $paired_image_id, 'medium' );
											$paired_image_url = $paired_image ? $paired_image[0] : wc_placeholder_img_src();

											// Get cross-sell IDs for this paired product
											$paired_cross_sells = $paired_prod->get_cross_sell_ids();
											$paired_cross_sells_json = json_encode( $paired_cross_sells );

											// Half price for paired option
											$half_price = $paired_price / 2;
											?>
											<div class="pizza-card"
												 data-product-id="<?php echo esc_attr( $paired_id ); ?>"
												 data-product-name="<?php echo esc_attr( $paired_name ); ?>"
												 data-product-price="<?php echo esc_attr( $half_price ); ?>"
												 data-product-image="<?php echo esc_url( $paired_image_url ); ?>"
												 data-cross-sells="<?php echo esc_attr( $paired_cross_sells_json ); ?>">
												<img src="<?php echo esc_url( $paired_image_url ); ?>" 
													 alt="<?php echo esc_attr( $paired_name ); ?>" 
													 class="pizza-card-img">
												<div class="pizza-card-info">
													<div class="pizza-card-title"><?php echo esc_html( $paired_name ); ?></div>
													<div class="pizza-card-price"><?php echo wp_kses_post( wc_price( $half_price ) ); ?></div>
												</div>
											</div>
											<?php
										}
									}
								}
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Right Column: Product Info & Extras -->
		<div class="product-info summary large-6 col entry-summary qv-right-column">

			<!-- Whole Pizza Toppings -->
			<div id="whole-pizza-toppings" class="toppings-container">
				<?php
				// Get toppings from cross-sell IDs for whole pizza
				$cross_sell_ids = $product->get_cross_sell_ids();
				$whole_toppings = get_toppings_from_cross_sells( $cross_sell_ids );

				if ( ! empty( $whole_toppings ) ) :
				?>
				<h3 class="topping-tab-whole">Whole Pizza Upgrade</h3>
				<?php
					foreach ( $whole_toppings as $category_id => $topping_category ) :
						if ( ! empty( $topping_category['products'] ) ) :
				?>
				<div class="extra-options-section">
					<h4 class="extra-options-title"><?php echo esc_html( $topping_category['category_name'] ); ?>:</h4>
					<div class="checkbox-group">
						<?php foreach ( $topping_category['products'] as $topping ) : ?>
							<label class="topping-label">
								<input type="checkbox"
									   value="<?php echo esc_attr( $topping['name'] ); ?>"
									   class="topping-checkbox whole-topping"
									   data-price="<?php echo esc_attr( $topping['price'] ); ?>"
									   data-product-id="<?php echo esc_attr( $topping['id'] ); ?>">
								<span class="topping-text">
									<?php echo esc_html( $topping['name'] ); ?>
									(<?php echo wp_kses_post( wc_price( $topping['price'] ) ); ?>)
								</span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
				<?php
						endif;
					endforeach;
				endif;
				?>
			</div>

			<!-- Paired Pizza Toppings -->
			<div id="paired-pizza-toppings" class="toppings-container" style="display: none;">
				<!-- Topping Tabs -->
				<h3 class="topping-tab" data-tab="left-toppings"><?php esc_html_e('Left Pizza Upgrade', 'flatsome'); ?></h3>
				<h3 class="topping-tab" data-tab="right-toppings"><?php esc_html_e('Right Pizza Upgrade', 'flatsome'); ?></h3>

				<!-- Left Half Toppings (same as whole pizza, using main product cross-sells) -->
				<div id="left-toppings" class="half-toppings-section tab-content active">
					<?php
					// Reuse whole_toppings for left half (same as main product)
					if ( ! empty( $whole_toppings ) ) :
						foreach ( $whole_toppings as $category_id => $topping_category ) :
							if ( ! empty( $topping_category['products'] ) ) :
					?>
					<div class="extra-options-section">
						<h4 class="extra-options-title"><?php echo esc_html( $topping_category['category_name'] ); ?>:</h4>
						<div class="checkbox-group">
							<?php foreach ( $topping_category['products'] as $topping ) : ?>
								<label class="topping-label">
									<input type="checkbox"
										   value="<?php echo esc_attr( $topping['name'] ); ?>"
										   class="topping-checkbox left-topping"
										   data-price="<?php echo esc_attr( $topping['price'] ); ?>"
										   data-product-id="<?php echo esc_attr( $topping['id'] ); ?>">
									<span class="topping-text">
										<?php echo esc_html( $topping['name'] ); ?>
										(<?php echo wp_kses_post( wc_price( $topping['price'] ) ); ?>)
									</span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
					<?php
							endif;
						endforeach;
					endif;
					?>
				</div>

				<!-- Right Half Toppings (will be dynamically loaded via JavaScript) -->
				<div id="right-toppings" class="half-toppings-section tab-content">
					<div class="right-toppings-placeholder">
						<p><?php esc_html_e( 'Please select a pizza from the options below to see available toppings.', 'flatsome' ); ?></p>
					</div>
				</div>
			</div>

			<!-- Special Request Section -->
			<div class="extra-options-section special-request-section">
				<h4 class="extra-options-title"><?php esc_html_e( 'Special Request:', 'flatsome' ); ?></h4>
				<textarea
					id="special-request"
					name="special_request"
					class="special-request-textarea"
					placeholder="<?php esc_attr_e( 'Add any special instructions here...', 'flatsome' ); ?>"
					rows="3"
					maxlength="500"></textarea>
				<span class="special-request-counter">0/500</span>
			</div>
		</div>
	</div>
</div>

<!-- Bottom Fixed Bar -->
<div class="qv-spacer"></div>
<div class="qv-bottom-bar">
	<!-- Subtotal -->
	<div class="qv-subtotal">
		<span><?php esc_html_e( 'Subtotal:', 'flatsome' ); ?></span>
		<span id="sub_total" class="qv-subtotal-amount" data-base-price="<?php echo esc_attr( $product_price ); ?>">
			<?php echo wp_kses_post( wc_price( $product_price ) ); ?>
		</span>
	</div>
	
	<!-- Quantity & Add to Cart -->
	<?php do_action( 'woocommerce_single_product_lightbox_summary' ); ?>
</div>

<?php
/**
 * Helper function to get topping categories (children of category 15)
 *
 * @return array Array of category data with their products
 */
function get_topping_categories() {
	$parent_category_id = 15;
	$categories = array();

	// Get child categories of category 15
	$child_categories = get_terms( array(
		'taxonomy'   => 'product_cat',
		'parent'     => $parent_category_id,
		'hide_empty' => false,
	) );
	
	echo '<script>console.log("Child Categories:", ' . json_encode($child_categories) . ');</script>';
	
	if ( ! empty( $child_categories ) && ! is_wp_error( $child_categories ) ) {
		foreach ( $child_categories as $category ) {
			$categories[] = array(
				'id'   => $category->term_id,
				'name' => $category->name,
				'slug' => $category->slug,
			);
		}
	}

	return $categories;
}

/**
 * Helper function to get toppings from cross-sell IDs
 *
 * @param array $cross_sell_ids Array of product IDs
 * @return array Grouped array of toppings by category
 */
function get_toppings_from_cross_sells( $cross_sell_ids ) {
	$toppings_by_category = array();

	if ( empty( $cross_sell_ids ) ) {
		return $toppings_by_category;
	}

	// Get all topping categories
	$topping_categories = get_topping_categories();
	
	echo '<script>console.log("Topping Categories:", ' . json_encode($topping_categories) . ');</script>';

	// Get all products from cross-sell IDs
	$products = wc_get_products( array(
		'include' => $cross_sell_ids,
		'status'  => 'publish',
		'limit'   => -1,
	) );

	if ( ! empty( $products ) ) {
		foreach ( $products as $product ) {
			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			// Get product categories
			$product_categories = $product->get_category_ids();

			// Check which topping category this product belongs to
			foreach ( $topping_categories as $topping_cat ) {
				if ( in_array( $topping_cat['id'], $product_categories ) ) {
					if ( ! isset( $toppings_by_category[ $topping_cat['id'] ] ) ) {
						$toppings_by_category[ $topping_cat['id'] ] = array(
							'category_name' => $topping_cat['name'],
							'category_slug' => $topping_cat['slug'],
							'products'      => array(),
						);
					}

					$toppings_by_category[ $topping_cat['id'] ]['products'][] = array(
						'id'    => $product->get_id(),
						'name'  => $product->get_name(),
						'price' => $product->get_price(),
					);

					break; // Product found in this category, no need to check others
				}
			}
		}
	}

	return $toppings_by_category;
}

do_action( 'wc_quick_view_after_single_product' );
?>

<style>
/* ===========================
   Quick View Styles
   =========================== */

/* Column Layout */
.qv-left-column {
	padding: 0 10px !important;
}

.qv-right-column {
	padding: 0 20px !important;
	font-size: 90%;
}

/* Product Custom Options */
.product-custom-options {
	margin-top: 20px;
	background: #fff;
	border-radius: 8px;
}

.pizza-size-options {
	margin-bottom: 20px;
}

/* Size Button Group */
.size-button-group {
	display: flex;
	gap: 10px;
	margin: 10px;
}

.size-option {
	flex: 1;
	padding: 12px 20px;
	background: #f5f5f5;
	color: #333;
	border: none;
	border-radius: 8px;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 8px;
	font-weight: 500;
	transition: all 0.3s ease;
}

.size-option.active {
	background: #dc0000 !important;
	color: white !important;
}

.size-option.active .size-icon {
	filter: brightness(0) invert(1);
}

.size-option:hover {
	opacity: 0.9;
}

.size-icon {
	width: 24px;
	height: 24px;
}

.size-nav-next {
	padding: 12px 20px;
	background: #f5f5f5;
	border: none;
	border-radius: 8px;
	cursor: pointer;
	transition: background 0.3s ease;
}

.size-nav-next:hover {
	background: #e0e0e0;
}

/* Whole Pizza Display */
.pizza-display {
	text-align: center;
	margin-bottom: 20px;
}

.pizza-main-image {
	max-width: 100%;
	height: auto;
	border-radius: 3px;
	margin-bottom: 15px;
}

.pizza-title {
	font-size: 24px;
	margin: 0 0 10px 0;
	font-weight: bold;
}

.pizza-description {
	color: #666;
	font-size: 14px;
	line-height: 1.6;
	margin-bottom: 10px;
}

.read-more-toggle {
	color: #dc0000;
	cursor: pointer;
	font-weight: 500;
	text-decoration: none;
}

.read-more-toggle:hover {
	text-decoration: underline;
}

.pizza-size-info {
	font-size: 14px;
	color: #333;
	margin: 0;
}

/* Paired Pizza Container */
.paired-pizza-container {
	display: none;
}

/* Header Section */
.header-section {
	display: flex;
	height: 200px;
}

.half-pizza-container {
	width: 50%;
	overflow: hidden;
	background: #f0f0f0;
	display: flex;
	align-items: center;
}

.half-pizza-container img:hover {
	cursor: pointer;
}

.left-pizza-img {
	width: 200%;
	height: 100%;
	object-fit: cover;
	object-position: right center;
	transform: translateX(50%);
	display: block;
}

.right-pizza-img {
	width: 200%;
	height: 100%;
	object-fit: cover;
	object-position: left center;
	transform: translateX(-50%);
	display: block;
}

.icon-row {
	margin-left: 80px;
}

/* Title Section */
.title-section {
	padding: 24px 20px 20px;
}

.title-section h1 {
	font-size: 28px;
	font-weight: 700;
	color: #000;
	margin: 0;
}

/* Pizza Grid */
.pizza-grid-wrapper {
	height: 300px;
	overflow: auto;
}

.pizza-grid {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	gap: 16px;
	padding: 0 20px 30px;
}

.pizza-card {
	background: white;
	border-radius: 8px;
	overflow: hidden;
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
	transition: all 0.3s ease;
	cursor: pointer;
}

.pizza-card:hover {
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
	transform: translateY(-2px);
}

.pizza-card.selected {
	border: 2px solid #dc0000;
	box-shadow: 0 4px 12px rgba(220, 0, 0, 0.3);
}

.pizza-card-img {
	width: 100%;
	aspect-ratio: 1;
	object-fit: cover;
}

.pizza-card-info {
	padding: 12px;
}

.pizza-card-title {
	font-size: 15px;
	font-weight: 600;
	color: #000;
	margin-bottom: 6px;
}

.pizza-card:hover .pizza-card-title {
	color: #cd0000;
}

.pizza-card-price {
	font-size: 13px;
	color: #a3a3a3;
}

.pizza-card:hover .pizza-card-price .amount {
	color: #cd0000;
}

/* Toppings Container */
.toppings-container {
	padding: 10px 0;
}

/* Topping Tabs */
.topping-tabs {
	display: flex;
	gap: 10px;
}

.topping-tab-whole {
	padding: 0 10px;
	font-weight: 900;
	color: #666;
	position: relative;
	bottom: -10px;
}

.topping-tab {
	padding: 0 10px;
	font-weight: 900;
	color: #666;
	position: relative;
	bottom: -10px;
	display: none;
}

.topping-tab.active {
	display: block;
}

/* Tab Content */
.tab-content {
	display: none;
	animation: fadeIn 0.3s ease;
}

.tab-content.active {
	display: block;
}

@keyframes fadeIn {
	from {
		opacity: 0;
		transform: translateY(10px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}

/* Half Toppings Sections */
.half-toppings-section {
	padding: 10px 10px;
}

/* Extra Options Section */
.extra-options-section {
	margin: 20px 0;
	padding: 0 20px;
}

.extra-options-title {
	font-size: 16px;
	font-weight: bold;
	margin-bottom: 12px;
}

.checkbox-group {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.topping-label {
	display: flex;
	align-items: center;
	padding: 0px 20px;
	cursor: pointer;
	margin: 0 !important;
	transition: background 0.2s ease;
	border-radius: 4px;
}

.topping-label:hover {
	background: #f9f9f9;
}

.topping-checkbox {
	margin-bottom: 0px !important;
	width: 18px;
	height: 18px;
	cursor: pointer;
}

.topping-text {
	padding-top: 5px;
}

/* Special Request Section */
.special-request-section {
	margin-top: 25px;
	padding-top: 20px;
	border-top: 2px solid #e0e0e0;
}

.special-request-textarea {
	width: 100%;
	padding: 12px;
	border: 1px solid #ddd;
	border-radius: 6px;
	font-size: 14px;
	font-family: inherit;
	resize: vertical;
	transition: border-color 0.3s ease;
}

.special-request-textarea:focus {
	outline: none;
	border-color: #dc0000;
	box-shadow: 0 0 0 1px rgba(220, 0, 0, 0.1);
}

.special-request-counter {
	display: block;
	text-align: right;
	font-size: 12px;
	color: #999;
	margin-top: 5px;
}

/* Bottom Bar */
.qv-spacer {
	height: 100px;
	margin-top: 20px;
}

.qv-bottom-bar {
	position: fixed;
	bottom: 0;
	left: 50%;
	transform: translateX(-50%);
	width: calc(100% - 40px);
	max-width: 600px;
	z-index: 999;
}

.qv-subtotal {
	background: #ffc107;
	padding: 12px 20px;
	border-radius: 8px;
	margin-bottom: 15px;
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.qv-subtotal-amount {
	font-size: 20px;
	font-weight: bold;
	color: #dc0000;
}

.cart {
	margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
	.title-section h1 {
		font-size: 24px;
	}

	.pizza-grid {
		gap: 12px;
		padding: 0 16px 24px;
	}
	
	.qv-bottom-bar {
		width: calc(100% - 20px);
	}
}

@media (max-width: 480px) {
	.size-button-group {
		flex-wrap: wrap;
	}
	
	.size-nav-next {
		width: 100%;
	}
}
</style>

<script>
(function($) {
	'use strict';

	$(document).ready(function() {
		// Configuration
		const config = {
			basePrice: parseFloat($('#sub_total').data('base-price')) || 0,
			currencyFormat: {
				style: 'currency',
				currency: 'VND'
			}
		};

		// Track selected pizza halves
		let selectedLeftHalf = null;
		let selectedRightHalf = null;

		// Size Toggle
		function initSizeToggle() {
			$('#btn-whole').on('click', function() {
				$('.size-option').removeClass('active');
				$(this).addClass('active');
				$('#pizza-whole').show();
				$('#pizza-paired').hide();

				// Show whole toppings, hide paired toppings
				$('#whole-pizza-toppings').show();
				$('#paired-pizza-toppings').hide();

				// Clear all topping selections
				$('.topping-checkbox').prop('checked', false);

				// Reset paired selections when switching to whole
				selectedLeftHalf = null;
				selectedRightHalf = null;
				$('.pizza-card').removeClass('selected');
				$('#right-pizza')
					.attr('src', '<?php echo esc_url( get_site_url() . "/wp-content/uploads/images/half_pizza.png" ); ?>')
					.removeClass('right-pizza-img')
					.addClass('icon-row');

				updateSubtotal();
			});

			$('#btn-paired').on('click', function() {
				$('.size-option').removeClass('active');
				$(this).addClass('active');
				$('#pizza-whole').hide();
				$('#pizza-paired').show();

				// Show paired toppings, hide whole toppings
				$('#whole-pizza-toppings').hide();
				$('#paired-pizza-toppings').show();

				// Clear all topping selections
				$('.topping-checkbox').prop('checked', false);

				// Set main product as left half when switching to paired mode
				const mainProductId = <?php echo esc_js( $product_id ); ?>;
				const mainProductName = <?php echo json_encode( get_the_title() ); ?>;
				const mainProductPrice = <?php echo esc_js( $product_price ); ?>;
				const mainProductImage = <?php
					if ( has_post_thumbnail() ) {
						$image_id = get_post_thumbnail_id();
						echo json_encode( wp_get_attachment_url( $image_id ) );
					} else {
						echo json_encode( wc_placeholder_img_src( 'woocommerce_single' ) );
					}
				?>

				selectedLeftHalf = {
					product_id: mainProductId,
					name: mainProductName,
					price: mainProductPrice / 2, // Half price for paired
					image: mainProductImage
				};

				updateSubtotal();
			});

			$('#left-pizza').on('click', function() {
				$('.topping-tab').removeClass('active');
				$('.topping-tab[data-tab="left-toppings"]').addClass('active');
				$('.tab-content').removeClass('active');
				$('#left-toppings').addClass('active');
			});

			$('#right-pizza').on('click', function() {
				if (!selectedRightHalf) {
					alert('Please select a pizza from the options below for the right half.');
					return;
				}
				$('.topping-tab').removeClass('active');
				$('.topping-tab[data-tab="right-toppings"]').addClass('active');
				$('.tab-content').removeClass('active');
				$('#right-toppings').addClass('active');
			});

			// Wait for DOM to be ready and modal to be visible
			setTimeout(function() {
				// Get the view type from sessionStorage
				const viewType = sessionStorage.getItem('pizza_view') || 'whole';
				console.log('pizza_view type saved sessionStorage:', viewType);
				
				// Show appropriate view based on stored value
				if (viewType === 'paired') {
					$('#btn-paired').trigger('click');
					$('#left-pizza').trigger('click');					
				} else {
					$('#btn-whole').trigger('click');
				}
				
				// Clear the storage after using it
				sessionStorage.removeItem('pizza_view');
			}, 100);
		}

		// Function to load right half toppings via AJAX
		function loadRightHalfToppings(crossSellIds) {
			const $rightToppings = $('#right-toppings');

			// Show loading state
			$rightToppings.html('<div class="right-toppings-placeholder"><p>Loading toppings...</p></div>');

			// Make AJAX request to get toppings
			$.ajax({
				url: '<?php echo esc_url( admin_url( "admin-ajax.php" ) ); ?>',
				type: 'POST',
				data: {
					action: 'get_product_toppings',
					nonce: '<?php echo wp_create_nonce( "product_toppings_nonce" ); ?>',
					cross_sell_ids: crossSellIds
				},
				success: function(response) {
					if (response.success && response.data.toppings) {
						const toppings = response.data.toppings;
						let html = '';

						// Build HTML for each topping category
						Object.keys(toppings).forEach(function(categoryId) {
							const category = toppings[categoryId];
							if (category.products && category.products.length > 0) {
								html += '<div class="extra-options-section">';
								html += '<h4 class="extra-options-title">' + category.category_name + ':</h4>';
								html += '<div class="checkbox-group">';

								category.products.forEach(function(topping) {
									html += '<label class="topping-label">';
									html += '<input type="checkbox" ';
									html += 'value="' + topping.name + '" ';
									html += 'class="topping-checkbox right-topping" ';
									html += 'data-price="' + topping.price + '" ';
									html += 'data-product-id="' + topping.id + '">';
									html += '<span class="topping-text">';
									html += topping.name + ' (' + topping.price_formatted + ')';
									html += '</span>';
									html += '</label>';
								});

								html += '</div></div>';
							}
						});

						if (html) {
							$rightToppings.html(html);
						} else {
							$rightToppings.html('<div class="right-toppings-placeholder"><p>No toppings available for this pizza.</p></div>');
						}
					} else {
						$rightToppings.html('<div class="right-toppings-placeholder"><p>Error loading toppings. Please try again.</p></div>');
					}
				},
				error: function() {
					$rightToppings.html('<div class="right-toppings-placeholder"><p>Error loading toppings. Please try again.</p></div>');
				}
			});
		}

		// Pizza Card Selection (for right half)
		function initPizzaCardSelection() {
			// Ensure we don't bind multiple handlers when the lightbox opens/closes
			// Remove any plain or namespaced handlers first
			$(document).off('click', '.pizza-card').off('click.pizzaCard', '.pizza-card')
				.on('click.pizzaCard', '.pizza-card', function() {
				const $card = $(this);
				const imageUrl = $card.data('product-image');
				const isCurrentlySelected = $card.hasClass('selected');
				console.log('Clicked pizza card for product ID:', $card.data('product-id'));
				console.log('Is currently selected:', isCurrentlySelected);
				const crossSells = $card.data('cross-sells');				

				// Remove selection from all cards
				$('.pizza-card').removeClass('selected');

				// If clicking a different card (not deselecting), select it
				if (!isCurrentlySelected) {
					$card.addClass('selected');

					// Update right pizza image
					$('#right-pizza')
						.attr('src', imageUrl)
						.removeClass('icon-row')
						.addClass('right-pizza-img');

					// Store right half data
					selectedRightHalf = {
						product_id: parseInt($card.data('product-id')),
						name: $card.data('product-name'),
						price: parseFloat($card.data('product-price')),
						image: imageUrl
					};

					// Load toppings for the right half
					if (crossSells && Array.isArray(crossSells) && crossSells.length > 0) {
						loadRightHalfToppings(crossSells);
					} else {
						$('#right-toppings').html('<div class="right-toppings-placeholder"><p>No toppings available for this pizza.</p></div>');
					}

					$('.topping-tab').removeClass('active');
					$('.topping-tab[data-tab="right-toppings"]').addClass('active');
					$('.tab-content').removeClass('active');
					$('#right-toppings').addClass('active');
				} else {
					// Reset to default placeholder
					$('#right-pizza')
						.attr('src', '<?php echo esc_url( get_site_url() . "/wp-content/uploads/images/half_pizza.png" ); ?>')
						.removeClass('right-pizza-img')
						.addClass('icon-row');

					// Clear right half selection
					selectedRightHalf = null;

					// Reset right toppings to placeholder
					$('#right-toppings').html('<div class="right-toppings-placeholder"><p><?php esc_html_e( "Please select a pizza from the options below to see available toppings.", "flatsome" ); ?></p></div>');
				}

				updateSubtotal();
			});
		}

		// Subtotal Calculation
		function updateSubtotal() {
			let subtotal = 0;

			// Check if paired mode is active
			const isPairedMode = $('#btn-paired').hasClass('active');

			if (isPairedMode) {
				// Add left half price (if exists)
				if (selectedLeftHalf && selectedLeftHalf.price) {
					subtotal += selectedLeftHalf.price;
				}

				// Add right half price (if exists)
				if (selectedRightHalf && selectedRightHalf.price) {
					subtotal += selectedRightHalf.price;
				}

				// Add left half toppings
				$('.left-topping:checked').each(function() {
					const price = parseFloat($(this).data('price'));
					if (!isNaN(price)) {
						subtotal += price;
					}
				});

				// Add right half toppings
				$('.right-topping:checked').each(function() {
					const price = parseFloat($(this).data('price'));
					if (!isNaN(price)) {
						subtotal += price;
					}
				});
			} else {
				// Whole pizza mode - use base price
				subtotal = config.basePrice;

				// Add whole pizza toppings
				$('.whole-topping:checked').each(function() {
					const price = parseFloat($(this).data('price'));
					if (!isNaN(price)) {
						subtotal += price;
					}
				});
			}

			// Update display
			const formatted = new Intl.NumberFormat('vi-VN', config.currencyFormat).format(subtotal);
			$('#sub_total').text(formatted);
		}

		// Topping Checkbox Handler
		function initToppingCheckboxes() {
			// Ensure single binding for topping checkboxes: remove any plain and namespaced handlers first
			$(document).off('change', '.topping-checkbox').off('change.toppingCheckbox', '.topping-checkbox')
				.on('change.toppingCheckbox', '.topping-checkbox', updateSubtotal);
		}

		// Add to Cart Handler
		function initAddToCart() {
			// Ensure single binding for add-to-cart submission handler: remove plain and namespaced handlers first
			$(document).off('click', 'form.cart button[type="submit"], .single_add_to_cart_button').off('click.addToCart', 'form.cart button[type="submit"], .single_add_to_cart_button')
				.on('click.addToCart', 'form.cart button[type="submit"], .single_add_to_cart_button', function(e) {
				// Check if paired mode is active
				const isPairedMode = $('#btn-paired').hasClass('active');
				let pizzaHalves = null;
				let leftToppings = [];
				let rightToppings = [];
				let wholeToppings = [];

				if (isPairedMode) {
					// Paired mode: gather left and right toppings separately
					$('.left-topping:checked').each(function() {
						const $checkbox = $(this);
						leftToppings.push({
							name: $checkbox.val(),
							price: parseFloat($checkbox.data('price')),
							product_id: parseInt($checkbox.data('product-id'))
						});
					});

					$('.right-topping:checked').each(function() {
						const $checkbox = $(this);
						rightToppings.push({
							name: $checkbox.val(),
							price: parseFloat($checkbox.data('price')),
							product_id: parseInt($checkbox.data('product-id'))
						});
					});

					// Add toppings to halves
					pizzaHalves = {
						left_half: {
							...selectedLeftHalf,
							toppings: leftToppings
						},
						right_half: {
							...selectedRightHalf,
							toppings: rightToppings
						}
					};

					console.log('Paired mode - Left half:', pizzaHalves.left_half);
					console.log('Paired mode - Right half:', pizzaHalves.right_half);
				} else {
					// Whole pizza mode: gather whole pizza toppings
					$('.whole-topping:checked').each(function() {
						const $checkbox = $(this);
						wholeToppings.push({
							name: $checkbox.val(),
							price: parseFloat($checkbox.data('price')),
							product_id: parseInt($checkbox.data('product-id'))
						});
					});

					console.log('Whole pizza mode');
					console.log('Whole pizza toppings:', wholeToppings);
				}

				// Get special request text
				const specialRequest = $('#special-request').val().trim();

				// Remove existing hidden inputs to avoid duplicates
				$('input[name="extra_topping_options"]').remove();
				$('input[name="pizza_halves"]').remove();
				$('input[name="paired_products"]').remove(); // Keep for backward compatibility
				$('input[name="special_request"]').remove();

				// Find the form
				const $form = $(this).closest('form.cart');
				const $target = $form.length ? $form : $('body');

				// Add hidden inputs with JSON data
				if (!isPairedMode && wholeToppings.length > 0) {
					// Whole pizza toppings
					$('<input>')
						.attr('type', 'hidden')
						.attr('name', 'extra_topping_options')
						.val(JSON.stringify(wholeToppings))
						.appendTo($target);

					console.log('Added whole pizza topping options:', JSON.stringify(wholeToppings));
				}

				if (pizzaHalves) {
					// Paired pizza with halves and their toppings
					$('<input>')
						.attr('type', 'hidden')
						.attr('name', 'pizza_halves')
						.val(JSON.stringify(pizzaHalves))
						.appendTo($target);

					console.log('Added pizza halves with toppings:', JSON.stringify(pizzaHalves));
				}

				// Add special request if provided
				if (specialRequest) {
					$('<input>')
						.attr('type', 'hidden')
						.attr('name', 'special_request')
						.val(specialRequest)
						.appendTo($target);

					console.log('Added special request:', specialRequest);
				}

				// Allow form submission to proceed
				console.log('Form submission proceeding...');
			});
		}

		// Special Request Character Counter
		function initSpecialRequestCounter() {
			// Ensure single binding for special request counter: remove plain and namespaced handlers first
			$(document).off('input', '#special-request').off('input.specialRequest', '#special-request')
				.on('input.specialRequest', '#special-request', function() {
				const length = $(this).val().length;
				const maxLength = $(this).attr('maxlength');
				$('.special-request-counter').text(length + '/' + maxLength);
			});
		}

		function resetAllParams() {
			console.log('Resetting all parameters to default state.');
			// Remove any hidden inputs added for submission
			$('input[name="extra_topping_options"]').remove();
			$('input[name="pizza_halves"]').remove();
			$('input[name="paired_products"]').remove();
			$('input[name="special_request"]').remove();

			// Uncheck all topping checkboxes
			$('.topping-checkbox').prop('checked', false);

			// Reset selected halves and UI selection
			selectedLeftHalf = null;
			selectedRightHalf = null;
			$('.pizza-card').removeClass('selected');

			// Reset special request textarea
			$('#special-request').val('');
			$('.special-request-counter').text('0/' + $('#special-request').attr('maxlength'));

			// Reset size toggle to whole
			$('#btn-paired').removeClass('active');
			$('#btn-whole').addClass('active');
			$('#pizza-whole').show();
			$('#pizza-paired').hide();

			// Reset subtotal display to base price
			const base = parseFloat($('#sub_total').data('base-price')) || 0;
			const formatted = new Intl.NumberFormat('vi-VN', { style: 'decimal' }).format(base);
			$('#sub_total').text(formatted + ' ₫');
		}

		resetAllParams();
		// Initialize all handlers
		initSizeToggle();
		initPizzaCardSelection();
		initToppingCheckboxes();
		initAddToCart();
		// initToppingTabs();
		initSpecialRequestCounter();
	});
})(jQuery);
</script>