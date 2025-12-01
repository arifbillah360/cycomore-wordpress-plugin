<?php
/**
 * Admin Purchase Notification Email Template
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo esc_html(get_bloginfo('name')); ?></title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 20px; margin-bottom: 20px;">
        <h2 style="color: #007bff; margin-top: 0;"><?php esc_html_e('New CIU Purchase', 'partner-ciu-manager'); ?></h2>

        <p><?php esc_html_e('A partner has purchased CIUs. Here are the details:', 'partner-ciu-manager'); ?></p>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6; font-weight: bold;"><?php esc_html_e('Partner:', 'partner-ciu-manager'); ?></td>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><?php echo esc_html($partner_name); ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6; font-weight: bold;"><?php esc_html_e('CIU Quantity:', 'partner-ciu-manager'); ?></td>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><?php echo esc_html($ciu_quantity); ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6; font-weight: bold;"><?php esc_html_e('Purchase Amount:', 'partner-ciu-manager'); ?></td>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><?php echo esc_html($currency_symbol . number_format($purchase_amount, 2)); ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6; font-weight: bold;"><?php esc_html_e('Order ID:', 'partner-ciu-manager'); ?></td>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">#<?php echo esc_html($order_id); ?></td>
            </tr>
        </table>

        <div style="margin-top: 30px;">
            <a href="<?php echo esc_url($partner_url); ?>" style="display: inline-block; background-color: #007bff; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 3px; margin-right: 10px;"><?php esc_html_e('View Partner Profile', 'partner-ciu-manager'); ?></a>
            <a href="<?php echo esc_url($order_url); ?>" style="display: inline-block; background-color: #28a745; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 3px;"><?php esc_html_e('View Order', 'partner-ciu-manager'); ?></a>
        </div>
    </div>

    <p style="color: #6c757d; font-size: 12px; margin-top: 20px;">
        <?php printf(esc_html__('This email was sent from %s', 'partner-ciu-manager'), esc_html(get_bloginfo('name'))); ?>
    </p>
</body>
</html>
