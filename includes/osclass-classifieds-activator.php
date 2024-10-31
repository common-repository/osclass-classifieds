<?php

/**
 * Fired during plugin activation
 *
 * @link       https://osclass.org
 * @since      1.0.0
 *
 * @package    Osclass_Classifieds
 * @subpackage osclass-classifieds/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Osclass_Classifieds
 * @subpackage osclass-classifieds/includes
 * @author     Osclass Team <wordpress@osclass.org>
 */
class Osclass_Classifieds_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// add osclass pages with shorcode inside
		// add home.php, search.php, login.php, register.php ...
		//
		// Create post object
		$home = array(
		'post_name'    => 'osc-classifieds',
		'post_title'    => 'Osclass classifieds',
		'post_content'  => '[OSCLASS_MAIN]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		);

		if(!self::_checkExist($home) ) {
			// Insert the post into the database
			$pageId = wp_insert_post( $home, '' );
		} else {
			$a = array(
				'name'        => $the_slug,
				'post_type'   => 'page',
				'post_status' => 'publish'
			);
			$my_posts = get_posts($a);
		}

		$searchfilters = array(
		'post_name'     => 'osc-advanced-search',
		'post_title'    => 'Search',
		'post_content'  => '[OSCLASS_SEARCH_FILTERS]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		'post_parent'   => $pageId
		);
		$useractivate = array(
		'post_name'    => 'osc-user-activate',
		'post_title'    => 'User activate',
		'post_content'  => '[OSCLASS_USER_ACTIVATE]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		'post_parent'   => $pageId
		);
		$itemactivate = array(
		'post_name'    => 'osc-item-activate',
		'post_title'    => 'Item activate',
		'post_content'  => '[OSCLASS_ITEM_ACTIVATE]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		'post_parent'   => $pageId
		);
		$categories = array(
		'post_name'     => 'osc-categories',
		'post_title'    => 'Categories',
		'post_content'  => '[OSCLASS_CATEGORIES]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		'post_parent'   => $pageId
		);
		$itemcontact = array(
		'post_name'     => 'osc-item-contact',
		'post_title'    => 'Contact seller',
		'post_content'  => '[OSCLASS_ITEM_CONTACT]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		'post_parent'   => $pageId
		);
		$userchangeemail = array(
		'post_name'     => 'osc-user-change-email',
		'post_title'    => 'Change email',
		'post_content'  => '[OSCLASS_USER_CHANGE_EMAIL]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		'post_parent'   => $pageId
		);
		$userchangepassword = array(
		'post_name'     => 'osc-user-change-password',
		'post_title'    => 'Change password',
		'post_content'  => '[OSCLASS_USER_CHANGE_PASSWORD]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		'post_parent'   => $pageId
		);
		$item = array(
		'post_name'     => 'osc-item',
		'post_title'    => 'Item',
		'post_content'  => '[OSCLASS_ITEM]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		'post_parent'   => $pageId
		);
		$itemdelete = array(
		'post_name'     => 'osc-item-delete',
		'post_title'    => 'Delete Item',
		'post_content'  => '[OSCLASS_ITEM_DELETE]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		'post_parent'   => $pageId
		);
		$userdelete = array(
		'post_name'     => 'osc-user-delete',
		'post_title'    => 'Delete User',
		'post_content'  => '[OSCLASS_USER_DELETE]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		'post_parent'   => $pageId
		);
		$item_form = array(
		'post_name'    => 'osc-item-form',
		'post_title'    => 'Item',
		'post_content'  => '[OSCLASS_ITEM_FORM]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		'post_parent'   => $pageId
		);
		$item_form_edit = array(
		'post_name'    => 'osc-item-form-edit',
		'post_title'    => 'Item',
		'post_content'  => '[OSCLASS_ITEM_FORM_EDIT]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		'post_parent'   => $pageId
		);
		$search = array(
		'post_name'    => 'osc-search',
		'post_title'    => 'Search',
		'post_content'  => '[OSCLASS_SEARCH]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		'post_parent'   => $pageId
		);
		$login = array(
		'post_name'     => 'osc-login',
		'post_title'    => 'Login',
		'post_content'  => '[OSCLASS_LOGIN]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		'post_parent'   => $pageId
		);
		$logout = array(
		'post_name'     => 'osc-logout',
		'post_title'    => 'Logout',
		'post_content'  => '[OSCLASS_LOGOUT]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		'post_parent'   => $pageId
		);
		$register = array(
		'post_name'     => 'osc-register',
		'post_title'    => 'Create User',
		'post_content'  => '[OSCLASS_REGISTER]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		'post_parent'   => $pageId
		);
		$recover = array(
		'post_name'     => 'osc-recover',
		'post_title'    => 'Recover',
		'post_content'  => '[OSCLASS_RECOVER]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		'post_parent'   => $pageId
		);
		$forgot = array(
		'post_name'     => 'osc-forgot',
		'post_title'    => 'Forgot',
		'post_content'  => '[OSCLASS_FORGOT]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		'post_parent'   => $pageId
		);
		$changeemail = array(
		'post_name'     => 'osc-change-user-email-confirm',
		'post_title'    => 'Email confirm',
		'post_content'  => '[OSCLASS_CHANGE_EMAIL_CONFIRM]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		'post_parent'   => $pageId
		);
		$myaccount = array(
		'post_name'     => 'osc-my-account',
		'post_title'    => 'My account',
		'post_content'  => '[OSCLASS_MY_ACCOUNT]',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'page',
		'post_parent'   => $pageId
		);

		if(!self::_checkExist($item) ) {				wp_insert_post( $item, '' ); }
		if(!self::_checkExist($itemdelete) ) {			wp_insert_post( $itemdelete, '' ); }
		if(!self::_checkExist($item_form) ) {			wp_insert_post( $item_form, '' ); }
		if(!self::_checkExist($item_form_edit) ) {		wp_insert_post( $item_form_edit, '' ); }
		if(!self::_checkExist($search) ) {				wp_insert_post( $search, '' ); }
		if(!self::_checkExist($login) ) {				wp_insert_post( $login, '' ); }
		if(!self::_checkExist($logout) ) {				wp_insert_post( $logout, '' ); }
		if(!self::_checkExist($register) ) {			wp_insert_post( $register, '' ); }
		if(!self::_checkExist($recover) ) {				wp_insert_post( $recover, '' ); }
		if(!self::_checkExist($forgot) ) {				wp_insert_post( $forgot, '' ); }
		if(!self::_checkExist($userdelete) ) {			wp_insert_post( $userdelete, '' ); }
		if(!self::_checkExist($changeemail) ) {			wp_insert_post( $changeemail, '' ); }
		if(!self::_checkExist($myaccount) ) {			wp_insert_post( $myaccount, '' ); }
		if(!self::_checkExist($itemcontact) ) {			wp_insert_post( $itemcontact, '' ); }
		if(!self::_checkExist($userchangeemail) ) {		wp_insert_post( $userchangeemail, '' ); }
		if(!self::_checkExist($userchangepassword) ) {	wp_insert_post( $userchangepassword, '' ); }
		if(!self::_checkExist($categories) ) {			wp_insert_post( $categories, '' ); }
		if(!self::_checkExist($useractivate) ) {		wp_insert_post( $useractivate, '' ); }
		if(!self::_checkExist($searchfilters) ) {		wp_insert_post( $searchfilters, '' ); }
		if(!self::_checkExist($itemactivate) ) {		wp_insert_post( $itemactivate, '' ); }
	}

	public static function _checkExist($post) {
		global $wpdb;
	
		$query = $wpdb->prepare(
			'SELECT ID FROM ' . $wpdb->posts . '
			WHERE post_name = %s
			AND post_type = \'page\'',
			$post['post_name']
		);
		$wpdb->query( $query );
	
		if ( $wpdb->num_rows ) {
			return true;
		}
		return false;
	}
}
