<?php
/**
 * CIU Allocation Frontend Display
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CIU_Frontend_Display
 */
class CIU_Frontend_Display {

    /**
     * Single instance
     *
     * @var CIU_Frontend_Display
     */
    protected static $instance = null;

    /**
     * Get instance
     *
     * @return CIU_Frontend_Display
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
        // Register shortcode
        add_shortcode('ciu_allocation_display', array($this, 'render_shortcode'));

        // Enqueue frontend scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        // Only enqueue if shortcode is present
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'ciu_allocation_display')) {
            wp_enqueue_style(
                'ciu-allocation-frontend',
                PARTNER_CIU_PLUGIN_URL . 'public/css/ciu-allocation-frontend.css',
                array(),
                PARTNER_CIU_VERSION
            );

            wp_enqueue_script(
                'ciu-allocation-frontend',
                PARTNER_CIU_PLUGIN_URL . 'public/js/ciu-allocation-frontend.js',
                array('jquery'),
                PARTNER_CIU_VERSION,
                true
            );
        }
    }

    /**
     * Render shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function render_shortcode($atts) {
        // Parse attributes
        $atts = shortcode_atts(array(
            'partner_id' => 0,
            'show_summary' => 'yes',
            'show_categories' => 'yes',
        ), $atts, 'ciu_allocation_display');

        // Get partner ID
        $partner_id = absint($atts['partner_id']);

        // If no partner_id specified, try to get current user's partner
        if ($partner_id === 0) {
            $partner_id = $this->get_current_user_partner_id();
        }

        // If still no partner_id, show all partners
        if ($partner_id === 0) {
            return $this->render_all_partners($atts);
        }

        // Get partner post
        $partner = get_post($partner_id);

        if (!$partner || $partner->post_type !== 'partner_profile') {
            return '<p class="ciu-error">' . esc_html__('Partner not found.', 'partner-ciu-manager') . '</p>';
        }

        // Get CIU allocation data
        $allocations = get_post_meta($partner_id, 'ciu_allocations', true);
        $summary = get_post_meta($partner_id, 'ciu_summary', true);

        if (empty($allocations)) {
            return '<p class="ciu-notice">' . esc_html__('No CIU allocation data available.', 'partner-ciu-manager') . '</p>';
        }

        // Start output buffering
        ob_start();

        // Include template
        include PARTNER_CIU_PLUGIN_DIR . 'templates/frontend-ciu-allocation.php';

        return ob_get_clean();
    }

    /**
     * Render all partners
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    private function render_all_partners($atts) {
        $args = array(
            'post_type' => 'partner_profile',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        );

        $partners = get_posts($args);

        if (empty($partners)) {
            return '<p class="ciu-notice">' . esc_html__('No partners found.', 'partner-ciu-manager') . '</p>';
        }

        ob_start();
        ?>
        <div class="ciu-all-partners">
            <h2><?php esc_html_e('All Partners CIU Allocation', 'partner-ciu-manager'); ?></h2>

            <?php foreach ($partners as $partner): ?>
                <?php
                $partner_id = $partner->ID;
                $allocations = get_post_meta($partner_id, 'ciu_allocations', true);
                $summary = get_post_meta($partner_id, 'ciu_summary', true);

                if (empty($allocations)) {
                    continue;
                }
                ?>

                <div class="ciu-partner-section">
                    <h3 class="ciu-partner-title"><?php echo esc_html($partner->post_title); ?></h3>
                    <?php include PARTNER_CIU_PLUGIN_DIR . 'templates/frontend-ciu-allocation.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get current user's partner ID
     *
     * @return int
     */
    private function get_current_user_partner_id() {
        if (!is_user_logged_in()) {
            return 0;
        }

        $user_id = get_current_user_id();

        // Find partner profile associated with this user
        $args = array(
            'post_type' => 'partner_profile',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => '_partner_user_id',
                    'value' => $user_id,
                )
            )
        );

        $partners = get_posts($args);

        return !empty($partners) ? $partners[0]->ID : 0;
    }

    /**
     * Get category icon HTML
     *
     * @param string $icon
     * @return string
     */
    public static function get_category_icon($icon) {
        return '<span class="ciu-category-icon">' . esc_html($icon) . '</span>';
    }

    /**
     * Get status badge HTML
     *
     * @param string $status
     * @return string
     */
    public static function get_status_badge($status) {
        $status_labels = array(
            'pending' => __('Pending', 'partner-ciu-manager'),
            'active' => __('Active', 'partner-ciu-manager'),
            'verified' => __('Verified/Retired', 'partner-ciu-manager'),
        );

        $label = isset($status_labels[$status]) ? $status_labels[$status] : $status;

        return '<span class="ciu-status-badge status-' . esc_attr($status) . '">' . esc_html($label) . '</span>';
    }

    /**
     * Format datetime for display
     *
     * @param string $datetime
     * @return string
     */
    public static function format_datetime($datetime) {
        if (empty($datetime)) {
            return '—';
        }

        $timestamp = strtotime($datetime);
        if (!$timestamp) {
            return esc_html($datetime);
        }

        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp);
    }
}
