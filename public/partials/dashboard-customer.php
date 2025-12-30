<?php
/**
 * Customer dashboard template.
 *
 * @package Peanut_Booker
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

$user_id    = get_current_user_id();
$customer   = Peanut_Booker_Customer::get_by_user_id( $user_id );
$bookings   = Peanut_Booker_Booking::get_customer_bookings( $user_id, 10 );
$events     = Peanut_Booker_Database::get_results( 'events', array( 'customer_user_id' => $user_id ), 'created_at', 'DESC', 10 );
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'overview';
?>

<div class="pb-dashboard pb-customer-dashboard">
    <div class="pb-dashboard-header">
        <div class="pb-dashboard-welcome">
            <h1><?php printf( esc_html__( 'Welcome, %s!', 'peanut-booker' ), esc_html( wp_get_current_user()->display_name ) ); ?></h1>
        </div>
        <a href="<?php echo esc_url( get_post_type_archive_link( 'pb_performer' ) ); ?>" class="pb-button pb-button-primary">
            <?php esc_html_e( 'Find Performers', 'peanut-booker' ); ?>
        </a>
    </div>

    <div class="pb-dashboard-tabs">
        <a href="?tab=overview" class="<?php echo 'overview' === $active_tab ? 'active' : ''; ?>">
            <?php esc_html_e( 'Overview', 'peanut-booker' ); ?>
        </a>
        <a href="?tab=bookings" class="<?php echo 'bookings' === $active_tab ? 'active' : ''; ?>">
            <?php esc_html_e( 'My Bookings', 'peanut-booker' ); ?>
        </a>
        <a href="?tab=events" class="<?php echo 'events' === $active_tab ? 'active' : ''; ?>">
            <?php esc_html_e( 'Posted Events', 'peanut-booker' ); ?>
        </a>
        <a href="?tab=reviews" class="<?php echo 'reviews' === $active_tab ? 'active' : ''; ?>">
            <?php esc_html_e( 'Reviews', 'peanut-booker' ); ?>
        </a>
        <a href="?tab=profile" class="<?php echo 'profile' === $active_tab ? 'active' : ''; ?>">
            <?php esc_html_e( 'Profile', 'peanut-booker' ); ?>
        </a>
    </div>

    <div class="pb-dashboard-content">
        <?php if ( 'overview' === $active_tab ) : ?>
            <!-- Overview Tab -->
            <div class="pb-stats-grid">
                <div class="pb-stat-card">
                    <h3><?php esc_html_e( 'Total Bookings', 'peanut-booker' ); ?></h3>
                    <div class="pb-stat-value"><?php echo esc_html( count( $bookings ) ); ?></div>
                </div>
                <div class="pb-stat-card">
                    <h3><?php esc_html_e( 'Posted Events', 'peanut-booker' ); ?></h3>
                    <div class="pb-stat-value"><?php echo esc_html( count( $events ) ); ?></div>
                </div>
                <div class="pb-stat-card">
                    <h3><?php esc_html_e( 'Your Rating', 'peanut-booker' ); ?></h3>
                    <div class="pb-stat-value">
                        <?php echo $customer && $customer->average_rating ? esc_html( number_format( $customer->average_rating, 1 ) ) . ' ★' : '—'; ?>
                    </div>
                </div>
            </div>

            <!-- Upcoming Events -->
            <div class="pb-section">
                <h2><?php esc_html_e( 'Upcoming Events', 'peanut-booker' ); ?></h2>
                <?php
                $upcoming = array_filter( $bookings, function( $b ) {
                    return in_array( $b->booking_status, array( 'pending', 'confirmed' ), true ) && strtotime( $b->event_date ) >= strtotime( 'today' );
                } );

                if ( empty( $upcoming ) ) :
                ?>
                    <p class="pb-empty"><?php esc_html_e( 'No upcoming events.', 'peanut-booker' ); ?></p>
                    <a href="<?php echo esc_url( get_post_type_archive_link( 'pb_performer' ) ); ?>" class="pb-button pb-button-primary">
                        <?php esc_html_e( 'Book a Performer', 'peanut-booker' ); ?>
                    </a>
                <?php else : ?>
                    <div class="pb-booking-cards">
                        <?php foreach ( array_slice( $upcoming, 0, 3 ) as $booking ) : ?>
                            <?php
                            $performer_data = Peanut_Booker_Database::get_row( 'performers', array( 'user_id' => $booking->performer_user_id ) );
                            $performer_user = get_userdata( $booking->performer_user_id );
                            ?>
                            <div class="pb-booking-card">
                                <div class="pb-booking-date">
                                    <span class="pb-date-day"><?php echo esc_html( date_i18n( 'j', strtotime( $booking->event_date ) ) ); ?></span>
                                    <span class="pb-date-month"><?php echo esc_html( date_i18n( 'M', strtotime( $booking->event_date ) ) ); ?></span>
                                </div>
                                <div class="pb-booking-info">
                                    <h4>
                                        <?php if ( $performer_data && $performer_data->profile_id ) : ?>
                                            <a href="<?php echo esc_url( get_permalink( $performer_data->profile_id ) ); ?>">
                                                <?php echo $performer_user ? esc_html( $performer_user->display_name ) : esc_html__( 'Performer', 'peanut-booker' ); ?>
                                            </a>
                                        <?php else : ?>
                                            <?php echo $performer_user ? esc_html( $performer_user->display_name ) : esc_html__( 'Performer', 'peanut-booker' ); ?>
                                        <?php endif; ?>
                                    </h4>
                                    <p><?php echo esc_html( $booking->event_location ?: __( 'Location TBD', 'peanut-booker' ) ); ?></p>
                                    <span class="pb-status pb-status-<?php echo esc_attr( $booking->booking_status ); ?>">
                                        <?php echo esc_html( ucfirst( $booking->booking_status ) ); ?>
                                    </span>
                                </div>
                                <div class="pb-booking-amount">
                                    <?php echo wc_price( $booking->total_amount ); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Market CTAs -->
            <div class="pb-cta-section">
                <div class="pb-cta-card">
                    <h3><?php esc_html_e( 'Need a Performer?', 'peanut-booker' ); ?></h3>
                    <p><?php esc_html_e( 'Post your event to the Market and let performers bid for the opportunity.', 'peanut-booker' ); ?></p>
                    <a href="?tab=events&action=new" class="pb-button pb-button-primary">
                        <?php esc_html_e( 'Post an Event', 'peanut-booker' ); ?>
                    </a>
                </div>
            </div>

        <?php elseif ( 'bookings' === $active_tab ) : ?>
            <!-- Bookings Tab -->
            <div class="pb-section">
                <h2><?php esc_html_e( 'My Bookings', 'peanut-booker' ); ?></h2>
                <?php if ( empty( $bookings ) ) : ?>
                    <p class="pb-empty"><?php esc_html_e( 'No bookings yet.', 'peanut-booker' ); ?></p>
                <?php else : ?>
                    <table class="pb-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Date', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Performer', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Location', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Amount', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Status', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Actions', 'peanut-booker' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $bookings as $booking ) : ?>
                                <?php $performer_user = get_userdata( $booking->performer_user_id ); ?>
                                <tr>
                                    <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->event_date ) ) ); ?></td>
                                    <td><?php echo $performer_user ? esc_html( $performer_user->display_name ) : '—'; ?></td>
                                    <td><?php echo esc_html( $booking->event_location ?: '—' ); ?></td>
                                    <td><?php echo wc_price( $booking->total_amount ); ?></td>
                                    <td>
                                        <span class="pb-status pb-status-<?php echo esc_attr( $booking->booking_status ); ?>">
                                            <?php echo esc_html( ucfirst( $booking->booking_status ) ); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ( 'confirmed' === $booking->booking_status && strtotime( $booking->event_date ) < time() ) : ?>
                                            <button class="pb-button pb-button-small pb-complete-booking" data-booking-id="<?php echo esc_attr( $booking->id ); ?>">
                                                <?php esc_html_e( 'Mark Complete', 'peanut-booker' ); ?>
                                            </button>
                                        <?php elseif ( 'pending' === $booking->booking_status ) : ?>
                                            <button class="pb-button pb-button-small pb-button-secondary pb-cancel-booking" data-booking-id="<?php echo esc_attr( $booking->id ); ?>">
                                                <?php esc_html_e( 'Cancel', 'peanut-booker' ); ?>
                                            </button>
                                        <?php elseif ( 'completed' === $booking->booking_status && ! Peanut_Booker_Reviews::customer_has_reviewed( $user_id, $booking->id ) ) : ?>
                                            <a href="#review-form" class="pb-button pb-button-small pb-leave-review" data-booking-id="<?php echo esc_attr( $booking->id ); ?>" data-performer-id="<?php echo esc_attr( $booking->performer_user_id ); ?>">
                                                <?php esc_html_e( 'Leave Review', 'peanut-booker' ); ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        <?php elseif ( 'events' === $active_tab ) : ?>
            <!-- Posted Events Tab -->
            <div class="pb-section">
                <div class="pb-section-header">
                    <h2><?php esc_html_e( 'My Posted Events', 'peanut-booker' ); ?></h2>
                    <a href="?tab=events&action=new" class="pb-button pb-button-primary">
                        <?php esc_html_e( 'Post New Event', 'peanut-booker' ); ?>
                    </a>
                </div>

                <?php if ( isset( $_GET['action'] ) && 'new' === $_GET['action'] ) : ?>
                    <!-- New Event Form -->
                    <div class="pb-form-section">
                        <h3><?php esc_html_e( 'Post a New Event', 'peanut-booker' ); ?></h3>
                        <form class="pb-event-form" method="post">
                            <?php wp_nonce_field( 'pb_create_event', 'pb_event_nonce' ); ?>

                            <div class="pb-form-row">
                                <label for="event_name"><?php esc_html_e( 'Event Name', 'peanut-booker' ); ?> *</label>
                                <input type="text" id="event_name" name="event_name" required>
                            </div>

                            <div class="pb-form-row">
                                <label for="event_description"><?php esc_html_e( 'Description', 'peanut-booker' ); ?> *</label>
                                <textarea id="event_description" name="event_description" rows="4" required></textarea>
                            </div>

                            <div class="pb-form-grid">
                                <div class="pb-form-row">
                                    <label for="event_date"><?php esc_html_e( 'Event Date', 'peanut-booker' ); ?> *</label>
                                    <input type="date" id="event_date" name="event_date" required min="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>">
                                </div>
                                <div class="pb-form-row">
                                    <label for="event_time"><?php esc_html_e( 'Event Time', 'peanut-booker' ); ?></label>
                                    <input type="time" id="event_time" name="event_time">
                                </div>
                            </div>

                            <div class="pb-form-row">
                                <label for="event_location"><?php esc_html_e( 'Location', 'peanut-booker' ); ?> *</label>
                                <input type="text" id="event_location" name="event_location" required>
                            </div>

                            <div class="pb-form-row">
                                <label for="performer_category"><?php esc_html_e( 'Performer Type Needed', 'peanut-booker' ); ?></label>
                                <select id="performer_category" name="performer_category">
                                    <option value=""><?php esc_html_e( 'Any', 'peanut-booker' ); ?></option>
                                    <?php
                                    $categories = get_terms( array( 'taxonomy' => 'pb_performer_category', 'hide_empty' => false ) );
                                    foreach ( $categories as $cat ) :
                                    ?>
                                        <option value="<?php echo esc_attr( $cat->slug ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="pb-form-grid">
                                <div class="pb-form-row">
                                    <label for="budget_min"><?php esc_html_e( 'Budget Min', 'peanut-booker' ); ?></label>
                                    <input type="number" id="budget_min" name="budget_min" min="0" step="1">
                                </div>
                                <div class="pb-form-row">
                                    <label for="budget_max"><?php esc_html_e( 'Budget Max', 'peanut-booker' ); ?></label>
                                    <input type="number" id="budget_max" name="budget_max" min="0" step="1">
                                </div>
                            </div>

                            <div class="pb-form-row">
                                <label for="duration_hours"><?php esc_html_e( 'Duration (hours)', 'peanut-booker' ); ?></label>
                                <input type="number" id="duration_hours" name="duration_hours" min="1" max="12" value="2">
                            </div>

                            <div class="pb-form-row">
                                <label for="bid_deadline"><?php esc_html_e( 'Bid Deadline', 'peanut-booker' ); ?></label>
                                <input type="date" id="bid_deadline" name="bid_deadline" min="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>">
                                <p class="pb-field-description"><?php esc_html_e( 'Leave blank for automatic deadline (3 days before event).', 'peanut-booker' ); ?></p>
                            </div>

                            <button type="submit" class="pb-button pb-button-primary">
                                <?php esc_html_e( 'Post Event', 'peanut-booker' ); ?>
                            </button>
                        </form>
                    </div>
                <?php elseif ( empty( $events ) ) : ?>
                    <p class="pb-empty"><?php esc_html_e( 'You haven\'t posted any events yet.', 'peanut-booker' ); ?></p>
                <?php else : ?>
                    <div class="pb-events-list">
                        <?php foreach ( $events as $event ) : ?>
                            <?php $bid_count = Peanut_Booker_Database::count( 'bids', array( 'event_id' => $event->id ) ); ?>
                            <div class="pb-event-card">
                                <div class="pb-event-header">
                                    <h3><?php echo esc_html( $event->event_name ); ?></h3>
                                    <span class="pb-status pb-status-<?php echo esc_attr( $event->status ); ?>">
                                        <?php echo esc_html( ucfirst( $event->status ) ); ?>
                                    </span>
                                </div>
                                <div class="pb-event-meta">
                                    <span><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $event->event_date ) ) ); ?></span>
                                    <span><?php echo esc_html( $event->event_location ); ?></span>
                                </div>
                                <div class="pb-event-bids">
                                    <strong><?php echo esc_html( $bid_count ); ?></strong> <?php esc_html_e( 'bids received', 'peanut-booker' ); ?>
                                    <?php if ( $bid_count > 0 && 'open' === $event->status ) : ?>
                                        <a href="?tab=events&event_id=<?php echo esc_attr( $event->id ); ?>&view=bids" class="pb-link">
                                            <?php esc_html_e( 'View Bids', 'peanut-booker' ); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ( 'reviews' === $active_tab ) : ?>
            <!-- Reviews Tab -->
            <div class="pb-section">
                <h2><?php esc_html_e( 'Reviews About Me', 'peanut-booker' ); ?></h2>
                <?php
                $reviews_about_me = Peanut_Booker_Reviews::get_customer_reviews( $user_id, 10 );
                if ( empty( $reviews_about_me ) ) :
                ?>
                    <p class="pb-empty"><?php esc_html_e( 'No reviews yet.', 'peanut-booker' ); ?></p>
                <?php else : ?>
                    <div class="pb-reviews-list">
                        <?php foreach ( $reviews_about_me as $review ) : ?>
                            <div class="pb-review">
                                <div class="pb-review-header">
                                    <strong><?php echo esc_html( $review['reviewer_name'] ); ?></strong>
                                    <span class="pb-review-rating"><?php echo esc_html( $review['rating'] ); ?> ★</span>
                                    <span class="pb-review-date"><?php echo esc_html( $review['date_formatted'] ); ?></span>
                                </div>
                                <div class="pb-review-content"><?php echo esc_html( $review['content'] ); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ( 'profile' === $active_tab ) : ?>
            <!-- Profile Tab -->
            <div class="pb-section">
                <h2><?php esc_html_e( 'My Profile', 'peanut-booker' ); ?></h2>
                <form class="pb-profile-form" method="post">
                    <?php wp_nonce_field( 'pb_update_profile', 'pb_profile_nonce' ); ?>

                    <div class="pb-form-row">
                        <label for="display_name"><?php esc_html_e( 'Display Name', 'peanut-booker' ); ?></label>
                        <input type="text" id="display_name" name="display_name" value="<?php echo esc_attr( wp_get_current_user()->display_name ); ?>">
                    </div>

                    <div class="pb-form-row">
                        <label for="email"><?php esc_html_e( 'Email', 'peanut-booker' ); ?></label>
                        <input type="email" id="email" name="email" value="<?php echo esc_attr( wp_get_current_user()->user_email ); ?>">
                    </div>

                    <button type="submit" class="pb-button pb-button-primary">
                        <?php esc_html_e( 'Update Profile', 'peanut-booker' ); ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
