<?php
/**
 * Plugin Name: Partner CIU Manager
 * Plugin URI: https://github.com/arifbillah360/cycomore-wordpress-plugin
 * Description: Simple WordPress system for partner profile management with basic partner information and settings.
 * Version: 2.0.0
 * Author: Cycomore
 * Author URI: https://cycomore.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: partner-ciu-manager
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PARTNER_CIU_VERSION', '2.0.0');
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
        require_once PARTNER_CIU_PLUGIN_DIR . 'includes/class-partner-role.php';
        require_once PARTNER_CIU_PLUGIN_DIR . 'includes/class-settings.php';
        require_once PARTNER_CIU_PLUGIN_DIR . 'includes/class-ciu-allocation-metabox.php';
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

        // Load text domain
        add_action('init', array($this, 'load_textdomain'));

        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create custom post types
        Partner_Post_Type::register();

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
        // Initialize post types
        Partner_Post_Type::instance();

        // Initialize settings
        Partner_CIU_Settings::instance();

        // Initialize CIU allocation metabox
        CIU_Allocation_Metabox::instance();
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
        // Only load on partner profile pages
        if (get_post_type() !== 'partner_profile') {
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
