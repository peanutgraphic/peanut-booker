<?php
/**
 * Booking wizard template.
 *
 * @package Peanut_Booker
 * @since   1.6.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// Expects $performer_id and $display_data to be set.
if ( ! isset( $performer_id ) || ! isset( $display_data ) ) {
	return;
}

$categories = Peanut_Booker_Booking_Wizard::get_service_categories( $display_data['profile_id'] );
?>

<div class="pb-booking-wizard" data-performer-id="<?php echo esc_attr( $performer_id ); ?>">

	<!-- Progress Indicator -->
	<div class="pb-wizard-progress" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="3" aria-label="<?php esc_attr_e( 'Booking progress', 'peanut-booker' ); ?>">
		<div class="pb-wizard-steps">
			<div class="pb-wizard-step active" data-step="1" aria-current="step">
				<span class="pb-step-number" aria-hidden="true">1</span>
				<span class="pb-step-label"><?php esc_html_e( 'Select Service', 'peanut-booker' ); ?></span>
			</div>
			<div class="pb-wizard-step" data-step="2">
				<span class="pb-step-number" aria-hidden="true">2</span>
				<span class="pb-step-label"><?php esc_html_e( 'Date & Details', 'peanut-booker' ); ?></span>
			</div>
			<div class="pb-wizard-step" data-step="3">
				<span class="pb-step-number" aria-hidden="true">3</span>
				<span class="pb-step-label"><?php esc_html_e( 'Review & Confirm', 'peanut-booker' ); ?></span>
			</div>
		</div>
	</div>

	<!-- Wizard Content -->
	<div class="pb-wizard-content">

		<!-- Step 1: Service Selection -->
		<div class="pb-wizard-panel active" data-step="1" role="tabpanel" aria-labelledby="step-1-label" tabindex="0">
			<h2 id="step-1-label" class="pb-wizard-title"><?php esc_html_e( 'Select Your Service', 'peanut-booker' ); ?></h2>

			<div class="pb-service-selection">
				<div class="pb-form-row">
					<label for="pb-service-type" id="service-type-label">
						<?php esc_html_e( 'Service Type', 'peanut-booker' ); ?>
						<span class="required" aria-label="<?php esc_attr_e( 'required', 'peanut-booker' ); ?>">*</span>
					</label>
					<select id="pb-service-type" name="service_type" required aria-required="true" aria-labelledby="service-type-label">
						<option value=""><?php esc_html_e( 'Select a service...', 'peanut-booker' ); ?></option>
						<option value="performance"><?php esc_html_e( 'Performance', 'peanut-booker' ); ?></option>
						<option value="event"><?php esc_html_e( 'Event Entertainment', 'peanut-booker' ); ?></option>
						<option value="private"><?php esc_html_e( 'Private Event', 'peanut-booker' ); ?></option>
						<option value="corporate"><?php esc_html_e( 'Corporate Event', 'peanut-booker' ); ?></option>
						<option value="other"><?php esc_html_e( 'Other', 'peanut-booker' ); ?></option>
					</select>
				</div>

				<?php if ( ! empty( $categories ) ) : ?>
				<div class="pb-form-row">
					<label for="pb-category" id="category-label"><?php esc_html_e( 'Category', 'peanut-booker' ); ?></label>
					<select id="pb-category" name="category" aria-labelledby="category-label">
						<option value=""><?php esc_html_e( 'Select a category...', 'peanut-booker' ); ?></option>
						<?php foreach ( $categories as $category ) : ?>
							<option value="<?php echo esc_attr( $category->slug ); ?>">
								<?php echo esc_html( $category->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php endif; ?>

				<div class="pb-rate-display" role="region" aria-label="<?php esc_attr_e( 'Pricing information', 'peanut-booker' ); ?>">
					<h3><?php esc_html_e( 'Pricing', 'peanut-booker' ); ?></h3>
					<div class="pb-rate-info">
						<span class="pb-rate-label"><?php esc_html_e( 'Hourly Rate:', 'peanut-booker' ); ?></span>
						<span class="pb-rate-amount"><?php echo wc_price( $display_data['display_price'] ); ?></span>
					</div>
					<div class="pb-deposit-info">
						<span class="pb-deposit-label"><?php printf( esc_html__( 'Deposit Required (%d%%):', 'peanut-booker' ), $display_data['deposit_percentage'] ); ?></span>
						<span class="pb-deposit-note"><?php esc_html_e( 'Charged at booking, remaining balance due before event', 'peanut-booker' ); ?></span>
					</div>
				</div>
			</div>
		</div>

		<!-- Step 2: Date/Time and Details -->
		<div class="pb-wizard-panel" data-step="2" role="tabpanel" aria-labelledby="step-2-label" tabindex="0" hidden>
			<h2 id="step-2-label" class="pb-wizard-title"><?php esc_html_e( 'Choose Date & Time', 'peanut-booker' ); ?></h2>

			<div class="pb-datetime-selection">
				<div class="pb-form-row">
					<label for="pb-event-date" id="event-date-label">
						<?php esc_html_e( 'Event Date', 'peanut-booker' ); ?>
						<span class="required" aria-label="<?php esc_attr_e( 'required', 'peanut-booker' ); ?>">*</span>
					</label>
					<input
						type="date"
						id="pb-event-date"
						name="event_date"
						required
						aria-required="true"
						aria-labelledby="event-date-label"
						aria-describedby="date-availability-status"
						min="<?php echo esc_attr( gmdate( 'Y-m-d', strtotime( '+1 day' ) ) ); ?>"
					>
					<span id="date-availability-status" class="pb-availability-status" role="status" aria-live="polite"></span>
				</div>

				<div class="pb-form-grid">
					<div class="pb-form-row">
						<label for="pb-event-start" id="start-time-label">
							<?php esc_html_e( 'Start Time', 'peanut-booker' ); ?>
							<span class="required" aria-label="<?php esc_attr_e( 'required', 'peanut-booker' ); ?>">*</span>
						</label>
						<input type="time" id="pb-event-start" name="event_start_time" required aria-required="true" aria-labelledby="start-time-label">
					</div>
					<div class="pb-form-row">
						<label for="pb-duration" id="duration-label">
							<?php esc_html_e( 'Duration (hours)', 'peanut-booker' ); ?>
							<span class="required" aria-label="<?php esc_attr_e( 'required', 'peanut-booker' ); ?>">*</span>
						</label>
						<select id="pb-duration" name="duration_hours" required aria-required="true" aria-labelledby="duration-label">
							<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
								<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $i, 2 ); ?>>
									<?php echo esc_html( $i . ' ' . _n( 'hour', 'hours', $i, 'peanut-booker' ) ); ?>
								</option>
							<?php endfor; ?>
						</select>
					</div>
				</div>

				<div class="pb-form-row">
					<label for="pb-event-location" id="location-label">
						<?php esc_html_e( 'Event Location', 'peanut-booker' ); ?>
						<span class="required" aria-label="<?php esc_attr_e( 'required', 'peanut-booker' ); ?>">*</span>
					</label>
					<input
						type="text"
						id="pb-event-location"
						name="event_location"
						required
						aria-required="true"
						aria-labelledby="location-label"
						placeholder="<?php esc_attr_e( 'Full address or venue name', 'peanut-booker' ); ?>"
					>
				</div>

				<div class="pb-form-row">
					<label for="pb-event-description" id="description-label">
						<?php esc_html_e( 'Event Details', 'peanut-booker' ); ?>
					</label>
					<textarea
						id="pb-event-description"
						name="event_description"
						rows="4"
						aria-labelledby="description-label"
						placeholder="<?php esc_attr_e( 'Tell the performer about your event...', 'peanut-booker' ); ?>"
					></textarea>
				</div>
			</div>
		</div>

		<!-- Step 3: Review & Confirm -->
		<div class="pb-wizard-panel" data-step="3" role="tabpanel" aria-labelledby="step-3-label" tabindex="0" hidden>
			<h2 id="step-3-label" class="pb-wizard-title"><?php esc_html_e( 'Review Your Booking', 'peanut-booker' ); ?></h2>

			<div class="pb-booking-review">
				<!-- Booking Summary -->
				<div class="pb-review-section" role="region" aria-labelledby="summary-heading">
					<h3 id="summary-heading"><?php esc_html_e( 'Booking Summary', 'peanut-booker' ); ?></h3>
					<dl class="pb-review-details">
						<div class="pb-review-row">
							<dt><?php esc_html_e( 'Performer:', 'peanut-booker' ); ?></dt>
							<dd><?php echo esc_html( $display_data['name'] ); ?></dd>
						</div>
						<div class="pb-review-row">
							<dt><?php esc_html_e( 'Service:', 'peanut-booker' ); ?></dt>
							<dd class="pb-review-service"><?php esc_html_e( 'Not selected', 'peanut-booker' ); ?></dd>
						</div>
						<div class="pb-review-row">
							<dt><?php esc_html_e( 'Date:', 'peanut-booker' ); ?></dt>
							<dd class="pb-review-date"><?php esc_html_e( 'Not selected', 'peanut-booker' ); ?></dd>
						</div>
						<div class="pb-review-row">
							<dt><?php esc_html_e( 'Time:', 'peanut-booker' ); ?></dt>
							<dd class="pb-review-time"><?php esc_html_e( 'Not selected', 'peanut-booker' ); ?></dd>
						</div>
						<div class="pb-review-row">
							<dt><?php esc_html_e( 'Duration:', 'peanut-booker' ); ?></dt>
							<dd class="pb-review-duration"><?php esc_html_e( 'Not selected', 'peanut-booker' ); ?></dd>
						</div>
						<div class="pb-review-row">
							<dt><?php esc_html_e( 'Location:', 'peanut-booker' ); ?></dt>
							<dd class="pb-review-location"><?php esc_html_e( 'Not entered', 'peanut-booker' ); ?></dd>
						</div>
					</dl>
				</div>

				<!-- Price Breakdown -->
				<div class="pb-review-section pb-price-breakdown" role="region" aria-labelledby="pricing-heading">
					<h3 id="pricing-heading"><?php esc_html_e( 'Price Breakdown', 'peanut-booker' ); ?></h3>
					<dl class="pb-price-details">
						<div class="pb-price-row">
							<dt><?php esc_html_e( 'Hourly Rate:', 'peanut-booker' ); ?></dt>
							<dd class="pb-review-hourly-rate"><?php echo wc_price( $display_data['display_price'] ); ?></dd>
						</div>
						<div class="pb-price-row">
							<dt><?php esc_html_e( 'Duration:', 'peanut-booker' ); ?></dt>
							<dd class="pb-review-hours">0 <?php esc_html_e( 'hours', 'peanut-booker' ); ?></dd>
						</div>
						<div class="pb-price-row pb-total-row">
							<dt><?php esc_html_e( 'Total:', 'peanut-booker' ); ?></dt>
							<dd class="pb-review-total"><?php echo wc_price( 0 ); ?></dd>
						</div>
						<div class="pb-price-row pb-deposit-row">
							<dt><?php printf( esc_html__( 'Deposit Due Now (%d%%):', 'peanut-booker' ), $display_data['deposit_percentage'] ); ?></dt>
							<dd class="pb-review-deposit"><?php echo wc_price( 0 ); ?></dd>
						</div>
					</dl>
				</div>

				<!-- Additional Information -->
				<div class="pb-review-section">
					<h3><?php esc_html_e( 'Additional Information', 'peanut-booker' ); ?></h3>

					<div class="pb-form-row">
						<label for="pb-contact-phone" id="phone-label"><?php esc_html_e( 'Contact Phone', 'peanut-booker' ); ?></label>
						<input type="tel" id="pb-contact-phone" name="contact_phone" aria-labelledby="phone-label" placeholder="<?php esc_attr_e( 'Optional', 'peanut-booker' ); ?>">
					</div>

					<div class="pb-form-row">
						<label for="pb-special-notes" id="notes-label"><?php esc_html_e( 'Special Notes', 'peanut-booker' ); ?></label>
						<textarea id="pb-special-notes" name="special_notes" rows="3" aria-labelledby="notes-label" placeholder="<?php esc_attr_e( 'Any special requests or notes...', 'peanut-booker' ); ?>"></textarea>
					</div>
				</div>

				<div class="pb-terms-agreement">
					<label class="pb-checkbox-label">
						<input type="checkbox" id="pb-agree-terms" name="agree_terms" required aria-required="true">
						<span>
							<?php
							printf(
								wp_kses(
									/* translators: %s: terms link */
									__( 'I agree to the <a href="%s" target="_blank" rel="noopener noreferrer">Terms of Service</a>', 'peanut-booker' ),
									array(
										'a' => array(
											'href'   => array(),
											'target' => array(),
											'rel'    => array(),
										),
									)
								),
								esc_url( get_privacy_policy_url() )
							);
							?>
						</span>
					</label>
				</div>
			</div>
		</div>

	</div>

	<!-- Wizard Navigation -->
	<div class="pb-wizard-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Wizard navigation', 'peanut-booker' ); ?>">
		<button type="button" class="pb-wizard-btn pb-btn-prev" disabled aria-label="<?php esc_attr_e( 'Go to previous step', 'peanut-booker' ); ?>">
			<span aria-hidden="true">&larr;</span> <?php esc_html_e( 'Previous', 'peanut-booker' ); ?>
		</button>
		<button type="button" class="pb-wizard-btn pb-btn-next" aria-label="<?php esc_attr_e( 'Go to next step', 'peanut-booker' ); ?>">
			<?php esc_html_e( 'Next', 'peanut-booker' ); ?> <span aria-hidden="true">&rarr;</span>
		</button>
		<button type="button" class="pb-wizard-btn pb-btn-submit" style="display: none;" aria-label="<?php esc_attr_e( 'Submit booking', 'peanut-booker' ); ?>">
			<?php esc_html_e( 'Proceed to Payment', 'peanut-booker' ); ?>
		</button>
	</div>

	<!-- Error/Success Messages -->
	<div class="pb-wizard-messages" role="alert" aria-live="assertive" aria-atomic="true"></div>

</div>
