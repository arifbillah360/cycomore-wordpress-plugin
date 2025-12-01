<?php
/**
 * Email Notifications
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Partner_Email_Notifications
 */
class Partner_Email_Notifications {

    /**
     * Single instance
     *
     * @var Partner_Email_Notifications
     */
    protected static $instance = null;

    /**
     * Get instance
     *
     * @return Partner_Email_Notifications
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
        add_action('partner_ciu_purchase_completed', array($this, 'send_purchase_notifications'), 10, 4);
    }

    /**
     * Send purchase notifications
     *
     * @param int $partner_id
     * @param int $order_id
     * @param int $ciu_quantity
     * @param float $purchase_amount
     */
    public function send_purchase_notifications($partner_id, $order_id, $ciu_quantity, $purchase_amount) {
        if (!Partner_CIU_Settings::are_notifications_enabled()) {
            return;
        }

        // Send admin notification
        $this->send_admin_notification($partner_id, $order_id, $ciu_quantity, $purchase_amount);

        // Send partner confirmation
        $this->send_partner_confirmation($partner_id, $order_id, $ciu_quantity, $purchase_amount);
    }

    /**
     * Send admin notification
     *
     * @param int $partner_id
     * @param int $order_id
     * @param int $ciu_quantity
     * @param float $purchase_amount
     */
    private function send_admin_notification($partner_id, $order_id, $ciu_quantity, $purchase_amount) {
        $partner = get_post($partner_id);
        if (!$partner) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $admin_email = Partner_CIU_Settings::get_admin_email();
        $subject = sprintf(__('[%s] New CIU Purchase - %s', 'partner-ciu-manager'), get_bloginfo('name'), $partner->post_title);

        $data = array(
            'partner_name' => $partner->post_title,
            'ciu_quantity' => $ciu_quantity,
            'purchase_amount' => $purchase_amount,
            'order_id' => $order_id,
            'order_url' => admin_url('post.php?post=' . $order_id . '&action=edit'),
            'partner_url' => admin_url('post.php?post=' . $partner_id . '&action=edit'),
            'currency_symbol' => get_woocommerce_currency_symbol(),
        );

        $message = $this->get_email_template('admin-purchase-notification', $data);

        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($admin_email, $subject, $message, $headers);
    }

    /**
     * Send partner confirmation
     *
     * @param int $partner_id
     * @param int $order_id
     * @param int $ciu_quantity
     * @param float $purchase_amount
     */
    private function send_partner_confirmation($partner_id, $order_id, $ciu_quantity, $purchase_amount) {
        $partner = get_post($partner_id);
        if (!$partner) {
            return;
        }

        $user_id = get_post_meta($partner_id, '_partner_user_id', true);
        if (!$user_id) {
            return;
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $subject = sprintf(__('[%s] CIU Purchase Confirmation', 'partner-ciu-manager'), get_bloginfo('name'));

        $data = array(
            'partner_name' => $partner->post_title,
            'ciu_quantity' => $ciu_quantity,
            'purchase_amount' => $purchase_amount,
            'order_id' => $order_id,
            'order_url' => $order->get_view_order_url(),
            'dashboard_url' => home_url('/partner-dashboard/'),
            'currency_symbol' => get_woocommerce_currency_symbol(),
        );

        $message = $this->get_email_template('partner-purchase-confirmation', $data);

        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($user->user_email, $subject, $message, $headers);
    }

    /**
     * Get email template
     *
     * @param string $template
     * @param array $data
     * @return string
     */
    private function get_email_template($template, $data) {
        $template_path = PARTNER_CIU_PLUGIN_DIR . 'templates/emails/' . $template . '.php';

        if (!file_exists($template_path)) {
            return '';
        }

        ob_start();
        extract($data);
        include $template_path;
        return ob_get_clean();
    }
}
