<?php
/**
 * Admin pages for Custom Review Plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display reviews list page
 */
function crvp_display_reviews_list() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'reviews';

    // Handle bulk actions
    if (isset($_POST['action']) && $_POST['action'] === 'bulk_delete' && isset($_POST['review_ids'])) {
        check_admin_referer('crvp_bulk_action');
        $ids = array_map('intval', $_POST['review_ids']);
        $ids_string = implode(',', $ids);
        $wpdb->query("DELETE FROM $table_name WHERE id IN ($ids_string)");
        echo '<div class="notice notice-success"><p>Reviews deleted successfully.</p></div>';
    }

    // Handle bulk status change
    if (isset($_POST['action2']) && in_array($_POST['action2'], ['bulk_approve', 'bulk_reject', 'bulk_pending']) && isset($_POST['review_ids'])) {
        check_admin_referer('crvp_bulk_action');
        $ids = array_map('intval', $_POST['review_ids']);
        $status_map = [
            'bulk_approve' => 'approved',
            'bulk_reject' => 'rejected',
            'bulk_pending' => 'pending'
        ];
        $status = $status_map[$_POST['action2']];
        $ids_string = implode(',', $ids);
        $wpdb->query($wpdb->prepare("UPDATE $table_name SET status = %s WHERE id IN ($ids_string)", $status));
        echo '<div class="notice notice-success"><p>Reviews status updated successfully.</p></div>';
    }

    // Get filter values
    $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';

    // Build query
    $where = '';
    if ($status_filter !== 'all') {
        $where = $wpdb->prepare(" WHERE status = %s", $status_filter);
    }

    // Get reviews
    $reviews = $wpdb->get_results("SELECT * FROM $table_name $where ORDER BY created_at DESC");

    // Get counts
    $total_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $approved_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'approved'");
    $pending_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'pending'");
    $rejected_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'rejected'");

    // Display messages
    if (isset($_GET['message'])) {
        $messages = array(
            'added' => 'Review added successfully.',
            'updated' => 'Review updated successfully.',
            'deleted' => 'Review deleted successfully.'
        );
        $message = isset($messages[$_GET['message']]) ? $messages[$_GET['message']] : '';
        if ($message) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
        }
    }
    ?>

    <div class="wrap">
        <h1 class="wp-heading-inline">Reviews</h1>
        <a href="<?php echo admin_url('admin.php?page=custom-reviews-add'); ?>" class="page-title-action">Add New</a>
        <hr class="wp-header-end">

        <ul class="subsubsub">
            <li><a href="<?php echo admin_url('admin.php?page=custom-reviews'); ?>" class="<?php echo $status_filter === 'all' ? 'current' : ''; ?>">All <span class="count">(<?php echo $total_count; ?>)</span></a> |</li>
            <li><a href="<?php echo admin_url('admin.php?page=custom-reviews&status=approved'); ?>" class="<?php echo $status_filter === 'approved' ? 'current' : ''; ?>">Approved <span class="count">(<?php echo $approved_count; ?>)</span></a> |</li>
            <li><a href="<?php echo admin_url('admin.php?page=custom-reviews&status=pending'); ?>" class="<?php echo $status_filter === 'pending' ? 'current' : ''; ?>">Pending <span class="count">(<?php echo $pending_count; ?>)</span></a> |</li>
            <li><a href="<?php echo admin_url('admin.php?page=custom-reviews&status=rejected'); ?>" class="<?php echo $status_filter === 'rejected' ? 'current' : ''; ?>">Rejected <span class="count">(<?php echo $rejected_count; ?>)</span></a></li>
        </ul>

        <form method="post" id="reviews-filter">
            <?php wp_nonce_field('crvp_bulk_action'); ?>
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <select name="action2" id="bulk-action-selector-top">
                        <option value="">Bulk Actions</option>
                        <option value="bulk_approve">Approve</option>
                        <option value="bulk_pending">Set Pending</option>
                        <option value="bulk_reject">Reject</option>
                        <option value="bulk_delete">Delete</option>
                    </select>
                    <input type="submit" id="doaction2" class="button action" value="Apply">
                </div>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <td class="manage-column column-cb check-column">
                            <input type="checkbox" id="cb-select-all">
                        </td>
                        <th scope="col" class="manage-column">Reviewer</th>
                        <th scope="col" class="manage-column">Review</th>
                        <th scope="col" class="manage-column">Rating</th>
                        <th scope="col" class="manage-column">Status</th>
                        <th scope="col" class="manage-column">Date</th>
                        <th scope="col" class="manage-column">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reviews)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px;">No reviews found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                            <tr>
                                <th scope="row" class="check-column">
                                    <input type="checkbox" name="review_ids[]" value="<?php echo $review->id; ?>">
                                </th>
                                <td>
                                    <strong><?php echo esc_html($review->reviewer_name); ?></strong><br>
                                    <small><?php echo esc_html($review->reviewer_location); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo esc_html($review->review_title); ?></strong><br>
                                    <small><?php echo esc_html(wp_trim_words($review->review_content, 15)); ?></small>
                                </td>
                                <td>
                                    <?php echo str_repeat('⭐', $review->rating); ?>
                                </td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    switch ($review->status) {
                                        case 'approved':
                                            $status_class = 'status-approved';
                                            break;
                                        case 'pending':
                                            $status_class = 'status-pending';
                                            break;
                                        case 'rejected':
                                            $status_class = 'status-rejected';
                                            break;
                                    }
                                    ?>
                                    <span class="review-status <?php echo $status_class; ?>" data-review-id="<?php echo $review->id; ?>">
                                        <?php echo ucfirst($review->status); ?>
                                    </span>
                                    <select class="status-changer" data-review-id="<?php echo $review->id; ?>" style="display:none;">
                                        <option value="pending" <?php selected($review->status, 'pending'); ?>>Pending</option>
                                        <option value="approved" <?php selected($review->status, 'approved'); ?>>Approved</option>
                                        <option value="rejected" <?php selected($review->status, 'rejected'); ?>>Rejected</option>
                                    </select>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($review->review_date)); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=custom-reviews-add&review_id=' . $review->id); ?>" class="button button-small">Edit</a>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=crvp_delete_review&review_id=' . $review->id), 'crvp_delete_review_' . $review->id); ?>" class="button button-small" onclick="return confirm('Are you sure you want to delete this review?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
    </div>

    <style>
        .status-approved {
            color: #46b450;
            font-weight: 600;
        }
        .status-pending {
            color: #ffb900;
            font-weight: 600;
        }
        .status-rejected {
            color: #dc3232;
            font-weight: 600;
        }
        .review-status {
            cursor: pointer;
            padding: 2px 8px;
            border-radius: 3px;
            display: inline-block;
        }
        .review-status:hover {
            opacity: 0.8;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Toggle status dropdown on click
        $('.review-status').on('click', function() {
            $(this).hide();
            $(this).next('.status-changer').show();
        });

        // Handle status change
        $('.status-changer').on('change', function() {
            var reviewId = $(this).data('review-id');
            var newStatus = $(this).val();
            var $select = $(this);
            var $statusSpan = $select.prev('.review-status');

            $.ajax({
                url: crvpAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'crvp_update_status',
                    review_id: reviewId,
                    status: newStatus,
                    nonce: crvpAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update status display
                        $statusSpan.text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
                        $statusSpan.removeClass('status-approved status-pending status-rejected');
                        $statusSpan.addClass('status-' + newStatus);
                        $select.hide();
                        $statusSpan.show();

                        // Show success notice
                        $('<div class="notice notice-success is-dismissible"><p>Status updated successfully!</p></div>')
                            .insertAfter('.wp-header-end')
                            .delay(3000)
                            .fadeOut();
                    }
                },
                error: function() {
                    alert('Failed to update status. Please try again.');
                    $select.hide();
                    $statusSpan.show();
                }
            });
        });

        // Select all checkbox
        $('#cb-select-all').on('click', function() {
            $('input[name="review_ids[]"]').prop('checked', this.checked);
        });
    });
    </script>
    <?php
}

