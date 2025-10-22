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
		53                                    // Position (after WooCommerce Products)
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
		__( 'Add New Pizza', 'flatsome' ),
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
		54                                    // Position (after Pizza)
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
		__( 'Add New Topping', 'flatsome' ),
		'edit_products',
		'post-new.php?post_type=product&product_type=topping'
	);

	// Hide the default WooCommerce Products menu
	remove_menu_page( 'edit.php?post_type=product' );

	// Add Product Categories as a standalone top-level menu
	add_menu_page(
		__( 'Product Categories', 'woocommerce' ),
		__( 'Categories', 'woocommerce' ),
		'manage_product_terms',
		'edit-tags.php?taxonomy=product_cat&post_type=product',
		'',
		'dashicons-category',
		55
	);
}

// Enqueue WooCommerce admin styles and scripts for custom product pages
add_action( 'admin_enqueue_scripts', 'enqueue_woocommerce_admin_assets_for_custom_pages' );
function enqueue_woocommerce_admin_assets_for_custom_pages( $hook ) {
	// Only load on our custom pizza and topping product pages
	if ( $hook !== 'toplevel_page_edit-pizza-products' && $hook !== 'toplevel_page_edit-topping-products' ) {
		return;
	}

	// Enqueue WooCommerce admin styles
	wp_enqueue_style( 'woocommerce_admin_styles' );

	// Enqueue additional WooCommerce styles if available
	if ( wp_style_is( 'woocommerce-layout', 'registered' ) ) {
		wp_enqueue_style( 'woocommerce-layout' );
	}
	if ( wp_style_is( 'woocommerce-smallscreen', 'registered' ) ) {
		wp_enqueue_style( 'woocommerce-smallscreen' );
	}
	if ( wp_style_is( 'woocommerce-general', 'registered' ) ) {
		wp_enqueue_style( 'woocommerce-general' );
	}

	// Enqueue WooCommerce admin scripts
	wp_enqueue_script( 'woocommerce_admin' );

	// Load WordPress list table scripts for better table styling
	wp_enqueue_script( 'list-table' );

	// Enqueue common WordPress admin styles
	wp_enqueue_style( 'common' );
	wp_enqueue_style( 'wp-admin' );
	wp_enqueue_style( 'forms' );
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
		// Get category 15 (topping) and all its children to exclude
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

		// Query pizza products (exclude topping products)
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
			'tax_query' => array(
				array(
					'taxonomy' => 'product_cat',
					'field' => 'term_id',
					'terms' => $excluded_cats,
					'operator' => 'NOT IN',
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
							<td class="column-thumb"><?php echo $product->get_image( 'thumbnail' ); ?></td>
							<td>
								<strong>
									<a href="<?php echo esc_url( add_query_arg( 'product_type', 'pizza', get_edit_post_link( get_the_ID() ) ) ); ?>">
										<?php echo esc_html( get_the_title() ); ?>
									</a>
								</strong>
							</td>
							<td><?php echo $product->get_price_html(); ?></td>
							<td><?php echo wc_get_product_category_list( get_the_ID(), ', ' ); ?></td>
							<td><?php echo esc_html( $product->get_stock_status() ); ?></td>
							<td>
								<a href="<?php echo esc_url( add_query_arg( 'product_type', 'pizza', get_edit_post_link( get_the_ID() ) ) ); ?>" class="button button-small">
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
							<td class="thumb column-thumb"><?php echo $product->get_image( 'thumbnail' ); ?></td>
							<td>
								<strong>
									<a href="<?php echo esc_url( add_query_arg( 'product_type', 'topping', get_edit_post_link( get_the_ID() ) ) ); ?>">
										<?php echo esc_html( get_the_title() ); ?>
									</a>
								</strong>
							</td>
							<td><?php echo $product->get_price_html(); ?></td>
							<td><?php echo wc_get_product_category_list( get_the_ID(), ', ' ); ?></td>
							<td><?php echo esc_html( $product->get_stock_status() ); ?></td>
							<td>
								<a href="<?php echo esc_url( add_query_arg( 'product_type', 'topping', get_edit_post_link( get_the_ID() ) ) ); ?>" class="button button-small">
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

// Hide categories and its children in the product categories metabox
add_action( 'admin_head', 'hide_categories_css_js' );
function hide_categories_css_js() {
	$current_screen = get_current_screen();

	// Only apply on product add/edit screens
	if ( ! $current_screen || $current_screen->post_type !== 'product' ) {
		return;
	}
	// Always apply on product edit screen
	?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('#uxbuilder-enable-disable').hide();
				$('.woocommerce-help-tip').hide();
				$('#woocommerce-product-data .product_data_tabs li.advanced_options').hide();
				$('#woocommerce-product-data .product_data_tabs li.ux_product_layout_tab').hide();
				$('#woocommerce-product-data .product_data_tabs li.ux_extra_tab').hide();

				// Hide "Paired With" and "Toppings" tabs from Product Categories metabox
				// These tabs should only appear in WooCommerce Product Data panel
				$('#product_cat-tabs li, #product_cattabs li').each(function() {
					var $link = $(this).find('a');
					var tabText = $link.text().trim();
					var tabHref = $link.attr('href');

					// Remove if text matches OR if href targets our custom panels
					if (tabText === 'Paired With' ||
					    tabText === 'Toppings' ||
					    tabHref === '#paired_with_product_data' ||
					    tabHref === '#toppings_product_data') {
						$(this).remove();
					}
				});

				// Also hide the content panels if they appear in Product Categories
				$('#product_catdiv').find('#paired_with_product_data, #toppings_product_data').remove();

				// Add custom parameter to cross-sell search to filter only topping products
				var isCrosssellActive = false;

				// Track when crosssell field is opened
				$(document).on('select2:opening', '#crosssell_ids', function(e) {
					isCrosssellActive = true;
				});

				// Track when crosssell field is closed
				$(document).on('select2:closing', '#crosssell_ids', function(e) {
					setTimeout(function() {
						isCrosssellActive = false;
					}, 500);
				});

				// Hook into AJAX requests to add field parameter
				$(document).ajaxSend(function(event, jqxhr, settings) {
					// Check if this is a product search AJAX request (URL contains the action parameter)
					if (settings.url && settings.url.indexOf('admin-ajax.php') > -1 &&
					    settings.url.indexOf('woocommerce_json_search_products') > -1) {

						// Add field=crosssell to URL if crosssell is active
						if (isCrosssellActive) {
							settings.url += '&field=crosssell';
						}
					}
				});
			});
		</script>
	<?php
	
	if ( isset( $_GET['product_type'] ) ) {
		// Only apply when product_type=pizza parameter is in the URL
		if ( $_GET['product_type'] === 'pizza' ) {
			?>
				<script type="text/javascript">
					jQuery(document).ready(function($) {
						$('.wp-heading-inline').text('Add New Pizza');
						$('#woocommerce-product-data .product_data_tabs li.attribute_options').show();
						$('#woocommerce-product-data .postbox-header h2').first().text('Pizza data');
						// Hide category 15 (topping) and all its children
						$('#in-product_cat-15-1').hide();
						$('#product_cat-tabs li.tabs a').text('Pizza categories');
						// Remove "Most Used" tab
						$('#product_cat-tabs li.hide-if-no-js').remove();
						$('#product_cat-pop').remove();
					});
				</script>
			<?php
		}else if ( $_GET['product_type'] === 'topping' ) {
			// Get all child categories of category 15
			$child_categories = get_terms( array(
				'taxonomy' => 'product_cat',
				'parent' => 15,
				'hide_empty' => false,
				'fields' => 'ids',
			) );

			if ( empty( $child_categories ) || is_wp_error( $child_categories ) ) {
				return;
			}
			?>
				<script type="text/javascript">
					jQuery(document).ready(function($) {
						// Hide all categories except its children of category 15 (topping)
						$('#product_catchecklist li').each(function() {
							var $checkbox = $(this).find('input[type="checkbox"]');
							if ($checkbox.length) {
								var catId = parseInt($checkbox.val());
								var childCatIds = <?php echo json_encode( $child_categories ); ?>;
								if (catId !== 15 && childCatIds.indexOf(catId) === -1) {
									$(this).hide();
								}
							}
						});
						$('.wp-heading-inline').text('Add New Topping');
						$('#woocommerce-product-data .postbox-header h2').first().text('Topping data');
						$('#woocommerce-product-data .product_data_tabs li.linked_product_options').hide();
						$('#woocommerce-product-data .product_data_tabs li.attribute_options').hide();
						$('#in-product_cat-15-1 label').first().hide();
						$('#product_cat-tabs li.tabs a').text('Topping categories');
						// Remove "Most Used" tab
						$('#product_cat-tabs li.hide-if-no-js').remove();
						$('#product_cat-pop').remove();
					});
				</script>
			<?php
		}
	}

}

// Helper function to check if current product is a pizza (not a topping)
function is_pizza_product( $post_id = null ) {
	if ( ! $post_id ) {
		global $post;
		$post_id = $post ? $post->ID : 0;
	}

	if ( ! $post_id ) {
		// New product - check URL parameter
		return isset( $_GET['product_type'] ) && $_GET['product_type'] === 'pizza';
	}

	// Existing product - check if it's NOT in topping categories
	$topping_parent_id = 15;
	$product_categories = wp_get_post_terms( $post_id, 'product_cat', array( 'fields' => 'ids' ) );

	if ( is_wp_error( $product_categories ) || empty( $product_categories ) ) {
		return isset( $_GET['product_type'] ) && $_GET['product_type'] === 'pizza';
	}

	// Get all topping category IDs (parent 15 and its children)
	$topping_cat_ids = array( $topping_parent_id );
	$child_categories = get_terms( array(
		'taxonomy' => 'product_cat',
		'parent' => $topping_parent_id,
		'hide_empty' => false,
		'fields' => 'ids',
	) );

	if ( ! empty( $child_categories ) && ! is_wp_error( $child_categories ) ) {
		$topping_cat_ids = array_merge( $topping_cat_ids, $child_categories );
	}

	// If product is in any topping category, it's NOT a pizza
	$is_topping = ! empty( array_intersect( $product_categories, $topping_cat_ids ) );
	return ! $is_topping;
}

// Customize WooCommerce product data tab labels for pizza products
add_filter( 'woocommerce_product_data_tabs', 'customize_pizza_product_tabs', 10, 1 );
function customize_pizza_product_tabs( $tabs ) {
	global $post;

	// Only show custom tabs if product_type=pizza parameter is present
	// This ensures the default Linked Products tab shows when editing without the parameter
	$is_pizza_edit_mode = isset( $_GET['product_type'] ) && $_GET['product_type'] === 'pizza';

	if ( $is_pizza_edit_mode && is_pizza_product( $post ? $post->ID : null ) ) {
		// Remove the default linked products tab completely for pizza products
		unset( $tabs['linked_product'] );

		// Add custom "Paired With" tab
		$tabs['paired_with_tab'] = array(
			'label'    => __( 'Paired With', 'flatsome' ),
			'target'   => 'paired_with_product_data',
			'class'    => array( 'show_if_simple', 'show_if_variable' ),
			'priority' => 15,
		);

		// Add custom "Toppings" tab
		$tabs['toppings_tab'] = array(
			'label'    => __( 'Toppings', 'flatsome' ),
			'target'   => 'toppings_product_data',
			'class'    => array( 'show_if_simple', 'show_if_variable' ),
			'priority' => 16,
		);
	}

	return $tabs;
}

// Add content for "Paired With" tab
if ( ! has_action( 'woocommerce_product_data_panels', 'add_paired_with_tab_content' ) ) {
	add_action( 'woocommerce_product_data_panels', 'add_paired_with_tab_content', 10 );
}
function add_paired_with_tab_content() {
	global $post;

	// Prevent multiple renders
	static $rendered = false;
	if ( $rendered ) {
		// echo '<!-- Paired With tab already rendered, skipping -->';
		return;
	}

	// Only show if product_type=pizza parameter is present
	$is_pizza_edit_mode = isset( $_GET['product_type'] ) && $_GET['product_type'] === 'pizza';
	if ( ! $is_pizza_edit_mode ) {
		return;
	}

	// Get the actual product ID being edited
	$product_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : ( $post ? $post->ID : 0 );

	// Only show for pizza products
	if ( ! is_pizza_product( $product_id ) ) {
		return;
	}

	$rendered = true;

	?>
	<div id="paired_with_product_data" class="panel woocommerce_options_panel hidden">
		<div class="options_group">
			<p class="form-field">
				<label><strong><?php _e( 'Select Pizzas for Pairing', 'flatsome' ); ?></strong></label>
				<span class="description"><?php _e( 'Choose pizzas that customers can pair with this pizza for half-and-half combinations.', 'flatsome' ); ?></span>
			</p>

			<?php
			// Get current upsell IDs - use the correct product ID
			$current_upsells = get_post_meta( $product_id, '_upsell_ids', true );
			if ( ! is_array( $current_upsells ) ) {
				$current_upsells = array();
			}
			// Ensure all IDs are integers and remove duplicates
			$current_upsells = array_values( array_unique( array_map( 'intval', $current_upsells ) ) );

			// Get all pizza products (exclude toppings from category 15)
			$topping_parent_id = 15;
			$excluded_cats = array( $topping_parent_id );

			$child_categories = get_terms( array(
				'taxonomy' => 'product_cat',
				'parent' => $topping_parent_id,
				'hide_empty' => false,
				'fields' => 'ids',
			) );

			if ( ! empty( $child_categories ) && ! is_wp_error( $child_categories ) ) {
				$excluded_cats = array_merge( $excluded_cats, $child_categories );
			}

			$args = array(
				'post_type' => 'product',
				'posts_per_page' => -1,
				'orderby' => 'title',
				'order' => 'ASC',
				'post__not_in' => array( $product_id ), // Exclude current product
				'tax_query' => array(
					array(
						'taxonomy' => 'product_cat',
						'field' => 'term_id',
						'terms' => $excluded_cats,
						'operator' => 'NOT IN',
					),
				),
			);

			$pizza_products = new WP_Query( $args );
			?>

			<div class="paired-products-grid">
				<table class="widefat" style="margin-top: 10px;">
					<thead>
						<tr>
							<th style="width: 40px; text-align: center;">
								<input type="checkbox" id="select_all_paired" />
							</th>
							<th style="width: 100px;"><?php _e( 'SKU', 'flatsome' ); ?></th>
							<th><?php _e( 'Name', 'flatsome' ); ?></th>
							<th style="width: 150px;"><?php _e( 'Category', 'flatsome' ); ?></th>
							<th style="width: 120px;"><?php _e( 'Price', 'flatsome' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						if ( $pizza_products->have_posts() ) :
							while ( $pizza_products->have_posts() ) : $pizza_products->the_post();
								$product_obj = wc_get_product( get_the_ID() );
								$is_checked = in_array( get_the_ID(), $current_upsells );
								$categories = wc_get_product_category_list( get_the_ID(), ', ', '', '' );
								?>
								<tr>
									<td style="text-align: center;">
										<input type="checkbox"
											id="paired_product_<?php echo esc_attr( get_the_ID() ); ?>"
											name="upsell_ids[]"
											value="<?php echo esc_attr( get_the_ID() ); ?>"
											<?php checked( $is_checked, true ); ?>
											class="paired-product-checkbox"
											data-product-id="<?php echo esc_attr( get_the_ID() ); ?>" />
									</td>
									<td><?php echo esc_html( $product_obj->get_sku() ? $product_obj->get_sku() : '-' ); ?></td>
									<td><strong><?php echo esc_html( get_the_title() ); ?></strong></td>
									<td><?php echo $categories ? $categories : '-'; ?></td>
									<td><?php echo $product_obj->get_price_html(); ?></td>
								</tr>
								<?php
							endwhile;
							wp_reset_postdata();
						else :
							?>
							<tr>
								<td colspan="5" style="text-align: center; padding: 20px;">
									<?php _e( 'No pizza products found.', 'flatsome' ); ?>
								</td>
							</tr>
							<?php
						endif;
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php
}

// Add content for "Toppings" tab
if ( ! has_action( 'woocommerce_product_data_panels', 'add_toppings_tab_content' ) ) {
	add_action( 'woocommerce_product_data_panels', 'add_toppings_tab_content', 11 );
}
function add_toppings_tab_content() {
	global $post;

	// Prevent multiple renders with a counter
	static $render_count = 0;
	$render_count++;

	if ( $render_count > 1 ) {
		// echo '<!-- Toppings tab render attempt #' . $render_count . ' - BLOCKED -->';
		return;
	}

	// Only show if product_type=pizza parameter is present
	$is_pizza_edit_mode = isset( $_GET['product_type'] ) && $_GET['product_type'] === 'pizza';
	if ( ! $is_pizza_edit_mode ) {
		return;
	}

	// Get the actual product ID being edited
	$product_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : ( $post ? $post->ID : 0 );

	// Only show for pizza products
	if ( ! is_pizza_product( $product_id ) ) {
		// echo '<!-- Toppings tab - not a pizza product (ID: ' . $product_id . ') -->';
		return;
	}

	// echo '<!-- Toppings tab rendering #' . $render_count . ' for product ' . $product_id . ' -->';

	?>
	<div id="toppings_product_data" class="panel woocommerce_options_panel hidden" data-render-count="<?php echo $render_count; ?>">
		<div class="options_group">
			<p class="form-field">
				<label><strong><?php _e( 'Select Available Toppings', 'flatsome' ); ?></strong></label>
				<span class="description"><?php _e( 'Choose topping products that customers can add to this pizza.', 'flatsome' ); ?></span>
			</p>

			<?php
			// Get current cross-sell IDs - use the correct product ID

			// Try multiple methods to get cross-sells
			$current_crosssells_meta = get_post_meta( $product_id, '_crosssell_ids', true );
			$product_obj = wc_get_product( $product_id );
			$current_crosssells_wc = $product_obj ? $product_obj->get_cross_sell_ids() : array();

			// Use WC method if meta is empty
			$current_crosssells = ! empty( $current_crosssells_meta ) ? $current_crosssells_meta : $current_crosssells_wc;

			if ( ! is_array( $current_crosssells ) ) {
				$current_crosssells = array();
			}
			// Ensure all IDs are integers and remove duplicates
			$current_crosssells = array_values( array_unique( array_map( 'intval', $current_crosssells ) ) );

			// // Debug output (visible in HTML comments)
			// echo '<!-- Toppings Debug: ';
			// echo 'Post ID: ' . $product_id . ' | ';
			// echo 'Meta Cross-sells: ' . print_r( $current_crosssells_meta, true ) . ' | ';
			// echo 'WC Cross-sells: ' . print_r( $current_crosssells_wc, true ) . ' | ';
			// echo 'Final Cross-sells: ' . print_r( $current_crosssells, true );
			// echo '-->';

			// Get topping categories (children of category 15)
			$topping_cats = array();
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

			// Get all topping products
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

			$topping_products = new WP_Query( $args );
			?>

			<div class="topping-products-grid">
				<table class="widefat" style="margin-top: 10px;">
					<thead>
						<tr>
							<th style="width: 40px; text-align: center;">
								<input type="checkbox" id="select_all_toppings" />
							</th>
							<th style="width: 100px;"><?php _e( 'SKU', 'flatsome' ); ?></th>
							<th><?php _e( 'Name', 'flatsome' ); ?></th>
							<th style="width: 150px;"><?php _e( 'Category', 'flatsome' ); ?></th>
							<th style="width: 120px;"><?php _e( 'Price', 'flatsome' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						if ( $topping_products->have_posts() ) :
							while ( $topping_products->have_posts() ) : $topping_products->the_post();
								$product_obj = wc_get_product( get_the_ID() );
								$product_id = intval( get_the_ID() );
								$is_checked = in_array( $product_id, $current_crosssells, true );
								$categories = wc_get_product_category_list( get_the_ID(), ', ', '', '' );

								// // Debug for each product - more detailed
								// echo '<!-- Topping Product ID: ' . $product_id . ' (' . get_the_title() . ')';
								// echo ' | Type: ' . gettype( $product_id );
								// echo ' | In Array: ' . ( in_array( $product_id, $current_crosssells, true ) ? 'YES' : 'NO' );
								// echo ' | In Array (loose): ' . ( in_array( $product_id, $current_crosssells, false ) ? 'YES' : 'NO' );
								// echo ' | Is Checked: ' . var_export( $is_checked, true );
								// echo ' -->';
								?>
								<tr>
									<td style="text-align: center;">
										<input type="checkbox"
											id="topping_product_<?php echo esc_attr( $product_id ); ?>"
											name="crosssell_ids[]"
											value="<?php echo esc_attr( $product_id ); ?>"
											<?php checked( $is_checked, true ); ?>
											class="topping-product-checkbox"
											data-product-id="<?php echo esc_attr( $product_id ); ?>"
											data-is-checked="<?php echo $is_checked ? 'true' : 'false'; ?>" />
									</td>
									<td><?php echo esc_html( $product_obj->get_sku() ? $product_obj->get_sku() : '-' ); ?></td>
									<td><strong><?php echo esc_html( get_the_title() ); ?></strong></td>
									<td><?php echo $categories ? $categories : '-'; ?></td>
									<td><?php echo $product_obj->get_price_html(); ?></td>
								</tr>
								<?php
							endwhile;
							wp_reset_postdata();
						else :
							?>
							<tr>
								<td colspan="5" style="text-align: center; padding: 20px;">
									<?php _e( 'No topping products found.', 'flatsome' ); ?>
								</td>
							</tr>
							<?php
						endif;
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php
}

// Save the custom tab data (priority 99 to run after WooCommerce defaults)
add_action( 'woocommerce_process_product_meta', 'save_custom_paired_and_toppings_data', 99, 1 );
function save_custom_paired_and_toppings_data( $post_id ) {
	// // Debug file path
	// $debug_file = ABSPATH . 'debug-save.txt';

	// // Debug: Log POST data
	// $log = "\n\n" . str_repeat('=', 60) . "\n";
	// $log .= "Save triggered at: " . date('Y-m-d H:i:s') . "\n";
	// $log .= "Product ID: " . $post_id . "\n";
	// $log .= "Is Pizza Product: " . ( is_pizza_product( $post_id ) ? 'YES' : 'NO' ) . "\n";
	// $log .= "POST upsell_ids: " . ( isset( $_POST['upsell_ids'] ) ? print_r( $_POST['upsell_ids'], true ) : 'NOT SET' ) . "\n";
	// $log .= "POST upsell_ids COUNT: " . ( isset( $_POST['upsell_ids'] ) ? count( $_POST['upsell_ids'] ) : 0 ) . "\n";
	// $log .= "POST crosssell_ids: " . ( isset( $_POST['crosssell_ids'] ) ? print_r( $_POST['crosssell_ids'], true ) : 'NOT SET' ) . "\n";
	// $log .= "POST crosssell_ids COUNT: " . ( isset( $_POST['crosssell_ids'] ) ? count( $_POST['crosssell_ids'] ) : 0 ) . "\n";

	// // Log unique counts
	// if ( isset( $_POST['crosssell_ids'] ) && is_array( $_POST['crosssell_ids'] ) ) {
	// 	$unique_crosssells = array_unique( array_map( 'intval', $_POST['crosssell_ids'] ) );
	// 	$log .= "POST crosssell_ids UNIQUE COUNT: " . count( $unique_crosssells ) . "\n";
	// 	$log .= "DUPLICATES FOUND: " . ( count( $_POST['crosssell_ids'] ) - count( $unique_crosssells ) ) . "\n";
	// }

	// error_log( '=== Save Custom Data for Product ID: ' . $post_id . ' ===' );
	// error_log( 'Is Pizza Product: ' . ( is_pizza_product( $post_id ) ? 'YES' : 'NO' ) );
	// error_log( 'POST upsell_ids: ' . ( isset( $_POST['upsell_ids'] ) ? print_r( $_POST['upsell_ids'], true ) : 'NOT SET' ) );
	// error_log( 'POST crosssell_ids: ' . ( isset( $_POST['crosssell_ids'] ) ? print_r( $_POST['crosssell_ids'], true ) : 'NOT SET' ) );

	// // Only save if this is a pizza product (not a topping)
	// if ( ! is_pizza_product( $post_id ) ) {
	// 	$log .= "Skipping save - not a pizza product\n";
	// 	file_put_contents( $debug_file, $log, FILE_APPEND );
	// 	error_log( 'Skipping save - not a pizza product' );
	// 	return;
	// }

	// IMPORTANT: Delete existing data FIRST to avoid duplicates
	delete_post_meta( $post_id, '_upsell_ids' );
	delete_post_meta( $post_id, '_crosssell_ids' );
	// $log .= "Deleted existing upsells and cross-sells\n";

	// Save upsell IDs (Paired With) - remove duplicates
	$upsell_ids = isset( $_POST['upsell_ids'] ) && is_array( $_POST['upsell_ids'] )
		? array_unique( array_map( 'intval', $_POST['upsell_ids'] ) )
		: array();
	// Re-index array to avoid gaps
	$upsell_ids = array_values( $upsell_ids );

	// Only save if there are IDs to save
	if ( ! empty( $upsell_ids ) ) {
		update_post_meta( $post_id, '_upsell_ids', $upsell_ids );
		// $log .= "Saved upsells: " . print_r( $upsell_ids, true ) . "\n";
		// error_log( 'Saved upsells: ' . print_r( $upsell_ids, true ) );
	} else {
		// $log .= "No upsells to save\n";
		// error_log( 'No upsells to save' );
	}

	// Save cross-sell IDs (Toppings) - remove duplicates
	$crosssell_ids_from_post = isset( $_POST['crosssell_ids'] ) && is_array( $_POST['crosssell_ids'] )
		? array_unique( array_map( 'intval', $_POST['crosssell_ids'] ) )
		: array();
	// Re-index array to avoid gaps
	$crosssell_ids = array_values( $crosssell_ids_from_post );

	// Only save if there are IDs to save
	if ( ! empty( $crosssell_ids ) ) {
		update_post_meta( $post_id, '_crosssell_ids', $crosssell_ids );
		// $log .= "Saved cross-sells: " . print_r( $crosssell_ids, true ) . "\n";
		// error_log( 'Saved cross-sells: ' . print_r( $crosssell_ids, true ) );
	} 
	// else {
	// 	$log .= "No cross-sells to save\n";
	// 	error_log( 'No cross-sells to save' );
	// }

	// Verify what was actually saved
	$verify_upsells = get_post_meta( $post_id, '_upsell_ids', true );
	$verify_crosssells = get_post_meta( $post_id, '_crosssell_ids', true );
	// $log .= "Verified upsells in DB: " . print_r( $verify_upsells, true ) . "\n";
	// $log .= "Verified cross-sells in DB: " . print_r( $verify_crosssells, true ) . "\n";
	// error_log( 'Verified upsells in DB: ' . print_r( $verify_upsells, true ) );
	// error_log( 'Verified cross-sells in DB: ' . print_r( $verify_crosssells, true ) );

	// // Write to debug file
	// file_put_contents( $debug_file, $log, FILE_APPEND );

	// Clear WooCommerce product cache to force reload
	wc_delete_product_transients( $post_id );
}

// Redirect to add product_type parameter after saving pizza or topping products
add_filter( 'redirect_post_location', 'add_product_type_to_redirect_url', 10, 2 );
function add_product_type_to_redirect_url( $location, $post_id ) {
	// $debug_file = ABSPATH . 'debug-save.txt';
	// $log = "\n--- Redirect Filter Called ---\n";
	// $log .= "Post ID: " . $post_id . "\n";
	// $log .= "Post Type: " . get_post_type( $post_id ) . "\n";
	// $log .= "Original Location: " . $location . "\n";

	// Only apply to product post type
	if ( get_post_type( $post_id ) !== 'product' ) {
		// $log .= "Skipping: Not a product\n";
		// file_put_contents( $debug_file, $log, FILE_APPEND );
		return $location;
	}

	// Check if product_type parameter is already in the URL
	if ( strpos( $location, 'product_type=' ) !== false ) {
		// $log .= "Skipping: product_type already in URL\n";
		// file_put_contents( $debug_file, $log, FILE_APPEND );
		return $location;
	}

	// Determine if this is a pizza or topping product
	$product_categories = wp_get_post_terms( $post_id, 'product_cat', array( 'fields' => 'ids' ) );

	if ( is_wp_error( $product_categories ) || empty( $product_categories ) ) {
		return $location;
	}

	// Get all topping category IDs (parent 15 and its children)
	$topping_parent_id = 15;
	$topping_cat_ids = array( $topping_parent_id );
	$child_categories = get_terms( array(
		'taxonomy' => 'product_cat',
		'parent' => $topping_parent_id,
		'hide_empty' => false,
		'fields' => 'ids',
	) );

	if ( ! empty( $child_categories ) && ! is_wp_error( $child_categories ) ) {
		$topping_cat_ids = array_merge( $topping_cat_ids, $child_categories );
	}

	// Check if product is in topping categories
	$is_topping = ! empty( array_intersect( $product_categories, $topping_cat_ids ) );

	if ( $is_topping ) {
		// Add product_type=topping to URL
		$location = add_query_arg( 'product_type', 'topping', $location );
		// $log .= "Is Topping: YES\n";
	} else {
		// Add product_type=pizza to URL
		$location = add_query_arg( 'product_type', 'pizza', $location );
		// $log .= "Is Topping: NO (Pizza)\n";
	}

	// $log .= "New Location: " . $location . "\n";
	// file_put_contents( $debug_file, $log, FILE_APPEND );

	return $location;
}

// Add styles and JavaScript for custom tabs
add_action( 'admin_head', 'customize_pizza_tabs_styles_scripts' );
function customize_pizza_tabs_styles_scripts() {
	global $post;
	$current_screen = get_current_screen();

	// Only apply on product add/edit screens
	if ( ! $current_screen || $current_screen->post_type !== 'product' ) {
		return;
	}

	// Only load when product_type=pizza parameter is present (matches tab visibility logic)
	$is_pizza_edit_mode = isset( $_GET['product_type'] ) && $_GET['product_type'] === 'pizza';
	if ( ! $is_pizza_edit_mode ) {
		return;
	}

	?>
	<style>
		/* Hide custom tabs from Product Categories metabox */
		#product_cat-tabs li a[href="#paired_with_product_data"],
		#product_cat-tabs li a[href="#toppings_product_data"],
		#product_cattabs li a[href="#paired_with_product_data"],
		#product_cattabs li a[href="#toppings_product_data"] {
			display: none !important;
		}

		/* Ensure custom tab panels only appear in WooCommerce product data */
		#product_catdiv #paired_with_product_data,
		#product_catdiv #toppings_product_data {
			display: none !important;
		}

		/* Style for custom product grid tables */
		.paired-products-grid table,
		.topping-products-grid table {
			border: 1px solid #ddd;
		}

		.paired-products-grid table thead th,
		.topping-products-grid table thead th {
			background-color: #f9f9f9;
			font-weight: 600;
			padding: 10px;
			border-bottom: 2px solid #ddd;
		}

		.paired-products-grid table tbody td,
		.topping-products-grid table tbody td {
			padding: 8px 10px;
			vertical-align: middle;
		}

		.paired-products-grid table tbody tr:hover,
		.topping-products-grid table tbody tr:hover {
			background-color: #f5f5f5;
		}

		/* Checkbox styling */
		.paired-product-checkbox,
		.topping-product-checkbox,
		#select_all_paired,
		#select_all_toppings {
			cursor: pointer;
			width: 16px;
			height: 16px;
		}

		/* Description text styling */
		#paired_with_product_data .description,
		#toppings_product_data .description {
			color: #666;
			font-style: italic;
			display: block;
			margin-top: 5px;
		}

		/* Tab label styling */
		#woocommerce-product-data ul.wc-tabs li.paired_with_tab_options a:before {
			content: "\f500"; /* dashicons-food */
			font-family: dashicons;
		}

		#woocommerce-product_data ul.wc-tabs li.toppings_tab_options a:before {
			content: "\f336"; /* dashicons-carrot */
			font-family: dashicons;
		}

		/* Search/filter box styling */
		.product-filter-box {
			margin: 10px 0;
			padding: 10px;
			background: #f9f9f9;
			border: 1px solid #ddd;
			border-radius: 3px;
		}

		.product-filter-box input[type="text"] {
			width: 100%;
			max-width: 300px;
			padding: 5px 10px;
		}

		/* Counter styling */
		.selected-count {
			display: inline-block;
			margin-left: 10px;
			padding: 3px 8px;
			background: #0073aa;
			color: #fff;
			border-radius: 3px;
			font-size: 12px;
			font-weight: 600;
		}
	</style>

	<script type="text/javascript">
		jQuery(document).ready(function($) {

			// CRITICAL FIX: Remove the 'name' attribute from ALL checkboxes immediately
			// This prevents them from being submitted with the form
			// We'll add hidden fields with the correct values on submit
			var pairedCount = 0;
			$('.paired-product-checkbox').each(function() {
				var originalName = $(this).attr('name');
				$(this).attr('data-original-name', originalName);  // Save for reference
				$(this).removeAttr('name');  // Remove name so it won't submit
				pairedCount++;
			});

			var toppingCount = 0;
			$('.topping-product-checkbox').each(function() {
				var originalName = $(this).attr('name');
				$(this).attr('data-original-name', originalName);  // Save for reference
				$(this).removeAttr('name');  // Remove name so it won't submit
				toppingCount++;
			});

			// CRITICAL: Remove WooCommerce's default linked product fields from the DOM
			// These might be hidden but still submitting
			var removedFields = 0;
			$('select[name="upsell_ids[]"], select[name="crosssell_ids[]"]').each(function() {
				$(this).remove();
				removedFields++;
			});
			// Also remove any hidden inputs that might have been added
			$('#linked_product_data input[name="upsell_ids[]"], #linked_product_data input[name="crosssell_ids[]"]').remove();

			// Handle "Select All" for Paired With tab
			$('#select_all_paired').on('change', function() {
				var isChecked = $(this).is(':checked');
				$('.paired-product-checkbox:visible').prop('checked', isChecked);
				updatePairedCount();
			});

			// Handle individual checkboxes for Paired With tab
			$('.paired-product-checkbox').on('change', function() {
				var total = $('.paired-product-checkbox').length;
				var checked = $('.paired-product-checkbox:checked').length;
				$('#select_all_paired').prop('checked', total === checked);
				updatePairedCount();
			});

			// Handle "Select All" for Toppings tab
			$('#select_all_toppings').on('change', function() {
				var isChecked = $(this).is(':checked');
				$('.topping-product-checkbox:visible').prop('checked', isChecked);
				updateToppingCount();
			});

			// Handle individual checkboxes for Toppings tab
			$('.topping-product-checkbox').on('change', function() {
				var total = $('.topping-product-checkbox').length;
				var checked = $('.topping-product-checkbox:checked').length;
				$('#select_all_toppings').prop('checked', total === checked);
				updateToppingCount();
			});

			// Update selected count for Paired With
			function updatePairedCount() {
				var count = $('.paired-product-checkbox:checked').length;
				var $label = $('#paired_with_product_data .form-field label strong');

				// Remove existing count badge
				$label.find('.selected-count').remove();

				// Add new count badge
				if (count > 0) {
					$label.append(' <span class="selected-count">' + count + ' selected</span>');
				}
			}

			// Update selected count for Toppings
			function updateToppingCount() {
				var count = $('.topping-product-checkbox:checked').length;
				var $label = $('#toppings_product_data .form-field label strong');

				// Remove existing count badge
				$label.find('.selected-count').remove();

				// Add new count badge
				if (count > 0) {
					$label.append(' <span class="selected-count">' + count + ' selected</span>');
				}
			}

			// Initialize counts on page load
			updatePairedCount();
			updateToppingCount();

			// Check "Select All" state on page load
			var totalPaired = $('.paired-product-checkbox').length;
			var checkedPaired = $('.paired-product-checkbox:checked').length;
			if (totalPaired > 0 && totalPaired === checkedPaired) {
				$('#select_all_paired').prop('checked', true);
			}

			var totalToppings = $('.topping-product-checkbox').length;
			var checkedToppings = $('.topping-product-checkbox:checked').length;
			if (totalToppings > 0 && totalToppings === checkedToppings) {
				$('#select_all_toppings').prop('checked', true);
			}

			// FIX: Add hidden fields for checked items only (checkboxes have no 'name' so won't submit)
			function addHiddenFieldsForSubmit() {
				// CRITICAL: Remove name attribute from ALL checkboxes again (in case they were re-rendered)
				var pairedRemoved = 0;
				$('.paired-product-checkbox[name]').each(function() {
					$(this).removeAttr('name');
					pairedRemoved++;
				});
				var toppingRemoved = 0;
				$('.topping-product-checkbox[name]').each(function() {
					$(this).removeAttr('name');
					toppingRemoved++;
				});

				// CRITICAL: Remove WooCommerce default fields again (in case they were re-added)
				var wcFieldsRemoved = 0;
				$('select[name="upsell_ids[]"], select[name="crosssell_ids[]"]').each(function() {
					$(this).remove();
					wcFieldsRemoved++;
				});
				$('#linked_product_data input[name="upsell_ids[]"], #linked_product_data input[name="crosssell_ids[]"]').each(function() {
					$(this).remove();
					wcFieldsRemoved++;
				});

				// Remove any previously added hidden fields (in case function runs multiple times)
				$('input[name="upsell_ids[]"]').remove();
				$('input[name="crosssell_ids[]"]').remove();

				// Collect checked paired IDs
				var pairedIds = [];
				$('#paired_with_product_data .paired-product-checkbox:checked').each(function() {
					var id = $(this).val();
					if (id && pairedIds.indexOf(id) === -1) {  // Avoid duplicates
						pairedIds.push(id);
					}
				});

				// Collect checked topping IDs
				var toppingIds = [];
				$('#toppings_product_data .topping-product-checkbox:checked').each(function() {
					var id = $(this).val();
					if (id && toppingIds.indexOf(id) === -1) {  // Avoid duplicates
						toppingIds.push(id);
					}
				});

				// Add hidden fields for paired products
				var $form = $('#post');
				pairedIds.forEach(function(id) {
					$('<input>').attr({
						type: 'hidden',
						name: 'upsell_ids[]',
						value: id
					}).appendTo($form);
				});

				// Add hidden fields for toppings
				toppingIds.forEach(function(id) {
					$('<input>').attr({
						type: 'hidden',
						name: 'crosssell_ids[]',
						value: id
					}).appendTo($form);
				});
			}

			// Add hidden fields on form submit (checkboxes won't submit because they have no 'name')
			$('#post').on('submit', function(e) {
				addHiddenFieldsForSubmit();
			});
		});
	</script>
	<?php
}

