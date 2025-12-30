<?php
/**
 * Subscriptions and Pro membership functionality.
 *
 * @package Peanut_Booker
 * @since   1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Subscriptions class.
 */
class Peanut_Booker_Subscriptions {

    /**
     * Plan types.
     */
    const PLAN_MONTHLY = 'monthly';
    const PLAN_ANNUAL  = 'annual';

    /**
     * Subscription statuses.
     */
    const STATUS_ACTIVE    = 'active';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED   = 'expired';
    const STATUS_PENDING   = 'pending';

    /**
     * Constructor.
     */
    public function __construct() {
        // WooCommerce Subscriptions hooks.
        add_action( 'woocommerce_subscription_status_active', array( $this, 'subscription_activated' ) );
        add_action( 'woocommerce_subscription_status_cancelled', array( $this, 'subscription_cancelled' ) );
        add_action( 'woocommerce_subscription_status_expired', array( $this, 'subscription_expired' ) );
        add_action( 'woocommerce_subscription_status_on-hold', array( $this, 'subscription_on_hold' ) );

        // Create subscription products on activation.
        add_action( 'peanut_booker_activated', array( $this, 'create_subscription_products' ) );

        // AJAX handlers.
        add_action( 'wp_ajax_pb_get_subscription_status', array( $this, 'ajax_get_status' ) );
    }

    /**
     * Create WooCommerce subscription products for Pro membership.
     */
    public function create_subscription_products() {
        if ( ! class_exists( 'WC_Product_Subscription' ) ) {
            return;
        }

        $options = get_option( 'peanut_booker_settings', array() );

        // Check if products already exist.
        $monthly_id = get_option( 'peanut_booker_pro_monthly_product' );
        $annual_id  = get_option( 'peanut_booker_pro_annual_product' );

        if ( ! $monthly_id || ! wc_get_product( $monthly_id ) ) {
            $monthly_id = $this->create_subscription_product(
                __( 'Peanut Booker Pro - Monthly', 'peanut-booker' ),
                $options['pro_monthly_price'] ?? 19.99,
                'month'
            );
            update_option( 'peanut_booker_pro_monthly_product', $monthly_id );
        }

        if ( ! $annual_id || ! wc_get_product( $annual_id ) ) {
            $annual_id = $this->create_subscription_product(
                __( 'Peanut Booker Pro - Annual', 'peanut-booker' ),
                $options['pro_annual_price'] ?? 199.99,
                'year'
            );
            update_option( 'peanut_booker_pro_annual_product', $annual_id );
        }
    }

    /**
     * Create a subscription product.
     *
     * @param string $name   Product name.
     * @param float  $price  Product price.
     * @param string $period Subscription period (month/year).
     * @return int Product ID.
     */
    private function create_subscription_product( $name, $price, $period ) {
        $product = new WC_Product_Subscription();

        $product->set_name( $name );
        $product->set_status( 'publish' );
        $product->set_catalog_visibility( 'hidden' );
        $product->set_regular_price( $price );
        $product->set_virtual( true );

        // Set subscription meta.
        $product->update_meta_data( '_subscription_price', $price );
        $product->update_meta_data( '_subscription_period', $period );
        $product->update_meta_data( '_subscription_period_interval', 1 );
        $product->update_meta_data( '_subscription_length', 0 );
        $product->update_meta_data( '_subscription_sign_up_fee', 0 );
        $product->update_meta_data( '_subscription_trial_period', '' );
        $product->update_meta_data( '_subscription_trial_length', 0 );

        // Mark as Peanut Booker product.
        $product->update_meta_data( '_pb_subscription_product', 'yes' );

        $product->save();

        return $product->get_id();
    }

    /**
     * Handle subscription activation.
     *
     * @param WC_Subscription $subscription WooCommerce subscription object.
     */
    public function subscription_activated( $subscription ) {
        if ( ! $this->is_pb_subscription( $subscription ) ) {
            return;
        }

        $user_id = $subscription->get_user_id();

        // Grant Pro capabilities.
        Peanut_Booker_Roles::grant_pro_capabilities( $user_id );

        // Record subscription.
        $plan_type = $this->get_subscription_plan_type( $subscription );

        // Check if record exists.
        $existing = Peanut_Booker_Database::get_row(
            'subscriptions',
            array( 'user_id' => $user_id )
        );

        $subscription_data = array(
            'wc_subscription_id' => $subscription->get_id(),
            'plan_type'          => $plan_type,
            'status'             => self::STATUS_ACTIVE,
            'start_date'         => current_time( 'mysql' ),
            'next_billing_date'  => $subscription->get_date( 'next_payment' ),
            'amount'             => $subscription->get_total(),
        );

        if ( $existing ) {
            Peanut_Booker_Database::update( 'subscriptions', $subscription_data, array( 'id' => $existing->id ) );
        } else {
            $subscription_data['user_id'] = $user_id;
            Peanut_Booker_Database::insert( 'subscriptions', $subscription_data );
        }

        do_action( 'peanut_booker_subscription_activated', $user_id, $subscription );
    }

