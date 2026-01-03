<?php
/**
 * Performer signup template.
 *
 * @package Peanut_Booker
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

$redirect_url = home_url( '/dashboard/' );
?>
<div class="pb-signup pb-signup-performer">
    <div class="pb-signup-header">
        <h2><?php esc_html_e( 'Become a Performer', 'peanut-booker' ); ?></h2>
        <p><?php esc_html_e( 'Create your profile and start getting booked for events.', 'peanut-booker' ); ?></p>
    </div>

    <?php if ( isset( $_GET['pb_auth_error'] ) ) : ?>
        <div class="pb-message pb-message-error">
            <?php echo esc_html( urldecode( sanitize_text_field( wp_unslash( $_GET['pb_auth_error'] ) ) ) ); ?>
        </div>
    <?php endif; ?>

    <?php
    // Show Google signup button if enabled.
    echo Peanut_Booker_Google_Auth::render_button( 'signup_performer', $redirect_url, __( 'Sign up with Google', 'peanut-booker' ) );

    if ( Peanut_Booker_Google_Auth::is_enabled() ) :
    ?>
        <div class="pb-social-divider">
            <span><?php esc_html_e( 'or', 'peanut-booker' ); ?></span>
        </div>
    <?php endif; ?>

    <form class="pb-signup-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <?php wp_nonce_field( 'pb_performer_signup', 'pb_signup_nonce' ); ?>
        <input type="hidden" name="action" value="pb_performer_signup">
        <input type="hidden" name="redirect" value="<?php echo esc_attr( $redirect_url ); ?>">

        <div class="pb-form-row">
            <label for="pb-signup-name"><?php esc_html_e( 'Full Name', 'peanut-booker' ); ?> <span class="required">*</span></label>
            <input type="text" id="pb-signup-name" name="display_name" required>
        </div>

        <div class="pb-form-row">
            <label for="pb-signup-email"><?php esc_html_e( 'Email Address', 'peanut-booker' ); ?> <span class="required">*</span></label>
            <input type="email" id="pb-signup-email" name="email" required>
        </div>

        <div class="pb-form-row">
            <label for="pb-signup-username"><?php esc_html_e( 'Username', 'peanut-booker' ); ?> <span class="required">*</span></label>
            <input type="text" id="pb-signup-username" name="username" required>
        </div>

        <div class="pb-form-row">
            <label for="pb-signup-password"><?php esc_html_e( 'Password', 'peanut-booker' ); ?> <span class="required">*</span></label>
            <input type="password" id="pb-signup-password" name="password" required minlength="8">
            <p class="pb-field-hint"><?php esc_html_e( 'Minimum 8 characters.', 'peanut-booker' ); ?></p>
        </div>

        <div class="pb-form-row">
            <label for="pb-signup-category"><?php esc_html_e( 'Primary Category', 'peanut-booker' ); ?> <span class="required">*</span></label>
            <select id="pb-signup-category" name="category" required>
                <option value=""><?php esc_html_e( 'Select a category...', 'peanut-booker' ); ?></option>
                <?php
                $categories = get_terms(
                    array(
                        'taxonomy'   => 'pb_performer_category',
                        'hide_empty' => false,
                    )
                );
                if ( ! is_wp_error( $categories ) ) {
                    foreach ( $categories as $cat ) {
                        printf(
                            '<option value="%s">%s</option>',
                            esc_attr( $cat->term_id ),
                            esc_html( $cat->name )
                        );
                    }
                }
                ?>
            </select>
        </div>

        <div class="pb-form-row pb-checkbox-row">
            <label>
                <input type="checkbox" name="agree_terms" required>
                <?php
                printf(
                    esc_html__( 'I agree to the %sTerms of Service%s and %sPrivacy Policy%s', 'peanut-booker' ),
                    '<a href="' . esc_url( home_url( '/terms/' ) ) . '" target="_blank">',
                    '</a>',
                    '<a href="' . esc_url( home_url( '/privacy/' ) ) . '" target="_blank">',
                    '</a>'
                );
                ?>
            </label>
        </div>

        <div class="pb-form-actions">
            <button type="submit" class="pb-button pb-button-primary pb-button-full">
                <?php esc_html_e( 'Create Performer Account', 'peanut-booker' ); ?>
            </button>
        </div>
    </form>

    <div class="pb-signup-footer">
        <p>
            <?php esc_html_e( 'Already have an account?', 'peanut-booker' ); ?>
            <a href="<?php echo esc_url( wp_login_url( $redirect_url ) ); ?>"><?php esc_html_e( 'Log in', 'peanut-booker' ); ?></a>
        </p>
        <p>
            <?php esc_html_e( 'Looking to book a performer?', 'peanut-booker' ); ?>
            <a href="<?php echo esc_url( home_url( '/customer-signup/' ) ); ?>"><?php esc_html_e( 'Sign up as a customer', 'peanut-booker' ); ?></a>
        </p>
    </div>

    <div class="pb-signup-benefits">
        <h3><?php esc_html_e( 'Why Join as a Performer?', 'peanut-booker' ); ?></h3>
        <ul>
            <li><?php esc_html_e( 'Create a professional profile to showcase your talent', 'peanut-booker' ); ?></li>
            <li><?php esc_html_e( 'Get discovered by customers looking for entertainment', 'peanut-booker' ); ?></li>
            <li><?php esc_html_e( 'Manage your bookings and availability in one place', 'peanut-booker' ); ?></li>
            <li><?php esc_html_e( 'Secure payment processing with escrow protection', 'peanut-booker' ); ?></li>
            <li><?php esc_html_e( 'Build your reputation with reviews and achievements', 'peanut-booker' ); ?></li>
        </ul>
    </div>
</div>
