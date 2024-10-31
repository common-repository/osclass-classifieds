<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Osclass_Classifieds
 * @subpackage osclass-classifieds/public
 * @author     Osclass Team <wordpress@osclass.org>
 */
class Osclass_Classifieds_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/osclass-classifieds-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name.'-font-awesome', plugin_dir_url( __FILE__ ) . 'css/font-awesome.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/osclass-classifieds-public.js', array( 'jquery' ), $this->version, false );
	}

	public function custom_query_vars_filter($vars) {
		$vars[] = 'page';
		$vars[] .= 'paged';
		$vars[] .= 'catId';
		$vars[] .= 'offset';
		$vars[] .= 'itemsPerPage';
		$vars[] .= 'userId';
		$vars[] .= 'user_id';
		$vars[] .= 'code';
		$vars[] .= 'item_id';
		$vars[] .= 'action';
		$vars[] .= 'secret';
		$vars[] .= 'order';
		$vars[] .= 'order_type';
		$vars[] .= 'pattern';
		$vars[] .= 'countryId';
		$vars[] .= 'regionId';
		$vars[] .= 'cityId';
		$vars[] .= 'min_price';
		$vars[] .= 'max_price';
		return $vars;
	}

	public function shortcodes_init() {
		// [OSCLASS_MAIN]
		add_shortcode( 'OSCLASS_MAIN', array($this,'home_func') );
		add_shortcode( 'OSCLASS_SEARCH', array($this,'search_func') );
		add_shortcode( 'OSCLASS_SEARCH_FILTERS', array($this,'search_filters_func') );

		add_shortcode( 'OSCLASS_LOGIN', array($this,'login_func') );
		add_shortcode( 'OSCLASS_LOGOUT', array($this,'logout_func') );
		add_shortcode( 'OSCLASS_REGISTER', array($this,'register_func') );
		add_shortcode( 'OSCLASS_USER_DELETE', array($this,'user_delete_func') );

		add_shortcode( 'OSCLASS_ITEM', array($this,'item_func') );
		add_shortcode( 'OSCLASS_ITEM_DELETE', array($this,'item_delete_func') );
		add_shortcode( 'OSCLASS_ITEM_FORM', array($this,'item_form_func') );
		add_shortcode( 'OSCLASS_ITEM_FORM_EDIT', array($this,'item_form_edit_func') );
		add_shortcode( 'OSCLASS_ITEM_ACTIVATE', array($this,'item_activate_func') );

		add_shortcode( 'OSCLASS_RECOVER', array($this,'recover_form_func') );
		add_shortcode( 'OSCLASS_FORGOT', array($this,'forgot_form_func') );
		add_shortcode( 'OSCLASS_CHANGE_EMAIL_CONFIRM', array($this,'change_email_confirm_form_func') );

		add_shortcode( 'OSCLASS_MY_ACCOUNT', array($this,'my_account_func') );
		add_shortcode( 'OSCLASS_ITEM_CONTACT', array($this,'item_contact_func') );

		add_shortcode( 'OSCLASS_USER_CHANGE_EMAIL', array($this,'user_change_email_func') );
		add_shortcode( 'OSCLASS_USER_CHANGE_PASSWORD', array($this,'user_change_password_func') );
		add_shortcode( 'OSCLASS_USER_ACTIVATE', array($this,'user_activate_func') );
		

		add_shortcode( 'OSCLASS_CATEGORIES', array($this,'categories_func') );
	}

	public function checkToken() {
		$result = osclass_oc_check_token();
	}

	public function checkSetup() {
		$error = false;
		if(osclass_oc_api_endpoint()=='') {
			$error = true;
		} else {
			$endpoint = osclass_oc_api_endpoint();
			$client = new GuzzleHttp\Client();
			$res = $client->request('GET', $endpoint);
			$site_exist = json_decode($res->getBody(), true);
			if(is_null($site_exist)) {
				$error = true;
			}
		}

		if($error) {
			ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/error.php');
			$content = ob_get_contents();
			ob_end_clean();

			return $content;
		}
		return '';
	}

	function only_logged_users() {
		if(!osclass_oc_is_web_user_logged_in()) {
			ob_start();
				include(OSCLASS_OC_DIR . '/public/partials/only_logged_users.php');
				$content = ob_get_contents();
			ob_end_clean();

			return $content;
		}
		return 0;
	}

	/*
	 *   PRIVATE  - ONLY LOGGED USERS
	 */
	function user_delete_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$only_users = $this->only_logged_users();
		if($only_users!==0) { return $only_users; }

		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/user-delete.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	function item_delete_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$only_users = $this->only_logged_users();
		if($only_users!==0) { return $only_users; }

		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/item-delete.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	function user_change_email_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$only_users = $this->only_logged_users();
		if($only_users!==0) { return $only_users; }

		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/user_change_email.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	function user_change_password_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$only_users = $this->only_logged_users();
		if($only_users!==0) { return $only_users; }

		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/user_change_password.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	function my_account_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$only_users = $this->only_logged_users();
		if($only_users!==0) { return $only_users; }

		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/my_account.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	// right now  NEW ITEM if users are logged in
	function item_form_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$only_users = $this->only_logged_users();
		if($only_users!==0) { 
			osclass_oc_set_fm_error(__('Only registered users can add new listings', 'osclass-classifieds'));
			wp_redirect( osclass_oc_register_url() );
			exit;
		}

		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/item_form.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	// right now  EDIT ITEM if users are logged in
	function item_form_edit_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$only_users = $this->only_logged_users();
		if($only_users!==0) { return $only_users; }

		$args = shortcode_atts( array(
			'action' => 'edit',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/item_form.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
	// ---------------------------------------------------------------------------

	function change_email_confirm_form_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/email-confirm.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
	function categories_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/categories.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	function item_activate_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/item_activate.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	function user_activate_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/user_activate.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	function contact_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/contact.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	function item_contact_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/item_contact.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	function forgot_form_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/forgot.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	function recover_form_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/recover.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	function item_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/item.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	function home_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();

		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/home.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	function search_filters_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/search-filters.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	function search_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/search.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	function login_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/login.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	function logout_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/logout.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	function register_func( $atts ) {
		if($this->checkSetup()!='') return $this->checkSetup();
		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/register.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
}