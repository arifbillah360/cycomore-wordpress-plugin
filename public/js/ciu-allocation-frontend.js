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
            this.initCategoryTabs();
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

            // Partner search functionality
            $(document).on('input', '#partner-search', function() {
                ciuFrontend.filterPartners($(this).val());
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
        },

        /**
         * Filter partners based on search query
         */
        filterPartners: function(searchQuery) {
            var query = searchQuery.toLowerCase().trim();
            var $partnerCards = $('.partner-card');
            var $noResults = $('.no-partners-found');
            var visibleCount = 0;

            if (query === '') {
                // Show all partners if search is empty
                $partnerCards.fadeIn(200);
                $noResults.hide();
                return;
            }

            // Filter partners
            $partnerCards.each(function() {
                var $card = $(this);
                var partnerName = $card.find('.partner-card-title').text().toLowerCase();

                if (partnerName.indexOf(query) !== -1) {
                    $card.fadeIn(200);
                    visibleCount++;
                } else {
                    $card.fadeOut(200);
                }
            });

            // Show/hide no results message
            if (visibleCount === 0) {
                $noResults.fadeIn(200);
            } else {
                $noResults.fadeOut(200);
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

        /**
         * Initialize category tabs
         */
        initCategoryTabs: function() {
            var self = this;
            
            // Tab click handler
            $(document).on('click', '.category-tab', function(e) {
                e.preventDefault();
                
                var $tab = $(this);
                var categorySlug = $tab.data('category');
                
                // Don't do anything if already active
                if ($tab.hasClass('active')) {
                    return;
                }
                
                // Remove active class from all tabs
                $('.category-tab').removeClass('active').attr('aria-selected', 'false');
                
                // Add active class to clicked tab
                $tab.addClass('active').attr('aria-selected', 'true');
                
                // Hide all panels
                $('.category-panel').removeClass('active').hide();
                
                // Show selected panel with animation
                $('#panel-' + categorySlug).addClass('active').fadeIn(300);
            });
            
            // Keyboard navigation for tabs
            $(document).on('keydown', '.category-tab', function(e) {
                var $tabs = $('.category-tab');
                var currentIndex = $tabs.index(this);
                var nextIndex;
                
                // Arrow left/up - previous tab
                if (e.keyCode === 37 || e.keyCode === 38) {
                    e.preventDefault();
                    nextIndex = currentIndex > 0 ? currentIndex - 1 : $tabs.length - 1;
                    $tabs.eq(nextIndex).focus().click();
                }
                
                // Arrow right/down - next tab
                if (e.keyCode === 39 || e.keyCode === 40) {
                    e.preventDefault();
                    nextIndex = currentIndex < $tabs.length - 1 ? currentIndex + 1 : 0;
                    $tabs.eq(nextIndex).focus().click();
                }
                
                // Home - first tab
                if (e.keyCode === 36) {
                    e.preventDefault();
                    $tabs.first().focus().click();
                }
                
                // End - last tab
                if (e.keyCode === 35) {
                    e.preventDefault();
                    $tabs.last().focus().click();
                }
            });
        },
