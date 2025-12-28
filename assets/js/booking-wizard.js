/**
 * Booking Wizard JavaScript
 *
 * @package Peanut_Booker
 * @since 1.6.0
 */

(function($) {
	'use strict';

	/**
	 * Booking Wizard class.
	 */
	class BookingWizard {
		constructor(element) {
			this.$wizard = $(element);
			this.currentStep = 1;
			this.totalSteps = 3;
			this.performerId = this.$wizard.data('performer-id');
			this.wizardData = {};

			this.init();
		}

		/**
		 * Initialize wizard.
		 */
		init() {
			this.attachEvents();
			this.updateNavigation();
			this.loadSavedData();
		}

		/**
		 * Attach event handlers.
		 */
		attachEvents() {
			// Navigation buttons
			this.$wizard.find('.pb-btn-next').on('click', () => this.nextStep());
			this.$wizard.find('.pb-btn-prev').on('click', () => this.prevStep());
			this.$wizard.find('.pb-btn-submit').on('click', () => this.submitBooking());

			// Step indicators
			this.$wizard.find('.pb-wizard-step').on('click', (e) => {
				const step = $(e.currentTarget).data('step');
				if (step < this.currentStep) {
					this.goToStep(step);
				}
			});

			// Real-time validation
			this.$wizard.find('#pb-event-date').on('change', () => this.checkAvailability());
			this.$wizard.find('#pb-duration').on('change', () => this.updatePricing());
			this.$wizard.find('#pb-service-type').on('change', () => this.saveStepData());

			// Keyboard navigation
			this.$wizard.find('.pb-wizard-panel').on('keydown', (e) => {
				if (e.key === 'Enter' && !$(e.target).is('textarea, button')) {
					e.preventDefault();
					this.nextStep();
				}
			});
		}

		/**
		 * Go to next step.
		 */
		async nextStep() {
			if (!await this.validateCurrentStep()) {
				return;
			}

			if (this.currentStep < this.totalSteps) {
				this.saveStepData();
				this.goToStep(this.currentStep + 1);
			}
		}

		/**
		 * Go to previous step.
		 */
		prevStep() {
			if (this.currentStep > 1) {
				this.saveStepData();
				this.goToStep(this.currentStep - 1);
			}
		}

		/**
		 * Go to specific step.
		 */
		goToStep(step) {
			// Hide current panel
			this.$wizard.find('.pb-wizard-panel').removeClass('active').attr('hidden', true);
			this.$wizard.find('.pb-wizard-step').removeClass('active').removeAttr('aria-current');

			// Show target panel
			const $panel = this.$wizard.find(`.pb-wizard-panel[data-step="${step}"]`);
			$panel.addClass('active').removeAttr('hidden').focus();

			// Update progress indicator
			this.$wizard.find(`.pb-wizard-step[data-step="${step}"]`).addClass('active').attr('aria-current', 'step');
			this.$wizard.find('.pb-wizard-progress').attr('aria-valuenow', step);

			// Mark completed steps
			this.$wizard.find('.pb-wizard-step').each((i, el) => {
				const stepNum = $(el).data('step');
				if (stepNum < step) {
					$(el).addClass('completed');
				} else {
					$(el).removeClass('completed');
				}
			});

			this.currentStep = step;
			this.updateNavigation();

			// Update review if on step 3
			if (step === 3) {
				this.updateReview();
			}

			// Announce step change for screen readers
			this.announceStepChange(step);
		}

		/**
		 * Update navigation buttons.
		 */
		updateNavigation() {
			const $prev = this.$wizard.find('.pb-btn-prev');
			const $next = this.$wizard.find('.pb-btn-next');
			const $submit = this.$wizard.find('.pb-btn-submit');

			// Previous button
			$prev.prop('disabled', this.currentStep === 1);

			// Next/Submit buttons
			if (this.currentStep === this.totalSteps) {
				$next.hide();
				$submit.show();
			} else {
				$next.show();
				$submit.hide();
			}
		}

		/**
		 * Validate current step.
		 */
		async validateCurrentStep() {
			const stepData = this.getStepData();
			let isValid = true;

			switch (this.currentStep) {
				case 1:
					if (!stepData.service_type) {
						this.showError(pbWizard.i18n.selectService);
						isValid = false;
					}
					break;

				case 2:
					if (!stepData.event_date) {
						this.showError(pbWizard.i18n.selectDate);
						isValid = false;
					} else if (!stepData.event_start_time || !stepData.duration_hours) {
						this.showError(pbWizard.i18n.fillRequired);
						isValid = false;
					} else {
						// Check availability via AJAX
						const available = await this.validateAvailability(stepData);
						if (!available) {
							this.showError(pbWizard.i18n.dateNotAvailable);
							isValid = false;
						}
					}
					break;

				case 3:
					if (!$('#pb-agree-terms').is(':checked')) {
						this.showError(pbWizard.i18n.fillRequired);
						isValid = false;
					}
					break;
			}

			return isValid;
		}

		/**
		 * Get current step data.
		 */
		getStepData() {
			const $panel = this.$wizard.find(`.pb-wizard-panel[data-step="${this.currentStep}"]`);
			const data = {};

			$panel.find('input, select, textarea').each(function() {
				const $field = $(this);
				const name = $field.attr('name');
				if (name && $field.val()) {
					data[name] = $field.val();
				}
			});

			return data;
		}

		/**
		 * Save step data.
		 */
		saveStepData() {
			const stepData = this.getStepData();
			Object.assign(this.wizardData, stepData);

			// Save to sessionStorage
			sessionStorage.setItem('pb_wizard_data', JSON.stringify(this.wizardData));
		}

		/**
		 * Load saved data.
		 */
		loadSavedData() {
			const saved = sessionStorage.getItem('pb_wizard_data');
			if (saved) {
				try {
					this.wizardData = JSON.parse(saved);
					this.populateFields();
				} catch (e) {
					console.error('Failed to load saved wizard data:', e);
				}
			}
		}

		/**
		 * Populate fields with saved data.
		 */
		populateFields() {
			Object.keys(this.wizardData).forEach(name => {
				const $field = this.$wizard.find(`[name="${name}"]`);
				if ($field.length) {
					$field.val(this.wizardData[name]);
				}
			});
		}

		/**
		 * Check availability for selected date/time.
		 */
		async checkAvailability() {
			const date = $('#pb-event-date').val();
			if (!date) return;

			const $status = $('#date-availability-status');
			$status.html('<span class="checking">' + pbWizard.i18n.checking + '...</span>');

			try {
				const response = await $.ajax({
					url: pbWizard.ajaxUrl,
					method: 'GET',
					data: {
						action: 'pb_get_availability',
						performer_id: this.performerId,
						date: date
					}
				});

				if (response.success) {
					const isAvailable = response.data.available !== false;
					$status.html(
						isAvailable
							? '<span class="available">✓ Available</span>'
							: '<span class="unavailable">✗ Not available</span>'
					);
				}
			} catch (error) {
				console.error('Availability check failed:', error);
			}
		}

		/**
		 * Validate availability via AJAX.
		 */
		async validateAvailability(data) {
			try {
				const response = await $.ajax({
					url: pbWizard.ajaxUrl,
					method: 'POST',
					data: {
						action: 'pb_wizard_validate_step',
						nonce: pbWizard.nonce,
						step: 2,
						performer_id: this.performerId,
						data: data
					}
				});

				return response.success;
			} catch (error) {
				console.error('Validation failed:', error);
				return false;
			}
		}

		/**
		 * Update pricing display.
		 */
		updatePricing() {
			const hours = parseInt($('#pb-duration').val()) || 0;
			const hourlyRate = parseFloat($('.pb-review-hourly-rate').data('rate')) || 0;
			const depositPct = parseFloat(this.$wizard.data('deposit-percent')) || 25;

			const total = hours * hourlyRate;
			const deposit = total * (depositPct / 100);

			$('.pb-review-hours').text(hours + ' ' + (hours === 1 ? 'hour' : 'hours'));
			$('.pb-review-total').text(this.formatPrice(total));
			$('.pb-review-deposit').text(this.formatPrice(deposit));
		}

		/**
		 * Update review section.
		 */
		updateReview() {
			// Service
			const serviceType = $('#pb-service-type option:selected').text();
			$('.pb-review-service').text(serviceType || 'Not selected');

			// Date
			const eventDate = $('#pb-event-date').val();
			if (eventDate) {
				const formatted = new Date(eventDate).toLocaleDateString();
				$('.pb-review-date').text(formatted);
			}

			// Time
			const startTime = $('#pb-event-start').val();
			const duration = $('#pb-duration').val();
			if (startTime && duration) {
				$('.pb-review-time').text(startTime);
				$('.pb-review-duration').text(duration + ' ' + (duration == 1 ? 'hour' : 'hours'));
			}

			// Location
			const location = $('#pb-event-location').val();
			$('.pb-review-location').text(location || 'Not entered');

			// Update pricing
			this.updatePricing();
		}

		/**
		 * Submit booking.
		 */
		async submitBooking() {
			if (!await this.validateCurrentStep()) {
				return;
			}

			this.saveStepData();

			const $submit = this.$wizard.find('.pb-btn-submit');
			$submit.prop('disabled', true).text('Processing...');

			try {
				const response = await $.ajax({
					url: pbWizard.ajaxUrl,
					method: 'POST',
					data: {
						action: 'pb_wizard_create_booking',
						nonce: pbWizard.nonce,
						performer_id: this.performerId,
						wizard_data: this.wizardData
					}
				});

				if (response.success) {
					// Clear saved data
					sessionStorage.removeItem('pb_wizard_data');

					// Redirect to checkout
					if (response.data.checkout_url) {
						window.location.href = response.data.checkout_url;
					} else {
						this.showSuccess(response.data.message);
					}
				} else {
					this.showError(response.data.message || pbWizard.i18n.bookingError);
					$submit.prop('disabled', false).text('Proceed to Payment');
				}
			} catch (error) {
				console.error('Booking submission failed:', error);
				this.showError(pbWizard.i18n.bookingError);
				$submit.prop('disabled', false).text('Proceed to Payment');
			}
		}

		/**
		 * Show error message.
		 */
		showError(message) {
			const $messages = this.$wizard.find('.pb-wizard-messages');
			$messages.html(`<div class="pb-message pb-error" role="alert">${message}</div>`);
			$messages[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });

			setTimeout(() => {
				$messages.find('.pb-message').fadeOut(() => {
					$messages.empty();
				});
			}, 5000);
		}

		/**
		 * Show success message.
		 */
		showSuccess(message) {
			const $messages = this.$wizard.find('.pb-wizard-messages');
			$messages.html(`<div class="pb-message pb-success" role="status">${message}</div>`);
			$messages[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
		}

		/**
		 * Announce step change for screen readers.
		 */
		announceStepChange(step) {
			const stepNames = {
				1: 'Step 1: Select Service',
				2: 'Step 2: Choose Date and Time',
				3: 'Step 3: Review and Confirm'
			};

			const announcement = stepNames[step];
			const $announcer = $('<div class="sr-only" role="status" aria-live="polite"></div>');
			$announcer.text(announcement);
			this.$wizard.append($announcer);

			setTimeout(() => $announcer.remove(), 1000);
		}

		/**
		 * Format price.
		 */
		formatPrice(amount) {
			return new Intl.NumberFormat('en-US', {
				style: 'currency',
				currency: 'USD'
			}).format(amount);
		}
	}

	/**
	 * Initialize wizards on page load.
	 */
	$(document).ready(function() {
		$('.pb-booking-wizard').each(function() {
			new BookingWizard(this);
		});
	});

})(jQuery);
