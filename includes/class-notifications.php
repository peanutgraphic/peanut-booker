<?php
/**
 * Email notifications functionality.
 *
 * @package Peanut_Booker
 * @since   1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Notifications class.
 */
class Peanut_Booker_Notifications {

    /**
     * Email templates.
     *
     * @var array
     */
    private static $templates = array(
        'new_booking'            => array(
            'subject' => 'New Booking Request: {event_title}',
            'to'      => 'performer',
        ),
        'booking_confirmed'      => array(
            'subject' => 'Booking Confirmed: {event_title}',
            'to'      => 'customer',
        ),
        'booking_cancelled'      => array(
            'subject' => 'Booking Cancelled: {event_title}',
            'to'      => 'both',
        ),
        'booking_completed'      => array(
            'subject' => 'Booking Completed: {event_title}',
            'to'      => 'both',
        ),
        'booking_reminder_1day'  => array(
            'subject' => 'Reminder: Your event is tomorrow!',
            'to'      => 'both',
        ),
        'booking_reminder_7day'  => array(
            'subject' => 'Reminder: Your event is in one week',
            'to'      => 'both',
        ),
        'escrow_released'        => array(
            'subject' => 'Payment Released: {event_title}',
            'to'      => 'performer',
        ),
        'new_bid'                => array(
            'subject' => 'New Bid Received: {event_title}',
            'to'      => 'customer',
        ),
        'bid_accepted'           => array(
            'subject' => 'Your Bid Was Accepted!',
            'to'      => 'performer',
        ),
        'bid_rejected'           => array(
            'subject' => 'Bid Update: {event_title}',
            'to'      => 'performer',
        ),
        'new_review'             => array(
            'subject' => 'New Review Received',
            'to'      => 'reviewee',
        ),
        'review_flagged'         => array(
            'subject' => '[Admin] Review Flagged for Arbitration',
            'to'      => 'admin',
        ),
        'review_arbitrated'      => array(
            'subject' => 'Review Arbitration Decision',
            'to'      => 'both_parties',
        ),
        'subscription_expiring'  => array(
            'subject' => 'Your Pro Subscription is Expiring Soon',
            'to'      => 'performer',
        ),
    );

    /**
     * Constructor.
     */
    public function __construct() {
        add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
    }

    /**
     * Set HTML content type for emails.
     *
     * @return string
     */
    public function set_html_content_type() {
        return 'text/html';
    }

    /**
     * Send a notification.
     *
     * @param string $type    Notification type.
     * @param int    $item_id Related item ID (booking, bid, review, etc.).
     * @return bool Success.
     */
    public static function send( $type, $item_id ) {
        if ( ! isset( self::$templates[ $type ] ) ) {
            return false;
        }

        $template = self::$templates[ $type ];
        $data     = self::get_notification_data( $type, $item_id );

        if ( empty( $data ) ) {
            return false;
        }

        $recipients = self::get_recipients( $type, $data );
        $subject    = self::parse_template( $template['subject'], $data );
        $body       = self::get_email_body( $type, $data );

        $sent = true;
        foreach ( $recipients as $email ) {
            if ( ! wp_mail( $email, $subject, $body, self::get_headers() ) ) {
                $sent = false;
            }
        }

        do_action( 'peanut_booker_notification_sent', $type, $item_id, $recipients );

        return $sent;
    }

