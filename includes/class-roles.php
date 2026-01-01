<?php
/**
 * User roles and capabilities management.
 *
 * @package Peanut_Booker
 * @since   1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * User roles and capabilities management.
 */
class Peanut_Booker_Roles {

    /**
     * Performer capabilities.
     *
     * @var array
     */
    private static $performer_caps = array(
        'pb_performer',
        'pb_edit_own_profile',
        'pb_view_bookings',
        'pb_manage_availability',
        'pb_respond_reviews',
        'pb_view_market',
    );

    /**
     * Pro performer additional capabilities.
     *
     * @var array
     */
    private static $pro_performer_caps = array(
        'pb_bid_on_events',
        'pb_extended_photos',
        'pb_extended_videos',
        'pb_advanced_analytics',
    );

    /**
     * Featured performer additional capabilities (includes all Pro caps).
     *
     * @var array
     */
    private static $featured_performer_caps = array(
        'pb_unlimited_photos',
        'pb_unlimited_videos',
        'pb_featured_badge',
        'pb_priority_support',
        'pb_custom_domain',
        'pb_homepage_spotlight',
    );

    /**
     * Customer capabilities.
     *
     * @var array
     */
    private static $customer_caps = array(
        'pb_customer',
        'pb_book_performers',
        'pb_create_events',
        'pb_leave_reviews',
        'pb_view_market',
    );

    /**
     * Admin capabilities.
     *
     * @var array
     */
    private static $admin_caps = array(
        'pb_manage_performers',
        'pb_manage_bookings',
        'pb_manage_reviews',
        'pb_manage_market',
        'pb_manage_settings',
        'pb_arbitrate_reviews',
        'pb_manage_payouts',
    );

    /**
     * Check if roles need updating and update if necessary.
     */
    public function maybe_update_capabilities() {
        $version = get_option( 'peanut_booker_roles_version', '0' );

        if ( version_compare( $version, PEANUT_BOOKER_VERSION, '<' ) ) {
            $this->update_roles();
            update_option( 'peanut_booker_roles_version', PEANUT_BOOKER_VERSION );
        }
    }

    /**
     * Update all roles with current capabilities.
     */
    private function update_roles() {
        // Update performer role.
        $performer = get_role( 'pb_performer' );
        if ( $performer ) {
            foreach ( self::$performer_caps as $cap ) {
                $performer->add_cap( $cap );
            }
        }

        // Update customer role.
        $customer = get_role( 'pb_customer' );
        if ( $customer ) {
            foreach ( self::$customer_caps as $cap ) {
                $customer->add_cap( $cap );
            }
        }

        // Update admin role.
        $admin = get_role( 'administrator' );
        if ( $admin ) {
            foreach ( self::$admin_caps as $cap ) {
                $admin->add_cap( $cap );
            }
        }
    }

    /**
     * Check if user is a performer.
     *
     * @param int $user_id Optional user ID.
     * @return bool
     */
    public static function is_performer( $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return false;
        }

