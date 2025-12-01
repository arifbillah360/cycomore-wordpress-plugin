<?php
/**
 * Chart Generator for Public Dashboard
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Partner_Chart_Generator
 */
class Partner_Chart_Generator {

    /**
     * Single instance
     *
     * @var Partner_Chart_Generator
     */
    protected static $instance = null;

    /**
     * Data aggregator instance
     *
     * @var Partner_Data_Aggregator
     */
    private $aggregator;

    /**
     * Get instance
     *
     * @return Partner_Chart_Generator
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
        $this->aggregator = Partner_Data_Aggregator::instance();
    }

    /**
     * Generate pie chart data for CIU status distribution
     *
     * @return array
     */
    public function get_status_pie_chart_data() {
        $totals = $this->aggregator->get_totals();

        return array(
            'type' => 'pie',
            'data' => array(
                'labels' => array(
                    __('Pending', 'partner-ciu-manager'),
                    __('Active', 'partner-ciu-manager'),
                    __('Verified/Retired', 'partner-ciu-manager'),
                ),
                'datasets' => array(
                    array(
                        'data' => array(
                            $totals['pending_cius'],
                            $totals['active_cius'],
                            $totals['verified_cius'],
                        ),
                        'backgroundColor' => array(
                            '#FFA500',
                            '#2196F3',
                            '#4CAF50',
                        ),
                        'borderWidth' => 2,
                        'borderColor' => '#ffffff',
                    ),
                ),
            ),
            'options' => array(
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => array(
                    'legend' => array(
                        'position' => 'bottom',
                        'labels' => array(
                            'padding' => 15,
                            'font' => array(
                                'size' => 14,
                            ),
                        ),
                    ),
                    'title' => array(
                        'display' => true,
                        'text' => __('CIU Status Distribution', 'partner-ciu-manager'),
                        'font' => array(
                            'size' => 18,
                            'weight' => 'bold',
                        ),
                    ),
                    'tooltip' => array(
                        'callbacks' => array(
                            'label' => 'function(context) {
                                return context.label + ": " + context.parsed.toLocaleString() + " CIUs (" + Math.round((context.parsed / ' . $totals['total_cius'] . ') * 100) + "%)";
                            }',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Generate bar chart data for top partners
     *
     * @param int $limit
     * @return array
     */
    public function get_top_partners_bar_chart_data($limit = 10) {
        $top_partners = $this->aggregator->get_top_partners($limit);

        $labels = array();
        $data = array();

        foreach ($top_partners as $partner) {
            $labels[] = $partner['name'];
            $data[] = $partner['total_cius'];
        }

        return array(
            'type' => 'bar',
            'data' => array(
                'labels' => $labels,
                'datasets' => array(
                    array(
                        'label' => __('Total CIUs', 'partner-ciu-manager'),
                        'data' => $data,
                        'backgroundColor' => '#2196F3',
                        'borderColor' => '#1976D2',
                        'borderWidth' => 1,
                    ),
                ),
            ),
            'options' => array(
                'responsive' => true,
                'maintainAspectRatio' => false,
                'indexAxis' => 'y',
                'plugins' => array(
                    'legend' => array(
                        'display' => false,
                    ),
                    'title' => array(
                        'display' => true,
                        'text' => sprintf(__('Top %d Contributing Partners', 'partner-ciu-manager'), $limit),
                        'font' => array(
                            'size' => 18,
                            'weight' => 'bold',
                        ),
                    ),
                    'tooltip' => array(
                        'callbacks' => array(
                            'label' => 'function(context) {
                                return context.parsed.x.toLocaleString() + " CIUs";
                            }',
                        ),
                    ),
                ),
                'scales' => array(
                    'x' => array(
                        'beginAtZero' => true,
                        'ticks' => array(
                            'callback' => 'function(value) { return value.toLocaleString(); }',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Generate line chart data for CIU growth timeline
     *
     * @param int $months
     * @return array
     */
    public function get_timeline_line_chart_data($months = 12) {
        $timeline = $this->aggregator->get_timeline_data($months);

        $labels = array();
        $cius_data = array();
        $funds_data = array();

        foreach ($timeline as $period) {
            $labels[] = $period['label'];
            $cius_data[] = $period['cius_purchased'];
            $funds_data[] = $period['funds_contributed'];
        }

        return array(
            'type' => 'line',
            'data' => array(
                'labels' => $labels,
                'datasets' => array(
                    array(
                        'label' => __('CIUs Purchased', 'partner-ciu-manager'),
                        'data' => $cius_data,
                        'borderColor' => '#2196F3',
                        'backgroundColor' => 'rgba(33, 150, 243, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                        'pointRadius' => 5,
                        'pointHoverRadius' => 7,
                    ),
                ),
            ),
            'options' => array(
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => array(
                    'legend' => array(
                        'display' => true,
                        'position' => 'top',
                    ),
                    'title' => array(
                        'display' => true,
                        'text' => __('CIU Purchase Growth', 'partner-ciu-manager'),
                        'font' => array(
                            'size' => 18,
                            'weight' => 'bold',
                        ),
                    ),
                    'tooltip' => array(
                        'callbacks' => array(
                            'label' => 'function(context) {
                                return context.dataset.label + ": " + context.parsed.y.toLocaleString() + " CIUs";
                            }',
                        ),
                    ),
                ),
                'scales' => array(
                    'y' => array(
                        'beginAtZero' => true,
                        'ticks' => array(
                            'callback' => 'function(value) { return value.toLocaleString(); }',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Generate donut chart data for collection allocation
     *
     * @return array
     */
    public function get_collections_donut_chart_data() {
        $collections = $this->aggregator->get_collections_data();

        $labels = array();
        $data = array();
        $colors = array();

        $color_palette = array(
            '#2196F3', '#4CAF50', '#FFA500', '#9C27B0', '#F44336',
            '#00BCD4', '#FFEB3B', '#795548', '#607D8B', '#E91E63',
        );

        foreach ($collections as $index => $collection) {
            $labels[] = $collection['name'];
            $data[] = $collection['total_cius'];
            $colors[] = $color_palette[$index % count($color_palette)];
        }

        return array(
            'type' => 'doughnut',
            'data' => array(
                'labels' => $labels,
                'datasets' => array(
                    array(
                        'data' => $data,
                        'backgroundColor' => $colors,
                        'borderWidth' => 2,
                        'borderColor' => '#ffffff',
                    ),
                ),
            ),
            'options' => array(
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => array(
                    'legend' => array(
                        'position' => 'bottom',
                        'labels' => array(
                            'padding' => 15,
                            'font' => array(
                                'size' => 14,
                            ),
                        ),
                    ),
                    'title' => array(
                        'display' => true,
                        'text' => __('Collection-wise CIU Allocation', 'partner-ciu-manager'),
                        'font' => array(
                            'size' => 18,
                            'weight' => 'bold',
                        ),
                    ),
                    'tooltip' => array(
                        'callbacks' => array(
                            'label' => 'function(context) {
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = Math.round((context.parsed / total) * 100);
                                return context.label + ": " + context.parsed.toLocaleString() + " CIUs (" + percentage + "%)";
                            }',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Generate mixed chart data (line + bar) for timeline with funds
     *
     * @param int $months
     * @return array
     */
    public function get_timeline_mixed_chart_data($months = 12) {
        $timeline = $this->aggregator->get_timeline_data($months);

        $labels = array();
        $cius_data = array();
        $funds_data = array();

        foreach ($timeline as $period) {
            $labels[] = $period['label'];
            $cius_data[] = $period['cius_purchased'];
            $funds_data[] = $period['funds_contributed'];
        }

        return array(
            'type' => 'bar',
            'data' => array(
                'labels' => $labels,
                'datasets' => array(
                    array(
                        'type' => 'bar',
                        'label' => __('CIUs Purchased', 'partner-ciu-manager'),
                        'data' => $cius_data,
                        'backgroundColor' => 'rgba(33, 150, 243, 0.7)',
                        'borderColor' => '#2196F3',
                        'borderWidth' => 1,
                        'yAxisID' => 'y',
                    ),
                    array(
                        'type' => 'line',
                        'label' => __('Funds Contributed', 'partner-ciu-manager'),
                        'data' => $funds_data,
                        'borderColor' => '#4CAF50',
                        'backgroundColor' => 'rgba(76, 175, 80, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                        'yAxisID' => 'y1',
                    ),
                ),
            ),
            'options' => array(
                'responsive' => true,
                'maintainAspectRatio' => false,
                'interaction' => array(
                    'mode' => 'index',
                    'intersect' => false,
                ),
                'plugins' => array(
                    'legend' => array(
                        'display' => true,
                        'position' => 'top',
                    ),
                    'title' => array(
                        'display' => true,
                        'text' => __('CIUs & Funds Timeline', 'partner-ciu-manager'),
                        'font' => array(
                            'size' => 18,
                            'weight' => 'bold',
                        ),
                    ),
                ),
                'scales' => array(
                    'y' => array(
                        'type' => 'linear',
                        'display' => true,
                        'position' => 'left',
                        'title' => array(
                            'display' => true,
                            'text' => __('CIUs', 'partner-ciu-manager'),
                        ),
                    ),
                    'y1' => array(
                        'type' => 'linear',
                        'display' => true,
                        'position' => 'right',
                        'title' => array(
                            'display' => true,
                            'text' => __('Funds (£)', 'partner-ciu-manager'),
                        ),
                        'grid' => array(
                            'drawOnChartArea' => false,
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Render chart container
     *
     * @param string $chart_id
     * @param string $chart_type
     * @param int $height
     * @return string
     */
    public static function render_chart_container($chart_id, $chart_type, $height = 400) {
        $data_attr = '';

        switch ($chart_type) {
            case 'status_pie':
                $data_attr = 'data-chart-type="status_pie"';
                break;
            case 'top_partners':
                $data_attr = 'data-chart-type="top_partners"';
                break;
            case 'timeline':
                $data_attr = 'data-chart-type="timeline"';
                break;
            case 'collections':
                $data_attr = 'data-chart-type="collections"';
                break;
            case 'timeline_mixed':
                $data_attr = 'data-chart-type="timeline_mixed"';
                break;
        }

        return sprintf(
            '<div class="chart-container" style="position: relative; height: %dpx;">
                <canvas id="%s" %s></canvas>
            </div>',
            $height,
            esc_attr($chart_id),
            $data_attr
        );
    }
}
