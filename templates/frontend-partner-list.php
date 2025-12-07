<?php
/**
 * Frontend Partner List Template
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Variables available:
// $partners - Array of WP_Post objects
// $atts - Shortcode attributes
?>

<div class="ciu-partners-list-wrapper">
    <div class="ciu-partners-header">
        <h2 class="partners-list-title"><?php esc_html_e('Our Partners', 'partner-ciu-manager'); ?></h2>
        <p class="partners-list-description"><?php esc_html_e('Browse our partner organizations and view their CIU allocation details', 'partner-ciu-manager'); ?></p>
    </div>

    <?php if ($atts['show_search'] === 'yes'): ?>
        <div class="ciu-partners-search">
            <input type="text"
                   id="partner-search"
                   class="partner-search-input"
                   placeholder="<?php esc_attr_e('Search partners...', 'partner-ciu-manager'); ?>"
                   aria-label="<?php esc_attr_e('Search partners', 'partner-ciu-manager'); ?>">
            <span class="search-icon">🔍</span>
        </div>
    <?php endif; ?>

    <div class="ciu-partners-grid" data-columns="<?php echo esc_attr($atts['columns']); ?>">
        <?php foreach ($partners as $partner): ?>
            <?php
            $partner_id = $partner->ID;
            $thumbnail_id = get_post_thumbnail_id($partner_id);
            $is_hero = get_post_meta($partner_id, '_is_hero_partner', true);
            $summary = get_post_meta($partner_id, 'ciu_summary', true);
            $allocations = get_post_meta($partner_id, 'ciu_allocations', true);

            // Get total CIUs
            $total_cius = isset($summary['total_cius']) ? $summary['total_cius'] : 0;
            $total_active = isset($summary['total_active']) ? $summary['total_active'] : 0;

            // Count categories with data
            $active_categories = 0;
            if (!empty($allocations)) {
                foreach ($allocations as $category) {
                    if (!empty($category['collections'])) {
                        $active_categories++;
                    }
                }
            }

            // Build partner link
            $partner_link = add_query_arg('partner', $partner_id, get_permalink());
            ?>

            <div class="partner-card <?php echo $is_hero ? 'hero-partner' : ''; ?>" data-partner-id="<?php echo esc_attr($partner_id); ?>">
                <?php if ($is_hero): ?>
                    <div class="hero-badge">
                        <span class="hero-icon">⭐</span>
                        <span class="hero-text"><?php esc_html_e('Hero Partner', 'partner-ciu-manager'); ?></span>
                    </div>
                <?php endif; ?>

                <a href="<?php echo esc_url($partner_link); ?>" class="partner-card-link">
                    <div class="partner-card-header">
                        <?php if ($thumbnail_id): ?>
                            <div class="partner-logo">
                                <?php echo get_the_post_thumbnail($partner_id, 'medium', array('alt' => esc_attr($partner->post_title))); ?>
                            </div>
                        <?php else: ?>
                            <div class="partner-logo-placeholder">
                                <span class="logo-initial"><?php echo esc_html(substr($partner->post_title, 0, 1)); ?></span>
                            </div>
                        <?php endif; ?>

                        <h3 class="partner-card-title"><?php echo esc_html($partner->post_title); ?></h3>
                    </div>

                    <div class="partner-card-stats">
                        <div class="stat-item total-cius">
                            <span class="stat-icon">📊</span>
                            <div class="stat-content">
                                <span class="stat-value"><?php echo esc_html(number_format($total_cius)); ?></span>
                                <span class="stat-label"><?php esc_html_e('Total CIUs', 'partner-ciu-manager'); ?></span>
                            </div>
                        </div>

                        <div class="stat-item active-cius">
                            <span class="stat-icon">✅</span>
                            <div class="stat-content">
                                <span class="stat-value"><?php echo esc_html(number_format($total_active)); ?></span>
                                <span class="stat-label"><?php esc_html_e('Active', 'partner-ciu-manager'); ?></span>
                            </div>
                        </div>

                        <div class="stat-item categories">
                            <span class="stat-icon">🏷️</span>
                            <div class="stat-content">
                                <span class="stat-value"><?php echo esc_html($active_categories); ?></span>
                                <span class="stat-label"><?php esc_html_e('Categories', 'partner-ciu-manager'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="partner-card-footer">
                        <span class="view-details-link">
                            <?php esc_html_e('View Details', 'partner-ciu-manager'); ?>
                            <span class="arrow-icon">→</span>
                        </span>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="no-partners-found" style="display: none;">
        <p><?php esc_html_e('No partners found matching your search.', 'partner-ciu-manager'); ?></p>
    </div>
</div>
