<?php
/**
 * Demo Data Creators Trait
 *
 * @package    Peanut_Booker
 * @subpackage Demo_Data
 * @since      1.5.5
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Trait Peanut_Booker_Demo_Creators
 *
 * Methods for creating demo data.
 *
 * @since 1.5.5
 */
trait Peanut_Booker_Demo_Creators {
    /**
     * Create demo categories.
     */
    private static function create_demo_categories() {
        $categories = array(
            'Musicians'    => 'Live music performers including bands, soloists, and orchestras',
            'DJs'          => 'Professional disc jockeys for parties and events',
            'Magicians'    => 'Illusionists and magic performers',
            'Comedians'    => 'Stand-up comics and comedy performers',
            'Speakers'     => 'Keynote speakers and motivational presenters',
            'Dancers'      => 'Dance performers and choreographers',
            'Variety Acts' => 'Unique performers including jugglers, acrobats, and more',
        );

        foreach ( $categories as $name => $description ) {
            if ( ! term_exists( $name, 'pb_performer_category' ) ) {
                wp_insert_term( $name, 'pb_performer_category', array(
                    'description' => $description,
                    'slug'        => sanitize_title( $name ),
                ) );
            }
        }

        // Service areas.
        $areas = array(
            'New York Metro',
            'Los Angeles',
            'Chicago',
            'Miami',
            'San Francisco Bay Area',
            'Austin',
            'Nashville',
            'Atlanta',
            'Las Vegas',
            'Portland',
            'Boston',
            'Phoenix',
            'Seattle',
            'Denver',
            'Dallas-Fort Worth',
        );

        foreach ( $areas as $area ) {
            if ( ! term_exists( $area, 'pb_service_area' ) ) {
                wp_insert_term( $area, 'pb_service_area' );
            }
        }
    }

    /**
     * Create demo performers.
     *
     * @return array User IDs of created performers.
     */
    private static function create_demo_performers() {
        global $wpdb;
        $user_ids = array();

        foreach ( self::$performers as $index => $performer_data ) {
            // Create WP user.
            $user_id = wp_create_user(
                'demo_performer_' . ( $index + 1 ),
                wp_generate_password(),
                $performer_data['email']
            );

            if ( is_wp_error( $user_id ) ) {
                continue;
            }

            // Update user details.
            wp_update_user( array(
                'ID'           => $user_id,
                'display_name' => $performer_data['name'],
                'first_name'   => explode( ' ', $performer_data['name'] )[0],
                'role'         => 'pb_performer',
            ) );

            // Create performer profile post.
            $profile_id = wp_insert_post( array(
                'post_type'    => 'pb_performer',
                'post_title'   => $performer_data['name'],
                'post_content' => $performer_data['bio'],
                'post_status'  => 'publish',
                'post_author'  => $user_id,
            ) );

            if ( is_wp_error( $profile_id ) ) {
                wp_delete_user( $user_id );
                continue;
            }

            // Set category.
            $term = get_term_by( 'name', $performer_data['category'], 'pb_performer_category' );
            if ( $term ) {
                wp_set_object_terms( $profile_id, $term->term_id, 'pb_performer_category' );
            }

            // Set service areas - main city plus some nearby.
            $city_to_area = array(
                'Las Vegas'     => 'Las Vegas',
                'New York'      => 'New York Metro',
                'Miami'         => 'Miami',
                'Chicago'       => 'Chicago',
                'Austin'        => 'Austin',
                'Atlanta'       => 'Atlanta',
                'Portland'      => 'Portland',
                'Los Angeles'   => 'Los Angeles',
                'San Francisco' => 'San Francisco Bay Area',
                'Nashville'     => 'Nashville',
                'Boston'        => 'Boston',
                'Phoenix'       => 'Phoenix',
            );

            $area_name = $city_to_area[ $performer_data['city'] ] ?? $performer_data['city'];
            $area_term = get_term_by( 'name', $area_name, 'pb_service_area' );
            if ( $area_term ) {
                wp_set_object_terms( $profile_id, $area_term->term_id, 'pb_service_area' );
            }

            // Calculate achievement score.
            $achievement_score = self::calculate_demo_achievement_score(
                $performer_data['completed_bookings'],
                $performer_data['avg_rating'],
                wp_rand( 85, 100 )
            );

            // Create performer database record.
            $wpdb->insert(
                $wpdb->prefix . 'pb_performers',
                array(
                    'user_id'              => $user_id,
                    'profile_id'           => $profile_id,
                    'tier'                 => $performer_data['tier'],
                    'hourly_rate'          => $performer_data['hourly_rate'],
                    'deposit_percentage'   => wp_rand( 25, 50 ),
                    'is_verified'          => $performer_data['verified'] ? 1 : 0,
                    'is_featured'          => $performer_data['featured'] ? 1 : 0,
                    'status'               => 'active',
                    'completed_bookings'   => $performer_data['completed_bookings'],
                    'total_reviews'        => $performer_data['total_reviews'],
                    'average_rating'       => $performer_data['avg_rating'],
                    'profile_completeness' => wp_rand( 85, 100 ),
                    'achievement_level'    => $performer_data['achievement_level'],
                    'achievement_score'    => $achievement_score,
                    'created_at'           => gmdate( 'Y-m-d H:i:s', strtotime( '-' . wp_rand( 60, 730 ) . ' days' ) ),
                ),
                array( '%d', '%d', '%s', '%f', '%d', '%d', '%d', '%s', '%d', '%d', '%f', '%d', '%s', '%d', '%s' )
            );

            $performer_id = $wpdb->insert_id;

            // Save post meta.
            update_post_meta( $profile_id, 'pb_user_id', $user_id );
            update_post_meta( $profile_id, 'pb_performer_id', $performer_id );
            update_post_meta( $profile_id, 'pb_tagline', $performer_data['tagline'] );
            update_post_meta( $profile_id, 'pb_hourly_rate', $performer_data['hourly_rate'] );
            update_post_meta( $profile_id, 'pb_location_city', $performer_data['city'] );
            update_post_meta( $profile_id, 'pb_location_state', $performer_data['state'] );
            update_post_meta( $profile_id, 'pb_experience_years', $performer_data['experience'] );
            update_post_meta( $profile_id, 'pb_travel_willing', 1 );
            update_post_meta( $profile_id, 'pb_travel_radius', wp_rand( 50, 200 ) );

            // Create availability.
            self::create_demo_availability( $performer_id );

            $user_ids[] = $user_id;
        }

        return $user_ids;
    }

