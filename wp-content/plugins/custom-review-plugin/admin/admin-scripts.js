/**
 * Admin scripts for Custom Review Plugin
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // Toggle status dropdown on click
        $('.review-status').on('click', function() {
            $(this).hide();
            $(this).next('.status-changer').show().focus();
        });

        // Handle status change via AJAX
        $('.status-changer').on('change', function() {
            var reviewId = $(this).data('review-id');
            var newStatus = $(this).val();
            var $select = $(this);
            var $statusSpan = $select.prev('.review-status');

            // Show loading state
            $select.prop('disabled', true);

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
                        showAdminNotice('Status updated successfully!', 'success');
                    } else {
                        showAdminNotice('Failed to update status: ' + response.data, 'error');
                        $select.hide();
                        $statusSpan.show();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    showAdminNotice('Failed to update status. Please try again.', 'error');
                    $select.hide();
                    $statusSpan.show();
                },
                complete: function() {
                    $select.prop('disabled', false);
                }
            });
        });

        // Hide status dropdown if clicked outside
        $('.status-changer').on('blur', function() {
            var $select = $(this);
            var $statusSpan = $select.prev('.review-status');
            setTimeout(function() {
                $select.hide();
                $statusSpan.show();
            }, 200);
        });

        // Select all checkbox functionality
        $('#cb-select-all').on('click', function() {
            $('input[name="review_ids[]"]').prop('checked', this.checked);
        });

        // Individual checkbox affects select all
        $('input[name="review_ids[]"]').on('change', function() {
            var allChecked = $('input[name="review_ids[]"]').length === $('input[name="review_ids[]"]:checked').length;
            $('#cb-select-all').prop('checked', allChecked);
        });

        // Confirm bulk delete action
        $('#reviews-filter').on('submit', function(e) {
            var action = $('#bulk-action-selector-top').val();
            if (action === 'bulk_delete') {
                var checkedBoxes = $('input[name="review_ids[]"]:checked').length;
                if (checkedBoxes > 0) {
                    if (!confirm('Are you sure you want to delete ' + checkedBoxes + ' review(s)? This action cannot be undone.')) {
                        e.preventDefault();
                        return false;
                    }
                } else {
                    alert('Please select at least one review.');
                    e.preventDefault();
                    return false;
                }
            }
        });

        // Form validation for add/edit review
        $('.crp-review-form').on('submit', function(e) {
            var reviewerName = $('#reviewer_name').val().trim();
            var reviewTitle = $('#review_title').val().trim();
            var reviewContent = $('#review_content').val().trim();
            var reviewDate = $('#review_date').val();

            if (!reviewerName || !reviewTitle || !reviewContent || !reviewDate) {
                e.preventDefault();
                showAdminNotice('Please fill in all required fields.', 'error');
                return false;
            }

            // Validate review content length
            if (reviewContent.length < 10) {
                e.preventDefault();
                showAdminNotice('Review content must be at least 10 characters long.', 'error');
                return false;
            }
        });

        // Character counter for review content
        $('#review_content').on('input', function() {
            var length = $(this).val().length;
            var $counter = $('#review_content_counter');

            if ($counter.length === 0) {
                $counter = $('<p id="review_content_counter" class="description"></p>');
                $(this).after($counter);
            }

            $counter.text(length + ' characters');

            if (length < 10) {
                $counter.css('color', '#dc3232');
            } else {
                $counter.css('color', '#646970');
            }
        });

        // Preview avatar on URL change
        $('#reviewer_avatar').on('blur', function() {
            var avatarUrl = $(this).val().trim();
            var $preview = $('#avatar_preview');

            if (avatarUrl) {
                if ($preview.length === 0) {
                    $preview = $('<div id="avatar_preview" style="margin-top: 10px;"></div>');
                    $(this).parent().append($preview);
                }
                $preview.html('<img src="' + avatarUrl + '" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;" onerror="this.style.display=\'none\'" />');
            } else {
                $preview.remove();
            }
        });

        // Trigger avatar preview on page load if editing
        if ($('#reviewer_avatar').val()) {
            $('#reviewer_avatar').trigger('blur');
        }

        // Auto-dismiss notices after 5 seconds
        setTimeout(function() {
            $('.notice.is-dismissible').fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);

    });

    /**
     * Show admin notice
     */
    function showAdminNotice(message, type) {
        type = type || 'info';
        var noticeClass = 'notice notice-' + type + ' is-dismissible';
        var $notice = $('<div class="' + noticeClass + '"><p>' + message + '</p></div>');

        $('.wp-header-end').after($notice);

        // Auto-dismiss after 3 seconds
        setTimeout(function() {
            $notice.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }

})(jQuery);
