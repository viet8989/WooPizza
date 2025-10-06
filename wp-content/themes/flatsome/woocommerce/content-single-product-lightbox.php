<?php
/**
 * Quick View.
 *
 * @package          Flatsome/WooCommerce/Templates
 * @flatsome-version 3.16.0
 */

defined( 'ABSPATH' ) || exit;

global $post, $product;
$product_price = $product->get_price();
$sub_total = $product_price;

do_action( 'wc_quick_view_before_single_product' );
?>
<div class="product-quick-view-container">
	<div class="row row-collapse mb-0 product" id="product-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="product-gallery large-6 col">
			<?php do_action( 'woocommerce_before_single_product_lightbox_summary' ); ?>

			<!-- Custom Options Section -->
			<div class="product-custom-options" style="margin-top: 20px; background: #fff; padding: 20px; border-radius: 8px;">
				<!-- Pizza Size Selection -->
				<div class="pizza-size-options" style="margin-bottom: 20px;">
					<div style="display: flex; gap: 10px; margin-bottom: 15px;">
						<button class="size-option active" data-size="whole" style="flex: 1; padding: 12px 20px; background: #dc0000; color: white; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 500;">
							<img src="/wp-content/uploads/images/pizza-icon-white.png" alt="Whole pizza" style="width: 24px; height: 24px;">
							<span>Whole pizza</span>
						</button>
						<button class="size-option" data-size="paired" style="flex: 1; padding: 12px 20px; background: #f5f5f5; color: #333; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 500;">
							<img src="/wp-content/uploads/images/pizza-icon-black.png" alt="Paired pizza" style="width: 24px; height: 24px;">
							<span>Paired pizza</span>
						</button>
						<button class="size-nav-next" style="padding: 12px 20px; background: #f5f5f5; border: none; border-radius: 8px; cursor: pointer;">
							<img src="/wp-content/uploads/images/arrow.png" alt="Next" style="width: 20px; height: 20px;">
						</button>
					</div>

					<!-- Pizza Image & Description -->
					<div class="pizza-display" style="text-align: center; margin-bottom: 20px;">
						<?php if ( has_post_thumbnail() ) :
							$image_title = esc_attr( get_the_title( get_post_thumbnail_id() ) );
							$image_link  = wp_get_attachment_url( get_post_thumbnail_id() );
							echo sprintf( '<img src="%s" alt="%s" style="height: auto; border-radius: 3px; margin-bottom: 15px;" />', $image_link, $image_title ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
						else :
							echo sprintf( '<img src="%s" alt="%s" style="height: auto; border-radius: 3px; margin-bottom: 15px;" />', wc_placeholder_img_src( 'woocommerce_single' ), esc_html__( 'Awaiting product image', 'woocommerce' ) ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
						endif;
						?>
						<h3 style="font-size: 24px; margin: 0 0 10px 0; font-weight: bold;"><?php the_title(); ?></h3>
						<p style="color: #666; font-size: 14px; line-height: 1.6; margin-bottom: 10px;">
							<?php echo $product->get_short_description(); ?>
							<p class="read-more-toggle" style="color: #dc0000; cursor: pointer; font-weight: 500;">Read more</p>
						</p>
						<p style="font-size: 14px; color: #333; margin: 0;"><strong>Pizza size: 18 Inch (8 slide)</strong></p>
					</div>

					<!-- Subtotal -->
					<div style="background: #ffc107; padding: 12px 20px; border-radius: 8px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
						<span style="font-weight: 500;">Subtotal:</span>
						<span id='sub_total' style="font-size: 20px; font-weight: bold; color: #dc0000;"><?php echo wc_price($sub_total); ?></span>
					</div>

					<!-- Quantity & Add to Cart -->
					<?php do_action( 'woocommerce_single_product_lightbox_summary' ); ?>
				</div>
			</div>
		</div>

		<div class="product-info summary large-6 col entry-summary" style="font-size:90%;">
			<!-- Extra Cheese Options -->
			<div class="extra-options-section" style="margin-bottom: 20px;">
				<h4 style="font-size: 16px; font-weight: bold; margin-bottom: 12px;">Add extra chees:</h4>
				   <!-- Dynamically load all products with category_id = 25 and display as checkboxes -->
				   <div class="checkbox-group">
					   <?php
					   $args = array(
						   'post_type' => 'product',
						   'posts_per_page' => -1,
						   'tax_query' => array(
							   array(
								   'taxonomy' => 'product_cat',
								   'field'    => 'term_id',
								   'terms'    => 25,
							   ),
						   ),
					   );
					   $cheese_query = new WP_Query($args);
					   if ( $cheese_query->have_posts() ) :
						   while ( $cheese_query->have_posts() ) : $cheese_query->the_post();
							   global $product;
							   $price = $product->get_price();
							   $name = get_the_title();
					   ?>
					   <label style="display: flex; align-items: center; padding: 10px 0; cursor: pointer;">
						   <input type="checkbox" value="<?php echo esc_attr($name); ?> (<?php echo esc_attr($price); ?>)" class="cheese-checkbox" data-price="<?php echo esc_attr($price); ?>" style="margin-right: 10px; width: 18px; height: 18px;">
						   <span><?php echo esc_html($name); ?> (<?php echo wc_price($price); ?>)</span>
					   </label>
					   <?php
						   endwhile;
						   wp_reset_postdata();
					   else :
					   ?>
					   <span>No cheese options found.</span>
					   <?php endif; ?>
				   </div>
			</div>

			<!-- <button class="toggle-more" style="margin-top: 10px; background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 5px; color: #666;">
				<img src="https://terravivapizza.com/wp-content/uploads/images/arrow-down.png" alt="Show more" style="width: 16px; height: 16px;">
			</button> -->

			<!-- Extra Cold Cuts Options -->
			<div class="extra-options-section">
				<h4 style="font-size: 16px; font-weight: bold; margin-bottom: 12px;">Add extra cold cuts:</h4>
				<!-- Dynamically load all products with category_id = 26 and display as checkboxes -->
				   <div class="checkbox-group">
					   <?php
					   $args = array(
						   'post_type' => 'product',
						   'posts_per_page' => -1,
						   'tax_query' => array(
							   array(
								   'taxonomy' => 'product_cat',
								   'field'    => 'term_id',
								   'terms'    => 26,
							   ),
						   ),
					   );
					   $cheese_query = new WP_Query($args);
					   if ( $cheese_query->have_posts() ) :
						   while ( $cheese_query->have_posts() ) : $cheese_query->the_post();
							   global $product;
							   $price = $product->get_price();
							   $name = get_the_title();
					   ?>
					   <label style="display: flex; align-items: center; padding: 10px 0; cursor: pointer;">
						   <input type="checkbox" value="<?php echo esc_attr($name); ?> (<?php echo esc_attr($price); ?>)" style="margin-right: 10px; width: 18px; height: 18px;">
						   <span><?php echo esc_html($name); ?> (<?php echo wc_price($price); ?>)</span>
					   </label>
					   <?php
						   endwhile;
						   wp_reset_postdata();
					   else :
					   ?>
					   <span>No cheese options found.</span>
					   <?php endif; ?>
				   </div>
			</div>
		</div>
	</div>
</div>


<style>
	.size-option.active {
		background: #dc0000 !important;
		color: white !important;
	}
	.size-option.active img {
		filter: brightness(0) invert(1);
	}
	.size-option:hover {
		opacity: 0.9;
	}
	.add-to-cart-btn:hover {
		background: #b30000;
	}
	.qty-minus:hover, .qty-plus:hover {
		background: #f5f5f5 !important;
	}
</style>

<script>
	jQuery(document).ready(function($) {
		// Size toggle
		$('.size-option').on('click', function() {
			$('.size-option').removeClass('active');
			$(this).addClass('active');
		});

		// Quantity controls
		$('.qty-minus').on('click', function() {
			var input = $(this).siblings('input[type="number"]');
			var val = parseInt(input.val());
			if (val > 1) {
				input.val(val - 1);
			}
		});

		$('.qty-plus').on('click', function() {
			var input = $(this).siblings('input[type="number"]');
			var val = parseInt(input.val());
			input.val(val + 1);
		});

		// Read more toggle
		$('.read-more-toggle').on('click', function() {
			$(this).text($(this).text() === 'Read more' ? 'Read less' : 'Read more');
		});
	});
</script>
<script>
	jQuery(document).ready(function($) {
		// Subtotal calculation for cheese checkboxes
		var baseSubtotal = <?php echo json_encode((float)$product_price); ?>;
		function updateSubtotal() {
			var subtotal = baseSubtotal;
			$('.cheese-checkbox:checked').each(function() {
				var price = parseFloat($(this).data('price'));
				if (!isNaN(price)) {
					subtotal += price;
				}
			});
			console.log("Updated Subtotal: " + subtotal);
			// Update the subtotal display
			var formatted = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(subtotal);
			$('#sub_total').text(formatted);
		}
		$(document).on('change', '.cheese-checkbox', updateSubtotal);
	});
</script>

<?php do_action( 'wc_quick_view_after_single_product' ); ?>