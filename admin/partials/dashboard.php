<?php
/**
 * Admin dashboard template.
 *
 * @package Peanut_Booker
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}
?>

<div class="wrap pb-admin-dashboard">
    <h1><?php esc_html_e( 'Peanut Booker Dashboard', 'peanut-booker' ); ?></h1>

    <?php if ( Peanut_Booker_Demo_Data::is_demo_mode() ) : ?>
        <div class="pb-demo-notice">
            <span class="dashicons dashicons-info"></span>
            <p>
                <strong><?php esc_html_e( 'Demo Mode Active', 'peanut-booker' ); ?></strong> -
                <?php esc_html_e( 'The site is populated with test data for demonstration purposes.', 'peanut-booker' ); ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-demo' ) ); ?>">
                    <?php esc_html_e( 'Manage Demo Mode', 'peanut-booker' ); ?>
                </a>
            </p>
        </div>
    <?php endif; ?>

    <div class="pb-stats-grid">
        <div class="pb-stat-card">
            <h3><?php esc_html_e( 'Total Performers', 'peanut-booker' ); ?></h3>
            <div class="pb-stat-value"><?php echo esc_html( number_format( $total_performers ) ); ?></div>
            <p class="pb-stat-detail">
                <?php
                printf(
                    /* translators: %d: number of pro performers */
                    esc_html__( '%d Pro', 'peanut-booker' ),
                    $pro_performers
                );
                ?>
            </p>
        </div>

        <div class="pb-stat-card">
            <h3><?php esc_html_e( 'Total Bookings', 'peanut-booker' ); ?></h3>
            <div class="pb-stat-value"><?php echo esc_html( number_format( $total_bookings ) ); ?></div>
        </div>

        <div class="pb-stat-card <?php echo $pending_bookings > 0 ? 'pb-alert' : ''; ?>">
            <h3><?php esc_html_e( 'Pending Bookings', 'peanut-booker' ); ?></h3>
            <div class="pb-stat-value"><?php echo esc_html( number_format( $pending_bookings ) ); ?></div>
            <?php if ( $pending_bookings > 0 ) : ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-bookings&status=pending' ) ); ?>" class="pb-stat-link">
                    <?php esc_html_e( 'View All', 'peanut-booker' ); ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="pb-stat-card">
            <h3><?php esc_html_e( 'Total Revenue', 'peanut-booker' ); ?></h3>
            <div class="pb-stat-value pb-currency"><?php echo esc_html( number_format( floatval( $total_revenue ), 2 ) ); ?></div>
        </div>

        <div class="pb-stat-card">
            <h3><?php esc_html_e( 'Platform Commission', 'peanut-booker' ); ?></h3>
            <div class="pb-stat-value pb-currency"><?php echo esc_html( number_format( floatval( $commission ), 2 ) ); ?></div>
        </div>

        <div class="pb-stat-card <?php echo $pending_reviews > 0 ? 'pb-alert' : ''; ?>">
            <h3><?php esc_html_e( 'Reviews Needing Arbitration', 'peanut-booker' ); ?></h3>
            <div class="pb-stat-value"><?php echo esc_html( $pending_reviews ); ?></div>
            <?php if ( $pending_reviews > 0 ) : ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-reviews&tab=flagged' ) ); ?>" class="pb-stat-link">
                    <?php esc_html_e( 'Review Now', 'peanut-booker' ); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="pb-dashboard-grid">
        <div class="pb-dashboard-main">
            <div class="pb-quick-actions">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-performers' ) ); ?>" class="pb-action-btn">
                    <span class="dashicons dashicons-groups"></span>
                    <?php esc_html_e( 'Manage Performers', 'peanut-booker' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-bookings' ) ); ?>" class="pb-action-btn">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <?php esc_html_e( 'View Bookings', 'peanut-booker' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-payouts' ) ); ?>" class="pb-action-btn pb-primary">
                    <span class="dashicons dashicons-money-alt"></span>
                    <?php esc_html_e( 'Process Payouts', 'peanut-booker' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-settings' ) ); ?>" class="pb-action-btn">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php esc_html_e( 'Settings', 'peanut-booker' ); ?>
                </a>
            </div>

            <?php
            // Recent bookings.
            $recent_bookings = Peanut_Booker_Database::get_results( 'bookings', array(), 'created_at', 'DESC', 5 );
            if ( ! empty( $recent_bookings ) ) :
            ?>
                <div class="pb-recent-bookings">
                    <h2><?php esc_html_e( 'Recent Bookings', 'peanut-booker' ); ?></h2>
                    <table class="pb-admin-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'ID', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Performer', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Customer', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Date', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Status', 'peanut-booker' ); ?></th>
                                <th><?php esc_html_e( 'Amount', 'peanut-booker' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $recent_bookings as $booking ) : ?>
                                <?php
                                $performer = get_userdata( $booking->performer_user_id );
                                $customer  = get_userdata( $booking->customer_user_id );
                                ?>
                                <tr>
                                    <td>#<?php echo esc_html( $booking->id ); ?></td>
                                    <td><?php echo $performer ? esc_html( $performer->display_name ) : '—'; ?></td>
                                    <td><?php echo $customer ? esc_html( $customer->display_name ) : '—'; ?></td>
                                    <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->event_date ) ) ); ?></td>
                                    <td><span class="pb-status pb-status-<?php echo esc_attr( $booking->booking_status ); ?>"><?php echo esc_html( ucfirst( $booking->booking_status ) ); ?></span></td>
                                    <td><?php echo wc_price( $booking->total_amount ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <aside class="pb-dashboard-sidebar">
            <div class="pb-recent-activity">
                <h2><?php esc_html_e( 'Recent Activity', 'peanut-booker' ); ?></h2>
                <ul class="pb-activity-list">
                    <?php
                    // Get recent activity (mock data - would come from activity log).
                    $activities = array();

                    // Recent performers.
                    $new_performers = Peanut_Booker_Database::get_results( 'performers', array(), 'created_at', 'DESC', 3 );
                    foreach ( $new_performers as $p ) {
                        $user = get_userdata( $p->user_id );
                        if ( $user ) {
                            $activities[] = array(
                                'icon'    => '👤',
                                'message' => sprintf( __( '%s joined as a performer', 'peanut-booker' ), '<strong>' . esc_html( $user->display_name ) . '</strong>' ),
                                'time'    => $p->created_at,
                            );
                        }
                    }

                    // Recent bookings.
                    foreach ( array_slice( $recent_bookings, 0, 3 ) as $b ) {
                        $activities[] = array(
                            'icon'    => '📅',
                            'message' => sprintf( __( 'New booking #%d created', 'peanut-booker' ), $b->id ),
                            'time'    => $b->created_at,
                        );
                    }

                    // Sort by time.
                    usort( $activities, function( $a, $b ) {
                        return strtotime( $b['time'] ) - strtotime( $a['time'] );
                    } );

                    $activities = array_slice( $activities, 0, 5 );

                    if ( empty( $activities ) ) :
                    ?>
                        <li class="pb-activity-item">
                            <div class="pb-activity-content">
                                <?php esc_html_e( 'No recent activity.', 'peanut-booker' ); ?>
                            </div>
                        </li>
                    <?php else : ?>
                        <?php foreach ( $activities as $activity ) : ?>
                            <li class="pb-activity-item">
                                <div class="pb-activity-icon"><?php echo esc_html( $activity['icon'] ); ?></div>
                                <div class="pb-activity-content">
                                    <p><?php echo wp_kses_post( $activity['message'] ); ?></p>
                                    <span class="pb-activity-time"><?php echo esc_html( human_time_diff( strtotime( $activity['time'] ) ) . ' ago' ); ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </aside>
    </div>
</div>