// Change "VAT" label to include tax rate on order received page
add_filter( 'woocommerce_get_order_item_totals', 'customize_vat_label_with_rate', 10, 3 );
function customize_vat_label_with_rate( $total_rows, $order, $tax_display ) {
	// Find any tax-related keys (they can be dynamic like 'vn-vat-1', 'tax', etc.)
	$found_tax_keys = array();
	foreach ( $total_rows as $key => $row ) {
		// Check if this row contains "VAT" or "Tax" in the label
		if ( isset( $row['label'] ) &&
		     ( stripos( $row['label'], 'VAT' ) !== false ||
		       stripos( $row['label'], 'Tax' ) !== false ) ) {
			$found_tax_keys[] = $key;
		}
	}

	if ( ! empty( $found_tax_keys ) ) {
		// Get tax rate from order
		$tax_rate = '';
		$taxes = $order->get_taxes();

		if ( ! empty( $taxes ) ) {
			foreach ( $taxes as $tax ) {
				$rate_id = $tax->get_rate_id();
				$rate_percent = WC_Tax::get_rate_percent( $rate_id );

				if ( $rate_percent ) {
					$tax_rate = ' (' . $rate_percent . ')';
					break;
				}
			}
		}

		// If no rate found, try to get from tax rate settings
		if ( empty( $tax_rate ) ) {
			$tax_rates = WC_Tax::get_rates();

			if ( ! empty( $tax_rates ) ) {
				$first_rate = reset( $tax_rates );
				if ( isset( $first_rate['rate'] ) ) {
					$tax_rate = ' (' . floatval( $first_rate['rate'] ) . '%)';
				}
			}
		}

		// Update all tax-related labels
		foreach ( $found_tax_keys as $tax_key ) {
			$total_rows[ $tax_key ]['label'] = 'VAT' . $tax_rate . ':';
		}
	}

	return $total_rows;
}

