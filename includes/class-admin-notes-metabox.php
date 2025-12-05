<?php
/**
 * Admin Notes Meta Box
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Partner_Admin_Notes_Metabox
 */
class Partner_Admin_Notes_Metabox {

    /**
     * Single instance
     *
     * @var Partner_Admin_Notes_Metabox
     */
    protected static $instance = null;

    /**
     * Get instance
     *
     * @return Partner_Admin_Notes_Metabox
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('save_post_partner_profile', array($this, 'save_meta_box'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Add meta box
     */
    public function add_meta_box() {
        add_meta_box(
            'partner_admin_notes',
            __('Admin Notes', 'partner-ciu-manager'),
            array($this, 'render_meta_box'),
            'partner_profile',
            'normal',
            'high'
        );
    }

    /**
     * Render meta box
     */
    public function render_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('partner_admin_notes_nonce', 'partner_admin_notes_nonce_field');

        // Get existing notes
        $notes = get_post_meta($post->ID, 'partner_admin_notes', true);
        if (!is_array($notes)) {
            $notes = array();
        }

        // Include template
        include PARTNER_CIU_PLUGIN_DIR . 'templates/admin-notes-metabox.php';
    }

    /**
     * Save meta box
     */
    public function save_meta_box($post_id, $post) {
        // Check nonce
        if (!isset($_POST['partner_admin_notes_nonce_field']) ||
            !wp_verify_nonce($_POST['partner_admin_notes_nonce_field'], 'partner_admin_notes_nonce')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Get notes from POST data
        $notes_json = isset($_POST['partner_admin_notes_data']) ? wp_unslash($_POST['partner_admin_notes_data']) : '[]';
        $notes = json_decode($notes_json, true);

        if (!is_array($notes)) {
            $notes = array();
        }

        // Sanitize and validate notes
        $sanitized_notes = array();
        $current_user = wp_get_current_user();

        foreach ($notes as $note) {
            if (empty($note['note_name']) || empty($note['note_content'])) {
                continue;
            }

            $sanitized_note = array(
                'note_id' => isset($note['note_id']) ? absint($note['note_id']) : time() . rand(100, 999),
                'note_name' => sanitize_text_field($note['note_name']),
                'note_content' => sanitize_textarea_field($note['note_content']),
                'created_by' => isset($note['created_by']) ? sanitize_text_field($note['created_by']) : $current_user->display_name,
                'created_by_id' => isset($note['created_by_id']) ? absint($note['created_by_id']) : $current_user->ID,
                'created_date' => isset($note['created_date']) ? sanitize_text_field($note['created_date']) : current_time('mysql'),
                'last_modified' => current_time('mysql'),
                'modified_by' => $current_user->display_name,
                'modified_by_id' => $current_user->ID
            );

            $sanitized_notes[] = $sanitized_note;
        }

        // Save notes
        update_post_meta($post_id, 'partner_admin_notes', $sanitized_notes);
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts($hook) {
        global $post_type;

        if ($post_type !== 'partner_profile') {
            return;
        }

        if ($hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }

        wp_enqueue_style(
            'partner-admin-notes',
            PARTNER_CIU_PLUGIN_URL . 'assets/css/admin-notes.css',
            array(),
            PARTNER_CIU_VERSION
        );

        wp_enqueue_script(
            'partner-admin-notes',
            PARTNER_CIU_PLUGIN_URL . 'assets/js/admin-notes.js',
            array('jquery'),
            PARTNER_CIU_VERSION,
            true
        );

        wp_localize_script('partner-admin-notes', 'partnerAdminNotesData', array(
            'strings' => array(
                'confirmRemove' => __('Are you sure you want to delete this note?', 'partner-ciu-manager'),
                'remove' => __('Remove', 'partner-ciu-manager'),
                'edit' => __('Edit', 'partner-ciu-manager'),
                'noteName' => __('Note Name', 'partner-ciu-manager'),
                'noteContent' => __('Note Content', 'partner-ciu-manager'),
                'description' => __('Description (Optional)', 'partner-ciu-manager'),
            )
        ));
    }
}
