<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Xophz_Compass_Magic_Formula
 * @subpackage Xophz_Compass_Magic_Formula/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Xophz_Compass_Magic_Formula
 * @subpackage Xophz_Compass_Magic_Formula/public
 * @author     Your Name <email@example.com>
 */
class Xophz_Compass_Magic_Formula_Public {

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
		 * defined in Xophz_Compass_Magic_Formula_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Xophz_Compass_Magic_Formula_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/xophz-compass-magic-formula-public.css', array(), $this->version, 'all' );

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
		 * defined in Xophz_Compass_Magic_Formula_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Xophz_Compass_Magic_Formula_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/xophz-compass-magic-formula-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the Spark with the Event Horizon registry
	 */
	public function register_spark( $sparks ) {
		$sparks['xophz-magic-formula'] = array(
			'title'       => __( 'Magic Formulas', 'xophz-compass-magic-formula' ),
			'description' => __( 'Forms and Data collection.', 'xophz-compass-magic-formula' ),
			'icon'        => 'fal fa-magic',
			'categories'  => array( 'system', 'productivity' ),
			'version'     => $this->version,
			'author'      => 'Hall of the Gods, Inc.'
		);
		return $sparks;
	}

	/**
	 * Return the structural manifest for rendering the Spark in YouMeOS
	 */
	public function get_spark_manifest( $manifest, $spark_id ) {
		if ( 'xophz-magic-formula' !== $spark_id ) {
			return $manifest;
		}

		return array(
			'id' => 'xophz-magic-formula',
			'meta' => array(
				'title' => 'Magic Formulas',
				'icon' => 'fal fa-magic',
				'dimensions' => array(
					'width' => 900,
					'height' => 700
				)
			),
			'navigation' => array(
				'items' => array(
					array( 'id' => 'dashboard', 'title' => 'Forms Dashboard', 'icon' => 'fal fa-list-alt' ),
					array( 'id' => 'settings', 'title' => 'Formula Settings', 'icon' => 'fal fa-cog' )
				),
				'defaultActive' => 'dashboard'
			),
			'views' => array(
				'dashboard' => array(
					'type' => 'layout',
					'root' => array(
						'type' => 'v-container',
						'props' => array( 'fluid' => true ),
						'children' => array(
							array(
								'type' => 'x-card',
								'props' => array(
									'title' => 'Forms Connection',
									'variant' => 'glass'
								),
								'children' => array(
									array(
										'type' => 'v-card-text',
										'content' => 'Magic Formulas Proxy connected and listening.'
									)
								)
							)
						)
					)
				),
				'settings' => array(
					'type' => 'html',
					'content' => '<div class="pa-4 text-center">Settings coming soon.</div>'
				)
			)
		);
	}
}
