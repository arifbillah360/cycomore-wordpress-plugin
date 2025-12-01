<?php
/**
 * Plugin Name: Partner CIU Manager
 * Plugin URI: https://github.com/arifbillah360/cycomore-wordpress-plugin
 * Description: Complete WordPress + WooCommerce system for partner management and CIU (Cumulative Impact Unit) purchasing with partner dashboards, admin tools, and specialized purchasing workflow.
 * Version: 1.0.0
 * Author: Cycomore
 * Author URI: https://cycomore.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: partner-ciu-manager
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 8.0
 * WC tested up to: 9.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PARTNER_CIU_VERSION', '1.0.0');
define('PARTNER_CIU_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PARTNER_CIU_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PARTNER_CIU_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class Partner_CIU_Manager {

    /**
     * Single instance of the class
     *
     * @var Partner_CIU_Manager
     */
    protected static $instance = null;

    /**
     * Get single instance
     *
     * @return Partner_CIU_Manager
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
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Include required files
     */
    private function includes() {
        // Core classes
        require_once PARTNER_CIU_PLUGIN_DIR . 'includes/class-partner-post-type.php';
        require_once PARTNER_CIU_PLUGIN_DIR . 'includes/class-ciu-transaction-post-type.php';
        require_once PARTNER_CIU_PLUGIN_DIR . 'includes/class-partner-role.php';
        require_once PARTNER_CIU_PLUGIN_DIR . 'includes/class-settings.php';
        require_once PARTNER_CIU_PLUGIN_DIR . 'includes/class-partner-dashboard.php';
        require_once PARTNER_CIU_PLUGIN_DIR . 'includes/class-woocommerce-integration.php';
        require_once PARTNER_CIU_PLUGIN_DIR . 'includes/class-email-notifications.php';
        require_once PARTNER_CIU_PLUGIN_DIR . 'includes/class-admin-panel.php';
        require_once PARTNER_CIU_PLUGIN_DIR . 'includes/class-partner-sorting.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Initialize plugin
        add_action('plugins_loaded', array($this, 'init'));

        // Check for WooCommerce dependency
        add_action('admin_notices', array($this, 'check_woocommerce_dependency'));

        // Load text domain
        add_action('init', array($this, 'load_textdomain'));

        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'public_enqueue_scripts'));
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create custom post types
        Partner_Post_Type::register();
        CIU_Transaction_Post_Type::register();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Create partner role
        Partner_Role::create_role();

        // Create default settings
        $this->create_default_settings();

        // Set activation flag
        set_transient('partner_ciu_manager_activated', true, 30);
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Initialize plugin
     */
    public function init() {
        if (!$this->is_woocommerce_active()) {
            return;
        }

        // Initialize post types
        Partner_Post_Type::instance();
        CIU_Transaction_Post_Type::instance();

        // Initialize settings
        Partner_CIU_Settings::instance();

        // Initialize dashboard
        Partner_Dashboard::instance();

        // Initialize WooCommerce integration
        Partner_WooCommerce_Integration::instance();

        // Initialize email notifications
        Partner_Email_Notifications::instance();

        // Initialize admin panel
        Partner_Admin_Panel::instance();

        // Initialize partner sorting
        Partner_Sorting::instance();
    }

    /**
     * Check if WooCommerce is active
     *
     * @return bool
     */
    private function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }

    /**
     * Display admin notice if WooCommerce is not active
     */
    public function check_woocommerce_dependency() {
        if (!$this->is_woocommerce_active()) {
            ?>
            <div class="error">
                <p><?php esc_html_e('Partner CIU Manager requires WooCommerce to be installed and active.', 'partner-ciu-manager'); ?></p>
            </div>
            <?php
        }
    }

    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain('partner-ciu-manager', false, dirname(PARTNER_CIU_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'partner-ciu') === false && get_post_type() !== 'partner_profile' && get_post_type() !== 'ciu_transaction') {
            return;
        }

        wp_enqueue_style('partner-ciu-admin', PARTNER_CIU_PLUGIN_URL . 'admin/css/admin.css', array(), PARTNER_CIU_VERSION);
        wp_enqueue_script('partner-ciu-admin', PARTNER_CIU_PLUGIN_URL . 'admin/js/admin.js', array('jquery', 'jquery-ui-sortable'), PARTNER_CIU_VERSION, true);

        wp_localize_script('partner-ciu-admin', 'partnerCiuAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('partner-ciu-admin-nonce'),
            'strings' => array(
                'confirmDelete' => __('Are you sure you want to delete this?', 'partner-ciu-manager'),
                'savingOrder' => __('Saving order...', 'partner-ciu-manager'),
                'orderSaved' => __('Order saved successfully!', 'partner-ciu-manager'),
            )
        ));
    }

    /**
     * Enqueue public scripts and styles
     */
    public function public_enqueue_scripts() {
        if (is_user_logged_in() && current_user_can('partner')) {
            wp_enqueue_style('partner-ciu-public', PARTNER_CIU_PLUGIN_URL . 'public/css/public.css', array(), PARTNER_CIU_VERSION);
            wp_enqueue_script('partner-ciu-public', PARTNER_CIU_PLUGIN_URL . 'public/js/public.js', array('jquery'), PARTNER_CIU_VERSION, true);

            wp_localize_script('partner-ciu-public', 'partnerCiuPublic', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('partner-ciu-public-nonce'),
                'ciuPrice' => Partner_CIU_Settings::get_ciu_price(),
                'currencySymbol' => get_woocommerce_currency_symbol(),
                'strings' => array(
                    'calculating' => __('Calculating...', 'partner-ciu-manager'),
                    'total' => __('Total:', 'partner-ciu-manager'),
                )
            ));
        }
    }

    /**
     * Create default settings
     */
    private function create_default_settings() {
        if (!get_option('partner_ciu_settings')) {
            $default_settings = array(
                'ciu_price' => 250,
                'currency' => 'GBP',
                'admin_email' => get_option('admin_email'),
                'enable_notifications' => true,
            );
            update_option('partner_ciu_settings', $default_settings);
        }
    }
}

/**
 * Returns the main instance of Partner_CIU_Manager
 *
 * @return Partner_CIU_Manager
 */
function partner_ciu_manager() {
    return Partner_CIU_Manager::instance();
}

// Initialize the plugin
partner_ciu_manager();
