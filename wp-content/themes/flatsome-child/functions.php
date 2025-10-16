<?php
// Add custom Theme Functions here

function enqueue_custom_js()
{
    wp_enqueue_script('custom-js', get_stylesheet_directory_uri() . '/js/custom.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_custom_js');

add_filter('woocommerce_currency_symbol', function ($currency_symbol, $currency) {
    if ($currency === 'VND') {
        $currency_symbol = '₫';
    }
    return $currency_symbol;
}, 10, 2);

// Disable sticky header by overriding theme mod
add_filter('theme_mod_header_sticky', '__return_zero');
add_filter('get_theme_mod_header_sticky', '__return_zero');

// Force disable sticky header in header classes
add_filter('flatsome_header_class', function($classes) {
    // Remove any sticky-related classes
    $classes = array_diff($classes, ['has-sticky', 'sticky-jump', 'sticky-fade', 'sticky-hide-on-scroll']);
    return $classes;
}, 999);

// Aggressively remove sticky header with JavaScript
function remove_sticky_header_script() {
    ?>
    <script type="text/javascript">
    (function($) {
        // Function to disable sticky
        function disableSticky() {
            // Remove all sticky classes
            $('#header, .header, .header-wrapper').removeClass('has-sticky stuck sticky-jump sticky-fade sticky-hide-on-scroll');

            // Force position relative
            $('#header, .header-wrapper').css({
                'position': 'relative !important',
                'top': 'auto !important',
                'left': 'auto !important',
                'right': 'auto !important',
                'transform': 'none !important'
            }).attr('style', function(i, style) {
                return style ? style.replace(/position\s*:\s*fixed/gi, 'position: relative') : style;
            });
        }

        // Run on page load
        $(document).ready(disableSticky);

        // Run after a delay to catch late scripts
        setTimeout(disableSticky, 100);
        setTimeout(disableSticky, 500);
        setTimeout(disableSticky, 1000);

        // Prevent scroll events from making header sticky
        $(window).on('scroll.disableSticky', function() {
            disableSticky();
        });

        // Use MutationObserver to watch for class changes
        if (window.MutationObserver) {
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'class') {
                        var target = $(mutation.target);
                        if (target.hasClass('stuck') || target.hasClass('has-sticky')) {
                            disableSticky();
                        }
                    }
                });
            });

            var header = document.getElementById('header');
            if (header) {
                observer.observe(header, { attributes: true, attributeFilter: ['class'] });
            }
        }
    })(jQuery);
    </script>
    <?php
}
add_action('wp_head', 'remove_sticky_header_script', 999);

// Viet add custom functions below this line

// AJAX handler to get toppings for a product based on cross-sell IDs
add_action( 'wp_ajax_get_product_toppings', 'ajax_get_product_toppings' );
add_action( 'wp_ajax_nopriv_get_product_toppings', 'ajax_get_product_toppings' );

function ajax_get_product_toppings() {
	// Verify nonce for security
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'product_toppings_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		return;
	}

	// Get cross-sell IDs from POST data
	if ( ! isset( $_POST['cross_sell_ids'] ) || ! is_array( $_POST['cross_sell_ids'] ) ) {
		wp_send_json_error( array( 'message' => 'No cross-sell IDs provided' ) );
		return;
	}

	$cross_sell_ids = array_map( 'intval', $_POST['cross_sell_ids'] );

	// Get topping categories
	$parent_category_id = 25;
	$topping_categories = get_terms( array(
		'taxonomy'   => 'product_cat',
		'parent'     => $parent_category_id,
		'hide_empty' => false,
	) );

	$toppings_by_category = array();

	if ( empty( $cross_sell_ids ) ) {
		wp_send_json_success( array( 'toppings' => $toppings_by_category ) );
		return;
	}

	// Get all products from cross-sell IDs
	$products = wc_get_products( array(
		'include' => $cross_sell_ids,
		'status'  => 'publish',
		'limit'   => -1,
	) );

	if ( ! empty( $products ) && ! empty( $topping_categories ) && ! is_wp_error( $topping_categories ) ) {
		foreach ( $products as $product ) {
			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			// Get product categories
			$product_categories = $product->get_category_ids();

			// Check which topping category this product belongs to
			foreach ( $topping_categories as $topping_cat ) {
				if ( in_array( $topping_cat->term_id, $product_categories ) ) {
					if ( ! isset( $toppings_by_category[ $topping_cat->term_id ] ) ) {
						$toppings_by_category[ $topping_cat->term_id ] = array(
							'category_name' => $topping_cat->name,
							'category_slug' => $topping_cat->slug,
							'products'      => array(),
						);
					}

					$toppings_by_category[ $topping_cat->term_id ]['products'][] = array(
						'id'    => $product->get_id(),
						'name'  => $product->get_name(),
						'price' => $product->get_price(),
						'price_formatted' => wc_price( $product->get_price() ),
					);

					break; // Product found in this category, no need to check others
				}
			}
		}
	}

	wp_send_json_success( array( 'toppings' => $toppings_by_category ) );
}

