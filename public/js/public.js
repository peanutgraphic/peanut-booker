/**
 * Peanut Booker - Public JavaScript
 *
 * @package Peanut_Booker
 */

(function($) {
    'use strict';

    /**
     * Public functionality.
     */
    var PB = {

        /**
         * Initialize.
         */
        init: function() {
            this.bindEvents();
            this.initCalendar();
            this.initGallery();
        },

        /**
         * Bind events.
         */
        bindEvents: function() {
            // Booking form.
            $(document).on('submit', '.pb-booking-form', this.handleBooking);
            $(document).on('change', '.pb-booking-date', this.checkAvailability);
            $(document).on('change', '.pb-booking-hours', this.updatePrice);

            // Bid form.
            $(document).on('submit', '.pb-bid-form', this.handleBid);

            // Review form.
            $(document).on('submit', '.pb-review-form', this.handleReview);

            // Star rating.
            $(document).on('click', '.pb-star-input', this.setRating);
            $(document).on('mouseenter', '.pb-star-input', this.hoverRating);
            $(document).on('mouseleave', '.pb-stars-input', this.resetRating);

            // Cancel booking.
            $(document).on('click', '.pb-cancel-booking', this.cancelBooking);

            // Complete booking.
            $(document).on('click', '.pb-complete-booking', this.completeBooking);

            // Accept/decline bid.
            $(document).on('click', '.pb-accept-bid', this.acceptBid);
            $(document).on('click', '.pb-decline-bid', this.declineBid);

            // Profile tabs.
            $(document).on('click', '.pb-profile-tabs a', this.switchTab);

            // Filter performers.
            $(document).on('change', '.pb-filter-category, .pb-filter-location, .pb-filter-price', this.filterPerformers);
            $(document).on('input', '.pb-search-performers', this.debounce(this.searchPerformers, 300));

            // Availability toggle.
            $(document).on('click', '.pb-availability-slot', this.toggleAvailability);

            // Load more.
            $(document).on('click', '.pb-load-more', this.loadMore);
        },

        /**
         * Initialize availability calendar.
         */
        initCalendar: function() {
            var $calendar = $('.pb-availability-calendar');
            if ($calendar.length === 0) return;

            var performerId = $calendar.data('performer-id');
            var currentMonth = new Date();

            this.loadCalendarMonth($calendar, performerId, currentMonth);

            $(document).on('click', '.pb-calendar-prev', function() {
                currentMonth.setMonth(currentMonth.getMonth() - 1);
                PB.loadCalendarMonth($calendar, performerId, currentMonth);
            });

            $(document).on('click', '.pb-calendar-next', function() {
                currentMonth.setMonth(currentMonth.getMonth() + 1);
                PB.loadCalendarMonth($calendar, performerId, currentMonth);
            });
        },

        /**
         * Load calendar month.
         */
        loadCalendarMonth: function($calendar, performerId, date) {
            $.ajax({
                url: peanutBooker.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pb_get_availability',
                    performer_id: performerId,
                    year: date.getFullYear(),
                    month: date.getMonth() + 1,
                    nonce: peanutBooker.nonces.availability
                },
                success: function(response) {
                    if (response.success) {
                        $calendar.html(response.data.html);
                    }
                }
            });
        },

        /**
         * Initialize photo gallery.
         */
        initGallery: function() {
            var $gallery = $('.pb-gallery-grid');
            if ($gallery.length === 0) return;

            $gallery.on('click', '.pb-gallery-item', function(e) {
                e.preventDefault();
                var $img = $(this).find('img');
                var src = $img.attr('src').replace('-300x300', '').replace('-150x150', '');

                PB.openLightbox(src, $img.attr('alt'));
            });
        },

        /**
         * Open lightbox.
         */
        openLightbox: function(src, alt) {
            var $lightbox = $('<div class="pb-lightbox">' +
                '<div class="pb-lightbox-overlay"></div>' +
                '<div class="pb-lightbox-content">' +
                '<button class="pb-lightbox-close">&times;</button>' +
                '<img src="' + src + '" alt="' + (alt || '') + '">' +
                '</div></div>');

            $('body').append($lightbox).addClass('pb-lightbox-open');

            $lightbox.on('click', '.pb-lightbox-overlay, .pb-lightbox-close', function() {
                $lightbox.remove();
                $('body').removeClass('pb-lightbox-open');
            });

            $(document).on('keydown.lightbox', function(e) {
                if (e.key === 'Escape') {
                    $lightbox.remove();
                    $('body').removeClass('pb-lightbox-open');
                    $(document).off('keydown.lightbox');
                }
            });
        },

        /**
         * Handle booking form submission.
         */
        handleBooking: function(e) {
            e.preventDefault();

            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');

            if (!PB.validateForm($form)) {
                return;
            }

            $btn.prop('disabled', true).text(peanutBooker.strings.loading);

            $.ajax({
                url: peanutBooker.ajaxUrl,
                type: 'POST',
                data: $form.serialize() + '&action=pb_create_booking&nonce=' + peanutBooker.nonces.booking,
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.checkout_url;
                    } else {
                        PB.showMessage($form, response.data.message || peanutBooker.strings.error, 'error');
                        $btn.prop('disabled', false).text('Book Now');
                    }
                },
                error: function() {
                    PB.showMessage($form, peanutBooker.strings.error, 'error');
                    $btn.prop('disabled', false).text('Book Now');
                }
            });
        },

        /**
         * Check availability for selected date.
         */
        checkAvailability: function() {
            var $form = $(this).closest('.pb-booking-form');
            var performerId = $form.find('[name="performer_id"]').val();
            var date = $(this).val();

            if (!date) return;

            $.ajax({
                url: peanutBooker.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pb_check_availability',
                    performer_id: performerId,
                    date: date,
                    nonce: peanutBooker.nonces.availability
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data.available) {
                            $form.find('.pb-availability-status')
                                .removeClass('pb-unavailable')
                                .addClass('pb-available')
                                .text('Available');
                            $form.find('button[type="submit"]').prop('disabled', false);
                        } else {
                            $form.find('.pb-availability-status')
                                .removeClass('pb-available')
                                .addClass('pb-unavailable')
                                .text('Not Available');
                            $form.find('button[type="submit"]').prop('disabled', true);
                        }
                    }
                }
            });
        },

        /**
         * Update price based on hours.
         */
        updatePrice: function() {
            var $form = $(this).closest('.pb-booking-form');
            var hours = parseFloat($(this).val()) || 1;
            var hourlyRate = parseFloat($form.data('hourly-rate')) || 0;
            var depositPercent = parseFloat($form.data('deposit-percent')) || 50;

            var total = hours * hourlyRate;
            var deposit = total * (depositPercent / 100);

            $form.find('.pb-total-amount').text(peanutBooker.currency + total.toFixed(2));
            $form.find('.pb-deposit-amount').text(peanutBooker.currency + deposit.toFixed(2));
        },

        /**
         * Handle bid form submission.
         */
        handleBid: function(e) {
            e.preventDefault();

            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');

            if (!PB.validateForm($form)) {
                return;
            }

            $btn.prop('disabled', true).text(peanutBooker.strings.loading);

            $.ajax({
                url: peanutBooker.ajaxUrl,
                type: 'POST',
                data: $form.serialize() + '&action=pb_submit_bid&nonce=' + peanutBooker.nonces.market,
                success: function(response) {
                    if (response.success) {
                        PB.showMessage($form, 'Bid submitted successfully!', 'success');
                        $form[0].reset();
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        PB.showMessage($form, response.data.message || peanutBooker.strings.error, 'error');
                        $btn.prop('disabled', false).text('Submit Bid');
                    }
                },
                error: function() {
                    PB.showMessage($form, peanutBooker.strings.error, 'error');
                    $btn.prop('disabled', false).text('Submit Bid');
                }
            });
        },

        /**
         * Handle review form submission.
         */
        handleReview: function(e) {
            e.preventDefault();

            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            var rating = $form.find('[name="rating"]').val();

            if (!rating || rating < 1) {
                PB.showMessage($form, 'Please select a rating.', 'error');
                return;
            }

            if (!PB.validateForm($form)) {
                return;
            }

            $btn.prop('disabled', true).text(peanutBooker.strings.loading);

            $.ajax({
                url: peanutBooker.ajaxUrl,
                type: 'POST',
                data: $form.serialize() + '&action=pb_submit_review&nonce=' + peanutBooker.nonces.review,
                success: function(response) {
                    if (response.success) {
                        PB.showMessage($form, 'Review submitted successfully!', 'success');
                        $form.slideUp();
                        if (response.data.html) {
                            $('.pb-reviews-list').prepend(response.data.html);
                        }
                    } else {
                        PB.showMessage($form, response.data.message || peanutBooker.strings.error, 'error');
                        $btn.prop('disabled', false).text('Submit Review');
                    }
                },
                error: function() {
                    PB.showMessage($form, peanutBooker.strings.error, 'error');
                    $btn.prop('disabled', false).text('Submit Review');
                }
            });
        },

        /**
         * Set star rating.
         */
        setRating: function() {
            var rating = $(this).data('rating');
            var $container = $(this).closest('.pb-stars-input');

            $container.find('[name="rating"]').val(rating);
            $container.find('.pb-star-input').each(function() {
                var starRating = $(this).data('rating');
                $(this).toggleClass('active', starRating <= rating);
            });
        },

        /**
         * Hover star rating.
         */
        hoverRating: function() {
            var rating = $(this).data('rating');
            var $container = $(this).closest('.pb-stars-input');

            $container.find('.pb-star-input').each(function() {
                var starRating = $(this).data('rating');
                $(this).toggleClass('hover', starRating <= rating);
            });
        },

        /**
         * Reset star rating display.
         */
        resetRating: function() {
            $(this).find('.pb-star-input').removeClass('hover');
        },

        /**
         * Cancel booking.
         */
        cancelBooking: function(e) {
            e.preventDefault();

            if (!confirm(peanutBooker.strings.confirmCancel)) {
                return;
            }

            var $btn = $(this);
            var bookingId = $btn.data('booking-id');

            $btn.prop('disabled', true).text(peanutBooker.strings.loading);

            $.ajax({
                url: peanutBooker.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pb_cancel_booking',
                    booking_id: bookingId,
                    nonce: peanutBooker.nonces.booking
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message || peanutBooker.strings.error);
                        $btn.prop('disabled', false).text('Cancel');
                    }
                },
                error: function() {
                    alert(peanutBooker.strings.error);
                    $btn.prop('disabled', false).text('Cancel');
                }
            });
        },

        /**
         * Complete booking.
         */
        completeBooking: function(e) {
            e.preventDefault();

            if (!confirm(peanutBooker.strings.confirmComplete)) {
                return;
            }

            var $btn = $(this);
            var bookingId = $btn.data('booking-id');

            $btn.prop('disabled', true).text(peanutBooker.strings.loading);

            $.ajax({
                url: peanutBooker.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pb_complete_booking',
                    booking_id: bookingId,
                    nonce: peanutBooker.nonces.booking
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message || peanutBooker.strings.error);
                        $btn.prop('disabled', false).text('Mark Complete');
                    }
                },
                error: function() {
                    alert(peanutBooker.strings.error);
                    $btn.prop('disabled', false).text('Mark Complete');
                }
            });
        },

        /**
         * Accept bid.
         */
        acceptBid: function(e) {
            e.preventDefault();

            var $btn = $(this);
            var bidId = $btn.data('bid-id');

            $btn.prop('disabled', true).text(peanutBooker.strings.loading);

            $.ajax({
                url: peanutBooker.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pb_accept_bid',
                    bid_id: bidId,
                    nonce: peanutBooker.nonces.market
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.checkout_url;
                    } else {
                        alert(response.data.message || peanutBooker.strings.error);
                        $btn.prop('disabled', false).text('Accept');
                    }
                },
                error: function() {
                    alert(peanutBooker.strings.error);
                    $btn.prop('disabled', false).text('Accept');
                }
            });
        },

        /**
         * Decline bid.
         */
        declineBid: function(e) {
            e.preventDefault();

            var $btn = $(this);
            var bidId = $btn.data('bid-id');

            $btn.prop('disabled', true);

            $.ajax({
                url: peanutBooker.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pb_decline_bid',
                    bid_id: bidId,
                    nonce: peanutBooker.nonces.market
                },
                success: function(response) {
                    if (response.success) {
                        $btn.closest('.pb-bid-item').fadeOut();
                    } else {
                        alert(response.data.message || peanutBooker.strings.error);
                        $btn.prop('disabled', false);
                    }
                },
                error: function() {
                    alert(peanutBooker.strings.error);
                    $btn.prop('disabled', false);
                }
            });
        },

        /**
         * Switch profile tabs.
         */
        switchTab: function(e) {
            var href = $(this).attr('href');
            if (href.indexOf('#') !== 0) return;

            e.preventDefault();

            $(this).closest('.pb-profile-tabs').find('a').removeClass('active');
            $(this).addClass('active');

            $('.pb-profile-tab-content').hide();
            $(href).show();
        },

        /**
         * Filter performers.
         */
        filterPerformers: function() {
            var $container = $('.pb-performers-grid');
            var category = $('.pb-filter-category').val();
            var location = $('.pb-filter-location').val();
            var maxPrice = $('.pb-filter-price').val();

            $.ajax({
                url: peanutBooker.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pb_filter_performers',
                    category: category,
                    location: location,
                    max_price: maxPrice,
                    nonce: peanutBooker.nonces.performer
                },
                beforeSend: function() {
                    $container.addClass('pb-loading');
                },
                success: function(response) {
                    $container.removeClass('pb-loading');
                    if (response.success) {
                        $container.html(response.data.html);
                    }
                }
            });
        },

        /**
         * Search performers.
         */
        searchPerformers: function() {
            var $input = $(this);
            var $container = $('.pb-performers-grid');
            var query = $input.val();

            if (query.length < 2) return;

            $.ajax({
                url: peanutBooker.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pb_search_performers',
                    query: query,
                    nonce: peanutBooker.nonces.performer
                },
                beforeSend: function() {
                    $container.addClass('pb-loading');
                },
                success: function(response) {
                    $container.removeClass('pb-loading');
                    if (response.success) {
                        $container.html(response.data.html);
                    }
                }
            });
        },

        /**
         * Toggle availability slot.
         */
        toggleAvailability: function() {
            var $slot = $(this);
            var date = $slot.data('date');
            var slotType = $slot.data('slot');
            var isAvailable = $slot.hasClass('pb-available');

            $.ajax({
                url: peanutBooker.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pb_toggle_availability',
                    date: date,
                    slot: slotType,
                    available: isAvailable ? 0 : 1,
                    nonce: peanutBooker.nonces.availability
                },
                success: function(response) {
                    if (response.success) {
                        $slot.toggleClass('pb-available pb-unavailable');
                    }
                }
            });
        },

        /**
         * Load more items.
         */
        loadMore: function(e) {
            e.preventDefault();

            var $btn = $(this);
            var $container = $btn.siblings('.pb-items-container');
            var page = parseInt($btn.data('page')) || 1;
            var type = $btn.data('type');

            $btn.prop('disabled', true).text(peanutBooker.strings.loading);

            $.ajax({
                url: peanutBooker.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pb_load_more',
                    type: type,
                    page: page + 1,
                    nonce: peanutBooker.nonces.performer
                },
                success: function(response) {
                    if (response.success) {
                        $container.append(response.data.html);
                        $btn.data('page', page + 1);

                        if (!response.data.has_more) {
                            $btn.hide();
                        } else {
                            $btn.prop('disabled', false).text('Load More');
                        }
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).text('Load More');
                }
            });
        },

        /**
         * Validate form.
         */
        validateForm: function($form) {
            var valid = true;

            $form.find('[required]').each(function() {
                var $field = $(this);
                if (!$field.val()) {
                    $field.addClass('pb-error');
                    valid = false;
                } else {
                    $field.removeClass('pb-error');
                }
            });

            if (!valid) {
                PB.showMessage($form, peanutBooker.strings.required, 'error');
            }

            return valid;
        },

        /**
         * Show message.
         */
        showMessage: function($context, message, type) {
            type = type || 'info';

            var $existing = $context.find('.pb-message');
            if ($existing.length) {
                $existing.remove();
            }

            var $message = $('<div class="pb-message pb-message-' + type + '">' + message + '</div>');
            $context.prepend($message);

            setTimeout(function() {
                $message.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },

        /**
         * Debounce function.
         */
        debounce: function(func, wait) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    func.apply(context, args);
                }, wait);
            };
        }
    };

    /**
     * Profile Wizard functionality.
     */
    var ProfileWizard = {
        currentTab: 0,
        tabs: ['basic-info', 'photos', 'videos', 'pricing', 'location', 'categories'],
        mediaFrame: null,

        /**
         * Initialize.
         */
        init: function() {
            var $wizard = $('.pb-profile-wizard');
            if ($wizard.length === 0) return;

            this.$wizard = $wizard;
            this.$form = $wizard.find('#pb-profile-wizard-form');
            this.$tabs = $wizard.find('.pb-wizard-tab');
            this.$panels = $wizard.find('.pb-wizard-panel');

            this.bindEvents();
        },

        /**
         * Bind events.
         */
        bindEvents: function() {
            var self = this;

            // Tab navigation
            this.$tabs.on('click', function() {
                var tabIndex = $(this).index();
                self.goToTab(tabIndex);
            });

            // Previous button
            this.$wizard.on('click', '.pb-wizard-prev', function() {
                self.goToTab(self.currentTab - 1);
            });

            // Next button
            this.$wizard.on('click', '.pb-wizard-next', function() {
                self.goToTab(self.currentTab + 1);
            });

            // Form submission
            this.$form.on('submit', function(e) {
                e.preventDefault();
                self.saveProfile();
            });

            // Travel willing checkbox toggle
            this.$wizard.on('change', '[name="travel_willing"]', function() {
                $('.pb-travel-radius-row').toggle($(this).is(':checked'));
            });

            // Add video link
            this.$wizard.on('click', '#pb-add-video', function() {
                var $list = $('#pb-video-list');
                var limit = parseInt($list.data('limit'));
                var currentCount = $list.find('.pb-video-item').length;

                if (limit > 0 && currentCount >= limit) {
                    PB.showMessage(self.$wizard, 'Video limit reached. Upgrade to Pro for more videos.', 'error');
                    return;
                }

                var $item = $('<div class="pb-video-item">' +
                    '<input type="url" name="video_links[]" placeholder="https://youtube.com/watch?v=...">' +
                    '<button type="button" class="pb-remove-video" title="Remove">&times;</button>' +
                    '</div>');
                $list.append($item);
            });

            // Remove video
            this.$wizard.on('click', '.pb-remove-video', function() {
                $(this).closest('.pb-video-item').remove();
            });

            // Remove photo
            this.$wizard.on('click', '.pb-remove-photo', function() {
                $(this).closest('.pb-photo-item').remove();
                self.updatePhotoInput();
            });

            // Upload photos
            this.$wizard.on('click', '#pb-upload-photos', function(e) {
                e.preventDefault();
                self.openMediaUploader();
            });
        },

        /**
         * Open WordPress Media Uploader.
         */
        openMediaUploader: function() {
            var self = this;
            var $gallery = $('.pb-photo-gallery');
            var limit = parseInt($gallery.data('limit'));
            var currentCount = $('#pb-photo-grid').find('.pb-photo-item').length;

            if (limit > 0 && currentCount >= limit) {
                PB.showMessage(this.$wizard, 'Photo limit reached. Upgrade to Pro for more photos.', 'error');
                return;
            }

            // If frame already exists, open it
            if (this.mediaFrame) {
                this.mediaFrame.open();
                return;
            }

            // Create media frame
            this.mediaFrame = wp.media({
                title: 'Select Profile Photos',
                button: { text: 'Use Selected Photos' },
                multiple: true,
                library: { type: 'image' }
            });

            // When images are selected
            this.mediaFrame.on('select', function() {
                var attachments = self.mediaFrame.state().get('selection').toJSON();
                var $grid = $('#pb-photo-grid');
                var remaining = limit > 0 ? limit - currentCount : attachments.length;

                attachments.slice(0, remaining).forEach(function(attachment) {
                    var imgSrc = attachment.sizes && attachment.sizes.medium
                        ? attachment.sizes.medium.url
                        : attachment.url;

                    var $item = $('<div class="pb-photo-item" data-id="' + attachment.id + '">' +
                        '<img src="' + imgSrc + '" alt="">' +
                        '<button type="button" class="pb-remove-photo" title="Remove">&times;</button>' +
                        '</div>');
                    $grid.append($item);
                });

                self.updatePhotoInput();
            });

            this.mediaFrame.open();
        },

        /**
         * Update hidden photo input.
         */
        updatePhotoInput: function() {
            var ids = [];
            $('#pb-photo-grid').find('.pb-photo-item').each(function() {
                ids.push($(this).data('id'));
            });
            $('#pb-gallery-images').val(ids.join(','));
        },

        /**
         * Navigate to tab.
         */
        goToTab: function(index) {
            if (index < 0 || index >= this.tabs.length) return;

            this.currentTab = index;
            var tabName = this.tabs[index];

            // Update tab buttons
            this.$tabs.removeClass('active').eq(index).addClass('active');

            // Update panels
            this.$panels.removeClass('active');
            this.$panels.filter('[data-panel="' + tabName + '"]').addClass('active');

            // Update navigation buttons
            this.$wizard.find('.pb-wizard-prev').toggle(index > 0);
            this.$wizard.find('.pb-wizard-next').toggle(index < this.tabs.length - 1);
            this.$wizard.find('.pb-wizard-save').toggle(index === this.tabs.length - 1);

            // Scroll to top of wizard
            $('html, body').animate({ scrollTop: this.$wizard.offset().top - 50 }, 200);
        },

        /**
         * Save profile via AJAX.
         */
        saveProfile: function() {
            var self = this;
            var $btn = this.$wizard.find('.pb-wizard-save');

            $btn.prop('disabled', true).find('.dashicons').removeClass('dashicons-saved').addClass('dashicons-update spin');

            // Prepare form data
            var formData = new FormData(this.$form[0]);
            formData.append('action', 'pb_update_performer_profile');
            formData.append('nonce', peanutBooker.nonces.performer);

            // Convert gallery_images from comma-separated to array
            var galleryIds = $('#pb-gallery-images').val();
            if (galleryIds) {
                formData.delete('gallery_images');
                galleryIds.split(',').forEach(function(id) {
                    if (id) formData.append('gallery_images[]', id);
                });
            }

            // Collect video links
            formData.delete('video_links[]');
            $('#pb-video-list').find('input[name="video_links[]"]').each(function() {
                var url = $(this).val();
                if (url) formData.append('video_links[]', url);
            });

            // Collect categories
            formData.delete('categories[]');
            this.$form.find('input[name="categories[]"]:checked').each(function() {
                formData.append('categories[]', $(this).val());
            });

            // Collect service areas
            formData.delete('service_areas[]');
            this.$form.find('input[name="service_areas[]"]:checked').each(function() {
                formData.append('service_areas[]', $(this).val());
            });

            $.ajax({
                url: peanutBooker.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        PB.showMessage(self.$wizard, response.data.message || 'Profile saved successfully!', 'success');

                        // Update progress bar
                        if (response.data.completeness !== undefined) {
                            self.$wizard.find('.pb-progress-bar').css('width', response.data.completeness + '%');
                            self.$wizard.find('.pb-progress-text').text(response.data.completeness + '% Complete');
                        }
                    } else {
                        PB.showMessage(self.$wizard, response.data.message || 'Error saving profile.', 'error');
                    }
                    $btn.prop('disabled', false).find('.dashicons').removeClass('dashicons-update spin').addClass('dashicons-saved');
                },
                error: function() {
                    PB.showMessage(self.$wizard, 'Error saving profile. Please try again.', 'error');
                    $btn.prop('disabled', false).find('.dashicons').removeClass('dashicons-update spin').addClass('dashicons-saved');
                }
            });
        }
    };

    /**
     * External Gig / Calendar Date Selection functionality.
     */
    var ExternalGig = {
        selectedDates: [],
        lastClickedDate: null,

        /**
         * Initialize.
         */
        init: function() {
            var $calendar = $('.pb-availability-calendar.pb-multi-select');
            if ($calendar.length === 0) return;

            this.$calendar = $calendar;
            this.$modal = $('#pb-external-gig-modal');
            this.$form = $('#pb-external-gig-form');
            this.$selectedInfo = $('#pb-selected-dates-info');

            this.bindEvents();
        },

        /**
         * Bind events.
         */
        bindEvents: function() {
            var self = this;

            // Calendar date click (with shift support for range)
            this.$calendar.on('click', '.pb-calendar-day.pb-clickable', function(e) {
                var date = $(this).data('date');
                var status = $(this).data('status') || 'available';

                // Don't allow selecting past or booked dates
                if (status === 'past' || status === 'booked') return;

                if (e.shiftKey && self.lastClickedDate) {
                    self.selectRange(self.lastClickedDate, date);
                } else {
                    self.toggleDate($(this), date);
                }

                self.lastClickedDate = date;
                self.updateUI();
            });

            // Clear selection
            $(document).on('click', '#pb-clear-selection', function() {
                self.clearSelection();
            });

            // Quick block button
            $(document).on('click', '#pb-quick-block', function() {
                self.quickBlock();
            });

            // Add external gig button - open modal
            $(document).on('click', '#pb-add-external-gig', function() {
                self.openModal();
            });

            // Unblock dates button
            $(document).on('click', '#pb-unblock-dates', function() {
                self.unblockDates();
            });

            // Modal close
            this.$modal.on('click', '.pb-modal-close, .pb-modal-overlay, .pb-modal-cancel', function() {
                self.closeModal();
            });

            // External gig form submission
            this.$form.on('submit', function(e) {
                e.preventDefault();
                self.submitExternalGig();
            });

            // Calendar navigation - preserve selection display
            $(document).on('click', '.pb-calendar-nav', function() {
                setTimeout(function() {
                    self.restoreSelectionDisplay();
                }, 500);
            });
        },

        /**
         * Toggle date selection.
         */
        toggleDate: function($cell, date) {
            var index = this.selectedDates.indexOf(date);

            if (index > -1) {
                this.selectedDates.splice(index, 1);
                $cell.removeClass('pb-selected');
            } else {
                this.selectedDates.push(date);
                $cell.addClass('pb-selected');
            }
        },

        /**
         * Select range of dates.
         */
        selectRange: function(startDate, endDate) {
            var start = new Date(startDate);
            var end = new Date(endDate);

            // Ensure start is before end
            if (start > end) {
                var temp = start;
                start = end;
                end = temp;
            }

            var current = new Date(start);
            while (current <= end) {
                var dateStr = current.toISOString().split('T')[0];
                var $cell = this.$calendar.find('.pb-calendar-day[data-date="' + dateStr + '"]');

                if ($cell.hasClass('pb-clickable') && this.selectedDates.indexOf(dateStr) === -1) {
                    this.selectedDates.push(dateStr);
                    $cell.addClass('pb-selected');
                }

                current.setDate(current.getDate() + 1);
            }
        },

        /**
         * Clear all selections.
         */
        clearSelection: function() {
            this.selectedDates = [];
            this.$calendar.find('.pb-calendar-day').removeClass('pb-selected');
            this.updateUI();
        },

        /**
         * Restore selection display after calendar navigation.
         */
        restoreSelectionDisplay: function() {
            var self = this;
            this.selectedDates.forEach(function(date) {
                self.$calendar.find('.pb-calendar-day[data-date="' + date + '"]').addClass('pb-selected');
            });
        },

        /**
         * Update UI elements based on selection.
         */
        updateUI: function() {
            var count = this.selectedDates.length;
            var hasSelection = count > 0;

            // Update selected count display
            this.$selectedInfo.toggle(hasSelection);
            this.$selectedInfo.find('.pb-selected-count').text(
                count + ' date' + (count !== 1 ? 's' : '') + ' selected'
            );

            // Enable/disable action buttons
            $('#pb-quick-block, #pb-add-external-gig, #pb-unblock-dates').prop('disabled', !hasSelection);
        },

        /**
         * Quick block selected dates.
         */
        quickBlock: function() {
            var self = this;
            var $btn = $('#pb-quick-block');

            if (this.selectedDates.length === 0) return;

            $btn.prop('disabled', true).find('.dashicons').addClass('spin');

            $.ajax({
                url: peanutBooker.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pb_block_dates',
                    dates: this.selectedDates,
                    nonce: this.$calendar.find('[data-performer-id]').length
                        ? peanutBooker.nonces.availability
                        : $('#pb_availability_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        PB.showMessage(self.$calendar.parent(), response.data.message, 'success');
                        self.clearSelection();
                        // Reload calendar
                        location.reload();
                    } else {
                        PB.showMessage(self.$calendar.parent(), response.data.message || 'Error blocking dates.', 'error');
                    }
                    $btn.prop('disabled', false).find('.dashicons').removeClass('spin');
                },
                error: function() {
                    PB.showMessage(self.$calendar.parent(), 'Error blocking dates.', 'error');
                    $btn.prop('disabled', false).find('.dashicons').removeClass('spin');
                }
            });
        },

        /**
         * Unblock selected dates.
         */
        unblockDates: function() {
            var self = this;
            var $btn = $('#pb-unblock-dates');

            if (this.selectedDates.length === 0) return;

            $btn.prop('disabled', true).find('.dashicons').addClass('spin');

            $.ajax({
                url: peanutBooker.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pb_unblock_dates',
                    dates: this.selectedDates,
                    nonce: peanutBooker.nonces.availability
                },
                success: function(response) {
                    if (response.success) {
                        PB.showMessage(self.$calendar.parent(), response.data.message, 'success');
                        self.clearSelection();
                        location.reload();
                    } else {
                        PB.showMessage(self.$calendar.parent(), response.data.message || 'Error unblocking dates.', 'error');
                    }
                    $btn.prop('disabled', false).find('.dashicons').removeClass('spin');
                },
                error: function() {
                    PB.showMessage(self.$calendar.parent(), 'Error unblocking dates.', 'error');
                    $btn.prop('disabled', false).find('.dashicons').removeClass('spin');
                }
            });
        },

        /**
         * Open external gig modal.
         */
        openModal: function() {
            if (this.selectedDates.length === 0) return;

            // Populate dates in modal
            var sortedDates = this.selectedDates.slice().sort();
            $('#pb-gig-dates').val(sortedDates.join(','));

            // Format dates for display
            var displayDates = sortedDates.map(function(date) {
                var d = new Date(date + 'T00:00:00');
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            });
            $('#pb-gig-dates-display').text(displayDates.join(', '));

            // Show modal
            this.$modal.fadeIn(200);
            $('body').addClass('pb-modal-open');
        },

        /**
         * Close modal.
         */
        closeModal: function() {
            this.$modal.fadeOut(200);
            $('body').removeClass('pb-modal-open');
            this.$form[0].reset();
        },

        /**
         * Submit external gig form.
         */
        submitExternalGig: function() {
            var self = this;
            var $btn = this.$form.find('button[type="submit"]');

            $btn.prop('disabled', true).find('.dashicons').addClass('spin');

            // Build data object
            var formData = {
                action: 'pb_block_external_gig',
                nonce: this.$form.find('[name="pb_availability_nonce"]').val(),
                dates: this.selectedDates,
                event_name: this.$form.find('[name="event_name"]').val(),
                venue_name: this.$form.find('[name="venue_name"]').val(),
                event_type: this.$form.find('[name="event_type"]').val(),
                event_location: this.$form.find('[name="event_location"]').val(),
                notes: this.$form.find('[name="notes"]').val()
            };

            $.ajax({
                url: peanutBooker.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        self.closeModal();
                        PB.showMessage(self.$calendar.parent(), response.data.message, 'success');
                        self.clearSelection();
                        location.reload();
                    } else {
                        PB.showMessage(self.$form, response.data.message || 'Error adding external gig.', 'error');
                    }
                    $btn.prop('disabled', false).find('.dashicons').removeClass('spin');
                },
                error: function() {
                    PB.showMessage(self.$form, 'Error adding external gig.', 'error');
                    $btn.prop('disabled', false).find('.dashicons').removeClass('spin');
                }
            });
        }
    };

    // Initialize on document ready.
    $(document).ready(function() {
        PB.init();
        ProfileWizard.init();
        ExternalGig.init();
    });

})(jQuery);
