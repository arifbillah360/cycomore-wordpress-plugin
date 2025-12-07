<?php
/**
 * Partner Profile Custom Post Type
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Partner_Post_Type
 */
class Partner_Post_Type {

    /**
     * Single instance
     *
     * @var Partner_Post_Type
     */
    protected static $instance = null;

    /**
     * Get instance
     *
     * @return Partner_Post_Type
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
        add_action('save_post_partner_profile', array($this, 'save_meta_boxes'), 10, 2);
    }

    /**
     * Register custom post type
     */
    public static function register() {
        $labels = array(
            'name' => __('Partner Profiles', 'partner-ciu-manager'),
            'singular_name' => __('Partner Profile', 'partner-ciu-manager'),
            'menu_name' => __('Partners', 'partner-ciu-manager'),
            'add_new' => __('Add New Partner', 'partner-ciu-manager'),
            'add_new_item' => __('Add New Partner', 'partner-ciu-manager'),
            'edit_item' => __('Edit Partner', 'partner-ciu-manager'),
            'new_item' => __('New Partner', 'partner-ciu-manager'),
            'view_item' => __('View Partner', 'partner-ciu-manager'),
            'search_items' => __('Search Partners', 'partner-ciu-manager'),
            'not_found' => __('No partners found', 'partner-ciu-manager'),
            'not_found_in_trash' => __('No partners found in trash', 'partner-ciu-manager'),
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-groups',
            'menu_position' => 25,
            'supports' => array('title', 'thumbnail'),
            'has_archive' => false,
            'rewrite' => false,
            'capability_type' => 'post',
            'capabilities' => array(
                'edit_post' => 'edit_partner_profile',
                'read_post' => 'read_partner_profile',
                'delete_post' => 'delete_partner_profile',
                'edit_posts' => 'edit_partner_profiles',
                'edit_others_posts' => 'edit_others_partner_profiles',
                'publish_posts' => 'publish_partner_profiles',
                'read_private_posts' => 'read_private_partner_profiles',
            ),
            'map_meta_cap' => true,
        );

        register_post_type('partner_profile', $args);
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'partner_hero_status',
            __('Hero Partner Settings', 'partner-ciu-manager'),
            array($this, 'render_hero_status_meta_box'),
            'partner_profile',
            'side',
            'default'
        );