// Split Products menu into Pizza and Topping menus in WordPress admin
add_action( 'admin_menu', 'add_pizza_and_topping_admin_menus', 99 );
function add_pizza_and_topping_admin_menus() {
	// Add Pizza menu (top level)
	add_menu_page(
		__( 'Pizzas', 'flatsome' ),          // Page title
		__( 'Pizzas', 'flatsome' ),          // Menu title
		'edit_products',                      // Capability
		'edit-pizza-products',                // Menu slug
		'display_pizza_products_page',        // Function to display the page
		'dashicons-food',                     // Icon
		56                                    // Position (after WooCommerce Products)
	);

	// Add submenu for All Pizzas
	add_submenu_page(
		'edit-pizza-products',
		__( 'All Pizzas', 'flatsome' ),
		__( 'All Pizzas', 'flatsome' ),
		'edit_products',
		'edit-pizza-products',
		'display_pizza_products_page'
	);

	// Add submenu for Add New Pizza
	add_submenu_page(
		'edit-pizza-products',
		__( 'Add New Pizza', 'flatsome' ),
		__( 'Add New', 'flatsome' ),
		'edit_products',
		'post-new.php?post_type=product&product_type=pizza'
	);

	// Add Topping menu (top level)
	add_menu_page(
		__( 'Toppings', 'flatsome' ),        // Page title
		__( 'Toppings', 'flatsome' ),        // Menu title
		'edit_products',                      // Capability
		'edit-topping-products',              // Menu slug
		'display_topping_products_page',      // Function to display the page
		'dashicons-carrot',                   // Icon
		57                                    // Position (after Pizza)
	);

	// Add submenu for All Toppings
	add_submenu_page(
		'edit-topping-products',
		__( 'All Toppings', 'flatsome' ),
		__( 'All Toppings', 'flatsome' ),
		'edit_products',
		'edit-topping-products',
		'display_topping_products_page'
	);

	// Add submenu for Add New Topping
	add_submenu_page(
		'edit-topping-products',
		__( 'Add New Topping', 'flatsome' ),
		__( 'Add New', 'flatsome' ),
		'edit_products',
		'post-new.php?post_type=product&product_type=topping'
	);
}

