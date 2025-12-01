/**
 * Filters and Search for Public Dashboard
 */

(function($) {
    'use strict';

    var filterTimeout;
    var currentPage = 1;

    $(document).ready(function() {
        initializeFilters();
    });

    /**
     * Initialize filters
     */
    function initializeFilters() {
        // Search filter
        $('#partner-search').on('input', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                applyFilters();
            }, 500);
        });

        // Status filter
        $('#status-filter').on('change', function() {
            applyFilters();
        });

        // Sort filter
        $('#sort-by').on('change', function() {
            applyFilters();
        });
    }

    /**
     * Apply filters
     */
    function applyFilters() {
        currentPage = 1;

        var search = $('#partner-search').val();
        var statusFilter = $('#status-filter').val();
        var sortBy = $('#sort-by').val() || 'total_cius';

        var grid = $('#partners-grid');
        grid.html('<div class="loading-spinner"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');

        $.ajax({
            url: partnerCiuPublicDashboard.ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_partners_data',
                nonce: partnerCiuPublicDashboard.nonce,
                page: currentPage,
                per_page: 12,
                search: search,
                status_filter: statusFilter,
                orderby: sortBy,
                order: 'DESC'
            },
            success: function(response) {
                grid.html('');

                if (response.success && response.data.partners.length > 0) {
                    appendPartners(response.data.partners, grid);

                    // Show/hide load more button
                    if (response.data.partners.length < 12) {
                        $('#load-more-partners').hide();
                    } else {
                        $('#load-more-partners').show();
                    }
                } else {
                    grid.html('<div class="no-results"><p>' + partnerCiuPublicDashboard.strings.noResults + '</p></div>');
                    $('#load-more-partners').hide();
                }

                // Reinitialize AOS
                if (typeof AOS !== 'undefined') {
                    AOS.refresh();
                }
            },
            error: function() {
                grid.html('<div class="error-message"><p>' + partnerCiuPublicDashboard.strings.error + '</p></div>');
            }
        });
    }

    /**
     * Append partners to grid
     */
    function appendPartners(partners, grid) {
        partners.forEach(function(partner, index) {
            var delay = index * 50;
            var logoUrl = partner.logo ? partner.logo_url : '';
            var heroClass = partner.is_hero ? 'hero-partner' : '';
            var heroBadge = partner.is_hero ? '<div class="hero-badge"><i class="fas fa-star"></i>' + (partner.hero_highlight || 'Hero Partner') + '</div>' : '';

            var html = '<div class="partner-card ' + heroClass + '" data-aos="fade-up" data-aos-delay="' + delay + '">' +
                heroBadge +
                '<div class="partner-logo">' +
                (logoUrl ? '<img src="' + logoUrl + '" alt="' + partner.name + '" loading="lazy">' : '<div class="no-logo">No Logo</div>') +
                '</div>' +
                '<div class="partner-info">' +
                '<h3 class="partner-name">' + partner.name + '</h3>' +
                '<div class="partner-stats">' +
                '<div class="partner-stat-item">' +
                '<span class="stat-label">Total CIUs</span>' +
                '<span class="stat-value">' + partner.total_cius.toLocaleString() + '</span>' +
                '</div>' +
                '<div class="partner-stat-item">' +
                '<span class="stat-label">Funds</span>' +
                '<span class="stat-value">' + partnerCiuPublicDashboard.currencySymbol + partner.total_funds.toFixed(2) + '</span>' +
                '</div>' +
                '</div>' +
                '<div class="partner-ciu-breakdown">' +
                '<div class="ciu-breakdown-item pending">' +
                '<span class="breakdown-label">Pending</span>' +
                '<span class="breakdown-value">' + partner.pending_cius + '</span>' +
                '</div>' +
                '<div class="ciu-breakdown-item active">' +
                '<span class="breakdown-label">Active</span>' +
                '<span class="breakdown-value">' + partner.active_cius + '</span>' +
                '</div>' +
                '<div class="ciu-breakdown-item verified">' +
                '<span class="breakdown-label">Verified</span>' +
                '<span class="breakdown-value">' + partner.verified_cius + '</span>' +
                '</div>' +
                '</div>' +
                (partner.last_purchase_date ? '<div class="partner-last-purchase"><i class="fas fa-clock"></i>As of ' + partner.last_purchase_date + '</div>' : '') +
                '</div>' +
                '</div>';

            grid.append(html);
        });
    }

})(jQuery);
