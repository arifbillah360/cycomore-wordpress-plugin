<?php
/**
 * Frontend CIU Allocation Display Template - Modern Minimal Design
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Variables available:
// $partner - WP_Post object
// $partner_id - Partner ID
// $allocations - Array of CIU allocations by category
// $summary - Array of summary totals
// $atts - Shortcode attributes

// Get currency from settings
$settings = get_option('partner_ciu_settings', array());
$currency = isset($settings['currency']) ? $settings['currency'] : 'GBP';
$currency_symbol = $currency === 'GBP' ? '£' : ($currency === 'USD' ? '$' : $currency);

// Get partner stats from meta
$pending_cius = get_post_meta($partner_id, '_pending_cius', true) ?: 0;
$active_cius = get_post_meta($partner_id, '_active_cius', true) ?: 0;
$verified_cius = get_post_meta($partner_id, '_verified_cius', true) ?: 0;
$total_cius = $pending_cius + $active_cius + $verified_cius;

// Get total funds from multiple sources (for compatibility)
$total_funds = 0;

// First try: Check summary array
if (!empty($summary['total_funds'])) {
    $total_funds = $summary['total_funds'];
}

// Second try: Direct meta field
if ($total_funds == 0) {
    $total_funds = get_post_meta($partner_id, '_total_funds', true);
}

// Third try: Calculate from collections if meta is empty
if (empty($total_funds) && !empty($allocations)) {
    foreach ($allocations as $category) {
        if (!empty($category['collections'])) {
            foreach ($category['collections'] as $collection) {
                if (!empty($collection['ciu_amount'])) {
                    $total_funds += intval($collection['ciu_amount']);
                }
            }
        }
    }
}

// Ensure it's a number
$total_funds = floatval($total_funds);
?>

<div class="ciu-minimal-dashboard">

    <!-- Partner Logo Section -->
    <div class="minimal-logo-section">
        <?php if (has_post_thumbnail($partner_id)): ?>
            <div class="partner-logo-minimal">
                <?php echo get_the_post_thumbnail($partner_id, 'medium', array(
                    'alt' => esc_attr($partner->post_title),
                    'class' => 'partner-logo-image'
                )); ?>
            </div>
        <?php else: ?>
            <div class="partner-logo-placeholder-minimal">
                <span class="logo-letter"><?php echo esc_html(strtoupper(substr($partner->post_title, 0, 1))); ?></span>
            </div>
        <?php endif; ?>
        <h1 class="partner-title-minimal"><?php echo esc_html($partner->post_title); ?></h1>
    </div>

    <!-- Stats Section -->
    <div class="minimal-stats-section">
        <div class="stats-container">
            <div class="stat-box">
                <div class="stat-number"><?php echo esc_html(number_format($total_cius)); ?></div>
                <div class="stat-label">Total CIUs</div>
            </div>

            <div class="stat-box">
                <div class="stat-number"><?php echo esc_html(number_format($pending_cius)); ?></div>
                <div class="stat-label">Pending</div>
            </div>

            <div class="stat-box">
                <div class="stat-number"><?php echo esc_html(number_format($active_cius)); ?></div>
                <div class="stat-label">Active</div>
            </div>

            <div class="stat-box">
                <div class="stat-number"><?php echo esc_html(number_format($verified_cius)); ?></div>
                <div class="stat-label">Verified</div>
            </div>

            <div class="stat-box">
                <!-- Debug: Funds value = <?php echo $total_funds; ?> from partner_id = <?php echo $partner_id; ?> -->
                <div class="stat-number"><?php echo esc_html($currency_symbol . number_format($total_funds, 0)); ?></div>
                <div class="stat-label">Funds Contributed</div>
            </div>
        </div>
    </div>

    <?php if ($atts['show_categories'] === 'yes' && !empty($allocations)): ?>
        <!-- Tabs Section -->
        <div class="minimal-tabs-section">

            <!-- Category Tabs Navigation -->
            <div class="tabs-navigation" role="tablist">
                <?php
                $tab_index = 0;
                foreach ($allocations as $category_slug => $category_data):
                    if (empty($category_data['collections'])): continue; endif;
                    $is_first = ($tab_index === 0);
                ?>
                    <button type="button"
                            class="tab-button <?php echo $is_first ? 'active' : ''; ?>"
                            data-category="<?php echo esc_attr($category_slug); ?>"
                            role="tab"
                            aria-selected="<?php echo $is_first ? 'true' : 'false'; ?>"
                            aria-controls="tab-panel-<?php echo esc_attr($category_slug); ?>"
                            id="tab-<?php echo esc_attr($category_slug); ?>">
                        <span class="tab-label"><?php echo esc_html($category_data['category_name']); ?></span>
                        <span class="tab-count"><?php echo esc_html(number_format($category_data['total_cius'])); ?></span>
                    </button>
                <?php
                    $tab_index++;
                endforeach;
                ?>
            </div>

            <!-- Category Tab Panels -->
            <div class="tabs-content">
                <?php
                $panel_index = 0;
                foreach ($allocations as $category_slug => $category_data):
                    if (empty($category_data['collections'])): continue; endif;
                    $is_first = ($panel_index === 0);
                ?>
                    <div id="tab-panel-<?php echo esc_attr($category_slug); ?>"
                         class="tab-panel <?php echo $is_first ? 'active' : ''; ?>"
                         role="tabpanel"
                         aria-labelledby="tab-<?php echo esc_attr($category_slug); ?>">

                        <!-- Panel Header -->
                        <div class="panel-header-minimal">
                            <div class="panel-title-group">
                                <h2 class="panel-title"><?php echo esc_html($category_data['category_name']); ?></h2>
                            </div>
                            <div class="panel-meta">
                                <span class="panel-total"><?php echo esc_html(number_format($category_data['total_cius'])); ?> CIUs</span>
                            </div>
                        </div>

                        <!-- Collections Grid -->
                        <div class="collections-grid">
                            <?php foreach ($category_data['collections'] as $collection_id => $collection): ?>
                                <div class="collection-card-minimal">

                                    <!-- Card Header -->
                                    <div class="collection-header-minimal">
                                        <div class="collection-number-badge">#<?php echo esc_html($collection['collection_number']); ?></div>
                                        <span class="status-pill status-<?php echo esc_attr($collection['status']); ?>">
                                            <?php
                                            $status_labels = array(
                                                'pending' => 'Pending',
                                                'active' => 'Active',
                                                'verified' => 'Verified',
                                            );
                                            echo esc_html(isset($status_labels[$collection['status']]) ? $status_labels[$collection['status']] : ucfirst($collection['status']));
                                            ?>
                                        </span>
                                    </div>

                                    <!-- Card Body -->
                                    <div class="collection-body">
                                        <h3 class="collection-title-minimal"><?php echo esc_html($collection['collection_title']); ?></h3>

                                        <?php if (!empty($collection['description'])): ?>
                                            <div class="collection-description-minimal">
                                                <?php
                                                // Truncate description to 20 words
                                                $truncated_description = wp_trim_words($collection['description'], 20, '...');
                                                // Convert newlines to <br> tags and allow safe HTML
                                                echo wp_kses_post(nl2br($truncated_description));
                                                ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="collection-meta-grid">
                                            <div class="meta-item">
                                                <span class="meta-label">CIU Amount</span>
                                                <span class="meta-value highlight"><?php echo esc_html(number_format($collection['ciu_amount'])); ?></span>
                                            </div>

                                            <?php if (!empty($collection['collection_datetime'])): ?>
                                                <div class="meta-item">
                                                    <span class="meta-label">Date & Time</span>
                                                    <span class="meta-value">
                                                        <?php echo esc_html(CIU_Frontend_Display::format_datetime($collection['collection_datetime'])); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (!empty($collection['description'])): ?>
                                            <!-- View Details Button - Opens modal with full collection info -->
                                            <button type="button"
                                                    class="view-collection-details-btn"
                                                    data-collection-id="<?php echo esc_attr($collection_id); ?>"
                                                    data-collection-title="<?php echo esc_attr($collection['collection_title']); ?>"
                                                    data-collection-number="<?php echo esc_attr($collection['collection_number']); ?>"
                                                    data-ciu-amount="<?php echo esc_attr($collection['ciu_amount']); ?>"
                                                    data-status="<?php echo esc_attr($collection['status']); ?>"
                                                    data-datetime="<?php echo esc_attr(!empty($collection['collection_datetime']) ? CIU_Frontend_Display::format_datetime($collection['collection_datetime']) : ''); ?>"
                                                    data-description="<?php echo esc_attr($collection['description']); ?>"
                                                    data-description-length="<?php echo esc_attr(strlen($collection['description'])); ?>"
                                                    data-debug="enabled"
                                                    aria-label="View full details for <?php echo esc_attr($collection['collection_title']); ?>">
                                                View Full Details →
                                            </button>
                                            <!-- Description length: <?php echo strlen($collection['description']); ?> characters -->
                                        <?php endif; ?>
                                    </div>

                                </div>
                            <?php endforeach; ?>
                        </div>

                    </div>
                <?php
                    $panel_index++;
                endforeach;
                ?>
            </div>

        </div>
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- COLLECTION DETAILS MODAL                     -->
    <!-- Modal ID: collection-details-modal           -->
    <!-- JavaScript: ciu-allocation-frontend.js       -->
    <!-- ============================================ -->
    <div id="collection-details-modal" class="collection-modal" style="display: none;" data-modal-version="2.0" data-debug="true">
        <!-- Modal Overlay (Click to close) -->
        <div class="modal-overlay" data-element="overlay"></div>

        <!-- Modal Container (Main content area) -->
        <div class="modal-container" data-element="container">

            <!-- Modal Header -->
            <div class="modal-header">
                <h3 class="modal-title" id="modal-collection-title" data-field="title">Loading...</h3>
                <button type="button" class="modal-close" aria-label="Close modal" data-action="close">×</button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <!-- Collection Metadata -->
                <div class="modal-meta">
                    <div class="modal-meta-item">
                        <span class="modal-meta-label">Collection #</span>
                        <span class="modal-meta-value" id="modal-collection-number" data-field="number">—</span>
                    </div>
                    <div class="modal-meta-item">
                        <span class="modal-meta-label">Status</span>
                        <span class="modal-status-badge" id="modal-status" data-field="status">—</span>
                    </div>
                    <div class="modal-meta-item">
                        <span class="modal-meta-label">CIU Amount</span>
                        <span class="modal-meta-value modal-ciu-amount" id="modal-ciu-amount" data-field="amount">—</span>
                    </div>
                    <div class="modal-meta-item">
                        <span class="modal-meta-label">Date & Time</span>
                        <span class="modal-meta-value" id="modal-datetime" data-field="datetime">—</span>
                    </div>
                </div>

                <!-- Full Description Section -->
                <div class="modal-description-section">
                    <h4 class="modal-section-title">Description</h4>
                    <div class="modal-description-content" id="modal-description" data-field="description">
                        Loading description...
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- ============================================ -->
    <!-- END COLLECTION DETAILS MODAL                 -->
    <!-- ============================================ -->

</div>
