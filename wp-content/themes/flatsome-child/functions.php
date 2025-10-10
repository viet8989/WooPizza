<?php
// Add custom Theme Functions here

function enqueue_custom_js()
{
    wp_enqueue_script('custom-js', get_stylesheet_directory_uri() . '/js/custom.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_custom_js');

add_filter('woocommerce_currency_symbol', function ($currency_symbol, $currency) {
    if ($currency === 'VND') {
        $currency_symbol = 'VND'; // Đổi từ ₫ sang VND
    }
    return $currency_symbol;
}, 10, 2);

// Viet add custom functions below this line

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
	if ( isset( $cart_item['extra_topping_options'] ) || isset( $cart_item['pizza_halves'] ) || isset( $cart_item['paired_products'] ) ) {
		$unique_key = md5( serialize( $cart_item ) );
		$cart_item['unique_key'] = $unique_key;
	}

	return $cart_item;
}
