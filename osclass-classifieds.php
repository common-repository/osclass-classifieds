<?php
/**
 * Plugin Name:       Osclass Classifieds
 * Plugin URI:        https://osclass.org/wordpress
 * Description:       Osclass classifieds software integration for wordpress.
 * Version:           1.1.0
 * Author:            Osclass Team
 * Author URI:        httpa://osclass.org/
 * Text Domain:       osclass-classifieds
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
define( 'OSCLASS_OC_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
define( 'OSCLASS_OC_SECRET', 'secret');
define( 'OSCLASS_OC_ALGORITHM', 'HS512');

define( 'OSCLASS_OC_PLUGIN_NAME', 'osclass-classifieds');
define( 'OSCLASS_OC_PLUGIN_VERSION', '1.0.0');

define( 'OSCLASS_OC_PLUGIN_WP_URL', 'https://wordpress.org/#');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/osclass-classifieds-activator.php
 */
function osclass_oc_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/osclass-classifieds-activator.php';
	Osclass_Classifieds_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/osclass-classifieds-deactivator.php
 */
function osclass_oc_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/osclass-classifieds-deactivator.php';
	Osclass_Classifieds_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'osclass_oc_activate' );
register_deactivation_hook( __FILE__, 'osclass_oc_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/osclass-classifieds-shortcodes.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/osclass-classifieds-hapi.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/osclass-classifieds-hform.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/osclass-classifieds-hoptions.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/osclass-classifieds-hemails.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/osclass-classifieds-hroutes.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/osclass-classifieds-helpers.php';
require plugin_dir_path( __FILE__ ) . 'includes/osclass-classifieds.php';
require 'vendor/autoload.php';

function myStartSession() {
    if(!session_id()) {
        session_start();
    }
}

function myEndSession() {
	unset($_SESSION['isLogged']);
	unset($_SESSION['jwttoken']);
	unset($_SESSION['userId']);
	unset($_SESSION['userEmail']);
	// error_log('INFO::logged out');
}

function osclass_oc_clear_plugin_settings() {
	//Get entire array
	$my_options = get_option('osclass_settings');

	//Alter the options array appropriately
	$my_options['osclass_api_url'] = '';
	$my_options['osclass_api_admin_username'] = '';
	$my_options['osclass_api_admin_password'] = '';

	//Update entire array
	update_option('osclass_settings', $my_options);
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function osclass_oc_run_osclass_classifieds() {
	//allow redirection, even if my theme starts to send output to the browser
	add_action('init', function () {
			ob_start();
	});
	add_action('init', 'myStartSession', 1);
	add_action('osclass_oc_logout', 'myEndSession');
	add_action('osclass_oc_login', 'myStartSession');

	$plugin = new Osclass_Classifieds();
	$plugin->run();

}
osclass_oc_run_osclass_classifieds();


