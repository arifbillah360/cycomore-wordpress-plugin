<?php
/**
 * Settings Management
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Partner_CIU_Settings
 */
class Partner_CIU_Settings {

    /**
     * Single instance
     *
     * @var Partner_CIU_Settings
     */
    protected static $instance = null;

    /**
     * Get instance
     *
     * @return Partner_CIU_Settings
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
        add_action('admin_menu', array($this, 'add_settings_page'), 99);
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Add settings page
     */
    public function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=partner_profile',
            __('Settings', 'partner-ciu-manager'),
            __('Settings', 'partner-ciu-manager'),
            'manage_options',
            'partner-ciu-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('partner_ciu_settings_group', 'partner_ciu_settings', array($this, 'sanitize_settings'));

        // General settings section
        add_settings_section(
            'partner_ciu_general_section',
            __('General Settings', 'partner-ciu-manager'),
            array($this, 'general_section_callback'),
            'partner-ciu-settings'
        );

        add_settings_field(
            'ciu_price',
            __('CIU Price', 'partner-ciu-manager'),
            array($this, 'ciu_price_callback'),
            'partner-ciu-settings',
            'partner_ciu_general_section'
        );

        add_settings_field(
            'currency',
            __('Currency', 'partner-ciu-manager'),
            array($this, 'currency_callback'),
            'partner-ciu-settings',
            'partner_ciu_general_section'
        );

        // Notification settings section
        add_settings_section(
            'partner_ciu_notification_section',
            __('Notification Settings', 'partner-ciu-manager'),
            array($this, 'notification_section_callback'),
            'partner-ciu-settings'
        );

        add_settings_field(
            'enable_notifications',
            __('Enable Notifications', 'partner-ciu-manager'),
            array($this, 'enable_notifications_callback'),
            'partner-ciu-settings',
            'partner_ciu_notification_section'
        );

        add_settings_field(
            'admin_email',
            __('Admin Email', 'partner-ciu-manager'),
            array($this, 'admin_email_callback'),
            'partner-ciu-settings',
            'partner_ciu_notification_section'
        );
    }

    /**
     * General section callback
     */
    public function general_section_callback() {
        echo '<p>' . esc_html__('Configure general plugin settings.', 'partner-ciu-manager') . '</p>';
    }

    /**
     * Notification section callback
     */
    public function notification_section_callback() {
        echo '<p>' . esc_html__('Configure email notification settings.', 'partner-ciu-manager') . '</p>';
    }

    /**
     * CIU price callback
     */
    public function ciu_price_callback() {
        $settings = get_option('partner_ciu_settings', array());
        $ciu_price = isset($settings['ciu_price']) ? $settings['ciu_price'] : 250;
        ?>
        <input type="number" name="partner_ciu_settings[ciu_price]" value="<?php echo esc_attr($ciu_price); ?>" min="0" step="0.01" class="regular-text">
        <p class="description"><?php esc_html_e('Default price per CIU (e.g., 250 for £250)', 'partner-ciu-manager'); ?></p>
        <?php
    }

    /**
     * Currency callback
     */
    public function currency_callback() {
        $settings = get_option('partner_ciu_settings', array());
        $currency = isset($settings['currency']) ? $settings['currency'] : 'GBP';
        ?>
        <input type="text" name="partner_ciu_settings[currency]" value="<?php echo esc_attr($currency); ?>" class="regular-text">
        <p class="description"><?php esc_html_e('Currency code (e.g., GBP, USD, EUR)', 'partner-ciu-manager'); ?></p>
        <?php
    }

    /**
     * Enable notifications callback
     */
    public function enable_notifications_callback() {
        $settings = get_option('partner_ciu_settings', array());
        $enable_notifications = isset($settings['enable_notifications']) ? $settings['enable_notifications'] : true;
        ?>
        <label>
            <input type="checkbox" name="partner_ciu_settings[enable_notifications]" value="1" <?php checked($enable_notifications, true); ?>>
            <?php esc_html_e('Send email notifications for CIU purchases', 'partner-ciu-manager'); ?>
        </label>
        <?php
    }

    /**
     * Admin email callback
     */
    public function admin_email_callback() {
        $settings = get_option('partner_ciu_settings', array());
        $admin_email = isset($settings['admin_email']) ? $settings['admin_email'] : get_option('admin_email');
        ?>
        <input type="email" name="partner_ciu_settings[admin_email]" value="<?php echo esc_attr($admin_email); ?>" class="regular-text">
        <p class="description"><?php esc_html_e('Email address to receive admin notifications', 'partner-ciu-manager'); ?></p>
        <?php
    }

    /**
     * Sanitize settings
     *
     * @param array $input
     * @return array
     */
    public function sanitize_settings($input) {
        $sanitized = array();

        if (isset($input['ciu_price'])) {
            $sanitized['ciu_price'] = floatval($input['ciu_price']);
        }

        if (isset($input['currency'])) {
            $sanitized['currency'] = sanitize_text_field($input['currency']);
        }

        if (isset($input['enable_notifications'])) {
            $sanitized['enable_notifications'] = (bool) $input['enable_notifications'];
        } else {
            $sanitized['enable_notifications'] = false;
        }

        if (isset($input['admin_email'])) {
            $sanitized['admin_email'] = sanitize_email($input['admin_email']);
        }

        return $sanitized;
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Handle form submission
        if (isset($_GET['settings-updated'])) {
            add_settings_error('partner_ciu_settings_messages', 'partner_ciu_settings_message', __('Settings saved.', 'partner-ciu-manager'), 'updated');
        }

        settings_errors('partner_ciu_settings_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('partner_ciu_settings_group');
                do_settings_sections('partner-ciu-settings');
                submit_button(__('Save Settings', 'partner-ciu-manager'));
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Get CIU price
     *
     * @return float
     */
    public static function get_ciu_price() {
        $settings = get_option('partner_ciu_settings', array());
        return isset($settings['ciu_price']) ? floatval($settings['ciu_price']) : 250.0;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public static function get_currency() {
        $settings = get_option('partner_ciu_settings', array());
        return isset($settings['currency']) ? $settings['currency'] : 'GBP';
    }

    /**
     * Get admin email
     *
     * @return string
     */
    public static function get_admin_email() {
        $settings = get_option('partner_ciu_settings', array());
        return isset($settings['admin_email']) ? $settings['admin_email'] : get_option('admin_email');
    }

    /**
     * Are notifications enabled
     *
     * @return bool
     */
    public static function are_notifications_enabled() {
        $settings = get_option('partner_ciu_settings', array());
        return isset($settings['enable_notifications']) ? (bool) $settings['enable_notifications'] : true;
    }
}
