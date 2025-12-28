<?php
/**
 * Admin demo mode template.
 *
 * @package Peanut_Booker
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

$is_demo_mode = Peanut_Booker_Demo_Data::is_demo_mode();
$demo_ids     = get_option( 'peanut_booker_demo_data_ids', array() );
?>

<div class="wrap pb-admin-demo">
    <h1><?php esc_html_e( 'Demo / Test Mode', 'peanut-booker' ); ?></h1>

    <div class="pb-demo-status <?php echo $is_demo_mode ? 'pb-demo-active' : 'pb-demo-inactive'; ?>">
        <div class="pb-demo-status-icon">
            <?php if ( $is_demo_mode ) : ?>
                <span class="dashicons dashicons-yes-alt"></span>
            <?php else : ?>
                <span class="dashicons dashicons-marker"></span>
            <?php endif; ?>
        </div>
        <div class="pb-demo-status-text">
            <h2>
                <?php
                if ( $is_demo_mode ) {
                    esc_html_e( 'Demo Mode is ACTIVE', 'peanut-booker' );
                } else {
                    esc_html_e( 'Demo Mode is OFF', 'peanut-booker' );
                }
                ?>
            </h2>
            <p>
                <?php
                if ( $is_demo_mode ) {
                    esc_html_e( 'Test data has been generated. Your site is ready for demonstrations.', 'peanut-booker' );
                } else {
                    esc_html_e( 'Enable demo mode to populate the site with realistic test data for demonstrations.', 'peanut-booker' );
                }
                ?>
            </p>
        </div>
    </div>

    <?php if ( $is_demo_mode ) : ?>
        <div class="pb-demo-info pb-settings-section">
            <h2><?php esc_html_e( 'Demo Data Summary', 'peanut-booker' ); ?></h2>

            <div class="pb-demo-stats">
                <div class="pb-demo-stat">
                    <span class="pb-demo-stat-number">
                        <?php echo esc_html( count( $demo_ids['performer_user_ids'] ?? array() ) ); ?>
                    </span>
                    <span class="pb-demo-stat-label"><?php esc_html_e( 'Demo Performers', 'peanut-booker' ); ?></span>
                </div>
                <div class="pb-demo-stat">
                    <span class="pb-demo-stat-number">
                        <?php echo esc_html( count( $demo_ids['customer_user_ids'] ?? array() ) ); ?>
                    </span>
                    <span class="pb-demo-stat-label"><?php esc_html_e( 'Demo Customers', 'peanut-booker' ); ?></span>
                </div>
                <div class="pb-demo-stat">
                    <span class="pb-demo-stat-number">
                        <?php echo esc_html( Peanut_Booker_Database::count( 'bookings' ) ); ?>
                    </span>
                    <span class="pb-demo-stat-label"><?php esc_html_e( 'Bookings', 'peanut-booker' ); ?></span>
                </div>
                <div class="pb-demo-stat">
                    <span class="pb-demo-stat-number">
                        <?php echo esc_html( Peanut_Booker_Database::count( 'reviews' ) ); ?>
                    </span>
                    <span class="pb-demo-stat-label"><?php esc_html_e( 'Reviews', 'peanut-booker' ); ?></span>
                </div>
                <div class="pb-demo-stat">
                    <span class="pb-demo-stat-number">
                        <?php echo esc_html( Peanut_Booker_Database::count( 'events' ) ); ?>
                    </span>
                    <span class="pb-demo-stat-label"><?php esc_html_e( 'Market Events', 'peanut-booker' ); ?></span>
                </div>
            </div>

            <h3><?php esc_html_e( 'Demo Performer Accounts', 'peanut-booker' ); ?></h3>
            <p class="description"><?php esc_html_e( 'Use these accounts to test performer functionality:', 'peanut-booker' ); ?></p>

            <table class="pb-admin-table widefat" style="max-width: 800px;">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Name', 'peanut-booker' ); ?></th>
                        <th><?php esc_html_e( 'Category', 'peanut-booker' ); ?></th>
                        <th><?php esc_html_e( 'Tier', 'peanut-booker' ); ?></th>
                        <th><?php esc_html_e( 'Profile', 'peanut-booker' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ( $demo_ids['performer_user_ids'] ?? array() as $user_id ) :
                        $user      = get_userdata( $user_id );
                        $performer = Peanut_Booker_Database::get_row( 'performers', array( 'user_id' => $user_id ) );
                        if ( ! $user || ! $performer ) {
                            continue;
                        }
                        $categories = wp_get_object_terms( $performer->profile_id, 'pb_performer_category', array( 'fields' => 'names' ) );
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html( $user->display_name ); ?></strong>
                                <br><small><?php echo esc_html( $user->user_email ); ?></small>
                            </td>
                            <td><?php echo esc_html( implode( ', ', $categories ) ); ?></td>
                            <td>
                                <span class="pb-tier pb-tier-<?php echo esc_attr( $performer->tier ); ?>">
                                    <?php echo esc_html( ucfirst( $performer->tier ) ); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo esc_url( get_permalink( $performer->profile_id ) ); ?>" target="_blank">
                                    <?php esc_html_e( 'View', 'peanut-booker' ); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h3 style="margin-top: 30px;"><?php esc_html_e( 'Demo Customer Accounts', 'peanut-booker' ); ?></h3>
            <p class="description"><?php esc_html_e( 'Use these accounts to test customer functionality:', 'peanut-booker' ); ?></p>

            <table class="pb-admin-table widefat" style="max-width: 500px;">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Name', 'peanut-booker' ); ?></th>
                        <th><?php esc_html_e( 'Email', 'peanut-booker' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ( $demo_ids['customer_user_ids'] ?? array() as $user_id ) :
                        $user = get_userdata( $user_id );
                        if ( ! $user ) {
                            continue;
                        }
                    ?>
                        <tr>
                            <td><strong><?php echo esc_html( $user->display_name ); ?></strong></td>
                            <td><?php echo esc_html( $user->user_email ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="pb-demo-note" style="margin-top: 30px; padding: 15px; background: #fef3c7; border-radius: 8px;">
                <strong><?php esc_html_e( 'Note:', 'peanut-booker' ); ?></strong>
                <?php esc_html_e( 'Demo accounts use @demo.peanutbooker.test email addresses which are not real. To log in as a demo user, use the "Switch User" functionality or reset their password from the Users screen.', 'peanut-booker' ); ?>
            </div>
        </div>

        <div class="pb-demo-actions pb-settings-section">
            <h2><?php esc_html_e( 'Quick Links for Testing', 'peanut-booker' ); ?></h2>

            <div class="pb-quick-actions" style="flex-wrap: wrap;">
                <?php
                $directory_page = get_option( 'pb_performer_directory_page' );
                $market_page    = get_option( 'pb_market_page' );
                $dashboard_page = get_option( 'pb_dashboard_page' );
                ?>

                <?php if ( $directory_page ) : ?>
                    <a href="<?php echo esc_url( get_permalink( $directory_page ) ); ?>" class="pb-action-btn" target="_blank">
                        <span class="dashicons dashicons-groups"></span>
                        <?php esc_html_e( 'Performer Directory', 'peanut-booker' ); ?>
                    </a>
                <?php endif; ?>

                <?php if ( $market_page ) : ?>
                    <a href="<?php echo esc_url( get_permalink( $market_page ) ); ?>" class="pb-action-btn" target="_blank">
                        <span class="dashicons dashicons-megaphone"></span>
                        <?php esc_html_e( 'Market', 'peanut-booker' ); ?>
                    </a>
                <?php endif; ?>

                <?php if ( $dashboard_page ) : ?>
                    <a href="<?php echo esc_url( get_permalink( $dashboard_page ) ); ?>" class="pb-action-btn" target="_blank">
                        <span class="dashicons dashicons-dashboard"></span>
                        <?php esc_html_e( 'User Dashboard', 'peanut-booker' ); ?>
                    </a>
                <?php endif; ?>

                <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-bookings' ) ); ?>" class="pb-action-btn">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <?php esc_html_e( 'View Bookings', 'peanut-booker' ); ?>
                </a>

                <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-reviews' ) ); ?>" class="pb-action-btn">
                    <span class="dashicons dashicons-star-filled"></span>
                    <?php esc_html_e( 'View Reviews', 'peanut-booker' ); ?>
                </a>

                <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-market' ) ); ?>" class="pb-action-btn">
                    <span class="dashicons dashicons-megaphone"></span>
                    <?php esc_html_e( 'View Market Events', 'peanut-booker' ); ?>
                </a>

                <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-payouts' ) ); ?>" class="pb-action-btn">
                    <span class="dashicons dashicons-money-alt"></span>
                    <?php esc_html_e( 'View Payouts', 'peanut-booker' ); ?>
                </a>
            </div>
        </div>

        <div class="pb-demo-disable pb-settings-section">
            <h2><?php esc_html_e( 'Disable Demo Mode', 'peanut-booker' ); ?></h2>
            <p class="description">
                <?php esc_html_e( 'This will remove all demo data including test performers, customers, bookings, reviews, and market events.', 'peanut-booker' ); ?>
            </p>

            <form method="post" id="pb-disable-demo-form">
                <?php wp_nonce_field( 'pb_demo_mode', 'pb_demo_nonce' ); ?>
                <input type="hidden" name="pb_demo_action" value="disable">

                <p>
                    <label>
                        <input type="checkbox" name="confirm_disable" value="1" required>
                        <?php esc_html_e( 'I understand that all demo data will be permanently deleted.', 'peanut-booker' ); ?>
                    </label>
                </p>

                <button type="submit" class="button button-secondary" style="color: #dc2626; border-color: #dc2626;">
                    <?php esc_html_e( 'Disable Demo Mode & Delete Data', 'peanut-booker' ); ?>
                </button>
            </form>
        </div>

    <?php else : ?>

        <div class="pb-demo-enable pb-settings-section">
            <h2><?php esc_html_e( 'Enable Demo Mode', 'peanut-booker' ); ?></h2>

            <div class="pb-demo-features">
                <h3><?php esc_html_e( 'What gets created:', 'peanut-booker' ); ?></h3>
                <ul>
                    <li>
                        <strong><?php esc_html_e( '10 Demo Performers', 'peanut-booker' ); ?></strong>
                        - <?php esc_html_e( 'Various categories (Musicians, DJs, Magicians, Comedians, Speakers, Dancers, Variety Acts)', 'peanut-booker' ); ?>
                    </li>
                    <li>
                        <strong><?php esc_html_e( '6 Demo Customers', 'peanut-booker' ); ?></strong>
                        - <?php esc_html_e( 'Ready to make bookings and post events', 'peanut-booker' ); ?>
                    </li>
                    <li>
                        <strong><?php esc_html_e( '20-30 Bookings', 'peanut-booker' ); ?></strong>
                        - <?php esc_html_e( 'Mix of completed, confirmed, and pending statuses', 'peanut-booker' ); ?>
                    </li>
                    <li>
                        <strong><?php esc_html_e( 'Reviews with Responses', 'peanut-booker' ); ?></strong>
                        - <?php esc_html_e( 'Realistic review content and performer responses', 'peanut-booker' ); ?>
                    </li>
                    <li>
                        <strong><?php esc_html_e( '6 Market Events', 'peanut-booker' ); ?></strong>
                        - <?php esc_html_e( 'With multiple bids from Pro performers', 'peanut-booker' ); ?>
                    </li>
                    <li>
                        <strong><?php esc_html_e( 'Availability Calendars', 'peanut-booker' ); ?></strong>
                        - <?php esc_html_e( 'Pre-populated for all performers', 'peanut-booker' ); ?>
                    </li>
                    <li>
                        <strong><?php esc_html_e( 'Performer Categories', 'peanut-booker' ); ?></strong>
                        - <?php esc_html_e( 'All standard categories created', 'peanut-booker' ); ?>
                    </li>
                </ul>
            </div>

            <div class="pb-demo-note" style="margin: 20px 0; padding: 15px; background: #dbeafe; border-radius: 8px;">
                <strong><?php esc_html_e( 'Safe to Use:', 'peanut-booker' ); ?></strong>
                <?php esc_html_e( 'Demo data is clearly marked and can be completely removed at any time. It will not interfere with real user data.', 'peanut-booker' ); ?>
            </div>

            <form method="post" id="pb-enable-demo-form">
                <?php wp_nonce_field( 'pb_demo_mode', 'pb_demo_nonce' ); ?>
                <input type="hidden" name="pb_demo_action" value="enable">

                <button type="submit" class="button button-primary button-hero">
                    <span class="dashicons dashicons-database-add" style="margin-right: 5px;"></span>
                    <?php esc_html_e( 'Enable Demo Mode & Generate Test Data', 'peanut-booker' ); ?>
                </button>
            </form>
        </div>

    <?php endif; ?>
</div>

<style>
.pb-demo-status {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 30px;
}
.pb-demo-active {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    border: 2px solid #22c55e;
}
.pb-demo-inactive {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    border: 2px solid #9ca3af;
}
.pb-demo-status-icon .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
}
.pb-demo-active .pb-demo-status-icon .dashicons {
    color: #22c55e;
}
.pb-demo-inactive .pb-demo-status-icon .dashicons {
    color: #9ca3af;
}
.pb-demo-status-text h2 {
    margin: 0 0 5px;
}
.pb-demo-status-text p {
    margin: 0;
    color: #4b5563;
}
.pb-demo-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin: 20px 0;
}
.pb-demo-stat {
    text-align: center;
    padding: 20px;
    background: #f9fafb;
    border-radius: 8px;
}
.pb-demo-stat-number {
    display: block;
    font-size: 32px;
    font-weight: 700;
    color: #6366f1;
}
.pb-demo-stat-label {
    display: block;
    font-size: 13px;
    color: #6b7280;
    margin-top: 5px;
}
.pb-demo-features ul {
    list-style: none;
    padding: 0;
}
.pb-demo-features li {
    padding: 10px 0;
    border-bottom: 1px solid #e5e7eb;
}
.pb-demo-features li:last-child {
    border-bottom: none;
}
</style>
