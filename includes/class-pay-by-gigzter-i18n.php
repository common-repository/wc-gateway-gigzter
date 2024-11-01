<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       gigzter.com
 * @since      1.0.0
 *
 * @package    Pay_By_Gigzter
 * @subpackage Pay_By_Gigzter/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Pay_By_Gigzter
 * @subpackage Pay_By_Gigzter/includes
 * @author     Giga Technologies Inc <info@gigzter.com>
 */
class Pay_By_Gigzter_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'pay-by-gigzter',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
