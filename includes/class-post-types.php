<?php
/**
 * Custom Post Types and Taxonomies.
 *
 * @package Peanut_Booker
 * @since   1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Custom Post Types and Taxonomies.
 */
class Peanut_Booker_Post_Types {

    /**
     * Register custom post types.
     */
    public function register_post_types() {
        $this->register_performer_profile();
        $this->register_market_event();
    }

    /**
     * Register performer profile post type.
     */
    private function register_performer_profile() {
        $labels = array(
            'name'                  => _x( 'Performer Profiles', 'Post type general name', 'peanut-booker' ),
            'singular_name'         => _x( 'Performer Profile', 'Post type singular name', 'peanut-booker' ),
            'menu_name'             => _x( 'Performers', 'Admin Menu text', 'peanut-booker' ),
            'name_admin_bar'        => _x( 'Performer', 'Add New on Toolbar', 'peanut-booker' ),
            'add_new'               => __( 'Add New', 'peanut-booker' ),
            'add_new_item'          => __( 'Add New Performer', 'peanut-booker' ),
            'new_item'              => __( 'New Performer', 'peanut-booker' ),
            'edit_item'             => __( 'Edit Performer', 'peanut-booker' ),
            'view_item'             => __( 'View Performer', 'peanut-booker' ),
            'all_items'             => __( 'All Performers', 'peanut-booker' ),
            'search_items'          => __( 'Search Performers', 'peanut-booker' ),
            'parent_item_colon'     => __( 'Parent Performer:', 'peanut-booker' ),
            'not_found'             => __( 'No performers found.', 'peanut-booker' ),
            'not_found_in_trash'    => __( 'No performers found in Trash.', 'peanut-booker' ),
            'featured_image'        => _x( 'Profile Photo', 'Overrides the "Featured Image" phrase', 'peanut-booker' ),
            'set_featured_image'    => _x( 'Set profile photo', 'Overrides the "Set featured image" phrase', 'peanut-booker' ),
            'remove_featured_image' => _x( 'Remove profile photo', 'Overrides the "Remove featured image" phrase', 'peanut-booker' ),
            'use_featured_image'    => _x( 'Use as profile photo', 'Overrides the "Use as featured image" phrase', 'peanut-booker' ),
            'archives'              => _x( 'Performer Archives', 'The post type archive label', 'peanut-booker' ),
            'attributes'            => _x( 'Performer Attributes', 'The post type attributes label', 'peanut-booker' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => false, // We'll add to our custom menu.
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'performer', 'with_front' => false ),
            'capability_type'    => 'post',
            'has_archive'        => 'performers',
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-groups',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
            'show_in_rest'       => true,
        );

        register_post_type( 'pb_performer', $args );
    }

    /**
     * Register market event post type.
     */
    private function register_market_event() {
        $labels = array(
            'name'                  => _x( 'Market Events', 'Post type general name', 'peanut-booker' ),
            'singular_name'         => _x( 'Market Event', 'Post type singular name', 'peanut-booker' ),
            'menu_name'             => _x( 'Market Events', 'Admin Menu text', 'peanut-booker' ),
            'name_admin_bar'        => _x( 'Event', 'Add New on Toolbar', 'peanut-booker' ),
            'add_new'               => __( 'Add New', 'peanut-booker' ),
            'add_new_item'          => __( 'Add New Event', 'peanut-booker' ),
            'new_item'              => __( 'New Event', 'peanut-booker' ),
            'edit_item'             => __( 'Edit Event', 'peanut-booker' ),
            'view_item'             => __( 'View Event', 'peanut-booker' ),
            'all_items'             => __( 'All Events', 'peanut-booker' ),
            'search_items'          => __( 'Search Events', 'peanut-booker' ),
            'parent_item_colon'     => __( 'Parent Event:', 'peanut-booker' ),
            'not_found'             => __( 'No events found.', 'peanut-booker' ),
            'not_found_in_trash'    => __( 'No events found in Trash.', 'peanut-booker' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'market-event', 'with_front' => false ),
            'capability_type'    => 'post',
            'has_archive'        => 'market',
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-calendar-alt',
            'supports'           => array( 'title', 'editor' ),
            'show_in_rest'       => true,
        );

        register_post_type( 'pb_market_event', $args );
    }

    /**
     * Register custom taxonomies.
     */
    public function register_taxonomies() {
        $this->register_performer_category();
        $this->register_service_area();
    }

