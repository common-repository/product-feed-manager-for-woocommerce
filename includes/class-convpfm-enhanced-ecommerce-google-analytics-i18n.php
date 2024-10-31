<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       tatvic.com
 * @since      1.0.0
 *
 * @package    Convpfm_Enhanced_Ecommerce_Google_Analytics
 * @subpackage Convpfm_Enhanced_Ecommerce_Google_Analytics/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Convpfm_Enhanced_Ecommerce_Google_Analytics
 * @subpackage Convpfm_Enhanced_Ecommerce_Google_Analytics/includes
 * @author     Tatvic
 */
class Convpfm_Enhanced_Ecommerce_Google_Analytics_i18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'product-feed-manager-for-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
