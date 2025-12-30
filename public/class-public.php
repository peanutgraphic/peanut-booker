<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package Peanut_Booker
 * @since   1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * The public-facing functionality of the plugin.
 */
class Peanut_Booker_Public {

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

        // Add template filters.
        add_filter( 'single_template', array( $this, 'load_single_performer_template' ) );
        add_filter( 'single_template', array( $this, 'load_single_market_event_template' ) );
        add_filter( 'archive_template', array( $this, 'load_archive_templates' ) );

        // Signup form handlers.
        add_action( 'admin_post_nopriv_pb_performer_signup', array( $this, 'handle_performer_signup' ) );
        add_action( 'admin_post_pb_performer_signup', array( $this, 'handle_performer_signup' ) );
        add_action( 'admin_post_nopriv_pb_customer_signup', array( $this, 'handle_customer_signup' ) );
        add_action( 'admin_post_pb_customer_signup', array( $this, 'handle_customer_signup' ) );
    }

    /**
     * Load single performer template from plugin.
     *
     * @param string $template The current template path.
     * @return string Modified template path.
     */
    public function load_single_performer_template( $template ) {
        global $post;

        if ( ! $post || 'pb_performer' !== $post->post_type ) {
            return $template;
        }

        // Check if theme has a custom template.
        $theme_template = locate_template( array( 'single-pb_performer.php', 'peanut-booker/single-performer.php' ) );

        if ( $theme_template ) {
            return $theme_template;
        }

        // Use plugin template.
        $plugin_template = PEANUT_BOOKER_PATH . 'templates/single-performer.php';
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }

        return $template;
    }

    /**
     * Load single market event template from plugin.
     *
     * @param string $template The current template path.
     * @return string Modified template path.
     */
    public function load_single_market_event_template( $template ) {
        global $post;

        if ( ! $post || 'pb_market_event' !== $post->post_type ) {
            return $template;
        }

        // Check if theme has a custom template.
        $theme_template = locate_template( array( 'single-pb_market_event.php', 'peanut-booker/single-market-event.php' ) );

        if ( $theme_template ) {
            return $theme_template;
        }

        // Use plugin template.
        $plugin_template = PEANUT_BOOKER_PATH . 'templates/single-market-event.php';
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }

        return $template;
    }

    /**
     * Load archive templates from plugin.
     *
     * @param string $template The current template path.
     * @return string Modified template path.
     */
    public function load_archive_templates( $template ) {
        if ( is_post_type_archive( 'pb_performer' ) ) {
            $theme_template = locate_template( array( 'archive-pb_performer.php', 'peanut-booker/archive-performer.php' ) );

            if ( $theme_template ) {
                return $theme_template;
            }

            $plugin_template = PEANUT_BOOKER_PATH . 'templates/archive-performer.php';
            if ( file_exists( $plugin_template ) ) {
                return $plugin_template;
            }
        }

        if ( is_post_type_archive( 'pb_market_event' ) ) {
            $theme_template = locate_template( array( 'archive-pb_market_event.php', 'peanut-booker/archive-market.php' ) );

            if ( $theme_template ) {
                return $theme_template;
            }

            $plugin_template = PEANUT_BOOKER_PATH . 'templates/archive-market.php';
            if ( file_exists( $plugin_template ) ) {
                return $plugin_template;
            }
        }

        return $template;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            PEANUT_BOOKER_URL . 'public/css/public.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            PEANUT_BOOKER_URL . 'public/js/public.js',
            array( 'jquery' ),
            $this->version,
            true
        );

        $localize_data = array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'restUrl' => rest_url( 'peanut-booker/v1/' ),
            'nonces'  => array(
                'booking'      => wp_create_nonce( 'pb_booking_nonce' ),
                'performer'    => wp_create_nonce( 'pb_performer_nonce' ),
                'customer'     => wp_create_nonce( 'pb_customer_nonce' ),
                'market'       => wp_create_nonce( 'pb_market_nonce' ),
                'review'       => wp_create_nonce( 'pb_review_nonce' ),
                'availability' => wp_create_nonce( 'pb_availability_nonce' ),
            ),
            'strings' => array(
                'loading'         => __( 'Loading...', 'peanut-booker' ),
                'error'           => __( 'An error occurred. Please try again.', 'peanut-booker' ),
                'confirmCancel'   => __( 'Are you sure you want to cancel this booking?', 'peanut-booker' ),
                'confirmComplete' => __( 'Confirm that this event has been completed?', 'peanut-booker' ),
                'selectDate'      => __( 'Please select a date.', 'peanut-booker' ),
                'required'        => __( 'This field is required.', 'peanut-booker' ),
            ),
            'isLoggedIn'  => is_user_logged_in(),
            'isPerformer' => Peanut_Booker_Roles::is_performer(),
            'isCustomer'  => Peanut_Booker_Roles::is_customer(),
            'isPro'       => Peanut_Booker_Roles::is_pro_performer(),
            'currency'    => get_woocommerce_currency_symbol(),
        );

        wp_localize_script( $this->plugin_name, 'peanutBooker', $localize_data );
    }

    /**
     * Register shortcodes.
     */
    public function register_shortcodes() {
        // Shortcodes are registered in the Peanut_Booker_Shortcodes class.
    }

    /**
     * Handle performer signup form submission.
     */
    public function handle_performer_signup() {
        // Verify nonce.
        if ( ! isset( $_POST['pb_signup_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pb_signup_nonce'] ) ), 'pb_performer_signup' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'peanut-booker' ), esc_html__( 'Error', 'peanut-booker' ), array( 'response' => 403 ) );
        }

        // Check if already logged in.
        if ( is_user_logged_in() ) {
            wp_safe_redirect( home_url( '/dashboard/' ) );
            exit;
        }

        // Sanitize input.
        $email        = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        $username     = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
        $password     = isset( $_POST['password'] ) ? $_POST['password'] : '';
        $display_name = isset( $_POST['display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) : '';
        $category     = isset( $_POST['category'] ) ? absint( $_POST['category'] ) : 0;

        // Split display name into first/last.
        $name_parts = explode( ' ', $display_name, 2 );
        $first_name = $name_parts[0];
        $last_name  = isset( $name_parts[1] ) ? $name_parts[1] : '';

        // Validation.
        $errors = array();

        if ( empty( $email ) || ! is_email( $email ) ) {
            $errors[] = __( 'Please enter a valid email address.', 'peanut-booker' );
        }

        if ( empty( $username ) ) {
            $errors[] = __( 'Please enter a username.', 'peanut-booker' );
        }

        if ( empty( $password ) || strlen( $password ) < 8 ) {
            $errors[] = __( 'Password must be at least 8 characters.', 'peanut-booker' );
        }

        if ( empty( $display_name ) ) {
            $errors[] = __( 'Please enter your name.', 'peanut-booker' );
        }

        if ( empty( $category ) ) {
            $errors[] = __( 'Please select a category.', 'peanut-booker' );
        }

        if ( email_exists( $email ) ) {
            $errors[] = __( 'An account with this email already exists.', 'peanut-booker' );
        }

        if ( username_exists( $username ) ) {
            $errors[] = __( 'This username is already taken.', 'peanut-booker' );
        }

        // If errors, redirect back with error messages.
        if ( ! empty( $errors ) ) {
            $redirect_url = add_query_arg(
                array(
                    'pb_auth_error' => urlencode( implode( ' ', $errors ) ),
                ),
                home_url( '/performer-signup/' )
            );
            wp_safe_redirect( $redirect_url );
            exit;
        }

        // Create user.
        $user_id = wp_create_user( $username, $password, $email );

        if ( is_wp_error( $user_id ) ) {
            $redirect_url = add_query_arg(
                array( 'pb_auth_error' => urlencode( $user_id->get_error_message() ) ),
                home_url( '/performer-signup/' )
            );
            wp_safe_redirect( $redirect_url );
            exit;
        }

        // Update user meta.
        wp_update_user( array(
            'ID'           => $user_id,
            'display_name' => $display_name,
            'first_name'   => $first_name,
            'last_name'    => $last_name,
        ) );

        // Assign performer role.
        $user = new WP_User( $user_id );
        $user->set_role( 'pb_performer' );

        // Create performer record.
        if ( class_exists( 'Peanut_Booker_Performer' ) ) {
            $performer_data = array(
                'stage_name'  => $display_name,
                'first_name'  => $first_name,
                'last_name'   => $last_name,
                'category_id' => $category,
            );
            Peanut_Booker_Performer::create_performer( $user_id, $performer_data );
        }

        // Log user in.
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );

        // Redirect to dashboard.
        wp_safe_redirect( home_url( '/dashboard/?welcome=performer' ) );
        exit;
    }

    /**
     * Handle customer signup form submission.
     */
    public function handle_customer_signup() {
        // Verify nonce.
        if ( ! isset( $_POST['pb_signup_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pb_signup_nonce'] ) ), 'pb_customer_signup' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'peanut-booker' ), esc_html__( 'Error', 'peanut-booker' ), array( 'response' => 403 ) );
        }

        // Check if already logged in.
        if ( is_user_logged_in() ) {
            wp_safe_redirect( home_url( '/dashboard/' ) );
            exit;
        }

        // Sanitize input.
        $email        = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        $username     = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
        $password     = isset( $_POST['password'] ) ? $_POST['password'] : '';
        $display_name = isset( $_POST['display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) : '';

        // Split display name into first/last.
        $name_parts = explode( ' ', $display_name, 2 );
        $first_name = $name_parts[0];
        $last_name  = isset( $name_parts[1] ) ? $name_parts[1] : '';

        // Validation.
        $errors = array();

        if ( empty( $email ) || ! is_email( $email ) ) {
            $errors[] = __( 'Please enter a valid email address.', 'peanut-booker' );
        }

        if ( empty( $username ) ) {
            $errors[] = __( 'Please enter a username.', 'peanut-booker' );
        }

        if ( empty( $password ) || strlen( $password ) < 8 ) {
            $errors[] = __( 'Password must be at least 8 characters.', 'peanut-booker' );
        }

        if ( empty( $display_name ) ) {
            $errors[] = __( 'Please enter your name.', 'peanut-booker' );
        }

        if ( email_exists( $email ) ) {
            $errors[] = __( 'An account with this email already exists.', 'peanut-booker' );
        }

        if ( username_exists( $username ) ) {
            $errors[] = __( 'This username is already taken.', 'peanut-booker' );
        }

        // If errors, redirect back with error messages.
        if ( ! empty( $errors ) ) {
            $redirect_url = add_query_arg(
                array(
                    'pb_auth_error' => urlencode( implode( ' ', $errors ) ),
                ),
                home_url( '/customer-signup/' )
            );
            wp_safe_redirect( $redirect_url );
            exit;
        }

        // Create user.
        $user_id = wp_create_user( $username, $password, $email );

        if ( is_wp_error( $user_id ) ) {
            $redirect_url = add_query_arg(
                array( 'pb_auth_error' => urlencode( $user_id->get_error_message() ) ),
                home_url( '/customer-signup/' )
            );
            wp_safe_redirect( $redirect_url );
            exit;
        }

        // Update user meta.
        wp_update_user( array(
            'ID'           => $user_id,
            'display_name' => $display_name,
            'first_name'   => $first_name,
            'last_name'    => $last_name,
        ) );

        // Assign customer role.
        $user = new WP_User( $user_id );
        $user->set_role( 'pb_customer' );

        // Log user in.
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );

        // Redirect to performer directory.
        wp_safe_redirect( home_url( '/performer-directory/' ) );
        exit;
    }
}
