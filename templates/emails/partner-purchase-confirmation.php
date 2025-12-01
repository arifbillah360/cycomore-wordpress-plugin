<?php
/**
 * Partner Purchase Confirmation Email Template
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
        <h2 style="color: #28a745; margin-top: 0;"><?php esc_html_e('Thank You for Your CIU Purchase!', 'partner-ciu-manager'); ?></h2>

        <p><?php printf(esc_html__('Dear %s,', 'partner-ciu-manager'), esc_html($partner_name)); ?></p>

        <p><?php esc_html_e('Thank you for your CIU purchase. Your order has been successfully processed.', 'partner-ciu-manager'); ?></p>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: #ffffff;">
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6; font-weight: bold;"><?php esc_html_e('CIU Quantity:', 'partner-ciu-manager'); ?></td>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><?php echo esc_html($ciu_quantity); ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6; font-weight: bold;"><?php esc_html_e('Total Amount:', 'partner-ciu-manager'); ?></td>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><?php echo esc_html($currency_symbol . number_format($purchase_amount, 2)); ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6; font-weight: bold;"><?php esc_html_e('Order Number:', 'partner-ciu-manager'); ?></td>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">#<?php echo esc_html($order_id); ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6; font-weight: bold;"><?php esc_html_e('Status:', 'partner-ciu-manager'); ?></td>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                    <span style="display: inline-block; background-color: #ffc107; color: #000; padding: 3px 8px; border-radius: 3px; font-size: 12px;">
                        <?php esc_html_e('Pending', 'partner-ciu-manager'); ?>
                    </span>
                </td>
            </tr>
        </table>

        <p><?php esc_html_e('Your CIUs have been added to your account with "Pending" status. Once they are allocated, their status will be updated accordingly.', 'partner-ciu-manager'); ?></p>

        <div style="margin-top: 30px;">
            <a href="<?php echo esc_url($dashboard_url); ?>" style="display: inline-block; background-color: #007bff; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 3px; margin-right: 10px;"><?php esc_html_e('View Dashboard', 'partner-ciu-manager'); ?></a>
            <a href="<?php echo esc_url($order_url); ?>" style="display: inline-block; background-color: #6c757d; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 3px;"><?php esc_html_e('View Order', 'partner-ciu-manager'); ?></a>
        </div>
    </div>

    <div style="background-color: #e9ecef; border-radius: 5px; padding: 15px; margin-top: 20px;">
        <p style="margin: 0; font-size: 14px;"><?php esc_html_e('If you have any questions about your purchase, please contact us.', 'partner-ciu-manager'); ?></p>
    </div>

    <p style="color: #6c757d; font-size: 12px; margin-top: 20px;">
        <?php printf(esc_html__('This email was sent from %s', 'partner-ciu-manager'), esc_html(get_bloginfo('name'))); ?>
    </p>
</body>
</html>