    /**
     * Calculate demo achievement score.
     *
     * @param int   $bookings Completed bookings.
     * @param float $rating   Average rating.
     * @param int   $profile  Profile completeness.
     * @return int Achievement score.
     */
    private static function calculate_demo_achievement_score( $bookings, $rating, $profile ) {
        return (int) ( ( $bookings * 10 ) + ( $rating * 20 ) + ( $profile * 0.5 ) );
    }

    /**
     * Create demo availability for a performer.
     *
     * @param int $performer_id Performer ID.
     */
    private static function create_demo_availability( $performer_id ) {
        global $wpdb;

        // Create availability for past 30 days and next 90 days.
        for ( $i = -30; $i <= 90; $i++ ) {
            $date = gmdate( 'Y-m-d', strtotime( "$i days" ) );

            // Weekends more likely to be booked.
            $day_of_week = gmdate( 'N', strtotime( $date ) );
            $is_weekend  = ( $day_of_week >= 6 );

            // Past dates: some booked, some available.
            // Future dates: mostly available.
            if ( $i < 0 ) {
                // Past: 40% were bookings (show as blocked), 60% were available.
                $is_available = ( wp_rand( 1, 100 ) > 40 );
            } else {
                // Future: 75-85% available (weekends more likely to be booked).
                $availability_chance = $is_weekend ? 70 : 85;
                $is_available = ( wp_rand( 1, 100 ) <= $availability_chance );
            }

            $wpdb->insert(
                $wpdb->prefix . 'pb_availability',
                array(
                    'performer_id' => $performer_id,
                    'date'         => $date,
                    'slot_type'    => 'full_day',
                    'status'       => $is_available ? 'available' : 'blocked',
                    'created_at'   => current_time( 'mysql' ),
                ),
                array( '%d', '%s', '%s', '%s', '%s' )
            );
        }
    }

