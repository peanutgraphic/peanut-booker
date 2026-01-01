<?php
/**
 * WooCommerce integration.
 *
 * @package Peanut_Booker
 * @since   1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * WooCommerce integration class.
 */
class Peanut_Booker_WooCommerce {

    /**
     * Constructor.
     */
    public function __construct() {
        // Register custom order statuses.
        add_action( 'init', array( $this, 'register_order_statuses' ) );
        add_filter( 'wc_order_statuses', array( $this, 'add_order_statuses' ) );

        // Handle booking checkout.
        add_action( 'wp', array( $this, 'handle_booking_checkout' ) );
        add_action( 'woocommerce_checkout_create_order', array( $this, 'add_booking_to_order' ), 10, 2 );
        add_action( 'woocommerce_payment_complete', array( $this, 'payment_complete' ) );

        // Order display.
        add_filter( 'woocommerce_get_order_item_totals', array( $this, 'add_booking_info_to_order' ), 10, 2 );

        // Custom product handling.
        add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_booking_cart_data' ), 10, 2 );
        add_filter( 'woocommerce_get_item_data', array( $this, 'display_booking_cart_data' ), 10, 2 );
        add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'save_booking_order_item_meta' ), 10, 4 );
    }

    /**
     * Register custom order statuses for escrow.
     */
    public function register_order_statuses() {
        register_post_status(
            'wc-escrow-held',
            array(
                'label'                     => _x( 'Escrow Held', 'Order status', 'peanut-booker' ),
                'public'                    => true,
                'show_in_admin_status_list' => true,
                'show_in_admin_all_list'    => true,
                'exclude_from_search'       => false,
                /* translators: %s: number of orders */
                'label_count'               => _n_noop( 'Escrow Held <span class="count">(%s)</span>', 'Escrow Held <span class="count">(%s)</span>', 'peanut-booker' ),
            )
        );

        register_post_status(
            'wc-event-complete',
            array(
                'label'                     => _x( 'Event Complete', 'Order status', 'peanut-booker' ),
                'public'                    => true,
                'show_in_admin_status_list' => true,
                'show_in_admin_all_list'    => true,
                'exclude_from_search'       => false,
                /* translators: %s: number of orders */
                'label_count'               => _n_noop( 'Event Complete <span class="count">(%s)</span>', 'Event Complete <span class="count">(%s)</span>', 'peanut-booker' ),
            )
        );

        register_post_status(
            'wc-funds-released',
            array(
                'label'                     => _x( 'Funds Released', 'Order status', 'peanut-booker' ),
                'public'                    => true,
                'show_in_admin_status_list' => true,
                'show_in_admin_all_list'    => true,
                'exclude_from_search'       => false,
                /* translators: %s: number of orders */
                'label_count'               => _n_noop( 'Funds Released <span class="count">(%s)</span>', 'Funds Released <span class="count">(%s)</span>', 'peanut-booker' ),
            )
        );
    }

    /**
     * Add custom statuses to WooCommerce.
     *
     * @param array $statuses Existing statuses.
     * @return array Modified statuses.
     */
    public function add_order_statuses( $statuses ) {
        $statuses['wc-escrow-held']    = _x( 'Escrow Held', 'Order status', 'peanut-booker' );
        $statuses['wc-event-complete'] = _x( 'Event Complete', 'Order status', 'peanut-booker' );
        $statuses['wc-funds-released'] = _x( 'Funds Released', 'Order status', 'peanut-booker' );

        return $statuses;
    }

    /**
     * Handle booking checkout requests.
     */
    public function handle_booking_checkout() {
        if ( ! isset( $_GET['pb_booking'] ) || ! isset( $_GET['action'] ) ) {
            return;
        }

        if ( 'checkout' !== $_GET['action'] ) {
            return;
        }

        // SECURITY: Verify nonce to prevent CSRF attacks.
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'pb_checkout_booking' ) ) {
            wc_add_notice( __( 'Security verification failed. Please try again.', 'peanut-booker' ), 'error' );
            return;
        }

        // SECURITY: Require user to be logged in.
        if ( ! is_user_logged_in() ) {
            wc_add_notice( __( 'You must be logged in to checkout.', 'peanut-booker' ), 'error' );
            wp_safe_redirect( wp_login_url( add_query_arg( array() ) ) );
            exit;
        }

        $booking_id = absint( $_GET['pb_booking'] );

        // SECURITY: Validate booking ID is a positive integer.
        if ( $booking_id <= 0 ) {
            wc_add_notice( __( 'Invalid booking ID.', 'peanut-booker' ), 'error' );
            return;
        }

        $booking = Peanut_Booker_Booking::get( $booking_id );

        if ( ! $booking ) {
            wc_add_notice( __( 'Invalid booking.', 'peanut-booker' ), 'error' );
            return;
        }

        // SECURITY: Verify customer owns this booking.
        if ( (int) $booking->customer_id !== get_current_user_id() ) {
            // Log potential unauthorized access attempt.
            error_log( sprintf(
                'Peanut Booker SECURITY: Unauthorized checkout attempt. Booking ID: %d, Owner: %d, Attempted by: %d, IP: %s',
                $booking_id,
                $booking->customer_id,
                get_current_user_id(),
                sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? 'unknown' ) )
            ) );
            wc_add_notice( __( 'Not authorized.', 'peanut-booker' ), 'error' );
            return;
        }

        // SECURITY: Verify booking is in a valid status for checkout.
        $valid_checkout_statuses = array(
            Peanut_Booker_Booking::STATUS_PENDING,
            Peanut_Booker_Booking::STATUS_CONFIRMED,
        );
        if ( ! in_array( $booking->status, $valid_checkout_statuses, true ) ) {
            wc_add_notice(
                sprintf(
                    /* translators: %s: booking status */
                    __( 'This booking cannot be checked out. Current status: %s', 'peanut-booker' ),
                    esc_html( $booking->status )
                ),
                'error'
            );
            return;
        }

        // SECURITY: Determine if this is a deposit or remaining balance payment.
        $is_remaining_payment = isset( $_GET['payment'] ) && 'remaining' === $_GET['payment'];

        if ( $is_remaining_payment ) {
            // Paying remaining balance - must have deposit already paid.
            if ( ! $booking->deposit_paid ) {
                wc_add_notice( __( 'Deposit must be paid before remaining balance.', 'peanut-booker' ), 'error' );
                return;
            }
            if ( $booking->fully_paid ) {
                wc_add_notice( __( 'This booking has already been fully paid.', 'peanut-booker' ), 'error' );
                return;
            }
        } else {
            // Paying deposit - must not already be paid.
            if ( $booking->deposit_paid ) {
                wc_add_notice( __( 'Deposit for this booking has already been paid.', 'peanut-booker' ), 'error' );
                return;
            }
        }

        // Clear cart and add booking product.
        WC()->cart->empty_cart();

        // Get or create booking product.
        $product_id = $this->get_booking_product_id();

        // Add to cart with booking data.
        WC()->cart->add_to_cart(
            $product_id,
            1,
            0,
            array(),
            array(
                'pb_booking_id'     => $booking_id,
                'pb_booking_number' => $booking->booking_number,
                'pb_event_title'    => $booking->event_title,
                'pb_event_date'     => $booking->event_date,
                'pb_total_amount'   => $booking->deposit_amount, // Charge deposit first.
                'pb_is_deposit'     => true,
            )
        );

        wp_safe_redirect( wc_get_checkout_url() );
        exit;
    }

    /**
     * Get or create the booking product.
     *
     * @return int Product ID.
     */
    private function get_booking_product_id() {
        $product_id = get_option( 'peanut_booker_booking_product' );

        if ( $product_id && wc_get_product( $product_id ) ) {
            return $product_id;
        }

        // Create product.
        $product = new WC_Product_Simple();
        $product->set_name( __( 'Performer Booking', 'peanut-booker' ) );
        $product->set_status( 'publish' );
        $product->set_catalog_visibility( 'hidden' );
        $product->set_price( 0 );
        $product->set_regular_price( 0 );
        $product->set_virtual( true );
        $product->set_sold_individually( true );
        $product->update_meta_data( '_pb_booking_product', 'yes' );
        $product->save();

        $product_id = $product->get_id();
        update_option( 'peanut_booker_booking_product', $product_id );

        return $product_id;
    }

    /**
     * Add booking data to cart item.
     *
     * @param array $cart_item_data Cart item data.
     * @param int   $product_id     Product ID.
     * @return array Modified cart item data.
     */
    public function add_booking_cart_data( $cart_item_data, $product_id ) {
        $booking_product_id = get_option( 'peanut_booker_booking_product' );

        if ( $product_id != $booking_product_id ) {
            return $cart_item_data;
        }

        // Data is already added in handle_booking_checkout.
        return $cart_item_data;
    }

    /**
     * Display booking info in cart.
     *
     * @param array $item_data Existing item data.
     * @param array $cart_item Cart item.
     * @return array Modified item data.
     */
    public function display_booking_cart_data( $item_data, $cart_item ) {
        if ( ! isset( $cart_item['pb_booking_id'] ) ) {
            return $item_data;
        }

        $item_data[] = array(
            'key'   => __( 'Booking', 'peanut-booker' ),
            'value' => '#' . $cart_item['pb_booking_number'],
        );

        $item_data[] = array(
            'key'   => __( 'Event', 'peanut-booker' ),
            'value' => $cart_item['pb_event_title'],
        );

        $item_data[] = array(
            'key'   => __( 'Date', 'peanut-booker' ),
            'value' => date_i18n( get_option( 'date_format' ), strtotime( $cart_item['pb_event_date'] ) ),
        );

        if ( $cart_item['pb_is_deposit'] ) {
            $item_data[] = array(
                'key'   => __( 'Payment Type', 'peanut-booker' ),
                'value' => __( 'Deposit', 'peanut-booker' ),
            );
        }

        return $item_data;
    }

    /**
     * Save booking meta to order item.
     *
     * @param WC_Order_Item_Product $item          Order item.
     * @param string                $cart_item_key Cart item key.
     * @param array                 $values        Cart item values.
     * @param WC_Order              $order         Order object.
     */
    public function save_booking_order_item_meta( $item, $cart_item_key, $values, $order ) {
        if ( ! isset( $values['pb_booking_id'] ) ) {
            return;
        }

        $item->add_meta_data( '_pb_booking_id', $values['pb_booking_id'] );
        $item->add_meta_data( '_pb_booking_number', $values['pb_booking_number'] );
        $item->add_meta_data( '_pb_event_title', $values['pb_event_title'] );
        $item->add_meta_data( '_pb_event_date', $values['pb_event_date'] );
        $item->add_meta_data( '_pb_is_deposit', $values['pb_is_deposit'] );
    }

    /**
     * Add booking to order on checkout.
     *
     * @param WC_Order $order Order object.
     * @param array    $data  Checkout data.
     */
    public function add_booking_to_order( $order, $data ) {
        foreach ( WC()->cart->get_cart() as $cart_item ) {
            if ( isset( $cart_item['pb_booking_id'] ) ) {
                $order->add_meta_data( '_pb_booking_id', $cart_item['pb_booking_id'] );
                $order->add_meta_data( '_pb_is_deposit', $cart_item['pb_is_deposit'] );

                // Update cart item price.
                $order->set_total( $cart_item['pb_total_amount'] );
            }
        }
    }

    /**
     * Handle successful payment.
     *
     * @param int $order_id Order ID.
     */
    public function payment_complete( $order_id ) {
        $order      = wc_get_order( $order_id );
        $booking_id = $order->get_meta( '_pb_booking_id' );
        $is_deposit = $order->get_meta( '_pb_is_deposit' );

        if ( ! $booking_id ) {
            return;
        }

        $booking = Peanut_Booker_Booking::get( $booking_id );
        if ( ! $booking ) {
            return;
        }

        // Update booking.
        $update_data = array(
            'order_id' => $order_id,
        );

        if ( $is_deposit ) {
            $update_data['deposit_paid']  = 1;
            $update_data['escrow_status'] = Peanut_Booker_Booking::ESCROW_DEPOSIT;
        } else {
            $update_data['fully_paid']    = 1;
            $update_data['escrow_status'] = Peanut_Booker_Booking::ESCROW_FULL;
        }

        Peanut_Booker_Booking::update( $booking_id, $update_data );

        // Record transaction.
        Peanut_Booker_Database::insert(
            'transactions',
            array(
                'booking_id'       => $booking_id,
                'order_id'         => $order_id,
                'transaction_type' => $is_deposit ? 'deposit' : 'full_payment',
                'amount'           => $order->get_total(),
                'payment_method'   => $order->get_payment_method(),
                'payment_id'       => $order->get_transaction_id(),
                'payer_id'         => $booking->customer_id,
                'status'           => 'completed',
            )
        );

        // Update order status.
        $order->update_status( 'escrow-held', __( 'Payment received, held in escrow.', 'peanut-booker' ) );

        // If performer already confirmed and deposit paid, confirm booking.
        if ( $booking->performer_confirmed && $update_data['deposit_paid'] ) {
            Peanut_Booker_Booking::update_status( $booking_id, Peanut_Booker_Booking::STATUS_CONFIRMED );
        }

        do_action( 'peanut_booker_payment_received', $booking_id, $order_id, $is_deposit );
    }

    /**
     * Add booking info to order totals display.
     *
     * @param array    $total_rows Order total rows.
     * @param WC_Order $order      Order object.
     * @return array Modified total rows.
     */
    public function add_booking_info_to_order( $total_rows, $order ) {
        $booking_id = $order->get_meta( '_pb_booking_id' );

        if ( ! $booking_id ) {
            return $total_rows;
        }

        $booking = Peanut_Booker_Booking::get( $booking_id );
        if ( ! $booking ) {
            return $total_rows;
        }

        // Add booking info at the beginning.
        $booking_rows = array(
            'booking_number' => array(
                'label' => __( 'Booking Number:', 'peanut-booker' ),
                'value' => $booking->booking_number,
            ),
            'event_title'    => array(
                'label' => __( 'Event:', 'peanut-booker' ),
                'value' => $booking->event_title,
            ),
            'event_date'     => array(
                'label' => __( 'Event Date:', 'peanut-booker' ),
                'value' => date_i18n( get_option( 'date_format' ), strtotime( $booking->event_date ) ),
            ),
        );

        return array_merge( $booking_rows, $total_rows );
    }

    /**
     * Get checkout URL for a booking deposit.
     *
     * @param int $booking_id Booking ID.
     * @return string Checkout URL with nonce.
     */
    public static function get_checkout_url( $booking_id ) {
        $booking = Peanut_Booker_Booking::get( $booking_id );

        if ( ! $booking ) {
            return '';
        }

        // Don't generate URL if deposit already paid.
        if ( $booking->deposit_paid ) {
            return '';
        }

        // SECURITY: Include nonce for CSRF protection.
        return add_query_arg(
            array(
                'pb_booking' => $booking_id,
                'action'     => 'checkout',
                '_wpnonce'   => wp_create_nonce( 'pb_checkout_booking' ),
            ),
            wc_get_checkout_url()
        );
    }

    /**
     * Process remaining balance payment.
     *
     * @param int $booking_id Booking ID.
     * @return string Checkout URL.
     */
    public static function get_remaining_balance_checkout_url( $booking_id ) {
        $booking = Peanut_Booker_Booking::get( $booking_id );

        if ( ! $booking || ! $booking->deposit_paid || $booking->fully_paid ) {
            return '';
        }

        // SECURITY: Include nonce for CSRF protection.
        return add_query_arg(
            array(
                'pb_booking' => $booking_id,
                'action'     => 'checkout',
                'payment'    => 'remaining',
                '_wpnonce'   => wp_create_nonce( 'pb_checkout_booking' ),
            ),
            wc_get_checkout_url()
        );
    }
}
