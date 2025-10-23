/**
 * Reservation Form Debug Helper
 *
 * Copy and paste this entire code into your browser console (F12)
 * when you're on the reservation page to debug form issues.
 */

console.log('🔧 Reservation Debug Helper Loaded');
console.log('════════════════════════════════════════');

// Check if jQuery is available
if (typeof jQuery === 'undefined') {
    console.error('❌ jQuery is not loaded!');
} else {
    console.log('✅ jQuery is available');

    jQuery(document).ready(function($) {
        console.log('\n📋 FORM STATUS CHECK:');
        console.log('════════════════════════════════════════');

        // Check if form exists
        const formExists = $('#crp-reservation-form').length > 0;
        console.log('Form exists:', formExists ? '✅' : '❌');

        if (!formExists) {
            console.error('❌ Reservation form not found! Make sure:');
            console.error('  1. Plugin is activated');
            console.error('  2. Shortcode [reservation_form] is on the page');
            return;
        }

        // Check form fields
        console.log('\n📝 FORM FIELDS:');
        console.log('════════════════════════════════════════');

        const fields = {
            'Date input': $('#reservation_date'),
            'Start time': $('#start_time'),
            'End time': $('#end_time'),
            'Party size': $('#party-size-selected'),
            'Branch radios': $('input[name="branch_id"]'),
            'Reservation type': $('input[name="reservation_type"]'),
            'Customer name': $('input[name="customer_name"]'),
            'Customer email': $('input[name="customer_email"]'),
            'Customer phone': $('input[name="customer_phone"]'),
            'Message': $('textarea[name="customer_message"]')
        };

        Object.keys(fields).forEach(function(fieldName) {
            const field = fields[fieldName];
            console.log(fieldName + ':', field.length > 0 ? '✅ Found' : '❌ Missing');
        });

        // Get current values
        console.log('\n📊 CURRENT VALUES:');
        console.log('════════════════════════════════════════');
        console.log('Date:', $('#reservation_date').val() || '(empty)');
        console.log('Start time:', $('#start_time').val() || '(empty)');
        console.log('End time:', $('#end_time').val() || '(empty)');
        console.log('Party size:', $('#party-size-selected').val() || '(empty)');
        console.log('Selected branch:', $('input[name="branch_id"]:checked').val() || '(none selected)');
        console.log('Reservation type:', $('input[name="reservation_type"]:checked').val() || '(none selected)');
        console.log('Customer name:', $('input[name="customer_name"]').val() || '(empty)');
        console.log('Customer email:', $('input[name="customer_email"]').val() || '(empty)');
        console.log('Customer phone:', $('input[name="customer_phone"]').val() || '(empty)');

        // Monitor form changes
        console.log('\n👀 MONITORING FORM CHANGES...');
        console.log('════════════════════════════════════════');
        console.log('(Make changes to the form and watch this console)\n');

        $('#start_time').on('change', function() {
            console.log('⏰ Start time changed:', $(this).val());
            console.log('   End time auto-set to:', $('#end_time').val());
        });

        $('.party-btn').on('click', function() {
            console.log('👥 Party size selected:', $(this).data('value'));
        });

        $('input[name="branch_id"]').on('change', function() {
            console.log('🏢 Branch selected:', $(this).next('strong').text());
        });

        $('input[name="reservation_type"]').on('change', function() {
            console.log('📋 Reservation type:', $(this).val());
        });

        // Monitor form submission
        $('#crp-reservation-form').on('submit', function(e) {
            console.log('\n🚀 FORM SUBMITTED');
            console.log('════════════════════════════════════════');

            const formData = $(this).serializeArray();
            console.log('Form data being sent:');
            formData.forEach(function(item) {
                console.log('  ' + item.name + ':', item.value);
            });
        });

        // Test helper functions
        window.ReservationDebug = {
            // Fill form with test data
            fillTestData: function() {
                console.log('\n🧪 Filling form with test data...');

                // Set date to tomorrow
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                const dateStr = tomorrow.toISOString().split('T')[0];
                $('#reservation_date').val(dateStr);

                // Set time
                $('#start_time').val('18:00').trigger('change');

                // Set party size to 4
                $('.party-btn[data-value="4"]').trigger('click');

                // Select first branch
                $('input[name="branch_id"]').first().prop('checked', true);

                // Select Standard Reservation
                $('input[name="reservation_type"][value="Standard Reservation"]').prop('checked', true);

                // Fill customer info
                $('input[name="customer_name"]').val('Test Customer');
                $('input[name="customer_email"]').val('test@example.com');
                $('input[name="customer_phone"]').val('0901234567');
                $('textarea[name="customer_message"]').val('This is a test reservation');

                console.log('✅ Test data filled!');
                console.log('   Review the form and click submit to test.');
            },

            // Get all form data
            getFormData: function() {
                const data = $('#crp-reservation-form').serializeArray();
                console.log('\n📋 Current form data:');
                data.forEach(function(item) {
                    console.log('  ' + item.name + ':', item.value);
                });
                return data;
            },

            // Clear form
            clearForm: function() {
                $('#crp-reservation-form')[0].reset();
                $('#end_time_display').text('');
                console.log('✅ Form cleared');
            },

            // Simulate AJAX submission
            testAjax: function() {
                console.log('\n🔌 Testing AJAX endpoint...');

                $.ajax({
                    url: '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'crp_handle_reservation',
                        customer_name: 'AJAX Test',
                        customer_email: 'ajax@test.com',
                        customer_phone: '0000000000',
                        customer_message: 'AJAX test message',
                        reservation_date: '2025-11-01',
                        start_time: '18:00',
                        end_time: '18:30',
                        party_size: '2',
                        branch_id: '1',
                        reservation_type: 'Standard Reservation'
                    },
                    success: function(response) {
                        console.log('✅ AJAX Success:', response);
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ AJAX Error:', error);
                        console.log('Status:', status);
                        console.log('Response:', xhr.responseText);
                    }
                });
            }
        };

        // Display helper functions
        console.log('\n🛠️ HELPER FUNCTIONS AVAILABLE:');
        console.log('════════════════════════════════════════');
        console.log('ReservationDebug.fillTestData()  - Fill form with test data');
        console.log('ReservationDebug.getFormData()   - Get current form data');
        console.log('ReservationDebug.clearForm()     - Clear the form');
        console.log('ReservationDebug.testAjax()      - Test AJAX submission');
        console.log('\nExample: ReservationDebug.fillTestData()');
        console.log('════════════════════════════════════════\n');
    });
}
