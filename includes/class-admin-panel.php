<?php
/**
 * Admin Panel
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Partner_Admin_Panel
 */
class Partner_Admin_Panel {

    /**
     * Single instance
     *
     * @var Partner_Admin_Panel
     */
    protected static $instance = null;

    /**
     * Get instance
     *
     * @return Partner_Admin_Panel
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
        add_action('admin_menu', array($this, 'add_admin_pages'));
        add_action('wp_ajax_partner_allocate_cius', array($this, 'handle_ciu_allocation'));
        add_action('wp_ajax_partner_bulk_allocate_cius', array($this, 'handle_bulk_allocation'));
    }

    /**
     * Add admin pages
     */
    public function add_admin_pages() {
        // CIU Allocation page
        add_submenu_page(
            'edit.php?post_type=partner_profile',
            __('CIU Allocation', 'partner-ciu-manager'),
            __('CIU Allocation', 'partner-ciu-manager'),
            'manage_partners',
            'partner-ciu-allocation',
            array($this, 'render_ciu_allocation_page')
        );

        // Partner Sorting page
        add_submenu_page(
            'edit.php?post_type=partner_profile',
            __('Partner Sorting', 'partner-ciu-manager'),
            __('Partner Sorting', 'partner-ciu-manager'),
            'manage_partners',
            'partner-sorting',
            array($this, 'render_partner_sorting_page')
        );
    }

    /**
     * Render CIU allocation page
     */
    public function render_ciu_allocation_page() {
        if (!current_user_can('manage_partners')) {
            return;
        }

        include PARTNER_CIU_PLUGIN_DIR . 'admin/partials/ciu-allocation.php';
    }

    /**
     * Render partner sorting page
     */
    public function render_partner_sorting_page() {
        if (!current_user_can('manage_partners')) {
            return;
        }

        include PARTNER_CIU_PLUGIN_DIR . 'admin/partials/partner-sorting.php';
    }

    /**
     * Handle CIU allocation
     */
    public function handle_ciu_allocation() {
        // Check nonce
        check_ajax_referer('partner-ciu-admin-nonce', 'nonce');

        // Check permissions
        if (!current_user_can('manage_partners')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'partner-ciu-manager')));
        }

        // Get parameters
        $partner_id = isset($_POST['partner_id']) ? absint($_POST['partner_id']) : 0;
        $from_status = isset($_POST['from_status']) ? sanitize_text_field($_POST['from_status']) : '';
        $to_status = isset($_POST['to_status']) ? sanitize_text_field($_POST['to_status']) : '';
        $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 0;

        // Validate
        if (!$partner_id || !$from_status || !$to_status || !$quantity) {
            wp_send_json_error(array('message' => __('Invalid parameters.', 'partner-ciu-manager')));
        }

        // Get current count
        $from_meta_key = '_' . $from_status . '_cius';
        $to_meta_key = '_' . $to_status . '_cius';

        $from_count = get_post_meta($partner_id, $from_meta_key, true) ?: 0;

        if ($from_count < $quantity) {
            wp_send_json_error(array('message' => __('Not enough CIUs to allocate.', 'partner-ciu-manager')));
        }

        // Update counts
        update_post_meta($partner_id, $from_meta_key, max(0, $from_count - $quantity));

        $to_count = get_post_meta($partner_id, $to_meta_key, true) ?: 0;
        update_post_meta($partner_id, $to_meta_key, $to_count + $quantity);

        wp_send_json_success(array('message' => sprintf(__('%d CIUs allocated successfully.', 'partner-ciu-manager'), $quantity)));
    }

    /**
     * Handle bulk allocation
     */
    public function handle_bulk_allocation() {
        // Check nonce
        check_ajax_referer('partner-ciu-admin-nonce', 'nonce');

        // Check permissions
        if (!current_user_can('manage_partners')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'partner-ciu-manager')));
        }

        // Get parameters
        $from_status = isset($_POST['from_status']) ? sanitize_text_field($_POST['from_status']) : '';
        $to_status = isset($_POST['to_status']) ? sanitize_text_field($_POST['to_status']) : '';

        // Validate
        if (!$from_status || !$to_status) {
            wp_send_json_error(array('message' => __('Invalid parameters.', 'partner-ciu-manager')));
        }

        // Get all partners
        $partners = get_posts(array(
            'post_type' => 'partner_profile',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ));

        $total_allocated = 0;

        foreach ($partners as $partner) {
            $from_meta_key = '_' . $from_status . '_cius';
            $to_meta_key = '_' . $to_status . '_cius';

            $from_count = get_post_meta($partner->ID, $from_meta_key, true) ?: 0;

            if ($from_count > 0) {
                // Move all from status to to status
                update_post_meta($partner->ID, $from_meta_key, 0);

                $to_count = get_post_meta($partner->ID, $to_meta_key, true) ?: 0;
                update_post_meta($partner->ID, $to_meta_key, $to_count + $from_count);

                $total_allocated += $from_count;
            }
        }

        wp_send_json_success(array('message' => sprintf(__('%d CIUs allocated across all partners.', 'partner-ciu-manager'), $total_allocated)));
    }
}