// Filter product search: exclude toppings from upsells, include ONLY toppings for cross-sells
add_filter( 'woocommerce_json_search_found_products', 'filter_products_by_field_type' );
function filter_products_by_field_type( $products ) {
	// Check if this is a WooCommerce AJAX product search request
	if ( ! isset( $_REQUEST['action'] ) ||
	     ( $_REQUEST['action'] !== 'woocommerce_json_search_products' &&
	       $_REQUEST['action'] !== 'woocommerce_json_search_products_and_variations' ) ) {
		return $products;
	}

	// Get all products in category 15 (topping) and its children
	$topping_parent_id = 15;

	// Get all child categories of category 15
	$child_categories = get_terms( array(
		'taxonomy' => 'product_cat',
		'parent' => $topping_parent_id,
		'hide_empty' => false,
		'fields' => 'ids',
	) );

	$topping_cat_ids = array( $topping_parent_id );
	if ( ! empty( $child_categories ) && ! is_wp_error( $child_categories ) ) {
		$topping_cat_ids = array_merge( $topping_cat_ids, $child_categories );
	}

	// Get all product IDs in topping categories
	$topping_product_ids = get_posts( array(
		'post_type' => 'product',
		'posts_per_page' => -1,
		'fields' => 'ids',
		'tax_query' => array(
			array(
				'taxonomy' => 'product_cat',
				'field' => 'term_id',
				'terms' => $topping_cat_ids,
				'operator' => 'IN',
			),
		),
	) );

	// Detect which field is being searched by checking custom parameter
	$is_crosssell_search = false;

	// Check if there's a custom parameter indicating this is cross-sell search
	if ( isset( $_REQUEST['field'] ) && $_REQUEST['field'] === 'crosssell' ) {
		$is_crosssell_search = true;
	}

	if ( $is_crosssell_search ) {
		// For cross-sells: ONLY show topping products
		$filtered_products = array();
		if ( ! empty( $topping_product_ids ) ) {
			foreach ( $products as $product_id => $product_name ) {
				if ( in_array( $product_id, $topping_product_ids ) ) {
					$filtered_products[ $product_id ] = $product_name;
				}
			}
		}
		return $filtered_products;
	} else {
		// For upsells and other fields: EXCLUDE topping products
		if ( ! empty( $topping_product_ids ) ) {
			foreach ( $topping_product_ids as $topping_id ) {
				if ( isset( $products[ $topping_id ] ) ) {
					unset( $products[ $topping_id ] );
				}
			}
		}
		return $products;
	}
}

// Remove billing_postcode from checkout fields
add_filter( 'woocommerce_checkout_fields', 'my_hide_billing_postcode' );
function my_hide_billing_postcode( $fields ) {
    if ( isset( $fields['billing']['billing_postcode'] ) ) {
        unset( $fields['billing']['billing_postcode'] );
    }
    return $fields;
}
