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
 * Shortcode to display reviews in a slider
 * Usage: [customer_reviews limit="10" status="approved" columns="4"]
 */
function crvp_customer_reviews_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => -1,
        'status' => 'approved',
        'columns' => 4
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

    // Generate unique ID for this slider instance
    $slider_id = 'crp-slider-' . uniqid();

    ob_start();
    ?>
    <div class="crp-reviews-container" id="<?php echo $slider_id; ?>">
        <style>
            .crp-reviews-container {
                font-family: "Bricolage Grotesque", sans-serif;
                max-width: 100%;
                margin: 0 auto;
                position: relative;
                padding: 0 50px;
            }

            .crp-slider-wrapper {
                overflow: hidden;
                position: relative;
            }

            .crp-slider-track {
                display: flex;
                gap: 20px;
                transition: transform 0.3s ease-in-out;
            }

            .crp-review-item {
                background: #fff;
                padding: 25px;
                border: 1px solid #e0e0e0;
                border-radius: 10px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                flex: 0 0 calc((100% - 60px) / 4);
                min-height: 400px;
                display: flex;
                flex-direction: column;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .crp-review-item:hover {
                transform: translateY(-5px);
                box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            }

            .crp-review-header {
                display: flex;
                align-items: center;
                gap: 12px;
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
                min-width: 0;
            }

            .crp-reviewer-name {
                font-size: 16px;
                font-weight: 600;
                color: #000;
                margin: 0 0 4px 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .crp-reviewer-location {
                font-size: 13px;
                color: #636363;
                margin: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .crp-reviewer-contributions {
                font-size: 12px;
                color: #636363;
                margin: 2px 0 0 0;
            }

            .crp-rating-stars {
                display: flex;
                gap: 4px;
                margin-bottom: 12px;
            }

            .crp-star {
                width: 20px;
                height: 20px;
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
                font-size: 18px;
                font-weight: 700;
                color: #000;
                margin: 0 0 10px 0;
                line-height: 1.3;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
                min-height: 48px;
            }

            .crp-review-meta {
                font-size: 13px;
                color: #636363;
                margin-bottom: 12px;
            }

            .crp-review-content {
                font-size: 14px;
                color: #000;
                line-height: 1.6;
                margin-bottom: 15px;
                flex: 1;
                display: -webkit-box;
                -webkit-line-clamp: 5;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .crp-review-footer {
                font-size: 12px;
                color: #8a8a8a;
                line-height: 1.4;
                margin-top: auto;
                padding-top: 15px;
                border-top: 1px solid #f0f0f0;
            }

            /* Slider Navigation */
            .crp-slider-nav {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                z-index: 10;
                transition: all 0.3s ease;
                color: #CD0000;
                font-size: 20px;
                font-weight: bold;
            }

            .crp-slider-nav:hover {
                background: #CD0000;
                color: #fff;
                transform: translateY(-50%) scale(1.1);
            }

            /* .crp-slider-nav.disabled {
                opacity: 0.3;
                cursor: not-allowed;
                pointer-events: none;
            } */

            .crp-slider-nav.disabled {
                display: none;
            }

            .crp-slider-prev {
                left: 0;
            }

            .crp-slider-next {
                right: 0;
            }

            /* Slider Dots */
            .crp-slider-dots {
                display: flex;
                justify-content: center;
                gap: 10px;
                margin-top: 30px;
            }

            .crp-slider-dot {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background: #ddd;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .crp-slider-dot.active {
                background: #CD0000;
                transform: scale(1.2);
            }

            .crp-slider-dot:hover {
                background: #a00000;
            }

            /* Responsive Design */
            @media (max-width: 1200px) {
                .crp-review-item {
                    flex: 0 0 calc((100% - 40px) / 3);
                }
            }

            @media (max-width: 992px) {
                .crp-review-item {
                    flex: 0 0 calc((100% - 20px) / 2);
                }
                .crp-reviews-container {
                    padding: 0 40px;
                }
            }

            @media (max-width: 768px) {
                .crp-review-item {
                    flex: 0 0 100%;
                    min-height: 350px;
                }
                .crp-reviews-container {
                    padding: 0 35px;
                }
                .crp-slider-nav {
                    width: 35px;
                    height: 35px;
                    font-size: 18px;
                }
                .crp-review-title {
                    font-size: 16px;
                }
                .crp-review-content {
                    font-size: 13px;
                }
            }

            @media (max-width: 480px) {
                .crp-reviews-container {
                    padding: 0 30px;
                }
                .crp-slider-nav {
                    width: 30px;
                    height: 30px;
                    font-size: 16px;
                }
            }
        </style>

        <!-- Slider Navigation -->
        <div class="crp-slider-prev crp-slider-nav" onclick="crvpSlideReviews('<?php echo $slider_id; ?>', -1)">
            <svg viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow"></path></svg>
        </div>
        <div class="crp-slider-next crp-slider-nav" onclick="crvpSlideReviews('<?php echo $slider_id; ?>', 1)">
            <svg viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow" transform="translate(100, 100) rotate(180) "></path></svg>
        </div>

        <!-- Slider Wrapper -->
        <div class="crp-slider-wrapper">
            <div class="crp-slider-track">
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
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Slider Dots -->
        <div class="crp-slider-dots" id="<?php echo $slider_id; ?>-dots"></div>

        <script>
        (function() {
            var sliderId = '<?php echo $slider_id; ?>';
            var container = document.getElementById(sliderId);
            var track = container.querySelector('.crp-slider-track');
            var items = track.querySelectorAll('.crp-review-item');
            var prevBtn = container.querySelector('.crp-slider-prev');
            var nextBtn = container.querySelector('.crp-slider-next');
            var dotsContainer = document.getElementById(sliderId + '-dots');

            var currentIndex = 0;
            var itemsPerSlide = 4;
            var totalSlides = Math.ceil(items.length / itemsPerSlide);

            // Adjust items per slide based on screen width
            function updateItemsPerSlide() {
                var width = window.innerWidth;
                if (width <= 768) {
                    itemsPerSlide = 1;
                } else if (width <= 992) {
                    itemsPerSlide = 2;
                } else if (width <= 1200) {
                    itemsPerSlide = 3;
                } else {
                    itemsPerSlide = 4;
                }
                totalSlides = Math.ceil(items.length / itemsPerSlide);
                currentIndex = Math.min(currentIndex, totalSlides - 1);
                updateSlider();
                createDots();
            }

            // Create navigation dots
            function createDots() {
                dotsContainer.innerHTML = '';
                for (var i = 0; i < totalSlides; i++) {
                    var dot = document.createElement('div');
                    dot.className = 'crp-slider-dot' + (i === currentIndex ? ' active' : '');
                    dot.setAttribute('data-index', i);
                    dot.onclick = function() {
                        currentIndex = parseInt(this.getAttribute('data-index'));
                        updateSlider();
                    };
                    dotsContainer.appendChild(dot);
                }
            }

            // Update slider position
            function updateSlider() {
                var slideWidth = 100 / itemsPerSlide;
                var translateX = -currentIndex * 100;
                track.style.transform = 'translateX(' + translateX + '%)';

                // Update navigation buttons
                prevBtn.classList.toggle('disabled', currentIndex === 0);
                nextBtn.classList.toggle('disabled', currentIndex >= totalSlides - 1);

                // Update dots
                var dots = dotsContainer.querySelectorAll('.crp-slider-dot');
                dots.forEach(function(dot, index) {
                    dot.classList.toggle('active', index === currentIndex);
                });
            }

            // Slide function
            window.crvpSlideReviews = function(id, direction) {
                if (id !== sliderId) return;

                currentIndex += direction;
                if (currentIndex < 0) currentIndex = 0;
                if (currentIndex >= totalSlides) currentIndex = totalSlides - 1;

                updateSlider();
            };

            // Auto-slide every 5 seconds
            var autoSlideInterval = setInterval(function() {
                if (currentIndex >= totalSlides - 1) {
                    currentIndex = 0;
                } else {
                    currentIndex++;
                }
                updateSlider();
            }, 5000);

            // Pause auto-slide on hover
            container.addEventListener('mouseenter', function() {
                clearInterval(autoSlideInterval);
            });

            container.addEventListener('mouseleave', function() {
                autoSlideInterval = setInterval(function() {
                    if (currentIndex >= totalSlides - 1) {
                        currentIndex = 0;
                    } else {
                        currentIndex++;
                    }
                    updateSlider();
                }, 5000);
            });

            // Handle window resize
            var resizeTimeout;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(updateItemsPerSlide, 250);
            });

            // Initialize
            updateItemsPerSlide();
        })();
        </script>
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
