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
$upsell_ids    = $product->get_upsell_ids();
$has_upsells   = ! empty( $upsell_ids );
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
						
						<button id="btn-paired" class="size-option" data-size="paired" type="button" <?php echo ! $has_upsells ? 'style="display: none !important;"' : ''; ?>>
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
								// Use upsell_ids already fetched at top of file
								if ( $has_upsells ) {
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

			<!-- Product Variation Selection (for variable products) -->
			<?php if ( $product->is_type( 'variable' ) ) : ?>
				<?php
				// Get variation attributes and available variations
				$attributes           = $product->get_variation_attributes();
				$available_variations = $product->get_available_variations();
				$attribute_keys       = array_keys( $attributes );
				$variations_json      = wp_json_encode( $available_variations );
				$variations_attr      = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

				do_action( 'woocommerce_before_add_to_cart_form' );
				?>
			<form class="variations_form cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok. ?>">
				<?php do_action( 'woocommerce_before_variations_form' ); ?>
			<div id="product-variations-section" class="product-variations-wrapper">
				<?php foreach ( $attributes as $attribute_name => $options ) : ?>
					<div class="variation-attribute-group">
						<h3 class="variation-attribute-title"><?php echo wc_attribute_label( $attribute_name ); ?>:</h3>
						<div class="variation-buttons-container" data-attribute="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>">
							<?php
							// Create button-style options instead of dropdown
							$attribute_slug = sanitize_title( $attribute_name );
							$option_index = 0;
							foreach ( $options as $option ) :
								$option_slug = sanitize_title( $option );
								// Set first option as selected by default
								$is_first = ( $option_index === 0 );
								$button_class = 'variation-button';
								$option_index++;
							?>
								<button type="button"
										class="<?php echo esc_attr( $button_class ); ?>"
										data-value="<?php echo esc_attr( $option ); ?>"
										data-attribute="<?php echo esc_attr( $attribute_slug ); ?>">
									<?php echo esc_html( $option ); ?>
								</button>
							<?php endforeach; ?>
						</div>
						<!-- Hidden select for WooCommerce compatibility -->
						<select class="variation-select-hidden"
								name="<?php echo esc_attr( 'attribute_' . $attribute_slug ); ?>"
								data-attribute_name="<?php echo esc_attr( 'attribute_' . $attribute_slug ); ?>"
								style="display: none;">
							<option value=""><?php esc_html_e( 'Choose an option', 'woocommerce' ); ?></option>
							<?php
							$option_index = 0;
							foreach ( $options as $option ) :
								// Set first option as selected by default
								$is_first = ( $option_index === 0 );
								$option_index++;
							?>
								<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $is_first, true ); ?>><?php echo esc_html( $option ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endforeach; ?>
				<div class="reset_variations_alert screen-reader-text" role="alert" aria-live="polite" aria-relevant="all"></div>
			</div>
			<?php endif; ?>

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
			<div class="special-request-section">
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
	<?php if ( $product->is_type( 'variable' ) ) : ?>
		<!-- WooCommerce single variation container (uses standard WooCommerce hooks) -->
		<div class="single_variation_wrap">
			<?php
			/**
			 * Hook: woocommerce_before_single_variation.
			 */
			do_action( 'woocommerce_before_single_variation' );

			/**
			 * Hook: woocommerce_single_variation. Used to output the cart button and placeholder for variation data.
			 *
			 * @since 2.4.0
			 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
			 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
			 */
			do_action( 'woocommerce_single_variation' );

			/**
			 * Hook: woocommerce_after_single_variation.
			 */
			do_action( 'woocommerce_after_single_variation' );
			?>
		</div>
	<?php else : ?>
		<?php
		// For non-variable products
		do_action( 'woocommerce_single_product_lightbox_summary' );
		?>
	<?php endif; ?>

	<!-- Close WooCommerce variations form -->
	<?php do_action( 'woocommerce_after_variations_form' ); ?>
	</form>
	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
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
	max-height: 250px;
	width: auto;
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
	max-height: 250px;
	width: auto;
	position: relative;
}

