<?php
/**
 * Data Aggregator for Public Dashboard
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Partner_Data_Aggregator
 */
class Partner_Data_Aggregator {

    /**
     * Single instance
     *
     * @var Partner_Data_Aggregator
     */
    protected static $instance = null;

    /**
     * Cache key prefix
     *
     * @var string
     */
    private $cache_key_prefix = 'partner_ciu_aggregate_';

    /**
     * Cache duration (1 hour)
     *
     * @var int
     */
    private $cache_duration = 3600;

    /**
     * Get instance
     *
     * @return Partner_Data_Aggregator
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get all aggregated CIU data
     *
     * @param bool $use_cache
     * @return array
     */
    public function get_all_data($use_cache = true) {
        $cache_key = $this->cache_key_prefix . 'all_data';

        if ($use_cache) {
            $cached_data = get_transient($cache_key);
            if ($cached_data !== false) {
                return $cached_data;
            }
        }

        $data = array(
            'totals' => $this->get_totals(),
            'partners' => $this->get_partners_data(),
            'collections' => $this->get_collections_data(),
            'timeline' => $this->get_timeline_data(),
            'top_partners' => $this->get_top_partners(10),
            'statistics' => $this->get_statistics(),
            'last_updated' => current_time('mysql'),
        );

        set_transient($cache_key, $data, $this->cache_duration);

        return $data;
    }

    /**
     * Get total CIU statistics
     *
     * @return array
     */
    public function get_totals() {
        $cache_key = $this->cache_key_prefix . 'totals';
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $partners = $this->get_all_partners();

        $totals = array(
            'total_cius' => 0,
            'pending_cius' => 0,
            'active_cius' => 0,
            'verified_cius' => 0,
            'total_funds' => 0,
            'active_partners' => 0,
            'hero_partners' => 0,
        );

        foreach ($partners as $partner) {
            $pending = get_post_meta($partner->ID, '_pending_cius', true) ?: 0;
            $active = get_post_meta($partner->ID, '_active_cius', true) ?: 0;
            $verified = get_post_meta($partner->ID, '_verified_cius', true) ?: 0;
            $funds = get_post_meta($partner->ID, '_total_funds', true) ?: 0;
            $is_hero = get_post_meta($partner->ID, '_is_hero_partner', true);

            $totals['pending_cius'] += (int) $pending;
            $totals['active_cius'] += (int) $active;
            $totals['verified_cius'] += (int) $verified;
            $totals['total_funds'] += (float) $funds;

            if ($pending + $active + $verified > 0) {
                $totals['active_partners']++;
            }

            if ($is_hero) {
                $totals['hero_partners']++;
            }
        }

        $totals['total_cius'] = $totals['pending_cius'] + $totals['active_cius'] + $totals['verified_cius'];

        // Calculate percentages
        if ($totals['total_cius'] > 0) {
            $totals['pending_percentage'] = round(($totals['pending_cius'] / $totals['total_cius']) * 100, 1);
            $totals['active_percentage'] = round(($totals['active_cius'] / $totals['total_cius']) * 100, 1);
            $totals['verified_percentage'] = round(($totals['verified_cius'] / $totals['total_cius']) * 100, 1);
        } else {
            $totals['pending_percentage'] = 0;
            $totals['active_percentage'] = 0;
            $totals['verified_percentage'] = 0;
        }

        set_transient($cache_key, $totals, $this->cache_duration);

        return $totals;
    }

    /**
     * Get all partners data
     *
     * @param array $args
     * @return array
     */
    public function get_partners_data($args = array()) {
        $defaults = array(
            'orderby' => 'total_cius',
            'order' => 'DESC',
            'search' => '',
            'status_filter' => '',
            'per_page' => -1,
            'page' => 1,
        );

        $args = wp_parse_args($args, $defaults);

        $partners = $this->get_all_partners();
        $partners_data = array();

        foreach ($partners as $partner) {
            $partner_data = $this->get_partner_data($partner->ID);

            // Apply search filter
            if (!empty($args['search'])) {
                if (stripos($partner_data['name'], $args['search']) === false) {
                    continue;
                }
            }

            // Apply status filter
            if (!empty($args['status_filter'])) {
                $status_key = $args['status_filter'] . '_cius';
                if ($partner_data[$status_key] <= 0) {
                    continue;
                }
            }

            $partners_data[] = $partner_data;
        }

        // Sort partners
        usort($partners_data, function($a, $b) use ($args) {
            // Hero partners always first
            if ($a['is_hero'] && !$b['is_hero']) {
                return -1;
            }
            if (!$a['is_hero'] && $b['is_hero']) {
                return 1;
            }

            // Then sort by specified field
            $field = $args['orderby'];
            $order = strtoupper($args['order']);

            if ($field === 'name') {
                $result = strcmp($a['name'], $b['name']);
            } else {
                $result = $a[$field] <=> $b[$field];
            }

            return $order === 'ASC' ? $result : -$result;
        });

        // Pagination
        if ($args['per_page'] > 0) {
            $offset = ($args['page'] - 1) * $args['per_page'];
            $partners_data = array_slice($partners_data, $offset, $args['per_page']);
        }

        return $partners_data;
    }

