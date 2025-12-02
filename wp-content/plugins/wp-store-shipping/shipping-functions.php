<?php
/**
 * Shipping Functions
 *
 * Handles checkout fields and shipping fees for Ho Chi Minh City
 * Part of WP Store Shipping plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

error_log('WP Store Shipping Functions: File loaded');

// Check if WooCommerce is active
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    error_log('WP Store Shipping Functions: WooCommerce is active');

    // Include city data
    include plugin_dir_path(__FILE__) . 'cities/tinh_thanhpho.php';
    include plugin_dir_path(__FILE__) . 'cities/xa_phuong_thitran.php';

    error_log('WP Store Shipping Functions: City data included');

    if (!class_exists('Custom_Shipping_Checkout_Class')) {
        error_log('WP Store Shipping Functions: Defining Custom_Shipping_Checkout_Class');

        class Custom_Shipping_Checkout_Class
        {
            protected static $instance;

            public static function init()
            {
                error_log('WP Store Shipping Functions: init() called');
                is_null(self::$instance) AND self::$instance = new self;
                return self::$instance;
            }

            public function __construct()
            {
                error_log('WP Store Shipping Functions: __construct() called');

                // Add checkout fields filter
                add_filter('woocommerce_checkout_fields', array($this, 'custom_override_checkout_fields'), 999999);

                // Add states filter
                add_filter('woocommerce_states', array($this, 'vietnam_cities_woocommerce'), 99999);

                // Add admin menu
                add_action('admin_menu', array($this, 'add_shipping_admin_menu'), 99);

                // Add AJAX handler for saving shipping fees
                add_action('wp_ajax_save_shipping_fee', array($this, 'ajax_save_shipping_fee'));
                add_action('wp_ajax_get_store_wards', array($this, 'ajax_get_store_wards'));
                add_action('wp_ajax_nopriv_get_store_wards', array($this, 'ajax_get_store_wards'));

                // Add filter to show only currently open stores
                add_filter('wpsl_store_data', array($this, 'filter_stores_by_opening_hours'), 10, 1);

                // Register custom shipping method
                add_filter('woocommerce_shipping_methods', array($this, 'register_store_shipping_method'));
                add_action('woocommerce_shipping_init', array($this, 'init_store_shipping_method'));

                // Ensure shipping packages are available
                add_filter('woocommerce_cart_shipping_packages', array($this, 'add_shipping_package'));

                // Add shipping/pickup fee to cart
                add_action('woocommerce_cart_calculate_fees', array($this, 'add_shipping_pickup_fee'));

                // Enable payment methods
                add_filter('woocommerce_available_payment_gateways', array($this, 'enable_payment_gateways'));

                error_log('WP Store Shipping Functions: Hooks registered');
            }

            function vietnam_cities_woocommerce($states)
            {
                global $tinh_thanhpho;
                $states['VN'] = apply_filters('devvn_states_vn', $tinh_thanhpho);
                return $states;
            }

            function custom_override_checkout_fields($fields)
            {
                global $tinh_thanhpho, $xa_phuong_thitran;

                WC()->customer->set_shipping_country('VN');

                // Prepare ward/commune options - Start with only placeholder
                // Wards will be loaded dynamically via JavaScript based on selected store
                $ward_options = array('' => __('Select Ward/Commune', 'wp-store-shipping'));

                // Remove billing last name
                if (isset($fields['billing']['billing_first_name'])) {
                    unset($fields['billing']['billing_first_name']);
                }

                // Set billing country to Vietnam (VN) as hidden field - WooCommerce requires this
                $fields['billing']['billing_country'] = array(
                    'type' => 'hidden',
                    'default' => 'VN',
                    'class' => array('update_totals_on_change'),
                );

                // Remove billing company
                if (isset($fields['billing']['billing_company'])) {
                    unset($fields['billing']['billing_company']);
                }

                // Set billing postcode to default value as hidden field
                $fields['billing']['billing_postcode'] = array(
                    'type' => 'hidden',
                    'default' => '70000',
                    'class' => array('update_totals_on_change'),
                );

                // Remove billing_address_2
                if (isset($fields['billing']['billing_address_2'])) {
                    unset($fields['billing']['billing_address_2']);
                }

                // Billing fields
                $fields['billing']['billing_last_name'] = array(
                    'label' => __('Full name', 'wp-store-shipping'),
                    'placeholder' => _x('Type Full name', 'placeholder', 'wp-store-shipping'),
                    'required' => true,
                    'class' => array('form-row-wide'),
                    'clear' => true,
                    'priority' => 10
                );

                if (isset($fields['billing']['billing_phone'])) {
                    $fields['billing']['billing_phone']['class'] = array('form-row-first');
                    $fields['billing']['billing_phone']['placeholder'] = __('Type your phone', 'wp-store-shipping');
                    $fields['billing']['billing_phone']['required'] = true;
                    $fields['billing']['billing_phone']['priority'] = 20;
                }

                if (isset($fields['billing']['billing_email'])) {
                    $fields['billing']['billing_email']['class'] = array('form-row-last');
                    $fields['billing']['billing_email']['placeholder'] = __('Type your email', 'wp-store-shipping');
                    $fields['billing']['billing_email']['priority'] = 21;
                }

                // Province/City field - Always HOCHIMINH, display Vietnamese name
                $state_code = 'HOCHIMINH';
                $state_name = isset($tinh_thanhpho[$state_code]) ? $tinh_thanhpho[$state_code] : $state_code;

                $fields['billing']['billing_state'] = array(
                    'label' => __('Province/City', 'wp-store-shipping'),
                    'required' => true,
                    'type' => 'select',
                    'class' => array('form-row-first'),
                    'options' => array($state_code => $state_name),
                    'default' => $state_code,
                    'custom_attributes' => array(
                        'disabled' => 'disabled',
                    ),
                    'priority' => 22
                );

                // Check delivery method to determine if ward and address are required
                // For pickup orders, these fields are not required
                $delivery_method = isset($_POST['delivery_method']) ? sanitize_text_field($_POST['delivery_method']) : 'delivery';
                $is_pickup = ($delivery_method === 'pickup');

                // Ward/Commune field
                $fields['billing']['billing_city'] = array(
                    'label' => __('Ward/Commune', 'wp-store-shipping'),
                    'required' => !$is_pickup, // Not required for pickup
                    'type' => 'select',
                    'class' => array('form-row-last'),
                    'placeholder' => _x('Select Ward/Commune', 'placeholder', 'wp-store-shipping'),
                    'options' => $ward_options,
                    'priority' => 23
                );

                // Street address (House number, street name)
                $fields['billing']['billing_address_1']['label'] = __('House Number, Street Name', 'wp-store-shipping');
                $fields['billing']['billing_address_1']['placeholder'] = _x('Ex: 194 Le Thanh Ton', 'placeholder', 'wp-store-shipping');
                $fields['billing']['billing_address_1']['required'] = !$is_pickup; // Not required for pickup
                $fields['billing']['billing_address_1']['class'] = array('form-row-wide');
                $fields['billing']['billing_address_1']['priority'] = 24;

                // Sort fields by priority
                uasort($fields['billing'], array($this, 'sort_fields_by_order'));
                // uasort($fields['shipping'], array($this, 'sort_fields_by_order'));

                return apply_filters('devvn_checkout_fields', $fields);
            }

            function sort_fields_by_order($a, $b)
            {
                if (!isset($b['priority']) || !isset($a['priority']) || $a['priority'] == $b['priority']) {
                    return 0;
                }
                return ($a['priority'] < $b['priority']) ? -1 : 1;
            }

            // Add admin menu under WooCommerce
            function add_shipping_admin_menu()
            {
                // Add "Shipping" menu
                add_submenu_page(
                    'woocommerce',
                    __('Shipping Fees', 'wp-store-shipping'),
                    __('Shipping', 'wp-store-shipping'),
                    'manage_woocommerce',
                    'hcm-shipping-fees',
                    array($this, 'render_shipping_fees_page')
                );
            }

            // AJAX handler for saving shipping fees
            function ajax_save_shipping_fee()
            {
                // Verify nonce
                if (!check_admin_referer('save_shipping_fee_action', 'save_shipping_fee_nonce')) {
                    wp_send_json_error(array('message' => __('Security check failed', 'wp-store-shipping')));
                    wp_die();
                }

                // Check user capability
                if (!current_user_can('manage_woocommerce')) {
                    wp_send_json_error(array('message' => __('Unauthorized access', 'wp-store-shipping')));
                    wp_die();
                }

                // Get and validate input
                $store_id = isset($_POST['store_id']) ? sanitize_text_field($_POST['store_id']) : '';
                $ward_id = isset($_POST['ward_id']) ? sanitize_text_field($_POST['ward_id']) : '';
                $fee = isset($_POST['fee']) ? floatval($_POST['fee']) : 0;

                if (empty($store_id) || empty($ward_id)) {
                    wp_send_json_error(array('message' => __('Store and Ward are required', 'wp-store-shipping')));
                    wp_die();
                }

                // Save shipping fee
                $shipping_fees = get_option('hcm_shipping_fees', array());
                $fee_key = $store_id . '_' . $ward_id;
                $shipping_fees[$fee_key] = array(
                    'store_id' => $store_id,
                    'ward_id' => $ward_id,
                    'fee' => $fee
                );
                update_option('hcm_shipping_fees', $shipping_fees);

                wp_send_json_success(array('message' => __('Shipping fee saved successfully!', 'wp-store-shipping')));
                wp_die();
            }

            // AJAX handler to get available wards for a store
            function ajax_get_store_wards()
            {
                global $xa_phuong_thitran;

                $store_id = isset($_GET['store_id']) ? sanitize_text_field($_GET['store_id']) : '';

                error_log('AJAX get_store_wards called - Store ID: ' . $store_id);

                if (empty($store_id)) {
                    error_log('AJAX get_store_wards - ERROR: Store ID is empty');
                    wp_send_json_error(array('message' => 'Store ID is required'));
                    wp_die();
                }

                // Get all shipping fees for this store
                $shipping_fees = get_option('hcm_shipping_fees', array());
                $available_wards = array();

                error_log('AJAX get_store_wards - Total shipping fees: ' . count($shipping_fees));

                foreach ($shipping_fees as $fee_key => $fee_data) {
                    // Fee key format: store_id_ward_id
                    if (isset($fee_data['store_id']) && $fee_data['store_id'] == $store_id) {
                        $ward_id = $fee_data['ward_id'];

                        // Find ward name from global data
                        foreach ($xa_phuong_thitran as $ward) {
                            if ($ward['xaid'] == $ward_id) {
                                $available_wards[] = array(
                                    'id' => $ward_id,
                                    'name' => $ward['name'],
                                    'fee' => $fee_data['fee']
                                );
                                break;
                            }
                        }
                    }
                }

                error_log('AJAX get_store_wards - Found ' . count($available_wards) . ' wards for store ' . $store_id);

                wp_send_json_success(array('wards' => $available_wards));
                wp_die();
            }

            // Filter stores to show only those currently open
            function filter_stores_by_opening_hours($store_data)
            {
                if (empty($store_data)) {
                    return $store_data;
                }

                // Get current time in Ho Chi Minh timezone
                date_default_timezone_set('Asia/Ho_Chi_Minh');
                $current_day = (int) date('w'); // 0=Sunday, 1=Monday, ..., 6=Saturday
                $current_time = (int) date('Hi'); // Format: HHMM (e.g., 1430 for 2:30 PM)

                // Determine which hours to check based on category filter
                // Category 42=PICKUP (check pickup_hours), 43=DELIVERY (check delivery_hours)
                $category_filter = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : '43';
                $hours_type = ($category_filter == '42') ? 'pickup' : 'delivery';

                $filtered_stores = array();

                foreach ($store_data as $store) {
                    // Check if store is currently open for the selected delivery method
                    $is_open = $this->is_store_open_now($store['id'], $current_day, $current_time, $hours_type);

                    if ($is_open) {
                        $filtered_stores[] = $store;
                    }
                }

                error_log('WP Store Shipping: Filtered stores (' . strtoupper($hours_type) . ') - Total: ' . count($store_data) . ', Open now: ' . count($filtered_stores) . ' at ' . date('l Hi'));

                return $filtered_stores;
            }

            // Add shipping/pickup fee to cart
            function add_shipping_pickup_fee()
            {
                if (is_admin() && !defined('DOING_AJAX')) {
                    return;
                }

                // Get delivery method from POST data
                $delivery_method = isset($_POST['post_data']) ? $_POST['post_data'] : '';
                parse_str($delivery_method, $post_data);

                // Get custom delivery fields
                $selected_method = isset($post_data['selected_delivery_method']) ? $post_data['selected_delivery_method'] : 'delivery';

                // Try to get selected_store from multiple sources
                $selected_store = '';
                if (isset($post_data['selected_store'])) {
                    $selected_store = sanitize_text_field($post_data['selected_store']);
                } elseif (isset($_POST['selected_store'])) {
                    $selected_store = sanitize_text_field($_POST['selected_store']);
                } elseif (WC()->session) {
                    // Fallback to session data
                    $selected_store = WC()->session->get('selected_store');
                }

                // Store in session for future use
                if (!empty($selected_store) && WC()->session) {
                    WC()->session->set('selected_store', $selected_store);
                }

                // Try to get billing_city from both post_data and direct POST
                $selected_ward = '';
                if (isset($post_data['billing_city'])) {
                    $selected_ward = sanitize_text_field($post_data['billing_city']);
                } elseif (isset($_POST['billing_city'])) {
                    $selected_ward = sanitize_text_field($_POST['billing_city']);
                }

                if ($selected_method === 'pickup') {
                    // PICKUP - Add 0 fee
                    WC()->cart->add_fee(__('Pickup', 'wp-store-shipping'), 0);
                } else {
                    // DELIVERY - Calculate shipping fee
                    $shipping_fee = 0;

                    error_log('Calculate Shipping Fee - Store: ' . $selected_store . ', Ward: ' . $selected_ward);

                    if (!empty($selected_store) && !empty($selected_ward)) {
                        $shipping_fees = get_option('hcm_shipping_fees', array());
                        $fee_key = $selected_store . '_' . $selected_ward;

                        error_log('Fee Key: ' . $fee_key);

                        if (isset($shipping_fees[$fee_key]) && isset($shipping_fees[$fee_key]['fee'])) {
                            $shipping_fee = floatval($shipping_fees[$fee_key]['fee']);
                            error_log('Shipping Fee Found: ' . $shipping_fee);
                        } else {
                            error_log('No shipping fee found for key: ' . $fee_key);
                        }
                    } else {
                        error_log('Store or Ward is empty - cannot calculate fee');
                    }

                    error_log('Adding fee to cart: ' . $shipping_fee);
                    WC()->cart->add_fee(__('Shipping Fee', 'wp-store-shipping'), $shipping_fee);
                }
            }

            // Initialize custom shipping method class
            function init_store_shipping_method()
            {
                if (!class_exists('WC_Store_Shipping_Method')) {
                    require_once plugin_dir_path(__FILE__) . 'class-wc-store-shipping-method.php';
                }
            }

            // Register custom shipping method
            function register_store_shipping_method($methods)
            {
                $methods['store_shipping'] = 'WC_Store_Shipping_Method';
                return $methods;
            }

            // Add shipping package to ensure WooCommerce recognizes shipping
            function add_shipping_package($packages)
            {
                if (empty($packages)) {
                    $packages = WC()->cart->get_shipping_packages();
                }
                return $packages;
            }

            // Enable payment gateways for checkout
            function enable_payment_gateways($available_gateways)
            {
                // Ensure payment gateways are available
                // This function simply returns all available gateways without filtering
                error_log('WP Store Shipping - Available payment gateways: ' . print_r(array_keys($available_gateways), true));
                return $available_gateways;
            }

            // Check if a store is currently open
            function is_store_open_now($store_id, $current_day, $current_time, $hours_type = 'delivery')
            {
                // Determine which meta key to check based on hours type
                // 'pickup' → wpsl_pickup_hours, 'delivery' → wpsl_delivery_hours
                $meta_key = ($hours_type == 'pickup') ? 'wpsl_pickup_hours' : 'wpsl_delivery_hours';

                // Get store opening hours from post meta
                $hours = get_post_meta($store_id, $meta_key, true);

                error_log('WP Store Shipping: Store ID ' . $store_id . ' checking ' . $meta_key);
                error_log('WP Store Shipping: Hours data: ' . print_r($hours, true));

                if (empty($hours) || !is_array($hours)) {
                    // No hours set, assume closed (don't show store)
                    error_log('WP Store Shipping: Store ID ' . $store_id . ' - NO HOURS SET for ' . $meta_key);
                    return false;
                }

                // Day names mapping (wpsl uses specific format)
                $day_names = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
                $day_name = $day_names[$current_day];

                error_log('WP Store Shipping: Today is ' . $day_name . ' (day=' . $current_day . '), current_time=' . $current_time);

                // Check if this day has hours set
                if (!isset($hours[$day_name]) || empty($hours[$day_name])) {
                    // No hours for this day, assume closed
                    error_log('WP Store Shipping: Store ID ' . $store_id . ' - NO HOURS for ' . $day_name);
                    return false;
                }

                $day_hours = $hours[$day_name];
                error_log('WP Store Shipping: Store ID ' . $store_id . ' - ' . $day_name . ' hours: ' . print_r($day_hours, true));

                // Check each time slot for today
                foreach ($day_hours as $period) {
                    // WPSL stores hours as comma-separated string: "11:00 AM,10:00 PM"
                    if (is_string($period)) {
                        $times = explode(',', $period);
                        if (count($times) != 2) {
                            continue;
                        }
                        $open_str = trim($times[0]);
                        $close_str = trim($times[1]);
                    } else if (is_array($period)) {
                        // Alternative format with 'open' and 'close' keys
                        if (empty($period['open']) || empty($period['close'])) {
                            continue;
                        }
                        $open_str = $period['open'];
                        $close_str = $period['close'];
                    } else {
                        continue;
                    }

                    // Convert 12-hour time with AM/PM to 24-hour HHMM format
                    $open_time = $this->convert_to_24h($open_str);
                    $close_time = $this->convert_to_24h($close_str);

                    error_log('WP Store Shipping: Checking period - open: ' . $open_str . ' (' . $open_time . '), close: ' . $close_str . ' (' . $close_time . '), current: ' . $current_time);

                    // Check if current time is within this period
                    if ($current_time >= $open_time && $current_time <= $close_time) {
                        error_log('WP Store Shipping: Store ID ' . $store_id . ' - OPEN!');
                        return true;
                    }
                }

                error_log('WP Store Shipping: Store ID ' . $store_id . ' - CLOSED (no matching time period)');
                return false;
            }

            // Convert 12-hour time format (e.g., "11:00 AM") to 24-hour HHMM integer (e.g., 1100)
            function convert_to_24h($time_str)
            {
                // Remove extra spaces
                $time_str = trim($time_str);

                // Parse time format like "11:00 AM" or "10:00 PM"
                if (preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i', $time_str, $matches)) {
                    $hour = (int) $matches[1];
                    $minute = (int) $matches[2];
                    $period = strtoupper($matches[3]);

                    // Convert to 24-hour format
                    if ($period == 'PM' && $hour != 12) {
                        $hour += 12;
                    } else if ($period == 'AM' && $hour == 12) {
                        $hour = 0;
                    }

                    // Return as HHMM integer
                    return $hour * 100 + $minute;
                }

                // If format doesn't match, try to parse as-is (e.g., "14:30")
                if (preg_match('/^(\d{1,2}):(\d{2})$/', $time_str, $matches)) {
                    $hour = (int) $matches[1];
                    $minute = (int) $matches[2];
                    return $hour * 100 + $minute;
                }

                // Default to 0 if cannot parse
                return 0;
            }

            // Render shipping fees admin page
            function render_shipping_fees_page()
            {
                global $xa_phuong_thitran;

                // Handle delete action
                if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && check_admin_referer('delete_shipping_fee_' . $_GET['id'])) {
                    $shipping_fees = get_option('hcm_shipping_fees', array());
                    unset($shipping_fees[$_GET['id']]);
                    update_option('hcm_shipping_fees', $shipping_fees);
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Shipping fee deleted successfully!', 'wp-store-shipping') . '</p></div>';
                }

                // Get saved shipping fees
                $shipping_fees = get_option('hcm_shipping_fees', array());

                // Get all stores
                $stores = get_posts(array(
                    'post_type' => 'wpsl_stores',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'orderby' => 'title',
                    'order' => 'ASC'
                ));

                ?>
                <div class="wrap">
                    <h1 class="wp-heading-inline"><?php echo esc_html__('Shipping Fees - Ho Chi Minh City', 'wp-store-shipping'); ?></h1>
                    <a href="#" class="page-title-action" id="add-new-shipping-fee"><?php _e('Add New', 'wp-store-shipping'); ?></a>
                    <hr class="wp-header-end">

                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width: 30%;"><?php _e('Store', 'wp-store-shipping'); ?></th>
                                <th style="width: 35%;"><?php _e('Ward/Commune', 'wp-store-shipping'); ?></th>
                                <th style="width: 20%;"><?php _e('Shipping Fee', 'wp-store-shipping'); ?></th>
                                <th style="width: 15%;"><?php _e('Actions', 'wp-store-shipping'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($shipping_fees)) {
                                foreach ($shipping_fees as $fee_key => $fee_data) {
                                    $store_id = isset($fee_data['store_id']) ? $fee_data['store_id'] : '';
                                    $ward_id = $fee_data['ward_id'];
                                    $fee = $fee_data['fee'];

                                    // Find store name
                                    $store_name = __('All Stores', 'wp-store-shipping');
                                    if (!empty($store_id)) {
                                        $store = get_post($store_id);
                                        if ($store) {
                                            $store_name = $store->post_title;
                                        }
                                    }

                                    // Find ward name
                                    $ward_name = '';
                                    if (!empty($xa_phuong_thitran)) {
                                        foreach ($xa_phuong_thitran as $ward) {
                                            if ($ward['xaid'] == $ward_id) {
                                                $ward_name = $ward['name'];
                                                break;
                                            }
                                        }
                                    }

                                    $delete_url = wp_nonce_url(
                                        add_query_arg(array('action' => 'delete', 'id' => $fee_key)),
                                        'delete_shipping_fee_' . $fee_key
                                    );
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html($store_name); ?></td>
                                        <td><?php echo esc_html($ward_name); ?></td>
                                        <td><?php echo number_format($fee, 0, ',', '.') . ' ₫'; ?></td>
                                        <td>
                                            <a href="#" class="edit-shipping-fee"
                                               data-store-id="<?php echo esc_attr($store_id); ?>"
                                               data-ward-id="<?php echo esc_attr($ward_id); ?>"
                                               data-fee="<?php echo esc_attr($fee); ?>">
                                                <?php _e('Edit', 'wp-store-shipping'); ?>
                                            </a>
                                            |
                                            <a href="<?php echo esc_url($delete_url); ?>"
                                               class="delete-shipping-fee"
                                               onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this shipping fee?', 'wp-store-shipping'); ?>');">
                                                <?php _e('Delete', 'wp-store-shipping'); ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="4" style="text-align: center;"><?php _e('No shipping fees configured yet. Click "Add New" to get started.', 'wp-store-shipping'); ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Modal Form -->
                <div id="shipping-fee-modal" style="display:none;">
                    <div id="shipping-fee-modal-backdrop" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 100000;"></div>
                    <div id="shipping-fee-modal-wrap" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 500px; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); z-index: 100001;">
                        <h2 id="modal-title"><?php _e('Add Shipping Fee', 'wp-store-shipping'); ?></h2>
                        <form id="shipping-fee-form">
                            <?php wp_nonce_field('save_shipping_fee_action', 'save_shipping_fee_nonce'); ?>
                            <input type="hidden" name="action" value="save_shipping_fee">

                            <p>
                                <label for="store_id"><strong><?php _e('Store', 'wp-store-shipping'); ?> *</strong></label>
                                <select name="store_id" id="store_id" required style="width: 100%;">
                                    <option value=""><?php _e('Select Store', 'wp-store-shipping'); ?></option>
                                    <?php
                                    if (!empty($stores)) {
                                        foreach ($stores as $store) {
                                            echo '<option value="' . esc_attr($store->ID) . '">' . esc_html($store->post_title) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </p>

                            <p>
                                <label for="ward_id"><strong><?php _e('Ward/Commune', 'wp-store-shipping'); ?> *</strong></label>
                                <select name="ward_id" id="ward_id" required style="width: 100%;">
                                    <option value=""><?php _e('Select Ward/Commune', 'wp-store-shipping'); ?></option>
                                    <?php
                                    if (!empty($xa_phuong_thitran)) {
                                        foreach ($xa_phuong_thitran as $ward) {
                                            echo '<option value="' . esc_attr($ward['xaid']) . '">' . esc_html($ward['name']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </p>

                            <p>
                                <label for="fee"><strong><?php _e('Shipping Fee (₫)', 'wp-store-shipping'); ?> *</strong></label>
                                <input type="number" name="fee" id="fee" required min="0" step="1000" style="width: 100%;" placeholder="0">
                            </p>

                            <p style="text-align: right; margin-top: 20px;">
                                <button type="button" class="button" id="cancel-shipping-fee"><?php _e('Cancel', 'wp-store-shipping'); ?></button>
                                <button type="submit" class="button button-primary"><?php _e('Save', 'wp-store-shipping'); ?></button>
                            </p>
                        </form>
                    </div>
                </div>

                <script>
                jQuery(document).ready(function($) {
                    var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';

                    // Open modal for Add New
                    $('#add-new-shipping-fee').on('click', function(e) {
                        e.preventDefault();
                        $('#modal-title').text('<?php _e('Add Shipping Fee', 'wp-store-shipping'); ?>');
                        $('#shipping-fee-form')[0].reset();
                        $('#store_id').prop('disabled', false);
                        $('#ward_id').prop('disabled', false);
                        $('#shipping-fee-modal').show();
                    });

                    // Open modal for Edit
                    $('.edit-shipping-fee').on('click', function(e) {
                        e.preventDefault();
                        var storeId = $(this).data('store-id');
                        var wardId = $(this).data('ward-id');
                        var fee = $(this).data('fee');

                        $('#modal-title').text('<?php _e('Edit Shipping Fee', 'wp-store-shipping'); ?>');
                        $('#store_id').val(storeId).prop('disabled', true);
                        $('#ward_id').val(wardId).prop('disabled', true);
                        $('#fee').val(fee);
                        $('#shipping-fee-modal').show();
                    });

                    // Close modal
                    $('#cancel-shipping-fee, #shipping-fee-modal-backdrop').on('click', function() {
                        $('#shipping-fee-modal').hide();
                    });

                    // Save form
                    $('#shipping-fee-form').on('submit', function(e) {
                        e.preventDefault();
                        var formData = $(this).serialize();

                        $.post(ajaxUrl, formData, function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert('<?php _e('Error saving shipping fee', 'wp-store-shipping'); ?>');
                            }
                        });
                    });
                });
                </script>
                <?php
            }

        }

        // Initialize the plugin
        add_action('plugins_loaded', array('Custom_Shipping_Checkout_Class', 'init'));
    }
}
