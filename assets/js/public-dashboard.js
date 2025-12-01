/**
 * Public CIU Dashboard Scripts
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize AOS (Animate On Scroll)
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 800,
                once: true,
                offset: 100
            });
        }

        // Initialize counter animations
        initCounters();

        // Initialize load more functionality
        initLoadMore();

        // Initialize collection details
        initCollectionDetails();
    });

    /**
     * Initialize counter animations
     */
    function initCounters() {
        $('.hero-stat-item').each(function() {
            var $stat = $(this).find('[data-count-to]');
            if ($stat.length) {
                var countTo = parseInt($stat.attr('data-count-to'));
                animateCounter($stat, countTo);
            }
        });
    }

    /**
     * Animate counter
     */
    function animateCounter($element, countTo) {
        $({countNum: 0}).animate({countNum: countTo}, {
            duration: 2000,
            easing: 'swing',
            step: function() {
                $element.text(Math.floor(this.countNum).toLocaleString());
            },
            complete: function() {
                $element.text(this.countNum.toLocaleString());
            }
        });
    }

    /**
     * Initialize load more partners
     */
    function initLoadMore() {
        var page = 2;
        var loading = false;

        $('#load-more-partners').on('click', function() {
            if (loading) {
                return;
            }

            loading = true;
            var button = $(this);
            var originalText = button.text();
            button.text(partnerCiuPublicDashboard.strings.loading).prop('disabled', true);

            $.ajax({
                url: partnerCiuPublicDashboard.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_partners_data',
                    nonce: partnerCiuPublicDashboard.nonce,
                    page: page,
                    per_page: 12,
                    orderby: $('#sort-by').val() || 'total_cius',
                    order: 'DESC',
                    search: $('#partner-search').val() || '',
                    status_filter: $('#status-filter').val() || ''
                },
                success: function(response) {
                    if (response.success && response.data.partners.length > 0) {
                        appendPartners(response.data.partners);
                        page++;

                        if (response.data.partners.length < 12) {
                            button.hide();
                        }
                    } else {
                        button.hide();
                    }

                    button.text(originalText).prop('disabled', false);
                    loading = false;

                    // Reinitialize AOS for new elements
                    if (typeof AOS !== 'undefined') {
                        AOS.refresh();
                    }
                },
                error: function() {
                    alert(partnerCiuPublicDashboard.strings.error);
                    button.text(originalText).prop('disabled', false);
                    loading = false;
                }
            });
        });
    }

    /**
     * Append partners to grid
     */
    function appendPartners(partners) {
        var grid = $('#partners-grid');

        partners.forEach(function(partner, index) {
            var delay = index * 50;
            var logoUrl = partner.logo ? partner.logo : partnerCiuPublicDashboard.placeholderLogo;
            var heroClass = partner.is_hero ? 'hero-partner' : '';
            var heroBadge = partner.is_hero ? '<div class="hero-badge"><i class="fas fa-star"></i>' + (partner.hero_highlight || 'Hero Partner') + '</div>' : '';

            var html = '<div class="partner-card ' + heroClass + '" data-aos="fade-up" data-aos-delay="' + delay + '">' +
                heroBadge +
                '<div class="partner-logo">' +
                '<img src="' + logoUrl + '" alt="' + partner.name + '" loading="lazy">' +
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

    /**
     * Initialize collection details
     */
    function initCollectionDetails() {
        $(document).on('click', '.view-collection-details', function() {
            var collectionName = $(this).data('collection');
            // This can be extended to show a modal with detailed collection information
            console.log('View details for collection:', collectionName);
        });
    }

})(jQuery);
