<?php
/**
 * Migration: Remove Transaction Features
 *
 * This migration removes all transaction-related data from the database
 * when transitioning from the transaction-based system to admin-only
 * CIU allocation.
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Partner_CIU_Migration_Remove_Transactions
 */
class Partner_CIU_Migration_Remove_Transactions {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_notices', array($this, 'show_migration_notice'));
        add_action('admin_init', array($this, 'handle_migration'));
        add_action('admin_menu', array($this, 'add_migration_page'), 999);
    }

    /**
     * Add hidden migration page
     */
    public function add_migration_page() {
        add_submenu_page(
            null, // Hidden from menu
            'CIU Migration',
            'CIU Migration',
            'manage_partner_ciu',
            'ciu-migration',
            array($this, 'render_migration_page')
        );
    }

    /**
     * Show migration notice
     */
    public function show_migration_notice() {
        $migration_done = get_option('ciu_transactions_removed', false);
        $screen = get_current_screen();

        if (!$migration_done && current_user_can('manage_partner_ciu') && $screen && $screen->post_type === 'partner_profile') {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong>Partner CIU Manager - Migration Required:</strong>
                    Transaction and WooCommerce features have been removed from this plugin.
                    <a href="<?php echo admin_url('admin.php?page=ciu-migration'); ?>" class="button button-primary">
                        Run Migration to Clean Up Data
                    </a>
                </p>
                <p>
                    <em>This will remove all transaction records and WooCommerce-related data. Partner profiles and CIU allocations will be preserved.</em>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Render migration page
     */
    public function render_migration_page() {
        $migration_done = get_option('ciu_transactions_removed', false);

        ?>
        <div class="wrap">
            <h1>Partner CIU Manager - Transaction Removal Migration</h1>

            <?php if ($migration_done): ?>
                <div class="notice notice-success">
                    <p><strong>Migration Complete!</strong> All transaction data has been removed.</p>
                    <p><a href="<?php echo admin_url('edit.php?post_type=partner_profile'); ?>" class="button">Return to Partners</a></p>
                </div>
            <?php else: ?>
                <div class="notice notice-info">
                    <p><strong>About This Migration:</strong></p>
                    <ul>
                        <li>Removes all CIU Transaction post types and their metadata</li>
                        <li>Removes WooCommerce CIU product (if exists)</li>
                        <li>Cleans transaction-related meta from partner profiles</li>
                        <li><strong>Preserves:</strong> All partner profiles, CIU allocations, and admin notes</li>
                    </ul>
                </div>

                <h2>Migration Preview</h2>
                <?php $this->show_migration_preview(); ?>

                <form method="post" action="" style="margin-top: 20px;">
                    <?php wp_nonce_field('ciu_migration_nonce', 'migration_nonce'); ?>
                    <input type="hidden" name="run_migration" value="1" />
                    <p>
                        <button type="submit" class="button button-primary button-large"
                                onclick="return confirm('Are you sure you want to run this migration? This action cannot be undone.');">
                            Run Migration Now
                        </button>
                    </p>
                </form>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Show migration preview
     */
    private function show_migration_preview() {
        global $wpdb;

        // Count transactions
        $transaction_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'ciu_transaction'"
        );

        // Count partners with transaction meta
        $partners_with_transactions = $wpdb->get_var(
            "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta}
             WHERE meta_key IN ('total_funds_contributed', 'last_purchase_date', 'transaction_count', 'woocommerce_customer_id')"
        );

        // Check for WooCommerce product
        $woo_product_id = get_option('woocommerce_ciu_product_id');

        ?>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Count</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>CIU Transaction Posts</td>
                    <td><strong><?php echo number_format($transaction_count); ?></strong></td>
                    <td><?php echo $transaction_count > 0 ? 'Will be deleted' : 'None found'; ?></td>
                </tr>
                <tr>
                    <td>Partners with Transaction Data</td>
                    <td><strong><?php echo number_format($partners_with_transactions); ?></strong></td>
                    <td><?php echo $partners_with_transactions > 0 ? 'Metadata will be cleaned' : 'None found'; ?></td>
                </tr>
                <tr>
                    <td>WooCommerce CIU Product</td>
                    <td><?php echo $woo_product_id ? 'Product ID: ' . $woo_product_id : 'Not found'; ?></td>
                    <td><?php echo $woo_product_id ? 'Will be deleted' : 'None found'; ?></td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Handle migration execution
     */
    public function handle_migration() {
        if (!isset($_POST['run_migration']) || !isset($_POST['migration_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['migration_nonce'], 'ciu_migration_nonce')) {
            wp_die('Security check failed');
        }

        if (!current_user_can('manage_partner_ciu')) {
            wp_die('Insufficient permissions');
        }

        // Run migration steps
        $results = array(
            'transactions_removed' => $this->remove_transaction_data(),
            'metadata_cleaned' => $this->clean_partner_metadata(),
            'product_removed' => $this->remove_woocommerce_product(),
            'options_cleaned' => $this->clean_options()
        );

        // Mark as done
        update_option('ciu_transactions_removed', true);
        update_option('ciu_migration_results', $results);
        update_option('ciu_migration_date', current_time('mysql'));

        // Log migration
        error_log('Partner CIU Manager: Migration completed - ' . print_r($results, true));

        // Redirect with success
        wp_redirect(admin_url('admin.php?page=ciu-migration&migration=success'));
        exit;
    }

    /**
     * Remove transaction data
     */
    private function remove_transaction_data() {
        global $wpdb;

        $transaction_ids = $wpdb->get_col(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'ciu_transaction'"
        );

        $count = 0;
        foreach ($transaction_ids as $transaction_id) {
            if (wp_delete_post($transaction_id, true)) {
                $count++;
            }
        }

        // Clean orphaned meta
        $wpdb->query(
            "DELETE FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT ID FROM {$wpdb->posts})"
        );

        return $count;
    }

    /**
     * Clean partner metadata
     */
    private function clean_partner_metadata() {
        $partners = get_posts(array(
            'post_type' => 'partner_profile',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ));

        $count = 0;
        $meta_keys_to_remove = array(
            'total_funds_contributed',
            'last_purchase_date',
            'transaction_count',
            'woocommerce_customer_id',
            'pending_orders',
            'payment_method'
        );

        foreach ($partners as $partner) {
            foreach ($meta_keys_to_remove as $meta_key) {
                delete_post_meta($partner->ID, $meta_key);
            }

            // Update last_updated timestamp
            update_post_meta($partner->ID, 'ciu_last_updated', current_time('mysql'));
            $count++;
        }

        return $count;
    }

    /**
     * Remove WooCommerce product
     */
    private function remove_woocommerce_product() {
        $product_id = get_option('woocommerce_ciu_product_id');

        if ($product_id) {
            wp_delete_post($product_id, true);
            delete_option('woocommerce_ciu_product_id');
            return true;
        }

        return false;
    }

    /**
     * Clean up options
     */
    private function clean_options() {
        $options_to_remove = array(
            'ciu_default_price',
            'ciu_purchase_notification_email',
            'woocommerce_ciu_product_id',
            'ciu_enable_purchases',
            'ciu_payment_gateway'
        );

        $count = 0;
        foreach ($options_to_remove as $option) {
            if (delete_option($option)) {
                $count++;
            }
        }

        return $count;
    }
}

// Initialize migration only if not already done
if (!get_option('ciu_transactions_removed', false)) {
    new Partner_CIU_Migration_Remove_Transactions();
}
