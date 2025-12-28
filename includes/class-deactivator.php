<?php
/**
 * Fired during plugin deactivation.
 *
 * @package Peanut_Booker
 * @since   1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Fired during plugin deactivation.
 */
class Peanut_Booker_Deactivator {

    /**
     * Deactivate the plugin.
     *
     * Does not remove data - that's handled in uninstall.php.
     */
    public static function deactivate() {
        // Clear scheduled events.
        wp_clear_scheduled_hook( 'peanut_booker_daily_tasks' );
        wp_clear_scheduled_hook( 'peanut_booker_hourly_tasks' );
        wp_clear_scheduled_hook( 'peanut_booker_check_escrow_releases' );
        wp_clear_scheduled_hook( 'peanut_booker_check_bid_deadlines' );
        wp_clear_scheduled_hook( 'peanut_booker_send_reminders' );

        // Flush rewrite rules.
        flush_rewrite_rules();
    }
}
