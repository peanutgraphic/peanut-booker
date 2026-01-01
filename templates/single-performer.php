<?php
/**
 * Single performer profile template.
 *
 * @package Peanut_Booker
 */

get_header();

// Check if user is logged in for access control.
$is_guest = ! is_user_logged_in();

while ( have_posts() ) :
    the_post();

    $performer_data = Peanut_Booker_Performer::get_by_profile_id( get_the_ID() );
    $display_data   = Peanut_Booker_Performer::get_display_data( get_the_ID() );
    $reviews        = $is_guest ? array() : Peanut_Booker_Reviews::get_performer_reviews( $performer_data->id, 5 );
    ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class( 'pb-performer-profile' ); ?>>
        <div class="pb-profile-header">
            <div class="pb-profile-hero">
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="pb-profile-image">
                        <?php the_post_thumbnail( 'large' ); ?>
                    </div>
                <?php endif; ?>

                <div class="pb-profile-info">
                    <div class="pb-profile-badges">
                        <?php echo wp_kses_post( Peanut_Booker_Performer::get_achievement_badge( $display_data['achievement_level'] ) ); ?>
                        <?php if ( $display_data['is_verified'] ) : ?>
                            <span class="pb-verified-badge" title="<?php esc_attr_e( 'Verified Performer', 'peanut-booker' ); ?>">✓ <?php esc_html_e( 'Verified', 'peanut-booker' ); ?></span>
                        <?php endif; ?>
                        <?php if ( 'pro' === $display_data['tier'] ) : ?>
                            <span class="pb-pro-badge"><?php esc_html_e( 'Pro', 'peanut-booker' ); ?></span>
                        <?php endif; ?>
                    </div>

                    <h1 class="pb-profile-name"><?php the_title(); ?></h1>

                    <?php if ( $display_data['tagline'] ) : ?>
                        <p class="pb-profile-tagline"><?php echo esc_html( $display_data['tagline'] ); ?></p>
                    <?php endif; ?>

                    <div class="pb-profile-rating">
                        <?php if ( $display_data['average_rating'] ) : ?>
                            <?php echo wp_kses_post( Peanut_Booker_Reviews::render_stars( $display_data['average_rating'], $display_data['total_reviews'] ) ); ?>
                        <?php else : ?>
                            <span class="pb-no-rating"><?php esc_html_e( 'No reviews yet', 'peanut-booker' ); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="pb-profile-stats">
                        <span class="pb-stat">
                            <strong><?php echo esc_html( $display_data['completed_bookings'] ); ?></strong>
                            <?php esc_html_e( 'bookings completed', 'peanut-booker' ); ?>
                        </span>
                        <?php if ( $display_data['experience_years'] ) : ?>
                            <span class="pb-stat">
                                <strong><?php echo esc_html( $display_data['experience_years'] ); ?></strong>
                                <?php esc_html_e( 'years experience', 'peanut-booker' ); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ( ! empty( $display_data['categories'] ) ) : ?>
                        <div class="pb-profile-categories">
                            <?php foreach ( $display_data['categories'] as $cat ) : ?>
                                <span class="pb-category-tag"><?php echo esc_html( $cat ); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ( $is_guest ) : ?>
            <!-- Guest View: Limited Preview -->
            <div class="pb-guest-overlay">
                <div class="pb-guest-signup-prompt">
                    <div class="pb-prompt-icon">🔒</div>
                    <h2><?php esc_html_e( 'Sign Up to View Full Profile', 'peanut-booker' ); ?></h2>
                    <p><?php esc_html_e( 'Create a free account to view photos, videos, reviews, availability, and book this performer.', 'peanut-booker' ); ?></p>

                    <div class="pb-prompt-actions">
                        <a href="<?php echo esc_url( home_url( '/login/?action=signup&redirect_to=' . urlencode( get_permalink() ) ) ); ?>" class="pb-button pb-button-primary pb-button-lg">
                            <?php esc_html_e( 'Sign Up Free', 'peanut-booker' ); ?>
                        </a>
                        <a href="<?php echo esc_url( home_url( '/login/?redirect_to=' . urlencode( get_permalink() ) ) ); ?>" class="pb-button pb-button-secondary">
                            <?php esc_html_e( 'Log In', 'peanut-booker' ); ?>
                        </a>
                    </div>

                    <div class="pb-guest-benefits">
                        <h4><?php esc_html_e( 'With an account you can:', 'peanut-booker' ); ?></h4>
                        <ul>
                            <li><?php esc_html_e( 'View full performer profiles', 'peanut-booker' ); ?></li>
                            <li><?php esc_html_e( 'See photos, videos, and reviews', 'peanut-booker' ); ?></li>
                            <li><?php esc_html_e( 'Check real-time availability', 'peanut-booker' ); ?></li>
                            <li><?php esc_html_e( 'Book performers for your events', 'peanut-booker' ); ?></li>
                            <li><?php esc_html_e( 'Post events and receive bids', 'peanut-booker' ); ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="pb-profile-body pb-guest-preview">
                <div class="pb-profile-main">
                    <!-- Teaser About Section -->
                    <section class="pb-profile-section pb-about pb-blurred-section">
                        <h2><?php esc_html_e( 'About', 'peanut-booker' ); ?></h2>
                        <div class="pb-content pb-content-teaser">
                            <?php
                            $content = get_the_content();
                            $teaser  = wp_trim_words( $content, 30, '...' );
                            echo '<p>' . esc_html( $teaser ) . '</p>';
                            ?>
                            <div class="pb-content-blur"></div>
                        </div>
                    </section>

                    <!-- Blurred Gallery Preview -->
                    <?php if ( ! empty( $display_data['gallery'] ) ) : ?>
                        <section class="pb-profile-section pb-gallery pb-blurred-section">
                            <h2><?php esc_html_e( 'Photos', 'peanut-booker' ); ?></h2>
                            <div class="pb-gallery-grid pb-locked">
                                <?php
                                $preview_count = min( 3, count( $display_data['gallery'] ) );
                                for ( $i = 0; $i < $preview_count; $i++ ) :
                                ?>
                                    <div class="pb-gallery-item pb-gallery-locked">
                                        <?php echo wp_get_attachment_image( $display_data['gallery'][ $i ], 'medium' ); ?>
                                        <div class="pb-lock-overlay">🔒</div>
                                    </div>
                                <?php endfor; ?>
                                <?php if ( count( $display_data['gallery'] ) > 3 ) : ?>
                                    <div class="pb-gallery-item pb-gallery-more">
                                        <span>+<?php echo count( $display_data['gallery'] ) - 3; ?> <?php esc_html_e( 'more', 'peanut-booker' ); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <!-- Locked Reviews Section -->
                    <section class="pb-profile-section pb-reviews-section pb-blurred-section">
                        <h2><?php esc_html_e( 'Reviews', 'peanut-booker' ); ?></h2>
                        <div class="pb-reviews-locked">
                            <p>
                                <?php
                                if ( $display_data['total_reviews'] > 0 ) {
                                    printf(
                                        esc_html( _n( '%d review available', '%d reviews available', $display_data['total_reviews'], 'peanut-booker' ) ),
                                        $display_data['total_reviews']
                                    );
                                } else {
                                    esc_html_e( 'Reviews available after signup', 'peanut-booker' );
                                }
                                ?>
                            </p>
                            <a href="<?php echo esc_url( home_url( '/login/?action=signup' ) ); ?>" class="pb-button pb-button-secondary">
                                <?php esc_html_e( 'Sign Up to Read Reviews', 'peanut-booker' ); ?>
                            </a>
                        </div>
                    </section>
                </div>

                <aside class="pb-profile-sidebar">
                    <!-- Pricing Visible -->
                    <div class="pb-booking-widget">
                        <div class="pb-pricing">
                            <?php if ( $display_data['sale_active'] && $display_data['sale_price'] ) : ?>
                                <span class="pb-sale-badge"><?php esc_html_e( 'Sale!', 'peanut-booker' ); ?></span>
                                <span class="pb-price pb-price-sale"><?php echo wc_price( $display_data['sale_price'] ); ?></span>
                                <span class="pb-price pb-price-original pb-strikethrough"><?php echo wc_price( $display_data['hourly_rate'] ); ?></span>
                            <?php else : ?>
                                <span class="pb-price"><?php echo wc_price( $display_data['hourly_rate'] ); ?></span>
                            <?php endif; ?>
                            <span class="pb-price-suffix"><?php esc_html_e( 'per hour', 'peanut-booker' ); ?></span>
                        </div>

                        <a href="<?php echo esc_url( home_url( '/login/?action=signup&redirect_to=' . urlencode( get_permalink() ) ) ); ?>" class="pb-button pb-button-primary pb-button-block">
                            <?php esc_html_e( 'Sign Up to Book', 'peanut-booker' ); ?>
                        </a>
                        <p class="pb-widget-hint"><?php esc_html_e( 'Free to create an account', 'peanut-booker' ); ?></p>
                    </div>

                    <!-- Locked Availability -->
                    <div class="pb-availability-widget pb-locked-widget">
                        <h3><?php esc_html_e( 'Availability', 'peanut-booker' ); ?></h3>
                        <div class="pb-locked-content">
                            <span class="pb-lock-icon">🔒</span>
                            <p><?php esc_html_e( 'Sign up to view availability', 'peanut-booker' ); ?></p>
                        </div>
                    </div>

                    <!-- Location (General Only) -->
                    <?php if ( $display_data['location_city'] ) : ?>
                        <div class="pb-location-widget">
                            <h3><?php esc_html_e( 'Location', 'peanut-booker' ); ?></h3>
                            <p>
                                <?php echo esc_html( $display_data['location_city'] ); ?>
                                <?php echo $display_data['location_state'] ? ', ' . esc_html( $display_data['location_state'] ) : ''; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </aside>
            </div>

        <?php else : ?>
            <!-- Logged-In View: Full Profile -->
            <div class="pb-profile-body">
                <div class="pb-profile-main">
                    <section class="pb-profile-section pb-about">
                        <h2><?php esc_html_e( 'About', 'peanut-booker' ); ?></h2>
                        <div class="pb-content">
                            <?php the_content(); ?>
                        </div>
                    </section>

                    <?php if ( ! empty( $display_data['gallery'] ) ) : ?>
                        <section class="pb-profile-section pb-gallery">
                            <h2><?php esc_html_e( 'Photos', 'peanut-booker' ); ?></h2>
                            <div class="pb-gallery-grid">
                                <?php foreach ( $display_data['gallery'] as $image_id ) : ?>
                                    <div class="pb-gallery-item">
                                        <?php echo wp_get_attachment_image( $image_id, 'medium' ); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <?php if ( ! empty( $display_data['videos'] ) ) : ?>
                        <section class="pb-profile-section pb-videos">
                            <h2><?php esc_html_e( 'Videos', 'peanut-booker' ); ?></h2>
                            <div class="pb-video-list">
                                <?php foreach ( $display_data['videos'] as $video_url ) : ?>
                                    <div class="pb-video-item">
                                        <?php echo wp_oembed_get( $video_url ); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <section class="pb-profile-section pb-reviews-section">
                        <h2><?php esc_html_e( 'Reviews', 'peanut-booker' ); ?></h2>

                        <?php if ( empty( $reviews ) ) : ?>
                            <p><?php esc_html_e( 'No reviews yet.', 'peanut-booker' ); ?></p>
                        <?php else : ?>
                            <div class="pb-reviews-list">
                                <?php foreach ( $reviews as $review ) : ?>
                                    <div class="pb-review">
                                        <div class="pb-review-header">
                                            <img src="<?php echo esc_url( $review['reviewer_avatar'] ); ?>" alt="" class="pb-review-avatar">
                                            <div class="pb-review-meta">
                                                <strong><?php echo esc_html( $review['reviewer_name'] ); ?></strong>
                                                <span class="pb-review-date"><?php echo esc_html( $review['date_formatted'] ); ?></span>
                                            </div>
                                            <div class="pb-review-rating">
                                                <?php echo wp_kses_post( Peanut_Booker_Reviews::render_stars( $review['rating'], 0, false ) ); ?>
                                            </div>
                                        </div>

                                        <?php if ( $review['title'] ) : ?>
                                            <h4 class="pb-review-title"><?php echo esc_html( $review['title'] ); ?></h4>
                                        <?php endif; ?>

                                        <div class="pb-review-content">
                                            <?php echo esc_html( $review['content'] ); ?>
                                        </div>

                                        <?php if ( $review['response'] ) : ?>
                                            <div class="pb-review-response">
                                                <strong><?php esc_html_e( 'Response from performer:', 'peanut-booker' ); ?></strong>
                                                <p><?php echo esc_html( $review['response'] ); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>

                <aside class="pb-profile-sidebar">
                    <div class="pb-booking-widget">
                        <div class="pb-pricing">
                            <?php if ( $display_data['sale_active'] && $display_data['sale_price'] ) : ?>
                                <span class="pb-sale-badge"><?php esc_html_e( 'Sale!', 'peanut-booker' ); ?></span>
                                <span class="pb-price pb-price-sale"><?php echo wc_price( $display_data['sale_price'] ); ?></span>
                                <span class="pb-price pb-price-original pb-strikethrough"><?php echo wc_price( $display_data['hourly_rate'] ); ?></span>
                            <?php else : ?>
                                <span class="pb-price"><?php echo wc_price( $display_data['hourly_rate'] ); ?></span>
                            <?php endif; ?>
                            <span class="pb-price-suffix"><?php esc_html_e( 'per hour', 'peanut-booker' ); ?></span>
                        </div>

                        <p class="pb-deposit-info">
                            <?php
                            printf(
                                /* translators: %d: deposit percentage */
                                esc_html__( 'Deposit: %d%% at booking', 'peanut-booker' ),
                                $display_data['deposit_percentage']
                            );
                            ?>
                        </p>

                        <?php if ( Peanut_Booker_Roles::is_customer() ) : ?>
                            <a href="#pb-booking-form" class="pb-button pb-button-primary pb-button-block">
                                <?php esc_html_e( 'Book Now', 'peanut-booker' ); ?>
                            </a>
                        <?php elseif ( Peanut_Booker_Roles::is_performer() ) : ?>
                            <p class="pb-booking-note"><?php esc_html_e( 'Switch to a customer account to book performers.', 'peanut-booker' ); ?></p>
                        <?php else : ?>
                            <a href="<?php echo esc_url( home_url( '/customer-signup/' ) ); ?>" class="pb-button pb-button-primary pb-button-block">
                                <?php esc_html_e( 'Sign Up to Book', 'peanut-booker' ); ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="pb-availability-widget">
                        <h3><?php esc_html_e( 'Availability', 'peanut-booker' ); ?></h3>
                        <?php echo wp_kses_post( Peanut_Booker_Availability::render_calendar( $performer_data->id ) ); ?>
                    </div>

                    <?php if ( $display_data['location_city'] ) : ?>
                        <div class="pb-location-widget">
                            <h3><?php esc_html_e( 'Location', 'peanut-booker' ); ?></h3>
                            <p>
                                <?php echo esc_html( $display_data['location_city'] ); ?>
                                <?php echo $display_data['location_state'] ? ', ' . esc_html( $display_data['location_state'] ) : ''; ?>
                            </p>
                            <?php if ( $display_data['travel_willing'] ) : ?>
                                <p class="pb-travel-info">
                                    <?php
                                    printf(
                                        /* translators: %d: travel radius in miles */
                                        esc_html__( 'Willing to travel up to %d miles', 'peanut-booker' ),
                                        $display_data['travel_radius']
                                    );
                                    ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $display_data['service_areas'] ) ) : ?>
                        <div class="pb-service-areas-widget">
                            <h3><?php esc_html_e( 'Service Areas', 'peanut-booker' ); ?></h3>
                            <ul>
                                <?php foreach ( $display_data['service_areas'] as $area ) : ?>
                                    <li><?php echo esc_html( $area ); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $display_data['social_links'] ) || $display_data['website'] ) : ?>
                        <div class="pb-social-widget">
                            <h3><?php esc_html_e( 'Connect', 'peanut-booker' ); ?></h3>
                            <div class="pb-social-links">
                                <?php if ( $display_data['website'] ) : ?>
                                    <a href="<?php echo esc_url( $display_data['website'] ); ?>" target="_blank" rel="noopener">
                                        <?php esc_html_e( 'Website', 'peanut-booker' ); ?>
                                    </a>
                                <?php endif; ?>
                                <?php foreach ( $display_data['social_links'] as $platform => $url ) : ?>
                                    <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">
                                        <?php echo esc_html( ucfirst( $platform ) ); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </aside>
            </div>

            <?php if ( Peanut_Booker_Roles::is_customer() ) : ?>
                <div id="pb-booking-form" class="pb-booking-form-section">
                    <?php echo do_shortcode( '[pb_booking_form performer_id="' . $performer_data->id . '"]' ); ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </article>

    <?php
    // Analytics tracking script
    $microsite = Peanut_Booker_Database::get_row( 'microsites', array( 'performer_id' => $performer_data->id ) );
    if ( $microsite && $microsite->slug ) :
    ?>
    <script>
    (function() {
        var slug = <?php echo wp_json_encode( $microsite->slug ); ?>;
        var apiUrl = <?php echo wp_json_encode( rest_url( 'peanut-booker/v1/microsites/' ) ); ?>;
        var referrer = document.referrer || '';

        // Track page view on load
        function trackEvent(eventType) {
            fetch(apiUrl + encodeURIComponent(slug) + '/track', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ event: eventType, referrer: referrer }),
                credentials: 'omit'
            }).catch(function() {});
        }

        // Track page view
        trackEvent('page_view');

        // Track booking button clicks
        document.addEventListener('click', function(e) {
            var target = e.target.closest('.pb-button-primary');
            if (target && (target.textContent.indexOf('Book') !== -1 || target.href && target.href.indexOf('#pb-booking') !== -1)) {
                trackEvent('booking_click');
            }
        });
    })();
    </script>
    <?php endif; ?>

    <?php
endwhile;

get_footer();
