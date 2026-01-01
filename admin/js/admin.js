/**
 * Peanut Booker - Admin JavaScript
 *
 * @package Peanut_Booker
 */

(function($) {
    'use strict';

    /**
     * Admin functionality.
     */
    var PBAdmin = {

        /**
         * Initialize.
         */
        init: function() {
            this.bindEvents();
            this.initTabs();
        },

        /**
         * Bind events.
         */
        bindEvents: function() {
            // Payout release.
            $(document).on('click', '.pb-release-payout', this.releasePayout);

            // Review arbitration.
            $(document).on('submit', '.pb-arbitration-form', this.handleArbitration);

            // Performer verification.
            $(document).on('click', '.pb-verify-performer', this.verifyPerformer);

            // Bulk actions.
            $(document).on('click', '.pb-bulk-action', this.handleBulkAction);

            // Settings save feedback.
            $(document).on('submit', 'form.pb-settings-form', this.savingIndicator);

            // Confirm dangerous actions.
            $(document).on('click', '[data-confirm]', this.confirmAction);

            // Toggle review details.
            $(document).on('click', '.pb-toggle-details', this.toggleDetails);

            // Date range filter.
            $(document).on('change', '.pb-date-filter', this.filterByDate);

            // Export functionality.
            $(document).on('click', '.pb-export-btn', this.exportData);

            // Create plugin pages.
            $(document).on('click', '#pb-create-pages', this.createPages);
        },

        /**
         * Initialize tabs.
         */
        initTabs: function() {
            var $tabs = $('.pb-settings-tabs a');

            $tabs.on('click', function(e) {
                var href = $(this).attr('href');
                if (href.indexOf('#') === 0) {
                    e.preventDefault();
                    $tabs.removeClass('active');
                    $(this).addClass('active');
                    $('.pb-settings-panel').hide();
                    $(href).show();
                }
            });
        },

        /**
         * Release payout.
         */
        releasePayout: function(e) {
            e.preventDefault();

            var $btn = $(this);
            var bookingId = $btn.data('booking-id');

            if (!confirm(pbAdmin.strings.confirm)) {
                return;
            }

            $btn.prop('disabled', true).text(pbAdmin.strings.saving);

            $.ajax({
                url: pbAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pb_admin_release_payout',
                    booking_id: bookingId,
                    nonce: pbAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $btn.closest('tr').fadeOut(function() {
                            $(this).remove();
                        });
                        PBAdmin.showNotice('Payout released successfully.', 'success');
                    } else {
                        PBAdmin.showNotice(response.data.message || 'Error releasing payout.', 'error');
                        $btn.prop('disabled', false).text('Release');
                    }
                },
                error: function() {
                    PBAdmin.showNotice('Network error. Please try again.', 'error');
                    $btn.prop('disabled', false).text('Release');
                }
            });
        },

        /**
         * Handle review arbitration.
         */
        handleArbitration: function(e) {
            e.preventDefault();

            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');

            $btn.prop('disabled', true).text(pbAdmin.strings.saving);

            $.ajax({
                url: pbAdmin.ajaxUrl,
                type: 'POST',
                data: $form.serialize() + '&action=pb_admin_arbitrate_review&nonce=' + pbAdmin.nonce,
                success: function(response) {
                    if (response.success) {
                        $form.closest('.pb-review-item').fadeOut();
                        PBAdmin.showNotice('Review arbitration completed.', 'success');
                    } else {
                        PBAdmin.showNotice(response.data.message || 'Error processing arbitration.', 'error');
                        $btn.prop('disabled', false).text('Submit Decision');
                    }
                },
                error: function() {
                    PBAdmin.showNotice('Network error. Please try again.', 'error');
                    $btn.prop('disabled', false).text('Submit Decision');
                }
            });
        },

        /**
         * Verify performer.
         */
        verifyPerformer: function(e) {
            e.preventDefault();

            var $btn = $(this);
            var performerId = $btn.data('performer-id');
            var verify = $btn.data('verify') ? 1 : 0;

            $btn.prop('disabled', true);

            $.ajax({
                url: pbAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pb_admin_verify_performer',
                    performer_id: performerId,
                    verify: verify,
                    nonce: pbAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        PBAdmin.showNotice(response.data.message || 'Error updating verification.', 'error');
                        $btn.prop('disabled', false);
                    }
                },
                error: function() {
                    PBAdmin.showNotice('Network error. Please try again.', 'error');
                    $btn.prop('disabled', false);
                }
            });
        },

        /**
         * Handle bulk action.
         */
        handleBulkAction: function(e) {
            e.preventDefault();

            var action = $('#pb-bulk-action-select').val();
            var $checked = $('input.pb-item-checkbox:checked');

            if (!action) {
                PBAdmin.showNotice('Please select an action.', 'warning');
                return;
            }

            if ($checked.length === 0) {
                PBAdmin.showNotice('Please select at least one item.', 'warning');
                return;
            }

            if (!confirm(pbAdmin.strings.confirm)) {
                return;
            }

            var ids = [];
            $checked.each(function() {
                ids.push($(this).val());
            });

            $.ajax({
                url: pbAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pb_admin_bulk_action',
                    bulk_action: action,
                    ids: ids,
                    nonce: pbAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        PBAdmin.showNotice(response.data.message || 'Error processing action.', 'error');
                    }
                },
                error: function() {
                    PBAdmin.showNotice('Network error. Please try again.', 'error');
                }
            });
        },

        /**
         * Show saving indicator.
         */
        savingIndicator: function() {
            var $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).text(pbAdmin.strings.saving);
        },

        /**
         * Confirm action.
         */
        confirmAction: function(e) {
            var message = $(this).data('confirm') || pbAdmin.strings.confirm;
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        },

        /**
         * Toggle details panel.
         */
        toggleDetails: function(e) {
            e.preventDefault();
            var target = $(this).data('target');
            $(target).slideToggle();
        },

        /**
         * Filter by date.
         */
        filterByDate: function() {
            var startDate = $('#pb-filter-start').val();
            var endDate = $('#pb-filter-end').val();

            if (startDate || endDate) {
                var url = new URL(window.location.href);
                if (startDate) url.searchParams.set('start_date', startDate);
                if (endDate) url.searchParams.set('end_date', endDate);
                window.location.href = url.toString();
            }
        },

        /**
         * Export data.
         */
        exportData: function(e) {
            e.preventDefault();

            var type = $(this).data('export-type');
            var format = $(this).data('export-format') || 'csv';

            window.location.href = pbAdmin.ajaxUrl + '?action=pb_admin_export&type=' + type + '&format=' + format + '&nonce=' + pbAdmin.nonce;
        },

        /**
         * Create plugin pages.
         */
        createPages: function(e) {
            e.preventDefault();

            var $btn = $(this);
            var originalText = $btn.text();

            $btn.prop('disabled', true).text(pbAdmin.strings.saving || 'Creating...');

            $.ajax({
                url: pbAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pb_create_pages',
                    nonce: pbAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        PBAdmin.showNotice(response.data.message, 'success');
                        if (response.data.reload) {
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            $btn.prop('disabled', false).text(originalText);
                        }
                    } else {
                        PBAdmin.showNotice(response.data.message || 'Error creating pages.', 'error');
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    PBAdmin.showNotice('Network error. Please try again.', 'error');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Show admin notice.
         */
        showNotice: function(message, type) {
            type = type || 'info';

            var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');

            $('.wrap h1').first().after($notice);

            // Auto dismiss after 5 seconds.
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };

    // Initialize on document ready.
    $(document).ready(function() {
        PBAdmin.init();
    });

    /**
     * Performer Editor functionality.
     */
    var PBPerformerEditor = {

        /**
         * Initialize.
         */
        init: function() {
            if (!$('.pb-performer-editor').length) {
                return;
            }

            this.bindEvents();
        },

        /**
         * Bind events.
         */
        bindEvents: function() {
            // Form submission.
            $(document).on('submit', '#pb-performer-editor-form', this.savePerformer.bind(this));

            // Toggle fields based on checkboxes.
            $(document).on('change', 'input[name="pb_sale_active"]', this.toggleSalePrice);
            $(document).on('change', 'input[name="pb_travel_willing"]', this.toggleTravelRadius);

            // Thumbnail upload.
            $(document).on('click', '.pb-upload-thumbnail', this.uploadThumbnail);
            $(document).on('click', '.pb-remove-thumbnail', this.removeThumbnail);

            // Gallery upload.
            $(document).on('click', '#pb-add-gallery-photos', this.uploadGalleryPhotos);
            $(document).on('click', '.pb-remove-gallery-item', this.removeGalleryItem);

            // Video management.
            $(document).on('click', '#pb-add-video', this.addVideoField);
            $(document).on('click', '.pb-remove-video', this.removeVideoField);
        },

        /**
         * Save performer via AJAX.
         */
        savePerformer: function(e) {
            e.preventDefault();

            var $form = $(e.target);
            var $btn = $('#pb-save-performer');
            var $saveText = $btn.find('.pb-save-text');
            var $savingText = $btn.find('.pb-saving-text');
            var $status = $('.pb-save-status');

            // Show saving state.
            $saveText.hide();
            $savingText.show();
            $btn.prop('disabled', true);
            $status.text('').removeClass('pb-success pb-error');

            $.ajax({
                url: pbAdmin.ajaxUrl,
                type: 'POST',
                data: $form.serialize() + '&action=pb_admin_save_performer',
                success: function(response) {
                    $saveText.show();
                    $savingText.hide();
                    $btn.prop('disabled', false);

                    if (response.success) {
                        $status.text('Saved!').addClass('pb-success');
                        setTimeout(function() {
                            $status.text('');
                        }, 3000);
                    } else {
                        $status.text(response.data.message || 'Error saving.').addClass('pb-error');
                    }
                },
                error: function() {
                    $saveText.show();
                    $savingText.hide();
                    $btn.prop('disabled', false);
                    $status.text('Network error. Please try again.').addClass('pb-error');
                }
            });
        },

        /**
         * Toggle sale price field.
         */
        toggleSalePrice: function() {
            var $field = $('.pb-sale-price-field');
            if ($(this).is(':checked')) {
                $field.removeClass('pb-hidden');
            } else {
                $field.addClass('pb-hidden');
            }
        },

        /**
         * Toggle travel radius field.
         */
        toggleTravelRadius: function() {
            var $field = $('.pb-travel-radius-field');
            if ($(this).is(':checked')) {
                $field.removeClass('pb-hidden');
            } else {
                $field.addClass('pb-hidden');
            }
        },

        /**
         * Upload thumbnail.
         */
        uploadThumbnail: function(e) {
            e.preventDefault();

            var frame = wp.media({
                title: 'Select Profile Photo',
                button: { text: 'Use this photo' },
                multiple: false,
                library: { type: 'image' }
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();

                // Update preview.
                var $preview = $('.pb-current-photo');
                if ($preview.find('img').length) {
                    $preview.find('img').attr('src', attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url);
                } else {
                    $preview.html('<img src="' + (attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url) + '" class="pb-thumbnail-preview">');
                }

                // Update hidden input.
                $('#pb_thumbnail_id').val(attachment.id);

                // Show remove button.
                if (!$('.pb-remove-thumbnail').length) {
                    $('.pb-photo-actions').append('<button type="button" class="button pb-remove-thumbnail">Remove</button>');
                }
            });

            frame.open();
        },

        /**
         * Remove thumbnail.
         */
        removeThumbnail: function(e) {
            e.preventDefault();

            $('.pb-current-photo').html('<div class="pb-no-photo"><span class="dashicons dashicons-camera"></span></div>');
            $('#pb_thumbnail_id').val('');
            $(this).remove();
        },

        /**
         * Upload gallery photos.
         */
        uploadGalleryPhotos: function(e) {
            e.preventDefault();

            var $grid = $('#pb-gallery-grid');
            var currentIds = $('#pb_gallery_images').val() ? $('#pb_gallery_images').val().split(',') : [];
            var limit = parseInt($('.pb-gallery-grid').closest('.pb-card-body').find('.pb-card-header .pb-card-hint').text().match(/of (\d+)/)[1], 10) || 5;
            var remaining = limit - currentIds.filter(function(id) { return id; }).length;

            if (remaining <= 0) {
                alert('Photo limit reached.');
                return;
            }

            var frame = wp.media({
                title: 'Select Gallery Photos',
                button: { text: 'Add to gallery' },
                multiple: true,
                library: { type: 'image' }
            });

            frame.on('select', function() {
                var selection = frame.state().get('selection').toJSON();
                var added = 0;

                selection.forEach(function(attachment) {
                    if (added >= remaining) return;
                    if (currentIds.indexOf(attachment.id.toString()) !== -1) return;

                    currentIds.push(attachment.id);

                    var imgUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
                    var $item = $(
                        '<div class="pb-gallery-item" data-id="' + attachment.id + '">' +
                            '<img src="' + imgUrl + '">' +
                            '<button type="button" class="pb-remove-gallery-item" title="Remove">' +
                                '<span class="dashicons dashicons-no-alt"></span>' +
                            '</button>' +
                        '</div>'
                    );

                    $grid.find('.pb-gallery-add').before($item);
                    added++;
                });

                // Update hidden input.
                $('#pb_gallery_images').val(currentIds.filter(function(id) { return id; }).join(','));

                // Check if limit reached.
                var newCount = currentIds.filter(function(id) { return id; }).length;
                if (newCount >= limit) {
                    $grid.find('.pb-gallery-add').remove();
                }
            });

            frame.open();
        },

        /**
         * Remove gallery item.
         */
        removeGalleryItem: function(e) {
            e.preventDefault();

            var $item = $(this).closest('.pb-gallery-item');
            var id = $item.data('id').toString();
            var currentIds = $('#pb_gallery_images').val().split(',').filter(function(v) { return v && v !== id; });

            // Update hidden input.
            $('#pb_gallery_images').val(currentIds.join(','));

            // Remove item.
            $item.fadeOut(function() {
                $(this).remove();

                // Re-add the add button if needed.
                var $grid = $('#pb-gallery-grid');
                if (!$grid.find('.pb-gallery-add').length) {
                    $grid.append(
                        '<button type="button" class="pb-gallery-add" id="pb-add-gallery-photos">' +
                            '<span class="dashicons dashicons-plus-alt2"></span>' +
                            '<span>Add Photos</span>' +
                        '</button>'
                    );
                }
            });
        },

        /**
         * Add video field.
         */
        addVideoField: function(e) {
            e.preventDefault();

            var template = $('#tmpl-pb-video-item').html();
            $('#pb-video-list').append(template);
        },

        /**
         * Remove video field.
         */
        removeVideoField: function(e) {
            e.preventDefault();
            $(this).closest('.pb-video-item').remove();
        }
    };

    // Initialize performer editor.
    $(document).ready(function() {
        PBPerformerEditor.init();
    });

})(jQuery);
