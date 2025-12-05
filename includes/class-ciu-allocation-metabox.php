<?php
/**
 * CIU Allocation Meta Box
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CIU_Allocation_Metabox
 */
class CIU_Allocation_Metabox {

    /**
     * Single instance
     *
     * @var CIU_Allocation_Metabox
     */
    protected static $instance = null;

    /**
     * Categories
     *
     * @var array
     */
    private $categories;

    /**
     * Get instance
     *
     * @return CIU_Allocation_Metabox
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
        $this->categories = $this->get_categories();

        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('save_post_partner_profile', array($this, 'save_meta_box'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Get predefined categories
     *
     * @return array
     */
    private function get_categories() {
        return array(
            'oceans' => array(
                'name' => __('Oceans', 'partner-ciu-manager'),
                'icon' => '🌊',
                'slug' => 'oceans'
            ),
            'fast_fashion' => array(
                'name' => __('Fast Fashion', 'partner-ciu-manager'),
                'icon' => '👕',
                'slug' => 'fast_fashion'
            ),
            'forests' => array(
                'name' => __('Forests', 'partner-ciu-manager'),
                'icon' => '🌳',
                'slug' => 'forests'
            ),
            'endangered_species' => array(
                'name' => __('Endangered Species', 'partner-ciu-manager'),
                'icon' => '🐅',
                'slug' => 'endangered_species'
            ),
            'sustainable_tourism' => array(
                'name' => __('Sustainable Tourism', 'partner-ciu-manager'),
                'icon' => '🏖️',
                'slug' => 'sustainable_tourism'
            ),
            'rivers' => array(
                'name' => __('Rivers', 'partner-ciu-manager'),
                'icon' => '🚰',
                'slug' => 'rivers'
            ),
            'eco_waste' => array(
                'name' => __('Eco Waste', 'partner-ciu-manager'),
                'icon' => '♻️',
                'slug' => 'eco_waste'
            ),
            'soil_erosion' => array(
                'name' => __('Soil Erosion', 'partner-ciu-manager'),
                'icon' => '🌱',
                'slug' => 'soil_erosion'
            )
        );
    }

    /**
     * Add meta box
     */
    public function add_meta_box() {
        add_meta_box(
            'ciu_allocation_metabox',
            __('CIU Allocation by Category', 'partner-ciu-manager'),
            array($this, 'render_meta_box'),
            'partner_profile',
            'normal',
            'high'
        );
    }

    /**
     * Render meta box content
     *
     * @param WP_Post $post
     */
    public function render_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('ciu_allocation_nonce_action', 'ciu_allocation_nonce');

        // Get existing data
        $allocations = get_post_meta($post->ID, 'ciu_allocations', true);
        if (empty($allocations) || !is_array($allocations)) {
            $allocations = $this->get_empty_allocations();
        }

        // Get summary data
        $summary = get_post_meta($post->ID, 'ciu_summary', true);
        if (empty($summary)) {
            $summary = array(
                'total_cius' => 0,
                'total_pending' => 0,
                'total_active' => 0,
                'total_verified' => 0,
                'total_funds' => 0
            );
        }

