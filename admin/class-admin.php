<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package Peanut_Booker
 * @since   1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * The admin-specific functionality of the plugin.
 */
class Peanut_Booker_Admin {

    /**
     * The ID of this plugin.
     *
     * @var string
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @var string
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version     The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;

        // AJAX handlers.
        add_action( 'wp_ajax_pb_create_pages', array( $this, 'ajax_create_pages' ) );
        add_action( 'wp_ajax_pb_admin_save_performer', array( $this, 'ajax_save_performer' ) );

        // Redirect performer edit links to custom editor.
        add_filter( 'post_row_actions', array( $this, 'modify_performer_row_actions' ), 10, 2 );
        add_filter( 'get_edit_post_link', array( $this, 'modify_performer_edit_link' ), 10, 3 );
    }

    /**
     * Check if we're on a Peanut Booker admin page.
     *
     * @return bool
     */
    private function is_booker_admin_page() {
        $screen = get_current_screen();
        if ( ! $screen ) {
            return false;
        }

        $booker_pages = array(
            'toplevel_page_peanut-booker',
            'peanut-booker_page_pb-performers',
            'peanut-booker_page_pb-bookings',
            'peanut-booker_page_pb-market',
            'peanut-booker_page_pb-reviews',
            'peanut-booker_page_pb-payouts',
            'peanut-booker_page_pb-microsites',
            'peanut-booker_page_pb-messages',
            'peanut-booker_page_pb-customers',
            'peanut-booker_page_pb-analytics',
            'peanut-booker_page_pb-settings',
            'peanut-booker_page_pb-demo',
            'admin_page_pb-edit-performer',
        );

        return in_array( $screen->id, $booker_pages, true );
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        // Always enqueue base admin styles.
        wp_enqueue_style(
            $this->plugin_name,
            PEANUT_BOOKER_URL . 'admin/css/admin.css',
            array(),
            $this->version,
            'all'
        );

        // Enqueue React app styles on Booker pages.
        if ( $this->is_booker_admin_page() ) {
            $this->enqueue_react_assets();
        }
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            PEANUT_BOOKER_URL . 'admin/js/admin.js',
            array( 'jquery' ),
            $this->version,
            true
        );