// Display Pizza products page
function display_pizza_products_page() {
	// Redirect to WooCommerce products page with pizza filter
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Pizzas', 'flatsome' ); ?></h1>
		<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product&product_type=pizza' ) ); ?>" class="page-title-action">
			<?php esc_html_e( 'Add New', 'flatsome' ); ?>
		</a>
		<hr class="wp-header-end">
		<?php
		// Get pizza category term
		$pizza_cat = get_term_by( 'slug', 'pizza', 'product_cat' );
		if ( ! $pizza_cat ) {
			// Try to find by name
			$pizza_terms = get_terms( array(
				'taxonomy' => 'product_cat',
				'name' => 'Pizza',
				'hide_empty' => false,
			) );
			if ( ! empty( $pizza_terms ) && ! is_wp_error( $pizza_terms ) ) {
				$pizza_cat = $pizza_terms[0];
			}
		}

		// Query pizza products
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
		);

		// Add category filter if pizza category exists
		if ( $pizza_cat ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'product_cat',
					'field' => 'term_id',
					'terms' => $pizza_cat->term_id,
					'operator' => 'IN',
				),
			);
		}

		$products = new WP_Query( $args );

		// Display products in a table
		?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th style="width: 50px;">ID</th>
					<th style="width: 80px;">Image</th>
					<th>Name</th>
					<th>Price</th>
					<th>Categories</th>
					<th>Stock</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( $products->have_posts() ) :
					while ( $products->have_posts() ) : $products->the_post();
						$product = wc_get_product( get_the_ID() );
						?>
						<tr>
							<td><?php echo esc_html( get_the_ID() ); ?></td>
							<td><?php echo $product->get_image( 'thumbnail' ); ?></td>
							<td>
								<strong>
									<a href="<?php echo esc_url( get_edit_post_link( get_the_ID() ) ); ?>">
										<?php echo esc_html( get_the_title() ); ?>
									</a>
								</strong>
							</td>
							<td><?php echo $product->get_price_html(); ?></td>
							<td><?php echo wc_get_product_category_list( get_the_ID(), ', ' ); ?></td>
							<td><?php echo esc_html( $product->get_stock_status() ); ?></td>
							<td>
								<a href="<?php echo esc_url( get_edit_post_link( get_the_ID() ) ); ?>" class="button button-small">
									<?php esc_html_e( 'Edit', 'flatsome' ); ?>
								</a>
							</td>
						</tr>
						<?php
					endwhile;
				else :
					?>
					<tr>
						<td colspan="7"><?php esc_html_e( 'No pizzas found.', 'flatsome' ); ?></td>
					</tr>
					<?php
				endif;
				wp_reset_postdata();
				?>
			</tbody>
		</table>
	</div>
	<?php
}

// Display Topping products page
function display_topping_products_page() {
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Toppings', 'flatsome' ); ?></h1>
		<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product&product_type=topping' ) ); ?>" class="page-title-action">
			<?php esc_html_e( 'Add New', 'flatsome' ); ?>
		</a>
		<hr class="wp-header-end">
		<?php
		// Get topping categories (have parent category 15 based on CLAUDE.md)
		$topping_cats = array();

		// Get all child categories of parent category 15
		$child_categories = get_terms( array(
			'taxonomy' => 'product_cat',
			'parent' => 15,
			'hide_empty' => false,
		) );

		if ( ! empty( $child_categories ) && ! is_wp_error( $child_categories ) ) {
			foreach ( $child_categories as $cat ) {
				$topping_cats[] = $cat->term_id;
			}
		}

		// Query topping products
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
			'tax_query' => array(
				array(
					'taxonomy' => 'product_cat',
					'field' => 'term_id',
					'terms' => $topping_cats,
					'operator' => 'IN',
				),
			),
		);

		$products = new WP_Query( $args );

		// Display products in a table
		?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th style="width: 50px;">ID</th>
					<th style="width: 80px;">Image</th>
					<th>Name</th>
					<th>Price</th>
					<th>Categories</th>
					<th>Stock</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( $products->have_posts() ) :
					while ( $products->have_posts() ) : $products->the_post();
						$product = wc_get_product( get_the_ID() );
						?>
						<tr>
							<td><?php echo esc_html( get_the_ID() ); ?></td>
							<td><?php echo $product->get_image( 'thumbnail' ); ?></td>
							<td>
								<strong>
									<a href="<?php echo esc_url( get_edit_post_link( get_the_ID() ) ); ?>">
										<?php echo esc_html( get_the_title() ); ?>
									</a>
								</strong>
							</td>
							<td><?php echo $product->get_price_html(); ?></td>
							<td><?php echo wc_get_product_category_list( get_the_ID(), ', ' ); ?></td>
							<td><?php echo esc_html( $product->get_stock_status() ); ?></td>
							<td>
								<a href="<?php echo esc_url( get_edit_post_link( get_the_ID() ) ); ?>" class="button button-small">
									<?php esc_html_e( 'Edit', 'flatsome' ); ?>
								</a>
							</td>
						</tr>
						<?php
					endwhile;
				else :
					?>
					<tr>
						<td colspan="7"><?php esc_html_e( 'No toppings found.', 'flatsome' ); ?></td>
					</tr>
					<?php
				endif;
				wp_reset_postdata();
				?>
			</tbody>
		</table>
	</div>
	<?php
}

