<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://osclass.org
 * @since      1.0.0
 *
 * @package    Osclass_Classifieds
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

osclass_oc_remove_page('osc-classifieds');
osclass_oc_remove_page('osc-login');
osclass_oc_remove_page('osc-logout');
osclass_oc_remove_page('osc-search');
osclass_oc_remove_page('osc-item');
osclass_oc_remove_page('osc-item-delete');
osclass_oc_remove_page('osc-item-form');
osclass_oc_remove_page('osc-item-form-edit');
osclass_oc_remove_page('osc-register');
osclass_oc_remove_page('osc-recover');
osclass_oc_remove_page('osc-forgot');
osclass_oc_remove_page('osc-user-delete');
osclass_oc_remove_page('osc-change-user-email-confirm');
osclass_oc_remove_page('osc-my-account');
osclass_oc_remove_page('osc-item-contact');
osclass_oc_remove_page('osc-user-change-email');
osclass_oc_remove_page('osc-user-change-password');
osclass_oc_remove_page('osc-categories');
osclass_oc_remove_page('osc-user-activate');
osclass_oc_remove_page('osc-advanced-search');
osclass_oc_remove_page('osc-item-activate');

function  osclass_oc_remove_page($the_slug) {
	$a = array(
	'name'        => $the_slug,
	'post_type'   => 'page',
	'post_status' => 'publish'
	);
	$my_posts = get_posts($a);

	if( $my_posts ) {
		$return = wp_delete_post($my_posts[0]->ID);
		if ( $return === false ) {
			return false;
		}
	} else {
		return false;
	}
	return true;
}

global $wpdb;
// delete all options with name starting with 'osclass'
$results = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE (option_name LIKE 'osclass%') ");
foreach($results as $option) {
    delete_option($option->option_name);
}