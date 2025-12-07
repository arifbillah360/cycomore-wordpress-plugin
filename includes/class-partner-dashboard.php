<?php
/**
 * Partner Dashboard
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Partner_Dashboard
 */
class Partner_Dashboard {

    /**
     * Single instance
     *
     * @var Partner_Dashboard
     */
    protected static $instance = null;

    /**
     * Get instance
     *
     * @return Partner_Dashboard
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
        add_shortcode('partner_dashboard', array($this, 'render_dashboard'));
        add_action('wp_ajax_partner_update_profile', array($this, 'handle_profile_update'));
    }

    /**
     * Render dashboard
     *
     * @return string
     */
    public function render_dashboard($atts) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return '<div class="partner-ciu-notice">' . __('Please log in to access your partner dashboard.', 'partner-ciu-manager') . '</div>';
        }

        // Get partner profile
        $partner_profile = Partner_Role::get_partner_profile();
        if (!$partner_profile) {
            return '<div class="partner-ciu-notice">' . __('No partner profile found for your account. Please contact the administrator.', 'partner-ciu-manager') . '</div>';
        }

        ob_start();
        include PARTNER_CIU_PLUGIN_DIR . 'public/partials/partner-dashboard.php';
        return ob_get_clean();
    }

    /**
     * Handle profile update
     */
    public function handle_profile_update() {
        // Check nonce
        check_ajax_referer('partner-ciu-public-nonce', 'nonce');

        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in.', 'partner-ciu-manager')));
        }

        // Get partner profile
        $partner_profile = Partner_Role::get_partner_profile();
        if (!$partner_profile) {
            wp_send_json_error(array('message' => __('No partner profile found.', 'partner-ciu-manager')));
        }

        // Update bank details
        if (isset($_POST['bank_name'])) {
            update_post_meta($partner_profile->ID, '_bank_name', sanitize_text_field($_POST['bank_name']));
        }
        if (isset($_POST['account_number'])) {
            update_post_meta($partner_profile->ID, '_account_number', sanitize_text_field($_POST['account_number']));
        }
        if (isset($_POST['sort_code'])) {
            update_post_meta($partner_profile->ID, '_sort_code', sanitize_text_field($_POST['sort_code']));
        }

        // Update logo
        if (isset($_POST['partner_logo'])) {
            update_post_meta($partner_profile->ID, '_partner_logo', absint($_POST['partner_logo']));
        }

        wp_send_json_success(array('message' => __('Profile updated successfully.', 'partner-ciu-manager')));
    }

    /**
     * Get partner data
     *
     * @param WP_Post $partner_profile
     * @return array
     */
    public static function get_partner_data($partner_profile) {
        $data = array(
            'id' => $partner_profile->ID,
            'name' => $partner_profile->post_title,
            'logo' => get_post_meta($partner_profile->ID, '_partner_logo', true),
            'bank_name' => get_post_meta($partner_profile->ID, '_bank_name', true),
            'account_number' => get_post_meta($partner_profile->ID, '_account_number', true),
            'sort_code' => get_post_meta($partner_profile->ID, '_sort_code', true),
            'is_hero' => get_post_meta($partner_profile->ID, '_is_hero_partner', true),
            'hero_highlight' => get_post_meta($partner_profile->ID, '_hero_highlight_text', true),
            'pending_cius' => get_post_meta($partner_profile->ID, '_pending_cius', true) ?: 0,
            'active_cius' => get_post_meta($partner_profile->ID, '_active_cius', true) ?: 0,
            'verified_cius' => get_post_meta($partner_profile->ID, '_verified_cius', true) ?: 0,
            'total_funds' => get_post_meta($partner_profile->ID, '_total_funds', true) ?: 0,
            'last_purchase_date' => get_post_meta($partner_profile->ID, '_last_purchase_date', true),
            'collections' => get_post_meta($partner_profile->ID, '_collection_breakdown', true),
        );

        $data['total_cius'] = $data['pending_cius'] + $data['active_cius'] + $data['verified_cius'];

        return $data;
    }
}