// Save custom options to cart item data
add_filter( 'woocommerce_add_cart_item_data', 'save_custom_pizza_options_to_cart', 10, 3 );
function save_custom_pizza_options_to_cart( $cart_item_data, $product_id, $variation_id ) {
	// Handle extra topping options (cheese + cold cuts)
	if ( isset( $_POST['extra_topping_options'] ) && ! empty( $_POST['extra_topping_options'] ) ) {
		$topping_options = json_decode( stripslashes( $_POST['extra_topping_options'] ), true );

		if ( is_array( $topping_options ) && ! empty( $topping_options ) ) {
			$cart_item_data['extra_topping_options'] = $topping_options;
		}
	}

	// Handle pizza halves (new structure)
	if ( isset( $_POST['pizza_halves'] ) && ! empty( $_POST['pizza_halves'] ) ) {
		$pizza_halves = json_decode( stripslashes( $_POST['pizza_halves'] ), true );

		if ( is_array( $pizza_halves ) && ! empty( $pizza_halves ) ) {
			$cart_item_data['pizza_halves'] = $pizza_halves;
		}
	}

	// Handle paired products (legacy support - backward compatibility)
	if ( isset( $_POST['paired_products'] ) && ! empty( $_POST['paired_products'] ) ) {
		$paired_products = json_decode( stripslashes( $_POST['paired_products'] ), true );

		if ( is_array( $paired_products ) && ! empty( $paired_products ) ) {
			$cart_item_data['paired_products'] = $paired_products;
		}
	}

	// Handle special request
	if ( isset( $_POST['special_request'] ) && ! empty( trim( $_POST['special_request'] ) ) ) {
		$special_request = sanitize_textarea_field( $_POST['special_request'] );
		$cart_item_data['special_request'] = $special_request;
	}

	return $cart_item_data;
}

// Display custom options in cart
add_filter( 'woocommerce_get_item_data', 'display_custom_pizza_options_in_cart', 10, 2 );
function display_custom_pizza_options_in_cart( $item_data, $cart_item ) {
	// Display extra toppings (whole pizza)
	if ( isset( $cart_item['extra_topping_options'] ) && ! empty( $cart_item['extra_topping_options'] ) ) {
		$topping_names = array();

		foreach ( $cart_item['extra_topping_options'] as $topping ) {
			if ( isset( $topping['name'] ) && isset( $topping['price'] ) ) {
				$topping_names[] = sprintf(
					'%s %s',
					esc_html( $topping['name'] ),
					wc_price( $topping['price'] )
				);
			}
		}

		if ( ! empty( $topping_names ) ) {
			$item_data[] = array(
				'key'     => __( 'Add', 'flatsome' ),
				'value'   => implode( '<br>', $topping_names ),
				'display' => '',
			);
		}
	}

	// Display pizza halves (new structure with per-half toppings)
	if ( isset( $cart_item['pizza_halves'] ) && ! empty( $cart_item['pizza_halves'] ) ) {
		$halves = $cart_item['pizza_halves'];

		// Display left half
		if ( isset( $halves['left_half'] ) && ! empty( $halves['left_half'] ) ) {
			$left = $halves['left_half'];
			if ( isset( $left['name'] ) ) {
				$left_display = esc_html( $left['name'] );

				// Add left half toppings
				if ( isset( $left['toppings'] ) && ! empty( $left['toppings'] ) ) {
					$left_topping_names = array();
					foreach ( $left['toppings'] as $topping ) {
						if ( isset( $topping['name'] ) && isset( $topping['price'] ) ) {
							$left_topping_names[] = sprintf(
								'%s %s',
								esc_html( $topping['name'] ),
								wc_price( $topping['price'] )
							);
						}
					}

					if ( ! empty( $left_topping_names ) ) {
						$left_display .= '<br>Add ' . implode( '<br>', $left_topping_names );
					}
				}

				$item_data[] = array(
					'key'     => strtoupper( $left['name'] ),
					'value'   => $left_display,
					'display' => '',
				);
			}
		}

		// Display right half
		if ( isset( $halves['right_half'] ) && ! empty( $halves['right_half'] ) ) {
			$right = $halves['right_half'];
			if ( isset( $right['name'] ) ) {
				$right_display = esc_html( $right['name'] );

				// Add right half toppings
				if ( isset( $right['toppings'] ) && ! empty( $right['toppings'] ) ) {
					$right_topping_names = array();
					foreach ( $right['toppings'] as $topping ) {
						if ( isset( $topping['name'] ) && isset( $topping['price'] ) ) {
							$right_topping_names[] = sprintf(
								'%s %s',
								esc_html( $topping['name'] ),
								wc_price( $topping['price'] )
							);
						}
					}

					if ( ! empty( $right_topping_names ) ) {
						$right_display .= '<br>Add ' . implode( '<br>', $right_topping_names );
					}
				}

				$item_data[] = array(
					'key'     => strtoupper( $right['name'] ),
					'value'   => $right_display,
					'display' => '',
				);
			}
		}
	}

	// Display paired products (legacy support)
	if ( isset( $cart_item['paired_products'] ) && ! empty( $cart_item['paired_products'] ) ) {
		foreach ( $cart_item['paired_products'] as $paired ) {
			if ( isset( $paired['name'] ) && isset( $paired['price'] ) ) {
				$item_data[] = array(
					'key'     => __( 'Paired with', 'flatsome' ),
					'value'   => sprintf(
						'%s (+%s)',
						esc_html( $paired['name'] ),
						wc_price( $paired['price'] )
					),
					'display' => '',
				);
			}
		}
	}

	// Display special request
	if ( isset( $cart_item['special_request'] ) && ! empty( $cart_item['special_request'] ) ) {
		$item_data[] = array(
			'key'     => __( 'Special Request', 'flatsome' ),
			'value'   => esc_html( $cart_item['special_request'] ),
			'display' => '',
		);
	}

	return $item_data;
}

