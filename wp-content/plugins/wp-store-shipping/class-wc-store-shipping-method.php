<?php
/**
 * Custom Store Shipping Method for WooCommerce
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WC_Store_Shipping_Method')) {

    class WC_Store_Shipping_Method extends WC_Shipping_Method
    {
        /**
         * Constructor
         */
        public function __construct($instance_id = 0)
        {
            $this->id = 'store_shipping';
            $this->instance_id = absint($instance_id);
            $this->method_title = __('Store Shipping', 'wp-store-shipping');
            $this->method_description = __('Custom shipping method for store-based delivery and pickup', 'wp-store-shipping');
            $this->supports = array(
                'shipping-zones',
                'instance-settings',
            );

            $this->init();
        }

        /**
         * Initialize shipping method
         */
        public function init()
        {
            // Load the settings
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables
            $this->enabled = $this->get_option('enabled');
            $this->title = $this->get_option('title');

            // Save settings
            add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
        }

        /**
         * Initialize form fields
         */
        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'wp-store-shipping'),
                    'type' => 'checkbox',
                    'label' => __('Enable Store Shipping', 'wp-store-shipping'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __('Method Title', 'wp-store-shipping'),
                    'type' => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'wp-store-shipping'),
                    'default' => __('Store Shipping', 'wp-store-shipping'),
                    'desc_tip' => true
                )
            );
        }

        /**
         * Calculate shipping cost
         */
        public function calculate_shipping($package = array())
        {
            // The fee is added via add_fee() in shipping-functions.php
            // We just need to register the shipping method as available
            $rate = array(
                'id' => $this->id,
                'label' => $this->title,
                'cost' => 0, // Cost is handled by cart fees
                'package' => $package,
            );

            $this->add_rate($rate);
        }
    }
}