    /**
     * Handle subscription cancellation.
     *
     * @param WC_Subscription $subscription WooCommerce subscription object.
     */
    public function subscription_cancelled( $subscription ) {
        if ( ! $this->is_pb_subscription( $subscription ) ) {
            return;
        }

        $user_id = $subscription->get_user_id();

        // Revoke Pro capabilities.
        Peanut_Booker_Roles::revoke_pro_capabilities( $user_id );

        // Update record.
        Peanut_Booker_Database::update(
            'subscriptions',
            array(
                'status'   => self::STATUS_CANCELLED,
                'end_date' => current_time( 'mysql' ),
            ),
            array( 'user_id' => $user_id )
        );

        do_action( 'peanut_booker_subscription_cancelled', $user_id, $subscription );
    }

    /**
     * Handle subscription expiration.
     *
     * @param WC_Subscription $subscription WooCommerce subscription object.
     */
    public function subscription_expired( $subscription ) {
        if ( ! $this->is_pb_subscription( $subscription ) ) {
            return;
        }

        $user_id = $subscription->get_user_id();

        // Revoke Pro capabilities.
        Peanut_Booker_Roles::revoke_pro_capabilities( $user_id );

        // Update record.
        Peanut_Booker_Database::update(
            'subscriptions',
            array(
                'status'   => self::STATUS_EXPIRED,
                'end_date' => current_time( 'mysql' ),
            ),
            array( 'user_id' => $user_id )
        );

        do_action( 'peanut_booker_subscription_expired', $user_id, $subscription );
    }

    /**
     * Handle subscription on hold.
     *
     * @param WC_Subscription $subscription WooCommerce subscription object.
     */
    public function subscription_on_hold( $subscription ) {
        if ( ! $this->is_pb_subscription( $subscription ) ) {
            return;
        }

        $user_id = $subscription->get_user_id();

        // Temporarily revoke Pro capabilities.
        Peanut_Booker_Roles::revoke_pro_capabilities( $user_id );

        do_action( 'peanut_booker_subscription_on_hold', $user_id, $subscription );
    }