// Add custom options price to cart item price
add_action( 'woocommerce_before_calculate_totals', 'add_custom_pizza_options_price', 10, 1 );
function add_custom_pizza_options_price( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}

	if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
		return;
	}

	foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
		$new_total_price = 0;
		$is_paired_mode = false;

		// Check if pizza halves (new structure with per-half toppings)
		if ( isset( $cart_item['pizza_halves'] ) && ! empty( $cart_item['pizza_halves'] ) ) {
			$is_paired_mode = true;
			$halves = $cart_item['pizza_halves'];

			// Add left half price
			if ( isset( $halves['left_half']['price'] ) ) {
				$new_total_price += floatval( $halves['left_half']['price'] );
			}

			// Add left half toppings
			if ( isset( $halves['left_half']['toppings'] ) && ! empty( $halves['left_half']['toppings'] ) ) {
				foreach ( $halves['left_half']['toppings'] as $topping ) {
					if ( isset( $topping['price'] ) ) {
						$new_total_price += floatval( $topping['price'] );
					}
				}
			}

			// Add right half price
			if ( isset( $halves['right_half']['price'] ) ) {
				$new_total_price += floatval( $halves['right_half']['price'] );
			}

			// Add right half toppings
			if ( isset( $halves['right_half']['toppings'] ) && ! empty( $halves['right_half']['toppings'] ) ) {
				foreach ( $halves['right_half']['toppings'] as $topping ) {
					if ( isset( $topping['price'] ) ) {
						$new_total_price += floatval( $topping['price'] );
					}
				}
			}
		} else {
			// Whole pizza mode - start with base price
			$new_total_price = $cart_item['data']->get_price();
		}

		// Add topping prices (whole pizza toppings)
		if ( isset( $cart_item['extra_topping_options'] ) && ! empty( $cart_item['extra_topping_options'] ) ) {
			foreach ( $cart_item['extra_topping_options'] as $topping ) {
				if ( isset( $topping['price'] ) ) {
					$new_total_price += floatval( $topping['price'] );
				}
			}
		}

		// Add paired product prices (legacy support)
		if ( isset( $cart_item['paired_products'] ) && ! empty( $cart_item['paired_products'] ) ) {
			foreach ( $cart_item['paired_products'] as $paired ) {
				if ( isset( $paired['price'] ) ) {
					$new_total_price += floatval( $paired['price'] );
				}
			}
		}

		// Set the new price
		if ( $is_paired_mode || isset( $cart_item['extra_topping_options'] ) || isset( $cart_item['paired_products'] ) ) {
			$cart_item['data']->set_price( $new_total_price );
		}
	}
}

