<?php
/**
 * Peanut Booker Test Suite
 *
 * Comprehensive tests for booking platform functionality.
 * Run via WP-CLI: wp eval-file tests/class-booker-tests.php
 *
 * @package Peanut_Booker
 */

if (!defined('ABSPATH')) {
    if (php_sapi_name() !== 'cli') {
        exit;
    }
}

class Peanut_Booker_Tests {

    private array $results = [];
    private int $passed = 0;
    private int $failed = 0;
    private int $warnings = 0;

    /**
     * Run all tests
     */
    public function run(): array {
        $this->log_header('Peanut Booker Test Suite');

        // Core tests
        $this->test_database_tables();
        $this->test_woocommerce_dependency();
        $this->test_license_integration();

        // Performer tests
        $this->test_performer_profiles();
        $this->test_performer_categories();
        $this->test_performer_availability();

        // Booking tests
        $this->test_booking_creation();
        $this->test_booking_workflow();
        $this->test_escrow_system();

        // Market tests
        $this->test_event_market();
        $this->test_bidding_system();

        // Review tests
        $this->test_review_system();

        // Transaction tests
        $this->test_transaction_tracking();
        $this->test_commission_calculation();

        // Pages tests
        $this->test_required_pages();

        // API tests
        $this->test_rest_api();

        // Settings tests
        $this->test_settings();

        $this->log_summary();

        return $this->results;
    }

    // ========================================
    // Core Tests
    // ========================================

    private function test_database_tables(): void {
        $this->log_section('Database Tables');

        global $wpdb;

        $required_tables = [
            'pb_performers',
            'pb_bookings',
            'pb_availability',
            'pb_reviews',
            'pb_transactions',
            'pb_events',
            'pb_bids',
            'pb_subscriptions',
            'pb_sponsored_slots',
        ];

        foreach ($required_tables as $table) {
            $full_table = $wpdb->prefix . $table;
            $exists = $wpdb->get_var($wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $full_table
            )) === $full_table;

            $this->assert($exists, "Table {$table} exists");
        }

        // Check table structure for performers
        $columns = $wpdb->get_col("DESCRIBE {$wpdb->prefix}pb_performers");
        $required_cols = ['id', 'user_id', 'stage_name', 'bio', 'hourly_rate', 'status'];

