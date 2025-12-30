<?php
/**
 * Market events archive template.
 *
 * @package Peanut_Booker
 */

get_header();

// Get filter values.
$category = isset( $_GET['category'] ) ? sanitize_text_field( $_GET['category'] ) : '';
$location = isset( $_GET['location'] ) ? sanitize_text_field( $_GET['location'] ) : '';

// Check if user can bid (Pro performers only).
$can_bid = is_user_logged_in() && Peanut_Booker_Roles::is_pro_performer();

// Build query args.
$args = array(
    'post_type'      => 'pb_market_event',
    'post_status'    => 'publish',
    'posts_per_page' => 20,
    'meta_query'     => array(
        array(
            'key'     => 'pb_event_status',
            'value'   => 'open',
            'compare' => '=',
        ),
    ),
    'orderby'        => 'meta_value',
    'meta_key'       => 'pb_event_date',
    'order'          => 'ASC',
);

// Add category filter.
if ( $category ) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'pb_performer_category',
            'field'    => 'slug',
            'terms'    => $category,
        ),
    );
}

// Add location filter.
if ( $location ) {
    $args['meta_query'][] = array(
        'relation' => 'OR',
        array(
            'key'     => 'pb_venue_city',
            'value'   => $location,
            'compare' => 'LIKE',
        ),
        array(
            'key'     => 'pb_venue_state',
            'value'   => $location,
            'compare' => 'LIKE',
        ),
    );
}

$events_query = new WP_Query( $args );
?>

