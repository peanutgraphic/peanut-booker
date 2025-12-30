<?php
/**
 * Single Market Event Template.
 *
 * @package Peanut_Booker
 */

get_header();

while ( have_posts() ) :
    the_post();

    $event_id    = get_the_ID();
    $customer_id = get_post_meta( $event_id, 'pb_customer_id', true );
    $event_date  = get_post_meta( $event_id, 'pb_event_date', true );
    $event_time  = get_post_meta( $event_id, 'pb_event_time', true );
    $venue_name  = get_post_meta( $event_id, 'pb_venue_name', true );
    $venue_city  = get_post_meta( $event_id, 'pb_venue_city', true );
    $venue_state = get_post_meta( $event_id, 'pb_venue_state', true );
    $budget_min  = get_post_meta( $event_id, 'pb_budget_min', true );
    $budget_max  = get_post_meta( $event_id, 'pb_budget_max', true );
    $status      = get_post_meta( $event_id, 'pb_event_status', true ) ?: 'open';
    $total_bids  = get_post_meta( $event_id, 'pb_total_bids', true ) ?: 0;
    $deadline    = get_post_meta( $event_id, 'pb_bid_deadline', true );
    $duration    = get_post_meta( $event_id, 'pb_event_duration', true );

    // Get category.
    $categories  = get_the_terms( $event_id, 'pb_performer_category' );
    $category    = $categories && ! is_wp_error( $categories ) ? $categories[0]->name : '';

    // Format location.
    $location = array_filter( array( $venue_city, $venue_state ) );
    $location_str = implode( ', ', $location );

    // Check if current user can bid.
    $can_bid = false;
    if ( is_user_logged_in() && 'open' === $status ) {
        $can_bid = Peanut_Booker_Roles::is_pro_performer();
    }

    // Get existing bids for this event.
    $bids = array();
    if ( class_exists( 'Peanut_Booker_Market' ) ) {
        $bids = Peanut_Booker_Market::get_event_bids( $event_id );
    }
    ?>

    <div class="pb-single-event">
        <div class="pb-event-detail-header">
            <div class="pb-event-header">
                <h1><?php the_title(); ?></h1>
                <span class="pb-event-status pb-status-<?php echo esc_attr( $status ); ?>">
                    <?php echo esc_html( ucfirst( $status ) ); ?>
                </span>
            </div>

            <?php if ( $category ) : ?>
                <div class="pb-event-category">
                    <span class="pb-category-badge"><?php echo esc_html( $category ); ?></span>
                </div>
            <?php endif; ?>

            <div class="pb-event-details-grid">
                <?php if ( $event_date ) : ?>
                    <div class="pb-detail-item">
                        <span class="pb-detail-label"><?php esc_html_e( 'Event Date', 'peanut-booker' ); ?></span>
                        <span class="pb-detail-value">
                            <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $event_date ) ) ); ?>
                            <?php if ( $event_time ) : ?>
                                <?php esc_html_e( 'at', 'peanut-booker' ); ?> <?php echo esc_html( date_i18n( get_option( 'time_format' ), strtotime( $event_time ) ) ); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ( $duration ) : ?>
                    <div class="pb-detail-item">
                        <span class="pb-detail-label"><?php esc_html_e( 'Duration', 'peanut-booker' ); ?></span>
                        <span class="pb-detail-value">
                            <?php echo esc_html( $duration ); ?> <?php esc_html_e( 'hours', 'peanut-booker' ); ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ( $location_str || $venue_name ) : ?>
                    <div class="pb-detail-item">
                        <span class="pb-detail-label"><?php esc_html_e( 'Location', 'peanut-booker' ); ?></span>
                        <span class="pb-detail-value">
                            <?php
                            if ( $venue_name ) {
                                echo esc_html( $venue_name );
                                if ( $location_str ) {
                                    echo '<br><small>' . esc_html( $location_str ) . '</small>';
                                }
                            } else {
                                echo esc_html( $location_str );
                            }
                            ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ( $budget_min || $budget_max ) : ?>
                    <div class="pb-detail-item">
                        <span class="pb-detail-label"><?php esc_html_e( 'Budget', 'peanut-booker' ); ?></span>
                        <span class="pb-detail-value pb-event-budget">
                            <?php
                            if ( $budget_min && $budget_max ) {
                                echo wc_price( $budget_min ) . ' - ' . wc_price( $budget_max );
                            } elseif ( $budget_max ) {
                                esc_html_e( 'Up to ', 'peanut-booker' );
                                echo wc_price( $budget_max );
                            } elseif ( $budget_min ) {
                                esc_html_e( 'From ', 'peanut-booker' );
                                echo wc_price( $budget_min );
                            }
                            ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ( $deadline ) : ?>
                    <div class="pb-detail-item">
                        <span class="pb-detail-label"><?php esc_html_e( 'Bid Deadline', 'peanut-booker' ); ?></span>
                        <span class="pb-detail-value pb-event-deadline">
                            <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $deadline ) ) ); ?>
                        </span>
                    </div>
                <?php endif; ?>

                <div class="pb-detail-item">
                    <span class="pb-detail-label"><?php esc_html_e( 'Total Bids', 'peanut-booker' ); ?></span>
                    <span class="pb-detail-value"><?php echo esc_html( $total_bids ); ?></span>
                </div>
            </div>
        </div>

        <div class="pb-event-content-grid">
            <div class="pb-event-main">
                <?php if ( get_the_content() ) : ?>
                    <div class="pb-event-section">
                        <h2><?php esc_html_e( 'Event Description', 'peanut-booker' ); ?></h2>
                        <div class="pb-event-description">
                            <?php the_content(); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ( 'open' === $status && $can_bid ) : ?>
                    <div class="pb-bid-section">
                        <h2><?php esc_html_e( 'Submit Your Bid', 'peanut-booker' ); ?></h2>
                        <form class="pb-bid-form" method="post">
                            <?php wp_nonce_field( 'pb_market_nonce', 'pb_bid_nonce' ); ?>
                            <input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>">

                            <div class="pb-form-row">
                                <label for="bid_amount"><?php esc_html_e( 'Your Bid Amount', 'peanut-booker' ); ?></label>
                                <input type="number" id="bid_amount" name="bid_amount" min="1" step="0.01"
                                    placeholder="<?php esc_attr_e( 'Enter your bid...', 'peanut-booker' ); ?>" required>
                                <?php if ( $budget_min && $budget_max ) : ?>
                                    <p class="pb-field-hint">
                                        <?php printf( esc_html__( 'Suggested range: %s - %s', 'peanut-booker' ), wc_price( $budget_min ), wc_price( $budget_max ) ); ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="pb-form-row">
                                <label for="bid_message"><?php esc_html_e( 'Message to Customer', 'peanut-booker' ); ?></label>
                                <textarea id="bid_message" name="bid_message" rows="4"
                                    placeholder="<?php esc_attr_e( 'Introduce yourself and explain why you\'re the right choice for this event...', 'peanut-booker' ); ?>"></textarea>
                            </div>

                            <button type="submit" class="pb-button pb-button-primary">
                                <?php esc_html_e( 'Submit Bid', 'peanut-booker' ); ?>
                            </button>
                        </form>
                    </div>
                <?php elseif ( 'open' === $status && is_user_logged_in() && ! $can_bid ) : ?>
                    <div class="pb-upgrade-notice">
                        <p><?php esc_html_e( 'Only Pro performers can bid on market events.', 'peanut-booker' ); ?></p>
                        <a href="<?php echo esc_url( home_url( '/dashboard/?tab=profile' ) ); ?>" class="pb-button">
                            <?php esc_html_e( 'Upgrade to Pro', 'peanut-booker' ); ?>
                        </a>
                    </div>
                <?php elseif ( 'open' === $status && ! is_user_logged_in() ) : ?>
                    <div class="pb-login-required">
                        <p><?php esc_html_e( 'Log in as a Pro performer to submit a bid.', 'peanut-booker' ); ?></p>
                        <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Log In', 'peanut-booker' ); ?></a>
                        |
                        <a href="<?php echo esc_url( home_url( '/performer-signup/' ) ); ?>"><?php esc_html_e( 'Sign Up', 'peanut-booker' ); ?></a>
                    </div>
                <?php elseif ( 'closed' === $status ) : ?>
                    <div class="pb-empty-state">
                        <div class="pb-empty-state-icon">🕐</div>
                        <h3><?php esc_html_e( 'Bidding Closed', 'peanut-booker' ); ?></h3>
                        <p><?php esc_html_e( 'The deadline for this event has passed.', 'peanut-booker' ); ?></p>
                    </div>
                <?php elseif ( 'filled' === $status ) : ?>
                    <div class="pb-empty-state">
                        <div class="pb-empty-state-icon">✅</div>
                        <h3><?php esc_html_e( 'Event Filled', 'peanut-booker' ); ?></h3>
                        <p><?php esc_html_e( 'A performer has been selected for this event.', 'peanut-booker' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="pb-event-sidebar">
                <div class="pb-event-section">
                    <h3><?php esc_html_e( 'Posted By', 'peanut-booker' ); ?></h3>
                    <?php
                    $customer = get_userdata( $customer_id );
                    if ( $customer ) :
                        ?>
                        <div class="pb-customer-info">
                            <?php echo get_avatar( $customer_id, 48 ); ?>
                            <span><?php echo esc_html( $customer->display_name ); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="pb-event-section">
                    <h3><?php esc_html_e( 'Quick Info', 'peanut-booker' ); ?></h3>
                    <ul class="pb-quick-info">
                        <li><?php printf( esc_html__( 'Posted: %s', 'peanut-booker' ), get_the_date() ); ?></li>
                        <li><?php printf( esc_html__( 'Bids received: %d', 'peanut-booker' ), $total_bids ); ?></li>
                        <?php if ( 'open' === $status && $deadline ) : ?>
                            <?php
                            $days_left = ceil( ( strtotime( $deadline ) - time() ) / DAY_IN_SECONDS );
                            if ( $days_left > 0 ) :
                                ?>
                                <li class="pb-deadline-warning">
                                    <?php printf( esc_html__( '%d days left to bid', 'peanut-booker' ), $days_left ); ?>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <a href="<?php echo esc_url( home_url( '/market/' ) ); ?>" class="pb-button pb-button-block">
                    <?php esc_html_e( 'Browse More Events', 'peanut-booker' ); ?>
                </a>
            </div>
        </div>
    </div>

    <?php
endwhile;

get_footer();
