<?php
/**
 * Plugin Name: Custom Review Plugin
 * Plugin URI: https://terravivapizza.com
 * Description: Custom plugin to manage and display customer reviews with admin interface and shortcode support
 * Version: 1.0.0
 * Author: WooPizza Team
 * Author URI: https://terravivapizza.com
 * Text Domain: custom-review-plugin
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CRVP_VERSION', '1.0.0');
define('CRVP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CRVP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CRVP_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Create database table on plugin activation
 */
function crvp_activate_plugin() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'reviews';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        reviewer_name VARCHAR(255) NOT NULL,
        reviewer_location VARCHAR(255) DEFAULT '',
        reviewer_avatar TEXT DEFAULT '',
        rating INT(1) NOT NULL DEFAULT 5,
        review_title VARCHAR(500) NOT NULL,
        review_content TEXT NOT NULL,
        review_date DATE NOT NULL,
        reviewer_type VARCHAR(100) DEFAULT '',
        contributions INT(11) DEFAULT 0,
        source VARCHAR(50) DEFAULT 'manual',
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Set plugin version
    update_option('crvp_version', CRVP_VERSION);
}
register_activation_hook(__FILE__, 'crvp_activate_plugin');

/**
 * Add admin menu
 */
function crvp_admin_menu() {
    add_menu_page(
        __('Reviews', 'custom-review-plugin'),
        __('Reviews', 'custom-review-plugin'),
        'manage_options',
        'custom-reviews',
        'crvp_reviews_list_page',
        'dashicons-star-filled',
        26
    );

    add_submenu_page(
        'custom-reviews',
        __('All Reviews', 'custom-review-plugin'),
        __('All Reviews', 'custom-review-plugin'),
        'manage_options',
        'custom-reviews',
        'crvp_reviews_list_page'
    );

    add_submenu_page(
        'custom-reviews',
        __('Add New Review', 'custom-review-plugin'),
        __('Add New Review', 'custom-review-plugin'),
        'manage_options',
        'custom-reviews-add',
        'crvp_add_review_page'
    );
}
add_action('admin_menu', 'crvp_admin_menu');

/**
 * Include admin pages
 */
function crvp_reviews_list_page() {
    $admin_file = CRVP_PLUGIN_DIR . 'admin/admin-page.php';
    if (file_exists($admin_file)) {
        require_once $admin_file;
        if (function_exists('crvp_display_reviews_list')) {
            crvp_display_reviews_list();
        }
    } else {
        echo '<div class="error"><p>Admin file not found. Please reinstall the plugin.</p></div>';
    }
}

function crvp_add_review_page() {
    $admin_file = CRVP_PLUGIN_DIR . 'admin/admin-page.php';
    if (file_exists($admin_file)) {
        require_once $admin_file;
        if (function_exists('crvp_display_add_review_form')) {
            crvp_display_add_review_form();
        }
    } else {
        echo '<div class="error"><p>Admin file not found. Please reinstall the plugin.</p></div>';
    }
}

/**
 * Handle review submission from admin
 */
function crvp_handle_admin_review_submission() {
    if (!isset($_POST['crvp_review_nonce']) || !wp_verify_nonce($_POST['crvp_review_nonce'], 'crvp_save_review')) {
        return;
    }

    if (!current_user_can('manage_options')) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'reviews';

    $data = array(
        'reviewer_name' => sanitize_text_field($_POST['reviewer_name']),
        'reviewer_location' => sanitize_text_field($_POST['reviewer_location']),
        'reviewer_avatar' => esc_url_raw($_POST['reviewer_avatar']),
        'rating' => intval($_POST['rating']),
        'review_title' => sanitize_text_field($_POST['review_title']),
        'review_content' => sanitize_textarea_field($_POST['review_content']),
        'review_date' => sanitize_text_field($_POST['review_date']),
        'reviewer_type' => sanitize_text_field($_POST['reviewer_type']),
        'contributions' => intval($_POST['contributions']),
        'source' => sanitize_text_field($_POST['source']),
        'status' => sanitize_text_field($_POST['status'])
    );

    if (isset($_POST['review_id']) && !empty($_POST['review_id'])) {
        // Update existing review
        $review_id = intval($_POST['review_id']);
        $wpdb->update($table_name, $data, array('id' => $review_id));
        $message = 'updated';
    } else {
        // Insert new review
        $wpdb->insert($table_name, $data);
        $message = 'added';
    }

    wp_redirect(admin_url('admin.php?page=custom-reviews&message=' . $message));
    exit;
}
add_action('admin_post_crvp_save_review', 'crvp_handle_admin_review_submission');

