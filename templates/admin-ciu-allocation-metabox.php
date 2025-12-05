<?php
/**
 * Admin CIU Allocation Meta Box Template
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="ciu-allocation-metabox">
    <!-- Summary Section -->
    <div class="ciu-summary-section">
        <h3><?php esc_html_e('CIU Summary', 'partner-ciu-manager'); ?></h3>
        <div class="ciu-summary-grid">
            <div class="summary-item total">
                <span class="summary-label"><?php esc_html_e('Total CIUs', 'partner-ciu-manager'); ?></span>
                <span class="summary-value" id="total-cius"><?php echo esc_html(number_format($summary['total_cius'])); ?></span>
            </div>
            <div class="summary-item pending">
                <span class="summary-label"><?php esc_html_e('Pending', 'partner-ciu-manager'); ?></span>
                <span class="summary-value" id="total-pending"><?php echo esc_html(number_format($summary['total_pending'])); ?></span>
            </div>
            <div class="summary-item active">
                <span class="summary-label"><?php esc_html_e('Active', 'partner-ciu-manager'); ?></span>
                <span class="summary-value" id="total-active"><?php echo esc_html(number_format($summary['total_active'])); ?></span>
            </div>
            <div class="summary-item verified">
                <span class="summary-label"><?php esc_html_e('Verified/Retired', 'partner-ciu-manager'); ?></span>
                <span class="summary-value" id="total-verified"><?php echo esc_html(number_format($summary['total_verified'])); ?></span>
            </div>
            <div class="summary-item funds">
                <span class="summary-label"><?php esc_html_e('Total Funds', 'partner-ciu-manager'); ?></span>
                <span class="summary-value" id="total-funds"><?php echo esc_html(get_woocommerce_currency_symbol() . number_format($summary['total_funds'], 2)); ?></span>
            </div>
        </div>
    </div>

    <!-- Categories Accordion -->
    <div class="ciu-categories-accordion">
        <?php foreach ($this->categories as $category_slug => $category): ?>
            <?php
            $category_data = isset($allocations[$category_slug]) ? $allocations[$category_slug] : array(
                'category_name' => $category['name'],
                'category_icon' => $category['icon'],
                'total_cius' => 0,
                'collections' => array()
            );
            ?>
            <div class="category-section" data-category="<?php echo esc_attr($category_slug); ?>">
                <div class="category-header">
                    <div class="category-title">
                        <span class="category-icon"><?php echo esc_html($category['icon']); ?></span>
                        <h4><?php echo esc_html($category['name']); ?></h4>
                        <span class="category-total"><?php echo esc_html(number_format($category_data['total_cius'])); ?> CIUs</span>
                    </div>
                    <button type="button" class="category-toggle">
                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                    </button>
                </div>

                <div class="category-content" style="display: none;">
                    <div class="collections-container" data-category="<?php echo esc_attr($category_slug); ?>">
                        <?php
                        $collection_number = 1;
                        if (!empty($category_data['collections'])):
                            foreach ($category_data['collections'] as $coll_id => $collection):
                        ?>
                            <div class="collection-item" data-collection-id="<?php echo esc_attr($coll_id); ?>">
                                <div class="collection-header">
                                    <span class="collection-number"><?php echo esc_html($collection_number); ?>.</span>
                                    <span class="collection-title-display"><?php echo esc_html($collection['collection_title']); ?></span>
                                    <button type="button" class="remove-collection button-link-delete">
                                        <?php esc_html_e('Remove', 'partner-ciu-manager'); ?>
                                    </button>
                                </div>

                                <input type="hidden"
                                       name="ciu_allocations[<?php echo esc_attr($category_slug); ?>][collections][<?php echo esc_attr($coll_id); ?>][collection_number]"
                                       value="<?php echo esc_attr($collection_number); ?>"
                                       class="collection-number-field">

                                <input type="hidden"
                                       name="ciu_allocations[<?php echo esc_attr($category_slug); ?>][collections][<?php echo esc_attr($coll_id); ?>][date_added]"
                                       value="<?php echo esc_attr($collection['date_added'] ?? date('Y-m-d')); ?>">

                                <div class="collection-fields">
                                    <div class="field-row">
                                        <div class="field-group">
                                            <label><?php esc_html_e('Collection Title', 'partner-ciu-manager'); ?> *</label>
                                            <input type="text"
                                                   name="ciu_allocations[<?php echo esc_attr($category_slug); ?>][collections][<?php echo esc_attr($coll_id); ?>][title]"
                                                   value="<?php echo esc_attr($collection['collection_title']); ?>"
                                                   class="widefat collection-title-input"
                                                   required>
                                        </div>

                                        <div class="field-group">
                                            <label><?php esc_html_e('CIU Amount', 'partner-ciu-manager'); ?> *</label>
                                            <input type="number"
                                                   name="ciu_allocations[<?php echo esc_attr($category_slug); ?>][collections][<?php echo esc_attr($coll_id); ?>][ciu_amount]"
                                                   value="<?php echo esc_attr($collection['ciu_amount']); ?>"
                                                   class="small-text ciu-amount-input"
                                                   min="0"
                                                   step="1"
                                                   required>
                                        </div>

                                        <div class="field-group">
                                            <label><?php esc_html_e('Status', 'partner-ciu-manager'); ?></label>
                                            <select name="ciu_allocations[<?php echo esc_attr($category_slug); ?>][collections][<?php echo esc_attr($coll_id); ?>][status]"
                                                    class="collection-status-select">
                                                <option value="pending" <?php selected($collection['status'], 'pending'); ?>>
                                                    <?php esc_html_e('Pending', 'partner-ciu-manager'); ?>
                                                </option>
                                                <option value="active" <?php selected($collection['status'], 'active'); ?>>
                                                    <?php esc_html_e('Active', 'partner-ciu-manager'); ?>
                                                </option>
                                                <option value="verified" <?php selected($collection['status'], 'verified'); ?>>
                                                    <?php esc_html_e('Verified/Retired', 'partner-ciu-manager'); ?>
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="field-row">
                                        <div class="field-group">
                                            <label><?php esc_html_e('Description (Optional)', 'partner-ciu-manager'); ?></label>
                                            <textarea name="ciu_allocations[<?php echo esc_attr($category_slug); ?>][collections][<?php echo esc_attr($coll_id); ?>][description]"
                                                      class="widefat"
                                                      rows="3"><?php echo esc_textarea($collection['description'] ?? ''); ?></textarea>
                                        </div>
                                    </div>

                                    <div class="field-row">
                                        <div class="field-group">
                                            <label><?php esc_html_e('External Link (Optional)', 'partner-ciu-manager'); ?></label>
                                            <input type="url"
                                                   name="ciu_allocations[<?php echo esc_attr($category_slug); ?>][collections][<?php echo esc_attr($coll_id); ?>][external_link]"
                                                   value="<?php echo esc_url($collection['external_link'] ?? ''); ?>"
                                                   class="widefat"
                                                   placeholder="https://">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                                $collection_number++;
                            endforeach;
                        endif;
                        ?>
                    </div>

                    <button type="button" class="add-collection button button-secondary">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <?php esc_html_e('Add Collection', 'partner-ciu-manager'); ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="ciu-allocation-help">
        <p class="description">
            <strong><?php esc_html_e('Note:', 'partner-ciu-manager'); ?></strong>
            <?php esc_html_e('Allocate CIUs across different categories and their collections (projects). The summary will update automatically based on your allocations.', 'partner-ciu-manager'); ?>
        </p>
    </div>
</div>
