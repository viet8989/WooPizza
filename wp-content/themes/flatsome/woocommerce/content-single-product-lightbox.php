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
						
						<button class="size-nav-next" type="button">
							<img src="<?php echo esc_url( get_site_url() . '/wp-content/uploads/images/arrow.png' ); ?>" 
								 alt="<?php esc_attr_e( 'Next', 'flatsome' ); ?>">
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
						
						<p class="pizza-size-info">
							<strong><?php esc_html_e( 'Pizza size: 18 Inch (8 slides)', 'flatsome' ); ?></strong>
						</p>
					</div>

					<!-- Paired Pizza Display -->
					<div id="pizza-paired" class="paired-pizza-container">
						<div class="header-section">
							<div class="half-pizza-container">
								<img id="left-pizza" 
									 class="left-pizza-img" 
									 src="<?php echo esc_url( get_site_url() . '/wp-content/uploads/images/pizza2.png' ); ?>"
									 alt="<?php esc_attr_e( 'Left pizza half', 'flatsome' ); ?>">
							</div>
							<div class="half-pizza-container">
								<img id="right-pizza" 
									 class="icon-row" 
									 src="<?php echo esc_url( get_site_url() . '/wp-content/uploads/images/half_pizza.png' ); ?>"
									 alt="<?php esc_attr_e( 'Right pizza half', 'flatsome' ); ?>">
							</div>
						</div>

						<div class="title-section">
							<h1><?php printf( esc_html__( '%s with', 'flatsome' ), esc_html( get_the_title() ) ); ?></h1>
						</div>

						<div class="pizza-grid-wrapper">
							<div class="pizza-grid">
								<?php
								// Get paired products from the same brand
								$brands = wp_get_post_terms( $product_id, 'product_brand' );
								
								if ( ! is_wp_error( $brands ) && ! empty( $brands ) ) {
									$first_brand_id = $brands[0]->term_id;
									
									$paired_products = wc_get_products( array(
										'status'    => 'publish',
										'limit'     => -1,
										'tax_query' => array(
											array(
												'taxonomy' => 'product_brand',
												'field'    => 'term_id',
												'terms'    => $first_brand_id,
											),
										),
									) );

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

											// Half price for paired option
											$half_price = $paired_price / 2;
											?>
											<div class="pizza-card" 
												 data-product-id="<?php echo esc_attr( $paired_id ); ?>"
												 data-product-name="<?php echo esc_attr( $paired_name ); ?>"
												 data-product-price="<?php echo esc_attr( $half_price ); ?>"
												 data-product-image="<?php echo esc_url( $paired_image_url ); ?>">
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
			
			<!-- Extra Cheese Options -->
			<?php
			$cheese_products = get_products_by_category( 25 );
			if ( ! empty( $cheese_products ) ) :
			?>
			<div class="extra-options-section">
				<h4 class="extra-options-title"><?php esc_html_e( 'Add extra cheese:', 'flatsome' ); ?></h4>
				<div class="checkbox-group">
					<?php foreach ( $cheese_products as $cheese ) : ?>
						<label class="topping-label">
							<input type="checkbox" 
								   value="<?php echo esc_attr( $cheese['name'] ); ?>" 
								   class="topping-checkbox" 
								   data-price="<?php echo esc_attr( $cheese['price'] ); ?>" 
								   data-product-id="<?php echo esc_attr( $cheese['id'] ); ?>">
							<span class="topping-text">
								<?php echo esc_html( $cheese['name'] ); ?> 
								(<?php echo wp_kses_post( wc_price( $cheese['price'] ) ); ?>)
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<!-- Extra Cold Cuts Options -->
			<?php
			$coldcuts_products = get_products_by_category( 26 );
			if ( ! empty( $coldcuts_products ) ) :
			?>
			<div class="extra-options-section">
				<h4 class="extra-options-title"><?php esc_html_e( 'Add extra cold cuts:', 'flatsome' ); ?></h4>
				<div class="checkbox-group">
					<?php foreach ( $coldcuts_products as $coldcut ) : ?>
						<label class="topping-label">
							<input type="checkbox" 
								   value="<?php echo esc_attr( $coldcut['name'] ); ?>" 
								   class="topping-checkbox" 
								   data-price="<?php echo esc_attr( $coldcut['price'] ); ?>" 
								   data-product-id="<?php echo esc_attr( $coldcut['id'] ); ?>">
							<span class="topping-text">
								<?php echo esc_html( $coldcut['name'] ); ?> 
								(<?php echo wp_kses_post( wc_price( $coldcut['price'] ) ); ?>)
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>
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
 * Helper function to get products by category
 *
 * @param int $category_id Category term ID
 * @return array Array of product data
 */
function get_products_by_category( $category_id ) {
	$args = array(
		'post_type'      => 'product',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'tax_query'      => array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => absint( $category_id ),
			),
		),
	);

	$query    = new WP_Query( $args );
	$products = array();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			global $product;
			
			if ( $product instanceof WC_Product ) {
				$products[] = array(
					'id'    => get_the_ID(),
					'name'  => get_the_title(),
					'price' => $product->get_price(),
				);
			}
		}
		wp_reset_postdata();
	}

	return $products;
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
	margin-bottom: 15px;
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
	width: 100%;
	height: 100%;
	object-fit: cover;
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

