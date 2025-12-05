/**
 * CIU Allocation Admin JavaScript
 *
 * @package Partner_CIU_Manager
 */

(function($) {
    'use strict';

    var ciuAllocation = {
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.calculateAllTotals();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;

            // Category accordion toggle
            $(document).on('click', '.category-header', function(e) {
                e.preventDefault();
                var $section = $(this).closest('.category-section');
                var $content = $section.find('.category-content');

                if ($section.hasClass('active')) {
                    $section.removeClass('active');
                    $content.slideUp(300);
                } else {
                    $section.addClass('active');
                    $content.slideDown(300);
                }
            });

            // Add collection
            $(document).on('click', '.add-collection', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $button = $(this);
                var $container = $button.siblings('.collections-container');
                var category = $container.data('category');

                self.addCollection($container, category);
            });

            // Remove collection
            $(document).on('click', '.remove-collection', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (confirm(ciuAllocationData.strings.confirmRemove)) {
                    var $item = $(this).closest('.collection-item');
                    var $container = $item.closest('.collections-container');

                    $item.fadeOut(300, function() {
                        $(this).remove();
                        self.renumberCollections($container);
                        self.calculateCategoryTotal($container);
                        self.calculateSummary();
                    });
                }
            });

            // Update calculations on input change
            $(document).on('input change', '.ciu-amount-input, .collection-status-select', function() {
                var $container = $(this).closest('.collections-container');
                self.calculateCategoryTotal($container);
                self.calculateSummary();
            });

            // Update title display when title changes
            $(document).on('input', '.collection-title-input', function() {
                var title = $(this).val();
                $(this).closest('.collection-item').find('.collection-title-display').text(title || 'Untitled Collection');
            });
        },

        /**
         * Add new collection
         */
        addCollection: function($container, category) {
            var collectionId = 'new_' + Date.now();
            var collectionNumber = $container.find('.collection-item').length + 1;

            var html = '<div class="collection-item new-item" data-collection-id="' + collectionId + '">' +
                '<div class="collection-header">' +
                    '<span class="collection-number">' + collectionNumber + '</span>' +
                    '<span class="collection-title-display">New Collection</span>' +
                    '<button type="button" class="remove-collection button-link-delete">' +
                        ciuAllocationData.strings.remove +
                    '</button>' +
                '</div>' +

                '<input type="hidden" ' +
                       'name="ciu_allocations[' + category + '][collections][' + collectionId + '][collection_number]" ' +
                       'value="' + collectionNumber + '" ' +
                       'class="collection-number-field">' +

                '<input type="hidden" ' +
                       'name="ciu_allocations[' + category + '][collections][' + collectionId + '][date_added]" ' +
                       'value="' + this.getCurrentDate() + '">' +

                '<div class="collection-fields">' +
                    '<div class="field-row">' +
                        '<div class="field-group">' +
                            '<label>' + ciuAllocationData.strings.collectionTitle + ' *</label>' +
                            '<input type="text" ' +
                                   'name="ciu_allocations[' + category + '][collections][' + collectionId + '][title]" ' +
                                   'value="" ' +
                                   'class="widefat collection-title-input" ' +
                                   'required>' +
                        '</div>' +

                        '<div class="field-group">' +
                            '<label>' + ciuAllocationData.strings.ciuAmount + ' *</label>' +
                            '<input type="number" ' +
                                   'name="ciu_allocations[' + category + '][collections][' + collectionId + '][ciu_amount]" ' +
                                   'value="0" ' +
                                   'class="small-text ciu-amount-input" ' +
                                   'min="0" ' +
                                   'step="1" ' +
                                   'required>' +
                        '</div>' +

                        '<div class="field-group">' +
                            '<label>' + ciuAllocationData.strings.status + '</label>' +
                            '<select name="ciu_allocations[' + category + '][collections][' + collectionId + '][status]" ' +
                                    'class="collection-status-select">' +
                                '<option value="pending">' + ciuAllocationData.strings.pending + '</option>' +
                                '<option value="active">' + ciuAllocationData.strings.active + '</option>' +
                                '<option value="verified">' + ciuAllocationData.strings.verified + '</option>' +
                            '</select>' +
                        '</div>' +
                    '</div>' +

                    '<div class="field-row">' +
                        '<div class="field-group">' +
                            '<label>' + ciuAllocationData.strings.description + '</label>' +
                            '<textarea name="ciu_allocations[' + category + '][collections][' + collectionId + '][description]" ' +
                                      'class="widefat" ' +
                                      'rows="3"></textarea>' +
                        '</div>' +
                    '</div>' +

                    '<div class="field-row">' +
                        '<div class="field-group">' +
                            '<label>' + (ciuAllocationData.strings.date || 'Date') + '</label>' +
                            '<input type="date" ' +
                                   'name="ciu_allocations[' + category + '][collections][' + collectionId + '][collection_date]" ' +
                                   'value="" ' +
                                   'class="widefat collection-date-input">' +
                            '<small class="field-hint">' + (ciuAllocationData.strings.dateHint || 'Project start date or milestone date') + '</small>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';

            $container.append(html);

            // Scroll to new item
            var $newItem = $container.find('.collection-item').last();
            $('html, body').animate({
                scrollTop: $newItem.offset().top - 100
            }, 500);

            // Focus on title input
            $newItem.find('.collection-title-input').focus();
        },

        /**
         * Renumber collections
         */
        renumberCollections: function($container) {
            $container.find('.collection-item').each(function(index) {
                var number = index + 1;
                $(this).find('.collection-number').text(number);
                $(this).find('.collection-number-field').val(number);
            });
        },

        /**
         * Calculate category total
         */
        calculateCategoryTotal: function($container) {
            var total = 0;

            $container.find('.collection-item').each(function() {
                var amount = parseInt($(this).find('.ciu-amount-input').val()) || 0;
                total += amount;
            });

            var $section = $container.closest('.category-section');
            $section.find('.category-total').text(total.toLocaleString() + ' CIUs');
        },

        /**
         * Calculate summary
         */
        calculateSummary: function() {
            var totalCius = 0;
            var totalPending = 0;
            var totalActive = 0;
            var totalVerified = 0;

            $('.collection-item').each(function() {
                var amount = parseInt($(this).find('.ciu-amount-input').val()) || 0;
                var status = $(this).find('.collection-status-select').val();

                totalCius += amount;

                switch (status) {
                    case 'pending':
                        totalPending += amount;
                        break;
                    case 'active':
                        totalActive += amount;
                        break;
                    case 'verified':
                        totalVerified += amount;
                        break;
                }
            });

            var totalFunds = totalCius * ciuAllocationData.ciuPrice;

            // Update summary display
            $('#total-cius').text(totalCius.toLocaleString());
            $('#total-pending').text(totalPending.toLocaleString());
            $('#total-active').text(totalActive.toLocaleString());
            $('#total-verified').text(totalVerified.toLocaleString());
            $('#total-funds').text(ciuAllocationData.currencySymbol + totalFunds.toFixed(2));

            // Add animation
            this.animateValue($('#total-cius'));
            this.animateValue($('#total-pending'));
            this.animateValue($('#total-active'));
            this.animateValue($('#total-verified'));
            this.animateValue($('#total-funds'));
        },

        /**
         * Calculate all totals on init
         */
        calculateAllTotals: function() {
            var self = this;

            $('.collections-container').each(function() {
                self.calculateCategoryTotal($(this));
            });

            this.calculateSummary();
        },

        /**
         * Animate value change
         */
        animateValue: function($element) {
            $element.addClass('updated');
            setTimeout(function() {
                $element.removeClass('updated');
            }, 300);
        },

        /**
         * Get current date
         */
        getCurrentDate: function() {
            var date = new Date();
            var year = date.getFullYear();
            var month = String(date.getMonth() + 1).padStart(2, '0');
            var day = String(date.getDate()).padStart(2, '0');
            return year + '-' + month + '-' + day;
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        ciuAllocation.init();
    });

})(jQuery);
