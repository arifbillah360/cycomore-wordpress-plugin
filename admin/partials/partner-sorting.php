<?php
/**
 * Partner Sorting Admin Page
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get all partners
$partners = Partner_Sorting::get_sorted_partners();
?>

<div class="wrap partner-sorting">
    <h1><?php esc_html_e('Partner Sorting', 'partner-ciu-manager'); ?></h1>
    <p class="description"><?php esc_html_e('Drag and drop to reorder partners. Hero partners will always appear first.', 'partner-ciu-manager'); ?></p>

    <div class="partner-sorting-container">
        <div class="sorting-notice" style="display: none;">
            <p></p>
        </div>

        <?php if (empty($partners)): ?>
            <p><?php esc_html_e('No partners found.', 'partner-ciu-manager'); ?></p>
        <?php else: ?>
            <ul id="partner-sortable-list" class="partner-sortable-list">
                <?php foreach ($partners as $partner): ?>
                    <?php
                    $logo = get_post_meta($partner->ID, '_partner_logo', true);
                    $is_hero = get_post_meta($partner->ID, '_is_hero_partner', true);
                    $hero_highlight = get_post_meta($partner->ID, '_hero_highlight_text', true);
                    ?>
                    <li class="partner-sortable-item <?php echo $is_hero ? 'is-hero' : ''; ?>" data-partner-id="<?php echo esc_attr($partner->ID); ?>">
                        <div class="partner-item-content">
                            <span class="partner-drag-handle dashicons dashicons-menu"></span>

                            <div class="partner-logo">
                                <?php if ($logo): ?>
                                    <img src="<?php echo esc_url(wp_get_attachment_url($logo)); ?>" alt="<?php echo esc_attr($partner->post_title); ?>">
                                <?php else: ?>
                                    <div class="partner-no-logo"><?php esc_html_e('No Logo', 'partner-ciu-manager'); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="partner-info">
                                <strong class="partner-name"><?php echo esc_html($partner->post_title); ?></strong>
                                <?php if ($is_hero): ?>
                                    <span class="hero-badge"><?php esc_html_e('Hero Partner', 'partner-ciu-manager'); ?></span>
                                <?php endif; ?>
                                <?php if ($hero_highlight): ?>
                                    <div class="hero-highlight"><?php echo esc_html($hero_highlight); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="partner-actions">
                                <a href="<?php echo esc_url(get_edit_post_link($partner->ID)); ?>" class="button button-small"><?php esc_html_e('Edit', 'partner-ciu-manager'); ?></a>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>

            <p class="submit">
                <button type="button" id="save-partner-order" class="button button-primary"><?php esc_html_e('Save Order', 'partner-ciu-manager'); ?></button>
            </p>
        <?php endif; ?>
    </div>
</div>