/* Extra Options Section */
.extra-options-section {
	margin: 20px 0;
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
	padding: 8px 20px;
	cursor: pointer;
	margin: 0 !important;
	transition: background 0.2s ease;
	border-radius: 4px;
}

.topping-label:hover {
	background: #f9f9f9;
}

.topping-checkbox {
	margin-right: 10px;
	width: 18px;
	height: 18px;
	cursor: pointer;
}

.topping-text {
	padding-bottom: 2px;
}

/* Bottom Bar */
.qv-spacer {
	height: 100px;
	margin-top: 20px;
}

.qv-bottom-bar {
	position: fixed;
	bottom: 20px;
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

/* Responsive Design */
@media (max-width: 768px) {
	.header-section {
		height: 160px;
	}

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

		// Size Toggle
		function initSizeToggle() {
			$('#btn-whole').on('click', function() {
				$('.size-option').removeClass('active');
				$(this).addClass('active');
				$('#pizza-whole').show();
				$('#pizza-paired').hide();
			});

			$('#btn-paired').on('click', function() {
				$('.size-option').removeClass('active');
				$(this).addClass('active');
				$('#pizza-whole').hide();
				$('#pizza-paired').show();
			});

			// Initialize with whole pizza visible
			$('#btn-whole').trigger('click');
		}

		// Pizza Card Selection
		function initPizzaCardSelection() {
			$(document).on('click', '.pizza-card', function() {
				const $card = $(this);
				const imageUrl = $card.data('product-image');
				
				// Toggle selection
				$card.toggleClass('selected');
				
				// Update right pizza image if selected
				if ($card.hasClass('selected')) {
					$('#right-pizza')
						.attr('src', imageUrl)
						.removeClass('icon-row')
						.addClass('right-pizza-img');
				} else {
					// Reset to default
					$('#right-pizza')
						.attr('src', '<?php echo esc_url( get_site_url() . "/wp-content/uploads/images/half_pizza.png" ); ?>')
						.removeClass('right-pizza-img')
						.addClass('icon-row');
				}
				
				updateSubtotal();
			});
		}

		// Subtotal Calculation
		function updateSubtotal() {
			let subtotal = config.basePrice;

			// Add topping prices
			$('.topping-checkbox:checked').each(function() {
				const price = parseFloat($(this).data('price'));
				if (!isNaN(price)) {
					subtotal += price;
				}
			});

			// Add paired pizza price
			$('.pizza-card.selected').each(function() {
				const price = parseFloat($(this).data('product-price'));
				if (!isNaN(price)) {
					subtotal += price;
				}
			});

			// Update display
			const formatted = new Intl.NumberFormat('vi-VN', config.currencyFormat).format(subtotal);
			$('#sub_total').text(formatted);
		}

		// Topping Checkbox Handler
		function initToppingCheckboxes() {
			$(document).on('change', '.topping-checkbox', updateSubtotal);
		}

		// Add to Cart Handler
		function initAddToCart() {
			$(document).on('click', 'form.cart button[type="submit"], .single_add_to_cart_button', function(e) {
				// Gather topping options
				const toppingOptions = [];
				$('.topping-checkbox:checked').each(function() {
					toppingOptions.push({
						name: $(this).val(),
						price: $(this).data('price'),
						product_id: $(this).data('product-id')
					});
				});

				// Gather paired products
				const pairedProducts = [];
				$('.pizza-card.selected').each(function() {
					const $card = $(this);
					pairedProducts.push({
						product_id: $card.data('product-id'),
						name: $card.data('product-name'),
						price: $card.data('product-price'),
						image: $card.data('product-image')
					});
				});

				// Remove existing hidden inputs
				$('input[name="extra_topping_options"], input[name="paired_products"]').remove();

				// Find the form
				const $form = $(this).closest('form.cart');
				const $target = $form.length ? $form : $('body');

				// Add hidden inputs
				if (toppingOptions.length > 0) {
					$target.append(
						$('<input>', {
							type: 'hidden',
							name: 'extra_topping_options',
							value: JSON.stringify(toppingOptions)
						})
					);
				}

				if (pairedProducts.length > 0) {
					$target.append(
						$('<input>', {
							type: 'hidden',
							name: 'paired_products',
							value: JSON.stringify(pairedProducts)
						})
					);
				}
			});
		}

		// Initialize all handlers
		initSizeToggle();
		initPizzaCardSelection();
		initToppingCheckboxes();
		initAddToCart();
	});
})(jQuery);
</script>