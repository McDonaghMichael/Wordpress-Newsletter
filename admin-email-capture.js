jQuery(document).ready(function($) {
    // Show the input boxes when "Send Email" option is selected
    $('select[name="action"]').on('change', function() {
        if ($(this).val() === 'send_email') {
            $('.email-inputs').show();
        } else {
            $('.email-inputs').hide();
        }
    });
});