    /**
     * Get notification data based on type.
     *
     * @param string $type    Notification type.
     * @param int    $item_id Item ID.
     * @return array Data for template.
     */
    private static function get_notification_data( $type, $item_id ) {
        $data = array(
            'site_name' => get_bloginfo( 'name' ),
            'site_url'  => home_url(),
        );

        switch ( $type ) {
            case 'new_booking':
            case 'booking_confirmed':
            case 'booking_cancelled':
            case 'booking_completed':
            case 'booking_reminder_1day':
            case 'booking_reminder_7day':
            case 'escrow_released':
                $booking = Peanut_Booker_Booking::get( $item_id );
                if ( ! $booking ) {
                    return array();
                }

                $performer = Peanut_Booker_Performer::get( $booking->performer_id );
                $customer  = Peanut_Booker_Customer::get( $booking->customer_id );

                $data['booking']        = $booking;
                $data['event_title']    = $booking->event_title;
                $data['event_date']     = date_i18n( get_option( 'date_format' ), strtotime( $booking->event_date ) );
                $data['event_time']     = $booking->event_start_time;
                $data['event_location'] = $booking->event_location;
                $data['total_amount']   = wc_price( $booking->total_amount );
                $data['booking_number'] = $booking->booking_number;
                $data['performer']      = $performer;
                $data['performer_name'] = $performer ? get_userdata( $performer->user_id )->display_name : '';
                $data['performer_email'] = $performer ? get_userdata( $performer->user_id )->user_email : '';
                $data['customer']       = $customer;
                $data['customer_name']  = $customer ? $customer['display_name'] : '';
                $data['customer_email'] = $customer ? $customer['email'] : '';
                $data['dashboard_url']  = home_url( '/dashboard/' );
                break;

            case 'new_bid':
            case 'bid_accepted':
            case 'bid_rejected':
                $bid = Peanut_Booker_Database::get_row( 'bids', array( 'id' => $item_id ) );
                if ( ! $bid ) {
                    return array();
                }

                $event     = Peanut_Booker_Market::get_event_data( $bid->event_id );
                $performer = Peanut_Booker_Performer::get( $bid->performer_id );

                $data['bid']            = $bid;
                $data['bid_amount']     = wc_price( $bid->bid_amount );
                $data['event']          = $event;
                $data['event_title']    = $event['title'];
                $data['event_date']     = $event['event_date_formatted'];
                $data['performer']      = $performer;
                $data['performer_name'] = $performer ? get_userdata( $performer->user_id )->display_name : '';
                $data['performer_email'] = $performer ? get_userdata( $performer->user_id )->user_email : '';
                $data['customer']       = $event['customer'];
                $data['customer_name']  = $event['customer']['display_name'] ?? '';
                $data['customer_email'] = $event['customer']['email'] ?? '';
                break;

            case 'new_review':
            case 'review_flagged':
            case 'review_arbitrated':
                $review = Peanut_Booker_Reviews::get( $item_id );
                if ( ! $review ) {
                    return array();
                }

                $reviewer = get_userdata( $review->reviewer_id );
                $reviewee = get_userdata( $review->reviewee_id );

                $data['review']         = $review;
                $data['rating']         = $review->rating;
                $data['review_content'] = $review->content;
                $data['reviewer_name']  = $reviewer ? $reviewer->display_name : '';
                $data['reviewer_email'] = $reviewer ? $reviewer->user_email : '';
                $data['reviewee_name']  = $reviewee ? $reviewee->display_name : '';
                $data['reviewee_email'] = $reviewee ? $reviewee->user_email : '';
                break;
        }

        return $data;
    }

    /**
     * Get email recipients.
     *
     * @param string $type Notification type.
     * @param array  $data Notification data.
     * @return array Email addresses.
     */
    private static function get_recipients( $type, $data ) {
        $template   = self::$templates[ $type ];
        $recipients = array();

        switch ( $template['to'] ) {
            case 'performer':
                if ( ! empty( $data['performer_email'] ) ) {
                    $recipients[] = $data['performer_email'];
                }
                break;

            case 'customer':
                if ( ! empty( $data['customer_email'] ) ) {
                    $recipients[] = $data['customer_email'];
                }
                break;

            case 'both':
                if ( ! empty( $data['performer_email'] ) ) {
                    $recipients[] = $data['performer_email'];
                }
                if ( ! empty( $data['customer_email'] ) ) {
                    $recipients[] = $data['customer_email'];
                }
                break;

            case 'reviewee':
                if ( ! empty( $data['reviewee_email'] ) ) {
                    $recipients[] = $data['reviewee_email'];
                }
                break;

            case 'both_parties':
                if ( ! empty( $data['reviewer_email'] ) ) {
                    $recipients[] = $data['reviewer_email'];
                }
                if ( ! empty( $data['reviewee_email'] ) ) {
                    $recipients[] = $data['reviewee_email'];
                }
                break;

            case 'admin':
                $recipients[] = get_option( 'admin_email' );
                break;
        }

        return array_unique( array_filter( $recipients ) );
    }

    /**
     * Parse template string with data.
     *
     * @param string $template Template string.
     * @param array  $data     Data array.
     * @return string Parsed string.
     */
    private static function parse_template( $template, $data ) {
        foreach ( $data as $key => $value ) {
            if ( is_string( $value ) || is_numeric( $value ) ) {
                $template = str_replace( '{' . $key . '}', $value, $template );
            }
        }
        return $template;
    }

