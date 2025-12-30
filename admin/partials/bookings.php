<?php
/**
 * Admin bookings list template.
 *
 * @package Peanut_Booker
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

$current_status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
?>

<div class="wrap pb-admin-bookings">
    <h1><?php esc_html_e( 'Bookings', 'peanut-booker' ); ?></h1>

    <ul class="subsubsub">
        <li>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-bookings' ) ); ?>" <?php echo empty( $current_status ) ? 'class="current"' : ''; ?>>
                <?php esc_html_e( 'All', 'peanut-booker' ); ?>
            </a> |
        </li>
        <li>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-bookings&status=pending' ) ); ?>" <?php echo 'pending' === $current_status ? 'class="current"' : ''; ?>>
                <?php esc_html_e( 'Pending', 'peanut-booker' ); ?>
            </a> |
        </li>
        <li>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-bookings&status=confirmed' ) ); ?>" <?php echo 'confirmed' === $current_status ? 'class="current"' : ''; ?>>
                <?php esc_html_e( 'Confirmed', 'peanut-booker' ); ?>
            </a> |
        </li>
        <li>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-bookings&status=completed' ) ); ?>" <?php echo 'completed' === $current_status ? 'class="current"' : ''; ?>>
                <?php esc_html_e( 'Completed', 'peanut-booker' ); ?>
            </a> |
        </li>
        <li>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-bookings&status=cancelled' ) ); ?>" <?php echo 'cancelled' === $current_status ? 'class="current"' : ''; ?>>
                <?php esc_html_e( 'Cancelled', 'peanut-booker' ); ?>
            </a>
        </li>
    </ul>

    <div class="pb-admin-filters" style="clear: both; margin-top: 20px;">
        <input type="date" id="pb-filter-start" placeholder="<?php esc_attr_e( 'Start Date', 'peanut-booker' ); ?>" class="pb-date-filter">
        <input type="date" id="pb-filter-end" placeholder="<?php esc_attr_e( 'End Date', 'peanut-booker' ); ?>" class="pb-date-filter">
        <button class="button pb-export-btn" data-export-type="bookings" data-export-format="csv">
            <?php esc_html_e( 'Export CSV', 'peanut-booker' ); ?>
        </button>
    </div>

    <?php if ( empty( $bookings ) ) : ?>
        <div class="pb-empty-state">
            <h3><?php esc_html_e( 'No bookings found', 'peanut-booker' ); ?></h3>
            <p><?php esc_html_e( 'Bookings will appear here once customers make reservations.', 'peanut-booker' ); ?></p>
        </div>
    <?php else : ?>
        <table class="pb-admin-table widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'ID', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Performer', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Customer', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Event Date', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Location', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Total', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Commission', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Escrow', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'peanut-booker' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $bookings as $booking ) : ?>
                    <?php
                    // Get performer user from performer_id (which references pb_performers table).
                    $performer_data = Peanut_Booker_Database::get_row( 'performers', array( 'id' => $booking->performer_id ) );
                    $performer      = $performer_data ? get_userdata( $performer_data->user_id ) : null;
                    // customer_id is the user ID directly.
                    $customer       = get_userdata( $booking->customer_id );
                    ?>
                    <tr>
                        <td>
                            <strong>#<?php echo esc_html( $booking->id ); ?></strong>
                            <?php if ( $booking->wc_order_id ) : ?>
                                <br>
                                <small>
                                    <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $booking->wc_order_id . '&action=edit' ) ); ?>">
                                        <?php printf( esc_html__( 'Order #%d', 'peanut-booker' ), $booking->wc_order_id ); ?>
                                    </a>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( $performer ) : ?>
                                <?php echo esc_html( $performer->display_name ); ?>
                                <br><small><?php echo esc_html( $performer->user_email ); ?></small>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( $customer ) : ?>
                                <?php echo esc_html( $customer->display_name ); ?>
                                <br><small><?php echo esc_html( $customer->user_email ); ?></small>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->event_date ) ) ); ?>
                            <?php if ( $booking->event_start_time ) : ?>
                                <br><small><?php echo esc_html( $booking->event_start_time ); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo esc_html( $booking->event_location ?: '—' ); ?>
                        </td>
                        <td><?php echo wc_price( $booking->total_amount ); ?></td>
                        <td><?php echo wc_price( $booking->platform_commission ); ?></td>
                        <td>
                            <span class="pb-status pb-status-<?php echo esc_attr( $booking->booking_status ); ?>">
                                <?php echo esc_html( ucfirst( $booking->booking_status ) ); ?>
                            </span>
                        </td>
                        <td>
                            <span class="pb-status pb-status-<?php echo esc_attr( str_replace( '_', '-', $booking->escrow_status ) ); ?>">
                                <?php echo esc_html( ucwords( str_replace( '_', ' ', $booking->escrow_status ) ) ); ?>
                            </span>
                        </td>
                        <td>
                            <div class="pb-row-actions">
                                <a href="#" class="pb-toggle-details" data-target="#pb-booking-<?php echo esc_attr( $booking->id ); ?>">
                                    <?php esc_html_e( 'Details', 'peanut-booker' ); ?>
                                </a>
                                <?php if ( 'completed' === $booking->booking_status && in_array( $booking->escrow_status, array( 'deposit_held', 'full_held' ), true ) ) : ?>
                                    | <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-payouts' ) ); ?>">
                                        <?php esc_html_e( 'Release Payout', 'peanut-booker' ); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <tr id="pb-booking-<?php echo esc_attr( $booking->id ); ?>" class="pb-booking-details" style="display: none;">
                        <td colspan="10">
                            <div class="pb-detail-grid">
                                <div>
                                    <h4><?php esc_html_e( 'Event Details', 'peanut-booker' ); ?></h4>
                                    <p><strong><?php esc_html_e( 'Description:', 'peanut-booker' ); ?></strong><br>
                                    <?php echo esc_html( $booking->event_description ?: __( 'No description provided.', 'peanut-booker' ) ); ?></p>
                                    <p><strong><?php esc_html_e( 'Duration:', 'peanut-booker' ); ?></strong> <?php echo esc_html( $booking->duration_hours ); ?> <?php esc_html_e( 'hours', 'peanut-booker' ); ?></p>
                                </div>
                                <div>
                                    <h4><?php esc_html_e( 'Payment Breakdown', 'peanut-booker' ); ?></h4>
                                    <p><strong><?php esc_html_e( 'Deposit:', 'peanut-booker' ); ?></strong> <?php echo wc_price( $booking->deposit_amount ); ?></p>
                                    <p><strong><?php esc_html_e( 'Remaining:', 'peanut-booker' ); ?></strong> <?php echo wc_price( $booking->total_amount - $booking->deposit_amount ); ?></p>
                                    <p><strong><?php esc_html_e( 'Performer Payout:', 'peanut-booker' ); ?></strong> <?php echo wc_price( $booking->performer_payout ); ?></p>
                                </div>
                                <div>
                                    <h4><?php esc_html_e( 'Timeline', 'peanut-booker' ); ?></h4>
                                    <p><strong><?php esc_html_e( 'Created:', 'peanut-booker' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $booking->created_at ) ) ); ?></p>
                                    <?php if ( $booking->confirmed_at ) : ?>
                                        <p><strong><?php esc_html_e( 'Confirmed:', 'peanut-booker' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $booking->confirmed_at ) ) ); ?></p>
                                    <?php endif; ?>
                                    <?php if ( $booking->completion_date ) : ?>
                                        <p><strong><?php esc_html_e( 'Completed:', 'peanut-booker' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $booking->completion_date ) ) ); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
