<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://osclass.org
 * @since      1.0.0
 *
 * @package    Osclass_Classifieds
 * @subpackage osclass-classifieds/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Osclass_Classifieds
 * @subpackage osclass-classifieds/includes
 * @author     Osclass Team <wordpress@osclass.org>
 */
class Osclass_Classifieds_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'osclass-classifieds',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