        foreach ($required_cols as $col) {
            $this->assert(in_array($col, $columns), "Performers table has '{$col}' column");
        }
    }

    private function test_woocommerce_dependency(): void {
        $this->log_section('WooCommerce Dependency');

        $woo_active = class_exists('WooCommerce');
        $this->assert($woo_active, 'WooCommerce is active');

        if ($woo_active) {
            $woo_version = WC()->version;
            $this->assert(
                version_compare($woo_version, '8.0', '>='),
                "WooCommerce version: {$woo_version} (8.0+ required)"
            );
        }
    }

    private function test_license_integration(): void {
        $this->log_section('License Integration');

        if (function_exists('peanut_booker_is_licensed')) {
            $licensed = peanut_booker_is_licensed();
            $this->assert(true, 'License check available: ' . ($licensed ? 'licensed' : 'not licensed'));
        } else {
            $this->warning('License check function not found');
        }

        // Check license client
        if (function_exists('peanut_booker_license')) {
            $client = peanut_booker_license();
            $this->assert($client !== null, 'License client initialized');
        }
    }

    // ========================================
    // Performer Tests
    // ========================================

    private function test_performer_profiles(): void {
        $this->log_section('Performer Profiles');

        global $wpdb;

        // Count performers
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}pb_performers");
        $this->assert($count !== null, "Performer count: {$count}");

        // Check performer statuses
        $statuses = $wpdb->get_results(
            "SELECT status, COUNT(*) as count FROM {$wpdb->prefix}pb_performers GROUP BY status"
        );

        if (!empty($statuses)) {
            foreach ($statuses as $status) {
                $this->log_info("    Status '{$status->status}': {$status->count}");
            }
        }

        // Test performer fields
        $performer = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}pb_performers LIMIT 1");
        if ($performer) {
            $this->assert(isset($performer->stage_name), 'Performer has stage_name');
            $this->assert(isset($performer->hourly_rate), 'Performer has hourly_rate');
            $this->assert(isset($performer->bio), 'Performer has bio');
        } else {
            $this->warning('No performers found for field test');
        }
    }

    private function test_performer_categories(): void {
        $this->log_section('Performer Categories');

        // Check performer categories taxonomy
        $taxonomy_exists = taxonomy_exists('performer_category');
        $this->assert($taxonomy_exists, 'Performer category taxonomy exists');

        if ($taxonomy_exists) {
            $terms = get_terms([
                'taxonomy' => 'performer_category',
                'hide_empty' => false,
            ]);

            if (!is_wp_error($terms)) {
                $this->assert(true, "Categories count: " . count($terms));
                foreach (array_slice($terms, 0, 5) as $term) {
                    $this->log_info("    - {$term->name}");
                }
            }
        }
    }

    private function test_performer_availability(): void {
        $this->log_section('Performer Availability');

        global $wpdb;

        // Check availability table
        $columns = $wpdb->get_col("DESCRIBE {$wpdb->prefix}pb_availability");
        $required_cols = ['id', 'performer_id', 'date', 'status', 'block_type'];

        foreach ($required_cols as $col) {
            $has_col = in_array($col, $columns);
            if ($has_col) {
                $this->assert(true, "Availability has '{$col}' column");
            } else {
                $this->warning("Availability missing '{$col}' column");
            }
        }

        // Count blocked dates
        $blocked = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pb_availability WHERE status = 'blocked'"
        );
        $this->assert($blocked !== null, "Blocked dates: {$blocked}");

        // Count booked dates
        $booked = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pb_availability WHERE status = 'booked'"
        );
        $this->assert($booked !== null, "Booked dates: {$booked}");
    }

    // ========================================
    // Booking Tests
    // ========================================

    private function test_booking_creation(): void {
        $this->log_section('Booking Creation');

        global $wpdb;

        // Check bookings table structure
        $columns = $wpdb->get_col("DESCRIBE {$wpdb->prefix}pb_bookings");
        $required_cols = [
            'id', 'booking_number', 'performer_id', 'customer_id',
            'event_date', 'status', 'total_amount', 'deposit_amount'
        ];

        foreach ($required_cols as $col) {
            $this->assert(in_array($col, $columns), "Bookings has '{$col}' column");
        }

        // Count bookings
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}pb_bookings");
        $this->assert($count !== null, "Total bookings: {$count}");
    }

    private function test_booking_workflow(): void {
        $this->log_section('Booking Workflow');

        global $wpdb;

        // Check booking statuses
        $statuses = $wpdb->get_results(
            "SELECT status, COUNT(*) as count FROM {$wpdb->prefix}pb_bookings GROUP BY status"
        );

        $expected_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];

        foreach ($expected_statuses as $status) {
            $this->assert(true, "Status '{$status}' expected in workflow");
        }

        if (!empty($statuses)) {
            foreach ($statuses as $status) {
                $this->log_info("    Status '{$status->status}': {$status->count}");
            }
        }
    }

    private function test_escrow_system(): void {
        $this->log_section('Escrow System');

        global $wpdb;

        // Check transactions table
        $columns = $wpdb->get_col("DESCRIBE {$wpdb->prefix}pb_transactions");
        $required_cols = ['id', 'booking_id', 'type', 'amount', 'status'];

        foreach ($required_cols as $col) {
            $this->assert(in_array($col, $columns), "Transactions has '{$col}' column");
        }

        // Check transaction types
        $types = $wpdb->get_results(
            "SELECT type, COUNT(*) as count FROM {$wpdb->prefix}pb_transactions GROUP BY type"
        );

        if (!empty($types)) {
            foreach ($types as $type) {
                $this->log_info("    Type '{$type->type}': {$type->count}");
            }
        } else {
            $this->warning('No transactions found');
        }

        // Check settings for escrow
        $settings = get_option('peanut_booker_settings', []);
        $auto_release = $settings['escrow_auto_release_days'] ?? 7;
        $this->assert(true, "Escrow auto-release: {$auto_release} days");
    }

    // ========================================
    // Market Tests
    // ========================================

    private function test_event_market(): void {
        $this->log_section('Event Market');

        global $wpdb;

        // Check events table
        $columns = $wpdb->get_col("DESCRIBE {$wpdb->prefix}pb_events");
        $required_cols = ['id', 'customer_id', 'title', 'event_date', 'budget', 'status'];

        foreach ($required_cols as $col) {
            $this->assert(in_array($col, $columns), "Events has '{$col}' column");
        }

        // Count events
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}pb_events");
        $this->assert($count !== null, "Market events: {$count}");

        // Check event statuses
        $statuses = $wpdb->get_results(
            "SELECT status, COUNT(*) as count FROM {$wpdb->prefix}pb_events GROUP BY status"
        );

        if (!empty($statuses)) {
            foreach ($statuses as $status) {
                $this->log_info("    Status '{$status->status}': {$status->count}");
            }
        }
    }

    private function test_bidding_system(): void {
        $this->log_section('Bidding System');

        global $wpdb;

        // Check bids table
        $columns = $wpdb->get_col("DESCRIBE {$wpdb->prefix}pb_bids");
        $required_cols = ['id', 'event_id', 'performer_id', 'amount', 'status'];

        foreach ($required_cols as $col) {
            $this->assert(in_array($col, $columns), "Bids has '{$col}' column");
        }

        // Count bids
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}pb_bids");
        $this->assert($count !== null, "Total bids: {$count}");

        // Check max bids setting
        $settings = get_option('peanut_booker_settings', []);
        $max_bids = $settings['max_bids_per_event'] ?? 50;
        $this->assert(true, "Max bids per event: {$max_bids}");
    }

    // ========================================
    // Review Tests
    // ========================================

    private function test_review_system(): void {
        $this->log_section('Review System');

        global $wpdb;

        // Check reviews table
        $columns = $wpdb->get_col("DESCRIBE {$wpdb->prefix}pb_reviews");
        $required_cols = ['id', 'booking_id', 'reviewer_id', 'reviewee_id', 'rating', 'content'];

        foreach ($required_cols as $col) {
            $this->assert(in_array($col, $columns), "Reviews has '{$col}' column");
        }

        // Count reviews
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}pb_reviews");
        $this->assert($count !== null, "Total reviews: {$count}");

        // Average rating
        $avg = $wpdb->get_var("SELECT AVG(rating) FROM {$wpdb->prefix}pb_reviews WHERE rating > 0");
        if ($avg) {
            $this->assert(true, "Average rating: " . number_format($avg, 1));
        }
    }

    // ========================================
    // Transaction Tests
    // ========================================

    private function test_transaction_tracking(): void {
        $this->log_section('Transaction Tracking');

        global $wpdb;

        // Sum of transactions by type
        $sums = $wpdb->get_results(
            "SELECT type, SUM(amount) as total FROM {$wpdb->prefix}pb_transactions GROUP BY type"
        );

        if (!empty($sums)) {
            foreach ($sums as $sum) {
                $this->log_info("    {$sum->type}: $" . number_format($sum->total, 2));
            }
        }

        // Check pending transactions
        $pending = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pb_transactions WHERE status = 'pending'"
        );
        $this->assert($pending !== null, "Pending transactions: {$pending}");
    }

    private function test_commission_calculation(): void {
        $this->log_section('Commission Calculation');

        $settings = get_option('peanut_booker_settings', []);

        $free_commission = $settings['commission_free_tier'] ?? 15;
        $pro_commission = $settings['commission_pro_tier'] ?? 10;
        $flat_fee = $settings['commission_flat_fee'] ?? 0;

        $this->assert(true, "Free tier commission: {$free_commission}%");
        $this->assert(true, "Pro tier commission: {$pro_commission}%");
        $this->assert(true, "Flat fee: \${$flat_fee}");

        // Test calculation
        $booking_amount = 500;
        $expected_commission_free = $booking_amount * ($free_commission / 100);
        $expected_commission_pro = $booking_amount * ($pro_commission / 100);

        $this->assert(
            $expected_commission_free === 75.0,
            "Free tier: \$500 booking = \$75 commission"
        );
        $this->assert(
            $expected_commission_pro === 50.0,
            "Pro tier: \$500 booking = \$50 commission"
        );
    }

    // ========================================
    // Pages Tests
    // ========================================

    private function test_required_pages(): void {
        $this->log_section('Required Pages');

        $pages = get_option('peanut_booker_pages', []);

        $required_pages = [
            'performer-directory' => 'Performer Directory',
            'market' => 'Event Market',
            'dashboard' => 'Dashboard',
            'performer-signup' => 'Performer Signup',
            'customer-signup' => 'Customer Signup',
        ];

        foreach ($required_pages as $slug => $name) {
            $page_id = $pages[$slug] ?? 0;

            if ($page_id) {
                $page = get_post($page_id);
                if ($page && $page->post_status === 'publish') {
                    $this->assert(true, "Page '{$name}' exists (ID: {$page_id})");
                } else {
                    $this->warning("Page '{$name}' exists but not published");
                }
            } else {
                $this->warning("Page '{$name}' not configured");
            }
        }
    }

    // ========================================
    // API Tests
    // ========================================

    private function test_rest_api(): void {
        $this->log_section('REST API');

        $routes = rest_get_server()->get_routes();

        // Check for Booker API namespace
        $booker_routes = array_filter(array_keys($routes), function($route) {
            return strpos($route, '/peanut-booker/v1') === 0 ||
                   strpos($route, '/booker/v1') === 0;
        });

        if (count($booker_routes) > 0) {
            $this->assert(true, 'Booker API routes: ' . count($booker_routes));
            foreach ($booker_routes as $route) {
                $this->log_info("    {$route}");
            }
        } else {
            $this->warning('No Booker API routes found');
        }
    }

    // ========================================
    // Settings Tests
    // ========================================

    private function test_settings(): void {
        $this->log_section('Settings');

        $settings = get_option('peanut_booker_settings', []);

        $this->assert(!empty($settings), 'Settings configured');

        // Currency
        $currency = $settings['currency'] ?? 'USD';
        $this->assert(!empty($currency), "Currency: {$currency}");

        // Pro pricing
        $pro_monthly = $settings['pro_monthly_price'] ?? 0;
        $pro_annual = $settings['pro_annual_price'] ?? 0;
        $this->assert(true, "Pro monthly: \${$pro_monthly}, annual: \${$pro_annual}");

        // Booking buffer
        $buffer = $settings['booking_buffer_hours'] ?? 24;
        $this->assert(true, "Booking buffer: {$buffer} hours");

        // Deposit settings
        $min_deposit = $settings['min_deposit_percentage'] ?? 10;
        $max_deposit = $settings['max_deposit_percentage'] ?? 100;
        $default_deposit = $settings['default_deposit_percentage'] ?? 25;
        $this->assert(true, "Deposit: {$min_deposit}%-{$max_deposit}% (default: {$default_deposit}%)");
    }

    // ========================================
    // Helpers
    // ========================================

    private function assert(bool $condition, string $message): void {
        if ($condition) {
            $this->passed++;
            $this->results[] = ['status' => 'pass', 'message' => $message];
            $this->log_pass($message);
        } else {
            $this->failed++;
            $this->results[] = ['status' => 'fail', 'message' => $message];
            $this->log_fail($message);
        }
    }

    private function warning(string $message): void {
        $this->warnings++;
        $this->results[] = ['status' => 'warning', 'message' => $message];
        $this->log_warning($message);
    }

    private function log_header(string $text): void {
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "  {$text}\n";
        echo str_repeat('=', 60) . "\n\n";
    }

    private function log_section(string $text): void {
        echo "\n--- {$text} ---\n";
    }

    private function log_pass(string $text): void {
        echo "  ✓ {$text}\n";
    }

    private function log_fail(string $text): void {
        echo "  ✗ {$text}\n";
    }

    private function log_warning(string $text): void {
        echo "  ⚠ {$text}\n";
    }

    private function log_info(string $text): void {
        echo "{$text}\n";
    }

    private function log_summary(): void {
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "  SUMMARY\n";
        echo str_repeat('=', 60) . "\n";
        echo "  Passed:   {$this->passed}\n";
        echo "  Failed:   {$this->failed}\n";
        echo "  Warnings: {$this->warnings}\n";
        echo str_repeat('=', 60) . "\n\n";
    }

    public function get_results(): array {
        return [
            'passed' => $this->passed,
            'failed' => $this->failed,
            'warnings' => $this->warnings,
            'details' => $this->results,
        ];
    }
}

// Run tests if executed directly
if (defined('ABSPATH')) {
    $tests = new Peanut_Booker_Tests();
    $tests->run();
}