.half-pizza-container {
	width: 50%;
	overflow: hidden;
	background: #f0f0f0;
	display: flex;
	align-items: center;
	justify-content: center;
	max-height: 250px;
}

.half-pizza-container img:hover {
	cursor: pointer;
}

.left-pizza-img {
	width: 200%;
	height: auto;
	max-height: 250px;
	object-fit: cover;
	object-position: right center;
	transform: translateX(+50%);
	display: block;
}

.right-pizza-img {
	width: 200%;
	height: auto;
	max-height: 250px;
	object-fit: cover;
	object-position: left center;
	transform: translateX(-50%);
	display: block;
}

.icon-row {
	/* margin-left: 80px; */
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

/* Hide default WooCommerce variation dropdown in bottom bar */
.qv-bottom-bar .variations_form .variations,
.qv-bottom-bar .variations_form table.variations,
.qv-bottom-bar .reset_variations,
.qv-bottom-bar .woocommerce-variation-description,
.qv-bottom-bar .woocommerce-variation-price,
.qv-bottom-bar .woocommerce-variation-availability {
	display: none !important;
}

/* Keep add-to-cart button visible */
.qv-bottom-bar .single_variation_wrap {
	display: block !important;
}

.qv-bottom-bar .variations_form .cart {
	margin: 0;
}

/* Product Variation Section (Custom Button Style) */
.product-variations-wrapper {
	padding: 0 20px;
	margin-bottom: 20px;
}

.variation-attribute-group {
	margin-bottom: 20px;
}

.variation-attribute-title {
	font-size: 16px;
	font-weight: bold;
	margin: 0 0 12px 0;
	color: #000;
}

.variation-buttons-container {
	display: flex;
	gap: 10px;
	flex-wrap: wrap;
}

.variation-button {
	flex: 1;
	min-width: 80px;
	padding: 12px 20px;
	background: #f5f5f5;
	color: #333;
	border: 2px solid transparent;
	border-radius: 4px;
	cursor: pointer;
	font-size: 14px;
	font-weight: 600;
	text-transform: uppercase;
	transition: all 0.3s ease;
	position: relative;
	z-index: 10;
	pointer-events: auto;
}

.variation-button:hover {
	background: #e8e8e8;
}

.variation-button.selected {
	background: #dc0000;
	color: white;
	border-color: #dc0000;
}

.variation-button:disabled {
	opacity: 0.4;
	cursor: not-allowed;
	background: #f5f5f5;
	color: #999;
}

/* Hide the default select dropdown */
.variation-select-hidden {
	display: none !important;
}

/* Extra Options Section */
.extra-options-section {
	margin: 20px 0;
	padding: 0 20px;
    min-height: 370px;
	max-height: 370px;
    overflow-y: scroll;
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
	padding: 20px;
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

/* Hide paired button when no upsells (failsafe) */
<?php if ( ! $has_upsells ) : ?>
#btn-paired {
	display: none !important;
}
<?php endif; ?>

/* Add to Cart Button - Disabled State */
.single_add_to_cart_button.disabled,
.single_add_to_cart_button:disabled {
	opacity: 0.5;
	cursor: not-allowed !important;
	pointer-events: none;
	background-color: #ccc !important;
	border-color: #ccc !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
	// Alert to confirm script is loaded
	alert('BBB');
});