// Save custom options to order items
add_action( 'woocommerce_checkout_create_order_line_item', 'save_custom_pizza_options_to_order_items', 10, 4 );
function save_custom_pizza_options_to_order_items( $item, $cart_item_key, $values, $order ) {
	// Save topping options (whole pizza)
	if ( isset( $values['extra_topping_options'] ) && ! empty( $values['extra_topping_options'] ) ) {
		$item->add_meta_data( '_extra_topping_options', $values['extra_topping_options'], true );

		// Format for display
		$topping_names = array();
		foreach ( $values['extra_topping_options'] as $topping ) {
			if ( isset( $topping['name'] ) && isset( $topping['price'] ) ) {
				$topping_names[] = sprintf(
					'%s %s',
					esc_html( $topping['name'] ),
					wc_price( $topping['price'] )
				);
			}
		}

		if ( ! empty( $topping_names ) ) {
			$item->add_meta_data( __( 'Add', 'flatsome' ), implode( "\n", $topping_names ), true );
		}
	}

	// Save pizza halves (new structure with per-half toppings)
	if ( isset( $values['pizza_halves'] ) && ! empty( $values['pizza_halves'] ) ) {
		$item->add_meta_data( '_pizza_halves', $values['pizza_halves'], true );

		$halves = $values['pizza_halves'];

		// Display left half
		if ( isset( $halves['left_half'] ) && ! empty( $halves['left_half'] ) ) {
			$left = $halves['left_half'];
			if ( isset( $left['name'] ) ) {
				$left_display = esc_html( $left['name'] );

				// Add left half toppings
				if ( isset( $left['toppings'] ) && ! empty( $left['toppings'] ) ) {
					$left_topping_names = array();
					foreach ( $left['toppings'] as $topping ) {
						if ( isset( $topping['name'] ) && isset( $topping['price'] ) ) {
							$left_topping_names[] = sprintf(
								'%s %s',
								esc_html( $topping['name'] ),
								wc_price( $topping['price'] )
							);
						}
					}

					if ( ! empty( $left_topping_names ) ) {
						$left_display .= "\nAdd " . implode( "\n", $left_topping_names );
					}
				}

				$item->add_meta_data( strtoupper( $left['name'] ), $left_display, true );
			}
		}

		// Display right half
		if ( isset( $halves['right_half'] ) && ! empty( $halves['right_half'] ) ) {
			$right = $halves['right_half'];
			if ( isset( $right['name'] ) ) {
				$right_display = esc_html( $right['name'] );

				// Add right half toppings
				if ( isset( $right['toppings'] ) && ! empty( $right['toppings'] ) ) {
					$right_topping_names = array();
					foreach ( $right['toppings'] as $topping ) {
						if ( isset( $topping['name'] ) && isset( $topping['price'] ) ) {
							$right_topping_names[] = sprintf(
								'%s %s',
								esc_html( $topping['name'] ),
								wc_price( $topping['price'] )
							);
						}
					}

					if ( ! empty( $right_topping_names ) ) {
						$right_display .= "\nAdd " . implode( "\n", $right_topping_names );
					}
				}

				$item->add_meta_data( strtoupper( $right['name'] ), $right_display, true );
			}
		}
	}

	// Save paired products (legacy support)
	if ( isset( $values['paired_products'] ) && ! empty( $values['paired_products'] ) ) {
		$item->add_meta_data( '_paired_products', $values['paired_products'], true );

		// Format for display
		foreach ( $values['paired_products'] as $paired ) {
			if ( isset( $paired['name'] ) && isset( $paired['price'] ) ) {
				$item->add_meta_data(
					__( 'Paired with', 'flatsome' ),
					sprintf(
						'%s (+%s)',
						esc_html( $paired['name'] ),
						wc_price( $paired['price'] )
					),
					true
				);
			}
		}
	}

	// Save special request
	if ( isset( $values['special_request'] ) && ! empty( $values['special_request'] ) ) {
		$item->add_meta_data( '_special_request', $values['special_request'], true );
		$item->add_meta_data( __( 'Special Request', 'flatsome' ), esc_html( $values['special_request'] ), true );
	}
}