        // Include template
        include PARTNER_CIU_PLUGIN_DIR . 'templates/admin-ciu-allocation-metabox.php';
    }

    /**
     * Get empty allocations structure
     *
     * @return array
     */
    private function get_empty_allocations() {
        $allocations = array();

        foreach ($this->categories as $slug => $category) {
            $allocations[$slug] = array(
                'category_name' => $category['name'],
                'category_icon' => $category['icon'],
                'total_cius' => 0,
                'collections' => array()
            );
        }

        return $allocations;
    }

    /**
     * Save meta box data
     *
     * @param int $post_id
     * @param WP_Post $post
     */
    public function save_meta_box($post_id, $post) {
        // Verify nonce
        if (!isset($_POST['ciu_allocation_nonce']) ||
            !wp_verify_nonce($_POST['ciu_allocation_nonce'], 'ciu_allocation_nonce_action')) {
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

        // Get and sanitize data
        if (isset($_POST['ciu_allocations'])) {
            $allocations = $this->sanitize_allocations($_POST['ciu_allocations']);

            // Calculate totals
            $allocations = $this->calculate_category_totals($allocations);
            $summary = $this->calculate_summary($allocations);

            // Save data
            update_post_meta($post_id, 'ciu_allocations', $allocations);
            update_post_meta($post_id, 'ciu_summary', $summary);

            // Also update the old meta fields for backward compatibility
            update_post_meta($post_id, '_pending_cius', $summary['total_pending']);
            update_post_meta($post_id, '_active_cius', $summary['total_active']);
            update_post_meta($post_id, '_verified_cius', $summary['total_verified']);
        }
    }

    /**
     * Sanitize allocations data
     *
     * @param array $data
     * @return array
     */
    private function sanitize_allocations($data) {
        $sanitized = array();

        foreach ($this->categories as $slug => $category) {
            if (isset($data[$slug]['collections']) && is_array($data[$slug]['collections'])) {
                $sanitized[$slug] = array(
                    'category_name' => $category['name'],
                    'category_icon' => $category['icon'],
                    'total_cius' => 0,
                    'collections' => array()
                );

                foreach ($data[$slug]['collections'] as $coll_id => $collection) {
                    // Skip if title is empty
                    if (empty($collection['title'])) {
                        continue;
                    }

                    $sanitized[$slug]['collections'][$coll_id] = array(
                        'collection_id' => sanitize_text_field($coll_id),
                        'collection_number' => absint($collection['collection_number'] ?? 1),
                        'collection_title' => sanitize_text_field($collection['title']),
                        'ciu_amount' => absint($collection['ciu_amount'] ?? 0),
                        'status' => in_array($collection['status'] ?? '', array('pending', 'active', 'verified'))
                                    ? $collection['status']
                                    : 'pending',
                        'description' => sanitize_textarea_field($collection['description'] ?? ''),
                        'collection_date' => sanitize_text_field($collection['collection_date'] ?? ''),
                        'date_added' => sanitize_text_field($collection['date_added'] ?? date('Y-m-d')),
                        'last_modified' => current_time('mysql')
                    );
                }
            } else {
                $sanitized[$slug] = array(
                    'category_name' => $category['name'],
                    'category_icon' => $category['icon'],
                    'total_cius' => 0,
                    'collections' => array()
                );
            }
        }

        return $sanitized;
    }

    /**
     * Calculate category totals
     *
     * @param array $allocations
     * @return array
     */
    private function calculate_category_totals($allocations) {
        foreach ($allocations as $slug => &$category) {
            $total = 0;

            if (!empty($category['collections'])) {
                foreach ($category['collections'] as $collection) {
                    $total += $collection['ciu_amount'];
                }
            }

            $category['total_cius'] = $total;
        }

        return $allocations;
    }

    /**
     * Calculate summary
     *
     * @param array $allocations
     * @return array
     */
    private function calculate_summary($allocations) {
        $summary = array(
            'total_cius' => 0,
            'total_pending' => 0,
            'total_active' => 0,
            'total_verified' => 0,
            'total_funds' => 0,
            'last_updated' => current_time('mysql')
        );

        foreach ($allocations as $category) {
            if (!empty($category['collections'])) {
                foreach ($category['collections'] as $collection) {
                    $amount = $collection['ciu_amount'];
                    $summary['total_cius'] += $amount;

                    switch ($collection['status']) {
                        case 'pending':
                            $summary['total_pending'] += $amount;
                            break;
                        case 'active':
                            $summary['total_active'] += $amount;
                            break;
                        case 'verified':
                            $summary['total_verified'] += $amount;
                            break;
                    }
                }
            }
        }

        // Get CIU price from settings
        $ciu_price = Partner_CIU_Settings::get_ciu_price();
        $summary['total_funds'] = $summary['total_cius'] * $ciu_price;

        return $summary;
    }

    /**
     * Enqueue scripts and styles
     *
     * @param string $hook
     */
    public function enqueue_scripts($hook) {
        global $post_type;

        if ($hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }

        if ($post_type !== 'partner_profile') {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'ciu-allocation-admin',
            PARTNER_CIU_PLUGIN_URL . 'assets/css/ciu-allocation-admin.css',
            array(),
            PARTNER_CIU_VERSION
        );

        // Enqueue JS
        wp_enqueue_script(
            'ciu-allocation-admin',
            PARTNER_CIU_PLUGIN_URL . 'assets/js/ciu-allocation-admin.js',
            array('jquery'),
            PARTNER_CIU_VERSION,
            true
        );

        // Localize script
        wp_localize_script('ciu-allocation-admin', 'ciuAllocationData', array(
            'ciuPrice' => Partner_CIU_Settings::get_ciu_price(),
            'currencySymbol' => '£', // GBP symbol (default currency)
            'strings' => array(
                'confirmRemove' => __('Are you sure you want to remove this collection?', 'partner-ciu-manager'),
                'collectionTitle' => __('Collection Title', 'partner-ciu-manager'),
                'ciuAmount' => __('CIU Amount', 'partner-ciu-manager'),
                'status' => __('Status', 'partner-ciu-manager'),
                'pending' => __('Pending', 'partner-ciu-manager'),
                'active' => __('Active', 'partner-ciu-manager'),
                'verified' => __('Verified/Retired', 'partner-ciu-manager'),
                'description' => __('Description (Optional)', 'partner-ciu-manager'),
                'externalLink' => __('External Link (Optional)', 'partner-ciu-manager'),
                'remove' => __('Remove', 'partner-ciu-manager'),
            )
        ));
    }
}
