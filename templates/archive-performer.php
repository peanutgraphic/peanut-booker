<?php
/**
 * Performer directory archive template.
 *
 * @package Peanut_Booker
 */

get_header();

// Get filter values.
$category  = isset( $_GET['category'] ) ? sanitize_text_field( $_GET['category'] ) : '';
$location  = isset( $_GET['location'] ) ? sanitize_text_field( $_GET['location'] ) : '';
$max_price = isset( $_GET['max_price'] ) ? absint( $_GET['max_price'] ) : '';
$sort      = isset( $_GET['sort'] ) ? sanitize_text_field( $_GET['sort'] ) : 'rating';
?>

<div class="pb-performer-directory">
    <header class="pb-directory-header">
        <h1><?php esc_html_e( 'Find a Performer', 'peanut-booker' ); ?></h1>
        <p class="pb-directory-description">
            <?php esc_html_e( 'Browse our talented performers and book the perfect entertainment for your event.', 'peanut-booker' ); ?>
        </p>
    </header>

    <div class="pb-directory-filters">
        <form class="pb-filter-form" method="get">
            <div class="pb-filter-row">
                <div class="pb-filter-field">
                    <label for="pb-category"><?php esc_html_e( 'Category', 'peanut-booker' ); ?></label>
                    <select id="pb-category" name="category" class="pb-filter-category">
                        <option value=""><?php esc_html_e( 'All Categories', 'peanut-booker' ); ?></option>
                        <?php
                        $categories = get_terms( array( 'taxonomy' => 'pb_performer_category', 'hide_empty' => true ) );
                        foreach ( $categories as $cat ) :
                        ?>
                            <option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $category, $cat->slug ); ?>>
                                <?php echo esc_html( $cat->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="pb-filter-field">
                    <label for="pb-location"><?php esc_html_e( 'Location', 'peanut-booker' ); ?></label>
                    <select id="pb-location" name="location" class="pb-filter-location">
                        <option value=""><?php esc_html_e( 'All Locations', 'peanut-booker' ); ?></option>
                        <?php
                        $locations = get_terms( array( 'taxonomy' => 'pb_service_area', 'hide_empty' => true ) );
                        foreach ( $locations as $loc ) :
                        ?>
                            <option value="<?php echo esc_attr( $loc->slug ); ?>" <?php selected( $location, $loc->slug ); ?>>
                                <?php echo esc_html( $loc->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="pb-filter-field">
                    <label for="pb-max-price"><?php esc_html_e( 'Max Price/Hour', 'peanut-booker' ); ?></label>
                    <select id="pb-max-price" name="max_price" class="pb-filter-price">
                        <option value=""><?php esc_html_e( 'Any Price', 'peanut-booker' ); ?></option>
                        <option value="50" <?php selected( $max_price, 50 ); ?>><?php esc_html_e( 'Up to $50', 'peanut-booker' ); ?></option>
                        <option value="100" <?php selected( $max_price, 100 ); ?>><?php esc_html_e( 'Up to $100', 'peanut-booker' ); ?></option>
                        <option value="200" <?php selected( $max_price, 200 ); ?>><?php esc_html_e( 'Up to $200', 'peanut-booker' ); ?></option>
                        <option value="500" <?php selected( $max_price, 500 ); ?>><?php esc_html_e( 'Up to $500', 'peanut-booker' ); ?></option>
                    </select>
                </div>

                <div class="pb-filter-field">
                    <label for="pb-sort"><?php esc_html_e( 'Sort By', 'peanut-booker' ); ?></label>
                    <select id="pb-sort" name="sort">
                        <option value="rating" <?php selected( $sort, 'rating' ); ?>><?php esc_html_e( 'Top Rated', 'peanut-booker' ); ?></option>
                        <option value="bookings" <?php selected( $sort, 'bookings' ); ?>><?php esc_html_e( 'Most Booked', 'peanut-booker' ); ?></option>
                        <option value="price_low" <?php selected( $sort, 'price_low' ); ?>><?php esc_html_e( 'Price: Low to High', 'peanut-booker' ); ?></option>
                        <option value="price_high" <?php selected( $sort, 'price_high' ); ?>><?php esc_html_e( 'Price: High to Low', 'peanut-booker' ); ?></option>
                        <option value="newest" <?php selected( $sort, 'newest' ); ?>><?php esc_html_e( 'Newest', 'peanut-booker' ); ?></option>
                    </select>
                </div>

                <div class="pb-filter-field pb-filter-search">
                    <label for="pb-search"><?php esc_html_e( 'Search', 'peanut-booker' ); ?></label>
                    <input type="text" id="pb-search" name="s" class="pb-search-performers"
                           placeholder="<?php esc_attr_e( 'Search performers...', 'peanut-booker' ); ?>"
                           value="<?php echo esc_attr( get_search_query() ); ?>">
                </div>

                <div class="pb-filter-field pb-filter-submit">
                    <button type="submit" class="pb-button pb-button-primary">
                        <?php esc_html_e( 'Filter', 'peanut-booker' ); ?>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="pb-performers-grid">
        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <?php
                $performer_data = Peanut_Booker_Performer::get_by_profile_id( get_the_ID() );
                $display_data   = Peanut_Booker_Performer::get_display_data( get_the_ID() );

                if ( ! $performer_data || 'active' !== $performer_data->status ) {
                    continue;
                }
                ?>
                <article class="pb-performer-card">
                    <a href="<?php the_permalink(); ?>" class="pb-performer-link">
                        <div class="pb-performer-image">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'medium' ); ?>
                            <?php else : ?>
                                <div class="pb-no-image"></div>
                            <?php endif; ?>

                            <?php if ( $display_data['sale_active'] ) : ?>
                                <span class="pb-sale-badge"><?php esc_html_e( 'Sale', 'peanut-booker' ); ?></span>
                            <?php endif; ?>

                            <?php if ( 'pro' === $display_data['tier'] ) : ?>
                                <span class="pb-pro-badge"><?php esc_html_e( 'Pro', 'peanut-booker' ); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="pb-performer-content">
                            <div class="pb-performer-header">
                                <h2 class="pb-performer-name">
                                    <?php the_title(); ?>
                                    <?php if ( $display_data['is_verified'] ) : ?>
                                        <span class="pb-verified-icon" title="<?php esc_attr_e( 'Verified', 'peanut-booker' ); ?>">✓</span>
                                    <?php endif; ?>
                                </h2>
                                <?php echo wp_kses_post( Peanut_Booker_Performer::get_achievement_badge( $display_data['achievement_level'] ) ); ?>
                            </div>

                            <?php if ( ! empty( $display_data['categories'] ) ) : ?>
                                <p class="pb-performer-categories">
                                    <?php echo esc_html( implode( ', ', array_slice( $display_data['categories'], 0, 2 ) ) ); ?>
                                </p>
                            <?php endif; ?>

                            <div class="pb-performer-rating">
                                <?php if ( $display_data['average_rating'] ) : ?>
                                    <span class="pb-stars"><?php echo esc_html( number_format( $display_data['average_rating'], 1 ) ); ?> ★</span>
                                    <span class="pb-review-count">(<?php echo esc_html( $display_data['total_reviews'] ); ?>)</span>
                                <?php else : ?>
                                    <span class="pb-no-reviews"><?php esc_html_e( 'New', 'peanut-booker' ); ?></span>
                                <?php endif; ?>
                                <span class="pb-bookings-count">
                                    <?php
                                    printf(
                                        esc_html( _n( '%d booking', '%d bookings', $display_data['completed_bookings'], 'peanut-booker' ) ),
                                        $display_data['completed_bookings']
                                    );
                                    ?>
                                </span>
                            </div>

                            <?php if ( $display_data['location_city'] ) : ?>
                                <p class="pb-performer-location">
                                    📍 <?php echo esc_html( $display_data['location_city'] ); ?>
                                    <?php echo $display_data['location_state'] ? ', ' . esc_html( $display_data['location_state'] ) : ''; ?>
                                </p>
                            <?php endif; ?>

                            <div class="pb-performer-price">
                                <?php if ( $display_data['sale_active'] && $display_data['sale_price'] ) : ?>
                                    <span class="pb-price-sale"><?php echo wc_price( $display_data['sale_price'] ); ?></span>
                                    <span class="pb-price-original"><?php echo wc_price( $display_data['hourly_rate'] ); ?></span>
                                <?php else : ?>
                                    <span class="pb-price"><?php echo wc_price( $display_data['hourly_rate'] ); ?></span>
                                <?php endif; ?>
                                <span class="pb-price-suffix"><?php esc_html_e( '/hour', 'peanut-booker' ); ?></span>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endwhile; ?>
        <?php else : ?>
            <div class="pb-no-results">
                <h2><?php esc_html_e( 'No performers found', 'peanut-booker' ); ?></h2>
                <p><?php esc_html_e( 'Try adjusting your filters or search criteria.', 'peanut-booker' ); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <?php if ( have_posts() ) : ?>
        <nav class="pb-pagination">
            <?php
            the_posts_pagination( array(
                'mid_size'  => 2,
                'prev_text' => __( '&larr; Previous', 'peanut-booker' ),
                'next_text' => __( 'Next &rarr;', 'peanut-booker' ),
            ) );
            ?>
        </nav>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
