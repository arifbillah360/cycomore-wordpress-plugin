<?php
/**
 * Collection Details Template
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="collection-card" data-aos="fade-up" data-aos-delay="<?php echo isset($delay) ? esc_attr($delay) : '0'; ?>">
    <div class="collection-header">
        <h3 class="collection-name"><?php echo esc_html($collection['name']); ?></h3>
        <span class="collection-badge"><?php echo esc_html(number_format($collection['total_cius'])); ?> <?php esc_html_e('CIUs', 'partner-ciu-manager'); ?></span>
    </div>

    <div class="collection-body">
        <div class="collection-stat">
            <i class="fas fa-users"></i>
            <div class="stat-info">
                <span class="stat-label"><?php esc_html_e('Contributing Partners', 'partner-ciu-manager'); ?></span>
                <span class="stat-value"><?php echo esc_html($collection['partners']); ?></span>
            </div>
        </div>

        <div class="collection-stat">
            <i class="fas fa-percentage"></i>
            <div class="stat-info">
                <span class="stat-label"><?php esc_html_e('Percentage of Total', 'partner-ciu-manager'); ?></span>
                <span class="stat-value"><?php echo esc_html($collection['percentage']); ?>%</span>
            </div>
        </div>

        <div class="collection-progress">
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo esc_attr($collection['percentage']); ?>%"></div>
            </div>
        </div>
    </div>

    <div class="collection-footer">
        <button type="button" class="btn btn-outline view-collection-details" data-collection="<?php echo esc_attr($collection['name']); ?>">
            <i class="fas fa-info-circle"></i>
            <?php esc_html_e('View Details', 'partner-ciu-manager'); ?>
        </button>
    </div>
</div>