    /**
     * Get single partner data
     *
     * @param int $partner_id
     * @return array
     */
    public function get_partner_data($partner_id) {
        $partner = get_post($partner_id);

        if (!$partner) {
            return array();
        }

        $pending = get_post_meta($partner_id, '_pending_cius', true) ?: 0;
        $active = get_post_meta($partner_id, '_active_cius', true) ?: 0;
        $verified = get_post_meta($partner_id, '_verified_cius', true) ?: 0;
        $total_cius = $pending + $active + $verified;

        return array(
            'id' => $partner_id,
            'name' => $partner->post_title,
            'logo' => get_post_meta($partner_id, '_partner_logo', true),
            'is_hero' => (bool) get_post_meta($partner_id, '_is_hero_partner', true),
            'hero_highlight' => get_post_meta($partner_id, '_hero_highlight_text', true),
            'pending_cius' => (int) $pending,
            'active_cius' => (int) $active,
            'verified_cius' => (int) $verified,
            'total_cius' => $total_cius,
            'total_funds' => (float) get_post_meta($partner_id, '_total_funds', true) ?: 0,
            'last_purchase_date' => get_post_meta($partner_id, '_last_purchase_date', true),
            'collections' => get_post_meta($partner_id, '_collection_breakdown', true) ?: array(),
        );
    }

    /**
     * Get collections data
     *
     * @return array
     */
    public function get_collections_data() {
        $cache_key = $this->cache_key_prefix . 'collections';
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $partners = $this->get_all_partners();
        $collections = array();

        foreach ($partners as $partner) {
            $partner_collections = get_post_meta($partner->ID, '_collection_breakdown', true);

            if (!is_array($partner_collections)) {
                continue;
            }

            foreach ($partner_collections as $collection) {
                $name = $collection['name'];
                $count = (int) $collection['count'];

                if (!isset($collections[$name])) {
                    $collections[$name] = array(
                        'name' => $name,
                        'total_cius' => 0,
                        'partners' => 0,
                    );
                }

                $collections[$name]['total_cius'] += $count;
                $collections[$name]['partners']++;
            }
        }

        // Calculate percentages
        $total_cius = $this->get_totals()['total_cius'];
        foreach ($collections as &$collection) {
            if ($total_cius > 0) {
                $collection['percentage'] = round(($collection['total_cius'] / $total_cius) * 100, 1);
            } else {
                $collection['percentage'] = 0;
            }
        }

        // Sort by total CIUs descending
        uasort($collections, function($a, $b) {
            return $b['total_cius'] <=> $a['total_cius'];
        });

        $collections = array_values($collections);

        set_transient($cache_key, $collections, $this->cache_duration);

        return $collections;
    }

    /**
     * Get timeline data (monthly)
     *
     * @param int $months Number of months to retrieve
     * @return array
     */
    public function get_timeline_data($months = 12) {
        $cache_key = $this->cache_key_prefix . 'timeline_' . $months;
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $timeline = array();
        $current_date = new DateTime();

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = clone $current_date;
            $date->modify("-$i months");
            $month_key = $date->format('Y-m');
            $month_label = $date->format('M Y');

            $timeline[$month_key] = array(
                'label' => $month_label,
                'cius_purchased' => 0,
                'funds_contributed' => 0,
                'transactions' => 0,
            );
        }

        // Get all transactions
        $transactions = get_posts(array(
            'post_type' => 'ciu_transaction',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ));