<div class="pb-market">
    <header class="pb-market-header">
        <h1><?php esc_html_e( 'Market', 'peanut-booker' ); ?></h1>
        <p class="pb-market-description">
            <?php esc_html_e( 'Browse events looking for performers. Pro performers can bid on opportunities.', 'peanut-booker' ); ?>
        </p>

        <?php if ( is_user_logged_in() && Peanut_Booker_Roles::is_customer() ) : ?>
            <a href="<?php echo esc_url( home_url( '/dashboard/?tab=events&action=new' ) ); ?>" class="pb-button pb-button-primary">
                <?php esc_html_e( 'Post Your Event', 'peanut-booker' ); ?>
            </a>
        <?php endif; ?>
    </header>

    <?php if ( ! $can_bid && is_user_logged_in() && Peanut_Booker_Roles::is_performer() ) : ?>
        <div class="pb-upgrade-notice">
            <p>
                <?php esc_html_e( 'Upgrade to Pro to bid on market events!', 'peanut-booker' ); ?>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
                    <?php esc_html_e( 'Learn More', 'peanut-booker' ); ?>
                </a>
            </p>
        </div>
    <?php endif; ?>

    <div class="pb-market-filters">
        <form class="pb-filter-form" method="get">
            <div class="pb-filter-row">
                <div class="pb-filter-field">
                    <label for="pb-category"><?php esc_html_e( 'Category Needed', 'peanut-booker' ); ?></label>
                    <select id="pb-category" name="category">
                        <option value=""><?php esc_html_e( 'All Categories', 'peanut-booker' ); ?></option>
                        <?php
                        $categories = get_terms( array( 'taxonomy' => 'pb_performer_category', 'hide_empty' => false ) );
                        if ( ! is_wp_error( $categories ) ) :
                            foreach ( $categories as $cat ) :
                        ?>
                            <option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $category, $cat->slug ); ?>>
                                <?php echo esc_html( $cat->name ); ?>
                            </option>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </select>
                </div>

                <div class="pb-filter-field">
                    <label for="pb-location"><?php esc_html_e( 'Location', 'peanut-booker' ); ?></label>
                    <input type="text" id="pb-location" name="location" value="<?php echo esc_attr( $location ); ?>"
                           placeholder="<?php esc_attr_e( 'City or region...', 'peanut-booker' ); ?>">
                </div>

                <div class="pb-filter-field pb-filter-submit">
                    <button type="submit" class="pb-button pb-button-primary">
                        <?php esc_html_e( 'Filter', 'peanut-booker' ); ?>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="pb-market-grid">
        <?php if ( $events_query->have_posts() ) : ?>
            <?php while ( $events_query->have_posts() ) : $events_query->the_post(); ?>
                <?php
                $event_id     = get_the_ID();
                $event_date   = get_post_meta( $event_id, 'pb_event_date', true );
                $event_time   = get_post_meta( $event_id, 'pb_event_time', true );
                $venue_name   = get_post_meta( $event_id, 'pb_venue_name', true );
                $venue_city   = get_post_meta( $event_id, 'pb_venue_city', true );
                $venue_state  = get_post_meta( $event_id, 'pb_venue_state', true );
                $budget_min   = get_post_meta( $event_id, 'pb_budget_min', true );
                $budget_max   = get_post_meta( $event_id, 'pb_budget_max', true );
                $duration     = get_post_meta( $event_id, 'pb_event_duration', true );
                $deadline     = get_post_meta( $event_id, 'pb_bid_deadline', true );
                $total_bids   = get_post_meta( $event_id, 'pb_total_bids', true ) ?: 0;
                $customer_id  = get_post_meta( $event_id, 'pb_customer_id', true );

                // Get category.
                $categories   = get_the_terms( $event_id, 'pb_performer_category' );
                $category_name = $categories && ! is_wp_error( $categories ) ? $categories[0]->name : '';

                // Format location.
                $location_parts = array_filter( array( $venue_city, $venue_state ) );
                $location_str   = implode( ', ', $location_parts );
                if ( $venue_name ) {
                    $location_str = $venue_name . ( $location_str ? ' (' . $location_str . ')' : '' );
                }

                // Calculate days left.
                $days_left = 0;
                if ( $deadline ) {
                    $days_left = ceil( ( strtotime( $deadline ) - time() ) / DAY_IN_SECONDS );
                }

                // Check if current user has already bid.
                $has_bid = false;
                if ( $can_bid && class_exists( 'Peanut_Booker_Market' ) ) {
                    $has_bid = Peanut_Booker_Market::has_user_bid( $event_id, get_current_user_id() );
                }
                ?>
                <article class="pb-market-card">
                    <a href="<?php the_permalink(); ?>" class="pb-market-card-link">
                        <div class="pb-market-card-header">
                            <h2 class="pb-event-title"><?php the_title(); ?></h2>
                            <?php if ( $event_date ) : ?>
                                <span class="pb-event-date">
                                    <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $event_date ) ) ); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="pb-market-card-body">
                            <?php if ( has_excerpt() || get_the_content() ) : ?>
                                <p class="pb-event-description">
                                    <?php echo esc_html( wp_trim_words( get_the_excerpt() ?: get_the_content(), 30 ) ); ?>
                                </p>
                            <?php endif; ?>

                            <div class="pb-event-details">
                                <?php if ( $location_str ) : ?>
                                    <span class="pb-detail">
                                        <strong><?php esc_html_e( 'Location:', 'peanut-booker' ); ?></strong>
                                        <?php echo esc_html( $location_str ); ?>
                                    </span>
                                <?php endif; ?>

                                <?php if ( $category_name ) : ?>
                                    <span class="pb-detail">
                                        <strong><?php esc_html_e( 'Looking for:', 'peanut-booker' ); ?></strong>
                                        <?php echo esc_html( $category_name ); ?>
                                    </span>
                                <?php endif; ?>

                                <?php if ( $duration ) : ?>
                                    <span class="pb-detail">
                                        <strong><?php esc_html_e( 'Duration:', 'peanut-booker' ); ?></strong>
                                        <?php echo esc_html( $duration ); ?> <?php esc_html_e( 'hours', 'peanut-booker' ); ?>
                                    </span>
                                <?php endif; ?>

                                <?php if ( $budget_min || $budget_max ) : ?>
                                    <span class="pb-detail pb-budget">
                                        <strong><?php esc_html_e( 'Budget:', 'peanut-booker' ); ?></strong>
                                        <?php if ( $budget_min && $budget_max ) : ?>
                                            <?php echo wc_price( $budget_min ); ?> - <?php echo wc_price( $budget_max ); ?>
                                        <?php elseif ( $budget_max ) : ?>
                                            <?php esc_html_e( 'Up to', 'peanut-booker' ); ?> <?php echo wc_price( $budget_max ); ?>
                                        <?php elseif ( $budget_min ) : ?>
                                            <?php esc_html_e( 'From', 'peanut-booker' ); ?> <?php echo wc_price( $budget_min ); ?>
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="pb-market-card-footer">
                            <div class="pb-event-stats">
                                <span class="pb-bid-count">
                                    <strong><?php echo esc_html( $total_bids ); ?></strong>
                                    <?php esc_html_e( 'bids', 'peanut-booker' ); ?>
                                </span>
                                <?php if ( $deadline ) : ?>
                                    <span class="pb-deadline <?php echo $days_left <= 2 ? 'pb-urgent' : ''; ?>">
                                        <?php if ( $days_left > 0 ) : ?>
                                            <?php
                                            printf(
                                                esc_html( _n( '%d day left', '%d days left', $days_left, 'peanut-booker' ) ),
                                                $days_left
                                            );
                                            ?>
                                        <?php else : ?>
                                            <?php esc_html_e( 'Closing soon', 'peanut-booker' ); ?>
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>

                    <div class="pb-event-actions">
                        <?php if ( $can_bid ) : ?>
                            <?php if ( $has_bid ) : ?>
                                <span class="pb-bid-submitted"><?php esc_html_e( 'Bid Submitted', 'peanut-booker' ); ?></span>
                            <?php else : ?>
                                <a href="<?php the_permalink(); ?>" class="pb-button pb-button-primary">
                                    <?php esc_html_e( 'View & Bid', 'peanut-booker' ); ?>
                                </a>
                            <?php endif; ?>
                        <?php elseif ( ! is_user_logged_in() ) : ?>
                            <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="pb-button pb-button-secondary">
                                <?php esc_html_e( 'Login to Bid', 'peanut-booker' ); ?>
                            </a>
                        <?php else : ?>
                            <a href="<?php the_permalink(); ?>" class="pb-button pb-button-secondary">
                                <?php esc_html_e( 'View Details', 'peanut-booker' ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <div class="pb-no-results">
                <div class="pb-empty-state">
                    <div class="pb-empty-state-icon">📋</div>
                    <h2><?php esc_html_e( 'No events currently available', 'peanut-booker' ); ?></h2>
                    <p><?php esc_html_e( 'Check back soon for new opportunities!', 'peanut-booker' ); ?></p>
                    <?php if ( is_user_logged_in() && Peanut_Booker_Roles::is_customer() ) : ?>
                        <a href="<?php echo esc_url( home_url( '/dashboard/?tab=events&action=new' ) ); ?>" class="pb-button pb-button-primary">
                            <?php esc_html_e( 'Post the First Event', 'peanut-booker' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if ( $events_query->max_num_pages > 1 ) : ?>
        <nav class="pb-pagination">
            <?php
            echo paginate_links( array(
                'total'     => $events_query->max_num_pages,
                'current'   => max( 1, get_query_var( 'paged' ) ),
                'prev_text' => __( '&larr; Previous', 'peanut-booker' ),
                'next_text' => __( 'Next &rarr;', 'peanut-booker' ),
            ) );
            ?>
        </nav>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
