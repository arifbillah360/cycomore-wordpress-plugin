/**
 * Partner Admin JavaScript
 * Handles featured image upload in Hero Partner Settings metabox
 *
 * @package Partner_CIU_Manager
 */

(function($) {
    'use strict';

    var partnerAdmin = {
        mediaUploader: null,

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Upload logo button
            $(document).on('click', '.partner-logo-upload-btn', function(e) {
                e.preventDefault();
                partnerAdmin.openMediaUploader();
            });

            // Remove logo button
            $(document).on('click', '.partner-logo-remove-btn', function(e) {
                e.preventDefault();
                partnerAdmin.removeLogo();
            });
        },

        /**
         * Open WordPress media uploader
         */
        openMediaUploader: function() {
            // If the uploader already exists, reopen it
            if (this.mediaUploader) {
                this.mediaUploader.open();
                return;
            }

            // Create the media uploader
            this.mediaUploader = wp.media({
                title: 'Select Partner Logo',
                button: {
                    text: 'Use as Partner Logo'
                },
                library: {
                    type: 'image'
                },
                multiple: false
            });

            // When an image is selected
            this.mediaUploader.on('select', function() {
                var attachment = partnerAdmin.mediaUploader.state().get('selection').first().toJSON();
                partnerAdmin.setLogo(attachment);
            });

            // Open the uploader
            this.mediaUploader.open();
        },

        /**
         * Set the selected logo
         */
        setLogo: function(attachment) {
            var $preview = $('.partner-logo-preview');
            var $logoId = $('#partner_logo_id');
            var $uploadBtn = $('.partner-logo-upload-btn');
            var $removeBtn = $('.partner-logo-remove-btn');

            // Update hidden input with attachment ID
            $logoId.val(attachment.id);

            // Update preview
            var imgUrl = attachment.sizes && attachment.sizes.medium
                ? attachment.sizes.medium.url
                : attachment.url;

            $preview.html('<img src="' + imgUrl + '" alt="Partner Logo" style="max-width: 100%; height: auto; display: block;">');

            // Update button text
            $uploadBtn.text('Change Logo');

            // Show remove button if not already visible
            if ($removeBtn.length === 0) {
                $('.partner-logo-buttons').append(
                    '<button type="button" class="button button-link-delete partner-logo-remove-btn">Remove</button>'
                );
            } else {
                $removeBtn.show();
            }
        },

        /**
         * Remove the logo
         */
        removeLogo: function() {
            var $preview = $('.partner-logo-preview');
            var $logoId = $('#partner_logo_id');
            var $uploadBtn = $('.partner-logo-upload-btn');
            var $removeBtn = $('.partner-logo-remove-btn');

            // Clear hidden input
            $logoId.val('');

            // Reset preview to placeholder
            $preview.html(
                '<div class="partner-logo-placeholder">' +
                    '<span class="dashicons dashicons-format-image"></span>' +
                    '<p>No logo uploaded</p>' +
                '</div>'
            );

            // Update button text
            $uploadBtn.text('Upload Logo');

            // Hide remove button
            $removeBtn.hide();
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        partnerAdmin.init();
    });

})(jQuery);
