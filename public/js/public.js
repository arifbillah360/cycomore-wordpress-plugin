/**
 * Partner CIU Manager Public Scripts
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize logo upload
        initLogoUpload();

        // Initialize profile form
        initProfileForm();

        // Initialize purchase form
        initPurchaseForm();

        // Calculate CIU cost
        calculateCiuCost();
    });

    /**
     * Initialize logo upload
     */
    function initLogoUpload() {
        var mediaUploader;

        $('.partner-upload-logo-btn').on('click', function(e) {
            e.preventDefault();

            var button = $(this);

            // If the media uploader already exists, reopen it
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            // Create the media uploader
            mediaUploader = wp.media({
                title: 'Select Partner Logo',
                button: {
                    text: 'Use this image'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            // When an image is selected
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();

                // Update the display
                $('.partner-logo-display').html('<img src="' + attachment.url + '" alt="Partner Logo">');

                // Save via AJAX
                $.ajax({
                    url: partnerCiuPublic.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'partner_update_profile',
                        nonce: partnerCiuPublic.nonce,
                        partner_logo: attachment.id
                    },
                    success: function(response) {
                        if (response.success) {
                            showMessage('Logo updated successfully!', 'success');
                        } else {
                            showMessage(response.data.message, 'error');
                        }
                    },
                    error: function() {
                        showMessage('An error occurred. Please try again.', 'error');
                    }
                });
            });

            mediaUploader.open();
        });
    }

    /**
     * Initialize profile form
     */
    function initProfileForm() {
        $('#partner-profile-form').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            var button = form.find('button[type="submit"]');
            var buttonText = button.text();

            button.prop('disabled', true).text('Updating...');

            $.ajax({
                url: partnerCiuPublic.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'partner_update_profile',
                    nonce: partnerCiuPublic.nonce,
                    bank_name: $('#bank_name').val(),
                    account_number: $('#account_number').val(),
                    sort_code: $('#sort_code').val()
                },
                success: function(response) {
                    if (response.success) {
                        showMessage(response.data.message, 'success');
                    } else {
                        showMessage(response.data.message, 'error');
                    }

                    button.prop('disabled', false).text(buttonText);
                },
                error: function() {
                    showMessage('An error occurred. Please try again.', 'error');
                    button.prop('disabled', false).text(buttonText);
                }
            });
        });
    }

    /**
     * Initialize purchase form
     */
    function initPurchaseForm() {
        $('#partner-purchase-form').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            var button = form.find('button[type="submit"]');
            var buttonText = button.text();
            var quantity = $('#ciu_quantity').val();

            if (!quantity || quantity <= 0) {
                showMessage('Please enter a valid quantity.', 'error');
                return;
            }

            button.prop('disabled', true).text('Processing...');

            $.ajax({
                url: partnerCiuPublic.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'partner_purchase_cius',
                    nonce: partnerCiuPublic.nonce,
                    quantity: quantity
                },
                success: function(response) {
                    if (response.success) {
                        showMessage(response.data.message, 'success');

                        // Redirect to checkout after a short delay
                        setTimeout(function() {
                            window.location.href = response.data.checkout_url;
                        }, 1000);
                    } else {
                        showMessage(response.data.message, 'error');
                        button.prop('disabled', false).text(buttonText);
                    }
                },
                error: function() {
                    showMessage('An error occurred. Please try again.', 'error');
                    button.prop('disabled', false).text(buttonText);
                }
            });
        });
    }

    /**
     * Calculate CIU cost
     */
    function calculateCiuCost() {
        $('#ciu_quantity').on('input', function() {
            var quantity = parseInt($(this).val()) || 0;
            var ciuPrice = parseFloat(partnerCiuPublic.ciuPrice) || 0;
            var total = quantity * ciuPrice;

            $('#total-cost').text(total.toFixed(2));
        });
    }

    /**
     * Show message
     */
    function showMessage(message, type) {
        var messageClass = type === 'success' ? 'success' : 'error';
        var messageHtml = '<div class="partner-message partner-message-' + messageClass + '">' + message + '</div>';

        // Remove existing messages
        $('.partner-message').remove();

        // Add new message
        $('.partner-ciu-dashboard').prepend(messageHtml);

        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.partner-message').fadeOut(function() {
                $(this).remove();
            });
        }, 5000);

        // Scroll to message
        $('html, body').animate({
            scrollTop: $('.partner-message').offset().top - 100
        }, 500);
    }

})(jQuery);
