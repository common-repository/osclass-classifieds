<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Osclass_Classifieds
 * @subpackage osclass-classifieds/includes
 * @author     Osclass Team <wordpress@osclass.org>
 */
class Osclass_Classifieds {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Osclass_Classifieds_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = OSCLASS_OC_PLUGIN_NAME;
		$this->version = OSCLASS_OC_PLUGIN_VERSION;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Osclass_Classifieds_Loader. Orchestrates the hooks of the plugin.
	 * - Osclass_Classifieds_i18n. Defines internationalization functionality.
	 * - Osclass_Classifieds_Admin. Defines all hooks for the admin area.
	 * - Osclass_Classifieds_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/osclass-classifieds-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/osclass-classifieds-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/osclass-classifieds-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/osclass-classifieds-public.php';

		$this->loader = new Osclass_Classifieds_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Osclass_Classifieds_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Osclass_Classifieds_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Osclass_Classifieds_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menu_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_osclass_settings' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Osclass_Classifieds_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'shortcodes_init' );
		$this->loader->add_action( 'init', $plugin_public, 'checkToken' );
		$this->loader->add_action( 'query_vars', $plugin_public, 'custom_query_vars_filter' ); // query params

		add_action('init', function() {
			add_action( 'wp_enqueue_scripts', function($hook) {
				global $post;
				if(isset($post->post_name)) {
					$file_vue = plugin_dir_url(OSCLASS_OC_DIR). 'osclass-classifieds/public/js/vue.js';
					$file_axios = plugin_dir_url(OSCLASS_OC_DIR). 'osclass-classifieds/public/js/axios.min.js';

					if( $post->post_name === 'osc-item-form' ||
						$post->post_name === 'osc-item-form-edit' ||
						$post->post_name === 'osc-advanced-search' ) {
						// add_filter( 'the_title', '__return_false' );

						wp_enqueue_script( OSCLASS_OC_PLUGIN_NAME . '-vue', $file_vue);

						wp_enqueue_script( OSCLASS_OC_PLUGIN_NAME . '-axios', $file_axios);
						wp_enqueue_script( 'jquery');

					}
					if( strpos($post->post_name, 'osclass-classifieds-')===0 ) {
					// if( $post->post_name === 'osc-item-contact' ||
					// 	$post->post_name === 'osc-my-account' ) {
						add_filter( 'the_title', '__return_false' );
					}
					if($post->post_name === 'osc-item') {
						// add_filter( 'the_title', '__return_false' );

						$file_bxslider_js = plugin_dir_url(OSCLASS_OC_DIR). 'osclass-classifieds/public/js/jquery.bxslider.js';
						wp_enqueue_script( OSCLASS_OC_PLUGIN_NAME . '-bxslider-js', $file_bxslider_js, array('jquery'));

						$file_bxslider_css = plugin_dir_url(OSCLASS_OC_DIR). 'osclass-classifieds/public/css/jquery.bxslider.css';
						wp_enqueue_style( OSCLASS_OC_PLUGIN_NAME . '-bxslider-css', $file_bxslider_css );
					}
				}
			}, 10, 1 );
		});
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Osclass_Classifieds_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
