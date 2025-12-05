<?php
/**
 * Admin Notes Meta Box Template
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="admin-notes-metabox">
    <p class="description">
        <?php esc_html_e('Internal notes visible only to administrators and editors. Not visible to partners.', 'partner-ciu-manager'); ?>
    </p>

    <!-- Existing Notes -->
    <div class="notes-list" id="notes-list">
        <?php if (!empty($notes)): ?>
            <?php foreach ($notes as $note): ?>
                <div class="note-item" data-note-id="<?php echo esc_attr($note['note_id']); ?>">
                    <div class="note-header">
                        <strong class="note-name"><?php echo esc_html($note['note_name']); ?></strong>
                        <span class="note-meta">
                            <?php
                            printf(
                                esc_html__('By %1$s on %2$s', 'partner-ciu-manager'),
                                esc_html($note['created_by']),
                                esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($note['created_date'])))
                            );
                            ?>
                        </span>
                        <button type="button" class="button button-small edit-note-btn" data-note-id="<?php echo esc_attr($note['note_id']); ?>">
                            <?php esc_html_e('Edit', 'partner-ciu-manager'); ?>
                        </button>
                        <button type="button" class="button button-small button-link-delete delete-note-btn" data-note-id="<?php echo esc_attr($note['note_id']); ?>">
                            <?php esc_html_e('Delete', 'partner-ciu-manager'); ?>
                        </button>
                    </div>
                    <div class="note-content">
                        <p><?php echo nl2br(esc_html($note['note_content'])); ?></p>
                    </div>
                    <?php if (isset($note['last_modified']) && $note['last_modified'] !== $note['created_date']): ?>
                        <div class="note-modified">
                            <small>
                                <?php
                                printf(
                                    esc_html__('Last modified by %1$s on %2$s', 'partner-ciu-manager'),
                                    esc_html($note['modified_by']),
                                    esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($note['last_modified'])))
                                );
                                ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-notes-message"><?php esc_html_e('No notes yet. Add your first note below.', 'partner-ciu-manager'); ?></p>
        <?php endif; ?>
    </div>

    <!-- Add New Note Form -->
    <div class="add-note-section">
        <h3><?php esc_html_e('Add New Note', 'partner-ciu-manager'); ?></h3>
        <div class="note-form">
            <div class="form-group">
                <label for="note-name"><?php esc_html_e('Note Name:', 'partner-ciu-manager'); ?> <span class="required">*</span></label>
                <input type="text"
                       id="note-name"
                       class="widefat"
                       placeholder="<?php esc_attr_e('e.g., Initial Meeting, Follow-up Call', 'partner-ciu-manager'); ?>" />
            </div>

            <div class="form-group">
                <label for="note-content"><?php esc_html_e('Note Content:', 'partner-ciu-manager'); ?> <span class="required">*</span></label>
                <textarea id="note-content"
                          class="widefat"
                          rows="5"
                          placeholder="<?php esc_attr_e('Enter your notes here...', 'partner-ciu-manager'); ?>"></textarea>
            </div>

            <button type="button" class="button button-primary" id="save-note-btn">
                <?php esc_html_e('Add Note', 'partner-ciu-manager'); ?>
            </button>
        </div>
    </div>

    <!-- Hidden field to store notes data -->
    <input type="hidden" name="partner_admin_notes_data" id="partner-admin-notes-data" value="<?php echo esc_attr(json_encode($notes)); ?>" />
</div>
