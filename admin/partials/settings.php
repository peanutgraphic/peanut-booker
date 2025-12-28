<?php
/**
 * Admin settings template.
 *
 * @package Peanut_Booker
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

$tabs = array(
    'general'       => __( 'General', 'peanut-booker' ),
    'license'       => __( 'License', 'peanut-booker' ),
    'commission'    => __( 'Commission', 'peanut-booker' ),
    'subscription'  => __( 'Pro Subscription', 'peanut-booker' ),
    'booking'       => __( 'Booking', 'peanut-booker' ),
    'achievements'  => __( 'Achievements', 'peanut-booker' ),
    'google'        => __( 'Google Login', 'peanut-booker' ),
);
?>

<div class="wrap pb-admin-settings">
    <h1><?php esc_html_e( 'Peanut Booker Settings', 'peanut-booker' ); ?></h1>

    <div class="pb-settings-tabs">
        <?php foreach ( $tabs as $tab_key => $tab_label ) : ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-settings&tab=' . $tab_key ) ); ?>" class="<?php echo $active_tab === $tab_key ? 'active' : ''; ?>">
                <?php echo esc_html( $tab_label ); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <form method="post" action="options.php" class="pb-settings-form">
        <?php settings_fields( 'peanut_booker_settings' ); ?>

        <?php if ( 'general' === $active_tab ) : ?>
            <div class="pb-settings-section">
                <h2><?php esc_html_e( 'General Settings', 'peanut-booker' ); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="pb-currency"><?php esc_html_e( 'Currency', 'peanut-booker' ); ?></label>
                        </th>
                        <td>
                            <?php
                            $options = get_option( 'peanut_booker_settings', array() );
                            ?>
                            <input type="text" id="pb-currency" name="peanut_booker_settings[currency]"
                                value="<?php echo esc_attr( $options['currency'] ?? 'USD' ); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e( 'Currency code (uses WooCommerce settings by default).', 'peanut-booker' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'WooCommerce Status', 'peanut-booker' ); ?></th>
                        <td>
                            <?php if ( class_exists( 'WooCommerce' ) ) : ?>
                                <span class="pb-status pb-status-active"><?php esc_html_e( 'Active', 'peanut-booker' ); ?></span>
                                <p class="description"><?php esc_html_e( 'WooCommerce is active. Payments will be processed through WooCommerce.', 'peanut-booker' ); ?></p>
                            <?php else : ?>
                                <span class="pb-status pb-status-cancelled"><?php esc_html_e( 'Not Active', 'peanut-booker' ); ?></span>
                                <p class="description" style="color: #dc2626;">
                                    <?php esc_html_e( 'WooCommerce is required for payment processing. Please install and activate WooCommerce.', 'peanut-booker' ); ?>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Plugin Pages', 'peanut-booker' ); ?></th>
                        <td>
                            <?php
                            $options = get_option( 'peanut_booker_settings', array() );
                            $page_settings = array(
                                'performer_directory_page' => array(
                                    'label'     => __( 'Performer Directory', 'peanut-booker' ),
                                    'shortcode' => '[pb_performer_directory]',
                                ),
                                'market_page'              => array(
                                    'label'     => __( 'Market', 'peanut-booker' ),
                                    'shortcode' => '[pb_market]',
                                ),
                                'dashboard_page'           => array(
                                    'label'     => __( 'Dashboard', 'peanut-booker' ),
                                    'shortcode' => '[pb_my_dashboard]',
                                ),
                                'login_page'               => array(
                                    'label'     => __( 'Login / Sign Up', 'peanut-booker' ),
                                    'shortcode' => '[pb_login]',
                                ),
                                'performer_signup_page'    => array(
                                    'label'     => __( 'Performer Sign Up', 'peanut-booker' ),
                                    'shortcode' => '[pb_performer_signup]',
                                ),
                                'customer_signup_page'     => array(
                                    'label'     => __( 'Customer Sign Up', 'peanut-booker' ),
                                    'shortcode' => '[pb_customer_signup]',
                                ),
                            );

                            $all_pages = get_pages( array( 'post_status' => 'publish' ) );

                            foreach ( $page_settings as $option_key => $page_info ) :
                                $page_id = isset( $options[ $option_key ] ) ? absint( $options[ $option_key ] ) : 0;
                            ?>
                                <p style="margin-bottom: 15px;">
                                    <label for="pb-<?php echo esc_attr( $option_key ); ?>">
                                        <strong><?php echo esc_html( $page_info['label'] ); ?>:</strong>
                                    </label><br>
                                    <select id="pb-<?php echo esc_attr( $option_key ); ?>" name="peanut_booker_settings[<?php echo esc_attr( $option_key ); ?>]" style="min-width: 300px;">
                                        <option value=""><?php esc_html_e( '— Select a page —', 'peanut-booker' ); ?></option>
                                        <?php foreach ( $all_pages as $page ) : ?>
                                            <option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $page_id, $page->ID ); ?>>
                                                <?php echo esc_html( $page->post_title ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ( $page_id ) : ?>
                                        <a href="<?php echo esc_url( get_permalink( $page_id ) ); ?>" target="_blank" class="button button-small"><?php esc_html_e( 'View', 'peanut-booker' ); ?></a>
                                        <a href="<?php echo esc_url( get_edit_post_link( $page_id ) ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'peanut-booker' ); ?></a>
                                    <?php endif; ?>
                                    <br>
                                    <span class="description"><?php printf( esc_html__( 'Add shortcode: %s', 'peanut-booker' ), '<code>' . esc_html( $page_info['shortcode'] ) . '</code>' ); ?></span>
                                </p>
                            <?php endforeach; ?>

                            <p style="margin-top: 20px;">
                                <strong><?php esc_html_e( 'Quick Setup:', 'peanut-booker' ); ?></strong>
                                <button type="button" class="button" id="pb-create-pages"><?php esc_html_e( 'Create All Pages', 'peanut-booker' ); ?></button>
                                <span class="description" style="margin-left: 10px;"><?php esc_html_e( 'Automatically creates pages with shortcodes', 'peanut-booker' ); ?></span>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

        <?php elseif ( 'license' === $active_tab ) : ?>
            <div class="pb-settings-section">
                <h2><?php esc_html_e( 'License & Updates', 'peanut-booker' ); ?></h2>
                <p class="description"><?php esc_html_e( 'Activate your license to receive automatic updates and access premium features.', 'peanut-booker' ); ?></p>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label><?php esc_html_e( 'License Key', 'peanut-booker' ); ?></label>
                        </th>
                        <td>
                            <?php
                            $license = peanut_booker_license();
                            if ( $license ) {
                                $license->render_license_field();
                            } else {
                                echo '<p class="description" style="color: #dc2626;">' . esc_html__( 'License client not initialized.', 'peanut-booker' ) . '</p>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="pb-license-server"><?php esc_html_e( 'License Server', 'peanut-booker' ); ?></label>
                        </th>
                        <td>
                            <?php $server_url = get_option( 'peanut_booker_license_server', 'https://peanutgraphic.com/wp-json/peanut-api/v1' ); ?>
                            <input type="url" id="pb-license-server" name="peanut_booker_license_server"
                                value="<?php echo esc_attr( $server_url ); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e( 'The URL of your Peanut License Server API endpoint.', 'peanut-booker' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Plugin Version', 'peanut-booker' ); ?></th>
                        <td>
                            <code><?php echo esc_html( PEANUT_BOOKER_VERSION ); ?></code>
                            <?php if ( peanut_booker_is_licensed() ) : ?>
                                <span style="color: #059669; margin-left: 10px;">✓ <?php esc_html_e( 'Auto-updates enabled', 'peanut-booker' ); ?></span>
                            <?php else : ?>
                                <span style="color: #d97706; margin-left: 10px;"><?php esc_html_e( 'Activate license for auto-updates', 'peanut-booker' ); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

                <div class="pb-license-info" style="margin-top: 20px; padding: 20px; background: #f0fdf4; border-radius: 8px; border: 1px solid #bbf7d0;">
                    <h4 style="margin-top: 0; color: #166534;"><?php esc_html_e( 'License Benefits', 'peanut-booker' ); ?></h4>
                    <ul style="margin-left: 20px; line-height: 1.8;">
                        <li><?php esc_html_e( 'Automatic plugin updates directly from WordPress admin', 'peanut-booker' ); ?></li>
                        <li><?php esc_html_e( 'Priority support from the Peanut Graphic team', 'peanut-booker' ); ?></li>
                        <li><?php esc_html_e( 'Access to premium features and templates', 'peanut-booker' ); ?></li>
                        <li><?php esc_html_e( 'Integration with Peanut Suite analytics dashboard', 'peanut-booker' ); ?></li>
                    </ul>
                    <p style="margin-bottom: 0;">
                        <a href="https://peanutgraphic.com/peanut-booker" target="_blank" class="button button-secondary">
                            <?php esc_html_e( 'Get a License', 'peanut-booker' ); ?>
                        </a>
                    </p>
                </div>
            </div>

        <?php elseif ( 'commission' === $active_tab ) : ?>
            <div class="pb-settings-section">
                <h2><?php esc_html_e( 'Commission Settings', 'peanut-booker' ); ?></h2>
                <p class="description"><?php esc_html_e( 'Set the platform commission rates for different performer tiers.', 'peanut-booker' ); ?></p>

                <table class="form-table">
                    <?php do_settings_fields( 'pb-settings-commission', 'pb_commission' ); ?>
                </table>

                <div class="pb-commission-preview" style="margin-top: 20px; padding: 15px; background: #f9fafb; border-radius: 8px;">
                    <h4><?php esc_html_e( 'Example Calculation', 'peanut-booker' ); ?></h4>
                    <?php
                    $options = get_option( 'peanut_booker_settings', array() );
                    $free_rate = $options['commission_free_tier'] ?? 15;
                    $pro_rate = $options['commission_pro_tier'] ?? 10;
                    $flat_fee = $options['commission_flat_fee'] ?? 0;
                    ?>
                    <p>
                        <strong><?php esc_html_e( 'For a $100 booking:', 'peanut-booker' ); ?></strong><br>
                        <?php esc_html_e( 'Free Tier:', 'peanut-booker' ); ?> $<?php echo esc_html( number_format( 100 * ( $free_rate / 100 ) + $flat_fee, 2 ) ); ?> <?php esc_html_e( 'commission', 'peanut-booker' ); ?>,
                        <?php esc_html_e( 'Performer gets', 'peanut-booker' ); ?> $<?php echo esc_html( number_format( 100 - ( 100 * ( $free_rate / 100 ) + $flat_fee ), 2 ) ); ?><br>
                        <?php esc_html_e( 'Pro Tier:', 'peanut-booker' ); ?> $<?php echo esc_html( number_format( 100 * ( $pro_rate / 100 ) + $flat_fee, 2 ) ); ?> <?php esc_html_e( 'commission', 'peanut-booker' ); ?>,
                        <?php esc_html_e( 'Performer gets', 'peanut-booker' ); ?> $<?php echo esc_html( number_format( 100 - ( 100 * ( $pro_rate / 100 ) + $flat_fee ), 2 ) ); ?>
                    </p>
                </div>
            </div>

        <?php elseif ( 'subscription' === $active_tab ) : ?>
            <div class="pb-settings-section">
                <h2><?php esc_html_e( 'Pro Subscription Pricing', 'peanut-booker' ); ?></h2>
                <p class="description"><?php esc_html_e( 'Set pricing for Pro performer subscriptions.', 'peanut-booker' ); ?></p>

                <table class="form-table">
                    <?php do_settings_fields( 'pb-settings-subscription', 'pb_subscription' ); ?>
                </table>

                <div class="pb-subscription-info" style="margin-top: 20px; padding: 15px; background: #ede9fe; border-radius: 8px;">
                    <h4><?php esc_html_e( 'Pro Tier Benefits', 'peanut-booker' ); ?></h4>
                    <ul style="margin-left: 20px;">
                        <li><?php esc_html_e( 'Lower commission rates', 'peanut-booker' ); ?></li>
                        <li><?php esc_html_e( 'Unlimited photos and videos on profile', 'peanut-booker' ); ?></li>
                        <li><?php esc_html_e( 'Access to Market bidding system', 'peanut-booker' ); ?></li>
                        <li><?php esc_html_e( 'Priority in search results', 'peanut-booker' ); ?></li>
                        <li><?php esc_html_e( 'Featured performer eligibility', 'peanut-booker' ); ?></li>
                    </ul>
                </div>

                <?php if ( ! class_exists( 'WC_Subscriptions' ) ) : ?>
                    <div class="notice notice-warning inline" style="margin-top: 20px;">
                        <p>
                            <?php esc_html_e( 'WooCommerce Subscriptions plugin is recommended for recurring subscription payments.', 'peanut-booker' ); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ( 'booking' === $active_tab ) : ?>
            <div class="pb-settings-section">
                <h2><?php esc_html_e( 'Booking Settings', 'peanut-booker' ); ?></h2>

                <table class="form-table">
                    <?php do_settings_fields( 'pb-settings-booking', 'pb_booking' ); ?>
                </table>

                <div class="pb-booking-info" style="margin-top: 20px; padding: 15px; background: #dbeafe; border-radius: 8px;">
                    <h4><?php esc_html_e( 'How Deposits Work', 'peanut-booker' ); ?></h4>
                    <p>
                        <?php esc_html_e( 'Performers can set their own deposit percentage within the min/max range you define.', 'peanut-booker' ); ?><br>
                        <?php esc_html_e( 'Deposits are held in escrow until the event is completed.', 'peanut-booker' ); ?>
                    </p>
                    <h4><?php esc_html_e( 'Escrow Auto-Release', 'peanut-booker' ); ?></h4>
                    <p>
                        <?php esc_html_e( 'If the customer does not confirm completion within the specified days, funds are automatically released to the performer.', 'peanut-booker' ); ?>
                    </p>
                </div>
            </div>

        <?php elseif ( 'achievements' === $active_tab ) : ?>
            <div class="pb-settings-section">
                <h2><?php esc_html_e( 'Achievement Thresholds', 'peanut-booker' ); ?></h2>
                <p class="description"><?php esc_html_e( 'Set the point thresholds for each achievement level.', 'peanut-booker' ); ?></p>

                <table class="form-table">
                    <?php do_settings_fields( 'pb-settings-achievements', 'pb_achievements' ); ?>
                </table>

                <div class="pb-achievement-info" style="margin-top: 20px; padding: 15px; background: #fef3c7; border-radius: 8px;">
                    <h4><?php esc_html_e( 'How Points Are Calculated', 'peanut-booker' ); ?></h4>
                    <p><code><?php esc_html_e( 'Points = (Completed Bookings × 10) + (Average Rating × 20) + (Profile Completeness × 0.5)', 'peanut-booker' ); ?></code></p>
                    <h4><?php esc_html_e( 'Achievement Levels', 'peanut-booker' ); ?></h4>
                    <ul style="margin-left: 20px;">
                        <li><span class="pb-level pb-level-bronze"><?php esc_html_e( 'Bronze', 'peanut-booker' ); ?></span> <?php esc_html_e( '0 points and above', 'peanut-booker' ); ?></li>
                        <?php
                        $options = get_option( 'peanut_booker_settings', array() );
                        ?>
                        <li><span class="pb-level pb-level-silver"><?php esc_html_e( 'Silver', 'peanut-booker' ); ?></span> <?php echo esc_html( $options['achievement_silver'] ?? 100 ); ?>+ <?php esc_html_e( 'points', 'peanut-booker' ); ?></li>
                        <li><span class="pb-level pb-level-gold"><?php esc_html_e( 'Gold', 'peanut-booker' ); ?></span> <?php echo esc_html( $options['achievement_gold'] ?? 500 ); ?>+ <?php esc_html_e( 'points', 'peanut-booker' ); ?></li>
                        <li><span class="pb-level pb-level-platinum"><?php esc_html_e( 'Platinum', 'peanut-booker' ); ?></span> <?php echo esc_html( $options['achievement_platinum'] ?? 2000 ); ?>+ <?php esc_html_e( 'points', 'peanut-booker' ); ?></li>
                    </ul>
                </div>
            </div>

        <?php elseif ( 'google' === $active_tab ) : ?>
            <div class="pb-settings-section">
                <h2><?php esc_html_e( 'Google Login Settings', 'peanut-booker' ); ?></h2>
                <p class="description"><?php esc_html_e( 'Enable Google OAuth to allow users to sign up and log in with their Google account.', 'peanut-booker' ); ?></p>

                <?php $options = get_option( 'peanut_booker_settings', array() ); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="pb-google-client-id"><?php esc_html_e( 'Google Client ID', 'peanut-booker' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="pb-google-client-id" name="peanut_booker_settings[google_client_id]"
                                value="<?php echo esc_attr( $options['google_client_id'] ?? '' ); ?>" class="large-text" placeholder="xxxxx.apps.googleusercontent.com">
                            <p class="description"><?php esc_html_e( 'Your Google OAuth Client ID from Google Cloud Console.', 'peanut-booker' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="pb-google-client-secret"><?php esc_html_e( 'Google Client Secret', 'peanut-booker' ); ?></label>
                        </th>
                        <td>
                            <input type="password" id="pb-google-client-secret" name="peanut_booker_settings[google_client_secret]"
                                value="<?php echo esc_attr( $options['google_client_secret'] ?? '' ); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e( 'Your Google OAuth Client Secret.', 'peanut-booker' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'OAuth Redirect URI', 'peanut-booker' ); ?></th>
                        <td>
                            <code style="padding: 8px 12px; display: inline-block; background: #f3f4f6; border-radius: 4px;">
                                <?php echo esc_html( Peanut_Booker_Google_Auth::get_redirect_uri() ); ?>
                            </code>
                            <p class="description">
                                <?php esc_html_e( 'Copy this URI and add it to your Google Cloud Console OAuth credentials as an Authorized Redirect URI.', 'peanut-booker' ); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Status', 'peanut-booker' ); ?></th>
                        <td>
                            <?php if ( Peanut_Booker_Google_Auth::is_enabled() ) : ?>
                                <span class="pb-status pb-status-active" style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 4px;">
                                    <?php esc_html_e( 'Enabled', 'peanut-booker' ); ?>
                                </span>
                                <p class="description"><?php esc_html_e( 'Google login buttons will appear on signup and login pages.', 'peanut-booker' ); ?></p>
                            <?php else : ?>
                                <span class="pb-status pb-status-pending" style="background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 4px;">
                                    <?php esc_html_e( 'Not Configured', 'peanut-booker' ); ?>
                                </span>
                                <p class="description"><?php esc_html_e( 'Enter your Client ID and Secret to enable Google login.', 'peanut-booker' ); ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

                <div class="pb-google-setup-guide" style="margin-top: 20px; padding: 20px; background: #ede9fe; border-radius: 8px;">
                    <h4 style="margin-top: 0;"><?php esc_html_e( 'How to Set Up Google OAuth', 'peanut-booker' ); ?></h4>
                    <ol style="margin-left: 20px; line-height: 1.8;">
                        <li><?php printf(
                            esc_html__( 'Go to the %s', 'peanut-booker' ),
                            '<a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a>'
                        ); ?></li>
                        <li><?php esc_html_e( 'Create a new project or select an existing one.', 'peanut-booker' ); ?></li>
                        <li><?php esc_html_e( 'Go to "APIs & Services" > "Credentials".', 'peanut-booker' ); ?></li>
                        <li><?php esc_html_e( 'Click "Create Credentials" > "OAuth client ID".', 'peanut-booker' ); ?></li>
                        <li><?php esc_html_e( 'Select "Web application" as the application type.', 'peanut-booker' ); ?></li>
                        <li><?php esc_html_e( 'Add the Redirect URI shown above to "Authorized redirect URIs".', 'peanut-booker' ); ?></li>
                        <li><?php esc_html_e( 'Copy the Client ID and Client Secret and paste them above.', 'peanut-booker' ); ?></li>
                    </ol>
                    <p style="margin-bottom: 0;">
                        <strong><?php esc_html_e( 'Note:', 'peanut-booker' ); ?></strong>
                        <?php esc_html_e( 'You may also need to configure the OAuth consent screen before creating credentials.', 'peanut-booker' ); ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <?php submit_button(); ?>
    </form>
</div>