// Display custom options in admin order
add_filter( 'woocommerce_order_item_display_meta_key', 'customize_pizza_options_meta_key', 10, 3 );
function customize_pizza_options_meta_key( $display_key, $meta, $item ) {
	// Hide internal meta keys (starting with _)
	if ( strpos( $display_key, '_' ) === 0 ) {
		return '';
	}
	return $display_key;
}

// Make cart items with different options unique
add_filter( 'woocommerce_add_cart_item', 'make_pizza_cart_items_unique', 10, 2 );
function make_pizza_cart_items_unique( $cart_item, $cart_item_key ) {
	// Generate unique key based on custom options
	if ( isset( $cart_item['extra_topping_options'] ) || isset( $cart_item['pizza_halves'] ) || isset( $cart_item['paired_products'] ) || isset( $cart_item['special_request'] ) ) {
		$unique_key = md5( serialize( $cart_item ) );
		$cart_item['unique_key'] = $unique_key;
	}

	return $cart_item;
}

// Add sticky mini cart widget on delivery page
add_action( 'wp_footer', 'custom_sticky_mini_cart_widget' );
function custom_sticky_mini_cart_widget() {
	// Only show on delivery page or pages with #content
	?>
	<div id="custom-sticky-mini-cart" class="custom-sticky-mini-cart" style="display: none;">
		<div class="mini-cart-header">
			<h4><?php esc_html_e( 'Your Order', 'flatsome' ); ?></h4>
			<button class="mini-cart-close">&times;</button>
		</div>
		<div class="mini-cart-body widget_shopping_cart_content">
			<?php woocommerce_mini_cart(); ?>
		</div>
	</div>

	<style>
	.custom-sticky-mini-cart {
		position: fixed;
		bottom: 20px;
		right: 20px;
		width: 350px;
		max-height: 600px;
		background: #fff;
		box-shadow: 0 4px 20px rgba(0,0,0,0.15);
		border-radius: 8px;
		z-index: 9999;
		overflow: hidden;
		transition: all 0.3s ease;
		transform: translateY(100%);
		opacity: 0;
	}

	.custom-sticky-mini-cart.show {
		transform: translateY(0);
		opacity: 1;
	}

	.mini-cart-header {
		background: #dc0000;
		color: #fff;
		padding: 15px 20px;
		display: flex;
		justify-content: space-between;
		align-items: center;
		border-bottom: 2px solid #b30000;
	}

	.mini-cart-header h4 {
		margin: 0;
		font-size: 18px;
		font-weight: 600;
		color: #fff;
	}

	.mini-cart-close {
		background: none;
		border: none;
		color: #fff;
		font-size: 28px;
		line-height: 1;
		cursor: pointer;
		padding: 0;
		width: 30px;
		height: 30px;
		display: flex;
		align-items: center;
		justify-content: center;
		transition: transform 0.2s ease;
	}

	.mini-cart-close:hover {
		transform: rotate(90deg);
	}

	.mini-cart-body {
		max-height: 500px;
		overflow-y: auto;
		padding: 15px;
	}

	.custom-sticky-mini-cart .woocommerce-mini-cart__total {
		padding: 15px 0;
		border-top: 2px solid #f0f0f0;
		margin-top: 10px;
	}

	.custom-sticky-mini-cart .woocommerce-mini-cart__buttons {
		display: flex;
		flex-direction: column;
		gap: 10px;
		margin-top: 15px;
	}

	.custom-sticky-mini-cart .button {
		width: 100%;
		text-align: center;
	}

	@media (max-width: 768px) {
		.custom-sticky-mini-cart {
			width: calc(100% - 40px);
			max-width: 350px;
		}
	}
	</style>

	<script type="text/javascript">
	jQuery(document).ready(function($) {
		var $stickyCart = $('#custom-sticky-mini-cart');
		var hideTimeout;

		// Function to update and show mini cart
		function showStickyMiniCart() {
			// Check if WooCommerce params are available
			if (typeof wc_add_to_cart_params === 'undefined' || typeof wc_add_to_cart_params.wc_ajax_url === 'undefined') {
				console.warn('WooCommerce AJAX params not available');
				return;
			}

			// Update mini cart content via AJAX
			$.ajax({
				url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'),
				type: 'POST',
				success: function(data) {
					if (data && data.fragments) {
						$.each(data.fragments, function(key, value) {
							if (key === 'div.widget_shopping_cart_content') {
								$stickyCart.find('.mini-cart-body').html(value);
							}
						});
					}

					// Show the cart if it has items
					var itemCount = $stickyCart.find('.woocommerce-mini-cart-item').length;
					if (itemCount > 0) {
						$stickyCart.addClass('show').fadeIn(300);

						// Auto-hide after 10 seconds
						clearTimeout(hideTimeout);
						hideTimeout = setTimeout(function() {
							$stickyCart.removeClass('show');
						}, 10000);
					} else {
						$stickyCart.removeClass('show').fadeOut(300);
					}
				}
			});
		}

		// Close button handler
		$stickyCart.on('click', '.mini-cart-close', function() {
			$stickyCart.removeClass('show');
			setTimeout(function() {
				$stickyCart.fadeOut(300);
			}, 300);
		});

		// Show on added_to_cart event
		$(document.body).on('added_to_cart', function(event, fragments, cart_hash, button) {
			// Close quick view lightbox
			if (typeof $.magnificPopup !== 'undefined' && $.magnificPopup.instance.isOpen) {
				$.magnificPopup.close();
			}

			// Wait a bit for cart to update, then show sticky cart
			setTimeout(function() {
				showStickyMiniCart();
			}, 300);
		});

		// Update cart when fragments are updated
		$(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function() {
			var itemCount = $('.woocommerce-mini-cart-item').length;
			if (itemCount > 0 && $stickyCart.hasClass('show')) {
				showStickyMiniCart();
			}
		});

		// Show cart on page load if there are items
		if (typeof wc_add_to_cart_params !== 'undefined') {
			setTimeout(function() {
				var itemCount = $('.cart-item .cart-icon strong').text();
				if (itemCount && parseInt(itemCount) > 0) {
					showStickyMiniCart();
				}
			}, 500);
		}
	});
	</script>
	<?php
}

