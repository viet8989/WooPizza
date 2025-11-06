/**
 * Admin JavaScript for Shipping in City Plugin
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        /**
         * Load wards when district is selected
         */
        $('#district_id').on('change', function() {
            var districtId = $(this).val();
            var $wardSelect = $('#ward_id');

            if (!districtId) {
                $wardSelect.html('<option value="">-- Select Ward --</option>');
                return;
            }

            // Show loading
            $wardSelect.html('<option value="">Loading...</option>').prop('disabled', true);

            // AJAX request to get wards
            $.ajax({
                url: sicAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'sic_get_wards_by_district',
                    nonce: sicAjax.nonce,
                    district_id: districtId
                },
                success: function(response) {
                    if (response.success && response.data) {
                        var options = '<option value="">-- Select Ward --</option>';

                        $.each(response.data, function(index, ward) {
                            options += '<option value="' + ward.id + '">' + ward.name + '</option>';
                        });

                        $wardSelect.html(options).prop('disabled', false);
                    } else {
                        $wardSelect.html('<option value="">No wards found</option>').prop('disabled', false);
                    }
                },
                error: function() {
                    $wardSelect.html('<option value="">Error loading wards</option>').prop('disabled', false);
                    alert('Failed to load wards. Please try again.');
                }
            });
        });

        /**
         * Delete shipping price
         */
        $('.sic-delete-price').on('click', function(e) {
            e.preventDefault();

            if (!confirm('Are you sure you want to delete this shipping price?')) {
                return;
            }

            var $link = $(this);
            var id = $link.data('id');
            var $row = $link.closest('tr');

            // Add loading state
            $link.html('<span class="sic-loading"></span>');

            // AJAX request to delete
            $.ajax({
                url: sicAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'sic_delete_shipping_price',
                    nonce: sicAjax.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        // Remove row with animation
                        $row.fadeOut(300, function() {
                            $(this).remove();

                            // Check if table is empty
                            if ($('table tbody tr').length === 0) {
                                location.reload();
                            }
                        });
                    } else {
                        alert(response.data.message || 'Failed to delete shipping price');
                        $link.html('Delete');
                    }
                },
                error: function() {
                    alert('Failed to delete shipping price. Please try again.');
                    $link.html('Delete');
                }
            });
        });

        /**
         * Delete district or ward
         */
        $('.sic-delete-item').on('click', function(e) {
            e.preventDefault();

            var type = $(this).data('type');
            var typeName = type === 'district' ? 'district' : 'ward';

            if (!confirm('Are you sure you want to delete this ' + typeName + '?')) {
                return;
            }

            var $link = $(this);
            var id = $link.data('id');
            var $row = $link.closest('tr');

            // Add loading state
            var originalHtml = $link.html();
            $link.html('<span class="sic-loading"></span>');

            // AJAX request to delete
            $.ajax({
                url: sicAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'sic_delete_' + type,
                    nonce: sicAjax.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        // Remove row with animation
                        $row.fadeOut(300, function() {
                            $(this).remove();

                            // Check if table is empty
                            var $tbody = $('table#the-list tbody, table tbody#the-list');
                            if ($tbody.find('tr').length === 0) {
                                location.reload();
                            }
                        });
                    } else {
                        alert(response.data.message || 'Failed to delete ' + typeName);
                        $link.html(originalHtml);
                    }
                },
                error: function() {
                    alert('Failed to delete ' + typeName + '. Please try again.');
                    $link.html(originalHtml);
                }
            });
        });

        /**
         * Form validation
         */
        $('form[action*="admin-post.php"]').on('submit', function() {
            var $form = $(this);
            var storeId = $form.find('#store_id').val();
            var districtId = $form.find('#district_id').val();
            var wardId = $form.find('#ward_id').val();
            var price = $form.find('#price').val();

            if (!storeId || !districtId || !wardId) {
                alert('Please fill in all required fields.');
                return false;
            }

            if (price < 0) {
                alert('Price must be a positive number.');
                return false;
            }

            return true;
        });

        /**
         * Auto-select ward if editing
         */
        if (window.location.href.indexOf('sic-edit-shipping-price') !== -1) {
            // Trigger change on page load to populate wards
            $('#district_id').trigger('change');
        }

    });

})(jQuery);
