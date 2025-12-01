<?php
/**
 * Public CIU Dashboard Template
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$totals = Partner_Public_Dashboard::format_totals($data['totals']);
$chart_generator = Partner_Chart_Generator::instance();
?>

<div class="public-ciu-dashboard" data-aos="fade-in">

    <!-- Hero Section -->
    <?php if ($settings['show_hero_section']): ?>
    <section class="dashboard-hero" data-aos="fade-down">
        <div class="hero-content">
            <h1 class="hero-title"><?php esc_html_e('CIU Transformation Journey', 'partner-ciu-manager'); ?></h1>
            <p class="hero-subtitle"><?php esc_html_e('Tracking our collective impact through Cumulative Impact Units', 'partner-ciu-manager'); ?></p>

            <div class="hero-stats">
                <div class="hero-stat-item" data-aos="zoom-in" data-aos-delay="100">
                    <i class="fas fa-chart-line"></i>
                    <div class="stat-content">
                        <span class="stat-value" data-count-to="<?php echo esc_attr($data['totals']['total_cius']); ?>">0</span>
                        <span class="stat-label"><?php esc_html_e('Total CIUs', 'partner-ciu-manager'); ?></span>
                    </div>
                </div>

                <div class="hero-stat-item" data-aos="zoom-in" data-aos-delay="200">
                    <i class="fas fa-pound-sign"></i>
                    <div class="stat-content">
                        <span class="stat-value"><?php echo esc_html($totals['total_funds']); ?></span>
                        <span class="stat-label"><?php esc_html_e('Total Funds', 'partner-ciu-manager'); ?></span>
                    </div>
                </div>

                <div class="hero-stat-item" data-aos="zoom-in" data-aos-delay="300">
                    <i class="fas fa-users"></i>
                    <div class="stat-content">
                        <span class="stat-value" data-count-to="<?php echo esc_attr($totals['active_partners']); ?>">0</span>
                        <span class="stat-label"><?php esc_html_e('Active Partners', 'partner-ciu-manager'); ?></span>
                    </div>
                </div>

                <div class="hero-stat-item" data-aos="zoom-in" data-aos-delay="400">
                    <i class="fas fa-clock"></i>
                    <div class="stat-content">
                        <span class="stat-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($data['last_updated']))); ?></span>
                        <span class="stat-label"><?php esc_html_e('Last Updated', 'partner-ciu-manager'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CIU Flow Visualization -->
    <?php if ($settings['show_flow_visualization']): ?>
    <section class="dashboard-section ciu-flow-section" data-aos="fade-up">
        <div class="section-container">
            <h2 class="section-title"><?php esc_html_e('CIU Transformation Flow', 'partner-ciu-manager'); ?></h2>

            <div class="flow-visualization">
                <div class="flow-item pending" data-aos="fade-right" data-aos-delay="100">
                    <div class="flow-icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="flow-content">
                        <h3><?php esc_html_e('Pending', 'partner-ciu-manager'); ?></h3>
                        <div class="flow-count"><?php echo esc_html($totals['pending_cius']); ?></div>
                        <div class="flow-percentage"><?php echo esc_html($totals['pending_percentage']); ?>%</div>
                    </div>
                    <div class="flow-progress-bar">
                        <div class="progress-fill pending" style="width: <?php echo esc_attr($totals['pending_percentage']); ?>%"></div>
                    </div>
                </div>

                <div class="flow-arrow" data-aos="fade" data-aos-delay="200">
                    <i class="fas fa-arrow-right"></i>
                </div>

                <div class="flow-item active" data-aos="fade-up" data-aos-delay="300">
                    <div class="flow-icon">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <div class="flow-content">
                        <h3><?php esc_html_e('Active', 'partner-ciu-manager'); ?></h3>
                        <div class="flow-count"><?php echo esc_html($totals['active_cius']); ?></div>
                        <div class="flow-percentage"><?php echo esc_html($totals['active_percentage']); ?>%</div>
                    </div>
                    <div class="flow-progress-bar">
                        <div class="progress-fill active" style="width: <?php echo esc_attr($totals['active_percentage']); %>%"></div>
                    </div>
                </div>

                <div class="flow-arrow" data-aos="fade" data-aos-delay="400">
                    <i class="fas fa-arrow-right"></i>
                </div>

                <div class="flow-item verified" data-aos="fade-left" data-aos-delay="500">
                    <div class="flow-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="flow-content">
                        <h3><?php esc_html_e('Verified/Retired', 'partner-ciu-manager'); ?></h3>
                        <div class="flow-count"><?php echo esc_html($totals['verified_cius']); ?></div>
                        <div class="flow-percentage"><?php echo esc_html($totals['verified_percentage']); ?>%</div>
                    </div>
                    <div class="flow-progress-bar">
                        <div class="progress-fill verified" style="width: <?php echo esc_attr($totals['verified_percentage']); %>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Charts Section -->
    <?php if ($settings['show_charts'] && $atts['show_charts'] === 'true'): ?>
    <section class="dashboard-section charts-section" data-aos="fade-up">
        <div class="section-container">
            <h2 class="section-title"><?php esc_html_e('Visual Analytics', 'partner-ciu-manager'); ?></h2>

            <div class="charts-grid">
                <div class="chart-box" data-aos="flip-left" data-aos-delay="100">
                    <h3 class="chart-title"><?php esc_html_e('Status Distribution', 'partner-ciu-manager'); ?></h3>
                    <?php echo wp_kses_post(Partner_Chart_Generator::render_chart_container('status-pie-chart', 'status_pie', 350)); ?>
                </div>

                <div class="chart-box" data-aos="flip-left" data-aos-delay="200">
                    <h3 class="chart-title"><?php esc_html_e('Top Contributors', 'partner-ciu-manager'); ?></h3>
                    <?php echo wp_kses_post(Partner_Chart_Generator::render_chart_container('top-partners-chart', 'top_partners', 350)); ?>
                </div>

                <?php if (!empty($data['collections'])): ?>
                <div class="chart-box" data-aos="flip-left" data-aos-delay="300">
                    <h3 class="chart-title"><?php esc_html_e('Collection Allocation', 'partner-ciu-manager'); ?></h3>
                    <?php echo wp_kses_post(Partner_Chart_Generator::render_chart_container('collections-chart', 'collections', 350)); ?>
                </div>
                <?php endif; ?>

                <?php if ($atts['show_timeline'] === 'true'): ?>
                <div class="chart-box chart-box-wide" data-aos="flip-left" data-aos-delay="400">
                    <h3 class="chart-title"><?php esc_html_e('Growth Timeline', 'partner-ciu-manager'); ?></h3>
                    <?php echo wp_kses_post(Partner_Chart_Generator::render_chart_container('timeline-chart', 'timeline', 350)); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Partners Section -->
    <?php if ($settings['show_partners_table'] && $atts['show_partners'] === 'true'): ?>
    <section class="dashboard-section partners-section" data-aos="fade-up">
        <div class="section-container">
            <h2 class="section-title"><?php esc_html_e('Partner Contributions', 'partner-ciu-manager'); ?></h2>

            <!-- Filters -->
            <div class="partners-filters" data-aos="fade-down">
                <div class="filter-group">
                    <label for="partner-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="partner-search" placeholder="<?php esc_attr_e('Search partners...', 'partner-ciu-manager'); ?>">
                    </label>
                </div>

                <div class="filter-group">
                    <label for="status-filter">
                        <i class="fas fa-filter"></i>
                        <select id="status-filter">
                            <option value=""><?php esc_html_e('All Statuses', 'partner-ciu-manager'); ?></option>
                            <option value="pending"><?php esc_html_e('Pending', 'partner-ciu-manager'); ?></option>
                            <option value="active"><?php esc_html_e('Active', 'partner-ciu-manager'); ?></option>
                            <option value="verified"><?php esc_html_e('Verified', 'partner-ciu-manager'); ?></option>
                        </select>
                    </label>
                </div>

                <div class="filter-group">
                    <label for="sort-by">
                        <i class="fas fa-sort"></i>
                        <select id="sort-by">
                            <option value="total_cius" selected><?php esc_html_e('Total CIUs', 'partner-ciu-manager'); ?></option>
                            <option value="total_funds"><?php esc_html_e('Total Funds', 'partner-ciu-manager'); ?></option>
                            <option value="name"><?php esc_html_e('Name (A-Z)', 'partner-ciu-manager'); ?></option>
                        </select>
                    </label>
                </div>
            </div>

            <!-- Partners Grid -->
            <div class="partners-grid" id="partners-grid">
                <?php
                $partners_to_display = array_slice($data['partners'], 0, (int) $atts['partners_per_page']);
                foreach ($partners_to_display as $index => $partner):
                    $delay = ($index % 12) * 50;
                    include PARTNER_CIU_PLUGIN_DIR . 'templates/partner-card.php';
                endforeach;
                ?>
            </div>

            <div class="partners-load-more">
                <button type="button" class="btn btn-primary" id="load-more-partners">
                    <?php esc_html_e('Load More Partners', 'partner-ciu-manager'); ?>
                </button>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Collections Section -->
    <?php if ($settings['show_collections'] && $atts['show_collections'] === 'true' && !empty($data['collections'])): ?>
    <section class="dashboard-section collections-section" data-aos="fade-up">
        <div class="section-container">
            <h2 class="section-title"><?php esc_html_e('Collection-wise Impact', 'partner-ciu-manager'); ?></h2>

            <div class="collections-grid">
                <?php foreach ($data['collections'] as $index => $collection):
                    $delay = $index * 100;
                    include PARTNER_CIU_PLUGIN_DIR . 'templates/collection-details.php';
                endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Statistics Section -->
    <section class="dashboard-section statistics-section" data-aos="fade-up">
        <div class="section-container">
            <h2 class="section-title"><?php esc_html_e('Key Statistics', 'partner-ciu-manager'); ?></h2>

            <div class="statistics-grid">
                <div class="stat-box" data-aos="zoom-in" data-aos-delay="100">
                    <i class="fas fa-calculator"></i>
                    <h3><?php esc_html_e('Average CIUs per Partner', 'partner-ciu-manager'); ?></h3>
                    <div class="stat-number"><?php echo esc_html(number_format($data['statistics']['average_cius_per_partner'], 1)); ?></div>
                </div>

                <div class="stat-box" data-aos="zoom-in" data-aos-delay="200">
                    <i class="fas fa-coins"></i>
                    <h3><?php esc_html_e('Average Contribution', 'partner-ciu-manager'); ?></h3>
                    <div class="stat-number"><?php echo esc_html(get_woocommerce_currency_symbol() . number_format($data['statistics']['average_contribution_per_partner'], 2)); ?></div>
                </div>

                <div class="stat-box" data-aos="zoom-in" data-aos-delay="300">
                    <i class="fas fa-receipt"></i>
                    <h3><?php esc_html_e('Total Transactions', 'partner-ciu-manager'); ?></h3>
                    <div class="stat-number"><?php echo esc_html($data['statistics']['total_transactions']); ?></div>
                </div>

                <div class="stat-box" data-aos="zoom-in" data-aos-delay="400">
                    <i class="fas fa-tag"></i>
                    <h3><?php esc_html_e('Average CIU Price', 'partner-ciu-manager'); ?></h3>
                    <div class="stat-number"><?php echo esc_html(get_woocommerce_currency_symbol() . number_format($data['statistics']['average_ciu_price'], 2)); ?></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Share Section -->
    <section class="dashboard-section share-section" data-aos="fade-up">
        <div class="section-container">
            <h3><?php esc_html_e('Share Our Impact', 'partner-ciu-manager'); ?></h3>
            <div class="social-share">
                <a href="https://twitter.com/intent/tweet?text=<?php echo rawurlencode(get_the_title()); ?>&url=<?php echo rawurlencode(get_permalink()); ?>" target="_blank" class="share-btn twitter">
                    <i class="fab fa-twitter"></i> <?php esc_html_e('Twitter', 'partner-ciu-manager'); ?>
                </a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo rawurlencode(get_permalink()); ?>&title=<?php echo rawurlencode(get_the_title()); ?>" target="_blank" class="share-btn linkedin">
                    <i class="fab fa-linkedin"></i> <?php esc_html_e('LinkedIn', 'partner-ciu-manager'); ?>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode(get_permalink()); ?>" target="_blank" class="share-btn facebook">
                    <i class="fab fa-facebook"></i> <?php esc_html_e('Facebook', 'partner-ciu-manager'); ?>
                </a>
                <button type="button" class="share-btn print" onclick="window.print()">
                    <i class="fas fa-print"></i> <?php esc_html_e('Print', 'partner-ciu-manager'); ?>
                </button>
            </div>
        </div>
    </section>

</div>
