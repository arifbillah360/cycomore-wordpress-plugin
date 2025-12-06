/**
 * CIU Allocation Frontend JavaScript
 *
 * @package Partner_CIU_Manager
 */

(function($) {
    'use strict';

    var ciuFrontend = {
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initAccessibility();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Category accordion toggle
            $(document).on('click', '.ciu-category-header, .category-toggle-btn', function(e) {
                e.preventDefault();
                var $header = $(this).closest('.ciu-category-header');
                var $card = $header.closest('.ciu-category-card');
                var $content = $card.find('.ciu-category-content');
                var $toggleBtn = $header.find('.category-toggle-btn');

                // Toggle content
                $content.slideToggle(300, function() {
                    var isExpanded = $content.is(':visible');
                    $toggleBtn.attr('aria-expanded', isExpanded);

                    // Add/remove active class
                    if (isExpanded) {
                        $card.addClass('active');
                    } else {
                        $card.removeClass('active');
                    }
                });
            });

            // Expand all button (if added in future)
            $(document).on('click', '.expand-all-categories', function(e) {
                e.preventDefault();
                ciuFrontend.expandAll();
            });

            // Collapse all button (if added in future)
            $(document).on('click', '.collapse-all-categories', function(e) {
                e.preventDefault();
                ciuFrontend.collapseAll();
            });
        },

        /**
         * Initialize accessibility features
         */
        initAccessibility: function() {
            // Add aria labels
            $('.category-toggle-btn').each(function() {
                var $btn = $(this);
                var categoryName = $btn.closest('.ciu-category-header').find('.category-title').text();
                $btn.attr('aria-label', 'Toggle ' + categoryName + ' category');
            });

            // Keyboard navigation
            $('.ciu-category-header').on('keypress', function(e) {
                if (e.which === 13 || e.which === 32) { // Enter or Space
                    e.preventDefault();
                    $(this).click();
                }
            });

            // Add tabindex for keyboard navigation
            $('.ciu-category-header').attr('tabindex', '0');
        },

        /**
         * Expand all categories
         */
        expandAll: function() {
            $('.ciu-category-content').slideDown(300, function() {
                $(this).closest('.ciu-category-card').addClass('active');
                $(this).siblings('.ciu-category-header').find('.category-toggle-btn').attr('aria-expanded', true);
            });
        },

        /**
         * Collapse all categories
         */
        collapseAll: function() {
            $('.ciu-category-content').slideUp(300, function() {
                $(this).closest('.ciu-category-card').removeClass('active');
                $(this).siblings('.ciu-category-header').find('.category-toggle-btn').attr('aria-expanded', false);
            });
        },

        /**
         * Smooth scroll to category
         */
        scrollToCategory: function(categorySlug) {
            var $category = $('.ciu-category-card[data-category="' + categorySlug + '"]');
            if ($category.length) {
                $('html, body').animate({
                    scrollTop: $category.offset().top - 100
                }, 500);

                // Expand if collapsed
                if (!$category.find('.ciu-category-content').is(':visible')) {
                    $category.find('.ciu-category-header').click();
                }
            }
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        ciuFrontend.init();
    });

    // Make available globally for custom scripts
    window.ciuFrontend = ciuFrontend;

})(jQuery);
