<?php
/**
 * Shortcodes functionality.
 *
 * @package Peanut_Booker
 * @since   1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Shortcodes class.
 */
class Peanut_Booker_Shortcodes {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->register_shortcodes();
    }

    /**
     * Register all shortcodes.
     */
    private function register_shortcodes() {
        add_shortcode( 'pb_performer_directory', array( $this, 'performer_directory' ) );
        add_shortcode( 'pb_market', array( $this, 'market' ) );
        add_shortcode( 'pb_my_dashboard', array( $this, 'dashboard' ) );
        add_shortcode( 'pb_performer_signup', array( $this, 'performer_signup' ) );
        add_shortcode( 'pb_customer_signup', array( $this, 'customer_signup' ) );
        add_shortcode( 'pb_featured_performers', array( $this, 'featured_performers' ) );
        add_shortcode( 'pb_booking_form', array( $this, 'booking_form' ) );
        add_shortcode( 'pb_performer_calendar', array( $this, 'performer_calendar' ) );
        add_shortcode( 'pb_upgrade_pro', array( $this, 'upgrade_pro' ) );
        add_shortcode( 'pb_auth', array( $this, 'auth_widget' ) );
        add_shortcode( 'pb_login', array( $this, 'login_page' ) );
    }

    /**
     * Performer directory shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function performer_directory( $atts ) {
        $atts = shortcode_atts(
            array(
                'category'     => '',
                'service_area' => '',
                'per_page'     => 12,
                'columns'      => 3,
                'show_filters' => 'yes',
            ),
            $atts
        );

        // Get current filters.
        $current_category = isset( $_GET['pb_category'] ) ? sanitize_text_field( $_GET['pb_category'] ) : $atts['category'];
        $current_area     = isset( $_GET['pb_area'] ) ? sanitize_text_field( $_GET['pb_area'] ) : $atts['service_area'];
        $current_search   = isset( $_GET['pb_search'] ) ? sanitize_text_field( $_GET['pb_search'] ) : '';
        $current_page     = isset( $_GET['pb_page'] ) ? absint( $_GET['pb_page'] ) : 1;

        $result = Peanut_Booker_Performer::query(
            array(
                'category'       => $current_category,
                'service_area'   => $current_area,
                'search'         => $current_search,
                'posts_per_page' => $atts['per_page'],
                'paged'          => $current_page,
            )
        );

        ob_start();
        ?>
        <div class="pb-performer-directory" data-columns="<?php echo esc_attr( $atts['columns'] ); ?>">
            <?php if ( 'yes' === $atts['show_filters'] ) : ?>
                <form class="pb-filters" method="get">
                    <div class="pb-filter-group">
                        <input type="text" name="pb_search" placeholder="<?php esc_attr_e( 'Search performers...', 'peanut-booker' ); ?>" value="<?php echo esc_attr( $current_search ); ?>">
                    </div>

                    <div class="pb-filter-group">
                        <select name="pb_category">
                            <option value=""><?php esc_html_e( 'All Categories', 'peanut-booker' ); ?></option>
                            <?php
                            $categories = get_terms(
                                array(
                                    'taxonomy'   => 'pb_performer_category',
                                    'hide_empty' => true,
                                )
                            );
                            foreach ( $categories as $cat ) {
                                printf(
                                    '<option value="%s" %s>%s</option>',
                                    esc_attr( $cat->slug ),
                                    selected( $current_category, $cat->slug, false ),
                                    esc_html( $cat->name )
                                );
                            }
                            ?>
                        </select>
                    </div>

                    <div class="pb-filter-group">
                        <select name="pb_area">
                            <option value=""><?php esc_html_e( 'All Areas', 'peanut-booker' ); ?></option>
                            <?php
                            $areas = get_terms(
                                array(
                                    'taxonomy'   => 'pb_service_area',
                                    'hide_empty' => true,
                                )
                            );
                            foreach ( $areas as $area ) {
                                printf(
                                    '<option value="%s" %s>%s</option>',
                                    esc_attr( $area->slug ),
                                    selected( $current_area, $area->slug, false ),
                                    esc_html( $area->name )
                                );
                            }
                            ?>
                        </select>
                    </div>

                    <button type="submit" class="pb-button"><?php esc_html_e( 'Search', 'peanut-booker' ); ?></button>
                </form>
            <?php endif; ?>

            <?php if ( empty( $result['performers'] ) ) : ?>
                <p class="pb-no-results"><?php esc_html_e( 'No performers found matching your criteria.', 'peanut-booker' ); ?></p>
            <?php else : ?>
                <div class="pb-performer-grid">
                    <?php foreach ( $result['performers'] as $performer ) : ?>
                        <div class="pb-performer-card">
                            <a href="<?php echo esc_url( $performer['permalink'] ); ?>">
                                <?php if ( $performer['thumbnail'] ) : ?>
                                    <img src="<?php echo esc_url( $performer['thumbnail'] ); ?>" alt="<?php echo esc_attr( $performer['name'] ); ?>" class="pb-performer-image">
                                <?php else : ?>
                                    <div class="pb-performer-image pb-no-image"></div>
                                <?php endif; ?>
                            </a>

                            <div class="pb-performer-info">
                                <h3 class="pb-performer-name">
                                    <a href="<?php echo esc_url( $performer['permalink'] ); ?>"><?php echo esc_html( $performer['name'] ); ?></a>
                                </h3>

                                <?php if ( $performer['tagline'] ) : ?>
                                    <p class="pb-performer-tagline"><?php echo esc_html( $performer['tagline'] ); ?></p>
                                <?php endif; ?>

                                <div class="pb-performer-meta">
                                    <?php echo wp_kses_post( Peanut_Booker_Performer::get_achievement_badge( $performer['achievement_level'] ) ); ?>

                                    <?php if ( $performer['average_rating'] ) : ?>
                                        <?php echo wp_kses_post( Peanut_Booker_Reviews::render_stars( $performer['average_rating'], $performer['total_reviews'] ) ); ?>
                                    <?php endif; ?>
                                </div>

                                <div class="pb-performer-pricing">
                                    <?php if ( $performer['sale_active'] && $performer['sale_price'] ) : ?>
                                        <span class="pb-price-sale"><?php echo wc_price( $performer['sale_price'] ); ?></span>
                                        <span class="pb-price-regular pb-strikethrough"><?php echo wc_price( $performer['hourly_rate'] ); ?></span>
                                    <?php else : ?>
                                        <span class="pb-price"><?php echo wc_price( $performer['hourly_rate'] ); ?></span>
                                    <?php endif; ?>
                                    <span class="pb-price-suffix"><?php esc_html_e( '/hour', 'peanut-booker' ); ?></span>
                                </div>

                                <?php if ( ! empty( $performer['categories'] ) ) : ?>
                                    <div class="pb-performer-categories">
                                        <?php echo esc_html( implode( ', ', $performer['categories'] ) ); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ( $result['max_pages'] > 1 ) : ?>
                    <div class="pb-pagination">
                        <?php
                        $big = 999999999;
                        echo paginate_links(
                            array(
                                'base'      => add_query_arg( 'pb_page', '%#%' ),
                                'format'    => '',
                                'current'   => $current_page,
                                'total'     => $result['max_pages'],
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                            )
                        );
                        ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Market shortcode - Shows performer marketplace.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function market( $atts ) {
        $atts = shortcode_atts(
            array(
                'per_page'     => 12,
                'columns'      => 3,
                'show_filters' => 'yes',
            ),
            $atts
        );

        // Get current filters.
        $current_category = isset( $_GET['pb_category'] ) ? sanitize_text_field( $_GET['pb_category'] ) : '';
        $current_area     = isset( $_GET['pb_area'] ) ? sanitize_text_field( $_GET['pb_area'] ) : '';
        $current_page     = isset( $_GET['pb_page'] ) ? absint( $_GET['pb_page'] ) : 1;

        // Query performers.
        $result = Peanut_Booker_Performer::query(
            array(
                'category'       => $current_category,
                'service_area'   => $current_area,
                'posts_per_page' => $atts['per_page'],
                'paged'          => $current_page,
            )
        );

        ob_start();
        ?>
        <div class="pb-market pb-performer-marketplace" data-columns="<?php echo esc_attr( $atts['columns'] ); ?>">
            <div class="pb-market-header">
                <h2><?php esc_html_e( 'Find Performers', 'peanut-booker' ); ?></h2>
                <p class="pb-market-subtitle"><?php esc_html_e( 'Browse our talented performers and book the perfect entertainment for your event.', 'peanut-booker' ); ?></p>
            </div>

            <?php if ( 'yes' === $atts['show_filters'] ) : ?>
                <form class="pb-filters pb-market-filters" method="get">
                    <div class="pb-filter-group">
                        <label><?php esc_html_e( 'Category Needed', 'peanut-booker' ); ?></label>
                        <select name="pb_category">
                            <option value=""><?php esc_html_e( 'All Categories', 'peanut-booker' ); ?></option>
                            <?php
                            $categories = get_terms(
                                array(
                                    'taxonomy'   => 'pb_performer_category',
                                    'hide_empty' => false,
                                )
                            );
                            if ( ! is_wp_error( $categories ) ) {
                                foreach ( $categories as $cat ) {
                                    printf(
                                        '<option value="%s" %s>%s</option>',
                                        esc_attr( $cat->slug ),
                                        selected( $current_category, $cat->slug, false ),
                                        esc_html( $cat->name )
                                    );
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="pb-filter-group">
                        <label><?php esc_html_e( 'Location', 'peanut-booker' ); ?></label>
                        <select name="pb_area">
                            <option value=""><?php esc_html_e( 'All Areas', 'peanut-booker' ); ?></option>
                            <?php
                            $areas = get_terms(
                                array(
                                    'taxonomy'   => 'pb_service_area',
                                    'hide_empty' => false,
                                )
                            );
                            if ( ! is_wp_error( $areas ) ) {
                                foreach ( $areas as $area ) {
                                    printf(
                                        '<option value="%s" %s>%s</option>',
                                        esc_attr( $area->slug ),
                                        selected( $current_area, $area->slug, false ),
                                        esc_html( $area->name )
                                    );
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <button type="submit" class="pb-button pb-button-primary"><?php esc_html_e( 'Filter', 'peanut-booker' ); ?></button>
                </form>
            <?php endif; ?>

            <?php if ( empty( $result['performers'] ) ) : ?>
                <p class="pb-no-results"><?php esc_html_e( 'No performers found matching your criteria.', 'peanut-booker' ); ?></p>
            <?php else : ?>
                <div class="pb-performer-grid pb-market-grid">
                    <?php foreach ( $result['performers'] as $performer ) : ?>
                        <div class="pb-performer-card pb-market-tile">
                            <a href="<?php echo esc_url( $performer['permalink'] ); ?>" class="pb-performer-link">
                                <?php if ( $performer['thumbnail'] ) : ?>
                                    <div class="pb-performer-image">
                                        <img src="<?php echo esc_url( $performer['thumbnail'] ); ?>" alt="<?php echo esc_attr( $performer['name'] ); ?>">
                                    </div>
                                <?php else : ?>
                                    <div class="pb-performer-image pb-no-image">
                                        <span class="pb-placeholder-icon">🎭</span>
                                    </div>
                                <?php endif; ?>

                                <div class="pb-performer-info">
                                    <h3 class="pb-performer-name"><?php echo esc_html( $performer['name'] ); ?></h3>

                                    <?php if ( ! empty( $performer['categories'] ) ) : ?>
                                        <div class="pb-performer-category">
                                            <?php echo esc_html( implode( ', ', $performer['categories'] ) ); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ( $performer['tagline'] ) : ?>
                                        <p class="pb-performer-tagline"><?php echo esc_html( wp_trim_words( $performer['tagline'], 10 ) ); ?></p>
                                    <?php endif; ?>

                                    <div class="pb-performer-meta">
                                        <?php if ( $performer['average_rating'] ) : ?>
                                            <span class="pb-rating">
                                                <span class="pb-stars"><?php echo esc_html( number_format( $performer['average_rating'], 1 ) ); ?> ★</span>
                                                <span class="pb-review-count">(<?php echo esc_html( $performer['total_reviews'] ); ?>)</span>
                                            </span>
                                        <?php endif; ?>

                                        <?php echo wp_kses_post( Peanut_Booker_Performer::get_achievement_badge( $performer['achievement_level'] ) ); ?>
                                    </div>

                                    <div class="pb-performer-pricing">
                                        <?php if ( $performer['sale_active'] && $performer['sale_price'] ) : ?>
                                            <span class="pb-price-sale"><?php echo wc_price( $performer['sale_price'] ); ?></span>
                                            <span class="pb-price-regular pb-strikethrough"><?php echo wc_price( $performer['hourly_rate'] ); ?></span>
                                        <?php else : ?>
                                            <span class="pb-price"><?php echo wc_price( $performer['hourly_rate'] ); ?></span>
                                        <?php endif; ?>
                                        <span class="pb-price-suffix"><?php esc_html_e( '/hour', 'peanut-booker' ); ?></span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ( $result['max_pages'] > 1 ) : ?>
                    <div class="pb-pagination">
                        <?php
                        echo paginate_links(
                            array(
                                'base'      => add_query_arg( 'pb_page', '%#%' ),
                                'format'    => '',
                                'current'   => $current_page,
                                'total'     => $result['max_pages'],
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                            )
                        );
                        ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Dashboard shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function dashboard( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<p class="pb-login-required">' . esc_html__( 'Please log in to access your dashboard.', 'peanut-booker' ) . ' <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">' . esc_html__( 'Log In', 'peanut-booker' ) . '</a></p>';
        }

        $user_id = get_current_user_id();

        ob_start();

        // Route to role-specific dashboard templates that handle their own tabs.
        if ( Peanut_Booker_Roles::is_performer( $user_id ) ) {
            $template_file = PEANUT_BOOKER_PATH . 'public/partials/dashboard-performer.php';
        } elseif ( Peanut_Booker_Roles::is_customer( $user_id ) ) {
            $template_file = PEANUT_BOOKER_PATH . 'public/partials/dashboard-customer.php';
        } else {
            // Not a performer or customer - show role selection.
            ?>
            <div class="pb-dashboard pb-no-role">
                <h2><?php esc_html_e( 'Welcome to Peanut Booker!', 'peanut-booker' ); ?></h2>
                <p><?php esc_html_e( 'Choose how you want to use the platform:', 'peanut-booker' ); ?></p>
                <div class="pb-role-selection">
                    <a href="<?php echo esc_url( home_url( '/performer-signup/' ) ); ?>" class="pb-role-card">
                        <h3><?php esc_html_e( 'I\'m a Performer', 'peanut-booker' ); ?></h3>
                        <p><?php esc_html_e( 'Create a profile, get booked for events, and grow your entertainment business.', 'peanut-booker' ); ?></p>
                    </a>
                    <a href="<?php echo esc_url( home_url( '/customer-signup/' ) ); ?>" class="pb-role-card">
                        <h3><?php esc_html_e( 'I\'m Booking Entertainment', 'peanut-booker' ); ?></h3>
                        <p><?php esc_html_e( 'Find and book amazing performers for your events.', 'peanut-booker' ); ?></p>
                    </a>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        if ( file_exists( $template_file ) ) {
            include $template_file;
        } else {
            echo '<p>' . esc_html__( 'Dashboard not available.', 'peanut-booker' ) . '</p>';
        }

        return ob_get_clean();
    }

    /**
     * Performer signup shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function performer_signup( $atts ) {
        if ( is_user_logged_in() ) {
            if ( Peanut_Booker_Roles::is_performer() ) {
                return '<p>' . esc_html__( 'You are already registered as a performer.', 'peanut-booker' ) . ' <a href="' . esc_url( home_url( '/dashboard/' ) ) . '">' . esc_html__( 'Go to Dashboard', 'peanut-booker' ) . '</a></p>';
            }
        }

        ob_start();
        include PEANUT_BOOKER_PATH . 'public/partials/signup-performer.php';
        return ob_get_clean();
    }

    /**
     * Customer signup shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function customer_signup( $atts ) {
        if ( is_user_logged_in() ) {
            if ( Peanut_Booker_Roles::is_customer() ) {
                return '<p>' . esc_html__( 'You are already registered.', 'peanut-booker' ) . ' <a href="' . esc_url( home_url( '/performer-directory/' ) ) . '">' . esc_html__( 'Find Performers', 'peanut-booker' ) . '</a></p>';
            }
        }

        ob_start();
        include PEANUT_BOOKER_PATH . 'public/partials/signup-customer.php';
        return ob_get_clean();
    }

    /**
     * Featured performers shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function featured_performers( $atts ) {
        $atts = shortcode_atts(
            array(
                'count'   => 4,
                'columns' => 4,
            ),
            $atts
        );

        $performers = Peanut_Booker_Performer::get_featured( $atts['count'] );

        if ( empty( $performers ) ) {
            return '';
        }

        ob_start();
        ?>
        <div class="pb-featured-performers" data-columns="<?php echo esc_attr( $atts['columns'] ); ?>">
            <h2><?php esc_html_e( 'Featured Performers', 'peanut-booker' ); ?></h2>

            <div class="pb-performer-grid">
                <?php foreach ( $performers as $performer ) : ?>
                    <div class="pb-performer-card pb-featured">
                        <a href="<?php echo esc_url( $performer['permalink'] ); ?>">
                            <?php if ( $performer['thumbnail'] ) : ?>
                                <img src="<?php echo esc_url( $performer['thumbnail'] ); ?>" alt="<?php echo esc_attr( $performer['name'] ); ?>">
                            <?php endif; ?>
                        </a>
                        <h3><a href="<?php echo esc_url( $performer['permalink'] ); ?>"><?php echo esc_html( $performer['name'] ); ?></a></h3>
                        <?php if ( $performer['average_rating'] ) : ?>
                            <?php echo wp_kses_post( Peanut_Booker_Reviews::render_stars( $performer['average_rating'] ) ); ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Booking form shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function booking_form( $atts ) {
        $atts = shortcode_atts(
            array(
                'performer_id' => 0,
            ),
            $atts
        );

        if ( ! is_user_logged_in() ) {
            return '<p>' . esc_html__( 'Please log in to book a performer.', 'peanut-booker' ) . '</p>';
        }

        $performer_id = absint( $atts['performer_id'] );
        if ( ! $performer_id ) {
            return '';
        }

        $performer = Peanut_Booker_Performer::get( $performer_id );
        if ( ! $performer || ! $performer->profile_id ) {
            return '<p>' . esc_html__( 'Performer not found.', 'peanut-booker' ) . '</p>';
        }

        $performer_data = Peanut_Booker_Performer::get_display_data( $performer->profile_id );

        ob_start();
        include PEANUT_BOOKER_PATH . 'public/partials/booking-form.php';
        return ob_get_clean();
    }

    /**
     * Performer calendar shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function performer_calendar( $atts ) {
        $atts = shortcode_atts(
            array(
                'performer_id' => 0,
            ),
            $atts
        );

        $performer_id = absint( $atts['performer_id'] );
        if ( ! $performer_id ) {
            return '';
        }

        $month = isset( $_GET['month'] ) ? sanitize_text_field( $_GET['month'] ) : gmdate( 'Y-m' );

        return Peanut_Booker_Availability::render_calendar( $performer_id, $month, false );
    }

    /**
     * Upgrade to Pro shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function upgrade_pro( $atts ) {
        if ( ! is_user_logged_in() || ! Peanut_Booker_Roles::is_performer() ) {
            return '';
        }

        if ( Peanut_Booker_Roles::is_pro_performer() ) {
            return '<p class="pb-pro-badge">' . esc_html__( 'You are a Pro member!', 'peanut-booker' ) . '</p>';
        }

        return Peanut_Booker_Subscriptions::render_upgrade_prompt();
    }

    /**
     * Auth widget shortcode - compact login/signup or user info.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function auth_widget( $atts ) {
        $atts = shortcode_atts(
            array(
                'style' => 'compact', // compact, full, dropdown.
            ),
            $atts
        );

        ob_start();

        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $dashboard_url = home_url( '/dashboard/' );
            ?>
            <div class="pb-auth-widget pb-auth-logged-in pb-auth-<?php echo esc_attr( $atts['style'] ); ?>">
                <div class="pb-auth-user">
                    <?php echo get_avatar( $user->ID, 32 ); ?>
                    <span class="pb-auth-name"><?php echo esc_html( $user->display_name ); ?></span>
                </div>
                <div class="pb-auth-links">
                    <a href="<?php echo esc_url( $dashboard_url ); ?>" class="pb-auth-link">
                        <?php esc_html_e( 'Dashboard', 'peanut-booker' ); ?>
                    </a>
                    <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="pb-auth-link pb-auth-logout">
                        <?php esc_html_e( 'Log Out', 'peanut-booker' ); ?>
                    </a>
                </div>
            </div>
            <?php
        } else {
            $login_url = home_url( '/login/' );
            ?>
            <div class="pb-auth-widget pb-auth-guest pb-auth-<?php echo esc_attr( $atts['style'] ); ?>">
                <div class="pb-auth-buttons">
                    <a href="<?php echo esc_url( $login_url ); ?>" class="pb-button pb-button-secondary pb-auth-login-btn">
                        <?php esc_html_e( 'Log In', 'peanut-booker' ); ?>
                    </a>
                    <a href="<?php echo esc_url( $login_url ); ?>?action=signup" class="pb-button pb-button-primary pb-auth-signup-btn">
                        <?php esc_html_e( 'Sign Up', 'peanut-booker' ); ?>
                    </a>
                </div>
            </div>
            <?php
        }

        return ob_get_clean();
    }

    /**
     * Custom login page shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function login_page( $atts ) {
        // If already logged in, redirect or show dashboard link.
        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            ob_start();
            ?>
            <div class="pb-auth-page pb-auth-logged-in">
                <div class="pb-auth-welcome">
                    <?php echo get_avatar( $user->ID, 64 ); ?>
                    <h2><?php printf( esc_html__( 'Welcome back, %s!', 'peanut-booker' ), esc_html( $user->display_name ) ); ?></h2>
                    <div class="pb-auth-actions">
                        <a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="pb-button pb-button-primary">
                            <?php esc_html_e( 'Go to Dashboard', 'peanut-booker' ); ?>
                        </a>
                        <a href="<?php echo esc_url( home_url( '/performer-directory/' ) ); ?>" class="pb-button pb-button-secondary">
                            <?php esc_html_e( 'Browse Performers', 'peanut-booker' ); ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        // Determine if showing login or signup.
        $action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'login';
        $redirect = isset( $_GET['redirect_to'] ) ? esc_url_raw( $_GET['redirect_to'] ) : home_url( '/dashboard/' );

        ob_start();
        ?>
        <div class="pb-auth-page">
            <div class="pb-auth-tabs">
                <a href="<?php echo esc_url( add_query_arg( 'action', 'login', remove_query_arg( 'action' ) ) ); ?>"
                   class="pb-auth-tab <?php echo 'login' === $action ? 'active' : ''; ?>">
                    <?php esc_html_e( 'Log In', 'peanut-booker' ); ?>
                </a>
                <a href="<?php echo esc_url( add_query_arg( 'action', 'signup', remove_query_arg( 'action' ) ) ); ?>"
                   class="pb-auth-tab <?php echo 'signup' === $action ? 'active' : ''; ?>">
                    <?php esc_html_e( 'Sign Up', 'peanut-booker' ); ?>
                </a>
            </div>

            <?php if ( isset( $_GET['pb_auth_error'] ) ) : ?>
                <div class="pb-message pb-message-error">
                    <?php echo esc_html( urldecode( sanitize_text_field( wp_unslash( $_GET['pb_auth_error'] ) ) ) ); ?>
                </div>
            <?php endif; ?>

            <?php if ( isset( $_GET['login'] ) && 'failed' === $_GET['login'] ) : ?>
                <div class="pb-message pb-message-error">
                    <?php esc_html_e( 'Invalid username or password.', 'peanut-booker' ); ?>
                </div>
            <?php endif; ?>

            <?php if ( isset( $_GET['logged_out'] ) && 'true' === $_GET['logged_out'] ) : ?>
                <div class="pb-message pb-message-success">
                    <?php esc_html_e( 'You have been logged out successfully.', 'peanut-booker' ); ?>
                </div>
            <?php endif; ?>

            <?php if ( 'signup' === $action ) : ?>
                <!-- Signup: Role Selection -->
                <div class="pb-auth-signup">
                    <h2><?php esc_html_e( 'Create Your Account', 'peanut-booker' ); ?></h2>
                    <p class="pb-auth-subtitle"><?php esc_html_e( 'How do you want to use Peanut Booker?', 'peanut-booker' ); ?></p>

                    <div class="pb-role-selection">
                        <a href="<?php echo esc_url( home_url( '/performer-signup/' ) ); ?>" class="pb-role-card pb-role-performer">
                            <div class="pb-role-icon">🎭</div>
                            <h3><?php esc_html_e( "I'm a Performer", 'peanut-booker' ); ?></h3>
                            <p><?php esc_html_e( 'Create a profile, showcase your talent, and get booked for events.', 'peanut-booker' ); ?></p>
                            <ul class="pb-role-benefits">
                                <li><?php esc_html_e( 'Professional profile page', 'peanut-booker' ); ?></li>
                                <li><?php esc_html_e( 'Booking calendar management', 'peanut-booker' ); ?></li>
                                <li><?php esc_html_e( 'Secure payment processing', 'peanut-booker' ); ?></li>
                            </ul>
                            <span class="pb-button pb-button-primary"><?php esc_html_e( 'Sign Up as Performer', 'peanut-booker' ); ?></span>
                        </a>

                        <a href="<?php echo esc_url( home_url( '/customer-signup/' ) ); ?>" class="pb-role-card pb-role-customer">
                            <div class="pb-role-icon">🎉</div>
                            <h3><?php esc_html_e( "I'm Booking Entertainment", 'peanut-booker' ); ?></h3>
                            <p><?php esc_html_e( 'Find and book amazing performers for your events.', 'peanut-booker' ); ?></p>
                            <ul class="pb-role-benefits">
                                <li><?php esc_html_e( 'Browse verified performers', 'peanut-booker' ); ?></li>
                                <li><?php esc_html_e( 'Post events for bids', 'peanut-booker' ); ?></li>
                                <li><?php esc_html_e( 'Escrow payment protection', 'peanut-booker' ); ?></li>
                            </ul>
                            <span class="pb-button pb-button-primary"><?php esc_html_e( 'Sign Up as Customer', 'peanut-booker' ); ?></span>
                        </a>
                    </div>

                    <div class="pb-auth-guest-option">
                        <p>
                            <?php esc_html_e( 'Just browsing?', 'peanut-booker' ); ?>
                            <a href="<?php echo esc_url( home_url( '/performer-directory/' ) ); ?>">
                                <?php esc_html_e( 'Continue as Guest', 'peanut-booker' ); ?>
                            </a>
                        </p>
                    </div>
                </div>

            <?php else : ?>
                <!-- Login Form -->
                <div class="pb-auth-login">
                    <?php
                    // Show Google login if enabled.
                    if ( class_exists( 'Peanut_Booker_Google_Auth' ) ) {
                        echo Peanut_Booker_Google_Auth::render_button( 'login', $redirect, __( 'Continue with Google', 'peanut-booker' ) );

                        if ( Peanut_Booker_Google_Auth::is_enabled() ) :
                        ?>
                            <div class="pb-social-divider">
                                <span><?php esc_html_e( 'or', 'peanut-booker' ); ?></span>
                            </div>
                        <?php
                        endif;
                    }
                    ?>

                    <form class="pb-login-form" method="post" action="<?php echo esc_url( wp_login_url() ); ?>">
                        <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect ); ?>">

                        <div class="pb-form-row">
                            <label for="pb-login-user"><?php esc_html_e( 'Username or Email', 'peanut-booker' ); ?></label>
                            <input type="text" id="pb-login-user" name="log" required autofocus>
                        </div>

                        <div class="pb-form-row">
                            <label for="pb-login-pass"><?php esc_html_e( 'Password', 'peanut-booker' ); ?></label>
                            <input type="password" id="pb-login-pass" name="pwd" required>
                        </div>

                        <div class="pb-form-row pb-form-row-inline">
                            <label class="pb-checkbox-label">
                                <input type="checkbox" name="rememberme" value="forever">
                                <?php esc_html_e( 'Remember me', 'peanut-booker' ); ?>
                            </label>
                            <a href="<?php echo esc_url( wp_lostpassword_url( get_permalink() ) ); ?>" class="pb-forgot-password">
                                <?php esc_html_e( 'Forgot password?', 'peanut-booker' ); ?>
                            </a>
                        </div>

                        <div class="pb-form-actions">
                            <button type="submit" class="pb-button pb-button-primary pb-button-full">
                                <?php esc_html_e( 'Log In', 'peanut-booker' ); ?>
                            </button>
                        </div>
                    </form>

                    <div class="pb-auth-guest-option">
                        <p>
                            <?php esc_html_e( 'Just browsing?', 'peanut-booker' ); ?>
                            <a href="<?php echo esc_url( home_url( '/performer-directory/' ) ); ?>">
                                <?php esc_html_e( 'Continue as Guest', 'peanut-booker' ); ?>
                            </a>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
