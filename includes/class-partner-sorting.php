<?php
/**
 * Partner Sorting
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Partner_Sorting
 */
class Partner_Sorting {

    /**
     * Single instance
     *
     * @var Partner_Sorting
     */
    protected static $instance = null;

    /**
     * Get instance
     *
     * @return Partner_Sorting
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
        add_action('wp_ajax_partner_save_sort_order', array($this, 'save_sort_order'));
    }

    /**
     * Save sort order
     */
    public function save_sort_order() {
        // Check nonce
        check_ajax_referer('partner-ciu-admin-nonce', 'nonce');

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'partner-ciu-manager')));
        }

        // Get order
        $order = isset($_POST['order']) ? array_map('absint', $_POST['order']) : array();

        if (empty($order)) {
            wp_send_json_error(array('message' => __('No order data received.', 'partner-ciu-manager')));
        }

        // Save order
        update_option('partner_sort_order', $order);

        // Clear data aggregator cache so the new order is reflected immediately
        if (class_exists('Partner_Data_Aggregator')) {
            Partner_Data_Aggregator::instance()->clear_cache();
        }

        wp_send_json_success(array('message' => __('Partner order saved successfully.', 'partner-ciu-manager')));
    }

    /**
     * Get sorted partners
     *
     * @param array $args
     * @return array
     */
    public static function get_sorted_partners($args = array()) {
        $defaults = array(
            'post_type' => 'partner_profile',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        );

        $args = wp_parse_args($args, $defaults);

        $partners = get_posts($args);

        // Get custom order
        $custom_order = get_option('partner_sort_order', array());

        if (!empty($custom_order)) {
            // Sort by custom order
            usort($partners, function($a, $b) use ($custom_order) {
                $pos_a = array_search($a->ID, $custom_order);
                $pos_b = array_search($b->ID, $custom_order);

                // If not in custom order, put at end
                if ($pos_a === false && $pos_b === false) {
                    return 0;
                }
                if ($pos_a === false) {
                    return 1;
                }
                if ($pos_b === false) {
                    return -1;
                }

                return $pos_a - $pos_b;
            });
        }

        // Ensure hero partners are first
        usort($partners, function($a, $b) {
            $a_is_hero = get_post_meta($a->ID, '_is_hero_partner', true);
            $b_is_hero = get_post_meta($b->ID, '_is_hero_partner', true);

            if ($a_is_hero && !$b_is_hero) {
                return -1;
            }
            if (!$a_is_hero && $b_is_hero) {
                return 1;
            }

            return 0;
        });

        return $partners;
    }
}