(function($) {
	'use strict';

	$(document).ready(function() {
		alert('AAA');
		// Check if upsells exist and hide paired button if not
		const hasUpsells = <?php echo $has_upsells ? 'true' : 'false'; ?>;

		if (!hasUpsells) {
			// Try multiple methods to ensure it's hidden
			const $pairedBtn = $('#btn-paired');
			$pairedBtn.hide();
			$pairedBtn.css('display', 'none');
			$pairedBtn.addClass('hidden-no-upsells');
		}

		// Global variable for main product price (updated when variation changes)
		let mainProductPrice = <?php echo esc_js( $product_price ); ?>;
		const mainProductId = <?php echo esc_js( $product_id ); ?>;

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

		// Function to sync paired pizza header height with whole pizza image
		function syncPairedPizzaHeight() {
			const $leftPizza = $('#left-pizza');
			const $headerSection = $('.header-section');
			const $wholePizza = $('.pizza-main-image');

			// Use whole pizza natural dimensions for perfect match
			let naturalWidth = $leftPizza[0].naturalWidth;
			let naturalHeight = $leftPizza[0].naturalHeight;

			// If whole pizza is available, use its dimensions for exact match
			if ($wholePizza.length && $wholePizza[0].naturalWidth && $wholePizza[0].naturalHeight) {
				naturalWidth = $wholePizza[0].naturalWidth;
				naturalHeight = $wholePizza[0].naturalHeight;
			}

			if (naturalWidth && naturalHeight) {
				// Get the natural aspect ratio
				const aspectRatio = naturalHeight / naturalWidth;

				// Get the current width of the header section
				const headerWidth = $headerSection.width();

				// Calculate the height based on aspect ratio and round to match whole pizza exactly
				const targetHeight = Math.round(headerWidth * aspectRatio);

				// Set the height on the header section
				$headerSection.css('height', targetHeight + 'px');
			}
		}

		// Size Toggle
		function initSizeToggle() {
			$('#btn-whole').on('click', function() {
				console.log('Whole pizza button clicked');
				$('.size-option').removeClass('active');
				$(this).addClass('active');
				$('#pizza-whole').show();
				$('#pizza-paired').hide();

				// Show whole toppings, hide paired toppings
				$('#whole-pizza-toppings').show();
				$('#paired-pizza-toppings').hide();

				// Set default variation after switching to whole mode
				setTimeout(function() {
					console.log('Setting default variation after whole button click');
					if (typeof setDefaultVariation === 'function') {
						setDefaultVariation();
					}
				}, 200);

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
				// Prevent clicking if no upsells available
				if (!hasUpsells) {
					return false;
				}

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
				// Note: mainProductId and mainProductPrice are global variables (declared at top)
				const mainProductName = <?php echo json_encode( get_the_title() ); ?>;
				const mainProductImage = <?php
					if ( has_post_thumbnail() ) {
						$image_id = get_post_thumbnail_id();
						echo json_encode( wp_get_attachment_url( $image_id ) );
					} else {
						echo json_encode( wc_placeholder_img_src( 'woocommerce_single' ) );
					}
				?>

				// Get current variation price if available (for variable products)
				// Note: Variation change handler is set up globally in setupVariationForm()
				// No need to attach handler here as it causes duplicates

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
			// This runs AFTER default variation is set to ensure mainProductPrice is available
			setTimeout(function() {
				// Get the view type from sessionStorage
				const viewType = sessionStorage.getItem('pizza_view') || 'whole';

				// Show appropriate view based on stored value
				// Only allow paired view if upsells exist and button is visible
				if (viewType === 'paired' && hasUpsells && $('#btn-paired').is(':visible')) {
					$('#btn-paired').trigger('click');
					$('#left-pizza').trigger('click');
				} else {
					$('#btn-whole').trigger('click');
				}

				// Clear the storage after using it
				sessionStorage.removeItem('pizza_view');
			}, 800); // Increased delay to run after setDefaultVariation (100ms + 500ms + buffer)
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

		// Function to get current product price (variation-aware)
		function getCurrentProductPrice() {
			// Use mainProductPrice which is updated by found_variation event
			// This is more reliable than reading from DOM
			if (typeof writeLogServer === 'function') writeLogServer({event: 'getCurrentProductPrice', mainProductPrice: mainProductPrice, basePrice: config.basePrice}, 'info');
			if (mainProductPrice && mainProductPrice > 0) {
				return mainProductPrice;
			}
			// Fall back to base price
			return config.basePrice;
		}

		// Subtotal Calculation
		function updateSubtotal() {
			let subtotal = 0;
			if (typeof writeLogServer === 'function') writeLogServer({event: 'updateSubtotal_called', currentPrice: getCurrentProductPrice()}, 'info');
			let canAddToCart = true;

			// Check if paired mode is active
			const isPairedMode = $('#btn-paired').hasClass('active');

			if (isPairedMode) {
				// Validate that both halves are selected
				const hasLeftHalf = selectedLeftHalf && selectedLeftHalf.price;
				const hasRightHalf = selectedRightHalf && selectedRightHalf.price;

				if (!hasRightHalf) {
					// In paired mode but right half not selected - disable add to cart
					canAddToCart = false;
				}

				// Add left half price (if exists)
				if (hasLeftHalf) {
					subtotal += selectedLeftHalf.price;
				}

				// Add right half price (if exists)
				if (hasRightHalf) {
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
				// Whole pizza mode - use current product price (variation-aware)
				subtotal = getCurrentProductPrice();

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

			// Enable/disable Add to Cart button based on validation
			const $addToCartBtn = $('.single_add_to_cart_button');
			if (canAddToCart) {
				$addToCartBtn.prop('disabled', false).removeClass('disabled');
			} else {
				$addToCartBtn.prop('disabled', true).addClass('disabled');
			}
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
				}

				if (pizzaHalves) {
					// Paired pizza with halves and their toppings
					$('<input>')
						.attr('type', 'hidden')
						.attr('name', 'pizza_halves')
						.val(JSON.stringify(pizzaHalves))
						.appendTo($target);
				}

				// Add special request if provided
				if (specialRequest) {
					$('<input>')
						.attr('type', 'hidden')
						.attr('name', 'special_request')
						.val(specialRequest)
						.appendTo($target);
				}
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
		
		// Variation Change Handler
		function initVariationHandler() {
			alert('OK');
			debugger;

			// Prevent multiple attachments
			if (window.variationHandlerAttached) {
				return;
			}
			window.variationHandlerAttached = true;

			if (typeof writeLogServer === 'function') writeLogServer({event: 'initVariationHandler_called', attached: window.variationHandlerAttached}, 'info');
			// Attach event handler to document (works even if lightbox not loaded yet)
			$(document).on('click', '.variation-button', function(e) {
				e.preventDefault();
				e.stopPropagation();
				const $button = $(this);
				const value = $button.data('value');
				const attribute = $button.data('attribute');
				if (typeof writeLogServer === 'function') writeLogServer({event: 'button_click', value: value, attribute: attribute}, 'info');

				// Remove selected class from sibling buttons
				$button.siblings('.variation-button').removeClass('selected');

				// Toggle selection
				const wasSelected = $button.hasClass('selected');
				if (!wasSelected) {
					$button.addClass('selected');

					// Find hidden select by matching attribute name
					// The select might have been moved to the form, so search globally
					const selectName = 'attribute_' + attribute;
					const $hiddenSelect = $('select[name="' + selectName + '"]');
					$hiddenSelect.val(value).trigger('change');
					if (typeof writeLogServer === 'function') writeLogServer({event: 'button_select_value_set', selectName: selectName, value: value, selectFound: .length}, 'info');
				} else {
					$button.removeClass('selected');

					// Find and clear the hidden select
					const selectName = 'attribute_' + attribute;
					const $hiddenSelect = $('select[name="' + selectName + '"]');
					$hiddenSelect.val('').trigger('change');
				}

				// Trigger WooCommerce variation form update
				const $variationsForm = $('.variations_form');
				if ($variationsForm.length) {
					$variationsForm.trigger('check_variations');
				}
			});

			// Move hidden selects into form when lightbox opens
			const setupVariationForm = function() {
				const $variationsForm = $('.variations_form');
				if ($variationsForm.length) {
					// Move selects to form
					$('.variation-select-hidden').each(function() {
						const $select = $(this);
						if (!$select.closest('form.variations_form').length) {
							$variationsForm.append($select.detach());
						}
					});

					// Initialize WooCommerce variation form if not already initialized
					if (!$variationsForm.data('wc-variation-form-initialized')) {
						$variationsForm.data('wc-variation-form-initialized', true);

						// Check if WooCommerce variation form plugin is available
						if (typeof $variationsForm.wc_variation_form === 'function') {
							$variationsForm.wc_variation_form();

							// Trigger WooCommerce to check for the pre-selected variation (set in PHP)
							// Use delay to ensure form is fully initialized
							setTimeout(function() {
								$variationsForm.trigger('check_variations');
							}, 300);
						}
				} else {
						// WooCommerce variation script not loaded - use manual handler
						if (typeof writeLogServer === 'function') writeLogServer({event: 'wc_script_not_loaded'}, 'info');
						const variationsData = $variationsForm.data('product_variations');
						if (variationsData && variationsData.length) {
							// Attach change handler to hidden select
							$variationsForm.find('.variation-select-hidden').on('change', function() {
								const selectedValue = $(this).val();
								if (!selectedValue) return;
								// Find matching variation
								for (let i = 0; i < variationsData.length; i++) {
									const v = variationsData[i];
									if (!v.attributes) continue;
									// Simple match: check if any attribute matches selected value
									for (let attr in v.attributes) {
										if (v.attributes[attr] && v.attributes[attr].toLowerCase() === selectedValue.toLowerCase()) {
											// Match found - update price
											mainProductPrice = parseFloat(v.display_price);
											if (typeof writeLogServer === 'function') writeLogServer({event: 'manual_price_update', price: mainProductPrice}, 'info');
											updateSubtotal();
											return;
										}
									}
								}
							});
						}
					}
					else {
						// WooCommerce variation script not loaded - use manual handler
						if (typeof writeLogServer === 'function') writeLogServer({event: 'wc_script_not_loaded', message: 'Using manual variation handler'}, 'info');
						// Parse variation data
						const variationsData = .data('product_variations');
						if (variationsData) {
							// Manual variation change handler
							.find('.variation-select-hidden').on('change', function() {
								const  = ;
								const selectedValue = .val();
								if (!selectedValue) return;
								// Find matching variation
								for (let i = 0; i < variationsData.length; i++) {
									const variation = variationsData[i];
									const attrs = variation.attributes || {};
									// Check if this variation matches
									let matches = true;
									for (let attrKey in attrs) {
										if (attrs[attrKey] && attrs[attrKey].toLowerCase() !== selectedValue.toLowerCase()) {
											matches = false;
											break;
										}
									}
									if (matches && variation.is_in_stock) {
										// Found matching variation - update price
										mainProductPrice = parseFloat(variation.display_price);
										if (typeof writeLogServer === 'function') writeLogServer({event: 'manual_variation_found', price: mainProductPrice, variation_id: variation.variation_id}, 'info');
										updateSubtotal();
										break;
									}
								}
							});
						}
					}
					}

					// Attach WooCommerce event listeners (only once globally)
					if (!window.wcVariationListenersAttached) {
						window.wcVariationListenersAttached = true;

						// Use event delegation on document to avoid duplicates
						$(document).on('found_variation', '.variations_form', function(event, variation) {
							// Update main product price
							if (typeof writeLogServer === 'function') writeLogServer({event: 'found_variation', display_price: variation.display_price, attributes: variation.attributes}, 'info');
							mainProductPrice = parseFloat(variation.display_price);
							console.log('[DEBUG] mainProductPrice updated to:
							if (typeof writeLogServer === 'function') writeLogServer({event: 'mainProductPrice_updated', mainProductPrice: mainProductPrice}, 'info');
							// Update button visual selection based on variation attributes
							if (variation.attributes) {
								Object.keys(variation.attributes).forEach(function(attrKey) {
									const attrValue = variation.attributes[attrKey];
									if (attrValue) {
										// Find and select the matching button
										$('.variation-button[data-attribute="' + attrKey.replace('attribute_', '') + '"][data-value="' + attrValue + '"]')
											.addClass('selected')
											.siblings('.variation-button')
											.removeClass('selected');
									}
								});
							}

							// Update subtotal when variation changes
							updateSubtotal();

							// Update base price for paired mode
							if ($('#btn-paired').hasClass('active') && selectedLeftHalf) {
								if (selectedLeftHalf.product_id === mainProductId) {
									selectedLeftHalf.price = mainProductPrice / 2;
									updateSubtotal();
								}
							}
						});

						// Listen for variation reset
						$(document).on('reset_data', '.variations_form', function() {
								// Only clear button selections if variation is truly being reset (select value is empty)
								const $select = $('.variation-select-hidden');
								if ($select.length && !$select.val()) {
									$('.variation-button').removeClass('selected');
								}
							// Update subtotal when variation is reset
							updateSubtotal();
						});

						// Listen for when variation is not available
						$(document).on('update_variation_values', '.variations_form', function() {
							// This event fires when WooCommerce updates which variations are available
							// We could use this to disable/enable buttons based on availability
						});
					}
				}
			};

			// Try to setup immediately (in case lightbox already open)
			setupVariationForm();
			setTimeout(setDefaultVariation, 500);


			// // Also try when lightbox opens (MagnificPopup event with delay)
			// $(document).on('mfpOpen', function() {
			// 	// Wait for lightbox content to fully render
			// 	setTimeout(function() {
			// 		setupVariationForm();
			// 		// Set default variation after a longer delay to ensure everything is ready
			// 		setTimeout(setDefaultVariation, 500);
			// 	}, 100);
			// });
		}

		// Function to set default variation (first option) - defined at global scope
		function setDefaultVariation() {
			console.log('setDefaultVariation called');
			const $firstButton = $('.variation-button').first();
			console.log('First button found:', $firstButton.length);

			if ($firstButton.length) {
				console.log('Setting default variation...');
				// Add selected class to first button (if not already selected)
				if (!$firstButton.hasClass('selected')) {
					$firstButton.addClass('selected');
				}

				// Update the hidden select
				const value = $firstButton.data('value');
				const attribute = $firstButton.data('attribute');
				const selectName = 'attribute_' + attribute;
				const $hiddenSelect = $('select[name="' + selectName + '"]');

				console.log('Hidden select found:', $hiddenSelect.length, 'Setting value to:', value);

				if ($hiddenSelect.length) {
					$hiddenSelect.val(value);

					// Trigger change event on the select to notify WooCommerce
					$hiddenSelect.trigger('change');

					// Trigger WooCommerce to check variations
					const $variationsForm = $('.variations_form');
					console.log('Variation form found:', $variationsForm.length);
					if ($variationsForm.length) {
						console.log('Form has wc-variation-form:', typeof $variationsForm.wc_variation_form);
						console.log('Form data:', $variationsForm.data('product_variations'));
						console.log('Triggering check_variations');
						$variationsForm.trigger('check_variations');

						// Check result after triggering
						setTimeout(function() {
							const $addToCartBtn = $('.single_add_to_cart_button');
							const $singleVariation = $('.single_variation_wrap');
							console.log('After check_variations:');
							console.log('  - Add to cart button exists:', $addToCartBtn.length);
							console.log('  - Single variation wrap exists:', $singleVariation.length);
							console.log('  - Variation wrap visible:', $singleVariation.length ? $singleVariation.is(':visible') : 'N/A');
						}, 1000);
					} else {
						console.error('Variation form NOT found!');
					}
				}

				console.log('Default variation set to:', value);
			} else {
				console.log('Button already selected or not found');
			}
		}

		function resetAllParams() {
			// Remove any hidden inputs added for submission
			$('input[name="extra_topping_options"]').remove();
			$('input[name="pizza_halves"]').remove();
			$('input[name="paired_products"]').remove();
			$('input[name="special_request"]').remove();

			// Uncheck all topping checkboxes
			$('.topping-checkbox').prop('checked', false);

			// Reset variation buttons
			$('.variation-button').removeClass('selected');
			$('.variation-select-hidden').val('');

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
		initSpecialRequestCounter();
		initVariationHandler();
	});
})(jQuery);
</script>