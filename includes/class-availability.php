<?php
/**
 * Availability calendar functionality.
 *
 * @package Peanut_Booker
 * @since   1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Availability calendar class.
 */
class Peanut_Booker_Availability {

    /**
     * Slot types.
     */
    const SLOT_FULL_DAY = 'full_day';
    const SLOT_MORNING  = 'morning';
    const SLOT_AFTERNOON = 'afternoon';
    const SLOT_EVENING  = 'evening';
    const SLOT_CUSTOM   = 'custom';

    /**
     * Status types.
     */
    const STATUS_AVAILABLE    = 'available';
    const STATUS_BOOKED       = 'booked';
    const STATUS_BLOCKED      = 'blocked';
    const STATUS_EXTERNAL_GIG = 'external_gig';

    /**
     * Block types.
     */
    const BLOCK_TYPE_MANUAL       = 'manual';
    const BLOCK_TYPE_BOOKING      = 'booking';
    const BLOCK_TYPE_EXTERNAL_GIG = 'external_gig';
    const BLOCK_TYPE_VACATION     = 'vacation';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'wp_ajax_pb_get_availability', array( $this, 'ajax_get_availability' ) );
        add_action( 'wp_ajax_nopriv_pb_get_availability', array( $this, 'ajax_get_availability' ) );
        add_action( 'wp_ajax_pb_update_availability', array( $this, 'ajax_update_availability' ) );
        add_action( 'wp_ajax_pb_block_dates', array( $this, 'ajax_block_dates' ) );
        add_action( 'wp_ajax_pb_unblock_dates', array( $this, 'ajax_unblock_dates' ) );
        add_action( 'wp_ajax_pb_block_external_gig', array( $this, 'ajax_block_external_gig' ) );
    }

    /**
     * Get performer availability for a date range.
     *
     * @param int    $performer_id Performer ID.
     * @param string $start_date   Start date (Y-m-d).
     * @param string $end_date     End date (Y-m-d).
     * @return array Array of availability slots.
     */
    public static function get( $performer_id, $start_date, $end_date ) {
        global $wpdb;

        $table = $wpdb->prefix . 'pb_availability';

        $slots = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table
                WHERE performer_id = %d
                AND date >= %s
                AND date <= %s
                ORDER BY date ASC, start_time ASC",
                $performer_id,
                $start_date,
                $end_date
            )
        );

        $availability = array();

        foreach ( $slots as $slot ) {
            $date = $slot->date;

            if ( ! isset( $availability[ $date ] ) ) {
                $availability[ $date ] = array(
                    'date'   => $date,
                    'status' => self::STATUS_AVAILABLE,
                    'slots'  => array(),
                );
            }

            $availability[ $date ]['slots'][] = array(
                'id'             => $slot->id,
                'slot_type'      => $slot->slot_type,
                'start_time'     => $slot->start_time,
                'end_time'       => $slot->end_time,
                'status'         => $slot->status,
                'booking_id'     => $slot->booking_id,
                'block_type'     => $slot->block_type ?? self::BLOCK_TYPE_MANUAL,
                'event_name'     => $slot->event_name ?? '',
                'venue_name'     => $slot->venue_name ?? '',
                'event_type'     => $slot->event_type ?? '',
                'event_location' => $slot->event_location ?? '',
                'notes'          => $slot->notes,
            );

            // If any slot is booked/blocked, mark the day accordingly.
            if ( $slot->status !== self::STATUS_AVAILABLE ) {
                if ( self::SLOT_FULL_DAY === $slot->slot_type ) {
                    $availability[ $date ]['status'] = $slot->status;
                }
            }
        }

        return $availability;
    }

    /**
     * Get availability for calendar display.
     *
     * @param int    $performer_id Performer ID.
     * @param string $month        Month (Y-m format).
     * @return array Calendar data with status colors.
     */
    public static function get_calendar_data( $performer_id, $month ) {
        $start_date = $month . '-01';
        $end_date   = gmdate( 'Y-m-t', strtotime( $start_date ) );

        $availability = self::get( $performer_id, $start_date, $end_date );
        $calendar     = array();

        // Generate all dates in month.
        $current = strtotime( $start_date );
        $end     = strtotime( $end_date );

        while ( $current <= $end ) {
            $date = gmdate( 'Y-m-d', $current );

            if ( isset( $availability[ $date ] ) ) {
                $day_data   = $availability[ $date ];
                $block_type = null;

                // Get block_type from first slot if available.
                if ( ! empty( $day_data['slots'] ) ) {
                    $block_type = $day_data['slots'][0]['block_type'] ?? null;
                }

                $calendar[ $date ] = array(
                    'date'       => $date,
                    'status'     => $day_data['status'],
                    'block_type' => $block_type,
                    'color'      => self::get_status_color( $day_data['status'], $block_type ),
                    'slots'      => $day_data['slots'],
                );
            } else {
                // No record = available.
                $calendar[ $date ] = array(
                    'date'       => $date,
                    'status'     => self::STATUS_AVAILABLE,
                    'block_type' => null,
                    'color'      => self::get_status_color( self::STATUS_AVAILABLE ),
                    'slots'      => array(),
                );
            }

            // Past dates are always unavailable.
            if ( strtotime( $date ) < strtotime( 'today' ) ) {
                $calendar[ $date ]['status'] = 'past';
                $calendar[ $date ]['color']  = '#e0e0e0';
            }

            $current = strtotime( '+1 day', $current );
        }

        return $calendar;
    }

    /**
     * Get status color.
     *
     * @param string $status     Status.
     * @param string $block_type Optional block type for more specific coloring.
     * @return string Hex color.
     */
    public static function get_status_color( $status, $block_type = null ) {
        // External gig has its own color regardless of status.
        if ( $block_type === self::BLOCK_TYPE_EXTERNAL_GIG || $status === self::STATUS_EXTERNAL_GIG ) {
            return '#9333ea'; // Purple.
        }

        $colors = array(
            self::STATUS_AVAILABLE => '#4CAF50', // Green.
            self::STATUS_BOOKED    => '#f44336', // Red.
            self::STATUS_BLOCKED   => '#ff9800', // Orange.
            'past'                 => '#e0e0e0', // Gray.
        );

        return $colors[ $status ] ?? '#e0e0e0';
    }

    /**
     * Check if performer is available on a date.
     *
     * @param int    $performer_id Performer ID.
     * @param string $date         Date to check.
     * @param string $start_time   Optional start time.
     * @param string $end_time     Optional end time.
     * @return bool
     */
    public static function is_available( $performer_id, $date, $start_time = null, $end_time = null ) {
        // Past dates are never available.
        if ( strtotime( $date ) < strtotime( 'today' ) ) {
            return false;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'pb_availability';

        // Check for full day blocks/bookings.
        $full_day_block = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table
                WHERE performer_id = %d
                AND date = %s
                AND slot_type = 'full_day'
                AND status != %s",
                $performer_id,
                $date,
                self::STATUS_AVAILABLE
            )
        );

        if ( $full_day_block > 0 ) {
            return false;
        }

        // If checking specific time slot.
        if ( $start_time && $end_time ) {
            $time_conflict = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $table
                    WHERE performer_id = %d
                    AND date = %s
                    AND status != %s
                    AND (
                        (start_time <= %s AND end_time > %s)
                        OR (start_time < %s AND end_time >= %s)
                        OR (start_time >= %s AND end_time <= %s)
                    )",
                    $performer_id,
                    $date,
                    self::STATUS_AVAILABLE,
                    $start_time,
                    $start_time,
                    $end_time,
                    $end_time,
                    $start_time,
                    $end_time
                )
            );

            return $time_conflict == 0;
        }

        return true;
    }

    /**
     * Block a date (for booking).
     *
     * @param int    $performer_id Performer ID.
     * @param string $date         Date to block.
     * @param int    $booking_id   Optional booking ID.
     * @param string $start_time   Optional start time.
     * @param string $end_time     Optional end time.
     * @return int|false Slot ID or false.
     */
    public static function block_date( $performer_id, $date, $booking_id = null, $start_time = null, $end_time = null ) {
        $slot_type = self::SLOT_FULL_DAY;

        if ( $start_time && $end_time ) {
            $slot_type = self::SLOT_CUSTOM;
        }

        return Peanut_Booker_Database::insert(
            'availability',
            array(
                'performer_id' => $performer_id,
                'date'         => $date,
                'slot_type'    => $slot_type,
                'start_time'   => $start_time,
                'end_time'     => $end_time,
                'status'       => $booking_id ? self::STATUS_BOOKED : self::STATUS_BLOCKED,
                'booking_id'   => $booking_id,
            )
        );
    }

    /**
     * Unblock a date.
     *
     * @param int    $performer_id Performer ID.
     * @param string $date         Date to unblock.
     * @param int    $booking_id   Optional booking ID to match.
     * @return bool
     */
    public static function unblock_date( $performer_id, $date, $booking_id = null ) {
        $where = array(
            'performer_id' => $performer_id,
            'date'         => $date,
        );

        if ( $booking_id ) {
            $where['booking_id'] = $booking_id;
        }

        return Peanut_Booker_Database::delete( 'availability', $where ) !== false;
    }

    /**
     * Block multiple dates.
     *
     * @param int    $performer_id Performer ID.
     * @param array  $dates        Array of dates to block.
     * @param string $notes        Optional notes.
     * @return int Number of dates blocked.
     */
    public static function block_dates( $performer_id, $dates, $notes = '' ) {
        $blocked = 0;

        foreach ( $dates as $date ) {
            // Skip if already blocked.
            if ( ! self::is_available( $performer_id, $date ) ) {
                continue;
            }

            $result = Peanut_Booker_Database::insert(
                'availability',
                array(
                    'performer_id' => $performer_id,
                    'date'         => $date,
                    'slot_type'    => self::SLOT_FULL_DAY,
                    'status'       => self::STATUS_BLOCKED,
                    'notes'        => $notes,
                )
            );

            if ( $result ) {
                $blocked++;
            }
        }

        return $blocked;
    }

    /**
     * Unblock multiple dates.
     *
     * @param int   $performer_id Performer ID.
     * @param array $dates        Array of dates to unblock.
     * @return int Number of dates unblocked.
     */
    public static function unblock_dates( $performer_id, $dates ) {
        $unblocked = 0;

        foreach ( $dates as $date ) {
            $result = Peanut_Booker_Database::delete(
                'availability',
                array(
                    'performer_id' => $performer_id,
                    'date'         => $date,
                    'status'       => self::STATUS_BLOCKED,
                )
            );

            if ( $result ) {
                $unblocked++;
            }
        }

        return $unblocked;
    }

    /**
     * Get next available dates for a performer.
     *
     * @param int $performer_id Performer ID.
     * @param int $count        Number of dates to return.
     * @return array Array of available dates.
     */
    public static function get_next_available( $performer_id, $count = 5 ) {
        $dates   = array();
        $current = strtotime( 'tomorrow' );
        $checked = 0;
        $max_check = 90; // Check up to 90 days.

        while ( count( $dates ) < $count && $checked < $max_check ) {
            $date = gmdate( 'Y-m-d', $current );

            if ( self::is_available( $performer_id, $date ) ) {
                $dates[] = array(
                    'date'      => $date,
                    'formatted' => date_i18n( get_option( 'date_format' ), $current ),
                    'day_name'  => date_i18n( 'l', $current ),
                );
            }

            $current = strtotime( '+1 day', $current );
            $checked++;
        }

        return $dates;
    }

    /**
     * Get performer's booked dates.
     *
     * @param int    $performer_id Performer ID.
     * @param string $start_date   Start date.
     * @param string $end_date     End date.
     * @return array Array of booked dates with booking info.
     */
    public static function get_booked_dates( $performer_id, $start_date, $end_date ) {
        global $wpdb;

        $table = $wpdb->prefix . 'pb_availability';
        $bookings_table = $wpdb->prefix . 'pb_bookings';

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT a.*, b.booking_number, b.event_title, b.customer_id
                FROM $table a
                LEFT JOIN $bookings_table b ON a.booking_id = b.id
                WHERE a.performer_id = %d
                AND a.date >= %s
                AND a.date <= %s
                AND a.status = %s
                ORDER BY a.date ASC",
                $performer_id,
                $start_date,
                $end_date,
                self::STATUS_BOOKED
            )
        );

        $booked = array();
        foreach ( $results as $row ) {
            $customer = $row->customer_id ? Peanut_Booker_Customer::get( $row->customer_id ) : null;

            $booked[] = array(
                'date'           => $row->date,
                'booking_id'     => $row->booking_id,
                'booking_number' => $row->booking_number,
                'event_title'    => $row->event_title,
                'customer_name'  => $customer ? $customer['display_name'] : '',
                'start_time'     => $row->start_time,
                'end_time'       => $row->end_time,
            );
        }

        return $booked;
    }

    /**
     * Render calendar HTML.
     *
     * @param int    $performer_id Performer ID.
     * @param string $month        Month (Y-m format).
     * @param bool   $editable     Whether calendar is editable.
     * @return string HTML.
     */
    public static function render_calendar( $performer_id, $month = null, $editable = false ) {
        if ( ! $month ) {
            $month = gmdate( 'Y-m' );
        }

        $calendar_data = self::get_calendar_data( $performer_id, $month );
        $first_day     = strtotime( $month . '-01' );
        $month_name    = date_i18n( 'F Y', $first_day );
        $start_weekday = (int) gmdate( 'w', $first_day );
        $total_days    = (int) gmdate( 't', $first_day );

        $prev_month = gmdate( 'Y-m', strtotime( '-1 month', $first_day ) );
        $next_month = gmdate( 'Y-m', strtotime( '+1 month', $first_day ) );

        ob_start();
        ?>
        <div class="pb-calendar" data-performer="<?php echo esc_attr( $performer_id ); ?>" data-month="<?php echo esc_attr( $month ); ?>" data-editable="<?php echo $editable ? '1' : '0'; ?>">
            <div class="pb-calendar-header">
                <button type="button" class="pb-calendar-nav pb-prev" data-month="<?php echo esc_attr( $prev_month ); ?>">
                    &laquo; <?php esc_html_e( 'Prev', 'peanut-booker' ); ?>
                </button>
                <h3 class="pb-calendar-title"><?php echo esc_html( $month_name ); ?></h3>
                <button type="button" class="pb-calendar-nav pb-next" data-month="<?php echo esc_attr( $next_month ); ?>">
                    <?php esc_html_e( 'Next', 'peanut-booker' ); ?> &raquo;
                </button>
            </div>

            <div class="pb-calendar-legend">
                <span class="pb-legend-item"><span class="pb-legend-color" style="background: <?php echo esc_attr( self::get_status_color( 'available' ) ); ?>"></span> <?php esc_html_e( 'Available', 'peanut-booker' ); ?></span>
                <span class="pb-legend-item"><span class="pb-legend-color" style="background: <?php echo esc_attr( self::get_status_color( 'booked' ) ); ?>"></span> <?php esc_html_e( 'Booked', 'peanut-booker' ); ?></span>
                <span class="pb-legend-item"><span class="pb-legend-color" style="background: <?php echo esc_attr( self::get_status_color( 'blocked' ) ); ?>"></span> <?php esc_html_e( 'Blocked', 'peanut-booker' ); ?></span>
                <span class="pb-legend-item"><span class="pb-legend-color" style="background: <?php echo esc_attr( self::get_status_color( null, self::BLOCK_TYPE_EXTERNAL_GIG ) ); ?>"></span> <?php esc_html_e( 'External Gig', 'peanut-booker' ); ?></span>
            </div>

            <table class="pb-calendar-grid">
                <thead>
                    <tr>
                        <?php
                        $weekdays = array(
                            __( 'Sun', 'peanut-booker' ),
                            __( 'Mon', 'peanut-booker' ),
                            __( 'Tue', 'peanut-booker' ),
                            __( 'Wed', 'peanut-booker' ),
                            __( 'Thu', 'peanut-booker' ),
                            __( 'Fri', 'peanut-booker' ),
                            __( 'Sat', 'peanut-booker' ),
                        );
                        foreach ( $weekdays as $day ) {
                            echo '<th>' . esc_html( $day ) . '</th>';
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $day_count = 1;
                    $rows      = ceil( ( $start_weekday + $total_days ) / 7 );

                    for ( $row = 0; $row < $rows; $row++ ) {
                        echo '<tr>';
                        for ( $col = 0; $col < 7; $col++ ) {
                            $cell_index = $row * 7 + $col;

                            if ( $cell_index < $start_weekday || $day_count > $total_days ) {
                                echo '<td class="pb-calendar-empty"></td>';
                            } else {
                                $date     = sprintf( '%s-%02d', $month, $day_count );
                                $day_data = $calendar_data[ $date ] ?? array();
                                $status   = $day_data['status'] ?? 'available';
                                $color    = $day_data['color'] ?? '#4CAF50';

                                $classes = array( 'pb-calendar-day', 'pb-status-' . $status );
                                if ( $editable && $status !== 'past' && $status !== 'booked' ) {
                                    $classes[] = 'pb-clickable';
                                }

                                printf(
                                    '<td class="%s" data-date="%s" style="background-color: %s;">
                                        <span class="pb-day-number">%d</span>
                                    </td>',
                                    esc_attr( implode( ' ', $classes ) ),
                                    esc_attr( $date ),
                                    esc_attr( $color ),
                                    $day_count
                                );

                                $day_count++;
                            }
                        }
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX: Get availability.
     */
    public function ajax_get_availability() {
        $performer_id = absint( $_GET['performer_id'] ?? 0 );
        $month        = sanitize_text_field( $_GET['month'] ?? gmdate( 'Y-m' ) );

        if ( ! $performer_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid performer.', 'peanut-booker' ) ) );
        }

        $calendar = self::get_calendar_data( $performer_id, $month );

        wp_send_json_success(
            array(
                'calendar' => $calendar,
                'html'     => self::render_calendar( $performer_id, $month, false ),
            )
        );
    }

    /**
     * AJAX: Update availability (performer only).
     */
    public function ajax_update_availability() {
        check_ajax_referer( 'pb_availability_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'peanut-booker' ) ) );
        }

        $performer = Peanut_Booker_Performer::get_by_user_id( get_current_user_id() );
        if ( ! $performer ) {
            wp_send_json_error( array( 'message' => __( 'Performer not found.', 'peanut-booker' ) ) );
        }

        $month = sanitize_text_field( $_POST['month'] ?? gmdate( 'Y-m' ) );

        wp_send_json_success(
            array(
                'html' => self::render_calendar( $performer->id, $month, true ),
            )
        );
    }

    /**
     * AJAX: Block dates.
     */
    public function ajax_block_dates() {
        check_ajax_referer( 'pb_availability_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'peanut-booker' ) ) );
        }

        $performer = Peanut_Booker_Performer::get_by_user_id( get_current_user_id() );
        if ( ! $performer ) {
            wp_send_json_error( array( 'message' => __( 'Performer not found.', 'peanut-booker' ) ) );
        }

        $dates = isset( $_POST['dates'] ) ? array_map( 'sanitize_text_field', (array) $_POST['dates'] ) : array();
        $notes = sanitize_text_field( $_POST['notes'] ?? '' );

        $blocked = self::block_dates( $performer->id, $dates, $notes );

        wp_send_json_success(
            array(
                'message' => sprintf(
                    _n( '%d date blocked.', '%d dates blocked.', $blocked, 'peanut-booker' ),
                    $blocked
                ),
            )
        );
    }

    /**
     * AJAX: Unblock dates.
     */
    public function ajax_unblock_dates() {
        check_ajax_referer( 'pb_availability_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'peanut-booker' ) ) );
        }

        $performer = Peanut_Booker_Performer::get_by_user_id( get_current_user_id() );
        if ( ! $performer ) {
            wp_send_json_error( array( 'message' => __( 'Performer not found.', 'peanut-booker' ) ) );
        }

        $dates = isset( $_POST['dates'] ) ? array_map( 'sanitize_text_field', (array) $_POST['dates'] ) : array();

        $unblocked = self::unblock_dates( $performer->id, $dates );

        wp_send_json_success(
            array(
                'message' => sprintf(
                    _n( '%d date unblocked.', '%d dates unblocked.', $unblocked, 'peanut-booker' ),
                    $unblocked
                ),
            )
        );
    }

    /**
     * Block dates as external gig.
     *
     * @param int    $performer_id   Performer ID.
     * @param array  $dates          Array of dates to block.
     * @param string $event_name     Event name.
     * @param string $venue_name     Venue name.
     * @param string $event_type     Event type.
     * @param string $event_location Event location.
     * @param string $notes          Optional notes.
     * @return int Number of dates blocked.
     */
    public static function block_external_gig( $performer_id, $dates, $event_name = '', $venue_name = '', $event_type = '', $event_location = '', $notes = '' ) {
        $blocked = 0;

        foreach ( $dates as $date ) {
            // Skip if already blocked/booked.
            if ( ! self::is_available( $performer_id, $date ) ) {
                continue;
            }

            $result = Peanut_Booker_Database::insert(
                'availability',
                array(
                    'performer_id'   => $performer_id,
                    'date'           => $date,
                    'slot_type'      => self::SLOT_FULL_DAY,
                    'status'         => self::STATUS_BLOCKED,
                    'block_type'     => self::BLOCK_TYPE_EXTERNAL_GIG,
                    'event_name'     => $event_name,
                    'venue_name'     => $venue_name,
                    'event_type'     => $event_type,
                    'event_location' => $event_location,
                    'notes'          => $notes,
                )
            );

            if ( $result ) {
                $blocked++;
            }
        }

        return $blocked;
    }

    /**
     * Get external gigs for a performer.
     *
     * @param int    $performer_id Performer ID.
     * @param string $start_date   Start date.
     * @param string $end_date     End date.
     * @return array Array of external gigs.
     */
    public static function get_external_gigs( $performer_id, $start_date, $end_date ) {
        global $wpdb;

        $table = $wpdb->prefix . 'pb_availability';

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table
                WHERE performer_id = %d
                AND date >= %s
                AND date <= %s
                AND block_type = %s
                ORDER BY date ASC",
                $performer_id,
                $start_date,
                $end_date,
                self::BLOCK_TYPE_EXTERNAL_GIG
            )
        );

        $gigs = array();
        foreach ( $results as $row ) {
            $gigs[] = array(
                'id'             => $row->id,
                'date'           => $row->date,
                'event_name'     => $row->event_name,
                'venue_name'     => $row->venue_name,
                'event_type'     => $row->event_type,
                'event_location' => $row->event_location,
                'notes'          => $row->notes,
            );
        }

        return $gigs;
    }

    /**
     * AJAX: Block external gig.
     */
    public function ajax_block_external_gig() {
        check_ajax_referer( 'pb_availability_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'peanut-booker' ) ) );
        }

        $performer = Peanut_Booker_Performer::get_by_user_id( get_current_user_id() );
        if ( ! $performer ) {
            wp_send_json_error( array( 'message' => __( 'Performer not found.', 'peanut-booker' ) ) );
        }

        $dates          = isset( $_POST['dates'] ) ? array_map( 'sanitize_text_field', (array) $_POST['dates'] ) : array();
        $event_name     = sanitize_text_field( $_POST['event_name'] ?? '' );
        $venue_name     = sanitize_text_field( $_POST['venue_name'] ?? '' );
        $event_type     = sanitize_text_field( $_POST['event_type'] ?? '' );
        $event_location = sanitize_text_field( $_POST['event_location'] ?? '' );
        $notes          = sanitize_textarea_field( $_POST['notes'] ?? '' );

        if ( empty( $dates ) ) {
            wp_send_json_error( array( 'message' => __( 'Please select at least one date.', 'peanut-booker' ) ) );
        }

        $blocked = self::block_external_gig(
            $performer->id,
            $dates,
            $event_name,
            $venue_name,
            $event_type,
            $event_location,
            $notes
        );

        wp_send_json_success(
            array(
                'message' => sprintf(
                    _n( 'External gig added for %d date.', 'External gig added for %d dates.', $blocked, 'peanut-booker' ),
                    $blocked
                ),
                'blocked' => $blocked,
            )
        );
    }
}