// Remove "Most Used" tab from Product Categories metabox
add_filter( 'wp_terms_checklist_args', 'remove_most_used_tab_product_categories', 10, 2 );
function remove_most_used_tab_product_categories( $args, $post_id ) {
	// Only apply to product category taxonomy on product edit screens
	if ( isset( $args['taxonomy'] ) && $args['taxonomy'] === 'product_cat' ) {
		$args['popular_cats'] = array(); // This removes the "Most Used" tab
	}
	return $args;
}

// Hide category 15 (topping) and its subcategories from Product Categories metabox
add_filter( 'wp_terms_checklist_args', 'exclude_topping_categories_from_product_metabox', 10, 2 );
function exclude_topping_categories_from_product_metabox( $args, $post_id ) {
	// Only apply to product category taxonomy
	if ( ! isset( $args['taxonomy'] ) || $args['taxonomy'] !== 'product_cat' ) {
		return $args;
	}

	// Get category 15 and all its children
	$topping_parent_id = 15;
	$excluded_cats = array( $topping_parent_id );

	// Get all child categories of category 15
	$child_categories = get_terms( array(
		'taxonomy' => 'product_cat',
		'parent' => $topping_parent_id,
		'hide_empty' => false,
		'fields' => 'ids',
	) );

	if ( ! empty( $child_categories ) && ! is_wp_error( $child_categories ) ) {
		$excluded_cats = array_merge( $excluded_cats, $child_categories );
	}

	// Add to exclude array
	if ( ! empty( $excluded_cats ) ) {
		if ( isset( $args['exclude'] ) && is_array( $args['exclude'] ) ) {
			$args['exclude'] = array_merge( $args['exclude'], $excluded_cats );
		} else {
			$args['exclude'] = $excluded_cats;
		}
	}

	return $args;
}

// Hide category 15 (topping) and its children in the product categories metabox
add_action( 'admin_head', 'hide_topping_categories_css_js' );
function hide_topping_categories_css_js() {
	$current_screen = get_current_screen();

	// Only apply on product add/edit screens
	if ( ! $current_screen || $current_screen->post_type !== 'product' ) {
		return;
	}
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		// Hide category 15 (topping) and all its children
		$('#in-product_cat-15').hide();

		// Remove "Most Used" tab
		$('#product_cat-tabs li.hide-if-no-js').remove();
		$('#product_cat-pop').remove();
	});
	</script>
	<?php
}
