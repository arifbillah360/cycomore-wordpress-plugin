<?php
/**
 * Partner Card Template
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$logo_url = $partner['logo'] ? wp_get_attachment_url($partner['logo']) : PARTNER_CIU_PLUGIN_URL . 'assets/images/placeholder-logo.png';
?>

<div class="partner-card <?php echo $partner['is_hero'] ? 'hero-partner' : ''; ?>" data-aos="fade-up" data-aos-delay="<?php echo isset($delay) ? esc_attr($delay) : '0'; ?>">
    <?php if ($partner['is_hero']): ?>
        <div class="hero-badge">
            <i class="fas fa-star"></i>
            <?php echo esc_html($partner['hero_highlight'] ?: __('Hero Partner', 'partner-ciu-manager')); ?>
        </div>
    <?php endif; ?>

    <div class="partner-logo">
        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($partner['name']); ?>" loading="lazy">
    </div>

    <div class="partner-info">
        <h3 class="partner-name"><?php echo esc_html($partner['name']); ?></h3>

        <div class="partner-stats">
            <div class="partner-stat-item">
                <span class="stat-label"><?php esc_html_e('Total CIUs', 'partner-ciu-manager'); ?></span>
                <span class="stat-value"><?php echo esc_html(number_format($partner['total_cius'])); ?></span>
            </div>

            <div class="partner-stat-item">
                <span class="stat-label"><?php esc_html_e('Funds', 'partner-ciu-manager'); ?></span>
                <span class="stat-value"><?php echo esc_html(get_woocommerce_currency_symbol() . number_format($partner['total_funds'], 2)); ?></span>
            </div>
        </div>

        <div class="partner-ciu-breakdown">
            <div class="ciu-breakdown-item pending">
                <span class="breakdown-label"><?php esc_html_e('Pending', 'partner-ciu-manager'); ?></span>
                <span class="breakdown-value"><?php echo esc_html($partner['pending_cius']); ?></span>
            </div>

            <div class="ciu-breakdown-item active">
                <span class="breakdown-label"><?php esc_html_e('Active', 'partner-ciu-manager'); ?></span>
                <span class="breakdown-value"><?php echo esc_html($partner['active_cius']); ?></span>
            </div>

            <div class="ciu-breakdown-item verified">
                <span class="breakdown-label"><?php esc_html_e('Verified', 'partner-ciu-manager'); ?></span>
                <span class="breakdown-value"><?php echo esc_html($partner['verified_cius']); ?></span>
            </div>
        </div>

        <?php if ($partner['last_purchase_date']): ?>
            <div class="partner-last-purchase">
                <i class="fas fa-clock"></i>
                <?php printf(
                    esc_html__('As of %s', 'partner-ciu-manager'),
                    esc_html(date_i18n(get_option('date_format'), strtotime($partner['last_purchase_date'])))
                ); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
