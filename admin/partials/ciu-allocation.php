<?php
/**
 * CIU Allocation Admin Page
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get all partners
$partners = get_posts(array(
    'post_type' => 'partner_profile',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC',
));
?>

<div class="wrap partner-ciu-allocation">
    <h1><?php esc_html_e('CIU Allocation', 'partner-ciu-manager'); ?></h1>
    <p class="description"><?php esc_html_e('Allocate CIUs between different statuses for partners.', 'partner-ciu-manager'); ?></p>

    <div class="partner-ciu-allocation-sections">
        <!-- Individual Allocation -->
        <div class="allocation-section">
            <h2><?php esc_html_e('Individual Partner Allocation', 'partner-ciu-manager'); ?></h2>

            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Partner', 'partner-ciu-manager'); ?></th>
                        <th><?php esc_html_e('Pending', 'partner-ciu-manager'); ?></th>
                        <th><?php esc_html_e('Active', 'partner-ciu-manager'); ?></th>
                        <th><?php esc_html_e('Verified', 'partner-ciu-manager'); ?></th>
                        <th><?php esc_html_e('Actions', 'partner-ciu-manager'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($partners)): ?>
                        <tr>
                            <td colspan="5"><?php esc_html_e('No partners found.', 'partner-ciu-manager'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($partners as $partner): ?>
                            <?php
                            $pending = get_post_meta($partner->ID, '_pending_cius', true) ?: 0;
                            $active = get_post_meta($partner->ID, '_active_cius', true) ?: 0;
                            $verified = get_post_meta($partner->ID, '_verified_cius', true) ?: 0;
                            ?>
                            <tr data-partner-id="<?php echo esc_attr($partner->ID); ?>">
                                <td>
                                    <strong><?php echo esc_html($partner->post_title); ?></strong>
                                    <div class="row-actions">
                                        <a href="<?php echo esc_url(get_edit_post_link($partner->ID)); ?>"><?php esc_html_e('Edit', 'partner-ciu-manager'); ?></a>
                                    </div>
                                </td>
                                <td class="pending-count"><strong><?php echo esc_html($pending); ?></strong></td>
                                <td class="active-count"><strong><?php echo esc_html($active); ?></strong></td>
                                <td class="verified-count"><strong><?php echo esc_html($verified); ?></strong></td>
                                <td>
                                    <button type="button" class="button allocate-cius-btn" data-partner-id="<?php echo esc_attr($partner->ID); ?>" data-partner-name="<?php echo esc_attr($partner->post_title); ?>">
                                        <?php esc_html_e('Allocate CIUs', 'partner-ciu-manager'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Bulk Allocation -->
        <div class="allocation-section">
            <h2><?php esc_html_e('Bulk Allocation', 'partner-ciu-manager'); ?></h2>
            <p class="description"><?php esc_html_e('Move all CIUs from one status to another across all partners.', 'partner-ciu-manager'); ?></p>

            <form id="bulk-allocation-form" class="bulk-allocation-form">
                <table class="form-table">
                    <tr>
                        <th><label for="bulk-from-status"><?php esc_html_e('From Status', 'partner-ciu-manager'); ?></label></th>
                        <td>
                            <select id="bulk-from-status" name="from_status" required>
                                <option value=""><?php esc_html_e('Select Status', 'partner-ciu-manager'); ?></option>
                                <option value="pending"><?php esc_html_e('Pending', 'partner-ciu-manager'); ?></option>
                                <option value="active"><?php esc_html_e('Active', 'partner-ciu-manager'); ?></option>
                                <option value="verified"><?php esc_html_e('Verified/Retired', 'partner-ciu-manager'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="bulk-to-status"><?php esc_html_e('To Status', 'partner-ciu-manager'); ?></label></th>
                        <td>
                            <select id="bulk-to-status" name="to_status" required>
                                <option value=""><?php esc_html_e('Select Status', 'partner-ciu-manager'); ?></option>
                                <option value="pending"><?php esc_html_e('Pending', 'partner-ciu-manager'); ?></option>
                                <option value="active"><?php esc_html_e('Active', 'partner-ciu-manager'); ?></option>
                                <option value="verified"><?php esc_html_e('Verified/Retired', 'partner-ciu-manager'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Bulk Allocate', 'partner-ciu-manager'); ?></button>
                </p>
            </form>
        </div>
    </div>
</div>

<!-- Allocation Modal -->
<div id="allocation-modal" class="partner-modal" style="display: none;">
    <div class="partner-modal-content">
        <span class="partner-modal-close">&times;</span>
        <h2><?php esc_html_e('Allocate CIUs', 'partner-ciu-manager'); ?></h2>

        <form id="allocation-form">
            <input type="hidden" id="allocation-partner-id" name="partner_id">

            <p><strong><?php esc_html_e('Partner:', 'partner-ciu-manager'); ?></strong> <span id="allocation-partner-name"></span></p>

            <table class="form-table">
                <tr>
                    <th><label for="allocation-from-status"><?php esc_html_e('From Status', 'partner-ciu-manager'); ?></label></th>
                    <td>
                        <select id="allocation-from-status" name="from_status" required>
                            <option value=""><?php esc_html_e('Select Status', 'partner-ciu-manager'); ?></option>
                            <option value="pending"><?php esc_html_e('Pending', 'partner-ciu-manager'); ?></option>
                            <option value="active"><?php esc_html_e('Active', 'partner-ciu-manager'); ?></option>
                            <option value="verified"><?php esc_html_e('Verified/Retired', 'partner-ciu-manager'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="allocation-to-status"><?php esc_html_e('To Status', 'partner-ciu-manager'); ?></label></th>
                    <td>
                        <select id="allocation-to-status" name="to_status" required>
                            <option value=""><?php esc_html_e('Select Status', 'partner-ciu-manager'); ?></option>
                            <option value="pending"><?php esc_html_e('Pending', 'partner-ciu-manager'); ?></option>
                            <option value="active"><?php esc_html_e('Active', 'partner-ciu-manager'); ?></option>
                            <option value="verified"><?php esc_html_e('Verified/Retired', 'partner-ciu-manager'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="allocation-quantity"><?php esc_html_e('Quantity', 'partner-ciu-manager'); ?></label></th>
                    <td><input type="number" id="allocation-quantity" name="quantity" min="1" required></td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary"><?php esc_html_e('Allocate', 'partner-ciu-manager'); ?></button>
                <button type="button" class="button cancel-allocation"><?php esc_html_e('Cancel', 'partner-ciu-manager'); ?></button>
            </p>
        </form>
    </div>
</div>