    /**
     * Create demo microsites for performers.
     *
     * @param array $performer_user_ids Performer user IDs.
     * @return int Number of microsites created.
     */
    private static function create_demo_microsites( $performer_user_ids ) {
        global $wpdb;

        $count = 0;
        $templates = array( 'classic', 'modern', 'bold', 'minimal' );
        $colors = array( '#3b82f6', '#ef4444', '#10b981', '#8b5cf6', '#f59e0b', '#ec4899' );

        foreach ( $performer_user_ids as $user_id ) {
            // Get performer record using $wpdb directly.
            $performer = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}pb_performers WHERE user_id = %d",
                    $user_id
                )
            );
            if ( ! $performer ) {
                continue;
            }

            // Get performer name for slug.
            $performer_name = '';
            if ( $performer->profile_id ) {
                $performer_name = get_post_meta( $performer->profile_id, '_pb_stage_name', true );
                if ( empty( $performer_name ) ) {
                    $performer_name = get_the_title( $performer->profile_id );
                }
            }

            if ( empty( $performer_name ) ) {
                $user = get_user_by( 'id', $user_id );
                $performer_name = $user ? $user->display_name : 'performer-' . $performer->id;
            }

            // Create URL-friendly slug.
            $slug = sanitize_title( $performer_name );

            // Make slug unique if needed.
            $base_slug = $slug;
            $counter = 1;
            while ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}pb_microsites WHERE slug = %s", $slug ) ) ) {
                $slug = $base_slug . '-' . $counter;
                $counter++;
            }

            // Create microsite with random template and color.
            $design_settings = array(
                'template'            => $templates[ array_rand( $templates ) ],
                'primary_color'       => $colors[ array_rand( $colors ) ],
                'secondary_color'     => '#1e40af',
                'background_color'    => '#ffffff',
                'text_color'          => '#1e293b',
                'font_family'         => 'Inter',
                'show_reviews'        => true,
                'show_calendar'       => true,
                'show_booking_button' => true,
            );

            $wpdb->insert(
                $wpdb->prefix . 'pb_microsites',
                array(
                    'performer_id'     => $performer->id,
                    'user_id'          => $user_id,
                    'status'           => 'active',
                    'slug'             => $slug,
                    'design_settings'  => wp_json_encode( $design_settings ),
                    'meta_title'       => $performer_name . ' - Book Now',
                    'meta_description' => 'Book ' . $performer_name . ' for your next event. View availability, read reviews, and book directly.',
                    'view_count'       => wp_rand( 50, 500 ),
                    'created_at'       => current_time( 'mysql' ),
                )
            );

            $count++;
        }

        return $count;
    }

    /**
     * Create demo customers.
     *
     * @return array User IDs of created customers.
     */
    private static function create_demo_customers() {
        $user_ids = array();

        foreach ( self::$customers as $index => $customer_data ) {
            $user_id = wp_create_user(
                'demo_customer_' . ( $index + 1 ),
                wp_generate_password(),
                $customer_data['email']
            );

            if ( is_wp_error( $user_id ) ) {
                continue;
            }

            wp_update_user( array(
                'ID'           => $user_id,
                'display_name' => $customer_data['name'],
                'first_name'   => explode( ' ', $customer_data['name'] )[0],
                'role'         => 'pb_customer',
            ) );

            // Add company meta if present.
            if ( ! empty( $customer_data['company'] ) ) {
                update_user_meta( $user_id, 'pb_company', $customer_data['company'] );
            }

            $user_ids[] = $user_id;
        }

        return $user_ids;
    }

    /**
     * Create demo bookings, reviews, and transactions.
     *
     * @param array $performer_user_ids Performer user IDs.
     * @param array $customer_user_ids  Customer user IDs.
     * @return array Counts of created items.
     */
    private static function create_demo_bookings( $performer_user_ids, $customer_user_ids ) {
        global $wpdb;

        $booking_count     = 0;
        $review_count      = 0;
        $transaction_count = 0;

        // Explicit booking configurations to ensure all admin page tabs have data.
        // Each config: status, escrow_status, count, create_review, flag_review.
        $booking_configs = array(
            // PENDING bookings (for Bookings > Pending tab).
            array( 'status' => 'pending', 'escrow' => 'pending', 'count' => 8, 'review' => false, 'flag' => false ),

            // CONFIRMED bookings (for Bookings > Confirmed tab).
            array( 'status' => 'confirmed', 'escrow' => 'deposit_held', 'count' => 10, 'review' => false, 'flag' => false ),

            // IN PROGRESS bookings.
            array( 'status' => 'in_progress', 'escrow' => 'full_held', 'count' => 3, 'review' => false, 'flag' => false ),

            // COMPLETED with RELEASED payout (normal completed).
            array( 'status' => 'completed', 'escrow' => 'released', 'count' => 20, 'review' => true, 'flag' => false ),

            // COMPLETED with PENDING payout (for Payouts admin page!).
            array( 'status' => 'completed', 'escrow' => 'full_held', 'count' => 8, 'review' => true, 'flag' => false ),

            // CANCELLED bookings (for Bookings > Cancelled tab).
            array( 'status' => 'cancelled', 'escrow' => 'refunded', 'count' => 6, 'review' => false, 'flag' => false ),

            // DISPUTED bookings.
            array( 'status' => 'disputed', 'escrow' => 'full_held', 'count' => 3, 'review' => true, 'flag' => true ),

            // Extra completed with flagged reviews (for Reviews > Flagged for Arbitration).
            array( 'status' => 'completed', 'escrow' => 'released', 'count' => 5, 'review' => true, 'flag' => true ),
        );

        $locations = array(
            'Grand Ballroom, Downtown Marriott',
            'Riverside Convention Center',
            'The Garden Pavilion at Sunset Park',
            'Private Residence',
            'Corporate Headquarters - Main Auditorium',
            'Beach Resort & Spa - Ocean Terrace',
            'City Park Amphitheater',
            'Metropolitan Art Museum - East Wing',
            'The Ritz-Carlton Ballroom',
            'Hilton Conference Center',
            'Private Vineyard Estate',
            'Rooftop Event Space - Sky Lounge',
            'Historic Manor House',
            'Country Club Grand Hall',
        );

        $event_titles = array(
            'Annual Company Gala',
            'Wedding Reception',
            'Corporate Holiday Party',
            'Product Launch Event',
            'Charity Fundraiser Dinner',
            'Birthday Celebration',
            'Anniversary Party',
            'Team Building Event',
            'Award Ceremony',
            'Retirement Celebration',
            'Graduation Party',
            'Networking Mixer',
            'Client Appreciation Event',
            'Summer Festival',
            'New Year\'s Eve Party',
        );

        // Flagged review templates (negative reviews for arbitration).
        $flagged_review_templates = array(
            array(
                'rating'      => 1,
                'title'       => 'Completely unprofessional - DO NOT BOOK',
                'content'     => '{name} was a complete disaster. Showed up an hour late with no explanation, was rude to guests, and left early. The "performance" was nothing like what was advertised. Worst experience ever. Demanding full refund.',
                'flag_reason' => 'Performer disputes accuracy of review. Claims customer is exaggerating timeline issues and that they completed full contracted time.',
            ),
            array(
                'rating'      => 1,
                'title'       => 'Scam artist - stay away!',
                'content'     => 'This was supposed to be a "professional" performance but {name} clearly had no idea what they were doing. Equipment kept breaking, sound was terrible, and they blamed us for not having proper setup. Complete waste of money.',
                'flag_reason' => 'Performer claims venue did not have agreed-upon electrical setup causing equipment issues. Has photos as evidence.',
            ),
            array(
                'rating'      => 2,
                'title'       => 'Not worth the money',
                'content'     => '{name} was mediocre at best. Performance was boring, didn\'t engage with the crowd at all, and seemed like they didn\'t want to be there. For what we paid, expected much better. Very disappointed.',
                'flag_reason' => 'Performer disputes characterization. States they were professional throughout and crowd engagement was limited due to venue layout.',
            ),
            array(
                'rating'      => 2,
                'title'       => 'False advertising',
                'content'     => 'The profile said 10 years experience but {name} performed like an amateur. Nothing like the videos on their profile. Either those videos are fake or they sent someone else. Would not recommend.',
                'flag_reason' => 'Performer claims this review contains defamatory statements. All profile content is accurate and verifiable.',
            ),
            array(
                'rating'      => 1,
                'title'       => 'Ruined my daughter\'s birthday',
                'content'     => 'Hired {name} for my daughter\'s 7th birthday party. They were supposed to do magic and balloon animals. Instead, they did inappropriate jokes that scared the kids and made parents uncomfortable. Had to ask them to leave early.',
                'flag_reason' => 'Performer strongly disputes this account. States material was entirely child-appropriate and was asked to cut short due to scheduling conflict on client side.',
            ),
        );

        // Loop through each config and create bookings.
        foreach ( $booking_configs as $config ) {
            for ( $i = 0; $i < $config['count']; $i++ ) {
                $performer_user_id = $performer_user_ids[ array_rand( $performer_user_ids ) ];
                $customer_user_id  = $customer_user_ids[ array_rand( $customer_user_ids ) ];

                $status        = $config['status'];
                $escrow_status = $config['escrow'];
                $create_review = $config['review'];
                $flag_review   = $config['flag'];

                // Get performer data.
                $performer = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}pb_performers WHERE user_id = %d",
                        $performer_user_id
                    )
                );

                if ( ! $performer ) {
                    continue;
                }

                $hours        = wp_rand( 2, 6 );
                $total_amount = $performer->hourly_rate * $hours;
                $deposit_pct  = $performer->deposit_percentage;
                $deposit_amt  = round( $total_amount * ( $deposit_pct / 100 ), 2 );
                $commission   = round( $total_amount * ( 'pro' === $performer->tier ? 0.10 : 0.15 ), 2 );
                $payout       = $total_amount - $commission;

                // Date based on status.
                switch ( $status ) {
                    case 'completed':
                        $event_date = gmdate( 'Y-m-d', strtotime( '-' . wp_rand( 7, 90 ) . ' days' ) );
                        break;
                    case 'in_progress':
                        $event_date = gmdate( 'Y-m-d' );
                        break;
                    case 'confirmed':
                        $event_date = gmdate( 'Y-m-d', strtotime( '+' . wp_rand( 7, 60 ) . ' days' ) );
                        break;
                    case 'pending':
                        $event_date = gmdate( 'Y-m-d', strtotime( '+' . wp_rand( 14, 90 ) . ' days' ) );
                        break;
                    case 'cancelled':
                        $event_date = gmdate( 'Y-m-d', strtotime( wp_rand( 0, 1 ) ? '-' . wp_rand( 7, 60 ) : '+' . wp_rand( 7, 30 ) . ' days' ) );
                        break;
                    case 'disputed':
                        $event_date = gmdate( 'Y-m-d', strtotime( '-' . wp_rand( 3, 30 ) . ' days' ) );
                        break;
                    default:
                        $event_date = gmdate( 'Y-m-d', strtotime( '+' . wp_rand( 14, 60 ) . ' days' ) );
                }

                // Payment status based on escrow.
                $deposit_paid = in_array( $escrow_status, array( 'deposit_held', 'full_held', 'released', 'refunded' ), true );
                $fully_paid   = in_array( $escrow_status, array( 'full_held', 'released' ), true );

                $booking_number = 'PB-' . strtoupper( substr( md5( uniqid() . $i ), 0, 8 ) );
                $created_at     = gmdate( 'Y-m-d H:i:s', strtotime( $event_date . ' -' . wp_rand( 14, 45 ) . ' days' ) );
                $confirmed_at   = $deposit_paid ? gmdate( 'Y-m-d H:i:s', strtotime( $created_at . ' +' . wp_rand( 1, 3 ) . ' days' ) ) : null;
                $completion_dt  = 'completed' === $status ? gmdate( 'Y-m-d H:i:s', strtotime( $event_date . ' +1 day' ) ) : null;

                // Only set payout_date if escrow is released.
                $payout_date = 'released' === $escrow_status
                    ? gmdate( 'Y-m-d H:i:s', strtotime( $event_date . ' +' . wp_rand( 3, 7 ) . ' days' ) )
                    : null;

                $wpdb->insert(
                    $wpdb->prefix . 'pb_bookings',
                    array(
                        'booking_number'      => $booking_number,
                        'performer_id'        => $performer->id,
                        'customer_id'         => $customer_user_id,
                        'event_title'         => $event_titles[ array_rand( $event_titles ) ],
                        'event_date'          => $event_date,
                        'event_start_time'    => sprintf( '%02d:00:00', wp_rand( 14, 20 ) ),
                        'event_end_time'      => sprintf( '%02d:00:00', wp_rand( 21, 23 ) ),
                        'event_location'      => $locations[ array_rand( $locations ) ],
                        'total_amount'        => $total_amount,
                        'deposit_amount'      => $deposit_amt,
                        'remaining_amount'    => $total_amount - $deposit_amt,
                        'deposit_paid'        => $deposit_paid ? 1 : 0,
                        'fully_paid'          => $fully_paid ? 1 : 0,
                        'platform_commission' => $commission,
                        'performer_payout'    => $payout,
                        'booking_status'      => $status,
                        'escrow_status'       => $escrow_status,
                        'performer_confirmed' => $deposit_paid ? 1 : 0,
                        'customer_confirmed_completion' => 'completed' === $status ? 1 : 0,
                        'created_at'          => $created_at,
                        'completion_date'     => $completion_dt,
                        'payout_date'         => $payout_date,
                    ),
                    array(
                        '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s',
                        '%f', '%f', '%f', '%d', '%d', '%f', '%f', '%s',
                        '%s', '%d', '%d', '%s', '%s', '%s',
                    )
                );

                $booking_id = $wpdb->insert_id;
                if ( ! $booking_id ) {
                    continue;
                }
                $booking_count++;

                // Create transactions for paid bookings.
                if ( $deposit_paid ) {
                    // Deposit payment (customer pays).
                    $wpdb->insert(
                        $wpdb->prefix . 'pb_transactions',
                        array(
                            'booking_id'       => $booking_id,
                            'transaction_type' => 'deposit',
                            'amount'           => $deposit_amt,
                            'payer_id'         => $customer_user_id,
                            'payee_id'         => $performer_user_id,
                            'status'           => 'completed',
                            'created_at'       => $created_at,
                        ),
                        array( '%d', '%s', '%f', '%d', '%d', '%s', '%s' )
                    );
                    $transaction_count++;

                    if ( $fully_paid && $total_amount > $deposit_amt ) {
                        // Balance payment (customer pays remainder).
                        $wpdb->insert(
                            $wpdb->prefix . 'pb_transactions',
                            array(
                                'booking_id'       => $booking_id,
                                'transaction_type' => 'balance',
                                'amount'           => $total_amount - $deposit_amt,
                                'payer_id'         => $customer_user_id,
                                'payee_id'         => $performer_user_id,
                                'status'           => 'completed',
                                'created_at'       => gmdate( 'Y-m-d H:i:s', strtotime( $event_date . ' -1 day' ) ),
                            ),
                            array( '%d', '%s', '%f', '%d', '%d', '%s', '%s' )
                        );
                        $transaction_count++;
                    }

                    // Payout transaction only for released escrow.
                    if ( 'released' === $escrow_status && $payout_date ) {
                        $wpdb->insert(
                            $wpdb->prefix . 'pb_transactions',
                            array(
                                'booking_id'       => $booking_id,
                                'transaction_type' => 'payout',
                                'amount'           => $payout,
                                'payer_id'         => null,
                                'payee_id'         => $performer_user_id,
                                'status'           => 'completed',
                                'notes'            => 'Escrow released to performer',
                                'created_at'       => $payout_date,
                            ),
                            array( '%d', '%s', '%f', '%d', '%d', '%s', '%s', '%s' )
                        );
                        $transaction_count++;
                    }

                    if ( 'refunded' === $escrow_status ) {
                        $wpdb->insert(
                            $wpdb->prefix . 'pb_transactions',
                            array(
                                'booking_id'       => $booking_id,
                                'transaction_type' => 'refund',
                                'amount'           => $deposit_amt,
                                'payer_id'         => null,
                                'payee_id'         => $customer_user_id,
                                'status'           => 'completed',
                                'notes'            => 'Booking cancelled - deposit refunded',
                                'created_at'       => gmdate( 'Y-m-d H:i:s', strtotime( $created_at . ' +5 days' ) ),
                            ),
                            array( '%d', '%s', '%f', '%d', '%d', '%s', '%s', '%s' )
                        );
                        $transaction_count++;
                    }
                }

                // Create review if configured.
                if ( $create_review && in_array( $status, array( 'completed', 'disputed' ), true ) ) {
                    $performer_user = get_userdata( $performer_user_id );

                    if ( $flag_review ) {
                        // Use flagged review template.
                        $flagged_template = $flagged_review_templates[ array_rand( $flagged_review_templates ) ];
                        $rating           = $flagged_template['rating'];
                        $title            = $flagged_template['title'];
                        $content          = str_replace( '{name}', $performer_user->display_name, $flagged_template['content'] );
                        $flag_reason      = $flagged_template['flag_reason'];
                        $is_flagged       = 1;
                        $has_response     = false;
                        $response         = null;
                    } else {
                        // Normal review.
                        $rating_weights = array( 5 => 50, 4 => 35, 3 => 15 );
                        $rand           = wp_rand( 1, 100 );
                        $cumulative     = 0;
                        $rating         = 5;
                        foreach ( $rating_weights as $r => $w ) {
                            $cumulative += $w;
                            if ( $rand <= $cumulative ) {
                                $rating = $r;
                                break;
                            }
                        }

                        $templates    = self::$review_templates[ $rating ] ?? self::$review_templates[5];
                        $template     = $templates[ array_rand( $templates ) ];
                        $title        = $template['title'];
                        $content      = str_replace( '{name}', $performer_user->display_name, $template['content'] );
                        $flag_reason  = null;
                        $is_flagged   = 0;
                        $has_response = ( $rating >= 4 && wp_rand( 1, 100 ) <= 60 );
                        $response     = $has_response && ! empty( $template['response'] ) ? $template['response'] : null;
                    }

                    $review_date = gmdate( 'Y-m-d H:i:s', strtotime( $event_date . ' +' . wp_rand( 1, 7 ) . ' days' ) );

                    $wpdb->insert(
                        $wpdb->prefix . 'pb_reviews',
                        array(
                            'booking_id'         => $booking_id,
                            'reviewer_id'        => $customer_user_id,
                            'reviewee_id'        => $performer_user_id,
                            'reviewer_type'      => 'customer',
                            'rating'             => $rating,
                            'title'              => $title,
                            'content'            => $content,
                            'response'           => $response,
                            'response_date'      => $response ? gmdate( 'Y-m-d H:i:s', strtotime( $review_date . ' +' . wp_rand( 1, 3 ) . ' days' ) ) : null,
                            'is_visible'         => 1,
                            'is_flagged'         => $is_flagged,
                            'flag_reason'        => $flag_reason,
                            'flagged_by'         => $is_flagged ? $performer_user_id : null,
                            'flagged_date'       => $is_flagged ? gmdate( 'Y-m-d H:i:s', strtotime( $review_date . ' +2 days' ) ) : null,
                            'arbitration_status' => $is_flagged ? 'pending' : null,
                            'created_at'         => $review_date,
                        ),
                        array( '%d', '%d', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%s', '%s', '%s' )
                    );
                    $review_count++;
                }
            }
        }

        return array(
            'bookings'     => $booking_count,
            'reviews'      => $review_count,
            'transactions' => $transaction_count,
        );
    }

    /**
     * Create demo market events and bids.
     *
     * @param array $performer_user_ids Performer user IDs.
     * @param array $customer_user_ids  Customer user IDs.
     * @return array Counts of created items.
     */
    private static function create_demo_market_events( $performer_user_ids, $customer_user_ids ) {
        global $wpdb;

        $event_count = 0;
        $bid_count   = 0;

        $cities = array(
            array( 'city' => 'New York', 'state' => 'NY' ),
            array( 'city' => 'Los Angeles', 'state' => 'CA' ),
            array( 'city' => 'Chicago', 'state' => 'IL' ),
            array( 'city' => 'Miami', 'state' => 'FL' ),
            array( 'city' => 'Austin', 'state' => 'TX' ),
            array( 'city' => 'San Francisco', 'state' => 'CA' ),
            array( 'city' => 'Atlanta', 'state' => 'GA' ),
            array( 'city' => 'Nashville', 'state' => 'TN' ),
            array( 'city' => 'Las Vegas', 'state' => 'NV' ),
            array( 'city' => 'Boston', 'state' => 'MA' ),
        );

        foreach ( self::$event_templates as $template ) {
            $customer_user_id = $customer_user_ids[ array_rand( $customer_user_ids ) ];

            // Date based on status.
            if ( 'closed' === $template['status'] || 'filled' === $template['status'] ) {
                $event_date = gmdate( 'Y-m-d', strtotime( '-' . wp_rand( 7, 45 ) . ' days' ) );
            } else {
                $event_date = gmdate( 'Y-m-d', strtotime( '+' . wp_rand( 14, 75 ) . ' days' ) );
            }

            $bid_deadline = gmdate( 'Y-m-d H:i:s', strtotime( $event_date . ' -5 days' ) );
            $created_at   = gmdate( 'Y-m-d H:i:s', strtotime( $event_date . ' -' . wp_rand( 20, 45 ) . ' days' ) );
            $location     = $cities[ array_rand( $cities ) ];

            // Get category term.
            $term = get_term_by( 'name', $template['category'], 'pb_performer_category' );

            // Map our status to the Market class constants.
            $status_map = array(
                'open'   => 'open',
                'closed' => 'closed',
                'filled' => 'booked',
            );
            $post_status = $status_map[ $template['status'] ] ?? 'open';

            // Create the WordPress post type (this is what the shortcode queries).
            $post_id = wp_insert_post( array(
                'post_type'    => 'pb_market_event',
                'post_status'  => 'publish',
                'post_title'   => $template['name'],
                'post_content' => $template['description'],
                'post_author'  => $customer_user_id,
                'post_date'    => $created_at,
            ) );

            if ( is_wp_error( $post_id ) || ! $post_id ) {
                continue;
            }

            // Set post meta (this is how the Market::query reads data).
            update_post_meta( $post_id, 'pb_customer_id', $customer_user_id );
            update_post_meta( $post_id, 'pb_event_date', $event_date );
            update_post_meta( $post_id, 'pb_event_time', sprintf( '%02d:00:00', wp_rand( 14, 19 ) ) );
            update_post_meta( $post_id, 'pb_event_duration', $template['duration'] );
            update_post_meta( $post_id, 'pb_venue_city', $location['city'] );
            update_post_meta( $post_id, 'pb_venue_state', $location['state'] );
            update_post_meta( $post_id, 'pb_budget_min', $template['budget_min'] );
            update_post_meta( $post_id, 'pb_budget_max', $template['budget_max'] );
            update_post_meta( $post_id, 'pb_bid_deadline', $bid_deadline );
            update_post_meta( $post_id, 'pb_event_status', $post_status );
            update_post_meta( $post_id, 'pb_total_bids', 0 );

            // Set category taxonomy.
            if ( $term ) {
                wp_set_object_terms( $post_id, $term->term_id, 'pb_performer_category' );
            }

            // Also insert into custom table for any direct table queries.
            $wpdb->insert(
                $wpdb->prefix . 'pb_events',
                array(
                    'customer_id'    => $customer_user_id,
                    'post_id'        => $post_id,
                    'title'          => $template['name'],
                    'description'    => $template['description'],
                    'event_date'     => $event_date,
                    'event_start_time' => sprintf( '%02d:00:00', wp_rand( 14, 19 ) ),
                    'city'           => $location['city'],
                    'state'          => $location['state'],
                    'budget_min'     => $template['budget_min'],
                    'budget_max'     => $template['budget_max'],
                    'bid_deadline'   => $bid_deadline,
                    'status'         => $post_status,
                    'created_at'     => $created_at,
                ),
                array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%s', '%s', '%s' )
            );

            $table_event_id = $wpdb->insert_id;
            $event_count++;

            // Create bids from Pro performers.
            $pro_performers = array();
            foreach ( $performer_user_ids as $uid ) {
                $performer = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}pb_performers WHERE user_id = %d AND tier = 'pro'",
                        $uid
                    )
                );
                if ( $performer ) {
                    $pro_performers[] = array(
                        'user_id'      => $uid,
                        'performer_id' => $performer->id,
                    );
                }
            }

            if ( empty( $pro_performers ) ) {
                continue;
            }

            // Number of bids varies by status.
            $max_bids = 'open' === $template['status'] ? min( 5, count( $pro_performers ) ) : min( 8, count( $pro_performers ) );
            $num_bids = wp_rand( 2, $max_bids );

            shuffle( $pro_performers );
            $bidding_performers = array_slice( $pro_performers, 0, $num_bids );

            $bid_statuses = array( 'pending' );
            if ( 'filled' === $template['status'] ) {
                $bid_statuses = array( 'accepted', 'rejected', 'rejected', 'rejected' );
            } elseif ( 'closed' === $template['status'] ) {
                $bid_statuses = array( 'expired', 'expired', 'withdrawn' );
            }

            foreach ( $bidding_performers as $index => $perf_data ) {
                $bid_amount = wp_rand( $template['budget_min'], $template['budget_max'] );

                $bid_status = 'pending';
                if ( 'open' !== $template['status'] ) {
                    $bid_status = $bid_statuses[ $index % count( $bid_statuses ) ];
                }

                $bid_messages = array(
                    "I'd love to perform at your event! With my experience and style, I believe I can make it truly memorable. Looking forward to discussing the details with you.",
                    "This sounds like a perfect fit for my act! I specialize in exactly this type of event. Let me know if you'd like to schedule a call to discuss.",
                    "Your event sounds fantastic! I'm available on that date and would be honored to be part of it. My rate is competitive and includes all equipment.",
                    "Hi there! I saw your posting and I think we'd be a great match. I have extensive experience with similar events. Happy to provide references!",
                    "Excited about this opportunity! I've performed at many events like this and consistently receive excellent feedback. Would love to chat more.",
                );

                $wpdb->insert(
                    $wpdb->prefix . 'pb_bids',
                    array(
                        'event_id'     => $post_id,
                        'performer_id' => $perf_data['performer_id'],
                        'bid_amount'   => $bid_amount,
                        'message'      => $bid_messages[ array_rand( $bid_messages ) ],
                        'status'       => $bid_status,
                        'created_at'   => gmdate( 'Y-m-d H:i:s', strtotime( $created_at . ' +' . wp_rand( 1, 10 ) . ' days' ) ),
                    ),
                    array( '%d', '%d', '%f', '%s', '%s', '%s' )
                );

                $bid_count++;
            }

            // Update total bids count on the post.
            update_post_meta( $post_id, 'pb_total_bids', count( $bidding_performers ) );
        }

        return array(
            'events' => $event_count,
            'bids'   => $bid_count,
        );
    }
}
