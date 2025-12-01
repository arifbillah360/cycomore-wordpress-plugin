<?php
/**
 * WooCommerce Integration
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Partner_WooCommerce_Integration
 */
class Partner_WooCommerce_Integration {

    /**
     * Single instance
     *
     * @var Partner_WooCommerce_Integration
     */
    protected static $instance = null;

    /**
     * Hidden CIU product ID
     *
     * @var int
     */
    private $ciu_product_id = null;

    /**
     * Get instance
     *
     * @return Partner_WooCommerce_Integration
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
        // Create hidden product on init
        add_action('init', array($this, 'create_ciu_product'));

        // Handle CIU purchase form submission
        add_action('wp_ajax_partner_purchase_cius', array($this, 'handle_purchase_form'));

        // Handle order completion
        add_action('woocommerce_order_status_completed', array($this, 'handle_order_completed'));
        add_action('woocommerce_order_status_processing', array($this, 'handle_order_completed'));

        // Hide CIU product from shop
        add_action('pre_get_posts', array($this, 'hide_ciu_product_from_shop'));
        add_filter('woocommerce_product_is_visible', array($this, 'hide_ciu_product'), 10, 2);

        // Add custom cart item data
        add_filter('woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 10, 3);
        add_filter('woocommerce_get_item_data', array($this, 'display_cart_item_data'), 10, 2);

        // Save order metadata
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'save_order_item_metadata'), 10, 4);
    }

    /**
     * Create or get hidden CIU product
     */
    public function create_ciu_product() {
        // Check if product already exists
        $product_id = get_option('partner_ciu_product_id');

        if ($product_id && get_post($product_id)) {
            $this->ciu_product_id = $product_id;
            return;
        }

        // Create new hidden product
        $product = new WC_Product_Simple();
        $product->set_name(__('CIU Purchase', 'partner-ciu-manager'));
        $product->set_status('private');
        $product->set_catalog_visibility('hidden');
        $product->set_price(Partner_CIU_Settings::get_ciu_price());
        $product->set_regular_price(Partner_CIU_Settings::get_ciu_price());
        $product->set_sold_individually(false);
        $product->set_virtual(true);

        $product_id = $product->save();

        if ($product_id) {
            update_option('partner_ciu_product_id', $product_id);
            $this->ciu_product_id = $product_id;
        }
    }

    /**
     * Get CIU product ID
     *
     * @return int
     */
    public function get_ciu_product_id() {
        if (!$this->ciu_product_id) {
            $this->ciu_product_id = get_option('partner_ciu_product_id');
        }
        return $this->ciu_product_id;
    }

    /**
     * Handle purchase form submission
     */
    public function handle_purchase_form() {
        // Check nonce
        check_ajax_referer('partner-ciu-public-nonce', 'nonce');

        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to purchase CIUs.', 'partner-ciu-manager')));
        }

        // Get partner profile
        $partner_profile = Partner_Role::get_partner_profile();
        if (!$partner_profile) {
            wp_send_json_error(array('message' => __('No partner profile found for your account.', 'partner-ciu-manager')));
        }

        // Get quantity
        $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 0;
        if ($quantity <= 0) {
            wp_send_json_error(array('message' => __('Invalid CIU quantity.', 'partner-ciu-manager')));
        }

        // Get CIU price
        $ciu_price = Partner_CIU_Settings::get_ciu_price();
        $total_price = $ciu_price * $quantity;

        // Clear cart
        WC()->cart->empty_cart();

        // Add CIU product to cart
        $product_id = $this->get_ciu_product_id();
        if (!$product_id) {
            wp_send_json_error(array('message' => __('CIU product not found. Please contact administrator.', 'partner-ciu-manager')));
        }

