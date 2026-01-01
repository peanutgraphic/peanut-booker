<?php
/**
 * The core plugin class.
 *
 * @package Peanut_Booker
 * @since   1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks,
 * and public-facing site hooks.
 */
class Peanut_Booker {

    /**
     * The loader that's responsible for maintaining and registering all hooks.
     *
     * @var Peanut_Booker_Loader
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @var string
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @var string
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     */
    public function __construct() {
        $this->version     = PEANUT_BOOKER_VERSION;
        $this->plugin_name = 'peanut-booker';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->register_post_types();
        $this->register_user_roles();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        // The class responsible for orchestrating the actions and filters.
        require_once PEANUT_BOOKER_PATH . 'includes/class-loader.php';

        // The class responsible for defining internationalization functionality.
        require_once PEANUT_BOOKER_PATH . 'includes/class-i18n.php';

        // Database operations.
        require_once PEANUT_BOOKER_PATH . 'includes/class-database.php';

        // Rate limiting for API protection.
        require_once PEANUT_BOOKER_PATH . 'includes/class-rate-limiter.php';

        // Audit logging for security and compliance.
        require_once PEANUT_BOOKER_PATH . 'includes/class-audit-log.php';

        // Data encryption for sensitive fields.
        require_once PEANUT_BOOKER_PATH . 'includes/class-encryption.php';

        // User roles and capabilities.
        require_once PEANUT_BOOKER_PATH . 'includes/class-roles.php';

        // Custom post types.
        require_once PEANUT_BOOKER_PATH . 'includes/class-post-types.php';

        // Performer functionality.
        require_once PEANUT_BOOKER_PATH . 'includes/class-performer.php';

        // Customer functionality.
        require_once PEANUT_BOOKER_PATH . 'includes/class-customer.php';

        // Booking engine.
        require_once PEANUT_BOOKER_PATH . 'includes/class-booking.php';

        // Market and bidding.
        require_once PEANUT_BOOKER_PATH . 'includes/class-market.php';

        // Reviews and ratings.
        require_once PEANUT_BOOKER_PATH . 'includes/class-reviews.php';

        // Availability calendar.
        require_once PEANUT_BOOKER_PATH . 'includes/class-availability.php';

        // Subscriptions and tiers.
        require_once PEANUT_BOOKER_PATH . 'includes/class-subscriptions.php';

        // Email notifications.
        require_once PEANUT_BOOKER_PATH . 'includes/class-notifications.php';

        // WooCommerce integration.
        require_once PEANUT_BOOKER_PATH . 'includes/class-woocommerce.php';

        // REST API endpoints.
        require_once PEANUT_BOOKER_PATH . 'includes/class-rest-api.php';

        // Admin REST API endpoints for React SPA.
        require_once PEANUT_BOOKER_PATH . 'includes/class-rest-api-admin.php';

        // Peanut Suite integration.
        require_once PEANUT_BOOKER_PATH . 'includes/class-peanut-suite.php';

        // Shortcodes.
        require_once PEANUT_BOOKER_PATH . 'includes/class-shortcodes.php';

        // Demo data generator.
        require_once PEANUT_BOOKER_PATH . 'includes/class-demo-data.php';

        // Google OAuth authentication.
        require_once PEANUT_BOOKER_PATH . 'includes/class-google-auth.php';

        // Booking wizard.
        require_once PEANUT_BOOKER_PATH . 'includes/class-booking-wizard.php';

        // Messaging system.
        require_once PEANUT_BOOKER_PATH . 'includes/class-messages.php';

        // Admin class.
        require_once PEANUT_BOOKER_PATH . 'admin/class-admin.php';

        // Public class.
        require_once PEANUT_BOOKER_PATH . 'public/class-public.php';

        $this->loader = new Peanut_Booker_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     */
    private function set_locale() {
        $plugin_i18n = new Peanut_Booker_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks() {
        $plugin_admin = new Peanut_Booker_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     */
    private function define_public_hooks() {
        $plugin_public = new Peanut_Booker_Public( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
    }

    /**
     * Register custom post types and taxonomies.
     */
    private function register_post_types() {
        $post_types = new Peanut_Booker_Post_Types();
        $this->loader->add_action( 'init', $post_types, 'register_post_types' );
        $this->loader->add_action( 'init', $post_types, 'register_taxonomies' );
    }

    /**
     * Register custom user roles and capabilities.
     */
    private function register_user_roles() {
        $roles = new Peanut_Booker_Roles();
        $this->loader->add_action( 'init', $roles, 'maybe_update_capabilities' );
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() {
        $this->loader->run();

        // Initialize components.
        $this->init_components();

        // Check if rewrite flush is needed (after activation).
        if ( get_transient( 'peanut_booker_flush_rewrite' ) ) {
            delete_transient( 'peanut_booker_flush_rewrite' );
            add_action( 'init', 'flush_rewrite_rules', 20 );
        }
    }

    /**
     * Initialize plugin components.
     */
    private function init_components() {
        // Initialize booking engine.
        new Peanut_Booker_Booking();

        // Initialize market.
        new Peanut_Booker_Market();

        // Initialize reviews.
        new Peanut_Booker_Reviews();

        // Initialize availability.
        new Peanut_Booker_Availability();

        // Initialize subscriptions.
        new Peanut_Booker_Subscriptions();

        // Initialize notifications.
        new Peanut_Booker_Notifications();

        // Initialize WooCommerce integration.
        new Peanut_Booker_WooCommerce();

        // Initialize REST API.
        new Peanut_Booker_REST_API();

        // Initialize Admin REST API for React SPA.
        new Peanut_Booker_REST_API_Admin();

        // Initialize Peanut Suite integration.
        new Peanut_Booker_Peanut_Suite();

        // Initialize shortcodes.
        new Peanut_Booker_Shortcodes();

        // Initialize Google OAuth.
        new Peanut_Booker_Google_Auth();

        // Initialize booking wizard.
        new Peanut_Booker_Booking_Wizard();

        // Initialize messaging system.
        new Peanut_Booker_Messages();

        // Initialize performer functionality.
        new Peanut_Booker_Performer();

        // Initialize audit logging.
        new Peanut_Booker_Audit_Log();
    }

    /**
     * The name of the plugin used to uniquely identify it.
     *
     * @return string
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return Peanut_Booker_Loader
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return string
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Get plugin option with default.
     *
     * @param string $key     Option key.
     * @param mixed  $default Default value.
     * @return mixed
     */
    public static function get_option( $key, $default = '' ) {
        $options = get_option( 'peanut_booker_settings', array() );
        return isset( $options[ $key ] ) ? $options[ $key ] : $default;
    }

    /**
     * Update plugin option.
     *
     * @param string $key   Option key.
     * @param mixed  $value Option value.
     */
    public static function update_option( $key, $value ) {
        $options         = get_option( 'peanut_booker_settings', array() );
        $options[ $key ] = $value;
        update_option( 'peanut_booker_settings', $options );
    }
}
