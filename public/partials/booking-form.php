<?php
/**
 * Booking form template.
 *
 * @package Peanut_Booker
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

// Expects $performer_id, $display_data to be set.
if ( ! isset( $performer_id ) || ! isset( $display_data ) ) {
    return;
}

$hourly_rate  = $display_data['sale_active'] && $display_data['sale_price'] ? $display_data['sale_price'] : $display_data['hourly_rate'];
$deposit_pct  = $display_data['deposit_percentage'];
$min_hours    = 1;
$max_hours    = 12;
?>

<div class="pb-booking-form-wrapper">
    <h2><?php esc_html_e( 'Book This Performer', 'peanut-booker' ); ?></h2>

    <form class="pb-booking-form" data-hourly-rate="<?php echo esc_attr( $hourly_rate ); ?>" data-deposit-percent="<?php echo esc_attr( $deposit_pct ); ?>">
        <?php wp_nonce_field( 'pb_booking_nonce', 'pb_booking_nonce_field' ); ?>
        <input type="hidden" name="performer_id" value="<?php echo esc_attr( $performer_id ); ?>">

        <div class="pb-form-row">
            <label for="pb-event-date"><?php esc_html_e( 'Event Date', 'peanut-booker' ); ?> <span class="required">*</span></label>
            <input type="date" id="pb-event-date" name="event_date" class="pb-booking-date" required
                   min="<?php echo esc_attr( date( 'Y-m-d', strtotime( '+1 day' ) ) ); ?>">
            <span class="pb-availability-status"></span>
        </div>

        <div class="pb-form-grid">
            <div class="pb-form-row">
                <label for="pb-event-start"><?php esc_html_e( 'Start Time', 'peanut-booker' ); ?> <span class="required">*</span></label>
                <input type="time" id="pb-event-start" name="event_start_time" required>
            </div>
            <div class="pb-form-row">
                <label for="pb-event-hours"><?php esc_html_e( 'Duration (hours)', 'peanut-booker' ); ?> <span class="required">*</span></label>
                <select id="pb-event-hours" name="duration_hours" class="pb-booking-hours" required>
                    <?php for ( $i = $min_hours; $i <= $max_hours; $i++ ) : ?>
                        <option value="<?php echo esc_attr( $i ); ?>" <?php selected( $i, 2 ); ?>>
                            <?php echo esc_html( $i . ' ' . _n( 'hour', 'hours', $i, 'peanut-booker' ) ); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>

        <div class="pb-form-row">
            <label for="pb-event-location"><?php esc_html_e( 'Event Location', 'peanut-booker' ); ?> <span class="required">*</span></label>
            <input type="text" id="pb-event-location" name="event_location" required
                   placeholder="<?php esc_attr_e( 'Full address or venue name', 'peanut-booker' ); ?>">
        </div>

        <div class="pb-form-row">
            <label for="pb-event-description"><?php esc_html_e( 'Event Details', 'peanut-booker' ); ?></label>
            <textarea id="pb-event-description" name="event_description" rows="4"
                      placeholder="<?php esc_attr_e( 'Tell the performer about your event (type of event, expected guests, any special requests...)', 'peanut-booker' ); ?>"></textarea>
        </div>

        <div class="pb-form-row">
            <label for="pb-contact-phone"><?php esc_html_e( 'Contact Phone', 'peanut-booker' ); ?></label>
            <input type="tel" id="pb-contact-phone" name="contact_phone"
                   placeholder="<?php esc_attr_e( 'Optional', 'peanut-booker' ); ?>">
        </div>

        <div class="pb-booking-summary">
            <h3><?php esc_html_e( 'Booking Summary', 'peanut-booker' ); ?></h3>

            <div class="pb-summary-row">
                <span><?php esc_html_e( 'Hourly Rate:', 'peanut-booker' ); ?></span>
                <span><?php echo wc_price( $hourly_rate ); ?></span>
            </div>

            <div class="pb-summary-row">
                <span><?php esc_html_e( 'Duration:', 'peanut-booker' ); ?></span>
                <span class="pb-duration-display">2 <?php esc_html_e( 'hours', 'peanut-booker' ); ?></span>
            </div>

            <div class="pb-summary-row pb-summary-total">
                <span><?php esc_html_e( 'Total:', 'peanut-booker' ); ?></span>
                <span class="pb-total-amount"><?php echo wc_price( $hourly_rate * 2 ); ?></span>
            </div>

            <div class="pb-summary-row pb-summary-deposit">
                <span><?php printf( esc_html__( 'Deposit (%d%%):', 'peanut-booker' ), $deposit_pct ); ?></span>
                <span class="pb-deposit-amount"><?php echo wc_price( ( $hourly_rate * 2 ) * ( $deposit_pct / 100 ) ); ?></span>
            </div>

            <p class="pb-deposit-note">
                <?php esc_html_e( 'The deposit is charged now. Remaining balance is due before the event.', 'peanut-booker' ); ?>
            </p>
        </div>

        <div class="pb-form-actions">
            <button type="submit" class="pb-button pb-button-primary pb-button-large">
                <?php esc_html_e( 'Proceed to Payment', 'peanut-booker' ); ?>
            </button>
        </div>

        <p class="pb-booking-terms">
            <?php
            printf(
                wp_kses(
                    /* translators: %s: terms link */
                    __( 'By booking, you agree to our <a href="%s" target="_blank">Terms of Service</a>.', 'peanut-booker' ),
                    array( 'a' => array( 'href' => array(), 'target' => array() ) )
                ),
                esc_url( get_privacy_policy_url() )
            );
            ?>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    var $form = $('.pb-booking-form');
    var hourlyRate = parseFloat($form.data('hourly-rate'));
    var depositPct = parseFloat($form.data('deposit-percent'));

    $form.find('.pb-booking-hours').on('change', function() {
        var hours = parseInt($(this).val());
        var total = hours * hourlyRate;
        var deposit = total * (depositPct / 100);

        $form.find('.pb-duration-display').text(hours + ' <?php echo esc_js( _n( 'hour', 'hours', 2, 'peanut-booker' ) ); ?>');
        $form.find('.pb-total-amount').text('<?php echo esc_js( get_woocommerce_currency_symbol() ); ?>' + total.toFixed(2));
        $form.find('.pb-deposit-amount').text('<?php echo esc_js( get_woocommerce_currency_symbol() ); ?>' + deposit.toFixed(2));
    });
});
</script>