        add_meta_box(
            'partner_ciu_stats',
            __('CIU Statistics', 'partner-ciu-manager'),
            array($this, 'render_ciu_stats_meta_box'),
            'partner_profile',
            'normal',
            'high'
        );
    }

    /**
     * Render hero status meta box
     */
    public function render_hero_status_meta_box($post) {
        wp_nonce_field('partner_profile_meta_box', 'partner_profile_meta_box_nonce');

        $is_hero = get_post_meta($post->ID, '_is_hero_partner', true);
        $hero_highlight = get_post_meta($post->ID, '_hero_highlight_text', true);
        ?>
        <p>
            <label>
                <input type="checkbox" name="is_hero_partner" value="1" <?php checked($is_hero, '1'); ?>>
                <?php esc_html_e('Hero Partner', 'partner-ciu-manager'); ?>
            </label>
        </p>
        <p>
            <label for="hero_highlight_text"><?php esc_html_e('Hero Highlight Text', 'partner-ciu-manager'); ?></label>
            <input type="text" id="hero_highlight_text" name="hero_highlight_text" value="<?php echo esc_attr($hero_highlight); ?>" class="widefat" placeholder="<?php esc_attr_e('e.g., Founding Partner', 'partner-ciu-manager'); ?>">
        </p>
        <p class="description">
            <?php esc_html_e('Use the "Featured Image" box on this page to upload a partner logo.', 'partner-ciu-manager'); ?>
        </p>
        <?php
    }

    /**
     * Render CIU stats meta box
     */
    public function render_ciu_stats_meta_box($post) {
        $pending_cius = get_post_meta($post->ID, '_pending_cius', true) ?: 0;
        $active_cius = get_post_meta($post->ID, '_active_cius', true) ?: 0;
        $verified_cius = get_post_meta($post->ID, '_verified_cius', true) ?: 0;
        $total_cius = $pending_cius + $active_cius + $verified_cius;
        $total_funds = get_post_meta($post->ID, '_total_funds', true) ?: 0;
        $last_purchase_date = get_post_meta($post->ID, '_last_purchase_date', true);

        // Get currency from settings
        $settings = get_option('partner_ciu_settings', array());
        $currency = isset($settings['currency']) ? $settings['currency'] : 'GBP';
        $currency_symbol = $currency === 'GBP' ? '£' : ($currency === 'USD' ? '$' : $currency);
        ?>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('Total CIUs', 'partner-ciu-manager'); ?></th>
                <td><strong><?php echo esc_html($total_cius); ?></strong></td>
            </tr>
            <tr>
                <th><label for="pending_cius"><?php esc_html_e('Pending CIUs', 'partner-ciu-manager'); ?></label></th>
                <td><input type="number" id="pending_cius" name="pending_cius" value="<?php echo esc_attr($pending_cius); ?>" class="small-text" min="0"></td>
            </tr>
            <tr>
                <th><label for="active_cius"><?php esc_html_e('Active CIUs', 'partner-ciu-manager'); ?></label></th>
                <td><input type="number" id="active_cius" name="active_cius" value="<?php echo esc_attr($active_cius); ?>" class="small-text" min="0"></td>
            </tr>
            <tr>
                <th><label for="verified_cius"><?php esc_html_e('Verified/Retired CIUs', 'partner-ciu-manager'); ?></label></th>
                <td><input type="number" id="verified_cius" name="verified_cius" value="<?php echo esc_attr($verified_cius); ?>" class="small-text" min="0"></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Total Funds Contributed', 'partner-ciu-manager'); ?></th>
                <td><strong><?php echo esc_html($currency_symbol . number_format($total_funds, 2)); ?></strong></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Last Purchase Date', 'partner-ciu-manager'); ?></th>
                <td><?php echo $last_purchase_date ? esc_html(date_i18n(get_option('date_format'), strtotime($last_purchase_date))) : esc_html__('No purchases yet', 'partner-ciu-manager'); ?></td>
            </tr>
        </table>
        <p class="description"><?php esc_html_e('CIU counts can be manually adjusted here.', 'partner-ciu-manager'); ?></p>
        <?php
    }

    /**
     * Save meta boxes
     */
    public function save_meta_boxes($post_id, $post) {
        // Check nonce
        if (!isset($_POST['partner_profile_meta_box_nonce']) || !wp_verify_nonce($_POST['partner_profile_meta_box_nonce'], 'partner_profile_meta_box')) {
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

        // Save hero status
        $is_hero = isset($_POST['is_hero_partner']) ? '1' : '0';
        update_post_meta($post_id, '_is_hero_partner', $is_hero);

        if (isset($_POST['hero_highlight_text'])) {
            update_post_meta($post_id, '_hero_highlight_text', sanitize_text_field($_POST['hero_highlight_text']));
        }

        // Save CIU stats
        if (isset($_POST['pending_cius'])) {
            update_post_meta($post_id, '_pending_cius', absint($_POST['pending_cius']));
        }
        if (isset($_POST['active_cius'])) {
            update_post_meta($post_id, '_active_cius', absint($_POST['active_cius']));
        }
        if (isset($_POST['verified_cius'])) {
            update_post_meta($post_id, '_verified_cius', absint($_POST['verified_cius']));
        }
    }

    /**
     * Get partner by user ID
     *
     * @param int $user_id
     * @return WP_Post|null
     */
    public static function get_partner_by_user_id($user_id) {
        $args = array(
            'post_type' => 'partner_profile',
            'meta_query' => array(
                array(
                    'key' => '_partner_user_id',
                    'value' => $user_id,
                ),
            ),
            'posts_per_page' => 1,
        );

        $partners = get_posts($args);
        return !empty($partners) ? $partners[0] : null;
    }

}
