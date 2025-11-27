<?php
/*
 * Plugin Name: Custom Shipping Checkout for WooCommerce
 * Plugin URI: https://terravivapizza.com
 * Description: Custom checkout fields for WooCommerce - Ho Chi Minh City only
 * Version: 2.0.0
 * Author: WooPizza Team
 * Author URI: https://terravivapizza.com
 * Text Domain: custom-shipping-checkout
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if WooCommerce is active
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    // Include city data
    include 'cities/tinh_thanhpho.php';
    include 'cities/quan_huyen.php';
    include 'cities/xa_phuong_thitran.php';

    if (!class_exists('Custom_Shipping_Checkout_Class')) {
        class Custom_Shipping_Checkout_Class
        {
            protected static $instance;

            public static function init()
            {
                is_null(self::$instance) AND self::$instance = new self;
                return self::$instance;
            }

            public function __construct()
            {
                // Add checkout fields filter
                add_filter('woocommerce_checkout_fields', array($this, 'custom_override_checkout_fields'), 999999);

                // Add states filter
                add_filter('woocommerce_states', array($this, 'vietnam_cities_woocommerce'), 99999);

                // Add admin menu
                add_action('admin_menu', array($this, 'add_shipping_admin_menu'), 99);
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

                // Prepare ward/commune options
                $ward_options = array('' => __('Select Ward/Commune', 'custom-shipping-checkout'));
                if (!empty($xa_phuong_thitran)) {
                    foreach ($xa_phuong_thitran as $ward) {
                        if (isset($ward['xaid']) && isset($ward['name'])) {
                            $ward_options[$ward['xaid']] = $ward['name'];
                        }
                    }
                }

                // Billing fields
                $fields['billing']['billing_last_name'] = array(
                    'label' => __('Full name', 'custom-shipping-checkout'),
                    'placeholder' => _x('Type Full name', 'placeholder', 'custom-shipping-checkout'),
                    'required' => true,
                    'class' => array('form-row-wide'),
                    'clear' => true,
                    'priority' => 10
                );

                if (isset($fields['billing']['billing_phone'])) {
                    $fields['billing']['billing_phone']['class'] = array('form-row-first');
                    $fields['billing']['billing_phone']['placeholder'] = __('Type your phone', 'custom-shipping-checkout');
                    $fields['billing']['billing_phone']['priority'] = 20;
                }

                if (isset($fields['billing']['billing_email'])) {
                    $fields['billing']['billing_email']['class'] = array('form-row-last');
                    $fields['billing']['billing_email']['placeholder'] = __('Type your email', 'custom-shipping-checkout');
                    $fields['billing']['billing_email']['priority'] = 21;
                }

                // Province/City field - Hidden, always HOCHIMINH
                $fields['billing']['billing_state'] = array(
                    'label' => __('Thành phố Hồ Chí Minh', 'custom-shipping-checkout'),
                    'required' => true,
                    'type' => 'text',
                    'class' => array('form-row-wide'),
                    'default' => 'HOCHIMINH',
                    'custom_attributes' => array(
                        'readonly' => 'readonly',
                        'style' => 'display:none;'
                    ),
                    'priority' => 30
                );

                // Ward/Commune field
                $fields['billing']['billing_address_2'] = array(
                    'label' => __('Ward/Commune', 'custom-shipping-checkout'),
                    'required' => true,
                    'type' => 'select',
                    'class' => array('form-row-wide'),
                    'placeholder' => _x('Select Ward/Commune', 'placeholder', 'custom-shipping-checkout'),
                    'options' => $ward_options,
                    'priority' => 40
                );

                // Street address
                $fields['billing']['billing_address_1']['placeholder'] = _x('Ex: No. 20, 90 Alley', 'placeholder', 'custom-shipping-checkout');
                $fields['billing']['billing_address_1']['class'] = array('form-row-wide');
                $fields['billing']['billing_address_1']['priority'] = 60;

                // Remove unnecessary billing fields
                unset($fields['billing']['billing_first_name']);
                unset($fields['billing']['billing_country']);
                unset($fields['billing']['billing_company']);

                // Shipping fields
                $fields['shipping']['shipping_last_name'] = array(
                    'label' => __('Recipient full name', 'custom-shipping-checkout'),
                    'placeholder' => _x('Recipient full name', 'placeholder', 'custom-shipping-checkout'),
                    'required' => true,
                    'class' => array('form-row-first'),
                    'clear' => true,
                    'priority' => 10
                );

                $fields['shipping']['shipping_phone'] = array(
                    'label' => __('Recipient phone', 'custom-shipping-checkout'),
                    'placeholder' => _x('Recipient phone', 'placeholder', 'custom-shipping-checkout'),
                    'required' => false,
                    'class' => array('form-row-last'),
                    'clear' => true,
                    'priority' => 20
                );

                // Province/City field - Hidden, always HOCHIMINH
                $fields['shipping']['shipping_state'] = array(
                    'label' => __('Thành phố Hồ Chí Minh', 'custom-shipping-checkout'),
                    'required' => true,
                    'type' => 'text',
                    'class' => array('form-row-wide'),
                    'default' => 'HOCHIMINH',
                    'custom_attributes' => array(
                        'readonly' => 'readonly',
                        'style' => 'display:none;'
                    ),
                    'priority' => 30
                );

                // Ward/Commune field
                $fields['shipping']['shipping_address_2'] = array(
                    'label' => __('Ward/Commune', 'custom-shipping-checkout'),
                    'required' => true,
                    'type' => 'select',
                    'class' => array('form-row-wide'),
                    'placeholder' => _x('Select Ward/Commune', 'placeholder', 'custom-shipping-checkout'),
                    'options' => $ward_options,
                    'priority' => 40
                );

                // Street address
                $fields['shipping']['shipping_address_1']['placeholder'] = _x('Ex: No. 20, 90 Alley', 'placeholder', 'custom-shipping-checkout');
                $fields['shipping']['shipping_address_1']['class'] = array('form-row-wide');
                $fields['shipping']['shipping_address_1']['priority'] = 60;

                // Remove unnecessary shipping fields
                unset($fields['shipping']['shipping_first_name']);
                unset($fields['shipping']['shipping_country']);
                unset($fields['shipping']['shipping_company']);

                // Sort fields by priority
                uasort($fields['billing'], array($this, 'sort_fields_by_order'));
                uasort($fields['shipping'], array($this, 'sort_fields_by_order'));

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
                add_submenu_page(
                    'woocommerce',
                    __('Shipping Fees', 'custom-shipping-checkout'),
                    __('Shipping', 'custom-shipping-checkout'),
                    'manage_woocommerce',
                    'hcm-shipping-fees',
                    array($this, 'render_shipping_fees_page')
                );
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
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Shipping fee deleted successfully!', 'custom-shipping-checkout') . '</p></div>';
                }

                // Handle save action (AJAX)
                if (isset($_POST['action']) && $_POST['action'] === 'save_shipping_fee' && check_admin_referer('save_shipping_fee_action', 'save_shipping_fee_nonce')) {
                    $shipping_fees = get_option('hcm_shipping_fees', array());
                    $ward_id = sanitize_text_field($_POST['ward_id']);
                    $shipping_fees[$ward_id] = array(
                        'ward_id' => $ward_id,
                        'fee' => floatval($_POST['fee'])
                    );
                    update_option('hcm_shipping_fees', $shipping_fees);
                    wp_send_json_success(array('message' => __('Shipping fee saved successfully!', 'custom-shipping-checkout')));
                }

                // Get saved shipping fees
                $shipping_fees = get_option('hcm_shipping_fees', array());

                ?>
                <div class="wrap">
                    <h1 class="wp-heading-inline"><?php echo esc_html__('Shipping Fees - Ho Chi Minh City', 'custom-shipping-checkout'); ?></h1>
                    <a href="#" class="page-title-action" id="add-new-shipping-fee"><?php _e('Add New', 'custom-shipping-checkout'); ?></a>
                    <hr class="wp-header-end">

                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width: 60%;"><?php _e('Ward/Commune', 'custom-shipping-checkout'); ?></th>
                                <th style="width: 25%;"><?php _e('Shipping Fee', 'custom-shipping-checkout'); ?></th>
                                <th style="width: 15%;"><?php _e('Actions', 'custom-shipping-checkout'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($shipping_fees)) {
                                foreach ($shipping_fees as $ward_id => $fee_data) {
                                    $fee = $fee_data['fee'];

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
                                        add_query_arg(array('action' => 'delete', 'id' => $ward_id)),
                                        'delete_shipping_fee_' . $ward_id
                                    );
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html($ward_name); ?></td>
                                        <td><?php echo number_format($fee, 0, ',', '.') . ' ₫'; ?></td>
                                        <td>
                                            <a href="#" class="edit-shipping-fee"
                                               data-ward-id="<?php echo esc_attr($ward_id); ?>"
                                               data-fee="<?php echo esc_attr($fee); ?>">
                                                <?php _e('Edit', 'custom-shipping-checkout'); ?>
                                            </a>
                                            |
                                            <a href="<?php echo esc_url($delete_url); ?>"
                                               class="delete-shipping-fee"
                                               onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this shipping fee?', 'custom-shipping-checkout'); ?>');">
                                                <?php _e('Delete', 'custom-shipping-checkout'); ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="3" style="text-align: center;"><?php _e('No shipping fees configured yet. Click "Add New" to get started.', 'custom-shipping-checkout'); ?></td>
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
                        <h2 id="modal-title"><?php _e('Add Shipping Fee', 'custom-shipping-checkout'); ?></h2>
                        <form id="shipping-fee-form">
                            <?php wp_nonce_field('save_shipping_fee_action', 'save_shipping_fee_nonce'); ?>
                            <input type="hidden" name="action" value="save_shipping_fee">

                            <p>
                                <label for="ward_id"><strong><?php _e('Ward/Commune', 'custom-shipping-checkout'); ?> *</strong></label>
                                <select name="ward_id" id="ward_id" required style="width: 100%;">
                                    <option value=""><?php _e('Select Ward/Commune', 'custom-shipping-checkout'); ?></option>
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
                                <label for="fee"><strong><?php _e('Shipping Fee (₫)', 'custom-shipping-checkout'); ?> *</strong></label>
                                <input type="number" name="fee" id="fee" required min="0" step="1000" style="width: 100%;" placeholder="0">
                            </p>

                            <p style="text-align: right; margin-top: 20px;">
                                <button type="button" class="button" id="cancel-shipping-fee"><?php _e('Cancel', 'custom-shipping-checkout'); ?></button>
                                <button type="submit" class="button button-primary"><?php _e('Save', 'custom-shipping-checkout'); ?></button>
                            </p>
                        </form>
                    </div>
                </div>

                <script>
                jQuery(document).ready(function($) {
                    // Open modal for Add New
                    $('#add-new-shipping-fee').on('click', function(e) {
                        e.preventDefault();
                        $('#modal-title').text('<?php _e('Add Shipping Fee', 'custom-shipping-checkout'); ?>');
                        $('#shipping-fee-form')[0].reset();
                        $('#ward_id').prop('disabled', false);
                        $('#shipping-fee-modal').show();
                    });

                    // Open modal for Edit
                    $('.edit-shipping-fee').on('click', function(e) {
                        e.preventDefault();
                        var wardId = $(this).data('ward-id');
                        var fee = $(this).data('fee');

                        $('#modal-title').text('<?php _e('Edit Shipping Fee', 'custom-shipping-checkout'); ?>');
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

                        $.post(ajaxurl, formData, function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert('<?php _e('Error saving shipping fee', 'custom-shipping-checkout'); ?>');
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