        foreach ($transactions as $transaction) {
            $purchase_date = get_post_meta($transaction->ID, '_purchase_date', true);

            if (!$purchase_date) {
                continue;
            }

            $date = new DateTime($purchase_date);
            $month_key = $date->format('Y-m');

            if (isset($timeline[$month_key])) {
                $timeline[$month_key]['cius_purchased'] += (int) get_post_meta($transaction->ID, '_ciu_quantity', true);
                $timeline[$month_key]['funds_contributed'] += (float) get_post_meta($transaction->ID, '_purchase_amount', true);
                $timeline[$month_key]['transactions']++;
            }
        }

        $timeline = array_values($timeline);

        set_transient($cache_key, $timeline, $this->cache_duration);

        return $timeline;
    }

    /**
     * Get top partners
     *
     * @param int $limit
     * @return array
     */
    public function get_top_partners($limit = 10) {
        $partners_data = $this->get_partners_data(array(
            'orderby' => 'total_cius',
            'order' => 'DESC',
        ));

        return array_slice($partners_data, 0, $limit);
    }

    /**
     * Get statistics
     *
     * @return array
     */
    public function get_statistics() {
        $cache_key = $this->cache_key_prefix . 'statistics';
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $partners = $this->get_all_partners();
        $totals = $this->get_totals();

        $stats = array(
            'average_cius_per_partner' => 0,
            'average_contribution_per_partner' => 0,
            'total_transactions' => 0,
            'average_ciu_price' => 0,
        );

        if ($totals['active_partners'] > 0) {
            $stats['average_cius_per_partner'] = round($totals['total_cius'] / $totals['active_partners'], 1);
            $stats['average_contribution_per_partner'] = round($totals['total_funds'] / $totals['active_partners'], 2);
        }

        // Get total transactions
        $transactions = wp_count_posts('ciu_transaction');
        $stats['total_transactions'] = $transactions->publish ?: 0;

        // Calculate average CIU price
        if ($totals['total_cius'] > 0) {
            $stats['average_ciu_price'] = round($totals['total_funds'] / $totals['total_cius'], 2);
        }

        set_transient($cache_key, $stats, $this->cache_duration);

        return $stats;
    }

    /**
     * Get all partners
     *
     * @return array
     */
    private function get_all_partners() {
        return get_posts(array(
            'post_type' => 'partner_profile',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ));
    }

    /**
     * Clear all caches
     */
    public function clear_cache() {
        global $wpdb;

        $pattern = '_transient_' . $this->cache_key_prefix . '%';
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->options WHERE option_name LIKE %s",
                $pattern
            )
        );

        $pattern = '_transient_timeout_' . $this->cache_key_prefix . '%';
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->options WHERE option_name LIKE %s",
                $pattern
            )
        );
    }

    /**
     * Get comparison data (current vs previous period)
     *
     * @param int $days
     * @return array
     */
    public function get_comparison_data($days = 30) {
        $current_start = new DateTime("-$days days");
        $current_end = new DateTime();
        $previous_start = new DateTime("-" . ($days * 2) . " days");
        $previous_end = clone $current_start;

        $current_data = $this->get_period_data($current_start, $current_end);
        $previous_data = $this->get_period_data($previous_start, $previous_end);

        return array(
            'current' => $current_data,
            'previous' => $previous_data,
            'comparison' => array(
                'cius_change' => $current_data['cius'] - $previous_data['cius'],
                'cius_change_percentage' => $previous_data['cius'] > 0
                    ? round((($current_data['cius'] - $previous_data['cius']) / $previous_data['cius']) * 100, 1)
                    : 0,
                'funds_change' => $current_data['funds'] - $previous_data['funds'],
                'funds_change_percentage' => $previous_data['funds'] > 0
                    ? round((($current_data['funds'] - $previous_data['funds']) / $previous_data['funds']) * 100, 1)
                    : 0,
            ),
        );
    }

    /**
     * Get data for specific period
     *
     * @param DateTime $start_date
     * @param DateTime $end_date
     * @return array
     */
    private function get_period_data($start_date, $end_date) {
        $transactions = get_posts(array(
            'post_type' => 'ciu_transaction',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'date_query' => array(
                array(
                    'after' => $start_date->format('Y-m-d'),
                    'before' => $end_date->format('Y-m-d'),
                    'inclusive' => true,
                ),
            ),
        ));

        $data = array(
            'cius' => 0,
            'funds' => 0,
            'transactions' => count($transactions),
        );

        foreach ($transactions as $transaction) {
            $data['cius'] += (int) get_post_meta($transaction->ID, '_ciu_quantity', true);
            $data['funds'] += (float) get_post_meta($transaction->ID, '_purchase_amount', true);
        }

        return $data;
    }
}
