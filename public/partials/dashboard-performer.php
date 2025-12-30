<?php
/**
 * Performer dashboard template.
 *
 * @package Peanut_Booker
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

$user_id       = get_current_user_id();
$performer     = Peanut_Booker_Performer::get_by_user_id( $user_id );
$display_data  = $performer ? Peanut_Booker_Performer::get_display_data( $performer->profile_id ) : array();
$bookings      = Peanut_Booker_Booking::get_performer_bookings( $user_id, 10 );
$pending_count = Peanut_Booker_Database::count( 'bookings', array( 'performer_user_id' => $user_id, 'booking_status' => 'pending' ) );
$earnings      = $performer ? Peanut_Booker_Performer::get_total_earnings( $performer->id ) : 0;
$active_tab    = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'overview';
?>

<div class="pb-dashboard pb-performer-dashboard">
    <div class="pb-dashboard-header">
        <div class="pb-dashboard-welcome">
            <h1><?php printf( esc_html__( 'Welcome, %s!', 'peanut-booker' ), esc_html( wp_get_current_user()->display_name ) ); ?></h1>
            <?php if ( $performer ) : ?>
                <div class="pb-performer-status">
                    <span class="pb-tier pb-tier-<?php echo esc_attr( $performer->tier ); ?>">
                        <?php echo esc_html( ucfirst( $performer->tier ) ); ?>
                    </span>
                    <?php echo wp_kses_post( Peanut_Booker_Performer::get_achievement_badge( $display_data['achievement_level'] ?? 'bronze' ) ); ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ( $performer && $performer->profile_id ) : ?>
            <a href="<?php echo esc_url( get_permalink( $performer->profile_id ) ); ?>" class="pb-button pb-button-secondary" target="_blank">
                <?php esc_html_e( 'View My Profile', 'peanut-booker' ); ?>
            </a>
        <?php endif; ?>
    </div>

    <div class="pb-dashboard-tabs">
        <a href="?tab=overview" class="<?php echo 'overview' === $active_tab ? 'active' : ''; ?>">
            <?php esc_html_e( 'Overview', 'peanut-booker' ); ?>
        </a>
        <a href="?tab=bookings" class="<?php echo 'bookings' === $active_tab ? 'active' : ''; ?>">
            <?php esc_html_e( 'Bookings', 'peanut-booker' ); ?>
            <?php if ( $pending_count > 0 ) : ?>
                <span class="pb-badge"><?php echo esc_html( $pending_count ); ?></span>
            <?php endif; ?>
        </a>
        <a href="?tab=availability" class="<?php echo 'availability' === $active_tab ? 'active' : ''; ?>">
            <?php esc_html_e( 'Availability', 'peanut-booker' ); ?>
        </a>
        <?php if ( 'pro' === ( $performer->tier ?? '' ) ) : ?>
            <a href="?tab=market" class="<?php echo 'market' === $active_tab ? 'active' : ''; ?>">
                <?php esc_html_e( 'Market Bids', 'peanut-booker' ); ?>
            </a>
        <?php endif; ?>
        <a href="?tab=reviews" class="<?php echo 'reviews' === $active_tab ? 'active' : ''; ?>">
            <?php esc_html_e( 'Reviews', 'peanut-booker' ); ?>
        </a>
        <a href="?tab=earnings" class="<?php echo 'earnings' === $active_tab ? 'active' : ''; ?>">
            <?php esc_html_e( 'Earnings', 'peanut-booker' ); ?>
        </a>
        <a href="?tab=profile" class="<?php echo 'profile' === $active_tab ? 'active' : ''; ?>">
            <?php esc_html_e( 'Edit Profile', 'peanut-booker' ); ?>
        </a>
    </div>

    <div class="pb-dashboard-content">
        <?php if ( 'overview' === $active_tab ) : ?>
            <!-- Overview Tab -->
            <div class="pb-stats-grid">
                <div class="pb-stat-card">
                    <h3><?php esc_html_e( 'Completed Bookings', 'peanut-booker' ); ?></h3>
                    <div class="pb-stat-value"><?php echo esc_html( $performer->completed_bookings ?? 0 ); ?></div>
                </div>
                <div class="pb-stat-card">
                    <h3><?php esc_html_e( 'Average Rating', 'peanut-booker' ); ?></h3>
                    <div class="pb-stat-value">
                        <?php echo $performer && $performer->average_rating ? esc_html( number_format( $performer->average_rating, 1 ) ) . ' ★' : '—'; ?>
                    </div>
                </div>
                <div class="pb-stat-card">
                    <h3><?php esc_html_e( 'Total Earnings', 'peanut-booker' ); ?></h3>
                    <div class="pb-stat-value"><?php echo wc_price( $earnings ); ?></div>
                </div>
                <?php if ( $pending_count > 0 ) : ?>
                    <div class="pb-stat-card pb-alert">
                        <h3><?php esc_html_e( 'Pending Requests', 'peanut-booker' ); ?></h3>
                        <div class="pb-stat-value"><?php echo esc_html( $pending_count ); ?></div>
                        <a href="?tab=bookings" class="pb-stat-link"><?php esc_html_e( 'View', 'peanut-booker' ); ?></a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ( 'free' === ( $performer->tier ?? 'free' ) ) : ?>
                <div class="pb-upgrade-banner">
                    <h3><?php esc_html_e( 'Upgrade to Pro', 'peanut-booker' ); ?></h3>
                    <p><?php esc_html_e( 'Lower commission rates, unlimited photos, market access, and more!', 'peanut-booker' ); ?></p>
                    <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="pb-button pb-button-primary">
                        <?php esc_html_e( 'Learn More', 'peanut-booker' ); ?>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Upcoming Bookings -->
            <div class="pb-section">
                <h2><?php esc_html_e( 'Upcoming Bookings', 'peanut-booker' ); ?></h2>
                <?php
                $upcoming = array_filter( $bookings, function( $b ) {
                    return in_array( $b->booking_status, array( 'pending', 'confirmed' ), true ) && strtotime( $b->event_date ) >= strtotime( 'today' );
                } );

                if ( empty( $upcoming ) ) :
                ?>
                    <p class="pb-empty"><?php esc_html_e( 'No upcoming bookings.', 'peanut-booker' ); ?></p>
                <?php else : ?>
                    <div class="pb-booking-cards">
                        <?php foreach ( array_slice( $upcoming, 0, 3 ) as $booking ) : ?>
                            <?php $customer = get_userdata( $booking->customer_user_id ); ?>
                            <div class="pb-booking-card">
                                <div class="pb-booking-date">
                                    <span class="pb-date-day"><?php echo esc_html( date_i18n( 'j', strtotime( $booking->event_date ) ) ); ?></span>
                                    <span class="pb-date-month"><?php echo esc_html( date_i18n( 'M', strtotime( $booking->event_date ) ) ); ?></span>
                                </div>
                                <div class="pb-booking-info">
                                    <h4><?php echo $customer ? esc_html( $customer->display_name ) : esc_html__( 'Customer', 'peanut-booker' ); ?></h4>
                                    <p><?php echo esc_html( $booking->event_location ?: __( 'Location TBD', 'peanut-booker' ) ); ?></p>
                                    <span class="pb-status pb-status-<?php echo esc_attr( $booking->booking_status ); ?>">
                                        <?php echo esc_html( ucfirst( $booking->booking_status ) ); ?>
                                    </span>
                                </div>
                                <div class="pb-booking-actions">
                                    <?php if ( 'pending' === $booking->booking_status ) : ?>
                                        <button class="pb-button pb-button-small pb-confirm-booking" data-booking-id="<?php echo esc_attr( $booking->id ); ?>">
                                            <?php esc_html_e( 'Confirm', 'peanut-booker' ); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ( 'bookings' === $active_tab ) : ?>
            <!-- Bookings Tab -->
            <div class="pb-section">
                <h2><?php esc_html_e( 'All Bookings', 'peanut-booker' ); ?></h2>
                <?php if ( empty( $bookings ) ) : ?>
                    <p class="pb-empty"><?php esc_html_e( 'No bookings yet.', 'peanut-booker' ); ?></p>
                <?php else : ?>
                    <table class="pb-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Date', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Customer', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Event', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Amount', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Status', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Actions', 'peanut-booker' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $bookings as $booking ) : ?>
                                <?php $customer = get_userdata( $booking->customer_user_id ); ?>
                                <tr>
                                    <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->event_date ) ) ); ?></td>
                                    <td><?php echo $customer ? esc_html( $customer->display_name ) : '—'; ?></td>
                                    <td><?php echo esc_html( wp_trim_words( $booking->event_description, 5 ) ?: __( 'Event', 'peanut-booker' ) ); ?></td>
                                    <td><?php echo wc_price( $booking->performer_payout ); ?></td>
                                    <td>
                                        <span class="pb-status pb-status-<?php echo esc_attr( $booking->booking_status ); ?>">
                                            <?php echo esc_html( ucfirst( $booking->booking_status ) ); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ( 'pending' === $booking->booking_status ) : ?>
                                            <button class="pb-button pb-button-small pb-confirm-booking" data-booking-id="<?php echo esc_attr( $booking->id ); ?>">
                                                <?php esc_html_e( 'Confirm', 'peanut-booker' ); ?>
                                            </button>
                                            <button class="pb-button pb-button-small pb-button-secondary pb-cancel-booking" data-booking-id="<?php echo esc_attr( $booking->id ); ?>">
                                                <?php esc_html_e( 'Decline', 'peanut-booker' ); ?>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        <?php elseif ( 'availability' === $active_tab ) : ?>
            <!-- Availability Tab -->
            <div class="pb-section">
                <h2><?php esc_html_e( 'Manage Availability', 'peanut-booker' ); ?></h2>
                <p class="pb-description"><?php esc_html_e( 'Click on dates to select them, then use the action buttons below. Hold Shift to select a range.', 'peanut-booker' ); ?></p>

                <!-- Selected Dates Info -->
                <div class="pb-selected-dates-info" id="pb-selected-dates-info" style="display: none;">
                    <span class="pb-selected-count"><?php esc_html_e( '0 dates selected', 'peanut-booker' ); ?></span>
                    <button type="button" class="pb-button pb-button-small pb-button-link" id="pb-clear-selection">
                        <?php esc_html_e( 'Clear selection', 'peanut-booker' ); ?>
                    </button>
                </div>

                <!-- Calendar Actions -->
                <div class="pb-calendar-actions" id="pb-calendar-actions">
                    <button type="button" class="pb-button pb-button-secondary" id="pb-quick-block" disabled>
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <?php esc_html_e( 'Quick Block', 'peanut-booker' ); ?>
                    </button>
                    <button type="button" class="pb-button pb-button-secondary" id="pb-add-external-gig" disabled>
                        <span class="dashicons dashicons-tickets-alt"></span>
                        <?php esc_html_e( 'Add External Gig', 'peanut-booker' ); ?>
                    </button>
                    <button type="button" class="pb-button pb-button-secondary" id="pb-unblock-dates" disabled>
                        <span class="dashicons dashicons-unlock"></span>
                        <?php esc_html_e( 'Unblock Selected', 'peanut-booker' ); ?>
                    </button>
                </div>

                <div class="pb-availability-calendar pb-editable pb-multi-select" data-performer-id="<?php echo esc_attr( $performer->id ?? 0 ); ?>">
                    <?php
                    if ( $performer ) {
                        echo wp_kses_post( Peanut_Booker_Availability::render_calendar( $performer->id, null, true ) );
                    }
                    ?>
                </div>
            </div>

            <!-- External Gig Modal -->
            <div class="pb-modal" id="pb-external-gig-modal" style="display: none;">
                <div class="pb-modal-overlay"></div>
                <div class="pb-modal-content">
                    <button type="button" class="pb-modal-close">&times;</button>
                    <h3><?php esc_html_e( 'Add External Gig', 'peanut-booker' ); ?></h3>
                    <p class="pb-modal-description"><?php esc_html_e( 'Record an event booked outside this platform. The selected dates will be marked as unavailable.', 'peanut-booker' ); ?></p>
                    <form id="pb-external-gig-form">
                        <?php wp_nonce_field( 'pb_availability_nonce', 'pb_availability_nonce' ); ?>
                        <input type="hidden" name="action" value="pb_block_external_gig">
                        <input type="hidden" name="dates" id="pb-gig-dates" value="">

                        <div class="pb-form-row">
                            <label for="pb-gig-event-name"><?php esc_html_e( 'Event/Show Name', 'peanut-booker' ); ?></label>
                            <input type="text" id="pb-gig-event-name" name="event_name" placeholder="<?php esc_attr_e( 'e.g., Wedding Reception, Corporate Party', 'peanut-booker' ); ?>">
                        </div>
                        <div class="pb-form-row">
                            <label for="pb-gig-venue"><?php esc_html_e( 'Venue', 'peanut-booker' ); ?></label>
                            <input type="text" id="pb-gig-venue" name="venue_name" placeholder="<?php esc_attr_e( 'e.g., Hilton Downtown', 'peanut-booker' ); ?>">
                        </div>
                        <div class="pb-form-row-group">
                            <div class="pb-form-row pb-form-row-half">
                                <label for="pb-gig-type"><?php esc_html_e( 'Event Type', 'peanut-booker' ); ?></label>
                                <select id="pb-gig-type" name="event_type">
                                    <option value=""><?php esc_html_e( 'Select...', 'peanut-booker' ); ?></option>
                                    <option value="wedding"><?php esc_html_e( 'Wedding', 'peanut-booker' ); ?></option>
                                    <option value="corporate"><?php esc_html_e( 'Corporate Event', 'peanut-booker' ); ?></option>
                                    <option value="private_party"><?php esc_html_e( 'Private Party', 'peanut-booker' ); ?></option>
                                    <option value="concert"><?php esc_html_e( 'Concert/Show', 'peanut-booker' ); ?></option>
                                    <option value="festival"><?php esc_html_e( 'Festival', 'peanut-booker' ); ?></option>
                                    <option value="club"><?php esc_html_e( 'Club/Bar', 'peanut-booker' ); ?></option>
                                    <option value="other"><?php esc_html_e( 'Other', 'peanut-booker' ); ?></option>
                                </select>
                            </div>
                            <div class="pb-form-row pb-form-row-half">
                                <label for="pb-gig-location"><?php esc_html_e( 'Location/City', 'peanut-booker' ); ?></label>
                                <input type="text" id="pb-gig-location" name="event_location" placeholder="<?php esc_attr_e( 'e.g., Los Angeles, CA', 'peanut-booker' ); ?>">
                            </div>
                        </div>
                        <div class="pb-form-row">
                            <label for="pb-gig-notes"><?php esc_html_e( 'Notes (optional)', 'peanut-booker' ); ?></label>
                            <textarea id="pb-gig-notes" name="notes" rows="2" placeholder="<?php esc_attr_e( 'Any additional details...', 'peanut-booker' ); ?>"></textarea>
                        </div>
                        <div class="pb-gig-selected-dates">
                            <strong><?php esc_html_e( 'Selected Dates:', 'peanut-booker' ); ?></strong>
                            <span id="pb-gig-dates-display"></span>
                        </div>
                        <div class="pb-modal-actions">
                            <button type="button" class="pb-button pb-button-secondary pb-modal-cancel">
                                <?php esc_html_e( 'Cancel', 'peanut-booker' ); ?>
                            </button>
                            <button type="submit" class="pb-button pb-button-primary">
                                <span class="dashicons dashicons-plus-alt"></span>
                                <?php esc_html_e( 'Add External Gig', 'peanut-booker' ); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        <?php elseif ( 'market' === $active_tab && 'pro' === ( $performer->tier ?? '' ) ) : ?>
            <!-- Market Bids Tab -->
            <div class="pb-section">
                <h2><?php esc_html_e( 'My Market Bids', 'peanut-booker' ); ?></h2>
                <?php
                $my_bids = Peanut_Booker_Database::get_results( 'bids', array( 'performer_user_id' => $user_id ), 'created_at', 'DESC', 20 );
                if ( empty( $my_bids ) ) :
                ?>
                    <p class="pb-empty"><?php esc_html_e( 'You haven\'t placed any bids yet.', 'peanut-booker' ); ?></p>
                    <a href="<?php echo esc_url( get_post_type_archive_link( 'pb_market_event' ) ); ?>" class="pb-button pb-button-primary">
                        <?php esc_html_e( 'Browse Market', 'peanut-booker' ); ?>
                    </a>
                <?php else : ?>
                    <table class="pb-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Event', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Event Date', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'My Bid', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Status', 'peanut-booker' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $my_bids as $bid ) : ?>
                                <?php $event = Peanut_Booker_Database::get_row( 'events', array( 'id' => $bid->event_id ) ); ?>
                                <tr>
                                    <td><?php echo $event ? esc_html( $event->event_name ) : '—'; ?></td>
                                    <td><?php echo $event ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $event->event_date ) ) ) : '—'; ?></td>
                                    <td><?php echo wc_price( $bid->bid_amount ); ?></td>
                                    <td>
                                        <span class="pb-status pb-status-<?php echo esc_attr( $bid->status ); ?>">
                                            <?php echo esc_html( ucfirst( $bid->status ) ); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        <?php elseif ( 'reviews' === $active_tab ) : ?>
            <!-- Reviews Tab -->
            <div class="pb-section">
                <h2><?php esc_html_e( 'My Reviews', 'peanut-booker' ); ?></h2>
                <?php
                $reviews = $performer ? Peanut_Booker_Reviews::get_performer_reviews( $performer->id, 20 ) : array();
                if ( empty( $reviews ) ) :
                ?>
                    <p class="pb-empty"><?php esc_html_e( 'No reviews yet.', 'peanut-booker' ); ?></p>
                <?php else : ?>
                    <div class="pb-reviews-list">
                        <?php foreach ( $reviews as $review ) : ?>
                            <div class="pb-review">
                                <div class="pb-review-header">
                                    <strong><?php echo esc_html( $review['reviewer_name'] ); ?></strong>
                                    <span class="pb-review-rating"><?php echo esc_html( $review['rating'] ); ?> ★</span>
                                    <span class="pb-review-date"><?php echo esc_html( $review['date_formatted'] ); ?></span>
                                </div>
                                <div class="pb-review-content"><?php echo esc_html( $review['content'] ); ?></div>
                                <?php if ( $review['response'] ) : ?>
                                    <div class="pb-review-response">
                                        <strong><?php esc_html_e( 'Your response:', 'peanut-booker' ); ?></strong>
                                        <p><?php echo esc_html( $review['response'] ); ?></p>
                                    </div>
                                <?php else : ?>
                                    <button class="pb-button pb-button-small pb-respond-review" data-review-id="<?php echo esc_attr( $review['id'] ); ?>">
                                        <?php esc_html_e( 'Respond', 'peanut-booker' ); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ( 'earnings' === $active_tab ) : ?>
            <!-- Earnings Tab -->
            <div class="pb-section">
                <h2><?php esc_html_e( 'Earnings', 'peanut-booker' ); ?></h2>
                <div class="pb-stats-grid">
                    <div class="pb-stat-card">
                        <h3><?php esc_html_e( 'Total Earned', 'peanut-booker' ); ?></h3>
                        <div class="pb-stat-value"><?php echo wc_price( $earnings ); ?></div>
                    </div>
                    <div class="pb-stat-card">
                        <h3><?php esc_html_e( 'Commission Rate', 'peanut-booker' ); ?></h3>
                        <div class="pb-stat-value"><?php echo esc_html( Peanut_Booker_Roles::get_commission_rate( $user_id ) ); ?>%</div>
                    </div>
                </div>

                <h3><?php esc_html_e( 'Recent Transactions', 'peanut-booker' ); ?></h3>
                <?php
                $completed = array_filter( $bookings, function( $b ) {
                    return 'completed' === $b->booking_status;
                } );
                if ( empty( $completed ) ) :
                ?>
                    <p class="pb-empty"><?php esc_html_e( 'No completed transactions yet.', 'peanut-booker' ); ?></p>
                <?php else : ?>
                    <table class="pb-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Date', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Booking Total', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Commission', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Your Earnings', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Payout Status', 'peanut-booker' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $completed as $booking ) : ?>
                                <tr>
                                    <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->completion_date ?: $booking->event_date ) ) ); ?></td>
                                    <td><?php echo wc_price( $booking->total_amount ); ?></td>
                                    <td><?php echo wc_price( $booking->platform_commission ); ?></td>
                                    <td><?php echo wc_price( $booking->performer_payout ); ?></td>
                                    <td>
                                        <span class="pb-status pb-status-<?php echo 'released' === $booking->escrow_status ? 'completed' : 'pending'; ?>">
                                            <?php echo 'released' === $booking->escrow_status ? esc_html__( 'Paid', 'peanut-booker' ) : esc_html__( 'Pending', 'peanut-booker' ); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        <?php elseif ( 'profile' === $active_tab ) : ?>
            <!-- Edit Profile Tab - Profile Wizard -->
            <?php if ( $performer && $performer->profile_id ) : ?>
                <?php
                $photo_limit   = Peanut_Booker_Roles::get_photo_limit( $user_id );
                $video_limit   = Peanut_Booker_Roles::get_video_limit( $user_id );
                $current_photos = $display_data['gallery'] ?? array();
                $current_videos = $display_data['videos'] ?? array();
                $categories     = get_terms( array( 'taxonomy' => 'pb_performer_category', 'hide_empty' => false ) );
                $areas          = get_terms( array( 'taxonomy' => 'pb_service_area', 'hide_empty' => false ) );
                $selected_cats  = wp_get_post_terms( $performer->profile_id, 'pb_performer_category', array( 'fields' => 'ids' ) );
                $selected_areas = wp_get_post_terms( $performer->profile_id, 'pb_service_area', array( 'fields' => 'ids' ) );
                ?>
                <div class="pb-profile-wizard" data-profile-id="<?php echo esc_attr( $performer->profile_id ); ?>">
                    <!-- Wizard Navigation Tabs -->
                    <div class="pb-wizard-nav">
                        <button type="button" class="pb-wizard-tab active" data-tab="basic-info">
                            <span class="pb-tab-number">1</span>
                            <?php esc_html_e( 'Basic Info', 'peanut-booker' ); ?>
                        </button>
                        <button type="button" class="pb-wizard-tab" data-tab="photos">
                            <span class="pb-tab-number">2</span>
                            <?php esc_html_e( 'Photos', 'peanut-booker' ); ?>
                        </button>
                        <button type="button" class="pb-wizard-tab" data-tab="videos">
                            <span class="pb-tab-number">3</span>
                            <?php esc_html_e( 'Videos', 'peanut-booker' ); ?>
                        </button>
                        <button type="button" class="pb-wizard-tab" data-tab="pricing">
                            <span class="pb-tab-number">4</span>
                            <?php esc_html_e( 'Pricing', 'peanut-booker' ); ?>
                        </button>
                        <button type="button" class="pb-wizard-tab" data-tab="location">
                            <span class="pb-tab-number">5</span>
                            <?php esc_html_e( 'Location', 'peanut-booker' ); ?>
                        </button>
                        <button type="button" class="pb-wizard-tab" data-tab="categories">
                            <span class="pb-tab-number">6</span>
                            <?php esc_html_e( 'Services', 'peanut-booker' ); ?>
                        </button>
                    </div>

                    <!-- Progress Bar -->
                    <div class="pb-wizard-progress">
                        <div class="pb-progress-bar" style="width: <?php echo esc_attr( $display_data['profile_completeness'] ?? 0 ); ?>%"></div>
                        <span class="pb-progress-text"><?php printf( esc_html__( '%d%% Complete', 'peanut-booker' ), $display_data['profile_completeness'] ?? 0 ); ?></span>
                    </div>

                    <!-- Wizard Form -->
                    <form class="pb-wizard-form" id="pb-profile-wizard-form">
                        <?php wp_nonce_field( 'pb_performer_nonce', 'pb_nonce' ); ?>

                        <!-- Tab 1: Basic Info -->
                        <div class="pb-wizard-panel active" data-panel="basic-info">
                            <h3><?php esc_html_e( 'Basic Information', 'peanut-booker' ); ?></h3>
                            <div class="pb-form-row">
                                <label for="pb-stage-name"><?php esc_html_e( 'Stage Name', 'peanut-booker' ); ?></label>
                                <input type="text" id="pb-stage-name" name="stage_name" value="<?php echo esc_attr( $display_data['stage_name'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Your performer name or alias', 'peanut-booker' ); ?>">
                            </div>
                            <div class="pb-form-row">
                                <label for="pb-tagline"><?php esc_html_e( 'Tagline', 'peanut-booker' ); ?></label>
                                <input type="text" id="pb-tagline" name="tagline" value="<?php echo esc_attr( $display_data['tagline'] ?? '' ); ?>" maxlength="100" placeholder="<?php esc_attr_e( 'A short description that appears under your name', 'peanut-booker' ); ?>">
                                <span class="pb-field-hint"><?php esc_html_e( 'Keep it short and memorable - max 100 characters', 'peanut-booker' ); ?></span>
                            </div>
                            <div class="pb-form-row">
                                <label for="pb-bio"><?php esc_html_e( 'Bio', 'peanut-booker' ); ?></label>
                                <textarea id="pb-bio" name="bio" rows="6" placeholder="<?php esc_attr_e( 'Tell potential clients about yourself, your experience, and what makes your performances special...', 'peanut-booker' ); ?>"><?php echo esc_textarea( $display_data['bio'] ?? '' ); ?></textarea>
                            </div>
                            <div class="pb-form-row">
                                <label for="pb-experience"><?php esc_html_e( 'Years of Experience', 'peanut-booker' ); ?></label>
                                <input type="number" id="pb-experience" name="experience_years" value="<?php echo esc_attr( $display_data['experience_years'] ?? '' ); ?>" min="0" max="50">
                            </div>
                        </div>

                        <!-- Tab 2: Photos -->
                        <div class="pb-wizard-panel" data-panel="photos">
                            <h3><?php esc_html_e( 'Profile Photos', 'peanut-booker' ); ?></h3>
                            <div class="pb-limit-info">
                                <?php if ( $photo_limit > 1 ) : ?>
                                    <?php printf( esc_html__( 'You can upload up to %d photos.', 'peanut-booker' ), $photo_limit ); ?>
                                <?php else : ?>
                                    <?php esc_html_e( 'Free accounts can upload 1 photo.', 'peanut-booker' ); ?>
                                    <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Upgrade to Pro for 5 photos', 'peanut-booker' ); ?></a>
                                <?php endif; ?>
                            </div>
                            <div class="pb-photo-gallery" data-limit="<?php echo esc_attr( $photo_limit ); ?>">
                                <div class="pb-photo-grid" id="pb-photo-grid">
                                    <?php foreach ( $current_photos as $photo_id ) : ?>
                                        <div class="pb-photo-item" data-id="<?php echo esc_attr( $photo_id ); ?>">
                                            <?php echo wp_get_attachment_image( $photo_id, 'medium' ); ?>
                                            <button type="button" class="pb-remove-photo" title="<?php esc_attr_e( 'Remove', 'peanut-booker' ); ?>">&times;</button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="pb-button pb-button-secondary" id="pb-upload-photos">
                                    <span class="dashicons dashicons-camera"></span>
                                    <?php esc_html_e( 'Add Photos', 'peanut-booker' ); ?>
                                </button>
                            </div>
                            <input type="hidden" name="gallery_images" id="pb-gallery-images" value="<?php echo esc_attr( implode( ',', $current_photos ) ); ?>">
                        </div>

                        <!-- Tab 3: Videos -->
                        <div class="pb-wizard-panel" data-panel="videos">
                            <h3><?php esc_html_e( 'Video Links', 'peanut-booker' ); ?></h3>
                            <?php if ( $video_limit === 0 ) : ?>
                                <div class="pb-limit-info pb-limit-locked">
                                    <span class="dashicons dashicons-lock"></span>
                                    <?php esc_html_e( 'Videos are a Pro feature.', 'peanut-booker' ); ?>
                                    <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Upgrade to add videos', 'peanut-booker' ); ?></a>
                                </div>
                            <?php else : ?>
                                <div class="pb-limit-info">
                                    <?php printf( esc_html__( 'Add up to %d video links (YouTube, Vimeo, etc.)', 'peanut-booker' ), $video_limit ); ?>
                                </div>
                            <?php endif; ?>
                            <div class="pb-video-list" id="pb-video-list" data-limit="<?php echo esc_attr( $video_limit ); ?>">
                                <?php if ( ! empty( $current_videos ) ) : ?>
                                    <?php foreach ( $current_videos as $video_url ) : ?>
                                        <div class="pb-video-item">
                                            <input type="url" name="video_links[]" value="<?php echo esc_url( $video_url ); ?>" placeholder="https://youtube.com/watch?v=...">
                                            <button type="button" class="pb-remove-video" title="<?php esc_attr_e( 'Remove', 'peanut-booker' ); ?>">&times;</button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="pb-button pb-button-secondary" id="pb-add-video" <?php echo $video_limit === 0 ? 'disabled' : ''; ?>>
                                <span class="dashicons dashicons-video-alt3"></span>
                                <?php esc_html_e( 'Add Video Link', 'peanut-booker' ); ?>
                            </button>
                        </div>

                        <!-- Tab 4: Pricing -->
                        <div class="pb-wizard-panel" data-panel="pricing">
                            <h3><?php esc_html_e( 'Pricing & Rates', 'peanut-booker' ); ?></h3>
                            <div class="pb-form-row">
                                <label for="pb-hourly-rate"><?php esc_html_e( 'Hourly Rate', 'peanut-booker' ); ?> (<?php echo esc_html( get_woocommerce_currency_symbol() ); ?>)</label>
                                <input type="number" id="pb-hourly-rate" name="hourly_rate" value="<?php echo esc_attr( $display_data['hourly_rate'] ?? '' ); ?>" min="0" step="1" placeholder="150">
                            </div>
                            <div class="pb-form-row">
                                <label for="pb-minimum-booking"><?php esc_html_e( 'Minimum Booking (hours)', 'peanut-booker' ); ?></label>
                                <input type="number" id="pb-minimum-booking" name="minimum_booking" value="<?php echo esc_attr( get_post_meta( $performer->profile_id, 'pb_minimum_booking', true ) ?: '1' ); ?>" min="1" max="12" step="0.5">
                                <span class="pb-field-hint"><?php esc_html_e( 'The minimum number of hours for a booking', 'peanut-booker' ); ?></span>
                            </div>
                            <div class="pb-form-row">
                                <label for="pb-deposit-percentage"><?php esc_html_e( 'Deposit Percentage', 'peanut-booker' ); ?></label>
                                <div class="pb-input-with-suffix">
                                    <input type="number" id="pb-deposit-percentage" name="deposit_percentage" value="<?php echo esc_attr( $display_data['deposit_percentage'] ?? 25 ); ?>" min="10" max="100" step="5">
                                    <span class="pb-input-suffix">%</span>
                                </div>
                                <span class="pb-field-hint"><?php esc_html_e( 'Percentage of total required upfront to secure the booking (10-100%)', 'peanut-booker' ); ?></span>
                            </div>
                        </div>

                        <!-- Tab 5: Location & Travel -->
                        <div class="pb-wizard-panel" data-panel="location">
                            <h3><?php esc_html_e( 'Location & Travel', 'peanut-booker' ); ?></h3>
                            <div class="pb-form-row-group">
                                <div class="pb-form-row pb-form-row-half">
                                    <label for="pb-city"><?php esc_html_e( 'City', 'peanut-booker' ); ?></label>
                                    <input type="text" id="pb-city" name="location_city" value="<?php echo esc_attr( $display_data['location_city'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'e.g., Los Angeles', 'peanut-booker' ); ?>">
                                </div>
                                <div class="pb-form-row pb-form-row-half">
                                    <label for="pb-state"><?php esc_html_e( 'State/Province', 'peanut-booker' ); ?></label>
                                    <input type="text" id="pb-state" name="location_state" value="<?php echo esc_attr( $display_data['location_state'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'e.g., California', 'peanut-booker' ); ?>">
                                </div>
                            </div>
                            <div class="pb-form-row pb-checkbox-row">
                                <label class="pb-checkbox-label">
                                    <input type="checkbox" name="travel_willing" value="1" <?php checked( $display_data['travel_willing'] ?? false ); ?>>
                                    <?php esc_html_e( 'Willing to travel for bookings', 'peanut-booker' ); ?>
                                </label>
                            </div>
                            <div class="pb-form-row pb-travel-radius-row" style="<?php echo ( $display_data['travel_willing'] ?? false ) ? '' : 'display:none;'; ?>">
                                <label for="pb-travel-radius"><?php esc_html_e( 'Travel Radius (miles)', 'peanut-booker' ); ?></label>
                                <input type="number" id="pb-travel-radius" name="travel_radius" value="<?php echo esc_attr( $display_data['travel_radius'] ?? '' ); ?>" min="0" max="500" placeholder="50">
                                <span class="pb-field-hint"><?php esc_html_e( 'How far are you willing to travel from your home base?', 'peanut-booker' ); ?></span>
                            </div>
                        </div>

                        <!-- Tab 6: Categories & Services -->
                        <div class="pb-wizard-panel" data-panel="categories">
                            <h3><?php esc_html_e( 'Categories & Services', 'peanut-booker' ); ?></h3>
                            <div class="pb-form-row">
                                <label><?php esc_html_e( 'What type of performer are you?', 'peanut-booker' ); ?></label>
                                <div class="pb-checkbox-group">
                                    <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                                        <?php foreach ( $categories as $cat ) : ?>
                                            <label class="pb-checkbox-item">
                                                <input type="checkbox" name="categories[]" value="<?php echo esc_attr( $cat->term_id ); ?>" <?php checked( in_array( $cat->term_id, $selected_cats, true ) ); ?>>
                                                <span><?php echo esc_html( $cat->name ); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="pb-form-row">
                                <label><?php esc_html_e( 'Service Areas', 'peanut-booker' ); ?></label>
                                <p class="pb-field-hint"><?php esc_html_e( 'Select the regions where you offer your services', 'peanut-booker' ); ?></p>
                                <div class="pb-checkbox-group">
                                    <?php if ( ! empty( $areas ) && ! is_wp_error( $areas ) ) : ?>
                                        <?php foreach ( $areas as $area ) : ?>
                                            <label class="pb-checkbox-item">
                                                <input type="checkbox" name="service_areas[]" value="<?php echo esc_attr( $area->term_id ); ?>" <?php checked( in_array( $area->term_id, $selected_areas, true ) ); ?>>
                                                <span><?php echo esc_html( $area->name ); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="pb-wizard-actions">
                            <button type="button" class="pb-button pb-button-secondary pb-wizard-prev" style="display:none;">
                                <span class="dashicons dashicons-arrow-left-alt"></span>
                                <?php esc_html_e( 'Previous', 'peanut-booker' ); ?>
                            </button>
                            <div class="pb-wizard-actions-right">
                                <button type="button" class="pb-button pb-wizard-next">
                                    <?php esc_html_e( 'Next', 'peanut-booker' ); ?>
                                    <span class="dashicons dashicons-arrow-right-alt"></span>
                                </button>
                                <button type="submit" class="pb-button pb-button-primary pb-wizard-save" style="display:none;">
                                    <span class="dashicons dashicons-saved"></span>
                                    <?php esc_html_e( 'Save Profile', 'peanut-booker' ); ?>
                                </button>
                            </div>
                        </div>
                    </form>

                    <p class="pb-admin-fallback">
                        <a href="<?php echo esc_url( get_edit_post_link( $performer->profile_id ) ); ?>">
                            <?php esc_html_e( 'Advanced: Edit in WordPress Admin', 'peanut-booker' ); ?>
                        </a>
                    </p>
                </div>
            <?php else : ?>
                <div class="pb-section">
                    <h2><?php esc_html_e( 'Edit Profile', 'peanut-booker' ); ?></h2>
                    <p><?php esc_html_e( 'Your profile is being set up. Please check back shortly.', 'peanut-booker' ); ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
