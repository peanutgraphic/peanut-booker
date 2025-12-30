<?php
/**
 * Define the internationalization functionality.
 *
 * @package Peanut_Booker
 * @since   1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Define the internationalization functionality.
 */
class Peanut_Booker_i18n {

    /**
     * Load the plugin text domain for translation.
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'peanut-booker',
            false,
            dirname( PEANUT_BOOKER_BASENAME ) . '/languages/'
        );
    }
}