/**
 * Display add/edit review form
 */
function crvp_display_add_review_form() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'reviews';

    // Check if editing existing review
    $review = null;
    $is_edit = false;
    if (isset($_GET['review_id'])) {
        $review_id = intval($_GET['review_id']);
        $review = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $review_id));
        $is_edit = true;
    }

    // Set default values
    $reviewer_name = $is_edit ? $review->reviewer_name : '';
    $reviewer_location = $is_edit ? $review->reviewer_location : '';
    $reviewer_avatar = $is_edit ? $review->reviewer_avatar : '';
    $rating = $is_edit ? $review->rating : 5;
    $review_title = $is_edit ? $review->review_title : '';
    $review_content = $is_edit ? $review->review_content : '';
    $review_date = $is_edit ? $review->review_date : date('Y-m-d');
    $reviewer_type = $is_edit ? $review->reviewer_type : '';
    $contributions = $is_edit ? $review->contributions : 0;
    $source = $is_edit ? $review->source : 'manual';
    $status = $is_edit ? $review->status : 'approved';
    ?>

    <div class="wrap">
        <h1><?php echo $is_edit ? 'Edit Review' : 'Add New Review'; ?></h1>

        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="crp-review-form">
            <input type="hidden" name="action" value="crvp_save_review">
            <?php if ($is_edit): ?>
                <input type="hidden" name="review_id" value="<?php echo $review->id; ?>">
            <?php endif; ?>
            <?php wp_nonce_field('crvp_save_review', 'crvp_review_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="reviewer_name">Reviewer Name *</label></th>
                    <td>
                        <input type="text" id="reviewer_name" name="reviewer_name" class="regular-text" value="<?php echo esc_attr($reviewer_name); ?>" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="reviewer_location">Location</label></th>
                    <td>
                        <input type="text" id="reviewer_location" name="reviewer_location" class="regular-text" value="<?php echo esc_attr($reviewer_location); ?>" placeholder="e.g., Thành phố Hồ Chí Minh">
                        <p class="description">City or location of the reviewer</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="reviewer_avatar">Avatar URL</label></th>
                    <td>
                        <input type="url" id="reviewer_avatar" name="reviewer_avatar" class="regular-text" value="<?php echo esc_attr($reviewer_avatar); ?>" placeholder="https://example.com/avatar.jpg">
                        <p class="description">URL to reviewer's avatar image. Leave empty for default avatar.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="rating">Rating *</label></th>
                    <td>
                        <select id="rating" name="rating" required>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php selected($rating, $i); ?>>
                                    <?php echo str_repeat('⭐', $i) . ' (' . $i . ' star' . ($i > 1 ? 's' : '') . ')'; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="review_title">Review Title *</label></th>
                    <td>
                        <input type="text" id="review_title" name="review_title" class="large-text" value="<?php echo esc_attr($review_title); ?>" required placeholder="e.g., Amazing food!">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="review_content">Review Content *</label></th>
                    <td>
                        <textarea id="review_content" name="review_content" rows="6" class="large-text" required><?php echo esc_textarea($review_content); ?></textarea>
                        <p class="description">The full review text</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="review_date">Review Date *</label></th>
                    <td>
                        <input type="date" id="review_date" name="review_date" value="<?php echo esc_attr($review_date); ?>" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="reviewer_type">Reviewer Type</label></th>
                    <td>
                        <input type="text" id="reviewer_type" name="reviewer_type" class="regular-text" value="<?php echo esc_attr($reviewer_type); ?>" placeholder="e.g., Couples, Family, Solo">
                        <p class="description">Type of traveler (e.g., Couples, Family, Friends, Solo)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="contributions">Contributions</label></th>
                    <td>
                        <input type="number" id="contributions" name="contributions" class="small-text" value="<?php echo esc_attr($contributions); ?>" min="0">
                        <p class="description">Number of contributions by the reviewer</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="source">Source</label></th>
                    <td>
                        <select id="source" name="source">
                            <option value="manual" <?php selected($source, 'manual'); ?>>Manual Entry</option>
                            <option value="Tripadvisor" <?php selected($source, 'Tripadvisor'); ?>>Tripadvisor</option>
                            <option value="Google" <?php selected($source, 'Google'); ?>>Google</option>
                            <option value="Facebook" <?php selected($source, 'Facebook'); ?>>Facebook</option>
                            <option value="Website" <?php selected($source, 'Website'); ?>>Website</option>
                        </select>
                        <p class="description">Where this review came from</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="status">Status *</label></th>
                    <td>
                        <select id="status" name="status" required>
                            <option value="approved" <?php selected($status, 'approved'); ?>>Approved</option>
                            <option value="pending" <?php selected($status, 'pending'); ?>>Pending</option>
                            <option value="rejected" <?php selected($status, 'rejected'); ?>>Rejected</option>
                        </select>
                        <p class="description">Only approved reviews will be displayed on the frontend</p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo $is_edit ? 'Update Review' : 'Add Review'; ?>">
                <a href="<?php echo admin_url('admin.php?page=custom-reviews'); ?>" class="button">Cancel</a>
            </p>
        </form>
    </div>

    <style>
        .crp-review-form .form-table th {
            width: 200px;
        }
        .crp-review-form .regular-text {
            width: 100%;
            max-width: 500px;
        }
        .crp-review-form .large-text {
            width: 100%;
            max-width: 700px;
        }
    </style>
    <?php
}
