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
            'supports' => array('title'),
            'has_archive' => false,
            'rewrite' => false,
            'capability_type' => 'partner_profile',
            'capabilities' => array(
                'edit_post' => 'edit_partner_profile',
                'read_post' => 'read_partner_profile',
                'delete_post' => 'delete_partner_profile',
                'edit_posts' => 'edit_partner_profiles',
                'edit_others_posts' => 'edit_others_partner_profiles',
                'publish_posts' => 'publish_partner_profiles',
                'read_private_posts' => 'read_private_partner_profiles',
                'delete_posts' => 'delete_partner_profiles',
                'delete_private_posts' => 'delete_private_partner_profiles',
                'delete_published_posts' => 'delete_published_partner_profiles',
                'delete_others_posts' => 'delete_others_partner_profiles',
                'edit_private_posts' => 'edit_private_partner_profiles',
                'edit_published_posts' => 'edit_published_partner_profiles',
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
            'partner_profile_details',
            __('Partner Profile Details', 'partner-ciu-manager'),
            array($this, 'render_profile_details_meta_box'),
            'partner_profile',
            'normal',
            'high'
        );

        add_meta_box(
            'partner_bank_details',
            __('Bank Details', 'partner-ciu-manager'),
            array($this, 'render_bank_details_meta_box'),
            'partner_profile',
            'normal',
            'high'
        );

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
     * Render profile details meta box
     */
    public function render_profile_details_meta_box($post) {
        wp_nonce_field('partner_profile_meta_box', 'partner_profile_meta_box_nonce');

        $partner_logo = get_post_meta($post->ID, '_partner_logo', true);
        $user_id = get_post_meta($post->ID, '_partner_user_id', true);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="partner_user_id"><?php esc_html_e('Associated User Account', 'partner-ciu-manager'); ?></label></th>
                <td>
                    <?php
                    wp_dropdown_users(array(
                        'name' => 'partner_user_id',
                        'id' => 'partner_user_id',
                        'selected' => $user_id,
                        'show_option_none' => __('Select User', 'partner-ciu-manager'),
                        'role__in' => array('partner', 'administrator'),
                    ));
                    ?>
                    <p class="description"><?php esc_html_e('Select the user account associated with this partner.', 'partner-ciu-manager'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="partner_logo"><?php esc_html_e('Partner Logo', 'partner-ciu-manager'); ?></label></th>
                <td>
                    <div class="partner-logo-upload">
                        <input type="hidden" id="partner_logo" name="partner_logo" value="<?php echo esc_attr($partner_logo); ?>">
                        <div class="partner-logo-preview">
                            <?php if ($partner_logo): ?>
                                <img src="<?php echo esc_url(wp_get_attachment_url($partner_logo)); ?>" style="max-width: 200px; height: auto;">
                            <?php else: ?>
                                <p><?php esc_html_e('No logo uploaded', 'partner-ciu-manager'); ?></p>
                            <?php endif; ?>
                        </div>
                        <p>
                            <button type="button" class="button partner-logo-upload-btn"><?php esc_html_e('Upload Logo', 'partner-ciu-manager'); ?></button>
                            <?php if ($partner_logo): ?>
                                <button type="button" class="button partner-logo-remove-btn"><?php esc_html_e('Remove Logo', 'partner-ciu-manager'); ?></button>
                            <?php endif; ?>
                        </p>
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render bank details meta box
     */
    public function render_bank_details_meta_box($post) {
        $bank_name = get_post_meta($post->ID, '_bank_name', true);
        $account_number = get_post_meta($post->ID, '_account_number', true);
        $sort_code = get_post_meta($post->ID, '_sort_code', true);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="bank_name"><?php esc_html_e('Bank Name', 'partner-ciu-manager'); ?></label></th>
                <td><input type="text" id="bank_name" name="bank_name" value="<?php echo esc_attr($bank_name); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="account_number"><?php esc_html_e('Account Number', 'partner-ciu-manager'); ?></label></th>
                <td><input type="text" id="account_number" name="account_number" value="<?php echo esc_attr($account_number); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="sort_code"><?php esc_html_e('Sort Code', 'partner-ciu-manager'); ?></label></th>
                <td><input type="text" id="sort_code" name="sort_code" value="<?php echo esc_attr($sort_code); ?>" class="regular-text" placeholder="XX-XX-XX"></td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render hero status meta box
     */
    public function render_hero_status_meta_box($post) {
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
                <td><strong><?php echo esc_html(get_woocommerce_currency_symbol() . number_format($total_funds, 2)); ?></strong></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Last Purchase Date', 'partner-ciu-manager'); ?></th>
                <td><?php echo $last_purchase_date ? esc_html(date_i18n(get_option('date_format'), strtotime($last_purchase_date))) : esc_html__('No purchases yet', 'partner-ciu-manager'); ?></td>
            </tr>
        </table>
        <p class="description"><?php esc_html_e('CIU counts are automatically updated when transactions are created. You can also manually adjust them here.', 'partner-ciu-manager'); ?></p>
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

        // Save partner user ID
        if (isset($_POST['partner_user_id'])) {
            update_post_meta($post_id, '_partner_user_id', sanitize_text_field($_POST['partner_user_id']));
        }

        // Save logo
        if (isset($_POST['partner_logo'])) {
            update_post_meta($post_id, '_partner_logo', absint($_POST['partner_logo']));
        }

        // Save bank details
        if (isset($_POST['bank_name'])) {
            update_post_meta($post_id, '_bank_name', sanitize_text_field($_POST['bank_name']));
        }
        if (isset($_POST['account_number'])) {
            update_post_meta($post_id, '_account_number', sanitize_text_field($_POST['account_number']));
        }
        if (isset($_POST['sort_code'])) {
            update_post_meta($post_id, '_sort_code', sanitize_text_field($_POST['sort_code']));
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

    /**
     * Update partner CIU stats
     *
     * @param int $partner_id
     * @param int $ciu_count
     * @param float $amount
     */
    public static function update_partner_stats($partner_id, $ciu_count, $amount) {
        // Update pending CIUs
        $pending_cius = get_post_meta($partner_id, '_pending_cius', true) ?: 0;
        update_post_meta($partner_id, '_pending_cius', $pending_cius + $ciu_count);

        // Update total funds
        $total_funds = get_post_meta($partner_id, '_total_funds', true) ?: 0;
        update_post_meta($partner_id, '_total_funds', $total_funds + $amount);

        // Update last purchase date
        update_post_meta($partner_id, '_last_purchase_date', current_time('mysql'));
    }
}