        // Add to cart with custom data
        $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, 0, array(), array(
            'ciu_purchase' => true,
            'partner_id' => $partner_profile->ID,
            'ciu_quantity' => $quantity,
            'ciu_price' => $ciu_price,
        ));

        if (!$cart_item_key) {
            wp_send_json_error(array('message' => __('Failed to add CIUs to cart.', 'partner-ciu-manager')));
        }

        // Return success with checkout URL
        wp_send_json_success(array(
            'message' => sprintf(__('%d CIUs added to cart. Redirecting to checkout...', 'partner-ciu-manager'), $quantity),
            'checkout_url' => wc_get_checkout_url(),
        ));
    }

    /**
     * Hide CIU product from shop
     *
     * @param WP_Query $query
     */
    public function hide_ciu_product_from_shop($query) {
        if (!is_admin() && $query->is_main_query() && (is_shop() || is_product_category() || is_product_tag())) {
            $product_id = $this->get_ciu_product_id();
            if ($product_id) {
                $query->set('post__not_in', array($product_id));
            }
        }
    }

    /**
     * Hide CIU product
     *
     * @param bool $visible
     * @param int $product_id
     * @return bool
     */
    public function hide_ciu_product($visible, $product_id) {
        if ($product_id === $this->get_ciu_product_id()) {
            return false;
        }
        return $visible;
    }

    /**
     * Add cart item data
     *
     * @param array $cart_item_data
     * @param int $product_id
     * @param int $variation_id
     * @return array
     */
    public function add_cart_item_data($cart_item_data, $product_id, $variation_id) {
        if ($product_id === $this->get_ciu_product_id() && isset($cart_item_data['ciu_purchase'])) {
            $cart_item_data['unique_key'] = md5(microtime() . rand());
        }
        return $cart_item_data;
    }

    /**
     * Display cart item data
     *
     * @param array $item_data
     * @param array $cart_item
     * @return array
     */
    public function display_cart_item_data($item_data, $cart_item) {
        if (isset($cart_item['ciu_purchase']) && $cart_item['ciu_purchase']) {
            $item_data[] = array(
                'key' => __('CIU Quantity', 'partner-ciu-manager'),
                'value' => $cart_item['ciu_quantity'],
            );
        }
        return $item_data;
    }

    /**
     * Save order item metadata
     *
     * @param WC_Order_Item_Product $item
     * @param string $cart_item_key
     * @param array $values
     * @param WC_Order $order
     */
    public function save_order_item_metadata($item, $cart_item_key, $values, $order) {
        if (isset($values['ciu_purchase']) && $values['ciu_purchase']) {
            $item->add_meta_data('_ciu_purchase', true, true);
            $item->add_meta_data('_partner_id', $values['partner_id'], true);
            $item->add_meta_data('_ciu_quantity', $values['ciu_quantity'], true);
            $item->add_meta_data('_ciu_price', $values['ciu_price'], true);
        }
    }

    /**
     * Handle order completed
     *
     * @param int $order_id
     */
    public function handle_order_completed($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // Check if already processed
        if ($order->get_meta('_ciu_transaction_created')) {
            return;
        }

        // Process each order item
        foreach ($order->get_items() as $item_id => $item) {
            $is_ciu_purchase = $item->get_meta('_ciu_purchase');
            if (!$is_ciu_purchase) {
                continue;
            }

            $partner_id = $item->get_meta('_partner_id');
            $ciu_quantity = $item->get_meta('_ciu_quantity');
            $ciu_price = $item->get_meta('_ciu_price');

            if (!$partner_id || !$ciu_quantity) {
                continue;
            }

            // Calculate purchase amount
            $purchase_amount = $ciu_quantity * $ciu_price;

            // Create transaction
            $transaction_id = CIU_Transaction_Post_Type::create_transaction(
                $partner_id,
                $order_id,
                $ciu_quantity,
                $purchase_amount
            );

            if (!is_wp_error($transaction_id)) {
                // Mark order as processed
                $order->update_meta_data('_ciu_transaction_created', true);
                $order->update_meta_data('_ciu_transaction_id', $transaction_id);
                $order->save();

                // Send notifications
                do_action('partner_ciu_purchase_completed', $partner_id, $order_id, $ciu_quantity, $purchase_amount);
            }
        }
    }
}
