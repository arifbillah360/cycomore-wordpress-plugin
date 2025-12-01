<?php
/**
 * Partner User Role
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Partner_Role
 */
class Partner_Role {

    /**
     * Create partner role
     */
    public static function create_role() {
        // Add partner role
        add_role('partner', __('Partner', 'partner-ciu-manager'), array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
            'publish_posts' => false,
            'upload_files' => true,
        ));

        // Add capabilities to administrator
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('edit_partner_profile');
            $admin->add_cap('read_partner_profile');
            $admin->add_cap('delete_partner_profile');
            $admin->add_cap('edit_partner_profiles');
            $admin->add_cap('edit_others_partner_profiles');
            $admin->add_cap('publish_partner_profiles');
            $admin->add_cap('read_private_partner_profiles');
            $admin->add_cap('delete_partner_profiles');
        }

        // Add partner capabilities
        $partner = get_role('partner');
        if ($partner) {
            $partner->add_cap('partner');
            $partner->add_cap('access_partner_dashboard');
        }
    }

    /**
     * Remove partner role
     */
    public static function remove_role() {
        remove_role('partner');
    }

    /**
     * Check if user is a partner
     *
     * @param int|null $user_id
     * @return bool
     */
    public static function is_partner($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $user = get_userdata($user_id);
        return $user && in_array('partner', (array) $user->roles);
    }

    /**
     * Get partner profile for user
     *
     * @param int|null $user_id
     * @return WP_Post|null
     */
    public static function get_partner_profile($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        return Partner_Post_Type::get_partner_by_user_id($user_id);
    }
}