    /**
     * Register performer category taxonomy.
     */
    private function register_performer_category() {
        $labels = array(
            'name'                       => _x( 'Performer Categories', 'taxonomy general name', 'peanut-booker' ),
            'singular_name'              => _x( 'Performer Category', 'taxonomy singular name', 'peanut-booker' ),
            'search_items'               => __( 'Search Categories', 'peanut-booker' ),
            'popular_items'              => __( 'Popular Categories', 'peanut-booker' ),
            'all_items'                  => __( 'All Categories', 'peanut-booker' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit Category', 'peanut-booker' ),
            'update_item'                => __( 'Update Category', 'peanut-booker' ),
            'add_new_item'               => __( 'Add New Category', 'peanut-booker' ),
            'new_item_name'              => __( 'New Category Name', 'peanut-booker' ),
            'separate_items_with_commas' => __( 'Separate categories with commas', 'peanut-booker' ),
            'add_or_remove_items'        => __( 'Add or remove categories', 'peanut-booker' ),
            'choose_from_most_used'      => __( 'Choose from the most used categories', 'peanut-booker' ),
            'not_found'                  => __( 'No categories found.', 'peanut-booker' ),
            'menu_name'                  => __( 'Categories', 'peanut-booker' ),
        );

        $args = array(
            'hierarchical'          => true,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'query_var'             => true,
            'rewrite'               => array( 'slug' => 'performer-category' ),
            'show_in_rest'          => true,
        );

        register_taxonomy( 'pb_performer_category', array( 'pb_performer', 'pb_market_event' ), $args );

        // Add default categories on first run.
        if ( ! get_option( 'peanut_booker_default_categories_added' ) ) {
            $this->add_default_categories();
            update_option( 'peanut_booker_default_categories_added', true );
        }
    }

    /**
     * Register service area taxonomy.
     */
    private function register_service_area() {
        $labels = array(
            'name'                       => _x( 'Service Areas', 'taxonomy general name', 'peanut-booker' ),
            'singular_name'              => _x( 'Service Area', 'taxonomy singular name', 'peanut-booker' ),
            'search_items'               => __( 'Search Areas', 'peanut-booker' ),
            'popular_items'              => __( 'Popular Areas', 'peanut-booker' ),
            'all_items'                  => __( 'All Areas', 'peanut-booker' ),
            'parent_item'                => __( 'Parent Area', 'peanut-booker' ),
            'parent_item_colon'          => __( 'Parent Area:', 'peanut-booker' ),
            'edit_item'                  => __( 'Edit Area', 'peanut-booker' ),
            'update_item'                => __( 'Update Area', 'peanut-booker' ),
            'add_new_item'               => __( 'Add New Area', 'peanut-booker' ),
            'new_item_name'              => __( 'New Area Name', 'peanut-booker' ),
            'separate_items_with_commas' => __( 'Separate areas with commas', 'peanut-booker' ),
            'add_or_remove_items'        => __( 'Add or remove areas', 'peanut-booker' ),
            'choose_from_most_used'      => __( 'Choose from the most used areas', 'peanut-booker' ),
            'not_found'                  => __( 'No areas found.', 'peanut-booker' ),
            'menu_name'                  => __( 'Service Areas', 'peanut-booker' ),
        );

        $args = array(
            'hierarchical'          => true,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'query_var'             => true,
            'rewrite'               => array( 'slug' => 'service-area' ),
            'show_in_rest'          => true,
        );

        register_taxonomy( 'pb_service_area', array( 'pb_performer', 'pb_market_event' ), $args );
    }

    /**
     * Add default performer categories.
     */
    private function add_default_categories() {
        $categories = array(
            'comedian'  => __( 'Comedian', 'peanut-booker' ),
            'musician'  => __( 'Musician', 'peanut-booker' ),
            'dj'        => __( 'DJ', 'peanut-booker' ),
            'magician'  => __( 'Magician', 'peanut-booker' ),
            'speaker'   => __( 'Speaker', 'peanut-booker' ),
            'dancer'    => __( 'Dancer', 'peanut-booker' ),
            'variety'   => __( 'Variety Act', 'peanut-booker' ),
        );

        foreach ( $categories as $slug => $name ) {
            if ( ! term_exists( $slug, 'pb_performer_category' ) ) {
                wp_insert_term( $name, 'pb_performer_category', array( 'slug' => $slug ) );
            }
        }
    }

    /**
     * Get performer meta fields.
     *
     * @return array
     */
    public static function get_performer_meta_fields() {
        return array(
            'pb_user_id'             => array(
                'type'    => 'integer',
                'label'   => __( 'User ID', 'peanut-booker' ),
                'default' => 0,
            ),
            'pb_stage_name'          => array(
                'type'    => 'string',
                'label'   => __( 'Stage Name', 'peanut-booker' ),
                'default' => '',
            ),
            'pb_tagline'             => array(
                'type'    => 'string',
                'label'   => __( 'Tagline', 'peanut-booker' ),
                'default' => '',
            ),
            'pb_hourly_rate'         => array(
                'type'    => 'number',
                'label'   => __( 'Hourly Rate', 'peanut-booker' ),
                'default' => 0,
            ),
            'pb_minimum_booking'     => array(
                'type'    => 'number',
                'label'   => __( 'Minimum Booking (hours)', 'peanut-booker' ),
                'default' => 1,
            ),
            'pb_deposit_percentage'  => array(
                'type'    => 'integer',
                'label'   => __( 'Deposit Percentage', 'peanut-booker' ),
                'default' => 25,
            ),
            'pb_sale_price'          => array(
                'type'    => 'number',
                'label'   => __( 'Sale Price', 'peanut-booker' ),
                'default' => 0,
            ),
            'pb_sale_active'         => array(
                'type'    => 'boolean',
                'label'   => __( 'Sale Active', 'peanut-booker' ),
                'default' => false,
            ),
            'pb_gallery_images'      => array(
                'type'    => 'array',
                'label'   => __( 'Gallery Images', 'peanut-booker' ),
                'default' => array(),
            ),
            'pb_video_links'         => array(
                'type'    => 'array',
                'label'   => __( 'Video Links', 'peanut-booker' ),
                'default' => array(),
            ),
            'pb_social_links'        => array(
                'type'    => 'array',
                'label'   => __( 'Social Links', 'peanut-booker' ),
                'default' => array(),
            ),
            'pb_website'             => array(
                'type'    => 'string',
                'label'   => __( 'Website', 'peanut-booker' ),
                'default' => '',
            ),
            'pb_phone'               => array(
                'type'    => 'string',
                'label'   => __( 'Phone', 'peanut-booker' ),
                'default' => '',
            ),
            'pb_email_public'        => array(
                'type'    => 'string',
                'label'   => __( 'Public Email', 'peanut-booker' ),
                'default' => '',
            ),
            'pb_location_city'       => array(
                'type'    => 'string',
                'label'   => __( 'City', 'peanut-booker' ),
                'default' => '',
            ),
            'pb_location_state'      => array(
                'type'    => 'string',
                'label'   => __( 'State', 'peanut-booker' ),
                'default' => '',
            ),
            'pb_travel_willing'      => array(
                'type'    => 'boolean',
                'label'   => __( 'Willing to Travel', 'peanut-booker' ),
                'default' => true,
            ),
            'pb_travel_radius'       => array(
                'type'    => 'integer',
                'label'   => __( 'Travel Radius (miles)', 'peanut-booker' ),
                'default' => 50,
            ),
            'pb_experience_years'    => array(
                'type'    => 'integer',
                'label'   => __( 'Years of Experience', 'peanut-booker' ),
                'default' => 0,
            ),
            'pb_equipment_provided'  => array(
                'type'    => 'boolean',
                'label'   => __( 'Equipment Provided', 'peanut-booker' ),
                'default' => false,
            ),
            'pb_equipment_details'   => array(
                'type'    => 'string',
                'label'   => __( 'Equipment Details', 'peanut-booker' ),
                'default' => '',
            ),
        );
    }

    /**
     * Get market event meta fields.
     *
     * @return array
     */
    public static function get_market_event_meta_fields() {
        return array(
            'pb_customer_id'     => array(
                'type'    => 'integer',
                'label'   => __( 'Customer ID', 'peanut-booker' ),
                'default' => 0,
            ),
            'pb_event_date'      => array(
                'type'    => 'string',
                'label'   => __( 'Event Date', 'peanut-booker' ),
                'default' => '',
            ),
            'pb_event_time'      => array(
                'type'    => 'string',
                'label'   => __( 'Event Time', 'peanut-booker' ),
                'default' => '',
            ),
            'pb_event_duration'  => array(
                'type'    => 'integer',
                'label'   => __( 'Duration (hours)', 'peanut-booker' ),
                'default' => 2,
            ),
            'pb_venue_name'      => array(
                'type'    => 'string',
                'label'   => __( 'Venue Name', 'peanut-booker' ),
                'default' => '',
            ),
            'pb_venue_address'   => array(
                'type'    => 'string',
                'label'   => __( 'Venue Address', 'peanut-booker' ),
                'default' => '',
            ),
            'pb_venue_city'      => array(
                'type'    => 'string',
                'label'   => __( 'City', 'peanut-booker' ),
                'default' => '',
            ),
            'pb_venue_state'     => array(
                'type'    => 'string',
                'label'   => __( 'State', 'peanut-booker' ),
                'default' => '',
            ),
            'pb_venue_zip'       => array(
                'type'    => 'string',
                'label'   => __( 'ZIP Code', 'peanut-booker' ),
                'default' => '',
            ),
            'pb_budget_min'      => array(
                'type'    => 'number',
                'label'   => __( 'Minimum Budget', 'peanut-booker' ),
                'default' => 0,
            ),
            'pb_budget_max'      => array(
                'type'    => 'number',
                'label'   => __( 'Maximum Budget', 'peanut-booker' ),
                'default' => 0,
            ),
            'pb_bid_deadline'    => array(
                'type'    => 'string',
                'label'   => __( 'Bid Deadline', 'peanut-booker' ),
                'default' => '',
            ),
            'pb_event_status'    => array(
                'type'    => 'string',
                'label'   => __( 'Status', 'peanut-booker' ),
                'default' => 'open',
            ),
            'pb_accepted_bid_id' => array(
                'type'    => 'integer',
                'label'   => __( 'Accepted Bid ID', 'peanut-booker' ),
                'default' => 0,
            ),
            'pb_total_bids'      => array(
                'type'    => 'integer',
                'label'   => __( 'Total Bids', 'peanut-booker' ),
                'default' => 0,
            ),
            'pb_special_requirements' => array(
                'type'    => 'string',
                'label'   => __( 'Special Requirements', 'peanut-booker' ),
                'default' => '',
            ),
        );
    }
}
