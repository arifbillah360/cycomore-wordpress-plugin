<?php
/**
 * CIU Transaction Custom Post Type
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CIU_Transaction_Post_Type
 */
class CIU_Transaction_Post_Type {

    /**
     * Single instance
     *
     * @var CIU_Transaction_Post_Type
     */
    protected static $instance = null;

    /**
     * Get instance
     *
     * @return CIU_Transaction_Post_Type
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
        add_action('init', array($this, 'register'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post_ciu_transaction', array($this, 'save_meta_boxes'), 10, 2);
        add_filter('manage_ciu_transaction_posts_columns', array($this, 'set_custom_columns'));
        add_action('manage_ciu_transaction_posts_custom_column', array($this, 'custom_column_content'), 10, 2);
    }

    /**
     * Register custom post type
     */
    public static function register() {
        $labels = array(
            'name' => __('CIU Transactions', 'partner-ciu-manager'),
            'singular_name' => __('CIU Transaction', 'partner-ciu-manager'),
            'menu_name' => __('Transactions', 'partner-ciu-manager'),
            'add_new' => __('Add Transaction', 'partner-ciu-manager'),
            'add_new_item' => __('Add New Transaction', 'partner-ciu-manager'),
            'edit_item' => __('Edit Transaction', 'partner-ciu-manager'),
            'new_item' => __('New Transaction', 'partner-ciu-manager'),
            'view_item' => __('View Transaction', 'partner-ciu-manager'),
            'search_items' => __('Search Transactions', 'partner-ciu-manager'),
            'not_found' => __('No transactions found', 'partner-ciu-manager'),
            'not_found_in_trash' => __('No transactions found in trash', 'partner-ciu-manager'),
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type=partner_profile',
            'supports' => array('title'),
            'has_archive' => false,
            'rewrite' => false,
            'capability_type' => 'post',
            'map_meta_cap' => true,
        );

        register_post_type('ciu_transaction', $args);
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'ciu_transaction_details',
            __('Transaction Details', 'partner-ciu-manager'),
            array($this, 'render_transaction_details_meta_box'),
            'ciu_transaction',
            'normal',
            'high'
        );
    }

    /**
     * Render transaction details meta box
     */
    public function render_transaction_details_meta_box($post) {
        wp_nonce_field('ciu_transaction_meta_box', 'ciu_transaction_meta_box_nonce');

        $partner_id = get_post_meta($post->ID, '_partner_id', true);
        $order_id = get_post_meta($post->ID, '_order_id', true);
        $ciu_quantity = get_post_meta($post->ID, '_ciu_quantity', true);
        $purchase_amount = get_post_meta($post->ID, '_purchase_amount', true);
        $purchase_date = get_post_meta($post->ID, '_purchase_date', true);
        $status = get_post_meta($post->ID, '_ciu_status', true) ?: 'pending';
        ?>
        <table class="form-table">
            <tr>
                <th><label for="partner_id"><?php esc_html_e('Partner', 'partner-ciu-manager'); ?></label></th>
                <td>
                    <?php
                    $partners = get_posts(array(
                        'post_type' => 'partner_profile',
                        'posts_per_page' => -1,
                        'orderby' => 'title',
                        'order' => 'ASC',
                    ));
                    ?>
                    <select name="partner_id" id="partner_id" class="regular-text">
                        <option value=""><?php esc_html_e('Select Partner', 'partner-ciu-manager'); ?></option>
                        <?php foreach ($partners as $partner): ?>
                            <option value="<?php echo esc_attr($partner->ID); ?>" <?php selected($partner_id, $partner->ID); ?>>
                                <?php echo esc_html($partner->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($partner_id): ?>
                        <p class="description">
                            <a href="<?php echo esc_url(get_edit_post_link($partner_id)); ?>" target="_blank"><?php esc_html_e('View Partner Profile', 'partner-ciu-manager'); ?></a>
                        </p>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><label for="order_id"><?php esc_html_e('WooCommerce Order ID', 'partner-ciu-manager'); ?></label></th>
                <td>
                    <input type="number" id="order_id" name="order_id" value="<?php echo esc_attr($order_id); ?>" class="small-text">
                    <?php if ($order_id): ?>
                        <p class="description">
                            <a href="<?php echo esc_url(admin_url('post.php?post=' . $order_id . '&action=edit')); ?>" target="_blank"><?php esc_html_e('View Order', 'partner-ciu-manager'); ?></a>
                        </p>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><label for="ciu_quantity"><?php esc_html_e('CIU Quantity', 'partner-ciu-manager'); ?></label></th>
                <td><input type="number" id="ciu_quantity" name="ciu_quantity" value="<?php echo esc_attr($ciu_quantity); ?>" class="small-text" min="1" required></td>
            </tr>
            <tr>
                <th><label for="purchase_amount"><?php esc_html_e('Purchase Amount', 'partner-ciu-manager'); ?></label></th>
                <td>
                    <?php echo esc_html(get_woocommerce_currency_symbol()); ?>
                    <input type="number" id="purchase_amount" name="purchase_amount" value="<?php echo esc_attr($purchase_amount); ?>" class="small-text" step="0.01" min="0" required>
                </td>
            </tr>
            <tr>
                <th><label for="purchase_date"><?php esc_html_e('Purchase Date', 'partner-ciu-manager'); ?></label></th>
                <td><input type="date" id="purchase_date" name="purchase_date" value="<?php echo esc_attr($purchase_date); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="ciu_status"><?php esc_html_e('CIU Status', 'partner-ciu-manager'); ?></label></th>
                <td>
                    <select name="ciu_status" id="ciu_status">
                        <option value="pending" <?php selected($status, 'pending'); ?>><?php esc_html_e('Pending', 'partner-ciu-manager'); ?></option>
                        <option value="active" <?php selected($status, 'active'); ?>><?php esc_html_e('Active', 'partner-ciu-manager'); ?></option>
                        <option value="verified" <?php selected($status, 'verified'); ?>><?php esc_html_e('Verified/Retired', 'partner-ciu-manager'); ?></option>
                    </select>
                    <p class="description"><?php esc_html_e('Change the status to move CIUs between categories.', 'partner-ciu-manager'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save meta boxes
     */
    public function save_meta_boxes($post_id, $post) {
        // Check nonce
        if (!isset($_POST['ciu_transaction_meta_box_nonce']) || !wp_verify_nonce($_POST['ciu_transaction_meta_box_nonce'], 'ciu_transaction_meta_box')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Get old status
        $old_status = get_post_meta($post_id, '_ciu_status', true) ?: 'pending';
        $old_partner_id = get_post_meta($post_id, '_partner_id', true);
        $old_quantity = get_post_meta($post_id, '_ciu_quantity', true) ?: 0;

        // Save partner ID
        if (isset($_POST['partner_id'])) {
            $partner_id = absint($_POST['partner_id']);
            update_post_meta($post_id, '_partner_id', $partner_id);
        }

        // Save order ID
        if (isset($_POST['order_id'])) {
            update_post_meta($post_id, '_order_id', absint($_POST['order_id']));
        }

        // Save CIU quantity
        if (isset($_POST['ciu_quantity'])) {
            $ciu_quantity = absint($_POST['ciu_quantity']);
            update_post_meta($post_id, '_ciu_quantity', $ciu_quantity);
        }

        // Save purchase amount
        if (isset($_POST['purchase_amount'])) {
            update_post_meta($post_id, '_purchase_amount', floatval($_POST['purchase_amount']));
        }

        // Save purchase date
        if (isset($_POST['purchase_date'])) {
            update_post_meta($post_id, '_purchase_date', sanitize_text_field($_POST['purchase_date']));
        }

        // Save status
        if (isset($_POST['ciu_status'])) {
            $new_status = sanitize_text_field($_POST['ciu_status']);
            update_post_meta($post_id, '_ciu_status', $new_status);

            // Update partner CIU counts if status changed
            if ($old_status !== $new_status && !empty($partner_id) && !empty($ciu_quantity)) {
                $this->update_partner_ciu_counts($partner_id, $old_status, $new_status, $ciu_quantity, $old_quantity);
            }
        }

        // Update post title
        if (!empty($partner_id) && !empty($ciu_quantity)) {
            $partner = get_post($partner_id);
            if ($partner) {
                $title = sprintf('%s - %d CIUs', $partner->post_title, $ciu_quantity);
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_title' => $title,
                ));
            }
        }
    }

    /**
     * Update partner CIU counts when transaction status changes
     *
     * @param int $partner_id
     * @param string $old_status
     * @param string $new_status
     * @param int $quantity
     * @param int $old_quantity
     */
    private function update_partner_ciu_counts($partner_id, $old_status, $new_status, $quantity, $old_quantity) {
        // Remove from old status
        if ($old_status && $old_quantity > 0) {
            $old_meta_key = '_' . $old_status . '_cius';
            $old_count = get_post_meta($partner_id, $old_meta_key, true) ?: 0;
            update_post_meta($partner_id, $old_meta_key, max(0, $old_count - $old_quantity));
        }

        // Add to new status
        $new_meta_key = '_' . $new_status . '_cius';
        $new_count = get_post_meta($partner_id, $new_meta_key, true) ?: 0;
        update_post_meta($partner_id, $new_meta_key, $new_count + $quantity);
    }

    /**
     * Set custom columns
     *
     * @param array $columns
     * @return array
     */
    public function set_custom_columns($columns) {
        $new_columns = array(
            'cb' => $columns['cb'],
            'title' => __('Transaction', 'partner-ciu-manager'),
            'partner' => __('Partner', 'partner-ciu-manager'),
            'ciu_quantity' => __('CIU Quantity', 'partner-ciu-manager'),
            'amount' => __('Amount', 'partner-ciu-manager'),
            'status' => __('Status', 'partner-ciu-manager'),
            'order' => __('Order', 'partner-ciu-manager'),
            'date' => __('Date', 'partner-ciu-manager'),
        );
        return $new_columns;
    }

    /**
     * Custom column content
     *
     * @param string $column
     * @param int $post_id
     */
    public function custom_column_content($column, $post_id) {
        switch ($column) {
            case 'partner':
                $partner_id = get_post_meta($post_id, '_partner_id', true);
                if ($partner_id) {
                    $partner = get_post($partner_id);
                    if ($partner) {
                        echo '<a href="' . esc_url(get_edit_post_link($partner_id)) . '">' . esc_html($partner->post_title) . '</a>';
                    }
                }
                break;

            case 'ciu_quantity':
                echo esc_html(get_post_meta($post_id, '_ciu_quantity', true));
                break;

            case 'amount':
                $amount = get_post_meta($post_id, '_purchase_amount', true);
                echo esc_html(get_woocommerce_currency_symbol() . number_format($amount, 2));
                break;

            case 'status':
                $status = get_post_meta($post_id, '_ciu_status', true) ?: 'pending';
                $status_labels = array(
                    'pending' => __('Pending', 'partner-ciu-manager'),
                    'active' => __('Active', 'partner-ciu-manager'),
                    'verified' => __('Verified/Retired', 'partner-ciu-manager'),
                );
                $status_colors = array(
                    'pending' => '#f0ad4e',
                    'active' => '#5bc0de',
                    'verified' => '#5cb85c',
                );
                echo '<span style="display: inline-block; padding: 3px 8px; background-color: ' . esc_attr($status_colors[$status]) . '; color: white; border-radius: 3px; font-size: 11px;">' . esc_html($status_labels[$status]) . '</span>';
                break;

            case 'order':
                $order_id = get_post_meta($post_id, '_order_id', true);
                if ($order_id) {
                    echo '<a href="' . esc_url(admin_url('post.php?post=' . $order_id . '&action=edit')) . '">#' . esc_html($order_id) . '</a>';
                } else {
                    echo '—';
                }
                break;
        }
    }

    /**
     * Create transaction
     *
     * @param int $partner_id
     * @param int $order_id
     * @param int $ciu_quantity
     * @param float $purchase_amount
     * @return int|WP_Error
     */
    public static function create_transaction($partner_id, $order_id, $ciu_quantity, $purchase_amount) {
        $partner = get_post($partner_id);
        if (!$partner) {
            return new WP_Error('invalid_partner', __('Invalid partner ID', 'partner-ciu-manager'));
        }

        $title = sprintf('%s - %d CIUs', $partner->post_title, $ciu_quantity);

        $transaction_id = wp_insert_post(array(
            'post_type' => 'ciu_transaction',
            'post_title' => $title,
            'post_status' => 'publish',
            'meta_input' => array(
                '_partner_id' => $partner_id,
                '_order_id' => $order_id,
                '_ciu_quantity' => $ciu_quantity,
                '_purchase_amount' => $purchase_amount,
                '_purchase_date' => current_time('mysql'),
                '_ciu_status' => 'pending',
            ),
        ));

        if (is_wp_error($transaction_id)) {
            return $transaction_id;
        }

        // Update partner stats
        Partner_Post_Type::update_partner_stats($partner_id, $ciu_quantity, $purchase_amount);

        return $transaction_id;
    }
}
