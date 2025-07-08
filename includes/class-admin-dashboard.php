<?php
class MagicPage_Analytics_Dashboard {

    private static $instance = null;

    private $post_type = 'magicpage';
    private $meta_group_key = '_group_id';

    public function enqueue_admin_assets( $hook ) {
        // Only load CSS on your plugin dashboard page
        if ( $hook !== 'magicpage-analytics-dashboard' ) {
            return;
        }
        wp_enqueue_style(
            'magicpage-analytics-css',
            plugin_dir_url(__FILE__) . 'css/magicpage-analytics.css',
            array(),
            '1.0'
        );
    }


    // Singleton pattern for clean instantiation
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', [ $this, 'add_dashboard_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_analytics_script' ] );
        
        add_action( 'wp_ajax_nopriv_magicpage_record_visit', [ $this, 'record_magicpage_visit' ] );
        add_action( 'wp_ajax_magicpage_record_visit', [ $this, 'record_magicpage_visit' ] );


        // Ajax endpoint or REST API endpoint to fetch data if needed
        add_action( 'wp_ajax_magicpage_analytics_data', [ $this, 'ajax_fetch_analytics_data' ] );
        
    }

    // Add submenu page under Magic Pages menu
    public function add_dashboard_menu() {
        add_submenu_page(
            'edit.php?post_type=' . $this->post_type,
            'Magic Pages Analytics',
            'Analytics',
            'manage_options',
            'magicpage-analytics-dashboard',
            [ $this, 'render_dashboard_page' ]
        );
    }

    // Enqueue scripts/styles, load Chart.js or WP provided scripts
    public function enqueue_assets( $hook ) {
        if ( $hook !== 'magicpage_page_magicpage-analytics-dashboard' ) {
            return;
        }

        // Enqueue Chart.js (official CDN or bundle locally if you prefer)
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js',
            [],
            '4.4.0',  // Or whichever version you’re targeting
            true
        );

        // Your custom JS for rendering charts/widgets
        wp_enqueue_script(
            'magicpage-analytics-js',
            plugin_dir_url( __FILE__ ) . '../assets/js/magicpage-analytics.js',
            [ 'chartjs', 'jquery' ],
            '1.0.0',
            true
        );

        // Custom CSS for dashboard widgets layout
        wp_enqueue_style(
            'magicpage-analytics-css',
            plugin_dir_url( __FILE__ ) . '../assets/css/magicpage-analytics.css',
            [],
            '1.0.0'
        );

        // Localize script for ajax URL, nonce etc
        wp_localize_script( 'magicpage-analytics-js', 'magicpageAnalytics', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'magicpage_analytics_nonce' ),
        ]);
    }


    // Main dashboard rendering
    public function render_dashboard_page() {
        ?>
        <div class="wrap magicpage-analytics-dashboard">
            <h1>Magic Pages Analytics Dashboard</h1>
            <div class="dashboard-charts">
                <div class="dashboard-chart">
                    <h2>Page Visits Over Time</h2>
                    <canvas id="chart-page-visits-over-time"></canvas>
                </div>
                <div class="dashboard-chart">
                    <h2>Visits by Group</h2>
                    <canvas id="chart-visits-by-group"></canvas>
                </div>
                <div class="dashboard-chart">
                    <h2>Visits by Location</h2>
                    <canvas id="chart-visits-by-location"></canvas>
                </div>
                <div class="dashboard-chart">
                    <h2>Top 10 Most Visited Pages</h2>
                    <canvas id="chart-top-pages"></canvas>
                </div>
                <div class="dashboard-chart">
                    <h2>404 Errors by URL</h2>
                    <canvas id="chart-404s"></canvas>
                </div>
                <div class="dashboard-chart">
                    <h2>Page Visits by Device Type</h2>
                    <canvas id="chart-device-type"></canvas>
                </div>
            </div>

            <div class="dashboard-tables">
                <h2>Top 10 Magic Pages by Visits</h2>
                <table id="table-top-pages" class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Visits</th>
                            <th>Group</th>
                            <th>Last Visited</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $this->render_top_pages_rows(); ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // Example Counter: Total Magic Pages
    private function render_counter_total_magic_pages() {
        $count = wp_count_posts( $this->post_type )->publish;
        echo '<div class="dashboard-counter">Total Magic Pages: <strong>' . intval( $count ) . '</strong></div>';
    }

    // Example Counter: Total Groups
    private function render_counter_total_groups() {
        global $wpdb;
        $groups = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT meta_value) FROM {$wpdb->postmeta} WHERE meta_key = %s",
            $this->meta_group_key
        ));
        echo '<div class="dashboard-counter">Total Groups: <strong>' . intval( $groups ) . '</strong></div>';
    }

    // Placeholder for Total Visits Counter (requires data source)
    private function render_counter_total_visits() {
        // TODO: Replace with actual visit count retrieval
        $total_visits = 0;
        echo '<div class="dashboard-counter">Total Page Visits: <strong>' . intval( $total_visits ) . '</strong></div>';
    }

    // Placeholder for Average Visits per Page Counter (requires data source)
    private function render_counter_avg_visits_per_page() {
        // TODO: Calculate from actual data
        $avg_visits = 0;
        echo '<div class="dashboard-counter">Average Visits per Page: <strong>' . intval( $avg_visits ) . '</strong></div>';
    }

    // Placeholder for Total Redirects (requires data source)
    private function render_counter_total_redirects() {
        // TODO: Replace with actual redirect count retrieval
        $total_redirects = 0;
        echo '<div class="dashboard-counter">Total Redirects Detected: <strong>' . intval( $total_redirects ) . '</strong></div>';
    }

    // Render rows for top 10 pages by visits - Placeholder
    private function render_top_pages_rows() {
        // TODO: Pull actual data and loop here
        echo '<tr><td colspan="4">No data available</td></tr>';
    }

    // Ajax handler for fetching chart data (optional)
    public function ajax_fetch_analytics_data() {
        check_ajax_referer( 'magicpage_analytics_nonce', 'nonce' );

        global $wpdb;

        $post_type = $this->post_type;
        $meta_group_key = $this->meta_group_key;

        // 1️⃣ Visits over last 30 days
        $days = 30;
        $date_counts = array_fill_keys(
            array_map(fn($i) => date('Y-m-d', strtotime("-$i days")), range($days-1, 0)),
            0
        );

        $magicpages = get_posts([
            'post_type'      => $post_type,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids'
        ]);

        foreach ( $magicpages as $post_id ) {
            $visits = get_post_meta( $post_id, 'magicpage_visits', true );
            if ( !empty($visits) && is_array($visits) ) {
                foreach ( $visits as $visit_date ) {
                    $key = date('Y-m-d', strtotime($visit_date));
                    if ( isset($date_counts[$key]) ) {
                        $date_counts[$key]++;
                    }
                }
            }
        }

        // 2️⃣ Visits by Group
        $group_counts = [];
        foreach ( $magicpages as $post_id ) {
            $group_id = get_post_meta( $post_id, $meta_group_key, true ) ?: 'Ungrouped';
            $group_counts[$group_id] = ($group_counts[$group_id] ?? 0) + 1;
        }

        // 3️⃣ Visits by Location (assuming meta `_location_id`)
        $location_counts = [];
        foreach ( $magicpages as $post_id ) {
            $location = get_post_meta( $post_id, '_location_id', true ) ?: 'Unassigned';
            $location_counts[$location] = ($location_counts[$location] ?? 0) + 1;
        }

        // 4️⃣ Top 10 Most Visited Pages
        $top_pages = [];
        foreach ( $magicpages as $post_id ) {
            $visits = get_post_meta( $post_id, 'magicpage_visits', true );
            $count  = is_array($visits) ? count($visits) : 0;
            $top_pages[] = [
                'title' => get_the_title($post_id),
                'count' => $count
            ];
        }
        usort($top_pages, fn($a, $b) => $b['count'] - $a['count']);
        $top_pages = array_slice($top_pages, 0, 10);

        // 5️⃣ 404s by URL (placeholder example — real implementation would need logs)
        $errors_404 = [
            '/example-missing-page' => 12,
            '/another-broken-url'   => 8
        ];

        // 6️⃣ Device Type (assuming meta `_device_type` if you’re logging it)
        $device_counts = [
            'desktop' => 120,
            'mobile'  => 80,
            'tablet'  => 15
        ];

        wp_send_json_success([
            'visits_over_time' => $date_counts,
            'visits_by_group'  => $group_counts,
            'visits_by_location' => $location_counts,
            'top_pages'        => $top_pages,
            'errors_404'       => $errors_404,
            'device_types'     => $device_counts
        ]);
    }

    public function enqueue_frontend_analytics_script() {
        if ( is_singular('magicpage') ) {
            wp_enqueue_script(
                'magicpage-frontend-analytics',
                plugin_dir_url(__FILE__) . 'js/magicpage-frontend-analytics.js',
                [ 'jquery' ],
                '1.0',
                true
            );

            wp_localize_script( 'magicpage-frontend-analytics', 'MagicPageAnalyticsFrontend', [
                'ajax_url'   => admin_url('admin-ajax.php'),
                'post_id'    => get_the_ID(),
                'nonce'      => wp_create_nonce('magicpage_visit_nonce'),
            ]);
        }
    }

    public function record_magicpage_visit() {
        check_ajax_referer('magicpage_visit_nonce', 'nonce');

        $post_id = intval($_POST['post_id']);
        if ( get_post_type($post_id) !== 'magicpage' ) {
            wp_send_json_error('Invalid post.');
        }

        // Option A: Increment simple counter
        $count = (int) get_post_meta($post_id, 'magicpage_visit_count', true);
        update_post_meta($post_id, 'magicpage_visit_count', $count + 1);

        // Option B: Append timestamp for granular history
        $visits = get_post_meta($post_id, 'magicpage_visits', true);
        if ( !is_array($visits) ) {
            $visits = [];
        }
        $visits[] = current_time('mysql'); // or date('Y-m-d H:i:s')
        update_post_meta($post_id, 'magicpage_visits', $visits);

        wp_send_json_success();
    }

}
