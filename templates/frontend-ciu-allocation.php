<?php
/**
 * Frontend CIU Allocation Display Template
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Variables available:
// $partner - WP_Post object
// $allocations - Array of CIU allocations by category
// $summary - Array of summary totals
// $atts - Shortcode attributes
?>

<div class="ciu-allocation-frontend-wrapper">

    <?php if ($atts['show_summary'] === 'yes' && !empty($summary)): ?>
        <!-- CIU Summary Section -->
        <div class="ciu-summary-section-frontend">
            <h3 class="ciu-section-title"><?php esc_html_e('CIU Summary', 'partner-ciu-manager'); ?></h3>
            <div class="ciu-summary-grid-frontend">
                <div class="ciu-summary-item total">
                    <div class="summary-icon">📊</div>
                    <div class="summary-content">
                        <span class="summary-label"><?php esc_html_e('Total CIUs', 'partner-ciu-manager'); ?></span>
                        <span class="summary-value"><?php echo esc_html(number_format($summary['total_cius'])); ?></span>
                    </div>
                </div>

                <div class="ciu-summary-item pending">
                    <div class="summary-icon">⏳</div>
                    <div class="summary-content">
                        <span class="summary-label"><?php esc_html_e('Pending', 'partner-ciu-manager'); ?></span>
                        <span class="summary-value"><?php echo esc_html(number_format($summary['total_pending'])); ?></span>
                    </div>
                </div>

                <div class="ciu-summary-item active">
                    <div class="summary-icon">✅</div>
                    <div class="summary-content">
                        <span class="summary-label"><?php esc_html_e('Active', 'partner-ciu-manager'); ?></span>
                        <span class="summary-value"><?php echo esc_html(number_format($summary['total_active'])); ?></span>
                    </div>
                </div>

                <div class="ciu-summary-item verified">
                    <div class="summary-icon">🏆</div>
                    <div class="summary-content">
                        <span class="summary-label"><?php esc_html_e('Verified/Retired', 'partner-ciu-manager'); ?></span>
                        <span class="summary-value"><?php echo esc_html(number_format($summary['total_verified'])); ?></span>
                    </div>
                </div>

                <?php
                // Get currency from settings
                $settings = get_option('partner_ciu_settings', array());
                $currency = isset($settings['currency']) ? $settings['currency'] : 'GBP';
                $currency_symbol = $currency === 'GBP' ? '£' : ($currency === 'USD' ? '$' : $currency);
                ?>
                <div class="ciu-summary-item funds">
                    <div class="summary-icon">💰</div>
                    <div class="summary-content">
                        <span class="summary-label"><?php esc_html_e('Total Funds', 'partner-ciu-manager'); ?></span>
                        <span class="summary-value"><?php echo esc_html($currency_symbol . number_format($summary['total_funds'], 2)); ?></span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($atts['show_categories'] === 'yes'): ?>
        <!-- Categories Section -->
        <div class="ciu-categories-section-frontend">
            <h3 class="ciu-section-title"><?php esc_html_e('CIU Allocation by Category', 'partner-ciu-manager'); ?></h3>

            <?php foreach ($allocations as $category_slug => $category_data): ?>
                <?php if (empty($category_data['collections'])): continue; endif; ?>

                <div class="ciu-category-card" data-category="<?php echo esc_attr($category_slug); ?>">
                    <div class="ciu-category-header">
                        <div class="category-title-section">
                            <?php echo CIU_Frontend_Display::get_category_icon($category_data['category_icon']); ?>
                            <h4 class="category-title"><?php echo esc_html($category_data['category_name']); ?></h4>
                            <span class="category-total-badge"><?php echo esc_html(number_format($category_data['total_cius'])); ?> <?php esc_html_e('CIUs', 'partner-ciu-manager'); ?></span>
                        </div>
                        <button type="button" class="category-toggle-btn" aria-expanded="false">
                            <span class="toggle-icon">▼</span>
                        </button>
                    </div>

                    <div class="ciu-category-content" style="display: none;">
                        <div class="collections-list">
                            <?php foreach ($category_data['collections'] as $collection_id => $collection): ?>
                                <div class="collection-card">
                                    <div class="collection-header-section">
                                        <div class="collection-number">#<?php echo esc_html($collection['collection_number']); ?></div>
                                        <h5 class="collection-title"><?php echo esc_html($collection['collection_title']); ?></h5>
                                        <?php echo CIU_Frontend_Display::get_status_badge($collection['status']); ?>
                                    </div>

                                    <div class="collection-details">
                                        <div class="collection-detail-item">
                                            <span class="detail-label"><?php esc_html_e('CIU Amount:', 'partner-ciu-manager'); ?></span>
                                            <span class="detail-value ciu-amount"><?php echo esc_html(number_format($collection['ciu_amount'])); ?></span>
                                        </div>

                                        <?php if (!empty($collection['collection_datetime'])): ?>
                                            <div class="collection-detail-item">
                                                <span class="detail-label"><?php esc_html_e('Date & Time:', 'partner-ciu-manager'); ?></span>
                                                <span class="detail-value datetime">
                                                    <span class="datetime-icon">📅</span>
                                                    <?php echo CIU_Frontend_Display::format_datetime($collection['collection_datetime']); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($collection['description'])): ?>
                                            <div class="collection-detail-item description">
                                                <span class="detail-label"><?php esc_html_e('Description:', 'partner-ciu-manager'); ?></span>
                                                <p class="detail-value"><?php echo wp_kses_post(wpautop($collection['description'])); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="collection-meta">
                                        <span class="meta-item">
                                            <span class="meta-icon">📌</span>
                                            <?php esc_html_e('Added:', 'partner-ciu-manager'); ?>
                                            <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($collection['date_added']))); ?>
                                        </span>
                                        <?php if (!empty($collection['last_modified'])): ?>
                                            <span class="meta-item">
                                                <span class="meta-icon">✏️</span>
                                                <?php esc_html_e('Modified:', 'partner-ciu-manager'); ?>
                                                <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($collection['last_modified']))); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
