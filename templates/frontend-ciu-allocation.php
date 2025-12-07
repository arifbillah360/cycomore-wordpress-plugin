<?php
/**
 * Frontend CIU Allocation Display Template - Corporate Black & White Design
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
$total_funds = get_post_meta($partner_id, '_total_funds', true) ?: 0;
?>

<div class="ciu-dashboard-wrapper">

    <!-- Partner Logo Section -->
    <div class="dashboard-logo-section">
        <?php if (has_post_thumbnail($partner_id)): ?>
            <div class="partner-logo-large">
                <?php echo get_the_post_thumbnail($partner_id, 'medium', array(
                    'alt' => esc_attr($partner->post_title),
                    'class' => 'partner-logo-img'
                )); ?>
            </div>
        <?php else: ?>
            <div class="partner-logo-placeholder-large">
                <span class="logo-initial-large"><?php echo esc_html(substr($partner->post_title, 0, 1)); ?></span>
            </div>
        <?php endif; ?>
        <h1 class="partner-name-large"><?php echo esc_html($partner->post_title); ?></h1>
    </div>

    <!-- Stats Section -->
    <div class="dashboard-stats-section">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo esc_html(number_format($total_cius)); ?></div>
                <div class="stat-label">Total CIU's</div>
            </div>

            <div class="stat-card">
                <div class="stat-value"><?php echo esc_html(number_format($pending_cius)); ?></div>
                <div class="stat-label">Pending</div>
            </div>

            <div class="stat-card">
                <div class="stat-value"><?php echo esc_html(number_format($active_cius)); ?></div>
                <div class="stat-label">Active</div>
            </div>

            <div class="stat-card">
                <div class="stat-value"><?php echo esc_html(number_format($verified_cius)); ?></div>
                <div class="stat-label">Verified/Retired</div>
            </div>

            <div class="stat-card">
                <div class="stat-value"><?php echo esc_html($currency_symbol . number_format($total_funds, 2)); ?></div>
                <div class="stat-label">Funds Contributed</div>
            </div>
        </div>
    </div>

    <?php if ($atts['show_categories'] === 'yes' && !empty($allocations)): ?>
        <!-- Category Tabs Section -->
        <div class="dashboard-categories-section">
            <h2 class="section-title">Conservation Impact Categories</h2>

            <!-- Category Tabs -->
            <div class="category-tabs">
                <?php
                $first_tab = true;
                foreach ($allocations as $category_slug => $category_data):
                    if (empty($category_data['collections'])): continue; endif;
                ?>
                    <button type="button"
                            class="category-tab <?php echo $first_tab ? 'active' : ''; ?>"
                            data-category="<?php echo esc_attr($category_slug); ?>"
                            role="tab"
                            aria-selected="<?php echo $first_tab ? 'true' : 'false'; ?>"
                            aria-controls="panel-<?php echo esc_attr($category_slug); ?>">
                        <span class="tab-icon"><?php echo esc_html($category_data['category_icon']); ?></span>
                        <span class="tab-name"><?php echo esc_html($category_data['category_name']); ?></span>
                        <span class="tab-badge"><?php echo esc_html(number_format($category_data['total_cius'])); ?></span>
                    </button>
                <?php
                    $first_tab = false;
                endforeach;
                ?>
            </div>

            <!-- Category Tab Panels -->
            <div class="category-panels">
                <?php
                $first_panel = true;
                foreach ($allocations as $category_slug => $category_data):
                    if (empty($category_data['collections'])): continue; endif;
                ?>
                    <div id="panel-<?php echo esc_attr($category_slug); ?>"
                         class="category-panel <?php echo $first_panel ? 'active' : ''; ?>"
                         role="tabpanel"
                         aria-labelledby="tab-<?php echo esc_attr($category_slug); ?>">

                        <div class="panel-header">
                            <h3 class="panel-title">
                                <?php echo esc_html($category_data['category_icon']); ?>
                                <?php echo esc_html($category_data['category_name']); ?>
                            </h3>
                            <div class="panel-total">
                                Total: <strong><?php echo esc_html(number_format($category_data['total_cius'])); ?> CIUs</strong>
                            </div>
                        </div>

                        <!-- Collections List -->
                        <div class="collections-table-wrapper">
                            <table class="collections-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Collection Title</th>
                                        <th>CIU Amount</th>
                                        <th>Status</th>
                                        <th>Date & Time</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($category_data['collections'] as $collection_id => $collection): ?>
                                        <tr class="collection-row">
                                            <td class="collection-number">
                                                <?php echo esc_html($collection['collection_number']); ?>
                                            </td>
                                            <td class="collection-title-cell">
                                                <strong><?php echo esc_html($collection['collection_title']); ?></strong>
                                            </td>
                                            <td class="collection-amount">
                                                <strong><?php echo esc_html(number_format($collection['ciu_amount'])); ?></strong>
                                            </td>
                                            <td class="collection-status">
                                                <span class="status-badge status-<?php echo esc_attr($collection['status']); ?>">
                                                    <?php
                                                    $status_labels = array(
                                                        'pending' => 'Pending',
                                                        'active' => 'Active',
                                                        'verified' => 'Verified/Retired',
                                                    );
                                                    echo esc_html(isset($status_labels[$collection['status']]) ? $status_labels[$collection['status']] : $collection['status']);
                                                    ?>
                                                </span>
                                            </td>
                                            <td class="collection-datetime">
                                                <?php
                                                if (!empty($collection['collection_datetime'])) {
                                                    echo esc_html(CIU_Frontend_Display::format_datetime($collection['collection_datetime']));
                                                } else {
                                                    echo '—';
                                                }
                                                ?>
                                            </td>
                                            <td class="collection-description">
                                                <?php
                                                if (!empty($collection['description'])) {
                                                    echo esc_html(wp_trim_words($collection['description'], 15, '...'));
                                                } else {
                                                    echo '—';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php
                    $first_panel = false;
                endforeach;
                ?>
            </div>
        </div>
    <?php endif; ?>

</div>
