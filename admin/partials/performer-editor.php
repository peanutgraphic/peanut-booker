<?php
/**
 * Clean performer editor - frontend-style interface for admin.
 *
 * @package Peanut_Booker
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

$post_id = isset( $_GET['performer_id'] ) ? absint( $_GET['performer_id'] ) : 0;

if ( ! $post_id || 'pb_performer' !== get_post_type( $post_id ) ) {
    echo '<div class="notice notice-error"><p>' . esc_html__( 'Invalid performer.', 'peanut-booker' ) . '</p></div>';
    return;
}

$post = get_post( $post_id );
$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'basic';

// Get all meta data.
$user_id          = get_post_meta( $post_id, 'pb_user_id', true );
$stage_name       = get_post_meta( $post_id, 'pb_stage_name', true );
$tagline          = get_post_meta( $post_id, 'pb_tagline', true );
$experience_years = get_post_meta( $post_id, 'pb_experience_years', true );
$website          = get_post_meta( $post_id, 'pb_website', true );
$phone            = get_post_meta( $post_id, 'pb_phone', true );
$email_public     = get_post_meta( $post_id, 'pb_email_public', true );

$hourly_rate        = get_post_meta( $post_id, 'pb_hourly_rate', true );
$minimum_booking    = get_post_meta( $post_id, 'pb_minimum_booking', true );
$deposit_percentage = get_post_meta( $post_id, 'pb_deposit_percentage', true );
$sale_price         = get_post_meta( $post_id, 'pb_sale_price', true );
$sale_active        = get_post_meta( $post_id, 'pb_sale_active', true );

$location_city  = get_post_meta( $post_id, 'pb_location_city', true );
$location_state = get_post_meta( $post_id, 'pb_location_state', true );
$travel_willing = get_post_meta( $post_id, 'pb_travel_willing', true );
$travel_radius  = get_post_meta( $post_id, 'pb_travel_radius', true );

$gallery_images = get_post_meta( $post_id, 'pb_gallery_images', true );
$gallery_ids    = $gallery_images ? explode( ',', $gallery_images ) : array();
$video_links    = get_post_meta( $post_id, 'pb_video_links', true );
$videos         = $video_links ? array_filter( explode( "\n", $video_links ) ) : array();

// Categories and areas.
$categories     = get_terms( array( 'taxonomy' => 'pb_performer_category', 'hide_empty' => false ) );
$areas          = get_terms( array( 'taxonomy' => 'pb_service_area', 'hide_empty' => false ) );
$selected_cats  = wp_get_post_terms( $post_id, 'pb_performer_category', array( 'fields' => 'ids' ) );
$selected_areas = wp_get_post_terms( $post_id, 'pb_service_area', array( 'fields' => 'ids' ) );

// Get linked user info.
$linked_user = $user_id ? get_userdata( $user_id ) : null;

// Get performer record for tier info.
global $wpdb;
$performer = $wpdb->get_row( $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}pb_performers WHERE profile_id = %d",
    $post_id
) );
$tier = $performer ? $performer->tier : 'free';

// Thumbnail.
$thumbnail_id = get_post_thumbnail_id( $post_id );
?>

<div class="wrap pb-performer-editor">
    <div class="pb-editor-header">
        <div class="pb-editor-header-left">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-performers' ) ); ?>" class="pb-back-link">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
                <?php esc_html_e( 'Back to Performers', 'peanut-booker' ); ?>
            </a>
            <h1><?php echo esc_html( $stage_name ?: $post->post_title ); ?></h1>
            <div class="pb-editor-badges">
                <span class="pb-tier pb-tier-<?php echo esc_attr( $tier ); ?>"><?php echo esc_html( ucfirst( $tier ) ); ?></span>
                <span class="pb-status pb-status-<?php echo esc_attr( $post->post_status ); ?>"><?php echo esc_html( ucfirst( $post->post_status ) ); ?></span>
            </div>
        </div>
        <div class="pb-editor-header-right">
            <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="button" target="_blank">
                <span class="dashicons dashicons-external"></span>
                <?php esc_html_e( 'View Profile', 'peanut-booker' ); ?>
            </a>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="pb-editor-tabs">
        <a href="?page=pb-edit-performer&performer_id=<?php echo $post_id; ?>&tab=basic" class="pb-editor-tab <?php echo 'basic' === $active_tab ? 'active' : ''; ?>">
            <span class="dashicons dashicons-admin-users"></span>
            <?php esc_html_e( 'Basic Info', 'peanut-booker' ); ?>
        </a>
        <a href="?page=pb-edit-performer&performer_id=<?php echo $post_id; ?>&tab=media" class="pb-editor-tab <?php echo 'media' === $active_tab ? 'active' : ''; ?>">
            <span class="dashicons dashicons-format-gallery"></span>
            <?php esc_html_e( 'Photos & Videos', 'peanut-booker' ); ?>
        </a>
        <a href="?page=pb-edit-performer&performer_id=<?php echo $post_id; ?>&tab=pricing" class="pb-editor-tab <?php echo 'pricing' === $active_tab ? 'active' : ''; ?>">
            <span class="dashicons dashicons-money-alt"></span>
            <?php esc_html_e( 'Pricing', 'peanut-booker' ); ?>
        </a>
        <a href="?page=pb-edit-performer&performer_id=<?php echo $post_id; ?>&tab=location" class="pb-editor-tab <?php echo 'location' === $active_tab ? 'active' : ''; ?>">
            <span class="dashicons dashicons-location"></span>
            <?php esc_html_e( 'Location', 'peanut-booker' ); ?>
        </a>
        <a href="?page=pb-edit-performer&performer_id=<?php echo $post_id; ?>&tab=categories" class="pb-editor-tab <?php echo 'categories' === $active_tab ? 'active' : ''; ?>">
            <span class="dashicons dashicons-tag"></span>
            <?php esc_html_e( 'Categories', 'peanut-booker' ); ?>
        </a>
    </div>

    <form method="post" id="pb-performer-editor-form" class="pb-editor-form">
        <?php wp_nonce_field( 'pb_admin_performer_edit', 'pb_admin_nonce' ); ?>
        <input type="hidden" name="action" value="pb_admin_save_performer">
        <input type="hidden" name="performer_id" value="<?php echo esc_attr( $post_id ); ?>">
        <input type="hidden" name="current_tab" value="<?php echo esc_attr( $active_tab ); ?>">

        <!-- Basic Info Tab -->
        <div class="pb-editor-panel <?php echo 'basic' === $active_tab ? 'active' : ''; ?>" data-tab="basic">
            <div class="pb-editor-card">
                <div class="pb-card-header">
                    <h2><?php esc_html_e( 'Profile Photo', 'peanut-booker' ); ?></h2>
                </div>
                <div class="pb-card-body">
                    <div class="pb-profile-photo-upload">
                        <div class="pb-current-photo">
                            <?php if ( $thumbnail_id ) : ?>
                                <?php echo wp_get_attachment_image( $thumbnail_id, 'thumbnail', false, array( 'class' => 'pb-thumbnail-preview' ) ); ?>
                            <?php else : ?>
                                <div class="pb-no-photo">
                                    <span class="dashicons dashicons-camera"></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="pb-photo-actions">
                            <button type="button" class="button pb-upload-thumbnail" data-target="pb_thumbnail_id">
                                <?php esc_html_e( 'Upload Photo', 'peanut-booker' ); ?>
                            </button>
                            <?php if ( $thumbnail_id ) : ?>
                                <button type="button" class="button pb-remove-thumbnail">
                                    <?php esc_html_e( 'Remove', 'peanut-booker' ); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="pb_thumbnail_id" id="pb_thumbnail_id" value="<?php echo esc_attr( $thumbnail_id ); ?>">
                    </div>
                </div>
            </div>

            <div class="pb-editor-card">
                <div class="pb-card-header">
                    <h2><?php esc_html_e( 'Basic Information', 'peanut-booker' ); ?></h2>
                </div>
                <div class="pb-card-body">
                    <div class="pb-form-grid">
                        <div class="pb-form-field">
                            <label for="pb_stage_name"><?php esc_html_e( 'Stage Name', 'peanut-booker' ); ?></label>
                            <input type="text" id="pb_stage_name" name="pb_stage_name" value="<?php echo esc_attr( $stage_name ); ?>" placeholder="<?php esc_attr_e( 'Your performer name or alias', 'peanut-booker' ); ?>">
                        </div>
                        <div class="pb-form-field">
                            <label for="pb_experience_years"><?php esc_html_e( 'Years Experience', 'peanut-booker' ); ?></label>
                            <input type="number" id="pb_experience_years" name="pb_experience_years" value="<?php echo esc_attr( $experience_years ); ?>" min="0" max="50">
                        </div>
                    </div>

                    <div class="pb-form-field pb-full-width">
                        <label for="pb_tagline"><?php esc_html_e( 'Tagline', 'peanut-booker' ); ?></label>
                        <input type="text" id="pb_tagline" name="pb_tagline" value="<?php echo esc_attr( $tagline ); ?>" maxlength="100" placeholder="<?php esc_attr_e( 'A short catchy description', 'peanut-booker' ); ?>">
                        <span class="pb-field-hint"><?php esc_html_e( 'Shown on cards and search results (max 100 characters)', 'peanut-booker' ); ?></span>
                    </div>

                    <div class="pb-form-field pb-full-width">
                        <label for="pb_bio"><?php esc_html_e( 'Bio', 'peanut-booker' ); ?></label>
                        <textarea id="pb_bio" name="pb_bio" rows="5" placeholder="<?php esc_attr_e( 'Tell clients about yourself, your experience, and what makes you special...', 'peanut-booker' ); ?>"><?php echo esc_textarea( $post->post_content ); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="pb-editor-card">
                <div class="pb-card-header">
                    <h2><?php esc_html_e( 'Contact Information', 'peanut-booker' ); ?></h2>
                    <span class="pb-card-hint"><?php esc_html_e( 'Optional - shown on your public profile', 'peanut-booker' ); ?></span>
                </div>
                <div class="pb-card-body">
                    <div class="pb-form-grid pb-form-grid-3">
                        <div class="pb-form-field">
                            <label for="pb_website"><?php esc_html_e( 'Website', 'peanut-booker' ); ?></label>
                            <input type="url" id="pb_website" name="pb_website" value="<?php echo esc_attr( $website ); ?>" placeholder="https://">
                        </div>
                        <div class="pb-form-field">
                            <label for="pb_phone"><?php esc_html_e( 'Phone', 'peanut-booker' ); ?></label>
                            <input type="tel" id="pb_phone" name="pb_phone" value="<?php echo esc_attr( $phone ); ?>" placeholder="(555) 123-4567">
                        </div>
                        <div class="pb-form-field">
                            <label for="pb_email_public"><?php esc_html_e( 'Public Email', 'peanut-booker' ); ?></label>
                            <input type="email" id="pb_email_public" name="pb_email_public" value="<?php echo esc_attr( $email_public ); ?>" placeholder="booking@example.com">
                        </div>
                    </div>
                </div>
            </div>

            <?php if ( current_user_can( 'manage_options' ) ) : ?>
            <div class="pb-editor-card pb-admin-only">
                <div class="pb-card-header">
                    <h2><?php esc_html_e( 'Account Settings', 'peanut-booker' ); ?></h2>
                    <span class="pb-card-hint pb-admin-badge"><?php esc_html_e( 'Admin Only', 'peanut-booker' ); ?></span>
                </div>
                <div class="pb-card-body">
                    <div class="pb-form-field">
                        <label for="pb_user_id"><?php esc_html_e( 'Linked User Account', 'peanut-booker' ); ?></label>
                        <?php
                        wp_dropdown_users( array(
                            'name'             => 'pb_user_id',
                            'id'               => 'pb_user_id',
                            'selected'         => $user_id,
                            'show_option_none' => __( '— Select User —', 'peanut-booker' ),
                            'role__in'         => array( 'pb_performer', 'administrator' ),
                            'class'            => 'pb-select-wide',
                        ) );
                        ?>
                        <?php if ( $linked_user ) : ?>
                            <span class="pb-field-hint"><?php printf( esc_html__( 'Currently linked to: %s (%s)', 'peanut-booker' ), $linked_user->display_name, $linked_user->user_email ); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Media Tab -->
        <div class="pb-editor-panel <?php echo 'media' === $active_tab ? 'active' : ''; ?>" data-tab="media">
            <div class="pb-editor-card">
                <div class="pb-card-header">
                    <h2><?php esc_html_e( 'Photo Gallery', 'peanut-booker' ); ?></h2>
                    <span class="pb-card-hint">
                        <?php
                        $photo_limit = 'pro' === $tier ? 5 : 1;
                        printf( esc_html__( '%d of %d photos used', 'peanut-booker' ), count( $gallery_ids ), $photo_limit );
                        ?>
                    </span>
                </div>
                <div class="pb-card-body">
                    <div class="pb-gallery-grid" id="pb-gallery-grid">
                        <?php foreach ( $gallery_ids as $img_id ) : ?>
                            <div class="pb-gallery-item" data-id="<?php echo esc_attr( $img_id ); ?>">
                                <?php echo wp_get_attachment_image( $img_id, 'medium' ); ?>
                                <button type="button" class="pb-remove-gallery-item" title="<?php esc_attr_e( 'Remove', 'peanut-booker' ); ?>">
                                    <span class="dashicons dashicons-no-alt"></span>
                                </button>
                            </div>
                        <?php endforeach; ?>
                        <?php if ( count( $gallery_ids ) < $photo_limit ) : ?>
                            <button type="button" class="pb-gallery-add" id="pb-add-gallery-photos">
                                <span class="dashicons dashicons-plus-alt2"></span>
                                <span><?php esc_html_e( 'Add Photos', 'peanut-booker' ); ?></span>
                            </button>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="pb_gallery_images" id="pb_gallery_images" value="<?php echo esc_attr( implode( ',', $gallery_ids ) ); ?>">

                    <?php if ( 'free' === $tier ) : ?>
                        <div class="pb-upgrade-hint">
                            <span class="dashicons dashicons-star-filled"></span>
                            <?php esc_html_e( 'Upgrade to Pro to add up to 5 photos', 'peanut-booker' ); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pb-editor-card">
                <div class="pb-card-header">
                    <h2><?php esc_html_e( 'Video Links', 'peanut-booker' ); ?></h2>
                    <span class="pb-card-hint">
                        <?php
                        $video_limit = 'pro' === $tier ? 3 : 0;
                        if ( $video_limit > 0 ) {
                            printf( esc_html__( '%d of %d videos used', 'peanut-booker' ), count( $videos ), $video_limit );
                        } else {
                            esc_html_e( 'Pro feature', 'peanut-booker' );
                        }
                        ?>
                    </span>
                </div>
                <div class="pb-card-body">
                    <?php if ( $video_limit > 0 ) : ?>
                        <div class="pb-video-list" id="pb-video-list">
                            <?php foreach ( $videos as $video_url ) : ?>
                                <div class="pb-video-item">
                                    <input type="text" name="pb_videos[]" value="<?php echo esc_attr( trim( $video_url ) ); ?>" placeholder="https://youtube.com/watch?v=..." class="pb-video-input">
                                    <button type="button" class="button pb-remove-video" title="<?php esc_attr_e( 'Remove', 'peanut-booker' ); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ( count( $videos ) < $video_limit ) : ?>
                            <button type="button" class="button" id="pb-add-video">
                                <span class="dashicons dashicons-plus-alt2"></span>
                                <?php esc_html_e( 'Add Video Link', 'peanut-booker' ); ?>
                            </button>
                        <?php endif; ?>
                    <?php else : ?>
                        <div class="pb-upgrade-hint">
                            <span class="dashicons dashicons-lock"></span>
                            <?php esc_html_e( 'Upgrade to Pro to add video links to your profile', 'peanut-booker' ); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Pricing Tab -->
        <div class="pb-editor-panel <?php echo 'pricing' === $active_tab ? 'active' : ''; ?>" data-tab="pricing">
            <div class="pb-editor-card">
                <div class="pb-card-header">
                    <h2><?php esc_html_e( 'Pricing', 'peanut-booker' ); ?></h2>
                </div>
                <div class="pb-card-body">
                    <div class="pb-form-grid">
                        <div class="pb-form-field">
                            <label for="pb_hourly_rate"><?php esc_html_e( 'Hourly Rate', 'peanut-booker' ); ?></label>
                            <div class="pb-input-with-prefix">
                                <span class="pb-input-prefix">$</span>
                                <input type="number" id="pb_hourly_rate" name="pb_hourly_rate" value="<?php echo esc_attr( $hourly_rate ); ?>" min="0" step="1" placeholder="150">
                            </div>
                        </div>
                        <div class="pb-form-field">
                            <label for="pb_minimum_booking"><?php esc_html_e( 'Minimum Hours', 'peanut-booker' ); ?></label>
                            <input type="number" id="pb_minimum_booking" name="pb_minimum_booking" value="<?php echo esc_attr( $minimum_booking ?: 1 ); ?>" min="1" max="12">
                            <span class="pb-field-hint"><?php esc_html_e( 'Minimum booking duration', 'peanut-booker' ); ?></span>
                        </div>
                    </div>

                    <div class="pb-form-field">
                        <label for="pb_deposit_percentage"><?php esc_html_e( 'Deposit Percentage', 'peanut-booker' ); ?></label>
                        <div class="pb-input-with-suffix">
                            <input type="number" id="pb_deposit_percentage" name="pb_deposit_percentage" value="<?php echo esc_attr( $deposit_percentage ?: 25 ); ?>" min="0" max="100" style="max-width: 100px;">
                            <span class="pb-input-suffix">%</span>
                        </div>
                        <span class="pb-field-hint"><?php esc_html_e( 'Amount due at booking (remainder due after event)', 'peanut-booker' ); ?></span>
                    </div>
                </div>
            </div>

            <div class="pb-editor-card">
                <div class="pb-card-header">
                    <h2><?php esc_html_e( 'Sale Pricing', 'peanut-booker' ); ?></h2>
                    <span class="pb-card-hint"><?php esc_html_e( 'Optional promotional pricing', 'peanut-booker' ); ?></span>
                </div>
                <div class="pb-card-body">
                    <div class="pb-form-field pb-checkbox-field">
                        <label class="pb-toggle">
                            <input type="checkbox" name="pb_sale_active" value="1" <?php checked( $sale_active, '1' ); ?>>
                            <span class="pb-toggle-slider"></span>
                            <span class="pb-toggle-label"><?php esc_html_e( 'Enable Sale Price', 'peanut-booker' ); ?></span>
                        </label>
                    </div>

                    <div class="pb-form-field pb-sale-price-field <?php echo $sale_active ? '' : 'pb-hidden'; ?>">
                        <label for="pb_sale_price"><?php esc_html_e( 'Sale Hourly Rate', 'peanut-booker' ); ?></label>
                        <div class="pb-input-with-prefix">
                            <span class="pb-input-prefix">$</span>
                            <input type="number" id="pb_sale_price" name="pb_sale_price" value="<?php echo esc_attr( $sale_price ); ?>" min="0" step="1">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Tab -->
        <div class="pb-editor-panel <?php echo 'location' === $active_tab ? 'active' : ''; ?>" data-tab="location">
            <div class="pb-editor-card">
                <div class="pb-card-header">
                    <h2><?php esc_html_e( 'Base Location', 'peanut-booker' ); ?></h2>
                </div>
                <div class="pb-card-body">
                    <div class="pb-form-grid">
                        <div class="pb-form-field">
                            <label for="pb_location_city"><?php esc_html_e( 'City', 'peanut-booker' ); ?></label>
                            <input type="text" id="pb_location_city" name="pb_location_city" value="<?php echo esc_attr( $location_city ); ?>" placeholder="Los Angeles">
                        </div>
                        <div class="pb-form-field">
                            <label for="pb_location_state"><?php esc_html_e( 'State', 'peanut-booker' ); ?></label>
                            <input type="text" id="pb_location_state" name="pb_location_state" value="<?php echo esc_attr( $location_state ); ?>" placeholder="California">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pb-editor-card">
                <div class="pb-card-header">
                    <h2><?php esc_html_e( 'Travel', 'peanut-booker' ); ?></h2>
                </div>
                <div class="pb-card-body">
                    <div class="pb-form-field pb-checkbox-field">
                        <label class="pb-toggle">
                            <input type="checkbox" name="pb_travel_willing" value="1" <?php checked( $travel_willing, '1' ); ?>>
                            <span class="pb-toggle-slider"></span>
                            <span class="pb-toggle-label"><?php esc_html_e( 'Willing to travel for gigs', 'peanut-booker' ); ?></span>
                        </label>
                    </div>

                    <div class="pb-form-field pb-travel-radius-field <?php echo $travel_willing ? '' : 'pb-hidden'; ?>">
                        <label for="pb_travel_radius"><?php esc_html_e( 'Travel Radius', 'peanut-booker' ); ?></label>
                        <div class="pb-input-with-suffix">
                            <input type="number" id="pb_travel_radius" name="pb_travel_radius" value="<?php echo esc_attr( $travel_radius ?: 50 ); ?>" min="0" max="500" style="max-width: 100px;">
                            <span class="pb-input-suffix"><?php esc_html_e( 'miles', 'peanut-booker' ); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Tab -->
        <div class="pb-editor-panel <?php echo 'categories' === $active_tab ? 'active' : ''; ?>" data-tab="categories">
            <div class="pb-editor-card">
                <div class="pb-card-header">
                    <h2><?php esc_html_e( 'Performance Categories', 'peanut-booker' ); ?></h2>
                    <span class="pb-card-hint"><?php esc_html_e( 'Select all that apply', 'peanut-booker' ); ?></span>
                </div>
                <div class="pb-card-body">
                    <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                        <div class="pb-checkbox-grid">
                            <?php foreach ( $categories as $cat ) : ?>
                                <label class="pb-checkbox-card">
                                    <input type="checkbox" name="pb_categories[]" value="<?php echo esc_attr( $cat->term_id ); ?>" <?php checked( in_array( $cat->term_id, $selected_cats, true ) ); ?>>
                                    <span class="pb-checkbox-card-inner">
                                        <span class="pb-checkbox-indicator"></span>
                                        <span class="pb-checkbox-label"><?php echo esc_html( $cat->name ); ?></span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p class="description"><?php esc_html_e( 'No categories available.', 'peanut-booker' ); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pb-editor-card">
                <div class="pb-card-header">
                    <h2><?php esc_html_e( 'Service Areas', 'peanut-booker' ); ?></h2>
                    <span class="pb-card-hint"><?php esc_html_e( 'Regions where you perform', 'peanut-booker' ); ?></span>
                </div>
                <div class="pb-card-body">
                    <?php if ( ! empty( $areas ) && ! is_wp_error( $areas ) ) : ?>
                        <div class="pb-checkbox-grid">
                            <?php foreach ( $areas as $area ) : ?>
                                <label class="pb-checkbox-card">
                                    <input type="checkbox" name="pb_service_areas[]" value="<?php echo esc_attr( $area->term_id ); ?>" <?php checked( in_array( $area->term_id, $selected_areas, true ) ); ?>>
                                    <span class="pb-checkbox-card-inner">
                                        <span class="pb-checkbox-indicator"></span>
                                        <span class="pb-checkbox-label"><?php echo esc_html( $area->name ); ?></span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p class="description"><?php esc_html_e( 'No service areas available.', 'peanut-booker' ); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sticky Save Bar -->
        <div class="pb-editor-save-bar">
            <div class="pb-save-bar-inner">
                <span class="pb-save-status"></span>
                <button type="submit" class="button button-primary button-large" id="pb-save-performer">
                    <span class="pb-save-text"><?php esc_html_e( 'Save Changes', 'peanut-booker' ); ?></span>
                    <span class="pb-saving-text" style="display: none;">
                        <span class="spinner is-active" style="float: none; margin: 0;"></span>
                        <?php esc_html_e( 'Saving...', 'peanut-booker' ); ?>
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>

<script type="text/html" id="tmpl-pb-video-item">
    <div class="pb-video-item">
        <input type="text" name="pb_videos[]" value="" placeholder="https://youtube.com/watch?v=..." class="pb-video-input">
        <button type="button" class="button pb-remove-video" title="<?php esc_attr_e( 'Remove', 'peanut-booker' ); ?>">
            <span class="dashicons dashicons-trash"></span>
        </button>
    </div>
</script>