    /**
     * Check if subscription is a Peanut Booker subscription.
     *
     * @param WC_Subscription $subscription Subscription object.
     * @return bool
     */
    private function is_pb_subscription( $subscription ) {
        foreach ( $subscription->get_items() as $item ) {
            $product = $item->get_product();
            if ( $product && 'yes' === $product->get_meta( '_pb_subscription_product' ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get subscription plan type.
     *
     * @param WC_Subscription $subscription Subscription object.
     * @return string Plan type.
     */
    private function get_subscription_plan_type( $subscription ) {
        $monthly_id = get_option( 'peanut_booker_pro_monthly_product' );

        foreach ( $subscription->get_items() as $item ) {
            $product_id = $item->get_product_id();
            if ( $product_id == $monthly_id ) {
                return self::PLAN_MONTHLY;
            }
        }

        return self::PLAN_ANNUAL;
    }

    /**
     * Get user's subscription status.
     *
     * @param int $user_id User ID.
     * @return array Subscription status data.
     */
    public static function get_user_subscription( $user_id ) {
        $subscription = Peanut_Booker_Database::get_row(
            'subscriptions',
            array( 'user_id' => $user_id )
        );

        if ( ! $subscription ) {
            return array(
                'has_subscription' => false,
                'is_pro'           => false,
                'status'           => null,
                'plan_type'        => null,
                'expires'          => null,
            );
        }

        return array(
            'has_subscription'  => true,
            'is_pro'            => self::STATUS_ACTIVE === $subscription->status,
            'status'            => $subscription->status,
            'status_label'      => self::get_status_label( $subscription->status ),
            'plan_type'         => $subscription->plan_type,
            'plan_label'        => self::get_plan_label( $subscription->plan_type ),
            'start_date'        => $subscription->start_date,
            'end_date'          => $subscription->end_date,
            'next_billing_date' => $subscription->next_billing_date,
            'amount'            => floatval( $subscription->amount ),
        );
    }

    /**
     * Get status label.
     *
     * @param string $status Status.
     * @return string Label.
     */
    public static function get_status_label( $status ) {
        $labels = array(
            self::STATUS_ACTIVE    => __( 'Active', 'peanut-booker' ),
            self::STATUS_CANCELLED => __( 'Cancelled', 'peanut-booker' ),
            self::STATUS_EXPIRED   => __( 'Expired', 'peanut-booker' ),
            self::STATUS_PENDING   => __( 'Pending', 'peanut-booker' ),
        );

        return $labels[ $status ] ?? $status;
    }

    /**
     * Get plan label.
     *
     * @param string $plan Plan type.
     * @return string Label.
     */
    public static function get_plan_label( $plan ) {
        $labels = array(
            self::PLAN_MONTHLY => __( 'Monthly', 'peanut-booker' ),
            self::PLAN_ANNUAL  => __( 'Annual', 'peanut-booker' ),
        );

        return $labels[ $plan ] ?? $plan;
    }

    /**
     * Get subscription checkout URL.
     *
     * @param string $plan_type Plan type (monthly/annual).
     * @return string Checkout URL.
     */
    public static function get_checkout_url( $plan_type = self::PLAN_MONTHLY ) {
        if ( self::PLAN_ANNUAL === $plan_type ) {
            $product_id = get_option( 'peanut_booker_pro_annual_product' );
        } else {
            $product_id = get_option( 'peanut_booker_pro_monthly_product' );
        }

        if ( ! $product_id ) {
            return '';
        }

        return add_query_arg( 'add-to-cart', $product_id, wc_get_checkout_url() );
    }

    /**
     * Get Pro membership pricing for display.
     *
     * @return array Pricing data.
     */
    public static function get_pricing() {
        $options = get_option( 'peanut_booker_settings', array() );

        $monthly_price = isset( $options['pro_monthly_price'] ) ? floatval( $options['pro_monthly_price'] ) : 19.99;
        $annual_price  = isset( $options['pro_annual_price'] ) ? floatval( $options['pro_annual_price'] ) : 199.99;

        $annual_monthly = $annual_price / 12;
        $savings        = ( $monthly_price * 12 ) - $annual_price;
        $savings_percent = round( ( $savings / ( $monthly_price * 12 ) ) * 100 );

        return array(
            'monthly'         => array(
                'price'         => $monthly_price,
                'formatted'     => wc_price( $monthly_price ),
                'period'        => __( '/month', 'peanut-booker' ),
                'checkout_url'  => self::get_checkout_url( self::PLAN_MONTHLY ),
            ),
            'annual'          => array(
                'price'          => $annual_price,
                'formatted'      => wc_price( $annual_price ),
                'period'         => __( '/year', 'peanut-booker' ),
                'monthly_price'  => $annual_monthly,
                'monthly_formatted' => wc_price( $annual_monthly ),
                'savings'        => $savings,
                'savings_formatted' => wc_price( $savings ),
                'savings_percent' => $savings_percent,
                'checkout_url'   => self::get_checkout_url( self::PLAN_ANNUAL ),
            ),
            'free_commission'  => isset( $options['commission_free_tier'] ) ? floatval( $options['commission_free_tier'] ) : 15,
            'pro_commission'   => isset( $options['commission_pro_tier'] ) ? floatval( $options['commission_pro_tier'] ) : 10,
        );
    }

    /**
     * Render Pro upgrade prompt.
     *
     * @return string HTML.
     */
    public static function render_upgrade_prompt() {
        $pricing = self::get_pricing();

        ob_start();
        ?>
        <div class="pb-upgrade-prompt">
            <h3><?php esc_html_e( 'Upgrade to Pro', 'peanut-booker' ); ?></h3>
            <p><?php esc_html_e( 'Unlock all features and grow your performance business.', 'peanut-booker' ); ?></p>

            <ul class="pb-pro-features">
                <li><?php esc_html_e( 'Unlimited photos and videos', 'peanut-booker' ); ?></li>
                <li><?php esc_html_e( 'Access to Event Market bidding', 'peanut-booker' ); ?></li>
                <li><?php esc_html_e( 'Lower commission rates', 'peanut-booker' ); ?> (<?php echo esc_html( $pricing['pro_commission'] ); ?>% <?php esc_html_e( 'vs', 'peanut-booker' ); ?> <?php echo esc_html( $pricing['free_commission'] ); ?>%)</li>
                <li><?php esc_html_e( 'Priority in search results', 'peanut-booker' ); ?></li>
                <li><?php esc_html_e( 'Advanced analytics', 'peanut-booker' ); ?></li>
            </ul>

            <div class="pb-pricing-options">
                <div class="pb-pricing-option">
                    <span class="pb-price"><?php echo wp_kses_post( $pricing['monthly']['formatted'] ); ?></span>
                    <span class="pb-period"><?php echo esc_html( $pricing['monthly']['period'] ); ?></span>
                    <a href="<?php echo esc_url( $pricing['monthly']['checkout_url'] ); ?>" class="pb-button">
                        <?php esc_html_e( 'Choose Monthly', 'peanut-booker' ); ?>
                    </a>
                </div>

                <div class="pb-pricing-option pb-recommended">
                    <span class="pb-badge"><?php esc_html_e( 'Save', 'peanut-booker' ); ?> <?php echo esc_html( $pricing['annual']['savings_percent'] ); ?>%</span>
                    <span class="pb-price"><?php echo wp_kses_post( $pricing['annual']['formatted'] ); ?></span>
                    <span class="pb-period"><?php echo esc_html( $pricing['annual']['period'] ); ?></span>
                    <span class="pb-note"><?php echo wp_kses_post( $pricing['annual']['monthly_formatted'] ); ?><?php esc_html_e( '/month', 'peanut-booker' ); ?></span>
                    <a href="<?php echo esc_url( $pricing['annual']['checkout_url'] ); ?>" class="pb-button pb-button-primary">
                        <?php esc_html_e( 'Choose Annual', 'peanut-booker' ); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX: Get subscription status.
     */
    public function ajax_get_status() {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Not logged in.', 'peanut-booker' ) ) );
        }

        $status = self::get_user_subscription( get_current_user_id() );
        wp_send_json_success( $status );
    }
}
