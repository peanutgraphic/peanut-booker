<?php
/**
 * Admin payouts template.
 *
 * @package Peanut_Booker
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}
?>

<div class="wrap pb-admin-payouts">
    <h1><?php esc_html_e( 'Performer Payouts', 'peanut-booker' ); ?></h1>

    <div class="pb-stats-grid" style="margin-bottom: 30px;">
        <?php
        global $wpdb;
        $table = $wpdb->prefix . 'pb_bookings';

        $total_pending = $wpdb->get_var(
            "SELECT SUM(performer_payout) FROM $table
            WHERE booking_status = 'completed'
            AND escrow_status IN ('deposit_held', 'full_held')"
        );

        $total_released = $wpdb->get_var(
            "SELECT SUM(performer_payout) FROM $table
            WHERE escrow_status = 'released'"
        );
        ?>
        <div class="pb-stat-card pb-alert">
            <h3><?php esc_html_e( 'Pending Payouts', 'peanut-booker' ); ?></h3>
            <div class="pb-stat-value pb-currency"><?php echo esc_html( number_format( floatval( $total_pending ), 2 ) ); ?></div>
            <p><?php echo esc_html( count( $pending_payouts ) ); ?> <?php esc_html_e( 'bookings ready', 'peanut-booker' ); ?></p>
        </div>
        <div class="pb-stat-card">
            <h3><?php esc_html_e( 'Total Released', 'peanut-booker' ); ?></h3>
            <div class="pb-stat-value pb-currency"><?php echo esc_html( number_format( floatval( $total_released ), 2 ) ); ?></div>
        </div>
    </div>

    <div class="pb-settings-section">
        <h2><?php esc_html_e( 'Bookings Ready for Payout', 'peanut-booker' ); ?></h2>
        <p class="description">
            <?php esc_html_e( 'These bookings have been completed and funds are ready to be released to performers.', 'peanut-booker' ); ?>
        </p>

        <?php if ( empty( $pending_payouts ) ) : ?>
            <div class="pb-empty-state">
                <h3><?php esc_html_e( 'No pending payouts', 'peanut-booker' ); ?></h3>
                <p><?php esc_html_e( 'All completed bookings have been paid out.', 'peanut-booker' ); ?></p>
            </div>
        <?php else : ?>
            <table class="pb-admin-table widefat">
                <thead>
                    <tr>
                        <th class="check-column"><input type="checkbox" id="pb-select-all-payouts"></th>
                        <th><?php esc_html_e( 'Booking', 'peanut-booker' ); ?></th>
                        <th><?php esc_html_e( 'Performer', 'peanut-booker' ); ?></th>
                        <th><?php esc_html_e( 'Event Date', 'peanut-booker' ); ?></th>
                        <th><?php esc_html_e( 'Completed', 'peanut-booker' ); ?></th>
                        <th><?php esc_html_e( 'Total', 'peanut-booker' ); ?></th>
                        <th><?php esc_html_e( 'Commission', 'peanut-booker' ); ?></th>
                        <th><?php esc_html_e( 'Payout Amount', 'peanut-booker' ); ?></th>
                        <th><?php esc_html_e( 'Escrow Status', 'peanut-booker' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'peanut-booker' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $pending_payouts as $booking ) : ?>
                        <?php
                        $performer = get_userdata( $booking->performer_user_id );
                        $days_since_completion = $booking->completion_date
                            ? floor( ( time() - strtotime( $booking->completion_date ) ) / DAY_IN_SECONDS )
                            : 0;

                        // Get settings for auto-release.
                        $settings = get_option( 'peanut_booker_settings', array() );
                        $auto_release_days = $settings['escrow_auto_release_days'] ?? 7;
                        $auto_release_eligible = $days_since_completion >= $auto_release_days;
                        ?>
                        <tr <?php echo $auto_release_eligible ? 'class="pb-auto-release-eligible"' : ''; ?>>
                            <td><input type="checkbox" class="pb-payout-checkbox" value="<?php echo esc_attr( $booking->id ); ?>"></td>
                            <td>
                                <strong>#<?php echo esc_html( $booking->id ); ?></strong>
                                <?php if ( $booking->wc_order_id ) : ?>
                                    <br><small>Order #<?php echo esc_html( $booking->wc_order_id ); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ( $performer ) : ?>
                                    <strong><?php echo esc_html( $performer->display_name ); ?></strong>
                                    <br><small><?php echo esc_html( $performer->user_email ); ?></small>
                                <?php else : ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->event_date ) ) ); ?></td>
                            <td>
                                <?php
                                if ( $booking->completion_date ) {
                                    echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->completion_date ) ) );
                                    echo '<br><small>' . esc_html( $days_since_completion ) . ' ' . esc_html__( 'days ago', 'peanut-booker' ) . '</small>';
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                            <td><?php echo wc_price( $booking->total_amount ); ?></td>
                            <td>
                                <?php echo wc_price( $booking->platform_commission ); ?>
                                <br><small><?php echo esc_html( round( ( $booking->platform_commission / $booking->total_amount ) * 100 ) ); ?>%</small>
                            </td>
                            <td>
                                <strong><?php echo wc_price( $booking->performer_payout ); ?></strong>
                            </td>
                            <td>
                                <span class="pb-status pb-status-<?php echo esc_attr( str_replace( '_', '-', $booking->escrow_status ) ); ?>">
                                    <?php echo esc_html( ucwords( str_replace( '_', ' ', $booking->escrow_status ) ) ); ?>
                                </span>
                                <?php if ( $auto_release_eligible ) : ?>
                                    <br><small class="pb-auto-release-note">
                                        <?php esc_html_e( 'Auto-release eligible', 'peanut-booker' ); ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="pb-payout-actions">
                                    <button class="pb-payout-btn pb-release pb-release-payout" data-booking-id="<?php echo esc_attr( $booking->id ); ?>">
                                        <?php esc_html_e( 'Release', 'peanut-booker' ); ?>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" style="text-align: right;"><strong><?php esc_html_e( 'Total Pending:', 'peanut-booker' ); ?></strong></td>
                        <td colspan="3"><strong><?php echo wc_price( $total_pending ); ?></strong></td>
                    </tr>
                </tfoot>
            </table>

            <div class="pb-bulk-payout-actions" style="margin-top: 20px;">
                <button class="button button-primary pb-bulk-release-selected">
                    <?php esc_html_e( 'Release Selected Payouts', 'peanut-booker' ); ?>
                </button>
                <button class="button pb-bulk-release-eligible" data-confirm="<?php esc_attr_e( 'Release all auto-release eligible payouts?', 'peanut-booker' ); ?>">
                    <?php esc_html_e( 'Release All Eligible', 'peanut-booker' ); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <div class="pb-settings-section" style="margin-top: 30px;">
        <h2><?php esc_html_e( 'Payout Settings', 'peanut-booker' ); ?></h2>
        <p>
            <strong><?php esc_html_e( 'Auto-Release Days:', 'peanut-booker' ); ?></strong>
            <?php
            $settings = get_option( 'peanut_booker_settings', array() );
            echo esc_html( $settings['escrow_auto_release_days'] ?? 7 );
            ?>
            <?php esc_html_e( 'days after event completion', 'peanut-booker' ); ?>
            <br>
            <small><?php esc_html_e( 'Change this in Settings > Booking Settings', 'peanut-booker' ); ?></small>
        </p>
    </div>
</div>