        return in_array( 'pb_performer', (array) $user->roles, true );
    }

    /**
     * Check if user is a customer.
     *
     * @param int $user_id Optional user ID.
     * @return bool
     */
    public static function is_customer( $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return false;
        }

        return in_array( 'pb_customer', (array) $user->roles, true );
    }

    /**
     * Get performer's current tier.
     *
     * @param int $user_id Optional user ID.
     * @return string Tier (free/pro/featured).
     */
    public static function get_performer_tier( $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        if ( ! self::is_performer( $user_id ) ) {
            return 'free';
        }

        $performer = Peanut_Booker_Database::get_row( 'performers', array( 'user_id' => $user_id ) );

        return $performer ? ( $performer->tier ?? 'free' ) : 'free';
    }

    /**
     * Check if performer is Pro tier or higher.
     *
     * @param int $user_id Optional user ID.
     * @return bool
     */
    public static function is_pro_performer( $user_id = null ) {
        $tier = self::get_performer_tier( $user_id );
        return in_array( $tier, array( 'pro', 'featured' ), true );
    }

    /**
     * Check if performer is Featured tier.
     *
     * @param int $user_id Optional user ID.
     * @return bool
     */
    public static function is_featured_performer( $user_id = null ) {
        return 'featured' === self::get_performer_tier( $user_id );
    }

    /**
     * Grant tier capabilities to a performer.
     *
     * @param int    $user_id User ID.
     * @param string $tier    Tier to grant (pro/featured).
     */
    public static function grant_tier_capabilities( $user_id, $tier = 'pro' ) {
        $user = get_userdata( $user_id );
        if ( ! $user || ! self::is_performer( $user_id ) ) {
            return;
        }

        // Always grant Pro capabilities for Pro and Featured.
        foreach ( self::$pro_performer_caps as $cap ) {
            $user->add_cap( $cap );
        }

        // Grant Featured capabilities if Featured tier.
        if ( 'featured' === $tier ) {
            foreach ( self::$featured_performer_caps as $cap ) {
                $user->add_cap( $cap );
            }
        }

        // Update performer tier in database.
        Peanut_Booker_Database::update(
            'performers',
            array( 'tier' => $tier ),
            array( 'user_id' => $user_id )
        );
    }

    /**
     * Grant Pro capabilities to a performer.
     *
     * @deprecated Use grant_tier_capabilities() instead.
     * @param int $user_id User ID.
     */
    public static function grant_pro_capabilities( $user_id ) {
        self::grant_tier_capabilities( $user_id, 'pro' );
    }

    /**
     * Revoke all tier capabilities from a performer.
     *
     * @param int $user_id User ID.
     */
    public static function revoke_tier_capabilities( $user_id ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return;
        }

        // Remove Pro capabilities.
        foreach ( self::$pro_performer_caps as $cap ) {
            $user->remove_cap( $cap );
        }

        // Remove Featured capabilities.
        foreach ( self::$featured_performer_caps as $cap ) {
            $user->remove_cap( $cap );
        }

        // Update performer tier in database.
        Peanut_Booker_Database::update(
            'performers',
            array( 'tier' => 'free' ),
            array( 'user_id' => $user_id )
        );
    }

    /**
     * Revoke Pro capabilities from a performer.
     *
     * @deprecated Use revoke_tier_capabilities() instead.
     * @param int $user_id User ID.
     */
    public static function revoke_pro_capabilities( $user_id ) {
        self::revoke_tier_capabilities( $user_id );
    }

    /**
     * Check if user can bid on events.
     *
     * @param int $user_id Optional user ID.
     * @return bool
     */
    public static function can_bid_on_events( $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        return self::is_pro_performer( $user_id );
    }

    /**
     * Check if user can upload unlimited photos.
     *
     * @param int $user_id Optional user ID.
     * @return bool
     */
    public static function can_unlimited_photos( $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        return self::is_pro_performer( $user_id );
    }

    /**
     * Get photo limit for user.
     *
     * Free tier: 1 photo
     * Pro tier: 10 photos
     * Featured tier: Unlimited (-1)
     *
     * @param int $user_id Optional user ID.
     * @return int Number of photos allowed (-1 for unlimited).
     */
    public static function get_photo_limit( $user_id = null ) {
        $tier = self::get_performer_tier( $user_id );

        switch ( $tier ) {
            case 'featured':
                return -1; // Unlimited.
            case 'pro':
                return 10;
            default:
                return 1;
        }
    }

    /**
     * Get video limit for user.
     *
     * Free tier: 0 videos
     * Pro tier: 5 videos
     * Featured tier: Unlimited (-1)
     *
     * @param int $user_id Optional user ID.
     * @return int Number of videos allowed (-1 for unlimited).
     */
    public static function get_video_limit( $user_id = null ) {
        $tier = self::get_performer_tier( $user_id );

        switch ( $tier ) {
            case 'featured':
                return -1; // Unlimited.
            case 'pro':
                return 5;
            default:
                return 0;
        }
    }

    /**
     * Check if user has unlimited photos.
     *
     * @param int $user_id Optional user ID.
     * @return bool
     */
    public static function has_unlimited_photos( $user_id = null ) {
        return self::get_photo_limit( $user_id ) === -1;
    }

    /**
     * Check if user has unlimited videos.
     *
     * @param int $user_id Optional user ID.
     * @return bool
     */
    public static function has_unlimited_videos( $user_id = null ) {
        return self::get_video_limit( $user_id ) === -1;
    }

    /**
     * Get commission rate for performer tier.
     *
     * Free tier: 20%
     * Pro tier: 12%
     * Featured tier: 8%
     *
     * @param string $tier Performer tier (free/pro/featured).
     * @return float Commission percentage.
     */
    public static function get_commission_rate( $tier = 'free' ) {
        $options = get_option( 'peanut_booker_settings', array() );

        switch ( $tier ) {
            case 'featured':
                return isset( $options['commission_featured_tier'] ) ? floatval( $options['commission_featured_tier'] ) : 8;
            case 'pro':
                return isset( $options['commission_pro_tier'] ) ? floatval( $options['commission_pro_tier'] ) : 12;
            default:
                return isset( $options['commission_free_tier'] ) ? floatval( $options['commission_free_tier'] ) : 20;
        }
    }

    /**
     * Get commission rate for a specific user.
     *
     * @param int $user_id Optional user ID.
     * @return float Commission percentage.
     */
    public static function get_user_commission_rate( $user_id = null ) {
        return self::get_commission_rate( self::get_performer_tier( $user_id ) );
    }

    /**
     * Remove all custom roles (for uninstall).
     */
    public static function remove_roles() {
        remove_role( 'pb_performer' );
        remove_role( 'pb_customer' );

        // Remove admin caps.
        $admin = get_role( 'administrator' );
        if ( $admin ) {
            foreach ( self::$admin_caps as $cap ) {
                $admin->remove_cap( $cap );
            }
        }
    }
}
