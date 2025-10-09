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
		<div class="product-gallery large-6 col" style="padding: 0 10px !important;">
			<?php do_action( 'woocommerce_before_single_product_lightbox_summary' ); ?>

			<!-- Custom Options Section -->
			<div class="product-custom-options" style="margin-top: 20px; background: #fff; border-radius: 8px;">
				<!-- Pizza Size Selection -->
				<div class="pizza-size-options" style="margin-bottom: 20px;">
					<div style="display: flex; gap: 10px; margin-bottom: 15px;">
						<button id="btn-whole" class="size-option active" data-size="whole" style="flex: 1; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 500;">
							<img src="/wp-content/uploads/images/pizza-icon-white.png" alt="Whole pizza" style="width: 24px; height: 24px;">
							<span>Whole pizza</span>
						</button>
						<button id="btn-paired" class="size-option" data-size="paired" style="flex: 1; padding: 12px 20px; background: #f5f5f5; color: #333; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 500;">
							<img src="/wp-content/uploads/images/pizza-icon-black.png" alt="Paired pizza" style="width: 24px; height: 24px;">
							<span>Paired pizza</span>
						</button>
						<button class="size-nav-next" style="padding: 12px 20px; background: #f5f5f5; border: none; border-radius: 8px; cursor: pointer;">
							<img src="/wp-content/uploads/images/arrow.png" alt="Next" />
						</button>
					</div>

					<!-- Pizza Image & Description -->
					<div id="pizza-whole" class="pizza-display" style="text-align: center; margin-bottom: 20px;">
						<?php if ( has_post_thumbnail() ) :
							$image_title = esc_attr( get_the_title( get_post_thumbnail_id() ) );
							$image_link = wp_get_attachment_url( get_post_thumbnail_id() );
							echo sprintf( '<img src="%s" alt="%s" style="height: auto; border-radius: 3px; margin-bottom: 15px;" />', $image_link, $image_title ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
						else :
							echo sprintf( '<img src="%s" alt="%s" style="height: auto; border-radius: 3px; margin-bottom: 15px;" />', wc_placeholder_img_src( 'woocommerce_single' ), esc_html__( 'Awaiting product image', 'woocommerce' ) ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
						endif;
						?>
						<h3 style="font-size: 24px; margin: 0 0 10px 0; font-weight: bold;"><?php the_title(); ?></h3>
						<p style="color: #666; font-size: 14px; line-height: 1.6; margin-bottom: 10px;">
							<?php echo $product->get_short_description(); 
							echo '<br />';  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							$product_link = get_permalink( $product->get_id() );
							echo '<a href="' . esc_url( $product_link ) . '" class="read-more-toggle" style="color: #dc0000; cursor: pointer; font-weight: 500;">Read more</a>';  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</p>
						<p style="font-size: 14px; color: #333; margin: 0;"><strong>Pizza size: 18 Inch (8 slide)</strong></p>
					</div>
					<!-- Paired Pizza -->
					<div id="pizza-paired" class="container">
						<!-- Header with Half Pizza and Choose Section -->
						<div class="header-section">
							<div class="half-pizza-container">
								<img id="left-pizza" class="left-pizza-img" src="https://terravivapizza.com/wp-content/uploads/images/pizza2.png">
							</div>
							<div class="half-pizza-container">
								<img id="right-pizza" class="icon-row" src="https://terravivapizza.com/wp-content/uploads/images/half_pizza.png">
								<!-- <img class="right-pizza-img" src="https://terravivapizza.com/wp-content/uploads/images/pizza2.png"> -->
							</div>
						</div>

						<!-- Title -->
						<div class="title-section">
							<h1>Terravia Pizza with</h1>
						</div>

						<div style="height: 300px; overflow: auto;">
							<!-- Pizza Grid -->
							<div class="pizza-grid">
								<?php $brands = wp_get_post_terms($product->get_id(), 'product_brand');
									echo '<script>console.log("A")</script>';
									if ( ! is_wp_error( $brands ) && ! empty( $brands ) ) {
										echo '<script>console.log("B")</script>';
										$first_brand_id = $brands[0]->term_id;
										echo '<script>console.log("C")</script>';
										$products_paired = wc_get_products( array(
											'status' => 'publish',
											'tax_query' => array(
												array(
													'taxonomy' => 'product_brand',
													'field'    => 'term_id',
													'terms'    => $first_brand_id,
												),
											),
										) );
										echo '<script>console.log("D")</script>';
										if ( ! empty( $products_paired ) ) {
											echo '<script>console.log("E")</script>';
											foreach ( $products_paired as $prod ) {
												echo '<script>console.log("F")</script>';
												if ( $prod->get_id() == $product->get_id() ) {
													continue; // Skip the current product
												}
												echo '<script>console.log("G")</script>';
												// Display product details
												$image = wp_get_attachment_image_src( get_post_thumbnail_id( $prod->get_id() ), 'single-post-thumbnail' );
												echo '<script>console.log("H")</script>';
												$image_url = $image ? $image[0] : wc_placeholder_img_src( 'woocommerce_single' );
												echo '<script>console.log("K")</script>';
												$name = $prod->get_name();
												echo '<script>console.log("L")</script>';
												$price = $prod->get_price_html();
												echo '<script>console.log("M")</script>';
												echo '<div class="pizza-card" onclick="selectPizza(\'' . esc_url( $image_url ) . '\')">
														<img src="' . esc_url( $image_url ) . '" 
															alt="' . esc_attr( $name ) . '" 
															class="pizza-card-img">
														<div class="pizza-card-info">
															<div class="pizza-card-title">' . esc_html( $name ) . '</div>
															<div class="pizza-card-price">' . wp_kses_post( $price ) . '</div>
														</div>
													</div>';	
												echo '<script>console.log("N")</script>';
											}	
										}
									}
								?>
							</div>
						</div>
					</div>
				<script>
					function selectPizza(src_image) {
						// Update the main pizza image
						const mainImage = document.getElementById('right-pizza');
						mainImage.src = src_image;
						mainImage.className = 'right-pizza-img';
					}
				</script>
				</div>
			</div>
		</div>

		<div class="product-info summary large-6 col entry-summary" style="padding: 0 20px !important; font-size:90%;">
			<!-- Extra Cheese Options -->
			<div class="extra-options-section" style="margin: 20px 0;">
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
					   <label style="display: flex; align-items: center; padding: 0 20px 0; cursor: pointer; margin: 0 !important;">
							<input type="checkbox" value="<?php echo esc_attr($name); ?> (<?php echo esc_attr($price); ?>)" class="topping-checkbox" data-price="<?php echo esc_attr($price); ?>" data-product-id="<?php echo esc_attr(get_the_ID()); ?>" style="margin-right: 10px; width: 18px; height: 18px;">
						   	<span style='padding-bottom: 10px;'><?php echo esc_html($name); ?> (<?php echo wc_price($price); ?>)</span>
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
					   <label style="display: flex; align-items: center; padding: 0 20px 0; cursor: pointer; margin: 0 !important;">
						   <input type="checkbox" value="<?php echo esc_attr($name); ?> (<?php echo esc_attr($price); ?>)" class="topping-checkbox" data-price="<?php echo esc_attr($price); ?>" data-product-id="<?php echo esc_attr(get_the_ID()); ?>" style="margin-right: 10px; width: 18px; height: 18px;">
						   <span style='padding-bottom: 10px;'><?php echo esc_html($name); ?> (<?php echo wc_price($price); ?>)</span>
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
<div style="height: 100px; margin-top: 20px;"></div> <!-- Spacer to avoid content being hidden behind fixed bottom bar -->	
	<div class="large-6 col" style="bottom: 20px; left: 50%; transform: translateX(-50%); width: calc(100% - 40px);">
		<!-- Subtotal -->
		<div style="background: #ffc107; padding: 12px 20px; border-radius: 8px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
			<span style="font-weight: 500;">Subtotal:</span>
			<span id='sub_total' style="font-size: 20px; font-weight: bold; color: #dc0000;"><?php echo wc_price($sub_total); ?></span>
		</div>
		<!-- Quantity & Add to Cart -->
		<?php do_action( 'woocommerce_single_product_lightbox_summary' ); ?>
	</div>
</div> <!-- Spacer to avoid content being hidden behind fixed bottom bar -->


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

	.container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
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

        .choose-section {
            width: 50%;
            background: #a67c52;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            color: white;
            padding: 20px;
        }

        .icon-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sound-icon, .plus-icon {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .sound-icon:hover, .plus-icon:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        .sound-icon::before {
            content: '';
            width: 0;
            height: 0;
            border-left: 8px solid white;
            border-top: 5px solid transparent;
            border-bottom: 5px solid transparent;
            margin-right: -4px;
        }

        .sound-icon::after {
            content: '';
            width: 6px;
            height: 10px;
            border: 2px solid white;
            border-left: none;
            border-radius: 0 3px 3px 0;
        }

        .plus-icon {
            font-size: 24px;
            font-weight: bold;
            color: white;
            line-height: 1;
        }

        .choose-text {
            text-align: center;
            font-size: 15px;
            line-height: 1.4;
        }

        /* Title Section */
        .title-section {
            padding: 24px 20px 20px;
        }

        .title-section h1 {
            font-size: 28px;
            font-weight: 700;
            color: #000;
        }

        /* Pizza Grid */
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
            transition: all 0.3s;
            cursor: pointer;
        }

        .pizza-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
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

        .pizza-card-price {
            font-size: 13px;
            color: #999;
        }

        /* Responsive */
        @media (max-width: 600px) {
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
        }
</style>

<script>
	jQuery(document).ready(function($) {
		$('#btn-whole').on('click', function() {
			$('#btn-paired').removeClass('active');
			$(this).addClass('active');
			$('#pizza-whole').show();
			$('#pizza-paired').hide();
		});

		$('#btn-paired').on('click', function() {
			$('#btn-whole').removeClass('active');
			$(this).addClass('active');
			$('#pizza-whole').hide();
			$('#pizza-paired').show();
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

		$('#btn-whole').click();
	});
</script>
<script>
jQuery(document).ready(function($) {
	// Subtotal calculation for cheese checkboxes
	var baseSubtotal = <?php echo json_encode((float)$product_price); ?>;
	function updateSubtotal() {
		var subtotal = baseSubtotal;
		$('.topping-checkbox:checked').each(function() {
			var price = parseFloat($(this).data('price'));
			if (!isNaN(price)) {
				subtotal += price;
			}
		});
		// Update the subtotal display
		var formatted = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(subtotal);
		$('#sub_total').text(formatted);
	}
	$(document).on('change', '.topping-checkbox', updateSubtotal);

	// Add to cart: include selected cheese products
	$(document).on('click', 'form.cart button[type="submit"], .single_add_to_cart_button', function(e) {
		console.log('Add to cart clicked');
		// Gather selected cheese options
		var cheeseOptions = [];
		$('.topping-checkbox:checked').each(function() {
			cheeseOptions.push({
				name: $(this).val(),
				price: $(this).data('price'),
				product_id: $(this).data('product-id')
			});
		});
		console.log('Selected cheese options:', cheeseOptions);
		// Add to hidden input or send via AJAX (for AJAX add to cart)
		// Remove any previous hidden input
		$('input[name="extra_cheese_options"]').remove();
		// Add hidden input to the form
		if (cheeseOptions.length > 0) {
			var input = $('<input>').attr({
				type: 'hidden',
				name: 'extra_cheese_options',
				value: JSON.stringify(cheeseOptions)
			});
			// Try to append to the closest form
			var form = $(this).closest('form.cart');
			if (form.length) {
				form.append(input);
			} else {
				// If no form, append to body (for AJAX handlers)
				$('body').append(input);
			}
		}
	});
});
</script>

<?php do_action( 'wc_quick_view_after_single_product' ); ?>