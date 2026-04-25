<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Xophz_Compass_Magic_Formula
 * @subpackage Xophz_Compass_Magic_Formula/includes
 */

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
 * @package    Xophz_Compass_Magic_Formula
 * @subpackage Xophz_Compass_Magic_Formula/includes
 * @author     Your Name <email@example.com>
 */
class Xophz_Compass_Magic_Formula {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Xophz_Compass_Magic_Formula_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'XOPHZ_COMPASS_MAGIC_FORMULA_VERSION' ) ) {
			$this->version = XOPHZ_COMPASS_MAGIC_FORMULA_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'xophz-compass-magic-formula';

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
	 * - Xophz_Compass_Magic_Formula_Loader. Orchestrates the hooks of the plugin.
	 * - Xophz_Compass_Magic_Formula_i18n. Defines internationalization functionality.
	 * - Xophz_Compass_Magic_Formula_Admin. Defines all hooks for the admin area.
	 * - Xophz_Compass_Magic_Formula_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xophz-compass-magic-formula-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xophz-compass-magic-formula-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-xophz-compass-magic-formula-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-xophz-compass-magic-formula-public.php';

        /**
		 * The class responsible for defining all REST API Endpoints.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xophz-compass-magic-formula-rest.php';

        /**
		 * The class responsible for WPMU DEV Forminator/Hustle integrations.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xophz-compass-magic-formula-wpmudev.php';

		$this->loader = new Xophz_Compass_Magic_Formula_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Xophz_Compass_Magic_Formula_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Xophz_Compass_Magic_Formula_i18n();

		$this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain', 5 );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Xophz_Compass_Magic_Formula_Admin( $this->get_xophz_compass_magic_formula(), $this->get_version() );
        $plugin_rest = new Xophz_Compass_Magic_Formula_REST();
        $plugin_wpmudev = new Xophz_Compass_Magic_Formula_WPMUDEV();

		// $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		// $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'addToMenu' );
        
        $this->loader->add_action( 'rest_api_init', $plugin_rest, 'register_routes' );
        $plugin_wpmudev->init_hooks();
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Xophz_Compass_Magic_Formula_Public( $this->get_xophz_compass_magic_formula(), $this->get_version() );

		// Register Spark with Event Horizon Registry
		$this->loader->add_filter( 'xophz_register_sparks', $plugin_public, 'register_spark' );
		$this->loader->add_filter( 'xophz_get_spark_manifest', $plugin_public, 'get_spark_manifest', 10, 2 );

		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

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
	public function get_xophz_compass_magic_formula() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Xophz_Compass_Magic_Formula_Loader    Orchestrates the hooks of the plugin.
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
