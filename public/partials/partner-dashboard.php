<?php
/**
 * Partner Dashboard Template
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$partner_data = Partner_Dashboard::get_partner_data($partner_profile);
$ciu_price = Partner_CIU_Settings::get_ciu_price();
$currency_symbol = get_woocommerce_currency_symbol();
?>

<div class="partner-ciu-dashboard">
    <h2><?php esc_html_e('Partner Dashboard', 'partner-ciu-manager'); ?></h2>

    <!-- Profile Section -->
    <div class="partner-section partner-profile-section">
        <h3><?php esc_html_e('Profile', 'partner-ciu-manager'); ?></h3>

        <div class="partner-profile-content">
            <div class="partner-logo-section">
                <label><?php esc_html_e('Partner Logo', 'partner-ciu-manager'); ?></label>
                <div class="partner-logo-display">
                    <?php if ($partner_data['logo']): ?>
                        <img src="<?php echo esc_url(wp_get_attachment_url($partner_data['logo'])); ?>" alt="<?php echo esc_attr($partner_data['name']); ?>">
                    <?php else: ?>
                        <p class="no-logo"><?php esc_html_e('No logo uploaded', 'partner-ciu-manager'); ?></p>
                    <?php endif; ?>
                </div>
                <button type="button" class="button partner-upload-logo-btn"><?php esc_html_e('Upload Logo', 'partner-ciu-manager'); ?></button>
            </div>

            <form id="partner-profile-form" class="partner-profile-form">
                <div class="form-group">
                    <label for="bank_name"><?php esc_html_e('Bank Name', 'partner-ciu-manager'); ?></label>
                    <input type="text" id="bank_name" name="bank_name" value="<?php echo esc_attr($partner_data['bank_name']); ?>" class="regular-text">
                </div>

                <div class="form-group">
                    <label for="account_number"><?php esc_html_e('Account Number', 'partner-ciu-manager'); ?></label>
                    <input type="text" id="account_number" name="account_number" value="<?php echo esc_attr($partner_data['account_number']); ?>" class="regular-text">
                </div>

                <div class="form-group">
                    <label for="sort_code"><?php esc_html_e('Sort Code', 'partner-ciu-manager'); ?></label>
                    <input type="text" id="sort_code" name="sort_code" value="<?php echo esc_attr($partner_data['sort_code']); ?>" class="regular-text" placeholder="XX-XX-XX">
                </div>

                <button type="submit" class="button button-primary"><?php esc_html_e('Update Profile', 'partner-ciu-manager'); ?></button>
            </form>
        </div>
    </div>

    <!-- CIU Purchase Section -->
    <div class="partner-section partner-purchase-section">
        <h3><?php esc_html_e('Purchase CIUs', 'partner-ciu-manager'); ?></h3>

        <form id="partner-purchase-form" class="partner-purchase-form">
            <div class="form-group">
                <label for="ciu_quantity"><?php esc_html_e('CIU Quantity', 'partner-ciu-manager'); ?></label>
                <input type="number" id="ciu_quantity" name="ciu_quantity" min="1" value="1" class="regular-text">
            </div>

            <div class="purchase-cost-display">
                <span class="cost-label"><?php esc_html_e('Total Cost:', 'partner-ciu-manager'); ?></span>
                <span class="cost-amount"><?php echo esc_html($currency_symbol); ?><span id="total-cost"><?php echo esc_html(number_format($ciu_price, 2)); ?></span></span>
            </div>

            <p class="cost-note"><?php printf(esc_html__('Price per CIU: %s%s', 'partner-ciu-manager'), esc_html($currency_symbol), esc_html(number_format($ciu_price, 2))); ?></p>

            <button type="submit" class="button button-primary button-large"><?php esc_html_e('Purchase CIUs', 'partner-ciu-manager'); ?></button>
        </form>
    </div>

    <!-- CIU Status Overview -->
    <div class="partner-section partner-status-section">
        <h3><?php esc_html_e('CIU Status Overview', 'partner-ciu-manager'); ?></h3>

        <div class="ciu-stats-grid">
            <div class="ciu-stat-item">
                <span class="stat-label"><?php esc_html_e('Total CIUs', 'partner-ciu-manager'); ?></span>
                <span class="stat-value"><?php echo esc_html($partner_data['total_cius']); ?></span>
            </div>

            <div class="ciu-stat-item pending">
                <span class="stat-label"><?php esc_html_e('Pending CIUs', 'partner-ciu-manager'); ?></span>
                <span class="stat-value"><?php echo esc_html($partner_data['pending_cius']); ?></span>
            </div>

            <div class="ciu-stat-item active">
                <span class="stat-label"><?php esc_html_e('Active CIUs', 'partner-ciu-manager'); ?></span>
                <span class="stat-value"><?php echo esc_html($partner_data['active_cius']); ?></span>
            </div>

            <div class="ciu-stat-item verified">
                <span class="stat-label"><?php esc_html_e('Verified/Retired CIUs', 'partner-ciu-manager'); ?></span>
                <span class="stat-value"><?php echo esc_html($partner_data['verified_cius']); ?></span>
            </div>
        </div>
    </div>

    <!-- Collection Breakdown -->
    <?php if (!empty($partner_data['collections']) && is_array($partner_data['collections'])): ?>
    <div class="partner-section partner-collections-section">
        <h3><?php esc_html_e('Collection Breakdown', 'partner-ciu-manager'); ?></h3>

        <table class="partner-collections-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Collection', 'partner-ciu-manager'); ?></th>
                    <th><?php esc_html_e('CIU Count', 'partner-ciu-manager'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($partner_data['collections'] as $collection): ?>
                <tr>
                    <td><?php echo esc_html($collection['name']); ?></td>
                    <td><?php echo esc_html($collection['count']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Funds Contributed -->
    <div class="partner-section partner-funds-section">
        <h3><?php esc_html_e('Financial Summary', 'partner-ciu-manager'); ?></h3>

        <div class="funds-info">
            <div class="funds-item">
                <span class="funds-label"><?php esc_html_e('Total Funds Contributed:', 'partner-ciu-manager'); ?></span>
                <span class="funds-value"><?php echo esc_html($currency_symbol . number_format($partner_data['total_funds'], 2)); ?></span>
            </div>

            <?php if ($partner_data['last_purchase_date']): ?>
            <div class="funds-item">
                <span class="funds-label"><?php esc_html_e('Last Purchase Date:', 'partner-ciu-manager'); ?></span>
                <span class="funds-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($partner_data['last_purchase_date']))); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
