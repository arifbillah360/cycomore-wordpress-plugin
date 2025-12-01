/**
 * Partner CIU Manager Admin Scripts
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Logo upload
        initLogoUpload();

        // Collection repeater
        initCollectionRepeater();

        // CIU Allocation
        initCiuAllocation();

        // Partner Sorting
        initPartnerSorting();
    });

    /**
     * Initialize logo upload
     */
    function initLogoUpload() {
        var mediaUploader;

        $(document).on('click', '.partner-logo-upload-btn', function(e) {
            e.preventDefault();

            var button = $(this);
            var previewContainer = button.closest('.partner-logo-upload').find('.partner-logo-preview');
            var inputField = button.closest('.partner-logo-upload').find('input[type="hidden"]');

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
                inputField.val(attachment.id);
                previewContainer.html('<img src="' + attachment.url + '" style="max-width: 200px; height: auto;">');

                // Show remove button if not already visible
                if (!button.siblings('.partner-logo-remove-btn').length) {
                    button.after('<button type="button" class="button partner-logo-remove-btn">Remove Logo</button>');
                }
            });

            mediaUploader.open();
        });

        // Remove logo
        $(document).on('click', '.partner-logo-remove-btn', function(e) {
            e.preventDefault();

            var button = $(this);
            var container = button.closest('.partner-logo-upload');
            var previewContainer = container.find('.partner-logo-preview');
            var inputField = container.find('input[type="hidden"]');

            inputField.val('');
            previewContainer.html('<p>No logo uploaded</p>');
            button.remove();
        });
    }

    /**
     * Initialize collection repeater
     */
    function initCollectionRepeater() {
        var collectionIndex = $('.collection-item').length;

        // Add collection
        $(document).on('click', '.add-collection-item', function(e) {
            e.preventDefault();

            var html = '<tr class="collection-item">' +
                '<td><input type="text" name="collections[' + collectionIndex + '][name]" value="" class="regular-text"></td>' +
                '<td><input type="number" name="collections[' + collectionIndex + '][count]" value="0" class="small-text" min="0"></td>' +
                '<td><button type="button" class="button remove-collection-item">Remove</button></td>' +
                '</tr>';

            $('.collection-items').append(html);
            collectionIndex++;
        });

        // Remove collection
        $(document).on('click', '.remove-collection-item', function(e) {
            e.preventDefault();
            $(this).closest('.collection-item').remove();
        });
    }

    /**
     * Initialize CIU allocation
     */
    function initCiuAllocation() {
        // Open allocation modal
        $(document).on('click', '.allocate-cius-btn', function(e) {
            e.preventDefault();

            var partnerId = $(this).data('partner-id');
            var partnerName = $(this).data('partner-name');

            $('#allocation-partner-id').val(partnerId);
            $('#allocation-partner-name').text(partnerName);
            $('#allocation-modal').fadeIn();
        });

        // Close modal
        $(document).on('click', '.partner-modal-close, .cancel-allocation', function() {
            $('#allocation-modal').fadeOut();
        });

        // Handle individual allocation
        $('#allocation-form').on('submit', function(e) {
            e.preventDefault();

            var partnerId = $('#allocation-partner-id').val();
            var fromStatus = $('#allocation-from-status').val();
            var toStatus = $('#allocation-to-status').val();
            var quantity = $('#allocation-quantity').val();

            if (fromStatus === toStatus) {
                alert('From and To status cannot be the same.');
                return;
            }

            $.ajax({
                url: partnerCiuAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'partner_allocate_cius',
                    nonce: partnerCiuAdmin.nonce,
                    partner_id: partnerId,
                    from_status: fromStatus,
                    to_status: toStatus,
                    quantity: quantity
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        $('#allocation-modal').fadeOut();
                        location.reload();
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        });

        // Handle bulk allocation
        $('#bulk-allocation-form').on('submit', function(e) {
            e.preventDefault();

            var fromStatus = $('#bulk-from-status').val();
            var toStatus = $('#bulk-to-status').val();

            if (fromStatus === toStatus) {
                alert('From and To status cannot be the same.');
                return;
            }

            if (!confirm('Are you sure you want to bulk allocate CIUs for all partners?')) {
                return;
            }

            $.ajax({
                url: partnerCiuAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'partner_bulk_allocate_cius',
                    nonce: partnerCiuAdmin.nonce,
                    from_status: fromStatus,
                    to_status: toStatus
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        });
    }

    /**
     * Initialize partner sorting
     */
    function initPartnerSorting() {
        if ($('#partner-sortable-list').length) {
            $('#partner-sortable-list').sortable({
                handle: '.partner-drag-handle',
                placeholder: 'partner-sortable-placeholder',
                opacity: 0.8,
                tolerance: 'pointer',
                update: function(event, ui) {
                    // Optional: Add visual feedback
                }
            });

            // Save order
            $('#save-partner-order').on('click', function(e) {
                e.preventDefault();

                var button = $(this);
                var order = [];

                $('#partner-sortable-list .partner-sortable-item').each(function() {
                    order.push($(this).data('partner-id'));
                });

                button.prop('disabled', true).text(partnerCiuAdmin.strings.savingOrder);

                $.ajax({
                    url: partnerCiuAdmin.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'partner_save_sort_order',
                        nonce: partnerCiuAdmin.nonce,
                        order: order
                    },
                    success: function(response) {
                        if (response.success) {
                            $('.sorting-notice').addClass('success').removeClass('error').find('p').text(partnerCiuAdmin.strings.orderSaved).end().fadeIn();
                        } else {
                            $('.sorting-notice').addClass('error').removeClass('success').find('p').text(response.data.message).end().fadeIn();
                        }

                        button.prop('disabled', false).text('Save Order');

                        setTimeout(function() {
                            $('.sorting-notice').fadeOut();
                        }, 3000);
                    },
                    error: function() {
                        $('.sorting-notice').addClass('error').removeClass('success').find('p').text('An error occurred. Please try again.').end().fadeIn();
                        button.prop('disabled', false).text('Save Order');
                    }
                });
            });
        }
    }

})(jQuery);
