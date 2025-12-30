<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Peanut_Booker
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/**
 * Clean up plugin data on uninstall.
 *
 * Only runs when the user explicitly deletes the plugin.
 * Deactivation does NOT trigger this.
 */

global $wpdb;

// Option to preserve data - check if user wants to keep data.
$preserve_data = get_option( 'peanut_booker_preserve_data_on_uninstall', false );

if ( $preserve_data ) {
    return;
}

// Remove custom database tables.
$tables = array(
    $wpdb->prefix . 'pb_performers',
    $wpdb->prefix . 'pb_bookings',
    $wpdb->prefix . 'pb_reviews',
    $wpdb->prefix . 'pb_events',
    $wpdb->prefix . 'pb_bids',
    $wpdb->prefix . 'pb_availability',
    $wpdb->prefix . 'pb_transactions',
    $wpdb->prefix . 'pb_subscriptions',
    $wpdb->prefix . 'pb_sponsored_slots',
);

foreach ( $tables as $table ) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
    $wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $table ) );
}

// Remove custom post types and their posts.
$post_types = array( 'pb_performer', 'pb_market_event' );

foreach ( $post_types as $post_type ) {
    $posts = get_posts( array(
        'post_type'      => $post_type,
        'posts_per_page' => -1,
        'post_status'    => 'any',
        'fields'         => 'ids',
    ) );

    foreach ( $posts as $post_id ) {
        wp_delete_post( $post_id, true );
    }
}

// Remove custom taxonomies terms.
$taxonomies = array( 'pb_performer_category', 'pb_service_area' );

foreach ( $taxonomies as $taxonomy ) {
    $terms = get_terms( array(
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
        'fields'     => 'ids',
    ) );

    if ( ! is_wp_error( $terms ) ) {
        foreach ( $terms as $term_id ) {
            wp_delete_term( $term_id, $taxonomy );
        }
    }
}

// Remove options.
$options = array(
    'peanut_booker_settings',
    'peanut_booker_db_version',
    'peanut_booker_preserve_data_on_uninstall',
    'pb_performer_directory_page',
    'pb_market_page',
    'pb_dashboard_page',
);

foreach ( $options as $option ) {
    delete_option( $option );
}

// Remove user meta.
$user_meta_keys = array(
    'pb_performer_id',
    'pb_customer_id',
    'pb_tier',
    'pb_subscription_status',
);

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->usermeta} WHERE meta_key IN (%s, %s, %s, %s)",
        ...$user_meta_keys
    )
);

// Remove custom user roles.
remove_role( 'pb_performer' );
remove_role( 'pb_customer' );

// Remove capabilities from admin.
$admin_role = get_role( 'administrator' );
if ( $admin_role ) {
    $capabilities = array(
        'pb_manage_performers',
        'pb_manage_bookings',
        'pb_manage_reviews',
        'pb_manage_market',
        'pb_manage_payouts',
        'pb_manage_settings',
        'pb_view_analytics',
    );

    foreach ( $capabilities as $cap ) {
        $admin_role->remove_cap( $cap );
    }
}

// Clear any scheduled cron events.
$cron_hooks = array(
    'pb_process_escrow_release',
    'pb_send_reminder_emails',
    'pb_close_expired_events',
    'pb_cleanup_old_data',
);

foreach ( $cron_hooks as $hook ) {
    $timestamp = wp_next_scheduled( $hook );
    if ( $timestamp ) {
        wp_unschedule_event( $timestamp, $hook );
    }
}

// Clear transients.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
    "DELETE FROM {$wpdb->options}
    WHERE option_name LIKE '_transient_pb_%'
    OR option_name LIKE '_transient_timeout_pb_%'"
);

// Flush rewrite rules on next load.
delete_option( 'rewrite_rules' );
