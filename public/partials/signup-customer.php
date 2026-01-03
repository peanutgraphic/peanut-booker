<?php
/**
 * Customer signup template.
 *
 * @package Peanut_Booker
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

$redirect_url = home_url( '/performer-directory/' );
?>
<div class="pb-signup pb-signup-customer">
    <div class="pb-signup-header">
        <h2><?php esc_html_e( 'Create an Account', 'peanut-booker' ); ?></h2>
        <p><?php esc_html_e( 'Sign up to book amazing performers for your events.', 'peanut-booker' ); ?></p>
    </div>

    <?php if ( isset( $_GET['pb_auth_error'] ) ) : ?>
        <div class="pb-message pb-message-error">
            <?php echo esc_html( urldecode( sanitize_text_field( wp_unslash( $_GET['pb_auth_error'] ) ) ) ); ?>
        </div>
    <?php endif; ?>

    <?php
    // Show Google signup button if enabled.
    echo Peanut_Booker_Google_Auth::render_button( 'signup_customer', $redirect_url, __( 'Sign up with Google', 'peanut-booker' ) );

    if ( Peanut_Booker_Google_Auth::is_enabled() ) :
    ?>
        <div class="pb-social-divider">
            <span><?php esc_html_e( 'or', 'peanut-booker' ); ?></span>
        </div>
    <?php endif; ?>

    <form class="pb-signup-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <?php wp_nonce_field( 'pb_customer_signup', 'pb_signup_nonce' ); ?>
        <input type="hidden" name="action" value="pb_customer_signup">
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
                <?php esc_html_e( 'Create Account', 'peanut-booker' ); ?>
            </button>
        </div>
    </form>

    <div class="pb-signup-footer">
        <p>
            <?php esc_html_e( 'Already have an account?', 'peanut-booker' ); ?>
            <a href="<?php echo esc_url( wp_login_url( $redirect_url ) ); ?>"><?php esc_html_e( 'Log in', 'peanut-booker' ); ?></a>
        </p>
        <p>
            <?php esc_html_e( 'Are you a performer?', 'peanut-booker' ); ?>
            <a href="<?php echo esc_url( home_url( '/performer-signup/' ) ); ?>"><?php esc_html_e( 'Sign up as a performer', 'peanut-booker' ); ?></a>
        </p>
    </div>

    <div class="pb-signup-benefits">
        <h3><?php esc_html_e( 'Why Create an Account?', 'peanut-booker' ); ?></h3>
        <ul>
            <li><?php esc_html_e( 'Browse and book from our talented performer directory', 'peanut-booker' ); ?></li>
            <li><?php esc_html_e( 'Manage all your bookings in one place', 'peanut-booker' ); ?></li>
            <li><?php esc_html_e( 'Secure payment processing with escrow protection', 'peanut-booker' ); ?></li>
            <li><?php esc_html_e( 'Post events to receive bids from performers', 'peanut-booker' ); ?></li>
            <li><?php esc_html_e( 'Leave reviews to help the community', 'peanut-booker' ); ?></li>
        </ul>
    </div>
</div>
