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
            this.initCollectionModal();
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
        },

        /**
         * Initialize category tabs (works for both designs)
         */
        initCategoryTabs: function() {
            var self = this;

            // Tab click handler - works for both minimal and corporate designs
            $(document).on('click', '.category-tab, .tab-button', function(e) {
                e.preventDefault();

                var $tab = $(this);
                var categorySlug = $tab.data('category');

                // Don't do anything if already active
                if ($tab.hasClass('active')) {
                    return;
                }

                // Determine which design we're using
                var isMinimalDesign = $tab.hasClass('tab-button');
                var tabSelector = isMinimalDesign ? '.tab-button' : '.category-tab';
                var panelSelector = isMinimalDesign ? '.tab-panel' : '.category-panel';
                var panelId = isMinimalDesign ? '#tab-panel-' + categorySlug : '#panel-' + categorySlug;

                // Remove active class from all tabs
                $(tabSelector).removeClass('active').attr('aria-selected', 'false');

                // Add active class to clicked tab
                $tab.addClass('active').attr('aria-selected', 'true');

                // Hide all panels
                $(panelSelector).removeClass('active').hide();

                // Show selected panel with animation
                $(panelId).addClass('active').fadeIn(300);
            });

            // Keyboard navigation for tabs
            $(document).on('keydown', '.category-tab, .tab-button', function(e) {
                var isMinimalDesign = $(this).hasClass('tab-button');
                var tabSelector = isMinimalDesign ? '.tab-button' : '.category-tab';
                var $tabs = $(tabSelector);
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

        /**
         * Initialize collection details modal
         */
        initCollectionModal: function() {
            var self = this;

            console.log('=== COLLECTION MODAL INITIALIZATION START ===');
            console.log('jQuery version:', $.fn.jquery);
            console.log('Current time:', new Date().toLocaleTimeString());

            // Check if modal exists in DOM
            var $modal = $('#collection-details-modal');
            console.log('Modal element exists:', $modal.length > 0);
            console.log('Modal element count:', $modal.length);

            if ($modal.length === 0) {
                console.error('❌ CRITICAL ERROR: Modal element #collection-details-modal not found in DOM!');
                console.log('Available modals:', $('.collection-modal').length);
            } else {
                console.log('✓ Modal element found successfully');
                console.log('Modal display style:', $modal.css('display'));
                console.log('Modal visibility:', $modal.css('visibility'));
                console.log('Modal z-index:', $modal.css('z-index'));
            }

            // Check if buttons exist
            var $buttons = $('.view-collection-details-btn');
            console.log('View Details buttons found:', $buttons.length);

            if ($buttons.length === 0) {
                console.warn('⚠️ WARNING: No .view-collection-details-btn buttons found in DOM (yet)');
                console.log('This is normal if collections load after page load');
            } else {
                console.log('✓ Found', $buttons.length, 'View Details buttons');

                // Log first button's data attributes for debugging
                if ($buttons.length > 0) {
                    var $firstBtn = $buttons.first();
                    console.log('First button sample data:');
                    console.log('  - Title:', $firstBtn.attr('data-collection-title'));
                    console.log('  - Number:', $firstBtn.attr('data-collection-number'));
                    console.log('  - Description length:', ($firstBtn.attr('data-description') || '').length, 'characters');
                }
            }

            // Open modal when "View Details" button is clicked
            // Using event delegation to handle dynamically loaded content
            $(document).on('click', '.view-collection-details-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                console.log('🔘 VIEW DETAILS BUTTON CLICKED!');
                console.log('Time:', new Date().toLocaleTimeString());
                console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

                var $btn = $(this);
                console.log('Button element:', $btn[0]);
                console.log('Button HTML:', $btn[0].outerHTML.substring(0, 200) + '...');

                // Get all data attributes using both methods
                var collectionData = {
                    title: $btn.attr('data-collection-title') || $btn.data('collection-title') || 'NO TITLE',
                    number: $btn.attr('data-collection-number') || $btn.data('collection-number') || 'NO NUMBER',
                    ciuAmount: $btn.attr('data-ciu-amount') || $btn.data('ciu-amount') || '0',
                    status: $btn.attr('data-status') || $btn.data('status') || 'unknown',
                    datetime: $btn.attr('data-datetime') || $btn.data('datetime') || '',
                    description: $btn.attr('data-description') || $btn.data('description') || 'NO DESCRIPTION'
                };

                console.log('Collection Data Retrieved:');
                console.log('  - Title:', collectionData.title);
                console.log('  - Number:', collectionData.number);
                console.log('  - CIU Amount:', collectionData.ciuAmount);
                console.log('  - Status:', collectionData.status);
                console.log('  - DateTime:', collectionData.datetime);
                console.log('  - Description length:', collectionData.description.length, 'characters');
                console.log('  - Description preview:', collectionData.description.substring(0, 100) + '...');

                console.log('Calling openCollectionModal()...');
                self.openCollectionModal(collectionData);
            });

            // Close modal when close button is clicked
            $(document).on('click', '.modal-close', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('✕ Close button clicked');
                self.closeCollectionModal();
            });

            // Close modal when overlay is clicked
            $(document).on('click', '.modal-overlay', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('✕ Overlay clicked');
                self.closeCollectionModal();
            });

            // Close modal with ESC key
            $(document).on('keydown', function(e) {
                if (e.keyCode === 27 && $('#collection-details-modal').is(':visible')) {
                    console.log('✕ ESC key pressed');
                    self.closeCollectionModal();
                }
            });

            console.log('✓ Event listeners attached successfully');
            console.log('=== COLLECTION MODAL INITIALIZATION COMPLETE ===');
            console.log(' ');
        },

        /**
         * Open collection details modal
         */
        openCollectionModal: function(data) {
            console.log('┌─────────────────────────────────────────┐');
            console.log('│   OPENING COLLECTION MODAL              │');
            console.log('└─────────────────────────────────────────┘');
            console.log('Received data:', data);

            // Check if modal exists
            var $modal = $('#collection-details-modal');
            console.log('Modal lookup: #collection-details-modal');
            console.log('Modal found:', $modal.length > 0);

            if ($modal.length === 0) {
                console.error('❌ FATAL ERROR: Modal element #collection-details-modal NOT FOUND!');
                console.error('Cannot open modal. Please check:');
                console.error('1. Template file includes modal HTML');
                console.error('2. Modal is not removed by JavaScript');
                console.error('3. DOM is fully loaded');

                // Try to find any modal elements
                var $anyModals = $('.collection-modal');
                console.log('Alternative search - .collection-modal:', $anyModals.length);

                alert('ERROR: Modal not found! Check browser console for details.');
                return;
            }

            console.log('✓ Modal element found, proceeding...');

            // Populate modal fields
            console.log('Populating modal fields...');

            var $title = $('#modal-collection-title');
            var $number = $('#modal-collection-number');
            var $amount = $('#modal-ciu-amount');
            var $datetime = $('#modal-datetime');
            var $description = $('#modal-description');
            var $status = $('#modal-status');

            console.log('Modal field elements found:');
            console.log('  - Title element:', $title.length > 0);
            console.log('  - Number element:', $number.length > 0);
            console.log('  - Amount element:', $amount.length > 0);
            console.log('  - DateTime element:', $datetime.length > 0);
            console.log('  - Description element:', $description.length > 0);
            console.log('  - Status element:', $status.length > 0);

            // Set title
            $title.text(data.title || 'N/A');
            console.log('Set title:', data.title);

            // Set collection number
            $number.text(data.number || 'N/A');
            console.log('Set number:', data.number);

            // Format and set CIU amount
            var ciuAmount = data.ciuAmount || 0;
            if (typeof ciuAmount === 'string') {
                ciuAmount = parseFloat(ciuAmount.replace(/[^0-9.-]+/g, '')) || 0;
            }
            var formattedAmount = Number(ciuAmount).toLocaleString();
            $amount.text(formattedAmount);
            console.log('Set CIU amount:', formattedAmount);

            // Set datetime
            $datetime.text(data.datetime || '—');
            console.log('Set datetime:', data.datetime || '—');

            // Set description (convert newlines to <br> tags)
            var descriptionHtml = data.description ? data.description.replace(/\n/g, '<br>') : 'No description available';
            $description.html(descriptionHtml);
            console.log('Set description:', descriptionHtml.length, 'characters (HTML)');

            // Set status badge
            var statusLabels = {
                'pending': 'Pending',
                'active': 'Active',
                'verified': 'Verified'
            };
            var statusLabel = statusLabels[data.status] || (data.status ? data.status.charAt(0).toUpperCase() + data.status.slice(1) : 'Unknown');
            $status
                .removeClass('status-pending status-active status-verified')
                .addClass('status-' + (data.status || 'pending'))
                .text(statusLabel);
            console.log('Set status:', statusLabel, '(class: status-' + data.status + ')');

            console.log('All fields populated successfully');

            // Show modal with animation
            console.log('Showing modal...');
            console.log('Current display:', $modal.css('display'));
            console.log('Current visibility:', $modal.css('visibility'));

            $modal.fadeIn(300, function() {
                console.log('✓ Modal fadeIn animation complete');
                console.log('New display:', $modal.css('display'));
                console.log('Is visible:', $modal.is(':visible'));
            });

            // Apply animation to container
            $('.modal-container').css({
                'animation': 'slideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
                'animation-fill-mode': 'forwards'
            });
            console.log('✓ Container animation applied');

            // Prevent body scroll
            $('body').addClass('modal-open');
            console.log('✓ Body scroll prevented (class: modal-open)');

            console.log('┌─────────────────────────────────────────┐');
            console.log('│   MODAL OPENED SUCCESSFULLY! ✓          │');
            console.log('└─────────────────────────────────────────┘');
            console.log(' ');
        },

        /**
         * Close collection details modal
         */
        closeCollectionModal: function() {
            console.log('┌─────────────────────────────────────────┐');
            console.log('│   CLOSING COLLECTION MODAL              │');
            console.log('└─────────────────────────────────────────┘');

            var $modal = $('#collection-details-modal');
            console.log('Modal found:', $modal.length > 0);
            console.log('Currently visible:', $modal.is(':visible'));

            $modal.fadeOut(300, function() {
                console.log('✓ Modal fadeOut animation complete');
                console.log('New display:', $modal.css('display'));
                console.log('Is visible:', $modal.is(':visible'));
            });

            $('body').removeClass('modal-open');
            console.log('✓ Body scroll restored (removed class: modal-open)');

            console.log('┌─────────────────────────────────────────┐');
            console.log('│   MODAL CLOSED SUCCESSFULLY! ✓          │');
            console.log('└─────────────────────────────────────────┘');
            console.log(' ');
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        console.log('═══════════════════════════════════════════════════════════');
        console.log('  CIU ALLOCATION FRONTEND JAVASCRIPT LOADED');
        console.log('═══════════════════════════════════════════════════════════');
        console.log('Document ready at:', new Date().toLocaleTimeString());
        console.log('jQuery version:', $.fn.jquery);
        console.log('Window width:', $(window).width());
        console.log('Window height:', $(window).height());
        console.log('───────────────────────────────────────────────────────────');

        console.log('Initializing ciuFrontend...');
        ciuFrontend.init();
        console.log('✓ ciuFrontend.init() completed');

        console.log('═══════════════════════════════════════════════════════════');
        console.log(' ');
    });

    // Make available globally for custom scripts
    window.ciuFrontend = ciuFrontend;
    console.log('✓ ciuFrontend object assigned to window.ciuFrontend');

})(jQuery);
