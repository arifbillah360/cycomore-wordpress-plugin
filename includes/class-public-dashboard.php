<?php
/**
 * Public CIU Dashboard
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Partner_Public_Dashboard
 */
class Partner_Public_Dashboard {

    /**
     * Single instance
     *
     * @var Partner_Public_Dashboard
     */
    protected static $instance = null;

    /**
     * Data aggregator instance
     *
     * @var Partner_Data_Aggregator
     */
    private $aggregator;

    /**
     * Get instance
     *
     * @return Partner_Public_Dashboard
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
        $this->aggregator = Partner_Data_Aggregator::instance();

        add_shortcode('public_ciu_dashboard', array($this, 'render_dashboard'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('init', array($this, 'create_dashboard_page'));

        // AJAX handlers
        add_action('wp_ajax_nopriv_get_partners_data', array($this, 'ajax_get_partners_data'));
        add_action('wp_ajax_get_partners_data', array($this, 'ajax_get_partners_data'));

        add_action('wp_ajax_nopriv_get_chart_data', array($this, 'ajax_get_chart_data'));
        add_action('wp_ajax_get_chart_data', array($this, 'ajax_get_chart_data'));

        // Clear cache when partner data changes
        add_action('save_post_partner_profile', array($this, 'clear_cache'));
        add_action('save_post_ciu_transaction', array($this, 'clear_cache'));
    }

    /**
     * Create dashboard page on plugin activation
     */
    public function create_dashboard_page() {
        // Check if page already exists
        $page_slug = 'ciu-transformation';
        $page_check = get_page_by_path($page_slug);

        if ($page_check) {
            return;
        }

        // Check if we should create the page
        if (!get_option('partner_ciu_create_public_page', false)) {
            return;
        }

        // Create the page
        $page_id = wp_insert_post(array(
            'post_title' => __('CIU Transformation Journey', 'partner-ciu-manager'),
            'post_content' => '[public_ciu_dashboard]',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => $page_slug,
            'comment_status' => 'closed',
            'ping_status' => 'closed',
        ));

        if ($page_id) {
            update_option('partner_ciu_public_page_id', $page_id);
            delete_option('partner_ciu_create_public_page');
        }
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // Only enqueue on pages with the shortcode
        global $post;
        if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'public_ciu_dashboard')) {
            return;
        }

        // Chart.js
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
            array(),
            '4.4.0',
            true
        );

        // DataTables
        wp_enqueue_style(
            'datatables',
            'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css',
            array(),
            '1.13.6'
        );

        wp_enqueue_script(
            'datatables',
            'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
            array('jquery'),
            '1.13.6',
            true
        );

        // AOS (Animate On Scroll)
        wp_enqueue_style(
            'aos',
            'https://unpkg.com/aos@2.3.1/dist/aos.css',
            array(),
            '2.3.1'
        );

        wp_enqueue_script(
            'aos',
            'https://unpkg.com/aos@2.3.1/dist/aos.js',
            array(),
            '2.3.1',
            true
        );

        // Font Awesome
        wp_enqueue_style(
            'font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
            array(),
            '6.4.0'
        );

        // Public dashboard styles
        wp_enqueue_style(
            'partner-ciu-public-dashboard',
            PARTNER_CIU_PLUGIN_URL . 'assets/css/public-dashboard.css',
            array(),
            PARTNER_CIU_VERSION
        );

        // Public dashboard scripts
        wp_enqueue_script(
            'partner-ciu-public-dashboard',
            PARTNER_CIU_PLUGIN_URL . 'assets/js/public-dashboard.js',
            array('jquery', 'chartjs', 'datatables', 'aos'),
            PARTNER_CIU_VERSION,
            true
        );

        // Charts script
        wp_enqueue_script(
            'partner-ciu-charts',
            PARTNER_CIU_PLUGIN_URL . 'assets/js/charts.js',
            array('jquery', 'chartjs'),
            PARTNER_CIU_VERSION,
            true
        );

        // Filters script
        wp_enqueue_script(
            'partner-ciu-filters',
            PARTNER_CIU_PLUGIN_URL . 'assets/js/filters.js',
            array('jquery', 'datatables'),
            PARTNER_CIU_VERSION,
            true
        );

        // Localize scripts
        wp_localize_script('partner-ciu-public-dashboard', 'partnerCiuPublicDashboard', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('partner-ciu-public-nonce'),
            'currencySymbol' => get_woocommerce_currency_symbol(),
            'strings' => array(
                'loading' => __('Loading...', 'partner-ciu-manager'),
                'error' => __('An error occurred. Please try again.', 'partner-ciu-manager'),
                'noResults' => __('No results found.', 'partner-ciu-manager'),
            ),
        ));
    }

    /**
     * Render dashboard shortcode
     *
     * @param array $atts
     * @return string
     */
    public function render_dashboard($atts) {
        $atts = shortcode_atts(array(
            'show_charts' => 'true',
            'show_partners' => 'true',
            'show_collections' => 'true',
            'show_timeline' => 'true',
            'partners_per_page' => '12',
            'default_sort' => 'total_cius',
        ), $atts);

        // Get all data
        $data = $this->aggregator->get_all_data();

        // Get settings
        $settings = $this->get_dashboard_settings();

        ob_start();
        include PARTNER_CIU_PLUGIN_DIR . 'templates/public-ciu-dashboard.php';
        return ob_get_clean();
    }

    /**
     * AJAX handler for getting partners data
     */
    public function ajax_get_partners_data() {
        check_ajax_referer('partner-ciu-public-nonce', 'nonce');

        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $status_filter = isset($_POST['status_filter']) ? sanitize_text_field($_POST['status_filter']) : '';
        $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'total_cius';
        $order = isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'DESC';
        $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? absint($_POST['per_page']) : 12;

        $partners = $this->aggregator->get_partners_data(array(
            'search' => $search,
            'status_filter' => $status_filter,
            'orderby' => $orderby,
            'order' => $order,
            'page' => $page,
            'per_page' => $per_page,
        ));

        wp_send_json_success(array(
            'partners' => $partners,
            'total' => count($this->aggregator->get_partners_data()),
        ));
    }

    /**
     * AJAX handler for getting chart data
     */
    public function ajax_get_chart_data() {
        check_ajax_referer('partner-ciu-public-nonce', 'nonce');

        $chart_type = isset($_POST['chart_type']) ? sanitize_text_field($_POST['chart_type']) : 'status';

        $data = array();

        switch ($chart_type) {
            case 'status':
                $totals = $this->aggregator->get_totals();
                $data = array(
                    'labels' => array('Pending', 'Active', 'Verified'),
                    'values' => array(
                        $totals['pending_cius'],
                        $totals['active_cius'],
                        $totals['verified_cius'],
                    ),
                    'colors' => array('#FFA500', '#2196F3', '#4CAF50'),
                );
                break;

            case 'top_partners':
                $top_partners = $this->aggregator->get_top_partners(10);
                $data = array(
                    'labels' => array_column($top_partners, 'name'),
                    'values' => array_column($top_partners, 'total_cius'),
                );
                break;

            case 'timeline':
                $timeline = $this->aggregator->get_timeline_data(12);
                $data = array(
                    'labels' => array_column($timeline, 'label'),
                    'values' => array_column($timeline, 'cius_purchased'),
                );
                break;

            case 'collections':
                $collections = $this->aggregator->get_collections_data();
                $data = array(
                    'labels' => array_column($collections, 'name'),
                    'values' => array_column($collections, 'total_cius'),
                );
                break;
        }

        wp_send_json_success($data);
    }

    /**
     * Get dashboard settings
     *
     * @return array
     */
    private function get_dashboard_settings() {
        $defaults = array(
            'enabled' => true,
            'show_hero_section' => true,
            'show_flow_visualization' => true,
            'show_partners_table' => true,
            'show_collections' => true,
            'show_timeline' => true,
            'show_charts' => true,
            'page_slug' => 'ciu-transformation',
            'refresh_interval' => 3600,
        );

        $settings = get_option('partner_ciu_public_dashboard_settings', array());

        return wp_parse_args($settings, $defaults);
    }

    /**
     * Clear cache
     */
    public function clear_cache() {
        $this->aggregator->clear_cache();
    }

    /**
     * Get formatted totals for display
     *
     * @param array $totals
     * @return array
     */
    public static function format_totals($totals) {
        return array(
            'total_cius' => number_format($totals['total_cius']),
            'pending_cius' => number_format($totals['pending_cius']),
            'active_cius' => number_format($totals['active_cius']),
            'verified_cius' => number_format($totals['verified_cius']),
            'total_funds' => get_woocommerce_currency_symbol() . number_format($totals['total_funds'], 2),
            'active_partners' => $totals['active_partners'],
            'pending_percentage' => $totals['pending_percentage'],
            'active_percentage' => $totals['active_percentage'],
            'verified_percentage' => $totals['verified_percentage'],
        );
    }
}
