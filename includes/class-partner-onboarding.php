<?php
/**
 * Partner Onboarding Form Handler
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Partner_CIU_Onboarding
 */
class Partner_CIU_Onboarding {

    /**
     * Single instance
     *
     * @var Partner_CIU_Onboarding
     */
    protected static $instance = null;

    /**
     * Get instance
     *
     * @return Partner_CIU_Onboarding
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
        add_action('wp_ajax_nopriv_submit_partner_onboarding', array($this, 'handle_submission'));
        add_action('wp_ajax_submit_partner_onboarding', array($this, 'handle_submission'));
        add_action('wp_ajax_nopriv_save_onboarding_draft', array($this, 'save_draft'));
        add_action('wp_ajax_save_onboarding_draft', array($this, 'save_draft'));
        add_shortcode('partner_onboarding_form', array($this, 'render_form'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Render onboarding form
     */
    public function render_form() {
        ob_start();
        include PARTNER_CIU_PLUGIN_DIR . 'templates/partner-onboarding-form.php';
        return ob_get_clean();
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        if (!has_shortcode(get_post()->post_content ?? '', 'partner_onboarding_form')) {
            return;
        }

        wp_enqueue_style(
            'partner-onboarding',
            PARTNER_CIU_PLUGIN_URL . 'assets/css/partner-onboarding.css',
            array(),
            PARTNER_CIU_VERSION
        );

        wp_enqueue_script(
            'partner-onboarding',
            PARTNER_CIU_PLUGIN_URL . 'assets/js/partner-onboarding.js',
            array('jquery'),
            PARTNER_CIU_VERSION,
            true
        );

        wp_localize_script('partner-onboarding', 'partnerOnboardingAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('partner_onboarding_nonce'),
            'strings' => array(
                'fillRequired' => __('Please fill in all required fields correctly', 'partner-ciu-manager'),
                'invalidEmail' => __('Invalid email address', 'partner-ciu-manager'),
                'weakPassword' => __('Password must be at least 12 characters with 1 number and 1 symbol', 'partner-ciu-manager'),
                'invalidURL' => __('Please enter a valid website URL', 'partner-ciu-manager'),
                'confirmRemove' => __('Are you sure you want to remove this team member?', 'partner-ciu-manager'),
            )
        ));
    }

    /**
     * Handle form submission
     */
    public function handle_submission() {
        // Verify nonce
        check_ajax_referer('partner_onboarding_nonce', 'nonce');

        // Validate and sanitize data
        $data = $this->validate_and_sanitize_data($_POST);

        if (is_wp_error($data)) {
            wp_send_json_error(array('message' => $data->get_error_message()));
        }

        // Create partner profile post
        $partner_id = wp_insert_post(array(
            'post_title' => $data['company_legal_name'],
            'post_type' => 'partner_profile',
            'post_status' => 'pending', // Pending until email verification
        ));

        if (is_wp_error($partner_id)) {
            wp_send_json_error(array('message' => __('Failed to create partner profile', 'partner-ciu-manager')));
        }

        // Save all meta data
        $this->save_partner_meta($partner_id, $data);

        // Create WordPress user account
        $user_id = $this->create_partner_user($partner_id, $data);

        if (is_wp_error($user_id)) {
            wp_delete_post($partner_id, true);
            wp_send_json_error(array('message' => $user_id->get_error_message()));
        }

        // Send verification email
        $this->send_verification_email($user_id, $data['primary_contact']['email']);

        // Send notification to admin
        $this->notify_admin_new_partner($partner_id, $data);

        wp_send_json_success(array(
            'message' => __('Partner account created successfully! Check your email for verification.', 'partner-ciu-manager'),
            'redirect_url' => home_url('/partner-dashboard/')
        ));
    }

    /**
     * Save draft
     */
    public function save_draft() {
        check_ajax_referer('partner_onboarding_nonce', 'nonce');

        $draft_data = isset($_POST['draft_data']) ? wp_unslash($_POST['draft_data']) : '';
        $draft_key = 'partner_onboarding_draft_' . md5($draft_data);

        set_transient($draft_key, $draft_data, DAY_IN_SECONDS * 7); // 7 days

        wp_send_json_success(array(
            'message' => __('Draft saved successfully', 'partner-ciu-manager'),
            'draft_key' => $draft_key
        ));
    }

    /**
     * Validate and sanitize form data
     */
    private function validate_and_sanitize_data($post_data) {
        $data = array();

        // Step 1: Company Fields
        $data['company_legal_name'] = sanitize_text_field($post_data['company_legal_name'] ?? '');
        $data['company_trading_name'] = sanitize_text_field($post_data['company_trading_name'] ?? '');
        $data['company_registration_number'] = sanitize_text_field($post_data['company_registration_number'] ?? '');
        $data['company_type'] = sanitize_text_field($post_data['company_type'] ?? '');
        $data['company_website'] = esc_url_raw($post_data['company_website'] ?? '');

        // Validate required fields
        if (empty($data['company_legal_name']) || empty($data['company_website'])) {
            return new WP_Error('validation_error', __('Company name and website are required', 'partner-ciu-manager'));
        }

        // Address
        $data['company_registered_address'] = array(
            'address1' => sanitize_text_field($post_data['address1'] ?? ''),
            'city' => sanitize_text_field($post_data['city'] ?? ''),
            'state' => sanitize_text_field($post_data['state'] ?? ''),
            'postal_code' => sanitize_text_field($post_data['postal_code'] ?? ''),
            'country' => sanitize_text_field($post_data['country'] ?? '')
        );

        // Logo upload (handled separately via $_FILES)
        if (!empty($_FILES['company_logo']['name'])) {
            $upload = $this->handle_file_upload('company_logo');
            if (!is_wp_error($upload)) {
                $data['company_logo'] = $upload['url'];
            }
        }

        // Step 2: Contacts
        $data['primary_contact'] = array(
            'full_name' => sanitize_text_field($post_data['primary_contact_name'] ?? ''),
            'role' => sanitize_text_field($post_data['primary_contact_role'] ?? ''),
            'email' => sanitize_email($post_data['primary_contact_email'] ?? ''),
            'phone' => sanitize_text_field($post_data['primary_contact_phone'] ?? '')
        );

        // Validate email
        if (!is_email($data['primary_contact']['email'])) {
            return new WP_Error('validation_error', __('Invalid email address', 'partner-ciu-manager'));
        }

        // Check for duplicate email
        if (email_exists($data['primary_contact']['email'])) {
            return new WP_Error('validation_error', __('Email already registered', 'partner-ciu-manager'));
        }

        // Impact reporting contact
        if (!empty($post_data['impact_contact_name'])) {
            $data['impact_reporting_contact'] = array(
                'full_name' => sanitize_text_field($post_data['impact_contact_name']),
                'email' => sanitize_email($post_data['impact_contact_email'] ?? '')
            );
        }

        // Password
        $data['password'] = $post_data['password'] ?? '';
        if (strlen($data['password']) < 12) {
            return new WP_Error('validation_error', __('Password must be at least 12 characters', 'partner-ciu-manager'));
        }

        // 2FA
        $data['2fa_enabled'] = isset($post_data['enable_2fa']);
        $data['2fa_method'] = sanitize_text_field($post_data['2fa_method'] ?? 'sms');

        // Team members
        if (!empty($post_data['team_members'])) {
            $data['team_access'] = array();
            foreach ($post_data['team_members'] as $member) {
                if (!empty($member['name']) && !empty($member['email'])) {
                    $data['team_access'][] = array(
                        'name' => sanitize_text_field($member['name']),
                        'email' => sanitize_email($member['email']),
                        'role' => sanitize_text_field($member['role'] ?? 'viewer')
                    );
                }
            }
        }

        // Step 3: Finance
        $data['billing_preferences'] = array(
            'preferred_currency' => sanitize_text_field($post_data['preferred_currency'] ?? 'GBP'),
            'invoicing_email' => sanitize_email($post_data['invoicing_email'] ?? ''),
            'po_number' => sanitize_text_field($post_data['po_number'] ?? '')
        );

        $data['bank_details'] = array(
            'account_name' => sanitize_text_field($post_data['account_name'] ?? ''),
            'account_number' => sanitize_text_field($post_data['account_number'] ?? ''),
            'swift_code' => sanitize_text_field($post_data['swift_code'] ?? ''),
            'billing_address_same_as_registered' => isset($post_data['billing_address_same'])
        );

        $data['tax_info'] = array(
            'vat_id' => sanitize_text_field($post_data['vat_id'] ?? ''),
            'tax_id' => sanitize_text_field($post_data['tax_id'] ?? '')
        );

        // Step 4: Impact
        $data['impact_preferences'] = array(
            'primary_categories' => array_map('sanitize_text_field', $post_data['primary_categories'] ?? array()),
            'collections_of_interest' => array_map('absint', $post_data['collections_of_interest'] ?? array())
        );

        $data['public_display_settings'] = array(
            'visibility' => sanitize_text_field($post_data['visibility'] ?? 'public'),
            'csr_statement' => sanitize_textarea_field($post_data['csr_statement'] ?? ''),
            'social_links' => array(
                'linkedin' => esc_url_raw($post_data['social_linkedin'] ?? ''),
                'twitter' => esc_url_raw($post_data['social_twitter'] ?? ''),
                'instagram' => esc_url_raw($post_data['social_instagram'] ?? '')
            )
        );

        $data['attribution_rules'] = array(
            'proportional_allocation' => isset($post_data['proportional_allocation'])
        );

        return $data;
    }

    /**
     * Save partner meta data
     */
    private function save_partner_meta($partner_id, $data) {
        foreach ($data as $key => $value) {
            if ($key !== 'password') {
                update_post_meta($partner_id, $key, $value);
            }
        }

        update_post_meta($partner_id, 'onboarding_completed', false);
        update_post_meta($partner_id, 'email_verified', false);
        update_post_meta($partner_id, 'created_date', current_time('mysql'));
    }

    /**
     * Create WordPress user
     */
    private function create_partner_user($partner_id, $data) {
        $user_id = wp_create_user(
            $data['primary_contact']['email'],
            $data['password'],
            $data['primary_contact']['email']
        );

        if (is_wp_error($user_id)) {
            return $user_id;
        }

        // Set user role
        $user = new WP_User($user_id);
        $user->set_role('partner');

        // Update user meta
        update_user_meta($user_id, 'first_name', $data['primary_contact']['full_name']);
        update_user_meta($user_id, 'partner_profile_id', $partner_id);
        update_user_meta($user_id, 'phone', $data['primary_contact']['phone']);

        // Link partner profile to user
        update_post_meta($partner_id, 'user_id', $user_id);

        return $user_id;
    }

    /**
     * Handle file upload
     */
    private function handle_file_upload($file_key) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');

        $file = $_FILES[$file_key];
        $upload = wp_handle_upload($file, array('test_form' => false));

        if (isset($upload['error'])) {
            return new WP_Error('upload_error', $upload['error']);
        }

        return $upload;
    }

    /**
     * Send verification email
     */
    private function send_verification_email($user_id, $email) {
        $verification_token = wp_generate_password(32, false);
        update_user_meta($user_id, 'email_verification_token', $verification_token);

        $verification_link = add_query_arg(array(
            'action' => 'verify_email',
            'token' => $verification_token,
            'user' => $user_id
        ), home_url());

        $subject = __('Verify your Partner Account', 'partner-ciu-manager');
        $message = sprintf(
            __("Welcome!\n\nPlease verify your email address by clicking the link below:\n\n%s\n\nIf you didn't create this account, please ignore this email.", 'partner-ciu-manager'),
            $verification_link
        );

        wp_mail($email, $subject, $message);
    }

    /**
     * Notify admin of new partner
     */
    private function notify_admin_new_partner($partner_id, $data) {
        $admin_email = get_option('admin_email');

        $subject = sprintf(__('New Partner Registration: %s', 'partner-ciu-manager'), $data['company_legal_name']);
        $message = sprintf(
            __("A new partner has registered:\n\nCompany: %s\nContact: %s\nEmail: %s\nWebsite: %s\n\nReview and approve: %s", 'partner-ciu-manager'),
            $data['company_legal_name'],
            $data['primary_contact']['full_name'],
            $data['primary_contact']['email'],
            $data['company_website'],
            admin_url('post.php?post=' . $partner_id . '&action=edit')
        );

        wp_mail($admin_email, $subject, $message);
    }
}