    /**
     * Get email body HTML.
     *
     * @param string $type Notification type.
     * @param array  $data Notification data.
     * @return string HTML body.
     */
    private static function get_email_body( $type, $data ) {
        $options   = get_option( 'peanut_booker_settings', array() );
        $from_name = $options['email_from_name'] ?? get_bloginfo( 'name' );

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
            <table role="presentation" style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 20px;">
                        <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <!-- Header -->
                            <tr>
                                <td style="background-color: #6366f1; padding: 20px; text-align: center;">
                                    <h1 style="color: #ffffff; margin: 0; font-size: 24px;"><?php echo esc_html( $from_name ); ?></h1>
                                </td>
                            </tr>

                            <!-- Content -->
                            <tr>
                                <td style="padding: 30px;">
                                    <?php echo self::get_email_content( $type, $data ); ?>
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style="background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #e9ecef;">
                                    <p style="margin: 0; color: #6c757d; font-size: 12px;">
                                        <?php echo esc_html( $from_name ); ?><br>
                                        <a href="<?php echo esc_url( home_url() ); ?>" style="color: #6366f1;"><?php echo esc_html( home_url() ); ?></a>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Get email content for specific notification type.
     *
     * @param string $type Notification type.
     * @param array  $data Notification data.
     * @return string HTML content.
     */
    private static function get_email_content( $type, $data ) {
        ob_start();

        switch ( $type ) {
            case 'new_booking':
                ?>
                <h2 style="color: #333; margin-top: 0;"><?php esc_html_e( 'New Booking Request!', 'peanut-booker' ); ?></h2>
                <p><?php esc_html_e( 'You have received a new booking request.', 'peanut-booker' ); ?></p>

                <div style="background-color: #f8f9fa; padding: 20px; border-radius: 4px; margin: 20px 0;">
                    <p style="margin: 5px 0;"><strong><?php esc_html_e( 'Event:', 'peanut-booker' ); ?></strong> <?php echo esc_html( $data['event_title'] ); ?></p>
                    <p style="margin: 5px 0;"><strong><?php esc_html_e( 'Date:', 'peanut-booker' ); ?></strong> <?php echo esc_html( $data['event_date'] ); ?></p>
                    <p style="margin: 5px 0;"><strong><?php esc_html_e( 'Location:', 'peanut-booker' ); ?></strong> <?php echo esc_html( $data['event_location'] ); ?></p>
                    <p style="margin: 5px 0;"><strong><?php esc_html_e( 'Customer:', 'peanut-booker' ); ?></strong> <?php echo esc_html( $data['customer_name'] ); ?></p>
                    <p style="margin: 5px 0;"><strong><?php esc_html_e( 'Total:', 'peanut-booker' ); ?></strong> <?php echo wp_kses_post( $data['total_amount'] ); ?></p>
                </div>

                <p>
                    <a href="<?php echo esc_url( $data['dashboard_url'] ); ?>" style="display: inline-block; background-color: #6366f1; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px;">
                        <?php esc_html_e( 'View in Dashboard', 'peanut-booker' ); ?>
                    </a>
                </p>
                <?php
                break;

            case 'booking_confirmed':
                ?>
                <h2 style="color: #333; margin-top: 0;"><?php esc_html_e( 'Booking Confirmed!', 'peanut-booker' ); ?></h2>
                <p><?php esc_html_e( 'Great news! Your booking has been confirmed.', 'peanut-booker' ); ?></p>

                <div style="background-color: #d4edda; padding: 20px; border-radius: 4px; margin: 20px 0;">
                    <p style="margin: 5px 0;"><strong><?php esc_html_e( 'Booking #:', 'peanut-booker' ); ?></strong> <?php echo esc_html( $data['booking_number'] ); ?></p>
                    <p style="margin: 5px 0;"><strong><?php esc_html_e( 'Event:', 'peanut-booker' ); ?></strong> <?php echo esc_html( $data['event_title'] ); ?></p>
                    <p style="margin: 5px 0;"><strong><?php esc_html_e( 'Date:', 'peanut-booker' ); ?></strong> <?php echo esc_html( $data['event_date'] ); ?></p>
                    <p style="margin: 5px 0;"><strong><?php esc_html_e( 'Performer:', 'peanut-booker' ); ?></strong> <?php echo esc_html( $data['performer_name'] ); ?></p>
                </div>

                <p>
                    <a href="<?php echo esc_url( $data['dashboard_url'] ); ?>" style="display: inline-block; background-color: #6366f1; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px;">
                        <?php esc_html_e( 'View Booking Details', 'peanut-booker' ); ?>
                    </a>
                </p>
                <?php
                break;

            case 'new_bid':
                ?>
                <h2 style="color: #333; margin-top: 0;"><?php esc_html_e( 'New Bid Received!', 'peanut-booker' ); ?></h2>
                <p><?php esc_html_e( 'A performer has submitted a bid for your event.', 'peanut-booker' ); ?></p>

                <div style="background-color: #f8f9fa; padding: 20px; border-radius: 4px; margin: 20px 0;">
                    <p style="margin: 5px 0;"><strong><?php esc_html_e( 'Event:', 'peanut-booker' ); ?></strong> <?php echo esc_html( $data['event_title'] ); ?></p>
                    <p style="margin: 5px 0;"><strong><?php esc_html_e( 'Performer:', 'peanut-booker' ); ?></strong> <?php echo esc_html( $data['performer_name'] ); ?></p>
                    <p style="margin: 5px 0;"><strong><?php esc_html_e( 'Bid Amount:', 'peanut-booker' ); ?></strong> <?php echo wp_kses_post( $data['bid_amount'] ); ?></p>
                </div>

                <p>
                    <a href="<?php echo esc_url( $data['dashboard_url'] ?? home_url( '/dashboard/' ) ); ?>" style="display: inline-block; background-color: #6366f1; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px;">
                        <?php esc_html_e( 'Review Bids', 'peanut-booker' ); ?>
                    </a>
                </p>
                <?php
                break;

            case 'bid_accepted':
                ?>
                <h2 style="color: #28a745; margin-top: 0;"><?php esc_html_e( 'Congratulations! Your Bid Was Accepted!', 'peanut-booker' ); ?></h2>
                <p><?php esc_html_e( 'Great news! The customer has accepted your bid.', 'peanut-booker' ); ?></p>

                <div style="background-color: #d4edda; padding: 20px; border-radius: 4px; margin: 20px 0;">
                    <p style="margin: 5px 0;"><strong><?php esc_html_e( 'Event:', 'peanut-booker' ); ?></strong> <?php echo esc_html( $data['event_title'] ); ?></p>
                    <p style="margin: 5px 0;"><strong><?php esc_html_e( 'Date:', 'peanut-booker' ); ?></strong> <?php echo esc_html( $data['event_date'] ); ?></p>
                    <p style="margin: 5px 0;"><strong><?php esc_html_e( 'Amount:', 'peanut-booker' ); ?></strong> <?php echo wp_kses_post( $data['bid_amount'] ); ?></p>
                </div>

                <p><?php esc_html_e( 'A booking has been created. Please check your dashboard for details.', 'peanut-booker' ); ?></p>
                <?php
                break;

            case 'new_review':
                ?>
                <h2 style="color: #333; margin-top: 0;"><?php esc_html_e( 'New Review Received', 'peanut-booker' ); ?></h2>
                <p><?php echo esc_html( $data['reviewer_name'] ); ?> <?php esc_html_e( 'has left you a review.', 'peanut-booker' ); ?></p>

                <div style="background-color: #f8f9fa; padding: 20px; border-radius: 4px; margin: 20px 0;">
                    <p style="margin: 5px 0;"><strong><?php esc_html_e( 'Rating:', 'peanut-booker' ); ?></strong> <?php echo str_repeat( '★', $data['rating'] ) . str_repeat( '☆', 5 - $data['rating'] ); ?></p>
                    <?php if ( ! empty( $data['review_content'] ) ) : ?>
                        <p style="margin: 10px 0 0;">"<?php echo esc_html( $data['review_content'] ); ?>"</p>
                    <?php endif; ?>
                </div>

                <p><?php esc_html_e( 'You can respond to this review from your dashboard.', 'peanut-booker' ); ?></p>
                <?php
                break;

            default:
                ?>
                <p><?php esc_html_e( 'You have a new notification.', 'peanut-booker' ); ?></p>
                <p>
                    <a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" style="display: inline-block; background-color: #6366f1; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px;">
                        <?php esc_html_e( 'View Dashboard', 'peanut-booker' ); ?>
                    </a>
                </p>
                <?php
                break;
        }

        return ob_get_clean();
    }

    /**
     * Get email headers.
     *
     * @return array Headers.
     */
    private static function get_headers() {
        $options      = get_option( 'peanut_booker_settings', array() );
        $from_name    = $options['email_from_name'] ?? get_bloginfo( 'name' );
        $from_address = $options['email_from_address'] ?? get_bloginfo( 'admin_email' );

        return array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_address . '>',
        );
    }
}
