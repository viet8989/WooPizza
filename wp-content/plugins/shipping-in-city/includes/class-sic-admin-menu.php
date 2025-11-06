<?php
/**
 * Admin Menu Management
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class SIC_Admin_Menu {

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_post_sic_save_district', array($this, 'handle_save_district'));
        add_action('admin_post_sic_save_ward', array($this, 'handle_save_ward'));
        add_action('admin_post_sic_save_shipping_price', array($this, 'handle_save_shipping_price'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Shipping in City', 'shipping-in-city'),
            __('Shipping in City', 'shipping-in-city'),
            'manage_options',
            'shipping-in-city',
            array($this, 'render_main_page'),
            'dashicons-location',
            56
        );

        // Districts submenu
        add_submenu_page(
            'shipping-in-city',
            __('Districts', 'shipping-in-city'),
            __('Districts', 'shipping-in-city'),
            'manage_options',
            'sic-districts',
            array($this, 'render_districts_page')
        );

        // Wards submenu
        add_submenu_page(
            'shipping-in-city',
            __('Wards', 'shipping-in-city'),
            __('Wards', 'shipping-in-city'),
            'manage_options',
            'sic-wards',
            array($this, 'render_wards_page')
        );

        // Shipping Prices
        add_submenu_page(
            'shipping-in-city',
            __('Shipping Prices', 'shipping-in-city'),
            __('Shipping Prices', 'shipping-in-city'),
            'manage_options',
            'sic-shipping-prices',
            array($this, 'render_shipping_prices_page')
        );

        // Add New Shipping Price
        add_submenu_page(
            'shipping-in-city',
            __('Add Shipping Price', 'shipping-in-city'),
            __('Add Shipping Price', 'shipping-in-city'),
            'manage_options',
            'sic-add-shipping-price',
            array($this, 'render_add_shipping_price_page')
        );

        // Edit Shipping Price (hidden from menu)
        add_submenu_page(
            null,
            __('Edit Shipping Price', 'shipping-in-city'),
            __('Edit Shipping Price', 'shipping-in-city'),
            'manage_options',
            'sic-edit-shipping-price',
            array($this, 'render_edit_shipping_price_page')
        );
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'shipping-in-city') !== false || strpos($hook, 'sic-') !== false) {
            wp_enqueue_style('sic-admin-css', SIC_PLUGIN_URL . 'assets/css/admin.css', array(), SIC_VERSION);
            wp_enqueue_script('sic-admin-js', SIC_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), SIC_VERSION, true);

            wp_localize_script('sic-admin-js', 'sicAjax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('sic_nonce'),
            ));
        }
    }

    /**
     * Render main page
     */
    public function render_main_page() {
        $district_obj = SIC_District::get_instance();
        $ward_obj = SIC_Ward::get_instance();
        $shipping_obj = SIC_Shipping_Price::get_instance();

        ?>
        <div class="wrap">
            <h1><?php _e('Shipping in City', 'shipping-in-city'); ?></h1>
            <p><?php _e('Manage districts, wards, and shipping prices for your city.', 'shipping-in-city'); ?></p>

            <div class="sic-dashboard">
                <div class="sic-card">
                    <h2><?php _e('Quick Links', 'shipping-in-city'); ?></h2>
                    <ul>
                        <li><a href="<?php echo admin_url('admin.php?page=sic-districts'); ?>"><?php _e('Manage Districts', 'shipping-in-city'); ?></a></li>
                        <li><a href="<?php echo admin_url('admin.php?page=sic-wards'); ?>"><?php _e('Manage Wards', 'shipping-in-city'); ?></a></li>
                        <li><a href="<?php echo admin_url('admin.php?page=sic-shipping-prices'); ?>"><?php _e('Manage Shipping Prices', 'shipping-in-city'); ?></a></li>
                    </ul>
                </div>

                <div class="sic-card">
                    <h2><?php _e('Statistics', 'shipping-in-city'); ?></h2>
                    <ul>
                        <li><?php printf(__('Total Districts: %d', 'shipping-in-city'), $district_obj->get_count()); ?></li>
                        <li><?php printf(__('Total Wards: %d', 'shipping-in-city'), $ward_obj->get_count()); ?></li>
                        <li><?php printf(__('Total Shipping Prices: %d', 'shipping-in-city'), $shipping_obj->get_total_count()); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render districts page (Category-style)
     */
    public function render_districts_page() {
        $district_obj = SIC_District::get_instance();
        $districts = $district_obj->get_all();

        // Check if editing
        $editing = false;
        $edit_district = null;
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
            $editing = true;
            $edit_district = $district_obj->get_by_id($_GET['id']);
        }

        ?>
        <div class="wrap">
            <h1><?php _e('Districts', 'shipping-in-city'); ?></h1>

            <?php if (isset($_GET['message'])) : ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <?php
                        if ($_GET['message'] === 'added') {
                            _e('District added successfully.', 'shipping-in-city');
                        } elseif ($_GET['message'] === 'updated') {
                            _e('District updated successfully.', 'shipping-in-city');
                        } elseif ($_GET['message'] === 'deleted') {
                            _e('District deleted successfully.', 'shipping-in-city');
                        }
                        ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])) : ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php echo esc_html(urldecode($_GET['error'])); ?></p>
                </div>
            <?php endif; ?>

            <div id="col-container" class="wp-clearfix">
                <!-- Add/Edit Form -->
                <div id="col-left">
                    <div class="col-wrap">
                        <div class="form-wrap">
                            <h2><?php echo $editing ? __('Edit District', 'shipping-in-city') : __('Add New District', 'shipping-in-city'); ?></h2>
                            <form id="addtag" method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="validate">
                                <input type="hidden" name="action" value="sic_save_district">
                                <?php wp_nonce_field('sic_save_district', 'sic_nonce'); ?>

                                <?php if ($editing && $edit_district) : ?>
                                    <input type="hidden" name="district_id" value="<?php echo esc_attr($edit_district->id); ?>">
                                <?php endif; ?>

                                <div class="form-field form-required term-name-wrap">
                                    <label for="district-name"><?php _e('Name', 'shipping-in-city'); ?></label>
                                    <input name="name" id="district-name" type="text" value="<?php echo $editing && $edit_district ? esc_attr($edit_district->name) : ''; ?>" size="40" required>
                                    <p><?php _e('The name is how it appears on your site.', 'shipping-in-city'); ?></p>
                                </div>

                                <div class="form-field term-description-wrap">
                                    <label for="district-description"><?php _e('Description', 'shipping-in-city'); ?></label>
                                    <textarea name="description" id="district-description" rows="5" cols="40"><?php echo $editing && $edit_district ? esc_textarea($edit_district->description) : ''; ?></textarea>
                                    <p><?php _e('The description is not prominent by default; however, some themes may show it.', 'shipping-in-city'); ?></p>
                                </div>

                                <p class="submit">
                                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo $editing ? __('Update', 'shipping-in-city') : __('Add New District', 'shipping-in-city'); ?>">
                                    <?php if ($editing) : ?>
                                        <a href="<?php echo admin_url('admin.php?page=sic-districts'); ?>" class="button"><?php _e('Cancel', 'shipping-in-city'); ?></a>
                                    <?php endif; ?>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- List Table -->
                <div id="col-right">
                    <div class="col-wrap">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th scope="col" class="manage-column column-name column-primary"><?php _e('Name', 'shipping-in-city'); ?></th>
                                    <th scope="col" class="manage-column column-description"><?php _e('Description', 'shipping-in-city'); ?></th>
                                    <th scope="col" class="manage-column column-slug"><?php _e('Slug', 'shipping-in-city'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="the-list">
                                <?php if (empty($districts)) : ?>
                                    <tr class="no-items">
                                        <td class="colspanchange" colspan="3"><?php _e('No districts found.', 'shipping-in-city'); ?></td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($districts as $district) : ?>
                                        <tr id="district-<?php echo esc_attr($district->id); ?>">
                                            <td class="name column-name has-row-actions column-primary" data-colname="Name">
                                                <strong><a href="<?php echo admin_url('admin.php?page=sic-districts&action=edit&id=' . $district->id); ?>"><?php echo esc_html($district->name); ?></a></strong>
                                                <div class="row-actions">
                                                    <span class="edit"><a href="<?php echo admin_url('admin.php?page=sic-districts&action=edit&id=' . $district->id); ?>"><?php _e('Edit', 'shipping-in-city'); ?></a> | </span>
                                                    <span class="delete"><a href="#" class="sic-delete-item" data-type="district" data-id="<?php echo esc_attr($district->id); ?>" style="color: #a00;"><?php _e('Delete', 'shipping-in-city'); ?></a></span>
                                                </div>
                                            </td>
                                            <td class="description column-description" data-colname="Description">
                                                <?php echo esc_html($district->description); ?>
                                            </td>
                                            <td class="slug column-slug" data-colname="Slug">
                                                <?php echo esc_html($district->slug); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render wards page (Category-style)
     */
    public function render_wards_page() {
        $ward_obj = SIC_Ward::get_instance();
        $district_obj = SIC_District::get_instance();
        $districts = $district_obj->get_all();
        $wards = $ward_obj->get_all();

        // Check if editing
        $editing = false;
        $edit_ward = null;
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
            $editing = true;
            $edit_ward = $ward_obj->get_by_id($_GET['id']);
        }

        ?>
        <div class="wrap">
            <h1><?php _e('Wards', 'shipping-in-city'); ?></h1>

            <?php if (isset($_GET['message'])) : ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <?php
                        if ($_GET['message'] === 'added') {
                            _e('Ward added successfully.', 'shipping-in-city');
                        } elseif ($_GET['message'] === 'updated') {
                            _e('Ward updated successfully.', 'shipping-in-city');
                        } elseif ($_GET['message'] === 'deleted') {
                            _e('Ward deleted successfully.', 'shipping-in-city');
                        }
                        ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])) : ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php echo esc_html(urldecode($_GET['error'])); ?></p>
                </div>
            <?php endif; ?>

            <div id="col-container" class="wp-clearfix">
                <!-- Add/Edit Form -->
                <div id="col-left">
                    <div class="col-wrap">
                        <div class="form-wrap">
                            <h2><?php echo $editing ? __('Edit Ward', 'shipping-in-city') : __('Add New Ward', 'shipping-in-city'); ?></h2>
                            <form id="addtag" method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="validate">
                                <input type="hidden" name="action" value="sic_save_ward">
                                <?php wp_nonce_field('sic_save_ward', 'sic_nonce'); ?>

                                <?php if ($editing && $edit_ward) : ?>
                                    <input type="hidden" name="ward_id" value="<?php echo esc_attr($edit_ward->id); ?>">
                                <?php endif; ?>

                                <div class="form-field form-required term-name-wrap">
                                    <label for="ward-name"><?php _e('Name', 'shipping-in-city'); ?></label>
                                    <input name="name" id="ward-name" type="text" value="<?php echo $editing && $edit_ward ? esc_attr($edit_ward->name) : ''; ?>" size="40" required>
                                    <p><?php _e('The name is how it appears on your site.', 'shipping-in-city'); ?></p>
                                </div>

                                <div class="form-field form-required">
                                    <label for="district-id"><?php _e('District', 'shipping-in-city'); ?></label>
                                    <select name="district_id" id="district-id" required>
                                        <option value=""><?php _e('-- Select District --', 'shipping-in-city'); ?></option>
                                        <?php foreach ($districts as $district) : ?>
                                            <option value="<?php echo esc_attr($district->id); ?>" <?php echo $editing && $edit_ward && $edit_ward->district_id == $district->id ? 'selected' : ''; ?>>
                                                <?php echo esc_html($district->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p><?php _e('The district this ward belongs to.', 'shipping-in-city'); ?></p>
                                </div>

                                <div class="form-field term-description-wrap">
                                    <label for="ward-description"><?php _e('Description', 'shipping-in-city'); ?></label>
                                    <textarea name="description" id="ward-description" rows="5" cols="40"><?php echo $editing && $edit_ward ? esc_textarea($edit_ward->description) : ''; ?></textarea>
                                    <p><?php _e('The description is not prominent by default; however, some themes may show it.', 'shipping-in-city'); ?></p>
                                </div>

                                <p class="submit">
                                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo $editing ? __('Update', 'shipping-in-city') : __('Add New Ward', 'shipping-in-city'); ?>">
                                    <?php if ($editing) : ?>
                                        <a href="<?php echo admin_url('admin.php?page=sic-wards'); ?>" class="button"><?php _e('Cancel', 'shipping-in-city'); ?></a>
                                    <?php endif; ?>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- List Table -->
                <div id="col-right">
                    <div class="col-wrap">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th scope="col" class="manage-column column-name column-primary"><?php _e('Name', 'shipping-in-city'); ?></th>
                                    <th scope="col" class="manage-column column-district"><?php _e('District', 'shipping-in-city'); ?></th>
                                    <th scope="col" class="manage-column column-description"><?php _e('Description', 'shipping-in-city'); ?></th>
                                    <th scope="col" class="manage-column column-slug"><?php _e('Slug', 'shipping-in-city'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="the-list">
                                <?php if (empty($wards)) : ?>
                                    <tr class="no-items">
                                        <td class="colspanchange" colspan="4"><?php _e('No wards found.', 'shipping-in-city'); ?></td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($wards as $ward) : ?>
                                        <?php
                                        $district = $district_obj->get_by_id($ward->district_id);
                                        $district_name = $district ? $district->name : __('N/A', 'shipping-in-city');
                                        ?>
                                        <tr id="ward-<?php echo esc_attr($ward->id); ?>">
                                            <td class="name column-name has-row-actions column-primary" data-colname="Name">
                                                <strong><a href="<?php echo admin_url('admin.php?page=sic-wards&action=edit&id=' . $ward->id); ?>"><?php echo esc_html($ward->name); ?></a></strong>
                                                <div class="row-actions">
                                                    <span class="edit"><a href="<?php echo admin_url('admin.php?page=sic-wards&action=edit&id=' . $ward->id); ?>"><?php _e('Edit', 'shipping-in-city'); ?></a> | </span>
                                                    <span class="delete"><a href="#" class="sic-delete-item" data-type="ward" data-id="<?php echo esc_attr($ward->id); ?>" style="color: #a00;"><?php _e('Delete', 'shipping-in-city'); ?></a></span>
                                                </div>
                                            </td>
                                            <td class="district column-district" data-colname="District">
                                                <?php echo esc_html($district_name); ?>
                                            </td>
                                            <td class="description column-description" data-colname="Description">
                                                <?php echo esc_html($ward->description); ?>
                                            </td>
                                            <td class="slug column-slug" data-colname="Slug">
                                                <?php echo esc_html($ward->slug); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render shipping prices list page
     */
    public function render_shipping_prices_page() {
        $shipping_price = SIC_Shipping_Price::get_instance();
        $page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        $shipping_prices = $shipping_price->get_all_shipping_prices($per_page, $offset);
        $total = $shipping_price->get_total_count();
        $total_pages = ceil($total / $per_page);

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e('Shipping Prices', 'shipping-in-city'); ?></h1>
            <a href="<?php echo admin_url('admin.php?page=sic-add-shipping-price'); ?>" class="page-title-action"><?php _e('Add New', 'shipping-in-city'); ?></a>
            <hr class="wp-header-end">

            <?php if (isset($_GET['message'])) : ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <?php
                        if ($_GET['message'] === 'added') {
                            _e('Shipping price added successfully.', 'shipping-in-city');
                        } elseif ($_GET['message'] === 'updated') {
                            _e('Shipping price updated successfully.', 'shipping-in-city');
                        } elseif ($_GET['message'] === 'deleted') {
                            _e('Shipping price deleted successfully.', 'shipping-in-city');
                        }
                        ?>
                    </p>
                </div>
            <?php endif; ?>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 50px;"><?php _e('ID', 'shipping-in-city'); ?></th>
                        <th><?php _e('Store', 'shipping-in-city'); ?></th>
                        <th><?php _e('District', 'shipping-in-city'); ?></th>
                        <th><?php _e('Ward', 'shipping-in-city'); ?></th>
                        <th><?php _e('Price', 'shipping-in-city'); ?></th>
                        <th><?php _e('Actions', 'shipping-in-city'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($shipping_prices)) : ?>
                        <tr>
                            <td colspan="6"><?php _e('No shipping prices found.', 'shipping-in-city'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($shipping_prices as $price) : ?>
                            <tr>
                                <td><?php echo esc_html($price['id']); ?></td>
                                <td><?php echo esc_html($price['store_name']); ?></td>
                                <td><?php echo esc_html($price['district_name']); ?></td>
                                <td><?php echo esc_html($price['ward_name']); ?></td>
                                <td><?php echo wc_price($price['price']); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=sic-edit-shipping-price&id=' . $price['id']); ?>"><?php _e('Edit', 'shipping-in-city'); ?></a> |
                                    <a href="#" class="sic-delete-price" data-id="<?php echo esc_attr($price['id']); ?>" style="color: #a00;"><?php _e('Delete', 'shipping-in-city'); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1) : ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php
                        echo paginate_links(array(
                            'base'      => add_query_arg('paged', '%#%'),
                            'format'    => '',
                            'prev_text' => __('&laquo;'),
                            'next_text' => __('&raquo;'),
                            'total'     => $total_pages,
                            'current'   => $page,
                        ));
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render add shipping price page
     */
    public function render_add_shipping_price_page() {
        $shipping_price = SIC_Shipping_Price::get_instance();
        $stores = $shipping_price->get_all_stores();
        $districts = SIC_District::get_instance()->get_all();

        ?>
        <div class="wrap">
            <h1><?php _e('Add Shipping Price', 'shipping-in-city'); ?></h1>

            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="sic_save_shipping_price">
                <?php wp_nonce_field('sic_save_shipping_price', 'sic_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="store_id"><?php _e('Store', 'shipping-in-city'); ?> <span class="required">*</span></label></th>
                        <td>
                            <select name="store_id" id="store_id" required>
                                <option value=""><?php _e('-- Select Store --', 'shipping-in-city'); ?></option>
                                <?php foreach ($stores as $store) : ?>
                                    <option value="<?php echo esc_attr($store['id']); ?>"><?php echo esc_html($store['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="district_id"><?php _e('District', 'shipping-in-city'); ?> <span class="required">*</span></label></th>
                        <td>
                            <select name="district_id" id="district_id" required>
                                <option value=""><?php _e('-- Select District --', 'shipping-in-city'); ?></option>
                                <?php foreach ($districts as $district) : ?>
                                    <option value="<?php echo esc_attr($district->id); ?>"><?php echo esc_html($district->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ward_id"><?php _e('Ward', 'shipping-in-city'); ?> <span class="required">*</span></label></th>
                        <td>
                            <select name="ward_id" id="ward_id" required>
                                <option value=""><?php _e('-- Select Ward --', 'shipping-in-city'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="price"><?php _e('Price', 'shipping-in-city'); ?> <span class="required">*</span></label></th>
                        <td>
                            <input type="number" name="price" id="price" step="0.01" min="0" required>
                            <p class="description"><?php _e('Enter shipping price for this location', 'shipping-in-city'); ?></p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Add Shipping Price', 'shipping-in-city'); ?>">
                    <a href="<?php echo admin_url('admin.php?page=sic-shipping-prices'); ?>" class="button"><?php _e('Cancel', 'shipping-in-city'); ?></a>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Render edit shipping price page
     */
    public function render_edit_shipping_price_page() {
        $id = isset($_GET['id']) ? absint($_GET['id']) : 0;

        if (!$id) {
            wp_die(__('Invalid shipping price ID', 'shipping-in-city'));
        }

        $shipping_price_obj = SIC_Shipping_Price::get_instance();
        $shipping_price = $shipping_price_obj->get_shipping_price($id);

        if (!$shipping_price) {
            wp_die(__('Shipping price not found', 'shipping-in-city'));
        }

        $stores = $shipping_price_obj->get_all_stores();
        $districts = SIC_District::get_instance()->get_all();
        $wards = SIC_Ward::get_instance()->get_all($shipping_price['district_id']);

        ?>
        <div class="wrap">
            <h1><?php _e('Edit Shipping Price', 'shipping-in-city'); ?></h1>

            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="sic_save_shipping_price">
                <input type="hidden" name="shipping_price_id" value="<?php echo esc_attr($id); ?>">
                <?php wp_nonce_field('sic_save_shipping_price', 'sic_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="store_id"><?php _e('Store', 'shipping-in-city'); ?> <span class="required">*</span></label></th>
                        <td>
                            <select name="store_id" id="store_id" required>
                                <option value=""><?php _e('-- Select Store --', 'shipping-in-city'); ?></option>
                                <?php foreach ($stores as $store) : ?>
                                    <option value="<?php echo esc_attr($store['id']); ?>" <?php selected($shipping_price['store_id'], $store['id']); ?>>
                                        <?php echo esc_html($store['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="district_id"><?php _e('District', 'shipping-in-city'); ?> <span class="required">*</span></label></th>
                        <td>
                            <select name="district_id" id="district_id" required>
                                <option value=""><?php _e('-- Select District --', 'shipping-in-city'); ?></option>
                                <?php foreach ($districts as $district) : ?>
                                    <option value="<?php echo esc_attr($district->id); ?>" <?php selected($shipping_price['district_id'], $district->id); ?>>
                                        <?php echo esc_html($district->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ward_id"><?php _e('Ward', 'shipping-in-city'); ?> <span class="required">*</span></label></th>
                        <td>
                            <select name="ward_id" id="ward_id" required>
                                <option value=""><?php _e('-- Select Ward --', 'shipping-in-city'); ?></option>
                                <?php foreach ($wards as $ward) : ?>
                                    <option value="<?php echo esc_attr($ward->id); ?>" <?php selected($shipping_price['ward_id'], $ward->id); ?>>
                                        <?php echo esc_html($ward->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="price"><?php _e('Price', 'shipping-in-city'); ?> <span class="required">*</span></label></th>
                        <td>
                            <input type="number" name="price" id="price" step="0.01" min="0" value="<?php echo esc_attr($shipping_price['price']); ?>" required>
                            <p class="description"><?php _e('Enter shipping price for this location', 'shipping-in-city'); ?></p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Update Shipping Price', 'shipping-in-city'); ?>">
                    <a href="<?php echo admin_url('admin.php?page=sic-shipping-prices'); ?>" class="button"><?php _e('Cancel', 'shipping-in-city'); ?></a>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Handle save district
     */
    public function handle_save_district() {
        // Check nonce
        if (!isset($_POST['sic_nonce']) || !wp_verify_nonce($_POST['sic_nonce'], 'sic_save_district')) {
            wp_die(__('Security check failed', 'shipping-in-city'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied', 'shipping-in-city'));
        }

        // Validate data
        $data = array(
            'name'        => isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '',
            'description' => isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '',
        );

        if (empty($data['name'])) {
            wp_die(__('District name is required', 'shipping-in-city'));
        }

        $district_obj = SIC_District::get_instance();
        $id = isset($_POST['district_id']) ? absint($_POST['district_id']) : 0;

        if ($id) {
            // Update
            $result = $district_obj->update($id, $data);
            $message = $result ? 'updated' : 'error';
        } else {
            // Add new
            $result = $district_obj->add($data);
            $message = $result ? 'added' : 'error';
        }

        wp_redirect(admin_url('admin.php?page=sic-districts&message=' . $message));
        exit;
    }

    /**
     * Handle save ward
     */
    public function handle_save_ward() {
        // Check nonce
        if (!isset($_POST['sic_nonce']) || !wp_verify_nonce($_POST['sic_nonce'], 'sic_save_ward')) {
            wp_die(__('Security check failed', 'shipping-in-city'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied', 'shipping-in-city'));
        }

        // Validate data
        $data = array(
            'name'        => isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '',
            'district_id' => isset($_POST['district_id']) ? absint($_POST['district_id']) : 0,
            'description' => isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '',
        );

        if (empty($data['name']) || !$data['district_id']) {
            wp_die(__('Ward name and district are required', 'shipping-in-city'));
        }

        $ward_obj = SIC_Ward::get_instance();
        $id = isset($_POST['ward_id']) ? absint($_POST['ward_id']) : 0;

        if ($id) {
            // Update
            $result = $ward_obj->update($id, $data);
            $message = $result ? 'updated' : 'error';
        } else {
            // Add new
            $result = $ward_obj->add($data);
            $message = $result ? 'added' : 'error';
        }

        wp_redirect(admin_url('admin.php?page=sic-wards&message=' . $message));
        exit;
    }

    /**
     * Handle save shipping price
     */
    public function handle_save_shipping_price() {
        // Check nonce
        if (!isset($_POST['sic_nonce']) || !wp_verify_nonce($_POST['sic_nonce'], 'sic_save_shipping_price')) {
            wp_die(__('Security check failed', 'shipping-in-city'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied', 'shipping-in-city'));
        }

        // Validate data
        $data = array(
            'store_id'    => isset($_POST['store_id']) ? absint($_POST['store_id']) : 0,
            'district_id' => isset($_POST['district_id']) ? absint($_POST['district_id']) : 0,
            'ward_id'     => isset($_POST['ward_id']) ? absint($_POST['ward_id']) : 0,
            'price'       => isset($_POST['price']) ? floatval($_POST['price']) : 0,
        );

        if (!$data['store_id'] || !$data['district_id'] || !$data['ward_id'] || $data['price'] < 0) {
            wp_die(__('Invalid data', 'shipping-in-city'));
        }

        $shipping_price = SIC_Shipping_Price::get_instance();
        $id = isset($_POST['shipping_price_id']) ? absint($_POST['shipping_price_id']) : 0;

        if ($id) {
            // Update
            $result = $shipping_price->update_shipping_price($id, $data);
            $message = $result ? 'updated' : 'error';
        } else {
            // Add new
            $result = $shipping_price->add_shipping_price($data);
            $message = $result ? 'added' : 'error';
        }

        wp_redirect(admin_url('admin.php?page=sic-shipping-prices&message=' . $message));
        exit;
    }
}
