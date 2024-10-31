<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://osclass.org
 * @since      1.0.0
 *
 * @package    Osclass_Classifieds
 * @subpackage osclass-classifieds/admin
 */

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Osclass_Classifieds
 * @subpackage osclass-classifieds/admin
 * @author     Osclass Team <wordpress@osclass.org>
 */
class Osclass_Classifieds_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		require_once plugin_dir_path( __FILE__ ) . 'partials/osctable.php';
		require_once plugin_dir_path( __FILE__ ) . 'partials/manage-listings.php';
		require_once plugin_dir_path( __FILE__ ) . 'partials/manage-users.php';

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Osclass_Classifieds_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Osclass_Classifieds_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/osclass-classifieds-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Osclass_Classifieds_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Osclass_Classifieds_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/osclass-classifieds-admin.js', array( 'jquery' ), $this->version, false );
		$file_vue = plugin_dir_url(OSCLASS_OC_DIR). 'osclass-classifieds/public/js/vue.js';
		$file_axios = plugin_dir_url(OSCLASS_OC_DIR). 'osclass-classifieds/public/js/axios.min.js';
		$file_jsonp = plugin_dir_url(OSCLASS_OC_DIR). 'osclass-classifieds/admin/js/jsonp.js';
		wp_enqueue_script( OSCLASS_OC_PLUGIN_NAME . '-vue', $file_vue);
		wp_enqueue_script( OSCLASS_OC_PLUGIN_NAME . '-axios', $file_axios);
		wp_enqueue_script( OSCLASS_OC_PLUGIN_NAME . '-jsonp', $file_jsonp);

	}

	function add_menu_page() { 
			add_menu_page(
				__( 'Osclass Classifieds Settings', 'osclass-classifieds' ),
				'Osclass',
				'manage_options',
				'osclass-admin',
				array($this,'home_menu_page'),
				'',
				100
			);

			if(get_option('osclass_settings')===false ||  !isset(get_option('osclass_settings')['osclass_api_url']) || get_option('osclass_settings')['osclass_api_url']=='') {
				return false;
			}

			$endpoint = osclass_oc_api_endpoint();
			$client = new GuzzleHttp\Client();
			$res = $client->request('GET', $endpoint, []);
			$site_exist = json_decode($res->getBody(), true);
			if(is_null($site_exist)) {
				$error = __('Osclass setup is not completed. You should receive an email with an activation link.', 'osclass-classifieds');
				$_SESSION['admin_fm_error'] = $error;
				return false;
			}

			add_submenu_page(
				'osclass-admin',
				'Osclass settings - Osclass classifieds',
				'Osclass settings',
				'manage_options',
				'osc-osclass-settings',
				array($this,'settings_menu_page')
			);
			add_submenu_page(
				'osclass-admin',
				'Manage Listings - Osclass classifieds',
				'Manage Listings',
				'manage_options',
				'osc-manage-listings',
				array($this,'render_manage_listings')
			);
			add_submenu_page(
				'osclass-admin',
				'Manage Users - Osclass classifieds',
				'Manage Users',
				'manage_options',
				'osc-manage-users',
				array($this,'render_manage_users')
			);
	}

	//  Admin table
	function render_manage_listings(){
		//Create an instance of our package class...
		$listTable = new ManageListings();
		//Fetch, prepare, sort, and filter our data...
		$listTable->prepare_items();

		?>
		<div class="wrap">
			<?php echo $listTable->show_fm() ?>
			<h2>Manage Listings</h2>

			<?php if($listTable->auth_error) { ?>
			<div class="uk-alert uk-alert-danger">
				<?php echo sprintf(__('Auth information required or incorrect. <a href="%s">Edit information</a>', 'osclass-classifieds'), '?page=osc-osclass-settings&tab=general-options' ); ?>
			</div>
			<?php } ?>

			<?php echo $listTable->views() ?>

			<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
			<form id="movies-filter" method="get">
				<!-- For plugins, we also need to ensure that the form posts back to our current page -->
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<!-- Now we can render the completed list table -->
				<?php $listTable->display() ?>
			</form>
		</div>
		<?php
	}

	//  Admin table
	function render_manage_users(){
		//Create an instance of our package class...
		$listTable = new ManageUsers();
		//Fetch, prepare, sort, and filter our data...
		$listTable->prepare_items();

		?>
		<div class="wrap">
			<?php echo $listTable->show_fm() ?>
			<h2>Manage Users</h2>

			<?php if($listTable->auth_error) { ?>
			<div class="uk-alert uk-alert-danger">
				<?php echo sprintf(__('Auth information required or incorrect. <a href="%s">Edit information</a>', 'osclass-classifieds'), '?page=osc-osclass-settings&tab=general-options' ); ?>
			</div>
			<?php } ?>

			<?php echo $listTable->views() ?>

			<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
			<form id="movies-filter" method="get">
				<!-- For plugins, we also need to ensure that the form posts back to our current page -->
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<!-- Now we can render the completed list table -->
				<?php $listTable->display() ?>
			</form>
		</div>
		<?php
	}

	function register_osclass_settings() {

		$tab = "general-options";
		if(isset($_GET["tab"]) ) {
			$tab = $_GET["tab"];
		}

		/*
		* SECTION SETTINGS
		*/
		register_setting("osclass_settings_options", "osclass_settings");
		// API INFORMATION
		// add_settings_section("osclass_general_section", "General", '', "general-options");
		// add_settings_field("osclass_api_url", "API url", array($this,"display_api_url_form_element"), "general-options", "osclass_general_section");
		// add_settings_field("osclass_api_admin_username", "Admin username", array($this,"display_api_admin_username_form_element"), "general-options", "osclass_general_section");
		// add_settings_field("osclass_api_admin_password", "Admin password", array($this,"display_api_admin_password_form_element"), "general-options", "osclass_general_section");
		// RECAPTCHA
		add_settings_section("recaptcha_section", "Recaptcha", '', "general-options");
		add_settings_field("osclass_recaptcha_help", "About recaptcha", array($this,"display_recaptcha_help_element"), "general-options", "recaptcha_section");
		
		/*
		* SECTION CATEGORIES
		*/
		register_setting("osclass_categories_options", "osclass_categories");
		// CATEGORIES
		add_settings_section("osclass_categories_section", "Categories", '', "categories-options");
		add_settings_field("osclass_categories_show_count", "Show Ad count in categories", array($this,"display_categories_show_count_form_element"), "categories-options", "osclass_categories_section");
		add_settings_field("osclass_hide_empty_categories", "Hide empty categories?", array($this,"display_hide_empty_categories_form_element"), "categories-options", "osclass_categories_section");

		/*
		* SECTION LISTINGS
		*/
		register_setting("osclass_listings_options", "osclass_listings");
		// LISTINGS SETTINGS
		add_settings_section("osclass_listings_section", "Listings", '', "listings-options");
		// add_settings_field("reg_user_can_contact", "Only users can contact", array($this,"display_reg_user_can_contact_form_element"), "listings-options", "osclass_listings_section");
		add_settings_field("words_in_excerpt", "Words in excerpt", array($this,"display_words_in_excerpt_form_element"), "listings-options", "osclass_listings_section");
		// add_settings_field("order_ad_listings", "Order ad listings", array($this,"display_order_ad_listings_form_element"), "listings-options", "osclass_listings_section");
		add_settings_field("items_per_page", "Items per page", array($this,"display_items_per_page_form_element"), "listings-options", "osclass_listings_section");
		add_settings_field("show_ad_views", "Show ad views", array($this,"display_show_ad_views_form_element"), "listings-options", "osclass_listings_section");

		/*
		* SECTION NOTIFICATIONS
		*/
		register_setting("osclass_notification_options", "osclass_notifications");
		// LISTINGS SETTINGS
		add_settings_section("osclass_user_notification_section", "User notification", '', "notification-options");
		add_settings_field("send_user_welcome_notification", "Welcome User", array($this,"display_user_welcome_notification_form_element"), "notification-options", "osclass_user_notification_section");
		add_settings_field("send_user_ad_created_notification", "Listing Created", array($this,"display_user_ad_created_notification_form_element"), "notification-options", "osclass_user_notification_section");

		add_settings_section("osclass_admin_notification_section", "Admin notification", '', "notification-options");
		add_settings_field("send_admin_ad_created_notification", "Listing Created", array($this,"display_admin_ad_created_notification_form_element"), "notification-options", "osclass_admin_notification_section");
		add_settings_field("send_admin_user_created_notification", "User Created", array($this,"display_admin_user_created_notification_form_element"), "notification-options", "osclass_admin_notification_section");
	}

	// function display_general_content(){echo "Osclass classifieds";}

	// notifications 
	function display_user_welcome_notification_form_element() {
		$checked = (osclass_oc_get_send_user_welcome_notification()) ? "checked='checked'" : '';  ?>
	<input type="checkbox" name="osclass_notifications[send_user_welcome_notification]" id="send_user_welcome_notification" value="1" <?php echo $checked; ?>/>
	<span class="description"><?php _e('A welcome email will be sent when the user is created.', 'osclass-classifieds'); ?></span>
	<?php }

	function display_user_ad_created_notification_form_element() {
		$checked = (osclass_oc_get_send_user_ad_created_notification()) ? "checked='checked'" : '';  ?>
	<input type="checkbox" name="osclass_notifications[send_user_ad_created_notification]" id="send_user_ad_created_notification" value="1" <?php echo $checked; ?>/>
	<span class="description"><?php _e('An email will be sent when a listing is created.', 'osclass-classifieds'); ?></span>
	<?php }

	function display_admin_user_created_notification_form_element() {
		$checked = (osclass_oc_get_send_admin_user_created_notification()) ? "checked='checked'" : '';  ?>
	<input type="checkbox" name="osclass_notifications[send_admin_user_created_notification]" id="send_admin_user_created_notification" value="1" <?php echo $checked; ?>/>
	<span class="description"><?php _e('An email will be sent when a user is created.', 'osclass-classifieds'); ?></span>
	<?php }

	function display_admin_ad_created_notification_form_element() {
		$checked = (osclass_oc_get_send_admin_ad_created_notification()) ? "checked='checked'" : '';  ?>
	<input type="checkbox" name="osclass_notifications[send_admin_ad_created_notification]" id="send_admin_ad_created_notification" value="1" <?php echo $checked; ?>/>
	<span class="description"><?php _e('An email will be sent when a listing is created.', 'osclass-classifieds'); ?></span>
	<?php }

	// listings
	function display_reg_user_can_contact_form_element() {
		$o = get_option('osclass_listings')['reg_user_can_contact'];
		$checked = (!is_null($o) && $o=="1") ? "checked='checked'" : ''; ?>
	<input type="checkbox" name="osclass_listings[reg_user_can_contact]" id="reg_user_can_contact" value="1" <?php echo $checked; ?>/>
	<?php }

	function display_words_in_excerpt_form_element() { ?>
		<input type="text" name="osclass_listings[words_in_excerpt]" id="words_in_excerpt" value="<?php echo osclass_oc_words_in_excerpt(); ?>" />
		<span class="description"><?php _e('Indicates the number of words that will appear in the description of the ads in the searches (by default they are 15 words)', 'osclass-classifieds'); ?></span>
	<?php }

	/* 
	function display_order_ad_listings_form_element() { ?>
	 	<input type="text" name="osclass_listings[order_ad_listings]" id="order_ad_listings" value="<?php echo get_option('osclass_listings')['order_ad_listings']; ?>" />
	<?php }
	*/

	function display_items_per_page_form_element() { ?>
		<input type="text" name="osclass_listings[items_per_page]" id="items_per_page" value="<?php echo get_option('osclass_listings')['items_per_page']; ?>" />
		<span class="description"><?php _e('Indicates how many ads you want to show on each results page (default is 10)', 'osclass-classifieds'); ?></span>
	<?php }

	function display_show_ad_views_form_element() {
		$o = get_option('osclass_listings')['show_ad_views'];
		$checked = (!is_null($o) && $o=="1") ? "checked='checked'" : ''; ?>
	<input type="checkbox" name="osclass_listings[show_ad_views]" id="show_ad_views" value="1" <?php echo $checked; ?>/>
	<span class="description"><?php _e('Leave it checked if you want your users to know how many times an ad has been visited', 'osclass-classifieds'); ?></span>
	<?php }

	// categories
	function display_categories_show_count_form_element() {
		$o = get_option('osclass_categories')['osclass_categories_show_count'];
		$checked = (!is_null($o) && $o=="1") ? "checked='checked'" : ''; ?>
	<input type="checkbox" name="osclass_categories[osclass_categories_show_count]" id="osclass_categories_show_count" value="1" <?php echo $checked; ?>/>
	<span class="description"><?php _e('Check this option if you want to display the number of ads published in each category of your classifieds site.', 'osclass-classifieds'); ?></span>
	<?php }

	function display_hide_empty_categories_form_element(){
		$o = isset(get_option('osclass_categories')['osclass_hide_empty_categories']) ? true: false;
		$checked = (!is_null($o) && $o=="1") ? "checked='checked'" : ''; ?>
	<input type="checkbox" name="osclass_categories[osclass_hide_empty_categories]" id="osclass_hide_empty_categories" value="1" <?php echo $checked; ?>/>
	<span class="description"><?php _e('The categories without ads do not appear in the list until they have at least one published ad.', 'osclass-classifieds'); ?></span>
	<?php }

	// settings
	function display_recaptcha_help_element() { ?>
		<div><?php echo sprintf(__('We recommend you to install and configure <a href="%s">google-captcha plugin</a> in order to avoid spam.', 'osclass-classifieds'), 'https://wordpress.org/plugins/google-captcha/' ); ?>
		<br><?php _e('Just install and configure google-captcha plugin and captcha boxes will automatically appear.', 'osclass-classifieds'); ?></div>
	<?php }

	function display_api_url_form_element(){ ?>
	<input type="text" name="osclass_settings[osclass_api_url]" id="osclass_api_url" value="<?php echo get_option('osclass_settings')['osclass_api_url']; ?>" />
	<?php }

	function display_api_admin_username_form_element(){ ?>
	<input type="text" name="osclass_settings[osclass_api_admin_username]" id="osclass_api_admin_username" value="<?php echo get_option('osclass_settings')['osclass_api_admin_username']; ?>" />
	<?php }

	function display_api_admin_password_form_element(){ ?>
	<input type="password" name="osclass_settings[osclass_api_admin_password]" id="osclass_api_admin_password" value="<?php echo get_option('osclass_settings')['osclass_api_admin_password']; ?>" />
	<?php }

	function display_api_key_form_element(){ ?>
	<input type="text" name="osclass_settings[osclass_api_key]" id="osclass_api_key" value="<?php echo get_option('osclass_settings')['osclass_api_key']; ?>" />
	<?php }

	function home_menu_page() {
		if ( function_exists('current_user_can') && !current_user_can('manage_options') )
        die(__('Cheatin&#8217; uh?', 'osclass-classifieds'));
		if(isset($_GET['reset']) && $_GET['reset']=='keys') {
			osclass_oc_clear_plugin_settings();
		}
		if( isset($_REQUEST['install']) && $_REQUEST['install']==1) {
			return $this->create_site();
		}
		$stats_items = $stats_users = array();
		$is_setup = false;
		if(get_option('osclass_settings')!==false &&  isset(get_option('osclass_settings')['osclass_api_url']) && get_option('osclass_settings')['osclass_api_url']!='') {
			$is_setup = true;
			$stats_items = osclass_oc_api_get_items_stats();
			$stats_users = osclass_oc_api_get_users_stats();
		}
?>
	<style>
		.osc-admin-content {
			width: 78%;
			float: left;
		}
		.osc-admin-sidebar {
			float:right;
			width: 20%;
		}
		.osc-admin-sidebar .postbox-container {
			float:right;
		}
		@media only screen and (max-width: 960px) {
			.osc-admin-sidebar {
				float: none;
				width: 100%;
			}
			.osc-admin-content {
				width: 100%;
				float: none;
			}
			.osc-admin-sidebar .postbox-container {
				float: none;
			}
		}
	</style>
	<div style="margin: 10px 20px 0 2px;">
		<h1 style="font-size: 23px;font-weight: 400;margin: 0;padding: 9px 0 4px;line-height: 29px; padding-bottom: 12px;">Osclass classifieds</h1>
		<div class="osc-admin-content">
			<div class="metabox-holder">
				<div class="meta-box-sortables">
					<div class="postbox">
						<h3 class="stuffbox"><span class="">Osclass Classifieds Plugin Stats</span></h3>
						<div class="inside">
						<?php $fm_error  = false;
							if(isset($_SESSION['admin_fm_error']))  {
								$fm_error = $_SESSION['admin_fm_error'];
								unset($_SESSION['admin_fm_error']);
								$is_setup= false; ?>
								<div class="uk-alert uk-alert-danger"><?php echo $fm_error; ?></div>
							<?php } ?>

							<?php if($fm_error===false && $is_setup===false) { ?>
							<div style="text-align: center;">
								<h2 style="font-weight: 300; line-height: 36px; padding: 30px;">You are about to create a classifieds page for your Wordpress website. Please note that this classifieds plugin will not work until you have validated your account with the e-mail that we will send you.</h2>
								<p><a class="button button-primary button-hero" href="?page=osclass-admin&install=1">Start Your Site</a></p>
								<div>Osclass classifieds: <strong><?php echo  OSCLASS_OC_PLUGIN_VERSION; ?></div>
							</div>
							<?php } ?>
							<?php if($is_setup) { ?>
							<ul>
								<li>Osclass classifieds: <strong><?php echo  OSCLASS_OC_PLUGIN_VERSION; ?></strong></li>
								<li><p><a class="button button-primary" target="_blank" href="<?php echo osclass_oc_home_url(); ?>">Your classifieds page</a></p></li>
								<li>Number of active listings currently in the system: <strong><?php echo @$stats_items['total']; ?></strong></li>
								<li>Number of inactive/expired/disabled listings currently in the system: <strong><?php echo @$stats_items['total_inactive']; ?></strong></li>
							</ul>
							<table>
								<tbody>
									<tr>
										<td>Total listings</td>
										<td style="padding: 0px 10px; background: aliceblue;"><?php echo @$stats_items['total']; ?></td>
										<td style="width:15px;">&nbsp;</td>
										<td>Listings last 30 days</td>
										<td style="padding: 0px 10px; background: aliceblue;"><strong><?php echo @$stats_items['items_last_30_days']; ?></strong></td>
									</tr>
									<tr>
										<td>Total users</td>
										<td style="padding: 0px 10px; background: aliceblue;"><?php echo @$stats_users['total']; ?></td>
										<td style="width:15px;">&nbsp;</td>
										<td>Users last 30 days</td>
										<td style="padding: 0px 10px; background: aliceblue;"><strong><?php echo @$stats_users['users_last_30_days']; ?></strong></td>
									</tr>
								</tbody>
							</table>
							<div style="clear:both"></div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="osc-admin-sidebar" style="">
			<div class="postbox-container" style="width: 100%;">
				<div class="metabox-holder">
					<div class="meta-box-sortables">
						<div style="min-width: 100%;" class="postbox">
							<h3 class="stuffbox"><span class=""><?php _e('Like this plugin?', 'osclass-classifieds'); ?></span></h3>
							<div class="inside">
							<p><?php _e('Why not do any or all of the following:', 'osclass-classifieds'); ?></p>
								<ul>
									<li class="li_link">
										<a href="<?php echo OSCLASS_OC_PLUGIN_WP_URL; ?>">
											<?php _e('Give it a good rating on WordPress.org.', 'osclass-classifieds'); ?>
										</a>
									</li>
									<li class="li_link">
										<a href="https://osclass.org/">
											<?php _e('Know more about Osclass.', 'osclass-classifieds'); ?>
										</a>
									</li>
								</ul>
							</div>
						</div> 
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php }

	function settings_menu_page(){
		if ( function_exists('current_user_can') && !current_user_can('manage_options') )
        die(__('Cheatin&#8217; uh?', 'osclass-classifieds'));
	?>
		<div class="wrap">

			<?php if(isset($_REQUEST['settings-updated']) && $_REQUEST['settings-updated']==="true")  { ?>
			<div class="uk-alert uk-alert-success">
				<?php _e('Saved correctly', 'osclass-classifieds'); ?>
			</div>
			<?php } ?>

            <div id="icon-options-general" class="icon32"></div>
			<h1><?php _e('Options', 'osclass-classifieds'); ?></h1>
			<?php
                //we check if the page is visited by click on the tabs or on the menu button.
                //then we get the active tab.
                $active_tab = isset($_GET['tab']) ? $_GET['tab'] : "general-options";
				if($active_tab == "categories-options")
				{
					$active_tab = "categories-options";
				}
				else if($active_tab == "listings-options")
				{
					$active_tab = "listings-options";
				}
				else if($active_tab == "notification-options")
				{
					$active_tab = "notification-options";
				}
				else
				{
					$active_tab = "general-options";
				}
            ?>

			<!-- wordpress provides the styling for tabs. -->
            <h2 class="nav-tab-wrapper">
                <!-- when tab buttons are clicked we jump back to the same page but with a new parameter that represents the clicked tab. accordingly we make it active -->
                <a href="?page=osc-osclass-settings&tab=general-options" class="nav-tab <?php if($active_tab == 'general-options'){echo 'nav-tab-active';} ?> "><?php _e('General', 'osclass-classifieds'); ?></a>
                <a href="?page=osc-osclass-settings&tab=categories-options" class="nav-tab <?php if($active_tab == 'categories-options'){echo 'nav-tab-active';} ?>"><?php _e('Categories', 'osclass-classifieds'); ?></a>
                <a href="?page=osc-osclass-settings&tab=listings-options" class="nav-tab <?php if($active_tab == 'listings-options'){echo 'nav-tab-active';} ?>"><?php _e('Listings', 'osclass-classifieds'); ?></a>
                <a href="?page=osc-osclass-settings&tab=notification-options" class="nav-tab <?php if($active_tab == 'notification-options'){echo 'nav-tab-active';} ?>"><?php _e('Notifications', 'osclass-classifieds'); ?></a>
            </h2>
			<form method="post" action="options.php">
				<?php
					if(isset($_GET["tab"]) && $_GET["tab"] == "general-options" || !isset($_GET["tab"]) ) {
						settings_fields("osclass_settings_options");
						do_settings_sections("general-options");
					}

					if(isset($_GET["tab"]) && $_GET["tab"] == "listings-options") {
						settings_fields("osclass_listings_options");
						do_settings_sections("listings-options");
					}

					if(isset($_GET["tab"]) && $_GET["tab"] == "categories-options") {
						settings_fields("osclass_categories_options");
						do_settings_sections("categories-options");
					}

					if(isset($_GET["tab"]) && $_GET["tab"] == "notification-options") {
						settings_fields("osclass_notification_options");
						do_settings_sections("notification-options");
					}
                    submit_button();
                ?>
            </form>
		</div>
<?php
	}

	function create_site() {
		require_once(OSCLASS_OC_DIR.'/admin/partials/create.php');
	}

}
