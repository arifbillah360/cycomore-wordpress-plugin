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
        // Register shortcodes
        add_shortcode('ciu_allocation_display', array($this, 'render_shortcode'));
        add_shortcode('ciu_partners', array($this, 'render_partners_shortcode'));

        // Enqueue frontend scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        // Always enqueue on frontend (WordPress will handle caching)
        // The conditional check was causing issues where scripts wouldn't load
        if (!is_admin()) {
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

            // Enqueue category scroll script for horizontal scrolling arrows
            wp_enqueue_script(
                'category-scroll',
                PARTNER_CIU_PLUGIN_URL . 'public/js/category-scroll.js',
                array('jquery'),
                PARTNER_CIU_VERSION,
                true
            );

            // Localize script with AJAX URL and debug flag
            wp_localize_script('ciu-allocation-frontend', 'ciuFrontendData', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ciu-frontend-nonce'),
                'debug' => true, // Enable debugging
            ));
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
     * Render partners shortcode (list or detail view)
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function render_partners_shortcode($atts) {
        // Parse attributes
        $atts = shortcode_atts(array(
            'view' => 'auto', // auto, list, detail
            'columns' => '3', // For grid view: 2, 3, or 4
            'show_search' => 'yes',
            'show_filter' => 'no',
        ), $atts, 'ciu_partners');

        // Check if viewing specific partner via URL parameter
        $partner_id = isset($_GET['partner']) ? absint($_GET['partner']) : 0;

        // Determine view
        if ($atts['view'] === 'auto') {
            $view = ($partner_id > 0) ? 'detail' : 'list';
        } else {
            $view = $atts['view'];
        }

        // Render appropriate view
        if ($view === 'detail' && $partner_id > 0) {
            return $this->render_partner_detail($partner_id, $atts);
        } else {
            return $this->render_partner_list($atts);
        }
    }

    /**
     * Render partner list (grid/cards view)
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    private function render_partner_list($atts) {
        // Get all partners
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
        include PARTNER_CIU_PLUGIN_DIR . 'templates/frontend-partner-list.php';
        return ob_get_clean();
    }

    /**
     * Render single partner detail view
     *
     * @param int $partner_id Partner ID
     * @param array $atts Shortcode attributes
     * @return string
     */
    private function render_partner_detail($partner_id, $atts) {
        $partner = get_post($partner_id);

        if (!$partner || $partner->post_type !== 'partner_profile') {
            return '<p class="ciu-error">' . esc_html__('Partner not found.', 'partner-ciu-manager') . '</p>';
        }

        // Get CIU allocation data
        $allocations = get_post_meta($partner_id, 'ciu_allocations', true);
        $summary = get_post_meta($partner_id, 'ciu_summary', true);

        // Force show both summary and categories for detail view
        $atts['show_summary'] = 'yes';
        $atts['show_categories'] = 'yes';

        ob_start();
        ?>
        <div class="ciu-partner-detail-wrapper">
            <div class="ciu-partner-detail-header">
                <a href="<?php echo esc_url(remove_query_arg('partner')); ?>" class="ciu-back-button">
                    <span class="back-arrow">←</span>
                    <?php esc_html_e('Back to Partners', 'partner-ciu-manager'); ?>
                </a>
            </div>

            <?php if (empty($allocations) && empty($partner_id)): ?>
                <p class="ciu-notice"><?php esc_html_e('No CIU allocation data available for this partner.', 'partner-ciu-manager'); ?></p>
            <?php else: ?>
                <?php include PARTNER_CIU_PLUGIN_DIR . 'templates/frontend-ciu-allocation.php'; ?>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
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