        wp_localize_script(
            $this->plugin_name,
            'pbAdmin',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'pb_admin_nonce' ),
                'strings' => array(
                    'confirm' => __( 'Are you sure?', 'peanut-booker' ),
                    'saving'  => __( 'Saving...', 'peanut-booker' ),
                    'saved'   => __( 'Saved!', 'peanut-booker' ),
                ),
            )
        );
    }

    /**
     * Enqueue React app assets.
     */
    private function enqueue_react_assets() {
        $dist_path = PEANUT_BOOKER_PATH . 'assets/dist/';
        $dist_url  = PEANUT_BOOKER_URL . 'assets/dist/';

        // Check for Vite manifest (production build).
        $manifest_path = $dist_path . '.vite/manifest.json';

        if ( file_exists( $manifest_path ) ) {
            // Production: Load from built assets.
            $manifest = json_decode( file_get_contents( $manifest_path ), true );

            if ( isset( $manifest['src/main.tsx'] ) ) {
                $entry = $manifest['src/main.tsx'];

                // Enqueue CSS.
                if ( isset( $entry['css'] ) ) {
                    foreach ( $entry['css'] as $index => $css_file ) {
                        wp_enqueue_style(
                            'peanut-booker-react-' . $index,
                            $dist_url . $css_file,
                            array(),
                            $this->version
                        );
                    }
                }

                // Enqueue JS as module.
                wp_enqueue_script(
                    'peanut-booker-react',
                    $dist_url . $entry['file'],
                    array(),
                    $this->version,
                    true
                );
                add_filter( 'script_loader_tag', array( $this, 'add_module_type' ), 10, 2 );
            }

            // Pass config to React app.
            wp_localize_script(
                'peanut-booker-react',
                'peanutBooker',
                $this->get_react_config()
            );
        } else {
            // Development: Load from Vite dev server.
            // Add module type filter for Vite scripts.
            add_filter( 'script_loader_tag', array( $this, 'add_module_type' ), 10, 2 );

            wp_enqueue_script(
                'peanut-booker-vite-client',
                'http://localhost:3001/@vite/client',
                array(),
                null,
                true
            );

            wp_enqueue_script(
                'peanut-booker-react',
                'http://localhost:3001/src/main.tsx',
                array( 'peanut-booker-vite-client' ),
                null,
                true
            );

            // Pass config before Vite scripts.
            wp_add_inline_script(
                'peanut-booker-vite-client',
                'window.peanutBooker = ' . wp_json_encode( $this->get_react_config() ) . ';',
                'before'
            );
        }
    }

    /**
     * Get React app configuration.
     *
     * @return array
     */
    private function get_react_config() {
        return array(
            'apiUrl'  => rest_url( 'peanut-booker/v1' ),
            'nonce'   => wp_create_nonce( 'wp_rest' ),
            'version' => $this->version,
            'tier'    => $this->get_current_tier(),
        );
    }

    /**
     * Add type="module" to React script tags.
     *
     * @param string $tag    The script tag.
     * @param string $handle The script handle.
     * @return string
     */
    public function add_module_type( $tag, $handle ) {
        if ( in_array( $handle, array( 'peanut-booker-react', 'peanut-booker-vite-client' ), true ) ) {
            $tag = str_replace( ' src=', ' type="module" src=', $tag );
        }
        return $tag;
    }

    /**
     * Get current subscription tier.
     *
     * @return string
     */
    private function get_current_tier() {
        $license_status = get_option( 'peanut_booker_license_status', '' );
        return 'valid' === $license_status ? 'pro' : 'free';
    }

    /**
     * Render the React SPA container.
     */
    private function render_react_app() {
        echo '<div id="peanut-booker-app" class="peanut-booker-wrap"></div>';
    }

    /**
     * Add admin menu pages.
     */
    public function add_admin_menu() {
        // Main menu.
        add_menu_page(
            __( 'Peanut Booker', 'peanut-booker' ),
            __( 'Peanut Booker', 'peanut-booker' ),
            'manage_options',
            'peanut-booker',
            array( $this, 'render_dashboard_page' ),
            'dashicons-tickets-alt',
            30
        );

        // Dashboard.
        add_submenu_page(
            'peanut-booker',
            __( 'Dashboard', 'peanut-booker' ),
            __( 'Dashboard', 'peanut-booker' ),
            'manage_options',
            'peanut-booker',
            array( $this, 'render_dashboard_page' )
        );

        // Performers.
        add_submenu_page(
            'peanut-booker',
            __( 'Performers', 'peanut-booker' ),
            __( 'Performers', 'peanut-booker' ),
            'pb_manage_performers',
            'pb-performers',
            array( $this, 'render_performers_page' )
        );

        // Bookings.
        add_submenu_page(
            'peanut-booker',
            __( 'Bookings', 'peanut-booker' ),
            __( 'Bookings', 'peanut-booker' ),
            'pb_manage_bookings',
            'pb-bookings',
            array( $this, 'render_bookings_page' )
        );

        // Market Events.
        add_submenu_page(
            'peanut-booker',
            __( 'Market Events', 'peanut-booker' ),
            __( 'Market Events', 'peanut-booker' ),
            'pb_manage_market',
            'pb-market',
            array( $this, 'render_market_page' )
        );

        // Reviews.
        add_submenu_page(
            'peanut-booker',
            __( 'Reviews', 'peanut-booker' ),
            __( 'Reviews', 'peanut-booker' ),
            'pb_manage_reviews',
            'pb-reviews',
            array( $this, 'render_reviews_page' )
        );

        // Payouts.
        add_submenu_page(
            'peanut-booker',
            __( 'Payouts', 'peanut-booker' ),
            __( 'Payouts', 'peanut-booker' ),
            'pb_manage_payouts',
            'pb-payouts',
            array( $this, 'render_payouts_page' )
        );

        // Messages.
        add_submenu_page(
            'peanut-booker',
            __( 'Messages', 'peanut-booker' ),
            __( 'Messages', 'peanut-booker' ),
            'manage_options',
            'pb-messages',
            array( $this, 'render_messages_page' )
        );

        // Customers.
        add_submenu_page(
            'peanut-booker',
            __( 'Customers', 'peanut-booker' ),
            __( 'Customers', 'peanut-booker' ),
            'manage_options',
            'pb-customers',
            array( $this, 'render_customers_page' )
        );

        // Analytics.
        add_submenu_page(
            'peanut-booker',
            __( 'Analytics', 'peanut-booker' ),
            __( 'Analytics', 'peanut-booker' ),
            'manage_options',
            'pb-analytics',
            array( $this, 'render_analytics_page' )
        );

        // Settings.
        add_submenu_page(
            'peanut-booker',
            __( 'Settings', 'peanut-booker' ),
            __( 'Settings', 'peanut-booker' ),
            'pb_manage_settings',
            'pb-settings',
            array( $this, 'render_settings_page' )
        );

        // Demo Mode.
        $demo_label = Peanut_Booker_Demo_Data::is_demo_mode()
            ? __( 'Demo Mode', 'peanut-booker' ) . ' <span class="pb-demo-badge">ON</span>'
            : __( 'Demo Mode', 'peanut-booker' );

        add_submenu_page(
            'peanut-booker',
            __( 'Demo / Test Mode', 'peanut-booker' ),
            $demo_label,
            'manage_options',
            'pb-demo',
            array( $this, 'render_demo_page' )
        );

        // Hidden performer editor page (no menu item).
        add_submenu_page(
            null, // No parent - hidden from menu.
            __( 'Edit Performer', 'peanut-booker' ),
            __( 'Edit Performer', 'peanut-booker' ),
            'pb_manage_performers',
            'pb-edit-performer',
            array( $this, 'render_performer_editor' )
        );
    }

    /**
     * Register plugin settings.
     */
    public function register_settings() {
        register_setting( 'peanut_booker_settings', 'peanut_booker_settings', array( $this, 'sanitize_settings' ) );
        register_setting( 'peanut_booker_settings', 'peanut_booker_license_server', 'esc_url_raw' );

        // General section.
        add_settings_section(
            'pb_general',
            __( 'General Settings', 'peanut-booker' ),
            null,
            'pb-settings-general'
        );

        add_settings_field(
            'currency',
            __( 'Currency', 'peanut-booker' ),
            array( $this, 'render_text_field' ),
            'pb-settings-general',
            'pb_general',
            array( 'field' => 'currency', 'default' => 'USD' )
        );

        // Commission section.
        add_settings_section(
            'pb_commission',
            __( 'Commission Settings', 'peanut-booker' ),
            null,
            'pb-settings-commission'
        );

        add_settings_field(
            'commission_free_tier',
            __( 'Free Tier Commission (%)', 'peanut-booker' ),
            array( $this, 'render_number_field' ),
            'pb-settings-commission',
            'pb_commission',
            array( 'field' => 'commission_free_tier', 'default' => 15, 'min' => 0, 'max' => 50 )
        );

        add_settings_field(
            'commission_pro_tier',
            __( 'Pro Tier Commission (%)', 'peanut-booker' ),
            array( $this, 'render_number_field' ),
            'pb-settings-commission',
            'pb_commission',
            array( 'field' => 'commission_pro_tier', 'default' => 10, 'min' => 0, 'max' => 50 )
        );

        add_settings_field(
            'commission_flat_fee',
            __( 'Flat Fee Per Transaction', 'peanut-booker' ),
            array( $this, 'render_number_field' ),
            'pb-settings-commission',
            'pb_commission',
            array( 'field' => 'commission_flat_fee', 'default' => 0, 'min' => 0, 'step' => '0.01' )
        );

        // Subscription section.
        add_settings_section(
            'pb_subscription',
            __( 'Pro Subscription Pricing', 'peanut-booker' ),
            null,
            'pb-settings-subscription'
        );

        add_settings_field(
            'pro_monthly_price',
            __( 'Monthly Price', 'peanut-booker' ),
            array( $this, 'render_number_field' ),
            'pb-settings-subscription',
            'pb_subscription',
            array( 'field' => 'pro_monthly_price', 'default' => 19.99, 'min' => 0, 'step' => '0.01' )
        );

        add_settings_field(
            'pro_annual_price',
            __( 'Annual Price', 'peanut-booker' ),
            array( $this, 'render_number_field' ),
            'pb-settings-subscription',
            'pb_subscription',
            array( 'field' => 'pro_annual_price', 'default' => 199.99, 'min' => 0, 'step' => '0.01' )
        );

        // Booking section.
        add_settings_section(
            'pb_booking',
            __( 'Booking Settings', 'peanut-booker' ),
            null,
            'pb-settings-booking'
        );

        add_settings_field(
            'min_deposit_percentage',
            __( 'Minimum Deposit (%)', 'peanut-booker' ),
            array( $this, 'render_number_field' ),
            'pb-settings-booking',
            'pb_booking',
            array( 'field' => 'min_deposit_percentage', 'default' => 10, 'min' => 0, 'max' => 100 )
        );

        add_settings_field(
            'max_deposit_percentage',
            __( 'Maximum Deposit (%)', 'peanut-booker' ),
            array( $this, 'render_number_field' ),
            'pb-settings-booking',
            'pb_booking',
            array( 'field' => 'max_deposit_percentage', 'default' => 100, 'min' => 0, 'max' => 100 )
        );

        add_settings_field(
            'escrow_auto_release_days',
            __( 'Auto Release Escrow After (days)', 'peanut-booker' ),
            array( $this, 'render_number_field' ),
            'pb-settings-booking',
            'pb_booking',
            array( 'field' => 'escrow_auto_release_days', 'default' => 7, 'min' => 1, 'max' => 30 )
        );

        // Achievement section.
        add_settings_section(
            'pb_achievements',
            __( 'Achievement Thresholds', 'peanut-booker' ),
            null,
            'pb-settings-achievements'
        );

        add_settings_field(
            'achievement_silver',
            __( 'Silver Threshold (points)', 'peanut-booker' ),
            array( $this, 'render_number_field' ),
            'pb-settings-achievements',
            'pb_achievements',
            array( 'field' => 'achievement_silver', 'default' => 100, 'min' => 1 )
        );

        add_settings_field(
            'achievement_gold',
            __( 'Gold Threshold (points)', 'peanut-booker' ),
            array( $this, 'render_number_field' ),
            'pb-settings-achievements',
            'pb_achievements',
            array( 'field' => 'achievement_gold', 'default' => 500, 'min' => 1 )
        );

        add_settings_field(
            'achievement_platinum',
            __( 'Platinum Threshold (points)', 'peanut-booker' ),
            array( $this, 'render_number_field' ),
            'pb-settings-achievements',
            'pb_achievements',
            array( 'field' => 'achievement_platinum', 'default' => 2000, 'min' => 1 )
        );
    }

    /**
     * Sanitize settings.
     *
     * @param array $input Settings input.
     * @return array Sanitized settings.
     */
    public function sanitize_settings( $input ) {
        $sanitized = array();

        $number_fields = array(
            'commission_free_tier',
            'commission_pro_tier',
            'commission_flat_fee',
            'pro_monthly_price',
            'pro_annual_price',
            'min_deposit_percentage',
            'max_deposit_percentage',
            'escrow_auto_release_days',
            'market_auto_deadline_days',
            'achievement_silver',
            'achievement_gold',
            'achievement_platinum',
        );

        $page_fields = array(
            'performer_directory_page',
            'market_page',
            'dashboard_page',
            'login_page',
            'performer_signup_page',
            'customer_signup_page',
        );

        foreach ( $input as $key => $value ) {
            if ( in_array( $key, $number_fields, true ) ) {
                $sanitized[ $key ] = floatval( $value );
            } elseif ( in_array( $key, $page_fields, true ) ) {
                $sanitized[ $key ] = absint( $value );
            } else {
                $sanitized[ $key ] = sanitize_text_field( $value );
            }
        }

        return $sanitized;
    }

    /**
     * Render text field.
     *
     * @param array $args Field arguments.
     */
    public function render_text_field( $args ) {
        $options = get_option( 'peanut_booker_settings', array() );
        $value   = isset( $options[ $args['field'] ] ) ? $options[ $args['field'] ] : ( $args['default'] ?? '' );
        printf(
            '<input type="text" name="peanut_booker_settings[%s]" value="%s" class="regular-text">',
            esc_attr( $args['field'] ),
            esc_attr( $value )
        );
    }

    /**
     * Render number field.
     *
     * @param array $args Field arguments.
     */
    public function render_number_field( $args ) {
        $options = get_option( 'peanut_booker_settings', array() );
        $value   = isset( $options[ $args['field'] ] ) ? $options[ $args['field'] ] : ( $args['default'] ?? 0 );
        printf(
            '<input type="number" name="peanut_booker_settings[%s]" value="%s" min="%s" max="%s" step="%s" class="small-text">',
            esc_attr( $args['field'] ),
            esc_attr( $value ),
            esc_attr( $args['min'] ?? 0 ),
            esc_attr( $args['max'] ?? '' ),
            esc_attr( $args['step'] ?? 1 )
        );
    }

    /**
     * Render dashboard page.
     */
    public function render_dashboard_page() {
        $this->render_react_app();
    }

    /**
     * Render performers page.
     */
    public function render_performers_page() {
        $this->render_react_app();
    }

    /**
     * Render bookings page.
     */
    public function render_bookings_page() {
        $this->render_react_app();
    }

    /**
     * Render market page.
     */
    public function render_market_page() {
        $this->render_react_app();
    }

    /**
     * Render reviews page.
     */
    public function render_reviews_page() {
        $this->render_react_app();
    }

    /**
     * Render payouts page.
     */
    public function render_payouts_page() {
        $this->render_react_app();
    }

    /**
     * Render settings page.
     */
    public function render_settings_page() {
        $this->render_react_app();
    }

    /**
     * Render demo mode page.
     */
    public function render_demo_page() {
        $this->render_react_app();
    }

    /**
     * Render microsites page.
     */
    public function render_microsites_page() {
        $this->render_react_app();
    }

    /**
     * Render messages page.
     */
    public function render_messages_page() {
        $this->render_react_app();
    }

    /**
     * Render customers page.
     */
    public function render_customers_page() {
        $this->render_react_app();
    }

    /**
     * Render analytics page.
     */
    public function render_analytics_page() {
        $this->render_react_app();
    }

    /**
     * AJAX handler to create plugin pages.
     */
    public function ajax_create_pages() {
        check_ajax_referer( 'pb_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'peanut-booker' ) ) );
        }

        $pages_to_create = array(
            'performer_directory_page' => array(
                'title'     => __( 'Performers', 'peanut-booker' ),
                'slug'      => 'performer-directory',
                'content'   => '[pb_performer_directory]',
            ),
            'market_page'              => array(
                'title'     => __( 'Market', 'peanut-booker' ),
                'slug'      => 'market',
                'content'   => '[pb_market]',
            ),
            'dashboard_page'           => array(
                'title'     => __( 'My Dashboard', 'peanut-booker' ),
                'slug'      => 'dashboard',
                'content'   => '[pb_my_dashboard]',
            ),
            'login_page'               => array(
                'title'     => __( 'Login', 'peanut-booker' ),
                'slug'      => 'login',
                'content'   => '[pb_login]',
            ),
            'performer_signup_page'    => array(
                'title'     => __( 'Performer Sign Up', 'peanut-booker' ),
                'slug'      => 'performer-signup',
                'content'   => '[pb_performer_signup]',
            ),
            'customer_signup_page'     => array(
                'title'     => __( 'Customer Sign Up', 'peanut-booker' ),
                'slug'      => 'customer-signup',
                'content'   => '[pb_customer_signup]',
            ),
        );

        $options = get_option( 'peanut_booker_settings', array() );
        $created = array();

        foreach ( $pages_to_create as $option_key => $page_data ) {
            // Skip if page already exists.
            if ( ! empty( $options[ $option_key ] ) && get_post( $options[ $option_key ] ) ) {
                continue;
            }

            // Create the page.
            $page_id = wp_insert_post( array(
                'post_title'   => $page_data['title'],
                'post_name'    => $page_data['slug'] ?? sanitize_title( $page_data['title'] ),
                'post_content' => $page_data['content'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ) );

            if ( $page_id && ! is_wp_error( $page_id ) ) {
                $options[ $option_key ] = $page_id;
                $created[] = $page_data['title'];
            }
        }

        // Save options.
        update_option( 'peanut_booker_settings', $options );

        if ( ! empty( $created ) ) {
            wp_send_json_success( array(
                'message' => sprintf(
                    /* translators: list of created pages */
                    __( 'Created pages: %s', 'peanut-booker' ),
                    implode( ', ', $created )
                ),
                'reload'  => true,
            ) );
        } else {
            wp_send_json_success( array(
                'message' => __( 'All pages already exist.', 'peanut-booker' ),
                'reload'  => false,
            ) );
        }
    }

    /**
     * Add performer meta boxes.
     */
    public function add_performer_meta_boxes() {
        add_meta_box(
            'pb_performer_details',
            __( 'Performer Details', 'peanut-booker' ),
            array( $this, 'render_performer_details_meta_box' ),
            'pb_performer',
            'normal',
            'high'
        );

        add_meta_box(
            'pb_performer_pricing',
            __( 'Pricing & Booking', 'peanut-booker' ),
            array( $this, 'render_performer_pricing_meta_box' ),
            'pb_performer',
            'side',
            'default'
        );

        add_meta_box(
            'pb_performer_location',
            __( 'Location & Travel', 'peanut-booker' ),
            array( $this, 'render_performer_location_meta_box' ),
            'pb_performer',
            'side',
            'default'
        );
    }

    /**
     * Render performer details meta box.
     *
     * @param WP_Post $post The post object.
     */
    public function render_performer_details_meta_box( $post ) {
        wp_nonce_field( 'pb_performer_meta', 'pb_performer_nonce' );

        $user_id          = get_post_meta( $post->ID, 'pb_user_id', true );
        $stage_name       = get_post_meta( $post->ID, 'pb_stage_name', true );
        $tagline          = get_post_meta( $post->ID, 'pb_tagline', true );
        $experience_years = get_post_meta( $post->ID, 'pb_experience_years', true );
        $website          = get_post_meta( $post->ID, 'pb_website', true );
        $phone            = get_post_meta( $post->ID, 'pb_phone', true );
        $email_public     = get_post_meta( $post->ID, 'pb_email_public', true );
        ?>
        <table class="form-table">
            <tr>
                <th><label for="pb_user_id"><?php esc_html_e( 'Linked User', 'peanut-booker' ); ?></label></th>
                <td>
                    <?php
                    wp_dropdown_users( array(
                        'name'             => 'pb_user_id',
                        'id'               => 'pb_user_id',
                        'selected'         => $user_id,
                        'show_option_none' => __( '— Select User —', 'peanut-booker' ),
                        'role__in'         => array( 'pb_performer', 'administrator' ),
                    ) );
                    ?>
                    <p class="description"><?php esc_html_e( 'The WordPress user account linked to this performer.', 'peanut-booker' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="pb_stage_name"><?php esc_html_e( 'Stage Name', 'peanut-booker' ); ?></label></th>
                <td>
                    <input type="text" id="pb_stage_name" name="pb_stage_name" value="<?php echo esc_attr( $stage_name ); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label for="pb_tagline"><?php esc_html_e( 'Tagline', 'peanut-booker' ); ?></label></th>
                <td>
                    <input type="text" id="pb_tagline" name="pb_tagline" value="<?php echo esc_attr( $tagline ); ?>" class="large-text">
                    <p class="description"><?php esc_html_e( 'A short description shown on cards.', 'peanut-booker' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="pb_experience_years"><?php esc_html_e( 'Years Experience', 'peanut-booker' ); ?></label></th>
                <td>
                    <input type="number" id="pb_experience_years" name="pb_experience_years" value="<?php echo esc_attr( $experience_years ); ?>" min="0" class="small-text">
                </td>
            </tr>
            <tr>
                <th><label for="pb_website"><?php esc_html_e( 'Website', 'peanut-booker' ); ?></label></th>
                <td>
                    <input type="url" id="pb_website" name="pb_website" value="<?php echo esc_attr( $website ); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label for="pb_phone"><?php esc_html_e( 'Phone', 'peanut-booker' ); ?></label></th>
                <td>
                    <input type="tel" id="pb_phone" name="pb_phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label for="pb_email_public"><?php esc_html_e( 'Public Email', 'peanut-booker' ); ?></label></th>
                <td>
                    <input type="email" id="pb_email_public" name="pb_email_public" value="<?php echo esc_attr( $email_public ); ?>" class="regular-text">
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render performer pricing meta box.
     *
     * @param WP_Post $post The post object.
     */
    public function render_performer_pricing_meta_box( $post ) {
        $hourly_rate        = get_post_meta( $post->ID, 'pb_hourly_rate', true );
        $minimum_booking    = get_post_meta( $post->ID, 'pb_minimum_booking', true );
        $deposit_percentage = get_post_meta( $post->ID, 'pb_deposit_percentage', true );
        $sale_price         = get_post_meta( $post->ID, 'pb_sale_price', true );
        $sale_active        = get_post_meta( $post->ID, 'pb_sale_active', true );
        ?>
        <p>
            <label for="pb_hourly_rate"><strong><?php esc_html_e( 'Hourly Rate ($)', 'peanut-booker' ); ?></strong></label><br>
            <input type="number" id="pb_hourly_rate" name="pb_hourly_rate" value="<?php echo esc_attr( $hourly_rate ); ?>" min="0" step="0.01" style="width: 100%;">
        </p>
        <p>
            <label for="pb_minimum_booking"><strong><?php esc_html_e( 'Minimum Hours', 'peanut-booker' ); ?></strong></label><br>
            <input type="number" id="pb_minimum_booking" name="pb_minimum_booking" value="<?php echo esc_attr( $minimum_booking ?: 1 ); ?>" min="1" style="width: 100%;">
        </p>
        <p>
            <label for="pb_deposit_percentage"><strong><?php esc_html_e( 'Deposit %', 'peanut-booker' ); ?></strong></label><br>
            <input type="number" id="pb_deposit_percentage" name="pb_deposit_percentage" value="<?php echo esc_attr( $deposit_percentage ?: 25 ); ?>" min="0" max="100" style="width: 100%;">
        </p>
        <hr>
        <p>
            <label>
                <input type="checkbox" name="pb_sale_active" value="1" <?php checked( $sale_active, '1' ); ?>>
                <strong><?php esc_html_e( 'Sale Active', 'peanut-booker' ); ?></strong>
            </label>
        </p>
        <p>
            <label for="pb_sale_price"><strong><?php esc_html_e( 'Sale Price ($)', 'peanut-booker' ); ?></strong></label><br>
            <input type="number" id="pb_sale_price" name="pb_sale_price" value="<?php echo esc_attr( $sale_price ); ?>" min="0" step="0.01" style="width: 100%;">
        </p>
        <?php
    }

    /**
     * Render performer location meta box.
     *
     * @param WP_Post $post The post object.
     */
    public function render_performer_location_meta_box( $post ) {
        $location_city   = get_post_meta( $post->ID, 'pb_location_city', true );
        $location_state  = get_post_meta( $post->ID, 'pb_location_state', true );
        $travel_willing  = get_post_meta( $post->ID, 'pb_travel_willing', true );
        $travel_radius   = get_post_meta( $post->ID, 'pb_travel_radius', true );
        ?>
        <p>
            <label for="pb_location_city"><strong><?php esc_html_e( 'City', 'peanut-booker' ); ?></strong></label><br>
            <input type="text" id="pb_location_city" name="pb_location_city" value="<?php echo esc_attr( $location_city ); ?>" style="width: 100%;">
        </p>
        <p>
            <label for="pb_location_state"><strong><?php esc_html_e( 'State', 'peanut-booker' ); ?></strong></label><br>
            <input type="text" id="pb_location_state" name="pb_location_state" value="<?php echo esc_attr( $location_state ); ?>" style="width: 100%;">
        </p>
        <hr>
        <p>
            <label>
                <input type="checkbox" name="pb_travel_willing" value="1" <?php checked( $travel_willing, '1' ); ?>>
                <strong><?php esc_html_e( 'Willing to Travel', 'peanut-booker' ); ?></strong>
            </label>
        </p>
        <p>
            <label for="pb_travel_radius"><strong><?php esc_html_e( 'Travel Radius (miles)', 'peanut-booker' ); ?></strong></label><br>
            <input type="number" id="pb_travel_radius" name="pb_travel_radius" value="<?php echo esc_attr( $travel_radius ?: 50 ); ?>" min="0" style="width: 100%;">
        </p>
        <?php
    }

    /**
     * Save performer meta data.
     *
     * @param int     $post_id The post ID.
     * @param WP_Post $post    The post object.
     */
    public function save_performer_meta( $post_id, $post ) {
        // Check nonce.
        if ( ! isset( $_POST['pb_performer_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pb_performer_nonce'] ) ), 'pb_performer_meta' ) ) {
            return;
        }

        // Check permissions.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Don't save on autosave.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Text fields.
        $text_fields = array( 'pb_stage_name', 'pb_tagline', 'pb_website', 'pb_phone', 'pb_email_public', 'pb_location_city', 'pb_location_state' );
        foreach ( $text_fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_post_meta( $post_id, $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
            }
        }

        // Number fields.
        $number_fields = array( 'pb_user_id', 'pb_experience_years', 'pb_hourly_rate', 'pb_minimum_booking', 'pb_deposit_percentage', 'pb_sale_price', 'pb_travel_radius' );
        foreach ( $number_fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_post_meta( $post_id, $field, floatval( $_POST[ $field ] ) );
            }
        }

        // Checkbox fields.
        update_post_meta( $post_id, 'pb_sale_active', isset( $_POST['pb_sale_active'] ) ? '1' : '' );
        update_post_meta( $post_id, 'pb_travel_willing', isset( $_POST['pb_travel_willing'] ) ? '1' : '' );
    }

    /**
     * Render the clean performer editor page.
     */
    public function render_performer_editor() {
        // Enqueue media uploader for image uploads.
        wp_enqueue_media();

        // Add performer ID to config for the editor.
        $performer_id = isset( $_GET['performer_id'] ) ? absint( $_GET['performer_id'] ) : 0;
        wp_add_inline_script(
            'peanut-booker-react',
            'window.peanutBookerPerformerId = ' . $performer_id . ';',
            'before'
        );

        $this->render_react_app();
    }

    /**
     * Modify performer row actions to use custom editor.
     *
     * @param array   $actions Row actions.
     * @param WP_Post $post    The post object.
     * @return array
     */
    public function modify_performer_row_actions( $actions, $post ) {
        if ( 'pb_performer' !== $post->post_type ) {
            return $actions;
        }

        // Replace Edit link with custom editor.
        if ( isset( $actions['edit'] ) ) {
            $edit_url = admin_url( 'admin.php?page=pb-edit-performer&performer_id=' . $post->ID );
            $actions['edit'] = sprintf(
                '<a href="%s">%s</a>',
                esc_url( $edit_url ),
                esc_html__( 'Edit', 'peanut-booker' )
            );
        }

        // Add "Edit in WordPress" link for advanced editing.
        $wp_edit_url = get_edit_post_link( $post->ID, 'raw' );
        $actions['edit_wp'] = sprintf(
            '<a href="%s">%s</a>',
            esc_url( str_replace( 'admin.php?page=pb-edit-performer', 'post.php?action=edit', $wp_edit_url ) ),
            esc_html__( 'Advanced Edit', 'peanut-booker' )
        );

        return $actions;
    }

    /**
     * Modify performer edit link to use custom editor.
     *
     * @param string $link    The edit link.
     * @param int    $post_id The post ID.
     * @param string $context The link context.
     * @return string
     */
    public function modify_performer_edit_link( $link, $post_id, $context ) {
        if ( 'pb_performer' !== get_post_type( $post_id ) ) {
            return $link;
        }

        // Only modify if we're not already on the edit screen.
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( $screen && 'pb_performer' === $screen->post_type && 'post' === $screen->base ) {
            return $link;
        }

        return admin_url( 'admin.php?page=pb-edit-performer&performer_id=' . $post_id );
    }

    /**
     * AJAX handler for saving performer from custom editor.
     */
    public function ajax_save_performer() {
        // Check nonce.
        if ( ! check_ajax_referer( 'pb_admin_performer_edit', 'pb_admin_nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'peanut-booker' ) ) );
        }

        $post_id = isset( $_POST['performer_id'] ) ? absint( $_POST['performer_id'] ) : 0;

        if ( ! $post_id || 'pb_performer' !== get_post_type( $post_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid performer.', 'peanut-booker' ) ) );
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'peanut-booker' ) ) );
        }

        // Update post content (bio).
        if ( isset( $_POST['pb_bio'] ) ) {
            wp_update_post( array(
                'ID'           => $post_id,
                'post_content' => wp_kses_post( wp_unslash( $_POST['pb_bio'] ) ),
            ) );
        }

        // Update thumbnail.
        if ( isset( $_POST['pb_thumbnail_id'] ) ) {
            $thumb_id = absint( $_POST['pb_thumbnail_id'] );
            if ( $thumb_id ) {
                set_post_thumbnail( $post_id, $thumb_id );
            } else {
                delete_post_thumbnail( $post_id );
            }
        }

        // Text fields.
        $text_fields = array( 'pb_stage_name', 'pb_tagline', 'pb_website', 'pb_phone', 'pb_email_public', 'pb_location_city', 'pb_location_state' );
        foreach ( $text_fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_post_meta( $post_id, $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
            }
        }

        // Number fields.
        $number_fields = array( 'pb_user_id', 'pb_experience_years', 'pb_hourly_rate', 'pb_minimum_booking', 'pb_deposit_percentage', 'pb_sale_price', 'pb_travel_radius' );
        foreach ( $number_fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_post_meta( $post_id, $field, floatval( $_POST[ $field ] ) );
            }
        }

        // Checkbox fields.
        update_post_meta( $post_id, 'pb_sale_active', isset( $_POST['pb_sale_active'] ) ? '1' : '' );
        update_post_meta( $post_id, 'pb_travel_willing', isset( $_POST['pb_travel_willing'] ) ? '1' : '' );

        // Gallery images.
        if ( isset( $_POST['pb_gallery_images'] ) ) {
            update_post_meta( $post_id, 'pb_gallery_images', sanitize_text_field( wp_unslash( $_POST['pb_gallery_images'] ) ) );
        }

        // Video links.
        if ( isset( $_POST['pb_videos'] ) && is_array( $_POST['pb_videos'] ) ) {
            $videos = array_map( 'esc_url_raw', wp_unslash( $_POST['pb_videos'] ) );
            $videos = array_filter( $videos );
            update_post_meta( $post_id, 'pb_video_links', implode( "\n", $videos ) );
        } else {
            update_post_meta( $post_id, 'pb_video_links', '' );
        }

        // Categories.
        if ( isset( $_POST['pb_categories'] ) && is_array( $_POST['pb_categories'] ) ) {
            $cat_ids = array_map( 'absint', $_POST['pb_categories'] );
            wp_set_post_terms( $post_id, $cat_ids, 'pb_performer_category' );
        } else {
            wp_set_post_terms( $post_id, array(), 'pb_performer_category' );
        }

        // Service areas.
        if ( isset( $_POST['pb_service_areas'] ) && is_array( $_POST['pb_service_areas'] ) ) {
            $area_ids = array_map( 'absint', $_POST['pb_service_areas'] );
            wp_set_post_terms( $post_id, $area_ids, 'pb_service_area' );
        } else {
            wp_set_post_terms( $post_id, array(), 'pb_service_area' );
        }

        wp_send_json_success( array(
            'message' => __( 'Performer saved successfully.', 'peanut-booker' ),
            'tab'     => isset( $_POST['current_tab'] ) ? sanitize_key( $_POST['current_tab'] ) : 'basic',
        ) );
    }
}
