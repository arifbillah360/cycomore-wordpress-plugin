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
    }

    /**
     * Render profile details meta box
     */
    public function render_profile_details_meta_box($post) {
        wp_nonce_field('partner_profile_meta_box', 'partner_profile_meta_box_nonce');

        // Get existing values
        $user_id = get_post_meta($post->ID, '_partner_user_id', true);
        $company_legal_name = get_post_meta($post->ID, '_company_legal_name', true);
        $company_trading_name = get_post_meta($post->ID, '_company_trading_name', true);
        $company_registration_number = get_post_meta($post->ID, '_company_registration_number', true);
        $company_type = get_post_meta($post->ID, '_company_type', true);
        $company_website = get_post_meta($post->ID, '_company_website', true);
        $partner_logo = get_post_meta($post->ID, '_partner_logo', true);

        // Get registered address
        $registered_address = get_post_meta($post->ID, '_registered_address', true);
        if (!is_array($registered_address)) {
            $registered_address = array(
                'address1' => '',
                'city' => '',
                'state' => '',
                'postal_code' => '',
                'country' => ''
            );
        }
        ?>

        <style>
        .partner-company-details { padding: 15px; }
        .form-field-row { margin-bottom: 20px; }
        .form-field-row label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-field-row .description { margin-top: 5px; color: #666; font-size: 13px; font-style: italic; }
        .form-row-two-columns { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .form-field-half label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-field-half .description { margin-top: 5px; color: #666; font-size: 13px; }
        .widefat { width: 100%; padding: 6px 8px; }
        .required-indicator { color: red; }
        @media (max-width: 782px) { .form-row-two-columns { grid-template-columns: 1fr; } }
        </style>

        <div class="partner-company-details">
            <p class="description" style="margin-bottom: 20px;">
                <?php esc_html_e('Enter complete company information. Fields marked with', 'partner-ciu-manager'); ?>
                <span class="required-indicator">*</span>
                <?php esc_html_e('are required.', 'partner-ciu-manager'); ?>
            </p>

            <!-- Associated User Account -->
            <div class="form-field-row">
                <label for="partner_user_id">
                    <?php esc_html_e('Associated User Account', 'partner-ciu-manager'); ?>
                </label>
                <?php
                wp_dropdown_users(array(
                    'name' => 'partner_user_id',
                    'id' => 'partner_user_id',
                    'selected' => $user_id,
                    'show_option_none' => __('Select User', 'partner-ciu-manager'),
                    'role__in' => array('partner', 'administrator', 'editor'),
                    'class' => 'widefat'
                ));
                ?>
                <p class="description"><?php esc_html_e('Select the user account associated with this partner.', 'partner-ciu-manager'); ?></p>
            </div>

            <hr style="margin: 30px 0; border: none; border-top: 1px solid #e0e0e0;">

            <!-- Company Legal Name -->
            <div class="form-field-row">
                <label for="company_legal_name">
                    <?php esc_html_e('Company Legal Name', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span>
                </label>
                <input type="text"
                       id="company_legal_name"
                       name="company_legal_name"
                       value="<?php echo esc_attr($company_legal_name); ?>"
                       class="widefat"
                       required
                       placeholder="<?php esc_attr_e('e.g., Levi Strauss & Co.', 'partner-ciu-manager'); ?>" />
                <p class="description"><?php esc_html_e('Official registered company name (used for legal documents and invoicing)', 'partner-ciu-manager'); ?></p>
            </div>

            <!-- Trading Name -->
            <div class="form-field-row">
                <label for="company_trading_name">
                    <?php esc_html_e('Trading Name', 'partner-ciu-manager'); ?>
                    <span style="color: #666; font-weight: normal;"><?php esc_html_e('(optional, public)', 'partner-ciu-manager'); ?></span>
                </label>
                <input type="text"
                       id="company_trading_name"
                       name="company_trading_name"
                       value="<?php echo esc_attr($company_trading_name); ?>"
                       class="widefat"
                       placeholder="<?php esc_attr_e('e.g., Levi\'s', 'partner-ciu-manager'); ?>" />
                <p class="description"><?php esc_html_e('Public-facing brand name (shown on your public profile if different from legal name)', 'partner-ciu-manager'); ?></p>
            </div>

            <div class="form-row-two-columns">
                <!-- Company Registration Number -->
                <div class="form-field-half">
                    <label for="company_registration_number">
                        <?php esc_html_e('Company Registration Number', 'partner-ciu-manager'); ?>
                    </label>
                    <input type="text"
                           id="company_registration_number"
                           name="company_registration_number"
                           value="<?php echo esc_attr($company_registration_number); ?>"
                           class="widefat"
                           placeholder="<?php esc_attr_e('e.g., 12345678', 'partner-ciu-manager'); ?>" />
                    <p class="description"><?php esc_html_e('Required for UK/EU/US companies', 'partner-ciu-manager'); ?></p>
                </div>

                <!-- Company Type -->
                <div class="form-field-half">
                    <label for="company_type">
                        <?php esc_html_e('Company Type', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span>
                    </label>
                    <select id="company_type" name="company_type" class="widefat" required>
                        <option value=""><?php esc_html_e('-- Select Type --', 'partner-ciu-manager'); ?></option>
                        <option value="private_ltd" <?php selected($company_type, 'private_ltd'); ?>><?php esc_html_e('Private Ltd', 'partner-ciu-manager'); ?></option>
                        <option value="public_co" <?php selected($company_type, 'public_co'); ?>><?php esc_html_e('Public Co', 'partner-ciu-manager'); ?></option>
                        <option value="non_profit" <?php selected($company_type, 'non_profit'); ?>><?php esc_html_e('Non-profit', 'partner-ciu-manager'); ?></option>
                        <option value="charity" <?php selected($company_type, 'charity'); ?>><?php esc_html_e('Charity', 'partner-ciu-manager'); ?></option>
                        <option value="foundation" <?php selected($company_type, 'foundation'); ?>><?php esc_html_e('Foundation', 'partner-ciu-manager'); ?></option>
                        <option value="other" <?php selected($company_type, 'other'); ?>><?php esc_html_e('Other', 'partner-ciu-manager'); ?></option>
                    </select>
                </div>
            </div>

            <!-- Website URL -->
            <div class="form-field-row">
                <label for="company_website">
                    <?php esc_html_e('Website URL', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span>
                </label>
                <input type="url"
                       id="company_website"
                       name="company_website"
                       value="<?php echo esc_url($company_website); ?>"
                       class="widefat"
                       required
                       placeholder="https://www.example.com" />
                <p class="description"><?php esc_html_e('Your company\'s official website', 'partner-ciu-manager'); ?></p>
            </div>

            <hr style="margin: 30px 0; border: none; border-top: 1px solid #e0e0e0;">

            <h3 style="margin-top: 0;"><?php esc_html_e('Registered Address', 'partner-ciu-manager'); ?></h3>

            <!-- Address Line 1 -->
            <div class="form-field-row">
                <label for="address1">
                    <?php esc_html_e('Address Line 1', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span>
                </label>
                <input type="text"
                       id="address1"
                       name="registered_address[address1]"
                       value="<?php echo esc_attr($registered_address['address1']); ?>"
                       class="widefat"
                       required
                       placeholder="<?php esc_attr_e('Street address', 'partner-ciu-manager'); ?>" />
            </div>

            <div class="form-row-two-columns">
                <!-- City -->
                <div class="form-field-half">
                    <label for="city">
                        <?php esc_html_e('City', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span>
                    </label>
                    <input type="text"
                           id="city"
                           name="registered_address[city]"
                           value="<?php echo esc_attr($registered_address['city']); ?>"
                           class="widefat"
                           required
                           placeholder="<?php esc_attr_e('City', 'partner-ciu-manager'); ?>" />
                </div>

                <!-- State/Region -->
                <div class="form-field-half">
                    <label for="state">
                        <?php esc_html_e('State/Region', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span>
                    </label>
                    <input type="text"
                           id="state"
                           name="registered_address[state]"
                           value="<?php echo esc_attr($registered_address['state']); ?>"
                           class="widefat"
                           required
                           placeholder="<?php esc_attr_e('State or Region', 'partner-ciu-manager'); ?>" />
                </div>
            </div>

            <div class="form-row-two-columns">
                <!-- Postal Code -->
                <div class="form-field-half">
                    <label for="postal_code">
                        <?php esc_html_e('Postal Code', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span>
                    </label>
                    <input type="text"
                           id="postal_code"
                           name="registered_address[postal_code]"
                           value="<?php echo esc_attr($registered_address['postal_code']); ?>"
                           class="widefat"
                           required
                           placeholder="<?php esc_attr_e('Postal/ZIP Code', 'partner-ciu-manager'); ?>" />
                </div>

                <!-- Country -->
                <div class="form-field-half">
                    <label for="country">
                        <?php esc_html_e('Country', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span>
                    </label>
                    <select id="country" name="registered_address[country]" class="widefat" required>
                        <option value=""><?php esc_html_e('-- Select Country --', 'partner-ciu-manager'); ?></option>
                        <?php
                        $countries = array(
                            'GB' => 'United Kingdom', 'US' => 'United States', 'CA' => 'Canada',
                            'AU' => 'Australia', 'FR' => 'France', 'DE' => 'Germany',
                            'IT' => 'Italy', 'ES' => 'Spain', 'NL' => 'Netherlands',
                            'BE' => 'Belgium', 'SE' => 'Sweden', 'NO' => 'Norway',
                            'DK' => 'Denmark', 'FI' => 'Finland', 'IE' => 'Ireland',
                            'PT' => 'Portugal', 'GR' => 'Greece', 'PL' => 'Poland',
                            'CZ' => 'Czech Republic', 'AT' => 'Austria', 'CH' => 'Switzerland',
                            'JP' => 'Japan', 'CN' => 'China', 'IN' => 'India',
                            'BR' => 'Brazil', 'MX' => 'Mexico', 'SG' => 'Singapore',
                            'NZ' => 'New Zealand', 'ZA' => 'South Africa'
                        );

                        foreach ($countries as $code => $name) {
                            echo '<option value="' . esc_attr($code) . '" ' . selected($registered_address['country'], $code, false) . '>' . esc_html($name) . '</option>';
                        }
                        ?>
                    </select>
                    <p class="description"><?php esc_html_e('ISO 3166 country code', 'partner-ciu-manager'); ?></p>
                </div>
            </div>

            <hr style="margin: 30px 0; border: none; border-top: 1px solid #e0e0e0;">

            <h3 style="margin-top: 0;"><?php esc_html_e('Company Logo', 'partner-ciu-manager'); ?></h3>

            <!-- Logo Upload -->
            <div class="form-field-row">
                <label for="partner_logo">
                    <?php esc_html_e('Upload Logo', 'partner-ciu-manager'); ?>
                    <span style="color: #666; font-weight: normal;"><?php esc_html_e('(optional, public)', 'partner-ciu-manager'); ?></span>
                </label>

                <div class="partner-logo-upload">
                    <input type="hidden" id="partner_logo" name="partner_logo" value="<?php echo esc_attr($partner_logo); ?>">
                    <div class="partner-logo-preview">
                        <?php if ($partner_logo): ?>
                            <img src="<?php echo esc_url(wp_get_attachment_url($partner_logo)); ?>"
                                 style="max-width: 150px; max-height: 150px; border: 1px solid #ddd; padding: 10px; background: #fff; border-radius: 4px;" />
                        <?php else: ?>
                            <p><?php esc_html_e('No logo uploaded', 'partner-ciu-manager'); ?></p>
                        <?php endif; ?>
                    </div>
                    <p style="margin-top: 10px;">
                        <button type="button" class="button partner-logo-upload-btn"><?php esc_html_e('Upload Logo', 'partner-ciu-manager'); ?></button>
                        <?php if ($partner_logo): ?>
                            <button type="button" class="button partner-logo-remove-btn"><?php esc_html_e('Remove Logo', 'partner-ciu-manager'); ?></button>
                        <?php endif; ?>
                    </p>
                    <p class="description">
                        <?php esc_html_e('PNG or SVG format only. Square crop recommended (1:1 ratio). Maximum file size: 2MB.', 'partner-ciu-manager'); ?><br>
                        <?php esc_html_e('This logo will be displayed on your public Cycomore profile.', 'partner-ciu-manager'); ?>
                    </p>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Auto-update post title when legal name changes
            $('#company_legal_name').on('change', function() {
                var legalName = $(this).val();
                if (legalName && !$('#title').val()) {
                    $('#title').val(legalName);
                }
            });

            // Validate URL format
            $('#company_website').on('blur', function() {
                var url = $(this).val();
                if (url && !url.startsWith('http://') && !url.startsWith('https://')) {
                    alert('<?php esc_js_e('Website URL must start with http:// or https://', 'partner-ciu-manager'); ?>');
                    $(this).focus();
                }
            });
        });
        </script>
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

        // Save company legal name (required) and update post title
        if (isset($_POST['company_legal_name'])) {
            $legal_name = sanitize_text_field($_POST['company_legal_name']);
            if (!empty($legal_name)) {
                update_post_meta($post_id, '_company_legal_name', $legal_name);

                // Also update post title to match legal name
                remove_action('save_post_partner_profile', array($this, 'save_meta_boxes'), 10);
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_title' => $legal_name
                ));
                add_action('save_post_partner_profile', array($this, 'save_meta_boxes'), 10, 2);
            }
        }

        // Save trading name (optional)
        if (isset($_POST['company_trading_name'])) {
            update_post_meta($post_id, '_company_trading_name', sanitize_text_field($_POST['company_trading_name']));
        }

        // Save registration number
        if (isset($_POST['company_registration_number'])) {
            update_post_meta($post_id, '_company_registration_number', sanitize_text_field($_POST['company_registration_number']));
        }

        // Save company type
        if (isset($_POST['company_type'])) {
            $allowed_types = array('private_ltd', 'public_co', 'non_profit', 'charity', 'foundation', 'other');
            $company_type = sanitize_text_field($_POST['company_type']);

            if (in_array($company_type, $allowed_types)) {
                update_post_meta($post_id, '_company_type', $company_type);
            }
        }

        // Save website URL (required)
        if (isset($_POST['company_website'])) {
            $website = esc_url_raw($_POST['company_website']);
            update_post_meta($post_id, '_company_website', $website);
        }

        // Save registered address
        if (isset($_POST['registered_address']) && is_array($_POST['registered_address'])) {
            $address = array(
                'address1' => sanitize_text_field($_POST['registered_address']['address1']),
                'city' => sanitize_text_field($_POST['registered_address']['city']),
                'state' => sanitize_text_field($_POST['registered_address']['state']),
                'postal_code' => sanitize_text_field($_POST['registered_address']['postal_code']),
                'country' => sanitize_text_field($_POST['registered_address']['country'])
            );
            update_post_meta($post_id, '_registered_address', $address);
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
