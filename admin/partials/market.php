<?php
/**
 * Admin market events list template.
 *
 * @package Peanut_Booker
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}
?>

<div class="wrap pb-admin-market">
    <h1><?php esc_html_e( 'Market Events', 'peanut-booker' ); ?></h1>

    <div class="pb-admin-filters">
        <select id="pb-filter-status">
            <option value=""><?php esc_html_e( 'All Statuses', 'peanut-booker' ); ?></option>
            <option value="open"><?php esc_html_e( 'Open for Bids', 'peanut-booker' ); ?></option>
            <option value="closed"><?php esc_html_e( 'Bidding Closed', 'peanut-booker' ); ?></option>
            <option value="booked"><?php esc_html_e( 'Booked', 'peanut-booker' ); ?></option>
            <option value="cancelled"><?php esc_html_e( 'Cancelled', 'peanut-booker' ); ?></option>
        </select>
        <input type="date" id="pb-filter-event-date" placeholder="<?php esc_attr_e( 'Event Date', 'peanut-booker' ); ?>">
    </div>

    <?php if ( empty( $events ) ) : ?>
        <div class="pb-empty-state">
            <h3><?php esc_html_e( 'No market events', 'peanut-booker' ); ?></h3>
            <p><?php esc_html_e( 'Events posted to the market will appear here.', 'peanut-booker' ); ?></p>
        </div>
    <?php else : ?>
        <table class="pb-admin-table widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'ID', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Event', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Customer', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Category', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Event Date', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Budget', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Bids', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Deadline', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'peanut-booker' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $events as $event ) : ?>
                    <?php
                    $customer   = get_userdata( $event->customer_id );
                    $bid_count  = Peanut_Booker_Database::count( 'bids', array( 'event_id' => $event->id ) );
                    $is_expired = $event->bid_deadline ? strtotime( $event->bid_deadline ) < time() : false;
                    // Get category name.
                    $category_name = '—';
                    if ( ! empty( $event->category_id ) ) {
                        $cat_term = get_term( $event->category_id, 'pb_performer_category' );
                        if ( $cat_term && ! is_wp_error( $cat_term ) ) {
                            $category_name = $cat_term->name;
                        }
                    }
                    ?>
                    <tr>
                        <td><strong>#<?php echo esc_html( $event->id ); ?></strong></td>
                        <td>
                            <strong><?php echo esc_html( $event->title ); ?></strong>
                            <br><small><?php echo esc_html( wp_trim_words( $event->description, 10 ) ); ?></small>
                        </td>
                        <td>
                            <?php if ( $customer ) : ?>
                                <?php echo esc_html( $customer->display_name ); ?>
                                <br><small><?php echo esc_html( $customer->user_email ); ?></small>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html( $category_name ); ?></td>
                        <td>
                            <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $event->event_date ) ) ); ?>
                            <?php if ( $event->event_start_time ) : ?>
                                <br><small><?php echo esc_html( $event->event_start_time ); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( $event->budget_min && $event->budget_max ) : ?>
                                <?php echo wc_price( $event->budget_min ); ?> - <?php echo wc_price( $event->budget_max ); ?>
                            <?php elseif ( $event->budget_max ) : ?>
                                <?php esc_html_e( 'Up to', 'peanut-booker' ); ?> <?php echo wc_price( $event->budget_max ); ?>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo esc_html( $bid_count ); ?></strong>
                            <?php if ( $bid_count > 0 ) : ?>
                                <br><a href="#" class="pb-toggle-details" data-target="#pb-bids-<?php echo esc_attr( $event->id ); ?>">
                                    <?php esc_html_e( 'View Bids', 'peanut-booker' ); ?>
                                </a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $deadline = strtotime( $event->bid_deadline );
                            if ( $is_expired ) {
                                echo '<span class="pb-deadline-expired">' . esc_html__( 'Expired', 'peanut-booker' ) . '</span>';
                            } else {
                                echo esc_html( date_i18n( get_option( 'date_format' ), $deadline ) );
                                echo '<br><small>' . esc_html( human_time_diff( time(), $deadline ) ) . ' ' . esc_html__( 'left', 'peanut-booker' ) . '</small>';
                            }
                            ?>
                        </td>
                        <td>
                            <span class="pb-status pb-status-<?php echo esc_attr( $event->status ); ?>">
                                <?php echo esc_html( ucfirst( $event->status ) ); ?>
                            </span>
                        </td>
                        <td>
                            <div class="pb-row-actions">
                                <?php if ( 'open' === $event->status ) : ?>
                                    <a href="#" class="pb-close-event" data-event-id="<?php echo esc_attr( $event->id ); ?>">
                                        <?php esc_html_e( 'Close Bidding', 'peanut-booker' ); ?>
                                    </a> |
                                <?php endif; ?>
                                <a href="#" class="pb-toggle-details" data-target="#pb-event-<?php echo esc_attr( $event->id ); ?>">
                                    <?php esc_html_e( 'Details', 'peanut-booker' ); ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <!-- Event details row -->
                    <tr id="pb-event-<?php echo esc_attr( $event->id ); ?>" class="pb-event-details" style="display: none;">
                        <td colspan="10">
                            <div class="pb-detail-panel">
                                <h4><?php esc_html_e( 'Event Details', 'peanut-booker' ); ?></h4>
                                <p><strong><?php esc_html_e( 'Description:', 'peanut-booker' ); ?></strong><br>
                                <?php echo esc_html( $event->description ); ?></p>
                                <p><strong><?php esc_html_e( 'Location:', 'peanut-booker' ); ?></strong> <?php echo esc_html( ( $event->city ? $event->city . ', ' . $event->state : '' ) ?: __( 'Not specified', 'peanut-booker' ) ); ?></p>
                                <p><strong><?php esc_html_e( 'Created:', 'peanut-booker' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $event->created_at ) ) ); ?></p>
                            </div>
                        </td>
                    </tr>
                    <!-- Bids row -->
                    <?php if ( $bid_count > 0 ) : ?>
                        <?php $bids = Peanut_Booker_Database::get_results( 'bids', array( 'event_id' => $event->id ), 'created_at', 'DESC' ); ?>
                        <tr id="pb-bids-<?php echo esc_attr( $event->id ); ?>" class="pb-bids-details" style="display: none;">
                            <td colspan="10">
                                <div class="pb-detail-panel">
                                    <h4><?php esc_html_e( 'Bids Received', 'peanut-booker' ); ?></h4>
                                    <table class="pb-inner-table">
                                        <thead>
                                            <tr>
                                                <th><?php esc_html_e( 'Performer', 'peanut-booker' ); ?></th>
                                                <th><?php esc_html_e( 'Bid Amount', 'peanut-booker' ); ?></th>
                                                <th><?php esc_html_e( 'Message', 'peanut-booker' ); ?></th>
                                                <th><?php esc_html_e( 'Submitted', 'peanut-booker' ); ?></th>
                                                <th><?php esc_html_e( 'Status', 'peanut-booker' ); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ( $bids as $bid ) : ?>
                                                <?php $bidder = get_userdata( $bid->performer_user_id ); ?>
                                                <tr>
                                                    <td><?php echo $bidder ? esc_html( $bidder->display_name ) : '—'; ?></td>
                                                    <td><?php echo wc_price( $bid->bid_amount ); ?></td>
                                                    <td><?php echo esc_html( wp_trim_words( $bid->message, 15 ) ); ?></td>
                                                    <td><?php echo esc_html( human_time_diff( strtotime( $bid->created_at ) ) . ' ago' ); ?></td>
                                                    <td>
                                                        <span class="pb-status pb-status-<?php echo esc_attr( $bid->status ); ?>">
                                                            <?php echo esc_html( ucfirst( $bid->status ) ); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