/**
 * Handle review deletion
 */
function crvp_handle_delete_review() {
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'crvp_delete_review_' . $_GET['review_id'])) {
        wp_die('Security check failed');
    }

    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to delete reviews');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'reviews';
    $review_id = intval($_GET['review_id']);

    $wpdb->delete($table_name, array('id' => $review_id));

    wp_redirect(admin_url('admin.php?page=custom-reviews&message=deleted'));
    exit;
}
add_action('admin_post_crvp_delete_review', 'crvp_handle_delete_review');

/**
 * AJAX handler to update review status
 */
function crvp_ajax_update_status() {
    check_ajax_referer('crvp_admin_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }

    $review_id = intval($_POST['review_id']);
    $status = sanitize_text_field($_POST['status']);

    if (!in_array($status, array('pending', 'approved', 'rejected'))) {
        wp_send_json_error('Invalid status');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'reviews';

    $result = $wpdb->update(
        $table_name,
        array('status' => $status),
        array('id' => $review_id)
    );

    if ($result !== false) {
        wp_send_json_success('Status updated');
    } else {
        wp_send_json_error('Failed to update status');
    }
}
add_action('wp_ajax_crvp_update_status', 'crvp_ajax_update_status');

/**
 * Enqueue admin scripts and styles
 */
function crvp_admin_enqueue_scripts($hook) {
    if (strpos($hook, 'custom-reviews') === false) {
        return;
    }

    // Enqueue CSS if file exists
    $css_file = CRVP_PLUGIN_DIR . 'admin/admin-styles.css';
    if (file_exists($css_file)) {
        wp_enqueue_style('crp-admin-styles', CRVP_PLUGIN_URL . 'admin/admin-styles.css', array(), CRVP_VERSION);
    }

    // Enqueue JS if file exists
    $js_file = CRVP_PLUGIN_DIR . 'admin/admin-scripts.js';
    if (file_exists($js_file)) {
        wp_enqueue_script('crp-admin-scripts', CRVP_PLUGIN_URL . 'admin/admin-scripts.js', array('jquery'), CRVP_VERSION, true);

        wp_localize_script('crp-admin-scripts', 'crvpAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('crvp_admin_nonce')
        ));
    }
}
add_action('admin_enqueue_scripts', 'crvp_admin_enqueue_scripts');

/**
 * Shortcode to display reviews
 * Usage: [customer_reviews limit="10" status="approved"]
 */
function crvp_customer_reviews_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => -1,
        'status' => 'approved'
    ), $atts, 'customer_reviews');

    global $wpdb;
    $table_name = $wpdb->prefix . 'reviews';

    $limit_sql = $atts['limit'] > 0 ? 'LIMIT ' . intval($atts['limit']) : '';
    $status = sanitize_text_field($atts['status']);

    $reviews = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE status = %s ORDER BY review_date DESC, created_at DESC $limit_sql",
        $status
    ));

    if (empty($reviews)) {
        return '<p class="no-reviews">' . __('No reviews found.', 'custom-review-plugin') . '</p>';
    }

    ob_start();
    ?>
    <div class="crp-reviews-container">
        <style>
            .crp-reviews-container {
                font-family: "Bricolage Grotesque", sans-serif;
                max-width: 100%;
                margin: 0 auto;
            }

            .crp-review-item {
                background: #fff;
                padding: 20px;
                border-bottom: 1px solid #e0e0e0;
            }

            .crp-review-item:last-child {
                border-bottom: none;
            }

            .crp-review-header {
                display: flex;
                align-items: flex-start;
                gap: 15px;
                margin-bottom: 15px;
            }

            .crp-reviewer-avatar {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                object-fit: cover;
                flex-shrink: 0;
                background: #f5f5f5;
            }

            .crp-reviewer-info {
                flex: 1;
            }

            .crp-reviewer-name {
                font-size: 18px;
                font-weight: 600;
                color: #000;
                margin: 0 0 4px 0;
            }

            .crp-reviewer-location {
                font-size: 14px;
                color: #636363;
                margin: 0 0 4px 0;
            }

            .crp-reviewer-contributions {
                font-size: 13px;
                color: #636363;
            }

            .crp-rating-stars {
                display: flex;
                gap: 3px;
                margin-bottom: 10px;
            }

            .crp-star {
                width: 24px;
                height: 24px;
                border-radius: 50%;
                display: inline-block;
            }

            .crp-star.filled {
                background-color: #00A86B;
            }

            .crp-star.empty {
                background-color: #C8E6C9;
            }

            .crp-review-title {
                font-size: 20px;
                font-weight: 700;
                color: #000;
                margin: 10px 0;
                line-height: 1.3;
            }

            .crp-review-meta {
                font-size: 14px;
                color: #636363;
                margin-bottom: 12px;
            }

            .crp-review-content {
                font-size: 16px;
                color: #000;
                line-height: 1.6;
                margin-bottom: 15px;
            }

            .crp-review-footer {
                font-size: 13px;
                color: #8a8a8a;
                line-height: 1.5;
            }

            .crp-review-source {
                margin-top: 10px;
                padding-top: 10px;
                border-top: 1px solid #f0f0f0;
            }

            @media (max-width: 768px) {
                .crp-review-header {
                    flex-direction: column;
                }

                .crp-review-title {
                    font-size: 18px;
                }

                .crp-review-content {
                    font-size: 15px;
                }
            }
        </style>

        <?php foreach ($reviews as $review): ?>
            <div class="crp-review-item">
                <div class="crp-review-header">
                    <?php if (!empty($review->reviewer_avatar)): ?>
                        <img src="<?php echo esc_url($review->reviewer_avatar); ?>"
                             alt="<?php echo esc_attr($review->reviewer_name); ?>"
                             class="crp-reviewer-avatar">
                    <?php else: ?>
                        <div class="crp-reviewer-avatar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                    <?php endif; ?>

                    <div class="crp-reviewer-info">
                        <h3 class="crp-reviewer-name"><?php echo esc_html($review->reviewer_name); ?></h3>
                        <?php if (!empty($review->reviewer_location)): ?>
                            <p class="crp-reviewer-location"><?php echo esc_html($review->reviewer_location); ?></p>
                        <?php endif; ?>
                        <?php if ($review->contributions > 0): ?>
                            <p class="crp-reviewer-contributions"><?php echo esc_html($review->contributions); ?> đóng góp</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="crp-rating-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="crp-star <?php echo $i <= $review->rating ? 'filled' : 'empty'; ?>"></span>
                    <?php endfor; ?>
                </div>

                <h4 class="crp-review-title"><?php echo esc_html($review->review_title); ?></h4>

                <p class="crp-review-meta">
                    <?php
                    $date = date_create($review->review_date);
                    echo 'thg ' . $date->format('n') . ' ' . $date->format('Y');
                    ?>
                    <?php if (!empty($review->reviewer_type)): ?>
                        • <?php echo esc_html($review->reviewer_type); ?>
                    <?php endif; ?>
                </p>

                <div class="crp-review-content">
                    <?php echo wp_kses_post(nl2br($review->review_content)); ?>
                </div>

                <div class="crp-review-footer">
                    <p>Đã viết vào <?php echo date('d', strtotime($review->review_date)); ?> tháng <?php echo date('n', strtotime($review->review_date)); ?>, <?php echo date('Y', strtotime($review->review_date)); ?></p>
                    <!-- <?php if (!empty($review->source) && $review->source != 'manual'): ?>
                        <div class="crp-review-source">
                            <p>Đánh giá này là ý kiến chủ quan của thành viên <?php echo esc_html($review->source); ?> chứ không phải của Terravivapizza thực hiện kiểm tra đánh giá.</p>
                        </div>
                    <?php endif; ?> -->
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('customer_reviews', 'crvp_customer_reviews_shortcode');

/**
 * Plugin initialization
 */
function crvp_init() {
    load_plugin_textdomain('custom-review-plugin', false, dirname(CRVP_PLUGIN_BASENAME) . '/languages');
}
add_action('plugins_loaded', 'crvp_init');
