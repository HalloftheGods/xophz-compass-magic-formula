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

		// Preload nested Forminator scripts early
		// Forminator sets in_footer=false for its scripts, so they MUST be enqueued before wp_head.
		// If [magic_gate_formula] runs during the_content, wp_head has already fired and the scripts are lost.
		global $post;
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'magic_gate_formula' ) ) {
			preg_match_all( '/' . get_shortcode_regex( array( 'magic_gate_formula' ) ) . '/s', $post->post_content, $matches, PREG_SET_ORDER );
			foreach ( $matches as $shortcode ) {
				if ( isset( $shortcode[3] ) ) {
					$atts = shortcode_parse_atts( $shortcode[3] );
					if ( is_array( $atts ) ) {
						$gated_id   = isset( $atts['gated_id'] ) ? $atts['gated_id'] : '';
						$default_id = isset( $atts['default_id'] ) ? $atts['default_id'] : '';
						
						// Instead of running do_shortcode early (which causes object cache conflicts and render_id collisions),
						// we manually load the models and call enqueue_form_scripts().
						if ( class_exists( 'Forminator_Base_Form_Model' ) && class_exists( 'Forminator_CForm_Front' ) ) {
							$forminator_front = Forminator_CForm_Front::get_instance();
							if ( ! empty( $gated_id ) ) {
								$model = Forminator_Base_Form_Model::get_model( $gated_id );
								if ( $model instanceof Forminator_Form_Model ) {
									$forminator_front->model = $model;
									$forminator_front->enqueue_form_scripts( false );
								}
							}
							if ( ! empty( $default_id ) ) {
								$model = Forminator_Base_Form_Model::get_model( $default_id );
								if ( $model instanceof Forminator_Form_Model ) {
									$forminator_front->model = $model;
									$forminator_front->enqueue_form_scripts( false );
								}
							}
						}
					}
				}
			}
		}

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

	/**
	 * Renders a clean preview iframe for the Magic Formula UI builder.
	 */
	public function render_magic_preview() {
		if ( isset( $_GET['magic_preview_form'] ) && current_user_can( 'manage_options' ) ) {
			$form_id = intval( $_GET['magic_preview_form'] );
			if ( $form_id ) {
				add_filter('show_admin_bar', '__return_false');
				echo '<!DOCTYPE html><html><head><meta charset="utf-8">';
				wp_head();
				echo '<style>
					body, html { background: transparent !important; }
					body { padding: 20px; overflow-x: hidden; }
					#wpadminbar { display: none !important; }
				</style>';
				echo '</head><body class="magic-preview-body">';
				echo do_shortcode( '[forminator_form id="' . $form_id . '"]' );
				wp_footer();
				echo '</body></html>';
				exit;
			}
		}
	}

	/**
	 * Renders the Magic Gate Formula shortcode.
	 * Allows gating Forminator forms based on user authentication and roles.
	 *
	 * @since 1.0.0
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output of the resolved form.
	 */
	public function render_magic_gate_formula( $atts ) {
		// Prevent page caching for pages containing this shortcode
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}

		$atts = shortcode_atts( array(
			'default_id' => '',
			'gated_id'   => '',
			'access'     => '', // Comma-separated list of allowed roles
		), $atts, 'magic_gate_formula' );

		$default_id = sanitize_text_field( $atts['default_id'] );
		$gated_id   = sanitize_text_field( $atts['gated_id'] );
		$access_str = sanitize_text_field( $atts['access'] );
		
		$container_id = 'magic-gate-' . uniqid();

		// Bypassing Object/Page Caches by rendering an empty placeholder and loading via AJAX.
		ob_start();
		?>
		<div id="<?php echo esc_attr( $container_id ); ?>" class="xophz-magic-gate-loader" style="min-height: 100px; display: flex; justify-content: center; align-items: center;">
			<div class="xophz-spinner" style="width: 40px; height: 40px; border: 3px solid rgba(0,0,0,0.1); border-radius: 50%; border-top-color: #62c9ff; animation: magic-spin 1s ease-in-out infinite;"></div>
		</div>
		<style>@keyframes magic-spin { to { transform: rotate(360deg); } }</style>
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			if ( typeof jQuery !== 'undefined' ) {
				jQuery.post( '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
					action: 'xophz_magic_gate_render',
					gated_id: '<?php echo esc_js( $gated_id ); ?>',
					default_id: '<?php echo esc_js( $default_id ); ?>',
					access: '<?php echo esc_js( $access_str ); ?>',
					_ajax_nonce: '<?php echo wp_create_nonce( 'xophz_magic_gate_nonce' ); ?>'
				}, function( response ) {
					if ( response && response.success ) {
						jQuery('#<?php echo esc_js( $container_id ); ?>').replaceWith( response.data.html );
					}
				});
			}
		});
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * AJAX endpoint to render the Magic Gate.
	 */
	public function ajax_render_magic_gate_formula() {
		check_ajax_referer( 'xophz_magic_gate_nonce' );

		$default_id = isset( $_POST['default_id'] ) ? sanitize_text_field( $_POST['default_id'] ) : '';
		$gated_id   = isset( $_POST['gated_id'] ) ? sanitize_text_field( $_POST['gated_id'] ) : '';
		$access_str = isset( $_POST['access'] ) ? sanitize_text_field( $_POST['access'] ) : '';

		$allowed_roles = array();
		if ( ! empty( $access_str ) ) {
			$access_str = trim( $access_str, '[]\'"' );
			$allowed_roles = array_filter( array_map( 'trim', explode( ',', str_replace( array( '"', '\'' ), '', $access_str ) ) ) );
		}

		$show_gated = false;
		$user_id    = get_current_user_id();

		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$user_roles = (array) $user->roles;

			if ( empty( $allowed_roles ) ) {
				$show_gated = true;
			} else {
				foreach ( $allowed_roles as $allowed_role ) {
					if ( in_array( $allowed_role, $user_roles, true ) ) {
						$show_gated = true;
						break;
					}
				}
			}
		}

		// Pass empty atts since we only have the raw ids now
		$atts = array( 'gated_id' => $gated_id, 'default_id' => $default_id, 'access' => $access_str );
		$show_gated = apply_filters( 'xophz_compass_magic_gate_show_gated', $show_gated, $atts, $user_id );

		$output = '<div class="magic-gate-wrapper">';

		if ( $show_gated && ! empty( $gated_id ) ) {
			$output .= '<div class="magic-gate-gated">';
			$output .= do_shortcode( '[forminator_form id="' . esc_attr( $gated_id ) . '"]' );
			$output .= '</div>';
		} elseif ( ! $show_gated && ! empty( $default_id ) ) {
			$output .= '<div class="magic-gate-default">';
			$output .= do_shortcode( '[forminator_form id="' . esc_attr( $default_id ) . '"]' );
			$output .= '</div>';
		}

		$output .= '</div>';

		wp_send_json_success( array( 'html' => $output ) );
	}
			$output .= '</div>';
		} elseif ( ! empty( $default_id ) ) {
			$output .= '<div class="magic-gate-default">';
			$output .= do_shortcode( '[forminator_form id="' . esc_attr( $default_id ) . '"]' );
			$output .= '</div>';
		}
		
		$output .= '</div>';

		/**
		 * Filter to allow modifying the final output of the gate.
		 *
		 * @param string $output     The final HTML output.
		 * @param bool   $show_gated Whether the gated form is shown.
		 * @param array  $atts       The shortcode attributes.
		 */
		return apply_filters( 'xophz_compass_magic_gate_output', $output, $show_gated, $atts );
	}
}
